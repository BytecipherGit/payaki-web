<?php
$page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/advertise-here.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header($lang['ADVERTISE_HERE']));
$page->SetParameter ('SITE_TITLE', $config['site_title']);
$page->SetParameter ('NAME', '');
$page->SetParameter ('TITLE', '');
$page->SetParameter ('HTML', '');
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
?>

