<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/secure_img_render.php,v $
|     $Revision: 1.13 $
|     $Date: 2006-07-17 01:13:12 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

while (list($global) = each($GLOBALS))
{
	if (!preg_match('/^(_SERVER|GLOBALS)$/', $global)) {
                unset($$global);
        }
}
unset($global);
$imgtypes=array("jpeg", "png", "gif");

define("e_QUERY", preg_replace("#&|/?PHPSESSID.*#i", "", $_SERVER['QUERY_STRING']));
$recnum = preg_replace("#\D#","",e_QUERY);
if(!$recnum){
  exit;
}

$mySQLserver = "";

@include_once(dirname(__FILE__)."e107_config.php");

$a=0;
$p="";

while(!$mySQLserver && $a<5){
        $a++;
        $p.="../";
	@include_once($p."e107_config.php");
}
mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword);
mysql_select_db($mySQLdefaultdb);
$result = mysql_query("SELECT tmp_info FROM {$mySQLprefix}tmp WHERE tmp_ip = '{$recnum}'");
if(!$row = mysql_fetch_array($result)){
  exit;
}

list($code,$url) = explode(",",$row['tmp_info']);
$type="none";
foreach($imgtypes as $t)
{
        if(function_exists("imagecreatefrom".$t))
{
                $type = $t;
                break;
        }
}

$path = realpath(dirname(__FILE__)."/../")."/".$IMAGES_DIRECTORY;

if(is_readable($path."secure_image_custom.php"))
{
  	require_once($path."secure_image_custom.php");
	/*   Example secure_image_custom.php file:

		$secureimg['image'] = "code_bg_custom";  // filename excluding the .ext
		$secureimg['size']	= "15";
		$secureimg['angle']	= "0";
		$secureimg['x']		= "6";
		$secureimg['y']		= "22";
		$secureimg['font'] 	= "imagecode.ttf";
		$secureimg['color'] = array(90,90,90); // red,green,blue

	*/
	$bg_file = $secureimg['image'];
}
else
{
    $bg_file = "generic/code_bg";
}

switch($type)
{
        case "jpeg":
		$image = ImageCreateFromJPEG($path.$bg_file.".jpg");
                break;
        case "png":
		$image = ImageCreateFromPNG($path.$bg_file.".png");
                break;
        case "gif":
		$image = ImageCreateFromGIF($path.$bg_file.".gif");
                break;
}

if($secureimg['color'])
{
	$tmp = explode(",",$secureimg['color']);
	$text_color = ImageColorAllocate($image,$tmp[0],$tmp[1],$tmp[2]);
}
else
{
	$text_color = ImageColorAllocate($image, 90, 90, 90);
}


header("Content-type: image/".$type);


if($secureimg['font'] && is_readable($path.$secureimg['font'])){
	imagettftext($image, $secureimg['size'],$secureimg['angle'], $secureimg['x'], $secureimg['y'], $text_color,$path.$secureimg['font'], $code);
}else{
	ImageString ($image, 5, 12, 2, $code, $text_color);
}

switch($type)
{
        case "jpeg":
                ImageJPEG($image,'',80);
                break;
        case "png":
                ImagePNG($image,'',80);
                break;
        case "gif":
                ImageGIF($image,'',80);
                break;
}
ImageDestroy($image);
die();

?>