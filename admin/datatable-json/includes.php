<?php
require_once('../../includes/autoload.php');
require_once('../../includes/lang/lang_'.$config['lang'].'.php');

admin_session_start();
$pdo = ORM::get_db();