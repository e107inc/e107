<?
/***********************************************************************
** Title.........:  Online Manipulation of Images
** Version.......:  1.0
** Author........:  Xiang Wei ZHUO <wei@zhuo.org>
** Filename......:  load_image.php
** Last changed..:  30 Aug 2003 
** Notes.........:  Configuration in config.inc.php 

                   Uses the GD, ImageMagic or NetPBM to manipulate
                   images online. ImageMagic is preferred as it provides
                   the best rotation algorithm. Below is a brief comparsion
                   of the image manipulation packages. Personal preference
                   is ImageMagick.

                              |     GD     | NetPBM | ImageMagick
                   ------------------------------------------------
                   GIF             NO(1)     YES        YES
                   JPEG            YES(2)    YES        YES
                   PNG             YES       YES        YES
                   Cropping        Good      Good       Good
                   Scaling         Fair      Good       Very Good
                   Rotation        Poor      Fair       Very Good
                   Flip            Good      Poor       Good
                   

                   (1) GIF is support in old GD say version 1.61 and below
                   (2) Full colour JPEG is not supported in GD versions
                       less than 2.01 with PHP.

***********************************************************************/

//***************************************************************************

include '../ImageManager/config.inc.php';

// set this to whatever subdir you make
$path = $BASE_ROOT.'/';

//***************************************************************************

//echo $path;

require_once 'Transform.php';

$action = '';

//get the image file
$img_file = $_GET['img'];

if($img_file != '') {
    $path_info = pathinfo(urldecode($img_file));
    $path = $path_info['dirname']."/";
    $img_file = $path_info['basename'];
}
//var_dump($path);
//var_dump($path_info);

//get the parameters
if (isset($_GET['action']))
    $action = $_GET['action'];
if (isset($_GET['params']))
    $params = $_GET['params'];
if(isset($_GET['file'])) {
    $save_file = urldecode($_GET['file']);
}

//manipulate the image if the parameters are valid
if(isset($params)) {
    $values =  explode(',',$params,4);
    if(count($values)>0) {
        $file = manipulate($img_file, $action, $values);
    }
}

//manipulate the images
function manipulate($img_file, $action, $values)
{
    global $path, $save_file, $BASE_DIR;

    //Load the Image Manipulation Driver
    $img = Image_Transform::factory(IMAGE_CLASS);
    $img->load($BASE_DIR.$path.$img_file);
//var_dump($_SERVER['DOCUMENT_ROOT'].$path.$img_file);
    switch ($action) {
        case 'crop':
            $img->crop(intval($values[0]),intval($values[1]),intval($values[2]),intval($values[3]));
        break;
    case 'scale':
            $img->resize(intval($values[0]),intval($values[1]));
        break;
    case 'rotate':
            $img->rotate(floatval($values[0]));
        break;
    case 'flip':
        if ($values[0] == 'hoz')
            $img->flip(true);
        else if($values[0] == 'ver') 
            $img->flip(false);
        break;
    case 'save':

        if (isset($save_file))
        {
            $quality = intval($values[1]);
            if($quality <0)
                $quality = 85;
            $img->save($BASE_DIR.$path.$save_file, $values[0], $quality);
        }
        break;
    }

    //get the unique file name
    $filename = $img->createUnique($BASE_DIR.$path);
    //save the manipulated image 
    $img->save($BASE_DIR.$path.$filename);
    $img->free();

    $imagesize = @getimagesize($filename);
    return array($filename, $imagesize[3]);
}


//well, let say the image was not manipulated, or no action parameter was given
//we will get the image dimension anyway.
$image = $img_file;
$size = @getimagesize($image);
$dimensions = $size[3];

if (isset($file) && is_array($file))
{
    $image = $file[0];
    $dimensions = $file[1];
}

//now display the image with 
include 'man_image.html';
?>