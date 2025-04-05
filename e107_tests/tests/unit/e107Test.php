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

	protected $tempFiles = [];
	/** @var e107 */
	private $e107;

	protected function _before()
	{

		try
		{
			$this->e107 = e107::getInstance();
		}
		catch(Exception $e)
		{
			self::fail("Couldn't load e107 object");
		}

	}

	protected function _after()
	{

		// Clean up temporary files
		foreach($this->tempFiles as $file)
		{
			if(file_exists($file))
			{
				unlink($file);
			}
		}
		$this->tempFiles = [];
	}

	public function testGetInstance()
	{

		//	$this->e107->getInstance();
		//$res = $this->e107::getInstance();
		//	$this::assertTrue($res);
	}

	/*public function testInitCore()
	{

		//$res = null;
		include_once(APP_PATH . '/e107_config.php'); // contains $E107_CONFIG = array('site_path' => '000000test');

		$e107_paths = @compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'DOWNLOADS_DIRECTORY', 'UPLOADS_DIRECTORY', 'SYSTEM_DIRECTORY', 'MEDIA_DIRECTORY', 'CACHE_DIRECTORY', 'LOGS_DIRECTORY', 'CORE_DIRECTORY', 'WEB_DIRECTORY');
		$sql_info = @compact('mySQLserver', 'mySQLuser', 'mySQLpassword', 'mySQLdefaultdb', 'mySQLprefix', 'mySQLport');
		$res = $this->e107->initCore($e107_paths, e_ROOT, $sql_info, varset($E107_CONFIG, array()));

		$this::assertEquals('000000test', $res->site_path);

		$this::assertEquals('/', e_HTTP);

	}*/

	public function testRenderLayout()
	{

		$opts = array(
			'magicSC'   => array(
				'{---HEADER---}' => '<h3>MY HEADER</h3>',
				'{---FOOTER---}' => '<h3>MY FOOTER</h3>',
			),
			'bodyStart' => '<script>google code</script>'
		);


		// test code insertion.
		$LAYOUT = '<body id="page-top">
			<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			      <div class="container">
			        <div class="navbar-header">
			          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			            <span class="sr-only">Toggle navigation</span>
			            <span class="icon-bar"></span>
			            <span class="icon-bar"></span>
			            <span class="icon-bar"></span>
			          </button>
			          <a class="navbar-brand" href="{SITEURL}">{BOOTSTRAP_BRANDING}</a>
			        </div>
			        <div class="navbar-collapse collapse {BOOTSTRAP_NAV_ALIGN}">
			            {NAVIGATION=main}
			            {BOOTSTRAP_USERNAV: placement=top}
			        </div><!--/.navbar-collapse -->
			      </div>
			    </div>
			
			<!--- Optional custom header template controlled by theme_shortcodes -->
			{---HEADER---}
			
			<!-- Page Content -->
			{---LAYOUT---}
			
			<!-- Footer --> 
			
			{SETSTYLE=default}
			<footer>
				<div class="container">
					<div class="row">
			
						<div>
							<div class="col-lg-6">
								{MENU=100}
							</div>
							<div class="col-lg-6">
								{MENU=101}
							</div>
						</div>
			
						<div>
							<div class="col-sm-12 col-lg-4">
								{MENU=102}
							</div>
			
							<div class="col-sm-12 col-lg-8">
								{MENU=103}
							</div>
						</div>
			
						<div >
							<div class="col-lg-12">
								{MENU=104}
							</div>
						</div>
			
						<div>
							<div class="col-lg-6">
								{MENU=105}
								{NAVIGATION=footer}
								{MENU=106}
							</div>
							<div class="col-lg-6 text-right">
								{BOOTSTRAP_USERNAV: placement=bottom&dir=up}
							</div>
						</div>
			
						<div>
							<div class="col-lg-12">
					
							</div>
						</div>
			
						<div>
							<div id="sitedisclaimer" class="col-lg-12 text-center">
								<small >{SITEDISCLAIMER}</small>
							</div>
						</div>
			
					</div>	 <!-- /row -->
				</div> <!-- /container -->
			</footer>
			
			{---MODAL---}
			<!--- Optional custom footer template controlled by theme_shortcodes -->
			{---FOOTER---}
			
			
			<!-- Javascripts and other information are automatically added below here -->
			</body> <!-- This tag is not necessary and is ignored and replaced. Left here only as a reference -->';


		ob_start();

		e107::renderLayout($LAYOUT, $opts);

		$result = ob_get_clean();


		$this::assertStringContainsString('<h3>MY HEADER</h3>', $result);
		$this::assertStringContainsString('<h3>MY FOOTER</h3>', $result);
		$this::assertStringContainsString('<script>google code</script>', $result);
		$this::assertStringNotContainsString('{BOOTSTRAP_BRANDING}', $result);

		//	var_export($result);

	}

	/*
			public function testInitInstall()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testMakeSiteHash()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testSetDirs()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testPrepareDirs()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testDefaultDirs()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testInitInstallSql()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetRegistry()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testSetRegistry()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetFolder()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetE107()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testIsCli()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetMySQLConfig()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetSitePath()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetHandlerPath()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testAddHandler()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testIsHandler()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetHandlerOverload()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testSetHandlerOverload()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testIsHandlerOverloadable()
			{
				$res = null;
				$this::assertTrue($res);
			}
*/
	public function testGetSingleton()
	{

		$e107 = $this->e107;

		// test with path.
		$result = $e107::getSingleton('override', e_HANDLER . 'override_class.php');

		$this::assertNotEmpty($result, 'Override class not loaded');

		$exists = method_exists($result, 'override_check');

		$this::assertTrue($exists, 'Failed to load override class singleton');

		// Test without path.
		$result2 = $e107::getOverride();
		$exists2 = method_exists($result2, 'override_check');
		$this::assertTrue($exists2, 'Failed to load override class singleton');

	}

	/*
				public function testGetObject()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetConfig()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetPref()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testFindPref()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetPlugConfig()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetPlugLan()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetPlugPref()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testFindPlugPref()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetThemeConfig()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetThemePref()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testSetThemePref()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetThemeGlyphs()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetParser()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetScParser()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetSecureImg()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetScBatch()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetDb()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetCache()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetBB()
				{
					$res = null;
					$this::assertTrue($res);
				}*/


	public function testGetUserSession()
	{

		$tmp = e107::getUserSession();

		$className = get_class($tmp);

		$res = ($className === 'UserHandler');

		$this::assertTrue($res);

	}

	/**
	 * Test sessions and namespaced sessions.
	 * Make sure data is kept separate.
	 */
	public function testGetSession()
	{

		$e107 = $this->e107;

		// Simple session set/get
		$sess = $e107::getSession();
		$input = 'test-key-result';
		$sess->set('test-key', $input);
		$this::assertSame($input, $sess->get('test-key'));

		// Create Session 2 with namespace. Make sure Session 1 key is not present.
		$sess2 = $e107::getSession('other');
		$this::assertEmpty($sess2->get('test-key'));

		// Make sure Session 2 key is set and not present in Session 1.
		$sess2->set('other-key', true);
		$this::assertEmpty($sess->get('other-key'));
		$this::assertTrue($sess2->get('other-key'));

	}

	/*
			public function testGetRedirect()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetRate()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetSitelinks()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetRender()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetEmail()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetBulkEmail()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetEvent()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetArrayStorage()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetMenu()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetTheme()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetUrl()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetFile()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetForm()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetAdminLog()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetLog()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetDateConvert()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetDate()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetDebug()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetNotify()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetOverride()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetLanguage()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetIPHandler()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetXml()
			{
				$res = null;
				$this::assertTrue($res);
			}
			*/

	public function testGetHybridAuth()
	{

		$object = e107::getHybridAuth();
		$this::assertInstanceOf(Hybridauth\Hybridauth::class, $object);
	}

	/*
	public function testGetUserClass()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetSystemUser()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testUser()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testSerialize()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testUnserialize()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetUser()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetModel()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetUserStructure()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetUserExt()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetUserPerms()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetRank()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetPlugin()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetPlug()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetOnline()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetChart()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetComment()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetCustomFields()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetMedia()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetNav()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetMessage()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetAjax()
	{
		$res = null;
		$this::assertTrue($res);
	}

	public function testGetLibrary()
	{
		$res = null;
		$this::assertTrue($res);
	}
*/
	public function testLibrary()
	{

		$e107 = $this->e107;

		$expected = array(
			'js'  =>
				array(
					0 => '{e_WEB}lib/font-awesome/5/js/all.min.js',
					1 => '{e_WEB}lib/font-awesome/5/js/v4-shims.min.js',
				),
			'css' =>
				array(
					0 => '{e_WEB}lib/font-awesome/5/css/all.min.css',
					1 => '{e_WEB}lib/font-awesome/5/css/v4-shims.min.css',
				),

		);

		$result = $e107::library('files', 'fontawesome5');
		$this::assertSame($expected, $result);


		// -------------------

		// Expecting only the JS portion of the library.
		$expected = array(
			'js' =>
				array(
					0 => '{e_WEB}lib/font-awesome/5/js/all.min.js',
					1 => '{e_WEB}lib/font-awesome/5/js/v4-shims.min.js',
				),
		);

		$result = $e107::library('files', 'fontawesome5', null, ['js']);
		$this::assertSame($expected, $result);

		// -------------------
		$expected = array(
			'js'  =>
				array(
					0 => '{e_WEB}lib/bootstrap/5/js/bootstrap.bundle.min.js',
				),
			'css' =>
				array(
					0 => '{e_WEB}lib/bootstrap/5/css/bootstrap.min.css',
				),

		);

		$result = $e107::library('files', 'bootstrap5');
		$this::assertSame($expected, $result);

	}

	/*
		public function testGetJs()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testSet()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testJs()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testLink()
		{


		}

		public function testCss()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testDebug()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testGetJshelper()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testMeta()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testGetAdminUI()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testGetAddon()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testGetAddonConfig()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testCallMethod()
		{
			$res = null;
			$this::assertTrue($res);
		}
	*/
	public function testGetUrlConfig()
	{


		$expected = array(
			'index' =>
				array(
					'alias'    => 'contact',
					'regex'    => '^{alias}\\/?$',
					'sef'      => '{alias}',
					'redirect' => '{e_BASE}contact.php',
				),
		);

		$result = e107::getUrlConfig();
		$this::assertNotEmpty($result['contact']);
		$this::assertSame($expected, $result['contact']);

		// ----

		$expected = array(
			'alias'    => 'contact',
			'regex'    => '^{alias}\\/?$',
			'sef'      => '{alias}',
			'redirect' => '{e_BASE}contact.php',
		);

		$result = e107::getUrlConfig('route');
		$this::assertNotEmpty($result['contact/index']);
		$this::assertSame($expected, $result['contact/index']);


	}

	/*
		public function testGetThemeInfo()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testCoreTemplatePath()
		{
			$res = null;
			$this::assertTrue($res);
		}

		public function testTemplatePath()
		{
			$res = null;
			$this::assertTrue($res);
		}
	*/
	public function testLoadAdminIcons()
	{

		$e107 = $this->e107;

		$legacyList = array(
			'E_16_FACEBOOK'              => '<img class=\'icon S16\' src=\'./e107_images/admin_images/facebook_16.png\' alt=\'\' />',
			'E_16_TWITTER'               => '<img class=\'icon S16\' src=\'./e107_images/admin_images/twitter_16.png\' alt=\'\' />',
			'E_16_GITHUB'                => '<img class=\'icon S16\' src=\'./e107_images/admin_images/github_16.png\' alt=\'\' />',
			'E_16_E107'                  => '<img class=\'icon S16\' src=\'./e107_images/e107_icon_16.png\' alt=\'\' />',
			'E_32_E107'                  => '<img class=\'icon S32\' src=\'./e107_images/e107_icon_32.png\' alt=\'\' />',
			'E_32_ADMIN'                 => '<i class=\'S32 e-admins-32\'></i>',
			'E_32_ADPASS'                => '<i class=\'S32 e-adminpass-32\'></i>',
			'E_32_BANLIST'               => '<i class=\'S32 e-banlist-32\'></i>',
			'E_32_CACHE'                 => '<i class=\'S32 e-cache-32\'></i> ',
			'E_32_CREDITS'               => '<i class=\'S32 e-e107_icon-32.png\'></i>',
			'E_32_CRON'                  => '<i class=\'S32 e-cron-32\'></i> ',
			'E_32_CUST'                  => '<i class=\'S32 e-custom-32\'></i> ',
			'E_32_DATAB'                 => '<i class=\'S32 e-database-32\'></i> ',
			'E_32_DOCS'                  => '<i class=\'S32 e-docs-32\'></i> ',
			'E_32_EMOTE'                 => '<i class=\'S32 e-emoticons-32\'></i> ',
			'E_32_FILE'                  => '<i class=\'S32 e-filemanager-32\'></i> ',
			'E_32_FORUM'                 => '<i class=\'S32 e-forums-32\'></i> ',
			'E_32_FRONT'                 => '<i class=\'S32 e-frontpage-32\'></i> ',
			'E_32_IMAGES'                => '<i class=\'S32 e-images-32\'></i> ',
			'E_32_INSPECT'               => '<i class=\'S32 e-fileinspector-32\'></i> ',
			'E_32_LINKS'                 => '<i class=\'S32 e-links-32\'></i> ',
			'E_32_WELCOME'               => '<i class=\'S32 e-welcome-32\'></i> ',
			'E_32_MAIL'                  => '<i class=\'S32 e-mail-32\'></i> ',
			'E_32_MAINTAIN'              => '<i class=\'S32 e-maintain-32\'></i> ',
			'E_32_MENUS'                 => '<i class=\'S32 e-menus-32\'></i> ',
			'E_32_META'                  => '<i class=\'S32 e-meta-32\'></i> ',
			'E_32_NEWS'                  => '<i class=\'S32 e-news-32\'></i> ',
			'E_32_NEWSFEED'              => '<i class=\'S32 e-newsfeeds-32\'></i> ',
			'E_32_NOTIFY'                => '<i class=\'S32 e-notify-32\'></i> ',
			'E_32_PHP'                   => '<i class=\'S32 e-phpinfo-32\'></i> ',
			'E_32_POLLS'                 => '<i class=\'S32 e-polls-32\'></i> ',
			'E_32_PREFS'                 => '<i class=\'S32 e-prefs-32\'></i> ',
			'E_32_SEARCH'                => '<i class=\'S32 e-search-32\'></i> ',
			'E_32_UPLOADS'               => '<i class=\'S32 e-uploads-32\'></i> ',
			'E_32_EURL'                  => '<i class=\'S32 e-eurl-32\'></i> ',
			'E_32_USER'                  => '<i class=\'S32 e-users-32\'></i> ',
			'E_32_USER_EXTENDED'         => '<i class=\'S32 e-extended-32\'></i> ',
			'E_32_USERCLASS'             => '<i class=\'S32 e-userclass-32\'></i> ',
			'E_32_LANGUAGE'              => '<i class=\'S32 e-language-32\'></i> ',
			'E_32_PLUGIN'                => '<i class=\'S32 e-plugins-32\'></i> ',
			'E_32_PLUGMANAGER'           => '<i class=\'S32 e-plugmanager-32\'></i> ',
			'E_32_MAIN'                  => '<i class=\'S32 e-main-32\'></i> ',
			'E_32_THEMEMANAGER'          => '<i class=\'S32 e-themes-32\'></i> ',
			'E_32_COMMENT'               => '<i class=\'S32 e-comments-32\'></i> ',
			'E_32_ADMINLOG'              => '<i class=\'S32 e-adminlogs-32\'></i> ',
			'E_32_LOGOUT'                => '<i class=\'S32 e-logout-32\'></i> ',
			'E_32_MANAGE'                => '<i class=\'S32 e-manage-32\'></i> ',
			'E_32_CREATE'                => '<i class=\'S32 e-add-32\'></i> ',
			'E_32_SETTINGS'              => '<i class=\'S32 e-settings-32\'></i> ',
			'E_32_SYSINFO'               => '<i class=\'S32 e-sysinfo-32\'></i> ',
			'E_32_CAT_SETT'              => '<i class=\'S32 e-cat_settings-32\'></i> ',
			'E_32_CAT_USER'              => '<i class=\'S32 e-cat_users-32\'></i> ',
			'E_32_CAT_CONT'              => '<i class=\'S32 e-cat_content-32\'></i> ',
			'E_32_CAT_FILE'              => '<i class=\'S32 e-cat_files-32\'></i> ',
			'E_32_CAT_TOOL'              => '<i class=\'S32 e-cat_tools-32\'></i> ',
			'E_32_CAT_PLUG'              => '<i class=\'S32 e-cat_plugins-32\'></i> ',
			'E_32_CAT_MANAGE'            => '<i class=\'S32 e-manage-32\'></i> ',
			'E_32_CAT_MISC'              => '<i class=\'S32 e-settings-32\'></i> ',
			'E_32_CAT_ABOUT'             => '<i class=\'S32 e-info-32\'></i> ',
			'E_32_NAV_MAIN'              => '<i class=\'S32 e-main-32\'></i> ',
			'E_32_NAV_DOCS'              => '<i class=\'S32 e-docs-32\'></i> ',
			'E_32_NAV_LEAV'              => '<i class=\'S32 e-leave-32\'></i> ',
			'E_32_NAV_LGOT'              => '<i class=\'S32 e-logout-32\'></i> ',
			'E_32_NAV_ARROW'             => '<i class=\'S32 e-arrow-32\'></i> ',
			'E_32_NAV_ARROW_OVER'        => '<i class=\'S32 e-arrow_over-32\'></i> ',
			'E_16_ADMIN'                 => '<i class=\'S16 e-admins-16\'></i>',
			'E_16_ADPASS'                => '<i class=\'S16 e-adminpass-16\'></i>',
			'E_16_BANLIST'               => '<i class=\'S16 e-banlist-16\'></i>',
			'E_16_CACHE'                 => '<i class=\'S16 e-cache-16\'></i>',
			'E_16_COMMENT'               => '<i class=\'S16 e-comments-16\'></i>',
			'E_16_CREDITS'               => '<i class=\'S16 e-e107_icon-16\'></i>',
			'E_16_CRON'                  => '<i class=\'S16 e-cron-16\'></i>',
			'E_16_CUST'                  => '<i class=\'S16 e-custom-16\'></i>',
			'E_16_CUSTOMFIELD'           => '<i class=\'S16 e-custom_field-16\'></i>',
			'E_16_DATAB'                 => '<i class=\'S16 e-database-16\'></i>',
			'E_16_DOCS'                  => '<i class=\'S16 e-docs-16\'></i>',
			'E_16_EMOTE'                 => '<i class=\'S16 e-emoticons-16\'></i>',
			'E_16_FILE'                  => '<i class=\'S16 e-filemanager-16\'></i>',
			'E_16_FORUM'                 => '<i class=\'S16 e-forums-16\'></i>',
			'E_16_FRONT'                 => '<i class=\'S16 e-frontpage-16\'></i>',
			'E_16_IMAGES'                => '<i class=\'S16 e-images-16\'></i>',
			'E_16_INSPECT'               => '<i class=\'S16 e-fileinspector-16\'></i>',
			'E_16_LINKS'                 => '<i class=\'S16 e-links-16\'></i>',
			'E_16_WELCOME'               => '<i class=\'S16 e-welcome-16\'></i>',
			'E_16_MAIL'                  => '<i class=\'S16 e-mail-16\'></i>',
			'E_16_MAINTAIN'              => '<i class=\'S16 e-maintain-16\'></i>',
			'E_16_MENUS'                 => '<i class=\'icon S16 e-menus-16\'></i>',
			'E_16_META'                  => '<i class=\'icon S16 e-meta-16\'></i>',
			'E_16_NEWS'                  => '<i class=\'icon S16 e-news-16\'></i>',
			'E_16_NEWSFEED'              => '<i class=\'S16 e-newsfeeds-16\'></i>',
			'E_16_NOTIFY'                => '<i class=\'S16 e-notify-16\'></i>',
			'E_16_PHP'                   => '<i class=\'S16 e-phpinfo-16\'></i>',
			'E_16_POLLS'                 => '<i class=\'S16 e-polls-16\'></i>',
			'E_16_PREFS'                 => '<i class=\'S16 e-prefs-16\'></i>',
			'E_16_SEARCH'                => '<i class=\'S16 e-search-16\'></i>',
			'E_16_UPLOADS'               => '<i class=\'S16 e-uploads-16\'></i>',
			'E_16_EURL'                  => '<i class=\'S16 e-eurl-16\'></i>',
			'E_16_USER'                  => '<i class=\'S16 e-users-16\'></i>',
			'E_16_USER_EXTENDED'         => '<i class=\'S16 e-extended-16\'></i>',
			'E_16_USERCLASS'             => '<i class=\'S16 e-userclass-16\'></i>',
			'E_16_LANGUAGE'              => '<i class=\'S16 e-language-16\'></i>',
			'E_16_PLUGIN'                => '<i class=\'S16 e-plugins-16\'></i>',
			'E_16_PLUGMANAGER'           => '<i class=\'S16 e-plugmanager-16\'></i>',
			'E_16_THEMEMANAGER'          => '<i class=\'S16 e-themes-16\'></i>',
			'E_16_ADMINLOG'              => '<i class=\'S16 e-adminlogs-16\'></i>',
			'E_16_MANAGE'                => '<i class=\'S16 e-manage-16\'></i>',
			'E_16_CREATE'                => '<i class=\'S16 e-add-16\'></i>',
			'E_16_SETTINGS'              => '<i class=\'S16 e-settings-16\'></i>',
			'E_16_SYSINFO'               => '<i class=\'S16 e-sysinfo-16\'></i>',
			'E_16_FAILEDLOGIN'           => '<i class=\'S16 e-failedlogin-16\'></i>',
			'E_32_TRUE'                  => '<i class=\'S32 e-true-32\'></i>',
			'ADMIN_CHILD_ICON'           => '<img src="/e107_images/generic/branchbottom.gif" class="treeprefix level-x icon" alt="" />',
			'ADMIN_FILTER_ICON'          => '<i class=\'fa fa-filter\'></i>',
			'ADMIN_TRUE_ICON'            => '<span class=\'text-success admin-true-icon\'>&#10004;</span>',
			'ADMIN_FALSE_ICON'           => '<span class=\'text-danger admin-false-icon\'>&#10799;</span>',
			'ADMIN_WARNING_ICON'         => '<i class=\'fa fa-warning text-warning\'></i>',
			'ADMIN_GRID_ICON'            => '<i class=\'fa fa-th\'></i>',
			'ADMIN_LIST_ICON'            => '<i class=\'fas fa-list\'></i>',
			'ADMIN_EDIT_ICON'            => "<i class='admin-ui-option fa fa-edit fa-2x fa-fw'></i>",
			'ADMIN_DELETE_ICON'          => "<i class='admin-ui-option fa fa-trash fa-2x fa-fw'></i>",
			'ADMIN_SORT_ICON'            => "<i class='admin-ui-option fa fa-sort fa-2x fa-fw'></i>",
			'ADMIN_EXECUTE_ICON'         => "<i class='admin-ui-option fa fa-play fa-2x fa-fw'></i>",
			'ADMIN_PAGES_ICON'           => "<i class='admin-ui-option fa fa-file fa-2x fa-fw'></i>",
			'ADMIN_ADD_ICON'             => '<i class=\'S32 e-add-32\'></i>',
			'ADMIN_INFO_ICON'            => '<i class=\'fa fa-question-circle\'></i>',
			'ADMIN_CONFIGURE_ICON'       => "<i class='admin-ui-option fa fa-cog fa-2x fa-fw'></i>",
			'ADMIN_VIEW_ICON'            => "<i class='admin-ui-option fa fa-search fa-2x fa-fw'></i>",
			'ADMIN_URL_ICON'             => '<i class=\'S16 e-forums-16\'></i>',
			'ADMIN_INSTALLPLUGIN_ICON'   => '<i class=\'S32 e-plugin_install-32\'></i>',
			'ADMIN_UNINSTALLPLUGIN_ICON' => "<i class='admin-ui-option fa fa-trash fa-2x fa-fw'></i>",
			'ADMIN_UPGRADEPLUGIN_ICON'   => "<i class='admin-ui-option fa fa-arrow-up fa-2x fa-fw'></i>",
			'ADMIN_REPAIRPLUGIN_ICON'    => "<i class='admin-ui-option fa fa-wrench fa-2x fa-fw'></i>",
			'ADMIN_UP_ICON'              => "<i class='admin-ui-option fa fa-chevron-up fa-2x fa-fw'></i>",
			'ADMIN_DOWN_ICON'            => "<i class='admin-ui-option fa fa-chevron-down fa-2x fa-fw'></i>",
			'ADMIN_EDIT_ICON_PATH'       => '/e107_images/admin_images/edit_32.png',
			'ADMIN_DELETE_ICON_PATH'     => '/e107_images/admin_images/delete_32.png',
			'ADMIN_WARNING_ICON_PATH'    => '/e107_images/admin_images/warning_32.png',
			'E_24_PLUGIN'                => "<i class='S24 e-plugins-24'></i> ",
			'E_16_UNDO'                  => "<img class='icon S16' src='" . e_IMAGE . "admin_images/undo_16.png' alt='' />",
			'E_32_UNDO'                  => "<img class='icon S32' src='" . e_IMAGE . "admin_images/undo_32.png' alt='' />"
		);


		$new = $e107::loadAdminIcons();

		foreach($new as $key => $val)
		{
			if(!isset($legacyList[$key]))
			{
				$this::fail("Remove $key FROM admin_icons_template");
			}

			$this::assertSame($legacyList[$key], $val, $key . " should equal: " . $legacyList[$key]);
		}

		foreach($legacyList as $key => $val)
		{
			if(!isset($new[$key]))
			{
				$this::fail("$key is missing from admin_icons_template");
			}

		}

		$template2 = $e107::loadAdminIcons();

		$this::assertSame($new, $template2);

		$range = range(1, 10);
		foreach($range as $t)
		{
			e107::loadAdminIcons();
			$e107::loadAdminIcons();
		}


	}


	/**
	 * @return void
	 */
	public function testGetCoreTemplate()
	{

		$e107 = $this->e107;
		$templates = scandir(e_CORE . "templates");

		// Load these constants before other tests fail because of what this test does:
		$e107::loadAdminIcons();

		$exclude = array(
			'bbcode_template.php',
			'online_template.php', // FIXME - convert the template to v2.x standards.
			'sitedown_template.php', // FIXME - convert the template to v2.x standards.
		);

		foreach($templates as $file)
		{
			if(strpos($file, '_template.php') === false || in_array($file, $exclude))
			{
				continue;
			}

			$path = str_replace('_template.php', '', $file);

			$e107::coreLan($path);

			if($path === 'signup')
			{
				$e107::coreLan('user');
			}

			$result = $e107::getCoreTemplate($path);

			$this::assertIsArray($result, $path . "  template was not an array");
			$this::assertNotEmpty($result, $path . " template was empty");

		}

		//$res = null;
		//$this::assertTrue($res);
	}
	/*
		private function clearRelatedRegistry($type)
		{
			$registry = e107::getRegistry('_all_');

			$result = [];
			foreach($registry as $reg => $v)
			{
				if(strpos($reg, $type) !== false)
				{
					e107::setRegistry($reg);
					$result[] = $reg;
				}

			}

			sort($result);

			return $result;
		}*/
	/*
		public function testGetTemplatePluginThemeMatch()
		{
			e107::plugLan('download', 'front', true);

			e107::getConfig()->set('sitetheme', 'bootstrap3');
			$template = e107::getTemplate('download', null, null);
			var_export($template['header']);
			echo "\n\n";


			e107::getConfig()->set('sitetheme', '_blank');
			$template = e107::getTemplate('download', null, null);
			var_export($template['header']);
			echo "\n\n";

			e107::getConfig()->set('sitetheme', 'bootstrap3'); // doesn't have a download template, so fallback.
			$template = e107::getTemplate('download', null, null); // theme override is enabled by default.
			var_export($template['header']);
			echo "\n\n";

			e107::getConfig()->set('sitetheme', 'bootstrap3');
		}
	*/

	public function testGetTemplateOverride()
	{

		// Loads e107_themes/bootstrap3/templates/gallery/gallery_template.php
		$template = e107::getTemplate('gallery'); // true & false default, loads theme (override true)  e107::getTemplate('gallery', null, null, true, false)
		$this::assertEquals("My Gallery", $template['list']['caption']);

		// Duplicate to load registry
		$template2 = e107::getTemplate('gallery'); // true & false default, loads theme (override true) ie. e107::getTemplate('gallery', null, null, true, false)
		$this::assertEquals("My Gallery", $template2['list']['caption']);

		$this::assertSame($template, $template2);

	}


	public function testGetTemplateOverrideMerge()
	{

		// Loads e107_plugins/gallery/templates/gallery_template.php then overwrites it with e107_themes/bootstrap3/templates/gallery/gallery_template.php
		$template = e107::getTemplate('gallery', null, null, true, true); // theme override is enabled, and theme merge is enabled.
		$this::assertArrayHasKey('merged-example', $template);
		$this::assertEquals("My Gallery", $template['list']['caption']); // ie. from the original
		$this::assertNotEmpty($template['merged-example']);

		// duplicate to load registry
		$template2 = e107::getTemplate('gallery', null, null, true, true); // theme override is enabled, and theme merge is enabled.
		$this::assertArrayHasKey('merged-example', $template2);
		$this::assertEquals("My Gallery", $template2['list']['caption']); // ie. from the original
		$this::assertNotEmpty($template2['merged-example']);

		$this::assertSame($template, $template2);

	}

	public function testGetTemplateMerge()
	{

		// // ie. should be from plugin template, not theme.
		$template = e107::getTemplate('gallery', null, null, false, true); // theme override is disabled, theme merge is enabled.
		$this::assertEquals("Gallery", $template['list']['caption']);
		$this::assertArrayNotHasKey('merged-example', $template);

		// duplicate to load registry.
		$template2 = e107::getTemplate('gallery', null, null, false, true); // theme override is disabled, theme merge is enabled.
		$this::assertEquals("Gallery", $template2['list']['caption']);
		$this::assertArrayNotHasKey('merged-example', $template2);

		$this::assertSame($template, $template2);

	}

	/**
	 * This test checks getTemplate() with no merging or override.
	 */
	public function testGetTemplate()
	{

		// Loads e107_plugins/gallery/templates/gallery_template.php
		$template = e107::getTemplate('gallery', null, null, false); // theme override is disabled.
		$this::assertEquals("Gallery", $template['list']['caption']);

		// Duplicate to load registry.
		$template2 = e107::getTemplate('gallery', null, null, false); // theme override is disabled.
		$this::assertEquals("Gallery", $template2['list']['caption']);

		$this::assertSame($template, $template2);
	}

	/*
			public function testTemplateWrapper()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testScStyle()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetTemplateInfo()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetLayouts()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function test_getTemplate()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testIncludeLan()
			{
				$res = null;
				$this::assertTrue($res);
			}
*/
	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testCoreLan()
	{

		// Example constant known to be in core language files, adjust accordingly.
		$constant = 'LAN_MEMBERS_0';
		$expected = "restricted area";

		// First, ensure the constant is not already defined (clean test scenario).
		if(defined($constant))
		{
			$this::markTestSkipped("Constant '$constant' was already defined. Skipped for accurate isolation.");
		}

		// Call the method you need to test.
		$this->e107::coreLan('membersonly'); // 'admin' is an example; adjust if needed based on your actual language files

		// Check if the constant is correctly defined afterward.
		$this::assertTrue(defined($constant), "coreLan() should define the constant '{$constant}'.");


		$this::assertEquals($expected, constant($constant), "coreLan() loaded an incorrect value for '{$constant}'.");
	}


	/**
	 * @runInSeparateProcess
	 */
	public function testCoreLanArray()
	{

		$languageDir = e_LANGUAGEDIR . 'English/';
		$langFile = $languageDir . 'lan_test0000.php';

		// Ensure the language directory exists.
		if(!is_dir($languageDir))
		{
			mkdir($languageDir, 0777, true);
		}

		// Populate the file dynamically with an array of test language terms.
		file_put_contents($langFile, "<?php\nreturn [\n" .
			"'LAN_TEST0000_ONE' => 'Test value one',\n" .
			"'LAN_TEST0000_TWO' => 'Test value two',\n" .
			"'LAN_TEST0000_THREE' => 'Test value three',\n" .
			"];");

		// Store created file for later cleanup.
		$this->tempFiles[] = $langFile;

		// Define the constant and expected output.
		$constant = 'LAN_TEST0000_ONE';
		$expected = 'Test value one';

		// Confirm the file was created.
		$this->assertTrue(is_readable($langFile), "{$langFile} should have been created and readable.");

		// Skip if already defined, ensuring proper isolation.
		if(defined($constant))
		{
			$this::markTestSkipped("Constant '{$constant}' was already defined. Skipped for accurate isolation.");
		}

		// Run the method you test, passing the newly generated identifier.
		$this->e107->coreLan('test0000');

		// Verify the constant has been defined and contains the correct value.
		$this->assertTrue(defined($constant), "coreLan() should define the constant '{$constant}'.");
		$this->assertEquals($expected, constant($constant), "coreLan() loaded an incorrect value for '{$constant}'.");
	}


	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testPlugLan()
	{

		// Prepare the plugin directory and test files
		$pluginName = 'testplugin';
		$languageDir = e_PLUGIN . $pluginName . '/languages/';
		$frontLangFile = $languageDir . 'English_front.php';
		$adminLangFile = $languageDir . 'English_admin.php';
		$globalLangFile = $languageDir . 'English_global.php';

		// Ensure language directory exists for testing
		if(!is_dir($languageDir))
		{
			mkdir($languageDir, 0777, true);
		}

		// Create mock language files with temporary constants clearly defined:
		file_put_contents($frontLangFile, "<?php define('TESTPLUGIN_FRONT_LAN', 'Front Language Loaded');");
		file_put_contents($adminLangFile, "<?php define('TESTPLUGIN_ADMIN_LAN', 'Admin Language Loaded');");
		file_put_contents($globalLangFile, "<?php define('TESTPLUGIN_GLOBAL_LAN', 'Global Language Loaded');");

		$this->tempFiles[] = $frontLangFile;
		$this->tempFiles[] = $adminLangFile;
		$this->tempFiles[] = $globalLangFile;

		// 1. Test normal front-end file loading
		$retFront = e107::plugLan($pluginName);
		$this::assertTrue(defined('TESTPLUGIN_FRONT_LAN'), 'Front-end language file should be loaded.');
		$this::assertEquals('Front Language Loaded', constant('TESTPLUGIN_FRONT_LAN'));

		// 2. Test Admin file loading
		$retAdmin = e107::plugLan($pluginName, true);
		$this::assertTrue(defined('TESTPLUGIN_ADMIN_LAN'), 'Admin language file should be loaded.');
		$this::assertEquals('Admin Language Loaded', constant('TESTPLUGIN_ADMIN_LAN'));

		// 3. Test Global file loading
		$retGlobal = e107::plugLan($pluginName, 'global');
		$this::assertTrue(defined('TESTPLUGIN_GLOBAL_LAN'), 'Global language file should be loaded.');
		$this::assertEquals('Global Language Loaded', constant('TESTPLUGIN_GLOBAL_LAN'));

		// 4. Test 'flat=true' parameter
		$flatLangDir = $languageDir . 'English';
		$flatLangFile = $flatLangDir . '/English_flatfile.php';
		if(!is_dir($flatLangDir))
		{
			mkdir($flatLangDir, 0777, true);
		}
		file_put_contents($flatLangFile, "<?php define('TESTPLUGIN_FLAT_LAN', 'Flat Language Loaded');");
		$this->tempFiles[] = $flatLangFile;

		$retFlat = e107::plugLan($pluginName, 'flatfile', true);
		$this::assertTrue(defined('TESTPLUGIN_FLAT_LAN'), 'Flat language file should be loaded.');
		$this::assertEquals('Flat Language Loaded', constant('TESTPLUGIN_FLAT_LAN'));

		// 5. Test return path functionality
		$returnedPath = e107::plugLan($pluginName, 'global', false, true);
		$expectedPath = e_PLUGIN . $pluginName . '/languages/English_global.php';
		$this::assertEquals($expectedPath, $returnedPath, 'plugLan() should correctly return the path when $returnPath=true.');
	}


	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testPlugLanArray()
	{

		$pluginName = 'testplugin2';
		$languageDir = e_PLUGIN . $pluginName . '/languages/';

		$frontLangFile = $languageDir . 'English_front.php';
		$adminLangFile = $languageDir . 'English_admin.php';
		$globalLangFile = $languageDir . 'English_global.php';

		if(!is_dir($languageDir))
		{
			mkdir($languageDir, 0777, true);
		}

		file_put_contents($frontLangFile, "<?php return ['TESTPLUGIN_FRONT_ARR_LAN' => 'Front Language Loaded'];");
		file_put_contents($adminLangFile, "<?php return ['TESTPLUGIN_ADMIN_ARR_LAN' => 'Admin Language Loaded'];");
		file_put_contents($globalLangFile, "<?php return ['TESTPLUGIN_GLOBAL_ARR_LAN' => 'Global Language Loaded'];");

		$this->tempFiles[] = $frontLangFile;
		$this->tempFiles[] = $adminLangFile;
		$this->tempFiles[] = $globalLangFile;

		$this->assertTrue(is_readable($frontLangFile), 'Front language file exists and is readable.');
		$retFront = e107::plugLan($pluginName);
		$this->assertTrue($retFront, 'plugLan() should return true after successful inclusion');
		$this->assertTrue(defined('TESTPLUGIN_FRONT_ARR_LAN'), 'Constant TESTPLUGIN_FRONT_ARR_LAN should be defined.');
		$this->assertEquals('Front Language Loaded', constant('TESTPLUGIN_FRONT_ARR_LAN'));

		$this->assertTrue(is_readable($adminLangFile), 'Admin language file exists and is readable.');
		$retAdmin = e107::plugLan($pluginName, true);
		$this->assertTrue($retAdmin, 'plugLan(true) should return true after admin file inclusion');
		$this->assertTrue(defined('TESTPLUGIN_ADMIN_ARR_LAN'), 'Constant TESTPLUGIN_ADMIN_ARR_LAN should be defined.');
		$this->assertEquals('Admin Language Loaded', constant('TESTPLUGIN_ADMIN_ARR_LAN'));

		$this->assertTrue(is_readable($globalLangFile), 'Global language file exists and is readable.');
		$retGlobal = e107::plugLan($pluginName, 'global');
		$this->assertTrue($retGlobal, 'plugLan(global) should return true after global file inclusion');
		$this->assertTrue(defined('TESTPLUGIN_GLOBAL_ARR_LAN'), 'Constant TESTPLUGIN_GLOBAL_ARR_LAN should be defined.');
		$this->assertEquals('Global Language Loaded', constant('TESTPLUGIN_GLOBAL_ARR_LAN'));

		$flatLangDir = $languageDir . 'English';
		$flatLangFile = $flatLangDir . '/English_flatfile.php';
		if(!is_dir($flatLangDir))
		{
			mkdir($flatLangDir, 0777, true);
		}

		file_put_contents($flatLangFile, "<?php return ['TESTPLUGIN_FLAT_LAN' => 'Flat Language Loaded'];");
		$this->tempFiles[] = $flatLangFile;

		$this->assertTrue(is_readable($flatLangFile), 'Flat language file exists and is readable.');
		$retFlat = e107::plugLan($pluginName, 'flatfile', true);
		$this->assertTrue($retFlat, 'Flat file inclusion via plugLan(true, flatfile) should return true');
		$this->assertTrue(defined('TESTPLUGIN_FLAT_LAN'), 'Constant TESTPLUGIN_FLAT_LAN should be defined.');
		$this->assertEquals('Flat Language Loaded', constant('TESTPLUGIN_FLAT_LAN'));

		$returnedPath = e107::plugLan($pluginName, 'global', false, true);
		$expectedPath = e_PLUGIN . $pluginName . '/languages/English_global.php';
		$this->assertEquals($expectedPath, $returnedPath, 'plugLan() should correctly return the path when $returnPath=true.');
	}


	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testPlugLanPath()
	{

		$e107 = $this->e107;

		$tests = array(
			// plug, param 1, param 2, expected
			0 => array('banner', '', false, 'e107_plugins/banner/languages/English_front.php'),
			1 => array('forum', 'front', true, 'e107_plugins/forum/languages/English/English_front.php'),
			2 => array('gallery', true, true, 'e107_plugins/gallery/languages/English/English_admin.php'),
			3 => array('forum', 'menu', true, 'e107_plugins/forum/languages/English/English_menu.php'),
			4 => array('banner', true, false, 'e107_plugins/banner/languages/English_admin.php'),
			5 => array('chatbox_menu', e_LANGUAGE, false, 'e107_plugins/chatbox_menu/languages/English/English.php'),
			6 => array('comment_menu', null, false, 'e107_plugins/comment_menu/languages/English.php'),
			7 => array('poll', null, false, 'e107_plugins/poll/languages/English.php'),
			8 => array('poll', null, false, 'e107_plugins/poll/languages/English.php'),
		);

		foreach($tests as $plug => $var)
		{
			$result = $e107::plugLan($var[0], $var[1], $var[2], true);
			if(!isset($var[3]))
			{
				echo $result . "\n";
				continue;
			}

			$this::assertStringContainsString($var[3], $result);
			$e107::plugLan($var[0], $var[1], $var[2]);
		}
		/*
				$registry = $e107::getRegistry('_all_');

				foreach($registry as $k=>$v)
				{
					if(strpos($k, 'core/e107/pluglan/') !== false)
					{
						echo $k."\n";

					}


				}*/


	}

	function testDetectRoute()
	{

		e107::getPlugin()->install('forum');

		$tests = array(
			0 => array(
				'plugin'   => 'forum',
				'uri'      => '/e107_plugins/forum/forum.php?f=rules',
				'expected' => 'forum/rules',
			),
			1 => array(
				'plugin'   => 'forum',
				'uri'      => '/e107_plugins/forum/forum_viewforum.php?id=543123',
				'expected' => 'forum/forum',
			),

		);

		foreach($tests as $index => $var)
		{
			$result = e107::detectRoute($var['plugin'], $var['uri']);
			if(empty($var['expected']))
			{
				echo $result . "\n";
				continue;
			}

			$this::assertSame($var['expected'], $result);
		}


		e107::getPlugin()->uninstall('forum');

	}

	/*
					public function testThemeLan()
					{
						$result = e107::themeLan(null, 'basic-light');

					}*/
	/*
				public function testLan()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testPref()
				{
					$res = null;
					$this::assertTrue($res);
				}
		*/


	private function generateRows($var, $plugin)
	{

		preg_match_all('#\{([a-z_]*)\}#', $var['sef'], $matches);


		$variables = array('-one-', '-two-', '-three-');
		$ret = [];

		if(!empty($matches[1]))
		{


			$c = 0;
			foreach($matches[1] as $v)
			{
				if($v === 'alias' && !empty($var['alias']))
				{
					$ret['alias'] = $var['alias'];
				}
				else
				{
					$ret[$v] = $variables[$c];
					$c++;
				}

			}

		}

		/*else
		{
			echo "\n".$plugin.' had no matches for: '.varset($var['sef'])."\n";
		}*/

		return $ret;

	}

	private function generateExpected($string, $rows)
	{

		$search = array('&');
		$replace = array('&amp;');

		foreach($rows as $k => $v)
		{
			$search[] = '{' . $k . '}';
			$replace[] = $v;

		}

		return SITEURL . str_replace($search, $replace, $string);

	}

	public function testCanonical()
	{

		$e107 = $this->e107;
		$e107::canonical('_RESET_');
		$e107::canonical('news');

		$result = $e107::canonical();
		$this::assertSame("https://localhost/e107/news", $result);

	}


	public function testUrl()
	{

		$obj = $this->e107;

		// Test FULL url option on Legacy url with new options['mode']
		$tests = array(
			0 => array(
				'plugin'  => 'news/view/item',
				'key'     => array('news_id' => 1, 'news_sef' => 'my-news-item', 'category_sef' => 'my-category'),
				'row'     => array(),
				'options' => ['mode' => 'full'],
			),
			1 => array(
				'plugin'  => 'news/view/item',
				'key'     => array('news_id' => 1, 'news_sef' => 'my-news-item', 'category_sef' => 'my-category'),
				'row'     => 'full=1&encode=0',
				'options' => ['mode' => 'full'],
			),
			2 => array(
				'plugin'  => 'news/view/item',
				'key'     => array('news_id' => 1, 'news_sef' => 'my-news-item', 'category_sef' => 'my-category'),
				'row'     => '',
				'options' => ['mode' => 'full'],
			),
			3 => array(
				'plugin'  => 'news/view/item',
				'key'     => array('news_id' => 1, 'news_sef' => 'my-news-item', 'category_sef' => 'my-category'),
				'row'     => null,
				'options' => ['mode' => 'full'],
			),

		);
		foreach($tests as $v)
		{
			$result = $obj::url($v['plugin'], $v['key'], $v['row'], $v['options']);
			self::assertStringContainsString('http', $result);
		}


		$tests = array();

		$all = e107::getAddonConfig('e_url');
		foreach($all as $plugin => $var)
		{
			if($plugin === 'gallery' || $plugin === 'rss_menu' || $plugin === 'vstore' || $plugin === '_blank') // fixme - sef may be enabled or disabled each time tests are run
			{
				continue;
			}

			foreach($var as $key => $value)
			{
				$rows = $this->generateRows($value, $plugin);
				$tests[] = array(
					'plugin'     => $plugin,
					'key'        => $key,
					'row'        => $rows,
					'options'    => ['mode' => 'full'],
					'_expected_' => $this->generateExpected($value['sef'], $rows),

				);
			}

		}


		foreach($tests as $index => $var)
		{
			if(empty($var['plugin']))
			{
				continue;
			}

			$result = $obj::url($var['plugin'], $var['key'], $var['row'], $var['options']);

			if(empty($var['_expected_']))
			{
				echo $result . "\n";
				continue;
			}
			self::assertEquals($var['_expected_'], $result, 'Failed on test #' . $index);
			//	$this::assertEquals("https://localhost/e107/news", $result);
		}


	}

	public function testUrlDomain()
	{

		// e107 v2.4 -  test for custom domain

		$obj = $this->e107;

		e107::getPlugin()->install('_blank');
		$result = $obj::url('_blank', 'parked', null, ['mode' => 'full']);
		self::assertSame('https://parked-domain.com/custom', $result);
		e107::getPlugin()->uninstall('_blank');

	}

	/**
	 *        /*
	 * e107::getUrl()->create('page/book/index', $row,'allow=chapter_id,chapter_sef,book_sef') ;
	 * e107::getUrl()->create('user/profile/view', $this->news_item)
	 * e107::getUrl()->create('user/profile/view', array('name' => $this->var['user_name'], 'id' => $this->var['user_id']));
	 * e107::getUrl()->create('page/chapter/index', $row,'allow=chapter_id,chapter_sef,book_sef') ;
	 * e107::getUrl()->create('user/myprofile/edit');
	 * e107::getUrl()->create('gallery/index/list', $this->var);
	 * e107::getUrl()->create('news/view/item', $row, array('full' => 1));
	 * e107::getUrl()->create('news/list/all'),
	 * e107::getUrl()->create('page/view/index',$row),
	 * e107::getUrl()->create('page/chapter/index', $sef),
	 * ($sef = $row;
	 * $sef['chapter_sef'] = $this->getSef($row['chapter_id']);
	 * $sef['book_sef']    = $this->getSef($row['chapter_parent']);)
	 *
	 * e107::getUrl()->create('news/list/tag', array('tag' => $word));
	 * $LINKTOFORUM = e107::getUrl()->create('forum/forum/view', array('id' => $row['thread_forum_id'])); //$e107->url->getUrl('forum', 'forum', "func=view&id={$row['thread_forum_id']}");
	 * e107::getUrl()->create('search');
	 */
	public function testUrlLegacy()
	{

		// set eURL config to 'Friendly'
		$oldConfig = e107::getPref('url_config');

		$newConfig = array(
			'news'   => 'core/sef_full',
			'page'   => 'core/sef_chapters',
			'search' => 'core/rewrite',
			'system' => 'core/rewrite',
			'user'   => 'core/rewrite',
			//		'gallery' => 'plugin/rewrite'
		);


		$this->setUrlConfig($newConfig);

		$legacyTests = array(

			0 => array(
				'route'      => 'news/view/item',
				'row'        => array('news_id' => 1, 'news_sef' => 'my-news-item', 'category_sef' => 'my-category'),
				'options'    => 'full=1',
				'_expected_' => 'https://localhost/e107/news/my-category/my-news-item'
			),
			1 => array(
				'route'      => 'news/view/item',
				'row'        => array('id' => 1, 'name' => 'my-news-item', 'category' => 'my-category'),
				'options'    => 'full=1',
				'_expected_' => 'https://localhost/e107/news/my-category/my-news-item'
			),
			2 => array(
				'route'      => 'news/list/short',
				'row'        => array('id' => 1, 'name' => 'my-news-item', 'category' => 'my-category'),
				'options'    => 'full=1',
				'_expected_' => 'https://localhost/e107/news/short/my-news-item'
			),
			3 => array(
				'route'      => 'news/list/tag',
				'row'        => array('tag' => 'myword'),
				'options'    => 'full=1',
				'_expected_' => 'https://localhost/e107/news/tag/myword'
			),
			4 => array(
				'route'      => 'search',
				'row'        => '',
				'options'    => 'full=1',
				'_expected_' => 'https://localhost/e107/search'
			),
			5 => array(
				'route'      => 'user/profile/view',
				'row'        => array('user_id' => 3, 'user_name' => 'john'),
				'options'    => 'full=1',
				'_expected_' => 'https://localhost/e107/user/john'
			),
			6 => array(
				'route'      => 'page/book/index',
				'row'        => array('chapter_id' => 2, 'chapter_sef' => 'my-book'),
				'options'    => 'full=1',
				'_expected_' => 'https://localhost/e107/page/my-book'
			),
			7 => array(
				'route'      => 'page/chapter/index',
				'row'        => array('chapter_id' => 2, 'chapter_sef' => 'my-chapter', 'book_sef' => 'my-book'),
				'options'    => 'full=1',
				'_expected_' => 'https://localhost/e107/page/my-book/my-chapter'
			),
			8 => array(
				'route'      => 'page/view',
				'row'        => array('page_id' => 3, 'page_sef' => 'my-page', 'chapter_id' => 2, 'chapter_sef' => 'my-chapter', 'book_sef' => 'my-book'),
				'options'    => 'full=1',
				'_expected_' => 'https://localhost/e107/page/my-book/my-chapter/my-page'
			),


			// todo add more.
		);

		$e107 = $this->e107;

		foreach($legacyTests as $index => $var)
		{
			if(empty($var['route']))
			{
				continue;
			}

			$result = $e107::url($var['route'], $var['row'], $var['options']);
			$lresult = e107::getUrl()->create($var['route'], $var['row'], $var['options']);

			if(empty($var['_expected_']))
			{
				echo $result . "\n";
				echo $lresult . "\n\n";
				continue;
			}

			$this::assertEquals($result, $lresult, "Legacy Test #" . $index . " -- e107::getUrl()->create('" . $var['route'] . "') didn't match e107::url('" . $var['route'] . "')");
			$this::assertEquals($var['_expected_'], $result, 'Legacy URL index #' . $index . ' failed');


		}


		$this->setUrlConfig($oldConfig);  // return config to previous state.


	}


	/**
	 * Save the url_config preference
	 *
	 * @param array $newConfig
	 */
	private function setUrlConfig($newConfig = array())
	{

		if(empty($newConfig))
		{
			return;
		}

		$cfg = e107::getConfig();

		foreach($newConfig as $k => $v)
		{
			$cfg->setPref('url_config/' . $k, $v);
		}

		$cfg->save(false, true);

		$router = e107::getUrl()->router(); // e107::getSingleton('eRouter');
		$rules = $router->getRuleSets();

		if(empty($rules['news']) || empty($rules['page']))
		{
			$router->loadConfig(true);
		}

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
				'f'  => 'post',
				'id' => 123
			),
		));
		$this::assertEquals(
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
				'did'    => 'much doge',
			),
		));
		$this::assertEquals(
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
		$this::assertEquals(
			e_HTTP .
			'forum/&lt;&gt;/#Arts &amp; Crafts &lt;tag attr=&quot;can&#039;t inject here&quot;&gt;&lt;/tag&gt;',
			$url, "Generated href did not prevent HTML tag injection as expected"
		);

	}

	/*
			public function testRedirect()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGetError()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testHttpBuildQuery()
			{
				$res = null;
				$this::assertTrue($res);
			}
*/
	public function testMinify()
	{

		$text = "something ; other or ; else";
		$expected = "something;other or;else";

		$result = e107::minify($text);

		$this::assertEquals($expected, $result);

	}

	public function testWysiwyg()
	{

		// Simulate editors being installed.
		$editors = array(
			'tinymce4'  => 'TinyMce4',
			'simplemde' => 'SimpleMDE',
		);

		e107::getConfig()
			->setPref('wysiwyg', true)
			->setPref('wysiwyg_list', $editors)
			->save();

		global $_E107;
		$_E107['phpunit'] = true; // make sure pref is re-loaded.
		//	$tinyMceInstalled = e107::isInstalled('tinymce4');

		$tests = array(
			//input     => expected
			'default'   => 'tinymce4',
			'bbcode'    => 'bbcode',
			'tinymce4'  => 'tinymce4',
			'simplemde' => 'simplemde',
			'nonexist'  => 'tinymce4',
		);

		foreach($tests as $input => $expected)
		{
			e107::wysiwyg($input);     // set the wysiwyg editor.
			$result = e107::wysiwyg(null, true);  // get the name of the editor.
			$this::assertSame($expected, $result, "Input: " . $input);
		}


		e107::getConfig()->setPref('wysiwyg', false)->save();  // wysiwyg is disabled.
		e107::wysiwyg('default');    // set as default.
		$result = e107::wysiwyg(null, true);   // get the editor value.
		$expected = 'bbcode';
		e107::getConfig()->setPref('wysiwyg', true)->save(); // enabled wysiwyg again.
		$this::assertSame($expected, $result);


		e107::getConfig()->setPref('wysiwyg', false)->save();  // wysiwyg is disabled.
	}

	/*
				public function testLoadLanFiles()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testPrepare_request()
				{
					$res = null;
					$this::assertTrue($res);
				}
		*/

	public function testBase64DecodeOnAjaxURL()
	{

		$query = "mode=main&iframe=1&action=info&src=aWQ9ODgzJnVybD1odHRwcyUzQSUyRiUyRmUxMDcub3JnJTJGZTEwN19wbHVnaW5zJTJGYWRkb25zJTJGYWRkb25zLnBocCUzRmlkJTNEODgzJTI2YW1wJTNCbW9kYWwlM0QxJm1vZGU9YWRkb24mcHJpY2U9";

		$result = base64_decode($query, true);

		$this::assertFalse($result); // correct result is 'false'.
	}


	public function testInAdminDir()
	{

		return null; // FIXME
		$this::markTestSkipped("Skipped until admin-area conflict can be resolved."); // FIXME
		$tests = array(
			0  => array('path' => 'thumb.php', 'plugdir' => false, 'expected' => false),
			1  => array('path' => 'index.php', 'plugdir' => false, 'expected' => false),
			2  => array('path' => 'e107_admin/prefs.php', 'plugdir' => false, 'expected' => true),
			3  => array('path' => 'e107_admin/menus.php', 'plugdir' => false, 'expected' => true),
			4  => array('path' => 'e107_plugins/forum/forum.php', 'plugdir' => true, 'expected' => false),
			5  => array('path' => 'e107_plugins/vstore/admin_config.php', 'plugdir' => true, 'expected' => true),
			6  => array('path' => 'e107_plugins/login_menu/config.php', 'plugdir' => true, 'expected' => true),
			7  => array('path' => 'e107_plugins/myplugin/prefs.php', 'plugdir' => true, 'expected' => true),
			8  => array('path' => 'e107_plugins/dtree_menu/dtree_config.php', 'plugdir' => true, 'expected' => true),
			9  => array('path' => 'e107_plugins/myplugin/admin/something.php', 'plugdir' => true, 'expected' => true),
			10 => array('path' => 'e107_plugins/myplugin/bla_admin.php', 'plugdir' => true, 'expected' => true),
			11 => array('path' => 'e107_plugins/myplugin/admin_xxx.php', 'plugdir' => true, 'expected' => true),
		);

		foreach($tests as $index => $var)
		{
			$curPage = basename($var['path']);
			$result = $this->e107->inAdminDir($var['path'], $curPage, $var['plugdir']);
			$this::assertSame($var['expected'], $result, "Failed on index #" . $index);
		}

		// Test legacy override.
		$GLOBALS['eplug_admin'] = true;
		$result = $this->e107->inAdminDir('myplugin.php', 'myplugin.php', true);
		$this::assertTrue($result, "Legacy Override Failed");

		// Test legacy off.
		$GLOBALS['eplug_admin'] = false;
		$result = $this->e107->inAdminDir('myplugin.php', 'myplugin.php', true);
		$this::assertFalse($result);
	}


	public function testFilter_request()
	{

		//	define('e_DEBUG', true);
		//	$_SERVER['QUEST_STRING'] = "mode=main&iframe=1&action=info&src=aWQ9ODgzJnVybD1odHRwcyUzQSUyRiUyRmUxMDcub3JnJTJGZTEwN19wbHVnaW5zJTJGYWRkb25zJTJGYWRkb25zLnBocCUzRmlkJTNEODgzJTI2YW1wJTNCbW9kYWwlM0QxJm1vZGU9YWRkb24mcHJpY2U9";

		//$result = $this->e107::filter_request($test,'QUERY_STRING','_SERVER');

		//	$this->e107->prepare_request();

		// 	$res = null;
		// $this::assertTrue($res);
	}

	/*
			public function testSet_base_path()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testSet_constants()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGet_override_rel()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testGet_override_http()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testSet_paths()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testFix_windows_paths()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testSet_urls()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testSet_urls_deferred()
			{
				$res = null;
				$this::assertTrue($res);
			}
*/
	public function testSet_request()
	{

		$tests = array(

			'mode=main&action=create'                     => 'mode=main&amp;action=create',
			'[debug=counts!]mode=pref_editor&type=vstore' => 'mode=pref_editor&amp;type=vstore',
			'searchquery=šýá&mode=main'                   => 'searchquery=šýá&amp;mode=main',
			'mode=main&action=custom&other[key]=1'        => 'mode=main&amp;action=custom&amp;other[key]=1',
			'searchquery="two words"&mode=main'           => 'searchquery=%22two words%22&amp;mode=main',
			"searchquery='two words'&mode=main"           => "searchquery=%27two words%27&amp;mode=main",
			//
		);

		foreach($tests as $input => $expected)
		{
			$result = $this->e107->set_request(true, $input);
			$this::assertSame($expected, $result);
		}


	}

	/*
				public function testCanCache()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testIsSecure()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGetip()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testIpEncode()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testIpdecode()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testGet_host_name()
				{
					$res = null;
					$this::assertTrue($res);
				}

				public function testParseMemorySize()
				{
					$res = null;
					$this::assertTrue($res);
				}
		*/
	public function testIsInstalled()
	{

		$obj = $this->e107;

		$result = $obj::isInstalled('user');

		$this::assertTrue($result);

		$result = $obj::isInstalled('news');

		$this::assertTrue($result);
	}


	public function testIsCompatible()
	{

		// version => expected
		$testPlugin = array(
			'1'     => false, // assumed incompatible.
			'1.2.3' => false,
			'1.2'   => false,
			'2'     => true, // assumed to work with all versions from 2+
			'2.0'   => true,  // assumed to work with all versions from 2+
			'2.3'   => true,  // assumed to work with all versions from 2.3 onward.
			'2.1.0' => true,
			'2.2.0' => true,
			'2.3.0' => true,
			'2.3.1' => true,
			'1.7b'  => false,
			'2.9'   => false,
			'2.9.2' => false,
			'3'     => false,
		);

		$e107 = $this->e107;

		foreach($testPlugin as $input => $expected)
		{
			$result = $e107::isCompatible($input, 'plugin');
			$this::assertSame($expected, $result);
		}

		$testTheme = array(
			'1'     => true, // assumed incompatible.
			'1.2.3' => true,
			'1.2'   => true,
			'2'     => true, // assumed to work with all versions from 2+
			'2.0'   => true,  // assumed to work with all versions from 2+
			'2.3'   => true,  // assumed to work with all versions from 2.3 onward.
			'2.1.0' => true,
			'2.2.0' => true,
			'2.3.0' => true,
			'2.3.1' => true,
			'1.7b'  => true,
			'2.9'   => false,
			'2.9.2' => false,
			'3'     => false,
		);

		foreach($testTheme as $input => $expected)
		{
			$result = $e107::isCompatible($input, 'theme');
			$this::assertSame($expected, $result);
			//	$ret[$input] = $result;
		}


	}

	public function testIsAllowedHost(): void
	{

		$reflection = new ReflectionClass($this->e107);
		$method = $reflection->getMethod('isAllowedHost');
		$method->setAccessible(true);

		$testCases = [
			'Empty allowed hosts should return true'                      => [
				'allowedHosts' => [],
				'httpHost'     => 'anyhost.com',
				'expected'     => true
			],
			'Exact matching host should return true'                      => [
				'allowedHosts' => ['example.com', 'testsite.org'],
				'httpHost'     => 'example.com',
				'expected'     => true
			],
			'Subdomain matching allowed host should return true'          => [
				'allowedHosts' => ['example.com'],
				'httpHost'     => 'subdomain.example.com',
				'expected'     => true
			],
			'Unrelated host should return false'                          => [
				'allowedHosts' => ['example.com'],
				'httpHost'     => 'unrelated.com',
				'expected'     => false
			],
			'Similar but incorrect subdomain pattern should return false' => [
				'allowedHosts' => ['example.com'],
				'httpHost'     => 'subdomain-example.com',
				'expected'     => false
			],
		];

		foreach($testCases as $scenario => $testCase)
		{
			$result = $method->invoke($this->e107, $testCase['allowedHosts'], $testCase['httpHost']);
			$this::assertSame($testCase['expected'], $result, "Failed scenario: $scenario");
		}
	}




	/*
			public function testIni_set()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testAutoload_register()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testAutoload()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function test__get()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testDestruct()
			{
				$res = null;
				$this::assertTrue($res);
			}

			public function testCoreUpdateAvailable()
			{
				$res = null;
				$this::assertTrue($res);
			}


	*/


/// -----START -------


	/**
	 * Test old-style language file with define()
	 *
	 * @runInSeparateProcess
	 */
	public function testIncludeLanOldStyle()
	{

		$file_content = <<<PHP
    <?php
    define('TEST_LAN_OLD', 'Old Style Test');
    define('TEST_LAN_ANOTHER', 'Another Value');
    PHP;

		$path = $this->createTempLanguageFile($file_content, 'English', 'lan_test_old');

		$result = e107::includeLan($path, false, 'English');

		self::assertEquals(1, $result, 'includeLan should return 1 for successful old-style file inclusion');
		self::assertTrue(defined('TEST_LAN_OLD'), 'TEST_LAN_OLD should be defined');
		self::assertEquals('Old Style Test', constant('TEST_LAN_OLD'), 'TEST_LAN_OLD should have correct value');
		self::assertTrue(defined('TEST_LAN_ANOTHER'), 'TEST_LAN_ANOTHER should be defined');
		self::assertEquals('Another Value', constant('TEST_LAN_ANOTHER'), 'TEST_LAN_ANOTHER should have correct value');
	}

	/**
	 * Test missing file
	 *
	 * @runInSeparateProcess
	 */
	public function testIncludeLanMissingFile()
	{

		$result = e107::includeLan(sys_get_temp_dir() . '/e107_test_languages/NonExistent/lan_missing.php', false, 'English');
		self::assertFalse($result, 'includeLan should return false for missing file');
	}

	/**
	 * Test new-style language file with array return
	 */
	public function testIncludeLanNewStyle()
	{

		$file_content = <<<PHP
    <?php
    return [
        'TEST_LAN_NEW_STYLE' => 'New Style Test',
        'TEST_LAN_ANOTHER_NEW' => 'Another New Value'
    ];
    PHP;

		$path = $this->createTempLanguageFile($file_content, 'English', 'lan_test_new');

		$result = e107::includeLan($path, true, 'English');

		self::assertTrue($result, 'includeLan should return true for new-style file');
		self::assertTrue(defined('TEST_LAN_NEW_STYLE'), 'TEST_LAN_NEW_STYLE should be defined');
		self::assertEquals('New Style Test', constant('TEST_LAN_NEW_STYLE'), 'TEST_LAN_NEW_STYLE should have correct value');
		self::assertTrue(defined('TEST_LAN_ANOTHER_NEW'), 'TEST_LAN_ANOTHER_NEW should be defined');
		self::assertEquals('Another New Value', constant('TEST_LAN_ANOTHER_NEW'), 'TEST_LAN_ANOTHER_NEW should have correct value');
	}


	/**
	 * Test non-English new-style file with English fallback (plugin-style path)
	 *
	 * @runInSeparateProcess
	 */
	public function testIncludeLanNonEnglishWithFallback()
	{

		$english_content = <<<PHP
    <?php
    return [
        'TEST_LAN_FALLBACK' => 'English Fallback',
        'TEST_LAN_SHARED' => 'Shared Value',
        'TEST_LAN_ENGLISH_ONLY' => 'English Only'
    ];
    PHP;
		$english_path = $this->createTempLanguageFile($english_content, 'English', 'lan_test_fallback', 'e107_plugins/testplugin/languages/');

		$spanish_content = <<<PHP
    <?php
    return [
        'TEST_LAN_FALLBACK' => 'Spanish Override',
        'TEST_LAN_SHARED' => 'Spanish Shared'
        // TEST_LAN_ENGLISH_ONLY missing
    ];
    PHP;
		$spanish_path = $this->createTempLanguageFile($spanish_content, 'Spanish', 'lan_test_fallback', 'e107_plugins/testplugin/languages/');

		// Load Spanish first to define constants, then English as fallback
		$result = e107::includeLan($spanish_path, true, 'Spanish');
		e107::includeLan($english_path, true, 'English');

		self::assertTrue($result, 'includeLan should return true for Spanish file');
		self::assertTrue(defined('TEST_LAN_FALLBACK'), 'TEST_LAN_FALLBACK should be defined');
		self::assertEquals('Spanish Override', constant('TEST_LAN_FALLBACK'), 'TEST_LAN_FALLBACK should use Spanish value');
		self::assertTrue(defined('TEST_LAN_SHARED'), 'TEST_LAN_SHARED should be defined');
		self::assertEquals('Spanish Shared', constant('TEST_LAN_SHARED'), 'TEST_LAN_SHARED should use Spanish value');
		self::assertTrue(defined('TEST_LAN_ENGLISH_ONLY'), 'TEST_LAN_ENGLISH_ONLY should be defined');
		self::assertEquals('English Only', constant('TEST_LAN_ENGLISH_ONLY'), 'TEST_LAN_ENGLISH_ONLY should fall back to English');
	}

	/**
	 * Test non-English new-style file with English fallback (custom path)
	 *
	 * @runInSeparateProcess
	 */
	public function testIncludeLanCustomPathWithFallback()
	{

		$english_content = <<<PHP
    <?php
    return [
        'TEST_LAN_CUSTOM' => 'English Custom',
        'TEST_LAN_SHARED_CUSTOM' => 'Shared Custom',
        'TEST_LAN_ENGLISH_ONLY_CUSTOM' => 'English Only Custom'
    ];
    PHP;
		$english_path = $this->createTempLanguageFile($english_content, 'English', 'Spanish_global', 'folder/');

		$spanish_content = <<<PHP
    <?php
    return [
        'TEST_LAN_CUSTOM' => 'Spanish Custom Override',
        'TEST_LAN_SHARED_CUSTOM' => 'Spanish Shared Custom'
        // TEST_LAN_ENGLISH_ONLY_CUSTOM missing
    ];
    PHP;
		$spanish_path = $this->createTempLanguageFile($spanish_content, 'Spanish', 'Spanish_global', 'folder/');

		// Load Spanish first to define constants, then English as fallback
		$result = e107::includeLan($spanish_path, true, 'Spanish');
		e107::includeLan($english_path, true, 'English');

		self::assertTrue($result, 'includeLan should return true for Spanish file with custom path');
		self::assertTrue(defined('TEST_LAN_CUSTOM'), 'TEST_LAN_CUSTOM should be defined');
		self::assertEquals('Spanish Custom Override', constant('TEST_LAN_CUSTOM'), 'TEST_LAN_CUSTOM should use Spanish value');
		self::assertTrue(defined('TEST_LAN_SHARED_CUSTOM'), 'TEST_LAN_SHARED_CUSTOM should be defined');
		self::assertEquals('Spanish Shared Custom', constant('TEST_LAN_SHARED_CUSTOM'), 'TEST_LAN_SHARED_CUSTOM should use Spanish value');
		self::assertTrue(defined('TEST_LAN_ENGLISH_ONLY_CUSTOM'), 'TEST_LAN_ENGLISH_ONLY_CUSTOM should be defined');
		self::assertEquals('English Only Custom', constant('TEST_LAN_ENGLISH_ONLY_CUSTOM'), 'TEST_LAN_ENGLISH_ONLY_CUSTOM should fall back to English');
	}

	/**
	 * Test includeLanArray directly with reflection
	 *
	 * @runInSeparateProcess
	 */
	public function testIncludeLanArrayDirectly()
	{

		$english_content = <<<PHP
    <?php
    return [
        'TEST_LAN_DIRECT' => 'English Direct',
        'TEST_LAN_SHARED_DIRECT' => 'Shared Direct',
        'TEST_LAN_ENGLISH_ONLY_DIRECT' => 'English Only Direct'
    ];
    PHP;
		$english_path = $this->createTempLanguageFile($english_content, 'English', 'lan_test_direct', 'e107_plugins/testplugin/languages/');

		$spanish_content = <<<PHP
    <?php
    return [
        'TEST_LAN_DIRECT' => 'Spanish Direct Override',
        'TEST_LAN_SHARED_DIRECT' => 'Spanish Shared Direct'
        // TEST_LAN_ENGLISH_ONLY_DIRECT missing
    ];
    PHP;
		$spanish_path = $this->createTempLanguageFile($spanish_content, 'Spanish', 'lan_test_direct', 'e107_plugins/testplugin/languages/');

		// Use ReflectionClass to access private static method
		$reflection = new ReflectionClass('e107');
		$method = $reflection->getMethod('includeLanArray');
		$method->setAccessible(true);

		// Load Spanish first, then English as fallback
		$spanish_terms = require($spanish_path);
		$method->invoke(null, $spanish_terms, $spanish_path, 'Spanish');

		$english_terms = require($english_path);
		$method->invoke(null, $english_terms, $english_path, 'English');

		self::assertTrue(defined('TEST_LAN_DIRECT'), 'TEST_LAN_DIRECT should be defined');
		self::assertEquals('Spanish Direct Override', constant('TEST_LAN_DIRECT'), 'TEST_LAN_DIRECT should use Spanish value');
		self::assertTrue(defined('TEST_LAN_SHARED_DIRECT'), 'TEST_LAN_SHARED_DIRECT should be defined');
		self::assertEquals('Spanish Shared Direct', constant('TEST_LAN_SHARED_DIRECT'), 'TEST_LAN_SHARED_DIRECT should use Spanish value');
		self::assertTrue(defined('TEST_LAN_ENGLISH_ONLY_DIRECT'), 'TEST_LAN_ENGLISH_ONLY_DIRECT should be defined');
		self::assertEquals('English Only Direct', constant('TEST_LAN_ENGLISH_ONLY_DIRECT'), 'TEST_LAN_ENGLISH_ONLY_DIRECT should fall back to English');
	}

	/**
	 * Test Spanish old-style language file with English array fallback
	 */
	public function testIncludeLanSpanishOldStyleWithEnglishArrayFallback()
	{

		$english_content = <<<PHP
    <?php
    return [
        'TEST_LAN_SPANISH_ENGLISH_FALLBACK_EN' => 'English Fallback',
        'TEST_LAN_SHARED_SPANISH_ENGLISH_EN' => 'Shared English Value',
        'TEST_LAN_ENGLISH_ONLY_SPANISH_ENGLISH' => 'English Only'
    ];
    PHP;
		$english_path = $this->createTempLanguageFile($english_content, 'English', 'lan_test_spanish_fallback', 'e107_plugins/testplugin/languages/');

		$spanish_content = <<<PHP
    <?php
    define('TEST_LAN_SPANISH_ENGLISH_FALLBACK_ES', 'Spanish Override');
    define('TEST_LAN_SHARED_SPANISH_ENGLISH_ES', 'Spanish Shared');
    // TEST_LAN_ENGLISH_ONLY_SPANISH_ENGLISH not defined
    PHP;
		$spanish_path = $this->createTempLanguageFile($spanish_content, 'Spanish', 'lan_test_spanish_fallback', 'e107_plugins/testplugin/languages/');

		// Load Spanish first (old-style), then English as fallback (array)
		$result = e107::includeLan($spanish_path, true, 'Spanish');
		e107::includeLan($english_path, true, 'English');

		self::assertEquals(1, $result, 'includeLan should return 1 for successful old-style Spanish file inclusion');
		self::assertTrue(defined('TEST_LAN_SPANISH_ENGLISH_FALLBACK_ES'), 'TEST_LAN_SPANISH_ENGLISH_FALLBACK_ES should be defined');
		self::assertEquals('Spanish Override', constant('TEST_LAN_SPANISH_ENGLISH_FALLBACK_ES'), 'TEST_LAN_SPANISH_ENGLISH_FALLBACK_ES should use Spanish value');
		self::assertTrue(defined('TEST_LAN_SHARED_SPANISH_ENGLISH_ES'), 'TEST_LAN_SHARED_SPANISH_ENGLISH_ES should be defined');
		self::assertEquals('Spanish Shared', constant('TEST_LAN_SHARED_SPANISH_ENGLISH_ES'), 'TEST_LAN_SHARED_SPANISH_ENGLISH_ES should use Spanish value');
		self::assertTrue(defined('TEST_LAN_ENGLISH_ONLY_SPANISH_ENGLISH'), 'TEST_LAN_ENGLISH_ONLY_SPANISH_ENGLISH should be defined');
		self::assertEquals('English Only', constant('TEST_LAN_ENGLISH_ONLY_SPANISH_ENGLISH'), 'TEST_LAN_ENGLISH_ONLY_SPANISH_ENGLISH should fall back to English');
	}


	/**
	 * Helper to create a temporary language file
	 */
	private function createTempLanguageFile($content, $lang, $file, $prefix = 'e107_test_languages/')
	{

		$dir = sys_get_temp_dir() . "/$prefix$lang/";
		if(!is_dir($dir))
		{
			mkdir($dir, 0777, true);
		}
		$path = tempnam($dir, $file); // Creates unique file with prefix
		file_put_contents($path, $content);
		$this->tempFiles[] = $path; // Track for cleanup

		return $path;
	}

/// ------ END --------

}
