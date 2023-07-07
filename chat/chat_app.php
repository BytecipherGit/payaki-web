<?php
namespace Main;

error_reporting(0);

use Classes\DB;
use Classes\Login;

require_once 'classes/DB.php';
require_once 'classes/Login.php';
require_once 'classes/Image.php';
require_once 'templates/header.php';

if (!empty($_GET['senderId'])) {
    $senderId = base64_decode($_GET['senderId']);
}

if (!empty($_GET['receiverId'])) {
    $receiverId = base64_decode($_GET['receiverId']);
}

if (!empty($receiverId)) {
    $loginTokenId = DB::_query('SELECT user_id FROM ad_login_tokens WHERE user_id=:user_id', ['user_id' => $receiverId])[0]['user_id'];
    if (!empty($loginTokenId)) {
        // Delete the login cookies.
        if (isset($_COOKIE['SNID'])) {
            unset($_COOKIE['SNID']);
            setcookie('SNID', null, -1, '/');
        }
        // Start :: Generate token & saved in to login_tokens table for chat
        $cstrong = true;
        $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
        DB::_query('UPDATE ad_login_tokens SET token=:token WHERE user_id=:user_id', ['token' => $token, 'user_id' => $receiverId]);
        setcookie('SNID', $token, time() + 60 * 60 * 24 * 7, '/', null, null, true);
        // End :: Generate token & saved in to login_tokens table for chat
    } else {
        // Start :: Generate token & saved in to login_tokens table for chat
        $cstrong = true;
        $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
        DB::_query('INSERT INTO ad_login_tokens (user_id, token) VALUES (:user_id, :token)', ['user_id' => $receiverId, 'token' => $token]);
        setcookie('SNID', $token, time() + 60 * 60 * 24 * 7, '/', null, null, true);
        // End :: Generate token & saved in to login_tokens table for chat
    }

    // $receiverId = Login::isLogged();
    if (!empty($receiverId)) {
        $username = DB::_query('SELECT username FROM ad_user WHERE id=:user_id', ['user_id' => $receiverId])[0]['username'];

        ?>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top header-top" style="background-color: #9C5FA3 !important;">
  <a class="navbar-brand" href="conversations.php">Payaki Chat | <?php echo ucfirst($username) ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto"></ul>
    <div class="form-inline my-2 my-lg-0">
	<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $domain = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . $domain . '/payaki-web/logout';
        ?>
		<a href="<?php echo $base_url ?>" class="btn btn-phchat-logout btn-sm"><i class="icon-feather-log-out"></i>Logout</a>
    	<div class="account-box">

    	</div>
    </div>
  </div>
</nav>

<div class="container conversations" style="padding-top: 80px;">
	<div class="row">
	<?php
if (!empty($senderId) && !empty($receiverId)) {
            $postOwnderUser = DB::_query('SELECT id,username FROM ad_user WHERE id=:user_id', ['user_id' => $senderId]);
            ?>
					<section class="col-md-4 conversations-section">
						<ul class="user-list">
							<li class="user-who-wrote-you" style="background-color: #9C5FA3; color: #FFFFFF;">
								<a href="#" data-id="<?php echo $postOwnderUser[0]['id']; ?>" class="user-list-item"></a>
								<span class="messager-name"><?php echo ucfirst($postOwnderUser[0]['username']); ?> (Click to start chat)</span>
							</li>
						</ul>
					</section>
				<?php
} else {
            ?>
				<!-- List of users who wrote you or you wrote them. -->
		<section class="col-md-4 conversations-section">
			<ul class="user-list">
				<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host_url = $_SERVER['HTTP_HOST'];
            $current_url = $protocol . "://" . $host_url . $_SERVER['REQUEST_URI'];
            $display_chat_image_url = str_replace("chat.php", "", $current_url);

            // List of users who wrote you or you wrote them.
            if (DB::_query('SELECT ad_user.username FROM ad_user, ad_custom_messages WHERE ad_custom_messages.receiver = ad_user.id OR ad_custom_messages.sender = ad_user.id AND ad_user.id = :user_id', ['user_id' => $receiverId])) {
                $usernames = DB::_query('SELECT * FROM ad_custom_messages, ad_user WHERE (ad_custom_messages.receiver = :user_id OR ad_custom_messages.sender = :user_id) AND (ad_custom_messages.receiver = ad_user.id OR ad_custom_messages.sender = ad_user.id) GROUP BY ad_user.id', ['user_id' => Login::isLogged()]);

                foreach ($usernames as $single_username) {
                    if ($single_username['id'] != Login::isLogged()) {
                        echo '<li class="user-who-wrote-you" style="background-color: #9C5FA3; color: #FFFFFF;">
								<a href="#" data-id="' . $single_username['id'] . '" class="user-list-item"></a>';
                        echo '<span class="messager-name">' . $single_username['username'] . ' (Click to start chat)</span></li>';
                    }
                }
            }
            ?>
			</ul>

			<!-- Search users -->
			<div class="search-user" style="margin-top: 50px;">
				<input class="form-control mr-sm-2 ph-searchbar" id="js-searchUser" type="search" placeholder="Search" aria-label="Search">
				<div class="list-group list-results"></div>
		    </div>
		</section>
				<?php
}

        ?>


		<!-- Actual messages. -->
		<section class="col-sm-12 col-md-8 clearfix messages">
			<div class="messages-show" id="js-messagesContainer"></div>

			<div class="write-your-message">
				<form action="<?php htmlentities($_SERVER['PHP_SELF'])?>" method="POST" id="js-sendMessage">
					<input type="text" class="input-phchat" id="js-messageBody" name="message" placeholder="Write your message" style="display:none"/>
					<input type="submit" id="js-messageSubmitButton" name="submit" style="display:none; background-color: #9C5FA3; color: #FFFFFF;" />
				</form>

			</div>
		</section>
	</div>
</div>
<?php } else {
        header('Location: index.php');
    }
}

?>

<?php require 'templates/footer.php';?>