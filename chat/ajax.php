<?php

use Classes\DB;
use Classes\Login;

require_once 'classes/DB.php';
require_once 'classes/Login.php';
require_once 'classes/Image.php';

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$domain = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $domain . '/payaki-web';
$profile_image_url = $protocol . $domain . '/payaki-web/storage/profile/';

if (isset($_POST['searchVal'])) {
    $value = htmlspecialchars($_POST['searchVal']);

    if ($value !== "") {
        if (DB::_query("SELECT ad_user.username FROM ad_user WHERE ad_user.username LIKE '%" . $value . "%'")) {
            $usernames = DB::_query("SELECT ad_user.username FROM ad_user WHERE ad_user.username LIKE '%" . $value . "%' LIMIT 4");
            $address = htmlentities($_SERVER['PHP_SELF']);

            foreach ($usernames as $username) {
                
                $receiver = DB::_query('SELECT ad_user.id,ad_user.image FROM ad_user WHERE ad_user.username = :username', ['username' => $username['username']]);
                if (Login::isLogged() != $receiver[0]['id']) {
                    if(!empty($receiver[0]['image'])){
                        $image = $profile_image_url.$receiver[0]['image'];
                    } else {
                        $image = 'assets/avatars/profile-default.png';
                    }
                    echo '<a href="#" data-id=' . $receiver[0]['id'] . ' data-image="'.$image.'" class="list-group-item list-group-item-action searched-user">' . $username['username'] . '</a>';
                }
            }
        }
    } else {
        echo "";
    }
}

if (isset($_POST['receiver']) && !isset($_POST['messageBody'])) {
    $receiver = htmlspecialchars($_POST['receiver']);
	

    if ($receiver != Login::isLogged()) {
		$username = DB::_query('SELECT username,image FROM ad_user WHERE id=:user_id', ['user_id' => $receiver]);
        if(!empty($username[0]['image'])){
            $image = $profile_image_url.$username[0]['image'];
        } else {
            $image = 'assets/avatars/profile-default.png';
        }
		$header = '<i class="fa fa-arrow-left" id="back_arrow"></i>
			<div class="uers-icon">
				<img src="'.$image.'" alt="Patient" />
			</div>
			<div class="uers-details">
				<h2>'.ucfirst($username[0]['username']).'</h2>
			</div>';
        if (DB::_query('SELECT ad_custom_messages.body, ad_custom_messages.receiver, ad_custom_messages.sender FROM ad_custom_messages WHERE (ad_custom_messages.receiver = :user_id OR ad_custom_messages.sender = :user_id) AND (ad_custom_messages.receiver = :receiver OR ad_custom_messages.sender = :receiver)', ['user_id' => Login::isLogged(), 'receiver' => $receiver])) {
            $messages = DB::_query('SELECT ad_custom_messages.body, ad_custom_messages.receiver, ad_custom_messages.sender FROM ad_custom_messages WHERE (ad_custom_messages.receiver = :user_id OR ad_custom_messages.sender = :user_id) AND (ad_custom_messages.receiver = :receiver OR ad_custom_messages.sender = :receiver)', ['user_id' => Login::isLogged(), 'receiver' => $receiver]);
			$msgResponse = '';
            foreach ($messages as $message) {
                $image = 'assets/avatars/profile-default.png';
                $dt = !empty($message['date_time']) ? $message['date_time'] : date('Y-m-d H:i:s');
                if ($message['sender'] === Login::isLogged()) {
                    $userImage = DB::_query('SELECT image FROM ad_user WHERE id=:user_id', ['user_id' => $message['sender']])[0]['image'];
                    if(!empty($userImage)){
                        $image = $profile_image_url.$userImage;
                    } else {
                        $image = 'assets/avatars/profile-default.png';
                    } 
                    $msgResponse .= '
						<div class="box-main-top">
						<div class="msg-box-bg right-msg ml-auto">
						<h2 class="right-msg bubble-message-me">' . $message['body'] . '</h2>
						<!-- <p>' . date("h:i A", strtotime($dt)) . '</p>-->
						</div>
						<div class="uers-bg-icon">
						<img src="'.$image.'" alt="Profile" />
						</div>
					</div>';

                } else {

                    $userImage = DB::_query('SELECT image FROM ad_user WHERE id=:user_id', ['user_id' => $message['receiver']])[0]['image'];
                    if(!empty($userImage)){
                        $image = $profile_image_url.$userImage;
                    } else {
                        $image = 'assets/avatars/profile-default.png';
                    } 
                    $msgResponse .= '
						<div class="box-main-top">
						<div class="uers-bg-icon">
						<img src="'.$image.'" alt="Patient" />
						</div>
						<div class="msg-box-bg">
						<h2 class="bubble-message-you">' . $message['body'] . '</h2>
						<!-- <p>' . date("h:i A", strtotime($dt)) . '</p> -->
						</div>
					</div>';
                    
                }
            }
			$response = ['rsp_header' => $header, 'rsp_message' => $msgResponse];
            echo json_encode($response);
        }
    }
}

if (isset($_POST['messageBody']) && isset($_POST['user_id'])) {
    $body = htmlspecialchars($_POST['messageBody']);
    $date_time = date('Y-m-d H:i:s');
    // The receiver passed from the AJAX request, it is related to the clicked user from the listed ones.
    $receiver = htmlspecialchars($_POST['user_id']);

    // The sender is you.
    $sender = Login::isLogged();

    // Is the receiver an existing user...?
    if (DB::_query('SELECT id from ad_user WHERE id = :receiver', ['receiver' => $receiver])) {
        if ($body != "") {

            $userImage = DB::_query('SELECT image FROM ad_user WHERE id=:user_id', ['user_id' => $sender])[0]['image'];
            if(!empty($userImage)){
                $image = $profile_image_url.$userImage;
            } else {
                $image = 'assets/avatars/profile-default.png';
            }
            // If yes, Update DB.
            DB::_query('INSERT INTO ad_custom_messages (receiver, sender, body, date_time) VALUES (:r, :s, :body, :date_time)', [
                'r' => $receiver,
                's' => $sender,
                'body' => $body,
                'date_time' => $date_time,
            ]);

            // Returns to the client-side the body of your message.
            // echo '<div class="bubble-message bubble-message-me"><p>'.$body.'</p></div>';
            echo '<div class="box-main-top">
			<div class="msg-box-bg right-msg ml-auto">
			<h2 class="right-msg bubble-message-me">' . $body . '</h2>
			<!-- <p>' . date("h:i A", strtotime($date_time)) . '</p> -->
			</div>
			<div class="uers-bg-icon">
			  <img src="'.$image.'" alt="Profile" />
			</div>
		  </div>';
        }
    }
}
