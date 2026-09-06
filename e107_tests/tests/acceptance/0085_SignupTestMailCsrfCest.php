<?php

/**
 * signup.php?test sends a real email, from a GET nothing was guarding.
 *
 * e_signup::run() routes the query string, and the 'test' branch calls
 * sendEmailPreview(), which hands a rendered activation email to
 * e107Email::sendEmail() addressed to the main administrator's own account.
 * The only gate on it is getperms('0'), and a permission is not a defence
 * against request forgery: in a forgery the victim is the one holding the
 * permission and the forged request rides their session, so any page the main
 * administrator visited could make the site send that mail, as often as it
 * liked.
 *
 * The e-token in a query string is e107's marker for a state-changing GET, the
 * way e107_admin/plugin.php, theme.php and language.php already use it: the
 * endpoint tests that one is present and attest() decides whether it is the
 * right one. The last two cases are the controls: the button the signup page
 * publishes for the administrator still sends the mail, and the two preview
 * queries beside it, which only render, still open from a bare URL.
 *
 * mail_log_options is set to "1,1" for the duration, which logs each message
 * and sends nothing, so "was an email sent" is a question the log can answer.
 * use_coppa is cleared with it, because the age gate renders instead of the
 * signup page and the administrator's own buttons are on the signup page.
 *
 * The fixture writes a probe into the docroot, so it answers nobody who cannot
 * show the secret this run minted for it, and it loads class2.php before it
 * looks at what it was asked to do. A probe left behind by a run that died is
 * otherwise an anonymous way to put the whole site's mail into a dry run, to
 * clear the age gate on registration, and to read back every message the site
 * has sent, addresses and bodies alike.
 *
 * @see e107_handlers/session_handler.php  e_session::attest()
 * @see e107_handlers/e_signup_class.php   e_signup::sendEmailPreview()
 */
class SignupTestMailCsrfCest
{
	const PROBE_FILE = 'e107_tests_signup_csrf.php';

	const SIGNUP = '/signup.php';

	/** e107Email writes this for every message it accepts, dry run included. */
	const SENT = 'Mail-ID=';

	/** A distinctive fragment of LAN_SIGNUP_REFUSED_TOKEN_MISSING. */
	const REFUSED = 'no security token';

	/**
	 * A GET that the framework does police: attest() refuses any e-token it
	 * cannot validate, whatever the request method, and answers with this.
	 */
	const UNAUTHORIZED = 'Unauthorized access!';

	/** @var string what a caller shows to prove it is this run of this case */
	private $secret;

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$this->secret = substr(hash('sha256', uniqid('', true).mt_rand()), 0, 32);
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$this->probe($I, 'act=setup');
		$this->probe($I, 'act=clearmaillog');
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'act=teardown');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * One <img> tag on any page the main administrator visits, and the site
	 * mails them.
	 */
	public function aTokenlessGetDoesNotSendTheTestEmail(AcceptanceTester $I)
	{
		$I->amOnPage(self::SIGNUP.'?test');
		$answer = $I->grabPageSource();

		$I->assertStringNotContainsString(self::SENT, $this->mailLog($I),
			'a query string alone must not make the site send mail');
		$I->assertStringContainsString(self::REFUSED, $answer,
			'the administrator must be told why no mail was sent');
	}

	/**
	 * Presence is all the endpoint tests; whether the value is the right one is
	 * attest()'s half. Both halves are needed, so assert the second one too.
	 */
	public function aGetCarryingTheWrongTokenIsRefused(AcceptanceTester $I)
	{
		$I->amOnPage(self::SIGNUP.'?test&e-token=not-even-close');
		$answer = $I->grabPageSource();

		$I->assertStringNotContainsString(self::SENT, $this->mailLog($I),
			'a token that does not validate must not make the site send mail');
		$I->assertStringContainsString(self::UNAUTHORIZED, $answer,
			'a token that does not validate must be refused by the framework');
	}

	/**
	 * The control that matters most. A guard on a button the administrator
	 * reaches by ordinary navigation is worse than the hole it closes if the
	 * button stops working, so this follows whatever the signup page publishes
	 * rather than a URL of the test's own.
	 */
	public function theSignupPagesOwnButtonStillSendsTheTestEmail(AcceptanceTester $I)
	{
		$I->amOnPage($this->publishedTestLink($I));

		$I->dontSeeInSource(self::REFUSED);
		$I->dontSeeInSource(self::UNAUTHORIZED);
		$I->assertStringContainsString(self::SENT, $this->mailLog($I),
			'the administrator must still be able to send themselves a test');
	}

	/**
	 * A query that renders rather than acts is not a CSRF surface, and gating
	 * one would refuse a bookmark and the browser's back button. The two
	 * preview queries beside the test button stay reachable bare.
	 */
	public function thePreviewQueriesStillOpenWithoutAToken(AcceptanceTester $I)
	{
		foreach(array('preview', 'preview.aftersignup') as $query)
		{
			$I->amOnPage(self::SIGNUP.'?'.$query);

			$I->dontSeeInSource(self::REFUSED);
			$I->dontSeeInSource(self::UNAUTHORIZED);
		}

		$I->assertStringNotContainsString(self::SENT, $this->mailLog($I),
			'a preview must render the email rather than send it');
	}

	/**
	 * The probe rewrites core preferences, so a caller that cannot show this
	 * run's secret has to get nothing at all. A probe left in the docroot by a
	 * run that died is otherwise an anonymous way to stop the site sending mail.
	 */
	public function theProbeRefusesACallerThatCannotShowTheSecret(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=teardown');

		$I->seeResponseCodeIs(403);
		$I->assertSame('MAILLOG=1,1 COPPA=0', trim($this->probe($I, 'act=state')),
			'a refused caller must not have put the mail preferences back');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string path to follow, tokenised exactly as the signup page
	 *   published it
	 */
	private function publishedTestLink(AcceptanceTester $I)
	{
		$I->amOnPage(self::SIGNUP);

		if(!preg_match("#href='([^']*signup\.php\?test[^']*)'#", $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('the signup page published no test activation link');
		}

		return html_entity_decode($matches[1]);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function mailLog(AcceptanceTester $I)
	{
		return $this->probe($I, 'act=maillog');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage($this->probeUrl($query));

		return $I->grabPageSource();
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
	 * @return string
	 */
	private function probeSource()
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
\$config = e107::getConfig('core');
\$logFile = e_LOG.'mailoutlog.log';

switch(\$act)
{
	case 'setup':
		\$config->set('e107_tests_signup_mail_backup', \$config->get('mail_log_options', ''));
		\$config->set('e107_tests_signup_coppa_backup', \$config->get('use_coppa', ''));
		\$config->set('mail_log_options', '1,1');
		\$config->set('use_coppa', '0');
		\$config->save(false, true, false);
		echo "PROBE_OK\\n";
		break;

	case 'teardown':
		\$config->set('mail_log_options', \$config->get('e107_tests_signup_mail_backup', ''));
		\$config->set('use_coppa', \$config->get('e107_tests_signup_coppa_backup', ''));
		\$config->remove('e107_tests_signup_mail_backup');
		\$config->remove('e107_tests_signup_coppa_backup');
		\$config->save(false, true, false);
		@unlink(\$logFile);
		echo "PROBE_OK\\n";
		break;

	case 'clearmaillog':
		@unlink(\$logFile);
		echo "PROBE_OK\\n";
		break;

	case 'maillog':
		echo "PROBE_OK\\n";
		echo is_readable(\$logFile) ? file_get_contents(\$logFile) : '';
		break;

	case 'state':
		echo 'MAILLOG='.\$config->get('mail_log_options', '').' COPPA='.\$config->get('use_coppa', '');
		break;

	default:
		echo "PROBE_UNKNOWN\\n";
		break;
}
PHP;
	}
}
