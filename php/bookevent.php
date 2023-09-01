<?php
global $config, $link;

$eventTicketArr = array();
if(isset($_GET['id']) && isset($_GET['uId'])){
    $user = ORM::for_table($config['db']['pre'] . 'user')->find_one($_GET['uId']);
    $total = 0;
    if(count($_SESSION["products"]) > 0){
        foreach($_SESSION["products"] as $product){
            $subtotal = ($product['product_price'] * $product['product_qty']);
			$total = ($total + $subtotal);
        }
    }
    //Get Product Event Type Details
    $eventResults = ORM::for_table($config['db']['pre'].'product_event_types')
        ->where('product_id', $_GET['id'])
        ->find_many();
    if(count($eventResults) > 0){
        foreach($eventResults as $key => $event){
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
    $page = new HtmlTemplate('templates/' .$config['tpl_name'].'/bookevent.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
    $page->SetLoop ('ITEM', $_SESSION["products"]);
    $page->SetLoop ('EVENT_TICKET', $eventTicketArr);
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