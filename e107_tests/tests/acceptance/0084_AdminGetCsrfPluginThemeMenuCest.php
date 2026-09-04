<?php

/**
 * Four admin operations acted on a bare GET, and e107's CSRF guard does not
 * police a GET: e_session::isStateChangingRequest() returns true only for POST,
 * so attest() returns early on every GET that carries no e-token at all. What
 * stands between an attacker's <img> tag and one of these operations is
 * therefore whatever the endpoint does for itself.
 *
 * plugin.php's Git sync ran `git reset --hard` and `git pull` in a plugin
 * folder; the plugin builder wrote a PHP file into e_PLUGIN; theme.php copied a
 * theme tree to an attacker-named folder and unlinked the admin files inside it;
 * and the Menu Manager took a menu out of the site's layout.
 *
 * The e-token in a query string is e107's established marker for a
 * state-changing GET, which plugin.php's four other action pages, theme.php's
 * download page and language.php already use: the endpoint tests that one is
 * present and attest() decides whether it is the right one. The last case here
 * asserts that second half, because the presence test relies on it.
 *
 * Every control follows what the admin page publishes for itself rather than a
 * URL of the test's own, because a guard that refuses ordinary admin navigation
 * would be worse than the hole it closes.
 *
 * @see e107_handlers/session_handler.php e_core_session::attest()
 */
class AdminGetCsrfPluginThemeMenuCest
{
	const PLUGINS = '/e107_admin/plugin.php';
	const THEMES = '/e107_admin/theme.php';
	const MENUS = '/e107_admin/menus.php';

	/** An installed plugin, so pullPage() gets past its e107::isInstalled() test. */
	const PLUGGED = 'news';

	/** A plugin folder that ships no <folder>_sql.php, which is what the builder writes. */
	const BUILT = 'admin_menu';

	/** A shipped theme to copy from, and the folder the copy would land in. */
	const THEME_SRC = 'bootstrap5';
	const THEME_COPY = 'e107_tests_p84_themecopy';

	/** menu_path is numeric so menuModify() does not prune the row as an orphan. */
	const MENU_NAME = 'e107_tests_p84_menu';
	const MENU_PATH = '999999';

	/** A distinctive fragment shared by the four refusal strings. */
	const REFUSED = 'no security token';

	/** git's own answer, which only reaches the page if gitPull() ran. */
	const PULLED = 'not a git repository';

	/** The tab caption step3() renders once the table's CREATE statement is on disk. */
	const BUILT_TABLE = 'Table: user';

	/** attest() answers a token it cannot validate with this, whatever the method. */
	const UNAUTHORIZED = 'Unauthorized access!';

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
	}

	/**
	 * gitPull() shell_execs `git reset --hard` and `git pull` in the plugin
	 * folder, so a hostile page could discard an administrator's local edits and
	 * pull whatever the configured remote is serving today.
	 */
	public function aTokenlessGetDoesNotPullThePluginFromGit(AcceptanceTester $I)
	{
		$I->amOnPage(self::PLUGINS . '?mode=installed&action=pull&path=' . self::PLUGGED);

		$I->dontSeeInSource(self::PULLED);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * The same request with a token reaches git, which is the control for the
	 * guard: the Git sync icon in the plugin list carries one already.
	 */
	public function aPluginPullCarryingATokenStillReachesGit(AcceptanceTester $I)
	{
		$token = $I->grabFreshAdminToken(self::PLUGINS . '?mode=installed&action=list');

		$I->amOnPage(self::PLUGINS . '?mode=installed&action=pull&path=' . self::PLUGGED
			. '&e-token=' . urlencode($token));

		$I->dontSeeInSource(self::REFUSED);
	}

	/**
	 * e_pluginbuilder::step3() writes <plugin>_sql.php into e_PLUGIN from a GET.
	 * The content is the CREATE TABLE statement of any table the request names,
	 * so a plugin the site has installed gains a file that creates tables the
	 * administrator never asked for.
	 */
	public function aTokenlessGetDoesNotWriteAPluginSqlFile(AcceptanceTester $I)
	{
		$I->amOnPage(self::PLUGINS . '?action=build&newplugin=' . self::BUILT . '&step=3&build=user');

		$I->dontSeeInSource(self::BUILT_TABLE);
		$I->seeInSource(self::REFUSED);

		$I->amOnPage('/e107_plugins/' . self::BUILT . '/' . self::BUILT . '_sql.php');
		$I->seeResponseCodeIs(404);
	}

	/**
	 * The builder's own two steps are the control. Step 1 publishes the token,
	 * step 2 carries it forward in the hidden fields it copies out of the query
	 * string, and step 3 then writes the file it always wrote.
	 */
	public function theBuildersOwnStepsStillWriteThePluginSqlFile(AcceptanceTester $I)
	{
		$I->amOnPage(self::PLUGINS . '?mode=create&action=build');
		$I->submitForm('#createplugin', array('newplugin' => self::BUILT), 'step');

		$I->submitForm('#buildtab', array('build' => 'user'), 'step');

		$I->dontSeeInSource(self::REFUSED);
		$I->seeInSource(self::BUILT_TABLE);
	}

	/**
	 * copyTheme() copies a whole theme tree to a folder the query string names
	 * and then unlinks every admin_* file inside the copy.
	 */
	public function aTokenlessGetDoesNotCopyATheme(AcceptanceTester $I)
	{
		$I->amOnPage(self::THEMES . '?mode=convert&action=main&src=' . self::THEME_SRC
			. '&newtheme=' . self::THEME_COPY);

		$I->seeInSource(self::REFUSED);

		$I->amOnPage('/e107_themes/' . self::THEME_COPY . '/theme.xml');
		$I->seeResponseCodeIs(404);
	}

	/**
	 * The create form stays a GET form, so the control submits the form the
	 * Theme Manager renders rather than a URL of the test's own: the token has
	 * to reach copyTheme() through the form's own hidden field.
	 */
	public function theThemeManagersOwnFormStillCopiesATheme(AcceptanceTester $I)
	{
		$I->amOnPage(self::THEMES . '?mode=convert&action=main');
		$I->submitForm('#copytheme', array('src' => self::THEME_SRC, 'newtheme' => self::THEME_COPY), 'step');

		$I->dontSeeInSource(self::REFUSED);

		$I->amOnPage('/e107_themes/' . self::THEME_COPY . '/theme.xml');
		$I->seeResponseCodeIs(200);
	}

	/**
	 * menuDeactivate() takes a menu out of its location, or deletes the row
	 * outright when a spare copy is already parked at location 0, and then
	 * renumbers everything below it.
	 */
	public function aTokenlessGetDoesNotDeactivateAMenu(AcceptanceTester $I)
	{
		$menuId = $this->haveMenuInLayout($I);

		$I->amOnPage(self::MENUS . '?mode=deac&id=' . $menuId);

		$I->seeInSource(self::REFUSED);
		$I->seeInDatabase('e107_menus', array('menu_id' => $menuId, 'menu_location' => 1));
	}

	/**
	 * The delete icon the Menu Manager draws over the layout is the only thing
	 * in core that builds mode=deac, so it is the one link that has to deliver
	 * the token the endpoint now asks for.
	 */
	public function theMenuManagersDeleteLinkCarriesAToken(AcceptanceTester $I)
	{
		$menuId = $this->haveMenuInLayout($I);

		$I->assertStringContainsString('e-token=', $this->menuManagerDeleteLink($I, $menuId));
	}

	/**
	 * The control follows that link exactly as published, whatever it carries,
	 * so it holds whether or not the guard is in place.
	 */
	public function theMenuManagersOwnDeleteLinkStillDeactivates(AcceptanceTester $I)
	{
		$menuId = $this->haveMenuInLayout($I);

		$I->amOnPage($this->menuManagerDeleteLink($I, $menuId));

		$I->dontSeeInDatabase('e107_menus', array('menu_id' => $menuId, 'menu_location' => 1));
	}

	/**
	 * Presence is all the endpoints test; whether the value is the right one is
	 * attest()'s half of the job. Both halves are needed, so assert the second.
	 */
	public function aGetCarryingTheWrongTokenIsRefused(AcceptanceTester $I)
	{
		$I->amOnPage(self::PLUGINS . '?mode=installed&action=pull&path=' . self::PLUGGED
			. '&e-token=not-even-close');

		$I->seeInSource(self::UNAUTHORIZED);
		$I->dontSeeInSource(self::PULLED);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return int menu_id of a menu sitting in area 1 of the default layout
	 */
	private function haveMenuInLayout(AcceptanceTester $I)
	{
		return $I->haveInDatabase('e107_menus', array(
			'menu_name'     => self::MENU_NAME,
			'menu_location' => 1,
			'menu_order'    => 99,
			'menu_class'    => '0',
			'menu_pages'    => '',
			'menu_path'     => self::MENU_PATH,
			'menu_layout'   => '',
			'menu_parms'    => '',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $menuId
	 * @return string path to follow, tokenised exactly as the Menu Manager published it
	 */
	private function menuManagerDeleteLink(AcceptanceTester $I, $menuId)
	{
		$I->amOnPage(self::MENUS);

		if(!preg_match('#menus\.php\?configure=([a-zA-Z0-9_-]+)#', $I->grabPageSource(), $layout))
		{
			throw new \RuntimeException('The Menu Manager published no layout to configure');
		}

		$I->amOnPage(self::MENUS . '?configure=' . $layout[1]);

		$pattern = '#menus\.php\?(configure=[^"\']*?mode=deac&amp;id=' . (int) $menuId . '[^"\']*)#';

		if(!preg_match($pattern, $I->grabPageSource(), $link))
		{
			throw new \RuntimeException('The Menu Manager published no delete link for menu ' . $menuId);
		}

		return self::MENUS . '?' . str_replace('&amp;', '&', $link[1]);
	}
}
