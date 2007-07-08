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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/resize_handler.php,v $
|     $Revision: 1.5 $
|     $Date: 2007-07-08 21:01:00 $
|     $Author: e107steved $
|
| Mod to give correct return code if source image already smaller than max size
|
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

function resize_image($source_file, $destination_file, $type = "upload", $model = "") 
{
// $destination_file - 'stdout' sends direct to browser. Otherwise treated as file name	
//						- if its a file, given '644' permissions
// $type - "upload"
//		 - numeric - sets new width of image
//		 - anything else - default preference for image width used, or failing that, 120 pixels
//			'avatar' is used
// $model - if "copy", creates a new file from $destination_file with the prefix 'thumb_'. 
//					Otherwise overwrites any existing $destination_file

// Returns:  TRUE - essentially, if $destination_file (or a file with a modified name) is valid:
//						- if resizing done
//						- source and (ultimate) destination files are the same, and the image was smaller than the limits
//						- destination was 'stdout', and file output successfully
//			 FALSE - essentially, if there is not a valid output file available - usually, that resizing failed, or some other error.

	global $pref;
	 
	$new_height = 0;
	$mode = ($pref['resize_method'] ? $pref['resize_method'] : "gd2");
	if ($type == "upload") 
	{
	  $new_size = varset($pref['im_width'],400);
	}
	elseif(is_numeric($type)) 
	{
	  $new_size = $type;
	} 
	else 
	{	// Use preferences or failing that hard-coded defaults for new size
	  $new_size = varset($pref['im_width'], 120);
	  $new_height = varset($pref['im_height'], 100);
	}
	 
	$im_quality = varset($pref['im_quality'], 99);
	 
	$image_stats = getimagesize($source_file);
	if ($image_stats == null) 
	{
//	  echo "<b>DEBUG</b> image_stats are null<br />";
	  return false;
	}
	if (($image_stats[0] == 0) || ($image_stats[1] == 0))
	{
	  return FALSE;				// Zero sized image - shouldn't happen
	}
	 
	// Check the image type. '1'=GIF, '2'=jpeg, '3' = PNG
	if ($image_stats[2] != 1 && $image_stats[2] != 2 && $image_stats[2] != 3 && ($mode == 'gd1' || $mode == 'gd2')) 
	{
	  echo "<b>DEBUG</b> Wrong image type<br />";
	  return FALSE;
	}
	
	$imagewidth = $image_stats[0];		// Width of existing image
	$imageheight = $image_stats[1];		// Height of existing image
	if ($imagewidth <= $new_size && ($imageheight <= $new_height || $new_height == 0)) 
	{  // Nothing to do if image width already smaller than the maximum
	  // If we were basically ensuring an existing file was within limits, return TRUE
	  // If we had to create a new file, return FALSE since it wasn't done.
	  return (($source_file == $destination_file) && ($model != 'copy'));
	}
	
	$ratio = ($imagewidth / $new_size);
	$new_imageheight = round($imageheight / $ratio);
	if (($new_height <= $new_imageheight) && $new_height > 0) 
	{
		$ratio = $new_imageheight / $new_height;
		$new_imageheight = $new_height;
		$new_size = round($new_size / $ratio);
		 
	}

	switch ($mode)
	{
	  case "ImageMagick" :
	    if ($destination_file == "stdout") 
		{		/* if destination is stdout, output directly to the browser */
//		  $destination_file = "jpg:-";
		  header("Content-type: image/jpeg");
		  // Use double quotes instead of single to keep Bill happy
		  passthru ($pref['im_path']."convert -quality ".$im_quality." -antialias -geometry ".$new_size."x".$new_imageheight." ".escapeshellarg($source_file)." \"jpg:-\"");
		} 
		else 
		{		/* otherwise output to file */
		  if ($model == "copy") 
		  {
			$name = substr($destination_file, (strrpos($destination_file, "/")+1));
			$name2 = "thumb_".$name;
			$destination_file = str_replace($name, $name2, $destination_file);
		  }
		  // Use double quotes instead of single to keep Bill happy
//		  exec ($pref['im_path']."convert -quality ".$im_quality." -antialias -geometry ".$new_size."x".$new_imageheight." ".escapeshellarg($source_file)." '".$destination_file."'");
		  exec ($pref['im_path']."convert -quality ".$im_quality." -antialias -geometry ".$new_size."x".$new_imageheight." ".escapeshellarg($source_file)." \"".$destination_file."\"");
		}
	  case "gd1" :
		switch ($image_stats[2])
		{
		  case IMAGETYPE_PNG : // 3 - PNG
			$src_img = imagecreatefrompng($source_file);
			break;
		  case IMAGETYPE_GIF : // 1 - GIF
		    if (!function_exists('imagecreatefromgif')) return FALSE;		// Some versions of GD library don't support GIF
			$src_img = imagecreatefromgif($source_file);
			break;
		  case IMAGETYPE_JPEG :	// 2 - Jpeg
		    $src_img = imagecreatefromjpeg($source_file);
			break;
		  default :
			return FALSE; // Unsupported image type
		}
		if (!$src_img) 
		{
		  return FALSE;
		}
		$dst_img = imagecreate($new_size, $new_imageheight);		// Create blank image of correct size as target
		// Only next line is different between gd1 and gd2
		imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, $new_size, $new_imageheight, $imagewidth, $imageheight);
		if ($model == "copy") 
		{
		  $name = substr($destination_file, (strrpos($destination_file, "/")+1));
		  $name2 = "thumb_".$name;
		  $destination_file = str_replace($name, $name2, $destination_file);
		}
		 
		if ($destination_file == "stdout") 
		{
		  header("Content-type: image/jpeg");
		  imagejpeg($dst_img, '', $im_quality);
		} 
		else 
		{
		  imagejpeg($dst_img, $destination_file, $im_quality);
		  imagedestroy($src_img);
		  imagedestroy($dst_img);
		}
	  case "gd2" :
		switch ($image_stats[2])
		{
		  case IMAGETYPE_PNG : // 3 - PNG
			$src_img = imagecreatefrompng($source_file);
			break;
		  case IMAGETYPE_GIF : // 1 - GIF
		    if (!function_exists('imagecreatefromgif')) return FALSE;		// Some versions of GD library don't support GIF
			$src_img = imagecreatefromgif($source_file);
			break;
		  case IMAGETYPE_JPEG :	// 2 - Jpeg
		    $src_img = imagecreatefromjpeg($source_file);
			break;
		  default :
			return FALSE; // Unsupported image type
		}
		if (!$src_img) 
		{
		  return FALSE;
		}
		 
		$dst_img = imagecreatetruecolor($new_size, $new_imageheight);
		// Only next line is different between gd1 and gd2
		imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_size, $new_imageheight, $imagewidth, $imageheight);
		if ($model == "copy") 
		{
		  $name = substr($destination_file, (strrpos($destination_file, "/")+1));
		  $name2 = "thumb_".$name;
		  $destination_file = str_replace($name, $name2, $destination_file);
		}
		
		if ($destination_file == "stdout") 
		{
		  header("Content-type: image/jpeg");
		  imagejpeg($dst_img, '', $im_quality);
		} 
		else 
		{
		  imagejpeg($dst_img, $destination_file, $im_quality);
		  imagedestroy($src_img);
		  imagedestroy($dst_img);
		}
	}   // End switch($mode)

	if ($destination_file == "stdout") return TRUE;		// Can't do anything more if file sent to stdout - assume success
	 
	@chmod($destination_file, 0644);
	if ($pref['image_owner']) 
	{
	  @chown($destination_file, $pref['image_owner']);
	}
	 
	$image_stats = getimagesize($destination_file);
	if ($image_stats == null) 
	{  
	  return FALSE;
	} 
	else 
	{
	  return TRUE;
	}
}
?>