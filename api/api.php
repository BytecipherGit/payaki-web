<?php
require_once 'JWT.php';
use Firebase\JWT\JWT;

class Api extends Rest
{
    public $dbConn;
    public function __construct()
    {
        parent::__construct();
        $db = new DbConnect;
        $this->dbConn = $db->connect();

    }

    public function login()
    {
        try {
            $email = $this->validateParameter('email', $this->param['email'], STRING);
            $password = $this->validateParameter('pass', $this->param['pass'], STRING);
            $stmt = $this->dbConn->prepare("SELECT * FROM ad_user WHERE email =:email OR username=:username");
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":username", $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($user)) {
                $this->returnResponse(INVALID_USER_PASS, "Email or Password is incorrect.");
            }

            //Check User password is valid or not
            if (!password_verify($password, $user['password_hash'])) {
                $this->returnResponse(INVALID_USER_PASS, "Email or Password is incorrect.");
            }
            // Check User Status && User status 0 for active, 1 for verify, 2 for de-active
            if ($user['status'] === '2') {
                $this->returnResponse(USER_NOT_ACTIVE, "User is not activated. Please contact to admin.");
            }
            $payload = [
                'iat' => time(),
                'iss' => 'localhost',
                'exp' => time() + (15*60),
                'userId' => $user['id'],
            ];
            $token = JWT::encode($payload, SECRETE_KEY, "HS256");
            $response = [
                "status"    => true,
                "code"    => 200,
                "Message"    => "Login successfully.",
                "token"    => $token,
                "data"  => $user
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
                $response = [
                    "status"    => true,
                    "code"    => 200,
                    "Message"    => "User already exist with this email or mobile."
                ];
                $this->returnResponse($response);

            else:
                $insert_query = "INSERT INTO `ad_user` (`username`,`name`,`email`,`phone`,`status`,`password_hash`) VALUES(:username,:name,:email,:phone,:status,:password_hash)";
                $insert_stmt = $this->dbConn->prepare($insert_query);
                // DATA BINDING
                $insert_stmt->bindValue(':username', htmlspecialchars(strip_tags($uName)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':name', htmlspecialchars(strip_tags($fName)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $insert_stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
                $insert_stmt->bindValue(':status', 0, PDO::PARAM_STR);
                $insert_stmt->bindValue(':password_hash', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                $insert_stmt->execute();
                $response = [
                    "status"    => true,
                    "code"    => 200,
                    "Message"    => "You have successfully registered."
                ];
                $this->returnResponse($response);
            endif;

        } catch (Exception $e) {
            $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
        }
    }
}
