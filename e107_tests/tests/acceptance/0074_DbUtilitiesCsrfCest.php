<?php

/**
 * Database Utilities acts on a GET, and e107's CSRF guard does not police a GET.
 *
 * e_session::isStateChangingRequest() returns true only for POST, so attest()
 * returns early on every GET that carries no e-token at all. What stands between
 * an attacker's <img> tag and a state-changing GET is therefore whatever the
 * endpoint does for itself, and e107_admin/db.php did nothing: a main
 * administrator loading a hostile page ran OPTIMIZE TABLE over every table, had
 * the addon and shortcode-override preferences rewritten, had the install
 * chmodded, and had a full database dump plus a zip of the media tree written
 * to e_BACKUP.
 *
 * The e-token in a query string is e107's established marker for a
 * state-changing GET, which e107_admin/plugin.php, theme.php and language.php
 * already use: the endpoint tests that it is present and attest() decides
 * whether it is the right one. These cases assert both halves of that division
 * of labour on the db.php modes that act rather than render.
 *
 * The modes that only render a page are deliberately left alone, and the last
 * three cases are the controls for that: an operation reached from e107's own
 * menu still runs, a POST carrying a valid token still reaches its operation,
 * and a read-only mode still opens from a bare URL.
 *
 * @see e107_handlers/session_handler.php  e_session::isStateChangingRequest()
 * @see e107_admin/db.php                  db_modeNeedsToken()
 */
class DbUtilitiesCsrfCest
{
	const MENU = '/e107_admin/db.php';

	/** DBLAN_11, emitted by system_tools::optimizesql() once it has run. */
	const OPTIMISED = 'optimized';

	/** DBLAN_105, emitted by system_tools::scan_override() once it has run. */
	const SCANNED = 'Batch shortcodes:';

	/** DBLAN_23, emitted by system_tools::plugin_viewscan() once it has run. */
	const PLUGINS_SCANNED = 'Scan Completed';

	/** DBLAN_73, the caption system_tools::correct_perms() renders under. */
	const CHMODDED = 'Correcting File and Directory Permissions';

	/** DBLAN_62, printed by the AJAX branch after e_db::backup() returns. */
	const DUMPED = 'Database backup complete!';

	/** LAN_UPDATE_5, the row title e107Update renders for the core structure. */
	const UPDATE_LISTED = 'Core database structure';

	/** A distinctive fragment of DBLAN_REFUSED_TOKEN_MISSING. */
	const REFUSED = 'no security token';

	/**
	 * A GET that the framework does police: attest() refuses any e-token it
	 * cannot validate, whatever the request method, and answers with this.
	 */
	const UNAUTHORIZED = 'Unauthorized access!';

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
	}

	/**
	 * OPTIMIZE TABLE on every table in the database, cross-site, repeatable.
	 */
	public function aTokenlessGetDoesNotOptimiseTheDatabase(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=optimize_sql');

		$I->dontSeeInSource(self::OPTIMISED);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * Overwrites the sc_override and sc_batch_override core preferences.
	 */
	public function aTokenlessGetDoesNotRescanTheShortcodeOverrides(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=sc_override_scan');

		$I->dontSeeInSource(self::SCANNED);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * plugin_viewscan() clears the plugin cache and calls buildAddonPrefLists(),
	 * which empties every e_*_list core preference and rebuilds it from disk.
	 * The two calls above it that look like the write, update_plugins_table()
	 * and save_addon_prefs(), are commented out and are not the reason it acts.
	 */
	public function aTokenlessGetDoesNotRescanThePluginDirectories(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=plugin_scan');

		$I->dontSeeInSource(self::PLUGINS_SCANNED);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * The same scan has a second doorway that carries no mode at all: the
	 * dispatch tested e_QUERY, so db.php?plugin reached it while every guard
	 * keyed on $_GET['mode'] looked the other way.
	 */
	public function theLegacyPluginQueryReachesTheSameGuard(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?plugin');

		$I->dontSeeInSource(self::PLUGINS_SCANNED);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * Recursive chmod of the whole installation from e_BASE down.
	 */
	public function aTokenlessGetDoesNotChmodTheInstallation(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=correct_perms');

		$I->dontSeeInSource(self::CHMODDED);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * The dump is written by the e_AJAX_REQUEST branch, which a hostile page
	 * reaches without setting a header: e107_class.php falls back to
	 * isset($_REQUEST['ajax_used']), and $_REQUEST carries the query string.
	 */
	public function aTokenlessGetDoesNotWriteADatabaseDump(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=backup&ajax_used=1');

		$I->dontSeeInSource(self::DUMPED);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * clear_sys() empties the whole system cache, and the include that reaches
	 * it executes every installed plugin's _setup.php.
	 */
	public function aTokenlessGetDoesNotStartACoreUpdate(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=db_update');

		$I->dontSeeInSource(self::UPDATE_LISTED);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * The menu builds this through db_modeUrl(), so the operation an
	 * administrator actually clicks still runs.
	 */
	public function theMenusOwnLinkStillStartsACoreUpdate(AcceptanceTester $I)
	{
		$I->amOnPage($this->menuLink($I, 'db_update'));

		$I->seeInSource(self::UPDATE_LISTED);
		$I->dontSeeInSource(self::REFUSED);
	}

	/**
	 * Presence is all the endpoint tests; whether the value is the right one is
	 * attest()'s half. Both halves are needed, so assert the second one too.
	 */
	public function aGetCarryingTheWrongTokenIsRefused(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=optimize_sql&e-token=not-even-close');

		$I->seeInSource(self::UNAUTHORIZED);
		$I->dontSeeInSource(self::OPTIMISED);
	}

	/**
	 * The control that matters most. A guard on a link the administrator
	 * reaches by ordinary navigation is worse than the hole it closes if the
	 * navigation stops working, so this follows whatever Database Utilities
	 * publishes for itself rather than a URL of the test's own.
	 */
	public function theMenusOwnLinkStillOptimisesTheDatabase(AcceptanceTester $I)
	{
		$I->amOnPage($this->menuLink($I, 'optimize_sql'));

		$I->seeInSource(self::OPTIMISED);
		$I->dontSeeInSource(self::REFUSED);
	}

	/**
	 * A POST to a gated mode is the framework's business, not the endpoint's:
	 * attest() already refuses one that brings no proof. A guard here as well
	 * would drop a POST whose token validated, because db.php's own forms put
	 * the mode in their action and the token in the body.
	 */
	public function aPostToAGatedModeIsLeftToTheFramework(AcceptanceTester $I)
	{
		$I->sendPostRequest(self::MENU . '?mode=optimize_sql',
			array('e-token' => $I->grabFreshAdminToken(self::MENU . '?mode=importForm')));

		$I->seeInSource(self::OPTIMISED);
		$I->dontSeeInSource(self::REFUSED);
	}

	/**
	 * A mode that renders rather than acts is not a CSRF surface, and gating one
	 * would refuse a bookmark, the browser's back button and every deep link a
	 * plugin has been shipping for twenty years. They stay reachable bare.
	 */
	public function aReadOnlyModeStillOpensWithoutAToken(AcceptanceTester $I)
	{
		foreach(array('verify_sql', 'pref_editor', 'exportForm', 'importForm', 'convert_to_utf8') as $mode)
		{
			$I->amOnPage(self::MENU . '?mode=' . $mode);

			$I->dontSeeInSource(self::REFUSED);
			$I->dontSeeInSource(self::UNAUTHORIZED);
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $mode a key of system_tools::$_options
	 * @return string path to follow, tokenised exactly as the menu published it
	 */
	private function menuLink(AcceptanceTester $I, $mode)
	{
		$I->amOnPage(self::MENU);

		$pattern = '#db\.php\?mode=' . preg_quote($mode, '#') . '(&amp;e-token=[^\'"]+)?[\'"]#';

		if(!preg_match($pattern, $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('Database Utilities published no link for ' . $mode);
		}

		$token = isset($matches[1]) ? $matches[1] : '';

		return self::MENU . '?mode=' . $mode . str_replace('&amp;', '&', $token);
	}
}
