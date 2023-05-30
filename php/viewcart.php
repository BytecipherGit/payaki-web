<?php
if (isset($_SESSION["cart"])) {
    // echo '<pre>';
    // print_r($_SESSION["cart"]);
    // // echo $lang['PROFILE'];
    // die();
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/viewcart.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetLoop ('CART', $_SESSION["cart"]);
    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit();

} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
?>