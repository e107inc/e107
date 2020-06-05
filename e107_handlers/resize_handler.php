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
 * $Source: /cvs_backup/e107_0.8/e107_handlers/resize_handler.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

// Given an image file name, return the mime type string. Returns FALSE if invalid
function mimeFromFilename($fileName)
{
	$fileExt = strtolower(substr(strrchr($fileName, "."), 1));
	$mimeTypes = array(
		'jpg' 	=> 'jpeg',
		'gif' 	=> 'gif',
		'png'	=> 'png',
		'jpeg'	=> 'jpeg',
		'pjpeg' => 'jpeg',
		'bmp'	=> 'bmp'
		);
	if (!isset($mimeTypes[$fileExt])) {	return FALSE;  }		// only allow image files  }
	return "Content-type: image/".$mimeTypes[$fileExt];
}


function resize_image($source_file, $destination_file, $type = "upload", $model = "") 
{
// $destination_file - 'stdout' sends direct to browser. Otherwise treated as file name	
//						- if its a file, given '644' permissions
// $type	- numeric - sets new width of image
//			- "upload" - uses preference 'im_width', or 400px if not defined
//		 	- anything else - default preference for image width  & heightused, or failing that, 120 px x 100 px
//				'avatar' may be used to invoke the default (see usersettings.php)
// $model	- "copy" -  creates a new file for the destination, by prefixing $destination_file with 'thumb_'. Return error for small images.
//			- 'upsize' - small images are enlarged
//			- 'noscale' - small images are transferred at their original size
//			- 'nocopy' - used in content manager plugin
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
		switch ($model)
		{
			case 'copy' :		// Not sure what to do here!
				return FALSE;	// This is what it used to do
				break;
			case 'upsize' :		// Scale source up to required size
				break;			// Just fall through to do that.
			case 'noscale' :	// No scaling of small images- just want destination to be the same as source
				if ($destination_file == 'stdout')
				{
					if (($result = mimeFromFilename($source_file)) === FALSE) { return FALSE; }
					header($result);
					if (eShims::readfile($source_file) === FALSE) { return FALSE; }
				}
				else
				{
					return copy($source_file,$destination_file);
				}
				return TRUE;
				break;
			default :
				return ($source_file == $destination_file);
		}
//	  return (($source_file == $destination_file) && ($model != 'copy'));
	}
	
	$ratio = ($imagewidth / $new_size);
	$new_imageheight = round($imageheight / $ratio);
	if (($new_height <= $new_imageheight) && $new_height > 0) 
	{
		$ratio = $new_imageheight / $new_height;
		$new_imageheight = $new_height;
		$new_size = round($new_size / $ratio);
		 
	}

	if (($destination_file != 'stdout') && ($model == 'copy'))
	{
		$destination_file = dirname($destination_file).'/thumb_'.basename($destination_file);
	}
	$returnError = 0;		// Return value from some of the commands
	switch ($mode)
	{
	  case "ImageMagick" :
	    if ($destination_file == "stdout") 
		{		// if destination is stdout, output directly to the browser 
//		  $destination_file = "jpg:-";
		  header("Content-type: image/jpeg");
		  // Use double quotes instead of single to keep Bill happy
		  passthru ($pref['im_path']."convert -quality ".$im_quality." -antialias -geometry ".$new_size."x".$new_imageheight." ".escapeshellarg($source_file)." \"jpg:-\"", $returnError);
		} 
		else 
		{		// otherwise output to file 
		  // Use double quotes instead of single to keep Bill happy
		  exec ($pref['im_path']."convert -quality ".$im_quality." -antialias -geometry ".$new_size."x".$new_imageheight." ".escapeshellarg($source_file)." \"".$destination_file."\"", $dummy, $returnError);
		}
		if ($returnError) echo "ImageMagick resize/output error: {$returnError}<br />";
		break;
	  case 'gd1' :
	  case 'gd2' :
		switch ($image_stats[2])
		{
		  case IMAGETYPE_PNG : // 3 - PNG
			$src_img = @imagecreatefrompng($source_file);
			$fileExt = 'png';
			break;
		  case IMAGETYPE_GIF : // 1 - GIF
		    if (!function_exists('imagecreatefromgif')) return FALSE;		// Some versions of GD library don't support GIF
			$src_img = @imagecreatefromgif($source_file);
			$fileExt = 'gif';
			break;
		  case IMAGETYPE_JPEG :	// 2 - Jpeg
		    $src_img = @imagecreatefromjpeg($source_file);
			$fileExt = 'jpg';
			break;
		  default :
			return FALSE; // Unsupported image type
		}
		if (!$src_img) 
		{
		  return FALSE;
		}
		// Only next line is different between gd1 and gd2
		if ($mode == 'gd1')
		{
			$dst_img = imagecreate($new_size, $new_imageheight);		// Create blank image of correct size as target
			if (!imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, $new_size, $new_imageheight, $imagewidth, $imageheight))  { $returnError = -4; }
		}
		else
		{
			$dst_img = imagecreatetruecolor($new_size, $new_imageheight);
			if (!imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_size, $new_imageheight, $imagewidth, $imageheight)) { $returnError = -5; }
		}
		if ($returnError)
		{
			echo "Resizing error (1): {$returnError}<br />";
			return FALSE;
		}

		// Now output or save the resized file

		$destName = $destination_file;
		if ($destination_file == "stdout") 
		{
			$destName = '';
			if (($result = mimeFromFilename($source_file)) === FALSE) 
			{ 
				$returnError = -6; 
			}
			else
			{
				header($result);
			}
		} 
		else
		{
			$fileExt = strtolower(substr(strrchr($destination_file, "."), 1));
		}

		if ($returnError == 0)
		{	// We can output the image, or save it to a file
			switch ($fileExt)
			{
				case 'png' :
					if (!imagepng($dst_img, $destName, 6)) { $returnError = -1; }		// Fix the quality for now
					$outputFunc = 'imagepng';
					break;
				case 'gif' :
					if (!imagegif($dst_img, $destName)) { $returnError = -1; }
					$outputFunc = 'imagegif';
					break;
				case 'jpg' :
				case 'jpeg' :
					if (!imagejpeg($dst_img, $destName, $im_quality)) { $returnError = -1; }
					$outputFunc = 'imagejpeg';
					break;
				default :
					$returnError = -7;			// Invalid output extension
					$outputFunc = 'none';
			}
		}

		if (!imagedestroy($src_img)) { $returnError = -2; }
		if (!imagedestroy($dst_img)) { $returnError = -3; }
		if ($returnError)
		{
			echo "Resizing error (2): {$returnError} - {$outputFunc} -> {$destName}<br />";
			return FALSE;
		}  
		break;
		default :
			echo "Invalid resize function: {$mode}<br />";
			return FALSE;
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
