<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2020 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_thumbnailTest extends \Codeception\Test\Unit
	{

		/** @var e_thumbnail */
		protected $thm;

		protected $thumbPath;

		protected function _before()
		{
			require_once(e_HANDLER."e_thumbnail_class.php");

			try
			{
				$this->thm = $this->make('e_thumbnail');
			}
			catch(Exception $e)
			{
				self::assertTrue(false, $e->getMessage());
			}

			$this->thm->setCache(false);
			$this->thm->setDebug(true);

			$this->thumbPath = codecept_data_dir()."thumbnailTest".DIRECTORY_SEPARATOR;

		}

		public function testSendImage()
		{
			$tests = array(
				0 => array (
					'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
					'w' => 222,
					'h' => 272,
					),

				1 => array (
					'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
					'w' => 100,
					'h' => 0,
					),

				2 => array (
					'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
					'w' => 0,
					'h' => 500,
					),

				3 => array (
					'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
					'w' => 200,
					'h' => 300,
					),


				4 => array (
					'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
					'aw' => 300,
					'ah' => 300,
					),

				5 => array (
					'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
					'aw' => 600,
					'ah' => 200,
					),

				// default image size
				6 => array (
					'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
					'w' => 0,
					'h' => 0,
					),

				7 => array (
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'aw' => 600,
				  'ah' => 200,
				  'c' => 't', // crop from top
				),

				8 => array (
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'aw' => 600,
				  'ah' => 200,
				  'c' => 'c', // crop at center
				),

				9 => array (
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'aw' => 600,
				  'ah' => 200,
				  'c' => 'b', // crop at bottom
				),

				10 => array (
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'aw' => 200,
				  'ah' => 400,
				  'c' => 'l', // crop left
				),

				11 => array (
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'aw' => 200,
				  'ah' => 400,
				  'c' => 'r', // crop right
				),

				// PNG at default size.
				12 => array (
				  'src' => 'e_IMAGE/logo.png',
				  'w' => 0,
				  'h' => 0,
				//  'c' => 'r', // crop right
				),

				// Resize up a PNG
				13 => array (
				  'src' => 'e_IMAGE/logo.png',
				  'w' => 400,
				  'h' => 0,
				//  'c' => 'r', // crop right
				),

				// Test Resize Auto-disabled low resolution icons.
				14 => array (
				  'src' => 'e_IMAGE/e107_icon_32.png',
				  'w' => 80,
				  'h' => 0,
				//  'c' => 'r', // crop right
				),
				15 => array (
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'aw' => 80,
				  'ah' => 0,
				//  'c' => 'r', // crop right
				),



			);

			// WebP support added in PHP 7.1+
			$ver = (float) phpversion();

			if ($ver > 7.0)
			{
				// $this->markTestSkipped('must be revisited.');

				// Test WebP format resize.
				$tests[] = array(
				  'src' => 'e_PLUGIN/gallery/images/beach.webp',
				  'aw' => 455,
				  'ah' => 0,

				);

				// Test Converting JPEG to WebP and resize. (Stored index file is saved with a .jpg extension but encoded as WebP)
				$tests[] = array(
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'w' => 222,
				  'h' => 272,
				  'type'=>'webp'
				);
			}

			foreach($tests as $index => $val)
			{

				$this->thm->setRequest($val);
				$this->thm->checkSrc();

				list($file,$ext) = explode(".",$val['src']);
				unset($file);
				$generatedImage = $this->thm->sendImage();
				$storedImage = $this->thumbPath."image_".$index.".".$ext;

				$compare = new compareImages($storedImage);
				$diff = $compare->compareWith($generatedImage);

				$status = ($diff < 5);

			//	$actual     = getimagesize($generatedImage);
			//	$expected   = getimagesize($storedImage);

				if($status === false)
				{
					rename($generatedImage,codecept_output_dir()."sendImage_".time()."_index_".$index.".".$ext);
				}

				self::assertTrue($status, "Image Index #".$index." failed the image-comparison check");


			}

		}

	}

/**
 * @author ThaoNv - 2016
 * Fast PHP compare images
 * https://github.com/nvthaovn/CompareImage
 * ---------------------------
 * @todo Move this class to an appropriate location.
 * */

class compareImages
{
    public $source = null;
    private $hasString = '';

    function __construct($source)
    {
        $this->source = $source;
    }

    private function mimeType($i)
    {
        /*returns array with mime type and if its jpg or png. Returns false if it isn't jpg or png*/
        $mime = getimagesize($i);
        $return = array($mime[0], $mime[1]);

        switch ($mime['mime']) {
            case 'image/jpeg':
                $return[] = 'jpg';
                return $return;
            case 'image/png':
                $return[] = 'png';
                return $return;
	        case 'image/webp':
	             $return[] = 'webp';
                return $return;
            case 'image/gif':
	             $return[] = 'gif';
                return $return;
            default:
                return false;
        }
    }

    private function createImage($i)
    {
        /*retuns image resource or false if its not jpg or png*/
        $mime = $this->mimeType($i);

	    switch($mime[2])
	    {
		    case "jpg":
			    return imagecreatefromjpeg($i);
			    break;

		    case "png":
			    return @imagecreatefrompng($i);
			    break;

			case "gif":
			    return imagecreatefromgif($i);
			    break;

			case "webp":
			    return imagecreatefromwebp($i);
			    break;

		    default:
			   return false;
	    }

    }

    private function resizeImage($source)
    {
        /*resizes the image to a 8x8 squere and returns as image resource*/
        $mime = $this->mimeType($source);
        $t = imagecreatetruecolor(8, 8);
        $source = $this->createImage($source);
        imagecopyresized($t, $source, 0, 0, 0, 0, 8, 8, $mime[0], $mime[1]);
        return $t;
    }

    private function colorMeanValue($i)
    {
        /*returns the mean value of the colors and the list of all pixel's colors*/
        $colorList = array();
        $colorSum = 0;
        for ($a = 0; $a < 8; $a++) {
            for ($b = 0; $b < 8; $b++) {
                $rgb = imagecolorat($i, $a, $b);
                $colorList[] = $rgb & 0xFF;
                $colorSum += $rgb & 0xFF;
            }
        }
        return array($colorSum / 64, $colorList);
    }

    private function bits($colorMean)
    {
        /*returns an array with 1 and zeros. If a color is bigger than the mean value of colors it is 1*/
        $bits = array();
        foreach ($colorMean[1] as $color) {
            $bits[] = ($color >= $colorMean[0]) ? 1 : 0;
        }
        return $bits;

    }

    public function compareWith($tagetImage)
    {
        $tagetString = $this->hasString($tagetImage);
        if ($tagetString) {
            return $this->compareHash($tagetString);
        }
        return 100;
    }

	/**
	 * Hash String from image. You can save this string to database for reuse
	 * @param $image
	 * @return String 64 character
	 */
    private function hasString($image)
    {
        $i1 = $this->createImage($image);
        if (!$i1) {
            return false;
        }
        $i1 = $this->resizeImage($image);
        imagefilter($i1, IMG_FILTER_GRAYSCALE);
        $colorMean1 = $this->colorMeanValue($i1);
        $bits1 = $this->bits($colorMean1);
        $result = '';
        for ($a = 0; $a < 64; $a++) {
            $result .= $bits1[$a];
        }
        return $result;
    }

    /**
     * Get current image hash String
     * */
    public function getHasString()
    {
        if ($this->hasString == '') {
            $this->hasString = $this->hasString($this->source);
        }
        return $this->hasString;
    }

	/**
	 * Get hash String from image url
	 * ex: $imageHash = $this->hasStringImage('http://media.com/image.jpg');
	 * @param $image
	 * @return false|String
	 */
    public function hasStringImage($image)
    {
        return $this->hasString($image);
    }

	/**
	 * Compare current image with an image hash String
	 * @param $imageHash
	 * @return int different rates . if different rates < 10 => duplicate image
	 */
    public function compareHash($imageHash)
    {
        $sString = $this->getHasString();
        if (strlen((string) $imageHash) == 64 && strlen((string) $sString) == 64) {
            $diff = 0;
            $sString = str_split((string) $sString);
            $imageHash = str_split((string) $imageHash);
            for($a = 0; $a < 64; $a++) {
                if ($imageHash[$a] != $sString[$a]) {
                    $diff++;
                }
            }
            return $diff;
        }
        return 64;
    }
}