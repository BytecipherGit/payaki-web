<?php
global $config, $lang;
// Start :: Destroy token from login_tokens table for chat
if(isset($_SESSION['user']['id'])){
    ORM::for_table($config['db']['pre'] . 'login_tokens')->where(array('user_id' => $_SESSION['user']['id']))->delete_many();
}
// End :: Destroy token from login_tokens table for chat

// Unset all session values
$_SESSION = array();

// get session parameters
$params = session_get_cookie_params();

// Delete the actual cookie.
setcookie(session_name(),
    '', time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]);
    

// Remove access token from session
unset($_SESSION['facebook_access_token']);
//Unset token and user data from session
unset($_SESSION['token']);

unset($_SESSION['user']);
unset($_SESSION['chatHistory']);
unset($_SESSION['openChatBoxes']);
// will delete just the name data
session_destroy();

// Delete the login cookies.
if (isset($_COOKIE['SNID'])) {
    unset($_COOKIE['SNID']);
    setcookie('SNID', null, -1, '/');
  }
  
// Destroy session will delete ALL data associated with that user.

if (isset($_COOKIE['qurm'])) {
    unset($_COOKIE['qurm']);
    setcookie('qurm', null, -1, '/');
}
echo "<script>window.location='login'</script>";
?>