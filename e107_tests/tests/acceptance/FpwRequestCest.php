<?php

/**
 * Asking fpw.php to reset a password.
 *
 * The form used to answer differently for every outcome, so anyone could ask
 * it which addresses belong to accounts here, whether those accounts were
 * banned or unvalidated, and whether a reset was already outstanding. It also
 * mailed the site's own administrator, with the caller's address in the body,
 * once per request and with nothing throttling it: a stranger held the
 * trigger on the owner's inbox.
 *
 * Now every outcome is the same page, the administrator hears about attempts
 * on their own account in the admin area instead, and one source is rationed
 * to the site's flood setting so the form cannot be run as fast as it can be
 * scripted.
 */
class FpwRequestCest
{
	const PROBE_FILE = 'e107_tests_fpw_probe.php';

	/** The one sentence every request is answered with. */
	const ANSWER = 'If that address belongs to an account here';

	/** What the page used to say instead, each of which was an answer about the account. */
	const TELLS = array(
		'was not found in database',
		'A request has already been sent',
		'unable to send email',
	);

	const MEMBER = 'fpwrequestmember';
	const MEMBER_EMAIL = 'fpwrequestmember@e107-fpw-probe.invalid';
	const BANNED = 'fpwrequestbanned';
	const BANNED_EMAIL = 'fpwrequestbanned@e107-fpw-probe.invalid';
	const UNKNOWN_EMAIL = 'fpwrequestnobody@e107-fpw-probe.invalid';

	/** @var string */
	private $adminEmail;

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->probe('act=setup');

		$this->adminEmail = trim($I->grabProbe('act=adminemail&plain=1'));
		$this->adminEmail = trim((string) substr($this->adminEmail, strpos($this->adminEmail, "\n")));
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnProbe('act=teardown');
	}

	/**
	 * One burst, one assertion set: the answers only mean anything compared
	 * with each other, so they are gathered here rather than split across
	 * tests that could not see one another's pages.
	 */
	public function everyOutcomeGetsTheSameAnswer(AcceptanceTester $I)
	{
		$I->wantTo('learn nothing about who has an account here from asking to reset a password');

		foreach(array(
			'an address nobody here uses' => self::UNKNOWN_EMAIL,
			'a member'                    => self::MEMBER_EMAIL,
			'a banned member'             => self::BANNED_EMAIL,
			'the main administrator'      => $this->adminEmail,
		) as $who => $address)
		{
			$I->probe('act=cleargate');
			$I->probe('act=clearpending');
			$I->resetAllCookies();
			$I->sendPostRequest('/fpw.php', array('pwsubmit' => 1, 'email' => $address));

			$body = $I->grabResponseBody();

			$I->assertSame(200, $I->grabResponseCode(), $who.' must be answered, not redirected away');
			$I->assertStringContainsString(self::ANSWER, $body, $who.' must get the one answer');

			foreach(self::TELLS as $tell)
			{
				$I->assertStringNotContainsString($tell, $body, $who.' must not be told "'.$tell.'"');
			}
		}
	}

	public function theMainAdministratorsAddressMailsNobodyAndIsRecorded(AcceptanceTester $I)
	{
		$I->wantTo('stop a stranger mailing the site owner by naming their address here');

		$I->probe('act=clearmaillog');
		$I->probe('act=clearincident');
		$I->probe('act=cleargate');

		$I->resetAllCookies();
		$I->sendPostRequest('/fpw.php', array('pwsubmit' => 1, 'email' => $this->adminEmail));
		$I->assertStringContainsString(self::ANSWER, $I->grabResponseBody());

		$I->assertSame(0, substr_count($I->grabProbe('act=maillog'), 'Mail-ID='),
			'naming the main administrator must mail nobody at all');

		$incident = $I->grabProbeJson('act=incident');
		$I->assertNotNull($incident, 'the attempt must be recorded for the admin area');
		$I->assertSame(1, $incident['count']);
		$I->assertNotEmpty($incident['ip'], 'the record carries where it came from');
	}

	public function aMemberStillGetsTheirResetLink(AcceptanceTester $I)
	{
		$I->wantTo('still be sent a reset link when I have forgotten my own password');

		$I->probe('act=clearmaillog');
		$I->probe('act=cleargate');
		$I->probe('act=clearpending');

		$I->resetAllCookies();
		$I->sendPostRequest('/fpw.php', array('pwsubmit' => 1, 'email' => self::MEMBER_EMAIL));
		$I->assertStringContainsString(self::ANSWER, $I->grabResponseBody());

		$I->assertSame(1, substr_count($I->grabProbe('act=maillog'), 'Mail-ID='),
			'the account that asked must still be sent its link');
	}

	public function aSecondRequestFromOneSourceIsRationed(AcceptanceTester $I)
	{
		$I->wantTo('stop one caller running the form as fast as they can script it');

		$I->probe('act=clearmaillog');
		$I->probe('act=cleargate');
		$I->probe('act=clearpending');

		$I->resetAllCookies();
		$I->sendPostRequest('/fpw.php', array('pwsubmit' => 1, 'email' => self::MEMBER_EMAIL));
		$I->probe('act=clearpending');
		$I->resetAllCookies();
		$I->sendPostRequest('/fpw.php', array('pwsubmit' => 1, 'email' => self::MEMBER_EMAIL));

		$I->assertStringContainsString(self::ANSWER, $I->grabResponseBody(),
			'a rationed request is answered exactly like every other');
		$I->assertSame(1, substr_count($I->grabProbe('act=maillog'), 'Mail-ID='),
			'the second request inside the window must send nothing');
	}

	public function theAdministratorIsToldInTheAdminAreaAndCanDismissIt(AcceptanceTester $I)
	{
		$I->wantTo('hear about attempts on my own account where I work, and stop hearing about them');

		$I->probe('act=clearincident');
		$I->probe('act=cleargate');

		$I->resetAllCookies();
		$I->sendPostRequest('/fpw.php', array('pwsubmit' => 1, 'email' => $this->adminEmail));

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/admin.php');
		$I->see('attempt(s) to reset the main administrator');

		$source = $I->grabPageSource();
		$matches = array();
		if(!preg_match('#admin\.php\?dismiss=fpw-admin-reset&amp;e-token=[^\'"]+#', $source, $matches))
		{
			throw new \RuntimeException('the dashboard published no dismiss link for the attempts notice');
		}

		$I->amOnPage('/e107_admin/'.str_replace('&amp;', '&', $matches[0]));
		$I->dontSee('attempt(s) to reset the main administrator');

		$I->amOnPage('/e107_admin/admin.php');
		$I->dontSee('attempt(s) to reset the main administrator');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$member = self::MEMBER;
		$memberEmail = self::MEMBER_EMAIL;
		$banned = self::BANNED;
		$bannedEmail = self::BANNED_EMAIL;

		return <<<PHP
<?php
// Fixture for FpwRequestCest.
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

\$sql = e107::getDb();
\$config = e107::getConfig('core');
\$logFile = e_LOG.'mailoutlog.log';
\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';

function fpwprobe_have(\$sql, \$name, \$email, \$ban)
{
	\$found = (int) \$sql->retrieve('user', 'user_id', "user_loginname='".\$name."'");

	if(\$found)
	{
		\$sql->update('user', "user_ban=".\$ban." WHERE user_id=".\$found);

		return \$found;
	}

	return (int) \$sql->insert('user', array(
		'user_name' => \$name, 'user_loginname' => \$name, 'user_email' => \$email,
		'user_password' => '', 'user_join' => time(), 'user_class' => '', 'user_admin' => 0,
		'user_perms' => '', 'user_ban' => \$ban, 'user_xup' => '', 'user_prefs' => '',
		'user_signature' => '', 'user_realm' => '',
	));
}

function fpwprobe_incidents()
{
	return new e107\\Admin\\Incident(new e107\\Cache\\Stamp(e_CACHE));
}

switch(\$act)
{
	case 'setup':
		\$config->set('e107_tests_fpw_mail_backup', \$config->get('mail_log_options', ''));
		\$config->set('mail_log_options', '1,1');
		\$config->save(false, true, false);
		fpwprobe_have(\$sql, '$member', '$memberEmail', 0);
		fpwprobe_have(\$sql, '$banned', '$bannedEmail', 1);
		echo "PROBE_OK\\n";
		break;

	case 'teardown':
		\$config->set('mail_log_options', \$config->get('e107_tests_fpw_mail_backup', ''));
		\$config->remove('e107_tests_fpw_mail_backup');
		\$config->save(false, true, false);
		@unlink(\$logFile);
		\$sql->delete('user', "user_loginname IN ('$member','$banned')");
		\$sql->delete('tmp', "tmp_ip IN ('fpwsource','pwreset')");
		fpwprobe_incidents()->clear(e107\\Admin\\Notices::FPW_ADMIN_RESET);
		(new e107\\Admin\\NoticeSuppression(e107::getConfig(), e107::getLog(), 0))
			->release(e107\\Admin\\Notices::FPW_ADMIN_RESET);
		echo "PROBE_OK\\n";
		break;

	case 'adminemail':
		echo "PROBE_OK\\n".\$sql->retrieve('user', 'user_email', 'user_id=1')."\\n";
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
		\$sql->delete('tmp', "tmp_ip='fpwsource'");
		echo "PROBE_OK\\n";
		break;

	case 'clearpending':
		\$sql->delete('tmp', "tmp_ip='pwreset'");
		echo "PROBE_OK\\n";
		break;

	case 'clearincident':
		fpwprobe_incidents()->clear(e107\\Admin\\Notices::FPW_ADMIN_RESET);
		(new e107\\Admin\\NoticeSuppression(e107::getConfig(), e107::getLog(), 0))
			->release(e107\\Admin\\Notices::FPW_ADMIN_RESET);
		echo "PROBE_OK\\n";
		break;

	case 'incident':
		echo "PROBE_OK\\n".json_encode(fpwprobe_incidents()->last(e107\\Admin\\Notices::FPW_ADMIN_RESET))."\\n";
		break;

	default:
		echo "unknown action\\n";
}
PHP;
	}
}
