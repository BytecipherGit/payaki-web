<?php
global $config, $link;

if (!checkloggedin()) {
    headerRedirect($link['LOGIN'] . "?ref=post-ad");
    exit();
}

$errorMsg = '';
$eventTicketArr = array();
if (isset($_GET['id']) && isset($_GET['uId'])) {
    $user = ORM::for_table($config['db']['pre'] . 'user')->find_one($_GET['uId']);
    //Get Product Event Type Details
    $eventResults = ORM::for_table($config['db']['pre'] . 'product_event_types')
        ->where('product_id', $_GET['id'])
        ->find_many();
    if (count($eventResults) > 0) {
        foreach ($eventResults as $key => $event) {
            $eventTicketArr[$event['id']]['id'] = $event['id'];
            $eventTicketArr[$event['id']]['product_id'] = $event['product_id'];
            $eventTicketArr[$event['id']]['ticket_type'] = $event['ticket_type'];
            $eventTicketArr[$event['id']]['ticket_price'] = $event['ticket_price'];
            $eventTicketArr[$event['id']]['available_quantity'] = $event['available_quantity'];
            $eventTicketArr[$event['id']]['selling_mode'] = $event['selling_mode'];
        }
    } else {
        $eventTicketArr = [];
    }
    if (isset($_GET['message'])) {
        $errorMsg = 'exceed_limit';
    }

    $page = new HtmlTemplate('templates/' . $config['tpl_name'] . '/bookevent.tpl');
    $page->SetParameter('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetLoop('EVENT_TICKET', $eventTicketArr);
    $page->SetParameter('NAME', $user['name']);
    $page->SetParameter('EMAIL', $user['email']);
    $page->SetParameter('PHONE', $user['phone']);
    $page->SetParameter('ADDRESS', $user['address']);
    $page->SetParameter('PRODUCTID', $_GET['id']);
    $page->SetParameter('USERID', $_GET['uId']);
    $page->SetParameter('CUSTOMEVENTPAYMENT', $link['CUSTOMEVENTPAYMENT']);
    $page->SetParameter('LIMITEXCEEDMSG', $errorMsg);
    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit();

} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
