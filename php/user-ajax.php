<?php
require_once '../includes/autoload.php';
require_once '../includes/lang/lang_' . $config['lang'] . '.php';

sec_session_start();
define("ROOTPATH", dirname(__DIR__));
if (isset($_GET['action'])) {
    if ($_GET['action'] == "forgot_pass") {forgot_pass();}
    if ($_GET['action'] == "ajaxsignup") {ajaxsignup();}
    if ($_GET['action'] == "mobile_verify") {mobile_verify();}
    if ($_GET['action'] == "otp_verify") {otp_verify();}
    if ($_GET['action'] == "dashboard_mobile_verify") {dashboard_mobile_verify();}
    if ($_GET['action'] == "email_contact_seller") {email_contact_seller();}
    if ($_GET['action'] == "deleteMyAd") {deleteMyAd();}
    if ($_GET['action'] == "deleteResumitAd") {deleteResumitAd();}
    if ($_GET['action'] == "openlocatoionPopup") {openlocatoionPopup();}
    if ($_GET['action'] == "getlocHomemap") {getlocHomemap();}
    if ($_GET['action'] == "searchCityFromCountry") {searchCityFromCountry();}
    if ($_GET['action'] == "submitBlogComment") {submitBlogComment();}
}

if (isset($_POST['action'])) {
    if ($_POST['action'] == "check_availability") {check_availability();}
    if ($_POST['action'] == "removeImage") {removeImage();}
    if ($_POST['action'] == "hideItem") {hideItem();}
    if ($_POST['action'] == "removeAdImg") {removeAdImg();}
    if ($_POST['action'] == "setFavAd") {setFavAd();}
    if ($_POST['action'] == "removeFavAd") {removeFavAd();}
    if ($_POST['action'] == "getsubcatbyidList") {getsubcatbyidList();}
    if ($_POST['action'] == "getsubcatbyid") {getsubcatbyid();}
    if ($_POST['action'] == "getCustomFieldByCatID") {getCustomFieldByCatID();}

    if ($_POST['action'] == "getStateByCountryID") {getStateByCountryID();}
    if ($_POST['action'] == "getCityByStateID") {getCityByStateID();}
    if ($_POST['action'] == "getCityidByCityName") {getCityidByCityName();}
    if ($_POST['action'] == "ModelGetStateByCountryID") {ModelGetStateByCountryID();}
    if ($_POST['action'] == "ModelGetCityByStateID") {ModelGetCityByStateID();}
    if ($_POST['action'] == "searchStateCountry") {searchStateCountry();}
    if ($_POST['action'] == "searchCityStateCountry") {searchCityStateCountry();}
    if ($_POST['action'] == "ajaxlogin") {ajaxlogin();}
    if ($_POST['action'] == "email_verify") {email_verify();}
    if ($_POST['action'] == "quickad_ajax_home_search") {quickad_ajax_home_search();}
    if ($_POST['action'] == "get_notification") {get_notification();}

}

function check_availability()
{
    global $config, $lang;

    // Check if this is an Name availability check from signup page using ajax
    if (isset($_POST["name"])) {
        if (empty($_POST["name"])) {
            $name_error = $lang['ENTER_FULL_NAME'];
            echo "<span class='status-not-available'> " . $name_error . "</span>";
            exit;
        }

        $name_length = strlen(utf8_decode($_POST['name']));
        if (($name_length < 4) or ($name_length > 21)) {
            $name_error = $lang['NAMELEN'];
            echo "<span class='status-not-available'> " . $name_error . ".</span>";
            exit;
        } else {
            echo "<span class='status-available'>" . $lang['SUCCESS'] . "</span>";
            exit;
        }

        /*if(preg_match('/[^A-Za-z\s]/',$_POST['name']))
    {
    $name_error = $lang['ONLY_LETTER_SPACE'];
    echo "<span class='status-not-available'> ".$name_error." [A-Z,a-z,0-9]</span>";
    exit;
    }*/
    }

// Check if this is an Username availability check from signup page using ajax
    if (isset($_POST["username"])) {

        if (empty($_POST["username"])) {
            $username_error = $lang['ENTERUNAME'];
            echo "<span class='status-not-available'> " . $username_error . "</span>";
            exit;
        }

        if (preg_match('/[^A-Za-z0-9]/', $_POST['username'])) {
            $username_error = $lang['USERALPHA'];
            echo "<span class='status-not-available'> " . $username_error . " [A-Z,a-z,0-9]</span>";
            exit;
        } elseif ((strlen($_POST['username']) < 4) or (strlen($_POST['username']) > 16)) {
            $username_error = $lang['USERLEN'];
            echo "<span class='status-not-available'> " . $username_error . ".</span>";
            exit;
        } else {
            if (checkloggedin()) {
                if ($_POST["username"] != $_SESSION['user']['username']) {
                    $user_count = check_username_exists($_POST["username"]);
                    if ($user_count > 0) {
                        $username_error = $lang['USERUNAV'];
                        echo "<span class='status-not-available'>" . $username_error . "</span>";
                    } else {
                        $username_error = $lang['USERUAV'];
                        echo "<span class='status-available'>" . $username_error . "</span>";
                    }
                    exit;
                } else {
                    echo "<span class='status-available'>" . $lang['SUCCESS'] . "</span>";
                    exit;
                }
            } else {
                $user_count = check_username_exists($_POST["username"]);
                if ($user_count > 0) {
                    $username_error = $lang['USERUNAV'];
                    echo "<span class='status-not-available'>" . $username_error . "</span>";
                } else {
                    $username_error = $lang['USERUAV'];
                    echo "<span class='status-available'>" . $username_error . "</span>";
                }
                exit;
            }

        }

    }

// Check if this is an Email availability check from signup page using ajax
    if (isset($_POST["email"])) {

        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';

        if (empty($_POST["email"])) {
            $email_error = $lang['ENTEREMAIL'];
            echo "<span class='status-not-available'> " . $email_error . "</span>";
            exit;
        } elseif (!preg_match($regex, $_POST['email'])) {
            $email_error = $lang['EMAILINV'];
            echo "<span class='status-not-available'> " . $email_error . ".</span>";
            exit;
        }

        if (checkloggedin()) {
            $ses_userdata = get_user_data($_SESSION['user']['username']);
            if ($_POST["email"] != $ses_userdata['email']) {
                $user_count = check_account_exists($_POST["email"]);
                if ($user_count > 0) {
                    $email_error = $lang['ACCAEXIST'];
                    echo "<span class='status-not-available'>" . $email_error . "</span>";
                } else {
                    $email_error = $lang['EMAILAVL'];
                    echo "<span class='status-available'>" . $email_error . "</span>";
                }
                exit;
            } else {
                echo "<span class='status-available'>" . $lang['SUCCESS'] . "</span>";
                exit;
            }
        } else {
            $user_count = check_account_exists($_POST["email"]);
            if ($user_count > 0) {
                $email_error = $lang['ACCAEXIST'];
                echo "<span class='status-not-available'>" . $email_error . "</span>";
            } else {
                $email_error = $lang['EMAILAVL'];
                echo "<span class='status-available'>" . $email_error . "</span>";
            }
            exit;
        }
    }
    // Phone Number availability check from signup page using ajax
    if (isset($_POST["phone"])) {
        $phoneNumber = $_POST["phone"];
        if (!empty($phoneNumber)) { // phone number is not empty
            /*if(preg_match('/^\d{10}$/',$phoneNumber)){
        $phoneNumber = '0' . $phoneNumber;

        // your other code here
        }
        else{
        $phone_error = $lang['MOBILE_INVALID'];
        echo "<span class='status-not-available'> ".$phone_error."</span>";
        exit;
        }*/
        } else {
            $phone_error = $lang['ENTER_MOBILE'];
            echo "<span class='status-not-available'> " . $phone_error . "</span>";
            exit;
        }
    }

// Check if this is an Password availability check from signup page using ajax
    if (isset($_POST["password"])) {

        if (empty($_POST["password"])) {
            $password_error = $lang['ENTERPASS'];
            echo "<span class='status-not-available'> " . $password_error . "</span>";
            exit;
        } elseif ((strlen($_POST['password']) < 5) or (strlen($_POST['password']) > 21)) {
            $password_error = $lang['PASSLENG'];
            echo "<span class='status-not-available'> " . $lang['PASSLENG'] . ".</span>";
            exit;
        } else {
            echo "<span class='status-available'>" . $lang['SUCCESS'] . "</span>";
            exit;
        }

    }
    die();
}

function forgot_pass()
{
    global $config, $lang;

    // Check if they are trying to retrieve their email
    if (isset($_POST['email'])) {
        // Lookup the email address
        $email_info1 = check_account_exists($_POST['email']);

        // Check if the email address exists
        if ($email_info1 != 0) {
            $email_userid = get_user_id_by_email($_POST['email']);
            // Send the email
            send_forgot_email($_POST['email'], $email_userid);

            echo 'success';
            die();
        } else {
            echo $lang['EMAILNOTEXIST'];
        }
    } else {
        echo $lang['ENTEREMAIL'];
    }
    die();
}

function ajaxlogin()
{
    global $config, $lang;
    $loggedin = userlogin($_POST['username'], $_POST['password']);

    if (!is_array($loggedin)) {
        echo $lang['USERNOTFOUND'];
    } elseif ($loggedin['status'] == 2) {
        echo $lang['ACCOUNTBAN'];
    } else {
        create_user_session($loggedin['id'], $loggedin['username'], $loggedin['password']);
        if (isset($_POST["remember"]) && $_POST["remember"] == 1) {
            setcookie('quickad_remember_me', $loggedin['id'], time() + 3600 * 24 * 30, '/', null, null, true);
        } else {
            if (isset($_COOKIE["quickad_remember_me"])) {
                setcookie("quickad_remember_me", "");
            }
        }
        update_lastactive();

        echo "success";
    }
    die();
}

function ajaxsignup()
{
    global $config, $lang;

    $errors = 0;

    if (empty($_POST["username"])) {
        $errors++;
        echo $lang['ENTERUNAME'];
        die();
    } elseif (preg_match('/[^A-Za-z0-9]/', $_POST['username'])) {
        $errors++;
        echo $lang['USERALPHA'];
        die();
    } elseif ((strlen($_POST['username']) < 4) or (strlen($_POST['username']) > 16)) {
        $errors++;
        echo $lang['USERLEN'];
        die();
    } else {
        $user_count = check_username_exists($_POST["username"]);
        if ($user_count > 0) {
            $errors++;
            echo $lang['USERUNAV'];
            die();
        }
    }

    // Check if this is an Email availability check from signup page using ajax
    $_POST["email"] = strtolower($_POST["email"]);
    $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';

    if (empty($_POST["email"])) {
        $errors++;
        echo $lang['ENTEREMAIL'];
        die();
    } elseif (!preg_match($regex, $_POST['email'])) {
        $errors++;
        echo $lang['EMAILINV'];
        die();
    } else {
        $user_count = check_account_exists($_POST["email"]);
        if ($user_count > 0) {
            $errors++;
            echo $lang['ACCAEXIST'];
            die();
        }
    }

    $phoneNumber = $_POST["phone"];
    if (!empty($phoneNumber)) { // phone number is not empty
        /*if(preg_match('/^\d{10}$/',$phoneNumber)){
    $phoneNumber = '0' . $phoneNumber;

    // your other code here
    }
    else{
    $errors++;
    echo $lang['MOBILE_INVALID'];
    die();
    }*/
    } else {
        $errors++;
        echo $lang['ENTER_MOBILE'];
        die();
    }

    // Check if this is an Password availability check from signup page using ajax
    if (empty($_POST["password"])) {
        $errors++;
        echo $lang['ENTERPASS'];
        die();
    } elseif ((strlen($_POST['password']) < 4) or (strlen($_POST['password']) > 21)) {
        $errors++;
        echo $lang['PASSLENG'];
        die();
    }
    if ($config['recaptcha_mode'] == 1) {
        if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
            //your site secret key
            //$secret = '6Lci1yMTAAAAAFjUEeYUBIXvOxsYDXkqL45dtoch';
            $secret = $config['recaptcha_private_key'];
            //get verify response data
            $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
            $responseData = json_decode($verifyResponse);
            if (!$responseData->success) {
                $errors++;
                echo $lang['RECAPTCHA_ERROR'];
                die();
            }
        } else {
            $errors++;
            echo $lang['RECAPTCHA_CLICK'];
            die();
        }
    }

    if ($errors == 0) {
        $confirm_id = get_random_id();
        $location = getLocationInfoByIp();
        $password = $_POST["password"];
        $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);
        $now = date("Y-m-d H:i:s");

        $insert_user = ORM::for_table($config['db']['pre'] . 'user')->create();
        $insert_user->status = '0';
        $insert_user->name = $_POST["username"];
        $insert_user->username = $_POST["username"];
        $insert_user->password_hash = $pass_hash;
        $insert_user->email = $_POST['email'];
        $insert_user->confirm = $confirm_id;
        $insert_user->created_at = $now;
        $insert_user->updated_at = $now;
        $insert_user->country = $location['country'];
        $insert_user->city = $location['city'];
        $insert_user->save();

        $user_id = $insert_user->id();

        $insert = !empty($user_id > 0) ? true : false;

        if ($insert) {
            insert_mobile_number($user_id, $_POST['phone']);

            /*SEND CONFIRMATION EMAIL*/
            email_template("signup_confirm", $user_id);

            /*SEND ACCOUNT DETAILS EMAIL*/
            email_template("signup_details", $user_id, $password);

            $loggedin = userlogin($_POST['username'], $_POST['password']);

            create_user_session($loggedin['id'], $loggedin['username'], $loggedin['password']);
            echo "success";
        } else {
            echo $lang['ERROR_TRY_AGAIN'];
        }
    }
    die();
}

function mobile_verify()
{
    global $config, $lang, $link;
    $errors = 0;

    // If mobile number submitted by the user
    if (isset($_POST['submit_mobile'])) {
        if (!empty($_POST['mobile_no'])) {
            // Recipient mobile number
            $recipient_no = $_POST['mobile_no'];

            // Generate random verification code
            $rand_no = rand(10000, 99999);

            // Check previous entry
            $conditions = array(
                'mobile_number' => $recipient_no,
            );

            $checkPrev = ORM::for_table($config['db']['pre'] . 'mobile_numbers')
                ->where($conditions)
                ->count();

            // Insert or update otp in the database
            if ($checkPrev) {

                $mobile_num = ORM::for_table($config['db']['pre'] . 'mobile_numbers')
                    ->where($conditions)
                    ->find_one();
                if (isset($mobile_num['user_id'])) {

                    $mobile_num->set('verification_code', $rand_no);
                    $mobile_num->save();

                    $insert = isset($mobile_num) ? true : false;
                } else {
                    $insert_mobile = ORM::for_table($config['db']['pre'] . 'mobile_numbers')->create();
                    $insert_mobile->mobile_number = $recipient_no;
                    $insert_mobile->verification_code = $rand_no;
                    $insert_mobile->verified = '0';
                    $insert_mobile->save();

                    $number_id = $insert_mobile->id();
                    $insert = !empty($number_id > 0) ? true : false;
                }

            } else {
                echo 'This mobile number not registered.';
                die();

                /*$insert_mobile = ORM::for_table($config['db']['pre'].'mobile_numbers')->create();
            $insert_mobile->mobile_number = $recipient_no;
            $insert_mobile->verification_code = $rand_no;
            $insert_mobile->verified = '0';
            $insert_mobile->save();

            $number_id = $insert_mobile->id();
            $insert = !empty($number_id > 0)?true:false;*/
            }

            if ($insert) {
                // Send otp to user via SMS
                if (checkloggedin()) {
                    $username = $_SESSION['user']['username'];
                } else {
                    $username = 'user';
                }
                // Send otp to user via SMS
                $page = new HtmlTemplate();
                $page->html = $config['sms_otp_template'];
                $page->SetParameter('USERNAME', $username);
                $page->SetParameter('OTP_CODE', $rand_no);
                $sms_body = $page->CreatePageReturn($lang, $config, $link);

                $send = sendSMS($recipient_no, $sms_body);
                if ($send) {
                    echo "success";
                } else {
                    echo "We're facing some issue on sending SMS, please try again.";
                }
            } else {
                echo 'Some problem occurred, please try again.';
            }
        } else {
            echo $lang['ENTER_MOBILE'];
            die();
        }

    }
    die();
}

function otp_verify()
{
    global $config, $lang;
    if (isset($_POST['submit_otp']) && !empty($_POST['otp_code'])) {
        $otpDisplay = 1;
        $recipient_no = $_POST['mobile_no'];
        if (!empty($_POST['otp_code'])) {
            $otp_code = $_POST['otp_code'];

            // Verify otp code
            $conditions = array(
                'mobile_number' => $recipient_no,
                'verification_code' => $otp_code,
            );

            $checkPrev = ORM::for_table($config['db']['pre'] . 'mobile_numbers')
                ->where($conditions)
                ->count();

            if ($checkPrev) {
                $mobile_num = ORM::for_table($config['db']['pre'] . 'mobile_numbers')
                    ->where($conditions)
                    ->find_one();
                $mobile_num->set('verified', '1');
                $mobile_num->save();

                if (isset($mobile_num['user_id'])) {
                    $user_id = $mobile_num['user_id'];

                    $info = ORM::for_table($config['db']['pre'] . 'user')
                        ->select_many('id', 'status', 'username', 'password_hash')
                        ->where('id', $user_id)
                        ->find_one();

                    $user_id = $info['id'];
                    $status = $info['status'];
                    $username = $info['username'];
                    $db_password = $info['password_hash'];

                    create_user_session($user_id, $username, $db_password);
                    echo 'success';
                } else {
                    echo 'This mobile number not registered with any user.';
                }

            } else {
                echo 'Verification code incorrect, please try again.';
            }
        } else {
            echo 'Please enter the verification code.';
        }
    }
    die();
}

function dashboard_mobile_verify()
{
    global $config, $lang, $link;
    $errors = 0;
    // If mobile number submitted by the user
    if (isset($_POST['submit_mobile'])) {
        if (!empty($_POST['mobile_no'])) {
            // Recipient mobile number
            $recipient_no = $_POST['mobile_no'];

            // Generate random verification code
            $rand_no = rand(10000, 99999);

            // Check previous entry
            $checkPrev = ORM::for_table($config['db']['pre'] . 'mobile_numbers')
                ->where('user_id', $_SESSION['user']['id'])
                ->count();
            // Insert or update otp in the database
            if ($checkPrev) {
                $mobile_num = ORM::for_table($config['db']['pre'] . 'mobile_numbers')
                    ->where('user_id', $_SESSION['user']['id'])
                    ->find_one();
                $mobile_num->set('mobile_number', $recipient_no);
                $mobile_num->set('verification_code', $rand_no);
                $mobile_num->set('verified', '0');
                $mobile_num->save();

                $insert = isset($mobile_num) ? true : false;

            } else {
                $insert_mobile = ORM::for_table($config['db']['pre'] . 'mobile_numbers')->create();
                $insert_mobile->user_id = $_SESSION['user']['id'];
                $insert_mobile->mobile_number = $recipient_no;
                $insert_mobile->verification_code = $rand_no;
                $insert_mobile->verified = '0';
                $insert_mobile->save();

                $number_id = $insert_mobile->id();
                $insert = !empty($number_id > 0) ? true : false;
            }

            if ($insert) {
                // Send otp to user via SMS
                if (checkloggedin()) {
                    $username = $_SESSION['user']['username'];
                } else {
                    $username = 'user';
                }

                // Send otp to user via SMS
                $page = new HtmlTemplate();
                $page->html = $config['sms_otp_template'];
                $page->SetParameter('USERNAME', $username);
                $page->SetParameter('OTP_CODE', $rand_no);
                $sms_body = $page->CreatePageReturn($lang, $config, $link);

                $send = sendSMS($recipient_no, $sms_body);
                if ($send) {
                    echo "success";
                } else {
                    echo "We're facing some issue on sending SMS, please try again.";
                }
            } else {
                echo 'Some problem occurred, please try again.';
            }
        } else {
            echo $lang['ENTER_MOBILE'];
            die();
        }

    }
    die();
}

function get_notification()
{
    global $config, $lang;

    if (checkloggedin()) {
        // Check previous entry
        $html = '';
        $getTotalNotificationCount = ORM::for_table($config['db']['pre'] . 'custom_notification')
            ->where('user_id', $_SESSION['user']['id'])
            ->where('status', false)
            ->count();

        if (!empty($getTotalNotificationCount)) {
            $html .= '<div class="toggleNotifi">
                        <div class="img-box">
                            <i class="icon-feather-bell"></i>
                            <span class="activePoint">' . $getTotalNotificationCount . '</span>
                        </div>
                    </div>';
        }
        $rows = ORM::for_table($config['db']['pre'] . 'custom_notification')
            ->select_many('id', 'notification_id', 'type', 'title', 'user_id', 'status')
            ->where(array(
                'user_id' => $_SESSION['user']['id'],
                'status' => false,
            ))
            ->order_by_desc('id')
            ->find_many();
        if (count($rows) > 0) {
            $html .= '<div class="menuNotiDrop">
            <ul>';
            foreach ($rows as $info) {
                $slug = '';
                $productId = '';
                if(!empty($info['notification_id'])){
                    $product = ORM::for_table($config['db']['pre'] . 'product')->select(['id','slug'])->find_one($info['notification_id']);
                    if(!empty($product['slug'])){
                        $productId = $product['id'];
                        $slug = $product['slug'];
                    }
                }
                $html .= '<li><a href="'.$config['site_url'].'ad/'.$productId.'/'.$slug.'">&nbsp;'.$info['title'].'</a></li>';
            }
            $html .= '</ul>
            </div>';
        }
        echo $html;
        die();
    } else {
        header("Location: " . $config['site_url'] . "login");
        exit;
    }
}

function email_verify()
{
    global $config, $lang;

    if (checkloggedin()) {
        /*SEND CONFIRMATION EMAIL*/
        email_template("signup_confirm", $_SESSION['user']['id']);

        echo $respond = $lang['SENT'];
        die();

    } else {
        header("Location: " . $config['site_url'] . "login");
        exit;
    }
}

function removeImage()
{
    global $config;
    if (isset($_POST['product_id'])) {
        $id = $_POST['product_id'];
        $info = ORM::for_table($config['db']['pre'] . 'product')->select('screen_shot')->find_one($_POST['product_id']);

        $screnshots = explode(',', $info['screen_shot']);
        if ($key = array_search($_POST['imagename'], $screnshots) != -1) {
            unset($screnshots[$key]);
            $screens = implode(',', $screnshots);
            $product = ORM::for_table($config['db']['pre'] . 'product')->find_one($id);
            $product->screen_shot = $screens;
            $product->save();
        }
    }

}

function email_contact_seller()
{
    global $config, $lang, $link;
    $error = '';
    if (empty($_POST['message'])) {
        $error = $lang['MESSAGE_REQ'];
    }

    if ($config['recaptcha_mode'] == 1) {
        if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
            //your site secret key
            //$secret = '6Lci1yMTAAAAAFjUEeYUBIXvOxsYDXkqL45dtoch';
            $secret = $config['recaptcha_private_key'];
            //get verify response data
            $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
            $responseData = json_decode($verifyResponse);
            if (!$responseData->success) {
                $error = $lang['RECAPTCHA_ERROR'];
            }
        } else {
            $error = $lang['RECAPTCHA_CLICK'];
        }
    }
    if ($error == '') {
        if (isset($_POST['sendemail'])) {
            $item_id = $_POST['id'];
            $iteminfo = get_item_by_id($item_id);
            $item_title = $iteminfo['title'];
            $item_author_name = $iteminfo['author_name'];
            $item_author_email = $iteminfo['author_email'];

            $ad_link = $link['POST-DETAIL'] . '/' . $item_id;
            $page = new HtmlTemplate();
            $page->html = $config['email_sub_contact_seller'];
            $page->SetParameter('ADTITLE', $item_title);
            $page->SetParameter('ADLINK', $ad_link);
            $page->SetParameter('SELLER_NAME', $item_author_name);
            $page->SetParameter('SELLER_EMAIL', $item_author_email);
            $page->SetParameter('SENDER_NAME', $_POST['name']);
            $page->SetParameter('SENDER_EMAIL', $_POST['email']);
            $page->SetParameter('SENDER_PHONE', $_POST['phone']);
            $email_subject = $page->CreatePageReturn($lang, $config, $link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_contact_seller'];
            $page->SetParameter('ADTITLE', $item_title);
            $page->SetParameter('ADLINK', $ad_link);
            $page->SetParameter('SELLER_NAME', $item_author_name);
            $page->SetParameter('SELLER_EMAIL', $item_author_email);
            $page->SetParameter('SENDER_NAME', $_POST['name']);
            $page->SetParameter('SENDER_EMAIL', $_POST['email']);
            $page->SetParameter('SENDER_PHONE', $_POST['phone']);
            $page->SetParameter('MESSAGE', $_POST['message']);
            $email_body = $page->CreatePageReturn($lang, $config, $link);

            email($item_author_email, $item_author_name, $email_subject, $email_body);

            echo 'success';
            die();
        }
    }

    echo 0;
    die();
}

function getStateByCountryID()
{
    global $config;
    $country_code = isset($_POST['id']) ? $_POST['id'] : 0;
    $selectid = isset($_POST['selectid']) ? $_POST['selectid'] : "";

    $rows = ORM::for_table($config['db']['pre'] . 'subadmin1')
        ->select_many('id', 'code', 'name')
        ->where(array(
            'country_code' => $country_code,
            'active' => '1',
        ))
        ->order_by_desc('name')
        ->find_many();

    if (count($rows) > 0) {

        $list = '<option value="">Select State</option>';
        foreach ($rows as $info) {
            $name = $info['name'];
            $state_id = $info['id'];
            $state_code = $info['code'];
            if ($selectid == $state_code) {
                $selected_text = "selected";
            } else {
                $selected_text = "";
            }
            $list .= '<option value="' . $state_code . '" ' . $selected_text . '>' . $name . '</option>';
        }

        echo $list;
    }
}

function getCityByStateID()
{
    global $config;
    $state_id = isset($_POST['id']) ? $_POST['id'] : 0;
    $selectid = isset($_POST['selectid']) ? $_POST['selectid'] : "";

    $rows = ORM::for_table($config['db']['pre'] . 'cities')
        ->select_many('id', 'name')
        ->where(array(
            'subadmin1_code' => $state_id,
            'active' => '1',
        ))
        ->find_many();

    if (count($rows) > 0) {

        $list = '<option value="">Select City</option>';
        foreach ($rows as $info) {
            $name = $info['name'];
            $id = $info['id'];
            if ($selectid == $id) {
                $selected_text = "selected";
            } else {
                $selected_text = "";
            }
            $list .= '<option value="' . $id . '" ' . $selected_text . '>' . $name . '</option>';
        }
        echo $list;
    }
}

function getCityidByCityName()
{
    global $config;
    $country_code = isset($_POST['country']) ? $_POST['country'] : "";
    $state = isset($_POST['state']) ? $_POST['state'] : "";
    $city_name = isset($_POST['city']) ? $_POST['city'] : "";

    $count = ORM::for_table($config['db']['pre'] . 'cities')
        ->select('id')
        ->where(array(
            'country_code' => $country_code,
            'active' => '1',
        ))
        ->where_raw('(`name` = ? OR `asciiname` = ?)', array($city_name, $city_name))
        ->find_one();

    if ($count) {

        $info = ORM::for_table($config['db']['pre'] . 'cities')
            ->select('id')
            ->where(array(
                'country_code' => $country_code,
                'active' => '1',
            ))
            ->where_raw('(`name` = ? OR `asciiname` = ?)', array($city_name, $city_name))
            ->find_one();
        if ($info['id']) {
            echo $id = $info['id'];
        }
    } else {
        echo "";
    }
    die();
}

function ModelGetStateByCountryID()
{
    global $config, $lang;
    $country_code = isset($_POST['id']) ? $_POST['id'] : 0;
    $countryName = get_countryName_by_id($country_code);

    $result = ORM::for_table($config['db']['pre'] . 'subadmin1')
        ->select_many('id', 'code', 'asciiname')
        ->where(array(
            'country_code' => $country_code,
            'active' => '1',
        ))
        ->order_by_desc('asciiname')
        ->find_many();

    $list = '<ul class="column col-md-12 col-sm-12 cities">';
    $count = 1;
    if (count($result) > 0) {
        foreach ($result as $row) {
            $name = $row['asciiname'];
            $id = $row['code'];

            if ($count == 1) {
                $list .= '<li class="selected"><a class="selectme" data-id="' . $country_code . '" data-name="' . $lang['ALL'] . ' ' . $countryName . '" data-type="country"><strong>' . $lang['ALL'] . ' ' . $countryName . '</strong></a></li>';
            }
            $list .= '<li class=""><a id="region' . $id . '" class="statedata" data-id="' . $id . '" data-name="' . $name . '"><span>' . $name . ' <i class="fa fa-angle-right"></i></span></a></li>';

            $count++;
        }
        echo $list . "</ul>";
    }
}

function ModelGetCityByStateID()
{
    global $config, $lang;
    $state_id = isset($_POST['id']) ? $_POST['id'] : '0';
    $stateName = get_stateName_by_id($state_id);
    //$state_code = substr($state_id,3);
    $country_code = substr($state_id, 0, 2);

    $result = ORM::for_table($config['db']['pre'] . 'cities')
        ->select_many('id', 'asciiname')
        ->where(array(
            'subadmin1_code' => $state_id,
            'country_code' => $country_code,
            'active' => '1',
        ))
        ->order_by_asc('asciiname')
        ->find_many();

    if ($result) {
        $total = count($result);
        $list = '<ul class="column col-md-12 col-sm-12 cities">';
        $count = 1;
        if ($total > 0) {
            foreach ($result as $row) {
                $name = $row['asciiname'];
                $id = $row['id'];
                if ($count == 1) {
                    $list .= '<li class="selected"><a id="changeState"><strong><i class="fa fa-arrow-left"></i>' . $lang['CHANGE_REGION'] . '</strong></a></li>';
                    $list .= '<li class="selected"><a class="selectme" data-id="' . $state_id . '" data-name="' . $stateName . ', ' . $lang['REGION'] . '" data-type="state"><strong>' . $lang['WHOLE'] . ' ' . $stateName . '</strong></a></li>';
                }

                $list .= '<li class=""><a id="region' . $id . '" class="selectme" data-id="' . $id . '" data-name="' . $name . ', ' . $lang['CITY'] . '" data-type="city"><span>' . $name . ' <i class="fa fa-angle-right"></i></span></a></li>';
                $count++;
            }

            echo $list . "</ul>";
        }

    } else {
        echo '<ul class="column col-md-12 col-sm-12 cities">
            <li class="selected"><a id="changeState"><strong><i class="fa fa-arrow-left"></i>' . $lang['CHANGE_REGION'] . '</strong></a></li>
            <li><a> ' . $lang['NO-CITY_AVAILABLE'] . '</a></li>
            </ul>';
    }

}

function searchCityFromCountry()
{
    global $config;
    $dataString = isset($_GET['q']) ? $_GET['q'] : "";
    $sortname = check_user_country();

    $perPage = 10;
    $page = isset($_GET['page']) ? $_GET['page'] : "1";
    $start = ($page - 1) * $perPage;
    if ($start < 0) {
        $start = 0;
    }

    $total = ORM::for_table($config['db']['pre'] . 'cities')
        ->where(array(
            'country_code' => 'sortname',
            'active' => '1',
        ))
        ->where_like('asciiname', '' . $dataString . '%')
        ->count();

    $sql = "SELECT c.id, c.asciiname, c.latitude, c.longitude, c.subadmin1_code, s.name AS statename
FROM `" . $config['db']['pre'] . "cities` AS c
INNER JOIN `" . $config['db']['pre'] . "subadmin1` AS s ON s.code = c.subadmin1_code and s.active = '1'
 WHERE (c.name like '%$dataString%' or c.asciiname like '%$dataString%') and c.country_code = '$sortname' and c.active = '1'
 ORDER BY
  CASE
    WHEN c.name = '$dataString' THEN 1
    WHEN c.name LIKE '$dataString%' THEN 2
    ELSE 3
  END ";
    $query = $sql . " limit " . $start . "," . $perPage;
    $pdo = ORM::get_db();
    $rows = $pdo->query($query);
    if (empty($_GET["rowcount"])) {
        $pdo = ORM::get_db();
        $result = $pdo->query($sql);
        $_GET["rowcount"] = $rowcount = $result->rowCount();
    }

    $pages = ceil($_GET["rowcount"] / $perPage);

    $items = '';
    $i = 0;
    $MyCity = array();

    foreach ($rows as $row) {
        $cityid = $row['id'];
        $cityname = $row['asciiname'];
        $latitude = $row['latitude'];
        $longitude = $row['longitude'];
        $statename = $row['statename'];

        $MyCity[$i]["id"] = $cityid;
        $MyCity[$i]["text"] = $cityname . ", " . $statename;
        $MyCity[$i]["latitude"] = $latitude;
        $MyCity[$i]["longitude"] = $longitude;
        $i++;
    }

    echo $json = '{"items" : ' . json_encode($MyCity, JSON_UNESCAPED_SLASHES) . ',"totalEntries" : ' . $total . '}';
    die();
}

function searchStateCountry()
{
    global $config, $lang;
    $dataString = isset($_POST['dataString']) ? $_POST['dataString'] : "";
    $sortname = check_user_country();
    $query = "SELECT c.id, c.asciiname, c.subadmin1_code, s.name AS statename
FROM `" . $config['db']['pre'] . "cities` AS c
INNER JOIN `" . $config['db']['pre'] . "subadmin1` AS s ON s.code = c.subadmin1_code and s.active = '1'
 WHERE (c.name like '%$dataString%' or c.asciiname like '%$dataString%') and c.country_code = '$sortname' and c.active = '1'
 ORDER BY
  CASE
    WHEN c.name = '$dataString' THEN 1
    WHEN c.name LIKE '$dataString%' THEN 2
    WHEN c.name LIKE '%$dataString' THEN 4
    ELSE 3
  END
 LIMIT 20";

    $pdo = ORM::get_db();
    $result = $pdo->query($query);
    $list = '<ul class="searchResgeo"><li><a href="#" class="title selectme" data-id="" data-name="" data-type="">' . $lang['ANY_CITY'] . '</span></a></li>';
    if ($result) {
        foreach ($result as $row) {
            $cityid = $row['id'];
            $cityname = $row['asciiname'];
            $stateid = $row['subadmin1_code'];
            $statename = $row['statename'];

            $list .= '<li><a href="#" class="title selectme" data-id="' . $cityid . '" data-name="' . $cityname . '" data-type="city">' . $cityname . ', <span class="color-9">' . $statename . '</span></a></li>';
        }
        $list .= '</ul>';
        echo $list;
    } else {
        echo '<ul class="searchResgeo"><li><span class="noresult">' . $lang['NO_RESULT_FOUND'] . '</span></li>';
    }
}

function searchCityStateCountry()
{
    global $config, $lang;
    $dataString = isset($_POST['dataString']) ? $_POST['dataString'] : "";
    $sortname = check_user_country();

    $query = "SELECT c.id, c.asciiname, c.subadmin1_code, s.name AS statename
FROM `" . $config['db']['pre'] . "cities` AS c
INNER JOIN `" . $config['db']['pre'] . "subadmin1` AS s ON s.code = c.subadmin1_code and s.active = '1'
 WHERE c.name like '%$dataString%' and c.country_code = '$sortname' and c.active = '1'
 ORDER BY
  CASE
    WHEN c.name = '$dataString' THEN 1
    WHEN c.name LIKE '$dataString%' THEN 2
    WHEN c.name LIKE '%$dataString' THEN 4
    ELSE 3
  END
 LIMIT 20";
    $pdo = ORM::get_db();
    $result = $pdo->query($query);
    $total = count($result);
    $list = '<ul class="searchResgeo">';
    if ($total > 0) {
        foreach ($result as $row) {
            $cityid = $row['id'];
            $cityname = $row['asciiname'];
            $stateid = $row['subadmin1_code'];
            $countryid = $sortname;
            $statename = $row['statename'];

            $list .= '<li><a href="#" class="title selectme" data-cityid="' . $cityid . '" data-stateid="' . $stateid . '"data-countryid="' . $countryid . '" data-name="' . $cityname . ', ' . $statename . '">' . $cityname . ', <span class="color-9">' . $statename . '</span></a></li>';
        }
        $list .= '</ul>';
        echo $list;
    } else {
        echo '<ul class="searchResgeo"><li><span class="noresult">' . $lang['NO_RESULT_FOUND'] . '</span></li>';
    }
}

function hideItem()
{
    global $config;
    $id = $_POST['id'];
    if (trim($id) != '') {
        $info = ORM::for_table($config['db']['pre'] . 'product')
            ->select('hide')
            ->find_one($id);
        $status = $info['hide'];
        $pdo = ORM::get_db();
        if ($status == "0") {
            $query = "UPDATE `" . $config['db']['pre'] . "product` set hide='1' WHERE `id` = '" . $id . "' and `user_id` = '" . $_SESSION['user']['id'] . "' ";
            $query_result = $pdo->query($query);
            echo 1;
        } else {
            $query = "UPDATE `" . $config['db']['pre'] . "product` set hide='0' WHERE `id` = '" . $id . "' and `user_id` = '" . $_SESSION['user']['id'] . "' ";
            $query_result = $pdo->query($query);
            echo 2;
        }
        die();
    } else {
        echo 0;
        die();
    }

}

function removeAdImg()
{
    global $config;
    $id = $_POST['id'];
    $img = $_POST['img'];

    $info = ORM::for_table($config['db']['pre'] . 'product')->select('screen_shot')->find_one($id);

    if (!empty($info)) {
        $screen = "";
        $uploaddir = "storage/products/";
        $screen_sm = explode(',', $info['screen_shot']);
        $count = 0;
        foreach ($screen_sm as $value) {
            $value = trim($value);

            if ($value == $img) {
                //Delete Image From Storage ----
                $filename1 = $uploaddir . $value;
                if (file_exists($filename1)) {
                    $filename1 = $uploaddir . $value;
                    $filename2 = $uploaddir . "small_" . $value;
                    unlink($filename1);
                    unlink($filename2);
                }
            } else {
                if ($count == 0) {
                    $screen .= $value;
                } else {
                    $screen .= "," . $value;
                }
                $count++;
            }
        }
        $product = ORM::for_table($config['db']['pre'] . 'product')->find_one($id);
        $product->screen_shot = $screen;
        $product->save();

        echo 1;
        die();
    } else {
        echo 0;
        die();
    }
}

function setFavAd()
{
    global $config;
    $num_rows = ORM::for_table($config['db']['pre'] . 'favads')
        ->where(array(
            'user_id' => $_POST['userId'],
            'product_id' => $_POST['id'],
        ))
        ->count();

    if ($num_rows == 0) {
        $insert_favads = ORM::for_table($config['db']['pre'] . 'favads')->create();
        $insert_favads->user_id = $_POST['userId'];
        $insert_favads->product_id = $_POST['id'];
        $insert_favads->save();

        if ($insert_favads->id()) {
            echo 1;
        } else {
            echo 0;
        }

    } else {
        $result = ORM::for_table($config['db']['pre'] . 'favads')
            ->where(array(
                'user_id' => $_POST['userId'],
                'product_id' => $_POST['id'],
            ))
            ->delete_many();
        if ($result) {
            echo 2;
        } else {
            echo 0;
        }

    }
    die();
}

function removeFavAd()
{
    global $config;
    $result = ORM::for_table($config['db']['pre'] . 'favads')
        ->where(array(
            'user_id' => $_POST['userId'],
            'product_id' => $_POST['id'],
        ))
        ->delete_many();

    if ($result) {
        echo 1;
    } else {
        echo 0;
    }

    die();
}

function deleteMyAd()
{
    global $config;
    if (isset($_POST['id'])) {
        $row = ORM::for_table($config['db']['pre'] . 'product')
            ->select('screen_shot')
            ->where(array(
                'id' => $_POST['id'],
                'user_id' => $_SESSION['user']['id'],
            ))
            ->find_one();

        if (!empty($row)) {
            $uploaddir = "storage/products/";
            $screen_sm = explode(',', $row['screen_shot']);
            foreach ($screen_sm as $value) {
                $value = trim($value);
                //Delete Image From Storage ----
                $filename1 = $uploaddir . $value;
                if (file_exists($filename1)) {
                    $filename1 = $uploaddir . $value;
                    $filename2 = $uploaddir . "small_" . $value;
                    unlink($filename1);
                    unlink($filename2);
                }
            }

            ORM::for_table($config['db']['pre'] . 'product')
                ->where(array(
                    'id' => $_POST['id'],
                    'user_id' => $_SESSION['user']['id'],
                ))
                ->delete_many();
        }

        echo 1;
        die();
    } else {
        echo 0;
        die();
    }

}

function deleteResumitAd()
{
    global $config;
    if (isset($_POST['id'])) {

        $info1 = ORM::for_table($config['db']['pre'] . 'product_resubmit')
            ->select_many('product_id', 'screen_shot')
            ->where(array(
                'id' => $_POST['id'],
                'user_id' => $_SESSION['user']['id'],
            ))
            ->find_one();

        if (!empty($info1)) {

            $info = ORM::for_table($config['db']['pre'] . 'product')
                ->select('screen_shot')
                ->where(array(
                    'id' => $info1['product_id'],
                    'user_id' => $_SESSION['user']['id'],
                ))
                ->find_one();

            $uploaddir = "storage/products/";
            $screen_sm = explode(',', $info['screen_shot']);
            $re_screen = explode(',', $info1['screen_shot']);

            $arr = array_diff($re_screen, $screen_sm);

            foreach ($arr as $value) {
                $value = trim($value);

                //Delete Image From Storage ----
                $filename1 = $uploaddir . $value;
                if (file_exists($filename1)) {
                    $filename1 = $uploaddir . $value;
                    $filename2 = $uploaddir . "small_" . $value;
                    unlink($filename1);
                    unlink($filename2);
                }
            }

            ORM::for_table($config['db']['pre'] . 'product_resubmit')
                ->where(array(
                    'id' => $_POST['id'],
                    'user_id' => $_SESSION['user']['id'],
                ))
                ->delete_many();
        }

        echo 1;
        die();
    } else {
        echo 0;
        die();
    }

}

function getsubcatbyid()
{
    global $config;
    $id = isset($_POST['catid']) ? $_POST['catid'] : 0;
    $selectid = isset($_POST['selectid']) ? $_POST['selectid'] : "";

    $rows = ORM::for_table($config['db']['pre'] . 'catagory_sub')
        ->where('main_cat_id', $id)
        ->find_many();

    if (count($rows) > 0) {

        foreach ($rows as $info) {
            $name = $info['sub_cat_name'];
            $sub_id = $info['sub_cat_id'];
            $photo_show = $info['photo_show'];
            $price_show = $info['price_show'];
            if ($selectid == $sub_id) {
                $selected_text = "selected";
            } else {
                $selected_text = "";
            }
            echo '<option value="' . $sub_id . '" data-photo-show="' . $photo_show . '" data-price-show="' . $price_show . '" ' . $selected_text . '>' . $name . '</option>';
        }
    } else {
        echo 0;
    }
    die();
}

function getsubcatbyidList()
{
    global $config;
    $id = isset($_POST['catid']) ? $_POST['catid'] : 0;
    $selectid = isset($_POST['selectid']) ? $_POST['selectid'] : "";

    $rows = ORM::for_table($config['db']['pre'] . 'catagory_sub')
        ->where('main_cat_id', $id)
        ->order_by_asc('cat_order')
        ->find_many();

    if (count($rows) > 0) {

        foreach ($rows as $info) {

            $name = $info['sub_cat_name'];
            $sub_id = $info['sub_cat_id'];
            $photo_show = $info['photo_show'];
            $price_show = $info['price_show'];
            if ($selectid == $sub_id) {
                $selected_text = "link-active";
            } else {
                $selected_text = "";
            }

            if ($config['lang_code'] != 'en' && $config['userlangsel'] == '1') {
                $subcat = get_category_translation("sub", $info['sub_cat_id']);
                if (isset($subcat['title']) && $subcat['title'] != "") {
                    $info['sub_cat_name'] = $subcat['title'];
                }
            }
            $name = $info['sub_cat_name'];
            echo '<li data-ajax-subcatid="' . $sub_id . '" data-photo-show="' . $photo_show . '" data-price-show="' . $price_show . '" class="' . $selected_text . '"><a href="#">' . $name . '</a></li>';
        }

    } else {
        echo 0;
    }
    die();
}

function getCustomFieldByCatID()
{
    global $config, $lang;
    $maincatid = isset($_POST['catid']) ? $_POST['catid'] : 0;
    $subcatid = isset($_POST['subcatid']) ? $_POST['subcatid'] : 0;

    if ($maincatid > 0) {
        $custom_fields = get_customFields_by_catid($maincatid, $subcatid);
        $showCustomField = (count($custom_fields) > 0) ? 1 : 0;
    } else {
        die();
    }
    $tpl = '';
    if ($showCustomField) {
        foreach ($custom_fields as $row) {
            $id = $row['id'];
            $name = $row['title'];
            $type = $row['type'];
            $required = $row['required'];

            if ($type == "text-field") {
                $tpl .= '<div class="row form-group">
                            <label class="col-sm-3 label-title">' . $name . ' ' . ($required === "1" ? '<span class="required">*</span>' : "") . '</label>
                            <div class="col-sm-9">
                                ' . $row['textbox'] . '
                            </div>
                        </div>';
            } elseif ($type == "textarea") {
                $tpl .= '<div class="row form-group">
                                <label class="col-sm-3 label-title">' . $name . ' ' . ($required === "1" ? '<span class="required">*</span>' : "") . '</label>
                                <div class="col-sm-9">
                                    ' . $row['textarea'] . '
                                </div>
                            </div>';
            } elseif ($type == "radio-buttons") {
                $tpl .= '<div class="row form-group">
                                <label class="col-sm-3 label-title">' . $name . ' ' . ($required === "1" ? '<span class="required">*</span>' : "") . '</label>
                                <div class="col-sm-9">' . $row['radio'] . '</div>
                            </div>';
            } elseif ($type == "checkboxes") {
                $tpl .= '<div class="row form-group">
                                <label class="col-sm-3 label-title">' . $name . ' ' . ($required === "1" ? '<span class="required">*</span>' : "") . '</label>
                                <div class="col-sm-9">' . $row['checkbox'] . '</div>
                            </div>';
            } elseif ($type == "drop-down") {
                $tpl .= '<div class="row form-group">
                                <label class="col-sm-3 label-title">' . $name . ' ' . ($required === "1" ? '<span class="required">*</span>' : "") . '</label>
                                <div class="col-sm-9">
                                    <select class="form-control selectpicker with-border quick-select" name="custom[' . $id . ']" data-name="' . $id . '"
                                                    data-req="' . $required . '">
                                        <option value="" selected>' . $lang['SELECT'] . ' ' . $name . '</option>
                                        ' . $row['selectbox'] . '
                                    </select>
                                    <div class="quick-error">' . $lang['FIELD_REQUIRED'] . '</div>
                                </div>
                            </div>';
            }
        }
        echo $tpl;
        die();
    } else {
        echo 0;
        die();
    }
}

function getlocHomemap()
{
    global $config;
    $appr = 'active';
    $country = check_user_country();

    if (isset($_GET['serachStr'])) {
        $serachStr = $_GET['serachStr'];
    } else {
        $serachStr = '';
    }

    if (isset($_GET['state'])) {
        $state = $_GET['state'];
    } else {
        $state = '';
    }
    if (!empty($_GET['city'])) {
        $city = $_GET['city'];
    } else {
        if (!empty($_GET['locality'])) {
            $city = $_GET['locality'];
        } else {
            $city = '';
        }
    }
    if (isset($_GET['searchBox'])) {
        $searchBox = $_GET['searchBox'];
    } else {
        $searchBox = '';
    }

    if (isset($_GET['catid'])) {
        $catid = $_GET['catid'];
    } else {
        $catid = '';
    }

    $where = " `status` = '" . validate_input($appr) . "' ";

    if ($city != '') {

        if ($serachStr != '') {
            $where .= " and product_name LIKE '%" . validate_input($serachStr) . "%'";
        }

        if ($searchBox != '') {
            $where .= " and category = '" . validate_input($searchBox) . "'";
        }

        if ($catid != '') {
            $where .= " and sub_category = '" . validate_input($catid) . "'";
        }

        if ($country != '') {
            $where .= " and country = '" . validate_input($country) . "'";
        }

        /*$query = "SELECT p.*,c.id AS cityid
    FROM `".$config['db']['pre']."cities` AS c
    INNER JOIN `".$config['db']['pre']."product` AS p ON p.city = c.id Where (c.name like '%$city%' or c.asciiname like '%$city%') AND p.status = 'active' $where";*/

    } else {

        if ($serachStr != '') {
            $where .= " and product_name LIKE '%" . validate_input($serachStr) . "%'";
        }

        if ($searchBox != '') {
            $where .= " and category = '" . validate_input($searchBox) . "'";
        }

        if ($catid != '') {
            $where .= " and sub_category = '" . validate_input($catid) . "'";
        }

        if ($country != '') {
            $where .= " and country = '" . validate_input($country) . "'";
        }

    }

    $results = ORM::for_table($config['db']['pre'] . 'product')
        ->where('status', $appr)
        ->where_raw($where)
        ->find_many();

    $data = array();
    $i = 0;
    if (count($results) > 0) {

        foreach ($results as $result) {
            $id = $result['id'];
            $featured = $result['featured'];
            $urgent = $result['urgent'];
            $highlight = $result['highlight'];
            $title = $result['product_name'];
            $cat = $result['category'];
            $price = $result['price'];
            $pics = $result['screen_shot'];
            $location = $result['location'];
            $latlong = $result['latlong'];
            $desc = $result['description'];
            $url = $config['site_url'] . $id;

            $fetch = ORM::for_table($config['db']['pre'] . 'catagory_main')
                ->where('cat_id', $cat)
                ->find_one();

            $catIcon = $fetch['icon'];
            $catname = $fetch['cat_name'];

            $map = explode(',', $latlong);
            $lat = $map[0];
            $long = $map[1];

            $p = explode(',', $pics);
            $pic = $p[0];
            $pic = $config['site_url'] . 'storage/products/' . $pic;

            $data[$i]['id'] = $id;
            $data[$i]['latitude'] = $lat;
            $data[$i]['longitude'] = $long;
            $data[$i]['featured'] = $featured;
            $data[$i]['title'] = $title;
            $data[$i]['location'] = $location;
            $data[$i]['category'] = $catname;
            $data[$i]['cat_icon'] = $catIcon;
            $data[$i]['marker_image'] = $pic;
            $data[$i]['url'] = $url;
            $data[$i]['description'] = strip_tags(htmlentities($desc));

            $i++;
        }
        echo json_encode($data);
    } else {
        echo '0';
    }
    die();
}

function openlocatoionPopup()
{
    global $config, $link;
    $result = ORM::for_table($config['db']['pre'] . 'product')->find_one($_POST['id']);

    $data = array();
    $i = 0;
    if (!empty($result)) {
        $id = $result['id'];
        $featured = $result['featured'];
        $urgent = $result['urgent'];
        $highlight = $result['highlight'];
        $title = $result['product_name'];
        $cat = $result['category'];
        $price = $result['price'];
        $pics = $result['screen_shot'];
        $location = $result['location'];
        $city_id = $result['city'];
        $cityname = get_cityName_by_id($result['city']);
        $country = get_countryName_by_id($result['country']);

        $location = $cityname . ", " . $country;

        $latlong = $result['latlong'];
        $desc = strip_tags(htmlentities($result['description']));
        $url = $link['POST-DETAIL'] . '/' . $id;

        $fetch = ORM::for_table($config['db']['pre'] . 'catagory_main')
            ->where('cat_id', $cat)
            ->find_one();
        $catIcon = $fetch['icon'];
        $catname = $fetch['cat_name'];

        $map = explode(',', $latlong);
        $lat = $map[0];
        $long = $map[1];

        $picture = explode(',', $pics);
        $pic_count = count($picture);
        if ($picture[0] != "") {
            $pic = $picture[0];
            $pic = $config['site_url'] . 'storage/products/thumb/' . $pic;
            $pic = '<img class="activator" src="' . $pic . '">';
        } else {
            $pic = "";
        }

        /*echo '<div class="item gmapAdBox" data-id="' . $id . '" style="margin-bottom: 0px;">
        <a href="' . $url . '" style="display: block;position: relative;">
        <div class="card small">
        <div class="card-image waves-effect waves-block waves-light">
        ' . $pic . '
        </div>
        <div class="card-content">
        <div class="label label-default">' . $catname . '</div>
        <span class="card-title activator grey-text text-darken-4 mapgmapAdBoxTitle">' . $title . '</span>
        <p class="mapgmapAdBoxLocation">' . $location . '</p>
        </div>
        </div>

        </a>
        </div>';*/
        echo '<div class="infoBox item gmapAdBox" data-id="' . $id . '" style="margin-bottom: 0px;"><div class="map-box"><a href="' . $url . '" class="job-listing"><div class="infoBox-close"><i class="icon-feather-x"></i></div><div class="job-listing-details"><div class="job-listing-company-logo"><div class="not-verified-badge"></div>' . $pic . '</div><div class="job-listing-description"><h4 class="job-listing-company">' . $catname . '</h4><h3 class="job-listing-title">' . $title . '</h3></div></div></a></div></div>';
    } else {
        echo false;
    }
    die();
}

function quickad_ajax_home_search()
{
    global $config, $lang, $link, $cats;
    $pdo = ORM::get_db();
    $searchmode = "titlematch";
    $qString = '';
    $qString = $_POST['tagID'];
    $qString = strtolower($qString);
    $output = array();
    $TAGOutput = array();
    $CATOutput = array();
    $TagCatOutput = array();
    $TitleOutput = array();
    $lpsearchMode = "titlematch";
    $catIcon_type = "icon";

    if (isset($searchmode)) {
        if (!empty($searchmode) && $searchmode == "keyword") {
            $lpsearchMode = "keyword";
        }
    }

    if (empty($qString)) {

        $categories = get_maincategory();
        $catIcon = '';
        foreach ($categories as $cat) {
            $catIcon = $cat['icon'];
            $catPicture = $cat['picture'];
            if (!empty($catIcon) or !empty($catPicture)) {
                if ($catPicture != "") {
                    $catIcon = '<img src="' . $cat['picture'] . '" />';
                } else {
                    $catIcon = '<i class="' . $cat['icon'] . '" ></i>';
                }

            }
            $cats[$cat['id']] = '<li class="lp-default-cats" data-catid="' . $cat['id'] . '">' . $catIcon . '<span class="qucikad-as-cat">' . $cat['name'] . '</span></li>';
        }
        $output = array(
            'tag' => '',
            'cats' => $cats,
            'tagsncats' => '',
            'titles' => '',
            'more' => '',
        );
        $query_suggestion = json_encode(array(
            "tagID" => $qString,
            "suggestions" => $output,
        ));
        die($query_suggestion);
    } else {
        //$catTerms = get_maincategory();

        if ($lpsearchMode == "keyword") {

            $sql = "SELECT DISTINCT *
FROM `" . $config['db']['pre'] . "catagory_main`
 WHERE cat_name like '%$qString%'
 ORDER BY
  CASE
    WHEN cat_name = '$qString' THEN 1
    WHEN cat_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";
        } else {

            $sql = "SELECT DISTINCT *
FROM `" . $config['db']['pre'] . "catagory_main`
 WHERE cat_name like '$qString%'
 ORDER BY
  CASE
    WHEN cat_name = '$qString' THEN 1
    WHEN cat_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";

        }

        $rows = $pdo->query($sql);
        foreach ($rows as $info) {
            $catTerms[$info['cat_id']]['id'] = $info['cat_id'];
            $catTerms[$info['cat_id']]['icon'] = $info['icon'];
            $catTerms[$info['cat_id']]['picture'] = $info['picture'];
            if ($config['lang_code'] != 'en' && $config['userlangsel'] == '1') {
                $maincat = get_category_translation("main", $info['cat_id']);
                if (isset($maincat['title']) && $maincat['title'] != "") {
                    $info['cat_name'] = $maincat['title'];
                    $info['slug'] = $maincat['slug'];
                }
            }
        }
        $catTerms[$info['cat_id']]['name'] = $info['cat_name'];
        $catTerms[$info['cat_id']]['slug'] = $info['slug'];

        if ($lpsearchMode == "keyword") {

            $sql = "SELECT DISTINCT *
FROM `" . $config['db']['pre'] . "catagory_sub`
 WHERE sub_cat_name like '%$qString%'
 ORDER BY
  CASE
    WHEN sub_cat_name = '$qString' THEN 1
    WHEN sub_cat_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";
        } else {

            $sql = "SELECT DISTINCT *
FROM `" . $config['db']['pre'] . "catagory_sub`
 WHERE sub_cat_name like '$qString%'
 ORDER BY
  CASE
    WHEN sub_cat_name = '$qString' THEN 1
    WHEN sub_cat_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";

        }
        $rows = $pdo->query($sql);
        foreach ($rows as $info) {
            $subcatTerms[$info['sub_cat_id']]['id'] = $info['sub_cat_id'];

            if ($config['lang_code'] != 'en' && $config['userlangsel'] == '1') {
                $subcategory = get_category_translation("sub", $info['sub_cat_id']);
                if (isset($subcategory['title']) && $subcategory['title'] != "") {
                    $info['sub_cat_name'] = $subcategory['title'];
                    $info['slug'] = $subcategory['slug'];
                }
            }
            $subcatTerms[$info['sub_cat_id']]['name'] = $info['sub_cat_name'];
            $subcatTerms[$info['sub_cat_id']]['slug'] = $info['slug'];
            $get_main = get_maincat_by_id($info['main_cat_id']);
            $subcatTerms[$info['sub_cat_id']]['main_cat_name'] = $get_main['cat_name'];
            $subcatTerms[$info['sub_cat_id']]['main_cat_icon'] = $get_main['icon'];
            $subcatTerms[$info['sub_cat_id']]['main_cat_pic'] = $get_main['picture'];
            $subcatTerms[$info['sub_cat_id']]['main_cat_id'] = $info['main_cat_id'];
        }
        //$subcatTerms = get_subcategories();

        $catName = '';
        $catIcon = '';
        if (!empty($catTerms) && !empty($subcatTerms)) {
            foreach ($catTerms as $cat) {
                $catIcon = $cat['icon'];
                $catPicture = $cat['picture'];
                if (!empty($catIcon) or !empty($catPicture)) {
                    if ($catPicture != "") {
                        $catIcon = '<img src="' . $cat['picture'] . '" />';
                    } else {
                        $catIcon = '<i class="' . $cat['icon'] . '" ></i>';
                    }

                }

                $catTermMatch = false;

                $catTernName = $cat['name'];
                $catTernName = strtolower($catTernName);
                if ($lpsearchMode == "keyword") {
                    preg_match("/[$qString]/", "$catTernName", $lpMatches, PREG_OFFSET_CAPTURE);
                    $lpresCnt = count($lpMatches);
                    if ($lpresCnt > 0) {
                        $catTermMatch = true;
                    }

                } else {
                    $catTermMatch = strpos($catTernName, $qString);
                }

                if ($catTermMatch !== false) {
                    $CATOutput[$cat['id']] = '<li class="qucikad-ajaxsearch-li-cats" data-catid="' . $cat['id'] . '">' . $catIcon . '<span class="qucikad-as-cat">' . $cat['name'] . '</span></li>';
                }
            }
            foreach ($subcatTerms as $subcat) {

                $tagTermMatch = false;
                $tagTernName = strtolower($subcat['name']);

                if ($lpsearchMode == "keyword") {
                    preg_match("/[$qString]/", "$tagTernName", $lpMatches, PREG_OFFSET_CAPTURE);
                    $lpresCnt = count($lpMatches);
                    if ($lpresCnt > 0) {
                        $tagTermMatch = true;
                    }
                } else {
                    $tagTermMatch = strpos($tagTernName, $qString);
                }

                if ($tagTermMatch !== false) {
                    $TAGOutput[$subcat['id']] = '<li class="qucikad-ajaxsearch-li-tags" data-tagid="' . $subcat['id'] . '"><span class="qucikad-as-tag">' . $subcat['name'] . '</span></li>';
                }
            }

        } else {

            if (!empty($catTerms)) {
                foreach ($catTerms as $cat) {

                    $catIcon = $cat['icon'];
                    $catPicture = $cat['picture'];
                    if (!empty($catIcon) or !empty($catPicture)) {
                        if ($catPicture != "") {
                            $catIcon = '<img src="' . $cat['picture'] . '" />';
                        } else {
                            $catIcon = '<i class="' . $cat['icon'] . '" ></i>';
                        }

                    }

                    $catTermMatch = false;

                    $catTernName = $cat['name'];
                    $catTernName = strtolower($catTernName);
                    if ($lpsearchMode == "keyword") {
                        preg_match("/[$qString]/", "$catTernName", $lpMatches, PREG_OFFSET_CAPTURE);
                        $lpresCnt = count($lpMatches);
                        if ($lpresCnt > 0) {
                            $catTermMatch = true;
                        }

                    } else {
                        $catTermMatch = strpos($catTernName, $qString);
                    }

                    if ($catTermMatch !== false) {
                        $CATOutput[$cat['id']] = '<li class="qucikad-ajaxsearch-li-cats" data-catid="' . $cat['id'] . '">' . $catIcon . '<span class="qucikad-as-cat">' . $cat['name'] . '</span></li>';
                    }
                }
            }

            if (!empty($subcatTerms)) {

                foreach ($subcatTerms as $subcat) {

                    $catIcon = $subcat['main_cat_icon'];
                    $catPicture = $subcat['main_cat_pic'];
                    if (!empty($catIcon) or !empty($catPicture)) {
                        if ($catPicture != "") {
                            $catIcon = '<img src="' . $catPicture . '" />';
                        } else {
                            $catIcon = '<i class="' . $catIcon . '" ></i>';
                        }

                    }

                    $tagTermMatch = false;
                    $tagTernName = strtolower($subcat['name']);

                    if ($lpsearchMode == "keyword") {
                        preg_match("/[$qString]/", "$tagTernName", $lpMatches, PREG_OFFSET_CAPTURE);
                        $lpresCnt = count($lpMatches);
                        if ($lpresCnt > 0) {
                            $tagTermMatch = true;
                        }
                    } else {
                        $tagTermMatch = strpos($tagTernName, $qString);
                    }

                    if ($tagTermMatch !== false) {
                        //$TAGOutput[$subcat['id']]    = '<li class="qucikad-ajaxsearch-li-tags" data-tagid="' . $subcat['id'] . '"><span class="qucikad-as-tag">' . $subcat['name'] . '</span></li>';

                        $TagCatOutput[] = '<li class="cats-n-tags" data-tagid="' . $subcat['id'] . '" data-catid="' . $subcat['main_cat_id'] . '">' . $catIcon . '<span class="qucikad-as-tag">' . $subcat['name'] . '</span><span> in </span><span class="qucikad-as-cat">' . $subcat['main_cat_name'] . '</span></li>';
                    }
                }

            }
        }

        $machTitles = false;
        $country_code = check_user_country();

        if ($lpsearchMode == "keyword") {

            $sql = "SELECT DISTINCT p.*,u.group_id
FROM `" . $config['db']['pre'] . "product` as p
LEFT JOIN `" . $config['db']['pre'] . "user` as u ON u.id = p.user_id
 WHERE p.product_name like '%$qString%' and p.status = 'active' and p.hide = '0' and p.country = '" . $country_code . "'
 ORDER BY
  CASE
    WHEN p.product_name = '$qString' THEN 1
    WHEN p.product_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";
        } else {

            $sql = "SELECT DISTINCT p.*,u.group_id
FROM `" . $config['db']['pre'] . "product` as p
INNER JOIN `" . $config['db']['pre'] . "user` as u ON u.id = p.user_id
 WHERE p.product_name like '$qString%' and p.status = 'active' and p.hide = '0' and p.country = '" . $country_code . "'
 ORDER BY
  CASE
    WHEN p.product_name = '$qString' THEN 1
    WHEN p.product_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";

        }

        $result = $pdo->query($sql);
        $num_rows = count($result);
        if ($num_rows > 0) {
            $machTitles = true; // output data of each row
            foreach ($result as $info) {

                // Get usergroup details
                $group_id = isset($info['group_id']) ? $info['group_id'] : 0;
                $show_in_home_search = '';
                // Get membership details
                switch ($group_id) {
                    case 'free':
                        $plan = json_decode(get_option('free_membership_plan'), true);
                        $settings = $plan['settings'];
                        $show_in_home_search = $settings['show_in_home_search'];
                        break;
                    case 'trial':
                        $plan = json_decode(get_option('trial_membership_plan'), true);
                        $settings = $plan['settings'];
                        $show_in_home_search = $settings['show_in_home_search'];
                        break;
                    default:
                        $plan = ORM::for_table($config['db']['pre'] . 'plans')
                            ->select('settings')
                            ->where('id', $group_id)
                            ->find_one();
                        if (!isset($plan['settings'])) {
                            $plan = json_decode(get_option('free_membership_plan'), true);
                            $settings = $plan['settings'];
                            $show_in_home_search = $settings['show_in_home_search'];
                        } else {
                            $settings = json_decode($plan['settings'], true);
                            $show_in_home_search = $settings['show_in_home_search'];
                        }
                        break;
                }

                if ($show_in_home_search == 'yes') {
                    //will run loop
                } else {
                    continue;
                }

                $listTitle = $info['product_name'];
                $listTitle = strtolower($listTitle);
                $pro_url = create_slug($info['product_name']);
                $permalink = $link['POST-DETAIL'] . '/' . $info['id'] . '/' . $pro_url;
                $cityname = get_cityName_by_id($info['city']);

                if (check_user_upgrades($info['user_id'])) {
                    $sub_info = get_user_membership_detail($info['user_id']);
                    $sub_title = $sub_info['name'];
                    $sub_image = $sub_info['badge'];
                    $premium_badge = "<img src='" . $sub_image . "' alt='" . $sub_title . "' width='20px'/>";
                } else {
                    $sub_title = '';
                    $sub_image = '';
                    $premium_badge = '';
                }

                $listThumb = '';
                $picture = explode(',', $info['screen_shot']);
                if (!empty($picture[0])) {
                    if (file_exists("../storage/products/thumb/" . $picture[0])) {
                        $image = $config['site_url'] . "storage/products/thumb/" . $picture[0];
                    } else {
                        $image = $config['site_url'] . "storage/products/thumb/default.png";
                    }
                    $listThumb = "<img src='" . $image . "' width='50' height='50'/>";
                } else {
                    $listThumb = '<img src="' . $config['site_url'] . 'storage/products/thumb/default.png" alt="" width="50" height="50">';
                }

                $TitleOutput[] = '<li class="qucikad-ajaxsearch-li-title" data-url="' . $permalink . '">' . $listThumb . '<span class="qucikad-as-title"><a href="' . $permalink . '">' . $listTitle . ' ' .
                    $premium_badge . ' <span class="lp-loc">' . $cityname . '</span></a></span></li>';

            }
        }

        $TAGOutput = array_unique($TAGOutput);
        $CATOutput = array_unique($CATOutput);
        $TagCatOutput = array_unique($TagCatOutput);
        $TitleOutput = array_unique($TitleOutput);
        if ((!empty($TAGOutput) && count($TAGOutput) > 0) || (!empty($CATOutput) && count($CATOutput) > 0) || (!empty($TagCatOutput) && count($TagCatOutput) > 0) || (!empty($TitleOutput) && count($TitleOutput) > 0)) {
            $output = array(
                'tag' => $TAGOutput,
                'cats' => $CATOutput,
                'tagsncats' => $TagCatOutput,
                'titles' => $TitleOutput,
                'more' => '',
                'matches' => $machTitles,
            );
        } else {
            $moreResult = array();
            $mResults = '<strong>' . $lang['MORE_RESULTS_FOR'] . '</strong>';
            $mResults .= $qString;
            $moreResult[] = '<li class="qucikad-ajaxsearch-li-more-results" data-moreval="' . $qString . '">' . $mResults . '</li>';
            $output = array(
                'tag' => '',
                'cats' => '',
                'tagsncats' => '',
                'titles' => '',
                'more' => $moreResult,
            );
        }
        $query_suggestion = json_encode(array(
            "tagID" => $qString,
            "suggestions" => $output,
        ));
        die($query_suggestion);
    }
}

function submitBlogComment()
{
    global $config, $lang;
    $comment_error = $name = $email = $user_id = $comment = null;
    $result = array();
    $is_admin = '0';
    $is_login = false;
    if (checkloggedin()) {
        $is_login = true;
    }
    $avatar = $config['site_url'] . 'storage/profile/default_user.png';
    if (!($is_login || isset($_SESSION['admin']['id']))) {
        if (empty($_POST['user_name']) || empty($_POST['user_email'])) {
            $comment_error = $lang['ALL_FIELDS_REQ'];
        } else {
            $name = removeEmailAndPhoneFromString($_POST['user_name']);
            $email = $_POST['user_email'];

            $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
            if (!preg_match($regex, $email)) {
                $comment_error = $lang['EMAILINV'];
            }
        }
    } else if ($is_login && isset($_SESSION['admin']['id'])) {
        $commenting_as = 'admin';
        if (!empty($_POST['commenting-as'])) {
            if (in_array($_POST['commenting-as'], array('admin', 'user'))) {
                $commenting_as = $_POST['commenting-as'];
            }
        }
        if ($commenting_as == 'admin') {
            $is_admin = '1';
            $info = ORM::for_table($config['db']['pre'] . 'admins')->find_one($_SESSION['admin']['id']);
            $user_id = $_SESSION['admin']['id'];
            $name = $info['name'];
            $email = $info['email'];
            if (!empty($info['image'])) {
                $avatar = $config['site_url'] . 'storage/profile/' . $info['image'];
            }
        } else {
            $user_id = $_SESSION['user']['id'];
            $user_data = get_user_data(null, $user_id);
            $name = $user_data['name'];
            $email = $user_data['email'];
            if (!empty($user_data['image'])) {
                $avatar = $config['site_url'] . 'storage/profile/' . $user_data['image'];
            }
        }
    } else if ($is_login) {
        $user_id = $_SESSION['user']['id'];
        $user_data = get_user_data(null, $user_id);
        $name = $user_data['name'];
        $email = $user_data['email'];
        if (!empty($user_data['image'])) {
            $avatar = $config['site_url'] . 'storage/profile/' . $user_data['image'];
        }
    } else if (isset($_SESSION['admin']['id'])) {
        $is_admin = '1';
        $info = ORM::for_table($config['db']['pre'] . 'admins')->find_one($_SESSION['admin']['id']);
        $user_id = $_SESSION['admin']['id'];
        $name = $info['name'];
        $email = $info['email'];
        if (!empty($info['image'])) {
            $avatar = $config['site_url'] . 'storage/profile/' . $info['image'];
        }
    } else {
        $comment_error = $lang['LOGIN_POST_COMMENT'];
    }

    if (empty($_POST['comment'])) {
        $comment_error = $lang['ALL_FIELDS_REQ'];
    } else {
        $comment = validate_input($_POST['comment']);
    }

    $duplicates = ORM::for_table($config['db']['pre'] . 'blog_comment')
        ->where('blog_id', $_POST['comment_post_ID'])
        ->where('name', $name)
        ->where('email', $email)
        ->where('comment', $comment)
        ->count();

    if ($duplicates > 0) {
        $comment_error = $lang['DUPLICATE_COMMENT'];
    }

    if (!$comment_error) {
        if ($is_admin) {
            $approve = '1';
        } else {
            if ($config['blog_comment_approval'] == 1) {
                $approve = '0';
            } else if ($config['blog_comment_approval'] == 2) {
                if ($is_login) {
                    $approve = '1';
                } else {
                    $approve = '0';
                }
            } else {
                $approve = '1';
            }
        }

        $blog_cmnt = ORM::for_table($config['db']['pre'] . 'blog_comment')->create();
        $blog_cmnt->blog_id = $_POST['comment_post_ID'];
        $blog_cmnt->user_id = $user_id;
        $blog_cmnt->is_admin = $is_admin;
        $blog_cmnt->name = $name;
        $blog_cmnt->email = $email;
        $blog_cmnt->comment = $comment;
        $blog_cmnt->created_at = date('Y-m-d H:i:s');
        $blog_cmnt->active = $approve;
        $blog_cmnt->parent = $_POST['comment_parent'];
        $blog_cmnt->save();

        $id = $blog_cmnt->id();
        $date = date('d, M Y');
        $approve_txt = '';
        if ($approve == '0') {
            $approve_txt = '<em><small>' . $lang['COMMENT_REVIEW'] . '</small></em>';
        }

        $html = '<li id="li-comment-' . $id . '"';
        if ($_POST['comment_parent'] != 0) {
            $html .= 'class="children-2"';
        }
        $html .= '>
                   <div class="comments-box" id="comment-' . $id . '">
                        <div class="comments-avatar">
                            <img src="' . $avatar . '" alt="' . $name . '">
                        </div>
                        <div class="comments-text">
                            <div class="avatar-name">
                                <h5>' . $name . '</h5>
                                <span>' . $date . '</span>
                            </div>
                            ' . $approve_txt . '
                            <p>' . nl2br(stripcslashes($comment)) . '</p>
                        </div>
                    </div>
                </li>';

        $result['success'] = true;
        $result['html'] = $html;
        $result['id'] = $id;
    } else {
        $result['success'] = false;
        $result['error'] = $comment_error;
    }
    die(json_encode($result));
}
