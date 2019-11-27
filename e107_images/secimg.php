<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/*   Example Custom secure_image_custom.php file:
	<?php

	$secureimg['image'] = "code_bg_custom";  // filename excluding the .ext
	$secureimg['size']	= "15";
	$secureimg['angle']	= "0";
	$secureimg['x']		= "6";
	$secureimg['y']		= "22";
	$secureimg['font'] 	= "imagecode.ttf";
	$secureimg['color'] = "90,90,90"; // red,green,blue

	 ?>
*/
// error_reporting(E_ALL);
// define('e107_INIT', true);
$_E107 = array();
$_E107['no_online'] = true;
$_E107['no_forceuserupdate'] = true;
$_E107['no_menus'] = true;
$_E107['no_maintenance'] = true;
//$_E107['no_theme'] = true;

require_once("../class2.php");
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT', true);
/*
define('e_BASE',realpath("..".DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
@include(e_BASE.'e107_config.php');
if(!isset($mySQLserver))
{
    if(defined('e_DEBUG'))
    {
          echo "FAILED TO LOAD ".e_BASE."e107_config.php in secimg.php";
    }
    exit;
}*/


// require_once(realpath(e_BASE.$HANDLERS_DIRECTORY.DIRECTORY_SEPARATOR."secure_img_handler.php"));

require_once(e_HANDLER."secure_img_handler.php");

$sim = new secure_image();

if(!isset($_GET['id']))
{
	exit;	
}

$code = $_GET['id'];

if(!empty($_GET['clr']) && preg_match('/^[a-f0-9]{6}$/i', $_GET['clr'])) //hex color is valid
{
	$color = $_GET['clr'];
} 
else
{
	$color = "cccccc";		
}

ob_clean(); // Precaution - clearout utf-8 BOM or any other garbage in e107_config.php
$sim->render($code,$color);

exit;

?>