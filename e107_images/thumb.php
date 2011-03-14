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
 * $Source: /cvs_backup/e107_0.8/e107_images/thumb.php,v $
 * $Revision$
 * $Date$
 * $Author$
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

$_E107['minimal'] = TRUE;

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
		$source = $tp->toDB($source);
		echo "Couldn't find: {$source}<br />";
	} 
} 

