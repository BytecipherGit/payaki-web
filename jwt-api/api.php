<?php
// error_reporting(E_ALL); // Report all types of errors
// ini_set('display_errors', 1); // Display errors in the output
error_reporting(0);
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

    protected $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
    protected $fcmServerKey = 'AAAAcdsalcE:APA91bE-UusISpW-YJ6QKQAjAwC6O4pCP1AAIvfsR7Dul6-JL2yGh6qAi418dCBxYqy0DvMWp67d2rLmfHZ8EQVbM0ysKbyBlQIipPoATHuyfQTKkhYw9SLtUmJ--HegoumMFGJM6lJL';

    protected $key = "BYTECIPHERPAYAKI";

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
        // $device_token = $this->validateParameter('device_token', $this->param['device_token'], STRING);
        // $device_type = $this->validateParameter('device_type', $this->param['device_type'], STRING);
        $device_token = !empty($this->param['device_token']) ? $this->param['device_token'] : '';
        $device_type = !empty($this->param['device_type']) ? $this->param['device_type'] : '';

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
            if (!empty($user['image'])) {
                $user['image'] = $this->display_image_url . 'storage/profile/' . $user['image'];
            }

            if (!empty($user['id_proof'])) {
                $user['id_proof'] = $this->display_image_url . 'storage/user_documents/id_proof/' . $user['id_proof'];
            }
            if (!empty($user['address_proof'])) {
                $user['address_proof'] = $this->display_image_url . 'storage/user_documents/address_proof/' . $user['address_proof'];
            }

            //Update Device Token & Device Type in user table
            $stmt = $this->dbConn->prepare('UPDATE ad_user SET device_token = :device_token,device_type = :device_type WHERE id = :id');
            // Bind the parameters and execute the statement
            $stmt->bindValue(':id', $user['id'], PDO::PARAM_STR);
            $stmt->bindValue(':device_token', $device_token, PDO::PARAM_STR);
            $stmt->bindValue(':device_type', $device_type, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $user['device_token'] = $device_token;
                $user['device_type'] = $device_type;
            }
            $lcuserid = base64_encode(openssl_encrypt($user['id'], 'AES-256-CBC', $this->key, 0));
            $user['chat_url'] = $this->display_image_url . "chat/mchat.php?receiverId=$lcuserid";
            $paylod = [
                'iat' => time(),
                'iss' => 'localhost',
                'exp' => time() + (14400000),
                'userId' => $user['id'],
                'name' => $user['name'],
                'address' => $user['address'],
                'phone' => $user['phone'],
                'email' => $user['email'],
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
        // $device_token = $this->validateParameter('device_token', $this->param['device_token'], STRING);
        // $device_type = $this->validateParameter('device_type', $this->param['device_type'], STRING);
        $device_token = !empty($this->param['device_token']) ? $this->param['device_token'] : '';
        $device_type = !empty($this->param['device_type']) ? $this->param['device_type'] : '';
        try {
            $phoneWithCountryCode = $countryCode . $phone;
            // $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE email =:email OR username=:username");
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE country_code =:country_code AND phone =:phone");
            $stmt->bindParam(":country_code", $countryCode);
            $stmt->bindParam(":phone", $phoneWithCountryCode);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($user['id'])) {
                $otp = mt_rand(111111, 999999);
                // Prepare the SQL UPDATE statement
                $stmt = $this->dbConn->prepare('UPDATE ad_user SET otp = :otp, device_token = :device_token, device_type = :device_type WHERE id = :id');
                // Bind the parameters and execute the statement
                $stmt->bindValue(':id', $user['id'], PDO::PARAM_STR);
                $stmt->bindValue(':otp', $otp, PDO::PARAM_STR);
                $stmt->bindValue(':device_token', $device_token, PDO::PARAM_STR);
                $stmt->bindValue(':device_type', $device_type, PDO::PARAM_STR);
                $stmt->execute();
                // Check for errors and return a response
                if ($stmt->rowCount() > 0) {
                    $response = ["status" => false, "code" => 200, "Message" => 'OTP successfully sent on your registered mobile.', "data" => ["country_code" => $countryCode, "phone" => $phone, "otp" => (string) $otp, "device_token" => $device_token, "device_type" => $device_type]];
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
            $phoneWithCountryCode = $countryCode . $phone;
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE country_code =:country_code AND phone =:phone");
            $stmt->bindParam(":country_code", $countryCode);
            $stmt->bindParam(":phone", $phoneWithCountryCode);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($user['id'])) {
                if ($user['otp'] === $otp) {
                    $paylod = [
                        'iat' => time(),
                        'iss' => 'localhost',
                        'exp' => time() + (14400000),
                        'userId' => $user['id'],
                        'name' => $user['name'],
                        'address' => $user['address'],
                        'phone' => $user['phone'],
                        'email' => $user['email'],
                    ];
                    $token = GlobalJWT::encode($paylod, SECRETE_KEY);

                    $lcuserid = base64_encode(openssl_encrypt($user['id'], 'AES-256-CBC', $this->key, 0));
                    $user['chat_url'] = $this->display_image_url . "chat/mchat.php?receiverId=$lcuserid";

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

    public function get_random_id()
    {
        $random = '';
        for ($i = 1; $i <= 8; $i++) {
            $random .= mt_rand(0, 9);
        }
        return $random;
    }

    public function register()
    {
        try {
            $siteUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $siteUrl = str_replace('/jwt-api/', '', $siteUrl);
            $confirm_id = $this->get_random_id();
            $now = date("Y-m-d H:i:s");

            $fName = $_POST['full_name'];
            $uName = $_POST['user_name'];
            $email = $_POST['email'];
            $countryCode = $_POST['country_code'];
            $phone = $_POST['country_code'] . $_POST['phone'];
            $password = $_POST['pass'];
            $id_proof_type = $_POST['id_proof_type'];
            $id_proof_number = $_POST['id_proof_number'];
            $id_proof_new_file_name = '';

            $device_token = !empty($_POST['device_token']) ? $_POST['device_token'] : '';
            $device_type = !empty($_POST['device_type']) ? $_POST['device_type'] : '';

            // $address_proof_type = $_POST['address_proof_type'];
            // $address_proof_number = $_POST['address_proof_number'];
            // $address_proof_new_file_name = '';

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
            $phoneWithCountryCode = $countryCode . $phone;
            $check_email = "SELECT `email` FROM `ad_user` WHERE `email`=:email OR `phone`=:phone";
            $check_email_stmt = $this->dbConn->prepare($check_email);
            $check_email_stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $check_email_stmt->bindValue(':phone', $phoneWithCountryCode, PDO::PARAM_STR);
            $check_email_stmt->execute();

            if ($check_email_stmt->rowCount()):
                $response = ["status" => false, "code" => 400, "Message" => "User already exist with this email or mobile."];
                $this->returnResponse($response);

            else:
                $otp = mt_rand(111111, 999999);
                $insert_query = "INSERT INTO `ad_user` (`username`,`name`,`email`,`confirm`,`created_at`,`updated_at`,`country_code`,`phone`,`status`,`password_hash`,`otp`,`id_proof_type`,`id_proof_number`,`id_proof`,`device_token`,`device_type`) VALUES(:username,:name,:email,:confirm,:created_at,:updated_at,:country_code,:phone,:status,:password_hash,:otp,:id_proof_type,:id_proof_number,:id_proof,:device_token,:device_type)";
                $insert_stmt = $this->dbConn->prepare($insert_query);
                // DATA BINDING
                $insert_stmt->bindValue(':username', htmlspecialchars(strip_tags($uName)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':name', htmlspecialchars(strip_tags($fName)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $insert_stmt->bindValue(':confirm', $confirm_id, PDO::PARAM_STR);
                $insert_stmt->bindValue(':created_at', $now, PDO::PARAM_STR);
                $insert_stmt->bindValue(':updated_at', $now, PDO::PARAM_STR);
                $insert_stmt->bindValue(':country_code', $countryCode, PDO::PARAM_STR);
                $insert_stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
                $insert_stmt->bindValue(':status', 0, PDO::PARAM_STR);
                $insert_stmt->bindValue(':password_hash', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                $insert_stmt->bindValue(':otp', $otp, PDO::PARAM_STR);
                $insert_stmt->bindValue(':id_proof_type', $id_proof_type, PDO::PARAM_STR);
                $insert_stmt->bindValue(':id_proof_number', $id_proof_number, PDO::PARAM_STR);
                $insert_stmt->bindValue(':id_proof', $id_proof_new_file_name, PDO::PARAM_STR);
                // $insert_stmt->bindValue(':address_proof_type', $address_proof_type, PDO::PARAM_STR);
                // $insert_stmt->bindValue(':address_proof_number', $address_proof_number, PDO::PARAM_STR);
                // $insert_stmt->bindValue(':address_proof', $address_proof_new_file_name, PDO::PARAM_STR);
                $insert_stmt->bindValue(':device_token', $device_token, PDO::PARAM_STR);
                $insert_stmt->bindValue(':device_type', $device_type, PDO::PARAM_STR);
                $insert_stmt->execute();
                $subject = 'Plese verify OTP';
                $body = 'Your verification OTP is ' . $otp;
                $this->sendMail($email, $subject, $body);

                // Get the last insert ID
                $user_id = $this->dbConn->lastInsertId();
                // Select the last insert row
                $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE id=:id");
                $stmt->bindParam(':id', $user_id);
                $stmt->execute();
                // Fetch the row
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $paylod = [
                    'iat' => time(),
                    'iss' => 'localhost',
                    'exp' => time() + (14400000),
                    'userId' => $user_id,
                    'name' => $user['name'],
                    'address' => $user['address'],
                    'phone' => $user['phone'],
                    'email' => $user['email'],
                ];
                $token = GlobalJWT::encode($paylod, SECRETE_KEY);

                $lcuserid = base64_encode(openssl_encrypt($user['id'], 'AES-256-CBC', $this->key, 0));
                $user['chat_url'] = $this->display_image_url . "chat/mchat.php?receiverId=$lcuserid";

                /*SEND CONFIRMATION EMAIL*/

                $subject = 'Payaki - Email Confirmation';
                $body = '<p>Greetings from Payaki Team!</p>
						                        <p>Thanks for registering with Payaki. We are thrilled to have you as a registered member and
						                        hope that you find our service beneficial. Before we get you started please activate your account by clicking on the link below</p>
						                        <p><a href="' . $siteUrl . '/signup?confirm=' . $confirm_id . '&amp;user=' . $user_id . '" target="_other" rel="nofollow">' . $siteUrl . '/signup?confirm=' . $confirm_id . '&amp;user=' . $user_id . '</a
						                        ></p><p>After your Account activation you will have Post Ad, Chat with sellers and more. Once you have your Profile filled in you are ready togo.</p><p>Have further questions? You can find answers in our FAQ Section at</p>
						                        <p><a href="' . $siteUrl . '/contact" target="_other" rel="nofollow" >' . $siteUrl . '/contact</a></p>Sincerely,<br /><br />Payaki Team!<br />
						                        <a href="' . $siteUrl . '" target="_other" rel="nofollow">' . $siteUrl . '</a>';
                $this->sendMail($email, $subject, $body);

                $response = ["status" => true, "code" => 200, "Message" => "We have sent confirmation email to your registred email. Please verify it. ", "token" => $token, "data" => $user, "otp" => $otp];

                $this->returnResponse($response);
            endif;
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function resendConfirmationEmail()
    {
        $email = $this->validateParameter('email', $this->param['email'], STRING);
        try {
            $siteUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $siteUrl = str_replace('/jwt-api/', '', $siteUrl);
            // Select the last insert row
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE email=:email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            // Fetch the row
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!empty($user['confirm']) && !empty($user['id'])) {
                /* Re SEND CONFIRMATION EMAIL*/
                $subject = 'Payaki - Email Confirmation';
                $body = '<p>Greetings from Payaki Team!</p>
                    <p>Thanks for registering with Payaki. We are thrilled to have you as a registered member and
                    hope that you find our service beneficial. Before we get you started please activate your account by clicking on the link below</p>
                    <p><a href="' . $siteUrl . '/signup?confirm=' . $user['confirm'] . '&amp;user=' . $user['id'] . '" target="_other" rel="nofollow">' . $siteUrl . '/signup?confirm=' . $user['confirm'] . '&amp;user=' . $user['id'] . '</a
                    ></p><p>After your Account activation you will have Post Ad, Chat with sellers and more. Once you have your Profile filled in you are ready togo.</p><p>Have further questions? You can find answers in our FAQ Section at</p>
                    <p><a href="' . $siteUrl . '/contact" target="_other" rel="nofollow" >' . $siteUrl . '/contact</a></p>Sincerely,<br /><br />Payaki Team!<br />
                    <a href="' . $siteUrl . '" target="_other" rel="nofollow">' . $siteUrl . '</a>';
                $this->sendMail($email, $subject, $body);
                $response = ["status" => true, "code" => 200, "Message" => "Confirmation email successfully resend."];
                $this->returnResponse($response);
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "Something went wrong"];
                $this->returnResponse($response);
            }

        } catch (Exception $e) {
            $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
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
            if (!empty($mobile) && !empty($countryCode)) {
                $phone = $countryCode . $mobile;
                $getuser = "SELECT `id` FROM `ad_user` WHERE `phone`=:phone";
                $userData = $this->dbConn->prepare($getuser);
                // $userData->bindValue(':country_code', $countryCode, PDO::PARAM_STR);
                $userData->bindValue(':phone', $phone, PDO::PARAM_STR);
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

    public function viewProfile()
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
                if (!empty($userData['id'])) {
                    $userData['image'] = $this->display_image_url . 'storage/profile/' . $userData['image'];
                    $response = [
                        "status" => true,
                        "code" => 200,
                        "Message" => "User profile successfully fetched.",
                        "data" => $userData,
                    ];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                    $this->returnResponse($response);
                }

            } else {
                return false;
            }
        }
    }

    public function updateProfile()
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
                if (!empty($userData['id'])) {
                    // $name = $this->validateParameter('name', $this->param['name'], STRING);
                    // $address = $this->validateParameter('address', $this->param['address'], STRING);
                    // $country = $this->validateParameter('country', $this->param['country'], STRING);
                    // $website = $this->validateParameter('website', $this->param['website'], STRING);
                    // $facebook = $this->validateParameter('facebook', $this->param['facebook'], STRING);
                    // $twitter = $this->validateParameter('twitter', $this->param['twitter'], STRING);
                    // $googleplus = $this->validateParameter('googleplus', $this->param['googleplus'], STRING);
                    // $instagram = $this->validateParameter('instagram', $this->param['instagram'], STRING);
                    // $linkedin = $this->validateParameter('linkedin', $this->param['linkedin'], STRING);
                    // $youtube = $this->validateParameter('youtube', $this->param['youtube'], STRING);

                    //Upload Profile Image
                    $avatar_file_name = '';
                    if (isset($_FILES['avatar'])) {
                        $avatar_name = $_FILES['avatar']['name'];
                        $avatar_tmp = $_FILES['avatar']['tmp_name'];
                        if ($avatar_tmp != '') {
                            $extension = pathinfo($avatar_name, PATHINFO_EXTENSION);
                            $avatar_file_name = microtime(true) . '.' . $extension;
                            $avatarNewFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/profile/' . $avatar_file_name;
                            move_uploaded_file($avatar_tmp, $avatarNewFilePath);
                        }
                    }
                    $name = !empty($_POST['username']) ? $_POST['username'] : $userData['name'];
                    $avatar = !empty($avatar_file_name) ? $avatar_file_name : $userData['image'];
                    $address = !empty($_POST['address']) ? $_POST['address'] : $userData['address'];
                    $country = !empty($_POST['country']) ? $_POST['country'] : $userData['country'];
                    $description = !empty($_POST['description']) ? $_POST['description'] : $userData['description'];
                    $website = !empty($_POST['website']) ? $_POST['website'] : $userData['website'];
                    $facebook = !empty($_POST['facebook']) ? $_POST['facebook'] : $userData['facebook'];
                    $twitter = !empty($_POST['twitter']) ? $_POST['twitter'] : $userData['twitter'];
                    $googleplus = !empty($_POST['googleplus']) ? $_POST['googleplus'] : $userData['googleplus'];
                    $instagram = !empty($_POST['instagram']) ? $_POST['instagram'] : $userData['instagram'];
                    $linkedin = !empty($_POST['linkedin']) ? $_POST['linkedin'] : $userData['linkedin'];
                    $youtube = !empty($_POST['youtube']) ? $_POST['youtube'] : $userData['youtube'];

                    //Update
                    // Prepare the SQL UPDATE statement
                    $stmt = $this->dbConn->prepare('UPDATE ad_user SET name = :name,image = :image, address = :address, country = :country, description = :description, website = :website, facebook = :facebook, twitter = :twitter, googleplus = :googleplus, instagram = :instagram, linkedin = :linkedin, youtube = :youtube WHERE id = :id');

                    // Bind the parameters and execute the statement
                    $stmt->bindValue(':id', $userData['id'], PDO::PARAM_STR);
                    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                    $stmt->bindValue(':image', $avatar, PDO::PARAM_STR);
                    $stmt->bindValue(':address', $address, PDO::PARAM_STR);
                    $stmt->bindValue(':country', $country, PDO::PARAM_STR);
                    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
                    $stmt->bindValue(':website', $website, PDO::PARAM_STR);
                    $stmt->bindValue(':facebook', $facebook, PDO::PARAM_STR);
                    $stmt->bindValue(':twitter', $twitter, PDO::PARAM_STR);
                    $stmt->bindValue(':googleplus', $googleplus, PDO::PARAM_STR);
                    $stmt->bindValue(':instagram', $instagram, PDO::PARAM_STR);
                    $stmt->bindValue(':linkedin', $linkedin, PDO::PARAM_STR);
                    $stmt->bindValue(':youtube', $youtube, PDO::PARAM_STR);
                    if ($stmt->execute()) {
                        // $response = ["status" => true, "code" => 200, "Message" => "User profile successfully updated.", "data" => $userData];
                        $response = ["status" => true, "code" => 200, "Message" => "User profile successfully updated."];
                        $this->returnResponse($response);
                    } else {
                        $response = ["status" => false, "code" => 400, "Message" => "Something went wrong."];
                        $this->returnResponse($response);
                    }
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                    $this->returnResponse($response);
                }

            } else {
                return false;
            }
        }
    }

    public function changePassword()
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
                if (!empty($userData['id'])) {
                    // $current_password = $this->validateParameter('current_password', $this->param['current_password'], STRING);
                    $new_password = $this->validateParameter('new_password', $this->param['new_password'], STRING);
                    $confirm_password = $this->validateParameter('confirm_password', $this->param['confirm_password'], STRING);
                    //Check current password is valid or not
                    // if (password_verify($current_password, $userData['password_hash'])) {
                    if ($new_password == $confirm_password) {
                        //Update New password
                        // Prepare the SQL UPDATE statement
                        $stmt = $this->dbConn->prepare('UPDATE ad_user SET password_hash = :password_hash WHERE id = :id');
                        // Bind the parameters and execute the statement
                        $stmt->bindValue(':id', $userData['id'], PDO::PARAM_STR);
                        $stmt->bindValue(':password_hash', password_hash($new_password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                        if ($stmt->execute()) {
                            $response = ["status" => true, "code" => 200, "Message" => "Password successfully updated."];
                            $this->returnResponse($response);
                        } else {
                            $response = ["status" => false, "code" => 400, "Message" => "Something went wrong."];
                            $this->returnResponse($response);
                        }
                    } else {
                        $response = ["status" => false, "code" => 400, "Message" => "New password & confirm password does not match."];
                        $this->returnResponse($response);
                    }

                    // } else {
                    //     $response = ["status" => false, "code" => 400, "Message" => "Current password does not matched."];
                    //     $this->returnResponse($response);
                    // }
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
        // $device_token = $this->validateParameter('device_token', $this->param['device_token'], STRING);
        // $device_type = $this->validateParameter('device_type', $this->param['device_type'], STRING);
        $device_token = !empty($this->param['device_token']) ? $this->param['device_token'] : '';
        $device_type = !empty($this->param['device_type']) ? $this->param['device_type'] : '';

        try {
            if (!empty($this->param['email'])) {
                $email = $this->param['email'];
            } else {
                $email = '';
            }

            $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE oauth_provider =:oauth_provider AND oauth_uid=:oauth_uid");
            $stmt->bindParam(":oauth_provider", $oauthProvider);
            $stmt->bindParam(":oauth_uid", $oauthUid);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($user)) {
                //Update
                // Prepare the SQL UPDATE statement
                $stmt = $this->dbConn->prepare('UPDATE ad_user SET oauth_provider = :oauth_provider, oauth_uid = :oauth_uid, email = :email, device_token = :device_token, device_type = :device_type WHERE id = :id');

                // Bind the parameters and execute the statement
                $stmt->bindValue(':id', $user['id'], PDO::PARAM_STR);
                $stmt->bindValue(':oauth_provider', $oauthProvider, PDO::PARAM_STR);
                $stmt->bindValue(':oauth_uid', $oauthUid, PDO::PARAM_STR);
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $stmt->bindValue(':device_token', $device_token, PDO::PARAM_STR);
                $stmt->bindValue(':device_type', $device_type, PDO::PARAM_STR);
                $stmt->execute();

                // Select the last insert row
                $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE id=:id");
                $stmt->bindParam(':id', $user['id']);
                $stmt->execute();
                // Fetch the row
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                $lcuserid = base64_encode(openssl_encrypt($user['id'], 'AES-256-CBC', $this->key, 0));
                $user['chat_url'] = $this->display_image_url . "chat/mchat.php?receiverId=$lcuserid";

                $paylod = [
                    'iat' => time(),
                    'iss' => 'localhost',
                    'exp' => time() + (14400000),
                    'userId' => $user['id'],
                    'name' => $user['name'],
                    'address' => $user['address'],
                    'phone' => $user['phone'],
                    'email' => $user['email'],
                ];
                $token = GlobalJWT::encode($paylod, SECRETE_KEY);

                $response = ["status" => true, "code" => 200, "Message" => "Login successfully.", "token" => $token, "data" => $user];
                $this->returnResponse($response);
            } else {
                //Create
                $insert_query = "INSERT INTO `ad_user` (`oauth_provider`,`oauth_uid`,`email`) VALUES(:oauth_provider,:oauth_uid,:email)";
                $stmt = $this->dbConn->prepare($insert_query);
                // DATA BINDING
                $stmt->bindValue(':oauth_provider', $oauthProvider, PDO::PARAM_STR);
                $stmt->bindValue(':oauth_uid', $oauthUid, PDO::PARAM_STR);
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);

                // $stmt->bindValue(':username', htmlspecialchars(strip_tags($uName)), PDO::PARAM_STR);
                // $stmt->bindValue(':name', htmlspecialchars(strip_tags($fName)), PDO::PARAM_STR);
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
                $paylod = [
                    'iat' => time(),
                    'iss' => 'localhost',
                    'exp' => time() + (14400000),
                    'userId' => $last_id,
                    'name' => $user['name'],
                    'address' => $user['address'],
                    'phone' => $user['phone'],
                    'email' => $user['email'],
                ];
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
                $seller_name = $_POST['seller_name'];
                $productName = $_POST['product_name'];

                if (!empty($productName)) {
                    $slug = $this->createSlug($productName);
                } else {
                    $slug = '';
                }
                $description = $_POST['description'];
                $category = $_POST['category'];
                $subCategory = $_POST['sub_category'];
                $price = isset($_POST['price']) ? $_POST['price'] : 0;
                $negotiable = isset($_POST['negotiable']) ? $_POST['negotiable'] : 0;
                $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
                $hidePhone = isset($_POST['hide_phone']) ? $_POST['hide_phone'] : 0;
                $location = isset($_POST['location']) ? $_POST['location'] : '';
                $city = isset($_POST['city']) ? $_POST['city'] : '';
                $country = isset($_POST['country']) ? $_POST['country'] : '';
                $latlong = isset($_POST['latlong']) ? $_POST['latlong'] : '';
                $state = isset($_POST['state']) ? $_POST['state'] : '';
                $tag = isset($_POST['tag']) ? $_POST['tag'] : '';
                $view = isset($_POST['view']) ? $_POST['view'] : 0;
                $event_date = $_POST['event_date'];
                $event_time = $_POST['event_time'];
                // $expire_date = $_POST['expire_date'];
                // $featured_exp_date = $_POST['featured_exp_date'];
                // $urgent_exp_date = $_POST['urgent_exp_date'];
                // $highlight_exp_date = $_POST['highlight_exp_date'];
                $adminSeen = isset($_POST['admin_seen']) ? $_POST['admin_seen'] : 0;
                $emailed = isset($_POST['emailed']) ? $_POST['emailed'] : 0;
                $hide = isset($_POST['hide']) ? $_POST['hide'] : 0;
                $expire_days = isset($_POST['available_days']) ? $_POST['available_days'] : 7;

                if ($category == 9) {
                    $postType = 'training';
                } else if ($category == 10) {
                    $postType = 'event';
                } else {
                    $postType = 'other';
                }

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
                $ad_duration = 7;
                $timenow = date('Y-m-d H:i:s');
                $expire_time = date('Y-m-d H:i:s', strtotime($timenow . ' +' . $ad_duration . ' day'));
                $expire_timestamp = strtotime($expire_time);
                $expired_date = date('Y-m-d H:i:s', strtotime($timenow . ' +' . $expire_days . ' day'));
                $promoVideoFileName = '';
                if (isset($_FILES["promo_video"]) && ($category == 9 || $category == 10)) {
                    // Define the target directory for storing video files
                    $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/training_video/';
                    // Create the target directory if it doesn't exist
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    $allowedExtensions = ["mp4", "avi", "mov", "mkv"];
                    $maxSizeMB = (int) $_POST["max_size"];

                    // Check if the file has no errors
                    if ($_FILES["promo_video"]["error"] === UPLOAD_ERR_OK) {
                        // Validate file size
                        $maxSizeBytes = $maxSizeMB * 1024 * 1024; // Convert MB to bytes
                        if ($_FILES["promo_video"]["size"] <= $maxSizeBytes) {
                            // Validate file extension
                            $fileExtension = strtolower(pathinfo($_FILES["promo_video"]["name"], PATHINFO_EXTENSION));
                            if (in_array($fileExtension, $allowedExtensions)) {
                                $trainingPromoVideoFileName = $_FILES['promo_video']['name'];
                                $trainingPromoVideoTempFileName = $_FILES['promo_video']['tmp_name'];
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
                $sql = 'INSERT INTO ad_product (id, status, user_id, featured, urgent, highlight, seller_name, product_name, slug, description, category, sub_category, post_type, event_date, event_time, price, negotiable, phone, hide_phone, location, city, state, country, latlong, screen_shot, promo_video, tag, view, created_at, updated_at, expire_days, expired_date, expire_date, featured_exp_date, urgent_exp_date, highlight_exp_date, admin_seen, emailed, hide) VALUES(null, :status, :user_id, :featured, :urgent, :highlight, :seller_name, :product_name, :slug, :description, :category, :sub_category, :post_type, :event_date, :event_time, :price, :negotiable, :phone, :hide_phone, :location, :city, :state, :country, :latlong, :screen_shot, :promo_video, :tag, :view, :created_at, :updated_at, :expire_days, :expired_date, :expire_date, :featured_exp_date, :urgent_exp_date, :highlight_exp_date, :admin_seen, :emailed, :hide)';
                if ($category == 9 || $category == 10) {
                    $status = 'active';
                } else {
                    $status = 'pending';
                }
                $createdDate = date('Y-m-d H:i:s');
                $featuredExpDate = null;
                $stmt = $this->dbConn->prepare($sql);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':featured', $featured);
                $stmt->bindParam(':urgent', $urgent);
                $stmt->bindParam(':highlight', $highlight);
                $stmt->bindParam(':seller_name', $seller_name);
                $stmt->bindParam(':product_name', $productName);
                $stmt->bindParam(':slug', $slug);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':category', $category);
                $stmt->bindParam(':sub_category', $subCategory);
                $stmt->bindParam(':event_date', $event_date);
                $stmt->bindParam(':event_time', $event_time);
                $stmt->bindParam(':post_type', $postType);
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
                $stmt->bindParam(':promo_video', $promoVideoFileName);
                $stmt->bindParam(':tag', $tag);
                $stmt->bindParam(':view', $view);
                $stmt->bindParam(':created_at', $createdDate);
                $stmt->bindParam(':updated_at', $createdDate);
                $stmt->bindParam(':expire_days', $expire_days);
                $stmt->bindParam(':expired_date', $expired_date);
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
                    //Send Custom Notification to user
                    if (!empty($last_id)) {
                        //Insert record into event table
                        if (!empty($_POST['events']) && $category == 10) {
                            foreach (json_decode($_POST['events']) as $key => $event) {
                                $ticket_type = $event->ticket_title;
                                $ticket_price = $event->ticket_price;
                                $available_quantity = $event->ticket_quantity;
                                $remaining_quantity = $event->ticket_quantity;
                                $selling_mode = $event->selling_mode;
                                $created_at = date("Y-m-d H:i:s");

                                //Insert code here
                                $sql = 'INSERT INTO ad_product_event_types (id, product_id, ticket_type, ticket_price, available_quantity, remaining_quantity, selling_mode, created_at) VALUES(null, :product_id, :ticket_type, :ticket_price, :available_quantity, :remaining_quantity, :selling_mode, :created_at)';
                                $stmt = $this->dbConn->prepare($sql);
                                $stmt->bindParam(':product_id', $last_id);
                                $stmt->bindParam(':ticket_type', $ticket_type);
                                $stmt->bindParam(':ticket_price', $ticket_price);
                                $stmt->bindParam(':available_quantity', $available_quantity);
                                $stmt->bindParam(':remaining_quantity', $remaining_quantity);
                                $stmt->bindParam(':selling_mode', $selling_mode);
                                $stmt->bindParam(':created_at', $created_at);
                                $stmt->execute();
                            }
                        }

                        // if (validate_input($_POST['catid']) == 9) {
                        //     // Check if files were uploaded
                        //     if (isset($_FILES['trainingVideo'])) {
                        //         $video = '';
                        //         // Define the target directory for storing video files
                        //         $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/training_video/';
                        //         // Create the target directory if it doesn't exist
                        //         if (!file_exists($targetDir)) {
                        //             mkdir($targetDir, 0777, true);
                        //         }
                        //         $countTrainingVidoe = 0;
                        //         // Loop through the uploaded files
                        //         foreach ($_FILES['trainingVideo']['tmp_name'] as $key => $tmp_name) {
                        //             $trainingVideoFileName = $_FILES['trainingVideo']['name'][$key];
                        //             $trainingVideoTempFileName = $_FILES['trainingVideo']['tmp_name'][$key];
                        //             if ($trainingVideoTempFileName != '') {
                        //                 $extension = pathinfo($trainingVideoFileName, PATHINFO_EXTENSION);
                        //                 $trainingVideoNewFileName = microtime(true) . '.' . $extension;
                        //                 if (!empty($trainingVideoNewFileName)) {
                        //                     if ($countTrainingVidoe == 0) {
                        //                         $video = $trainingVideoNewFileName;
                        //                     } elseif ($countTrainingVidoe >= 1) {
                        //                         $video = $video . "," . $trainingVideoNewFileName;
                        //                     }
                        //                 }
                        //                 $trainingVideoFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/training_video/' . $id_proof_new_file_name;
                        //                 move_uploaded_file($trainingVideoTempFileName, $trainingVideoFilePath);
                        //                 $countTrainingVidoe++;
                        //             }
                        //         }

                        //     }
                        //     //Insert record in Training Gallery
                        //     $tGInsert = ORM::for_table($config['db']['pre'] . 'training_gallery')->create();
                        //     $tGInsert->product_id = $product_id;
                        //     $tGInsert->training_video = $video;
                        //     $tGInsert->save();
                        // } else if (validate_input($_POST['catid']) == 10) {
                        //     //Write Insert Event code here
                        // }

                        $notification_id = $last_id;
                        $title = $productName;
                        //Fetch all active user who's status = 1
                        $status = '1';
                        $getUsers = "SELECT id FROM ad_user WHERE id != :userId and status != '0'";
                        $getUserData = $this->dbConn->prepare($getUsers);
                        $getUserData->bindValue(':userId', $userId, PDO::PARAM_STR);
                        $getUserData->execute();
                        // echo "Last executed query: " . $getUserData->queryString;
                        // exit;
                        $getUserData = $getUserData->fetchAll(PDO::FETCH_ASSOC);
                        if (count($getUserData) > 0) {
                            foreach ($getUserData as $key => $user) {
                                $notificationSql = 'INSERT INTO ad_custom_notification (id, notification_id, type, title, redirect_url, user_id, status, created_at) VALUES(null, :notification_id, :type, :title, :redirect_url, :user_id, :status, :created_at)';
                                $type = 'post';
                                $user_id = $user['id'];
                                $nStatus = 0;
                                $createdDate = date('Y-m-d H:i:s');
                                $redirect_url = $this->display_image_url . 'ad/' . $last_id . '/' . $slug;
                                $notifivationStmt = $this->dbConn->prepare($notificationSql);
                                $notifivationStmt->bindParam(':notification_id', $notification_id);
                                $notifivationStmt->bindParam(':type', $type);
                                $notifivationStmt->bindParam(':title', $title);
                                $notifivationStmt->bindParam(':redirect_url', $redirect_url);
                                $notifivationStmt->bindParam(':user_id', $user_id);
                                $notifivationStmt->bindParam(':status', $nStatus);
                                $notifivationStmt->bindParam(':created_at', $createdDate);
                                $notifivationStmt->execute();

                            }
                        }
                    }

                    // Select the last insert row
                    $stmt = $this->dbConn->prepare("SELECT * FROM ad_product WHERE id=:id");
                    $stmt->bindParam(':id', $last_id);
                    $stmt->execute();
                    // Fetch the row
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!empty($product)) {
                        //Send Push notification to all user except logged in user id in request
                        //Get Latest Post
                        $getUsers = "SELECT device_token FROM ad_user WHERE device_token !='' AND id != :userId";
                        $getUserData = $this->dbConn->prepare($getUsers);
                        $getUserData->bindValue(':userId', $userId, PDO::PARAM_STR);
                        $getUserData->execute();
                        // echo "Last executed query: " . $getUserData->queryString;
                        // exit;
                        $getUserData = $getUserData->fetchAll(PDO::FETCH_ASSOC);
                        if (count($getUserData) > 0) {
                            foreach ($getUserData as $key => $user) {
                                $title = 'New product ' . $productName . 'has been posted on payaki';
                                $message = $this->display_image_url . 'ad/' . $product['id'] . '/' . $product['slug'];
                                $deviceToken = $user['device_token'];
                                $this->pushNotificationForApp($deviceToken, $title, $message);
                            }
                        }

                    }

                    // Add record into transaction table
                    if ($featured == 1 || $urgent == 1 || $highlight == 1) {
                        $productName = !empty($productName) ? $productName : '';
                        $productId = !empty($last_id) ? $last_id : '';
                        $userId = !empty($userId) ? $userId : '';
                        $amount = !empty($_POST['amount']) ? $_POST['amount'] : '';
                        $currencyCode = !empty($_POST['currency']) ? $_POST['currency'] : 'USD';
                        $baseAmount = !empty($_POST['amount']) ? $_POST['amount'] : '';
                        $transactionTime = time();
                        // Status should be enum('pending', 'success', 'failed', 'cancel')
                        $status = !empty($_POST['status']) ? $_POST['status'] : '';
                        $paymentId = !empty($_POST['paymentId']) ? $_POST['paymentId'] : '';
                        $paymentGatway = !empty($_POST['payment_method']) ? $_POST['payment_method'] : 'paypal';
                        $transactionIpAddress = !empty($_POST['transaction_ip_address']) ? $_POST['transaction_ip_address'] : null;

                        // Package Featured Urgent Highlight
                        $transactionDescription = 'Package';
                        if ($featured == 1) {
                            $transactionDescription .= ' Featured';
                        }
                        if ($urgent == 1) {
                            $transactionDescription .= ' Urgent';
                        }
                        if ($highlight == 1) {
                            $transactionDescription .= ' Highlight';
                        }

                        // $transactionDescription = !empty($_POST['transaction_description']) ? $_POST['transaction_description'] : '';
                        // Premium Ad
                        $transactionMethod = 'Premium Ad';
                        // Frequency enum('MONTHLY', 'YEARLY', 'LIFETIME')
                        // $frequency = !empty($_POST['frequency']) ? $_POST['frequency'] : null;
                        $frequency = null;

                        $billing = array(
                            'type' => $this->getUserOptions($userId, 'billing_details_type'),
                            'tax_id' => $this->getUserOptions($userId, 'billing_tax_id'),
                            'name' => $this->getUserOptions($userId, 'billing_name'),
                            'address' => $this->getUserOptions($userId, 'billing_address'),
                            'city' => $this->getUserOptions($userId, 'billing_city'),
                            'state' => $this->getUserOptions($userId, 'billing_state'),
                            'zipcode' => $this->getUserOptions($userId, 'billing_zipcode'),
                            'country' => $this->getUserOptions($userId, 'billing_country'),
                        );
                        $billing = !empty($billing) ? json_encode($billing) : '';
                        // $taxesIds = !empty($_POST['taxes_ids']) ? $_POST['taxes_ids'] : '';
                        $taxesIds = null;

                        $insert_query = "INSERT INTO `ad_transaction` (`product_name`,`product_id`,`seller_id`,`amount`,`currency_code`,`base_amount`,`featured`,`urgent`,`highlight`,`transaction_time`,`status`,`payment_id`,`transaction_gatway`,`transaction_ip`,`transaction_description`,`transaction_method`,`frequency`,`billing`,`taxes_ids`) VALUES(:product_name,:product_id,:seller_id,:amount,:currency_code,:base_amount,:featured,:urgent,:highlight,:transaction_time,:status,:payment_id,:transaction_gatway,:transaction_ip,:transaction_description,:transaction_method,:frequency,:billing,:taxes_ids)";
                        $stmt = $this->dbConn->prepare($insert_query);
                        $stmt->bindValue(':product_name', $productName, PDO::PARAM_STR);
                        $stmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
                        $stmt->bindValue(':seller_id', $userId, PDO::PARAM_STR);
                        $stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
                        $stmt->bindValue(':currency_code', $currencyCode, PDO::PARAM_STR);
                        $stmt->bindValue(':base_amount', $baseAmount, PDO::PARAM_STR);
                        $stmt->bindValue(':featured', $featured, PDO::PARAM_STR);
                        $stmt->bindValue(':urgent', $urgent, PDO::PARAM_STR);
                        $stmt->bindValue(':highlight', $highlight, PDO::PARAM_STR);
                        $stmt->bindValue(':transaction_time', $transactionTime, PDO::PARAM_STR);
                        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
                        $stmt->bindValue(':payment_id', $paymentId, PDO::PARAM_STR);
                        $stmt->bindValue(':transaction_gatway', $paymentGatway, PDO::PARAM_STR);
                        $stmt->bindValue(':transaction_ip', $transactionIpAddress, PDO::PARAM_STR);
                        $stmt->bindValue(':transaction_description', $transactionDescription, PDO::PARAM_STR);
                        $stmt->bindValue(':transaction_method', $transactionMethod, PDO::PARAM_STR);
                        $stmt->bindValue(':frequency', $frequency, PDO::PARAM_STR);
                        $stmt->bindValue(':billing', $billing, PDO::PARAM_STR);
                        $stmt->bindValue(':taxes_ids', $taxesIds, PDO::PARAM_STR);
                        $stmt->execute();
                    }

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

    public function addTrainingVideo()
    {
        try {
            $productId = $_POST['product_id'];
            if (!empty($productId) && !empty($_POST["max_size"]) && isset($_FILES["trainingVideo"])) {
                // Define the target directory for storing video files
                $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/training_video/';
                // Create the target directory if it doesn't exist
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $allowedExtensions = ["mp4", "avi", "mov", "mkv"];
                $maxSizeMB = (int) $_POST["max_size"];

                // Check if the file has no errors
                if ($_FILES["trainingVideo"]["error"] === UPLOAD_ERR_OK) {
                    // Validate file size
                    $maxSizeBytes = $maxSizeMB * 1024 * 1024; // Convert MB to bytes
                    if ($_FILES["trainingVideo"]["size"] <= $maxSizeBytes) {
                        // Validate file extension
                        $fileExtension = strtolower(pathinfo($_FILES["trainingVideo"]["name"], PATHINFO_EXTENSION));
                        if (in_array($fileExtension, $allowedExtensions)) {
                            $trainingVideoFileName = $_FILES['trainingVideo']['name'];
                            $trainingVideoTempFileName = $_FILES['trainingVideo']['tmp_name'];
                            if ($trainingVideoTempFileName != '') {
                                $extension = pathinfo($trainingVideoFileName, PATHINFO_EXTENSION);
                                $trainingVideoNewFileName = microtime(true) . '.' . $extension;
                                if (!empty($trainingVideoNewFileName)) {
                                    $trainingVideoFilePath = $_SERVER['DOCUMENT_ROOT'] . '/payaki-web/storage/training_video/' . $trainingVideoNewFileName;
                                    if (move_uploaded_file($trainingVideoTempFileName, $trainingVideoFilePath)) {
                                        //Insert record in Training Gallery
                                        $sql = 'INSERT INTO ad_training_gallery (id, product_id, training_video) VALUES(null, :product_id, :training_video)';
                                        $stmt = $this->dbConn->prepare($sql);
                                        $stmt->bindParam(':product_id', $productId);
                                        $stmt->bindParam(':training_video', $trainingVideoNewFileName);
                                        if ($stmt->execute()) {
                                            $response = ["status" => true, "code" => 200, "Message" => "Training video successfuly uploaded."];
                                            $this->returnResponse($response);
                                        } else {
                                            $response = ["status" => false, "code" => 400, "Message" => "Something went wrong"];
                                            $this->returnResponse($response);
                                        }
                                    } else {
                                        $response = ["status" => false, "code" => 400, "Message" => "Error moving the uploaded file."];
                                        $this->returnResponse($response);
                                    }
                                }
                            }
                        } else {
                            $response = ["status" => false, "code" => 400, "Message" => "Invalid file extension. Allowed extensions: " . implode(", ", $allowedExtensions)];
                            $this->returnResponse($response);
                        }
                    } else {
                        $response = ["status" => false, "code" => 400, "Message" => "File size exceeds the maximum allowed size of {$maxSizeMB} MB."];
                        $this->returnResponse($response);
                    }
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Error uploading the file."];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "productId required"];
                $this->returnResponse($response);
            }
        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }

    public function addAndUpdateEvent()
    {
        try {
            if (!empty($this->param['events']) && !empty($this->param['product_id'])) {
                $product_id = $this->param['product_id'];
                foreach ($this->param['events'] as $key => $event) {
                    $ticket_type = $event['ticket_title'];
                    $ticket_price = $event['ticket_price'];
                    $available_quantity = $event['ticket_quantity'];
                    $remaining_quantity = $event['ticket_quantity'];
                    $selling_mode = $event['selling_mode'];
                    $created_at = date("Y-m-d H:i:s");
                    if (!empty($event['id']) && $event['id'] != null) {
                        //Update code
                        $stmt = $this->dbConn->prepare('UPDATE ad_product_event_types SET ticket_type = :ticket_type,ticket_price = :ticket_price,available_quantity = :available_quantity,selling_mode = :selling_mode WHERE id = :id');
                        // Bind the parameters and execute the statement
                        $stmt->bindValue(':id', $event['id'], PDO::PARAM_STR);
                        $stmt->bindValue(':ticket_type', $ticket_type, PDO::PARAM_STR);
                        $stmt->bindValue(':ticket_price', $ticket_price, PDO::PARAM_STR);
                        $stmt->bindValue(':available_quantity', $available_quantity, PDO::PARAM_STR);
                        $stmt->bindValue(':selling_mode', $selling_mode, PDO::PARAM_STR);
                        $stmt->execute();
                    } else {
                        //Insert code here
                        $sql = 'INSERT INTO ad_product_event_types (id, product_id, ticket_type, ticket_price, available_quantity, remaining_quantity, selling_mode, created_at) VALUES(null, :product_id, :ticket_type, :ticket_price, :available_quantity, :remaining_quantity, :selling_mode, :created_at)';
                        $stmt = $this->dbConn->prepare($sql);
                        $stmt->bindParam(':product_id', $product_id);
                        $stmt->bindParam(':ticket_type', $ticket_type);
                        $stmt->bindParam(':ticket_price', $ticket_price);
                        $stmt->bindParam(':available_quantity', $available_quantity);
                        $stmt->bindParam(':remaining_quantity', $remaining_quantity);
                        $stmt->bindParam(':selling_mode', $selling_mode);
                        $stmt->bindParam(':created_at', $created_at);
                        $stmt->execute();
                    }
                }
                $response = ["status" => true, "code" => 200, "Message" => "events successfully updated."];
                $this->returnResponse($response);
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "events & product_id is required."];
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
                $now = date("Y-m-d H:i:s");
                // $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.id=:id AND ap.status='active' AND ap.expired_date >= :expired_date";
                $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.id=:id";
                $postData = $this->dbConn->prepare($getpost);
                $postData->bindValue(':id', $postId, PDO::PARAM_STR);
                // $postData->bindValue(':expired_date', $now, PDO::PARAM_STR);
                $postData->execute();
                // echo "Last executed query: " . $postData->queryString;
                // exit;
                $postData = $postData->fetch(PDO::FETCH_ASSOC);
                if (!empty($postData)) {
                    // Get location,City, State, Country
                    $fullAddress = '';
                    if (!empty($postData['location'])) {
                        $fullAddress .= $postData['location'];
                    }
                    if (!empty($postData['city_name'])) {
                        $fullAddress .= " " . $postData['city_name'];
                    }
                    if (!empty($postData['state_name'])) {
                        $fullAddress .= " " . $postData['state_name'];
                    }
                    if (!empty($postData['country_name'])) {
                        $fullAddress .= " " . $postData['country_name'];
                    }
                    $postData['full_address'] = trim($fullAddress);
                    if (!empty($postData['slug'])) {
                        $postData['post_url'] = $this->display_image_url . 'ad/' . $postData['id'] . '/' . $postData['slug'];
                    } else {
                        $postData['post_url'] = '';
                    }

                    //$this->param['userId'] // Logged In User Id
                    //$postData['user_id'] // Item AuthorId mean post owner id
                    if (!empty($postData['user_id']) && !empty($this->param['userId']) && ($postData['user_id'] != $this->param['userId'])) {
                        // Post Owner Id jiski post hai
                        $qcuserid = base64_encode(openssl_encrypt($postData['user_id'], 'AES-256-CBC', $this->key, 0));
                        // $qcuserid = base64_encode($postData['user_id']);
                        // Logged In User id
                        $lcuserid = base64_encode(openssl_encrypt($this->param['userId'], 'AES-256-CBC', $this->key, 0));
                        // $lcuserid = base64_encode($this->param['userId']);
                        $postData['chat_url'] = $this->display_image_url . "chat/mchat.php?senderId=$qcuserid&receiverId=$lcuserid";
                    } else {
                        $postData['chat_url'] = null;
                    }

                    if (!empty($this->param['userId'])) {
                        //Check Is favourite
                        $getFavourite = "SELECT af.* FROM ad_favads AS af WHERE af.user_id=:userId AND af.product_id=:postId";
                        $postFavouriteData = $this->dbConn->prepare($getFavourite);
                        $postFavouriteData->bindValue(':userId', $this->param['userId'], PDO::PARAM_STR);
                        $postFavouriteData->bindValue(':postId', $postId, PDO::PARAM_STR);
                        $postFavouriteData->execute();
                        $postFavouriteData = $postFavouriteData->fetch(PDO::FETCH_ASSOC);
                        if (!empty($postFavouriteData['id'])) {
                            $postData['is_favourite'] = true;
                        } else {
                            $postData['is_favourite'] = false;
                        }
                    } else {
                        $postData['is_favourite'] = false;
                    }
                    //Get Category & Sub Category to show simillar ads
                    $categoryId = $postData['category'];
                    $subCategoryId = $postData['sub_category'];
                    if (!empty($postData['screen_shot'])) {
                        $screenShotArr = explode(",", $postData['screen_shot']);
                        if (count($screenShotArr) > 0) {
                            for ($i = 0; $i < count($screenShotArr); $i++) {
                                // echo $screenShotArr[$i].'<br>';
                                $postData['image'][$i] = $this->display_image_url . 'storage/products/' . $screenShotArr[$i];
                            }
                        }
                    } else {
                        $postData['image'] = [];
                    }
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

                    $getReviewAndRatings = "SELECT ar.rating,ar.comments,ar.date,au.username FROM ad_reviews AS ar LEFT JOIN ad_user AS au ON au.id = ar.user_id WHERE ar.productID=:productID AND publish=1";
                    $getReviewAndRatings = $this->dbConn->prepare($getReviewAndRatings);
                    $getReviewAndRatings->bindValue(':productID', $postId, PDO::PARAM_STR);
                    $getReviewAndRatings->execute();
                    $getReviewAndRatings = $getReviewAndRatings->fetchAll(PDO::FETCH_ASSOC);
                    if (count($getReviewAndRatings) > 0) {
                        for ($i = 0; $i < count($getReviewAndRatings); $i++) {
                            $postData['review_rating'][$i]['rating'] = $getReviewAndRatings[$i]['rating'];
                            $postData['review_rating'][$i]['review'] = $getReviewAndRatings[$i]['comments'];
                            $postData['review_rating'][$i]['reviewer_name'] = $getReviewAndRatings[$i]['username'];
                            $postData['review_rating'][$i]['review_date'] = $getReviewAndRatings[$i]['date'];
                        }
                    }

                    if (!empty($categoryId) && !empty($subCategoryId)) {
                        $getSimilarPost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.status='active' AND ap.category=:category AND ap.sub_category=:sub_category ORDER BY ap.created_at DESC LIMIT 10";
                        $similarPostData = $this->dbConn->prepare($getSimilarPost);
                        $similarPostData->bindValue(':category', $categoryId, PDO::PARAM_STR);
                        $similarPostData->bindValue(':sub_category', $subCategoryId, PDO::PARAM_STR);
                    } else if (!empty($categoryId)) {
                        $getSimilarPost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.status='active' AND ap.category=:category ORDER BY ap.created_at DESC LIMIT 10";
                        $similarPostData = $this->dbConn->prepare($getSimilarPost);
                        $similarPostData->bindValue(':category', $categoryId, PDO::PARAM_STR);
                    }

                    $similarPostData->execute();
                    $similarPostData = $similarPostData->fetchAll(PDO::FETCH_ASSOC);
                    if (count($similarPostData) > 0) {
                        // $postData['similar_post'] = $similarPostData;
                        for ($i = 0; $i < count($similarPostData); $i++) {
                            $postData['similar_post'][$i] = $similarPostData[$i];
                            // Get location,City, State, Country
                            $fullAddress = '';
                            if (!empty($similarPostData[$i]['location'])) {
                                $fullAddress .= $similarPostData[$i]['location'];
                            }
                            if (!empty($similarPostData[$i]['city_name'])) {
                                $fullAddress .= " " . $similarPostData[$i]['city_name'];
                            }
                            if (!empty($similarPostData[$i]['state_name'])) {
                                $fullAddress .= " " . $similarPostData[$i]['state_name'];
                            }
                            if (!empty($similarPostData[$i]['country_name'])) {
                                $fullAddress .= " " . $similarPostData[$i]['country_name'];
                            }
                            $postData['similar_post'][$i]['full_address'] = trim($fullAddress);
                            if (!empty($similarPostData[$i]['screen_shot'])) {
                                $screenShotArr = explode(",", $similarPostData[$i]['screen_shot']);
                                if (count($screenShotArr) > 0) {
                                    for ($j = 0; $j < count($screenShotArr); $j++) {
                                        $postData['similar_post'][$i]['image'][$j] = $this->display_image_url . 'storage/products/' . $screenShotArr[$j];
                                    }
                                }
                            } else {
                                $postData['similar_post'][$i]['image'] = [];
                            }
                        }
                    }
                    $response = ["status" => true, "code" => 200, "Message" => "Advertisement details fetched.", "data" => $postData];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Post not found."];
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

    public function getTrainingVideo()
    {
        try {
            $postId = $this->validateParameter('postId', $this->param['postId'], INTEGER);
            if (!empty($postId)) {
                $responseArr = array();
                // $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.id=:id AND ap.status='active' AND ap.expired_date >= :expired_date";
                $getpost = "SELECT ap.* FROM ad_training_gallery AS ap WHERE ap.product_id=:postId";
                $postData = $this->dbConn->prepare($getpost);
                $postData->bindValue(':postId', $postId, PDO::PARAM_STR);
                $postData->execute();
                // echo "Last executed query: " . $postData->queryString;
                // exit;
                $postData = $postData->fetchAll(PDO::FETCH_ASSOC);
                if (count($postData) > 0) {
                    foreach ($postData as $key => $post) {
                        $responseArr[$key]['id'] = $post['id'];
                        $responseArr[$key]['product_id'] = $post['product_id'];
                        if (!empty($post['training_video'])) {
                            $responseArr[$key]['training_video'] = $this->display_image_url . 'storage/training_video/' . $post['training_video'];
                        } else {
                            $responseArr[$key]['training_video'] = '';
                        }
                    }
                    $response = ["status" => true, "code" => 200, "Message" => "Training video successfully fetched.", "data" => $responseArr];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Training vidoe not found."];
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

    public function getUserPost()
    {
        try {
            $listType = $this->validateParameter('listing_type', $this->param['listing_type'], STRING);
            $token = $this->getBearerToken();
            if (!empty($token)) {
                $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
                if (!empty($payload->userId)) {
                    $now = date("Y-m-d H:i:s");
                    $responseArr = array();
                    if (!empty($listType) && $listType == 'favourite') {
                        $postIds = array();
                        $getFavPost = "SELECT product_id FROM ad_favads WHERE user_id=:userId";
                        $favPostData = $this->dbConn->prepare($getFavPost);
                        $favPostData->bindValue(':userId', $payload->userId, PDO::PARAM_STR);
                        $favPostData->execute();
                        $favPostData = $favPostData->fetchAll(PDO::FETCH_ASSOC);
                        if (count($favPostData) > 0) {
                            foreach ($favPostData as $key => $favPost) {
                                $postIds[] = $favPost['product_id'];
                            }
                            // Create the placeholders string
                            $placeholders = rtrim(str_repeat('?,', count($postIds)), ',');
                            $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.id IN ($placeholders) AND ap.status='active' AND ap.category != 9 AND ap.category != 10";
                            $postData = $this->dbConn->prepare($getpost);
                            // Bind the values to the statement
                            foreach ($postIds as $key => $value) {
                                $postData->bindValue(($key + 1), $value, PDO::PARAM_INT);
                            }

                        } else {
                            $response = ["status" => true, "code" => 200, "Message" => "All post details fetched.", "data" => $responseArr];
                            $this->returnResponse($response);
                        }
                    } elseif (!empty($listType) && $listType == 'expire') {
                        $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.status='expire' AND ap.user_id=:userId AND ap.category != 9 AND ap.category != 10";
                        $postData = $this->dbConn->prepare($getpost);
                        $postData->bindValue(':userId', $payload->userId, PDO::PARAM_STR);
                    } elseif (!empty($listType) && $listType == 'pending') {
                        $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.status='pending' AND ap.user_id=:userId AND ap.category != 9 AND ap.category != 10";
                        $postData = $this->dbConn->prepare($getpost);
                        $postData->bindValue(':userId', $payload->userId, PDO::PARAM_STR);
                    } elseif (!empty($listType) && $listType == 'all') {
                        $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.status='active' AND ap.user_id=:userId AND ap.expired_date >= :expired_date AND ap.category != 9 AND ap.category != 10";
                        $postData = $this->dbConn->prepare($getpost);
                        $postData->bindValue(':userId', $payload->userId, PDO::PARAM_STR);
                        $postData->bindValue(':expired_date', $now, PDO::PARAM_STR);
                    }
                    $postData->execute();
                    // echo "Last executed query: " . $postData->queryString;
                    // exit;
                    $postData = $postData->fetchAll(PDO::FETCH_ASSOC);
                    if (count($postData) > 0) {
                        foreach ($postData as $key => $post) {
                            $responseArr[$key] = $post;
                            // Get location,City, State, Country
                            $fullAddress = '';
                            if (!empty($post['location'])) {
                                $fullAddress .= $post['location'];
                            }
                            if (!empty($post['city_name'])) {
                                $fullAddress .= " " . $post['city_name'];
                            }
                            if (!empty($post['state_name'])) {
                                $fullAddress .= " " . $post['state_name'];
                            }
                            if (!empty($post['country_name'])) {
                                $fullAddress .= " " . $post['country_name'];
                            }
                            $responseArr[$key]['full_address'] = trim($fullAddress);
                            if (!empty($post['screen_shot'])) {
                                $screenShotArr = explode(",", $post['screen_shot']);
                                if (count($screenShotArr) > 0) {
                                    for ($i = 0; $i < count($screenShotArr); $i++) {
                                        $responseArr[$key]['image'][$i] = $this->display_image_url . 'storage/products/' . $screenShotArr[$i];
                                    }
                                }
                            }
                        }
                        $response = ["status" => true, "code" => 200, "Message" => "All post details fetched.", "data" => $responseArr];
                        $this->returnResponse($response);
                    } else {
                        $response = ["status" => true, "code" => 200, "Message" => "No post found.", "data" => $responseArr];
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

    public function getTransactionListing()
    {
        try {
            $token = $this->getBearerToken();
            if (!empty($token)) {
                $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
                if (!empty($payload->userId)) {
                    $responseArr = array();
                    $getpost = "SELECT product_name,amount,featured,urgent,highlight,transaction_time,status,transaction_gatway FROM ad_transaction WHERE seller_id=:userId";
                    $postData = $this->dbConn->prepare($getpost);
                    $postData->bindValue(':userId', $payload->userId, PDO::PARAM_STR);
                    $postData->execute();
                    // echo "Last executed query: " . $postData->queryString;
                    // exit;
                    $postData = $postData->fetchAll(PDO::FETCH_ASSOC);
                    if (count($postData) > 0) {
                        foreach ($postData as $key => $post) {
                            $responseArr[$key] = $post;
                            if (!empty($post['transaction_time'])) {
                                $responseArr[$key]['transaction_time'] = date('d M Y h:i A', $post['transaction_time']);
                            }
                            $responseArr[$key]['invoice_url'] = $this->display_image_url . 'invoice/' . $payload->userId;
                        }

                        $response = ["status" => true, "code" => 200, "Message" => "Transaction listing successfully fetched.", "data" => $responseArr];
                        $this->returnResponse($response);
                    } else {
                        $response = ["status" => true, "code" => 200, "Message" => "No post found.", "data" => $responseArr];
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

    public function getAllUserFavoritePosts()
    {
        try {
            $token = $this->getBearerToken();
            if (!empty($token)) {
                $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
                if (!empty($payload->userId)) {
                    $now = date("Y-m-d H:i:s");
                    $responseArr = array();
                    $getpost = "SELECT af.*,ap.*,acm.cat_name,acs.sub_cat_name,ac.name FROM ad_favads AS af LEFT JOIN ad_product AS ap ON ap.id = af.product_id LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city WHERE af.user_id=:userId AND ap.status='active' AND ap.expired_date >= :expired_date ORDER BY af.product_id ASC";
                    $postData = $this->dbConn->prepare($getpost);
                    $postData->bindValue(':userId', $payload->userId, PDO::PARAM_STR);
                    $postData->bindValue(':expired_date', $now, PDO::PARAM_STR);
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
            $now = date("Y-m-d H:i:s");
            $responseArr = array();
            $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE status='active' AND ap.expired_date >= :expired_date";

            if (!empty($this->param['title'])) {
                $getpost .= " AND ap.product_name LIKE CONCAT( '%', :title, '%')";
            }
            if (!empty($this->param['category'])) {
                $getpost .= " AND ap.category=:categoryId";
            } else {
                $getpost .= " AND ap.category != 9 AND ap.category != 10 ";
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
                $getpost .= " AND ap.price BETWEEN " . $this->param['pricefrom'] . " AND " . $this->param['priceto'] . "";
            }

            /*if(!empty($this->param['yearto']) && !empty($this->param['yearfrom'])){
            $getpost .= " AND ap.created_at BETWEEN '".$this->param['yearto']."' AND '".$this->param['yearfrom']."'";
            }*/

            if (!empty($this->param['listing_type']) && $this->param['listing_type'] == 'premium') {
                $getpost .= " AND (ap.featured = :featured OR ap.urgent = :urgent OR ap.highlight = :highlight)";
            }

            if (!empty($this->param['sortbyfieldname']) && $this->param['sortbyfieldname'] == 'product_name_asc') {
                $getpost .= " ORDER BY ap.product_name ASC";
            } else if (!empty($this->param['sortbyfieldname']) && $this->param['sortbyfieldname'] == 'product_name_desc') {
                $getpost .= " ORDER BY ap.product_name DESC";
            } else if (!empty($this->param['sortbyfieldname']) && $this->param['sortbyfieldname'] == 'price_asc') {
                $getpost .= " ORDER BY ap.price ASC";
            } else if (!empty($this->param['sortbyfieldname']) && $this->param['sortbyfieldname'] == 'price_desc') {
                $getpost .= " ORDER BY ap.price DESC";
            } else if (!empty($this->param['sortbyfieldname']) && $this->param['sortbyfieldname'] == 'created_at_asc') {
                $getpost .= " ORDER BY ap.created_at ASC";
            } else if (!empty($this->param['sortbyfieldname']) && $this->param['sortbyfieldname'] == 'created_at_desc') {
                $getpost .= " ORDER BY ap.created_at DESC";
            } else if (!empty($this->param['listing_type']) && $this->param['listing_type'] == 'latest') {
                $getpost .= " ORDER BY ap.created_at DESC";
            }
            $postData = $this->dbConn->prepare($getpost);

            $postData->bindValue(':expired_date', $now, PDO::PARAM_STR);

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

            if (!empty($this->param['listing_type']) && $this->param['listing_type'] == 'premium') {
                $postData->bindValue(':featured', 1, PDO::PARAM_STR);
                $postData->bindValue(':urgent', 1, PDO::PARAM_STR);
                $postData->bindValue(':highlight', 1, PDO::PARAM_STR);
            }

            $postData->execute();
            // echo "Last executed query: " . $postData->queryString;
            // exit;
            $postData = $postData->fetchAll(PDO::FETCH_ASSOC);
            if (count($postData) > 0) {
                foreach ($postData as $key => $post) {
                    $responseArr[$key] = $post;
                    // Get location,City, State, Country
                    $fullAddress = '';
                    if (!empty($post['location'])) {
                        $fullAddress .= $post['location'];
                    }
                    if (!empty($post['city_name'])) {
                        $fullAddress .= " " . $post['city_name'];
                    }
                    if (!empty($post['state_name'])) {
                        $fullAddress .= " " . $post['state_name'];
                    }
                    if (!empty($post['country_name'])) {
                        $fullAddress .= " " . $post['country_name'];
                    }
                    $responseArr[$key]['full_address'] = trim($fullAddress);
                    if (!empty($post['screen_shot'])) {
                        $screenShotArr = explode(",", $post['screen_shot']);
                        if (count($screenShotArr) > 0) {
                            for ($i = 0; $i < count($screenShotArr); $i++) {
                                $responseArr[$key]['image'][$i] = $this->display_image_url . 'storage/products/' . $screenShotArr[$i];
                            }
                        }
                    } else {
                        $responseArr[$key]['image'] = [];
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

    public function getTrainingPost()
    {
        try {
            $token = $this->getBearerToken();
            if (!empty($token)) {
                $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
                if (!empty($payload->userId)) {
                    $now = date("Y-m-d H:i:s");
                    $responseArr = array();
                    if (!empty($this->param['user_id'])) {
                        $getpost = "SELECT ap.*,au.username as post_user_name,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_user AS au ON au.id = ap.user_id LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.status='active' AND ap.user_id = '" . $this->param['user_id'] . "' AND ap.category = 9 ORDER BY ap.created_at DESC";
                    } else {
                        $getpost = "SELECT ap.*,au.username as post_user_name,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_user AS au ON au.id = ap.user_id LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.status='active' AND ap.category = 9 ORDER BY ap.created_at DESC";
                    }
                    $postData = $this->dbConn->prepare($getpost);
                    $postData->execute();
                    // echo "Last executed query: " . $postData->queryString;
                    // exit;
                    $postData = $postData->fetchAll(PDO::FETCH_ASSOC);
                    if (count($postData) > 0) {
                        foreach ($postData as $key => $post) {
                            $responseArr[$key] = $post;
                            // Get location,City, State, Country
                            $fullAddress = '';
                            if (!empty($post['location'])) {
                                $fullAddress .= $post['location'];
                            }
                            if (!empty($post['city_name'])) {
                                $fullAddress .= " " . $post['city_name'];
                            }
                            if (!empty($post['state_name'])) {
                                $fullAddress .= " " . $post['state_name'];
                            }
                            if (!empty($post['country_name'])) {
                                $fullAddress .= " " . $post['country_name'];
                            }
                            $responseArr[$key]['full_address'] = trim($fullAddress);

                            //Check if product is purchased from logged in User
                            /*$getOrder = "SELECT order_id FROM ad_shop_order_item WHERE product_id = :product_id";
                            $getOrderData = $this->dbConn->prepare($getOrder);
                            $getOrderData->bindValue(':product_id', $post['id'], PDO::PARAM_STR);
                            $getOrderData->execute();
                            $getOrderData = $getOrderData->fetch(PDO::FETCH_ASSOC);
                            if(!empty($getOrderData['order_id'])){
                            //Check product purchase order status for logged in user
                            $getPurchaseStatus = "SELECT member_id,order_status FROM ad_shop_order WHERE id = :id";
                            $getPurchaseStatusData = $this->dbConn->prepare($getPurchaseStatus);
                            $getPurchaseStatusData->bindValue(':id', $getOrderData['order_id'], PDO::PARAM_STR);
                            $getPurchaseStatusData->execute();
                            $getPurchaseStatusData = $getPurchaseStatusData->fetch(PDO::FETCH_ASSOC);
                            if($getPurchaseStatusData['order_status'] == 'PAID' && $getPurchaseStatusData['member_id'] == $payload->userId){
                            $responseArr[$key]['is_purchased'] = True;
                            } else {
                            $responseArr[$key]['is_purchased'] = False;
                            }
                            } else {
                            $responseArr[$key]['is_purchased'] = False;
                            }*/

                            $getOrder = "SELECT order_id FROM ad_shop_order_item WHERE product_id = :product_id";
                            $getOrderData = $this->dbConn->prepare($getOrder);
                            $getOrderData->bindValue(':product_id', $post['id'], PDO::PARAM_STR);
                            $getOrderData->execute();
                            $getOrderData = $getOrderData->fetchAll(PDO::FETCH_ASSOC);
                            if (count($getOrderData) > 0) {
                                foreach ($getOrderData as $key => $row) {
                                    //Check product purchase order status for logged in user
                                    $getPurchaseStatus = "SELECT member_id,order_status FROM ad_shop_order WHERE id = :id";
                                    $getPurchaseStatusData = $this->dbConn->prepare($getPurchaseStatus);
                                    $getPurchaseStatusData->bindValue(':id', $row['order_id'], PDO::PARAM_STR);
                                    $getPurchaseStatusData->execute();
                                    $getPurchaseStatusData = $getPurchaseStatusData->fetch(PDO::FETCH_ASSOC);
                                    if ($getPurchaseStatusData['order_status'] == 'PAID' && $getPurchaseStatusData['member_id'] == $payload->userId) {
                                        $responseArr[$key]['is_purchased'] = true;
                                    } else {
                                        $responseArr[$key]['is_purchased'] = false;
                                    }
                                }
                            } else {
                                $responseArr[$key]['is_purchased'] = false;
                            }

                            if (!empty($post['screen_shot'])) {
                                $screenShotArr = explode(",", $post['screen_shot']);
                                if (count($screenShotArr) > 0) {
                                    for ($i = 0; $i < count($screenShotArr); $i++) {
                                        $responseArr[$key]['image'][$i] = $this->display_image_url . 'storage/products/' . $screenShotArr[$i];
                                    }
                                }
                            } else {
                                $responseArr[$key]['image'] = [];
                            }
                            if (!empty($post['promo_video'])) {
                                $responseArr[$key]['promo_video'] = $this->display_image_url . 'storage/training_video/' . $post['promo_video'];
                            }
                            // Fetched Training Vidoe From Training Gallery table for response
                            $getTrainingVideo = "SELECT tv.* FROM ad_training_gallery AS tv WHERE tv.product_id='" . $post['id'] . "'";
                            $trainingVideoData = $this->dbConn->prepare($getTrainingVideo);
                            $trainingVideoData->execute();
                            $trainingVideoData = $trainingVideoData->fetchAll(PDO::FETCH_ASSOC);
                            if (count($trainingVideoData) > 0) {
                                foreach ($trainingVideoData as $key1 => $video) {
                                    $responseArr[$key]['gallery'][$key1] = $video;
                                    $responseArr[$key]['gallery'][$key1]['training_video'] = $this->display_image_url . 'storage/training_video/' . $video['training_video'];
                                }
                            }
                        }
                        $response = ["status" => true, "code" => 200, "Message" => "Training listing successfully fetched.", "data" => $responseArr];
                        $this->returnResponse($response);
                    } else {
                        $response = ["status" => false, "code" => 400, "Message" => "Record not found."];
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

    public function getEventPost()
    {
        try {
            $now = date("Y-m-d H:i:s");
            $responseArr = array();
            if (!empty($this->param['user_id'])) {
                $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE status='active' AND ap.user_id='" . $this->param['user_id'] . "' AND ap.category = 10 ORDER BY ap.created_at DESC";
            } else {
                $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE status='active' AND ap.category = 10 ORDER BY ap.created_at DESC";
            }
            $postData = $this->dbConn->prepare($getpost);
            $postData->execute();
            // echo "Last executed query: " . $postData->queryString;
            // exit;
            $postData = $postData->fetchAll(PDO::FETCH_ASSOC);
            if (count($postData) > 0) {
                foreach ($postData as $key => $post) {
                    $responseArr[$key] = $post;
                    // Get location,City, State, Country
                    $fullAddress = '';
                    if (!empty($post['location'])) {
                        $fullAddress .= $post['location'];
                    }
                    if (!empty($post['city_name'])) {
                        $fullAddress .= " " . $post['city_name'];
                    }
                    if (!empty($post['state_name'])) {
                        $fullAddress .= " " . $post['state_name'];
                    }
                    if (!empty($post['country_name'])) {
                        $fullAddress .= " " . $post['country_name'];
                    }
                    $responseArr[$key]['full_address'] = trim($fullAddress);
                    if (!empty($post['screen_shot'])) {
                        $screenShotArr = explode(",", $post['screen_shot']);
                        if (count($screenShotArr) > 0) {
                            for ($i = 0; $i < count($screenShotArr); $i++) {
                                $responseArr[$key]['image'][$i] = $this->display_image_url . 'storage/products/' . $screenShotArr[$i];
                            }
                        }
                    } else {
                        $responseArr[$key]['image'] = [];
                    }
                    if (!empty($post['promo_video'])) {
                        $responseArr[$key]['promo_video'] = $this->display_image_url . 'storage/training_video/' . $post['promo_video'];
                    }
                    // Fetched Event Seats details From Event Types table for response
                    $getEvent = "SELECT e.* FROM ad_product_event_types AS e WHERE e.product_id='" . $post['id'] . "' ORDER BY e.created_at DESC";
                    $eventData = $this->dbConn->prepare($getEvent);
                    $eventData->execute();
                    $eventData = $eventData->fetchAll(PDO::FETCH_ASSOC);
                    if (count($eventData) > 0) {
                        foreach ($eventData as $key1 => $event) {
                            $responseArr[$key]['event'][$key1] = $event;
                        }
                    }
                }
                $response = ["status" => true, "code" => 200, "Message" => "Event listing successfully fetched.", "data" => $responseArr];
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

    public function getPremiumAndLatestPost()
    {
        try {
            $now = date("Y-m-d H:i:s");
            $responseArr = array();

            //Get Premium Post
            $getPremiumPost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE status='active' AND ap.expired_date >= :expired_date AND ap.category != 9 AND ap.category != 10 AND (ap.featured = :featured OR ap.urgent = :urgent OR ap.highlight = :highlight) ORDER BY ap.created_at DESC LIMIT 10";
            $premiumPostData = $this->dbConn->prepare($getPremiumPost);
            $premiumPostData->bindValue(':expired_date', $now, PDO::PARAM_STR);
            $premiumPostData->bindValue(':featured', 1, PDO::PARAM_STR);
            $premiumPostData->bindValue(':urgent', 1, PDO::PARAM_STR);
            $premiumPostData->bindValue(':highlight', 1, PDO::PARAM_STR);
            $premiumPostData->execute();
            // echo "Last executed query: " . $premiumPostData->queryString;
            // exit;
            $premiumPostData = $premiumPostData->fetchAll(PDO::FETCH_ASSOC);
            if (count($premiumPostData) > 0) {
                foreach ($premiumPostData as $key => $post) {
                    $responseArr['premium'][$key] = $post;
                    // Get location,City, State, Country
                    $fullAddress = '';
                    if (!empty($post['location'])) {
                        $fullAddress .= $post['location'];
                    }
                    if (!empty($post['city_name'])) {
                        $fullAddress .= " " . $post['city_name'];
                    }
                    if (!empty($post['state_name'])) {
                        $fullAddress .= " " . $post['state_name'];
                    }
                    if (!empty($post['country_name'])) {
                        $fullAddress .= " " . $post['country_name'];
                    }
                    $responseArr['premium'][$key]['full_address'] = trim($fullAddress);
                    if (!empty($post['screen_shot'])) {
                        $screenShotArr = explode(",", $post['screen_shot']);
                        if (count($screenShotArr) > 0) {
                            for ($i = 0; $i < count($screenShotArr); $i++) {
                                $responseArr['premium'][$key]['image'][$i] = $this->display_image_url . 'storage/products/' . $screenShotArr[$i];
                            }
                        }
                    } else {
                        $responseArr['premium'][$key]['image'] = [];
                    }
                }
            } else {
                $responseArr['premium'] = [];
            }

            //Get Latest Post
            $getLatestPost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE status='active' AND ap.expired_date >= :expired_date AND ap.category != 9 AND ap.category != 10 ORDER BY ap.created_at DESC LIMIT 10";
            $latestPostData = $this->dbConn->prepare($getLatestPost);
            $latestPostData->bindValue(':expired_date', $now, PDO::PARAM_STR);
            $latestPostData->execute();
            // echo "Last executed query: " . $latestPostData->queryString;
            // exit;
            $latestPostData = $latestPostData->fetchAll(PDO::FETCH_ASSOC);
            if (count($latestPostData) > 0) {
                foreach ($latestPostData as $key => $post) {
                    $responseArr['latest'][$key] = $post;
                    // Get location,City, State, Country
                    $fullAddress = '';
                    if (!empty($post['location'])) {
                        $fullAddress .= $post['location'];
                    }
                    if (!empty($post['city_name'])) {
                        $fullAddress .= " " . $post['city_name'];
                    }
                    if (!empty($post['state_name'])) {
                        $fullAddress .= " " . $post['state_name'];
                    }
                    if (!empty($post['country_name'])) {
                        $fullAddress .= " " . $post['country_name'];
                    }
                    $responseArr['latest'][$key]['full_address'] = trim($fullAddress);
                    if (!empty($post['screen_shot'])) {
                        $screenShotArr = explode(",", $post['screen_shot']);
                        if (count($screenShotArr) > 0) {
                            for ($i = 0; $i < count($screenShotArr); $i++) {
                                $responseArr['latest'][$key]['image'][$i] = $this->display_image_url . 'storage/products/' . $screenShotArr[$i];
                            }
                        }
                    } else {
                        $responseArr['latest'][$key]['image'] = [];
                    }
                }
            } else {
                $responseArr['latest'] = [];
            }

            $response = ["status" => true, "code" => 200, "Message" => "Post successfully fetched.", "data" => $responseArr];
            $this->returnResponse($response);

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
            $stmt = $this->dbConn->prepare("SELECT ac.*,st.name as state_name FROM ad_cities AS ac LEFT JOIN ad_subadmin1 AS st ON st.code = ac.subadmin1_code ORDER BY ac.id ASC");
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
        // $username = $this->validateParameter('username', $this->param['username'], STRING);
        $violation_type = $this->validateParameter('violation_type', $this->param['violation_type'], STRING);
        // $other_person_name = $this->validateParameter('other_person_name', $this->param['other_person_name'], STRING);
        // $violation_url = $this->validateParameter('violation_url', $this->param['violation_url'], STRING);
        $violation_details = $this->validateParameter('violation_details', $this->param['violation_details'], STRING);
        if (!empty($name) && !empty($email) && !empty($violation_type) && !empty($violation_details)) {
            if (!empty($this->param['username'])) {
                $username = $this->param['username'];
            } else {
                $username = '';
            }

            if (!empty($this->param['other_person_name'])) {
                $other_person_name = $this->param['other_person_name'];
            } else {
                $other_person_name = '';
            }

            if (!empty($this->param['violation_url'])) {
                $violation_url = $this->param['violation_url'];
            } else {
                $violation_url = '';
            }

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
                            $response = ["status" => true, "code" => 200, "Message" => "Product has been removed from your favourite list."];
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
                            $response = ["status" => true, "code" => 200, "Message" => "Product has been added in your favourite list."];
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
                        $response = ["status" => true, "code" => 400, "Message" => "You have already submit rating and review."];
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
                            // Select the last insert row
                            $stmt = $this->dbConn->prepare("SELECT * FROM ad_product WHERE id=:id");
                            $stmt->bindParam(':id', $product_id);
                            $stmt->execute();
                            // Fetch the row
                            $product = $stmt->fetch(PDO::FETCH_ASSOC);
                            if (!empty($product['user_id'])) {
                                $getUser = "SELECT device_token FROM ad_user WHERE device_token !='' AND id = :userId";
                                $getUserData = $this->dbConn->prepare($getUser);
                                $getUserData->bindValue(':userId', $product['user_id'], PDO::PARAM_STR);
                                $getUserData->execute();
                                $getUserData = $getUserData->fetch(PDO::FETCH_ASSOC);
                                if (!empty($getUserData['device_token'])) {
                                    $title = 'One of the user has added review & rating on your payaki product ' . $product['product_name'] . '. Once review approved by admin then it will show on websites.';
                                    $message = $this->display_image_url . 'ad/' . $product['id'] . '/' . $product['slug'];
                                    $deviceToken = $getUserData['device_token'];
                                    $this->pushNotificationForApp($deviceToken, $title, $message);

                                    $notificationSql = 'INSERT INTO ad_custom_notification (id, notification_id, type, title, redirect_url, user_id, status, created_at) VALUES(null, :notification_id, :type, :title, :redirect_url, :user_id, :status, :created_at)';
                                    $type = 'review';
                                    $user_id = $product['user_id'];
                                    $nStatus = 0;
                                    $createdDate = date('Y-m-d H:i:s');
                                    $redirect_url = $message;
                                    $notifivationStmt = $this->dbConn->prepare($notificationSql);
                                    $notifivationStmt->bindParam(':notification_id', $product['id']);
                                    $notifivationStmt->bindParam(':type', $type);
                                    $notifivationStmt->bindParam(':title', $title);
                                    $notifivationStmt->bindParam(':redirect_url', $redirect_url);
                                    $notifivationStmt->bindParam(':user_id', $user_id);
                                    $notifivationStmt->bindParam(':status', $nStatus);
                                    $notifivationStmt->bindParam(':created_at', $createdDate);
                                    $notifivationStmt->execute();
                                }
                            }
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

    public function placeQuote()
    {
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if (!empty($payload->userId)) {
                $post_id = $this->validateParameter('post_id', $this->param['post_id'], STRING);
                $post_user_id = $this->validateParameter('post_user_id', $this->param['post_user_id'], STRING);
                $amount = $this->validateParameter('amount', $this->param['amount'], STRING);
                $message = $this->validateParameter('message', $this->param['message'], STRING);
                if (!empty($post_id) && !empty($post_user_id) && !empty($amount) && !empty($message)) {
                    $timenow = date('Y-m-d H:i:s');
                    // Write insert code
                    $sql = 'INSERT INTO ad_quotes (id, post_id, seller_id, sender_id, amount, message, created_at) VALUES(null, :post_id, :seller_id, :sender_id, :amount, :message, :created_at)';
                    $stmt = $this->dbConn->prepare($sql);
                    $stmt->bindParam(':post_id', $post_id);
                    $stmt->bindParam(':seller_id', $post_user_id);
                    $stmt->bindParam(':sender_id', $payload->userId);
                    $stmt->bindParam(':amount', $amount);
                    $stmt->bindParam(':message', $message);
                    $stmt->bindParam(':created_at', $timenow);
                    if ($stmt->execute()) {
                        //Get Product post details
                        $getPost = "SELECT * FROM `ad_product` WHERE `id`=:id";
                        $postData = $this->dbConn->prepare($getPost);
                        $postData->bindValue(':id', $post_id, PDO::PARAM_STR);
                        $postData->execute();
                        $postData = $postData->fetch(PDO::FETCH_ASSOC);
                        if (!empty($postData)) {
                            $postUrl = $this->display_image_url . 'ad/' . $postData['id'] . '/' . $postData['slug'];
                            $getuser = "SELECT * FROM `ad_user` WHERE `id`=:id";
                            $userData = $this->dbConn->prepare($getuser);
                            $userData->bindValue(':id', $postData['user_id'], PDO::PARAM_STR);
                            $userData->execute();
                            $userData = $userData->fetch(PDO::FETCH_ASSOC);
                            if (!empty($userData)) {
                                /*SEND CONFIRMATION EMAIL*/
                                $subject = 'Payaki - Quote received for the product ' . $postData['product_name'];
                                $body = '<p>Click below link to see post :-</p><br />
                                        <a href="' . $postUrl . '" target="_other" rel="nofollow">' . $postData['product_name'] . '</a>';
                                $this->sendMail($userData['email'], $subject, $body);

                                //Send push notification to user
                                $title = $subject;
                                $message = $this->display_image_url . 'ad/' . $postData['id'] . '/' . $postData['slug'];
                                $deviceToken = $userData['device_token'];
                                $this->pushNotificationForApp($deviceToken, $title, $message);

                                $notificationSql = 'INSERT INTO ad_custom_notification (id, notification_id, type, title, redirect_url, user_id, status, created_at) VALUES(null, :notification_id, :type, :title, :redirect_url, :user_id, :status, :created_at)';
                                $type = 'quote';
                                $user_id = $postData['user_id'];
                                $nStatus = 0;
                                $createdDate = date('Y-m-d H:i:s');
                                $redirect_url = $message;
                                $notifivationStmt = $this->dbConn->prepare($notificationSql);
                                $notifivationStmt->bindParam(':notification_id', $postData['id']);
                                $notifivationStmt->bindParam(':type', $type);
                                $notifivationStmt->bindParam(':title', $title);
                                $notifivationStmt->bindParam(':redirect_url', $redirect_url);
                                $notifivationStmt->bindParam(':user_id', $user_id);
                                $notifivationStmt->bindParam(':status', $nStatus);
                                $notifivationStmt->bindParam(':created_at', $createdDate);
                                $notifivationStmt->execute();

                            }
                        }
                        $response = ["status" => true, "code" => 200, "Message" => "Quote successfully placed."];
                        $this->returnResponse($response);
                    } else {
                        $response = ["status" => false, "code" => 400, "Message" => "Something is wrong."];
                        $this->returnResponse($response);
                    }
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "All field required."];
                    $this->returnResponse($response);
                }

            } else {
                return false;
            }
        } else {
            $response = ["status" => false, "code" => 400, "Message" => "Auth token not found!"];
            $this->returnResponse($response);
        }
    }

    public function chatUserListing()
    {
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if (!empty($payload->userId)) {
                $listUserArr = array();
                $getChatUserList = "SELECT DISTINCT ad_user.id,ad_user.username,ad_user.image FROM ad_custom_messages, ad_user WHERE (ad_custom_messages.receiver = :userid OR ad_custom_messages.sender = :userid) AND (ad_custom_messages.receiver = ad_user.id OR ad_custom_messages.sender = ad_user.id)";
                $getChatUserListData = $this->dbConn->prepare($getChatUserList);
                $getChatUserListData->bindValue(':userid', $payload->userId, PDO::PARAM_STR);
                $getChatUserListData->execute();
                $getChatUserListData = $getChatUserListData->fetchAll(PDO::FETCH_ASSOC);

                if (count($getChatUserListData) > 0) {
                    $returnArr = array();

                    foreach ($getChatUserListData as $user) {
                        if ($user['id'] != $payload->userId) {
                            $listUserArr['id'] = $user['id'];
                            $listUserArr['username'] = $user['username'];
                            $key = "BYTECIPHERPAYAKI";
                            $qcuserid = base64_encode(openssl_encrypt($user['id'], 'AES-256-CBC', $key, 0));
                            $lcuserid = base64_encode(openssl_encrypt($payload->userId, 'AES-256-CBC', $key, 0));
                            $listUserArr['chat_url'] = $this->display_image_url . "chat/mchat.php?senderId=$qcuserid&receiverId=$lcuserid";
                            $listUserArr['image'] = $this->display_image_url . 'storage/profile/' . $user['image'];
                            // receiver id $payload->userId
                            // Sender id $user['id']
                            // Need to fetch last record order by desc id from ad_custom_messages
                            $getUserLastChat = "SELECT body,date_time FROM `ad_custom_messages` WHERE (`receiver`=:receiver OR `receiver`=:sender) AND (`sender`=:sender or `sender`=:receiver)  ORDER BY id DESC";
                            $getUserLastChatData = $this->dbConn->prepare($getUserLastChat);
                            $getUserLastChatData->bindValue(':receiver', $payload->userId, PDO::PARAM_STR);
                            $getUserLastChatData->bindValue(':sender', $user['id'], PDO::PARAM_STR);
                            $getUserLastChatData->execute();
                            $getUserLastChatData = $getUserLastChatData->fetch(PDO::FETCH_ASSOC);

                            if (!empty($getUserLastChatData['body'])) {
                                $listUserArr['last_message'] = $getUserLastChatData['body'];
                            } else {
                                $listUserArr['last_message'] = '';
                            }
                            if (!empty($getUserLastChatData['date_time'])) {
                                $listUserArr['last_message_time'] = $getUserLastChatData['date_time'];
                            } else {
                                $listUserArr['last_message_time'] = '';
                            }

                            $returnArr[] = $listUserArr;
                        }
                    }
                    $response = ["status" => true, "code" => 200, "Message" => "Chat user list successfully fetched.", "data" => $returnArr];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => true, "code" => 200, "Message" => "No user listing found", "data" => $listUserArr];
                    $this->returnResponse($response);
                }
            } else {
                return false;
            }
        }
    }

    public function getUserOptions($userId, $userOptions)
    {
        $stmt = $this->dbConn->prepare("SELECT * FROM ad_user_options WHERE user_id =:user_id AND option_name LIKE CONCAT( '%', :option_name, '%') ");
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":option_name", $userOptions);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($user["option_value"])) {
            return $user["option_value"];
        } else {
            return '';
        }
    }

    //We are not using this api
    public function payment()
    {
        try {
            $token = $this->getBearerToken();
            if (!empty($token)) {
                $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
                if (!empty($payload->userId)) {
                    $productName = !empty($this->param['product_name']) ? $this->param['product_name'] : '';
                    $productId = !empty($this->param['product_id']) ? $this->param['product_id'] : '';
                    $userId = !empty($this->param['seller_id']) ? $this->param['seller_id'] : '';
                    $amount = !empty($this->param['amount']) ? $this->param['amount'] : '';
                    $currencyCode = !empty($this->param['currency_code']) ? $this->param['currency_code'] : 'USD';
                    $baseAmount = !empty($this->param['amount']) ? $this->param['amount'] : '';
                    $featured = !empty($this->param['featured']) ? $this->param['featured'] : 0;
                    $urgent = !empty($this->param['urgent']) ? $this->param['urgent'] : 0;
                    $highlight = !empty($this->param['highlight']) ? $this->param['highlight'] : 0;
                    $transactionTime = !empty($this->param['transaction_time']) ? $this->param['transaction_time'] : time();
                    // Status should be enum('pending', 'success', 'failed', 'cancel')
                    $status = !empty($this->param['status']) ? $this->param['status'] : '';
                    $paymentId = !empty($this->param['payment_id']) ? $this->param['payment_id'] : '';
                    $paymentGatway = !empty($this->param['payment_gatway']) ? $this->param['payment_gatway'] : 'paypal';
                    $transactionIpAddress = !empty($this->param['transaction_ip_address']) ? $this->param['transaction_ip_address'] : '';

                    // Package Featured Urgent Highlight
                    $transactionDescription = !empty($this->param['transaction_description']) ? $this->param['transaction_description'] : '';
                    // Premium Ad
                    $transactionMethod = !empty($this->param['transaction_method']) ? $this->param['transaction_method'] : '';
                    // Frequency enum('MONTHLY', 'YEARLY', 'LIFETIME')
                    $frequency = !empty($this->param['frequency']) ? $this->param['frequency'] : null;

                    $billing = array(
                        'type' => $this->getUserOptions($payload->userId, 'billing_details_type'),
                        'tax_id' => $this->getUserOptions($payload->userId, 'billing_tax_id'),
                        'name' => $this->getUserOptions($payload->userId, 'billing_name'),
                        'address' => $this->getUserOptions($payload->userId, 'billing_address'),
                        'city' => $this->getUserOptions($payload->userId, 'billing_city'),
                        'state' => $this->getUserOptions($payload->userId, 'billing_state'),
                        'zipcode' => $this->getUserOptions($payload->userId, 'billing_zipcode'),
                        'country' => $this->getUserOptions($payload->userId, 'billing_country'),
                    );
                    $billing = !empty($billing) ? json_encode($billing) : '';
                    $taxesIds = !empty($this->param['taxes_ids']) ? $this->param['taxes_ids'] : '';

                    $insert_query = "INSERT INTO `ad_transaction` (`product_name`,`product_id`,`seller_id`,`amount`,`currency_code`,`base_amount`,`featured`,`urgent`,`highlight`,`transaction_time`,`status`,`payment_id`,`transaction_gatway`,`transaction_ip`,`transaction_description`,`transaction_method`,`frequency`,`billing`,`taxes_ids`) VALUES(:product_name,:product_id,:seller_id,:amount,:currency_code,:base_amount,:featured,:urgent,:highlight,:transaction_time,:status,:payment_id,:transaction_gatway,:transaction_ip,:transaction_description,:transaction_method,:frequency,:billing,:taxes_ids)";
                    $stmt = $this->dbConn->prepare($insert_query);
                    $stmt->bindValue(':product_name', $productName, PDO::PARAM_STR);
                    $stmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
                    $stmt->bindValue(':seller_id', $userId, PDO::PARAM_STR);
                    $stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
                    $stmt->bindValue(':currency_code', $currencyCode, PDO::PARAM_STR);
                    $stmt->bindValue(':base_amount', $baseAmount, PDO::PARAM_STR);
                    $stmt->bindValue(':featured', $featured, PDO::PARAM_STR);
                    $stmt->bindValue(':urgent', $urgent, PDO::PARAM_STR);
                    $stmt->bindValue(':highlight', $highlight, PDO::PARAM_STR);
                    $stmt->bindValue(':transaction_time', $transactionTime, PDO::PARAM_STR);
                    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
                    $stmt->bindValue(':payment_id', $paymentId, PDO::PARAM_STR);
                    $stmt->bindValue(':transaction_gatway', $paymentGatway, PDO::PARAM_STR);
                    $stmt->bindValue(':transaction_ip', $transactionIpAddress, PDO::PARAM_STR);
                    $stmt->bindValue(':transaction_description', $transactionDescription, PDO::PARAM_STR);
                    $stmt->bindValue(':transaction_method', $transactionMethod, PDO::PARAM_STR);
                    $stmt->bindValue(':frequency', $frequency, PDO::PARAM_STR);
                    $stmt->bindValue(':billing', $billing, PDO::PARAM_STR);
                    $stmt->bindValue(':taxes_ids', $taxesIds, PDO::PARAM_STR);
                    $stmt->execute();
                    // Get the last insert ID
                    $transactionId = $this->dbConn->lastInsertId();
                    if (!empty($transactionId)) {
                        $response = ["status" => true, "code" => 200, "Message" => "Transaction successfully done"];
                        $this->returnResponse($response);
                    } else {
                        $response = ["status" => false, "code" => 400, "Message" => "Something went wrong"];
                        $this->returnResponse($response);
                    }
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Something went wrong"];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "Requst token not found"];
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

    public function pushNotificationForApp($token, $title, $message)
    {
        // $token = $this->validateParameter('device_token', $this->param['device_token'], STRING);
        // $title = $this->validateParameter('title', $this->param['title'], STRING);
        // $message = $this->validateParameter('message', $this->param['message'], STRING);

        $fields = array(
            'to' => $token,
            'priority' => "high",
            'notification' => array(
                "title" => $title,
                "sound" => "default",
                "body" => $message,
            ),
            'data' => array("message" => $message),
        );

        $headers = array(
            $this->fcmUrl,
            'Content-Type: application/json',
            'Authorization: key=' . $this->fcmServerKey,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        // dd($result);
        if ($result === false) {
            die('Problem occurred: ' . curl_error($ch));
        }
        curl_close($ch);
        $decode_result = json_decode($result);

        if (!empty($decode_result)) {
            if ($decode_result->failure == 1) {
                // $response = ["status" => false, "code" => 400, "Message" => "Notification not send.", "data" => $fields , "result" => $decode_result];
                // $this->returnResponse($response);
                return false;
            }
        }
        return true;
        // $response = ["status" => true, "code" => 200, "Message" => "Notification successfully send.", "data" => $fields , "result" => $decode_result];
        // $this->returnResponse($response);

    }

    public function sendPushNotificationToApp($token = '', $title = '', $message = '')
    {
        $token = $this->validateParameter('device_token', $this->param['device_token'], STRING);
        $title = $this->validateParameter('title', $this->param['title'], STRING);
        $message = $this->validateParameter('message', $this->param['message'], STRING);

        $fields = array(
            'to' => $token,
            'priority' => "high",
            'notification' => array(
                "title" => $title,
                "sound" => "default",
                "body" => $message,
            ),
            'data' => array("message" => $message),
        );

        $headers = array(
            $this->fcmUrl,
            'Content-Type: application/json',
            'Authorization: key=' . $this->fcmServerKey,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        // dd($result);
        if ($result === false) {
            die('Problem occurred: ' . curl_error($ch));
        }
        curl_close($ch);
        $decode_result = json_decode($result);

        if (!empty($decode_result)) {
            if ($decode_result->failure == 1) {
                $response = ["status" => false, "code" => 400, "Message" => "Notification not send.", "data" => $fields, "result" => $decode_result];
                $this->returnResponse($response);
            }
        }

        $response = ["status" => true, "code" => 200, "Message" => "Notification successfully send.", "data" => $fields, "result" => $decode_result];
        $this->returnResponse($response);

    }

    public function addToCart()
    {
        $product_id = $this->validateParameter('product_id', $this->param['product_id'], INTEGER);
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if (!empty($payload->userId)) {
                $getItem = "SELECT * FROM `ad_product_add_to_cart_mobile` WHERE `user_id`=:user_id AND `product_id`=:product_id";
                $getItemData = $this->dbConn->prepare($getItem);
                $getItemData->bindValue(':user_id', $payload->userId, PDO::PARAM_STR);
                $getItemData->bindValue(':product_id', $product_id, PDO::PARAM_STR);
                $getItemData->execute();
                $getItemData = $getItemData->fetch(PDO::FETCH_ASSOC);
                if (!empty($getItemData)) {
                    $response = ["status" => true, "code" => 200, "Message" => "Product already added into your cart list."];
                    $this->returnResponse($response);
                }

                $getProduct = "SELECT id,product_name,price,screen_shot FROM `ad_product` WHERE `id`=:product_id";
                $getProductDetails = $this->dbConn->prepare($getProduct);
                $getProductDetails->bindValue(':product_id', $product_id, PDO::PARAM_STR);
                $getProductDetails->execute();
                $getProductDetails = $getProductDetails->fetch(PDO::FETCH_ASSOC);
                if (empty($getProductDetails['id'])) {
                    $response = ["status" => false, "code" => 400, "Message" => "Product not found."];
                    $this->returnResponse($response);
                }
                $product_qty = 1;
                if (!empty($getProductDetails['screen_shot'])) {
                    $screenShotArr = explode(",", $getProductDetails['screen_shot']);
                    if (count($screenShotArr) > 0) {
                        $product_image = $this->display_image_url . 'storage/products/' . $screenShotArr[0];
                    }
                } else {
                    $product_image = '';
                }
                $insert_query = "INSERT INTO `ad_product_add_to_cart_mobile` (`user_id`,`product_id`,`product_name`,`product_price`,`product_qty`,`product_image`) VALUES(:user_id,:product_id,:product_name,:product_price,:product_qty,:product_image)";
                $stmt = $this->dbConn->prepare($insert_query);
                $stmt->bindValue(':user_id', $payload->userId, PDO::PARAM_STR);
                $stmt->bindValue(':product_id', $product_id, PDO::PARAM_STR);
                $stmt->bindValue(':product_name', $getProductDetails['product_name'], PDO::PARAM_STR);
                $stmt->bindValue(':product_price', $getProductDetails['price'], PDO::PARAM_STR);
                $stmt->bindValue(':product_qty', $product_qty, PDO::PARAM_STR);
                $stmt->bindValue(':product_image', $product_image, PDO::PARAM_STR);
                $stmt->execute();
                // Get the last insert ID
                $transactionId = $this->dbConn->lastInsertId();
                if (!empty($transactionId)) {
                    $response = ["status" => true, "code" => 200, "Message" => "Product successfully added into your cart list."];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Something went wrong."];
                    $this->returnResponse($response);
                }

            } else {
                $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                $this->returnResponse($response);
            }
        }
    }

    public function deleteFromCart()
    {
        $product_id = $this->validateParameter('product_id', $this->param['product_id'], INTEGER);
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if (!empty($payload->userId)) {
                $stmt = $this->dbConn->prepare('DELETE FROM ad_product_add_to_cart_mobile WHERE user_id =:user_id AND product_id =:product_id');
                $stmt->bindParam(":user_id", $payload->userId);
                $stmt->bindParam(":product_id", $product_id);
                if ($stmt->execute()) {
                    $response = ["status" => true, "code" => 200, "Message" => "Product has been removed from your cart list."];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Something is wrong."];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                $this->returnResponse($response);
            }
        }
    }

    public function getCartItems()
    {
        $responseArr = array();
        $totalAmt = 0;
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if (!empty($payload->userId)) {
                $getItem = "SELECT * FROM `ad_product_add_to_cart_mobile` WHERE `user_id`=:user_id";
                $getItemData = $this->dbConn->prepare($getItem);
                $getItemData->bindValue(':user_id', $payload->userId, PDO::PARAM_STR);
                $getItemData->execute();
                $getItemData = $getItemData->fetchAll(PDO::FETCH_ASSOC);
                // $totalItem = count($getItemData);
                if (count($getItemData) > 0) {
                    $responseArr['products'] = $getItemData;
                    foreach ($getItemData as $key => $row) {
                        $totalAmt = $totalAmt + $row['product_price'];
                    }
                    $responseArr['total'] = $totalAmt;
                } else {
                    $responseArr['products'] = [];
                    $responseArr['total'] = 0;
                }
                $response = ["status" => true, "code" => 200, "Message" => "Cart listing", "data" => $responseArr];
                $this->returnResponse($response);
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                $this->returnResponse($response);
            }
        }
    }

    public function finalCallAppyPayApi()
    {
        $transactionId = $this->validateParameter('transactionId', $this->param['transactionId'], STRING);
        $merchantTransactionId = $this->validateParameter('merchantTransactionId', $this->param['merchantTransactionId'], STRING);
        $accessToken = $this->validateParameter('accessToken', $this->param['accessToken'], STRING);
        $orderId = $this->validateParameter('orderId', $this->param['orderId'], STRING);
        $order_at = date('Y-m-d H:i:s');
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if (!empty($payload->userId)) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://gwy-api-tst.appypay.co.ao/v2.0/charges/' . $transactionId,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: ' . $accessToken . '',
                        'Cookie: ARRAffinity=61d869b39c80b800fa66bdafa3089846c090ff86f5d67f887aa34253e56405fb; ARRAffinitySameSite=61d869b39c80b800fa66bdafa3089846c090ff86f5d67f887aa34253e56405fb',
                    ),
                ));

                $appyPayApiResponse = curl_exec($curl);
                // Decode the JSON response
                $appyPayApiResponseData = json_decode($appyPayApiResponse, true);
                curl_close($curl);
                //Get Product Id
                $getProduct = "SELECT product_id FROM `ad_shop_order_item` WHERE `order_id`=:order_id";
                $getProductDetails = $this->dbConn->prepare($getProduct);
                $getProductDetails->bindValue(':order_id', $orderId, PDO::PARAM_STR);
                $getProductDetails->execute();
                $getProductDetails = $getProductDetails->fetch(PDO::FETCH_ASSOC);

                //Update code
                $stmt = $this->dbConn->prepare('UPDATE ad_shop_payment SET txn_id=:txn_id,payment_status = :payment_status,order_status = :order_status,total_amount = :total_amount,create_at = :create_at,payment_response = :payment_response,code = :code,message = :message,source = :source,sourceDetails_attempt = :sourceDetails_attempt,sourceDetails_type = :sourceDetails_type,sourceDetails_code = :sourceDetails_code,sourceDetails_message = :sourceDetails_message WHERE merchantTransactionId = :merchantTransactionId');
                // Bind the parameters and execute the statement
                $stmt->bindValue(':merchantTransactionId', $merchantTransactionId, PDO::PARAM_STR);
                $stmt->bindValue(':txn_id', $appyPayApiResponseData['payment']['id'], PDO::PARAM_STR);
                $stmt->bindValue(':payment_status', $appyPayApiResponseData['payment']['transactionEvents']['responseStatus']['successful'], PDO::PARAM_STR);
                $stmt->bindValue(':order_status', $appyPayApiResponseData['payment']['transactionEvents']['responseStatus']['successful'], PDO::PARAM_STR);
                $stmt->bindValue(':total_amount', $appyPayApiResponseData['payment']['amount'], PDO::PARAM_STR);
                $stmt->bindValue(':create_at', $order_at, PDO::PARAM_STR);
                $stmt->bindValue(':payment_response', json_encode($appyPayApiResponseData), PDO::PARAM_STR);
                $stmt->bindValue(':code', $appyPayApiResponseData['payment']['transactionEvents'][0]['responseStatus']['code'], PDO::PARAM_STR);
                $stmt->bindValue(':message', $appyPayApiResponseData['payment']['transactionEvents'][0]['responseStatus']['message'], PDO::PARAM_STR);
                $stmt->bindValue(':source', $appyPayApiResponseData['payment']['transactionEvents'][0]['responseStatus']['source'], PDO::PARAM_STR);
                $stmt->bindValue(':sourceDetails_attempt', $appyPayApiResponseData['payment']['transactionEvents'][0]['responseStatus']['sourceDetails']['attempt'], PDO::PARAM_STR);
                $stmt->bindValue(':sourceDetails_type', $appyPayApiResponseData['payment']['transactionEvents'][0]['responseStatus']['sourceDetails']['type'], PDO::PARAM_STR);
                $stmt->bindValue(':sourceDetails_code', $appyPayApiResponseData['payment']['transactionEvents'][0]['responseStatus']['sourceDetails']['code'], PDO::PARAM_STR);
                $stmt->bindValue(':sourceDetails_message', $appyPayApiResponseData['payment']['transactionEvents'][0]['responseStatus']['sourceDetails']['message'], PDO::PARAM_STR);
                $stmt->execute();

                if ($appyPayApiResponseData['payment']['transactionEvents']['responseStatus']['successful'] == true) {
                    $deleteAddToCartProduct = $this->dbConn->prepare('DELETE FROM ad_product_add_to_cart_mobile WHERE user_id =:user_id AND product_id =:product_id');
                    $deleteAddToCartProduct->bindParam(":user_id", $payload->userId);
                    $deleteAddToCartProduct->bindParam(":product_id", $getProductDetails['product_id']);
                    $deleteAddToCartProduct->execute();
                }
                $response = ["status" => true, "code" => 200, "Message" => "Transaction successfully done.", "merchantTransactionId" => $merchantTransactionId, "transactionId" => $appyPayApiResponseData['id'], "success" => $appyPayApiResponseData['responseStatus']['successful'], "accessToken" => $accessToken, 'orderId' => $orderId];
                $this->returnResponse($response);
            }
        }
    }

    public function checkoutPaypal()
    {
        $prefix = 'TR'; // You can customize the prefix
        $numericId = rand(0, 999999999999); // Generate a random numeric ID
        $numericId = str_pad($numericId, 12, '0', STR_PAD_LEFT);
        $merchantTransactionId = $prefix . $numericId;
        $mobile = $this->validateParameter('mobile', $this->param['mobile'], STRING);

        if (count($this->param['productIds']) > 0) {
            if (!is_array($this->param['productIds'])) {
                $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for productIds. It should be type array.");
            }
        } else {
            $this->throwError(VALIDATE_PARAMETER_DATATYPE, "productIds should not be empty array");
        }

        if (count($this->param['amounts']) > 0) {
            if (!is_array($this->param['amounts'])) {
                $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for amounts. It should be type array.");
            }
        } else {
            $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Amounts should not be empty array");
        }

        $totalAmount = $this->validateParameter('totalAmount', $this->param['totalAmount'], INTEGER);
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if (!empty($payload->userId)) {
                $order_status = 'PENDING';
                $order_at = date("Y-m-d H:i:s");
                $insertSO = "INSERT INTO `ad_shop_order` (`member_id`,`name`,`address`,`mobile`,`email`,`order_status`,`order_at`) VALUES(:member_id,:name,:address,:mobile,:email,:order_status,:order_at)";
                $insertSOST = $this->dbConn->prepare($insertSO);
                $insertSOST->bindValue(':member_id', $payload->userId, PDO::PARAM_STR);
                $insertSOST->bindValue(':name', $payload->name, PDO::PARAM_STR);
                $insertSOST->bindValue(':address', $payload->address, PDO::PARAM_STR);
                $insertSOST->bindValue(':mobile', $payload->phone, PDO::PARAM_STR);
                $insertSOST->bindValue(':email', $payload->email, PDO::PARAM_STR);
                $insertSOST->bindValue(':order_status', $order_status, PDO::PARAM_STR);
                $insertSOST->bindValue(':order_at', $order_at, PDO::PARAM_STR);
                $insertSOST->execute();
                // Get the last insert ID
                $orderId = $this->dbConn->lastInsertId();
                if (!empty($orderId)) {
                    $qty = 1;
                    if (count($this->param['productIds']) > 0) {
                        for ($i = 0; $i < count($this->param['productIds']); $i++) {
                            $productId = !empty($this->param['productIds'][$i]) ? $this->param['productIds'][$i] : 0;
                            $amount = !empty($this->param['amounts'][$i]) ? $this->param['amounts'][$i] : 0;
                            //Get Product Id
                            $getASOI = "SELECT id  FROM `ad_shop_order_item` WHERE `member_id`=:member_id AND product_id =:product_id ";
                            $getASOIDetails = $this->dbConn->prepare($getASOI);
                            $getASOIDetails->bindValue(':member_id', $payload->userId, PDO::PARAM_STR);
                            $getASOIDetails->bindValue(':product_id', $productId, PDO::PARAM_STR);
                            $getASOIDetails->execute();
                            $getASOIDetails = $getASOIDetails->fetch(PDO::FETCH_ASSOC);
                            if (empty($getASOIDetails['id'])) {
                                $insertSOIT = "INSERT INTO `ad_shop_order_item` (`member_id`,`order_id`,`product_id`,`item_price`,`quantity`) VALUES(:member_id,:order_id,:product_id,:item_price,:quantity)";
                                $insertSOSTIT = $this->dbConn->prepare($insertSOIT);
                                $insertSOSTIT->bindValue(':member_id', $payload->userId, PDO::PARAM_STR);
                                $insertSOSTIT->bindValue(':order_id', $orderId, PDO::PARAM_STR);
                                $insertSOSTIT->bindValue(':product_id', $productId, PDO::PARAM_STR);
                                $insertSOSTIT->bindValue(':item_price', $amount, PDO::PARAM_STR);
                                $insertSOSTIT->bindValue(':quantity', $qty, PDO::PARAM_STR);
                                $insertSOSTIT->execute();
                                $curl = curl_init();
                                curl_setopt_array($curl, array(
                                    CURLOPT_URL => 'https://login.microsoftonline.com/appypaydev.onmicrosoft.com/oauth2/token',
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'GET',
                                    CURLOPT_POSTFIELDS => 'grant_type=client_credentials&client_id=5afeadcb-dd1c-4ad1-b5e7-84c9599b6b86&client_secret=LWW8Q~EL3cQ_cfBPmE37DeGVSSOaMj~zFYTxsdBX&resource=2aed7612-de64-46b5-9e59-1f48f8902d14',
                                    CURLOPT_HTTPHEADER => array(
                                        'Content-Type: application/x-www-form-urlencoded',
                                        'Cookie: fpc=AncQbIi-FMVBpMA3DQ_OhVe4iW3OAQAAAFmX_9wOAAAA',
                                    ),
                                ));
                                $responseFromFirstApi = curl_exec($curl);
                                curl_close($curl);
                                // Decode the JSON response
                                $jsonDecodeDataForFirstApi = json_decode($responseFromFirstApi, true);

                                // Access the access token
                                $tokenType = $jsonDecodeDataForFirstApi['token_type'];
                                // $expiresIn = $jsonDecodeDataForFirstApi['expires_in'];
                                // $extExpiresIn = $jsonDecodeDataForFirstApi['ext_expires_in'];
                                // $expiresOn = $jsonDecodeDataForFirstApi['expires_on'];
                                // $notBefore = $jsonDecodeDataForFirstApi['not_before'];
                                // $resource = $jsonDecodeDataForFirstApi['resource'];
                                $accessToken = $jsonDecodeDataForFirstApi['access_token'];
                                if (!empty($accessToken)) {
                                    $authorization = $tokenType . ' ' . $accessToken;
                                    $curl = curl_init();
                                    curl_setopt_array($curl, array(
                                        CURLOPT_URL => 'https://gwy-api-tst.appypay.co.ao/v2.0/charges',
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_ENCODING => '',
                                        CURLOPT_MAXREDIRS => 10,
                                        CURLOPT_TIMEOUT => 0,
                                        CURLOPT_FOLLOWLOCATION => true,
                                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                        CURLOPT_CUSTOMREQUEST => 'POST',
                                        CURLOPT_POSTFIELDS => '{
                                            "amount": "' . $totalAmount . '",
                                            "currency": "AOA",
                                            "description": "Purchased Product",
                                            "merchantTransactionId": "' . $merchantTransactionId . '",
                                            "paymentMethod": "GPO_d16765a2-d951-4f08-9db8-2f9a6b5a8b45",
                                            "paymentInfo": {
                                                "phoneNumber": "' . $mobile . '"
                                            },
                                            "notify": {
                                                "name": "' . $payload->name . '",
                                                "telephone": "' . $payload->phone . '",
                                                "email": "' . $payload->email . '"
                                            }
                                        }',
                                        CURLOPT_HTTPHEADER => array(
                                            'Accept: application/json',
                                            'Accept-Language: pt',
                                            'Assertion: ',
                                            'Content-Type: application/json',
                                            'Authorization: ' . $authorization . '',
                                        ),
                                    ));
                                    $responseFromSecondApi = curl_exec($curl);
                                    // Decode the JSON response
                                    $jsonDecodeDataForSecondApi = json_decode($responseFromSecondApi, true);
                                    curl_close($curl);
                                    // if (!empty($jsonDecodeDataForSecondApi['id']) && $jsonDecodeDataForSecondApi['responseStatus']['successful'] == true) {
                                    if (!empty($jsonDecodeDataForSecondApi['id'])) {
                                        //Get Product Id
                                        $getProduct = "SELECT product_id FROM `ad_shop_order_item` WHERE `order_id`=:order_id";
                                        $getProductDetails = $this->dbConn->prepare($getProduct);
                                        $getProductDetails->bindValue(':order_id', $orderId, PDO::PARAM_STR);
                                        $getProductDetails->execute();
                                        $getProductDetails = $getProductDetails->fetch(PDO::FETCH_ASSOC);

                                        $insertASP = "INSERT INTO `ad_shop_payment` (`merchantTransactionId`,`member_id`,`order_id`,`product_id`,`txn_id`,`payer_id`,`payment_status`,`order_status`,`total_amount`,`create_at`,`payment_response`,`code`,`message`,`source`,`sourceDetails_attempt`,`sourceDetails_type`,`sourceDetails_code`,`sourceDetails_message`) VALUES(:merchantTransactionId,:member_id,:order_id,:product_id,:txn_id,:payer_id,:payment_status,:order_status,:total_amount,:create_at,:payment_response,:code,:message,:source,:sourceDetails_attempt,:sourceDetails_type,:sourceDetails_code,:sourceDetails_message)";
                                        $insertASPT = $this->dbConn->prepare($insertASP);
                                        $insertASPT->bindValue(':merchantTransactionId', $merchantTransactionId, PDO::PARAM_STR);
                                        $insertASPT->bindValue(':member_id', $payload->userId, PDO::PARAM_STR);
                                        $insertASPT->bindValue(':order_id', $orderId, PDO::PARAM_STR);
                                        $insertASPT->bindValue(':product_id', $getProductDetails['product_id'], PDO::PARAM_STR);
                                        $insertASPT->bindValue(':txn_id', $jsonDecodeDataForSecondApi['id'], PDO::PARAM_STR);
                                        $insertASPT->bindValue(':payer_id', '', PDO::PARAM_STR);
                                        $insertASPT->bindValue(':payment_status', $jsonDecodeDataForSecondApi['responseStatus']['successful'], PDO::PARAM_STR);
                                        $insertASPT->bindValue(':order_status', $jsonDecodeDataForSecondApi['responseStatus']['successful'], PDO::PARAM_STR);
                                        $insertASPT->bindValue(':total_amount', $totalAmount, PDO::PARAM_STR);
                                        $insertASPT->bindValue(':create_at', $order_at, PDO::PARAM_STR);
                                        $insertASPT->bindValue(':payment_response', json_encode($jsonDecodeDataForSecondApi), PDO::PARAM_STR);
                                        $insertASPT->bindValue(':code', $jsonDecodeDataForSecondApi['responseStatus']['code'], PDO::PARAM_STR);
                                        $insertASPT->bindValue(':message', $jsonDecodeDataForSecondApi['responseStatus']['message'], PDO::PARAM_STR);
                                        $insertASPT->bindValue(':source', $jsonDecodeDataForSecondApi['responseStatus']['source'], PDO::PARAM_STR);
                                        $insertASPT->bindValue(':sourceDetails_attempt', $jsonDecodeDataForSecondApi['responseStatus']['sourceDetails']['attempt'], PDO::PARAM_STR);
                                        $insertASPT->bindValue(':sourceDetails_type', $jsonDecodeDataForSecondApi['responseStatus']['sourceDetails']['type'], PDO::PARAM_STR);
                                        $insertASPT->bindValue(':sourceDetails_code', $jsonDecodeDataForSecondApi['responseStatus']['sourceDetails']['code'], PDO::PARAM_STR);
                                        $insertASPT->bindValue(':sourceDetails_message', $jsonDecodeDataForSecondApi['responseStatus']['sourceDetails']['message'], PDO::PARAM_STR);
                                        $insertASPT->execute();
                                        if ($jsonDecodeDataForSecondApi['responseStatus']['successful'] == true) {
                                            $deleteAddToCartProduct = $this->dbConn->prepare('DELETE FROM ad_product_add_to_cart_mobile WHERE user_id =:user_id AND product_id =:product_id');
                                            $deleteAddToCartProduct->bindParam(":user_id", $payload->userId);
                                            $deleteAddToCartProduct->bindParam(":product_id", $getProductDetails['product_id']);
                                            $deleteAddToCartProduct->execute();
                                        }
                                        $response = ["status" => true, "code" => 200, "Message" => "Transaction successfully done.", "merchantTransactionId" => $merchantTransactionId, "transactionId" => $jsonDecodeDataForSecondApi['id'], "success" => $jsonDecodeDataForSecondApi['responseStatus']['successful'], "accessToken" => $authorization, 'orderId' => $orderId];
                                        $this->returnResponse($response);
                                    } else {
                                        $response = ["status" => true, "code" => 200, "Message" => "Transaction successfully done.", "merchantTransactionId" => $merchantTransactionId, "transactionId" => $jsonDecodeDataForSecondApi['id'], "success" => $jsonDecodeDataForSecondApi['responseStatus']['successful'], "accessToken" => $authorization, 'orderId' => $orderId];
                                        $this->returnResponse($response);
                                    }
                                }
                            } else {
                                $stmt = $this->dbConn->prepare('UPDATE ad_shop_order_item SET order_id = :order_id WHERE member_id = :member_id AND product_id = :product_id');
                                // Bind the parameters and execute the statement
                                $stmt->bindValue(':member_id', $payload->userId, PDO::PARAM_STR);
                                $stmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
                                $stmt->bindValue(':order_id', $orderId, PDO::PARAM_STR);
                                if ($stmt->execute()) {
                                    $stmt1 = $this->dbConn->prepare('UPDATE ad_shop_payment SET order_id = :order_id WHERE member_id = :member_id AND product_id = :product_id');
                                    // Bind the parameters and execute the statement
                                    $stmt1->bindValue(':member_id', $payload->userId, PDO::PARAM_STR);
                                    $stmt1->bindValue(':product_id', $productId, PDO::PARAM_STR);
                                    $stmt1->bindValue(':order_id', $orderId, PDO::PARAM_STR);
                                    if ($stmt1->execute()) {
                                        $getASOI1 = "SELECT *  FROM `ad_shop_payment` WHERE `member_id`=:member_id AND product_id =:product_id ";
                                        $getASOIDetails1 = $this->dbConn->prepare($getASOI1);
                                        $getASOIDetails1->bindValue(':member_id', $payload->userId, PDO::PARAM_STR);
                                        $getASOIDetails1->bindValue(':product_id', $productId, PDO::PARAM_STR);
                                        $getASOIDetails1->execute();
                                        $getASOIDetails1 = $getASOIDetails1->fetch(PDO::FETCH_ASSOC);
                                        $curl = curl_init();
                                        curl_setopt_array($curl, array(
                                            CURLOPT_URL => 'https://login.microsoftonline.com/appypaydev.onmicrosoft.com/oauth2/token',
                                            CURLOPT_RETURNTRANSFER => true,
                                            CURLOPT_ENCODING => '',
                                            CURLOPT_MAXREDIRS => 10,
                                            CURLOPT_TIMEOUT => 0,
                                            CURLOPT_FOLLOWLOCATION => true,
                                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                            CURLOPT_CUSTOMREQUEST => 'GET',
                                            CURLOPT_POSTFIELDS => 'grant_type=client_credentials&client_id=5afeadcb-dd1c-4ad1-b5e7-84c9599b6b86&client_secret=LWW8Q~EL3cQ_cfBPmE37DeGVSSOaMj~zFYTxsdBX&resource=2aed7612-de64-46b5-9e59-1f48f8902d14',
                                            CURLOPT_HTTPHEADER => array(
                                                'Content-Type: application/x-www-form-urlencoded',
                                                'Cookie: fpc=AncQbIi-FMVBpMA3DQ_OhVe4iW3OAQAAAFmX_9wOAAAA',
                                            ),
                                        ));
                                        $responseFromFirstApi = curl_exec($curl);
                                        curl_close($curl);
                                        // Decode the JSON response
                                        $jsonDecodeDataForFirstApi = json_decode($responseFromFirstApi, true);
        
                                        // Access the access token
                                        $tokenType = $jsonDecodeDataForFirstApi['token_type'];
                                        // $expiresIn = $jsonDecodeDataForFirstApi['expires_in'];
                                        // $extExpiresIn = $jsonDecodeDataForFirstApi['ext_expires_in'];
                                        // $expiresOn = $jsonDecodeDataForFirstApi['expires_on'];
                                        // $notBefore = $jsonDecodeDataForFirstApi['not_before'];
                                        // $resource = $jsonDecodeDataForFirstApi['resource'];
                                        $accessToken = $jsonDecodeDataForFirstApi['access_token'];
                                        $authorization = $tokenType . ' ' . $accessToken;
                                        $response = ["status" => true, "code" => 200, "Message" => "Transaction successfully done.", "merchantTransactionId" => $getASOIDetails1['merchantTransactionId'], "transactionId" => $getASOIDetails1['txn_id'], "success" => $getASOIDetails1['txn_id'], "accessToken" => $authorization, 'orderId' => $orderId];
                                        $this->returnResponse($response);     
                                    }
                                }
                            }
                        }
                    }

                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Something went wrong in order creations."];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                $this->returnResponse($response);
            }
        }
    }

    public function checkoutEventPaypal()
    {
        $prefix = 'TR'; // You can customize the prefix
        $numericId = rand(0, 999999999999); // Generate a random numeric ID
        $numericId = str_pad($numericId, 12, '0', STR_PAD_LEFT);
        $merchantTransactionId = $prefix . $numericId;
        $mobile = $this->validateParameter('mobile', $this->param['mobile'], STRING);
        $productId = $this->validateParameter('productId', $this->param['productId'], INTEGER);
        if (count($this->param['ticketTypeIds']) > 0) {
            // Check if the variables are arrays or not
            if (!is_array($this->param['ticketTypeIds'])) {
                $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ticketTypeIds. It should be type array.");
            }
        } else {
            $this->throwError(VALIDATE_PARAMETER_DATATYPE, "ticketTypeIds should not be empty array");
        }
        if (count($this->param['ticketAmounts']) > 0) {
            // Check if the variables are arrays or not
            if (!is_array($this->param['ticketAmounts'])) {
                $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ticketAmounts. It should be type array.");
            }
        } else {
            $this->throwError(VALIDATE_PARAMETER_DATATYPE, "ticketAmounts should not be empty array");
        }
        if (count($this->param['ticketQuantities']) > 0) {
            // Check if the variables are arrays or not
            if (!is_array($this->param['ticketQuantities'])) {
                $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ticketQuantities. It should be type array.");
            }
        } else {
            $this->throwError(VALIDATE_PARAMETER_DATATYPE, "ticketQuantities should not be empty array");
        }

        $totalAmount = $this->validateParameter('totalAmount', $this->param['totalAmount'], INTEGER);
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if (!empty($payload->userId)) {
                $order_status = 'PENDING';
                $order_at = date("Y-m-d H:i:s");
                $insertSO = "INSERT INTO `ad_shop_order` (`member_id`,`name`,`address`,`mobile`,`email`,`order_status`,`order_at`) VALUES(:member_id,:name,:address,:mobile,:email,:order_status,:order_at)";
                $insertSOST = $this->dbConn->prepare($insertSO);
                $insertSOST->bindValue(':member_id', $payload->userId, PDO::PARAM_STR);
                $insertSOST->bindValue(':name', $payload->name, PDO::PARAM_STR);
                $insertSOST->bindValue(':address', $payload->address, PDO::PARAM_STR);
                $insertSOST->bindValue(':mobile', $payload->phone, PDO::PARAM_STR);
                $insertSOST->bindValue(':email', $payload->email, PDO::PARAM_STR);
                $insertSOST->bindValue(':order_status', $order_status, PDO::PARAM_STR);
                $insertSOST->bindValue(':order_at', $order_at, PDO::PARAM_STR);
                $insertSOST->execute();
                // Get the last insert ID
                $orderId = $this->dbConn->lastInsertId();
                if (!empty($orderId)) {
                    if (!empty($this->param['ticketTypeIds']) && !empty($this->param['ticketAmounts']) && !empty($this->param['ticketQuantities'])) {
                        for ($i = 0; $i < count($this->param['ticketTypeIds']); $i++) {
                            $insertSOIT = "INSERT INTO `ad_shop_order_item` (`order_id`,`product_id`,`event_type_id`,`item_price`,`quantity`) VALUES(:order_id,:product_id,:event_type_id,:item_price,:quantity)";
                            $insertSOSTIT = $this->dbConn->prepare($insertSOIT);
                            $insertSOSTIT->bindValue(':order_id', $orderId, PDO::PARAM_STR);
                            $insertSOSTIT->bindValue(':product_id', $productId, PDO::PARAM_STR);
                            $insertSOSTIT->bindValue(':event_type_id', $this->param['ticketTypeIds'][$i], PDO::PARAM_STR);
                            $insertSOSTIT->bindValue(':item_price', $this->param['ticketAmounts'][$i], PDO::PARAM_STR);
                            $insertSOSTIT->bindValue(':quantity', $this->param['ticketQuantities'][$i], PDO::PARAM_STR);
                            $insertSOSTIT->execute();
                        }
                    }

                    // $response = ["status" => true, "code" => 200, "Message" => "Your event successfully booked.", "merchantTransactionId" => $orderId];
                    // $this->returnResponse($response);
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://login.microsoftonline.com/appypaydev.onmicrosoft.com/oauth2/token',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_POSTFIELDS => 'grant_type=client_credentials&client_id=5afeadcb-dd1c-4ad1-b5e7-84c9599b6b86&client_secret=LWW8Q~EL3cQ_cfBPmE37DeGVSSOaMj~zFYTxsdBX&resource=2aed7612-de64-46b5-9e59-1f48f8902d14',
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded',
                            'Cookie: fpc=AncQbIi-FMVBpMA3DQ_OhVe4iW3OAQAAAFmX_9wOAAAA',
                        ),
                    ));
                    $responseFromFirstApi = curl_exec($curl);
                    curl_close($curl);
                    // Decode the JSON response
                    $jsonDecodeDataForFirstApi = json_decode($responseFromFirstApi, true);

                    // Access the access token
                    $tokenType = $jsonDecodeDataForFirstApi['token_type'];
                    // $expiresIn = $jsonDecodeDataForFirstApi['expires_in'];
                    // $extExpiresIn = $jsonDecodeDataForFirstApi['ext_expires_in'];
                    // $expiresOn = $jsonDecodeDataForFirstApi['expires_on'];
                    // $notBefore = $jsonDecodeDataForFirstApi['not_before'];
                    // $resource = $jsonDecodeDataForFirstApi['resource'];
                    $accessToken = $jsonDecodeDataForFirstApi['access_token'];
                    if (!empty($accessToken)) {
                        $authorization = $tokenType . ' ' . $accessToken;
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://gwy-api-tst.appypay.co.ao/v2.0/charges',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS => '{
                                "amount": "' . $totalAmount . '",
                                "currency": "AOA",
                                "description": "Purchased Product",
                                "merchantTransactionId": "' . $merchantTransactionId . '",
                                "paymentMethod": "GPO_d16765a2-d951-4f08-9db8-2f9a6b5a8b45",
                                "paymentInfo": {
                                    "phoneNumber": "' . $mobile . '"
                                },
                                "notify": {
                                    "name": "' . $payload->name . '",
                                    "telephone": "' . $payload->phone . '",
                                    "email": "' . $payload->email . '"
                                }
                            }',
                            CURLOPT_HTTPHEADER => array(
                                'Accept: application/json',
                                'Accept-Language: pt',
                                'Assertion: ',
                                'Content-Type: application/json',
                                'Authorization: ' . $authorization . '',
                            ),
                        ));
                        $responseFromSecondApi = curl_exec($curl);
                        // Decode the JSON response
                        $jsonDecodeDataForSecondApi = json_decode($responseFromSecondApi, true);
                        curl_close($curl);
                        // if (!empty($jsonDecodeDataForSecondApi['id']) && $jsonDecodeDataForSecondApi['responseStatus']['successful'] == true) {
                        if (!empty($jsonDecodeDataForSecondApi['id'])) {
                            //Get Product Id
                            $getProduct = "SELECT product_id FROM `ad_shop_order_item` WHERE `order_id`=:order_id";
                            $getProductDetails = $this->dbConn->prepare($getProduct);
                            $getProductDetails->bindValue(':order_id', $orderId, PDO::PARAM_STR);
                            $getProductDetails->execute();
                            $getProductDetails = $getProductDetails->fetch(PDO::FETCH_ASSOC);

                            $insertASP = "INSERT INTO `ad_shop_payment` (`merchantTransactionId`,`member_id`,`order_id`,`product_id`,`txn_id`,`payer_id`,`payment_status`,`order_status`,`total_amount`,`create_at`,`payment_response`,`code`,`message`,`source`,`sourceDetails_attempt`,`sourceDetails_type`,`sourceDetails_code`,`sourceDetails_message`) VALUES(:merchantTransactionId,:member_id,:order_id,:product_id,:txn_id,:payer_id,:payment_status,:order_status,:total_amount,:create_at,:payment_response,:code,:message,:source,:sourceDetails_attempt,:sourceDetails_type,:sourceDetails_code,:sourceDetails_message)";
                            $insertASPT = $this->dbConn->prepare($insertASP);
                            $insertASPT->bindValue(':merchantTransactionId', $merchantTransactionId, PDO::PARAM_STR);
                            $insertASPT->bindValue(':member_id', $payload->userId, PDO::PARAM_STR);
                            $insertASPT->bindValue(':order_id', $orderId, PDO::PARAM_STR);
                            $insertASPT->bindValue(':product_id', $getProductDetails['product_id'], PDO::PARAM_STR);
                            $insertASPT->bindValue(':txn_id', $jsonDecodeDataForSecondApi['id'], PDO::PARAM_STR);
                            $insertASPT->bindValue(':payer_id', '', PDO::PARAM_STR);
                            $insertASPT->bindValue(':payment_status', $jsonDecodeDataForSecondApi['responseStatus']['successful'], PDO::PARAM_STR);
                            $insertASPT->bindValue(':order_status', $jsonDecodeDataForSecondApi['responseStatus']['successful'], PDO::PARAM_STR);
                            $insertASPT->bindValue(':total_amount', $totalAmount, PDO::PARAM_STR);
                            $insertASPT->bindValue(':create_at', $order_at, PDO::PARAM_STR);
                            $insertASPT->bindValue(':payment_response', json_encode($jsonDecodeDataForSecondApi), PDO::PARAM_STR);
                            $insertASPT->bindValue(':code', $jsonDecodeDataForSecondApi['responseStatus']['code'], PDO::PARAM_STR);
                            $insertASPT->bindValue(':message', $jsonDecodeDataForSecondApi['responseStatus']['message'], PDO::PARAM_STR);
                            $insertASPT->bindValue(':source', $jsonDecodeDataForSecondApi['responseStatus']['source'], PDO::PARAM_STR);
                            $insertASPT->bindValue(':sourceDetails_attempt', $jsonDecodeDataForSecondApi['responseStatus']['sourceDetails']['attempt'], PDO::PARAM_STR);
                            $insertASPT->bindValue(':sourceDetails_type', $jsonDecodeDataForSecondApi['responseStatus']['sourceDetails']['type'], PDO::PARAM_STR);
                            $insertASPT->bindValue(':sourceDetails_code', $jsonDecodeDataForSecondApi['responseStatus']['sourceDetails']['code'], PDO::PARAM_STR);
                            $insertASPT->bindValue(':sourceDetails_message', $jsonDecodeDataForSecondApi['responseStatus']['sourceDetails']['message'], PDO::PARAM_STR);
                            $insertASPT->execute();

                            $response = ["status" => true, "code" => 200, "Message" => "Transaction successfully done.", "merchantTransactionId" => $merchantTransactionId, "transactionId" => $jsonDecodeDataForSecondApi['id'], "success" => $jsonDecodeDataForSecondApi['responseStatus']['successful'], "accessToken" => $authorization, 'orderId' => $orderId];
                            $this->returnResponse($response);
                        } else {
                            $response = ["status" => true, "code" => 200, "Message" => "Transaction successfully done.", "merchantTransactionId" => $merchantTransactionId, "transactionId" => $jsonDecodeDataForSecondApi['id'], "success" => $jsonDecodeDataForSecondApi['responseStatus']['successful'], "accessToken" => $authorization, 'orderId' => $orderId];
                            $this->returnResponse($response);
                        }

                    }
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Something went wrong in order creations."];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                $this->returnResponse($response);
            }
        }
    }

    /*public function checkoutPaypal()
    {
    if (count($this->param['productIds']) > 0) {
    if (!is_array($this->param['productIds'])) {
    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for productIds. It should be type array.");
    }
    } else {
    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "productIds should not be empty array");
    }

    if (count($this->param['amounts']) > 0) {
    if (!is_array($this->param['amounts'])) {
    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for amounts. It should be type array.");
    }
    } else {
    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Amounts should not be empty array");
    }

    $totalAmount = $this->validateParameter('totalAmount', $this->param['totalAmount'], INTEGER);
    $token = $this->getBearerToken();
    if (!empty($token)) {
    $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
    if (!empty($payload->userId)) {
    $order_status = 'PENDING';
    $order_at = date("Y-m-d H:i:s");
    $insertSO = "INSERT INTO `ad_shop_order` (`member_id`,`name`,`address`,`mobile`,`email`,`order_status`,`order_at`) VALUES(:member_id,:name,:address,:mobile,:email,:order_status,:order_at)";
    $insertSOST = $this->dbConn->prepare($insertSO);
    $insertSOST->bindValue(':member_id', $payload->userId, PDO::PARAM_STR);
    $insertSOST->bindValue(':name', $payload->name, PDO::PARAM_STR);
    $insertSOST->bindValue(':address', $payload->address, PDO::PARAM_STR);
    $insertSOST->bindValue(':mobile', $payload->phone, PDO::PARAM_STR);
    $insertSOST->bindValue(':email', $payload->email, PDO::PARAM_STR);
    $insertSOST->bindValue(':order_status', $order_status, PDO::PARAM_STR);
    $insertSOST->bindValue(':order_at', $order_at, PDO::PARAM_STR);
    $insertSOST->execute();
    // Get the last insert ID
    $orderId = $this->dbConn->lastInsertId();
    if (!empty($orderId)) {
    $qty = 1;
    if (count($this->param['productIds']) > 0) {
    for ($i = 0; $i < count($this->param['productIds']); $i++) {
    $productId = !empty($this->param['productIds'][$i]) ? $this->param['productIds'][$i] : 0;
    $amount = !empty($this->param['amounts'][$i]) ? $this->param['amounts'][$i] : 0;
    $insertSOIT = "INSERT INTO `ad_shop_order_item` (`order_id`,`product_id`,`item_price`,`quantity`) VALUES(:order_id,:product_id,:item_price,:quantity)";
    $insertSOSTIT = $this->dbConn->prepare($insertSOIT);
    $insertSOSTIT->bindValue(':order_id', $orderId, PDO::PARAM_STR);
    $insertSOSTIT->bindValue(':product_id', $productId, PDO::PARAM_STR);
    $insertSOSTIT->bindValue(':item_price', $amount, PDO::PARAM_STR);
    $insertSOSTIT->bindValue(':quantity', $qty, PDO::PARAM_STR);
    $insertSOSTIT->execute();
    }
    }
    $response = ["status" => true, "code" => 200, "Message" => "Transaction successfully done.", "merchantTransactionId" => $orderId];
    $this->returnResponse($response);
    } else {
    $response = ["status" => false, "code" => 400, "Message" => "Something went wrong in order creations."];
    $this->returnResponse($response);
    }
    } else {
    $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
    $this->returnResponse($response);
    }
    }
    }*/

    /*public function checkoutEventPaypal()
    {
    $productId = $this->validateParameter('productId', $this->param['productId'], INTEGER);
    if (count($this->param['ticketTypeIds']) > 0) {
    // Check if the variables are arrays or not
    if (!is_array($this->param['ticketTypeIds'])) {
    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ticketTypeIds. It should be type array.");
    }
    } else {
    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "ticketTypeIds should not be empty array");
    }
    if (count($this->param['ticketAmounts']) > 0) {
    // Check if the variables are arrays or not
    if (!is_array($this->param['ticketAmounts'])) {
    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ticketAmounts. It should be type array.");
    }
    } else {
    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "ticketAmounts should not be empty array");
    }
    if (count($this->param['ticketQuantities']) > 0) {
    // Check if the variables are arrays or not
    if (!is_array($this->param['ticketQuantities'])) {
    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ticketQuantities. It should be type array.");
    }
    } else {
    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "ticketQuantities should not be empty array");
    }

    $totalAmount = $this->validateParameter('totalAmount', $this->param['totalAmount'], INTEGER);
    $token = $this->getBearerToken();
    if (!empty($token)) {
    $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
    if (!empty($payload->userId)) {
    $order_status = 'PENDING';
    $order_at = date("Y-m-d H:i:s");
    $insertSO = "INSERT INTO `ad_shop_order` (`member_id`,`name`,`address`,`mobile`,`email`,`order_status`,`order_at`) VALUES(:member_id,:name,:address,:mobile,:email,:order_status,:order_at)";
    $insertSOST = $this->dbConn->prepare($insertSO);
    $insertSOST->bindValue(':member_id', $payload->userId, PDO::PARAM_STR);
    $insertSOST->bindValue(':name', $payload->name, PDO::PARAM_STR);
    $insertSOST->bindValue(':address', $payload->address, PDO::PARAM_STR);
    $insertSOST->bindValue(':mobile', $payload->phone, PDO::PARAM_STR);
    $insertSOST->bindValue(':email', $payload->email, PDO::PARAM_STR);
    $insertSOST->bindValue(':order_status', $order_status, PDO::PARAM_STR);
    $insertSOST->bindValue(':order_at', $order_at, PDO::PARAM_STR);
    $insertSOST->execute();
    // Get the last insert ID
    $orderId = $this->dbConn->lastInsertId();
    if (!empty($orderId)) {
    if (!empty($this->param['ticketTypeIds']) && !empty($this->param['ticketAmounts']) && !empty($this->param['ticketQuantities'])) {
    for ($i = 0; $i < count($this->param['ticketTypeIds']); $i++) {
    $insertSOIT = "INSERT INTO `ad_shop_order_item` (`order_id`,`product_id`,`event_type_id`,`item_price`,`quantity`) VALUES(:order_id,:product_id,:event_type_id,:item_price,:quantity)";
    $insertSOSTIT = $this->dbConn->prepare($insertSOIT);
    $insertSOSTIT->bindValue(':order_id', $orderId, PDO::PARAM_STR);
    $insertSOSTIT->bindValue(':product_id', $productId, PDO::PARAM_STR);
    $insertSOSTIT->bindValue(':event_type_id', $this->param['ticketTypeIds'][$i], PDO::PARAM_STR);
    $insertSOSTIT->bindValue(':item_price', $this->param['ticketAmounts'][$i], PDO::PARAM_STR);
    $insertSOSTIT->bindValue(':quantity', $this->param['ticketQuantities'][$i], PDO::PARAM_STR);
    $insertSOSTIT->execute();
    }
    }

    $response = ["status" => true, "code" => 200, "Message" => "Your event successfully booked.", "merchantTransactionId" => $orderId];
    $this->returnResponse($response);
    } else {
    $response = ["status" => false, "code" => 400, "Message" => "Something went wrong in order creations."];
    $this->returnResponse($response);
    }
    } else {
    $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
    $this->returnResponse($response);
    }
    }
    }*/

    public function deleteUserPost()
    {
        $product_id = $this->validateParameter('product_id', $this->param['product_id'], INTEGER);
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if (!empty($payload->userId)) {
                $stmt = $this->dbConn->prepare('DELETE FROM ad_product WHERE user_id =:user_id AND id =:product_id');
                $stmt->bindParam(":user_id", $payload->userId);
                $stmt->bindParam(":product_id", $product_id);
                if ($stmt->execute()) {
                    $response = ["status" => true, "code" => 200, "Message" => "Product successfully deleted."];
                    $this->returnResponse($response);
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Something is wrong."];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                $this->returnResponse($response);
            }
        }
    }

    public function deleteUserEventPost()
    {
        $product_id = $this->validateParameter('product_id', $this->param['product_id'], INTEGER);
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if (!empty($payload->userId)) {
                $stmt = $this->dbConn->prepare('DELETE FROM ad_product WHERE user_id =:user_id AND id =:product_id');
                $stmt->bindParam(":user_id", $payload->userId);
                $stmt->bindParam(":product_id", $product_id);
                if ($stmt->execute()) {
                    $stmt = $this->dbConn->prepare('DELETE FROM ad_product_event_types WHERE product_id=:product_id');
                    $stmt->bindParam(":product_id", $product_id);
                    if ($stmt->execute()) {
                        $response = ["status" => true, "code" => 200, "Message" => "Event successfully deleted."];
                        $this->returnResponse($response);
                    } else {
                        $response = ["status" => true, "code" => 200, "Message" => "Event successfully deleted."];
                        $this->returnResponse($response);
                    }
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Something is wrong."];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                $this->returnResponse($response);
            }
        }
    }

    public function deleteUserTrainingPost()
    {
        $product_id = $this->validateParameter('product_id', $this->param['product_id'], INTEGER);
        $token = $this->getBearerToken();
        if (!empty($token)) {
            $payload = GlobalJWT::decode($token, SECRETE_KEY, ['HS256']);
            if (!empty($payload->userId)) {
                $stmt = $this->dbConn->prepare('DELETE FROM ad_product WHERE user_id =:user_id AND id =:product_id');
                $stmt->bindParam(":user_id", $payload->userId);
                $stmt->bindParam(":product_id", $product_id);
                if ($stmt->execute()) {
                    $stmt = $this->dbConn->prepare('DELETE FROM ad_training_gallery WHERE product_id=:product_id');
                    $stmt->bindParam(":product_id", $product_id);
                    if ($stmt->execute()) {
                        $response = ["status" => true, "code" => 200, "Message" => "Training successfully deleted."];
                        $this->returnResponse($response);
                    } else {
                        $response = ["status" => true, "code" => 200, "Message" => "Training successfully deleted."];
                        $this->returnResponse($response);
                    }
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Something is wrong."];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "User not found by given token."];
                $this->returnResponse($response);
            }
        }
    }
}
