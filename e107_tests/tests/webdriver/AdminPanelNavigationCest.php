<?php

/**
 * The admin side-navigation carries several kinds of link in one container, and only one of them switches an in-page
 * panel. This asks a real browser which of them the panel switcher touches.
 *
 * Regression cover for `3658fbfe24`, which scoped that switcher to `#admin-prefs`. That is the `<body>` id of core
 * prefs.php, so the switcher bound on one page and every other legacy admin page lost its panels. Reported on
 * discussion #6097 against the list_new plugin.
 *
 * Master only: release/v2.3.x has no webdriver suite.
 */
class AdminPanelNavigationCest
{
	const PROBE = 'e107_tests_admin_nav_probe.php';

	const LIST_NEW = '/e107_plugins/list_new/admin_list_config.php';
	const BLANK = '/e107_plugins/_blank/admin_config.php';
	const MENUS = '/e107_admin/menus.php';
	const DOCS = '/e107_admin/docs.php';
	const PREFS = '/e107_admin/prefs.php';

	const PANE_CLASS = 'e-nav-pane';

	public function _before(\WebDriverTester $I)
	{
		$I->writeAppFile(self::PROBE, $this->probeSource());
		$I->amOnPage('/'.self::PROBE.'?act=install');
		$I->see('PROBE_OK');
		$I->loginAsAdmin();
	}

	public function _after(\WebDriverTester $I)
	{
		$I->amOnPage('/'.self::PROBE.'?act=uninstall');
		$I->deleteAppFile(self::PROBE);
	}

	/**
	 * The fault from #6097. Every list_new panel carries e-hideme, including the one its admin menu declares active,
	 * so with nothing to reveal it the page body is empty.
	 */
	public function aLegacyPluginPageOpensItsDeclaredPanel(\WebDriverTester $I)
	{
		$I->wantTo('load a legacy plugin admin page and see its declared panel');
		$I->amOnPage(self::LIST_NEW);

		$I->seeElement('#list-new-recent-page');
		$I->dontSeeElement('#list-new-recent-menu');
		$I->dontSeeElement('#list-new-new-page');
		$I->dontSeeElement('#list-new-new-menu');
	}

	public function aLegacyPluginPageSwitchesPanelsOnClick(\WebDriverTester $I)
	{
		$I->wantTo('click a side-navigation entry and see the panels swap');
		$I->amOnPage(self::LIST_NEW);
		$I->seeElement('#list-new-recent-page');

		$I->click("//a[@href='#list-new-recent-menu']");
		$I->waitForElementVisible('#list-new-recent-menu', 10);
		$I->dontSeeElement('#list-new-recent-page');
	}

	public function aRememberedPanelIsRestoredFromTheFragment(\WebDriverTester $I)
	{
		$I->wantTo('reopen the panel a #nav- fragment names');
		$I->amOnPage(self::LIST_NEW.'#nav-list-new-new-menu');
		$I->seeElementInDOM('#list-new-new-menu');
		$I->waitForElementVisible('#list-new-new-menu', 10);
		$I->dontSeeElement('#list-new-recent-page');
	}

	/**
	 * A fragment can outlive the panel it names: a bookmark, a shared URL, an entry hidden from this admin by perms,
	 * or a plugin that renamed a panel across an upgrade. Falling through to the declared panel keeps the page usable
	 * instead of reproducing the blank body this whole change is about.
	 */
	public function aFragmentNamingNoPanelStillLeavesThePageUsable(\WebDriverTester $I)
	{
		$I->wantTo('load a stale #nav- fragment and still get a panel');
		$I->amOnPage(self::LIST_NEW.'#nav-list-new-does-not-exist');
		$I->seeElementInDOM('#list-new-recent-page');
		$I->waitForElementVisible('#list-new-recent-page', 10);
	}

	/**
	 * Core prefs.php is the one page the broken selector still bound to, so it must not change.
	 */
	public function corePreferencesStillSwitchesPanels(\WebDriverTester $I)
	{
		$I->wantTo('confirm core preferences is unaffected');
		$I->amOnPage(self::PREFS);

		$I->seeElement('#core-prefs-main');
		$I->dontSeeElement('#core-prefs-email');

		$I->click("//a[@href='#core-prefs-email']");
		$I->waitForElementVisible('#core-prefs-email', 10);
		$I->dontSeeElement('#core-prefs-main');
	}

	/**
	 * A pin, not a regression test: no shipped page carries a collapsing parent and a marked panel link in the same
	 * navigation, so the switcher does not reach this page on either side of the fix. It is here so that widening the
	 * marker later cannot quietly re-break what `3658fbfe24` was protecting.
	 */
	public function aCollapsingSubMenuStillToggles(\WebDriverTester $I)
	{
		$I->wantTo('toggle a collapsing sub-menu without the panel switcher interfering');
		$I->amOnPage(self::BLANK);

		$I->seeElementInDOM('#sub-main-custom');
		$I->dontSeeElement('#sub-main-custom');
		$I->dontSeeElement('#plugin-navigation-main-custom.'.self::PANE_CLASS);

		$I->click('#plugin-navigation-main-custom');
		$I->waitForElementVisible('#sub-main-custom', 10);
	}

	/**
	 * Menu Manager layout links and docs.php help items are `#anchor` links too, but each brings its own handler. An
	 * href-shaped guard would capture them; the marker must not.
	 */
	public function otherAnchorNavigationIsLeftAlone(\WebDriverTester $I)
	{
		$I->wantTo('confirm the marker is absent from navigation owned by other handlers');

		// Without this, every absence below would hold just as well on a build that marks nothing at all.
		$I->amOnPage(self::LIST_NEW);
		$I->seeElementInDOM('.plugin-navigation a.'.self::PANE_CLASS);

		$I->amOnPage(self::MENUS);
		$I->seeElement('a.menuManagerSelect');
		$I->dontSeeElementInDOM('a.menuManagerSelect.'.self::PANE_CLASS);

		$I->amOnPage(self::DOCS);
		$I->seeElement('.docs-item');
		$I->dontSeeElementInDOM('.plugin-navigation a.'.self::PANE_CLASS);
		$I->assertSame(1, (int) $I->executeJS("
			var items = document.querySelectorAll('.docs-item'), shown = 0;
			for(var i = 0; i < items.length; i++) { if(items[i].offsetParent !== null) { shown++; } }
			return shown;
		"), 'docs.php shows exactly one help item');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$plugins = array('list_new', '_blank');

e107::getPlug()->clearCache();
$plugin = e107::getPlugin();

foreach($plugins as $folder)
{
	if(!e107::getDb()->createQueryBuilder()->from('plugin')->where('plugin_path', $folder)->count())
	{
		$plugin->update_plugins_table('update');
		break;
	}
}

foreach($plugins as $folder)
{
	if($act === 'install' && !e107::isInstalled($folder))
	{
		$plugin->install_plugin_xml($folder, 'install');
	}
	elseif($act === 'uninstall' && e107::isInstalled($folder))
	{
		$plugin->install_plugin_xml($folder, 'uninstall', array('delete_tables' => true));
	}
}

e107::getPlug()->clearCache()->buildAddonPrefLists();

foreach(glob(e_CACHE_CONTENT.'S_Config_*.cache.php') ?: array() as $file)
{
	@unlink($file);
}

foreach($plugins as $folder)
{
	if(e107::isInstalled($folder) !== ($act === 'install'))
	{
		echo "FAILED {$folder}\n";
		exit;
	}
}

echo "PROBE_OK {$act}\n";
PHP;
	}
}
