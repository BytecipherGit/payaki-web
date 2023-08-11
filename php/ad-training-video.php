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
$recaptcha_error = '';
$GetCategory = get_maincategory();
$cat_dropdown = get_categories_dropdown($lang);
$meta_desc = substr(strip_tags($item_description),0,150);
$meta_desc = trim(preg_replace('/\s\s+/', ' ', $meta_desc));
// Output to template
$page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/ad-training-video.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header($item_title,$meta_desc,$meta_image,true));
// $page->SetParameter ('TOTAL_ITEMS', count($item));
// $page->SetLoop ('ITEM', $item);
// $page->SetParameter ('CAT_DROPDOWN',$cat_dropdown);
// $page->SetLoop ('CATEGORY',$GetCategory);
// $page->SetLoop ('ITEM_SCREENSHOT', $item_screenshot);
// $page->SetParameter ('ITEM_CUSTOMFIELD', $item_custom_field);
// $page->SetLoop ('ITEM_CUSTOM', $item_custom);
// $page->SetLoop ('ITEM_CUSTOM_TEXTAREA', $item_custom_textarea);
// $page->SetLoop ('ITEM_CUSTOM_CHECKBOX', $item_checkbox);
// $page->SetParameter ('QUICKCHAT_URL', $quickchat_url);
// $page->SetParameter ('CUSTOMCHAT_URL', $customChatUrl);
// $page->SetParameter ('POST_AUTHOR_ID', $item_author_id);
// $page->SetParameter ('LOGGEDIN_USER_ID', $_SESSION['user']['id']);
// $page->SetParameter ('ITEM_FAVORITE', check_product_favorite($item_id));
$page->SetParameter ('ITEM_ID', $item_id);
$page->SetParameter ('ITEM_TITLE', $item_title);
// $page->SetParameter ('ITEM_FEATURED', $item_featured);
// $page->SetParameter ('ITEM_URGENT', $item_urgent);
// $page->SetParameter ('ITEM_HIGHLIGHT', $item_highlight);
// $page->SetParameter ('ITEM_AUTHORID', $item_author_id);
// $page->SetParameter ('ITEM_AUTHORLINK', $item_author_link);
// $page->SetParameter ('ITEM_AUTHORUEMAIL', $item_author_email);
// $page->SetParameter ('ITEM_AUTHORNAME', $item_author_name);
// $page->SetParameter ('ITEM_AUTHORUNAME', $item_author_username);
// $page->SetParameter ('ITEM_AUTHORIMG', $item_author_image);
// $page->SetParameter ('ITEM_AUTHORONLINE', $item_author_online);
// $page->SetParameter ('ITEM_AUTHORCOUNTRY', $item_author_country);
// $page->SetParameter ('ITEM_AUTHORJOINED', $item_author_joined);
// if(check_user_upgrades($item_author_id))
// {
//     $sub_info = get_user_membership_detail($item_author_id);
//     $page->SetParameter('SUB_TITLE', $sub_info['name']);
//     $page->SetParameter('SUB_IMAGE', $sub_info['badge']);
// }else{
//     $page->SetParameter('SUB_TITLE','');
//     $page->SetParameter('SUB_IMAGE', '');
// }
$page->SetParameter ('ITEM_CATEGORY', $item_category);
$page->SetParameter ('ITEM_SUB_CATEGORY', $item_sub_category);
$page->SetParameter ('ITEM_LINK', $item_link);
// $page->SetParameter ('QUOTE_LINK', $quote_link);
$page->SetParameter ('ITEM_CATLINK', $item_catlink);
$page->SetParameter ('ITEM_SUBCATLINK', $item_subcatlink);
// $page->SetParameter ('ITEM_LOCATION', $item_location);
// $page->SetParameter ('ITEM_CITY', $item_city);
// $page->SetParameter ('ITEM_STATE', $item_state);
// $page->SetParameter ('ITEM_COUNTRY', $item_country);
// $page->SetParameter ('ITEM_LAT', $lat);
// $page->SetParameter ('ITEM_LONG', $long);
// $page->SetParameter ('ITEM_CREATED', $item_created_at);
// $page->SetParameter ('ITEM_DESC', $item_description);
// $page->SetParameter ('ITEM_SHOWMORE', $showmore);
// $page->SetParameter ('ITEM_PRICE', $item_price);
// $page->SetParameter ('ITEM_NEGOTIATE', $item_negotiable);
// $page->SetParameter ('ITEM_PHONE', $item_phone);
// $page->SetParameter ('ITEM_HIDE_PHONE', $item_hide_phone);
// $page->SetParameter ('MAIN_SCREEN', $main_Screen);
// $page->SetParameter ('META_IMAGE', $meta_image);
// $page->SetParameter ('ITEM_SCREENS_SM', $screen_sm);
// $page->SetParameter ('ITEM_SCREENS_BIG', $screen_big);
// $page->SetParameter ('ITEM_SCREENS_CLASSB', $screen_classicb);
// $page->SetParameter ('ITEM_SCREENS_CLASSSM', $screen_classicsm);
// $page->SetParameter ('SHOW_IMAGE_SLIDER', $show_image_slider);
// $page->SetParameter ('ITEM_STATUS', $item_status);
// $page->SetParameter ('ITEM_TAG', $item_tag);
// $page->SetParameter ('SHOW_TAG', $show_tag);
// $page->SetParameter ('ITEM_VIEW', $item_view);
// $page->SetParameter ('MAILSENT', $mailsent);
$page->SetParameter('SUCCESS', $success);
$page->SetParameter('ERROR', $error);
$page->SetParameter('CUSTOMERROR', $customError);
$page->SetParameter ('RECAPTCH_ERROR', $recaptcha_error);
// $page->SetParameter ('ITEMREVIEW', count_product_review($item_id));
// $page->SetParameter('ZECHAT', get_option("zechat_on_off"));
// $page->SetParameter('QUICKCHAT', get_option("quickchat_ajax_on_off"));
// $page->SetParameter('MAP_COLOR', get_option("map_color"));
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
?>