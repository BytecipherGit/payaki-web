<?php
if(isset($_SESSION["products"])){
    unset($_SESSION["products"]);
}
global $config, $link;
$successMsg = "Your payment successfully done";
$page = new HtmlTemplate('templates/' .$config['tpl_name'].'/customsuccess.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
$page->SetParameter('SUCCESSMESSAGE', $successMsg);
$page->SetParameter('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
exit();
?>