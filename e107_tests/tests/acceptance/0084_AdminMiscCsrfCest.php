<?php

/**
 * Seven admin GETs that act, and e107's CSRF guard does not police a GET.
 *
 * e_session::isStateChangingRequest() returns true only for POST, so attest()
 * returns early on every GET that carries no e-token at all. What stands between
 * an attacker's <img> tag and a state-changing GET is therefore whatever the
 * endpoint does for itself, and these seven did nothing:
 *
 *  - users.php?mode=main&action=test opens an outbound SMTP conversation with
 *    the mail host of whichever account the attacker names;
 *  - users.php?mode=main&action=logoutas ends the main administrator's Login As
 *    session;
 *  - admin.php?dismiss=upgrade writes a flag file that suppresses the upgrade
 *    notice for good;
 *  - language.php?mode=main&action=tools&sub=verify creates directories and
 *    writes stub PHP files across every plugin and theme, for a language name
 *    the query string picks;
 *  - admin.php?mode=core&type=update rewrites the db_updates preferences and
 *    includes every installed plugin's _setup.php;
 *  - admin.php?mode=addons&type=plugin forces an outbound fetch to ADDONFEED
 *    and caches what comes back;
 *  - admin.php?mode=addons&type=update asks the marketplace for the plugin and
 *    theme version lists and caches both for twelve hours.
 *
 * The last three are AJAX branches that a hostile page reaches without setting a
 * header, because e107_class.php falls back to isset($_REQUEST['ajax_used'])
 * and $_REQUEST carries the query string.
 *
 * The e-token in a query string is e107's established marker for a
 * state-changing GET, which plugin.php, theme.php and language.php's own
 * download page already use: the endpoint tests that it is present and attest()
 * decides whether it is the right one. These cases assert both halves of that
 * division of labour, and the controls assert that ordinary admin navigation
 * still reaches every one of the seven.
 *
 * The probe seeds a member, rewrites install_date and reaches Login As, so it
 * answers nobody who cannot show the secret this run minted for it, and it
 * loads class2.php before it looks at what it was asked to do. A probe left in
 * the docroot by a run that died would otherwise be an anonymous way to write
 * to the site's preferences and to its user table.
 *
 * @see e107_handlers/session_handler.php  e_session::isStateChangingRequest()
 */
class AdminMiscCsrfCest
{
	const PROBE_FILE = 'e107_tests_p9_csrf_probe.php';

	const DASHBOARD = '/e107_admin/admin.php';
	const USERS = '/e107_admin/users.php';
	const LANGUAGE = '/e107_admin/language.php';

	/** A language name no installed pack answers to, so every file is a new one. */
	const SCRATCH_LAN = 'Csrfprobe';

	const MEMBER_NAME = 'p9member';

	/** A domain that resolves nowhere, so the probe cannot reach a real host. */
	const MEMBER_EMAIL = 'p9probe@e107-csrf-probe.invalid';

	/** The fragment every one of the three refusal strings shares. */
	const REFUSED = 'no security token';

	/** USRLAN_234, appended to the address once testEmail() has answered. */
	const MAIL_TESTED = self::MEMBER_EMAIL.' - Invalid';

	/** users.php:1120, emitted once logoutAs() has ended the session. */
	const LOGGED_OUT_AS = 'Successfully logged out from';

	/** ADLAN_164, rendered by auth.php on every admin page while Login As holds. */
	const LOGGED_IN_AS = 'Successfully logged in as';

	private $memberId = 0;

	/** @var string what a caller shows to prove it is this run of this case */
	private $secret;

	public function _before(AcceptanceTester $I)
	{
		$this->secret = substr(hash('sha256', uniqid('', true).mt_rand()), 0, 32);
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->loginAsAdmin();
		$I->amOnPage($this->probeUrl('act=arm'));
		$I->seeInSource('P9_OK arm');

		$source = $I->grabPageSource();
		$matches = array();
		if(!preg_match('/uid=(\d+)/', $source, $matches))
		{
			throw new \RuntimeException('The probe did not report a member id: '.$source);
		}
		$this->memberId = (int) $matches[1];
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl('act=cleanup'));
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * ValidateEmailBox() opens an SMTP conversation with the MX host of whatever
	 * address the named account carries, which is an outbound connection an
	 * attacker's page chooses the destination of.
	 */
	public function aTokenlessGetDoesNotProbeTheUsersMailHost(AcceptanceTester $I)
	{
		$I->amOnPage(self::USERS.'?mode=main&action=test&id='.$this->memberId);

		$I->dontSeeInSource(self::MAIL_TESTED);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * Ends the main administrator's Login As session from an <img> tag.
	 */
	public function aTokenlessGetDoesNotEndTheLoginAsSession(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl('act=loginas&id='.$this->memberId));
		$I->seeInSource('P9_OK loginas');

		$I->amOnPage(self::USERS.'?mode=main&action=logoutas');

		$I->dontSeeInSource(self::LOGGED_OUT_AS);
		$I->seeInSource(self::REFUSED);

		$I->amOnPage(self::DASHBOARD);
		$I->seeInSource(self::LOGGED_IN_AS);
	}

	/**
	 * Writes e_CACHE/dismiss.upgrade.alert.txt, after which the upgrade notice
	 * never appears again on that installation.
	 */
	public function aTokenlessGetDoesNotDismissTheUpgradeNotice(AcceptanceTester $I)
	{
		$I->amOnPage(self::DASHBOARD.'?dismiss=upgrade');

		$I->seeInSource(self::REFUSED);
		$this->seeProbeReports($I, 'flag=0');
	}

	/**
	 * check_all() reaches lancheck::newFile(), which mkdir()s and writes a stub
	 * PHP file for every language file the named pack is missing. Against a name
	 * no pack answers to that is every file there is, across every plugin and
	 * every theme, at a path the query string picks.
	 */
	public function aTokenlessGetDoesNotWriteLanguageStubs(AcceptanceTester $I)
	{
		$I->amOnPage(self::LANGUAGE.'?mode=main&action=tools&sub=verify&lan='.self::SCRATCH_LAN);

		$I->seeInSource(self::REFUSED);
		$this->seeProbeReports($I, 'lanfiles=0');
	}

	/**
	 * update_check() rewrites the db_updates and db_updates_version preferences
	 * and includes every installed plugin's _setup.php on the way.
	 */
	public function aTokenlessGetDoesNotRunTheCoreUpdateCheck(AcceptanceTester $I)
	{
		$I->amOnPage(self::DASHBOARD.'?mode=core&type=update&ajax_used=1');

		$I->assertSame(403, $I->grabResponseCode(), 'a tokenless update check must be refused');
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * The addons panel fetches ADDONFEED and caches the composed HTML for three
	 * hours, so one forged request costs an outbound request and a cache write.
	 */
	public function aTokenlessGetDoesNotFetchTheAddonsFeed(AcceptanceTester $I)
	{
		$I->amOnPage(self::DASHBOARD.'?mode=addons&type=plugin&ajax_used=1');

		$I->assertSame(403, $I->grabResponseCode(), 'a tokenless addons fetch must be refused');
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * getVersionList() asks e107.org for the plugin and the theme version list
	 * and caches each answer for twelve hours, so a forged request costs two
	 * outbound requests and two cache writes.
	 */
	public function aTokenlessGetDoesNotRunTheAddonsUpdateCheck(AcceptanceTester $I)
	{
		$I->amOnPage(self::DASHBOARD.'?mode=addons&type=update&ajax_used=1');

		$I->assertSame(403, $I->grabResponseCode(), 'a tokenless addons update check must be refused');
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * Presence is all the endpoint tests; whether the value is the right one is
	 * attest()'s half. Both halves are needed, so assert the second one too.
	 */
	public function aGetCarryingTheWrongTokenIsRefused(AcceptanceTester $I)
	{
		$I->amOnPage(self::USERS.'?mode=main&action=test&id='.$this->memberId.'&e-token=not-even-close');

		$I->seeInSource('Unauthorized access!');
		$I->dontSeeInSource(self::MAIL_TESTED);
	}

	/**
	 * The control that matters most for the user list: the batch action still
	 * reaches the mail test. It is driven the way the administrator drives it,
	 * through the form on the list page, so a guard that refused the redirect
	 * that form issues would fail here.
	 */
	public function theUserListsOwnBatchActionStillTestsTheAddress(AcceptanceTester $I)
	{
		$I->amOnPage(self::USERS.'?mode=main&action=list');

		$I->sendPostRequest(self::USERS.'?mode=main&action=list', array(
			'useraction' => 'test',
			'userid' => $this->memberId,
			'e-token' => $I->grabFreshAdminToken(self::USERS.'?mode=main&action=list'),
		));

		$I->seeInSource(self::MAIL_TESTED);
		$I->dontSeeInSource(self::REFUSED);
	}

	/**
	 * The other way the administrator reaches logoutas, and the one the guard
	 * has to stay out of the way of. The batch dropdown rewrites the request in
	 * place rather than redirecting, so LogoutasObserver runs inside the POST
	 * itself, with the token in the body where e_token_injector put it and
	 * nothing in the query string. A guard that asked a POST for a token there
	 * would refuse the administrator's own submission.
	 */
	public function theUserListsOwnBatchActionStillEndsTheLoginAsSession(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl('act=loginas&id='.$this->memberId));
		$I->seeInSource('P9_OK loginas');

		$I->amOnPage(self::USERS.'?mode=main&action=list');

		$I->sendPostRequest(self::USERS.'?mode=main&action=list', array(
			'useraction' => 'logoutas',
			'userid' => $this->memberId,
			'e-token' => $I->grabFreshAdminToken(self::USERS.'?mode=main&action=list'),
		));

		$I->seeInSource(self::LOGGED_OUT_AS);
		$I->dontSeeInSource(self::REFUSED);
	}

	/**
	 * The Login As banner publishes its own [logout] link on every admin page,
	 * so follow that rather than a URL of the test's own.
	 */
	public function theLoginAsBannersOwnLinkStillLogsOut(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl('act=loginas&id='.$this->memberId));
		$I->seeInSource('P9_OK loginas');

		$I->amOnPage($this->publishedLink($I, self::DASHBOARD,
			'#users\.php\?mode=main&amp;action=logoutas(&amp;e-token=[^\'"]*)?#'));

		$I->seeInSource(self::LOGGED_OUT_AS);
		$I->dontSeeInSource(self::REFUSED);
	}

	/**
	 * The notice's own "Don't show again" button still dismisses it.
	 */
	public function theUpgradeNoticesOwnButtonStillDismissesIt(AcceptanceTester $I)
	{
		$I->amOnPage($this->publishedLink($I, self::DASHBOARD,
			'#admin\.php\?dismiss=upgrade(&amp;e-token=[^\'"]*)?#'));

		$this->seeProbeReports($I, 'flag=1');
	}

	/**
	 * The language check still runs, and still writes the stubs it exists to
	 * write, for a request carrying a token. The Language Packs list publishes
	 * a verify link only for a pack other than English, and a default install
	 * has none, so the token comes from the page rather than from a link.
	 */
	public function aTokenedVerifyStillRunsTheLanguageCheck(AcceptanceTester $I)
	{
		$token = $I->grabFreshAdminToken(self::USERS.'?mode=main&action=list');

		$I->amOnPage(self::LANGUAGE.'?mode=main&action=tools&sub=verify&lan='
			.self::SCRATCH_LAN.'&e-token='.$token);

		$I->seeInSource('sub=edit');
		$I->dontSeeInSource(self::REFUSED);
		$this->seeProbeReports($I, 'lanfiles=1');
	}

	/**
	 * The dashboard's own scripts still reach both AJAX branches.
	 */
	public function theDashboardsOwnPanelsStillLoad(AcceptanceTester $I)
	{
		$core = $this->publishedLink($I, self::DASHBOARD,
			'#admin\.php\?mode=core&type=update(&e-token=[^\'"]*)?#');
		$addons = $this->publishedLink($I, self::DASHBOARD,
			'#admin\.php\?mode=addons&type=plugin(&e-token=[^\'"]*)?#');
		$addonsUpdate = $this->publishedLink($I, self::DASHBOARD,
			'#admin\.php\?mode=addons&type=update(&e-token=[^\'"]*)?#');

		$I->amOnPage($core.'&ajax_used=1');
		$I->assertSame(200, $I->grabResponseCode(), 'the dashboard update check must still run');
		$I->dontSeeInSource(self::REFUSED);

		$I->amOnPage($addons.'&ajax_used=1');
		$I->assertSame(200, $I->grabResponseCode(), 'the dashboard addons panel must still load');
		$I->dontSeeInSource(self::REFUSED);

		$I->amOnPage($addonsUpdate.'&ajax_used=1');
		$I->assertSame(200, $I->grabResponseCode(), 'the dashboard addons update check must still run');
		$I->dontSeeInSource(self::REFUSED);
	}

	/**
	 * A page that only renders is not a CSRF surface, and gating one would refuse
	 * a bookmark and the browser's back button. They stay reachable bare.
	 */
	public function theReadOnlyAdminPagesStillOpenWithoutAToken(AcceptanceTester $I)
	{
		$pages = array(
			self::DASHBOARD,
			self::USERS.'?mode=main&action=list',
			self::LANGUAGE.'?mode=main&action=tools',
			self::LANGUAGE.'?mode=main&action=db',
		);

		foreach($pages as $page)
		{
			$I->amOnPage($page);

			$I->dontSeeInSource(self::REFUSED);
			$I->dontSeeInSource('Unauthorized access!');
		}
	}

	/**
	 * The probe inserts a member, rewrites a core preference and deletes the
	 * upgrade notice's flag file, so a caller that cannot show this run's
	 * secret has to get nothing at all. A probe left in the docroot by a run
	 * that died is otherwise an anonymous way to change what the site stores.
	 */
	public function theProbeRefusesACallerThatCannotShowTheSecret(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=cleanup');

		$I->seeResponseCodeIs(403);
		$this->seeProbeReports($I, 'member=1');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $expected a fragment of the probe's state line
	 * @return void
	 */
	private function seeProbeReports(AcceptanceTester $I, $expected)
	{
		$I->amOnPage($this->probeUrl('act=state'));
		$I->seeInSource($expected);
	}

	/**
	 * @param string $query
	 * @return string
	 */
	private function probeUrl($query)
	{
		$url = '/'.self::PROBE_FILE.'?probe='.$this->secret;

		return ($query === '') ? $url : $url.'&'.$query;
	}

	/**
	 * Follow whatever the page publishes rather than a URL of the test's own, so
	 * that a guard which broke ordinary admin navigation could not pass.
	 *
	 * @param AcceptanceTester $I
	 * @param string $page admin page that carries the link
	 * @param string $pattern must match the whole link, token group optional
	 * @return string path to request
	 */
	private function publishedLink(AcceptanceTester $I, $page, $pattern)
	{
		$I->amOnPage($page);

		$matches = array();
		if(!preg_match($pattern, $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException($page.' published no link matching '.$pattern);
		}

		return '/e107_admin/'.str_replace('&amp;', '&', $matches[0]);
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$secret = $this->secret;
		$lan = self::SCRATCH_LAN;
		$name = self::MEMBER_NAME;
		$email = self::MEMBER_EMAIL;

		return <<<PROBE
<?php
require_once(__DIR__.'/class2.php');

if(!isset(\$_GET['probe']) || !hash_equals('$secret', \$_GET['probe']))
{
	header('HTTP/1.1 403 Forbidden', true, 403);
	echo 'Unauthorized access!';
	exit;
}

header('Content-Type: text/plain');

\$p9act = isset(\$_GET['act']) ? \$_GET['act'] : '';

\$sql = e107::getDb();
\$flag = e_CACHE.'dismiss.upgrade.alert.txt';

function p9_scratch()
{
	\$found = array();

	foreach(array(e_PLUGIN.'*/languages/', e_THEME.'*/languages/', e_LANGUAGEDIR) as \$root)
	{
		foreach(array('$lan*', '$lan/*') as \$leaf)
		{
			\$hits = glob(\$root.\$leaf);

			if(\$hits)
			{
				\$found = array_merge(\$found, \$hits);
			}
		}
	}

	return \$found;
}

function p9_clearScratch()
{
	foreach(p9_scratch() as \$path)
	{
		if(is_file(\$path))
		{
			unlink(\$path);
		}
	}

	foreach(p9_scratch() as \$path)
	{
		if(is_dir(\$path))
		{
			rmdir(\$path);
		}
	}
}

function p9_memberId(\$sql)
{
	return (int) \$sql->retrieve('user', 'user_id', "user_loginname='$name'");
}

function p9_member(\$sql)
{
	\$found = p9_memberId(\$sql);

	if(\$found)
	{
		return \$found;
	}

	return (int) \$sql->insert('user', array(
		'user_name'      => '$name',
		'user_loginname' => '$name',
		'user_email'     => '$email',
		'user_password'  => '',
		'user_join'      => time(),
		'user_class'     => '',
		'user_admin'     => 0,
		'user_perms'     => '',
		'user_ban'       => 0,
		'user_xup'       => '',
		'user_prefs'     => '',
		'user_signature' => '',
		'user_realm'     => '',
	));
}

switch(\$p9act)
{
	case 'arm':
		\$beforeE107v2 = strtotime('1 January 2010');

		if(!e107::getSession()->has('p9-install-date'))
		{
			e107::getSession()->set('p9-install-date', e107::getPref('install_date'));
		}

		e107::getConfig()->set('install_date', \$beforeE107v2)->save(false, true, false);
		e107::getSession()->set('core-update-checked', false);
		e107::getSession()->set('addons-update-checked', false);

		if(is_file(\$flag))
		{
			unlink(\$flag);
		}

		p9_clearScratch();
		e107::getUser()->logoutAs();

		echo "P9_OK arm uid=".p9_member(\$sql)."\\n";
		break;

	case 'state':
		echo "flag=".(is_file(\$flag) ? 1 : 0)." lanfiles=".(count(p9_scratch()) ? 1 : 0)
			." member=".(p9_memberId(\$sql) ? 1 : 0)."\\n";
		break;

	case 'loginas':
		\$done = e107::getUser()->loginAs((int) \$_GET['id']);
		echo (\$done ? "P9_OK loginas" : "P9_FAIL loginas")."\\n";
		break;

	case 'cleanup':
		\$stored = e107::getSession()->get('p9-install-date');

		if(!empty(\$stored))
		{
			e107::getConfig()->set('install_date', \$stored)->save(false, true, false);
		}

		if(is_file(\$flag))
		{
			unlink(\$flag);
		}

		p9_clearScratch();
		e107::getUser()->logoutAs();
		\$sql->delete('user', "user_loginname='$name'");

		echo "P9_OK cleanup\\n";
		break;

	default:
		echo "P9_FAIL unknown act\\n";
}
PROBE;
	}
}
