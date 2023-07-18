<?php
if(isset($_SESSION["products"]) && count($_SESSION["products"])>0){
    $total = 0;
	$list_tax = '';
    $cart_box = '';
	$items = '';
    if(count($_SESSION["products"]) > 0){
        foreach($_SESSION["products"] as $product){
            $item_price = sprintf("%01.2f",($product['product_price'] * $product['product_qty'])); 	
		    $items = $items." ".$product['product_name'];
            $subtotal = ($product['product_price'] * $product['product_qty']);
			$total = ($total + $subtotal);
        }
    }
    $grand_total = $total + $shipping_cost;
	foreach($taxes as $key => $value){
			$tax_amount = round($total * ($value / 100));
			$tax_item[$key] = $tax_amount;
			$grand_total = $grand_total + $tax_amount; 
	}	
	foreach($tax_item as $key => $value){
		$list_tax .= $key. ' : '. $currency. sprintf("%01.2f", $value).'<br />';
	}	
	$shipping_cost = ($shipping_cost)?'Shipping Cost : '.$currency. sprintf("%01.2f", $shipping_cost).'<br />':'';	
	$cart_box .= "<span>$shipping_cost  $list_tax <hr>Payable Amount : $currency ".sprintf("%01.2f", $grand_total)."</span>";	
	$_SESSION["payableAmount"] = round($grand_total);
	$_SESSION["cartItems"] = $items;

    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/checkout.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetLoop ('ITEM', $_SESSION["products"]);
    $page->SetParameter('TOTALITEM', count($_SESSION["products"]));
    $page->SetParameter('TOTALAMOUNT', $total);
    $page->SetParameter('PLACEORDER', $link['PLACEORDER']);
    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit();

} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
?>