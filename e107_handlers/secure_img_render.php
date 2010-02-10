<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/secure_img_render.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

while (@ob_end_clean());
ob_start();
function e107_ini_set($var, $value)
{
	if (function_exists('ini_set'))
	{
		ini_set($var, $value);
	}
}

// setup some php options
e107_ini_set('magic_quotes_runtime',     0);
e107_ini_set('magic_quotes_sybase',      0);
e107_ini_set('arg_separator.output',     '&amp;');
e107_ini_set('session.use_only_cookies', 1);
e107_ini_set('session.use_trans_sid',    0);

while (list($global) = each($GLOBALS))
{
	if (!preg_match('/^(_SERVER|GLOBALS)$/', $global))
	{
		unset($$global);
	}
}

unset($global);

$imgtypes = array("jpeg", "png", "gif");

define("e_QUERY", preg_replace("#&|/?PHPSESSID.*#i", "", $_SERVER['QUERY_STRING']));

$recnum = preg_replace("#\D#","",e_QUERY);

if($recnum == false){ exit; }

$mySQLserver = "";

$a = 0;
$p = "";

$ifile = dirname(__FILE__);
if (substr($ifile,-1,1) != '/') $ifile .= '/';
@include_once($ifile."e107_config.php");

while(!$mySQLserver && $a < 5)
{
  $a ++;
  $p .= "../";
  @include_once($ifile.$p.'e107_config.php');		// *** Revised
}

mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword);
mysql_select_db($mySQLdefaultdb);

$result = mysql_query("SELECT tmp_info FROM {$mySQLprefix}tmp WHERE tmp_ip = '{$recnum}'");
if(!$row = mysql_fetch_array($result))
{
	exit;
}

list($code, $url) = explode(",",$row['tmp_info']);

$type = "none";

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
	$secureimg['color'] = "90,90,90"; // red,green,blue

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

if(isset($secureimg['color']))
{
	$tmp = explode(",",$secureimg['color']);
	$text_color = ImageColorAllocate($image,$tmp[0],$tmp[1],$tmp[2]);
}
else
{
	$text_color = ImageColorAllocate($image, 90, 90, 90);
}

header("Content-type: image/{$type}");

if(isset($secureimg['font']) && is_readable($path.$secureimg['font']))
{
	imagettftext($image, $secureimg['size'],$secureimg['angle'], $secureimg['x'], $secureimg['y'], $text_color,$path.$secureimg['font'], $code);
}
else
{
	imagestring ($image, 5, 12, 2, $code, $text_color);
}

ob_end_clean();
switch($type)
{
	case "jpeg":
		imagejpeg($image);
		break;
	case "png":
		imagepng($image);
		break;
	case "gif":
		imagegif($image);
		break;
}

imagedestroy($image);
