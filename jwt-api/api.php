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
            $countryCode = $this->validateParameter('country_code', $this->param['country_code'], STRING);
            $phone = $this->validateParameter('phone', $this->param['phone'], STRING);
            $password = $this->validateParameter('pass', $this->param['pass'], STRING);
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
                $insert_query = "INSERT INTO `ad_user` (`username`,`name`,`email`,`country_code`,`phone`,`status`,`password_hash`,`otp`) VALUES(:username,:name,:email,:country_code,:phone,:status,:password_hash,:otp)";
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
                $paylod = ['iat' => time(), 'iss' => 'localhost', 'exp' => time() + (36000), 'userId' => $last_id];
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

    public function createSlug($productName) {
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

            $userId = $this->validateParameter('user_id', $this->param['user_id'], STRING, false);
            $featured = $this->validateParameter('featured', $this->param['featured'], STRING, false);
            $urgent = $this->validateParameter('urgent', $this->param['urgent'], INTEGER, false);
            $highlight = $this->validateParameter('highlight', $this->param['highlight'], INTEGER, false);
            $product_name = $this->validateParameter('product_name', $this->param['product_name'], STRING, false);
            if(!empty($product_name)){
                $slug = $this->createSlug($product_name);
            }
            $description = $this->validateParameter('description', $this->param['description'], STRING, false);
            $category = $this->validateParameter('category', $this->param['category'], STRING, false);
            $sub_category = $this->validateParameter('sub_category', $this->param['sub_category'], STRING, false);
            $price = $this->validateParameter('price', $this->param['price'], STRING, false);
            $negotiable = $this->validateParameter('negotiable', $this->param['negotiable'], STRING, false);
            $productImages = $this->validateParameter('productImages', $this->param['productImages'], STRING, false);

            $location = $this->validateParameter('location', $this->param['location'], STRING, false);
            $city = $this->validateParameter('city', $this->param['city'], STRING, false);
            $state = $this->validateParameter('state', $this->param['state'], STRING, false);
            $country = $this->validateParameter('country', $this->param['country'], STRING, false);
            $latlong = $this->validateParameter('latlong', $this->param['latlong'], STRING, false);
            $username = $this->validateParameter('username', $this->param['username'], STRING, false);
            $email = $this->validateParameter('email', $this->param['email'], STRING, false);
            $phone = $this->validateParameter('phone', $this->param['phone'], STRING, false);
            
            // $urgent = $this->validateParameter('urgent', $this->param['urgent'], STRING, false);
            // $highlight = $this->validateParameter('highlight', $this->param['highlight'], INTEGER, false);
            // $slug = $this->validateParameter('slug', $this->param['slug'], STRING, false);
            // $phone = $this->validateParameter('phone', $this->param['phone'], STRING, false);
            // $hide_phone = $this->validateParameter('hide_phone', $this->param['hide_phone'], STRING, false);
            // $city = $this->validateParameter('city', $this->param['city'], STRING, false);
            // $state = $this->validateParameter('state', $this->param['state'], STRING, false);
            // $country = $this->validateParameter('country', $this->param['country'], STRING, false);
            // $latlong = $this->validateParameter('latlong', $this->param['latlong'], STRING, false);
            // $screen_shot = $this->validateParameter('screen_shot', $this->param['screen_shot'], STRING, false);
            // $tag = $this->validateParameter('tag', $this->param['tag'], STRING, false);
            // $view = $this->validateParameter('view', $this->param['view'], STRING, false);

            $sql = 'INSERT INTO ad_product (id, status, user_id, featured, urgent, highlight, product_name, slug, description, category, sub_category, price, negotiable, phone, hide_phone, location, city, state, country, latlong, screen_shot, tag, view, created_at, updated_at, expire_date, featured_exp_date, urgent_exp_date, highlight_exp_date, admin_seen, emailed, hide) VALUES(null, :status, :user_id, :featured, :urgent, :highlight, :product_name, :slug, :description, :category, :sub_category, :price, :negotiable, :phone, :hide_phone, :location, :city, :state, :country, :latlong, :screen_shot, :tag, :view, :created_at, :updated_at, :expire_date, :featured_exp_date, :urgent_exp_date, :highlight_exp_date, :admin_seen, :emailed, :hide)';

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindParam(':status', 'pending');
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':featured', $featured);
            $stmt->bindParam(':urgent', $urgent);
            $stmt->bindParam(':highlight', $highlight);
            $stmt->bindParam(':product_name', $product_name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':sub_category', $sub_category);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':negotiable', $negotiable);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':hide_phone', $hide_phone);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':state', $state);
            $stmt->bindParam(':country', $country);
            $stmt->bindParam(':latlong', $latlong);
            $stmt->bindParam(':screen_shot', $screen_shot);
            $stmt->bindParam(':tag', $tag);
            $stmt->bindParam(':view', $view);
            $stmt->bindParam(':created_at', $created_at);
            $stmt->bindParam(':updated_at', $updated_at);
            $stmt->bindParam(':expire_date', $expire_date);
            $stmt->bindParam(':featured_exp_date', $featured_exp_date);
            $stmt->bindParam(':urgent_exp_date', $urgent_exp_date);
            $stmt->bindParam(':highlight_exp_date', $highlight_exp_date);
            $stmt->bindParam(':admin_seen', $admin_seen);
            $stmt->bindParam(':emailed', $emailed);
            $stmt->bindParam(':hide', $hide);
            
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
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

    public function uploadFile()
    {
        try {
            $image = $this->validateParameter('image', $this->param['image'], STRING);
            $image_name = '';
            if (strlen($image) > 0) {
                $image_name = round(microtime(true) * 1000) . ".jpg";
                $image_upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/PAYAKI/storage/image/' . $image_name;
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
            $productImages = $this->validateParameter('product_images', $this->param['product_images'], STRING);
            echo '<pre>';
            print_r($productImages);
            exit;
            /*$image_name = '';
            if (strlen($image) > 0) {
                $image_name = round(microtime(true) * 1000) . ".jpg";
                $image_upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/PAYAKI/storage/image/' . $image_name;
                $flag = file_put_contents($image_upload_dir, base64_decode($image));
                if ($flag) {
                    
                } else {
                    $response = ["status" => false, "code" => 400, "Message" => "Something went wrong"];
                    $this->returnResponse($response);
                }
            } else {
                $response = ["status" => false, "code" => 400, "Message" => "Please post image"];
                $this->returnResponse($response);
            }*/

        } catch (Exception $e) {
            $response = ["status" => false, "code" => 400, "Message" => $e->getMessage()];
            $this->returnResponse($response);
        }
    }
}
