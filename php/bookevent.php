<?php
global $config, $link;

if(isset($_GET['id']) && isset($_GET['uId'])){
    $user = ORM::for_table($config['db']['pre'] . 'user')->find_one($_GET['uId']);
    // echo '<pre>';
    // print_r($user);
    // die;
    $total = 0;
    if(count($_SESSION["products"]) > 0){
        foreach($_SESSION["products"] as $product){
            $subtotal = ($product['product_price'] * $product['product_qty']);
			$total = ($total + $subtotal);
        }
    }
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/bookevent.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetLoop ('ITEM', $_SESSION["products"]);
    // $page->SetParameter('TOTALITEM', count($_SESSION["products"]));
    $page->SetParameter('NAME', $user['name']);
    $page->SetParameter('EMAIL', $user['email']);
    $page->SetParameter('PHONE', $user['phone']);
    $page->SetParameter('ADDRESS', $user['address']);
    // $page->SetParameter('TOTALAMOUNT', $total);
    $page->SetParameter('CUSTOMPAYMENT', $link['CUSTOMPAYMENT']);
    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit();

} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
?>