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
				$this->fail($e->getMessage());
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
				array('path'=>'path-to-file/image.webp', 'mime' => 'svg+xml',    'expected'=>'path-to-file/image.webp'),
			);


			foreach($types as $val)
			{
				$actual = $this->md->checkFileExtension($val['path'],$val['mime']);
				$this->assertEquals($val['expected'],$actual);
			}

		}

		public function testProcessAjaxImport()
		{
			$tests = array(
				0 => array(
						'file'  => e_PLUGIN."gallery/images/horse.jpg",
						'param' => array (
							  'for' => 'news ',
							  'w' => '206',
							  'h' => '190',
						),
				),
				1 => array(
						'file'  => e_PLUGIN."gallery/images/beach.webp",
						'param' => array (
							  'for' => 'news ',
							  'w' => '206',
							  'h' => '190',
						),
				),

			);

			foreach($tests as $index => $var)
			{
				$source = $var['file'];
				$file = e_IMPORT.basename($var['file']);
				copy($source,$file);

				$json = $this->md->processAjaxImport($file,$var['param']);

				$result = json_decode($json, JSON_PRETTY_PRINT);
			//	var_dump($result);
				$this->assertNotFalse($result);

			//	var_dump($result);

				$this->assertStringEndsWith('/'.basename($var['file']), $result['result']);

				$this->assertNotEmpty($result['preview']);
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
*/
		public function testImportFile()
		{
			/* FIXME: https://github.com/e107inc/e107/issues/4033 */


			$tests = array(
		//		0   => array('file'=> codecept_data_dir().'icon_64.png', 'cat'  => '_icon',  'expected'=>"{e_MEDIA_ICON}icon_64.png"),
				1   => array('file'=> e_PLUGIN.'gallery/images/horse.jpg', 'cat'  => 'news',  'expected'=>"horse.jpg"),
				2   => array('file'=> e_PLUGIN.'gallery/images/beach.webp', 'cat'  => 'news',  'expected'=>"beach.webp"),
			);

			foreach($tests as $var)
			{
				$importPath = e_IMPORT.basename($var['file']);
				copy($var['file'], $importPath);

				if(!file_exists($importPath))
				{
					$this->fail("Couldn't copy file to ".$importPath);
				}

				$result = $this->md->importFile($importPath, $var['cat']);
				$this->assertStringEndsWith($var['expected'],$result);
			}

		}
/*
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
*/
		public function testGetGlyphs()
		{
			$result = $this->md->getGlyphs('bs3');
			$this->assertEquals('adjust', $result[0]);
			$this->assertEquals('zoom-out', $result[198]);

			$result = $this->md->getGlyphs('fab');
			$this->assertTrue(in_array('xbox', $result));

			$result = $this->md->getGlyphs('fas');
			$this->assertTrue(in_array('check-circle', $result));

		}
/*
		public function testImportIcons()
		{

		}

		public function testCreateCategories()
		{

		}

		public function testDeleteCategory()
		{

		}
*/
		public function testResizeImage()
		{
			$tests = array(
				0 => array(
					'input' => array('file'=>"{e_PLUGIN}gallery/images/butterfly.jpg", 'w' => 500, 'h' => 900),
					'expected' => array('filename'=>'500x900_butterfly.jpg', 'w' => 500, 'h' => 333) // aspect ratio maintained.
				),

			);

			foreach($tests as $index=>$var)
			{
				$output = codecept_output_dir().basename($var['input']['file']);
				$result = $this->md->resizeImage($var['input']['file'], $output,['w'=>500,'h'=>900]);

				$this->assertNotFalse($result, 'resizeImage() returned a value of false.');

				$info = getimagesize($result);

				$this->assertEquals($var['expected']['w'], $info[0], 'Image width mismatch on index #'.$index);
				$this->assertEquals($var['expected']['h'], $info[1], 'Image height mismatch on index #'.$index);

			}

		}
/*
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
*/
		public function testGetPath()
		{
			$result = $this->md->getPath('image/jpeg');
			$this->assertStringContainsString(e_MEDIA.'images/', $result);
		}
	}
