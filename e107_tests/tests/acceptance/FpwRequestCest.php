<?php

/**
 * Asking fpw.php for a password reset link, and the one-per-account ration on asking again.
 */
class FpwRequestCest
{
	const PROBE_FILE = 'e107_tests_fpw_request_probe.php';

	const PASSWORD = 'fpw-request-password';

	const MEMBER = 'fpwrequestmember';
	const MEMBER_EMAIL = 'fpwrequestmember@e107-fpw-probe.invalid';
	const NUMBERED_EMAIL = 'fpwrequestnumbered@e107-fpw-probe.invalid';

	/** What a request that sends a link is answered with. */
	const SENT = 'An email has been sent to you with a link';

	/** What a request is answered with while the account has a reset outstanding. */
	const OUTSTANDING = 'A request has already been sent to reset this password';

	/** @var int */
	private $memberId;

	/** @var string */
	private $mailLogWas = '';

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->probe('act=clear');
		$this->mailLogWas = $I->haveSitePref('mail_log_options', '1,1');

		$this->memberId = $I->haveMember(self::MEMBER, self::PASSWORD, array('user_email' => self::MEMBER_EMAIL));
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveSitePref('mail_log_options', $this->mailLogWas === '' ? null : $this->mailLogWas);
		$I->probe('act=clear');
	}

	public function aSecondRequestForOneAccountSendsNothingUntilTheFirstExpires(AcceptanceTester $I)
	{
		$I->wantTo('not have my inbox filled by somebody who knows my address');

		$this->askForReset($I, self::MEMBER_EMAIL);
		$this->askForReset($I, self::MEMBER_EMAIL);

		$I->assertStringContainsString(self::OUTSTANDING, $I->grabResponseBody(),
			'a request the account already has outstanding must be answered as one');
		$I->assertSame(1, $this->mailsSentTo($I, self::MEMBER_EMAIL),
			'one reset link per account per window, however often it is asked for');
	}

	public function aResetOutstandingForAnotherAccountDoesNotHoldThisOneBack(AcceptanceTester $I)
	{
		$I->wantTo('be sent my reset link when my login name is the id of somebody who asked for theirs');

		$I->haveMember((string) $this->memberId, self::PASSWORD, array('user_email' => self::NUMBERED_EMAIL));

		$this->askForReset($I, self::MEMBER_EMAIL);
		$this->askForReset($I, self::NUMBERED_EMAIL);

		$I->assertStringContainsString(self::SENT, $I->grabResponseBody(),
			'another account\'s outstanding reset must not be taken for this one\'s');
		$I->assertSame(1, $this->mailsSentTo($I, self::NUMBERED_EMAIL),
			'the account that asked must be sent its own link');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $email
	 * @return void
	 */
	private function askForReset(AcceptanceTester $I, $email)
	{
		$I->resetAllCookies();
		$I->sendPostRequest('/fpw.php', array('pwsubmit' => 1, 'email' => $email));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $email
	 * @return int
	 */
	private function mailsSentTo(AcceptanceTester $I, $email)
	{
		return substr_count($I->grabProbe('act=maillog'), ' at '.$email.' Mail-ID=');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<PHP
<?php
// Fixture for FpwRequestCest.
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

\$logFile = e_LOG.'mailoutlog.log';
\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';

switch(\$act)
{
	case 'clear':
		@unlink(\$logFile);
		e107::getDb()->delete('tmp', "tmp_ip='pwreset'");
		echo "PROBE_OK\\n";
		break;

	case 'maillog':
		echo "PROBE_OK\\n";
		echo is_readable(\$logFile) ? file_get_contents(\$logFile) : '';
		break;

	default:
		echo "unknown action\\n";
}
PHP;
	}
}
