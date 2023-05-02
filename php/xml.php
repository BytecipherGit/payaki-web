<?php
global $config, $link;

header('Content-type: text/xml');

const FREQ_DAILY = 'daily',
FREQ_WEEKLY = 'weekly',
PRIORITY_MAIN = 1.0,
PRIORITY_PRODUCT = 0.9,
PRIORITY_DOC = 0.8,
PRIORITY_BLOG = 0.7,
PRIORITY_PAGE = 0.6;

$today_date = date('Y-m-d');

function text_replace_for_xml($text){
    $text = str_replace("&","&amp;",stripslashes($text));
    $text = str_replace('<','&lt;',$text);
    $text = str_replace('>','&gt;',$text);
    return $text;
}

// start sitemap
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
    "<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
    "xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\" " .
    "xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" " .
    "xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\" " .
    "xmlns:video=\"http://www.google.com/schemas/sitemap-video/1.1\" " .
    "xmlns:news=\"http://www.google.com/schemas/sitemap-news/0.9\" " .
    "xmlns:mobile=\"http://www.google.com/schemas/sitemap-mobile/1.0\" " .
    "xmlns:pagemap=\"http://www.google.com/schemas/sitemap-pagemap/1.0\" " .
    "xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">" .
    "<url>" .
    "<loc>{$config['site_url']}</loc>" .
    "<lastmod>$today_date</lastmod>" .
    "<changefreq>" . FREQ_DAILY . "</changefreq>" .
    "<priority>" . PRIORITY_MAIN . "</priority>" .
    "</url>";

$rows = ORM::for_table($config['db']['pre'].'catagory_main')
    ->select_many('cat_id','slug')
    ->order_by_asc('cat_order')
    ->find_many();

foreach ($rows as $info)
{
    $slug = text_replace_for_xml($info['slug']);
    $catlink = $link['SEARCH_CAT'].'/'.$slug;

    echo '<url>';
    echo '<loc>' . $catlink . '</loc>';
    echo '<changefreq>monthly</changefreq>';
    echo '<priority>1</priority>';
    echo '</url>';

    $rows1 = ORM::for_table($config['db']['pre'].'catagory_sub')
        ->select('slug')
        ->where('main_cat_id', $info['cat_id'])
        ->find_many();

    foreach ($rows1 as $info1){
        $sub_slug = text_replace_for_xml($info1['slug']);
        $subcatlink = $link['SEARCH_CAT'].'/'.$slug.'/'.$sub_slug;
        echo '<url>';
        echo '<loc>' . $subcatlink . '</loc>';
        echo '<changefreq>monthly</changefreq>';
        echo '<priority>1</priority>';
        echo '</url>';
    }
}


$rows = ORM::for_table($config['db']['pre'].'product')
    ->select_many('id','product_name','created_at','updated_at','featured','urgent','highlight')
    ->order_by_asc('id')
    ->find_many();

foreach ($rows as $info)
{

    $premium = 0;
    if ($info['featured'] == "1"){
        $premium = 1;
    }

    if($info['urgent'] == "1")
    {
        $premium = 1;
    }

    if($info['highlight'] == "1")
    {
        $premium = 1;
    }

    $pro_url = create_slug($info['product_name']);
    $item_link = $link['POST-DETAIL'].'/' . $info['id'] . '/'.$pro_url;
    $item_created_at = date('Y-m-d', strtotime($info['created_at']));
    $item_updated_at = date('Y-m-d', strtotime($info['updated_at']));
    echo '<url>';
    echo '<loc>' . $item_link . '</loc>';
    echo '<lastmod>'.$item_updated_at.'</lastmod>';
    echo '<changefreq>daily</changefreq>';
    if($premium == 1){
        echo '<priority>1</priority>';
    }
    echo '</url>';
}
$rows = ORM::for_table($config['db']['pre'].'user')
    ->select_many('username','created_at','updated_at')
    ->order_by_asc('id')
    ->find_many();
foreach ($rows as $info)
{
    $url = create_slug($info['username']);
    $user_link = $link['PROFILE'].'/'.$url;
    $created_at = date('Y-m-d', strtotime($info['created_at']));
    $updated_at = date('Y-m-d', strtotime($info['updated_at']));
    echo '<url>';
    echo '<loc>' . $user_link . '</loc>';
    echo '<lastmod>'.$updated_at.'</lastmod>';
    echo '<changefreq>daily</changefreq>';
    echo '</url>';
}

// get recent blog
$sql = "SELECT id,title FROM `".$config['db']['pre']."blog` WHERE status = 'publish' ORDER BY created_at DESC";
$rows = ORM::for_table($config['db']['pre'].'blog')->raw_query($sql)->find_many();

$rows = ORM::for_table($config['db']['pre'].'blog')
    ->select_many('id','title','created_at','updated_at')
    ->whereEqual('status','publish')
    ->order_by_desc('id')
    ->find_many();

foreach ($rows as $info) {
    $blog_link = $link['BLOG-SINGLE'].'/'.$info['id'].'/'.create_slug($info['title']);
    $created_at = date('Y-m-d', strtotime($info['updated_at']));
    echo '<url>';
    echo '<loc>' . $blog_link . '</loc>';
    echo '<lastmod>'.$created_at.'</lastmod>';
    echo '<changefreq>daily</changefreq>';
    echo '<priority>1</priority>';
    echo '</url>';
}

echo '</urlset>';
?>