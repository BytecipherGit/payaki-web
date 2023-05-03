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
    protected $Port = 465;

    public function __construct()
    {
        parent::__construct();
    }

    public function login()
    {
        $email = $this->validateParameter('email', $this->param['email'], STRING);
        $password = $this->validateParameter('pass', $this->param['pass'], STRING);
        try {
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE email =:email OR username=:username");
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":username", $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($user)) {
                $response = ["status" => false, "code" => 422, "Message" => "Email or Password is incorrect."];
                $this->returnResponse($response);
            }

            //Check User password is valid or not
            if (!password_verify($password, $user['password_hash'])) {
                $response = ["status" => false, "code" => 422, "Message" => "Email or Password is incorrect."];
                $this->returnResponse($response);
            }
            // Check User Status && User status 0 for active, 1 for verify, 2 for de-active
            if ($user['status'] === '2') {
                $response = ["status" => false, "code" => 422, "Message" => "User account is de-activated. Please contact to admin."];
                $this->returnResponse($response);
            }

            $paylod = [
                'iat' => time(),
                'iss' => 'localhost',
                'exp' => time() + (36000),
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

    public function register()
    {
        try {
            $fName = $this->validateParameter('full_name', $this->param['full_name'], STRING);
            $uName = $this->validateParameter('user_name', $this->param['user_name'], STRING);
            $email = $this->validateParameter('email', $this->param['email'], STRING);
            $phone = $this->validateParameter('phone', $this->param['phone'], STRING);
            $password = $this->validateParameter('pass', $this->param['pass'], STRING);
            $check_email = "SELECT `email` FROM `ad_user` WHERE `email`=:email OR `phone`=:phone";
            $check_email_stmt = $this->dbConn->prepare($check_email);
            $check_email_stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $check_email_stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
            $check_email_stmt->execute();

            if ($check_email_stmt->rowCount()):
                $response = ["status" => true, "code" => 200, "Message" => "User already exist with this email or mobile."];
                $this->returnResponse($response);

            else:
                $otp = mt_rand(111111, 999999);
                $insert_query = "INSERT INTO `ad_user` (`username`,`name`,`email`,`phone`,`status`,`password_hash`,`otp`) VALUES(:username,:name,:email,:phone,:status,:password_hash,:otp)";
                $insert_stmt = $this->dbConn->prepare($insert_query);
                // DATA BINDING
                $insert_stmt->bindValue(':username', htmlspecialchars(strip_tags($uName)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':name', htmlspecialchars(strip_tags($fName)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $insert_stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
                $insert_stmt->bindValue(':status', 0, PDO::PARAM_STR);
                $insert_stmt->bindValue(':password_hash', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                $insert_stmt->bindValue(':otp', $otp, PDO::PARAM_STR);
                $insert_stmt->execute();
                $subject = 'Plese verify OTP';
                $body = 'Your verification OTP is ' . $otp;
                $this->sendMail($email, $subject, $body);
                $response = ["status" => true, "code" => 200, "Message" => "You have successfully registered."];
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
                $stmt = $this->dbConn->prepare('UPDATE ad_user SET isEmailVerified = :isEmailVerified WHERE id = :id');

                // Bind the parameters and execute the statement
                $stmt->bindValue(':id', $user['id'], PDO::PARAM_STR);
                $stmt->bindValue(':isEmailVerified', true, PDO::PARAM_STR);
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
                $stmt = $this->dbConn->prepare('UPDATE ad_user SET isPhoneVerified = :isPhoneVerified WHERE id = :id');

                // Bind the parameters and execute the statement
                $stmt->bindValue(':id', $user['id'], PDO::PARAM_STR);
                $stmt->bindValue(':isPhoneVerified', true, PDO::PARAM_STR);
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
            if (!empty($mobile)) {
                $getuser = "SELECT `id` FROM `ad_user` WHERE `phone`=:phone";
                $userData = $this->dbConn->prepare($getuser);
                $userData->bindValue(':phone', $mobile, PDO::PARAM_STR);
                $userData->execute();
                $userData = $userData->fetch(PDO::FETCH_ASSOC);
                if ($userData['id']) {
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
                    $response = ["status" => false, "code" => 422, "Message" => "User not found."];
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
                    $response = ["status" => false, "code" => 422, "Message" => "OTP do not match."];
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
                    $response = ["status" => false, "code" => 422, "Message" => "Password not updated."];
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
                    $response = ["status" => false, "code" => 422, "Message" => "User not found by given token."];
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
                $paylod = ['iat' => time(), 'iss' => 'localhost', 'exp' => time() + (36000), 'userId' => $user['id']];
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
                $paylod = ['iat' => time(), 'iss' => 'localhost', 'exp' => time() + (36000), 'userId' => $last_id];
                $token = GlobalJWT::encode($paylod, SECRETE_KEY);
                $response = ["status" => true, "code" => 200, "Message" => "Login successfully.", "token" => $token, "data" => $user];
                $this->returnResponse($response);
            }

        } catch (Exception $e) {
            $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
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
}
