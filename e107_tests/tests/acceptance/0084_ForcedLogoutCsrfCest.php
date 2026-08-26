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

	/** @var bool whether this test switched the social plugin's test page on */
	private $xupOpened = false;

	/** @var bool whether this test wrote NO_TOKEN_PROBE into the docroot */
	private $probeWritten = false;

	/** @var string what a caller shows to prove it is this run of this case */
	private $secret;

	public function _before(AcceptanceTester $I)
	{
		$this->secret = substr(hash('sha256', uniqid('', true).mt_rand()), 0, 32);
	}

	/**
	 * Taking the probe away is what stops it outliving the case, so it cannot
	 * be conditional on the restore round trip that precedes it answering:
	 * one failed request would otherwise leave an anonymous caller a file that
	 * rewrites e107_config.php. The restore still throws where it fails, and
	 * the parked copy heals the security level whichever way that goes.
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
				$this->probe($I, 'act=restore');
			}
		}
		finally
		{
			if($this->probeWritten)
			{
				$I->deleteAppFile(self::NO_TOKEN_PROBE);
				$this->probeWritten = false;
			}

			if($this->xupOpened)
			{
				$I->haveSitePref('social_login_active', null);
				$I->dropForumProbe();
				$this->xupOpened = false;
			}
		}
	}

	/**
	 * `?logout` on the front page, cross-site, for any signed-in visitor.
	 */
	public function aTokenlessLogoutOnTheFrontPageLeavesTheSessionStanding(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage('/index.php?logout');

		$I->seeInSource(self::REFUSED);
		$this->seeStillSignedIn($I);
	}

	/**
	 * The same query string works on every entry point in the product, admin
	 * pages included, because class2.php is what reads it.
	 */
	public function aTokenlessLogoutOnAnAdminPageLeavesTheSessionStanding(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage(self::ADMIN_PANEL.'?logout');

		$I->seeInSource(self::REFUSED);
		$I->seeInSource(\Helper\AdminLogin::CONTROL_PANEL_MARKER);
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

		$I->dontSeeInSource(self::REFUSED);
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

		$I->dontSeeInSource(self::REFUSED);
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

		$I->dontSeeInSource(self::REFUSED);
		$I->dontSeeInSource(self::UNAUTHORIZED);
		$this->seeStillSignedIn($I);
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
		$I->seeCurrentUrlEquals(self::SETTINGS_PAGE);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function seeSignedOut(AcceptanceTester $I)
	{
		$I->amOnPage(self::SETTINGS_PAGE);
		$I->dontSeeCurrentUrlEquals(self::SETTINGS_PAGE);
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

		$I->assertSame('PROBE_OK', $this->probe($I, 'act=lower'));
		$I->assertSame('LEVEL=0 TOKEN=0', $this->probe($I, 'act=state'),
			'the fixture must be able to take an install below SECURITY_LEVEL_LOW');

		$I->amOnPage('/?route=system/xup/test&logout=true&e-token=');

		$I->seeInSource(self::XUP_TESTER);
		$I->dontSeeInSource(self::REFUSED);
		$this->seeSignedOut($I);
	}

	/**
	 * The lowering outlives the process that made it, so whatever heals it has
	 * to be on disk before the level goes down rather than in an _after that a
	 * killed run never reaches. Extension\WorkspaceCleanup puts e107_config.php
	 * back from the copy beside it on its way into a run, and Helper\E107Base
	 * puts it back at the end of a suite, so what both need is that a lowered
	 * config always has that copy and that the copy carries no lowering.
	 */
	public function loweringTheLevelParksAConfigWithNoLoweringInIt(AcceptanceTester $I)
	{
		$this->noTokenProbeIsInPlace($I);

		$I->assertSame('PROBE_OK', $this->probe($I, 'act=lower'));

		$I->assertSame('PARKED=1 LOWERED=0', $this->probe($I, 'act=parked'),
			'a killed run must find a config beside the lowered one to heal from');
	}

	/**
	 * The probe rewrites e107_config.php, so a caller that cannot show this
	 * run's secret has to get nothing at all. A probe left in the docroot by a
	 * run that died is otherwise an anonymous way to stop the site minting
	 * tokens, which by e_core_session::check() turns attest() off for every
	 * request the site serves, this branch's own guards included.
	 */
	public function theProbeRefusesACallerThatCannotShowTheSecret(AcceptanceTester $I)
	{
		$this->noTokenProbeIsInPlace($I);
		$I->assertStringContainsString('TOKEN=1', $this->probe($I, 'act=state'),
			'the install must be minting tokens before the forged call');

		$I->amOnPage('/'.self::NO_TOKEN_PROBE.'?act=lower');

		$I->seeResponseCodeIs(403);
		$I->assertStringContainsString('TOKEN=1', $this->probe($I, 'act=state'),
			'a refused caller must not have stopped the site minting tokens');
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
		$I->writeAppFile(self::NO_TOKEN_PROBE, $this->noTokenProbeSource());
		$this->probeWritten = true;
	}

	/**
	 * @param string $query
	 * @return string
	 */
	private function probeUrl($query)
	{
		$url = '/'.self::NO_TOKEN_PROBE.'?probe='.$this->secret;

		return ($query === '') ? $url : $url.'&'.$query;
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage($this->probeUrl($query));

		return trim($I->grabPageSource());
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
	 * Lowering the level first parks a copy of the config at
	 * e107_config.php.bak with any lowering taken out of it, preferring
	 * whatever is parked there already to the live file. That is the one name
	 * Extension\WorkspaceCleanup puts back on its way into a run, and it is
	 * also what Helper\E107Base hands back at the end of a suite, so a run
	 * killed between the lowering and the restore no longer leaves the site
	 * below SECURITY_LEVEL_LOW: whichever of the two gets there first finds a
	 * config with no lowering in it and puts that back. A tree an earlier run
	 * already left lowered is put right the same way, because the lowering
	 * comes out of the parked copy too.
	 *
	 * @return string
	 */
	private function noTokenProbeSource()
	{
		$secret = $this->secret;

		return <<<PHP
<?php
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}

if(!isset(\$_GET['probe']) || !hash_equals('$secret', \$_GET['probe']))
{
	header('HTTP/1.1 403 Forbidden', true, 403);
	echo 'Unauthorized access!';
	exit;
}

header('Content-Type: text/plain');

\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';
\$config = __DIR__.'/e107_config.php';
\$backup = \$config.'.bak';
\$line = "define('e_SECURITY_LEVEL', 0);\\n";

switch(\$act)
{
	case 'lower':
	case 'restore':
		\$src = str_replace("\\n".\$line, '', file_get_contents(\$config));

		if(\$act === 'lower')
		{
			\$parked = file_exists(\$backup) ? file_get_contents(\$backup) : \$src;
			file_put_contents(\$backup, str_replace("\\n".\$line, '', \$parked));

			\$at = strpos(\$src, '<?php') + 5;
			\$src = substr(\$src, 0, \$at)."\\n".\$line.substr(\$src, \$at);
		}

		file_put_contents(\$config, \$src);
		echo 'PROBE_OK';
		break;

	case 'parked':
		clearstatcache();
		\$parked = file_exists(\$backup);
		echo 'PARKED='.(\$parked ? 1 : 0)
			.' LOWERED='.((\$parked && strpos(file_get_contents(\$backup), \$line) !== false) ? 1 : 0);
		break;

	default:
		echo 'LEVEL='.e_SECURITY_LEVEL.' TOKEN='.(defined('e_TOKEN') ? 1 : 0);
		break;
}
PHP;
	}
}
