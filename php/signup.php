<?php

if (isset($_GET['confirm'])) {
    $check_confirm = 0;

    $check_confirm = ORM::for_table($config['db']['pre'] . 'user')
        ->where(array(
            'id' => $_GET['user'],
            'confirm' => $_GET['confirm'],
        ))
        ->count();

    if ($check_confirm) {
        $pdo = ORM::get_db();
        $query = "UPDATE `" . $config['db']['pre'] . "user` SET `status` = '1', `confirm` = '' WHERE id='" . mysqli_real_escape_string($mysqli, $_GET['user']) . "' AND confirm='" . mysqli_real_escape_string($mysqli, $_GET['confirm']) . "' LIMIT 1 ";

        $pdo->query($query);

        $user_info = get_user_data(null, $_GET['user']);
        $user_email = $user_info['email'];

        message($lang['SUCCESS'], $lang['THANKSIGNUP'], 'login');
    } else {
        message($lang['ERROR'], $lang['CONFUSED'], '', false);
    }

    exit;
}

if (checkloggedin()) {
    header("Location: " . $config['site_url'] . "dashboard");
    exit;
}
// Check if this is an Name availability check from signup page using ajax

if (isset($_POST["submit"])) {
    $errors = 0;
    $name_error = '';
    $username_error = '';
    $idprooftype_error = '';
    $idproofnumber_error = '';
    $idproof_error = '';
    $addressprooftype_error = '';
    $addressproofnumber_error = '';
    $addressproof_error = '';
    $email_error = '';
    $phone_error = '';
    $password_error = '';
    $recaptcha_error = '';
    $id_proof_new_file_name = '';
    $address_proof_new_file_name = '';
    $name_length = strlen(utf8_decode($_POST['name']));
    if (empty($_POST["name"])) {
        $errors++;
        $name_error = $lang['ENTER_FULL_NAME'];
        $name_error = "<span class='status-not-available'> " . $name_error . "</span>";
    } elseif (($name_length < 4) or ($name_length > 21)) {
        $errors++;
        $name_error = $lang['NAMELEN'];
        $name_error = "<span class='status-not-available'> " . $name_error . ".</span>";
    }
    /*elseif(preg_match('/[^A-Za-z\s]/',$_POST['name']))
    {
    $errors++;
    $name_error = $lang['ONLY_LETTER_SPACE'];
    $name_error = "<span class='status-not-available'> ".$name_error." [A-Z,a-z,0-9]</span>";
    }*/

    // Check if this is an Username availability check from signup page using ajax

    if (empty($_POST["username"])) {
        $errors++;
        $username_error = $lang['ENTERUNAME'];
        $username_error = "<span class='status-not-available'> " . $username_error . "</span>";
    } elseif (preg_match('/[^A-Za-z0-9]/', $_POST['username'])) {
        $errors++;
        $username_error = $lang['USERALPHA'];
        $username_error = "<span class='status-not-available'> " . $username_error . " [A-Z,a-z,0-9]</span>";
    } elseif ((strlen($_POST['username']) < 4) or (strlen($_POST['username']) > 16)) {
        $errors++;
        $username_error = $lang['USERLEN'];
        $username_error = "<span class='status-not-available'> " . $username_error . ".</span>";
    } else {
        $user_count = check_username_exists($_POST["username"]);
        if ($user_count > 0) {
            $errors++;
            $username_error = $lang['USERUNAV'];
            $username_error = "<span class='status-not-available'>" . $username_error . "</span>";
        } else {
            $username_error = $lang['USERUAV'];
            $username_error = "<span class='status-available'>" . $username_error . "</span>";
        }
    }

    if (empty($_POST["id_proof_type"])) {
        $errors++;
        $idprooftype_error = 'ID Proof Type';
        $idprooftype_error = "<span class='status-not-available'> " . $idprooftype_error . "</span>";
    }

    if (empty($_POST["id_proof_number"])) {
        $errors++;
        $idproofnumber_error = 'ID Proof Number';
        $idproofnumber_error = "<span class='status-not-available'> " . $idproofnumber_error . "</span>";
    }

    if (!isset($_FILES['id_proof'])) {
        $errors++;
        $idproof_error = 'ID Proof Document';
        $idproof_error = "<span class='status-not-available'> " . $idproof_error . "</span>";
    }

    // if (empty($_POST["address_proof_type"])) {
    //     $errors++;
    //     $addressprooftype_error = 'Address Proof Type';
    //     $addressprooftype_error = "<span class='status-not-available'> " . $addressprooftype_error . "</span>";
    // }

    // if (empty($_POST["address_proof_number"])) {
    //     $errors++;
    //     $addressproofnumber_error = 'Address Proof Number';
    //     $addressproofnumber_error = "<span class='status-not-available'> " . $addressproofnumber_error . "</span>";
    // }

    // if (!isset($_FILES['address_proof'])) {
    //     $errors++;
    //     $addressproof_error = 'Address Proof Document';
    //     $addressproof_error = "<span class='status-not-available'> " . $addressproof_error . "</span>";
    // }

    //Upload Id Proof & Address Proof Document on storage directory

    if (isset($_FILES['id_proof'])) {
        $id_proof_file_name = $_FILES['id_proof']['name'];
        $id_proof_file_tmp = $_FILES['id_proof']['tmp_name'];
        if ($id_proof_file_tmp != '') {
            $extension = pathinfo($id_proof_file_name, PATHINFO_EXTENSION);
            $id_proof_new_file_name = microtime(true) . '.' . $extension;
            $idProofNewMainFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/user_documents/id_proof/' . $id_proof_new_file_name;
            move_uploaded_file($id_proof_file_tmp, $idProofNewMainFilePath);
        }
    }

    // if (isset($_FILES['address_proof'])) {
    //     $address_proof_file_name = $_FILES['address_proof']['name'];
    //     $address_proof_file_tmp = $_FILES['address_proof']['tmp_name'];
    //     if ($address_proof_file_tmp != '') {
    //         $extension = pathinfo($address_proof_file_name, PATHINFO_EXTENSION);
    //         $address_proof_new_file_name = microtime(true) . '.' . $extension;
    //         $addressProofNewMainFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/user_documents/address_proof/' . $address_proof_new_file_name;
    //         move_uploaded_file($address_proof_file_tmp, $addressProofNewMainFilePath);
    //     }
    // }

    // Check if this is an Email availability check from signup page using ajax
    $_POST["email"] = strtolower($_POST["email"]);
    $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';

    if (empty($_POST["email"])) {
        $errors++;
        $email_error = $lang['ENTEREMAIL'];
        $email_error = "<span class='status-not-available'> " . $email_error . "</span>";
    } elseif (!preg_match($regex, $_POST['email'])) {
        $errors++;
        $email_error = $lang['EMAILINV'];
        $email_error = "<span class='status-not-available'> " . $email_error . ".</span>";
    } else {
        $user_count = check_account_exists($_POST["email"]);
        if ($user_count > 0) {
            $errors++;
            $email_error = $lang['ACCAEXIST'];
            $email_error = "<span class='status-not-available'>" . $email_error . "</span>";
        }
    }

    if (get_option('SMS_VERIFY_MODE')) {
        if (empty($_POST["phone"])) {
            $errors++;
            $phone_error = $lang['ENTER_MOBILE'];
            $phone_error = "<span class='status-not-available'> " . $phone_error . "</span>";
        }
    }

    // Check if this is an Password availability check from signup page using ajax
    if (empty($_POST["password"])) {
        $errors++;
        $password_error = $lang['ENTERPASS'];
        $password_error = "<span class='status-not-available'> " . $password_error . "</span>";
    } elseif ((strlen($_POST['password']) < 4) or (strlen($_POST['password']) > 21)) {
        $errors++;
        $password_error = $lang['PASSLENG'];
        $password_error = "<span class='status-not-available'> " . $password_error . ".</span>";
    }
    if ($config['recaptcha_mode'] == 1) {
        if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
            //your site secret key
            $secret = $config['recaptcha_private_key'];
            //get verify response data
            $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
            $responseData = json_decode($verifyResponse);
            if (!$responseData->success) {
                $errors++;
                $recaptcha_error = $lang['RECAPTCHA_ERROR'];
                $recaptcha_error = "<span class='status-not-available'> " . $recaptcha_error . ".</span>";
            }
        } else {
            $errors++;
            $recaptcha_error = $lang['RECAPTCHA_CLICK'];
            $recaptcha_error = "<span class='status-not-available'> " . $recaptcha_error . ".</span>";
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
        $insert_user->name = $_POST["name"];
        $insert_user->username = $_POST["username"];
        $insert_user->password_hash = $pass_hash;
        $insert_user->email = $_POST['email'];
        $insert_user->confirm = $confirm_id;
        $insert_user->created_at = $now;
        $insert_user->updated_at = $now;
        $insert_user->country_code = $_POST['country_code'];
        $insert_user->phone = $_POST['phone'];
        $insert_user->country = $location['country'];
        $insert_user->city = $location['city'];
        $insert_user->id_proof_type = $_POST["id_proof_type"];
        $insert_user->id_proof_number = $_POST["id_proof_number"];
        $insert_user->id_proof = $id_proof_new_file_name;
        // $insert_user->address_proof_type = $_POST["address_proof_type"];
        // $insert_user->address_proof_number = $_POST["address_proof_number"];
        // $insert_user->address_proof = $address_proof_new_file_name;
        $insert_user->save();
        $user_id = $insert_user->id();
        $insert = !empty($user_id > 0) ? true : false;

        if ($insert) {
            if (get_option('SMS_VERIFY_MODE')) {
                insert_mobile_number($user_id, $_POST['phone']);
            }
            /*SEND CONFIRMATION EMAIL*/
            email_template("signup_confirm", $user_id);

            /*SEND ACCOUNT DETAILS EMAIL*/
            email_template("signup_details", $user_id, $password);

            $loggedin = userlogin($_POST['username'], $_POST['password']);

            create_user_session($loggedin['id'], $loggedin['username'], $loggedin['password']);

            message($lang['WELCOME'], $lang['WELCOMETOSITE'], 'dashboard', false);
        } else {
            echo $lang['ERROR_TRY_AGAIN'];
        }
        exit;
    }
}

// Output to template
$page = new HtmlTemplate("templates/" . $config['tpl_name'] . "/signup.tpl");
$page->SetParameter('OVERALL_HEADER', create_header($lang['CREATE_AN_ACCOUNT']));

if (isset($_POST['submit'])) {
    $page->SetParameter('NAME_FIELD', $_POST['name']);
    $page->SetParameter('USERNAME_FIELD', $_POST['username']);
    $page->SetParameter('IDPROOFTYPE_FIELD', $_POST['id_proof_type']);
    $page->SetParameter('IDPROOFNUMBER_FIELD', $_POST['id_proof_number']);
    // $page->SetParameter('ADDRESSPROOFTYPE__FIELD', $_POST['address_proof_type']);
    // $page->SetParameter('ADDRESSPROOFNUMBER_FIELD', $_POST['address_proof_number']);
    $page->SetParameter('EMAIL_FIELD', $_POST['email']);
    $page->SetParameter('PHONE_FIELD', '');
    $page->SetParameter('NAME_ERROR', $name_error);
    $page->SetParameter('USERNAME_ERROR', $username_error);
    $page->SetParameter('IDPROOFTYPE_ERROR', $idprooftype_error);
    $page->SetParameter('IDPROOFNUMBER_ERROR', $idproofnumber_error);
    $page->SetParameter('IDPROOF', $idproof_error);
    // $page->SetParameter('ADDRESSPROOFTYPE_ERROR', $addressprooftype_error);
    // $page->SetParameter('ADDRESSPROOFNUMBER_ERROR', $addressproofnumber_error);
    // $page->SetParameter('ADDRESSPROOF_ERROR', $addressproof_error);
    $page->SetParameter('EMAIL_ERROR', $email_error);
    $page->SetParameter('PHONE_ERROR', $phone_error);
    $page->SetParameter('PASSWORD_ERROR', $password_error);
    $page->SetParameter('RECAPTCH_ERROR', $recaptcha_error);
} else {
    $page->SetParameter('NAME_FIELD', '');
    $page->SetParameter('USERNAME_FIELD', '');
    $page->SetParameter('IDPROOFTYPE_FIELD', '');
    $page->SetParameter('IDPROOFNUMBER_FIELD', '');
    $page->SetParameter('ADDRESSPROOFTYPE__FIELD', '');
    $page->SetParameter('ADDRESSPROOFNUMBER_FIELD', '');
    $page->SetParameter('EMAIL_FIELD', '');
    $page->SetParameter('PHONE_FIELD', '');
    $page->SetParameter('NAME_ERROR', '');
    $page->SetParameter('USERNAME_ERROR', '');
    $page->SetParameter('IDPROOFTYPE_ERROR', $idprooftype_error);
    $page->SetParameter('IDPROOFNUMBER_ERROR', $idproofnumber_error);
    $page->SetParameter('IDPROOF', $idproof_error);
    $page->SetParameter('ADDRESSPROOFTYPE_ERROR', $addressprooftype_error);
    $page->SetParameter('ADDRESSPROOFNUMBER_ERROR', $addressproofnumber_error);
    $page->SetParameter('ADDRESSPROOF_ERROR', $addressproof_error);
    $page->SetParameter('EMAIL_ERROR', '');
    $page->SetParameter('PHONE_ERROR', '');
    $page->SetParameter('PASSWORD_ERROR', '');
    $page->SetParameter('RECAPTCH_ERROR', '');
}
$page->SetParameter('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
