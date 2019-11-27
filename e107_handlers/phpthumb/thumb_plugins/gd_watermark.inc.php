<?php
/**
 * GD Watermark Lib Plugin Definition File
 * 
 * This file contains the plugin definition for GD Watermark
 * Usage:
 * <?php
 * 	require_once 'path/to/ThumbLib.inc.php';
 * 	$pic = PhpThumbFactory::create('path/to/pic/destination');
 * 	$watermark = PhpThumbFactory::create('path/to/watermark/destination');
 * 
 * 	//overlays the $pic-image with the $watermark-image on the right bottom corner, no offset 
 * 	$pic->addWatermark($watermark, 'rightBottom', 50, 0, 0);
 * 	$pic->show();  //or $pic->save('path/to/new/pic/destination');
 * ?>
 * 
 * PHP Version 5 with GD 2.0+
 * PhpThumb : PHP Thumb Library <http://phpthumb.gxdlabs.com>
 * Copyright (c) 2009, Ian Selby/Gen X Design
 * 
 * Author(s): Ian Selby <ian@gen-x-design.com>
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 * @author Thomas Dullnig <thomas.dullnig@sevenspire.com>
 * @copyright Copyright (c) 2009-2010 SEVENSPIRE
 * @link http://www.sevenspire.com
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package PhpThumb
 * @filesource
 */

/**
 * GD Watermark Lib Plugin
 * 
 * Overlays an image with another image to create a watermark-effect
 * 
 * @package PhpThumb
 * @subpackage Plugins
 */
class GdWatermark
{	
	/**
	 * Function copied from: http://www.php.net/manual/en/function.imagecopymerge.php#92787
	 * Does the same as "imagecopymerge" but preserves the alpha-channel
	 */
	protected function imageCopyMergeAlpha(&$dst_im, &$src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
		$cut = imagecreatetruecolor($src_w, $src_h);
		imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
		imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
		imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);
	}
	
	/**
	 * Adds a watermark to the current image and overlays it on the given position.
	 * @param object $wm a GdThumb-Object
	 * @param object $pos Can be: left/west, right/east, center for the x-axis and top/north/upper, bottom/lower/south, center for the y-axis
	 * Examples:
	 * 		- leftTop/leftop/topleft/topLeft same as westNorth same as westupper same as leftnorth or any other combination
	 * 		- center --> centers both the x- and y-axis
	 * 		- leftCenter --> set x-axis to the left corner of the image and centers the y-axis
	 * @param object $opacity
	 * 		- set the opacity of the watermark in percent, 0 = total transparent, 100 = total opaque
	 * @param object $offsetX
	 * 		- add an offset on the x-axis. can be negative to set an offset to the left
	 * @param object $offsetY
	 * 		- add an offset on the y-axis. can be negative to set an offset to the top
	 * @param object $that
	 * 		- the current GdThumb-Object
	 * @return the manipulated GdThumb-Object for chaining
	 */
	public function addWatermark($wm, $pos, $opacity, $offsetX, $offsetY, $that) // dont use &wm etc. 
	{
		$picDim = $that->getCurrentDimensions();
		$wmDim = $wm->getCurrentDimensions();
		
		$wmPosX = $offsetX;
		$wmPosY = $offsetY;
		
		if(preg_match('/right|east/i', $pos)){$wmPosX += $picDim['width'] - $wmDim['width'];}
		else if(!preg_match('/left|west/i', $pos)){$wmPosX += intval($picDim['width']/2 - $wmDim['width']/2);}
		
		if(preg_match('/bottom|lower|south/i', $pos)){$wmPosY += $picDim['height'] - $wmDim['height'];}
		else if(!preg_match('/upper|top|north/i', $pos)){$wmPosY += intval($picDim['height']/2 - $wmDim['height']/2);}
		
		$workingImage = $that->getWorkingImage();
		$wmImage = ($wm->getWorkingImage() ? $wm->getWorkingImage() : $wm->getOldImage());
		
		$this->imageCopyMergeAlpha($workingImage, $wmImage, $wmPosX, $wmPosY, 0, 0, $wmDim['width'], $wmDim['height'], $opacity);
		
		$that->setWorkingImage($workingImage);
	
		return $that;	
	}
}

$pt = PhpThumb::getInstance();
$pt->registerPlugin('GdWatermark', 'gd');
