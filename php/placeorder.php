<?php
global $config, $link;
if(isset($_SESSION['user']['id']) && isset($_SESSION["products"])){
    $user = ORM::for_table($config['db']['pre'] . 'user')->find_one($_SESSION['user']['id']);
    $total = 0;
    $productId = "";
    if(count($_SESSION["products"]) > 0){
        foreach($_SESSION["products"] as $product){
            $productId .= $product['id'].',';
            $subtotal = ($product['product_price'] * $product['product_qty']);
			$total = ($total + $subtotal);
        }
    }

    $productId = rtrim($productId, ', ');
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/placeorder.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetParameter ('ITEM', $productId);
    // $page->SetParameter('TOTALITEM', count($_SESSION["products"]));
    // $page->SetParameter('NAME', $user['name']);
    // $page->SetParameter('EMAIL', $user['email']);
    // $page->SetParameter('PHONE', $user['phone']);
    // $page->SetParameter('ADDRESS', $user['address']);
    $page->SetParameter('TOTALAMOUNT', $total);
    $page->SetParameter('CUSTOMPAYMENT', $link['CUSTOMPAYMENT']);
    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit();

} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
?>