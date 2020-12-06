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
				$this->assertTrue(false, "Couldn't load e_thumbnail object");
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

				6 => array (
					'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
					'w' => 0,
					'h' => 0,
					),

				// TODO Find a way to test that the images have been cropped correctly. (see below)
/*
				7 => array (
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'aw' => '600',
				  'ah' => '200',
				  'c' => 't', // crop from top
				),

				8 => array (
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'aw' => '600',
				  'ah' => '200',
				  'c' => 'c', // crop at center
				),

				9 => array (
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'aw' => '600',
				  'ah' => '200',
				  'c' => 'b', // crop at bottom
				),

				10 => array (
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'aw' => '200',
				  'ah' => '400',
				  'c' => 'l', // crop left
				),

				11 => array (
				  'src' => 'e_PLUGIN/gallery/images/butterfly.jpg',
				  'aw' => '200',
				  'ah' => '400',
				  'c' => 'r', // crop right
				),

			*/

			);

			foreach($tests as $index => $val)
			{

				$this->thm->setRequest($val);
				$this->thm->checkSrc();

				$generatedImage = $this->thm->sendImage();

				$actual     = getimagesize($generatedImage);
				$expected   = getimagesize($this->thumbPath."image_".$index.".jpg");

				$this->assertSame($expected,$actual, "Image Index #".$index." failed the check");
			}

		}

	}
