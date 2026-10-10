<?php

/**
 * Ending somebody else's session is a state change, and every doorway into it
 * was a bare GET.
 *
 * class2.php acted on `?logout` appended to any core page: the online row was
 * rewritten, a USER_AUDIT_LOGOUT entry written, e_user::logout() called, the
 * session destroyed and the cookie cleared. usersettings.php called logout()
 * unconditionally after processUserDelete(), so `?del=` with any junk value
 * emptied the session while the emailed hash still guarded the deletion itself.
 * The xup test page logged the visitor out on `?logout=true`. None of the three
 * asked for anything only this site could have handed out, so an <img> tag on
 * another site signed the reader out of theirs.
 *
 * The e-token in a query string is core's established marker for a
 * state-changing GET: the endpoint tests that one is present and
 * e_core_session::attest() decides whether it is the right one. These cases
 * assert both halves on all three doorways, plus the controls that say the
 * feature still works from the site's own menus.
 *
 * One case needs an install that mints no token at all, which only
 * e107_config.php can produce, so the fixture rewrites it. The probe therefore
 * loads class2.php before it looks at what it was asked to do, and answers
 * nobody who cannot show the secret this run minted for it. A probe that acted
 * first would be an unauthenticated way for anyone at all to turn token
 * minting off for the whole site, which is a larger hole than the one this
 * package closes.
 *
 * @see class2.php  logout_requested(), logout_refused()
 * @see e107_handlers/session_handler.php  e_core_session::attest()
 */
class ForcedLogoutCsrfCest
{
	const ADMIN_PANEL = '/e107_admin/admin.php';

	/** Rendered whenever a signed-in visitor reaches usersettings.php. */
	const SETTINGS_PAGE = '/usersettings.php';

	/** A distinctive fragment of LAN_LOGOUT_REFUSED_TOKEN_MISSING. */
	const REFUSED = 'no security token';

	/** Where a logout that carried no token is asked about; class2.php sends other scripts' there. */
	const CONFIRM_PAGE = '/index.php?logout';

	/** LAN_LOGOUT_CONFIRM_QUESTION, which only the confirmation page renders. */
	const CONFIRM = 'Are you sure you want to log out?';

	/** A public page an administrator can be on when they log out. */
	const NEWS_PAGE = '/news.php';

	/** The confirmation's Cancel link. */
	const CANCEL_LINK = '#logout-confirm a';

	/** A referrer from a host that is not this site's. */
	const FOREIGN_REFERRER = 'http://elsewhere.invalid/page.php';

	/** A distinctive fragment of LAN_USET_DELETE_LINK_INVALID. */
	const DELETE_REFUSED = 'confirmation link is no longer valid';

	/**
	 * A GET the framework does police: attest() refuses any e-token it cannot
	 * validate, whatever the request method, and answers with this.
	 */
	const UNAUTHORIZED = 'Unauthorized access!';

	/** Login name of the member the xup case needs; see xupTestPageIsOpen(). */
	const MEMBER = 'logoutcsrf';

	/** An admin page that renders a form, so it publishes e_TOKEN in one. */
	const TOKEN_SOURCE = '/e107_admin/db.php?mode=importForm';

	/** Where a theme publishes its user menu, and so its logout link. */
	const FRONT_PAGE = '/index.php';

	/** Table every visitor on the site has a row in while track_online is on. */
	const ONLINE_TABLE = 'e107_online';

	/** What online_user_id holds for a guest, whoever the guest is. */
	const ONLINE_GUEST = '0';

	/** RFC 5737 documentation address, so the row can only be the seeded one. */
	const BYSTANDER_IP = '203.0.113.7';

	/** The user list, and the route the impersonation is ended on. */
	const USER_LIST = '/e107_admin/users.php?mode=main';

	/** A distinctive fragment of ADLAN_164, the impersonation banner. */
	const IMPERSONATING = 'Successfully logged in as';

	/** Login name of the account the logoutas cases impersonate. */
	const IMPERSONATED = 'logoutascsrf';

	/** Rendered by the xup test page whatever the query string asks for. */
	const XUP_TESTER = 'Social Login Tester';

	/** Docroot fixture; see noTokenProbeSource(). */
	const NO_TOKEN_PROBE = 'e107_tests_no_token_probe.php';

	/** Docroot fixture; see staleIndexProbeSource(). */
	const STALE_INDEX_PROBE = 'e107_tests_stale_index_probe.php';

	/** @var bool whether this test switched the social plugin's test page on */
	private $xupOpened = false;

	/** @var bool whether this test wrote NO_TOKEN_PROBE into the docroot */
	private $probeWritten = false;

	/** @var array preference name => the value it held before this test changed it */
	private $changedPrefs = array();

	/**
	 * The restore throws where it fails, so a lowered level never passes for the next test's.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	public function _after(AcceptanceTester $I)
	{
		try
		{
			if($this->probeWritten)
			{
				$I->probe('act=restore');
			}
		}
		finally
		{
			$this->probeWritten = false;

			if($this->xupOpened)
			{
				$I->haveSitePref('social_login_active', null);
				$I->dropForumProbe();
				$this->xupOpened = false;
			}

			if($this->changedPrefs)
			{
				$I->resetAllCookies();

				foreach($this->changedPrefs as $name => $was)
				{
					$I->haveSitePref($name, $was);
				}

				$I->dropForumProbe();
				$this->changedPrefs = array();
			}
		}
	}

	/**
	 * `?logout` on the front page, cross-site, for any signed-in visitor, is answered with a question.
	 */
	public function aTokenlessLogoutOnTheFrontPageAsksFirst(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage(self::CONFIRM_PAGE);

		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::CONFIRM);
		$this->seeStillSignedIn($I);
	}

	/**
	 * The same query string works on every entry point, admin pages included, because class2.php reads it; they send it to index.php to be asked.
	 */
	public function aTokenlessLogoutOnAnAdminPageAsksFirst(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage(self::ADMIN_PANEL.'?logout');

		$I->seeInCurrentUrl(self::CONFIRM_PAGE);
		$I->seeInSource(self::CONFIRM);
		$this->seeStillSignedIn($I);
	}

	/**
	 * The confirmation page's own button ends the session, with whatever token the page published.
	 */
	public function theConfirmationPagesOwnButtonEndsTheSession(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->amOnPage(self::CONFIRM_PAGE);

		$I->submitForm('#logout-confirm', array());

		$this->seeSignedOut($I);
	}

	/**
	 * Where a missing token is only logged, {@see e_core_session::attest()} lets a forged POST through, so the logout has to refuse it itself.
	 */
	public function aTokenlessPostLeavesTheSessionStandingWhereMissingTokensAreOnlyLogged(AcceptanceTester $I)
	{
		$this->changeSitePref($I, 'csrf_enforce', 1);
		$I->loginAsAdmin();

		$I->sendPostRequest(self::CONFIRM_PAGE, array());

		$this->seeStillSignedIn($I);
	}

	/**
	 * A member whose profile lacks a required field is asked as well, not sent to complete the profile first.
	 */
	public function aMemberWithAnIncompleteProfileIsStillAsked(AcceptanceTester $I)
	{
		$this->changeSitePref($I, 'signup_option_signature', 2);
		$this->changeSitePref($I, 'force_userupdate', 1);
		$I->loginAsAdmin();

		$I->amOnPage(self::CONFIRM_PAGE);

		$I->seeInSource(self::CONFIRM);
		$I->dontSeeInCurrentUrl(self::SETTINGS_PAGE);
	}

	/**
	 * The question is not a landing page, so the administrator goes back to where they were.
	 */
	public function confirmingReturnsAnAdministratorToThePageTheyWereOn(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->amOnPage(self::NEWS_PAGE);
		$I->amOnPage(self::CONFIRM_PAGE);

		$I->submitForm('#logout-confirm', array());

		$I->seeInCurrentUrl(self::NEWS_PAGE);
	}

	/**
	 * On a members-only site, a guest who comes back to the question is sent to log in without it being kept as the page to return to.
	 */
	public function aMembersOnlySiteDoesNotKeepTheQuestionForAfterLogin(AcceptanceTester $I)
	{
		$this->changeSitePref($I, 'membersonly_enabled', 1);

		$I->amOnPage(self::CONFIRM_PAGE);
		$I->amOnPage('/login.php');
		$I->fillField('username', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('userpass', \Helper\AdminLogin::ADMIN_PASS);
		$I->click('userlogin');

		$I->dontSeeInCurrentUrl(self::CONFIRM_PAGE);
		$I->dontSeeInSource(self::CONFIRM);
	}

	/**
	 * The footer logs e_PAGE and e_QUERY when page accesses are logged, so the route has to define both before the theme runs.
	 */
	public function theQuestionRendersWherePageAccessesAreLogged(AcceptanceTester $I)
	{
		$this->changeSitePref($I, 'log_page_accesses', 1);
		$I->loginAsAdmin();

		$I->amOnPage(self::CONFIRM_PAGE);

		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::CONFIRM);
		$I->seeInSource('</html>');
	}

	/**
	 * Cancel returns to the page the member came from when that page is on one of this site's hosts, and to the front page otherwise.
	 */
	public function cancelReturnsToAReferrerOnThisSiteAndHomeFromAnyOther(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		try
		{
			$I->haveHttpHeader('Referer', self::FOREIGN_REFERRER);
			$I->amOnPage(self::CONFIRM_PAGE);
			$home = $I->grabAttributeFrom(self::CANCEL_LINK, 'href');
			$I->assertStringNotContainsString('elsewhere.invalid', $home);

			$I->haveHttpHeader('Referer', $home.'news.php');
			$I->amOnPage(self::CONFIRM_PAGE);
			$I->assertSame($home.'news.php', $I->grabAttributeFrom(self::CANCEL_LINK, 'href'));
		}
		finally
		{
			$I->deleteHeader('Referer');
		}
	}

	/**
	 * An index.php customised before the route table existed still sets single_entry; it keeps the refusal rather than ignoring the logout or redirecting to itself.
	 */
	public function aStaleIndexScriptStillRefusesATokenlessLogout(AcceptanceTester $I)
	{
		$I->haveProbe(self::STALE_INDEX_PROBE, $this->staleIndexProbeSource());
		$I->loginAsAdmin();

		$I->amOnPage('/'.self::STALE_INDEX_PROBE.'?logout&'.\Helper\ProbeGuard::query());

		$I->seeInSource('STALE_INDEX_RENDERED');
		$I->seeInSource(self::REFUSED);
		$this->seeStillSignedIn($I);
	}

	/**
	 * Presence is all class2.php tests; whether the value is the right one is
	 * attest()'s half. Both halves are needed, so assert the second one too.
	 */
	public function aLogoutCarryingTheWrongTokenIsRefused(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage('/index.php?logout&e-token=not-even-close');

		$I->seeInSource(self::UNAUTHORIZED);
		$this->seeStillSignedIn($I);
	}

	/**
	 * usersettings.php gates the deletion on the hash it emailed, and then ran
	 * the logout whether or not that hash matched, so `?del=` with anything at
	 * all in it was a forced logout with no secret behind it.
	 */
	public function aRejectedAccountDeletionLeavesTheSessionStanding(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage(self::SETTINGS_PAGE.'?del=not-the-emailed-hash');

		$I->seeInSource(self::DELETE_REFUSED);
		$this->seeStillSignedIn($I);
	}

	/**
	 * The xup test page returns early for a main administrator, so the account
	 * that reaches its logout is an ordinary one, and the page itself only
	 * exists while the social plugin's test-page flag is set.
	 */
	public function aTokenlessXupTestLogoutLeavesTheSessionStanding(AcceptanceTester $I)
	{
		$this->xupTestPageIsOpen($I);

		$I->amOnPage('/?route=system/xup/test&logout=true');

		$I->seeInSource(self::REFUSED);
		$this->seeStillSignedIn($I);
	}

	/**
	 * The control that matters most. A guard on a link an administrator reaches
	 * by ordinary navigation is worse than the hole it closes if the navigation
	 * stops working, so this follows whatever the admin menu publishes for
	 * itself rather than a URL of the test's own.
	 */
	public function theAdminMenusOwnLogoutLinkStillEndsTheSession(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage($this->adminLogoutLink($I));

		$I->dontSeeInSource(self::CONFIRM);
		$this->seeSignedOut($I);
	}

	/**
	 * And the same for the front end, where the token has to survive being
	 * written into a query string this test assembles the way a theme does.
	 */
	public function aLogoutCarryingTheSitesOwnTokenStillEndsTheSession(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage('/index.php?logout&e-token='.$I->grabFreshAdminToken(self::TOKEN_SOURCE));

		$this->seeSignedOut($I);
	}

	/**
	 * The link an ordinary visitor clicks. bootstrap3 is the theme a default
	 * install renders, and its user menu writes the query string into markup,
	 * so the token has to survive being escaped on the way out and unescaped by
	 * the browser on the way back. voux publishes the same markup from its own
	 * copy of the shortcode.
	 */
	public function theSiteThemesOwnLogoutLinkStillEndsTheSession(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage($this->frontEndLogoutLink($I));

		$I->dontSeeInSource(self::CONFIRM);
		$this->seeSignedOut($I);
	}

	/**
	 * Backwards-compatibility control: an ordinary page view is untouched by
	 * any of this, and no visitor is told about a security token they were
	 * never asked for.
	 */
	public function anOrdinaryPageViewIsUnaffected(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage('/index.php');

		$I->dontSeeInSource(self::CONFIRM);
		$I->dontSeeInSource(self::UNAUTHORIZED);
		$this->seeStillSignedIn($I);
	}

	/**
	 * Nobody who is not signed in can be signed out, so a guest's `?logout` has
	 * nothing to confirm and nothing to say about a security token.
	 */
	public function aGuestIsToldNothingAboutATokenTheyWereNeverAskedFor(AcceptanceTester $I)
	{
		$I->resetAllCookies();

		$I->amOnPage(self::FRONT_PAGE . '?logout');

		$I->seeResponseCodeIs(200);
		$I->dontSeeInSource(self::CONFIRM);
		$I->dontSeeInSource(self::UNAUTHORIZED);
	}

	/**
	 * A guest carrying a token the site itself published used to reach the
	 * logout in full. `online_user_id` is '0' for every guest on the site, and
	 * the row the branch rewrote was matched on that value, so one visitor's
	 * `?logout` incremented `online_pagecount` for all of them. That column is
	 * what {@see e_online::goOnline()} auto-bans a guest address on.
	 *
	 * The bystander is seeded rather than browsed for, because the suite reaches
	 * the site from one address and a table-wide write is only visible against a
	 * row that address could not have touched.
	 */
	public function aGuestsTokenisedLogoutLeavesEveryOnlineRowAlone(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->haveInDatabase(self::ONLINE_TABLE, array(
			'online_timestamp' => time(),
			'online_flag'      => 0,
			'online_user_id'   => self::ONLINE_GUEST,
			'online_ip'        => self::BYSTANDER_IP,
			'online_location'  => self::FRONT_PAGE,
			'online_pagecount' => 5,
			'online_active'    => 0,
			'online_agent'     => __CLASS__,
			'online_language'  => 'en',
		));

		$before = $I->grabFromDatabase(self::ONLINE_TABLE, 'online_pagecount',
			array('online_ip' => self::BYSTANDER_IP));

		$token = $this->guestToken($I);

		$I->stopFollowingRedirects();
		$I->amOnPage(self::FRONT_PAGE . '?logout&e-token=' . $token);
		$I->startFollowingRedirects();

		$I->assertSame($before, $I->grabFromDatabase(self::ONLINE_TABLE, 'online_pagecount',
			array('online_ip' => self::BYSTANDER_IP)),
			'a logout nobody was signed in for must not touch another visitor\'s row');
		$I->seeResponseCodeIs(303);
	}

	/**
	 * usersettings.php redirects a visitor who holds no session, so the page it
	 * answers with says whether one is still standing. Asked of the application
	 * rather than of a page marker, so the oracle reads the same for the main
	 * administrator and for an ordinary member.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function seeStillSignedIn(AcceptanceTester $I)
	{
		$I->amOnPage(self::SETTINGS_PAGE);
		$I->seeInCurrentUrl(self::SETTINGS_PAGE);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function seeSignedOut(AcceptanceTester $I)
	{
		$I->amOnPage(self::SETTINGS_PAGE);
		$I->dontSeeInCurrentUrl(self::SETTINGS_PAGE);
	}

	/**
	 * Signs the browser out first, as _after() does before it puts every preference back, so that force_userupdate cannot bounce the probe.
	 *
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	private function changeSitePref(AcceptanceTester $I, $name, $value)
	{
		$I->resetAllCookies();
		$was = $I->haveSitePref($name, $value);

		if(!array_key_exists($name, $this->changedPrefs))
		{
			$this->changedPrefs[$name] = $was;
		}
	}

	/**
	 * Sign in as a member and switch the social plugin's test page on, which is
	 * everything actionTest() needs before it reads ?logout. Bits 0 and 1 of
	 * social_login_active are the global switch and the test page; isFlagActive()
	 * reads no other bit as set while the global one is clear.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function xupTestPageIsOpen(AcceptanceTester $I)
	{
		$I->haveSitePref('social_login_active', 3);
		$this->xupOpened = true;

		$I->haveForumMember(self::MEMBER);
		$I->loginToForum(self::MEMBER);

		$this->seeStillSignedIn($I);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string path to follow, tokenised exactly as the admin menu published it
	 */
	private function adminLogoutLink(AcceptanceTester $I)
	{
		$I->amOnPage(self::ADMIN_PANEL);

		return $this->publishedLogoutLink($I, '#["\']([^"\']*admin\.php\?logout[^"\']*)["\']#');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string path to follow, tokenised exactly as the site theme published it
	 */
	private function frontEndLogoutLink(AcceptanceTester $I)
	{
		$I->amOnPage(self::FRONT_PAGE);

		return $this->publishedLogoutLink($I, '#["\']([^"\']*index\.php\?logout[^"\']*)["\']#');
	}

	/**
	 * A guest is handed a token like anybody else, in the head of every page and
	 * in any form the theme renders, so obtaining one costs an attacker a page
	 * view.
	 *
	 * @param AcceptanceTester $I
	 * @return string a token the guest's own session will accept
	 */
	private function guestToken(AcceptanceTester $I)
	{
		$I->amOnPage(self::FRONT_PAGE);

		if(!preg_match('#name=[\'"]e-token[\'"][^>]*\s(?:content|value)=[\'"]([^\'"]+)[\'"]#',
			$I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The front page published no token for a guest');
		}

		return $matches[1];
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $pattern matches the href as the page carries it, group 1
	 * @return string that href with its markup escaping undone
	 */
	private function publishedLogoutLink(AcceptanceTester $I, $pattern)
	{
		if(!preg_match($pattern, $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('That page published no logout link');
		}

		return str_replace('&amp;', '&', $matches[1]);
	}

	/**
	 * An install below SECURITY_LEVEL_LOW never reaches the define in
	 * e_core_session::check(), so defset('e_TOKEN') is the empty string and
	 * every link the site publishes carries an empty e-token. A guard that
	 * asked only whether the submitted value was empty would refuse the page's
	 * own retry link and answer by telling the member to use it.
	 */
	public function aLogoutIsNotRefusedWhereNoTokenIsEverMinted(AcceptanceTester $I)
	{
		$this->xupTestPageIsOpen($I);
		$this->noTokenProbeIsInPlace($I);

		$I->probe('act=lower');
		$I->assertSame('LEVEL=0 TOKEN=0', $I->grabProbe('act=state'),
			'the fixture must be able to take an install below SECURITY_LEVEL_LOW');

		$I->amOnPage('/?route=system/xup/test&logout=true&e-token=');

		$I->seeInSource(self::XUP_TESTER);
		$I->dontSeeInSource(self::REFUSED);
		$this->seeSignedOut($I);
	}

	/**
	 * users.php?mode=main&action=logoutas ends the impersonated session, and
	 * e_admin_controller::dispatchObserver() calls LogoutasObserver() on
	 * method_exists alone. The token check inside the class covers the posted
	 * etrigger_ keys, which this route is not one of.
	 */
	public function aTokenlessLogoutAsLeavesTheImpersonationStanding(AcceptanceTester $I)
	{
		$this->impersonateAMember($I);

		$I->amOnPage(self::USER_LIST.'&action=logoutas');

		$I->amOnPage(self::ADMIN_PANEL);
		$I->seeInSource(self::IMPERSONATING);
	}

	/**
	 * The control: the banner every admin page publishes while an
	 * impersonation is standing still ends it, followed exactly as it was
	 * published rather than assembled here.
	 */
	public function theAdminBannersOwnLogoutAsLinkStillEndsTheImpersonation(AcceptanceTester $I)
	{
		$this->impersonateAMember($I);

		$I->amOnPage($this->publishedLogoutAsLink($I));

		$I->amOnPage(self::ADMIN_PANEL);
		$I->dontSeeInSource(self::IMPERSONATING);
	}

	/**
	 * The user list posts this action rather than linking to it, and a POST is
	 * policed by attest() already, so it carries no e-token of its own in the
	 * query string and must go on working.
	 */
	public function theUserListsOwnPostedLogoutAsStillEndsTheImpersonation(AcceptanceTester $I)
	{
		$this->impersonateAMember($I);

		$I->sendPostRequest(self::USER_LIST, array(
			'useraction' => 'logoutas',
			'userid'     => 0,
			'e-token'    => $I->grabFreshAdminToken(self::USER_LIST),
		));

		$I->amOnPage(self::ADMIN_PANEL);
		$I->dontSeeInSource(self::IMPERSONATING);
	}

	/**
	 * Sign in as the main administrator and take on an ordinary member's
	 * identity, the way the user list's own action does.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function impersonateAMember(AcceptanceTester $I)
	{
		$memberId = $I->haveForumMember(self::IMPERSONATED);

		$I->loginAsAdmin();
		$I->sendPostRequest(self::USER_LIST, array(
			'useraction' => 'loginas',
			'userid'     => $memberId,
			'e-token'    => $I->grabFreshAdminToken(self::USER_LIST),
		));

		$I->amOnPage(self::ADMIN_PANEL);
		$I->seeInSource(self::IMPERSONATING);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string path to follow, tokenised exactly as the banner published it
	 */
	private function publishedLogoutAsLink(AcceptanceTester $I)
	{
		$I->amOnPage(self::ADMIN_PANEL);

		return $this->publishedLogoutLink($I, '#["\']([^"\']*users\.php\?mode=main&amp;action=logoutas[^"\']*)["\']#');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function noTokenProbeIsInPlace(AcceptanceTester $I)
	{
		$I->haveProbe(self::NO_TOKEN_PROBE, $this->noTokenProbeSource());
		$this->probeWritten = true;
	}

	/**
	 * The one thing an operator sets in e107_config.php that stops a token
	 * from ever being minted, written into the file an operator would write it
	 * into. class2.php leaves e_SECURITY_LEVEL alone when it is already
	 * defined, so this is the same install the setting produces, and the site
	 * is reached through its own entry points rather than through the probe.
	 *
	 * 0 is e_session::SECURITY_LEVEL_NONE, named "Looking for trouble (none)"
	 * in the admin preferences.
	 *
	 * @return string
	 */
	private function noTokenProbeSource()
	{
		return <<<'PHP'
<?php
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}

header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$config = __DIR__.'/e107_config.php';
$line = "define('e_SECURITY_LEVEL', 0);\n";

switch($act)
{
	case 'lower':
	case 'restore':
		$src = str_replace("\n".$line, '', file_get_contents($config));

		if($act === 'lower')
		{
			$at = strpos($src, '<?php') + 5;
			$src = substr($src, 0, $at)."\n".$line.substr($src, $at);
		}

		file_put_contents($config, $src);
		echo 'PROBE_OK';
		break;

	default:
		echo 'LEVEL='.e_SECURITY_LEVEL.' TOKEN='.(defined('e_TOKEN') ? 1 : 0);
		break;
}
PHP;
	}

	/**
	 * @return string an entry script shaped like an index.php that predates the route table
	 */
	private function staleIndexProbeSource()
	{
		return <<<'PHP'
<?php
$_E107['single_entry'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}

echo e107::getMessage()->render();
echo 'STALE_INDEX_RENDERED';
PHP;
	}
}
