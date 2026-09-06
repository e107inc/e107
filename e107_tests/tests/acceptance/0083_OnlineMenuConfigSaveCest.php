<?php

/**
 * Which keys saving the online menu's configuration screen puts on the shared
 * `menu` preference row.
 *
 * The screen renders six fields, and the save handler used to store every field
 * the request carried instead. One arrives on every save without anybody typing
 * it: the token injector adds e-token to every admin form. The Update button's
 * own field arrives too, and a key with a slash in it lands inside another
 * menu's subtree, because e_model treats a slash as an array path.
 *
 * Asserted through the application's own preference handler, read back in a
 * fresh process, because a value observed in the process that wrote it proves
 * nothing about what was stored.
 *
 * @see e107_plugins/online/config.php the save handler under test
 */
class OnlineMenuConfigSaveCest
{
	const CONFIG_PATH = '/e107_plugins/online/config.php';

	/** The six fields the screen renders, with the values this test types into them. */
	const OWNED_PREFS = array(
		'online_caption'                  => 'e107 tests online',
		'online_ls_caption'               => 'e107 tests last seen',
		'online_ls_amount'                => '12',
		'online_show_guests'              => '1',
		'online_show_memberlist'          => '1',
		'online_show_memberlist_extended' => '0',
	);

	/**
	 * Fields the screen does not own, one of them addressing another menu's
	 * subtree, standing in for whatever else a request may carry.
	 */
	const SMUGGLED_PREFS = array(
		'smuggled'            => 'stored',
		'login_menu/new_news' => '99',
	);

	/** What the seed puts at login_menu/new_news, and what has to still be there afterwards. */
	const LOGIN_MENU_SEED = '0';

	public function _before(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreMenuPrefs();
		$I->dropMenuPrefProbe();
	}

	public function savingTheOnlineMenuStoresOnlyItsOwnKeys(AcceptanceTester $I)
	{
		$I->wantTo('save the online menu configuration without filing the rest of the request as preferences');

		$I->haveMenuPrefs(
			array_merge(array_keys(self::OWNED_PREFS), array('update_menu', 'e-token', 'smuggled', 'login_menu')),
			array('login_menu/new_news' => self::LOGIN_MENU_SEED));

		$this->saveConfig($I, array_merge(self::OWNED_PREFS, self::SMUGGLED_PREFS));

		$menu = $I->grabMenuPrefs();

		foreach(self::OWNED_PREFS as $key => $value)
		{
			$I->assertSame($value, isset($menu[$key]) ? $menu[$key] : null,
				'The save must have landed for the next assertions to mean anything.');
		}

		foreach(array('e-token', 'update_menu', 'smuggled') as $key)
		{
			$I->assertArrayNotHasKey($key, $menu,
				'Only the keys the screen owns may reach the shared menu row, and "'.$key.'" is not one of them.');
		}

		$login = isset($menu['login_menu']) ? $menu['login_menu'] : array();

		$I->assertSame(self::LOGIN_MENU_SEED, isset($login['new_news']) ? $login['new_news'] : null,
			'A posted key with a slash in it must not be able to write into another menu\'s settings.');
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
}
