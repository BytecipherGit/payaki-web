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
            $totalAmount = $totalAmount + $_POST['price'][$i];
        }
    }
    $order_id = 0;
    if(isset($_POST["proceedPayment"])) {
        $insert_so = ORM::for_table($config['db']['pre'].'event_order')->create();
        $insert_so->member_id = $member_id;
        $insert_so->name = $_POST['name'];
        $insert_so->address = $_POST['address'];
        $insert_so->mobile = $_POST['contactNumber'];
        $insert_so->email = $_POST['emailAddress'];
        $insert_so->order_status = "PENDING";
        $insert_so->order_at = date("Y-m-d H:i:s");
        $insert_so->payment_type = "PAYPAL";
        $insert_so->save();
        $orderId = $insert_so->id();
        // insert order item details
        if(!empty($orderId)) {
            $ticketTypeIds = implode(",", $_POST['ticketId']);
            $ticketAmounts = implode(",", $_POST['price']);
            $ticketQuantities = implode(",", $_POST['quantity']);
            $insert_soi = ORM::for_table($config['db']['pre'].'event_order_item')->create();
            $insert_soi->order_id = $orderId;
            $insert_soi->product_id = $_POST['pId'];
            $insert_soi->event_type_id = $ticketTypeIds;
            $insert_soi->item_price = $ticketAmounts;
            $insert_soi->quantity = $ticketQuantities;
            $insert_soi->save();
        }
    }
   
    $payableAmount = price_format($totalAmount,'AOA');
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/customeventpayment.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetLoop ('ITEM', $title);
    $page->SetParameter('TOTALAMOUNTPAYBLE', $totalAmount);
    $page->SetParameter('DISPLAYTOTALAMOUNTPAYBLE', $payableAmount);
    $page->SetParameter('NAME', $_POST["name"]);
    $page->SetParameter('ADDRESS', $_POST["address"]);
    $page->SetParameter('PHONE', $_POST["contactNumber"]);
    $page->SetParameter('EMAIL', $_POST["emailAddress"]);
    $page->SetParameter('CARTITEM', $title);
    $page->SetParameter('ORDERID', $orderId);
    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit();

} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
?>