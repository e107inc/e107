<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_mediaTest extends \Codeception\Test\Unit
	{

		/** @var e_media  */
		protected $md;

		protected function _before()
		{
			try
			{
				$this->md = $this->make('e_media');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load e_media object");
			}

		}


		public function testCheckFileExtension()
		{
			$types = array(
				array('path'=>'path-to-file/image.jpg', 'mime' => 'image/jpeg', 'expected'=>'path-to-file/image.jpg'),
				array('path'=>'path-to-file/image',     'mime' => 'image/jpeg', 'expected'=>'path-to-file/image.jpg'),
				array('path'=>'path-to-file/audio' ,    'mime' => 'audio/mpeg', 'expected'=>'path-to-file/audio.mp3'),
				array('path'=>'path-to-file/audio.mp3', 'mime' => 'audio/mpeg', 'expected'=>'path-to-file/audio.mp3'),
				array('path'=>'path-to-file/image.svg', 'mime' => 'svg+xml',    'expected'=>'path-to-file/image.svg'),
			);


			foreach($types as $val)
			{
				$actual = $this->md->checkFileExtension($val['path'],$val['mime']);

				$this->assertEquals($val['expected'],$actual);
				//echo ($actual)."\n";
			}



		}


/*

		public function testConvertImageToJpeg()
		{

		}




		public function testCheckDupe()
		{

		}

		public function testBrowserIndicators()
		{

		}

		public function testMediaData()
		{

		}

		public function testImport()
		{

		}

		public function testBrowserCarouselItem()
		{

		}

		public function testImportFile()
		{

		}

		public function testBrowserCarousel()
		{

		}

		public function testCountImages()
		{

		}

		public function testMediaSelect()
		{

		}

		public function testCreateCategory()
		{

		}

		public function testGetImages()
		{

		}

		public function testRemoveCat()
		{

		}

		public function testRemovePath()
		{

		}

		public function testCreateUserCategory()
		{

		}

		public function testGetFiles()
		{

		}

		public function testListIcons()
		{

		}

		public function testGetGlyphs()
		{

		}

		public function testImportIcons()
		{

		}

		public function testCreateCategories()
		{

		}

		public function testDeleteCategory()
		{

		}

		public function testResizeImage()
		{

		}

		public function testPreviewTag()
		{

		}

		public function testDetectType()
		{

		}

		public function testGetVideos()
		{

		}

		public function testSaveThumb()
		{

		}

		public function testGetAudios()
		{

		}

		public function testDebug()
		{

		}

		public function testGetCategories()
		{

		}

		public function testGetThumb()
		{

		}

		public function testDeleteAllCategories()
		{

		}

		public function testLog()
		{

		}

		public function testGetIcons()
		{

		}

		public function testGetPath()
		{

		}*/
	}
