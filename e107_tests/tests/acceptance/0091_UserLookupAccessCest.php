<?php

/**
 * Regression test for GHSA-72mc-5q6j-2fqq: the user-name lookups answered an
 * anonymous caller with the whole member directory. user.php's AJAX branch ran
 * ahead of the memberlist_access check the same file makes for the page below
 * it, and the PM plugin ran its own lookup ahead of the pm_class gate. Neither
 * capped the rows it returned, neither excluded banned or unvalidated accounts,
 * and a keyword of nothing but markup filtered down to an empty pattern that
 * matched every row.
 */
class UserLookupAccessCest
{
	const LOOKUP = '/user.php?ajax_used=1';

	const PM_LOOKUP = '/e107_plugins/pm/pm.php';

	/** Distinctive enough that finding it in a response body means the lookup answered. */
	const PREFIX = 'LookupFixture';

	const OPEN = 'LookupFixtureOpen';

	const BANNED = 'LookupFixtureBanned';

	const PENDING = 'LookupFixturePending';

	/** The member who is entitled to the typeahead, by memberlist_access's default. */
	const VIEWER = 'LookupFixtureViewer';

	const VIEWER_LOGIN = 'lookupfixtureviewer';

	const VIEWER_PASS = 'Lookup1Fixture!';

	/** More than the cap, so a request that asks for all of them cannot have them all. */
	const CROWD = 55;

	const CAP = 50;

	const PREFS_PAGE = '/e107_admin/users.php?mode=main&action=prefs';

	const PREFS_FORM = "form[action*='users.php']";

	const TOKEN_PATTERN = '/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/';

	/** e_UC_PUBLIC and e_UC_MEMBER, as the preference stores them. */
	const PUBLIC_CLASS = '0';

	const MEMBER_CLASS = '253';

	public function _before(AcceptanceTester $I)
	{
		$this->seedUsers($I);
		$I->resetAllCookies();
	}

	public function aGuestGetsNoAnswerWhileTheMemberListIsMembersOnly(AcceptanceTester $I)
	{
		$I->wantTo('refuse an anonymous user lookup while the member list is members-only');

		$I->sendPostRequest(self::LOOKUP, array('q' => self::PREFIX, 'l' => '100'));

		$I->assertStringNotContainsString(self::OPEN, $I->grabResponseBody(),
			'An anonymous caller was handed member names the member list withholds from them');
	}

	public function aGuestGetsAnAnswerOnceTheMemberListIsPublic(AcceptanceTester $I)
	{
		$I->wantTo('keep the typeahead answering guests on a site that publishes its member list');

		$body = '';

		$this->haveMemberlistAccess($I, self::PUBLIC_CLASS);

		try
		{
			$I->resetAllCookies();
			$I->sendPostRequest(self::LOOKUP, array('q' => self::PREFIX, 'l' => '100'));
			$body = $I->grabResponseBody();
		}
		finally
		{
			$this->haveMemberlistAccess($I, self::MEMBER_CLASS);
		}

		$I->assertStringContainsString(self::OPEN, $body,
			'A site that made its member list public lost the guest typeahead as well');
	}

	public function anAuthorisedLookupIgnoresAKeywordThatFiltersToNothing(AcceptanceTester $I)
	{
		$I->wantTo('return nothing rather than everything for a keyword that filters down to nothing');

		$token = $this->loginAsViewer($I);
		$I->sendPostRequest(self::LOOKUP, array('q' => '<x>', 'l' => '100000', 'e-token' => $token));

		$I->assertStringNotContainsString(self::PREFIX, $I->grabResponseBody(),
			'A keyword that filters down to an empty pattern still matched every member');
	}

	public function anAuthorisedLookupSkipsBannedAndUnvalidatedAccounts(AcceptanceTester $I)
	{
		$I->wantTo('keep accounts the member list withholds out of the typeahead');

		$token = $this->loginAsViewer($I);
		$I->sendPostRequest(self::LOOKUP, array('q' => self::PREFIX, 'l' => '100', 'e-token' => $token));

		$body = $I->grabResponseBody();

		$I->assertStringContainsString(self::OPEN, $body,
			'The typeahead has to answer a member, or this proves nothing');
		$I->assertStringNotContainsString(self::BANNED, $body,
			'A banned account was offered by the typeahead');
		$I->assertStringNotContainsString(self::PENDING, $body,
			'An account that has not validated its registration was offered by the typeahead');
	}

	public function anAuthorisedLookupCapsTheRowsItReturns(AcceptanceTester $I)
	{
		$I->wantTo('cap the lookup however many rows the caller asks for');

		$this->seedCrowd($I);
		$token = $this->loginAsViewer($I);
		$I->sendPostRequest(self::LOOKUP,
			array('q' => self::PREFIX . 'Crowd', 'l' => '100000', 'e-token' => $token));

		$returned = substr_count($I->grabResponseBody(), '"label"');

		$I->assertGreaterThan(0, $returned, 'The lookup answered nothing at all');
		$I->assertLessThanOrEqual(self::CAP, $returned,
			'The caller set the page size, so one request still drains the member table');
	}

	public function thePrivateMessagePluginNoLongerAnswersAUserLookup(AcceptanceTester $I)
	{
		$I->wantTo('leave the PM plugin without an unauthenticated user lookup');

		$I->havePluginInstalled('pm');
		$I->resetAllCookies();
		$I->sendPostRequest(self::PM_LOOKUP, array('keyword' => '-'));

		$I->assertStringNotContainsString(self::OPEN, $I->grabResponseBody(),
			'The PM plugin still hands the member directory to an anonymous caller');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function seedUsers(AcceptanceTester $I)
	{
		$this->haveUser($I, self::OPEN, 0);
		$this->haveUser($I, self::BANNED, 1);
		$this->haveUser($I, self::PENDING, 2);
		$this->haveUser($I, self::VIEWER, 0, self::VIEWER_LOGIN, self::VIEWER_PASS);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function seedCrowd(AcceptanceTester $I)
	{
		for($i = 1; $i <= self::CROWD; $i++)
		{
			$this->haveUser($I, self::PREFIX . 'Crowd' . str_pad($i, 2, '0', STR_PAD_LEFT), 0);
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name display name
	 * @param int $ban user_ban value: 0 open, 1 banned, 2 registered but not validated
	 * @param string $login login name, defaulting to the display name lowercased
	 * @param string $password plain password to store hashed, or '' for an account nobody logs into
	 * @return void
	 */
	private function haveUser(AcceptanceTester $I, $name, $ban, $login = '', $password = '')
	{
		$login = $login !== '' ? $login : strtolower($name);

		$I->haveInDatabase('e107_user', array(
			'user_name'      => $name,
			'user_loginname' => $login,
			'user_login'     => $login,
			'user_email'     => $login . '@example.test',
			'user_password'  => $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : '',
			'user_join'      => time(),
			'user_ban'       => $ban,
			'user_admin'     => 0,
			'user_class'     => '',
			'user_perms'     => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
			'user_xup'       => '',
		));
	}

	/**
	 * Log the seeded member in and hand back the session token their posts need:
	 * class2.php refuses a cookie-bearing POST that carries none, so an
	 * authenticated lookup without one never reaches the code under test.
	 *
	 * @param AcceptanceTester $I
	 * @return string the e-token this session's posts must carry
	 */
	private function loginAsViewer(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->amOnPage('/login.php');
		$I->fillField('username', self::VIEWER_LOGIN);
		$I->fillField('userpass', self::VIEWER_PASS);
		$I->click('userlogin');
		$I->amOnPage('/usersettings.php');
		$I->seeInSource(self::VIEWER);

		$matches = array();
		$found = preg_match(self::TOKEN_PATTERN, $I->grabPageSource(), $matches);
		$I->assertNotEmpty($found, 'usersettings.php renders an e-token');

		return $matches[1];
	}

	/**
	 * Store memberlist_access through the admin form, so the preference handler
	 * refreshes the cache it would otherwise serve the old value from.
	 *
	 * @param AcceptanceTester $I
	 * @param string $class userclass id to store
	 * @return void
	 */
	private function haveMemberlistAccess(AcceptanceTester $I, $class)
	{
		$I->resetAllCookies();
		$I->loginAsAdmin();
		$I->amOnPage(self::PREFS_PAGE);
		$I->submitForm(self::PREFS_FORM, array('memberlist_access' => $class), 'etrigger_save');
	}
}
