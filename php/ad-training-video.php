<?php

if(checkloggedin()) {
    update_lastactive();
}

if(!isset($_GET['id']))
{
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit;
}

if(!empty($_GET['notification_id'])){
    update_notification_status($_GET['notification_id']);
}

$num_rows = ORM::for_table($config['db']['pre'].'product')
    ->where('id',$_GET['id'])
    ->count();
$item_custom = array();
$item_custom_textarea = array();
$item_checkbox = array();

if ($num_rows > 0) {

    $info = ORM::for_table($config['db']['pre'].'product')->find_one($_GET['id']);
    // output data of each row
    update_itemview($_GET['id']);
    $item_id = $info['id'];
    $item_title = $info['product_name'];
    $item_status = $info['status'];
    $item_featured = $info['featured'];
    $item_urgent = $info['urgent'];
    $item_highlight = $info['highlight'];
    $item_description = nl2br(stripcslashes($info['description']));
    $showmore = (strlen($item_description) > 1000)? 1 : 0;
    $item_tag = $info['tag'];
    $item_location = $info['location'];
    $item_city = get_cityName_by_id($info['city']);
    $item_state = get_stateName_by_id($info['state']);
    $item_country = get_countryName_by_id($info['country']);
    $item_view = thousandsCurrencyFormat($info['view']);
    $item_created_at = timeAgo($info['created_at']);
    $item_catid = $info['category'];
    $get_main = get_maincat_by_id($info['category']);
    $get_sub = get_subcat_by_id($info['sub_category']);
    $item_category = $get_main['cat_name'];
    $item_sub_category = $get_sub['sub_cat_name'];
    $item_title_slug = create_slug($item_title);
    $item_link = $link['POST-DETAIL'].'/'.$item_id.'/'.$item_title_slug;
    $post_id = $info['id'];
    $post_user_id = $info['user_id'];
    $quote_link = $link['POST-QUOTE'].'/'.$post_id.'/'.$post_user_id;
    $item_catlink = $link['SEARCH_CAT'].'/'.$get_main['slug'];
    $item_subcatlink = $link['SEARCH_CAT'].'/'.$get_main['slug'].'/'.$get_sub['slug'];
    $item_phone = $info['phone'];
    $item_hide_phone = $info['hide_phone'];
}
else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit;
}

$trainingVideoArr = array();
$trainingResult = ORM::for_table($config['db']['pre'].'training_gallery')
        ->where('product_id', $item_id)
        ->find_many();
if(count($trainingResult) > 0){
    foreach($trainingResult as $key => $training){
        $trainingVideoArr[$training['id']]['id'] = $training['id'];
        $trainingVideoArr[$training['id']]['product_id'] = $training['product_id'];
        $trainingVideoArr[$training['id']]['training_video'] = $training['training_video'];
    }
} else {
    $trainingVideoArr = [];
}

if (isset($_GET['action'])) {
    global $config, $lang, $link;
    $customError = '';
    $success = '';
    if ($_GET['action'] == "post_training_video" && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["trainingVideo"])) {
        
        // Define the target directory for storing video files
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/training_video/';
        // Create the target directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $allowedExtensions = ["mp4", "avi", "mov", "mkv"];
        $maxSizeMB = (int)$_POST["max_size"];
    
        // Check if the file has no errors
        if ($_FILES["trainingVideo"]["error"] === UPLOAD_ERR_OK) {
            // Validate file size
            $maxSizeBytes = $maxSizeMB * 1024 * 1024; // Convert MB to bytes
            if ($_FILES["trainingVideo"]["size"] <= $maxSizeBytes) {
                // Validate file extension
                $fileExtension = strtolower(pathinfo($_FILES["trainingVideo"]["name"], PATHINFO_EXTENSION));
                if (in_array($fileExtension, $allowedExtensions)) {
                    $trainingVideoFileName = $_FILES['trainingVideo']['name'];
                    $trainingVideoTempFileName = $_FILES['trainingVideo']['tmp_name'];
                    if ($trainingVideoTempFileName != '') {
                        $extension = pathinfo($trainingVideoFileName, PATHINFO_EXTENSION);
                        $trainingVideoNewFileName = microtime(true) . '.' . $extension;
                        if (!empty($trainingVideoNewFileName)) {
                            $trainingVideoFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/training_video/' . $trainingVideoNewFileName;
                            if (move_uploaded_file($trainingVideoTempFileName, $trainingVideoFilePath)) {
                                //Insert record in Training Gallery
                                $tGInsert = ORM::for_table($config['db']['pre'] . 'training_gallery')->create();
                                $tGInsert->product_id = $_POST["productId"];
                                $tGInsert->training_video = $trainingVideoNewFileName;
                                if($tGInsert->save()){
                                    $success = "File " . htmlspecialchars(basename($_FILES["trainingVideo"]["name"])) . " has been uploaded successfully.";;
                                }
                                $tGInsert->save();
                            } else {
                                $customError = "Error moving the uploaded file.";
                            }
                        }
                    }
                } else {
                    $customError = "Invalid file extension. Allowed extensions: " . implode(", ", $allowedExtensions);
                }
            } else {
                $customError = "File size exceeds the maximum allowed size of {$maxSizeMB} MB.";
            }
        } else {
            $customError = "Error uploading the file.";
        }
    }
}

$mailsent =0;
$errors = 0;
$error = '';
if(empty($success)){
    $success = '';
}
if(empty($customError)){
    $customError = '';
}
$recaptcha_error = '';
$GetCategory = get_maincategory();
$cat_dropdown = get_categories_dropdown($lang);
$meta_desc = substr(strip_tags($item_description),0,150);
$meta_desc = trim(preg_replace('/\s\s+/', ' ', $meta_desc));
// Output to template
$page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/ad-training-video.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header($item_title,$meta_desc,true));
$page->SetParameter ('ITEM_ID', $item_id);
$page->SetParameter ('ITEM_TITLE', $item_title);
$page->SetLoop ('TRAINING_VIDEO', $trainingVideoArr);
$page->SetParameter ('ITEM_CATEGORY', $item_category);
$page->SetParameter ('ITEM_SUB_CATEGORY', $item_sub_category);
$page->SetParameter ('ITEM_LINK', $item_link);
$page->SetParameter ('ITEM_CATLINK', $item_catlink);
$page->SetParameter ('ITEM_SUBCATLINK', $item_subcatlink);
$page->SetParameter('ERROR', $error);
$page->SetParameter('SUCCESS', $success);
$page->SetParameter('CUSTOMERROR', $customError);
$page->SetParameter ('RECAPTCH_ERROR', $recaptcha_error);
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
?>