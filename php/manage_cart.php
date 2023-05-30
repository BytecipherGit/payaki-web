<?php
require_once('DbConnect.php');
$db = new DbConnect;
$dbConn = $db->connect();
global $config;
// $_SESSION["products"] = array();
session_start();
// session_destroy();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productId = $_POST["product_id"];
	if(!empty($productId)){
		$stmt = $dbConn->prepare("SELECT ap.* FROM ad_product as ap WHERE ap.id =:id");
		$stmt->bindParam(":id", $productId);
		$stmt->execute();
		$productDetails = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!empty($productDetails)){
			$_SESSION["user"]["cart"]["id"]  = $productDetails['id'];
			// if(!isset($_SESSION["user"]["cart"])){
			// 	$_SESSION["user"]["cart"] = [];
			// }
			// $cart = $_SESSION["user"]["cart"];
			// if (isset($cart[$productId])) {
			// 	$cart[$productId]['quantity']++;
			// } else {
			// 	$cart[$productId] = [
			// 		"product_id" => $productDetails['id'],
			// 		"product_name" => $productDetails['product_name'],
			// 		"price" => $productDetails['price'],
			// 		"quantity" => 1,
			// 	];
			// }
			// $_SESSION["user"]["cart"] = $cart;
		}
		echo '<pre>';
		print_r($_SESSION["user"]["cart"]);
		die();
		die(json_encode(array('status'=>true,'message'=>'product successfully added into cart')));
	}
}