<?php

use Classes\DB;
use Classes\Login;

require_once 'classes/DB.php';
require_once 'classes/Login.php';
require_once 'classes/Image.php';

if (isset($_POST['searchVal'])) {
    $value = htmlspecialchars($_POST['searchVal']);

    if ($value !== "") {
        if (DB::_query("SELECT ad_user.username FROM ad_user WHERE ad_user.username LIKE '%" . $value . "%'")) {
            $usernames = DB::_query("SELECT ad_user.username FROM ad_user WHERE ad_user.username LIKE '%" . $value . "%' LIMIT 4");
            $address = htmlentities($_SERVER['PHP_SELF']);

            foreach ($usernames as $username) {
                $receiver = DB::_query('SELECT ad_user.id FROM ad_user WHERE ad_user.username = :username', ['username' => $username['username']])[0]['id'];
                if (Login::isLogged() != $receiver) {
                    echo '<a href="#" data-id=' . $receiver . ' data-image="yep" class="list-group-item list-group-item-action searched-user">' . $username['username'] . '</a>';
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
		$username = DB::_query('SELECT username FROM ad_user WHERE id=:user_id', ['user_id' => $receiver])[0]['username'];
		$header = '<i class="fa fa-arrow-left" id="back_arrow"></i>
			<div class="uers-icon">
				<img src="assets/avatars/profile-default.png" alt="Patient" />
			</div>
			<div class="uers-details">
				<h2>'.ucfirst($username).'</h2>
			</div>';
        if (DB::_query('SELECT ad_custom_messages.body, ad_custom_messages.receiver, ad_custom_messages.sender FROM ad_custom_messages WHERE (ad_custom_messages.receiver = :user_id OR ad_custom_messages.sender = :user_id) AND (ad_custom_messages.receiver = :receiver OR ad_custom_messages.sender = :receiver)', ['user_id' => Login::isLogged(), 'receiver' => $receiver])) {
            $messages = DB::_query('SELECT ad_custom_messages.body, ad_custom_messages.receiver, ad_custom_messages.sender FROM ad_custom_messages WHERE (ad_custom_messages.receiver = :user_id OR ad_custom_messages.sender = :user_id) AND (ad_custom_messages.receiver = :receiver OR ad_custom_messages.sender = :receiver)', ['user_id' => Login::isLogged(), 'receiver' => $receiver]);
			$msgResponse = '';
            foreach ($messages as $message) {
                $dt = !empty($message['date_time']) ? $message['date_time'] : date('Y-m-d H:i:s');
                if ($message['sender'] === Login::isLogged()) {
                   
                    $msgResponse .= '
						<div class="box-main-top">
						<div class="msg-box-bg right-msg ml-auto">
						<h2 class="right-msg bubble-message-me">' . $message['body'] . '</h2>
						<!-- <p>' . date("h:i A", strtotime($dt)) . '</p>-->
						</div>
						<div class="uers-bg-icon">
						<img src="assets/avatars/profile-default.png" alt="Profile" />
						</div>
					</div>';

                } else {
                    
                    $msgResponse .= '
						<div class="box-main-top">
						<div class="uers-bg-icon">
						<img src="assets/avatars/profile-default.png" alt="Patient" />
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
			  <img src="assets/avatars/profile-default.png" alt="Profile" />
			</div>
		  </div>';
        }
    }
}
