<?php
/**
 * Quickad Rating & Reviews - jQuery & Ajax php
 * @author Bylancer
 * @version 1.0
 */

include_once "setting.php";

// Converts linebreaks to <br>
function mynl2br($text)
{
    return strtr($text, array("\r\n" => '<br />', "\r" => '<br />', "\n" => '<br />'));
}

$login_error_msg = '<p><strong><font color="red">' . $lang['ERROR'] . ':</font></strong> ' . $lang['RATING_LOGIN_EROR'] . '</p>';
$error_msg = '<p><strong><font color="red">' . $lang['ERROR'] . ':</font></strong> ' . $lang['RATING_SAVE_ERROR'] . '</p>';
$success_msg = '<div style="background:#B6FABE; border:solid 1px #82D18B; padding-left:10px;"><p class="saved-success"><strong>' . $lang['THANKS_YOU'] . '!</strong> ' . $lang['RATING_SAVED'] . '<p></div>';

if (isset($_POST['rating'])) {

    if (isset($_SESSION['user']['id'])) {
        $timenow = date('Y-m-d H:i:s');
        $message = sanitize_string($_POST['message']);
        $save_reviews = ORM::for_table($config['db']['pre'] . 'reviews')->create();
        $save_reviews->productID = $productid;
        $save_reviews->user_id = $_SESSION['user']['id'];
        $save_reviews->comments = $message;
        $save_reviews->rating = $_POST['rating'];
        $save_reviews->date = $timenow;
        $save_reviews->publish = 0;
        $save_reviews->save();

        // save review to database
        if ($save_reviews->id()) {
            $productDetails = ORM::for_table($config['db']['pre'] . 'product')
                ->select(['id', 'user_id', 'product_name', 'slug'])
                ->where('id', $productid)
                ->find_one();
            if (!empty($productDetails['id'])) {
                $insert_notification = ORM::for_table($config['db']['pre'] . 'custom_notification')->create();
                $insert_notification->notification_id = $productDetails['id'];
                $insert_notification->type = 'review';
                $insert_notification->title = $productDetails['product_name'];
                $insert_notification->redirect_url = $config['site_url'] . 'ad/' . $productDetails['id'] . '/' . $productDetails['slug'];
                $insert_notification->user_id = $productDetails['user_id'];
                $insert_notification->status = 0;
                $insert_notification->created_at = date("Y-m-d H:i:s");
                $insert_notification->save();
            }
            echo $success_msg;
        } else {
            echo $error_msg;
        }
    } else {
        echo $login_error_msg;
    }

}
