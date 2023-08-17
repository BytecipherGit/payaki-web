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



if (isset($_GET['action']) && $_GET['action'] == "post_event") {
    global $config, $lang, $link;
    $customError = '';
    $success = '';
    // Loop through submitted data and insert into the database
    if (isset($_POST['ticket_type']) && isset($_POST['ticket_price']) && isset($_POST['available_quantity']) && isset($_POST['selling_mode'])) {
        $ids = $_POST['id'];
        $productIds = $_POST['product_id'];
        $ticketTypes = $_POST['ticket_type'];
        $ticketPrices = $_POST['ticket_price'];
        $availableQuantities = $_POST['available_quantity'];
        $sellingModes = $_POST['selling_mode'];

        foreach ($ticketTypes as $key => $ticketType) {
            $id = $ids[$key];
            $productId = $productIds[$key];
            $ticketPrice = $ticketPrices[$key];
            $availableQuantity = $availableQuantities[$key];
            $sellingMode = $sellingModes[$key];

             // Check if ID exists
             if (!empty($id)) {
                // Update the existing record
                ORM::for_table($config['db']['pre'] . 'product_event_types')->find_one($id)->set(
                    [
                        'product_id' => $productId, 
                        'ticket_type' => $ticketType,
                        'ticket_price' => $ticketPrice,
                        'available_quantity' => $availableQuantity,
                        'selling_mode' => $sellingMode
                    ])->save();
                $success = "Event successfully updated";
            } else {
                // Insert a new record
                //Insert record in Training Gallery
                $tGInsert = ORM::for_table($config['db']['pre'] . 'product_event_types')->create();
                $tGInsert->product_id = $item_id;
                $tGInsert->ticket_type = $ticketType;
                $tGInsert->ticket_price = $ticketPrice;
                $tGInsert->available_quantity = $availableQuantity;
                $tGInsert->remaining_quantity = $availableQuantity;
                $tGInsert->selling_mode = $sellingMode;
                $tGInsert->created_at = date("Y-m-d H:i:s");
                if($tGInsert->save()){
                    $success = "Event successfully updated";
                } else {
                    $customError = "Error while creating event records.";
                }
            }
        }
    }
}

$eventArr = array();
$eventResult = ORM::for_table($config['db']['pre'].'product_event_types')
        ->where('product_id', $item_id)
        ->find_many();
if(count($eventResult) > 0){
    foreach($eventResult as $key => $event){
        $eventArr[$event['id']]['id'] = $event['id'];
        $eventArr[$event['id']]['product_id'] = $event['product_id'];
        $eventArr[$event['id']]['ticket_type'] = $event['ticket_type'];
        $eventArr[$event['id']]['ticket_price'] = $event['ticket_price'];
        $eventArr[$event['id']]['available_quantity'] = $event['available_quantity'];
        $eventArr[$event['id']]['selling_mode'] = $event['selling_mode'];
    }
} else {
    $eventArr = [];
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
$page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/ad-event.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header($item_title,$meta_desc,true));
$page->SetParameter ('ITEM_ID', $item_id);
$page->SetParameter ('ITEM_TITLE', $item_title);
$page->SetLoop ('EVENTS', $eventArr);
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