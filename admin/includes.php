<?php
// Path to root directory of app.
require_once('../includes/autoload.php');
require_once('../includes/lang/lang_'.$config['lang'].'.php');
$mysqli = db_connect();
admin_session_start();
if (!checkloggedadmin()) {
    headerRedirect('login.php');
}

define("ROOTPATH", dirname(__DIR__));
define("ADMINPATH", __DIR__);

// Check if SSL enabled
$ssl = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off"
    ? true
    : false;
define("SSL_ENABLED", $ssl);

// Define SITEURL
$admin_url = (SSL_ENABLED ? "https" : "http")
    . "://"
    . $_SERVER["SERVER_NAME"]
    . (dirname($_SERVER["SCRIPT_NAME"]) == DIRECTORY_SEPARATOR ? "" : "/")
    . trim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");

define("ADMINURL", $admin_url);
include('header.php');

