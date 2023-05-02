<?php
if(checkloggedin())
{
    update_lastactive();
    $ses_userdata = get_user_data($_SESSION['user']['username']);

    $author_image = $ses_userdata['image'];
    $author_lastactive = $ses_userdata['lastactive'];
    $author_country = $ses_userdata['country'];
    $country_code = check_user_country();
    $phone_code = ORM::for_table($config['db']['pre'].'countries')
        ->select('phone')
        ->where('code',$country_code)
        ->find_one();

    $phone_code = $phone_code['phone'];
    $created_at = date('d-m-Y', strtotime(str_replace('-','/', $ses_userdata['created_at'])));

    $notify_cat = explode(',', $ses_userdata['notify_cat']);

    $conditions = array(
        'user_id' => $_SESSION['user']['id'],
    );
    $mobile_num = ORM::for_table($config['db']['pre'].'mobile_numbers')
        ->where($conditions)
        ->find_one();
    if(isset($mobile_num['user_id'])){
        $mobile_number = $mobile_num['mobile_number'];
        $mobile_number_verified = $mobile_num['verified'];
    }else{
        $mobile_number = '';
        $mobile_number_verified = '0';
    }

    $category = get_maincategory($notify_cat,"checked");

    if(!isset($_POST['submit']))
    {
        // Output to template
        $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/dashboard.tpl');
        $page->SetParameter ('OVERALL_HEADER', create_header($lang['DASHBOARD']));
        $page->SetLoop ('CATEGORY',$category);
        $page->SetParameter ('RESUBMITADS', resubmited_ads_count($_SESSION['user']['id']));
        $page->SetParameter ('HIDDENADS', hidden_ads_count($_SESSION['user']['id']));
        $page->SetParameter ('PENDINGADS', pending_ads_count($_SESSION['user']['id']));
        $page->SetParameter ('EXPIREADS', expire_ads_count($_SESSION['user']['id']));
        $page->SetParameter ('FAVORITEADS', favorite_ads_count($_SESSION['user']['id']));
        $page->SetParameter ('MYADS', myads_count($_SESSION['user']['id']));
        $page->SetLoop('ERRORS', "");
        $page->SetLoop('COUNTRY', get_country_list($ses_userdata['country']));
        $page->SetParameter ('AUTHORUNAME', ucfirst($ses_userdata['username']));
        $page->SetParameter ('AUTHORNAME', ucfirst($ses_userdata['name']));
        $page->SetParameter ('AUTHORIMG', $author_image);
        $page->SetParameter ('LASTACTIVE', $author_lastactive);
        $page->SetParameter ('EMAIL', $ses_userdata['email']);
        $page->SetParameter ('PHONE_CODE', $phone_code);
        $page->SetParameter ('USER_COUNTRY', strtolower($country_code));
        $page->SetParameter ('PHONE', $mobile_number);
        $page->SetParameter ('PHONE_VERIFY', $mobile_number_verified);
        $page->SetParameter ('POSTCODE', $ses_userdata['postcode']);
        $page->SetParameter ('ADDRESS', $ses_userdata['address']);
        $page->SetParameter ('CITY', $ses_userdata['city']);
        $page->SetParameter ('COUNTRY', $ses_userdata['country']);

        if(check_user_upgrades($_SESSION['user']['id']))
        {
            $sub_info = get_user_membership_detail($_SESSION['user']['id']);
            $page->SetParameter('SUB_TITLE', $sub_info['name']);
            $page->SetParameter('SUB_IMAGE', $sub_info['badge']);
        }else{
            $page->SetParameter('SUB_TITLE','');
            $page->SetParameter('SUB_IMAGE', '');
        }
        $page->SetParameter ('AUTHORTAGLINE', $ses_userdata['tagline']);
        $page->SetParameter ('AUTHORABOUT', stripslashes(nl2br($ses_userdata['description'])));

        $page->SetParameter ('FACEBOOK', $ses_userdata['facebook']);
        $page->SetParameter ('TWITTER', $ses_userdata['twitter']);
        $page->SetParameter ('GOOGLEPLUS', $ses_userdata['googleplus']);
        $page->SetParameter ('INSTAGRAM', $ses_userdata['instagram']);
        $page->SetParameter ('LINKEDIN', $ses_userdata['linkedin']);
        $page->SetParameter ('YOUTUBE', $ses_userdata['youtube']);
        $page->SetParameter ('JOIN_DATE', $created_at);
        $page->SetParameter ('WEBSITE', $ses_userdata['website']);
        $page->SetParameter ('NOTIFY', $ses_userdata['notify']);
        $page->SetLoop ('HTMLPAGE', get_html_pages());
        $page->SetParameter('COPYRIGHT_TEXT', get_option("copyright_text"));
        $page->SetParameter ('OVERALL_FOOTER', create_footer());
        $page->CreatePageEcho();
    }
    else{
        $errors = array();
        if(!isset($_POST['heading']))
            $_POST['heading'] = "";
        if(!isset($_POST['content']))
            $_POST['content'] = "";
        if(!isset($_POST['postcode']))
            $_POST['postcode'] = "";
        if(!isset($_POST['city']))
            $_POST['city'] = "";
        if(!isset($_POST['country']))
            $_POST['country'] = "";
        if(!isset($_POST['notify']))
            $_POST['notify'] = "";

        if(count($errors) == 0){
            if (isset($_FILES['avatar']['name']) && $_FILES['avatar']['name'] != "") {
                $target_dir = ROOTPATH . "/storage/profile/";
                $result = quick_file_upload('avatar', $target_dir);
                if ($result['success']) {
                    $avatarName = $result['file_name'];
                    resizeImage(150, $target_dir . $avatarName, $target_dir . $avatarName);
                    if (file_exists($target_dir . $author_image) && $author_image != 'default_user.png') {
                        unlink($target_dir . $author_image);
                    }
                } else {
                    $errors[]['message'] = $result['error'];
                }
            }else{
                $avatarName = $author_image;
            }
        }

        if(count($errors) > 0)
        {
            $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/dashboard.tpl');
            $page->SetParameter ('OVERALL_HEADER', create_header($lang['DASHBOARD']));
            $page->SetLoop ('CATEGORY',$category);
            $page->SetParameter ('RESUBMITADS', resubmited_ads_count($_SESSION['user']['id']));
            $page->SetParameter ('HIDDENADS', hidden_ads_count($_SESSION['user']['id']));
            $page->SetParameter ('PENDINGADS', pending_ads_count($_SESSION['user']['id']));
            $page->SetParameter ('EXPIREADS', expire_ads_count($_SESSION['user']['id']));
            $page->SetParameter ('FAVORITEADS', favorite_ads_count($_SESSION['user']['id']));
            $page->SetParameter ('MYADS', myads_count($_SESSION['user']['id']));
            $page->SetLoop('ERRORS', $errors);
            $page->SetParameter ('AUTHORUNAME', $_SESSION['user']['username']);
            $page->SetParameter ('AUTHORNAME', $_POST['name']);
            $page->SetParameter ('LASTACTIVE', $author_lastactive);
            $page->SetParameter ('EMAIL', $ses_userdata['email']);
            $page->SetParameter ('PHONE', $mobile_number);
            $page->SetParameter ('PHONE_VERIFY', $mobile_number_verified);
            $page->SetParameter ('POSTCODE', $_POST['postcode']);
            $page->SetParameter ('ADDRESS', $_POST['address']);
            $page->SetParameter ('CITY', $_POST['city']);
            $page->SetParameter ('COUNTRY', $_POST['country']);
            if(check_user_upgrades($_SESSION['user']['id']))
            {
                $sub_info = get_user_membership_detail($_SESSION['user']['id']);
                $page->SetParameter('SUB_TITLE', $sub_info['name']);
                $page->SetParameter('SUB_IMAGE', $sub_info['badge']);
            }else{
                $page->SetParameter('SUB_TITLE','');
                $page->SetParameter('SUB_IMAGE', '');
            }
            $page->SetParameter ('AUTHORTAGLINE', $_POST['heading']);
            $page->SetParameter ('AUTHORABOUT', stripslashes(nl2br($_POST['content'])));

            $page->SetParameter ('FACEBOOK', $_POST['facebook']);
            $page->SetParameter ('TWITTER', $_POST['twitter']);
            $page->SetParameter ('GOOGLEPLUS', $_POST['googleplus']);
            $page->SetParameter ('INSTAGRAM', $_POST['instagram']);
            $page->SetParameter ('LINKEDIN', $_POST['linkedin']);
            $page->SetParameter ('YOUTUBE', $_POST['youtube']);
            $page->SetParameter ('AUTHORIMG', $author_image);
            $page->SetParameter ('WEBSITE', $_POST['website']);
            $page->SetParameter ('NOTIFY', $_POST['notify']);
            $page->SetLoop ('HTMLPAGE', get_html_pages());
            $page->SetParameter('COPYRIGHT_TEXT', get_option("copyright_text"));
            $page->SetParameter ('OVERALL_FOOTER', create_footer());
            $page->CreatePageEcho();
            exit();
        }
        else{
            $notify = isset($_POST['notify']) ? '1' : '0';

            if (isset($_POST['choice']) && is_array($_POST['choice'])) {
                $choice = validate_input(implode(',', $_POST['choice']));
            }else{
                $choice = '';
            }

            $description = validate_input($_POST['content'],true);

            $website_link = addhttp(validate_input($_POST['website']));
            $now = date("Y-m-d H:i:s");
            $user_update = ORM::for_table($config['db']['pre'].'user')->find_one($_SESSION['user']['id']);
            $user_update->set('name', validate_input($_POST['name']));
            $user_update->set('image', $avatarName);
            $user_update->set('tagline', validate_input($_POST['heading']));
            $user_update->set('description', $description);
            $user_update->set('postcode', validate_input($_POST['postcode']));
            $user_update->set('address', validate_input($_POST['address']));
            $user_update->set('city', validate_input($_POST['city']));
            $user_update->set('country', validate_input($_POST['country']));
            $user_update->set('facebook', validate_input($_POST['facebook']));
            $user_update->set('twitter', validate_input($_POST['twitter']));
            $user_update->set('googleplus', validate_input($_POST['googleplus']));
            $user_update->set('instagram', validate_input($_POST['instagram']));
            $user_update->set('linkedin', validate_input($_POST['linkedin']));
            $user_update->set('youtube', validate_input($_POST['youtube']));
            $user_update->set('website', $website_link);
            $user_update->set('notify', $notify);
            $user_update->set('notify_cat', $choice);
            $user_update->set('updated_at', $now);
            $user_update->save();

            ORM::for_table($config['db']['pre'].'notification')
                ->where_equal('user_id', $_SESSION['user']['id'])
                ->delete_many();

            if($notify)
            {
                if(isset($_POST['choice']))
                {
                    foreach ($_POST['choice'] as $key=>$value)
                    {
                        $notification = ORM::for_table($config['db']['pre'].'notification')->create();
                        $notification->user_id = $_SESSION['user']['id'];
                        $notification->cat_id = $key;
                        $notification->user_email = $ses_userdata['email'];
                        $notification->save();
                    }
                }
            }

            transfer($link['DASHBOARD'],'Profile Updated Successfully','Profile Updated Successfully');
            exit;

        }
    }
}
else{
    headerRedirect($link['LOGIN']);
}
?>