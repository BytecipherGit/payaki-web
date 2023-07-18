<?php
global $config, $link;
// payment information from PayPal 
if(!empty($_GET['tx']) && !empty($_GET['amt']) && !empty($_GET['cc']) && !empty($_GET['st'])){
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/customsuccess.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetLoop ('ITEM', $_SESSION["products"]);
    $page->SetParameter('TXNID', $_GET['tx']);
    $page->SetParameter('AMOUNT', $_GET['amt']);
    $page->SetParameter('CURRENCYCODE', $_GET['cc']);
    $page->SetParameter('PAYMENTSTATUS', $_GET['st']);
    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit();

} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
?>