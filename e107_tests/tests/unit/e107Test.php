<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2018 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


class e107Test extends \Codeception\Test\Unit
{

	/** @var e107 */
	private $e107;

	protected function _before()
	{
		try
		{
			$this->e107 = e107::getInstance();
		}
		catch (Exception $e)
		{
			$this->fail("Couldn't load e107 object");
		}

		// var_dump($this->e107);
	}

	public function testGetInstance()
	{
		//	$this->e107->getInstance();
		//$res = $this->e107::getInstance();
		//	$this->assertTrue($res);
	}

	public function testInitCore()
	{
		//$res = null;
		include_once(APP_PATH.'/e107_config.php'); // contains $E107_CONFIG = array('site_path' => '000000test');

		$e107_paths = @compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'DOWNLOADS_DIRECTORY','UPLOADS_DIRECTORY','SYSTEM_DIRECTORY', 'MEDIA_DIRECTORY','CACHE_DIRECTORY','LOGS_DIRECTORY', 'CORE_DIRECTORY', 'WEB_DIRECTORY');
		$sql_info = @compact('mySQLserver', 'mySQLuser', 'mySQLpassword', 'mySQLdefaultdb', 'mySQLprefix', 'mySQLport');
		$res = $this->e107->initCore($e107_paths, e_ROOT, $sql_info, varset($E107_CONFIG, array()));

		$this->assertEquals('000000test', $res->site_path);

	}

	/*
			public function testInitInstall()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testMakeSiteHash()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testSetDirs()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testPrepareDirs()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testDefaultDirs()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testInitInstallSql()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetRegistry()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testSetRegistry()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetFolder()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetE107()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testIsCli()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetMySQLConfig()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetSitePath()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetHandlerPath()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testAddHandler()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testIsHandler()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetHandlerOverload()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testSetHandlerOverload()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testIsHandlerOverloadable()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetSingleton()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetObject()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetConfig()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetPref()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testFindPref()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetPlugConfig()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetPlugLan()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetPlugPref()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testFindPlugPref()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetThemeConfig()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetThemePref()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testSetThemePref()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetThemeGlyphs()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetParser()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetScParser()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetSecureImg()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetScBatch()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetDb()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetCache()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetBB()
			{
				$res = null;
				$this->assertTrue($res);
			}*/


	public function testGetThumb()
	{
		$tmp = e107::getThumb(e_PLUGIN."gallery/images/horse.jpg");

		$className = get_class($tmp);

		$this->assertEquals('GdThumb', $className);

	}


	public function testGetUserSession()
	{
		$tmp = e107::getUserSession();

		$className = get_class($tmp);

		$res = ($className === 'UserHandler');

		$this->assertTrue($res);

	}
	/*
			public function testGetSession()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetRedirect()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetRate()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetSitelinks()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetRender()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetEmail()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetBulkEmail()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetEvent()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetArrayStorage()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetMenu()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetTheme()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetUrl()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetFile()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetForm()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetAdminLog()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetLog()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetDateConvert()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetDate()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetDebug()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetNotify()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetOverride()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetLanguage()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetIPHandler()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetXml()
			{
				$res = null;
				$this->assertTrue($res);
			}
			*/

			public function testGetHybridAuth()
			{
				$object = e107::getHybridAuth();
				$this->assertInstanceOf(Hybridauth\Hybridauth::class, $object);
			}

			/*
			public function testGetUserClass()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetSystemUser()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testUser()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testSerialize()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testUnserialize()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetUser()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetModel()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetUserStructure()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetUserExt()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetUserPerms()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetRank()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetPlugin()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetPlug()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetOnline()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetChart()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetComment()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetCustomFields()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetMedia()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetNav()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetMessage()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetAjax()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetLibrary()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testLibrary()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetJs()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testSet()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testJs()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testLink()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testCss()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testDebug()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetJshelper()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testMeta()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetAdminUI()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetAddon()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetAddonConfig()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testCallMethod()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetUrlConfig()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetThemeInfo()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testCoreTemplatePath()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testTemplatePath()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetCoreTemplate()
			{
				$res = null;
				$this->assertTrue($res);
			}
	*/
	/**
	 * This test checks getTemplate() use on loading between the core download plugin template and the _blank theme download template
	 */
	public function testGetTemplate()
	{
		e107::getConfig()->set('sitetheme', '_blank');

		$template = e107::getTemplate('download', null, null); // theme override is enabled by default.
		$this->assertEquals('{DOWNLOAD_BREADCRUMB} Custom', $template['header']); // ie. should be from _blank theme download template (override of plugin).
		$footer = empty($template['footer']); // theme overrides everything, since merge is not enabled. theme does not contain 'footer'.
		$this->assertTrue($footer);

		$template = e107::getTemplate('download', null, null, false); // theme override is disabled.
		$this->assertEquals("{DOWNLOAD_BREADCRUMB}", $template['header']); // ie. should be from plugin template, not theme.
		$this->assertEquals('', $template['footer']); // main plugin template is active, since override is false. 'footer' is set.

		$template = e107::getTemplate('download', null, null, true, true); // theme override is enabled, and theme merge is enabled.
		$this->assertEquals("{DOWNLOAD_BREADCRUMB} Custom", $template['header']); //from theme
		$this->assertEquals("", $template['footer']); // 'footer' missing from theme, so plugin template used. ie. arrays have been merged.

		$template = e107::getTemplate('download', null, null, false, true); // theme override is disabled, theme merge is enabled.
		$this->assertEquals("{DOWNLOAD_BREADCRUMB}", $template['header']); // ie. should be from plugin template, not theme.
		//	$this->assertEquals("test", $template['other']); // 'test' is missing from plugin template, but merge is enabled. Not an override of plugin template key so merge is okay.
		// FIXME above..
		//	var_dump($template['other']);

		e107::getConfig()->set('sitetheme', 'bootstrap3');


	}
	/*
			public function testTemplateWrapper()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testScStyle()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetTemplateInfo()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetLayouts()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function test_getTemplate()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testIncludeLan()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testCoreLan()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testPlugLan()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testThemeLan()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testLan()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testPref()
			{
				$res = null;
				$this->assertTrue($res);
			}
	*/
	public function testUrl()
	{
		$obj = $this->e107;

		$result = $obj::url('news','index', array(), array('mode'=>'full'));

		$this->assertEquals("https://localhost/e107/news", $result);
	}

	/**
	 * @see https://github.com/e107inc/e107/issues/4054
	 */
	public function testUrlOptionQueryHasCompliantAmpersand()
	{
		$e107 = $this->e107;
		$e107::getPlugin()->install('forum');
		$url = $e107::url('forum', 'topic', [], array(
			'query' => array(
				'f' => 'post',
				'id' => 123
			),
		));
		$this->assertEquals(
			e_PLUGIN_ABS . 'forum/forum_viewtopic.php?f=post&amp;id=123',
			$url, "Generated href does not match expectation"
		);
	}

	public function testUrlOptionQueryUrlEncoded()
	{
		$e107 = $this->e107;
		$e107::getPlugin()->install('forum');
		$url = $e107::url('forum', 'post', [], array(
			'query' => array(
				"didn't" => '<tag attr="such wow"></tag>',
				'did' => 'much doge',
			),
		));
		$this->assertEquals(
			e_HTTP .
			'forum/post/?didn%27t=%3Ctag%20attr%3D%22such%20wow%22%3E%3C/tag%3E&amp;did=much%20doge',
			$url, "Generated href query string did not have expected URL encoding"
		);
	}

	public function testUrlEscapesHtmlSpecialChars()
	{
		$e107 = $this->e107;
		$e107::getPlugin()->install('forum');
		$url = $e107::url('forum', 'forum', [
			'forum_sef' => '<>',
		], array(
			'fragment' => 'Arts & Crafts <tag attr="can\'t inject here"></tag>'
		));
		$this->assertEquals(
			e_HTTP .
			'forum/&lt;&gt;/#Arts &amp; Crafts &lt;tag attr=&quot;can&#039;t inject here&quot;&gt;&lt;/tag&gt;',
			$url, "Generated href did not prevent HTML tag injection as expected"
		);
	}
	/*
			public function testRedirect()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetError()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testHttpBuildQuery()
			{
				$res = null;
				$this->assertTrue($res);
			}
*/
			public function testMinify()
			{
				$text = "something ; other or ; else";
				$expected = "something;other or;else";

				$result = e107::minify($text);

				$this->assertEquals($expected,$result);

			}
/*
			public function testWysiwyg()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testLoadLanFiles()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testPrepare_request()
			{
				$res = null;
				$this->assertTrue($res);
			}
	*/

	public function testBase64DecodeOnAjaxURL()
	{
		$query = "mode=main&iframe=1&action=info&src=aWQ9ODgzJnVybD1odHRwcyUzQSUyRiUyRmUxMDcub3JnJTJGZTEwN19wbHVnaW5zJTJGYWRkb25zJTJGYWRkb25zLnBocCUzRmlkJTNEODgzJTI2YW1wJTNCbW9kYWwlM0QxJm1vZGU9YWRkb24mcHJpY2U9";

		$result = base64_decode($query, true);

		$this->assertFalse($result); // correct result is 'false'.
	}


	public function testFilter_request()
	{

		//	define('e_DEBUG', true);
		//	$_SERVER['QUEST_STRING'] = "mode=main&iframe=1&action=info&src=aWQ9ODgzJnVybD1odHRwcyUzQSUyRiUyRmUxMDcub3JnJTJGZTEwN19wbHVnaW5zJTJGYWRkb25zJTJGYWRkb25zLnBocCUzRmlkJTNEODgzJTI2YW1wJTNCbW9kYWwlM0QxJm1vZGU9YWRkb24mcHJpY2U9";

		//$result = $this->e107::filter_request($test,'QUERY_STRING','_SERVER');

		//	$this->e107->prepare_request();

		//	var_dump($_SERVER['QUEST_STRING']);


		// 	$res = null;
		// $this->assertTrue($res);
	}
	/*
			public function testSet_base_path()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testSet_constants()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGet_override_rel()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGet_override_http()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testSet_paths()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testFix_windows_paths()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testSet_urls()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testSet_urls_deferred()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testSet_request()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testCanCache()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testIsSecure()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGetip()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testIpEncode()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testIpdecode()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testGet_host_name()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testParseMemorySize()
			{
				$res = null;
				$this->assertTrue($res);
			}
	*/
	public function testIsInstalled()
	{
		$obj = $this->e107;

		$result = $obj::isInstalled('user');

		// var_dump($result);
		$this->assertTrue($result);

		$result = $obj::isInstalled('news');

		// var_dump($result);
		$this->assertTrue($result);
	}
	/*
			public function testIni_set()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testAutoload_register()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testAutoload()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function test__get()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testDestruct()
			{
				$res = null;
				$this->assertTrue($res);
			}

			public function testCoreUpdateAvailable()
			{
				$res = null;
				$this->assertTrue($res);
			}


	*/
}
