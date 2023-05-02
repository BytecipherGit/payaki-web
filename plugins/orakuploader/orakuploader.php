<?php
define("ROOTPATH", dirname(dirname(__DIR__)));
define("APPPATH", ROOTPATH."/php/");

require_once ROOTPATH . '/includes/autoload.php';
require_once ROOTPATH . '/includes/lang/lang_'.$config['lang'].'.php';
sec_session_start();

if(!isset($_REQUEST['path'])) exit;

$dirback_calc = str_replace('/', '../', preg_replace("#[^\/]#", '', trim($_REQUEST['path'], '/')));
$dirback = $dirback_calc != '' ? '../'.$dirback_calc : "../";

$main_path = $dirback.$_REQUEST["main_path"];
$thumbnail_path = $dirback.$_REQUEST["thumbnail_path"];

function rotateImage($image_name, $path, $degree_lvl)
{
    if($degree_lvl == 4)
    {
        unlink($path."/".$image_name);
        rename($path."/cache/".$image_name, $path."/".$image_name);
        return $image_name;
    }

    if(!file_exists($path."/cache/".$image_name)) {
        @mkdir($path."/cache", 0777);
        copy($path."/".$image_name, $path."/cache/".$image_name);
        unlink($path."/".$image_name);
    }

    $image_extension = @end(explode(".", $image_name));

    switch($image_extension)
    {
        case "jpg":
            @$image = imagecreatefromjpeg($path."/cache/".$image_name);
            break;
        case "jpeg":
            @$image = imagecreatefromjpeg($path."/cache/".$image_name);
            break;
        case "png":
            $image = imagecreatefrompng($path."/cache/".$image_name);
            break;
    }

    $transColor = imagecolorallocatealpha($image, 255, 255, 255, 270);
    $rotated_image = imagerotate($image, -90*$degree_lvl, $transColor);


    switch($image_extension)
    {
        case "jpg":
            header('Content-type: image/jpeg');
            imagejpeg($rotated_image, "$path/$image_name", 100);
            break;
        case "jpeg":
            header('Content-type: image/jpeg');
            imagejpeg($rotated_image, "$path/$image_name", 100);
            break;
        case "png":
            header('Content-type: image/png');
            imagepng($rotated_image, "$path/$image_name");
            break;
    }
    return $image_name;
}

if(isset($_GET['delete']))
{
    if(!is_dir($main_path."/".$_GET['delete'])){
        if(file_exists($main_path."/".$_GET['delete'])) unlink($main_path."/".$_GET['delete']);
    }
    if(!is_dir($thumbnail_path."/".$_GET['delete'])){
        if(file_exists($thumbnail_path."/".$_GET['delete'])) unlink($thumbnail_path."/".$_GET['delete']);
    }
    if(file_exists($main_path."/cache/".$_GET['delete'])) unlink($main_path."/cache/".$_GET['delete']);
    if(file_exists($thumbnail_path."/cache/".$_GET['delete'])) unlink($thumbnail_path."/cache/".$_GET['delete']);
    exit;
}
elseif(isset($_GET['rotate']))
{
    rotateImage($_GET['rotate'], $main_path, $_GET['degree_lvl']);
    rotateImage($_GET['rotate'], $thumbnail_path, $_GET['degree_lvl']);
    echo $_GET['rotate'];
    exit;
}

$target_dir = ROOTPATH . "/storage/products/";
$thumb_dir = ROOTPATH . "/storage/products/thumb/";
$result = quick_file_upload('thefile',$target_dir);
if($result['success']){
    $filename = $result['file_name'];
    resizeImage(350, $thumb_dir . $filename, $target_dir . $filename);
    echo $filename;
}else{
    echo $result['error'];
}
exit;
