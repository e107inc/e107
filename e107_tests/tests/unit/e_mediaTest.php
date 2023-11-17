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

			$filetypesFile = e_SYSTEM."filetypes.xml";

			$content = '<?xml version="1.0" encoding="utf-8"?>
							<e107Filetypes>
								<class name="253" type="zip,gz,jpg,jpeg,png,webp,gif,xml,pdf" maxupload="2M" />
							</e107Filetypes>';

			file_put_contents($filetypesFile, $content);

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

				$this->assertNotFalse($result);
				$this->assertStringEndsWith('/'.basename($var['file']), $result['result']);
				$this->assertNotEmpty($result['preview']);
			}

			$refusalTests = array(
			0 => array(
						'file'  => codecept_data_dir()."mediaTest/vulnerable.png.svg",
						'param' => array (
							  'for' => 'news ',
							  'w' => '206',
							  'h' => '190',
						),
						'error' => 120
				),
			);

			foreach($refusalTests as $index => $var)
			{
				$source = $var['file'];
				$file = e_IMPORT.basename($var['file']);
				copy($source,$file);

				$json = $this->md->processAjaxImport($file,$var['param']);

				$result = json_decode($json, JSON_PRETTY_PRINT);

				$this->assertNotFalse($result);
				$this->assertNotEmpty($result['error']);
				$this->assertNotEmpty($result['error']['code']);
				$this->assertSame($var['error'], $result['error']['code']);
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

		private function compileFontAwesomeMeta($version)
		{
			$raw = file_get_contents(e_WEB."lib/font-awesome/$version/metadata/icons.json");
			$icons = e107::unserialize($raw);

			$ret = [];

			$keys = array('brands'  => 'fab', 'solid' => 'fas', 'regular'=> 'far');

			foreach($icons as $icon => $meta)
			{
				foreach($meta['free'] as $type)
				{
					$key = $keys[$type];

					$ret[$key][] = $icon;
				}

			}

			$ret['fa'.$version.'-shims'] = $this->compileFontAwesomeShims($version);

			return $ret;

		}

		/**
		 * @param string $version (major version number. eg. 5 or 6)
		 * @return array
		 */
		private function compileFontAwesomeShims($version)
		{
			$raw = file_get_contents(e_WEB."lib/font-awesome/$version/metadata/shims.json");
			$icons = e107::unserialize($raw);

			$ret = [];
			foreach($icons as $var)
			{
				$i = $var[0];
				$prefix = !empty($var[1]) ? $var[1] : 'fa';
				$ico = !empty($var[2]) ? $var[2] : $i ;

				$ret[$i] = $prefix." fa-".$ico;

			}
			return $ret;
		}

		public function testGetGlyphs()
		{

			// @todo uncomment to rebuild  getGlyphs() arrays for fontawesome. (requires 'metadata' folder)
		//	$meta = $this->compileFontAwesomeMeta(6);
		//	var_export($meta);
		//	$far = $this->md->getGlyphs('far');
		//	$this->assertSame($meta['far'], $far);
		// 	$fas = $this->md->getGlyphs('fas');
		//	$this->assertSame($meta['fas'], $fas);
		// 	$fab = $this->md->getGlyphs('fab');
		//	$this->assertSame($meta['fab'], $fab);
			// Check that FontAwesome 5 meta arrays are up-to-date.

			// FontAwesome 6
			$fa6_fas = $this->md->getGlyphs('fa6-fas');
			$this->assertContains('wine-glass-empty', $fa6_fas);

			$fa6Shims = $this->md->getGlyphs('fa6-shims');
			$this->assertArrayHasKey('glass', $fa6Shims);

			// FontAwesome 5
			$fab = $this->md->getGlyphs('fa5-fab');
			$this->assertContains('500px', $fab);

			$fas = $this->md->getGlyphs('fa5-fas');
			$this->assertContains('address-book', $fas);

			$far = $this->md->getGlyphs('fa5-far');
			$this->assertContains('arrow-alt-circle-down', $far);

			// Check FontAwesome 4
			$fa4 = $this->md->getGlyphs('fa5-fas');
			$this->assertContains('heart', $fa4);

			// Check Bootstrap 3
			$result = $this->md->getGlyphs('bs3');
			$this->assertNotEmpty($result['adjust']);
			$this->assertNotEmpty($result['zoom-out']);

			// Check FontAwesome 5 Shims
			$fa5Shims = $this->md->getGlyphs('fa5-shims');
			$this->assertArrayHasKey('glass', $fa5Shims);

			$prefixTest = $this->md->getGlyphs('fa5-fab', 'myprefix-');
			 $this->assertContains('myprefix-500px', $prefixTest);

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
*/
		public function testDetectType()
		{
			$tests = array(
				0 => array(
					'input'     => 'gallery/images/butterfly.jpg',
					'expected' => 'image'
				),
				1 => array(
					'input'     => 'myfile.mov',
					'expected' => 'video'
				),
				2 => array(
					'input'     => 'myfile.mp4',
					'expected' => 'video'
				),
				3 => array(
					'input'     => 'https://via.placeholder.com/728x90.png?text=Label',
					'expected' => 'image'
				),



			);

			foreach($tests as $index => $var)
			{
				$result = $this->md->detectType($var['input']);
				$this->assertSame($var['expected'], $result, 'Failed on index #'.$index. ': '.$var['input']);
			}


		}
/*
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
