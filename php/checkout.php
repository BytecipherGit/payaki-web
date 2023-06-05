<?php
if (isset($_SESSION["cart"])) {
    
    if (isset($_SESSION["cart"]) && isset($_POST["submit"])) {
        echo '<pre>';
        print_r($_POST);
        die();    
    }
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/checkout.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetLoop ('CART', $_SESSION["cart"]);
    $page->SetParameter('TOTAL', $_SESSION["total"]);
    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit();

} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
?>