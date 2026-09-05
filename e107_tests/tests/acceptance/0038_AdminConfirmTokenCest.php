<?php

/**
 * e107's own confirm-your-identity guard on three sensitive admin forms, and
 * the operator-precedence bug that has stopped all three from firing.
 *
 * Three files carry the same line:
 *
 *     if (!$_POST['ac'] == md5(ADMINPWCHANGE))
 *     {
 *         exit;
 *     }
 *
 * PHP parses that as ((!$_POST['ac']) == md5(ADMINPWCHANGE)). The left side is
 * a boolean and the right side is a 32 character string, so on PHP 8 the
 * comparison is false for every non-empty value of ac and the guard never
 * fires. What survives is an accident: the guard rejects a request that omits
 * ac entirely, because !null is true and true == 'cfcd2084...' is true, and
 * accepts literally any other value.
 *
 * ADMINPWCHANGE is the signed-in administrator's own user_pwchange, so the
 * value the forms carry is not guessable across accounts and the guard is a
 * re-confirmation that the request came from a document this administrator was
 * served. Making it work is a behaviour change for anything that has been
 * posting to these endpoints with a value of its own; the positive control in
 * each pair is the assertion that matters most here, because it is the one
 * that says e107's own forms still submit.
 *
 * The three copies are asserted separately. Four published e107 advisories were
 * fixed at one call site while a sibling stayed open, and the third copy here
 * is in a handler rather than an admin page, so a sweep of e107_admin/ alone
 * would miss it.
 *
 * @see e107_admin/users.php:1764           users_admin_ui::AddSubmitTrigger()
 * @see e107_admin/plugin.php:311           plugin_ui::pluginProcessUpload()
 * @see e107_handlers/theme_handler.php:1602  themeHandler::themeUpload()
 * @see class2.php  define('ADMINPWCHANGE', $user->getAdminPwchange())
 */
class AdminConfirmTokenCest
{
	const PROBE_FILE = 'e107_tests_confirmtoken_probe.php';

	const ROUTE_ADD = '/e107_admin/users.php?mode=main&action=add';

	const ROUTE_PLUGIN_UPLOAD = '/e107_admin/plugin.php?mode=avail&action=upload';

	/** The theme upload form posts to e_SELF, with no query string. */
	const ROUTE_THEME = '/e107_admin/theme.php';

	const ROUTE_THEME_UPLOAD = '/e107_admin/theme.php?mode=main&action=upload';

	/**
	 * A well-formed value that is not the one this administrator was served.
	 * Well-formed on purpose: the surviving half of the broken guard rejects an
	 * absent or empty ac, so a test that omitted the field would pass on the
	 * unfixed tree and prove nothing.
	 */
	const WRONG_TOKEN = 'deadbeefdeadbeefdeadbeefdeadbeef';

	/** Login name of the account the quick-add submission creates. */
	const CREATED_USER = 'p7ctcreated';

	/**
	 * Emitted by e107::getFile()->unzipArchive(), which pluginProcessUpload()
	 * and themeUpload() both reach once they are past the guard. Its presence
	 * is the archive handler saying it ran; nothing else on either page says
	 * so, and both pages render in full whether the upload was processed or
	 * not.
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
		$this->reset($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$this->reset($I);
		$I->deleteAppFile(self::PROBE_FILE);
	}

	// -----------------------------------------------------------------
	// users.php
	// -----------------------------------------------------------------

	/**
	 * The quick-add trigger must not act on a submission whose ac is not the
	 * one this administrator was served.
	 *
	 * Read back out of the user table. The trigger's refusal is an exit(), so
	 * the response is a truncated page either way and there is nothing on it to
	 * tell the two apart.
	 */
	public function quickAddRefusesAnIdentityConfirmationValueThatIsNotTheOne(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a quick-add submission carrying the wrong identity-confirmation value');

		$I->loginAsAdmin();
		$I->amOnPage(self::ROUTE_ADD);
		$I->seeResponseCodeIs(200);

		$payload = $this->quickAddPayload($I);
		$payload['ac'] = self::WRONG_TOKEN;

		$I->sendPostRequest(self::ROUTE_ADD, $payload);

		$I->dontSeeInDatabase('e107_user', array('user_loginname' => self::CREATED_USER));
	}

	/**
	 * Positive control: e107's own quick-add form still submits.
	 *
	 * This is the assertion the item turns on. The guard has not fired for any
	 * non-empty value since PHP 8, so making it fire is a behaviour change for
	 * every caller, e107's own forms included, and a form that has drifted away
	 * from emitting the field would break here rather than in production.
	 */
	public function quickAddStillAcceptsTheFormsOwnIdentityConfirmationValue(AcceptanceTester $I)
	{
		$I->wantTo('Keep the quick-add form working when it carries its own identity-confirmation value');

		$I->loginAsAdmin();
		$I->amOnPage(self::ROUTE_ADD);

		$I->sendPostRequest(self::ROUTE_ADD, $this->quickAddPayload($I));

		$I->seeInDatabase('e107_user', array('user_loginname' => self::CREATED_USER));
	}

	// -----------------------------------------------------------------
	// plugin.php
	// -----------------------------------------------------------------

	/**
	 * The plugin uploader must not process a submission whose ac is not the one
	 * this administrator was served.
	 *
	 * plugin_ui::uploadPage() calls pluginProcessUpload() and then
	 * redirectAction('list') unconditionally, so the redirect is what says the
	 * upload was handled: the guard's exit() happens first and leaves the
	 * response on the page it was already rendering.
	 */
	public function thePluginUploaderRefusesAnIdentityConfirmationValueThatIsNotTheOne(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a plugin upload carrying the wrong identity-confirmation value');

		$I->loginAsAdmin();
		$I->amOnPage(self::ROUTE_PLUGIN_UPLOAD);
		$I->seeResponseCodeIs(200);

		$token = $this->grabToken($I);

		$I->stopFollowingRedirects();
		$I->sendPostRequest(self::ROUTE_PLUGIN_UPLOAD, array(
			'upload'        => 'Upload',
			'MAX_FILE_SIZE' => 2000000,
			'ac'            => self::WRONG_TOKEN,
			'e-token'       => $token,
		));

		$code = $I->grabResponseCode();

		$I->assertNotSame(301, $code,
			'plugin.php processed an upload carrying ac = '.self::WRONG_TOKEN.' and redirected to the '
			.'plugin list. Only pluginProcessUpload() returning lets uploadPage() reach that redirect, '
			.'so the guard at plugin.php:311 did not fire.');
	}

	/**
	 * Positive control: the plugin upload form still reaches its handler.
	 */
	public function thePluginUploaderStillAcceptsTheFormsOwnIdentityConfirmationValue(AcceptanceTester $I)
	{
		$I->wantTo('Keep the plugin upload form reaching its handler with its own confirmation value');

		$I->loginAsAdmin();
		$I->amOnPage(self::ROUTE_PLUGIN_UPLOAD);

		$token = $this->grabToken($I);
		$confirm = $this->grabConfirmToken($I);

		$I->stopFollowingRedirects();
		$I->sendPostRequest(self::ROUTE_PLUGIN_UPLOAD, array(
			'upload'        => 'Upload',
			'MAX_FILE_SIZE' => 2000000,
			'ac'            => $confirm,
			'e-token'       => $token,
		));

		$I->assertSame(301, $I->grabResponseCode(),
			'plugin.php no longer processes an upload carrying the ac value its own form renders, so '
			.'the refusal asserted above is a dead page rather than an identity check.');
	}

	// -----------------------------------------------------------------
	// theme_handler.php
	// -----------------------------------------------------------------

	/**
	 * The theme uploader must not process a submission whose ac is not the one
	 * this administrator was served.
	 *
	 * themeHandler::themeUpload() runs from postObserver() in the handler's
	 * constructor, before the page has rendered anything, so the guard's exit()
	 * produces a zero-byte response. Asserting on the byte count would tie the
	 * test to that particular refusal; asserting that the archive handler's own
	 * message is absent says the handler did not run whichever way the guard
	 * declines.
	 */
	public function theThemeUploaderRefusesAnIdentityConfirmationValueThatIsNotTheOne(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a theme upload carrying the wrong identity-confirmation value');

		$I->loginAsAdmin();
		$I->amOnPage(self::ROUTE_THEME_UPLOAD);
		$I->seeResponseCodeIs(200);

		$token = $this->grabToken($I);

		$I->sendPostRequest(self::ROUTE_THEME, array(
			'upload'        => 1,
			'MAX_FILE_SIZE' => 2000000,
			'ac'            => self::WRONG_TOKEN,
			'e-token'       => $token,
		));

		$I->assertStringNotContainsString(self::ARCHIVE_HANDLER_MARKER, $I->grabPageSource(),
			'theme.php processed an upload carrying ac = '.self::WRONG_TOKEN.': the response carries the '
			.'archive handler\'s own message, so themeHandler::themeUpload() ran past the guard at '
			.'theme_handler.php:1602.');
	}

	/**
	 * Positive control: the theme upload form still reaches its handler.
	 */
	public function theThemeUploaderStillAcceptsTheFormsOwnIdentityConfirmationValue(AcceptanceTester $I)
	{
		$I->wantTo('Keep the theme upload form reaching its handler with its own confirmation value');

		$I->loginAsAdmin();
		$I->amOnPage(self::ROUTE_THEME_UPLOAD);

		$token = $this->grabToken($I);
		$confirm = $this->grabConfirmToken($I);

		$I->sendPostRequest(self::ROUTE_THEME, array(
			'upload'        => 1,
			'MAX_FILE_SIZE' => 2000000,
			'ac'            => $confirm,
			'e-token'       => $token,
		));

		$I->assertStringContainsString(self::ARCHIVE_HANDLER_MARKER, $I->grabPageSource(),
			'theme.php no longer reaches its archive handler for an upload carrying the ac value its own '
			.'form renders, so the refusal asserted above is a dead page rather than an identity check.');
	}

	// -----------------------------------------------------------------
	// fixture
	// -----------------------------------------------------------------

	/**
	 * @return array the quick-add form's parameter set, carrying the ac value
	 *         and CSRF token the page currently loaded rendered
	 */
	private function quickAddPayload(AcceptanceTester $I)
	{
		return array(
			'etrigger_submit' => 'Add user',
			'username'        => self::CREATED_USER,
			'loginname'       => self::CREATED_USER,
			'email'           => self::CREATED_USER.'@example.com',
			'realname'        => '',
			'password'        => 'p7ct-Str0ng-Pass',
			'sendconfemail'   => 0,
			'ac'              => $this->grabConfirmToken($I),
			'e-token'         => $this->grabToken($I),
		);
	}

	/**
	 * @return string the CSRF token on the page currently loaded
	 */
	private function grabToken(AcceptanceTester $I)
	{
		$matches = array();

		if(!preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The current page rendered no e-token to post back.');
		}

		return $matches[1];
	}

	/**
	 * @return string the 'ac' identity-confirmation value on the page currently loaded
	 */
	private function grabConfirmToken(AcceptanceTester $I)
	{
		$matches = array();

		if(!preg_match('/name=[\'"]ac[\'"][^>]*value=[\'"]([^\'"]*)[\'"]/', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The current page rendered no ac field to post back.');
		}

		return $matches[1];
	}

	/**
	 * Drop the account this Cest creates through the application, and clear the
	 * request ban: e107 bans an address after 50 requests in a window and every
	 * container request arrives from the same bridge address.
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
// Fixture for 0041_AdminConfirmTokenCest. Removed again in the Cest's _after().
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

\$db = e107::getDb();

switch(isset(\$_GET['act']) ? \$_GET['act'] : '')
{
	case 'reset':
		// Created through the application, so Codeception's Db module, which
		// rolls back only the rows it inserted itself, knows nothing about it.
		\$db->delete('user', "user_loginname LIKE 'p7ct%'");

		echo "PROBE_OK\n";
		break;

	default:
		echo "PROBE_UNKNOWN_ACTION\n";
}
PHP;
	}
}
