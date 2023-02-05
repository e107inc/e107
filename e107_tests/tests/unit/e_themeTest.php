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
		}
		catch(Exception $e)
		{
			$this->fail("Couldn't load e_theme object");
		}

		$this->tm->clearCache();
		e107::getTheme()->clearCache();
	}



	public function testCssAttribute()
	{
		$result = e107::getTheme('bootstrap5')->cssAttribute('front','name');
		$this->assertSame('style.css', $result);
	}
/*
			public function testUpgradeThemeCode()
			{

			}

			public function testGetThemeList()
			{

			}
	*/
	public function testGetScope()
	{

		$tests = array(
			0 => array(
				'theme'    => 'front',
				'type'     => 'library',
				'scope'    => 'front',
				'expected' => array(
					'bootstrap'   =>
						array(
							'name'    => 'bootstrap',
							'version' => '3',
						),
					'fontawesome' =>
						array(
							'name'    => 'fontawesome',
							'version' => '6',
						),
				)
			),
			1 => array(
				'theme'    => 'front',
				'type'     => 'library',
				'scope'    => 'all',
				'expected' => array(
					'bootstrap'          =>
						array(
							'name'    => 'bootstrap',
							'version' => '3',
						),
					'fontawesome'        =>
						array(
							'name'    => 'fontawesome',
							'version' => '6',
						),
					'bootstrap.editable' =>
						array(
							'name'    => 'bootstrap.editable',
				//			'version' => '',
						),
				)
			),
			2 => array(
				'theme'    => 'front',
				'type'     => 'library',
				'scope'    => 'admin',
				'expected' => array(
					'bootstrap'          =>
						array(
							'name'    => 'bootstrap',
							'version' => '3',
						),
					'fontawesome'        =>
						array(
							'name'    => 'fontawesome',
							'version' => '6',
						),
					'bootstrap.editable' =>
						array(
							'name'    => 'bootstrap.editable',
						//	'version' => '',
						),
				)
			),
			3 => array(
				'theme'    => '_blank',
				'type'     => 'library',
				'scope'    => 'front',
				'expected' => array(
					'bootstrap'   =>
						array(
							'name'    => 'bootstrap',
							'version' => '3',
						),
					'fontawesome' =>
						array(
							'name'    => 'fontawesome',
							'version' => '4',
						),
				)
			),
			4 => array(
				'theme'    => 'bootstrap3',
				'type'     => 'css',
				'scope'    => 'front',
				'expected' => array(
					'style.css' =>
						array(
							'name'        => 'style.css',
							'info'        => 'Default',
							'nonadmin'    => true,
							'default'     => false,
							'exclude'     => '',
							'description' => '',
							'thumbnail'   => '',
						),
					'*'         => array(
						'name'        => '*',
						'info'        => '*',
						'nonadmin'    => true,
						'default'     => false,
						'exclude'     => '',
						'description' => '',
						'thumbnail'   => '',
					),
				),
			),
			5 => array(
				'theme'    => 'bootstrap3',
				'type'     => 'css',
				'scope'    => 'admin',
				'expected' => array(
					'css/modern-light.css'                                                         =>
						array(
							'name'        => 'css/modern-light.css',
							'info'        => 'Modern Light',
							'nonadmin'    => false,
							'default'     => false,
							'exclude'     => 'bootstrap',
							'description' => 'A high-contrast light skin',
							'thumbnail'   => 'images/admin_modern-light.webp',
						),
					'css/modern-dark.css'                                                          =>
						array(
							'name'        => 'css/modern-dark.css',
							'info'        => 'Modern Dark',
							'nonadmin'    => false,
							'default'     => false,
							'exclude'     => 'bootstrap',
							'description' => 'A high-contrast dark skin',
							'thumbnail'   => 'images/admin_modern-dark.webp',
						),
					'css/bootstrap-dark.min.css'                                                   =>
						array(
							'name'        => 'css/bootstrap-dark.min.css',
							'info'        => 'Legacy Dark Admin',
							'nonadmin'    => false,
							'default'     => false,
							'exclude'     => 'bootstrap',
							'description' => 'A dark admin area skin',
							'thumbnail'   => 'images/admin_bootstrap-dark.webp',
						),
					'css/kadmin.css'                                                               =>
						array(
							'name'        => 'css/kadmin.css',
							'info'        => 'K-Admin Inspired',
							'nonadmin'    => false,
							'default'     => false,
							'exclude'     => '',
							'description' => 'A light admin area skin',
							'thumbnail'   => 'images/admin_kadmin.webp',
						),
					'css/corporate.css'                                                            =>
						array(
							'name'        => 'css/corporate.css',
							'info'        => 'Corporate',
							'nonadmin'    => false,
							'default'     => false,
							'exclude'     => 'bootstrap',
							'description' => '',
							'thumbnail'   => 'images/admin_corporate.webp',
						),
					'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/flatly/bootstrap.min.css'    =>
						array(
							'name'        => 'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/flatly/bootstrap.min.css',
							'info'        => 'Flatly',
							'nonadmin'    => false,
							'default'     => false,
							'exclude'     => 'bootstrap',
							'description' => '',
							'thumbnail'   => 'images/admin_flatly.webp',
						),
					'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/sandstone/bootstrap.min.css' =>
						array(
							'name'        => 'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/sandstone/bootstrap.min.css',
							'info'        => 'Sandstone',
							'nonadmin'    => false,
							'default'     => false,
							'exclude'     => 'bootstrap',
							'description' => '',
							'thumbnail'   => 'images/admin_sandstone.webp',
						),
					'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/superhero/bootstrap.min.css' =>
						array(
							'name'        => 'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/superhero/bootstrap.min.css',
							'info'        => 'Superhero',
							'nonadmin'    => false,
							'default'     => false,
							'exclude'     => 'bootstrap',
							'description' => '',
							'thumbnail'   => 'images/admin_superhero.webp',
						),
				),
			),

		);

		foreach($tests as $index => $var)
		{
			$result = e107::getTheme($var['theme'])->getScope($var['type'], $var['scope']);
			if(empty($var['expected']))
			{
				var_export($result);
				continue;
			}
			$this->assertSame($var['expected'], $result, 'Test #' . $index . ' failed.');
		}


	}


	public function testGetThemeFiles()
	{

		$expected = array(
			0 =>
				array(
					'js'  =>
						array(
							0 => '{e_WEB}lib/bootstrap/3/js/bootstrap.min.js',
					),
					'css' =>
						array(
							0 => '{e_WEB}lib/bootstrap/3/css/bootstrap.min.css',
						),

				),
			1 =>
				array(
					'css' =>
						array(
							0 => '{e_WEB}lib/font-awesome/6/css/all.min.css',
							1 => '{e_WEB}lib/font-awesome/6/css/v4-shims.min.css',
						),

				),
		);

		/** Expecting bootstrap 3 files fontawesome 5 files  */
		$result = e107::getTheme('bootstrap3')->getThemeFiles('library', 'front');
		$this->assertSame($expected, $result);


		$expected = array(
			0 =>
				array(
				'js'  =>
						array(
							0 => '{e_WEB}lib/bootstrap/3/js/bootstrap.min.js',
						),
					'css' =>
						array(
							0 => '{e_WEB}lib/bootstrap/3/css/bootstrap.min.css',
						),

				),
			1 =>
				array(
					'css' =>
						array(
							0 => '{e_WEB}lib/font-awesome/6/css/all.min.css',
							1 => '{e_WEB}lib/font-awesome/6/css/v4-shims.min.css',
						),

				),
		);

		$result = e107::getTheme('bootstrap3')->getThemeFiles('library', 'wysiwyg');
		$this->assertSame($expected, $result);

		$expected = array(
			'css' =>
				array(),
		);

		$result = e107::getTheme('bootstrap3')->getThemeFiles('css', 'wysiwyg');
		$this->assertSame($expected, $result);

		//	$result = e107::getTheme('bootstrap5')->getThemeFiles('css', 'wysiwyg');

			/** Expecting bootstrap 5 files fontawesome 5 (js only)  */

		$expected = array (
		  0 =>
		  array (
		    'js' =>
		    array (
		      0 => '{e_WEB}lib/bootstrap/5/js/bootstrap.bundle.min.js',
		    ),
		    'css' =>
		    array (
		      0 => '{e_WEB}lib/bootstrap/5/css/bootstrap.min.css',
		    ),

		  ),
		  1 =>
		  array (
		    'js' =>
		    array (
		      0 => '{e_WEB}lib/font-awesome/5/js/all.min.js',
		      1 => '{e_WEB}lib/font-awesome/5/js/v4-shims.min.js',
		    ),
		  ),
		  2 =>
		  array (
		    'css' =>
		    array (
		      0 => '{e_WEB}lib/animate.css/animate.min.css',
		    ),
		  ),
		);


		$result = e107::getTheme('bootstrap5')->getThemeFiles('library', 'front');
		$this->assertSame($expected, $result);

	}


	public function testLoadLibrary()
	{

		$tests = array(
			0 => array(
				'theme'    => 'front',
				'scope'    => 'front',
				'expected' => ['bootstrap', 'fontawesome6']
			),
			1 => array(
				'theme'    => 'front',
				'scope'    => 'admin',
				'expected' => ['bootstrap', 'fontawesome6', 'bootstrap.editable']
			),
			2 => array(
				'theme'    => '_blank',
				'scope'    => 'front',
				'expected' => ['bootstrap', 'fontawesome']
			),
			3 => array(
				'theme'    => 'bootstrap5',
				'scope'    => 'front',
				'expected' => ['bootstrap5', 'fontawesome5', 'animate.css']
			),

		);

		foreach($tests as $index => $var)
		{
			$loaded = e107::getTheme($var['theme'])->loadLibrary($var['scope']);
			$this->assertSame($var['expected'], $loaded, 'Test #' . $index . ' failed.');
		}

		//	var_export($loaded);


	}

	public function testGet()
	{

		$tests = array(
			0 => array(
				'theme'    => 'front',
				'type'     => 'library',
				'expected' => array(
					0 =>
						array(
							'name'    => 'bootstrap',
							'version' => '3',
							'scope'   => 'front,admin,wysiwyg',
						),
					1 =>
						array(
							'name'    => 'fontawesome',
							'version' => '6',
							'scope'   => 'front,admin,wysiwyg',
						),
					2 =>
						array(
							'name'    => 'bootstrap.editable',
					//		'version' => '',
							'scope'   => 'admin',
						),
				)
			),
			1 => array(
				'theme'    => 'bootstrap3',
				'type'     => 'library',
				'expected' => array(
					0 =>
						array(
							'name'    => 'bootstrap',
							'version' => '3',
							'scope'   => 'front,admin,wysiwyg',
						),
					1 =>
						array(
							'name'    => 'fontawesome',
							'version' => '6',
							'scope'   => 'front,admin,wysiwyg',
						),
					2 =>
						array(
							'name'    => 'bootstrap.editable',
						//	'version' => '',
							'scope'   => 'admin',
						),
				)
			),
			2 => array(
				'theme'    => '_blank',
				'type'     => 'library',
				'expected' => array(
					0 =>
						array(
							'name'    => 'bootstrap',
							'version' => '3',
							'scope'   => 'front',
						),
					1 =>
						array(
							'name'    => 'fontawesome',
							'version' => '4',
							'scope'   => 'front',
						),
				)
			),

		);


		foreach($tests as $index => $var)
		{
			$result = e107::getTheme($var['theme'])->get($var['type']);
			$this->assertSame($var['expected'], $result, 'Test #' . $index . ' failed');
		}


	}

	/*
			public function testParse_theme_php()
			{

			}
*/
	public function testGetThemeInfo()
	{
		$themeObj = $this->tm;
		$data = $themeObj::getThemeInfo('bootstrap3');
		$result= !empty($data['multipleStylesheets']);
		$this->assertTrue($result);

	}

	public function testGetThemeLayout()
	{

		$pref = array(
			'jumbotron_home'          =>
				array(
					0 => 'FRONTPAGE',
					1 => 'page.php?3!',
					2 => '/my-sef-url!',
					3 => '/news/?page=',
				),
			'jumbotron_full'          =>
				array(
					0 => 'forum',
					1 => 'user.php!', // <-- exact match of URL
					2 => ':forum/index',
					3 => ':myplugin/',

					//			2 => '/user', // <-- Expecting URL to match both user and usersetting since it contains no "!"
				),
			'jumbotron_sidebar_right' =>
				array(
					0 => '/news',
					1 => '/user/',
					2 => 'user.php?id',
					//		1 => '/usersettings.php'
				),
			'other_layout'            =>
				array(
					0 => 'myplugin.php$', // <-- $ = script name match
					1 => 'forum/index.php',
					2 => 'page.php$', // <-- $ = script name match
					3 => '/user/settings?',
					4 => 'script.php$',
					5 => '/news/?bla',
					6 => ':news/view/index',
				),
			'script_match'            =>
				array(
					0 => 'myplugin/index.php$', // <-- $ = script name match

				),
		);

		$defaultLayout = "jumbotron_sidebar_right";


		$tests = array(
			0  => array('url' => SITEURL . "index.php", 'expected' => 'jumbotron_home'),
			1  => array('url' => SITEURL . "index.php?", 'expected' => 'jumbotron_home'),
			2  => array('url' => SITEURL . "index.php?fbclid=asdlkjasdlakjsdasd", 'expected' => 'jumbotron_home'),
			3  => array('url' => SITEURL . "index.php?utm_source=asdd&utm_medium=asdsd", 'expected' => 'jumbotron_home'),
			4  => array('url' => SITEURL . "news", 'expected' => 'jumbotron_sidebar_right'),
			5  => array('url' => SITEURL . "forum", 'script' => "/forum/index.php", 'expected' => 'jumbotron_full'),
			6  => array('url' => SITEURL . "other/page", 'script' => '/page.php', 'expected' => 'other_layout'),
			7  => array('url' => SITEURL . "news.php?5.3", 'script' => '/news.php', 'expected' => 'jumbotron_sidebar_right'),
			8  => array('url' => SITEURL . "usersettings.php", 'script' => '/usersettings.php', 'expected' => 'jumbotron_sidebar_right'),
			9  => array('url' => SITEURL . "user.php", 'script' => '/user.php', 'expected' => 'jumbotron_full'),
			10 => array('url' => SITEURL . "page.php", 'script' => '/page.php', 'expected' => 'other_layout'),
			11 => array('url' => SITEURL . "page.php?3", 'script' => '/page.php', 'expected' => 'jumbotron_home'),
			12 => array('url' => SITEURL . "somepage/", 'script' => "/script.php", 'expected' => 'other_layout'),
			13 => array('url' => SITEURL . "plugin/", 'script' => "/myplugin.php", 'expected' => 'other_layout'),
			14 => array('url' => SITEURL . "forum/index.php", 'script' => "/index.php", 'expected' => 'other_layout'),
			15 => array('url' => SITEURL . "my-chapter/my-title", 'script' => "/page.php", 'expected' => 'other_layout'),
			16 => array('url' => SITEURL . "my-sef-url", 'script' => '/index.php', 'expected' => 'jumbotron_home'),
			17 => array('url' => SITEURL . "user/settings?id=1", 'script' => '/usersettings.php', 'expected' => 'other_layout'),
			18 => array('url' => SITEURL . "user/Tijn", 'script' => '/user.php', 'expected' => 'jumbotron_sidebar_right'),
			19 => array('url' => SITEURL . "user.php?id.1", 'script' => '/user.php', 'expected' => 'jumbotron_sidebar_right'),
			20 => array('url' => SITEURL . "pluginpage/", 'script' => '/myplugin/index.php', 'expected' => 'script_match'),
			21 => array('url' => SITEURL . "news/?page=", 'script' => '/news.php', 'expected' => 'jumbotron_home'),
			22 => array('url' => SITEURL . "news/my-news-title", 'script' => '/news.php', 'expected' => 'jumbotron_sidebar_right'),
			23 => array('url' => SITEURL . "news/?bla", 'script' => '/news.php', 'expected' => 'other_layout'),

			// Using e_ROUTE;
			24 => array('url' => 'whatever.php', 'script' => 'whatever.php', 'route' => 'news/view/index', 'expected' => 'other_layout'),
			25 => array('url' => 'whatever.php', 'script' => 'whatever.php', 'route' => 'forum/index', 'expected' => 'jumbotron_full'),
			26 => array('url' => 'whatever.php', 'script' => 'whatever.php', 'route' => 'myplugin/index', 'expected' => 'jumbotron_full'),

		);

		$themeObj = $this->tm;

		foreach($tests as $item => $var)
		{
			$var['script'] = isset($var['script']) ? $var['script'] : null;

			$result = $themeObj::getThemeLayout($pref, $defaultLayout, $var);
			$diz = isset($var['route']) ? $var['route'] : $var['url'];
			$this->assertEquals($var['expected'], $result, "Wrong theme layout returned for item [" . $item . "] " . $diz);
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
	*/

	public function testParse_theme_xml()
	{
		$tests = array(
			'bootstrap3'    => array(
				'library'   => array (
				    0 =>
				    array (
				      'name' => 'bootstrap',
				      'version' => '3',
				      'scope' => 'front,admin,wysiwyg',
				    ),
				    1 =>
				    array (
				      'name' => 'fontawesome',
				      'version' => '6',
				      'scope' => 'front,admin,wysiwyg',
				    ),
				    2 =>
				    array (
				      'name' => 'bootstrap.editable',
				//      'version' => '',
				      'scope' => 'admin',
				    ),
				  )

			),
			'bootstrap5'  => array(
				'library'   => array (
					  0 =>
					  array (
					    'name' => 'bootstrap',
					    'version' => '5',
					    'scope' => 'front',
					  ),
					  1 =>
					  array (
					    'name' => 'fontawesome',
					    'version' => '5',
					    'scope' => 'front',
					    'files'  => 'js',
					  ),
					  2 =>
					  array (
					    'name' => 'fontawesome',
					    'version' => '5',
					    'scope' => 'wysiwyg',
					    'files'  => 'css',
					  ),
					  3 => array (
			            'name' => 'animate.css',
			    //        'version' => '',
			            'scope' => 'front',
			         )
					),


			),
			'voux'  => array( // theme using defines for FONTAWESOME and BOOTSTRAP
				'library'   => array (
					  0 =>
					  array (
					    'name' => 'bootstrap',
					    'version' => '3',
					    'scope' => 'front,wysiwyg',
					  ),
					  1 =>
					  array (
					    'name' => 'fontawesome',
					    'version' => '4',
					    'scope' => 'front,wysiwyg',
					  ),
					)
			),
		);

		foreach($tests as $theme => $var)
		{
			$result = e_theme::parse_theme_xml($theme);
			foreach($var as $att => $value)
			{
				if(empty($value))
				{
					var_export($result[$att]);
					continue;
				}

				$this->assertSame($result[$att], $value);
			}

		}



	}

	public function testGetLegacyBSFA()
	{
		$result = e_theme::getLegacyBSFA('voux');

		$expected = array (
		  0 =>
		  array (
		    'name' => 'bootstrap',
		    'version' => '3',
		    'scope' => 'front,wysiwyg',
		  ),
		  1 =>
		  array (
		    'name' => 'fontawesome',
		    'version' => '4',
		    'scope' => 'front,wysiwyg',
		  ),
		);

		$this->assertSame($expected, $result);

		// $result = e_theme::getLegacyBSFA('basic-light');
		// var_dump($result);
	}



	//	public function testLoadLayout()
	//	{
	// $res = e_theme::loadLayout('full', 'bootstrap4');

	//	}
	/*
			public function testGetThemesMigrations()
			{
				$thm = e107::getSingleton('themeHandler');

				$tests = array(null, 'id', 'xml');

				foreach($tests as $mode)
				{
					$old = $thm->getThemes($mode);

					$this->tm->__construct(['force'=>true]);
					$new = $this->tm->getThemes($mode);

					$this->assertSame($old,$new);
				}


			}
	*/

	/*
			public function testThemeInfoMigration()
			{
				$thm = e107::getSingleton('themeHandler');

				$name = 'bootstrap3';

				$this->tm->__construct(['themedir'=>$name, 'force'=>true]);
				$old = $thm->getThemeInfo($name);

				$new = $this->tm->get();

				$this->assertNotEmpty($new, "New parsing of ".$name." returned null");
				$this->assertNotEmpty($old, "Old parsing of ".$name." returned null");

			//	unset($new['id']); // introduced.

				$this->assertSame($old, $new);

			}
	*/

}
