<?php
global $config, $link;

// echo '<pre>';
// print_r($_POST);
// die;
$errors = array();
if(isset($_SESSION['user']['id']) && !empty($_POST['uId']) && !empty($_POST['pId']) && !empty($_POST['pId'])){
    $member_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : $_POST['uId'];
    if(!empty($_POST['quantity']) && !empty($_POST['available_quantity'])){
        for ($i=0; $i < count($_POST['quantity']); $i++) { 
            // echo $_POST['quantity'][$i].'=='.$_POST['available_quantity'][$i];
            if($_POST['available_quantity'][$i] < $_POST['quantity'][$i]){
                headerRedirect($link['BOOKEVENT'] . "/" . $_POST['pId']. "/" . $_POST['uId']."?message=seat_booking_exceed");
            }
        }
    }
    
    $order_id = 0;
    if(isset($_POST["proceedPayment"])) {
        $insert_so = ORM::for_table($config['db']['pre'].'shop_order')->create();
        $insert_so->member_id = $member_id;
        $insert_so->name = $_POST['name'];
        $insert_so->address = $_POST['address'];
        $insert_so->mobile = $_POST['contactNumber'];
        $insert_so->email = $_POST['emailAddress'];
        $insert_so->order_status = "PENDING";
        $insert_so->order_at = date("Y-m-d H:i:s");
        $insert_so->payment_type = "PAYPAL";
        $insert_so->save();
        // Print the last executed query
        // echo ORM::get_last_query();
        // die;
        $orderId = $insert_so->id();
        // insert order item details
        if(!empty($orderId)) {	
            if(isset($_SESSION["products"]) && count($_SESSION["products"])>0) { 
                foreach($_SESSION["products"] as $product){	
                    $insert_soi = ORM::for_table($config['db']['pre'].'shop_order_item')->create();
                    $insert_soi->order_id = $orderId;
                    $insert_soi->product_id = $product['id'];
                    $insert_soi->item_price = $product['product_price'];
                    $insert_soi->quantity = $product['product_qty'];
                    $insert_soi->save();
                }
            }
        }
    }
   
    $payableAmount = price_format($_SESSION["payableAmount"],'AOA');
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/customeventpayment.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetLoop ('ITEM', $_SESSION["products"]);
    $page->SetParameter('TOTALAMOUNTPAYBLE', $_SESSION["payableAmount"]);
    $page->SetParameter('DISPLAYTOTALAMOUNTPAYBLE', $payableAmount);
    $page->SetParameter('NAME', $_POST["name"]);
    $page->SetParameter('ADDRESS', $_POST["address"]);
    $page->SetParameter('PHONE', $_POST["contactNumber"]);
    $page->SetParameter('EMAIL', $_POST["emailAddress"]);
    $page->SetParameter('CARTITEM', $_SESSION["cartItems"]);
    $page->SetParameter('ORDERID', $orderId);
    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit();

} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
?>