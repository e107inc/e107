<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2019 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


class e_jsmanagerTest extends \Codeception\Test\Unit
{

	/** @var e_jsmanager */
	protected $js;

	protected function _before()
	{

		try
		{
			$this->js = $this->make('e_jsmanager');
		}
		catch(Exception $e)
		{
			$this->assertTrue(false, "Couldn't load e_jsmanager object");
		}

	}

	/*
			public function testHeaderPlugin()
			{

			}

			public function testTryHeaderInline()
			{

			}
	*/
	public function testIsInAdmin()
	{
		$result = $this->js->isInAdmin();
		$this->assertFalse($result);

	}

	/*
			public function testRequireCoreLib()
			{

			}

			public function testSetInAdmin()
			{

			}

			public function testCoreCSS()
			{

			}

			public function testResetDependency()
			{

			}

			public function testJsSettings()
			{

			}

			public function testGetInstance()
			{

			}

			public function testFooterFile()
			{

			}

			public function testSetData()
			{

			}

			public function testLibraryCSS()
			{

			}

			public function testTryHeaderFile()
			{

			}

			public function testThemeCSS()
			{

			}

			public function testOtherCSS()
			{

			}

			public function testSetLastModfied()
			{

			}

			public function testRenderLinks()
			{

			}

			public function testThemeLib()
			{

			}

			public function testRenderFile()
			{

			}

			public function testHeaderCore()
			{

			}

			public function testRenderInline()
			{

			}

			public function testFooterTheme()
			{

			}

			public function testGetData()
			{

			}

			public function testRequirePluginLib()
			{

			}

			public function testGetCacheId()
			{

			}

			public function testHeaderTheme()
			{

			}

			public function testInlineCSS()
			{

			}
	*/
	public function testHeaderFile()
	{
		$load = array(
			0 => array(
				'file' => '{e_PLUGIN}forum/js/forum.js',
				'zone' => 5,
				'opts' => []
			),
			1 => array(
				'file' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js',
				'zone' => 1,
				'opts' => []
			),
			2 => array(
				'file' => '{e_WEB}js/bootstrap-notify/js/bootstrap-notify.js',
				'zone' => 2,
				'opts' => []

			),
			3 => array(
				'file' => 'https://somewhere/something.min.js',
				'zone' => 3,
				'opts' => array('defer' => true)
			),
			4 => array(
				'file' => 'https://somewhere/async.js',
				'zone' => 4,
				'opts' => array('defer', 'async')
			),


		);

		foreach($load as $t)
		{
			$this->js->headerFile($t['file'], $t['zone'], null, null, $t['opts']);
		}

		// Test loaded files.

		$result = $this->js->renderJs('header', 1, true, true);
		$this->assertStringContainsString('<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>', $result);
		$this->assertStringContainsString('zone #1', $result);

		$result = $this->js->renderJs('header', 3, true, true);
		$this->assertStringContainsString('<script src="https://somewhere/something.min.js" defer></script>', $result);
		$this->assertStringContainsString('zone #3', $result);

		$result = $this->js->renderJs('header', 4, true, true);
		$this->assertStringContainsString('<script src="https://somewhere/async.js" defer async></script>', $result);
		$this->assertStringContainsString('zone #4', $result);

	}

	public function testFooterFile()
	{
		$load = array(
			0 => array(
				'file' => '{e_PLUGIN}forum/js/forum.js',
				'zone' => 5,
				'opts' => []
			),
			1 => array(
				'file' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js',
				'zone' => 1,
				'opts' => []
			),
			2 => array(
				'file' => '{e_WEB}js/bootstrap-notify/js/bootstrap-notify.js',
				'zone' => 2,
				'opts' => []

			),
			3 => array(
				'file' => 'https://somewhere/something.min.js',
				'zone' => 3,
				'opts' => array('defer' => true)
			),

			4 => array(
				'file' => 'https://somewhere/async.js',
				'zone' => 4,
				'opts' => array('defer', 'async')
			),


		);

		foreach($load as $t)
		{
			$this->js->footerFile($t['file'], $t['zone'], null, null, $t['opts']);
		}

		// Test loaded files.

		$result = $this->js->renderJs('footer', 1, true, true);
		$this->assertStringContainsString('<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>', $result);
		$this->assertStringContainsString('priority #1', $result);

		$result = $this->js->renderJs('footer', 3, true, true);
		$this->assertStringContainsString('<script src="https://somewhere/something.min.js" defer></script>', $result);
		$this->assertStringContainsString('priority #3', $result);

		$result = $this->js->renderJs('footer', 4, true, true);
		$this->assertStringContainsString('<script src="https://somewhere/async.js" defer async></script>', $result);
		$this->assertStringContainsString('priority #4', $result);

	}

	/*
			public function testSetDependency()
			{

			}

			public function testHeaderInline()
			{

			}

			public function testGetLastModfied()
			{

			}

			public function testSetCacheId()
			{

			}

			public function testGetCurrentTheme()
			{

			}

			public function testPluginCSS()
			{

			}

			public function testCheckLibDependence()
			{

			}

			public function testRenderCached()
			{

			}

			public function testGetCurrentLocation()
			{

			}

			public function testFooterInline()
			{

			}

			public function testAddLibPref()
			{

			}
	*/
	public function testAddLink()
	{
		$tests = array(
			0 => array(
				'expected' => '<link rel="preload" href="https://fonts.googleapis.com/css?family=Nunito&display=swap" as="style" onload="this.onload=null;" />',
				'input'    => array('rel' => 'preload', 'href' => 'https://fonts.googleapis.com/css?family=Nunito&display=swap', 'as' => 'style', 'onload' => "this.onload=null;"),
				'cacheid'  => false,
			),
			1 => array(
				'expected' => '<link rel="preload" href="' . e_THEME_ABS . 'bootstrap3/assets/fonts/fontawesome-webfont.woff2?v=4.7.0" as="font" type="font/woff2" crossorigin />', // partial
				'input'    => 'rel="preload" href="{THEME}assets/fonts/fontawesome-webfont.woff2?v=4.7.0" as="font" type="font/woff2" crossorigin',
				'cacheid'  => false,
			),
			2 => array(
				'expected' => '<link rel="preload" href="' . e_WEB_ABS . 'script.js?0" as="script" />',
				'input'    => array('rel' => 'preload', 'href' => '{e_WEB}script.js', 'as' => 'script'),
				'cacheid'  => true,
			),

			/* Static URLs enabled from this point. */

			3 => array(
				'expected' => '<link rel="preload" href="https://static.mydomain.com/e107_web/script.js?0" as="script" />',
				'input'    => array('rel' => 'preload', 'href' => '{e_WEB}script.js', 'as' => 'script'),
				'cacheid'  => true,
				'static'   => true,
			),
		);

		$tp = e107::getParser();

		foreach($tests as $var)
		{
			$static = !empty($var['static']) ? 'https://static.mydomain.com/' : null;
			$tp->setStaticUrl($static);

			$this->js->addLink($var['input'], $var['cacheid']);
			//	$this->assertSame($var['expected'],$actual);
		}

		$actual = $this->js->renderLinks(true);

		foreach($tests as $var)
		{
			$result = (strpos((string) $actual, $var['expected']) !== false);
			$this->assertTrue($result, $var['expected'] . " was not found in the rendered links. Render links result:" . $actual . "\n\n");
		}

		// -----------------
		$static = [
			'https://static.mydomain.com/',
			'https://static2.mydomain.com/',
			'https://static3.mydomain.com/',
		];

		$tp->setStaticUrl(null);
		e107::getParser()->setStaticUrl($static);

		$staticTests = [
			0 => array(
				'expected' => '<link rel="preload" href="https://static.mydomain.com/e107_web/script.js?0" as="script" />',
				'input'    => array('rel' => 'preload', 'href' => '{e_WEB}script.js', 'as' => 'script'),
				'cacheid'  => true,
				'static'   => true,
			),
			1 => array(
				'expected' => '<link rel="preload" as="image" type="image/jpeg" href="https://static.mydomain.com/e107_themes/bootstrap3/image/header.jpg" media="(max-width: 415px)" />',
				'input'    => ['rel'=>'preload', 'as'=>'image', 'type'=> "image/jpeg", 'href'=>THEME_ABS.'image/header.jpg', 'media'=>"(max-width: 415px)"],
				'cacheid'  => false,
				'static'   => true,
			),
			2 => array(
				'expected' => '<link rel="preload" as="image" type="image/jpeg" href="https://static.mydomain.com/e107_themes/bootstrap3/image/header.jpg" media="(max-width: 415px)" />',
				'input'    => ['rel'=>'preload', 'as'=>'image', 'type'=> "image/jpeg", 'href'=>THEME_ABS.'image/header.jpg', 'media'=>"(max-width: 415px)"],
				'cacheid'  => false,
				'static'   => true,
			),
			3 => array(
				'expected' => '<link rel="preload" as="image" type="image/jpeg" href="https://static.mydomain.com/e107_themes/bootstrap3/image/header.jpg" media="(max-width: 415px)" />',
				'input'    => ['rel'=>'preload', 'as'=>'image', 'type'=> "image/jpeg", 'href'=>THEME_ABS.'image/header.jpg', 'media'=>"(max-width: 415px)"],
				'cacheid'  => false,
				'static'   => true,
			),

		];



		foreach($staticTests as $var)
		{
			$this->js->addLink($var['input'], $var['cacheid']);
		}

		$actual = $this->js->renderLinks(true);


		foreach($staticTests as $var)
		{
			$result = (strpos((string) $actual, $var['expected']) !== false);
			self::assertTrue($result, $var['expected'] . " was not found in the rendered links. Render links result:" . $actual . "\n\n");
		}

		$tp->setStaticUrl(null);
		e107::getParser()->setStaticUrl(null);

	}
	/*
			public function testLibDisabled()
			{

			}

			public function testArrayMergeDeepArray()
			{

			}

			public function testRenderJs()
			{

			}

			public function testRemoveLibPref()
			{

			}
	*/


	function testRenderFavicon()
	{
		$file = e_PLUGIN."gsitemap/images/icon.png";
		$result = $this->js->renderFavicon($file);
		self::assertNotEmpty($result);


	}

}
