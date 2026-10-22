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

		/** @var string a name of a type that may not be uploaded, which the rename tests ask for */
		const REFUSED = 'media-rename-test.svg';

		/** @var string what a staged PDF holds */
		const PDF = "%PDF-1.4\n%%EOF\n";

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

		public function testProcessAjaxImportRefusesARenameToATypeThatMayNotBeUploaded()
		{
			list($result, $left) = $this->importStaged(self::REFUSED);

			$this->assertSame(array(false, false), $left, 'the upload was kept, or stored under the name of a type that may not be uploaded');
			$this->assertSame(120, $result['error']['code']);
		}

		public function testProcessAjaxImportKeepsAnUploadsOwnNameWhenTheRenameIsNotAName()
		{
			list($result, $left) = $this->importStaged(array(self::REFUSED));

			$this->assertArrayNotHasKey('error', $result);
			$this->assertSame(array(true, false), $left, 'the upload was not kept under its own name');
		}

		/**
		 * @param mixed $rename what processAjaxImport() is asked to rename an uploaded JPEG to
		 * @return array the decoded reply, and whether a file is left under the upload's own name and under self::REFUSED
		 */
		private function importStaged($rename)
		{
			$source = e_PLUGIN.'gallery/images/horse.jpg';
			$paths = array(e_IMPORT.'media-rename-test.jpg', e_IMPORT.self::REFUSED);

			copy($source, $paths[0]);

			try
			{
				return array(json_decode($this->md->processAjaxImport($paths[0], array('rename' => $rename)), true), array_map('file_exists', $paths));
			}
			finally
			{
				self::removeFiles($paths);
			}
		}

		/**
		 * @param string[] $paths files a test staged or made, removed where they are still there
		 */
		private static function removeFiles(array $paths)
		{
			foreach(array_filter($paths, 'is_file') as $path)
			{
				unlink($path);
			}
		}

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

		public function testImportFileStoresTheBareCommonCategoryAsTheCommonCategoryOfItsType()
		{
			$this->assertSame('_common_image', $this->importedCategory('media-common-test.jpg', $this->jpeg(), array('_common')));
			$this->assertSame('_common_file', $this->importedCategory('media-common-test.pdf', self::PDF, array('_common')));
		}

		public function testImportFileStoresAnImportWithNoCategoryUnderTheCommonCategoryOfItsType()
		{
			$this->assertSame('_common_file', $this->importedCategory('media-common-test.pdf', self::PDF, array('')));
			$this->assertSame('_common_file', $this->importedCategory('media-common-test.pdf', self::PDF, array()));
		}

		public function testImportFileDropsTheIncludeCommonMarker()
		{
			$this->assertSame('news', $this->importedCategory('media-marker-test.jpg', $this->jpeg(), array('news^')));
		}

		/**
		 * @return string what a staged JPEG holds
		 */
		private function jpeg()
		{
			return file_get_contents(e_PLUGIN.'gallery/images/horse.jpg');
		}

		/**
		 * @param string $name the name the file is staged under in e_IMPORT, which this removes again with whatever the import made of it
		 * @param string $content what the staged file holds
		 * @param array $arguments what importFile() is given after the name, the category first
		 * @return string the media_category the import was stored under
		 */
		private function importedCategory($name, $content, array $arguments)
		{
			$sql = e107::getDb();
			file_put_contents(e_IMPORT.$name, $content);
			$url = false;
			$row = false;

			try
			{
				$url = call_user_func_array(array($this->md, 'importFile'), array_merge(array($name), $arguments));
				$this->assertNotFalse($url, 'the import failed');

				$sql->select('core_media', 'media_id, media_category', 'media_url = :url', array('url' => $url));
				$row = $sql->fetch();
				$this->assertNotFalse($row, 'the import stored no row');

				return $row['media_category'];
			}
			finally
			{
				$paths = array(e_IMPORT.$name);

				if($row)
				{
					$sql->delete('core_media', 'media_id = '.(int) $row['media_id']);
				}

				if($url)
				{
					$paths[] = e107::getParser()->replaceConstants($url);
				}

				self::removeFiles($paths);
			}
		}

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

		/**
		 * The media manager inserts these names as they are, so whitespace inside one breaks the icon it names.
		 */
		public function testGetGlyphsNamesCarryNoWhitespace()
		{
			foreach(array('bs2', 'bs3', 'fa4', 'fa5-fab', 'fa5-fas', 'fa5-far', 'fa6-fab', 'fa6-fas') as $set)
			{
				self::assertSame(array(), preg_grep('/\s/', $this->md->getGlyphs($set)), 'getGlyphs() returned names with whitespace in them for '.$set);
			}
		}

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
				4 => array(
					'input'     => 'https://placehold.co/728x90.jpg?text=Label',
					'expected' => 'image'
				),
				5 => array(
					'input'     => '{e_PLUGIN}hero/images/placeholder-1.svg',
					'expected' => 'image'
				),
				6 => array(
					'input'     => 'myfile.mp4?version=2',
					'expected' => 'video'
				),

			);

			foreach($tests as $index => $var)
			{
				$result = $this->md->detectType($var['input']);
				$this->assertSame($var['expected'], $result, 'Failed on index #'.$index. ': '.$var['input']);
			}


		}
		public function testGetPath()
		{
			$result = $this->md->getPath('image/jpeg');
			$this->assertStringContainsString(e_MEDIA.'images/', $result);
		}

		/**
		 * @return array
		 */
		public function commonRowsAndLookalikes()
		{
			return array(
				'an image filed as the legacy _common' => array('getImages', '_common', 'image/jpeg', array('news^', '', '_common_image', '_common')),
				'a video filed as the legacy _common'  => array('getVideos', '_common', 'video/mp4', array('news^', '', '_common_video', '_common')),
				'an audio filed as the legacy _common' => array('getAudios', '_common', 'audio/mpeg', array('news^', '', '_common_audio', '_common')),
				'a file filed as the legacy _common'   => array('getFiles', '_common', 'application/pdf', array('news^', '', '_common_file')),
				'a file filed as _common_file'         => array('getFiles', '_common_file', 'application/pdf', array('news^', '', '_common_file')),
				'an image filed as old_common'         => array('getImages', 'old_common', 'image/jpeg', array(), array('news^', '', '_common_image', '_common')),
				'an image filed as _common_file'       => array('getImages', '_common_file', 'image/jpeg', array(), array('news^', '', '_common_image')),
			);
		}

		/**
		 * @dataProvider commonRowsAndLookalikes
		 * @param string $getter the e_media method a picker of the row's type lists through
		 * @param string $category what the row is filed under
		 * @param string $type the row's MIME type
		 * @param string[] $offering categories whose picker offers the row
		 * @param string[] $refusing categories whose picker leaves the row out
		 */
		public function testAPickerOffersTheCommonRowsOfItsTypeAndNoLookalike($getter, $category, $type, array $offering, array $refusing = array('news'))
		{
			$sql = e107::getDb();
			$id = (int) $sql->insert('core_media', array(
				'media_name'      => 'mediatest common row',
				'media_category'  => $category,
				'media_url'       => '{e_MEDIA}mediatest-common-row',
				'media_type'      => $type,
				'media_userclass' => 0,
			));
			$this->assertGreaterThan(0, $id);

			try
			{
				foreach($offering as $cat)
				{
					$this->assertArrayHasKey($id, $this->md->$getter($cat), "a picker for '$cat' left the row out");
				}

				foreach($refusing as $cat)
				{
					$this->assertArrayNotHasKey($id, $this->md->$getter($cat), "a picker for '$cat' offered the row");
				}
			}
			finally
			{
				$sql->delete('core_media', 'media_id = '.$id);
			}
		}
	}
