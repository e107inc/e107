<?php

/**
 * themeHandler::postObserver() is the theme manager's whole POST surface, and
 * until this package it ran for callers who are not administrators at all.
 *
 * The chain is short and entirely in core. themeHandler::__construct() calls
 * postObserver() unconditionally (theme_handler.php). class2.php's
 * init_session() constructs a themeHandler on the FRONT END for any signed-in
 * member whose class matches the allow_theme_select preference, so that the
 * user-theme menu can offer them a theme of their own:
 *
 *     if ($user->checkClass(e107::getPref('allow_theme_select', false), false))
 *     {
 *         if (isset($_POST['settheme']))
 *         {
 *             if(e107::getPref('sitetheme') !== $_POST['sitetheme'])
 *             {
 *                 $utheme = new themeHandler;
 *
 * e107_admin/theme.php gates on getperms('1|TMP'). That caller gates on
 * nothing, and postObserver() acts on installplugin, git_pull, installContent,
 * submit_style, submit_adminstyle, setMenuPreset and upload. Installing a
 * plugin is arbitrary code execution on the next request, so this is a member
 * taking the site.
 *
 * The confirm-your-identity value the theme uploader checks is no barrier
 * here: e_system_user::getAdminPwchange() returns false for a caller who is not
 * an administrator, so ADMINPWCHANGE is false and md5(ADMINPWCHANGE) is
 * md5(''), the same constant for every member on every site.
 *
 * The permission has to sit on postObserver() rather than on any one sink,
 * because the sinks are seven branches of one method, and it must not sit so
 * high that the legitimate front-end caller loses the theme picker: class2.php
 * reads $utheme->themeArray straight afterwards. Both halves are asserted here.
 *
 * @see e107_handlers/theme_handler.php  themeHandler::__construct(), postObserver()
 * @see class2.php  init_session(), the allow_theme_select branch
 * @see e107_admin/theme.php  the getperms('1|TMP') the front-end caller does not have
 */
class ThemeHandlerFrontEndCest
{
	const PROBE_FILE = 'e107_tests_themefront_probe.php';

	/** An ordinary member: no user_admin, no user_perms. */
	const MEMBER = 'p7thmember';

	/** What the attack writes into the core preference set. */
	const OWNED_ADMINCSS = 'p7th-owned.css';

	/** What the positive control writes, from an entry point that may. */
	const LEGITIMATE_ADMINCSS = 'p7th-legitimate.css';

	/**
	 * A theme that is not the one the site is running, resolved per request.
	 *
	 * The front-end branch only constructs a themeHandler when the posted theme
	 * differs from the site's, so naming a theme literally would make every
	 * attack in this Cest a no-op the day the install fixture changes theme -
	 * and it would pass, because nothing would have happened.
	 *
	 * @var string|null
	 */
	private $otherTheme;

	/**
	 * Emitted by e107::getFile()->unzipArchive(), which themeUpload() reaches
	 * once it is past its own guard.
	 *
	 * A submission carrying no file part arrives with no file name at all and is
	 * refused before the archive is opened. This is the prefix that refusal
	 * shares with the one for an archive ZipArchive cannot open, whose own half
	 * of the message names the reason.
	 *
	 * @see e_file::unzipArchive()
	 */
	const ARCHIVE_HANDLER_MARKER = "Couldn't open the archive.";

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->startFollowingRedirects();
		$this->otherTheme = null;
		$this->reset($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$this->reset($I);
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * A member rewrites the site's core preferences.
	 *
	 * themeHandler::setAdminStyle() pushes admincss, adminstyle and adminpref
	 * straight into e107::getConfig() and saves, so the branch is a write to
	 * the site configuration by whoever posted it. Read back through a probe,
	 * because core preferences live serialised inside one e107_core row and
	 * because postObserver() runs inside init_session(): the page that comes
	 * back is whatever the front page was going to be either way.
	 */
	public function anOrdinaryMemberCannotRewriteCorePreferencesThroughTheThemeHandler(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a core preference write driven from the front-end theme picker');

		$this->allowMemberThemeSelect($I);
		$this->loginAsMember($I);

		$before = $this->dump($I);

		$I->sendPostRequest('/', $this->themeSelectPayload($I, array(
			'submit_adminstyle' => 1,
			'curTheme'          => $this->otherTheme($I),
			'admincss'          => self::OWNED_ADMINCSS,
			'adminstyle'        => 'p7th-owned',
			'adminpref'         => 1,
		)));

		$after = $this->dump($I);

		$I->assertSame($before['admincss'], $after['admincss'],
			'An ordinary member rewrote the core preference admincss to '
			.var_export($after['admincss'], true).' by posting submit_adminstyle alongside a '
			.'front-end theme selection. themeHandler::postObserver() runs from the constructor, '
			.'and class2.php constructs a themeHandler for any member the allow_theme_select '
			.'preference admits.');

		$I->assertSame($before['adminstyle'], $after['adminstyle'],
			'An ordinary member rewrote the core preference adminstyle from the front end.');
	}

	/**
	 * The sink the package's confirm-your-identity item was aimed at, driven
	 * from the caller the item never considered.
	 *
	 * md5(ADMINPWCHANGE) is md5('') for a member, so the value posted here is
	 * the one the repaired guard accepts. What must stop the request is that
	 * the caller is not a theme administrator, not that they guessed wrong.
	 */
	public function anOrdinaryMemberCannotReachTheThemeUploader(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a theme upload driven from the front-end theme picker');

		$this->allowMemberThemeSelect($I);
		$this->loginAsMember($I);

		$I->sendPostRequest('/', $this->themeSelectPayload($I, array(
			'upload'        => 1,
			'MAX_FILE_SIZE' => 2000000,
			'ac'            => md5(''),
		)));

		$I->assertStringNotContainsString(self::ARCHIVE_HANDLER_MARKER, $I->grabPageSource(),
			'A front-end request by an ordinary member reached themeHandler::themeUpload(): the '
			.'response carries the archive handler\'s own message. md5(ADMINPWCHANGE) is md5(\'\') '
			.'for every caller who is not an administrator, so the ac guard is not what refuses this.');
	}

	/**
	 * Positive control for the guard's placement: the front-end theme picker
	 * that owns this call site must still work.
	 *
	 * class2.php dereferences $utheme->themeArray immediately after
	 * constructing the handler, so a guard that returns before that array is
	 * built takes the feature out with the vulnerability. The member's chosen
	 * theme lands in their own user preferences, which is what the user-theme
	 * menu reads back.
	 */
	public function anOrdinaryMemberCanStillChooseTheirOwnTheme(AcceptanceTester $I)
	{
		$I->wantTo('Keep the front-end theme picker working for a member who may use it');

		$this->allowMemberThemeSelect($I);
		$memberId = $this->loginAsMember($I);

		$I->sendPostRequest('/', $this->themeSelectPayload($I));

		$prefs = (string) $I->grabFromDatabase('e107_user', 'user_prefs',
			array('user_id' => $memberId));

		$I->assertStringContainsString($this->otherTheme($I), $prefs,
			'A member allowed to choose their own theme can no longer do so: '.$this->otherTheme($I)
			.' is not in their user_prefs after posting the picker. class2.php reads '
			.'$utheme->themeArray straight after constructing the handler, so a guard placed above '
			.'that assignment removes the feature the call site exists for.');
	}

	/**
	 * Positive control for the guard itself: the theme manager must still act
	 * on the same POST when the caller is entitled to it.
	 *
	 * Driven through e107_admin/theme.php, which is the gated entry point, so
	 * what this proves is that postObserver() still processes submit_adminstyle
	 * rather than that the branch was deleted.
	 */
	public function aThemeAdministratorStillWritesCorePreferencesThroughTheSameObserver(AcceptanceTester $I)
	{
		$I->wantTo('Keep the theme manager writing its own preferences for an administrator');

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/theme.php');
		$I->seeResponseCodeIs(200);

		$I->sendPostRequest('/e107_admin/theme.php', array(
			'submit_adminstyle' => 1,
			'admincss'          => self::LEGITIMATE_ADMINCSS,
			'adminstyle'        => 'p7th-legitimate',
			'adminpref'         => 1,
			'e-token'           => $this->grabToken($I),
		));

		$after = $this->dump($I);

		$I->assertSame(self::LEGITIMATE_ADMINCSS, (string) $after['admincss'],
			'e107_admin/theme.php no longer writes admincss through postObserver(), so the refusal '
			.'asserted above is a deleted branch rather than a permission check.');
	}

	// -----------------------------------------------------------------
	// fixture
	// -----------------------------------------------------------------

	/**
	 * The POST the front-end theme picker sends, plus whatever else is being
	 * smuggled alongside it.
	 *
	 * @param array $extra
	 * @return array
	 */
	private function themeSelectPayload(AcceptanceTester $I, array $extra = array())
	{
		return array_merge(array(
			'settheme'  => 1,
			'sitetheme' => $this->otherTheme($I),
			'e-token'   => $this->grabSessionToken($I),
		), $extra);
	}

	/**
	 * A theme that ships with this install and is not the one the site runs.
	 *
	 * @return string
	 */
	private function otherTheme(AcceptanceTester $I)
	{
		if($this->otherTheme !== null)
		{
			return $this->otherTheme;
		}

		$I->amOnPage('/'.self::PROBE_FILE.'?act=themes');
		$body = $I->grabPageSource();
		$matches = array();

		if(!preg_match('/PROBE_SITETHEME=(\S*)\s+PROBE_THEMES=(\S*)/', $body, $matches))
		{
			throw new \RuntimeException('The probe reported no theme list: '.trim(strip_tags($body)));
		}

		foreach(explode(',', $matches[2]) as $theme)
		{
			if($theme !== '' && $theme !== $matches[1] && $theme !== '_blank')
			{
				$this->otherTheme = $theme;

				return $theme;
			}
		}

		throw new \RuntimeException('This install ships only one usable theme, so the front-end '
			.'theme picker cannot be driven: '.$body);
	}

	/**
	 * Open the front-end theme picker to members, which is the configuration
	 * the vulnerable call site exists to serve.
	 *
	 * @return void
	 */
	private function allowMemberThemeSelect(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=allow');

		if(strpos($I->grabPageSource(), 'PROBE_OK') === false)
		{
			throw new \RuntimeException('Could not open allow_theme_select to members.');
		}
	}

	/**
	 * Seed an ordinary member and sign in on the front end.
	 *
	 * @return int user id
	 */
	private function loginAsMember(AcceptanceTester $I)
	{
		$memberId = (int) $I->haveInDatabase('e107_user', array(
			'user_name'      => self::MEMBER,
			'user_loginname' => self::MEMBER,
			'user_email'     => self::MEMBER.'@example.com',
			'user_password'  => md5(self::MEMBER),
			'user_join'      => 1262304000,
			'user_class'     => '',
			'user_admin'     => 0,
			'user_perms'     => '',
			'user_pwchange'  => 0,
			'user_xup'       => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
		));

		$I->sendPostRequest('/', array(
			'userlogin' => 'Login',
			'username'  => self::MEMBER,
			'userpass'  => self::MEMBER,
			'autologin' => 0,
			'e-token'   => $this->grabSessionToken($I),
		));

		$I->amOnPage('/'.self::PROBE_FILE.'?act=whoami');

		$I->assertStringContainsString('PROBE_USER='.$memberId, $I->grabPageSource(),
			'The member did not sign in, so every assertion below would pass against a logged-out '
			.'session that never reaches the theme handler at all.');

		return $memberId;
	}

	/**
	 * @return array the core preferences this Cest watches
	 */
	private function dump(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=dump');

		$body = $I->grabPageSource();
		$matches = array();

		if(!preg_match('/PROBE_DUMP(.*)PROBE_END/s', $body, $matches))
		{
			throw new \RuntimeException('Fixture dump failed: '.trim(strip_tags($body)));
		}

		$decoded = json_decode(trim($matches[1]), true);

		if(!is_array($decoded))
		{
			throw new \RuntimeException('Fixture dump was not JSON: '.trim($matches[1]));
		}

		return $decoded;
	}

	/**
	 * @return string the CSRF token on the page currently loaded
	 */
	private function grabToken(AcceptanceTester $I)
	{
		$source = $I->grabPageSource();
		$matches = array();

		if(!preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', $source, $matches))
		{
			throw new \RuntimeException('The current page rendered no e-token to post back.');
		}

		return $matches[1];
	}

	/**
	 * The front page renders no form of its own on a default install, so the
	 * session's token is read from the application rather than scraped.
	 *
	 * A token says who is holding the session, never who may write, so reading
	 * it this way cannot make an authorisation test pass.
	 *
	 * @return string
	 */
	private function grabSessionToken(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=token');

		$matches = array();

		if(!preg_match('/PROBE_TOKEN=(\S+)/', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The probe reported no session token.');
		}

		return $matches[1];
	}

	/**
	 * Put back the preference this Cest opens, drop the accounts and plugin
	 * state it creates through the application, and clear the request ban.
	 */
	private function reset(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=reset');

		$body = $I->grabPageSource();

		if(strpos($body, 'PROBE_OK') === false)
		{
			throw new \RuntimeException('Fixture reset failed: '.trim(strip_tags($body)));
		}
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<PHP
<?php
// Fixture for 0042_ThemeHandlerFrontEndCest. Removed again in the Cest's _after().
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

\$core = e107::getConfig('core');
\$db = e107::getDb();

switch(isset(\$_GET['act']) ? \$_GET['act'] : '')
{
	case 'allow':
		// e_UC_MEMBER. The user-theme menu ships with this preference set to
		// nobody, and an operator who turns it on is the configuration the
		// vulnerable call site serves.
		\$core->set('allow_theme_select', 253)->save(false, true, false);
		echo "PROBE_OK\n";
		break;

	case 'whoami':
		echo 'PROBE_USER='.(int) USERID."\n";
		break;

	case 'token':
		echo 'PROBE_TOKEN='.e_TOKEN."\n";
		break;

	case 'dump':
		echo 'PROBE_DUMP'.json_encode(array(
			'admincss'   => \$core->get('admincss'),
			'adminstyle' => \$core->get('adminstyle'),
		))."PROBE_END\n";
		break;

	case 'themes':
		require_once(e_HANDLER.'theme_handler.php');
		echo 'PROBE_SITETHEME='.e107::getPref('sitetheme')."\n";
		echo 'PROBE_THEMES='.implode(',', array_keys(e107::getTheme()->getList()))."\n";
		break;

	case 'reset':
		\$db->delete('user', "user_loginname LIKE 'p7th%'");

		\$core->set('allow_theme_select', 0)
			->set('admincss', 'css/bootstrap-dark.min.css')
			->set('adminstyle', 'infopanel')
			->set('adminpref', 0)
			->save(false, true, false);

		echo "PROBE_OK\n";
		break;

	default:
		echo "PROBE_UNKNOWN_ACTION\n";
}
PHP;
	}
}
