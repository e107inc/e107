<?php

/**
 * Asking signup.php?resend to send an account its activation email again, as a guest.
 */
class SignupResendCest
{
	const PROBE_FILE = 'e107_tests_signup_resend_probe.php';

	const ANSWER = 'still waiting to be activated';

	const PASSWORD = 'resend-cest-password';

	const WAITING = 'resendcestwaiting';
	const WAITING_DISPLAY = 'Resendcest Waiting';
	const WAITING_EMAIL = 'resendcestwaiting@e107-signup-probe.invalid';
	const ACTIVE = 'resendcestactive';
	const UNKNOWN = 'resendcestnobody';
	const NEW_EMAIL = 'resendcestmoved@e107-signup-probe.invalid';
	const CORRECTED_EMAIL = 'resendcestcorrected@e107-signup-probe.invalid';
	const WIDE_EMAIL = 'resendcestwide@e107-signup-probe.invalid';

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->probe('act=setup');

		$I->haveMember(self::WAITING, self::PASSWORD, array(
			'user_name'  => self::WAITING_DISPLAY,
			'user_email' => self::WAITING_EMAIL,
			'user_ban'   => 2,
			'user_sess'  => 'resendcestsession',
			'user_class' => '',
		));
		$I->haveMember(self::ACTIVE, self::PASSWORD, array('user_sess' => ''));
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnProbe('act=teardown');
	}

	/**
	 * The pages are compared with each other as well as read for the answer, because a page that also named its outcome would still carry the answer.
	 */
	public function everyOutcomeGetsTheSameAnswer(AcceptanceTester $I)
	{
		$I->wantTo('learn nothing about who has an account here from asking for an activation email');

		$I->probe('act=mailquiet');

		$answers = array();

		foreach(array(
			array('a name nobody here uses', self::UNKNOWN, '', true),
			array('a name nobody here uses, with a password', self::UNKNOWN, 'not-the-password', true),
			array('an activated member', self::ACTIVE, '', true),
			array('an unactivated member by login name', self::WAITING, '', true),
			array('an unactivated member asked for a moment ago', self::WAITING, '', false),
			array('an unactivated member by display name', self::WAITING_DISPLAY, '', true),
			array('an unactivated member by address', self::WAITING_EMAIL, '', true),
			array('an unactivated member with a wrong password', self::WAITING, 'not-the-password', true),
			array('an unactivated member with a list for a password', self::WAITING, array('not-the-password'), true),
		) as $outcome)
		{
			list($who, $identifier, $password, $unasked) = $outcome;

			$I->probe($unasked ? 'act=cleargate' : 'act=havegate');

			$this->askForResend($I, $identifier, $password);

			$I->assertSame(200, $I->grabResponseCode(), $who.' must be answered, not redirected away');
			$I->assertStringContainsString(self::ANSWER, $I->grabResponseBody(), $who.' must get the one answer');

			$answers[$who] = $I->grabComparableResponseBody();
		}

		$first = key($answers);

		foreach($answers as $who => $answer)
		{
			$I->assertSame($answers[$first], $answer,
				$who.' must be answered with the same page as '.$first.', to the byte');
		}
	}

	public function anUnactivatedMemberStillGetsTheirActivationEmail(AcceptanceTester $I)
	{
		$I->wantTo('still be sent my activation email when I ask for it');

		$I->probe('act=clearmaillog');
		$I->probe('act=cleargate');

		$this->askForResend($I, self::WAITING);

		$I->assertSame(1, $this->mailsSent($I), 'the account that asked must still be sent its activation email');
	}

	public function aNameAsLongAsTheSiteAllowsIsFoundInAnyAlphabet(AcceptanceTester $I)
	{
		$I->wantTo('still be sent my activation email when my user name is as long as the site allows, in any alphabet');

		$name = str_repeat('ж', 100);
		$I->haveMember($name, self::PASSWORD, array(
			'user_email' => self::WIDE_EMAIL,
			'user_ban'   => 2,
			'user_sess'  => 'resendcestwidesession',
			'user_class' => '',
		));

		$I->probe('act=clearmaillog');
		$I->probe('act=cleargate');

		$this->askForResend($I, $name);

		$I->assertSame(1, $this->mailsSent($I, self::WIDE_EMAIL),
			'a name as long as the account columns hold must be looked up whole, however many bytes its alphabet takes');
	}

	public function aSecondRequestForOneAccountSendsNothingUntilTheWindowCloses(AcceptanceTester $I)
	{
		$I->wantTo('not have my inbox filled by somebody who knows my user name');

		$I->probe('act=clearmaillog');
		$I->probe('act=cleargate');

		$this->askForResend($I, self::WAITING);
		$this->askForResend($I, self::WAITING_EMAIL);

		$I->assertSame(1, $this->mailsSent($I), 'one activation email per account per window, however it is named');

		$I->probe('act=cleargate');
		$this->askForResend($I, self::WAITING_DISPLAY);

		$I->assertSame(2, $this->mailsSent($I), 'the account must be sent its email again once the window has closed');
	}

	public function aResendByNameDoesNotHoldBackTheOwnersCorrection(AcceptanceTester $I)
	{
		$I->wantTo('move my account to the right address even after somebody asked for the email by my name');

		$I->probe('act=clearmaillog');
		$I->probe('act=cleargate');

		$this->askForResend($I, self::WAITING_DISPLAY);
		$this->askForResend($I, self::WAITING, self::PASSWORD);

		$I->assertSame(1, $this->mailsSent($I, self::NEW_EMAIL),
			'the account holder\'s own correction must be sent to the new address');
	}

	public function aWrongPasswordCountsAgainstTheCallerWhetherOrNotTheAccountExists(AcceptanceTester $I)
	{
		$I->wantTo('not learn which names have accounts here from when I get banned for guessing');

		$this->askForResend($I, self::UNKNOWN, 'not-the-password');
		$this->askForResend($I, self::WAITING, 'not-the-password');

		$counts = $I->grabProbeJson('act=failedlogins');

		$I->assertSame($counts[self::WAITING], $counts[self::UNKNOWN],
			'a wrong password must count the same against the caller for a name nobody uses as for an account');
		$I->assertSame(1, $counts[self::UNKNOWN], 'each wrong password must count once');
	}

	public function anOverLongNameIsCountedWithoutStoringAllOfIt(AcceptanceTester $I)
	{
		$I->wantTo('not be able to fill the failed-login list by posting a very long name');

		$this->askForResend($I, str_repeat(self::UNKNOWN, 5000), 'not-the-password');

		$longest = $I->grabProbeJson('act=longestfailure');

		$I->assertSame(1, $longest['rows'], 'the attempt must still be counted against the caller');
		$I->assertLessThan(1000, $longest['length'], 'the note must not carry the whole of what was posted');
	}

	public function aWrongPasswordDoesNotUseUpTheWindow(AcceptanceTester $I)
	{
		$I->wantTo('still get my activation email after somebody else guessed at my password');

		$I->probe('act=clearmaillog');
		$I->probe('act=cleargate');

		$this->askForResend($I, self::WAITING, 'not-the-password');
		$this->askForResend($I, self::WAITING);

		$I->assertSame(1, $this->mailsSent($I), 'a request refused for its password must not hold back the next one');
	}

	public function aFailedSendDoesNotUseUpTheWindow(AcceptanceTester $I)
	{
		$I->wantTo('ask again straight away when my activation email could not be sent');

		$I->probe('act=clearmaillog');
		$I->probe('act=cleargate');

		$I->haveSitePref('mail_log_options', '2,0');
		$this->askForResend($I, self::WAITING);
		$I->haveSitePref('mail_log_options', '1,1');
		$this->askForResend($I, self::WAITING);

		$I->assertSame(1, $this->mailsSent($I, '', 'Fail'), 'the first email must have been tried and not sent');
		$I->assertSame(1, $this->mailsSent($I, '', 'Success'),
			'an email that could not be sent must not hold back the next one');
	}

	public function aFailedSendToAMovedAddressKeepsTheMoveWindowButNotTheResend(AcceptanceTester $I)
	{
		$I->wantTo('be sent my activation email at the address I moved to when the first email there could not be sent');

		$I->probe('act=clearmaillog');
		$I->probe('act=cleargate');

		$I->haveSitePref('mail_log_options', '2,0');
		$this->askForResend($I, self::WAITING, self::PASSWORD);
		$I->haveSitePref('mail_log_options', '1,1');
		$this->askForResend($I, self::WAITING, self::PASSWORD, self::CORRECTED_EMAIL);
		$this->askForResend($I, self::WAITING);

		$I->assertSame(1, $this->mailsSent($I, self::NEW_EMAIL, 'Fail'),
			'the email to the new address must have been tried and not sent');
		$I->assertSame(0, $this->mailsSent($I, self::CORRECTED_EMAIL),
			'a second address move inside the window must not be sent, even after the first one failed');
		$I->assertSame(1, $this->mailsSent($I, self::NEW_EMAIL, 'Success'),
			'a resend by name must be sent to the moved address at once');
	}

	public function aFailedSendToAMovedAddressDoesNotReopenTheResendWindow(AcceptanceTester $I)
	{
		$I->wantTo('not have an address move whose email failed undo the window of a resend that was sent');

		$I->probe('act=clearmaillog');
		$I->probe('act=cleargate');

		$this->askForResend($I, self::WAITING);
		$I->haveSitePref('mail_log_options', '2,0');
		$this->askForResend($I, self::WAITING, self::PASSWORD);
		$I->haveSitePref('mail_log_options', '1,1');
		$this->askForResend($I, self::WAITING);

		$I->assertSame(1, $this->mailsSent($I, self::WAITING_EMAIL, 'Success'), 'the first resend must have been sent');
		$I->assertSame(1, $this->mailsSent($I, self::NEW_EMAIL, 'Fail'),
			'the email to the new address must have been tried and not sent');
		$I->assertSame(0, $this->mailsSent($I, self::NEW_EMAIL, 'Success'),
			'a resend by name inside the window of one that was sent must not be sent, even after a failed move');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $identifier what the visitor typed as their user name or email
	 * @param string|array $password when set, sent with a new address, as the form's second half asks
	 * @param string $newEmail the address the form's second half moves the account to
	 * @return void
	 */
	private function askForResend(AcceptanceTester $I, $identifier, $password = '', $newEmail = self::NEW_EMAIL)
	{
		$I->resetAllCookies();
		$I->sendPostRequest('/signup.php?resend', array(
			'submit_resend'   => 1,
			'resend_email'    => $identifier,
			'resend_newemail' => $password === '' ? '' : $newEmail,
			'resend_password' => $password,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $address only sends to this address, when set
	 * @param string $result only sends the mail log records with this result, 'Success' or 'Fail', when set
	 * @return int
	 */
	private function mailsSent(AcceptanceTester $I, $address = '', $result = '')
	{
		$to = $address === '' ? '\S+' : preg_quote($address, '/');

		return preg_match_all('/ at '.$to.' Mail-ID=\S* - '.$result.'/', $I->grabProbe('act=maillog'));
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$waiting = self::WAITING;
		$unknown = self::UNKNOWN;

		return <<<PHP
<?php
// Fixture for SignupResendCest.
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

\$sql = e107::getDb();
\$config = e107::getConfig('core');
\$logFile = e_LOG.'mailoutlog.log';
\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';
\$prefs = array('user_reg' => 1, 'user_reg_veri' => 1, 'auth_method' => 'e107', 'mail_log_options' => '1,1', 'mailer' => 'sendmail', 'sendmail' => '/bin/false');

switch(\$act)
{
	case 'setup':
		\$backup = array();
		foreach(\$prefs as \$key => \$value)
		{
			\$backup[\$key] = \$config->get(\$key, '');
			\$config->set(\$key, \$value);
		}
		\$config->set('e107_tests_signup_resend_backup', \$backup);
		\$config->save(false, true, false);
		echo "PROBE_OK\\n";
		break;

	case 'teardown':
		foreach((array) \$config->get('e107_tests_signup_resend_backup', array()) as \$key => \$value)
		{
			\$config->set(\$key, \$value);
		}
		\$config->remove('e107_tests_signup_resend_backup');
		\$config->save(false, true, false);
		@unlink(\$logFile);
		\$sql->delete('tmp', "tmp_ip IN ('signupresend','signupresendmove')");
		\$sql->delete('generic', "gen_type='failed_login' AND gen_chardata LIKE '%resendcest%'");
		echo "PROBE_OK\\n";
		break;

	case 'mailquiet':
		\$config->set('mail_log_options', '0,0')->save(false, true, false);
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

	case 'cleargate':
		\$sql->delete('tmp', "tmp_ip IN ('signupresend','signupresendmove')");
		echo "PROBE_OK\\n";
		break;

	case 'havegate':
		require_once(e_HANDLER.'e_signup_class.php');
		\$gate = new \\e107\\Flood\\SourceGate(\$sql, true, e_signup::RESEND_WINDOW);
		\$waitingId = \$sql->retrieve('user', 'user_id', "user_loginname='$waiting'");
		\$gate->record(e_signup::RESEND_FLOOD_KIND, \$waitingId);
		echo \$gate->isClosedTo(e_signup::RESEND_FLOOD_KIND, \$waitingId) ? "PROBE_OK\\n" : "the window did not close\\n";
		break;

	case 'failedlogins':
		\$counts = array();
		foreach(array('$unknown', '$waiting') as \$name)
		{
			\$counts[\$name] = (int) \$sql->count('generic', '(*)', "gen_type='failed_login' AND gen_chardata LIKE '%".\$name."%'");
		}
		echo "PROBE_OK\\n".json_encode(\$counts)."\\n";
		break;

	case 'longestfailure':
		\$row = \$sql->retrieve('generic', 'COUNT(*) AS found, MAX(LENGTH(gen_chardata)) AS longest', "gen_type='failed_login' AND gen_chardata LIKE '%$unknown$unknown%'");
		echo "PROBE_OK\\n".json_encode(array('rows' => (int) \$row['found'], 'length' => (int) \$row['longest']))."\\n";
		break;

	default:
		echo "unknown action\\n";
}
PHP;
	}
}
