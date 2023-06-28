<?php

$postID = $sellerId = $senderId = $postUserEmail = '';
if (!isset($_GET['post_id']) || !isset($_GET['post_user_id'])) {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit;
} else {
    $postID = $_GET['post_id'];
    $sellerId = $_GET['post_user_id'];
    $getPostUserData = get_user_data('', $_GET['post_user_id']);
    if (!empty($getPostUserData['email'])) {
        $postUserEmail = $getPostUserData['email'];
    } else {
        $postUserEmail = '';
    }
}

if (isset($_POST['amount'])) {
    $_POST['amount'] = htmlentities($_POST['amount']);
}

if (isset($_POST['message'])) {
    $_POST['message'] = htmlentities($_POST['message']);
}

$errors = 0;
$amount_error = '';
$message_error = '';

if (!isset($_POST['Submit'])) {
    $page = new HtmlTemplate("templates/" . $config['tpl_name'] . "/quote.tpl");
    $page->SetParameter('OVERALL_HEADER', create_header($lang['QUOTEVIO']));
    $page->SetParameter('AMOUNT', '');
    $page->SetParameter('MESSAGE', '');
    $page->SetParameter('AMOUNT_ERROR', '');
    $page->SetParameter('MESSAGE_ERROR', '');

    if (isset($_SESSION['user']['username'])) {
        $ses_userdata = get_user_data($_SESSION['user']['username']);
        $page->SetParameter('USERNAME', $_SESSION['user']['username']);
        $page->SetParameter('NAME', $ses_userdata['name']);
        $page->SetParameter('EMAIL', $ses_userdata['email']);
        $page->SetParameter('SENDER_ID', $ses_userdata['id']);

    } else {
        $page->SetParameter('USERNAME', '');
        $page->SetParameter('NAME', '');
        $page->SetParameter('EMAIL', '');
        $page->SetParameter('SENDER_ID', '');

    }
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer = $_SERVER['HTTP_REFERER'];
        if ((strpos($referer, $link['POST-DETAIL']) !== false)) {
            $page->SetParameter('REDIRECT_URL', $_SERVER['HTTP_REFERER']);
        } else {
            $page->SetParameter('REDIRECT_URL', '');
        }
    } else {
        $page->SetParameter('REDIRECT_URL', '');
    }

    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
} else {
    if (trim($_POST['amount']) == '') {
        $errors++;
        $amount_error = 'Enter the amount';
    }

    if (trim($_POST['message']) == '') {
        $errors++;
        $message_error = 'Enter the message';
    }

    if ($errors) {
        $page = new HtmlTemplate("templates/" . $config['tpl_name'] . "/quote.tpl");
        $page->SetParameter('OVERALL_HEADER', create_header($lang['QUOTEVIO']));
        $page->SetParameter('AMOUNT', $_POST['amount']);
        $page->SetParameter('MESSAGE', $_POST['message']);
        $page->SetParameter('AMOUNT_ERROR', $amount_error);
        $page->SetParameter('MESSAGE_ERROR', $message_error);

        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
            if ((strpos($referer, $link['POST-DETAIL']) !== false)) {
                $page->SetParameter('REDIRECT_URL', $_SERVER['HTTP_REFERER']);
            } else {
                $page->SetParameter('REDIRECT_URL', '');
            }
        } else {
            $page->SetParameter('REDIRECT_URL', '');
        }

        $page->SetParameter('OVERALL_FOOTER', create_footer());
        $page->CreatePageEcho();
    } else {
        $insert_quotes = ORM::for_table($config['db']['pre'] . 'quotes')->create();
        $insert_quotes->post_id = $postID;
        $insert_quotes->seller_id = $sellerId;
        $insert_quotes->sender_id = $_POST['sender_id'];
        $insert_quotes->amount = $_POST['amount'];
        $insert_quotes->message = $_POST['message'];
        $insert_quotes->created_at = date('Y-m-d H:i:s');
        $insert_quotes->save();
        $quote_id = $insert_quotes->id();
        if (!empty($quote_id)) {
            $postInfo = ORM::for_table($config['db']['pre'] . 'product')->select(['id', 'product_name', 'slug'])->where('id', $postID)->find_one();
            if (!empty($postInfo['id'])) {
                $sellerInfo = ORM::for_table($config['db']['pre'] . 'user')->select(['username', 'email'])->where('id', $sellerId)->find_one();
                $email_to = $sellerInfo['email'];
                $email_to_name = $sellerInfo['username'];
                $email_subject = 'Payaki - Quote received for the product ' . $postInfo['product_name'];
                $postUrl = $link['POST-DETAIL'] . '/' . $postInfo['id'] . '/' . $postInfo['slug'];
                $email_body = '<p>Click below link to see post :-</p><br /><a href="' . $postUrl . '" target="_other" rel="nofollow">' . $postInfo['product_name'] . '</a>';
                $headers = 'From: payaki@example.com' . "\r\n" . 'Reply-To: payaki@example.com' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                mail($email_to, $email_subject, $email_body, $headers);

                //Insert custom notification
                $productDetails = ORM::for_table($config['db']['pre'] . 'product')
                    ->select(['id', 'user_id', 'product_name', 'slug'])
                    ->where('id', $postID)
                    ->find_one();
                if (!empty($productDetails['id'])) {
                    $insert_notification = ORM::for_table($config['db']['pre'] . 'custom_notification')->create();
                    $insert_notification->notification_id = $productDetails['id'];
                    $insert_notification->type = 'quote';
                    $insert_notification->title = $productDetails['product_name'];
                    $insert_notification->redirect_url = $config['site_url'] . 'ad/' . $productDetails['id'] . '/' . $productDetails['slug'];
                    $insert_notification->user_id = $productDetails['user_id'];
                    $insert_notification->status = 0;
                    $insert_notification->created_at = date("Y-m-d H:i:s");
                    $insert_notification->save();
                }
            }

        }
        message($lang['THANKS'], $lang['QUOTE_THANKS']);
    }
}
