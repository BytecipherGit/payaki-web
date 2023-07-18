<?php
if (isset($_SESSION["products"])) {
    $total = 0;
    if(count($_SESSION["products"]) > 0){
        foreach($_SESSION["products"] as $product){
            $subtotal = ($product['product_price'] * $product['product_qty']);
			$total = ($total + $subtotal);
        }
    }
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/viewcart.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetLoop ('ITEM', $_SESSION["products"]);
    $page->SetParameter('TOTALITEM', count($_SESSION["products"]));
    $page->SetParameter('TOTALAMOUNT', $total);
    $page->SetParameter('CHECKOUT', $link['CHECKOUT']);
    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit();

} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
?>