<?php
global $config, $link;

$totalAmount = 0.00;
$title = '';
$errors = array();
if(isset($_SESSION['user']['id']) && !empty($_POST['uId']) && !empty($_POST['pId'])){
    //Get Product Details
    $productInfo = ORM::for_table($config['db']['pre'].'product')->where('id', $_POST['pId'])->find_one();
    if(!empty($productInfo['product_name'])){
        $title = $productInfo['product_name'];
    }
    
    $member_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : $_POST['uId'];
    if(!empty($_POST['quantity']) && !empty($_POST['available_quantity'])){
        for ($i=0; $i < count($_POST['quantity']); $i++) { 
            // echo $_POST['quantity'][$i].'=='.$_POST['available_quantity'][$i];
            if($_POST['available_quantity'][$i] < $_POST['quantity'][$i]){
                headerRedirect($link['BOOKEVENT'] . "/" . $_POST['pId']. "/" . $_POST['uId']."?message=seat_booking_exceed");
            }
        }
    }
    
    if(!empty($_POST['price'])){
        for ($i=0; $i < count($_POST['price']); $i++) { 
            $totalAmount = $totalAmount + ($_POST['quantity'][$i] * $_POST['price'][$i]);
        }
    }
    // $order_id = 0;
    if(isset($_POST["proceedPayment"])) {
        // $insert_so = ORM::for_table($config['db']['pre'].'shop_order')->create();
        // $insert_so->member_id = $member_id;
        // $insert_so->name = $_POST['name'];
        // $insert_so->address = $_POST['address'];
        // $insert_so->mobile = $_POST['contactNumber'];
        // $insert_so->email = $_POST['emailAddress'];
        // $insert_so->order_status = "PENDING";
        // $insert_so->order_at = date("Y-m-d H:i:s");
        // $insert_so->save();
        // $orderId = $insert_so->id();
        // // insert order item details
        // if(!empty($orderId)) {
        $prefix = 'TR'; // You can customize the prefix
        $numericId = rand(0, 999999999999); // Generate a random numeric ID
        $numericId = str_pad($numericId, 12, '0', STR_PAD_LEFT);
        $merchantTransactionId = $prefix . $numericId;
            if(!empty($_POST['ticketId']) && !empty($_POST['price']) && !empty($_POST['quantity'])){
                for ($i=0; $i < count($_POST['ticketId']) ; $i++) {
                    $eventName = '';
                    $getEventDetails = ORM::for_table($config['db']['pre'] . 'product_event_types')->select('ticket_type')->where('id', $_POST['ticketId'][$i])->find_one();
                    $eventName = $getEventDetails->ticket_type;
                    $insert_soi = ORM::for_table($config['db']['pre'].'shop_order_item')->create();
                    $insert_soi->merchantTransactionId = $merchantTransactionId;
                    $insert_soi->user_id = $member_id;
                    $insert_soi->product_id = $_POST['pId'];
                    $insert_soi->type = 'event';
                    $insert_soi->event_type_id = $_POST['ticketId'][$i];
                    $insert_soi->product_name = $title;
                    $insert_soi->event_name = $eventName;
                    $insert_soi->item_price = $_POST['price'][$i];
                    $insert_soi->currency_code = 'AOA';
                    $insert_soi->currency = 'Kz';
                    $insert_soi->quantity = $_POST['quantity'][$i];
                    $insert_soi->save();
                }
            }
        // }
    }
   
    $payableAmount = price_format($totalAmount,'AOA');
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/customeventpayment.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetParameter ('ITEM', $merchantTransactionId);
    $page->SetParameter ('TYPE', "event");
    $page->SetParameter('TOTALAMOUNTPAYBLE', $totalAmount);
    $page->SetParameter('DISPLAYTOTALAMOUNTPAYBLE', $payableAmount);
    $page->SetParameter('NAME', $_POST["name"]);
    $page->SetParameter('ADDRESS', $_POST["address"]);
    $page->SetParameter('PHONE', $_POST["contactNumber"]);
    $page->SetParameter('EMAIL', $_POST["emailAddress"]);
    $page->SetParameter('CARTITEM', $title);
    $page->SetParameter('ORDERID', $merchantTransactionId);
    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit();

} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
?>