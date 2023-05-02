<?php
$start = microtime(true);
$limit = 12;

if(isset($match['params']['country'])) {
    if ($match['params']['country'] != ""){
        change_user_country($match['params']['country']);
    }
}

$sortname = check_user_country();

if($latlong = get_lat_long_of_country($sortname)){
    $mapLat     =  $latlong['lat'];
    $mapLong    =  $latlong['lng'];
}else{
    $mapLat     =  get_option("home_map_latitude");
    $mapLong    =  get_option("home_map_longitude");
}

//Get cities

$popular = array();
$count = 1;

$result = ORM::for_table($config['db']['pre'].'cities')
    ->select_many('id','asciiname')
    ->where(array(
        'active' => '1'
    ))
    ->order_by_desc('population')
    ->limit(18)
    ->find_many();
foreach ($result as $info) {
    $id = $info['id'];
    $name = $info['asciiname'];

    $city_count = ORM::for_table($config['db']['pre'].'product')
        ->where('city', $id)
        ->count();

    $popular[$count]['tpl'] =  '<li><a href= "'.$config['site_url'].'/city/'.$id.'/'.$name.'" class="selectme" data-id="'.$id.'" data-name="'.$name.'"><span>'.$name.' ('.$city_count.')</span></a></li>';
    $count++;
}

//Loop for Premium Ads and (featured = 1 or urgent = 1 or highlight = 1)

$item = get_items("","active",true,1,$limit,"id",true,true,"DESC");
$item2 = get_items("","active",false,1,$limit,"id",true);

$category = get_maincategory();
$cat_dropdown = get_categories_dropdown($lang);

$result = ORM::for_table($config['db']['pre'].'catagory_main')
    ->order_by_asc('cat_order')
    ->find_many();
foreach ($result as $info) {
    if($config['lang_code'] != 'en' && $config['userlangsel'] == '1'){
        $maincat = get_category_translation("main",$info['cat_id']);
        if(isset($maincat['title']) && $maincat['title'] != "") {
            $info['cat_name'] = $maincat['title'];
            $info['slug'] = $maincat['slug'];
        }
    }
    $cat[$info['cat_id']]['icon'] = $info['icon'];
    $cat[$info['cat_id']]['main_title'] = $info['cat_name'];
    $cat[$info['cat_id']]['main_id'] = $info['cat_id'];
    $cat[$info['cat_id']]['picture'] = $info['picture'];
    $cat[$info['cat_id']]['catlink'] = $link['SEARCH_CAT'].'/'.$info['slug'];

    $totalAdsMaincat = get_items_count(false,"active",false,null,$info['cat_id'],true);
    //$totalAdsMaincat = 0;
    $cat[$info['cat_id']]['main_ads_count'] = $totalAdsMaincat;
    $count = 1;

    $result1 = ORM::for_table($config['db']['pre'].'catagory_sub')
        ->where('main_cat_id', $info['cat_id'])
        ->limit(4)
        ->find_many();
    foreach ($result1 as $info1) {
        //$totalads = get_items_count(false,"active",false,$info1['sub_cat_id'],null,true);

        if($config['lang_code'] != 'en' && $config['userlangsel'] == '1'){
            $subcat = get_category_translation("sub",$info1['sub_cat_id']);
            if(isset($subcat['title']) && $subcat['title'] != "") {
                $info1['sub_cat_name'] = $subcat['title'];
                $info1['slug'] = $subcat['slug'];
            }
        }
        $subcatlink = $link['SEARCH_CAT'].'/'.$info['slug'].'/'.$info1['slug'];

        if($count == 1)
            $cat[$info['cat_id']]['sub_title'] = '<li><a href="'.$subcatlink.'" title="'.$info1['sub_cat_name'].'">'.$info1['sub_cat_name'].'</a></li>';
        else
            $cat[$info['cat_id']]['sub_title'] .= '<li><a href="'.$subcatlink.'" title="'.$info1['sub_cat_name'].'">'.$info1['sub_cat_name'].'</a></li>';

        if($count == 4)
            $cat[$info['cat_id']]['sub_title'] .= '<li><a href="'.$link['SITEMAP'].'" style="color: #6f6f6f;text-decoration: underline;">'.$lang['VIEW_MORE'].'...</a></li>';
        $count++;
    }
}

// get recent blog
$sql = "SELECT
MAX(b.id) id, MAX(b.title) title, MAX(b.author) author, MAX(u.name) name, MAX(u.username) username, MAX(u.image) author_pic, GROUP_CONCAT(c.title) categories, GROUP_CONCAT(c.slug) cat_slugs
FROM `".$config['db']['pre']."blog` b
LEFT JOIN `".$config['db']['pre']."admins` u ON u.id = b.author
LEFT JOIN `" . $config['db']['pre'] . "blog_cat_relation` bc ON bc.blog_id = b.id
LEFT JOIN `" . $config['db']['pre'] . "blog_categories` c ON bc.category_id = c.id
WHERE b.status = 'publish' GROUP BY b.id ORDER BY b.created_at DESC LIMIT 3";
$rows = ORM::for_table($config['db']['pre'].'blog')->raw_query($sql)->find_many();
$recent_blog = array();
foreach ($rows as $info) {
    $recent_blog[$info['id']]['id'] = $info['id'];
    $recent_blog[$info['id']]['title'] = $info['title'];
    $recent_blog[$info['id']]['image'] = !empty($info['image'])?$info['image']:'default.png';
    $recent_blog[$info['id']]['description'] = strlimiter(strip_tags(stripslashes($info['description'])),100);
    $recent_blog[$info['id']]['author'] = $info['name'];
    $recent_blog[$info['id']]['author_link'] = $link['BLOG-AUTHOR'].'/'.$info['username'];
    $recent_blog[$info['id']]['author_pic'] = !empty($info['author_pic'])?$info['author_pic']:'default_user.png';
    $recent_blog[$info['id']]['created_at'] = timeAgo($info['created_at']);
    $recent_blog[$info['id']]['link'] = $link['BLOG-SINGLE'].'/'.$info['id'].'/'.create_slug($info['title']);

    $categories = explode(',',$info['categories']);
    $cat_slugs = explode(',',$info['cat_slugs']);
    $arr = array();
    for($i = 0; $i < count($categories); $i++){
        $arr[] = '<a href="'.$link['BLOG-CAT'].'/'.$cat_slugs[$i].'">'.$categories[$i].'</a>';
    }
    $recent_blog[$info['id']]['categories'] = implode(', ',$arr);
}

// get testimonials
$rows = ORM::for_table($config['db']['pre'] . 'testimonials')
    ->order_by_desc('id')
    ->limit(5)
    ->find_many();
$testimonials = array();
foreach ($rows as $row) {
    $testimonials[$row['id']]['id'] = $row['id'];
    $testimonials[$row['id']]['name'] = $row['name'];
    $testimonials[$row['id']]['designation'] = $row['designation'];
    $testimonials[$row['id']]['content'] = $row['content'];
    $testimonials[$row['id']]['image'] = !empty($row['image']) ? $row['image'] : 'default_user.png';
}

/**
 * Memebrship Plans
 * Start
 */
$sub_info = get_user_membership_detail(isset($_SESSION['user']['id'])?$_SESSION['user']['id']:null);
$sub_types = array();

// custom settings
$plan_custom = ORM::for_table($config['db']['pre'].'plan_options')
    ->where('active', 1)
    ->order_by_asc('position')
    ->find_many();

$plan = json_decode(get_option('free_membership_plan'), true);
if($plan['status']){
    if($plan['id'] == $sub_info['id']) {
        $sub_types[$plan['id']]['Selected'] = 1;
    } else {
        $sub_types[$plan['id']]['Selected'] = 0;
    }

    $sub_types[$plan['id']]['id'] = $plan['id'];
    $sub_types[$plan['id']]['title'] = $plan['name'];
    $sub_types[$plan['id']]['monthly_price'] = price_format(0,$config['currency_code']);
    $sub_types[$plan['id']]['annual_price'] = price_format(0,$config['currency_code']);
    $sub_types[$plan['id']]['lifetime_price'] = price_format(0,$config['currency_code']);

    $settings = $plan['settings'];
    $sub_types[$plan['id']]['limit'] = ($settings['ad_limit'] == "999")? $lang['UNLIMITED']: $settings['ad_limit'];
    $sub_types[$plan['id']]['duration'] = $settings['ad_duration'];
    $sub_types[$plan['id']]['featured_fee'] = $settings['featured_project_fee'];
    $sub_types[$plan['id']]['urgent_fee'] = $settings['urgent_project_fee'];
    $sub_types[$plan['id']]['highlight_fee'] = $settings['highlight_project_fee'];
    $sub_types[$plan['id']]['featured_duration'] = $settings['featured_duration'];
    $sub_types[$plan['id']]['urgent_duration'] = $settings['urgent_duration'];
    $sub_types[$plan['id']]['highlight_duration'] = $settings['highlight_duration'];
    $sub_types[$plan['id']]['top_search_result'] = $settings['top_search_result'];
    $sub_types[$plan['id']]['show_on_home'] = $settings['show_on_home'];
    $sub_types[$plan['id']]['show_in_home_search'] = $settings['show_in_home_search'];

    $sub_types[$plan['id']]['custom_settings'] = '';
    if(!empty($plan_custom)) {
        foreach ($plan_custom as $custom) {
            if(!empty($custom['title']) && trim($custom['title']) != '') {
                $tpl = '<li><span class="icon-text no"><i class="icon-feather-x-circle margin-right-2"></i></span> ' . $custom['title'] . '</li>';

                if (isset($settings['custom'][$custom['id']]) && $settings['custom'][$custom['id']] == '1') {
                    $tpl = '<li><span class="icon-text yes"><i class="icon-feather-check-circle margin-right-2"></i></span> ' . $custom['title'] . '</li>';
                }
                $sub_types[$plan['id']]['custom_settings'] .= $tpl;
            }
        }
    }
}

$plan = json_decode(get_option('trial_membership_plan'), true);
if($plan['status']){
    if($plan['id'] == $sub_info['id']) {
        $sub_types[$plan['id']]['Selected'] = 1;
    } else {
        $sub_types[$plan['id']]['Selected'] = 0;
    }

    $sub_types[$plan['id']]['id'] = $plan['id'];
    $sub_types[$plan['id']]['title'] = $plan['name'];
    $sub_types[$plan['id']]['monthly_price'] = price_format(0,$config['currency_code']);
    $sub_types[$plan['id']]['annual_price'] = price_format(0,$config['currency_code']);
    $sub_types[$plan['id']]['lifetime_price'] = price_format(0,$config['currency_code']);

    $settings = $plan['settings'];
    $sub_types[$plan['id']]['limit'] = ($settings['ad_limit'] == "999")? $lang['UNLIMITED']: $settings['ad_limit'];
    $sub_types[$plan['id']]['duration'] = $settings['ad_duration'];
    $sub_types[$plan['id']]['featured_fee'] = $settings['featured_project_fee'];
    $sub_types[$plan['id']]['urgent_fee'] = $settings['urgent_project_fee'];
    $sub_types[$plan['id']]['highlight_fee'] = $settings['highlight_project_fee'];
    $sub_types[$plan['id']]['featured_duration'] = $settings['featured_duration'];
    $sub_types[$plan['id']]['urgent_duration'] = $settings['urgent_duration'];
    $sub_types[$plan['id']]['highlight_duration'] = $settings['highlight_duration'];
    $sub_types[$plan['id']]['top_search_result'] = $settings['top_search_result'];
    $sub_types[$plan['id']]['show_on_home'] = $settings['show_on_home'];
    $sub_types[$plan['id']]['show_in_home_search'] = $settings['show_in_home_search'];

    $sub_types[$plan['id']]['custom_settings'] = '';
    if(!empty($plan_custom)) {
        foreach ($plan_custom as $custom) {
            if(!empty($custom['title']) && trim($custom['title']) != '') {
                $tpl = '<li><span class="icon-text no"><i class="icon-feather-x-circle margin-right-2"></i></span> ' . $custom['title'] . '</li>';

                if (isset($settings['custom'][$custom['id']]) && $settings['custom'][$custom['id']] == '1') {
                    $tpl = '<li><span class="icon-text yes"><i class="icon-feather-check-circle margin-right-2"></i></span> ' . $custom['title'] . '</li>';
                }
                $sub_types[$plan['id']]['custom_settings'] .= $tpl;
            }
        }
    }
}

$total_monthly = $total_annual = $total_lifetime = 0;

$rows = ORM::for_table($config['db']['pre'].'plans')
    ->where('status', '1')
    ->find_many();

foreach ($rows as $plan)
{
    if($plan['id'] == $sub_info['id']) {
        $sub_types[$plan['id']]['Selected'] = 1;
    } else {
        $sub_types[$plan['id']]['Selected'] = 0;
    }

    $sub_types[$plan['id']]['id'] = $plan['id'];
    $sub_types[$plan['id']]['title'] = $plan['name'];
    $sub_types[$plan['id']]['recommended'] = $plan['recommended'];

    $total_monthly += $plan['monthly_price'];
    $total_annual += $plan['annual_price'];
    $total_lifetime += $plan['lifetime_price'];

    $sub_types[$plan['id']]['monthly_price'] = price_format($plan['monthly_price'],$config['currency_code']);
    $sub_types[$plan['id']]['annual_price'] = price_format($plan['annual_price'],$config['currency_code']);
    $sub_types[$plan['id']]['lifetime_price'] = price_format($plan['lifetime_price'],$config['currency_code']);

    $settings = json_decode($plan['settings'], true);
    $sub_types[$plan['id']]['limit'] = ($settings['ad_limit'] == "999")? $lang['UNLIMITED']: $settings['ad_limit'];
    $sub_types[$plan['id']]['duration'] = $settings['ad_duration'];
    $sub_types[$plan['id']]['featured_fee'] = $settings['featured_project_fee'];
    $sub_types[$plan['id']]['urgent_fee'] = $settings['urgent_project_fee'];
    $sub_types[$plan['id']]['highlight_fee'] = $settings['highlight_project_fee'];
    $sub_types[$plan['id']]['featured_duration'] = $settings['featured_duration'];
    $sub_types[$plan['id']]['urgent_duration'] = $settings['urgent_duration'];
    $sub_types[$plan['id']]['highlight_duration'] = $settings['highlight_duration'];
    $sub_types[$plan['id']]['top_search_result'] = $settings['top_search_result'];
    $sub_types[$plan['id']]['show_on_home'] = $settings['show_on_home'];
    $sub_types[$plan['id']]['show_in_home_search'] = $settings['show_in_home_search'];

    $sub_types[$plan['id']]['custom_settings'] = '';
    if(!empty($plan_custom)) {
        foreach ($plan_custom as $custom) {
            if(!empty($custom['title']) && trim($custom['title']) != '') {
                $tpl = '<li><span class="icon-text no"><i class="icon-feather-x-circle margin-right-2"></i></span> ' . $custom['title'] . '</li>';

                if (isset($settings['custom'][$custom['id']]) && $settings['custom'][$custom['id']] == '1') {
                    $tpl = '<li><span class="icon-text yes"><i class="icon-feather-check-circle margin-right-2"></i></span> ' . $custom['title'] . '</li>';
                }
                $sub_types[$plan['id']]['custom_settings'] .= $tpl;
            }
        }
    }
}
/**
 * Memebrship Plans
 * End
 */

// Output to template

$page = new HtmlTemplate ('templates/'.$config['tpl_name'].'/home-map.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header());
$page->SetLoop ('POPULARCITY',$popular);
$page->SetLoop ('ITEM', $item);
$page->SetLoop ('ITEM2', $item2);
$page->SetParameter('POST_PREMIUM_LISTING', count($item));
$page->SetLoop ('CATEGORY',$category);
$page->SetParameter ('CAT_DROPDOWN',$cat_dropdown);
$page->SetLoop ('CAT',$cat);
$page->SetParameter('BANNER_IMAGE', $config['home_banner']);
$page->SetParameter('LATITUDE', $mapLat);
$page->SetParameter('LONGITUDE', $mapLong);
$page->SetParameter('MAP_COLOR', $config['map_color']);
$page->SetParameter('ZOOM', $config['home_map_zoom']);
$page->SetParameter('DEFAULT_COUNTRY', get_countryName_by_sortname($sortname));
$page->SetParameter('SPECIFIC_COUNTRY', $sortname);

// Get Cron Job Settings
$cron_time = isset($config['cron_time']) ? $config['cron_time'] : time();
$cron_exec_time = isset($config['cron_exec_time']) ? $config['cron_exec_time'] : "86400";
if((time()-$cron_exec_time) > $cron_time) {
    run_cron_job();
}
$page->SetLoop('RECENT_BLOG', $recent_blog);
$page->SetLoop('TESTIMONIALS', $testimonials);
$page->SetLoop ('SUB_TYPES', $sub_types);
$page->SetParameter('TOTAL_MONTHLY', $total_monthly);
$page->SetParameter('TOTAL_ANNUAL', $total_annual);
$page->SetParameter('TOTAL_LIFETIME', $total_lifetime);
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
//echo "Execution time : ".$time_elapsed_secs = microtime(true) - $start." Seconds";
?>