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

			if($unasked)
			{
				$I->probe('act=cleargate');
			}

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

		$I->assertSame(1, substr_count($I->grabProbe('act=maillog'), ' at '.self::WIDE_EMAIL.' Mail-ID='),
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

		$I->assertSame(1, substr_count($I->grabProbe('act=maillog'), ' at '.self::NEW_EMAIL.' Mail-ID='),
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

	/**
	 * @param AcceptanceTester $I
	 * @param string $identifier what the visitor typed as their user name or email
	 * @param string|array $password when set, sent with a new address, as the form's second half asks
	 * @return void
	 */
	private function askForResend(AcceptanceTester $I, $identifier, $password = '')
	{
		$I->resetAllCookies();
		$I->sendPostRequest('/signup.php?resend', array(
			'submit_resend'   => 1,
			'resend_email'    => $identifier,
			'resend_newemail' => $password === '' ? '' : self::NEW_EMAIL,
			'resend_password' => $password,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @return int
	 */
	private function mailsSent(AcceptanceTester $I)
	{
		return substr_count($I->grabProbe('act=maillog'), 'Mail-ID=');
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
\$prefs = array('user_reg' => 1, 'user_reg_veri' => 1, 'auth_method' => 'e107', 'mail_log_options' => '1,1');

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
