<?php
require_once 'plugins/watermark/watermark.php';

if (isset($match['params']['country'])) {
    if ($match['params']['country'] != "") {
        change_user_country($match['params']['country']);
    }
}

if (get_option("post_without_login") == '0') {
    if (!checkloggedin()) {
        headerRedirect($link['LOGIN'] . "?ref=post-ad");
        exit();
    }
}

if (isset($_GET['action'])) {
    if ($_GET['action'] == "post_ad") {
        ajax_post_advertise();
    }
}

if (checkloggedin()) {
    if (!$config['non_active_allow']) {
        $user_data = get_user_data(null, $_SESSION['user']['id']);
        if ($user_data['status'] == 0) {
            message($lang['NOTIFY'], $lang['EMAIL_VERIFY_MSG']);
            exit();
        }
    }
    check_user_post_limit();
}

function check_user_post_limit()
{
    global $config, $lang;

    // Get usergroup details
    $group_id = get_user_group();
    $sub_info = get_usergroup_settings($group_id);
    $ad_limit = $sub_info['ad_limit'];

    if ($ad_limit != "999") {
        $total_user_post = ORM::for_table($config['db']['pre'] . 'product')
            ->where('user_id', $_SESSION['user']['id'])
            ->count();

        if ($total_user_post >= $ad_limit) {
            message($lang['NOTIFY'], $lang['POST_LIMIT_EXCEED']);
            exit();
        }
    }
}

function ajax_post_advertise()
{

    global $config, $lang, $link;
    if (isset($_POST['submit'])) {
        $errors = array();
        $item_screen = "";

        if (empty($_POST['subcatid']) or empty($_POST['catid'])) {
            $errors[]['message'] = $lang['CAT_REQ'];
        }
        if (empty($_POST['seller_name'])) {
            $errors[]['message'] = "Seller name is required";
        }
        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['ADTITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['DESC_REQ'];
        }
        if (empty($_POST['city'])) {
            $errors[]['message'] = $lang['CITY_REQ'];
        }
        if (!empty($_POST['price'])) {
            if (!is_numeric($_POST['price'])) {
                $errors[]['message'] = $lang['PRICE_MUST_NO'];
            }
        }
        /*IF : USER NOT LOGIN THEN CHECK SELLER INFORMATION*/
        if (!checkloggedin()) {
            if (isset($_POST['seller_name'])) {
                $seller_name = $_POST['seller_name'];
                if (empty($seller_name)) {
                    $errors[]['message'] = $lang['SELLER_NAME_REQ'];
                } /*else {
            if (preg_match('/^\p{L}[\p{L} _.-]+$/u', $seller_name)) {
            $errors[]['message'] = $lang['SELLER_NAME'] . " : " . $lang['ONLY_LETTER_SPACE'];
            } elseif ((strlen($seller_name) < 4) OR (strlen($seller_name) > 21)) {
            $errors[]['message'] = $lang['SELLER_NAME'] . " : " . $lang['NAMELEN'];
            }
            }*/
            } else {
                $errors[]['message'] = $lang['SELLER_NAME_REQ'];
            }

            if (isset($_POST['seller_email'])) {
                $seller_email = $_POST['seller_email'];

                if (empty($seller_email)) {
                    $errors[]['message'] = $lang['SELLER_EMAIL_REQ'];
                } else {
                    $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
                    if (!preg_match($regex, $seller_email)) {
                        $errors[]['message'] = $lang['SELLER_EMAIL'] . " : " . $lang['EMAILINV'];
                    }
                }
            } else {
                $errors[]['message'] = $lang['SELLER_EMAIL_REQ'];
            }
        }
        /*IF : USER NOT LOGIN THEN CHECK SELLER INFORMATION*/

        /*IF : USER GO TO PEMIUM POST*/
        if ($_POST['make_premium']) {
            $urgent = isset($_POST['urgent']) ? 1 : 0;
            $featured = isset($_POST['featured']) ? 1 : 0;
            $highlight = isset($_POST['highlight']) ? 1 : 0;
        } else {
            $urgent = 0;
            $featured = 0;
            $highlight = 0;
        }

        /*$payment_req = "";
        if (isset($_POST['urgent'])) {
        if (!isset($_POST['payment_id'])) {
        $payment_req = $lang['PAYMENT_METHOD_REQ'];
        }
        }
        if (isset($_POST['featured'])) {
        if (!isset($_POST['payment_id'])) {
        $payment_req = $lang['PAYMENT_METHOD_REQ'];
        }
        }
        if (isset($_POST['highlight'])) {
        if (!isset($_POST['payment_id'])) {
        $payment_req = $lang['PAYMENT_METHOD_REQ'];
        }
        }
        if (!empty($payment_req))
        $errors[]['message'] = $payment_req;*/

        /*IF : USER GO TO PEMIUM POST*/

        if (!count($errors) > 0) {
            if (isset($_POST['item_screen']) && count($_POST['item_screen']) > 0) {
                $valid_formats = array("jpg", "jpeg", "png"); // Valid image formats
                $countScreen = 0;
                foreach ($_POST['item_screen'] as $name) {
                    $filename = stripslashes($name);
                    $ext = getExtension($filename);
                    $ext = strtolower($ext);
                    if (!empty($filename)) {
                        //File extension check
                        if (in_array($ext, $valid_formats)) {
                            //Valid File extension check

                        } else {
                            $errors[]['message'] = $lang['ONLY_JPG_ALLOW'];
                        }
                        if ($countScreen == 0) {
                            $item_screen = $filename;
                        } elseif ($countScreen >= 1) {
                            $item_screen = $item_screen . "," . $filename;
                        }

                        $countScreen++;
                    }
                }
            }
        }

        if (!count($errors) > 0) {

            if (!checkloggedin()) {
                $seller_name = $_POST['seller_name'];
                $seller_email = $_POST['seller_email'];

                $user_count = check_account_exists($seller_email);
                if ($user_count > 0) {
                    $seller_username = get_username_by_email($seller_email);

                    $json = '{"status" : "email-exist","errors" : "' . $lang['ACCAEXIST'] . '","email" : "' . $seller_email . '","username" : "' . $seller_username . '"}';
                    echo $json;
                    die();
                } else {
                    /*Create user account with givern email id*/
                    $created_username = parse_name_from_email($seller_email);
                    //mysql query to select field username if it's equal to the username that we check '
                    $check_username = ORM::for_table($config['db']['pre'] . 'user')
                        ->select('username')
                        ->where('username', $created_username)
                        ->count();

                    //if number of rows fields is bigger them 0 that means it's NOT available '
                    if ($check_username > 0) {
                        $username = createusernameslug($created_username);
                    } else {
                        $username = $created_username;
                    }
                    $location = getLocationInfoByIp();
                    $confirm_id = get_random_id();
                    $password = get_random_id();
                    $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);
                    $now = date("Y-m-d H:i:s");

                    $insert_user = ORM::for_table($config['db']['pre'] . 'user')->create();
                    $insert_user->status = '0';
                    $insert_user->name = $seller_name;
                    $insert_user->username = $username;
                    $insert_user->password_hash = $pass_hash;
                    $insert_user->email = $seller_email;
                    $insert_user->confirm = $confirm_id;
                    $insert_user->created_at = $now;
                    $insert_user->updated_at = $now;
                    $insert_user->country = $location['country'];
                    $insert_user->city = $location['city'];
                    $insert_user->save();

                    $user_id = $insert_user->id();

                    /*CREATE ACCOUNT CONFIRMATION EMAIL*/
                    email_template("signup_confirm", $user_id);

                    /*SEND ACCOUNT DETAILS EMAIL*/
                    email_template("signup_details", $user_id, $password);

                    $loggedin = userlogin($username, $password);
                    create_user_session($loggedin['id'], $loggedin['username'], $loggedin['password']);

                }
            }

            if (checkloggedin()) {

                $price = $_POST['price'];
                $phone = $_POST['phone'];
                $price = isset($_POST['price']) ? $_POST['price'] : '0';
                $phone = isset($_POST['phone']) ? $_POST['phone'] : '0';

                if (empty($_POST['price'])) {
                    $price = 0;
                }

                $negotiable = isset($_POST['negotiable']) ? '1' : '0';
                $hide_phone = isset($_POST['hide_phone']) ? '1' : '0';

                if ($config['post_desc_editor'] == 1) {
                    $description = validate_input($_POST['content'], true);
                } else {
                    $description = validate_input($_POST['content']);
                }

                $cityid = $_POST['city'];
                $citydata = get_cityDetail_by_id($cityid);
                $country = $citydata['country_code'];
                $state = $citydata['subadmin1_code'];

                if (isset($_POST['location'])) {
                    $location = $_POST['location'];
                } else {
                    $location = '';
                }
                $mapLat = $_POST['latitude'];
                $mapLong = $_POST['longitude'];
                $latlong = $mapLat . "," . $mapLong;

                $post_title = removeEmailAndPhoneFromString($_POST['title']);
                $slug = create_post_slug($post_title);

                if (isset($_POST['tags'])) {
                    $tags = $_POST['tags'];
                } else {
                    $tags = '';
                }

                /*if ($config['post_auto_approve'] == 1) {
                    $status = "active";
                } else {
                    $status = "pending";
                }*/

                if ($_POST['catid'] == 9 || $_POST['catid'] == 10) {
                    $status = "active";
                } else {
                    $status = "pending";
                }

                if (checkloggedin()) {
                    $group_id = get_user_group();
                    // Get usergroup details
                    switch ($group_id) {
                        case 'free':
                            $plan = json_decode(get_option('free_membership_plan'), true);
                            $group_get_info = $plan['settings'];

                            break;
                        case 'trial':
                            $plan = json_decode(get_option('trial_membership_plan'), true);
                            $group_get_info = $plan['settings'];

                            break;
                        default:
                            $plan = ORM::for_table($config['db']['pre'] . 'plans')
                                ->select('settings')
                                ->where('id', $group_id)
                                ->find_one();
                            if (!isset($plan['settings'])) {
                                $plan = json_decode(get_option('free_membership_plan'), true);
                                $group_get_info = $plan['settings'];

                            } else {
                                $group_get_info = json_decode($plan['settings'], true);

                            }
                            break;
                    }
                } else {
                    $plan = json_decode(get_option('free_membership_plan'), true);
                    $group_get_info = $plan['settings'];
                }

                $urgent_project_fee = $group_get_info['urgent_project_fee'];
                $featured_project_fee = $group_get_info['featured_project_fee'];
                $highlight_project_fee = $group_get_info['highlight_project_fee'];

                $ad_duration = $group_get_info['ad_duration'];
                $timenow = date('Y-m-d H:i:s');
                $expire_time = date('Y-m-d H:i:s', strtotime($timenow . ' +' . $ad_duration . ' day'));
                $expire_timestamp = strtotime($expire_time);

                if (isset($_POST['available_days'])) {
                    $expired_date = date('Y-m-d H:i:s', strtotime($timenow . ' +' . $_POST['available_days'] . ' day'));
                } else {
                    $expired_date = date('Y-m-d H:i:s', strtotime($timenow . ' +7 day'));
                }

                if (validate_input($_POST['catid']) == 9) {
                    $postType = 'training';
                } else if (validate_input($_POST['catid']) == 10) {
                    $postType = 'event';
                } else {
                    $postType = 'other';
                }

                $promoVideoFileName = '';
                if (isset($_FILES["trainingPromoVideo"]) && ($_POST['catid'] == 9 || $_POST['catid'] == 10)) {
                    // Define the target directory for storing video files
                    $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/training_video/';
                    // Create the target directory if it doesn't exist
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    $allowedExtensions = ["mp4", "avi", "mov", "mkv"];
                    $maxSizeMB = (int)$_POST["max_size"];
                
                    // Check if the file has no errors
                    if ($_FILES["trainingPromoVideo"]["error"] === UPLOAD_ERR_OK) {
                        // Validate file size
                        $maxSizeBytes = $maxSizeMB * 1024 * 1024; // Convert MB to bytes
                        if ($_FILES["trainingPromoVideo"]["size"] <= $maxSizeBytes) {
                            // Validate file extension
                            $fileExtension = strtolower(pathinfo($_FILES["trainingPromoVideo"]["name"], PATHINFO_EXTENSION));
                            if (in_array($fileExtension, $allowedExtensions)) {
                                $trainingPromoVideoFileName = $_FILES['trainingPromoVideo']['name'];
                                $trainingPromoVideoTempFileName = $_FILES['trainingPromoVideo']['tmp_name'];
                                if ($trainingPromoVideoTempFileName != '') {
                                    $extension = pathinfo($trainingPromoVideoFileName, PATHINFO_EXTENSION);
                                    $trainingPromoVideoNewFileName = microtime(true) . '.' . $extension;
                                    if (!empty($trainingPromoVideoNewFileName)) {
                                        $trainingPromoVideoFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/training_video/' . $trainingPromoVideoNewFileName;
                                        if (move_uploaded_file($trainingPromoVideoTempFileName, $trainingPromoVideoFilePath)) {
                                            $promoVideoFileName = $trainingPromoVideoNewFileName; 
                                        } 
                                    }
                                }
                            } 
                        } 
                    } 
                }
                
                if(isset($_POST['event_date']) && validate_input($_POST['catid']) == 10){
                    $event_date = $_POST['event_date'];
                } 

                if(isset($_POST['event_time']) && validate_input($_POST['catid']) == 10){
                    $event_time = date("h:i A", strtotime($_POST['event_time']));
                } 
                
                $item_insrt = ORM::for_table($config['db']['pre'] . 'product')->create();
                $item_insrt->user_id = $_SESSION['user']['id'];
                $item_insrt->seller_name = validate_input($_POST['seller_name']);
                $item_insrt->product_name = validate_input($post_title);
                $item_insrt->slug = $slug;
                $item_insrt->status = validate_input($status);
                $item_insrt->category = validate_input($_POST['catid']);
                $item_insrt->sub_category = validate_input($_POST['subcatid']);
                $item_insrt->post_type = $postType;
                $item_insrt->event_date = !empty($event_date) ? $event_date : date("Y-m-d");
                $item_insrt->event_time = !empty($event_time) ? $event_time : date("h:i A");
                $item_insrt->description = $description;
                $item_insrt->price = validate_input($price);
                $item_insrt->negotiable = validate_input($negotiable);
                $item_insrt->phone = validate_input($phone);
                $item_insrt->hide_phone = validate_input($hide_phone);
                $item_insrt->location = validate_input($location);
                $item_insrt->city = validate_input($_POST['city']);
                $item_insrt->state = validate_input($state);
                $item_insrt->country = validate_input($country);
                $item_insrt->latlong = validate_input($latlong);
                $item_insrt->screen_shot = $item_screen;
                $item_insrt->promo_video = $promoVideoFileName;
                $item_insrt->tag = validate_input($tags);
                $item_insrt->created_at = $timenow;
                $item_insrt->updated_at = $timenow;
                $item_insrt->expire_days = isset($_POST['available_days']) ? $_POST['available_days'] : 7;
                $item_insrt->expired_date = $expired_date;
                $item_insrt->expire_date = $expire_timestamp;
                $item_insrt->save();
                // Print last executed query
                // $lastQuery = ORM::getLastQuery();
                // echo $lastQuery;
                // die;
                $product_id = $item_insrt->id();
                //Send Custom Notification to user
                if (!empty($product_id)) {
                    $users = ORM::for_table($config['db']['pre'] . 'user')->where('status', '1')->whereNotEqual('id', $_SESSION['user']['id'])->find_many();
                    if (count($users) > 0) {
                        foreach ($users as $user) {
                            $insert_notification = ORM::for_table($config['db']['pre'] . 'custom_notification')->create();
                            $insert_notification->notification_id = $product_id;
                            $insert_notification->type = 'post';
                            $insert_notification->title = validate_input($post_title);
                            $insert_notification->redirect_url = $config['site_url'] . 'ad/' . $product_id . '/' . $slug;
                            $insert_notification->user_id = $user['id'];
                            $insert_notification->status = 0;
                            $insert_notification->created_at = date("Y-m-d H:i:s");
                            $insert_notification->save();
                        }
                    }
                    /*if (validate_input($_POST['catid']) == 10) {
                        // Loop through submitted data and insert into the database
                        if (isset($_POST['ticket_type']) && isset($_POST['ticket_price']) && isset($_POST['available_quantity']) && isset($_POST['selling_mode'])) {
                            $ticketTypes = $_POST['ticket_type'];
                            $ticketPrices = $_POST['ticket_price'];
                            $availableQuantities = $_POST['available_quantity'];
                            $sellingModes = $_POST['selling_mode'];

                            foreach ($ticketTypes as $key => $ticketType) {
                                $ticketPrice = $ticketPrices[$key];
                                $availableQuantity = $availableQuantities[$key];
                                $sellingMode = $sellingModes[$key];
                                
                                //Insert record in Training Gallery
                                $tGInsert = ORM::for_table($config['db']['pre'] . 'product_event_types')->create();
                                $tGInsert->product_id = $product_id;
                                $tGInsert->ticket_type = $ticketType;
                                $tGInsert->ticket_price = $ticketPrice;
                                $tGInsert->available_quantity = $availableQuantity;
                                $tGInsert->remaining_quantity = $availableQuantity;
                                $tGInsert->selling_mode = $sellingMode;
                                $tGInsert->created_at = date("Y-m-d H:i:s");
                                $tGInsert->save();
                            }
                        }
                    }*/

                    /*if (validate_input($_POST['catid']) == 9) {
                        // Check if files were uploaded
                        if (isset($_FILES['trainingVideo'])) {
                            $video = '';
                            // Define the target directory for storing video files
                            $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/training_video/';
                            // Create the target directory if it doesn't exist
                            if (!file_exists($targetDir)) {
                                mkdir($targetDir, 0777, true);
                            }
                            $countTrainingVidoe = 0;
                            // Loop through the uploaded files
                            foreach ($_FILES['trainingVideo']['tmp_name'] as $key => $tmp_name) {
                                $trainingVideoFileName = $_FILES['trainingVideo']['name'][$key];
                                $trainingVideoTempFileName = $_FILES['trainingVideo']['tmp_name'][$key];
                                if ($trainingVideoTempFileName != '') {
                                    $extension = pathinfo($trainingVideoFileName, PATHINFO_EXTENSION);
                                    $trainingVideoNewFileName = microtime(true) . '.' . $extension;
                                    if (!empty($trainingVideoNewFileName)) {
                                        if ($countTrainingVidoe == 0) {
                                            $video = $trainingVideoNewFileName;
                                        } elseif ($countTrainingVidoe >= 1) {
                                            $video = $video . "," . $trainingVideoNewFileName;
                                        }
                                    }
                                    $trainingVideoFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/training_video/' . $trainingVideoNewFileName;
                                    move_uploaded_file($trainingVideoTempFileName, $trainingVideoFilePath);
                                    $countTrainingVidoe++;
                                }
                            }

                        }
                        //Insert record in Training Gallery
                        $tGInsert = ORM::for_table($config['db']['pre'] . 'training_gallery')->create();
                        $tGInsert->product_id = $product_id;
                        $tGInsert->training_video = $video;
                        $tGInsert->save();
                    } else if (validate_input($_POST['catid']) == 10) {
                        //Write Insert Event code here
                    }*/

                }
                add_post_customField_data($_POST['catid'], $_POST['subcatid'], $product_id);

                $amount = 0;
                $trans_desc = $lang['PACKAGE'];

                $premium_tpl = "";

                if ($featured == 1) {
                    $amount = $featured_project_fee;
                    $trans_desc = $trans_desc . " " . $lang['FEATURED'];
                    $premium_tpl .= ' <div class="ModalPayment-paymentDetails">
                                            <div class="ModalPayment-label">' . $lang['FEATURED'] . '</div>
                                            <div class="ModalPayment-price">
                                                <span class="ModalPayment-totalCost-price">' . $config['currency_sign'] . $featured_project_fee . '</span>
                                            </div>
                                        </div>';
                }
                if ($urgent == 1) {
                    $amount = $amount + $urgent_project_fee;
                    $trans_desc = $trans_desc . " " . $lang['URGENT'];
                    $premium_tpl .= ' <div class="ModalPayment-paymentDetails">
                                            <div class="ModalPayment-label">' . $lang['URGENT'] . '</div>
                                            <div class="ModalPayment-price">
                                                <span class="ModalPayment-totalCost-price">' . $config['currency_sign'] . $urgent_project_fee . '</span>
                                            </div>
                                        </div>';
                }
                if ($highlight == 1) {
                    $amount = $amount + $highlight_project_fee;
                    $trans_desc = $trans_desc . " " . $lang['HIGHLIGHT'];
                    $premium_tpl .= ' <div class="ModalPayment-paymentDetails">
                                            <div class="ModalPayment-label">' . $lang['HIGHLIGHT'] . '</div>
                                            <div class="ModalPayment-price">
                                                <span class="ModalPayment-totalCost-price">' . $config['currency_sign'] . $highlight_project_fee . '</span>
                                            </div>
                                        </div>';
                }

                if ($amount > 0) {
                    $premium_tpl .= '<div class="ModalPayment-totalCost">
                                            <span class="ModalPayment-totalCost-label">' . $lang['TOTAL'] . ': </span>
                                            <span class="ModalPayment-totalCost-price">' . $config['currency_sign'] . $amount . " " . $config['currency_code'] . '</span>
                                        </div>';

                    /*These details save in session and get on payment sucecess*/
                    $title = $post_title;
                    $payment_type = "premium";
                    $access_token = uniqid();

                    $_SESSION['quickad'][$access_token]['name'] = $title;
                    $_SESSION['quickad'][$access_token]['amount'] = $amount;
                    $_SESSION['quickad'][$access_token]['payment_type'] = $payment_type;
                    $_SESSION['quickad'][$access_token]['trans_desc'] = $trans_desc;
                    $_SESSION['quickad'][$access_token]['product_id'] = $product_id;
                    $_SESSION['quickad'][$access_token]['featured'] = $featured;
                    $_SESSION['quickad'][$access_token]['urgent'] = $urgent;
                    $_SESSION['quickad'][$access_token]['highlight'] = $highlight;
                    /*End These details save in session and get on payment sucecess*/

                    $url = $link['PAYMENT'] . "/" . $access_token;
                    $response = array();
                    $response['status'] = "success";
                    $response['ad_type'] = "package";
                    $response['redirect'] = $url;
                    $response['tpl'] = $premium_tpl;

                    echo json_encode($response, JSON_UNESCAPED_SLASHES);
                    die();
                } else {
                    if (validate_input($_POST['catid']) == 9) {
                        $ad_link = $link['POST-TRAINING-VIDEO'] . "/" . $product_id;
                    } else if (validate_input($_POST['catid']) == 10) {
                        $ad_link = $link['POST-EVENT'] . "/" . $product_id;
                    } else {
                        $ad_link = $link['POST-DETAIL'] . "/" . $product_id;
                    }
                    unset($_POST);
                    $json = '{"status" : "success","ad_type" : "free","redirect" : "' . $ad_link . '"}';
                    echo $json;
                    die();
                }
            } else {
                $status = "error";
                $errors[]['message'] = $lang['POST_SAVE_ERROR'];
            }

        } else {
            $status = "error";
        }

        $json = '{"status" : "' . $status . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
        echo $json;
        die();
    }
}

if (isset($_GET['country'])) {
    if ($_GET['country'] != "") {
        change_user_country($_GET['country']);
    }
}

$country_code = check_user_country();
$currency_info = set_user_currency($country_code);
$currency_sign = $currency_info['html_entity'];

if ($latlong = get_lat_long_of_country($country_code)) {
    $mapLat = $latlong['lat'];
    $mapLong = $latlong['lng'];
} else {
    $mapLat = get_option("home_map_latitude");
    $mapLong = get_option("home_map_longitude");
}

$custom_fields = get_customFields_by_catid();

// Output to template
$page = new HtmlTemplate('templates/' . $config['tpl_name'] . '/ad-post.tpl');
$page->SetParameter('OVERALL_HEADER', create_header($lang['POST_AD']));
$page->SetLoop('HTMLPAGE', get_html_pages());
$page->SetLoop('COUNTRYLIST', get_country_list());
$page->SetLoop('CATEGORY', get_maincategory());
$page->SetLoop('CUSTOMFIELDS', $custom_fields);
$page->SetParameter('SHOWCUSTOMFIELD', (count($custom_fields) > 0) ? 1 : 0);
$page->SetParameter('LATITUDE', $mapLat);
$page->SetParameter('LONGITUDE', $mapLong);
$page->SetParameter('USER_COUNTRY', strtolower($country_code));
$page->SetParameter('USER_CURRENCY_SIGN', $currency_sign);
$page->SetParameter('PAGE_TITLE', $lang['POST_AD']);

if (checkloggedin()) {
    $group_id = get_user_group();
    // Get usergroup details
    switch ($group_id) {
        case 'free':
            $plan = json_decode(get_option('free_membership_plan'), true);
            $group_get_info = $plan['settings'];

            break;
        case 'trial':
            $plan = json_decode(get_option('trial_membership_plan'), true);
            $group_get_info = $plan['settings'];

            break;
        default:
            $plan = ORM::for_table($config['db']['pre'] . 'plans')
                ->select('settings')
                ->where('id', $group_id)
                ->find_one();
            if (!isset($plan['settings'])) {
                $plan = json_decode(get_option('free_membership_plan'), true);
                $group_get_info = $plan['settings'];

            } else {
                $group_get_info = json_decode($plan['settings'], true);

            }
            break;
    }
} else {

    $plan = json_decode(get_option('free_membership_plan'), true);
    $group_get_info = $plan['settings'];
}

$urgent_project_fee = $group_get_info['urgent_project_fee'];
$featured_project_fee = $group_get_info['featured_project_fee'];
$highlight_project_fee = $group_get_info['highlight_project_fee'];
$urgent_duration = $group_get_info['urgent_duration'];
$featured_duration = $group_get_info['featured_duration'];
$highlight_duration = $group_get_info['highlight_duration'];

$page->SetParameter('FEATURED_FEE', price_format($featured_project_fee, $config['currency_code']));
$page->SetParameter('URGENT_FEE', price_format($urgent_project_fee, $config['currency_code']));
$page->SetParameter('HIGHLIGHT_FEE', price_format($highlight_project_fee, $config['currency_code']));
$page->SetParameter('FEATURED_PRICE', $featured_project_fee);
$page->SetParameter('URGENT_PRICE', $urgent_project_fee);
$page->SetParameter('HIGHLIGHT_PRICE', $urgent_project_fee);
$page->SetParameter('FEATURED_DURATION', $featured_duration);
$page->SetParameter('URGENT_DURATION', $urgent_duration);
$page->SetParameter('HIGHLIGHT_DURATION', $highlight_duration);
$page->SetParameter('LANGUAGE_DIRECTION', get_current_lang_direction());
$page->SetParameter('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
