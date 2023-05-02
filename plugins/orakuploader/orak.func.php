<?php
function hyphenize($string) {
    $dict = array(
        "I'm"      => "I am",
        "thier"    => "their",
        // Add your own replacements here
    );
    return strtolower(
        preg_replace(
            array( '#[\\s-]+#', '#[^A-Za-z0-9\. -]+#' ),
            array( '-', '' ),
            // the full cleanString() can be downloaded from http://www.unexpectedit.com/php/php-clean-string-of-utf8-chars-convert-to-similar-ascii-char
            cleanString(
                str_replace( // preg_replace can be used to support more complicated replacements
                    array_keys($dict),
                    array_values($dict),
                    urldecode($string)
                )
            )
        )
    );
}

function cleanString($text) {
    $utf8 = array(
        '/[áàâãªä]/u'   =>   'a',
        '/[ÁÀÂÃÄ]/u'    =>   'A',
        '/[ÍÌÎÏ]/u'     =>   'I',
        '/[íìîï]/u'     =>   'i',
        '/[éèêë]/u'     =>   'e',
        '/[ÉÈÊË]/u'     =>   'E',
        '/[óòôõºö]/u'   =>   'o',
        '/[ÓÒÔÕÖ]/u'    =>   'O',
        '/[úùûü]/u'     =>   'u',
        '/[ÚÙÛÜ]/u'     =>   'U',
        '/ç/'           =>   'c',
        '/Ç/'           =>   'C',
        '/ñ/'           =>   'n',
        '/Ñ/'           =>   'N',
        '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
        '/[“”«»„]/u'    =>   ' ', // Double quote
        '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
    );
    return preg_replace(array_keys($utf8), array_values($utf8), $text);
}

function createThumbnail($imageDirectory, $imageName, $thumbDirectory, $thumbWidth, $quality = 100)
{
    $image_extension = @end(explode(".", $imageName));
    switch($image_extension)
    {
        case "jpg":
            @$srcImg = imagecreatefromjpeg("$imageDirectory/$imageName");
            break;
        case "jpeg":
            @$srcImg = imagecreatefromjpeg("$imageDirectory/$imageName");
            break;
        case "png":
            $srcImg = imagecreatefrompng("$imageDirectory/$imageName");
            break;
    }

    if(!$srcImg) exit;
    $origWidth = imagesx($srcImg);
    $origHeight = imagesy($srcImg);
    $ratio = $origHeight/ $origWidth;
    $thumbHeight = $thumbWidth * $ratio;
    $thumbWidth = intval($thumbWidth);
    $thumbHeight = intval($thumbHeight);
    $thumbImg = imagecreatetruecolor($thumbWidth, $thumbHeight);

    if($image_extension == 'png')
    {
        $background = imagecolorallocate($thumbImg, 0, 0, 0);
        imagecolortransparent($thumbImg, $background);
        imagealphablending($thumbImg, false);
        imagesavealpha($thumbImg, true);
    }

    imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $origWidth, $origHeight);

    switch($image_extension)
    {
        case "jpg":
            imagejpeg($thumbImg, "$thumbDirectory/$imageName", $quality);
            break;
        case "jpeg":
            imagejpeg($thumbImg, "$thumbDirectory/$imageName", $quality);
            break;
        case "png":
            imagepng($thumbImg, "$thumbDirectory/$imageName");
            break;
    }

}

function normalizeChars($s) {
    $replace = array(
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'Ae', 'Å'=>'A', 'Æ'=>'A', 'Ă'=>'A', 'Ą' => 'A', 'ą' => 'a',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'ae', 'å'=>'a', 'ă'=>'a', 'æ'=>'ae',
        'þ'=>'b', 'Þ'=>'B',
        'Ç'=>'C', 'ç'=>'c', 'Ć' => 'C', 'ć' => 'c',
        'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ę' => 'E', 'ę' => 'e',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e',
        'Ğ'=>'G', 'ğ'=>'g',
        'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'İ'=>'I', 'ı'=>'i', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
        'Ł' => 'L', 'ł' => 'l',
        'Ñ'=>'N', 'Ń' => 'N', 'ń' => 'n',
        'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'Oe', 'Ø'=>'O', 'ö'=>'oe', 'ø'=>'o',
        'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
        'Š'=>'S', 'š'=>'s', 'Ş'=>'S', 'ș'=>'s', 'Ș'=>'S', 'ş'=>'s', 'ß'=>'ss', 'Ś' => 'S', 'ś' => 's',
        'ț'=>'t', 'Ț'=>'T',
        'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'Ue',
        'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'ue',
        'Ý'=>'Y',
        'ý'=>'y', 'ý'=>'y', 'ÿ'=>'y',
        'Ž'=>'Z', 'ž'=>'z', 'Ż' => 'Z', 'ż' => 'z', 'Ź' => 'Z', 'ź' => 'z'
    );
    return strtr($s, $replace);
}



function addWatermark($watermark, $imageDirectory, $imageName, $x = 0, $y = 0)
{
    if(file_exists($watermark))
    {
        $marge_right  = 0;
        $marge_bottom = 0;

        $stamp = imagecreatefrompng($watermark);

        $image_extension = @end(explode(".", $imageName));
        switch($image_extension)
        {
            case "jpg":
                $im = imagecreatefromjpeg("$imageDirectory/$imageName");
                break;
            case "jpeg":
                $im = imagecreatefromjpeg("$imageDirectory/$imageName");
                break;
            case "png":
                $im = imagecreatefrompng("$imageDirectory/$imageName");
                break;
        }

        $imageSize = getimagesize("$imageDirectory/$imageName");
        $watermark_o_width = imagesx($stamp);
        $watermark_o_height = imagesy($stamp);

        $newWatermarkWidth = $imageSize[0];
        $newWatermarkHeight = $watermark_o_height * $newWatermarkWidth / $watermark_o_width;


        if((int)$x <= 0)
            $x = $imageSize[0]/2 - $newWatermarkWidth/2;
        if((int)$y <= 0)
            $y = $imageSize[1]/2 - $newWatermarkHeight/2;

        imagecopyresized($im, $stamp, intval($x), intval($y), 0, 0, intval($newWatermarkWidth), intval($newWatermarkHeight), intval(imagesx($stamp)), intval(imagesy($stamp)));

        switch($image_extension)
        {
            case "jpg":
                header('Content-type: image/jpeg');
                imagejpeg($im, "$imageDirectory/$imageName", 100);
                break;
            case "jpeg":
                header('Content-type: image/jpeg');
                imagejpeg($im, "$imageDirectory/$imageName", 100);
                break;
            case "png":
                header('Content-type: image/png');
                imagepng($im, "$imageDirectory/$imageName");
                break;
        }
    }
}

function crop($max_width, $max_height, $source_file, $dst_dir)
{
    $imgsize = getimagesize($source_file);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];

    switch($mime){
        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            break;

        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            break;

        default:
            return false;
            break;
    }

    $dst_img = imagecreatetruecolor($max_width, $max_height);

    if($mime == 'image/png')
    {
        $background = imagecolorallocate($dst_img, 0, 0, 0);
        imagecolortransparent($dst_img, $background);
        imagealphablending($dst_img, false);
        imagesavealpha($dst_img, true);
    }

    $src_img = $image_create($source_file);

    $width_new = $height * $max_width / $max_height;
    $height_new = $width * $max_height / $max_width;

    if($width_new > $width){
        $h_point = (($height - $height_new) / 2);
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
    } else{
        $w_point = (($width - $width_new) / 2);
        imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
    }

    if($mime == 'image/jpeg')
        $image($dst_img, $dst_dir, 100);
    else
        $image($dst_img, $dst_dir);

    if($dst_img)imagedestroy($dst_img);
    if($src_img)imagedestroy($src_img);
}