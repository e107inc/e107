<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * Regression coverage for issues #4968 and #6044: both registry guards in
 * login_menu_class compared e107::getRegistry() against FALSE, but a registry
 * miss hands back the $default, which is null. Every call therefore returned
 * null on its first line, the e_loginbox.php scan never ran, and the login
 * menu's external links and statistics were unreachable.
 */
class login_menu_classTest extends \Codeception\Test\Unit
{
	const FIXTURE_PLUGIN = '_login_menu_test_plugin';

	/** @var array */
	protected $savedInstalled;

	protected function _before()
	{
		require_once(e_PLUGIN . 'login_menu/login_menu_class.php');

		$this->forgetTheScan();

		$this->savedInstalled = e107::getConfig()->getPref('plug_installed');
	}

	protected function _after()
	{
		e107::getConfig()->setPref('plug_installed', $this->savedInstalled);

		$this->forgetTheScan();
		$this->removeFixturePlugin();
	}

	public function testParseExternalListScansInsteadOfReturningTheRegistryMiss()
	{
		$this->assertTrue(is_array($this->loginMenu()->parse_external_list(true, false)));
	}

	public function testParseExternalListCachesWhatItScanned()
	{
		$lmc = $this->loginMenu();

		$first = $lmc->parse_external_list(true, false);

		$this->assertTrue(is_array(e107::getRegistry('loginbox_elist_1')));
		$this->assertSame($first, $lmc->parse_external_list(true, false));
	}

	public function testGetPluginDataResolvesTheNameTheConfigScreenPrints()
	{
		$data = $this->loginMenu()->get_plugin_data('gallery');

		$this->assertArrayHasKey('eplug_name', $data);
		$this->assertNotEmpty($data['eplug_name']);
	}

	public function testGetPluginDataIsEmptyForADirectoryThatIsNotAPlugin()
	{
		$this->assertSame(array(), $this->loginMenu()->get_plugin_data('no_such_plugin'));
	}

	/**
	 * The configuration screen exists to list plugins that ship an
	 * e_loginbox.php, which is what #6044 reports it never does.
	 */
	public function testAnInstalledPluginsLoginboxIsRead()
	{
		$this->writeFixturePlugin();
		e107::getConfig()->setPref('plug_installed/' . self::FIXTURE_PLUGIN, '1.0');

		$ret = $this->loginMenu(self::FIXTURE_PLUGIN)->parse_external_list(true, false);

		$this->assertArrayHasKey('links', $ret);
		$this->assertArrayHasKey(self::FIXTURE_PLUGIN, $ret['links']);
		$this->assertSame('Test link', $ret['links'][self::FIXTURE_PLUGIN][0]['link_label']);
	}

	/**
	 * The stored preference is a list of folder names that arrived as POST
	 * array keys, and each one is concatenated into an include() path.
	 */
	public function testAnUninstalledFolderNamedByThePreferenceIsNotIncluded()
	{
		$this->writeFixturePlugin();

		$lmc = $this->loginMenu(self::FIXTURE_PLUGIN);

		$this->assertSame(array(), $lmc->parse_external_list(true, false));
	}

	/**
	 * get_external_list() sorts by the stored order, and the sort ran on a
	 * variable the loop above it had never assigned when nothing matched.
	 */
	public function testGetExternalListSortsWhenNoInstalledPluginOffersALoginbox()
	{
		$lmc = $this->loginMenu('no_such_plugin');

		$this->assertSame(array(), $lmc->get_external_list());
	}

	/**
	 * The constructor reads the shared menu preferences, which other tests in
	 * the suite also read, so the preferences are injected instead.
	 *
	 * @param string $externalLinks
	 * @return login_menu_class
	 */
	private function loginMenu($externalLinks = '')
	{
		try
		{
			return $this->make('login_menu_class', array(
				'loginPrefs' => array(
					'external_links' => $externalLinks,
					'external_stats' => '',
				),
			));
		}
		catch (Exception $e)
		{
			self::fail($e->getMessage());
		}
	}

	private function forgetTheScan()
	{
		e107::setRegistry('loginbox_elist_0', null);
		e107::setRegistry('loginbox_elist_1', null);
	}

	private function writeFixturePlugin()
	{
		$dir = e_PLUGIN . self::FIXTURE_PLUGIN;

		mkdir($dir, 0755, true);
		file_put_contents(
			$dir . '/e_loginbox.php',
			"<?php\n\$lbox_links[] = array('link_label' => 'Test link',"
			. " 'link_url' => 'http://example.com/');\n"
		);
	}

	private function removeFixturePlugin()
	{
		$dir = e_PLUGIN . self::FIXTURE_PLUGIN;

		if (is_file($dir . '/e_loginbox.php'))
		{
			unlink($dir . '/e_loginbox.php');
		}
		if (is_dir($dir))
		{
			rmdir($dir);
		}
	}
}
