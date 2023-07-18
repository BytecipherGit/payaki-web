<?php
global $config, $link;

if(isset($_SESSION['user']['id']) && isset($_SESSION["products"])){
    $member_id = $_SESSION['user']['id']; 
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
    
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/custompayment.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetLoop ('ITEM', $_SESSION["products"]);
    $page->SetParameter('TOTALAMOUNTPAYBLE', $_SESSION["payableAmount"]);
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