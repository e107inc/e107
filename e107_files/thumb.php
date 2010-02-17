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
|     $Source: /cvs_backup/e107_0.7/e107_files/thumb.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
/*
		!!!!***** Deprecated location for this file - use the one in e107_images *****!!!
*/
/*
 Usage: simply replace your <img src='filename.jpg'
 with
 <img src='".e_IMAGE_ABS."thumb.php?filename.jpg+size"' />
 or
 <img src='".e_IMAGE_ABS."thumb.php?<full path to file>/filename.jpg+size"' />
 eg <img src='".e_IMAGE_ABS."thumb.php?home/images/myfilename.jpg+100)"' />
 By default a small image is upsized. To render the image unchanged, append '+noscale', thus:
 eg <img src='".e_IMAGE_ABS."thumb.php?home/images/myfilename.jpg+100+noscale)"' />

*/


require_once("../class2.php");
require_once(e_HANDLER."resize_handler.php");

if (e_QUERY)
{
	$tmp = explode('+',rawurldecode(e_QUERY));
	if(strpos($tmp[0], '/') === 0 || strpos($tmp[0], ":") >= 1)
	{
		$source = $tmp[0];	// Full path to image specified
	}
	else
	{
		$source = "../".str_replace('../','',$tmp[0]);
	}
	if (!$source)
	{
		echo "No image name.<br />";
		exit;
	} 
	$newsize = intval($tmp[1]);
	
	if (($newsize < 5) || ($newsize > 4000))	// Pretty generous limits
	{
		echo "Bad image size: {$newsize}<br />";
		exit;
	} 
	$opts = varset($tmp[2],'upsize');
	if(!resize_image($source, 'stdout', $newsize, $opts))
	{
		echo "Couldn't find: {$source}<br />";
	} 
} 

?>