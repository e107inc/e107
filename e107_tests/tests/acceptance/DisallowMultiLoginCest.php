<?php

/**
 * With disallowMultiLogin on, a login ends the account's other live sessions and notes it in the rolling log; an expired row is no session and earns no note.
 * Two browsers signing in as one member, under each session save method core has a handler for: the first is signed out, the second stays.
 */
class DisallowMultiLoginCest
{
	const PASSWORD = 'multilogin-pass';

	const PROBE_FILE = 'e107_tests_multilogin_probe.php';

	/** Rolling log title of a login that ended the account's other sessions. */
	const DROPPED = 'LAN_ROLL_LOG_07';

	/** @var string[] */
	private $prefsWere = array();

	public function _before(AcceptanceTester $I)
	{
		foreach(array('disallowMultiLogin' => 1, 'roll_log_active' => 1) as $name => $value)
		{
			$this->prefsWere[$name] = $I->haveSitePref($name, $value);
		}
	}

	/** @var int[] members whose account file a test may have left in the session directory */
	private $members = array();

	public function _after(AcceptanceTester $I)
	{
		foreach($this->members as $userId)
		{
			$I->grabProbe('act=forget&uid='.$userId);
		}

		foreach($this->prefsWere as $name => $was)
		{
			$I->haveSitePref($name, $was === '' ? null : $was);
		}
	}

	/**
	 * Guards the database store, which signed the first browser out before this change as well.
	 */
	public function theSecondSignInSignsTheFirstBrowserOutOnDatabaseSessions(AcceptanceTester $I)
	{
		$this->signInFromTwoBrowsers($I, 'db');
	}

	public function theSecondSignInSignsTheFirstBrowserOutOnFileSessions(AcceptanceTester $I)
	{
		$this->signInFromTwoBrowsers($I, 'files');
	}

	public function theSecondSignInSignsTheFirstBrowserOutOnFileSessionsWithoutLocking(AcceptanceTester $I)
	{
		$this->signInFromTwoBrowsers($I, 'nonblocking');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $method session_save_method for the run
	 */
	private function signInFromTwoBrowsers(AcceptanceTester $I, $method)
	{
		$this->prefsWere['session_save_method'] = $I->haveSitePref('session_save_method', $method);
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());

		$name = 'multilogin'.$method;
		$userId = $I->haveMember($name, self::PASSWORD);
		$this->members[] = $userId;

		$I->loginAsMember($name, self::PASSWORD);
		$first = $this->probe($I);
		$I->assertSame((string) $userId, $first['USER_ID'], 'the first browser is signed in');
		$I->assertSame($method, $first['SAVE_METHOD']);

		$I->loginAsMember($name, self::PASSWORD);
		$second = $this->probe($I);
		$I->assertNotSame($first['SESSION_ID'], $second['SESSION_ID']);

		$this->returnAs($I, $first);
		$I->assertSame('0', $this->probe($I)['USER_ID'], 'the first browser was signed out by the second sign-in');

		$this->returnAs($I, $second);
		$I->assertSame((string) $userId, $this->probe($I)['USER_ID'], 'the second browser stays signed in');
	}

	/**
	 * Empties the jar and puts back the session cookie a browser had, so the next request is that browser's.
	 *
	 * @param AcceptanceTester $I
	 * @param array $browser what {@see DisallowMultiLoginCest::probe()} read in it
	 */
	private function returnAs(AcceptanceTester $I, array $browser)
	{
		$I->resetAllCookies();
		$I->setCookie($browser['SESSION_NAME'], $browser['SESSION_ID'], array('path' => $browser['COOKIE_PATH']));
	}

	/**
	 * @param AcceptanceTester $I
	 * @return array SESSION_NAME, SESSION_ID, COOKIE_PATH, USER_ID and SAVE_METHOD as the application sees them
	 */
	private function probe(AcceptanceTester $I)
	{
		$body = $I->grabProbe();
		$read = array();

		foreach(array('SESSION_NAME', 'SESSION_ID', 'COOKIE_PATH', 'USER_ID', 'SAVE_METHOD') as $key)
		{
			if(!preg_match('/^'.$key.':(.*)$/m', $body, $matches))
			{
				throw new \RuntimeException('Probe reported no '.$key.': '.strip_tags($body));
			}

			$read[$key] = trim($matches[1]);
		}

		return $read;
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<PHP
<?php
// Fixture for DisallowMultiLoginCest.
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

if(isset(\$_GET['act']) && \$_GET['act'] === 'forget')
{
	\$parts = explode(';', (string) session_save_path());
	\$directory = rtrim(end(\$parts), '/');
	\$directory = \$directory === '' ? sys_get_temp_dir() : \$directory;
	foreach((array) glob(\$directory.'/e107_*_'.(int) \$_GET['uid']) as \$accountFile) { unlink(\$accountFile); }
	echo "PROBE_OK forget\\n";
	exit;
}

echo "SESSION_NAME:", session_name(), "\\n";
echo "SESSION_ID:", session_id(), "\\n";
echo "COOKIE_PATH:", defined('e_HTTP') ? e_HTTP : '/', "\\n";
echo "USER_ID:", (int) e107::getUser()->getId(), "\\n";
echo "SAVE_METHOD:", e107::getSession()->getSaveMethod(), "\\n";
PHP;
	}

	public function aLiveSessionOfTheAccountIsEndedAndNoted(AcceptanceTester $I)
	{
		$userId = $I->haveMember('multiloginlive', self::PASSWORD);
		$other = $this->haveSessionOf($I, $userId, time() + 600);
		$lastLog = $this->lastLogId($I);

		$I->loginAsMember('multiloginlive', self::PASSWORD);

		$I->dontSeeInDatabase('e107_session', array('session_id' => $other));
		$I->assertSame(1, $I->grabNumRecords('e107_dblog', array('dblog_id >' => $lastLog, 'dblog_title' => self::DROPPED)));
	}

	public function anExpiredSessionOfTheAccountIsNotNotedAsEnded(AcceptanceTester $I)
	{
		$userId = $I->haveMember('multiloginexpired', self::PASSWORD);
		$this->haveSessionOf($I, $userId, time() - 60);
		$lastLog = $this->lastLogId($I);

		$I->loginAsMember('multiloginexpired', self::PASSWORD);

		$I->assertSame(0, $I->grabNumRecords('e107_dblog', array('dblog_id >' => $lastLog, 'dblog_title' => self::DROPPED)),
			'an expired row is left to the collector, not reported as a session the login ended');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $userId
	 * @param int $expires
	 * @return string the row's session_id
	 */
	private function haveSessionOf(AcceptanceTester $I, $userId, $expires)
	{
		$id = 'sha256$'.hash('sha256', 'multilogin-elsewhere-'.$userId);

		$I->haveInDatabase('e107_session', array(
			'session_id'      => $id,
			'session_expires' => $expires,
			'session_user'    => $userId,
			'session_data'    => '',
		));

		return $id;
	}

	/**
	 * @param AcceptanceTester $I
	 * @return int
	 */
	private function lastLogId(AcceptanceTester $I)
	{
		$ids = $I->grabColumnFromDatabase('e107_dblog', 'dblog_id');

		return $ids ? (int) max($ids) : 0;
	}
}
