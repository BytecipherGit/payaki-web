<?php
    global $config, $link;
    // echo 'webhook_appy.php';
    // die('webhook_appy.php');

    // Get the raw POST data
    $payload = file_get_contents("php://input");

    // Decode JSON payload
    $data = json_decode($payload, true);

    // Validate JSON data
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        exit('Invalid JSON');
    }

    // Extract data
    $id = $data['id'];
    $merchantTransactionId = $data['merchantTransactionId'];
    $amount = $data['amount'];
    // $smartcardNumber = $data['options']['SmartcardNumber'];
    // $merchantOrigin = $data['options']['MerchantOrigin'];
    $successful = $data['responseStatus']['successful'];
    $code = $data['responseStatus']['code'];
    $message = $data['responseStatus']['message'];
    $source = $data['responseStatus']['source'];

    $update_shop_payment = ORM::for_table($config['db']['pre'].'shop_payment')
            ->where('merchantTransactionId', escape_html($merchantTransactionId))
            ->find_one();
    $update_shop_payment->set('payment_status',  $successful);
    $update_shop_payment->set('order_status',  $successful);
    $update_shop_payment->set('payment_response', json_encode($data));
    $update_shop_payment->set('total_amount', $amount);
    $update_shop_payment->set('create_at', date('Y-m-d H:i:s'));
    $update_shop_payment->set('code', $code);
    $update_shop_payment->set('message', $message);
    $update_shop_payment->set('source', $source);
    $update_shop_payment->save();

?>