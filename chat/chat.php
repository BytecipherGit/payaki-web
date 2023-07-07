<?php

namespace Main;
error_reporting(0);
use Classes\DB;
use Classes\Login;

require_once 'classes/DB.php';
require_once 'classes/Login.php';
require_once 'classes/Image.php';
require_once 'templates/header.php';
$key="BYTECIPHERPAYAKI";

if (!empty($_GET['senderId'])) {
  $senderId = openssl_decrypt(base64_decode($_GET['senderId']), 'AES-256-CBC', $key, 0);
  // $senderId = urldecode($_GET['senderId']);
}

if (!empty($_GET['receiverId'])) {
  $receiverId = openssl_decrypt(base64_decode($_GET['receiverId']), 'AES-256-CBC', $key, 0);
  // $receiverId = urldecode($_GET['receiverId']);
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

    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top header-top"
      style="background-color: #9C5FA3 !important;">
      <a class="navbar-brand" href="chat.php">Payaki Chat |
        <?php echo ucfirst($username) ?>
      </a>
      <!-- <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button> -->

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
           <!--  <a href="<?php // echo $base_url . '/logout' ?>" class="btn btn-phchat-logout btn-sm"><i
              class="icon-feather-log-out"></i>Logout</a>
          <div class="account-box">

          </div> -->
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

              <li class="user-who-wrote-you">
                <a href="#" data-id="<?php echo $postOwnderUser[0]['id']; ?>" class="user-list-item"></a>

                <span class="messager-name">
                  <div class="uers-icon">
                    <img src="assets/avatars/profile-default.png" alt="Avatars" />
                  </div>
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
              if (DB::_query('SELECT ad_user.username FROM ad_user, ad_custom_messages WHERE ad_custom_messages.receiver = ad_user.id OR ad_custom_messages.sender = ad_user.id AND ad_user.id = :user_id', ['user_id' => $receiverId])) {
                $usernames = DB::_query('SELECT * FROM ad_custom_messages, ad_user WHERE (ad_custom_messages.receiver = :user_id OR ad_custom_messages.sender = :user_id) AND (ad_custom_messages.receiver = ad_user.id OR ad_custom_messages.sender = ad_user.id) GROUP BY ad_user.id', ['user_id' => Login::isLogged()]);

                foreach ($usernames as $single_username) {
                  if ($single_username['id'] != Login::isLogged()) {
                    echo '<li class="user-who-wrote-you" >
									<a href="#" data-id="' . $single_username['id'] . '" class="user-list-item">

                  </a>';
                    echo '<span class="messager-name"> <div class="uers-icon">
                        <img src="assets/avatars/profile-default.png" alt="Avatars" />

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
        <section class="col-sm-12 col-md-8 clearfix messages">
          <div class="messages-show" id="js-messagesContainer"></div>

          <div class="write-your-message">
            <form action="<?php htmlentities($_SERVER['PHP_SELF']) ?>" method="POST" id="js-sendMessage">
              <input type="text" class="input-phchat" id="js-messageBody" name="message" placeholder="Write your message"
                style="display:none" />
              <input type="submit" id="js-messageSubmitButton" name="submit"
                style="display:none; background-color: #9C5FA3; color: #FFFFFF;" />
            </form>

          </div>
        </section>
      </div>
    </div>

  <?php } else {
    header('Location: index.php');
  }
} else {
  $exists = DB::recordExists("ad_login_tokens", "user_id = '.$senderId.'");
  if ($exists) {
    // Delete the login cookies.
    if (isset($_COOKIE['SNID'])) {
      unset($_COOKIE['SNID']);
      setcookie('SNID', null, -1, '/');
    }
    // Start :: Generate token & saved in to login_tokens table for chat
    $cstrong = true;
    $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
    DB::_query('UPDATE ad_login_tokens SET token=:token WHERE user_id=:user_id', ['token' => $token, 'user_id' => $senderId]);
    setcookie('SNID', $token, time() + 60 * 60 * 24 * 7, '/', null, null, true);
    // End :: Generate token & saved in to login_tokens table for chat
  } else {
    // Start :: Generate token & saved in to login_tokens table for chat
    $cstrong = true;
    $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
    DB::_query('INSERT INTO ad_login_tokens (user_id, token) VALUES (:user_id, :token)', ['user_id' => $senderId, 'token' => $token]);
    setcookie('SNID', $token, time() + 60 * 60 * 24 * 7, '/', null, null, true);
    // End :: Generate token & saved in to login_tokens table for chat
  }
  $user_id = Login::isLogged();
  if (!empty($user_id)) {
    $username = DB::_query('SELECT username FROM ad_user WHERE id=:user_id', ['user_id' => $user_id])[0]['username'];

    ?>

    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top header-top"
      style="background-color: #9C5FA3 !important;">
      <a class="navbar-brand" href="chat.php">Payaki Chat |
        <?php echo ucfirst($username) ?>
      </a>
      <!-- <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span> -->
      </button>

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
          if (!empty($_GET['postid']) && !empty($_GET['userid']) && !empty($_GET['loggedinuserid'])) {
            $postId = base64_decode($_GET['postid']);
            $userId = base64_decode($_GET['userid']);
            $LoggedInUserId = base64_decode($_GET['loggedinuserid']);
            $postOwnderUser = DB::_query('SELECT id,username FROM ad_user WHERE id=:user_id', ['user_id' => $userId]);
            ?>
            <section class="col-md-4 conversations-section">
              <ul class="user-list">
                <li class="user-who-wrote-you" style="background-color: #9C5FA3; color: #FFFFFF;">
                  <a href="#" data-id="<?php echo $postOwnderUser[0]['id']; ?>" class="user-list-item"></a>
                  <span class="messager-name">
                    <?php echo ucfirst($postOwnderUser[0]['username']); ?> (Click to start chat)
                  </span>
                </li>
              </ul>
              <ul class="online-viewBox" id="style-5">
                <li class="">
                  <button>
                    <div class="uers-icon">
                      <img src="assets/avatars/profile-default.png" alt="Patient" />
                      <span class="online-bg"></span>
                    </div>
                    <div class="uers-details">
                      <h2><span>Jessica Jane 1</span></h2>
                    </div>
                  </button>
                </li>
                <li>
                  <button>
                    <div class="uers-icon">
                      <img src="assets/avatars/profile-default.png" alt="Patient" />
                      <span></span>
                    </div>
                    <div class="uers-details">
                      <h2>Ahmad Culhane 1</h2>
                    </div>
                  </button>
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
                if (DB::_query('SELECT ad_user.username FROM ad_user, ad_custom_messages WHERE ad_custom_messages.receiver = ad_user.id OR ad_custom_messages.sender = ad_user.id AND ad_user.id = :user_id', ['user_id' => $user_id])) {
                  $usernames = DB::_query('SELECT * FROM ad_custom_messages, ad_user WHERE (ad_custom_messages.receiver = :user_id OR ad_custom_messages.sender = :user_id) AND (ad_custom_messages.receiver = ad_user.id OR ad_custom_messages.sender = ad_user.id) GROUP BY ad_user.id', ['user_id' => Login::isLogged()]);

                  foreach ($usernames as $single_username) {
                    if ($single_username['id'] != Login::isLogged()) {
                      echo '<li class="user-who-wrote-you" >
									<a href="#" data-id="' . $single_username['id'] . '" class="user-list-item">

                  </a>';
                      echo '<span class="messager-name"> <div class="uers-icon">
                        <img src="assets/avatars/profile-default.png" alt="Avatars" />

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
            <!-- <div class="msg-headar">
              <i class="fa fa-arrow-left" id="back_arrow"></i>
              <div class="uers-icon">
                <img src="assets/avatars/profile-default.png" alt="Patient" />
              </div>
              <div class="uers-details">
                <h2>Dr. Jessica Jane</h2>
              </div>
            </div> -->
            <div class="messages-show" id="js-messagesContainer">

            </div>
            <div class="write-your-message">
              <form action="<?php htmlentities($_SERVER['PHP_SELF']) ?>" method="POST" id="js-sendMessage">
                <input type="text" class="input-phchat" id="js-messageBody" name="message" placeholder="Write your message"
                  style="display:none" />
                <!-- <input type="submit" id="js-messageSubmitButton" name="submit" style="display:none; background-color: #9C5FA3; color: #FFFFFF;" /> -->
                <button type="submit" id="js-messageSubmitButton" name="submit" style="display:none;"><img
                    src="assets/avatars/send.png" alt="send" /></button>
              </form>

            </div>
          </section>
        </div>
      </div>
    </div>
  <?php } else {
    header('Location: index.php');
  }

}
?>

<?php require 'templates/footer.php'; ?>