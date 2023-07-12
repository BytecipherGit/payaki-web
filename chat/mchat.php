<?php

namespace Main;

error_reporting(0);
use Classes\DB;
use Classes\Login;
use Classes\Image;

require_once('classes/DB.php');
require_once('classes/Login.php');
require_once('classes/Image.php');
require_once('templates/header.php');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$domain = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $domain . '/payaki-web';
$profile_image_url = $protocol . $domain . '/payaki-web/storage/profile/';

$key = "BYTECIPHERPAYAKI";

//Sender id jiska post hai
if (!empty($_GET['senderId'])) {
	$senderId = openssl_decrypt(base64_decode($_GET['senderId']), 'AES-256-CBC', $key, 0);
	// $loginTokenId = DB::_query('SELECT user_id FROM ad_login_tokens WHERE user_id=:user_id', ['user_id' => $senderId])[0]['user_id'];
	// if (empty($loginTokenId)) {
	// 	// Start :: Generate token & saved in to login_tokens table for chat
	// 	$cstrong = true;
	// 	$token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
	// 	DB::_query('INSERT INTO ad_login_tokens (user_id, token) VALUES (:user_id, :token)', ['user_id' => $senderId, 'token' => $token]);
	// 	setcookie('SNID', $token, time() + 60 * 60 * 24 * 7, '/', null, null, true);
	// 	// End :: Generate token & saved in to login_tokens table for chat
	// }
}

//Receiver id jo msg send kar raha hai post owner ko
if (!empty($_GET['receiverId'])) {
	$receiverId = openssl_decrypt(base64_decode($_GET['receiverId']), 'AES-256-CBC', $key, 0);
	// $loginTokenId = DB::_query('SELECT user_id FROM ad_login_tokens WHERE user_id=:user_id', ['user_id' => $receiverId])[0]['user_id'];
	// if (empty($loginTokenId)) {
	// 	// Start :: Generate token & saved in to login_tokens table for chat
	// 	$cstrong = true;
	// 	$token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
	// 	DB::_query('INSERT INTO ad_login_tokens (user_id, token) VALUES (:user_id, :token)', ['user_id' => $receiverId, 'token' => $token]);
	// 	setcookie('SNID', $token, time() + 60 * 60 * 24 * 7, '/', null, null, true);
	// 	// End :: Generate token & saved in to login_tokens table for chat
	// }
}

$user_id = $receiverId;
$username = DB::_query('SELECT username FROM ad_user WHERE id=:user_id', ['user_id' => $user_id])[0]['username'];
if ($user_id) {
	?>

	<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top header-top"
		style="background-color: #9C5FA3 !important;">
		<a class="navbar-brand" href="chat.php">Payaki Chat |
			<?php echo ucfirst($username) ?>
		</a>
		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav mr-auto"></ul>
			<div class="form-inline my-2 my-lg-0">
				<?php
				$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
				$domain = $_SERVER['HTTP_HOST'];
				$base_url = $protocol . $domain . '/payaki-web';
				?>
				<a href="<?php echo $base_url ?>" class="btn btn-phchat-logout btn-sm"><i
						class="icon-feather-log-out"></i>Dashboard</a>
			</div>
		</div>
	</nav>

	<div class="container conversations" style="padding-top: 80px;">
	<div class="chat-page">
		<div class="row">
			<?php
			if (!empty($senderId) && !empty($receiverId)) {
				$postOwnderUser = DB::_query('SELECT id,username,image FROM ad_user WHERE id=:user_id', ['user_id' => $senderId]);
				?>
				<section id="messages_container" class="col-md-4 conversations-section">
					<ul class="user-list">

						<li class="user-who-wrote-you">
							<a href="#" data-id="<?php echo $postOwnderUser[0]['id']; ?>"
								data-senderid="<?php echo $receiverId; ?>" class="user-list-item"></a>

							<span class="messager-name">
									<?php
									if(!empty($postOwnderUser[0]['image'])){
										$image = $profile_image_url.$postOwnderUser[0]['image'];
									} else {
										$image = 'assets/avatars/profile-default.png';
									}
									?>
									<img src="<?php echo $image;?>" alt="Avatars" />
								<p>
									<?php echo ucfirst($postOwnderUser[0]['username']); ?>
								</p>
							</span>
						</li>
					</ul>
				</section>
				<?php
			} else {
				
				?>
				<!-- List of users who wrote you or you wrote them. -->
				<section id="messages_container" class="col-md-4 conversations-section chat-box-left">
					<h2 class="chatheadingleft">Chat</h2>
					<!-- Search users -->
					<div class="search-user">

						<input class="form-control mr-sm-2 ph-searchbar" id="js-searchUser" type="search" placeholder="Search"
							aria-label="Search">
						<div class="list-group list-results"></div>
					</div>
					<ul class="user-list">
						<?php
						$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
						$host_url = $_SERVER['HTTP_HOST'];
						$current_url = $protocol . "://" . $host_url . $_SERVER['REQUEST_URI'];
						$display_chat_image_url = str_replace("chat.php", "", $current_url);

						// List of users who wrote you or you wrote them.
						if (DB::_query('SELECT ad_user.username FROM ad_user, ad_custom_messages WHERE ad_custom_messages.receiver = ad_user.id OR ad_custom_messages.sender = ad_user.id AND ad_user.id = :user_id', ['user_id' => $senderId])) {
							// $usernames = DB::_query('SELECT * FROM t1, ad_user WHERE (ad_custom_messages.sender = :user_id OR ad_custom_messages.receiver = :user_id) AND (ad_custom_messages.receiver = ad_user.id OR ad_custom_messages.sender = ad_user.id) GROUP BY users.id', ['user_id' => Login::isLogged()]);
							$usernames = DB::_query('SELECT DISTINCT ad_user.id,ad_user.username,ad_user.image FROM ad_custom_messages, ad_user WHERE (ad_custom_messages.receiver = :userid OR ad_custom_messages.sender = :userid) AND (ad_custom_messages.receiver = ad_user.id OR ad_custom_messages.sender = ad_user.id)', [ 'userid' => $receiverId ]);
							foreach ($usernames as $single_username) {
								if ($single_username['id'] != $receiverId) {
									if(!empty($single_username['image'])){
										$image = $profile_image_url.$single_username['image'];
									} else {
										$image = 'assets/avatars/profile-default.png';
									}
									echo '<li class="user-who-wrote-you" >
									<a href="#" data-id="' . $single_username['id'] . '" data-senderid="'.$receiverId.'" class="user-list-item"></a>';
									echo '<span class="messager-name"> <div class="uers-icon">
									<img src="'.$image.'" alt="Avatars" />
                      				</div> <p>' . $single_username['username'] . '</p>
                      				</span></li>';
								}
							}
						}
						?>
					</ul>
				</section>
				<?php
			}

			?>


			<!-- Actual messages. -->
			<section id="messages_container_1" class="col-sm-12 col-md-8 clearfix messages">
					<div class="msg-headar" id="msg-headar">
					
					</div>
				<!--<div class="msg-headar">
				<i class="fa fa-arrow-left" id="back_arrow"></i>
				<div class="uers-icon">
					<img src="assets/avatars/profile-default.png" alt="Patient" />
				</div>
				<div class="uers-details">
					<h2>Dr. Jessica Jane</h2>
				</div>
				</div> -->
				<div class="messages-show" id="js-messagesContainer"></div>

				<div class="write-your-message">
					<form action="<?php htmlentities($_SERVER['PHP_SELF']) ?>" method="POST" id="js-sendMessage">
						<input type="text" class="input-phchat" id="js-messageBody" name="message"
							placeholder="Write your message" style="display:none" />
						<button type="submit" id="js-messageSubmitButton" name="submit" style="display:none;"><img
								src="assets/avatars/send.png" alt="send" /></button>
					</form>

				</div>
			</section>
		</div>
	
	</div>
	</div>

	<script>
		document.getElementById("js-profilePicUpload").onchange = function () {
			document.getElementById("js-profilePicUploadForm").submit();
		};
	</script>

<?php }  ?>

<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="assets/js/mscripts.js"></script>
	
	</body>
</html>