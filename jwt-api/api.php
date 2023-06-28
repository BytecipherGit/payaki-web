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

    protected $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
    protected $fcmServerKey = 'AAAAcdsalcE:APA91bE-UusISpW-YJ6QKQAjAwC6O4pCP1AAIvfsR7Dul6-JL2yGh6qAi418dCBxYqy0DvMWp67d2rLmfHZ8EQVbM0ysKbyBlQIipPoATHuyfQTKkhYw9SLtUmJ--HegoumMFGJM6lJL';

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

            $paylod = [
                'iat' => time(),
                'iss' => 'localhost',
                'exp' => time() + (14400000),
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
                $paylod = ['iat' => time(), 'iss' => 'localhost', 'exp' => time() + (14400000), 'userId' => $user_id];
                $token = GlobalJWT::encode($paylod, SECRETE_KEY);

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

                $paylod = ['iat' => time(), 'iss' => 'localhost', 'exp' => time() + (14400000), 'userId' => $user['id']];
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
                $paylod = ['iat' => time(), 'iss' => 'localhost', 'exp' => time() + (14400000), 'userId' => $last_id];
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
                $expire_days = isset($_POST['available_days']) ? $_POST['available_days'] : 7;

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
                $sql = 'INSERT INTO ad_product (id, status, user_id, featured, urgent, highlight, product_name, slug, description, category, sub_category, price, negotiable, phone, hide_phone, location, city, state, country, latlong, screen_shot, tag, view, created_at, updated_at, expire_days, expired_date, expire_date, featured_exp_date, urgent_exp_date, highlight_exp_date, admin_seen, emailed, hide) VALUES(null, :status, :user_id, :featured, :urgent, :highlight, :product_name, :slug, :description, :category, :sub_category, :price, :negotiable, :phone, :hide_phone, :location, :city, :state, :country, :latlong, :screen_shot, :tag, :view, :created_at, :updated_at, :expire_days, :expired_date, :expire_date, :featured_exp_date, :urgent_exp_date, :highlight_exp_date, :admin_seen, :emailed, :hide)';
                $status = 'pending';
                $createdDate = date('Y-m-d H:i:s');
                $featuredExpDate = null;
                $stmt = $this->dbConn->prepare($sql);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':user_id', $userId);
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

                    $get s = "SELECT ar.rating,ar.comments,ar.date,au.username FROM ad_reviews AS ar LEFT JOIN ad_user AS au ON au.id = ar.user_id WHERE ar.productID=:productID AND publish=1";
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
                            $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.id IN ($placeholders) AND ap.status='active'";
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
                        $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.status='expire' AND ap.user_id=:userId";
                        $postData = $this->dbConn->prepare($getpost);
                        $postData->bindValue(':userId', $payload->userId, PDO::PARAM_STR);
                    } elseif (!empty($listType) && $listType == 'pending') {
                        $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.status='pending' AND ap.user_id=:userId";
                        $postData = $this->dbConn->prepare($getpost);
                        $postData->bindValue(':userId', $payload->userId, PDO::PARAM_STR);
                    } elseif (!empty($listType) && $listType == 'all') {
                        $getpost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE ap.status='active' AND ap.user_id=:userId AND ap.expired_date >= :expired_date";
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

    public function getPremiumAndLatestPost()
    {
        try {
            $now = date("Y-m-d H:i:s");
            $responseArr = array();

            //Get Premium Post
            $getPremiumPost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE status='active' AND ap.expired_date >= :expired_date AND (ap.featured = :featured OR ap.urgent = :urgent OR ap.highlight = :highlight) ORDER BY ap.created_at DESC LIMIT 10";
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
            $getLatestPost = "SELECT ap.*,acm.cat_name,acs.sub_cat_name,ac.name as city_name,ads.name as state_name,adc.asciiname as country_name FROM ad_product AS ap LEFT JOIN ad_catagory_main AS acm ON acm.cat_id = ap.category LEFT JOIN ad_catagory_sub AS acs ON acs.sub_cat_id = ap.sub_category LEFT JOIN ad_cities AS ac ON ac.id = ap.city LEFT JOIN ad_subadmin1 AS ads ON ads.code = ac.subadmin1_code LEFT JOIN ad_countries AS adc ON adc.code = ads.country_code WHERE status='active' AND ap.expired_date >= :expired_date ORDER BY ap.created_at DESC LIMIT 10";
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
                            // Select the last insert row
                            $stmt = $this->dbConn->prepare("SELECT * FROM ad_product WHERE id=:id");
                            $stmt->bindParam(':id', $product_id);
                            $stmt->execute();
                            // Fetch the row
                            $product = $stmt->fetch(PDO::FETCH_ASSOC);
                            if (!empty($product['user_id'])) {
                                $getUser = "SELECT device_token FROM ad_user WHERE device_token !='' AND id != :userId";
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
}
