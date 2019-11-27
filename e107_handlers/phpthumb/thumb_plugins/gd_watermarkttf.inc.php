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
 * @author Cameron @ e107inc
 * @copyright e107 Inc. 
 * @link http://www.e107.org
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
class GdWatermarkTTF
{
	protected $parentInstance;
	protected $currentDimensions;
	protected $workingImage;
	protected $newImage;
	protected $options;	
	protected $parms;
		
	private function DebugMessage($val)
	{
	//	echo $val."<br />";	
	}
	
	private function shadow_text($im, $size, $angle, $x, $y, $font, $text,$opacity=100)
  	{
  		$col = $this->hex2rgb($this->parms['color']);
		$scol = $this->hex2rgb($this->parms['shadowcolor']);
		
    	$shadowcolor = imagecolorallocatealpha($im, $scol[0], $scol[1], $scol[2], $opacity);
    	$white = imagecolorallocatealpha($im, $col[0], $col[1], $col[2], $opacity);
		
	//	imagefilter($shadowcolor, IMG_FILTER_GAUSSIAN_BLUR);
		imagefilter($shadowcolor, IMG_FILTER_SELECTIVE_BLUR);
		
    	imagettftext($im, $size, 0, $x + 1, $y + 1, $shadowcolor, $font, $text);
    	imagettftext($im, $size, 0, $x -1, $y + 1, $shadowcolor, $font, $text);
   	 	imagettftext($im, $size, 0, $x + 0, $y + 0, $white, $font, $text);
  	}
	
	private function hex2rgb($hex) {
	   $hex = str_replace("#", "", $hex);
	 
	   if(strlen($hex) == 3) {
	      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
	      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
	      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	   } else {
	      $r = hexdec(substr($hex,0,2));
	      $g = hexdec(substr($hex,2,2));
	      $b = hexdec(substr($hex,4,2));
	   }
	   $rgb = array($r, $g, $b);
	   //return implode(",", $rgb); // returns the rgb values separated by commas
	   return $rgb; // returns an array with the rgb values
	}	



	
	// Taken from http://phpthumb.sourceforge.net/index.php?source=phpthumb.filters.php
	// public function WatermarkText($text, $size=12, $alignment='BR', $hex_color='000000', $ttffont='', $opacity=100, $margin=5, $angle=0, $bg_color=false, $bg_opacity=0, $fillextend='', &$that) 
	public function WatermarkText($parms,  &$that) 
	{
		$this->parms = $parms;
		$text		= $parms['text'];
		$size 		= $parms['size'];
		$alignment	= $parms['pos'];
		$hex_color	= $parms['color'];
		$ttffont	= $parms['font'];
		$opacity	= (isset($parms['opacity'])) ? $parms['opacity'] : 100; 
		$margin		= $parms['margin']; // 30; // (isset($parms[6])) ? $parms[6] : 25; 
		$angle		= 0; // $parms['angle'];
		$bg_color	= false;
		$bg_opacity	= 0;
		$fillextend	= '';
		
		$this->parentInstance 		= $that;
		$this->currentDimensions 	= $this->parentInstance->getCurrentDimensions();
		$this->workingImage			= $this->parentInstance->getWorkingImage();
		$this->newImage				= $this->parentInstance->getOldImage();
		$this->options				= $this->parentInstance->getOptions();
	
		$gdimg = $this->workingImage;
	
        // text watermark requested
        if (!$text) {
            return false;
        }
        ImageAlphaBlending($gdimg, true);

        if (preg_match('#^([0-9\\.\\-]*)x([0-9\\.\\-]*)(@[LCR])?$#i', $alignment, $matches)) {
            $originOffsetX = intval($matches[1]);
            $originOffsetY = intval($matches[2]);
            $alignment = (@$matches[4] ? $matches[4] : 'L');
            $margin = 0;
        } else {
            $originOffsetX = 0;
            $originOffsetY = 0;
        }

        $metaTextArray = array(
            '^Fb' =>       $this->phpThumbObject->getimagesizeinfo['filesize'],
            '^Fk' => 	round($this->phpThumbObject->getimagesizeinfo['filesize'] / 1024),
            '^Fm' => 	round($this->phpThumbObject->getimagesizeinfo['filesize'] / 1048576),
            '^X'  => 	$this->currentDimensions['width'], //$this->phpThumbObject->getimagesizeinfo[0],
            '^Y'  => 	$this->currentDimensions['height'], // $this->phpThumbObject->getimagesizeinfo[1],
            '^x'  => 	ImageSX($gdimg),
            '^y'  => 	ImageSY($gdimg),
            '^^'  => 	'^',
        );
		
		$this->DebugMessage("Margin: ".$margin);
		$this->DebugMessage("Dimensions: ".$this->currentDimensions['width']." X ".$this->currentDimensions['height']);

        $text = strtr($text, $metaTextArray);

        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r",   "\n", $text);
        $textlines = explode("\n", $text);
        $this->DebugMessage('Processing '.count($textlines).' lines of text', __FILE__, __LINE__);

        if (@is_readable($ttffont) && is_file($ttffont)) {

            $opacity = 100 - intval(max(min($opacity, 100), 0));
		//	$letter_color_text = imagecolorallocate($gdimg,0,0,0);
			$letter_color_text = imagecolorallocatealpha($gdimg,0,0,0,$opacity);
        //    $letter_color_text = ImageHexColorAllocate($gdimg, $hex_color, false, $opacity * 1.27);

            $this->DebugMessage('Using TTF font "'.$ttffont.'"', __FILE__, __LINE__);

            $TTFbox = ImageTTFbBox($size, $angle, $ttffont, $text);

            $min_x = min($TTFbox[0], $TTFbox[2], $TTFbox[4], $TTFbox[6]);
            $max_x = max($TTFbox[0], $TTFbox[2], $TTFbox[4], $TTFbox[6]);
            //$text_width = round($max_x - $min_x + ($size * 0.5));
            $text_width = round($max_x - $min_x);

            $min_y = min($TTFbox[1], $TTFbox[3], $TTFbox[5], $TTFbox[7]);
            $max_y = max($TTFbox[1], $TTFbox[3], $TTFbox[5], $TTFbox[7]);
            //$text_height = round($max_y - $min_y + ($size * 0.5));
            $text_height = round($max_y - $min_y);

            $TTFboxChar = ImageTTFbBox($size, $angle, $ttffont, 'jH');
            $char_min_y = min($TTFboxChar[1], $TTFboxChar[3], $TTFboxChar[5], $TTFboxChar[7]);
            $char_max_y = max($TTFboxChar[1], $TTFboxChar[3], $TTFboxChar[5], $TTFboxChar[7]);
            $char_height = round($char_max_y - $char_min_y);

            if ($alignment == '*') {

                $text_origin_y = $char_height + $margin;
                while (($text_origin_y - $text_height) < ImageSY($gdimg)) {
                    $text_origin_x = $margin;
                    while ($text_origin_x < ImageSX($gdimg)) {
                        ImageTTFtext($gdimg, $size, $angle, $text_origin_x, $text_origin_y, $letter_color_text, $ttffont, $text);
                        $text_origin_x += ($text_width + $margin);
                    }
                    $text_origin_y += ($text_height + $margin);
                }

            } else {

                // this block for background color only

                switch ($alignment) {
                    case '*':
                        // handled separately
                        break;

                    case 'T':
                        $text_origin_x = ($originOffsetX ? $originOffsetX - round($text_width / 2) : round((ImageSX($gdimg) - $text_width) / 2));
                        $text_origin_y = $char_height + $margin + $originOffsetY;
                        break;

                    case 'B':
                        $text_origin_x = ($originOffsetX ? $originOffsetX - round($text_width / 2) : round((ImageSX($gdimg) - $text_width) / 2));
                        $text_origin_y = ImageSY($gdimg) + $TTFbox[1] - $margin + $originOffsetY;
                        break;

                    case 'L':
                        $text_origin_x = $margin + $originOffsetX;
                        $text_origin_y = ($originOffsetY ? $originOffsetY : round((ImageSY($gdimg) - $text_height) / 2) + $char_height);
                        break;

                    case 'R':
                        $text_origin_x = ($originOffsetX ? $originOffsetX - $text_width : ImageSX($gdimg) - $text_width  + $TTFbox[0] - $min_x + round($size * 0.25) - $margin);
                        $text_origin_y = ($originOffsetY ? $originOffsetY : round((ImageSY($gdimg) - $text_height) / 2) + $char_height);
                        break;

                    case 'C':
                        $text_origin_x = ($originOffsetX ? $originOffsetX - round($text_width / 2) : round((ImageSX($gdimg) - $text_width) / 2));
                        $text_origin_y = ($originOffsetY ? $originOffsetY : round((ImageSY($gdimg) - $text_height) / 2) + $char_height);
                        break;

                    case 'TL':
                        $text_origin_x = $margin + $originOffsetX;
                        $text_origin_y = $char_height + $margin + $originOffsetY;
                        break;

                    case 'TR':
                        $text_origin_x = ($originOffsetX ? $originOffsetX - $text_width : ImageSX($gdimg) - $text_width  + $TTFbox[0] - $min_x + round($size * 0.25) - $margin);
                        $text_origin_y = $char_height + $margin + $originOffsetY;
                        break;

                    case 'BL':
                        $text_origin_x = $margin + $originOffsetX;
                        $text_origin_y = ImageSY($gdimg) + $TTFbox[1] - $margin + $originOffsetY;
                        break;

                    case 'BR':
                    default:
                        $text_origin_x = ($originOffsetX ? $originOffsetX - $text_width : ImageSX($gdimg) - $text_width  + $TTFbox[0] - $min_x + round($size * 0.25) - $margin);
                        $text_origin_y = ImageSY($gdimg) + $TTFbox[1] - $margin + $originOffsetY;
                        break;
                }

                //ImageRectangle($gdimg, $text_origin_x + $min_x, $text_origin_y + $TTFbox[1], $text_origin_x + $min_x + $text_width, $text_origin_y + $TTFbox[1] - $text_height, $letter_color_text);
             /*   if (phpthumb_functions::IsHexColor($bg_color)) {
                    $text_background_alpha = round(127 * ((100 - min(max(0, $bg_opacity), 100)) / 100));
                    $text_color_background = phpthumb_functions::ImageHexColorAllocate($gdimg, $bg_color, false, $text_background_alpha);
                } else {
                    $text_color_background = phpthumb_functions::ImageHexColorAllocate($gdimg, 'FFFFFF', false, 127);
                }
			*/	
				$text_color_background  = imagecolorallocatealpha($gdimg,0,0,0,0);
				
				
                $x1 = $text_origin_x + $min_x;
                $y1 = $text_origin_y + $TTFbox[1];
                $x2 = $text_origin_x + $min_x + $text_width;
                $y2 = $text_origin_y + $TTFbox[1] - $text_height;
                $x_TL = preg_match('#x#i', $fillextend) ?               0 : min($x1, $x2);
                $y_TL = preg_match('#y#i', $fillextend) ?               0 : min($y1, $y2);
                $x_BR = preg_match('#x#i', $fillextend) ? ImageSX($gdimg) : max($x1, $x2);
                $y_BR = preg_match('#y#i', $fillextend) ? ImageSY($gdimg) : max($y1, $y2);
                //while ($y_BR > ImageSY($gdimg)) {
                //    $y_TL--;
                //    $y_BR--;
                //    $text_origin_y--;
                //}
                $this->DebugMessage('WatermarkText() calling ImageFilledRectangle($gdimg, '.$x_TL.', '.$y_TL.', '.$x_BR.', '.$y_BR.', $text_color_background)', __FILE__, __LINE__);
             //   ImageFilledRectangle($gdimg, $x_TL, $y_TL, $x_BR, $y_BR, $text_color_background);

                // end block for background color only


                $y_offset = 0;
                foreach ($textlines as $dummy => $line) {

                    $TTFboxLine = ImageTTFbBox($size, $angle, $ttffont, $line);
                    $min_x_line = min($TTFboxLine[0], $TTFboxLine[2], $TTFboxLine[4], $TTFboxLine[6]);
                    $max_x_line = max($TTFboxLine[0], $TTFboxLine[2], $TTFboxLine[4], $TTFboxLine[6]);
                    //$text_width = round($max_x - $min_x + ($size * 0.5));
                    $text_width_line = round($max_x_line - $min_x_line);

                    $min_y_line = min($TTFboxLine[1], $TTFboxLine[3], $TTFboxLine[5], $TTFboxLine[7]);
                    $max_y_line = max($TTFboxLine[1], $TTFboxLine[3], $TTFboxLine[5], $TTFboxLine[7]);
                    //$text_height = round($max_y - $min_y + ($size * 0.5));
                    $text_height_line = round($max_y_line - $min_y_line);

                    switch ($alignment) {
                        // $text_origin_y set above, just re-set $text_origin_x here as needed

                        case 'L':
                        case 'TL':
                        case 'BL':
                            // no change neccesary
                            break;

                        case 'C':
                        case 'T':
                        case 'B':
                            $text_origin_x = ($originOffsetX ? $originOffsetX - round($text_width_line / 2) : round((ImageSX($gdimg) - $text_width_line) / 2));
                            break;

                        case 'R':
                        case 'TR':
                        case 'BR':
                            $text_origin_x = ($originOffsetX ? $originOffsetX - $text_width_line : ImageSX($gdimg) - $text_width_line  + $TTFbox[0] - $min_x + round($size * 0.25) - $margin);
                            break;
                    }

                    //ImageTTFtext($gdimg, $size, $angle, $text_origin_x, $text_origin_y, $letter_color_text, $ttffont, $text);
                    $this->DebugMessage('WatermarkText() calling ImageTTFtext($gdimg, '.$size.', '.$angle.', '.$text_origin_x.', '.($text_origin_y + $y_offset).', $letter_color_text, '.$ttffont.', '.$line.')', __FILE__, __LINE__);
                    
                    
                    $this->shadow_text($gdimg, $size, $angle, $text_origin_x, $text_origin_y + $y_offset, $ttffont, $line,$opacity);
					
               //    ImageTTFtext($gdimg, $size, $angle, $text_origin_x, $text_origin_y + $y_offset, $letter_color_text, $ttffont, $line);

                    $y_offset += $char_height;
                }

            }
			return $that;
           // return true;

        } else { //TODO FIX and Test. 

            $size = min(5, max(1, $size));
            $this->DebugMessage('Using built-in font (size='.$size.') for text watermark'.($ttffont ? ' because $ttffont !is_readable('.$ttffont.')' : ''), __FILE__, __LINE__);

            $text_width  = 0;
            $text_height = 0;
            foreach ($textlines as $dummy => $line) {
                $text_width   = max($text_width, ImageFontWidth($size) * strlen($line));
                $text_height += ImageFontHeight($size);
            }
            if ($img_watermark = phpthumb_functions::ImageCreateFunction($text_width, $text_height)) {
                ImageAlphaBlending($img_watermark, false);
                if (phpthumb_functions::IsHexColor($bg_color)) {
                    $text_background_alpha = round(127 * ((100 - min(max(0, $bg_opacity), 100)) / 100));
                    $text_color_background = phpthumb_functions::ImageHexColorAllocate($img_watermark, $bg_color, false, $text_background_alpha);
                } else {
                    $text_color_background = phpthumb_functions::ImageHexColorAllocate($img_watermark, 'FFFFFF', false, 127);
                }
                $this->DebugMessage('WatermarkText() calling ImageFilledRectangle($img_watermark, 0, 0, '.ImageSX($img_watermark).', '.ImageSY($img_watermark).', $text_color_background)', __FILE__, __LINE__);
                ImageFilledRectangle($img_watermark, 0, 0, ImageSX($img_watermark), ImageSY($img_watermark), $text_color_background);

                if ($angle && function_exists('ImageRotate')) {
                    // using $img_watermark_mask is pointless if ImageRotate function isn't available
                    if ($img_watermark_mask = phpthumb_functions::ImageCreateFunction($text_width, $text_height)) {
                        $mask_color_background = ImageColorAllocate($img_watermark_mask, 0, 0, 0);
                        ImageAlphaBlending($img_watermark_mask, false);
                        ImageFilledRectangle($img_watermark_mask, 0, 0, ImageSX($img_watermark_mask), ImageSY($img_watermark_mask), $mask_color_background);
                        $mask_color_watermark = ImageColorAllocate($img_watermark_mask, 255, 255, 255);
                    }
                }

                $text_color_watermark = phpthumb_functions::ImageHexColorAllocate($img_watermark, $hex_color);
                foreach ($textlines as $key => $line) {
                    switch ($alignment) {
                        case 'C':
                            $x_offset = round(($text_width - (ImageFontWidth($size) * strlen($line))) / 2);
                            $originOffsetX = (ImageSX($gdimg) - ImageSX($img_watermark)) / 2;
                            $originOffsetY = (ImageSY($gdimg) - ImageSY($img_watermark)) / 2;
                            break;

                        case 'T':
                            $x_offset = round(($text_width - (ImageFontWidth($size) * strlen($line))) / 2);
                            $originOffsetX = (ImageSX($gdimg) - ImageSX($img_watermark)) / 2;
                            $originOffsetY = $margin;
                            break;

                        case 'B':
                            $x_offset = round(($text_width - (ImageFontWidth($size) * strlen($line))) / 2);
                            $originOffsetX = (ImageSX($gdimg) - ImageSX($img_watermark)) / 2;
                            $originOffsetY = ImageSY($gdimg) - ImageSY($img_watermark) - $margin;
                            break;

                        case 'L':
                            $x_offset = 0;
                            $originOffsetX = $margin;
                            $originOffsetY = (ImageSY($gdimg) - ImageSY($img_watermark)) / 2;
                            break;

                        case 'TL':
                            $x_offset = 0;
                            $originOffsetX = $margin;
                            $originOffsetY = $margin;
                            break;

                        case 'BL':
                            $x_offset = 0;
                            $originOffsetX = $margin;
                            $originOffsetY = ImageSY($gdimg) - ImageSY($img_watermark) - $margin;
                            break;

                        case 'R':
                            $x_offset = $text_width - (ImageFontWidth($size) * strlen($line));
                            $originOffsetX = ImageSX($gdimg) - ImageSX($img_watermark) - $margin;
                            $originOffsetY = (ImageSY($gdimg) - ImageSY($img_watermark)) / 2;
                            break;

                        case 'TR':
                            $x_offset = $text_width - (ImageFontWidth($size) * strlen($line));
                            $originOffsetX = ImageSX($gdimg) - ImageSX($img_watermark) - $margin;
                            $originOffsetY = $margin;
                            break;

                        case 'BR':
                        default:
                            $x_offset = $text_width - (ImageFontWidth($size) * strlen($line));
                            $originOffsetX = ImageSX($gdimg) - ImageSX($img_watermark) - $margin;
                            $originOffsetY = ImageSY($gdimg) - ImageSY($img_watermark) - $margin;
                            break;
                    }
                    $this->DebugMessage('WatermarkText() calling ImageString($img_watermark, '.$size.', '.$x_offset.', '.($key * ImageFontHeight($size)).', '.$line.', $text_color_watermark)', __FILE__, __LINE__);
                    ImageString($img_watermark, $size, $x_offset, $key * ImageFontHeight($size), $line, $text_color_watermark);
                    if ($angle && $img_watermark_mask) {
                        $this->DebugMessage('WatermarkText() calling ImageString($img_watermark_mask, '.$size.', '.$x_offset.', '.($key * ImageFontHeight($size)).', '.$text.', $mask_color_watermark)', __FILE__, __LINE__);
                        ImageString($img_watermark_mask, $size, $x_offset, $key * ImageFontHeight($size), $text, $mask_color_watermark);
                    }
                }
                if ($angle && $img_watermark_mask) {
                    $img_watermark      = ImageRotate($img_watermark,      $angle, $text_color_background);
                    $img_watermark_mask = ImageRotate($img_watermark_mask, $angle, $mask_color_background);
                    phpthumb_filters::ApplyMask($img_watermark_mask, $img_watermark);
                }
                //phpthumb_filters::WatermarkOverlay($gdimg, $img_watermark, $alignment, $opacity, $margin);
                $this->DebugMessage('WatermarkText() calling phpthumb_filters::WatermarkOverlay($gdimg, $img_watermark, '.($originOffsetX.'x'.$originOffsetY).', '.$opacity.', 0)', __FILE__, __LINE__);
                phpthumb_filters::WatermarkOverlay($gdimg, $img_watermark, $originOffsetX.'x'.$originOffsetY, $opacity, 0);
                ImageDestroy($img_watermark);
                return true;
            }

        }
        return false;
    }

	
	
	
	
	
}

$pt = PhpThumb::getInstance();
$pt->registerPlugin('GdWatermarkTTF', 'gd');
