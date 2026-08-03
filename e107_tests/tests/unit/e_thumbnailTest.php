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

		/** @var array media_url => the media_userclass the install shipped */
		private $restoreRows = array();

		protected function _after()
		{
			$this->releaseMediaRows();
		}

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

		/**
		 * The decisions media_userclass encodes, one row per case.
		 *
		 * checkSrc() is the seam: it is what both entry points call and what
		 * the permission test lives in, so the answer asserted here is the
		 * answer the endpoints turn into 200 or 403.
		 */
		public function testCheckSrcAppliesMediaUserclass()
		{
			$cases = array(
				// media_userclass, classes the caller holds, may read
				array('',        array(e_UC_GUEST),  true,  'the column default is not a restriction'),
				array('0',       array(e_UC_GUEST),  true,  'e_UC_PUBLIC admits everyone'),
				array(' 0 ',     array(e_UC_GUEST),  true,  'a padded value is still read'),
				array('253',     array(e_UC_GUEST),  false, 'a guest does not hold e_UC_MEMBER'),
				array('253',     array(e_UC_MEMBER), true,  'the holder of the class reads it'),
				array('253,254', array(e_UC_ADMIN),  true,  'any one member of the list is enough'),
				array('253,254', array(e_UC_GUEST),  false, 'holding none of the list refuses'),
				array('255',     array(e_UC_MAINADMIN, e_UC_ADMIN, e_UC_MEMBER), false, 'e_UC_NOBODY refuses even a main admin'),
				array('abc',     array(e_UC_MEMBER), false, 'a value naming no class admits nobody'),
				array('253,abc', array(e_UC_MEMBER), true,  'an unreadable entry does not cost the holder the item'),
				array('-253',    array(e_UC_MEMBER), false, 'an inverted class is refused, not resolved'),
			);

			foreach($cases as $case)
			{
				list($userclass, $held, $expected, $why) = $case;

				$this->restrictMediaRow('{e_IMAGE}logo.png', $userclass);

				$thm = $this->thumbnailFor($held, 'e_IMAGE/logo.png');
				$actual = $thm->checkSrc();

				$this->releaseMediaRows();

				self::assertSame($expected, $actual,
					"media_userclass '".$userclass."' against class list '".implode(',', $held)."': ".$why);
			}
		}

		/**
		 * media_url is not confined to {e_MEDIA*}. A theme install, a plugin
		 * install and the 1.x upgrade routines all write rows outside it, and
		 * every directory they write about is a directory this endpoint serves
		 * from, so the key has to be derived for all of them.
		 *
		 * Two of these are rows a stock install already holds, put there by the
		 * plugin and theme installers, and the case restricts them the way the
		 * media manager's inline editor does. The keys are spelled out rather
		 * than computed, because the point of the case is that the thumbnailer
		 * reads back exactly what e_media::import() wrote.
		 */
		public function testCheckSrcAppliesMediaUserclassOutsideMediaRoot()
		{
			$cases = array(
				'{e_IMAGE}logo.png'                      => 'e_IMAGE/logo.png',
				'{e_PLUGIN}gallery/images/butterfly.jpg' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				'{e_THEME}bootstrap5/images/lumen.png'   => 'e_THEME/bootstrap5/images/lumen.png',
			);

			foreach($cases as $key => $src)
			{
				$this->restrictMediaRow($key, '253');

				$refused = $this->thumbnailFor(array(e_UC_GUEST), $src)->checkSrc();
				$served = $this->thumbnailFor(array(e_UC_MEMBER), $src)->checkSrc();

				$this->releaseMediaRows();

				self::assertFalse($refused, "A guest was given ".$key.", which is restricted to e_UC_MEMBER.");
				self::assertTrue($served, "The holder of the class was refused ".$key.".");
			}
		}

		/**
		 * An e_thumbnail whose caller identity is supplied rather than looked
		 * up. The class resolves it from USERCLASS_LIST or from a session, and
		 * a unit run has the first of those and no way to change it, so the
		 * seam is stubbed. The shipped class deliberately has no public setter
		 * for it: that would let any caller declare itself into a class.
		 *
		 * @param array  $held class ids the caller holds
		 * @param string $src  request source, in e107 shortcode path form
		 * @return e_thumbnail
		 */
		private function thumbnailFor(array $held, $src)
		{
			$thm = $this->make('e_thumbnail', array(
				'userClasses' => function() use ($held) { return $held; },
			));

			$thm->setRequest(array('src' => $src));

			return $thm;
		}

		/**
		 * Put $url behind $class, whichever way the install got there: a plugin
		 * or theme install has already written a row for its own images, and
		 * media_url is unique, so an insert would be rejected and the case
		 * would then be about the shipped row instead of the intended one.
		 *
		 * @param string $url   media_url, as createConstants() spells it
		 * @param string $class media_userclass
		 * @return void
		 */
		private function restrictMediaRow($url, $class)
		{
			$sql = e107::getDb();

			$row = false;

			if($sql->select('core_media', 'media_userclass', "media_url='".$sql->escape($url)."' LIMIT 1"))
			{
				$row = $sql->fetch();
			}

			if(empty($row))
			{
				$sql->insert('core_media', array('data' => array(
					'media_type'        => 'image/png',
					'media_name'        => basename($url),
					'media_caption'     => 'p16unit',
					'media_description' => '',
					'media_category'    => '_common_image',
					'media_datestamp'   => time(),
					'media_author'      => 1,
					'media_url'         => $url,
					'media_size'        => 0,
					'media_dimensions'  => '',
					'media_userclass'   => $class,
					'media_usedby'      => '',
					'media_tags'        => '',
				)));

				return;
			}

			$this->restoreRows[$url] = $row['media_userclass'];

			$sql->update('core_media', "media_userclass='".$sql->escape($class)
				."' WHERE media_url='".$sql->escape($url)."'");
		}

		/**
		 * @return void
		 */
		private function releaseMediaRows()
		{
			$sql = e107::getDb();

			foreach($this->restoreRows as $url => $class)
			{
				$sql->update('core_media', "media_userclass='".$sql->escape($class)
					."' WHERE media_url='".$sql->escape($url)."'");
			}

			$this->restoreRows = array();

			$sql->delete('core_media', "media_caption='p16unit'");
		}

		public function testPlaceholderImage()
		{
			$svg = $this->thm->placeholderImage(800, 350);
			self::assertStringStartsWith('<svg', $svg);
			self::assertStringContainsString('width="800"', $svg);
			self::assertStringContainsString('height="350"', $svg);
			self::assertStringContainsString('>800x350<', $svg);
			self::assertStringNotContainsString('placehold', $svg); // generated locally, no external service

			$svg = $this->thm->placeholderImage('', null);
			self::assertStringContainsString('width="100"', $svg);
			self::assertStringContainsString('height="100"', $svg);

			$svg = $this->thm->placeholderImage('"><script>alert(1)</script>', '9999999');
			self::assertStringNotContainsString('script', $svg);
			self::assertStringContainsString('width="100"', $svg);
			self::assertStringContainsString('height="4000"', $svg);
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
        if (strlen($imageHash) == 64 && strlen($sString) == 64) {
            $diff = 0;
            $sString = str_split($sString);
            $imageHash = str_split($imageHash);
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