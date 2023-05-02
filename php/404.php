<?php
require_once('includes/autoload.php');
require_once('includes/lang/lang_'.$config['lang'].'.php');

sec_session_start();

error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);


?>