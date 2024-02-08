<?php
global $config, $link;

// Relative path to the file
$relativePath = 'storage/webhook/appy.txt';
// Get the absolute path
$fileName = realpath($relativePath);

// Get the raw POST data
$payload = file_get_contents("php://input");
// Decode JSON payload
$data = json_decode($payload, true);

$file = fopen($fileName, 'w');
if ($file) {
    fwrite($file, $data);
    fclose($file);
    echo "JSON data has been successfully written to the file: $fileName";
    // Set permissions for the file
    chmod($fileName, 0644); // Example permission (read/write for owner, read for group and others)
} else {
    echo "Error: Unable to write JSON data to the file.";
}

// Create a new record
$newRecord = ORM::for_table('ad_webhook_response')->create();
$newRecord->json_response = json_encode($data, true);
$newRecord->date = date('Y-m-d H:i:s');
$newRecord->save();

// Validate JSON data
// if (json_last_error() !== JSON_ERROR_NONE) {
//     http_response_code(400);
//     exit('Invalid JSON');
// }

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

$update_shop_payment = ORM::for_table($config['db']['pre'] . 'shop_payment')
    ->where('merchantTransactionId', escape_html($merchantTransactionId))
    ->find_one();
$update_shop_payment->set('payment_status', $successful);
$update_shop_payment->set('order_status', $successful);
$update_shop_payment->set('payment_response', json_encode($data));
$update_shop_payment->set('total_amount', $amount);
$update_shop_payment->set('create_at', date('Y-m-d H:i:s'));
$update_shop_payment->set('code', $code);
$update_shop_payment->set('message', $message);
$update_shop_payment->set('source', $source);
$update_shop_payment->save();
