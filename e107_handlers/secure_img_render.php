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
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:27 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
while (list($global) = each($GLOBALS))
{
        if (!preg_match('/^(_SERVER|GLOBALS)$/', $global))
  {
                unset($$global);
        }
}
unset($global);
$imgtypes=array("jpeg", "png", "gif");

define("e_QUERY", eregi_replace("&|/?PHPSESSID.*", "", $_SERVER['QUERY_STRING']));
$recnum = preg_replace("#\D#","",e_QUERY);
if(!$recnum)
{
  exit;
}
@include("e107_config.php");
$a=0;
$p="";

while(!$mySQLserver && $a<5)
{
        $a++;
        $p.="../";
        @include($p."e107_config.php");
}
mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword);
mysql_select_db($mySQLdefaultdb);
$result = mysql_query("SELECT tmp_info FROM {$mySQLprefix}tmp WHERE tmp_ip = '{$recnum}'");
if(!$row = mysql_fetch_array($result))
{
  exit;
}

list($code,$url) = explode(",",$row['tmp_info']);
$type="none";
foreach($imgtypes as $t)
{
        if(function_exists("imagecreatefrom".$t)){
                $type = $t;
                break;
        }
}
switch($type)
{
        case "jpeg":
                $image = ImageCreateFromJPEG($url."generic/code_bg.jpg");
                break;
        case "png":
                $image = ImageCreateFromPNG($url."generic/code_bg.png");
                break;
        case "gif":
                $image = ImageCreateFromGIF($url."generic/code_bg.gif");
                break;
}
$text_color = ImageColorAllocate($image, 80, 80, 80);
ob_clean();
Header("Content-type: image/{$type}");
ImageString ($image, 5, 12, 2, $code, $text_color);
switch($type)
{
        case "jpeg":
                ImageJPEG($image,'',75);
                break;
        case "png":
                ImagePNG($image,'',75);
                break;
        case "gif":
                ImageGIF($image,'',75);
                break;
}
ImageDestroy($image);
die();
?>