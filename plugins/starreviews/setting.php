<?php
/**
 * Quickad Rating & Reviews - jQuery & Ajax php
 * @author Bylancer
 * @version 1.0
 */

global $db;
global $productid;

require_once('../../includes/autoload.php');
require_once('../../includes/lang/lang_'.$config['lang'].'.php');
sec_session_start();


if (isset($_GET['productid'])) {
    if (!empty($_GET['productid'])) {
         $productid = $_GET['productid'];  
    } else {
       $productid = '';  
    }
} else {
    $productid = '';
}
?>