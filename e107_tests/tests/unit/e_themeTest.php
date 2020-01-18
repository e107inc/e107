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

			$pref = array (
				'jumbotron_home' =>
					array (
						0 => 'FRONTPAGE',
						1 => 'page.php?3!',
						2 => '/my-sef-url!',
						3 => '/news/?page=',
					),
				'jumbotron_full' =>
					array (
						0 => 'forum',
						1 => 'user.php!', // <-- exact match of URL
			//			2 => '/user', // <-- Expecting URL to match both user and usersetting since it contains no "!"
					),
				'jumbotron_sidebar_right' =>
					array (
						0 => '/news',
						1 => '/user/',
						2 => 'user.php?id',
				//		1 => '/usersettings.php'
					),
				'other_layout'  =>
					array(
						0 => 'myplugin.php$', // <-- $ = script name match
						1 => 'forum/index.php',
						2 => 'page.php$', // <-- $ = script name match
						3 => '/user/settings?',
						4 => 'script.php$',
						5 => '/news/?bla',
					),
				'script_match' =>
					array(
					 0  => 'myplugin/index.php$', // <-- $ = script name match

					),
			);

			$defaultLayout = "jumbotron_sidebar_right";


			$tests = array(
				0 => array('url' => SITEURL."index.php",                                                'expected' => 'jumbotron_home'),
				1 => array('url' => SITEURL."index.php?",                                               'expected' => 'jumbotron_home'),
				2 => array('url' => SITEURL."index.php?fbclid=asdlkjasdlakjsdasd",                      'expected' => 'jumbotron_home'),
				3 => array('url' => SITEURL."index.php?utm_source=asdd&utm_medium=asdsd",               'expected' => 'jumbotron_home'),
				4 => array('url' => SITEURL."news",                                                     'expected' => 'jumbotron_sidebar_right'),
				5 => array('url' => SITEURL."forum",                    'script' => "/forum/index.php",            'expected' => 'jumbotron_full'),
				6 => array('url' => SITEURL."other/page",               'script' => '/page.php',             'expected' => 'other_layout'),
				7 => array('url' => SITEURL."news.php?5.3",             'script' => '/news.php',             'expected' => 'jumbotron_sidebar_right'),
				8 => array('url' => SITEURL."usersettings.php",         'script' => '/usersettings.php',     'expected' => 'jumbotron_sidebar_right'),
				9 => array('url' => SITEURL."user.php",                 'script' => '/user.php',             'expected' => 'jumbotron_full'),
				10 => array('url' => SITEURL."page.php",                'script' => '/page.php',             'expected' => 'other_layout'),
				11 => array('url' => SITEURL."page.php?3",              'script' => '/page.php',             'expected' => 'jumbotron_home'),
				12 => array('url' => SITEURL."somepage/",               'script' => "/script.php",           'expected' => 'other_layout'),
				13 => array('url' => SITEURL."plugin/",                 'script' => "/myplugin.php",         'expected' => 'other_layout'),
				14 => array('url' => SITEURL."forum/index.php",         'script' => "/index.php",            'expected' => 'other_layout'),
				15 => array('url' => SITEURL."my-chapter/my-title",     'script' => "/page.php",             'expected' => 'other_layout'),
				16 => array('url' => SITEURL."my-sef-url",              'script' => '/index.php',            'expected' => 'jumbotron_home'),
				17 => array('url' => SITEURL."user/settings?id=1",      'script' => '/usersettings.php',     'expected' => 'other_layout'),
				18 => array('url' => SITEURL."user/Tijn",               'script' => '/user.php',             'expected' => 'jumbotron_sidebar_right'),
				19 => array('url' => SITEURL."user.php?id.1",           'script' => '/user.php',             'expected' => 'jumbotron_sidebar_right'),
				20 => array('url' => SITEURL."pluginpage/",             'script' => '/myplugin/index.php',   'expected' => 'script_match'),
				21 => array('url' => SITEURL."news/?page=",             'script' => '/news.php',             'expected' => 'jumbotron_home'),
				22 => array('url' => SITEURL."news/my-news-title",      'script' => '/news.php',             'expected' => 'jumbotron_sidebar_right'),
				23 => array('url' => SITEURL."news/?bla",                  'script' => '/news.php',             'expected' => 'other_layout'),

			);

			foreach($tests as $item=>$var)
			{
				$var['script'] = isset($var['script']) ? $var['script'] : null;

				$result = $this->tm->getThemeLayout($pref, $defaultLayout, $var['url'], $var['script']);
				$this->assertEquals($var['expected'],$result, "Wrong theme layout returned for item [".$item."] ".$var['url']);
			//	echo $var['url']."\t\t\t".$result."\n\n";
			}


			// print_r($_SERVER);

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



		public function testLoadLayout()
		{
			// $res = e_theme::loadLayout('full', 'bootstrap4');

		//	var_dump($res);


		}




	}
