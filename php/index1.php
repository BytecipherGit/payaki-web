<?php
//Loop for Premium Ads and (featured = 1 or urgent = 1 or highlight = 1)

$item = get_items("","active",true,1,10,"id",true);
$item2 = get_items("","active",false,1,10,"id",true);

$category = get_maincategory();
$cat_dropdown = get_categories_dropdown($lang);

$sortname = check_user_country();

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
    $cat[$info['cat_id']]['main_ads_count'] = $totalAdsMaincat;
    $count = 1;
    $result1 = ORM::for_table($config['db']['pre'].'catagory_sub')
        ->where('main_cat_id', $info['cat_id'])
        ->limit(4)
        ->find_many();
    foreach ($result1 as $info1) {
        $totalads = get_items_count(false,"active",false,$info1['sub_cat_id'],null,true);

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
            $cat[$info['cat_id']]['sub_title'] .= '<li><a href="'.$link['SITEMAP'].'" style="color: #6f6f6f;text-decoration: underline;">'.$lang['VIEW_MORE'].'...</a>('.$totalAdsMaincat.')</li>';
        $count++;
    }
}

// Output to template
$page = new HtmlTemplate ('templates/'.$config['tpl_name'].'/index.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header($lang['HOME_IMAGE']));
$page->SetLoop ('ITEM', $item);
$page->SetLoop ('ITEM2', $item2);
$page->SetParameter('POST_PREMIUM_LISTING', count($item));
$page->SetLoop ('CATEGORY',$category);
$page->SetParameter ('CAT_DROPDOWN',$cat_dropdown);
$page->SetLoop ('CAT',$cat);
if(checkloggedin()) {
    $page->SetParameter('USER_ID', $_SESSION['user']['id']);
}else{
    $page->SetParameter('USER_ID', "");
}
$page->SetParameter('BANNER_IMAGE', $config['home_banner']);
$page->SetParameter('SPECIFIC_COUNTRY', $sortname);
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
?>