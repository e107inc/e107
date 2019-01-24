<?php
	/**
	 * Created by PhpStorm.
	 * User: Wiz
	 * Date: 1/24/2019
	 * Time: 9:21 AM
	 */


	class e_themeTest extends \Codeception\Test\Unit
	{

		/** @var e_theme */
		private $tm;

		protected function _before()
		{
			// require_once(e_HANDLER."e_marketplace.php");

			try
			{
				$this->tm = $this->make('e_theme');
				$this->tm->__construct();
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_theme object");
			}
		}


/*
		public function testCssAttribute()
		{

		}

		public function testUpgradeThemeCode()
		{

		}

		public function testGetThemeList()
		{

		}

		public function testLoadLibrary()
		{

		}

		public function testParse_theme_php()
		{

		}

		public function testGetThemeInfo()
		{

		}*/

		public function testGetThemeLayout()
		{
			// FRONTPAGE    = jumbotron_home
			// /news        = jumbotron_sidebar_right
			// forum        = jumbotron_full

			$tests = array(
				0 => array('url' => SITEURL."index.php",   'expected'=> 'jumbotron_home'),
				1 => array('url' => SITEURL."index.php?",   'expected'=> 'jumbotron_home'),
				2 => array('url' => SITEURL."index.php?fbclid=asdlkjasdlakjsdasd",   'expected'=> 'jumbotron_home'),
				3 => array('url' => SITEURL."index.php?utm_source=asdlkajsdasd&utm_medium=asdlkjasd",   'expected'=> 'jumbotron_home'),
				4 => array('url' => SITEURL."news",   'expected'=> 'jumbotron_sidebar_right'),
				5 => array('url' => SITEURL."forum",   'expected'=> 'jumbotron_full'),
				6 => array('url' => SITEURL."other/page",   'expected'=> 'jumbotron_sidebar_right'),
				7 => array('url' => SITEURL."news.php?5.3",   'expected'=> 'jumbotron_sidebar_right'),
			);



			foreach($tests as $var)
			{
				$result = $this->tm->getThemeLayout($var['url']);
				$this->assertEquals($var['expected'],$result, "Wrong theme layout returned for ".$var['url']);
			//	echo $var['url']."\t\t\t".$result."\n\n";
			}


		}
/*
				public function testClearCache()
				{

				}

				public function testGet()
				{

				}

				public function testGetList()
				{

				}

				public function testParse_theme_xml()
				{

				}
		*/



	}
