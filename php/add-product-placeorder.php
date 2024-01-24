<?php
global $config, $link;
if(isset($_GET['totalAmount']) && isset($_GET['userId']) && isset($_GET['productId'])) {
    $total = $_GET['totalAmount'];
    $productId = $_GET['productId'];
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/addproductplaceorder.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetParameter ('ITEM', $productId);
    $page->SetParameter ('TYPE', "post_product");
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