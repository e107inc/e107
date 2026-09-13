<?php

/**
 * What saving the login menu's configuration screen does to the shared `menu`
 * preference row.
 *
 * Every menu keeps its settings in that one row: the online and lastseen menus
 * read flat keys off it, and the login menu owns the login_menu subtree. The
 * save handler used to call {@see e_pref::reset()}, which empties the object,
 * and save() then wrote the emptied object wholesale, so pressing Save wrote a
 * row holding login_menu and nothing else. It also copied $_POST['pref'] wholesale, storing any key the
 * request carried under login_menu/.
 *
 * Both faults are asserted through the application's own preference handler,
 * read back by a probe in a fresh process, because a value observed in the
 * process that wrote it proves nothing about what was stored.
 *
 * @see e107_plugins/login_menu/config.php the save handler under test
 */
class LoginMenuConfigSaveCest
{
	const CONFIG_PATH = '/e107_plugins/login_menu/config.php';

	/** The statistics rows render only for an installed core plugin, and this is the one a stock docroot can gain cheaply. */
	const STATS_PLUGIN = 'chatbox_menu';

	/** Flat keys the lastseen menu reads off the shared row, as this Cest seeds them. */
	const SIBLING_PREFS = array(
		'online_ls_amount'  => '15',
		'online_ls_caption' => 'e107 tests last seen',
	);

	/** Everything on the shared row this Cest may disturb: the siblings above, and the subtree the screen owns. */
	const OWNED_PREFS = array('online_ls_amount', 'online_ls_caption', 'login_menu');

	public function _before(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreMenuPrefs();
		$I->dropMenuPrefProbe();
		$I->dropPluginInstall(self::STATS_PLUGIN);
		$I->dropPluginProbe();
	}

	public function savingTheLoginMenuKeepsEveryOtherMenusPreferences(AcceptanceTester $I)
	{
		$I->wantTo('save the login menu configuration without losing another menu\'s settings');

		$this->seedMenuRow($I);

		$this->saveConfig($I, array('pref' => array('new_news' => '1')));

		$menu = $I->grabMenuPrefs();
		$login = isset($menu['login_menu']) ? $menu['login_menu'] : array();

		$I->assertSame('1', isset($login['new_news']) ? $login['new_news'] : null,
			'The save must have landed for the next assertion to mean anything.');

		foreach(self::SIBLING_PREFS as $key => $value)
		{
			$I->assertSame($value, isset($menu[$key]) ? $menu[$key] : null,
				'Saving the login menu configuration must not discard another menu\'s "'.$key.'" preference.');
		}
	}

	public function savingTheLoginMenuStoresOnlyItsOwnKeys(AcceptanceTester $I)
	{
		$I->wantTo('see the save store only the keys the screen owns');

		$this->seedMenuRow($I);

		$this->saveConfig($I, array('pref' => array(
			'new_news' => '1',
			'smuggled' => 'stored',
		)));

		$menu = $I->grabMenuPrefs();
		$login = isset($menu['login_menu']) ? $menu['login_menu'] : array();

		$I->assertSame('1', isset($login['new_news']) ? $login['new_news'] : null,
			'The save must have landed for the next assertion to mean anything.');

		$keys = array_keys($login);
		sort($keys);

		$I->assertSame(
			array('external_links', 'external_stats', 'new_comments', 'new_members', 'new_news'),
			$keys,
			'The login_menu subtree must hold exactly the five keys the screen owns.');
	}

	public function savingAStatisticsCheckboxTicksItOnTheFormThatComesBack(AcceptanceTester $I)
	{
		$I->wantTo('see the box I just ticked come back ticked');

		$this->seedMenuRow($I);
		$I->havePluginInstalled(self::STATS_PLUGIN);

		$this->saveConfig($I, array(
			'pref'           => array('new_news' => '1'),
			'external_stats' => array(self::STATS_PLUGIN => '1'),
		));

		$form = $I->grabPageSource();

		$menu = $I->grabMenuPrefs();
		$login = isset($menu['login_menu']) ? $menu['login_menu'] : array();

		$I->assertSame(self::STATS_PLUGIN, isset($login['external_stats']) ? $login['external_stats'] : null,
			'The save must have landed for the next assertion to mean anything.');

		$I->assertStringContainsString(
			'name="external_stats['.self::STATS_PLUGIN.']" value="1" checked="checked"',
			$form,
			'The form returned by the save must show the statistics box ticked, or the next save writes the pre-save state back.');
	}

	// -----------------------------------------------------------------
	// helpers
	// -----------------------------------------------------------------

	/**
	 * Submit the configuration form the way the browser does, with the fields
	 * under test layered on top.
	 *
	 * @param AcceptanceTester $I
	 * @param array $fields
	 * @return void
	 */
	private function saveConfig(AcceptanceTester $I, array $fields)
	{
		$fields['update_menu'] = 'Save';
		$fields['e-token'] = $I->grabFreshAdminToken(self::CONFIG_PATH);

		$I->sendPostRequest(self::CONFIG_PATH, $fields);
	}

	/**
	 * The row as this Cest needs it before a save: the sibling menu's settings,
	 * and one key inside the subtree the screen owns.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function seedMenuRow(AcceptanceTester $I)
	{
		$I->haveMenuPrefs(self::OWNED_PREFS,
			array_merge(self::SIBLING_PREFS, array('login_menu/new_forum' => '1')));
	}
}
