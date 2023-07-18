<?php

$postData = file_get_contents('php://input');
$filename = 'response_data.txt';
$filePath = __DIR__ . '/' . $filename;

// Write the response data to the file
if (file_put_contents($filePath, $postData) === false) {
    die('Failed to create/write to the file.');
}

echo 'Response data has been written to the file: ' . $filename;
$postArray = explode('&', $postData);
$postValue = array();
foreach ($postArray as $value) {
	$value = explode ('=', $value);
	if (count($value) == 2)
		$postValue[$value[0]] = urldecode($value[1]);
}
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
	$get_magic_quotes_exists = true;
}
foreach ($postValue as $key => $value) {
    if($get_magic_quotes_exists == true) {
	// if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
		$value = urlencode(stripslashes($value));
	} else {
		$value = urlencode($value);
	}
	$req .= "&$key=$value";
}
// The post IPN data back to PayPal to validate the IPN data  
$ch = curl_init("https://www.sandbox.paypal.com/cgi-bin/webscr");
if ($ch == FALSE) {
	return FALSE;
}
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
$res = curl_exec($ch);
if (curl_errno($ch) != 0) {
	error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, 'app.log');
	curl_close($ch);
	exit;
} else {
	error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, 'app.log');
	error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, 'app.log');
	curl_close($ch);
}
// Inspect IPN validation result and act accordingly
$payment_response = $res;
$tokens = explode("\r\n\r\n", trim($res));
$res = trim(end($tokens));
if (strcmp ($res, "VERIFIED") == 0) {
	$item_name = $_POST['item_name'];
	$item_number = $_POST['item_number'];
	$payment_status = $_POST['payment_status'];
	$payment_amount = $_POST['mc_gross'];
	$payment_currency = $_POST['mc_currency'];
	$txn_id = $_POST['txn_id'];
	$receiver_email = $_POST['receiver_email'];
	$payer_email = $_POST['payer_email'];	
	$isPaymentCompleted = false;
	if($payment_status == "Completed") {
		$isPaymentCompleted = true;
	}
	// insert payment details
	// $insertPayment = "INSERT INTO shop_payment(order_id, payment_status, payment_response)VALUES('".$order_id."', '".$payment_status."', '".$payment_response."')";
	// mysqli_query($conn, $insertPayment) or die("database error:". mysqli_error($conn));	
    $insert_sp = ORM::for_table($config['db']['pre'].'shop_payment')->create();
    $insert_sp->order_id = $order_id;
    $insert_sp->payment_status = $payment_status;
    $insert_sp->payment_response = $payment_response;
    $insert_sp->save();

	// update order status after payment	
	// $updateOrder = "UPDATE shop_order set order_status = 'PAID' WHERE id = '".$item_number."'";
	// mysqli_query($conn, $updateOrder) or die("database error:". mysqli_error($conn));	
    // Update the user with ID 1 and change the email
    $shopOrder = ORM::for_table($config['db']['pre'].'shop_order')
    ->where_equal('id', $item_number)
    ->find_one();

    if ($shopOrder) {
        $shopOrder->order_status = 'PAID';
        $shopOrder->save();
    } else {
        echo "Order not found for order id ".$item_number;
    }
	error_log(date('[Y-m-d H:i e] '). "Verified IPN: $req ". PHP_EOL, 3, 'app.log');	
} else if (strcmp ($res, "INVALID") == 0) {
	error_log(date('[Y-m-d H:i e] '). "Invalid IPN: $req" . PHP_EOL, 3, 'app.log');
}
?>