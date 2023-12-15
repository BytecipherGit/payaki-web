<?php

if (!isset($_GET['page'])) {
    $_GET['page'] = 1;
}

$limit = 4;

if (checkloggedin()) {
    $ses_userdata = get_user_data($_SESSION['user']['username']);

    $author_image = $ses_userdata['image'];
    $transactions = array();
    $count = 0;

    $rows = ORM::for_table('ad_shop_payment')
        ->join('ad_user', ['ad_shop_payment.member_id', '=', 'ad_user.id'])
        ->join('ad_product', ['ad_shop_payment.product_id', '=', 'ad_product.id'])
        ->where('ad_shop_payment.member_id', $_SESSION['user']['id']) // Example WHERE condition
        ->order_by_desc('ad_shop_payment.id')
        ->find_many();

    /*$usersArray = [];
    foreach ($rows as $row) {
    $usersArray[] = $row->as_array();
    }
    var_dump($usersArray);
    die;*/

    $total_item = count($rows);
    foreach ($rows as $row) {
        // $currency_code = ($row['currency_code'] != null)? $row['currency_code']: $config['currency_code'];
        $currency_code = get_countryCurrecny_by_code('AO');
        $amount = price_format($row['total_amount'], $currency_code);
        $transactions[$count]['id'] = $row['id'];
        // $transactions[$count]['product_id'] = $row['product_id'];
        $transactions[$count]['product_name'] = $row['product_name'];
        $transactions[$count]['amount'] = $amount;
        $transactions[$count]['txn_id'] = $row['txn_id'];
        $transactions[$count]['time'] = $row['create_at'];

        $pro_url = create_slug($row['product_name']);

        $premium = '';
        if ($row['transaction_method'] == 'Subscription') {
            $premium = '<span class="label label-default">' . $lang['MEMBERSHIP'] . '</span>';
            $product_link = '#';
            $transactions[$count]['product_link'] = $product_link;
        } elseif ($row['transaction_method'] == 'banner-advertise') {
            $premium = '<span class="label label-default">' . $lang['BANNER_ADVERTISE'] . '</span>';
            $product_link = '#';
            $transactions[$count]['product_link'] = $product_link;
        } else {
            $featured = $row['featured'];
            $urgent = $row['urgent'];
            $highlight = $row['highlight'];

            if ($featured == "1") {
                $premium = $premium . '<span class="label label-warning">' . $lang['FEATURED'] . '</span>';
            }

            if ($urgent == "1") {
                $premium = $premium . '<span class="label label-success">' . $lang['URGENT'] . '</span>';
            }

            if ($highlight == "1") {
                $premium = $premium . '<span class="label label-info">' . $lang['HIGHLIGHT'] . '</span>';
            }

            $product_link = $link['POST-DETAIL'] . '/' . $row['product_id'] . '/' . $pro_url;
            $transactions[$count]['product_link'] = $product_link;
        }

        $t_status = $row['order_status'];
        // echo $t_status;
        // die;
        $status = '';
        if ($t_status == "panding") {
            $status = '<span class="label label-success">Pending</span>';
        } elseif ($t_status == "processing") {
            $status = '<span class="label label-warning">Processing</span>';
        } elseif ($t_status == "shipped") {
            $status = '<span class="label label-danger">Shipped</span>';
        } elseif ($t_status == "delivered") {
            $status = '<span class="label label-danger">Delivered</span>';
        } elseif ($t_status == "completed") {
            $status = '<span class="label label-danger">Completed</span>';
        } elseif ($t_status == "canceled") {
            $status = '<span class="label label-danger">Cancel</span>';
        } elseif ($t_status == "refunded") {
            $status = '<span class="label label-danger">Refunded</span>';
        } elseif ($t_status == "onhold") {
            $status = '<span class="label label-danger">On Hold</span>';
        } elseif ($t_status == "failed") {
            $status = '<span class="label label-danger">Failed</span>';
        } elseif ($t_status == "returned") {
            $status = '<span class="label label-danger">Returned</span>';
        } elseif ($t_status == "outfordelivery") {
            $status = '<span class="label label-danger">Out For Delivery</span>';
        }

        $transactions[$count]['premium'] = $premium;
        $transactions[$count]['status'] = $status;
        $transactions[$count]['invoice'] = $t_status == "success" ? $link['INVOICE'] . '/' . $row['id'] : '';

        $count++;
    }
    // Output to template
    $page = new HtmlTemplate('templates/' . $config['tpl_name'] . '/order.tpl');
    $page->SetParameter('OVERALL_HEADER', create_header('My Order Details'));
    $page->SetLoop('TRANSACTIONS', $transactions);
    $page->SetLoop('PAGES', pagenav($total_item, $_GET['page'], 20, $link['ORDER'], 0));
    $page->SetParameter('TOTALITEM', $total_item);
    $page->SetLoop('HTMLPAGE', get_html_pages());
    $page->SetParameter('COPYRIGHT_TEXT', get_option("copyright_text"));
    $page->SetParameter('AUTHORUNAME', ucfirst($ses_userdata['username']));
    $page->SetParameter('AUTHORNAME', ucfirst($ses_userdata['name']));
    $page->SetParameter('AUTHORIMG', $author_image);
    $page->SetParameter('OVERALL_FOOTER', create_footer());

    $page->CreatePageEcho();
} else {
    error($lang['PAGE_NOT_FOUND'], __LINE__, __FILE__, 1);
    exit();
}
