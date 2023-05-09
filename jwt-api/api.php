<?php

use JWT as GlobalJWT;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

class Api extends Rest
{
    protected $Host = 'smtp.gmail.com';
    protected $SMTPAuth = true;
    protected $Username = 'jharshita259@gmail.com';
    protected $Password = 'bfhagppogpishvbq';
    protected $SMTPSecure = 'tls';
    protected $Port = 587;

    protected $protocol;
    protected $host_url;

    protected $current_url;
    protected $display_image_url;

    public function __construct()
    {
        parent::__construct();
        $this->protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $this->host_url = $_SERVER['HTTP_HOST'];
        $this->current_url = $this->protocol . "://" . $this->host_url . $_SERVER['REQUEST_URI'];
        $this->display_image_url = str_replace("jwt-api/", "", $this->current_url);
    }

    public function login()
    {
        $email = $this->validateParameter('email', $this->param['email'], STRING);
        $password = $this->validateParameter('pass', $this->param['pass'], STRING);
        try {
            // $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE email =:email OR username=:username");
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE email =:email");
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($user)) {
                $response = ["status" => false, "code" => 400, "Message" => "Email or Password is incorrect."];
                $this->returnResponse($response);
            }

            //Check User password is valid or not
            if (!password_verify($password, $user['password_hash'])) {
                $response = ["status" => false, "code" => 400, "Message" => "Email or Password is incorrect."];
                $this->returnResponse($response);
            }
            // Check User Status && User status 0 for active, 1 for verify, 2 for de-active
            if ($user['status'] === '2') {
                $response = ["status" => false, "code" => 400, "Message" => "User account is de-activated. Please contact to admin."];
                $this->returnResponse($response);
            }

            if (!empty($user['id_proof'])) {
                $user['id_proof'] = $this->display_image_url . 'storage/user_documents/id_proof/' . $user['id_proof'];
            }
            if (!empty($user['address_proof'])) {
                $user['address_proof'] = $this->display_image_url . 'storage/user_documents/address_proof/' . $user['address_proof'];
            }

            $paylod = [
                'iat' => time(),
                'iss' => 'localhost',
                'exp' => time() + (144000),
                'userId' => $user['id'],
            ];

            $token = GlobalJWT::encode($paylod, SECRETE_KEY);

            $response = [
                "status" => true,
                "code" => 200,
                "Message" => "Login successfully.",
                "token" => $token,
                "data" => $user,
            ];
            $this->returnResponse($response);
        } catch (Exception $e) {
            $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
        }
    }

    public function loginWithPhone()
    {
        $countryCode = $this->validateParameter('country_code', $this->param['country_code'], STRING);
        $phone = $this->validateParameter('phone', $this->param['phone'], STRING);

        try {
            // $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE email =:email OR username=:username");
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE country_code =:country_code AND phone =:phone");
            $stmt->bindParam(":country_code", $countryCode);
            $stmt->bindParam(":phone", $phone);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($user['id'])) {
                $otp = mt_rand(111111, 999999);
                // Prepare the SQL UPDATE statement
                $stmt = $this->dbConn->prepare('UPDATE ad_user SET otp = :otp WHERE id = :id');
                // Bind the parameters and execute the statement
                $stmt->bindValue(':id', $user['id'], PDO::PARAM_STR);
                $stmt->bindValue(':otp', $otp, PDO::PARAM_STR);
                $stmt->execute();
                // Check for errors and return a response
                if ($stmt->rowCount() > 0) {
                    $response = ["status" => false, "code" => 200, "Message" => 'OTP successfully sent on your registered mobile.', "data" => ["country_code" => $countryCode, "phone" => $phone, "otp" => (string) $otp]];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => 'Record not found'];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "User does not exist with this phone number."];
                $this->returnResponse($response);
            }
        } catch (Exception $e) {
            $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
        }
    }

    public function verifyLoginOTP()
    {
        $countryCode = $this->validateParameter('country_code', $this->param['country_code'], STRING);
        $phone = $this->validateParameter('phone', $this->param['phone'], STRING);
        $otp = $this->validateParameter('otp', $this->param['otp'], STRING);

        try {
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE country_code =:country_code AND phone =:phone");
            $stmt->bindParam(":country_code", $countryCode);
            $stmt->bindParam(":phone", $phone);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($user['id'])) {
                if ($user['otp'] === $otp) {
                    $paylod = [
                        'iat' => time(),
                        'iss' => 'localhost',
                        'exp' => time() + (144000),
                        'userId' => $user['id'],
                    ];
                    $token = GlobalJWT::encode($paylod, SECRETE_KEY);
                    $response = ["status" => true, "code" => 200, "Message" => "Login successfully.", "token" => $token, "data" => $user];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => 'Given OTP does not match.'];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "User does not exist with this phone number."];
                $this->returnResponse($response);
            }
        } catch (Exception $e) {
            $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
        }
    }

    public function register()
    {

        try {
            // $fName = $this->validateParameter('full_name', $this->param['full_name'], STRING);
            // $uName = $this->validateParameter('user_name', $this->param['user_name'], STRING);
            // $email = $this->validateParameter('email', $this->param['email'], STRING);
            // $countryCode = $this->validateParameter('country_code', $this->param['country_code'], STRING);
            // $phone = $this->validateParameter('phone', $this->param['phone'], STRING);
            // $password = $this->validateParameter('pass', $this->param['pass'], STRING);

            $fName = $_POST['full_name'];
            $uName = $_POST['user_name'];
            $email = $_POST['email'];
            $countryCode = $_POST['country_code'];
            $phone = $_POST['phone'];
            $password = $_POST['pass'];
            $id_proof_new_file_name = '';
            $address_proof_new_file_name = '';
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

            if (isset($_FILES['address_proof'])) {
                $address_proof_file_name = $_FILES['address_proof']['name'];
                $address_proof_file_tmp = $_FILES['address_proof']['tmp_name'];
                if ($address_proof_file_tmp != '') {
                    $extension = pathinfo($address_proof_file_name, PATHINFO_EXTENSION);
                    $address_proof_new_file_name = microtime(true) . '.' . $extension;
                    $addressProofNewMainFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/user_documents/address_proof/' . $address_proof_new_file_name;
                    move_uploaded_file($address_proof_file_tmp, $addressProofNewMainFilePath);
                }
            }
            $check_email = "SELECT `email` FROM `ad_user` WHERE `email`=:email OR `phone`=:phone";
            $check_email_stmt = $this->dbConn->prepare($check_email);
            $check_email_stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $check_email_stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
            $check_email_stmt->execute();

            if ($check_email_stmt->rowCount()):
                $response = ["status" => false, "code" => 400, "Message" => "User already exist with this email or mobile."];
                $this->returnResponse($response);

            else:
                $otp = mt_rand(111111, 999999);
                $insert_query = "INSERT INTO `ad_user` (`username`,`name`,`email`,`country_code`,`phone`,`status`,`password_hash`,`otp`,`id_proof`,`address_proof`) VALUES(:username,:name,:email,:country_code,:phone,:status,:password_hash,:otp,:id_proof,:address_proof)";
                $insert_stmt = $this->dbConn->prepare($insert_query);
                // DATA BINDING
                $insert_stmt->bindValue(':username', htmlspecialchars(strip_tags($uName)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':name', htmlspecialchars(strip_tags($fName)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $insert_stmt->bindValue(':country_code', $countryCode, PDO::PARAM_STR);
                $insert_stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
                $insert_stmt->bindValue(':status', 0, PDO::PARAM_STR);
                $insert_stmt->bindValue(':password_hash', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                $insert_stmt->bindValue(':otp', $otp, PDO::PARAM_STR);
                $insert_stmt->bindValue(':id_proof', $id_proof_new_file_name, PDO::PARAM_STR);
                $insert_stmt->bindValue(':address_proof', $address_proof_new_file_name, PDO::PARAM_STR);
                $insert_stmt->execute();
                $subject = 'Plese verify OTP';
                $body = 'Your verification OTP is ' . $otp;
                $this->sendMail($email, $subject, $body);

                // Get the last insert ID
                $last_id = $this->dbConn->lastInsertId();
                // Select the last insert row
                $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE id=:id");
                $stmt->bindParam(':id', $last_id);
                $stmt->execute();
                // Fetch the row
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $paylod = ['iat' => time(), 'iss' => 'localhost', 'exp' => time() + (144000), 'userId' => $last_id];
                $token = GlobalJWT::encode($paylod, SECRETE_KEY);
                $response = ["status" => true, "code" => 200, "Message" => "You have successfully registered.", "token" => $token, "data" => $user, "otp" => $otp];

                $this->returnResponse($response);
            endif;
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function verifyEmail()
    {
        try {
            $otp = $this->validateParameter('otp', $this->param['otp'], STRING);
            $user = $this->getUserDetailThroughToken();
            if (!empty($user) && ($user['otp'] === $otp)) {
                // Prepare the SQL UPDATE statement
                $stmt = $this->dbConn->prepare('UPDATE ad_user SET status = :status WHERE id = :id');

                // Bind the parameters and execute the statement
                $stmt->bindValue(':id', $user['id'], PDO::PARAM_STR);
                $stmt->bindValue(':status', 1, PDO::PARAM_STR);
                $stmt->execute();
                // Check for errors and return a response
                if ($stmt->rowCount() > 0) {
                    $response = ["status" => false, "code" => 200, "Message" => 'Email successfully verified. Thank you'];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => 'Record not found'];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => 'OTP does not matched.'];
                $this->returnResponse($response);
            }
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function verifyPhone()
    {
        try {
            $otp = $this->validateParameter('otp', $this->param['otp'], STRING);
            $user = $this->getUserDetailThroughToken();
            if (!empty($user) && ($user['otp'] === $otp)) {
                // Prepare the SQL UPDATE statement
                $stmt = $this->dbConn->prepare('UPDATE ad_user SET status = :status WHERE id = :id');

                // Bind the parameters and execute the statement
                $stmt->bindValue(':id', $user['id'], PDO::PARAM_STR);
                $stmt->bindValue(':status', 1, PDO::PARAM_STR);
                $stmt->execute();
                // Check for errors and return a response
                if ($stmt->rowCount() > 0) {
                    $response = ["status" => false, "code" => 200, "Message" => 'Mobile successfully verified. Thank you'];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => 'Record not found'];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => 'OTP does not matched.'];
                $this->returnResponse($response);
            }
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function forgetPassword()
    {
        try {
            $mobile = $this->validateParameter('mobile', $this->param['mobile'], STRING);
            $countryCode = $this->validateParameter('country_code', $this->param['country_code'], STRING);
            if (!empty($mobile)) {
                $getuser = "SELECT `id` FROM `ad_user` WHERE `country_code`=:country_code AND `phone`=:phone";
                $userData = $this->dbConn->prepare($getuser);
                $userData->bindValue(':country_code', $countryCode, PDO::PARAM_STR);
                $userData->bindValue(':phone', $mobile, PDO::PARAM_STR);
                $userData->execute();
                $userData = $userData->fetch(PDO::FETCH_ASSOC);
                if (!empty($userData['id'])) {
                    $otp = mt_rand(111111, 999999);
                    // Prepare the SQL UPDATE statement
                    $stmt = $this->dbConn->prepare('UPDATE ad_user SET otp = :otp WHERE id = :id');

                    // Bind the parameters and execute the statement
                    $stmt->bindValue(':id', $userData['id'], PDO::PARAM_STR);
                    $stmt->bindValue(':otp', $otp, PDO::PARAM_STR);
                    // Check for errors and return a response
                    if ($stmt->execute()) {
                        $response = ["status" => true, "code" => 200, "Message" => 'Please verify otp for generating new password', "data" => ["otp" => (string) $otp, "user_id" => $userData['id']]];
                        $this->returnResponse($response);
                    }
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "User not found."];
                    $this->returnResponse($response);
                }
            }
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function verifyForgetPassword()
    {
        try {
            $otp = $this->validateParameter('otp', $this->param['otp'], STRING);
            $user_id = $this->validateParameter('user_id', $this->param['user_id'], STRING);
            if (!empty($otp) && !empty($user_id)) {

                $getuser = "SELECT `otp` FROM `ad_user` WHERE `id`=:id";
                $userData = $this->dbConn->prepare($getuser);
                $userData->bindValue(':id', $user_id, PDO::PARAM_STR);
                $userData->execute();
                $userData = $userData->fetch(PDO::FETCH_ASSOC);
                if (!empty($userData['otp']) && ($userData['otp'] == $otp)) {
                    $response = ["status" => true, "code" => 200, "Message" => 'OTP successfully matched.', "data" => ["user_id" => $user_id]];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "OTP do not match."];
                    $this->returnResponse($response);
                }
            }
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function generateNewPassword()
    {
        try {
            $user_id = $this->validateParameter('user_id', $this->param['user_id'], STRING);
            $password = $this->validateParameter('password', $this->param['password'], STRING);
            if (!empty($password) && !empty($user_id)) {

                // Prepare the SQL UPDATE statement
                $stmt = $this->dbConn->prepare('UPDATE ad_user SET password_hash = :password_hash WHERE id = :id');

                // Bind the parameters and execute the statement
                $stmt->bindValue(':id', $user_id, PDO::PARAM_STR);
                $stmt->bindValue(':password_hash', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                // Check for errors and return a response
                if ($stmt->execute()) {
                    $response = ["status" => true, "code" => 200, "Message" => 'Password successfully updated'];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Password not updated."];
                    $this->returnResponse($response);
                }
            }
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function getUserDetailThroughToken()
    {
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if ($payload) {
                $getuser = "SELECT * FROM `ad_user` WHERE `id`=:id";
                $userData = $this->dbConn->prepare($getuser);
                $userData->bindValue(':id', $payload->userId, PDO::PARAM_STR);
                $userData->execute();
                $userData = $userData->fetch(PDO::FETCH_ASSOC);
                if ($userData) {
                    return $userData;
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                    $this->returnResponse($response);
                }

            } else {
                return false;
            }
        }
    }

    public function social_login()
    {
        $oauthProvider = $this->validateParameter('oauth_provider', $this->param['oauth_provider'], STRING);
        $oauthUid = $this->validateParameter('oauth_uid', $this->param['oauth_uid'], STRING);

        try {
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE oauth_provider =:oauth_provider AND oauth_uid=:oauth_uid");
            $stmt->bindParam(":oauth_provider", $oauthProvider);
            $stmt->bindParam(":oauth_uid", $oauthUid);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($user)) {
                //Update
                // Prepare the SQL UPDATE statement
                $stmt = $this->dbConn->prepare('UPDATE ad_user SET oauth_provider = :oauth_provider, oauth_uid = :oauth_uid WHERE id = :id');

                // Bind the parameters and execute the statement
                $stmt->bindValue(':id', $user['id'], PDO::PARAM_STR);
                $stmt->bindValue(':oauth_provider', $oauthProvider, PDO::PARAM_STR);
                $stmt->bindValue(':oauth_uid', $oauthUid, PDO::PARAM_STR);
                $stmt->execute();
                $paylod = ['iat' => time(), 'iss' => 'localhost', 'exp' => time() + (144000), 'userId' => $user['id']];
                $token = GlobalJWT::encode($paylod, SECRETE_KEY);
                $response = ["status" => true, "code" => 200, "Message" => "Login successfully.", "token" => $token, "data" => $user];
                $this->returnResponse($response);
            } else {
                //Create
                $insert_query = "INSERT INTO `ad_user` (`oauth_provider`,`oauth_uid`) VALUES(:oauth_provider,:oauth_uid)";
                $stmt = $this->dbConn->prepare($insert_query);
                // DATA BINDING
                $stmt->bindValue(':oauth_provider', $oauthProvider, PDO::PARAM_STR);
                $stmt->bindValue(':oauth_uid', $oauthUid, PDO::PARAM_STR);
                // $stmt->bindValue(':username', htmlspecialchars(strip_tags($uName)), PDO::PARAM_STR);
                // $stmt->bindValue(':name', htmlspecialchars(strip_tags($fName)), PDO::PARAM_STR);
                // $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                // $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
                // $stmt->bindValue(':status', 0, PDO::PARAM_STR);
                // $stmt->bindValue(':password_hash', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                // $stmt->bindValue(':otp', $otp, PDO::PARAM_STR);
                $stmt->execute();
                // Get the last insert ID
                $last_id = $this->dbConn->lastInsertId();
                // Select the last insert row
                $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE id=:id");
                $stmt->bindParam(':id', $last_id);
                $stmt->execute();
                // Fetch the row
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $paylod = ['iat' => time(), 'iss' => 'localhost', 'exp' => time() + (144000), 'userId' => $last_id];
                $token = GlobalJWT::encode($paylod, SECRETE_KEY);
                $response = ["status" => true, "code" => 200, "Message" => "Login successfully.", "token" => $token, "data" => $user];
                $this->returnResponse($response);
            }

        } catch (Exception $e) {
            $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
        }
    }

    public function createSlug($productName)
    {
        // Convert to lowercase
        $slug = strtolower($productName);
        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        // Remove duplicate hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');
        return $slug;
    }
    public function addPost()
    {
        try {
            $userId = $_POST['user_id'];
            if (!empty($userId)) {

                $featured = isset($_POST['featured']) ? $_POST['featured'] : 0;
                $urgent = isset($_POST['urgent']) ? $_POST['urgent'] : 0;
                $highlight = isset($_POST['highlight']) ? $_POST['highlight'] : 0;
                $productName = $_POST['product_name'];
                if (!empty($productName)) {
                    $slug = $this->createSlug($productName);
                } else {
                    $slug = '';
                }
                $description = $_POST['description'];
                $category = $_POST['category'];
                $subCategory = $_POST['sub_category'];
                $price = $_POST['price'];
                $negotiable = $_POST['negotiable'];
                $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
                $hidePhone = isset($_POST['hide_phone']) ? $_POST['hide_phone'] : 0;
                $location = $_POST['location'];
                $city = $_POST['city'];
                $country = $_POST['country'];
                $latlong = $_POST['latlong'];
                $state = $_POST['state'];
                $tag = isset($_POST['tag']) ? $_POST['tag'] : '';
                $view = isset($_POST['view']) ? $_POST['view'] : 0;
                // $expire_date = $_POST['expire_date'];
                // $featured_exp_date = $_POST['featured_exp_date'];
                // $urgent_exp_date = $_POST['urgent_exp_date'];
                // $highlight_exp_date = $_POST['highlight_exp_date'];
                $adminSeen = isset($_POST['admin_seen']) ? $_POST['admin_seen'] : 0;
                $emailed = isset($_POST['emailed']) ? $_POST['emailed'] : 0;
                $hide = isset($_POST['hide']) ? $_POST['hide'] : 0;

                //Upload Images gally
                $total_count = count($_FILES['product_images']['name']);
                if ($total_count > 0) {
                    $screenShot = '';
                    for ($i = 0; $i < $total_count; $i++) {
                        $new_name = '';
                        //The temp file path is obtained
                        $tmpFilePath = $_FILES['product_images']['tmp_name'][$i];
                        //A file path needs to be present
                        if ($tmpFilePath != "") {
                            //Setup our new file path
                            $timestamp = microtime(true);
                            $original_name = $_FILES['product_images']['name'][$i];
                            $extension = pathinfo($original_name, PATHINFO_EXTENSION);
                            $new_name = $timestamp . '.' . $extension;

                            $newMainFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/products/' . $new_name;
                            $newThumbnailFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/products/thumb/' . $new_name;
                            if (move_uploaded_file($tmpFilePath, $newMainFilePath)) {
                                chmod($newMainFilePath, 0777);
                                copy($newMainFilePath, $newThumbnailFilePath);
                                $screenShot .= $new_name . ',';
                            }
                        }
                    }
                }
                $screenShot = rtrim($screenShot, ",");

                /*if(checkloggedin()) {
                $group_id = get_user_group();
                // Get usergroup details
                switch ($group_id)
                {
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
                if(!isset($plan['settings'])){
                $plan = json_decode(get_option('free_membership_plan'), true);
                $group_get_info = $plan['settings'];

                }else{
                $group_get_info = json_decode($plan['settings'],true);

                }
                break;
                }
                }else{
                $plan = json_decode(get_option('free_membership_plan'), true);
                $group_get_info = $plan['settings'];
                }

                $urgent_project_fee = $group_get_info['urgent_project_fee'];
                $featured_project_fee = $group_get_info['featured_project_fee'];
                $highlight_project_fee = $group_get_info['highlight_project_fee'];

                $ad_duration = $group_get_info['ad_duration'];
                $timenow = date('Y-m-d H:i:s');
                $expire_time = date('Y-m-d H:i:s', strtotime($timenow . ' +'.$ad_duration.' day'));
                $expire_timestamp = strtotime($expire_time);*/
                $ad_duration = 7;
                $timenow = date('Y-m-d H:i:s');
                $expire_time = date('Y-m-d H:i:s', strtotime($timenow . ' +' . $ad_duration . ' day'));
                $expire_timestamp = strtotime($expire_time);

                $sql = 'INSERT INTO ad_product (id, status, user_id, featured, urgent, highlight, product_name, slug, description, category, sub_category, price, negotiable, phone, hide_phone, location, city, state, country, latlong, screen_shot, tag, view, created_at, updated_at, expire_date, featured_exp_date, urgent_exp_date, highlight_exp_date, admin_seen, emailed, hide) VALUES(null, :status, :user_id, :featured, :urgent, :highlight, :product_name, :slug, :description, :category, :sub_category, :price, :negotiable, :phone, :hide_phone, :location, :city, :state, :country, :latlong, :screen_shot, :tag, :view, :created_at, :updated_at, :expire_date, :featured_exp_date, :urgent_exp_date, :highlight_exp_date, :admin_seen, :emailed, :hide)';
                $status = 'pending';
                $createdDate = date('Y-m-d H:i:s');
                $featuredExpDate = null;
                $stmt = $this->dbConn->prepare($sql);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':user_id', $userId);
                // $stmt->bindParam(':featured', !empty($featured) ? $featured : 0);
                $stmt->bindParam(':featured', $featured);
                $stmt->bindParam(':urgent', $urgent);
                $stmt->bindParam(':highlight', $highlight);
                $stmt->bindParam(':product_name', $productName);
                $stmt->bindParam(':slug', $slug);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':category', $category);
                $stmt->bindParam(':sub_category', $subCategory);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':negotiable', $negotiable);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':hide_phone', $hidePhone);
                $stmt->bindParam(':location', $location);
                $stmt->bindParam(':city', $city);
                $stmt->bindParam(':state', $state);
                $stmt->bindParam(':country', $country);
                $stmt->bindParam(':latlong', $latlong);
                $stmt->bindParam(':screen_shot', $screenShot);
                $stmt->bindParam(':tag', $tag);
                $stmt->bindParam(':view', $view);
                $stmt->bindParam(':created_at', $createdDate);
                $stmt->bindParam(':updated_at', $createdDate);
                $stmt->bindParam(':expire_date', $expire_timestamp);
                $stmt->bindParam(':featured_exp_date', $featuredExpDate);
                $stmt->bindParam(':urgent_exp_date', $featuredExpDate);
                $stmt->bindParam(':highlight_exp_date', $featuredExpDate);
                $stmt->bindParam(':admin_seen', $adminSeen);
                $stmt->bindParam(':emailed', $emailed);
                $stmt->bindParam(':hide', $hide);

                if ($stmt->execute()) {
                    // Get the last insert ID
                    $last_id = $this->dbConn->lastInsertId();
                    // Select the last insert row
                    $stmt = $this->dbConn->prepare("SELECT * FROM ad_product WHERE id=:id");
                    $stmt->bindParam(':id', $last_id);
                    $stmt->execute();
                    // Fetch the row
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    $response = ["status" => true, "code" => 200, "Message" => "Advertisement successfuly posted.", "data" => $product];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Something went wrong"];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "Userid required"];
                $this->returnResponse($response);
            }
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function getPostDetails()
    {
        try {
            $postId = $this->validateParameter('postId', $this->param['postId'], INTEGER);
            if (!empty($postId)) {
                $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city WHERE ap.id=:id";
                $postData = $this->dbConn->prepare($getpost);
                $postData->bindValue(':id', $postId, PDO::PARAM_STR);
                $postData->execute();
                $postData = $postData->fetch(PDO::FETCH_ASSOC);
                if (!empty($postData)) {
                    //Fetch Post user details
                    if (!empty($postData['user_id'])) {
                        $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE id =:id");
                        $stmt->bindParam(":id", $postData['user_id']);
                        $stmt->execute();
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (!empty($user)) {
                            $postData['post_user_details'] = $user;
                            $postData['post_user_details']['image'] = $this->display_image_url . 'storage/profile/' . $user['image'];
                            $postData['post_user_details']['id_proof'] = $this->display_image_url . 'storage/user_documents/id_proof/' . $user['image'];
                            $postData['post_user_details']['address_proof'] = $this->display_image_url . 'storage/user_documents/address_proof/' . $user['image'];
                        }
                    }
                    if (!empty($postData['screen_shot'])) {
                        $screenShotArr = explode(",", $postData['screen_shot']);
                        if (count($screenShotArr) > 0) {
                            for ($i = 0; $i < count($screenShotArr); $i++) {
                                // echo $screenShotArr[$i].'<br>';
                                $postData['image'][$i] = $this->display_image_url . 'storage/products/' . $screenShotArr[$i];
                            }
                        }
                    }
                    $response = ["status" => true, "code" => 200, "Message" => "Advertisement details fetched.", "data" => $postData];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "postId required."];
                $this->returnResponse($response);
            }

        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }

    }

    public function getAllUserPostDetails()
    {
        try {
            $token = $this->getBearerToken();
            if (!empty($token)) {
                $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
                if (!empty($payload->userId)) {
                    $responseArr = array();
                    $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city WHERE ap.user_id=:userId";
                    $postData = $this->dbConn->prepare($getpost);
                    $postData->bindValue(':userId', $payload->userId, PDO::PARAM_STR);
                    $postData->execute();
                    $postData = $postData->fetchAll(PDO::FETCH_ASSOC);
                    if (count($postData) > 0) {
                        foreach ($postData as $key => $post) {
                            $responseArr[$key] = $post;
                            if (!empty($post['screen_shot'])) {
                                $screenShotArr = explode(",", $post['screen_shot']);
                                if (count($screenShotArr) > 0) {
                                    for ($i = 0; $i < count($screenShotArr); $i++) {
                                        $responseArr[$key]['image'][$i] = $this->display_image_url . 'storage/products/' . $screenShotArr[$i];
                                    }
                                }
                            }
                        }
                        $response = ["status" => true, "code" => 200, "Message" => "All Advertisement details fetched.", "data" => $responseArr];
                        $this->returnResponse($response);
                    } else {
                        $response = ["status" => false, "code" => 400, "Message" => "No post found for this user."];
                        $this->returnResponse($response);
                    }
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "Authorization token not found."];
                $this->returnResponse($response);
            }

        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }

    }

    public function getAllPost()
    {
        try {
            $responseArr = array();
            $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city WHERE 1=1";
            if (!empty($this->param['title'])) {
                $getpost .= " AND ap.product_name LIKE CONCAT( '%', :title, '%')";
            }
            if (!empty($this->param['category'])) {
                $getpost .= " AND ap.category=:categoryId";
            }
            if (!empty($this->param['sub_category'])) {
                $getpost .= " AND ap.sub_category=:subCategoryId";
            }
            if (!empty($this->param['location'])) {
                $getpost .= " AND ap.location LIKE CONCAT( '%', :location, '%')";
            }
            if (!empty($this->param['city'])) {
                $getpost .= " AND ap.city=:cityId";
            }
            if (!empty($this->param['state'])) {
                $getpost .= " AND ap.state LIKE CONCAT( '%', :stateId, '%')";
            }
            if (!empty($this->param['country'])) {
                $getpost .= " AND ap.country LIKE CONCAT( '%', :countryId, '%')";
            }
            if (!empty($this->param['priceto']) && !empty($this->param['pricefrom'])) {
                $getpost .= " AND ap.price BETWEEN " . $this->param['priceto'] . " AND " . $this->param['pricefrom'] . "";
            }

            /*if(!empty($this->param['yearto']) && !empty($this->param['yearfrom'])){
            $getpost .= " AND ap.created_at BETWEEN '".$this->param['yearto']."' AND '".$this->param['yearfrom']."'";
            }*/

            if (!empty($this->param['sortbyfieldname']) && !empty($this->param['sortbytypename'])) {
                $getpost .= " ORDER BY " . $this->param['sortbyfieldname'] . " " . $this->param['sortbytypename'];
            }

            $postData = $this->dbConn->prepare($getpost);

            if (!empty($this->param['title'])) {
                $postData->bindValue(':title', $this->param['title'], PDO::PARAM_STR);
            }

            if (!empty($this->param['category'])) {
                $postData->bindValue(':categoryId', $this->param['category'], PDO::PARAM_STR);
            }
            if (!empty($this->param['sub_category'])) {
                $postData->bindValue(':subCategoryId', $this->param['sub_category'], PDO::PARAM_STR);
            }
            if (!empty($this->param['location'])) {
                $postData->bindValue(':location', $this->param['location'], PDO::PARAM_STR);
            }
            if (!empty($this->param['city'])) {
                $postData->bindValue(':cityId', $this->param['city'], PDO::PARAM_STR);
            }
            if (!empty($this->param['state'])) {
                $postData->bindValue(':stateId', $this->param['state'], PDO::PARAM_STR);
            }
            if (!empty($this->param['country'])) {
                $postData->bindValue(':countryId', $this->param['country'], PDO::PARAM_STR);
            }

            $postData->execute();
            // echo "Last executed query: " . $postData->queryString;
            // exit;
            $postData = $postData->fetchAll(PDO::FETCH_ASSOC);
            if (count($postData) > 0) {
                foreach ($postData as $key => $post) {
                    $responseArr[$key] = $post;
                    if (!empty($post['screen_shot'])) {
                        $screenShotArr = explode(",", $post['screen_shot']);
                        if (count($screenShotArr) > 0) {
                            for ($i = 0; $i < count($screenShotArr); $i++) {
                                $responseArr[$key]['image'][$i] = $this->display_image_url . 'storage/products/' . $screenShotArr[$i];
                            }
                        }
                    }
                }
                $response = ["status" => true, "code" => 200, "Message" => "All Advertisement successfully fetched.", "data" => $responseArr];
                $this->returnResponse($response);
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "Record not found."];
                $this->returnResponse($response);
            }

        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }

    }

    public function addCustomer()
    {
        $name = $this->validateParameter('name', $this->param['name'], STRING, false);
        $email = $this->validateParameter('email', $this->param['email'], STRING, false);
        $addr = $this->validateParameter('addr', $this->param['addr'], STRING, false);
        $mobile = $this->validateParameter('mobile', $this->param['mobile'], INTEGER, false);

        $cust = new Customer;
        $cust->setName($name);
        $cust->setEmail($email);
        $cust->setAddress($addr);
        $cust->setMobile($mobile);
        $cust->setCreatedBy($this->userId);
        $cust->setCreatedOn(date('Y-m-d'));

        if (!$cust->insert()) {
            $message = 'Failed to insert.';
        } else {
            $message = "Inserted successfully.";
        }

        $this->returnResponse(SUCCESS_RESPONSE, $message);
    }

    public function getCustomerDetails()
    {
        $customerId = $this->validateParameter('customerId', $this->param['customerId'], INTEGER);

        $cust = new Customer;
        $cust->setId($customerId);
        $customer = $cust->getCustomerDetailsById();
        if (!is_array($customer)) {
            $this->returnResponse(SUCCESS_RESPONSE, ['message' => 'Customer details not found.']);
        }

        $response['customerId'] = $customer['id'];
        $response['cutomerName'] = $customer['name'];
        $response['email'] = $customer['email'];
        $response['mobile'] = $customer['mobile'];
        $response['address'] = $customer['address'];
        $response['createdBy'] = $customer['created_user'];
        $response['lastUpdatedBy'] = $customer['updated_user'];
        $this->returnResponse(SUCCESS_RESPONSE, $response);
    }

    public function updateCustomer()
    {
        $customerId = $this->validateParameter('customerId', $this->param['customerId'], INTEGER);
        $name = $this->validateParameter('name', $this->param['name'], STRING, false);
        $addr = $this->validateParameter('addr', $this->param['addr'], STRING, false);
        $mobile = $this->validateParameter('mobile', $this->param['mobile'], INTEGER, false);

        $cust = new Customer;
        $cust->setId($customerId);
        $cust->setName($name);
        $cust->setAddress($addr);
        $cust->setMobile($mobile);
        $cust->setUpdatedBy($this->userId);
        $cust->setUpdatedOn(date('Y-m-d'));

        if (!$cust->update()) {
            $message = 'Failed to update.';
        } else {
            $message = "Updated successfully.";
        }

        $this->returnResponse(SUCCESS_RESPONSE, $message);
    }

    public function deleteCustomer()
    {
        $customerId = $this->validateParameter('customerId', $this->param['customerId'], INTEGER);

        $cust = new Customer;
        $cust->setId($customerId);

        if (!$cust->delete()) {
            $message = 'Failed to delete.';
        } else {
            $message = "deleted successfully.";
        }

        $this->returnResponse(SUCCESS_RESPONSE, $message);
    }

    public function getCategories()
    {
        try {
            $cat = new Category;
            $category = $cat->getAllCategories();
            if (!is_array($category)) {
                $response = ["status" => false, "code" => 400, "Message" => 'Categories not found.'];
                $this->returnResponse($response);
            }
            $response = ["status" => true, "code" => 200, "Message" => "Category list successfully fetched.", "data" => $category];
            $this->returnResponse($response);
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }

    }

    public function getSubCategories()
    {
        try {
            $categoryId = $this->validateParameter('categoryId', $this->param['categoryId'], INTEGER);
            $cat = new Category;
            $cat->setCatId($categoryId);
            $category = $cat->getSubCategoryListingById();
            if (!is_array($category)) {
                $response = ["status" => false, "code" => 400, "Message" => 'Categories not found.'];
                $this->returnResponse($response);
            }
            $response = ["status" => true, "code" => 200, "Message" => "Category list successfully fetched.", "data" => $category];
            $this->returnResponse($response);
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }

    }

    public function getCountries()
    {
        try {
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_countries ORDER BY id ASC");
            $stmt->execute();
            $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ["status" => true, "code" => 200, "Message" => "Country list successfully fetched.", "data" => $countries];
            $this->returnResponse($response);
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    // Get state by ad_subadmin1
    public function getStates()
    {
        try {
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_subadmin1 ORDER BY id ASC");
            $stmt->execute();
            $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ["status" => true, "code" => 200, "Message" => "States list successfully fetched.", "data" => $countries];
            $this->returnResponse($response);
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    // Get state by ad_subadmin2
    public function getStatesBySubAdmin2()
    {
        try {
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_subadmin2 ORDER BY id ASC");
            $stmt->execute();
            $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ["status" => true, "code" => 200, "Message" => "States list successfully fetched.", "data" => $countries];
            $this->returnResponse($response);
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function getCities()
    {
        try {
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_cities ORDER BY id ASC");
            $stmt->execute();
            $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ["status" => true, "code" => 200, "Message" => "City list successfully fetched.", "data" => $countries];
            $this->returnResponse($response);
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function replyByEmail()
    {
        $name = $this->validateParameter('name', $this->param['name'], STRING);
        $email = $this->validateParameter('email', $this->param['email'], STRING);
        $phone = $this->validateParameter('phone', $this->param['phone'], STRING);
        $message = $this->validateParameter('message', $this->param['message'], STRING);
        $toEmail = $this->validateParameter('toEmail', $this->param['toEmail'], STRING);
        $receiverName = $this->validateParameter('receiverName', $this->param['receiverName'], STRING);
        $productId = $this->validateParameter('productId', $this->param['productId'], STRING);
        $productName = $this->validateParameter('productName', $this->param['productName'], STRING);
        if (!empty($name) && !empty($email) && !empty($phone) && !empty($message) && !empty($toEmail) && !empty($receiverName) && !empty($productId) && !empty($productName)) {
            $mail = new PHPMailer(true);
            $mail->Host = $this->Host;
            $mail->SMTPAuth = $this->SMTPAuth;
            $mail->Username = $this->Username;
            $mail->Password = $this->Password;
            $mail->SMTPSecure = $this->SMTPSecure;
            $mail->Port = $this->Port;
            //Set Details for Sending mail
            $mail->setFrom('jharshita259@gmail.com', 'Payaki');
            $mail->addAddress($toEmail, $receiverName);
            $mail->isHTML(true);
            $mail->Subject = 'Payaki classified ad';
            $mail->Body = '<html><body>';
            $mail->Body .= '<p>' . $name . ' wants to connect with you.</p>';
            $mail->Body .= '<p>Ad Id# : ' . $productId . '</p>';
            $mail->Body .= '<p>Ad Name : ' . $productName . '</p>';
            $mail->Body .= '<p>Name : ' . $name . '</p>';
            $mail->Body .= '<p>Email : ' . $email . '</p>';
            $mail->Body .= '<p>Phone : ' . $phone . '</p>';
            $mail->Body .= '<p>Message : ' . $message . '</p>';
            $mail->Body .= '</body></html>';
            if ($mail->send()) {
                $response = ["status" => true, "code" => 200, "Message" => "Email successfully sent."];
                $this->returnResponse($response);
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "Something is wrong."];
                $this->returnResponse($response);
            }
        }
    }

    public function reportViolation()
    {
        $name = $this->validateParameter('name', $this->param['name'], STRING);
        $email = $this->validateParameter('email', $this->param['email'], STRING);
        $username = $this->validateParameter('username', $this->param['username'], STRING);
        $violation_type = $this->validateParameter('violation_type', $this->param['violation_type'], STRING);
        $other_person_name = $this->validateParameter('other_person_name', $this->param['other_person_name'], STRING);
        $violation_url = $this->validateParameter('violation_url', $this->param['violation_url'], STRING);
        $violation_details = $this->validateParameter('violation_details', $this->param['violation_details'], STRING);
        if (!empty($name) && !empty($email) && !empty($username) && !empty($violation_type) && !empty($other_person_name) && !empty($violation_url) && !empty($violation_details)) {
            $mail = new PHPMailer(true);
            $mail->Host = $this->Host;
            $mail->SMTPAuth = $this->SMTPAuth;
            $mail->Username = $this->Username;
            $mail->Password = $this->Password;
            $mail->SMTPSecure = $this->SMTPSecure;
            $mail->Port = $this->Port;
            // Set the email details
            $mail->setFrom('jharshita259@gmail.com', 'Payaki');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Report violation mail from payaki';
            $mail->Body = '<html><body>';
            $mail->Body .= '<p>Name : ' . $name . '</p>';
            $mail->Body .= '<p>User Name : ' . $username . '</p>';
            $mail->Body .= '<p>Email : ' . $email . '</p>';
            $mail->Body .= '<p>Violation : ' . $violation_type . '</p>';
            $mail->Body .= '<p>Violator : ' . $other_person_name . '</p>';
            $mail->Body .= '<p>Violation URl : ' . $violation_url . '</p>';
            $mail->Body .= '<p>' . $violation_details . '</p>';
            $mail->Body .= '</body></html>';
            if ($mail->send()) {
                $response = ["status" => true, "code" => 200, "Message" => "Email successfully sent."];
                $this->returnResponse($response);
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "Something is wrong."];
                $this->returnResponse($response);
            }
        }
    }
    public function sendMail($toEmail, $subject, $body)
    {
        $mail = new PHPMailer(true);
        $mail->Host = $this->Host;
        $mail->SMTPAuth = $this->SMTPAuth;
        $mail->Username = $this->Username;
        $mail->Password = $this->Password;
        $mail->SMTPSecure = $this->SMTPSecure;
        $mail->Port = $this->Port;
        $mail->setFrom('jharshita259@gmail.com');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    }

    public function sendTestEmail()
    {
        $mail = new PHPMailer(true);
        $mail->Host = $this->Host;
        $mail->SMTPAuth = $this->SMTPAuth;
        $mail->Username = $this->Username;
        $mail->Password = $this->Password;
        $mail->SMTPSecure = $this->SMTPSecure;
        $mail->Port = $this->Port;
        $mail->setFrom('jharshita259@gmail.com');
        $mail->addAddress('tiwarilalit601@mailinator.com');
        $mail->isHTML(true);
        $mail->Subject = 'Testing subject mail send through rest api';
        $mail->Body = 'Testing Body mail send through rest api';
        $mail->send();
        echo 'Mail successfully sent';

    }

    public function likeDislikePost()
    {
        try {
            $token = $this->getBearerToken();
            if (!empty($token)) {
                $product_id = $this->validateParameter('product_id', $this->param['product_id'], STRING);
                $user_id = $this->validateParameter('user_id', $this->param['user_id'], STRING);
                if (!empty($user_id) && !empty($product_id)) {
                    $stmt = $this->dbConn->prepare("SELECT * FROM ad_favads WHERE user_id =:user_id AND product_id =:product_id");
                    $stmt->bindParam(":user_id", $user_id);
                    $stmt->bindParam(":product_id", $product_id);
                    $stmt->execute();
                    // Check if there are any rows returned
                    if ($stmt->rowCount() > 0) {
                        // Write delete code
                        $stmt = $this->dbConn->prepare('DELETE FROM ad_favads WHERE user_id =:user_id AND product_id =:product_id');
                        $stmt->bindParam(":user_id", $user_id);
                        $stmt->bindParam(":product_id", $product_id);
                        if ($stmt->execute()) {
                            $response = ["status" => true, "code" => 200, "Message" => "You successfully disliked this products."];
                            $this->returnResponse($response);
                        } else {
                            $response = ["status" => false, "code" => 400, "Message" => "Something is wrong."];
                            $this->returnResponse($response);
                        }
                    } else {
                        // Write insert code
                        $sql = 'INSERT INTO ad_favads (id, user_id, product_id) VALUES(null, :user_id, :product_id)';
                        $stmt = $this->dbConn->prepare($sql);
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->bindParam(':product_id', $product_id);
                        if ($stmt->execute()) {
                            $response = ["status" => true, "code" => 200, "Message" => "You successfully liked this products."];
                            $this->returnResponse($response);
                        } else {
                            $response = ["status" => false, "code" => 400, "Message" => "Something is wrong."];
                            $this->returnResponse($response);
                        }
                    }
                }

            } else {
                $response = ["status" => false, "code" => 400, "Message" => "Authorization token not found."];
                $this->returnResponse($response);
            }

        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }

    }

    public function reviewAndRating()
    {
        try {
            $token = $this->getBearerToken();
            if (!empty($token)) {
                $product_id = $this->validateParameter('product_id', $this->param['product_id'], STRING);
                $user_id = $this->validateParameter('user_id', $this->param['user_id'], STRING);
                $rating = $this->validateParameter('rating', $this->param['rating'], STRING);
                $comment = $this->validateParameter('comment', $this->param['comment'], STRING);
                if (!empty($user_id) && !empty($product_id) && !empty($rating) && !empty($comment)) {
                    $stmt = $this->dbConn->prepare("SELECT * FROM ad_reviews WHERE user_id =:user_id AND productID =:productID");
                    $stmt->bindParam(":productID", $product_id);
                    $stmt->bindParam(":user_id", $user_id);
                    $stmt->execute();
                    // Check if there are any rows returned
                    if ($stmt->rowCount() > 0) {
                        // Write delete code
                        $response = ["status" => true, "code" => 200, "Message" => "You have already submit rating and review."];
                        $this->returnResponse($response);
                    } else {
                        $timenow = date('Y-m-d H:i:s');
                        $publish = 0;
                        // Write insert code
                        $sql = 'INSERT INTO ad_reviews (reviewID, productID, user_id, rating, comments, date, publish) VALUES(null, :productID, :user_id, :rating, :comments, :date, :publish)';
                        $stmt = $this->dbConn->prepare($sql);
                        $stmt->bindParam(':productID', $product_id);
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->bindParam(':rating', $rating);
                        $stmt->bindParam(':comments', $comment);
                        $stmt->bindParam(':date', $timenow);
                        $stmt->bindParam(':publish', $publish);
                        if ($stmt->execute()) {
                            $response = ["status" => true, "code" => 200, "Message" => "Review and rating successfully submitted."];
                            $this->returnResponse($response);
                        } else {
                            $response = ["status" => false, "code" => 400, "Message" => "Something is wrong."];
                            $this->returnResponse($response);
                        }
                    }
                }

            } else {
                $response = ["status" => false, "code" => 400, "Message" => "Authorization token not found."];
                $this->returnResponse($response);
            }

        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }

    }

    public function uploadFile()
    {
        try {
            $image = $this->validateParameter('image', $this->param['image'], STRING);
            $image_name = '';
            if (strlen($image) > 0) {
                $image_name = round(microtime(true) * 1000) . ".jpg";
                $image_upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/image/' . $image_name;
                $flag = file_put_contents($image_upload_dir, base64_decode($image));
                if ($flag) {
                    //Write insert db code here like given below
                    // $q = mysqli_query($conn,'insert into image');
                    // $response = ["status" => true, "code" => 200, "Message" => "Image successfully uploaded", "image_name" => $image_name];
                    // $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Something went wrong"];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "Please post image"];
                $this->returnResponse($response);
            }

        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function multipleFileUpload()
    {
        try {
            $total_count = count($_FILES['product_images']['name']);
            if ($total_count > 0) {
                $imageNameCommoSeperate = '';
                for ($i = 0; $i < $total_count; $i++) {
                    $new_name = '';
                    //The temp file path is obtained
                    $tmpFilePath = $_FILES['product_images']['tmp_name'][$i];
                    //A file path needs to be present
                    if ($tmpFilePath != "") {
                        //Setup our new file path
                        $timestamp = microtime(true);
                        $original_name = $_FILES['product_images']['name'][$i];
                        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
                        $new_name = $timestamp . '.' . $extension;

                        $newMainFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/products/' . $new_name;
                        $newThumbnailFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/products/thumb/' . $new_name;
                        if (move_uploaded_file($tmpFilePath, $newMainFilePath)) {
                            chmod($newMainFilePath, 0777);
                            copy($newMainFilePath, $newThumbnailFilePath);
                            $imageNameCommoSeperate .= $new_name . ',';
                        }
                    }
                }
            }
            echo rtrim($imageNameCommoSeperate, ",");
            // echo $imageNameCommoSeperate;
            exit;
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }
}
