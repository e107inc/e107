<?php

/**
 * Each signed-in session is one of its account's sessions: recorded when it signs in, forgotten when it signs out, and signed out once its record is ended from elsewhere.
 * With "Disallow multiple logins" on, a sign-in ends the account's other sessions, whichever store keeps them.
 */
class AccountSessionsCest
{
	const PASSWORD = 'account-sessions-pass';

	const PROBE_FILE = 'e107_tests_account_sessions_probe.php';

	const TABLE = 'e107_user_session';

	/** Rolling log title of a sign-in that ended the account's other sessions. */
	const DROPPED = 'LAN_ROLL_LOG_07';

	/** @var string[] site preferences a test changed, and what they were */
	private $prefsWere = array();

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
	}

	public function _after(AcceptanceTester $I)
	{
		foreach($this->prefsWere as $name => $was)
		{
			$I->haveSitePref($name, $was === '' ? null : $was);
		}

		$this->prefsWere = array();
	}

	public function aSignInIsRecordedUntilItSignsOut(AcceptanceTester $I)
	{
		$userId = $I->haveMember('sessionsrecorded', self::PASSWORD);

		$I->loginAsMember('sessionsrecorded', self::PASSWORD);
		$browser = $this->probe($I);

		$I->assertSame((string) $userId, $browser['USER_ID']);
		$I->seeInDatabase(self::TABLE, array('user_session_handle' => $browser['HANDLE'], 'user_session_user' => $userId));

		$I->probe('act=logout');

		$I->dontSeeInDatabase(self::TABLE, array('user_session_handle' => $browser['HANDLE']));
	}

	public function aSessionEndedElsewhereIsSignedOutUnderANewId(AcceptanceTester $I)
	{
		$userId = $I->haveMember('sessionsended', self::PASSWORD);
		$I->loginAsMember('sessionsended', self::PASSWORD);
		$before = $this->probe($I);

		$I->probe('act=end&uid='.$userId.'&handle='.$before['HANDLE']);

		$after = $this->probe($I);
		$I->assertSame('0', $after['USER_ID'], 'the ended session is a guest');
		$I->assertSame('', $after['HANDLE']);
		$I->assertNotSame($before['SESSION_ID'], $after['SESSION_ID']);
	}

	/**
	 * The page that finds the session ended is the one showing the login form.
	 */
	public function aSessionEndedElsewhereSignsStraightBackInFromTheFormItIsShown(AcceptanceTester $I)
	{
		$userId = $I->haveMember('sessionsreturn', self::PASSWORD);
		$I->loginAsMember('sessionsreturn', self::PASSWORD);
		$I->probe('act=end&uid='.$userId.'&handle='.$this->probe($I)['HANDLE']);

		$this->signInWithTheForm($I, 'sessionsreturn');

		$I->assertSame((string) $userId, $this->probe($I)['USER_ID']);
	}

	public function aSessionSignedInBeforeRecordsWereKeptIsRecorded(AcceptanceTester $I)
	{
		$userId = $I->haveMember('sessionsadopted', self::PASSWORD);
		$I->loginAsMember('sessionsadopted', self::PASSWORD);

		$I->probe('act=unrecord');
		$I->assertSame(0, $I->grabNumRecords(self::TABLE, array('user_session_user' => $userId)));

		$browser = $this->probe($I);
		$I->assertSame((string) $userId, $browser['USER_ID'], 'it stays signed in');
		$I->assertNotSame('', $browser['HANDLE']);
		$I->seeInDatabase(self::TABLE, array('user_session_handle' => $browser['HANDLE'], 'user_session_user' => $userId));
	}

	public function aPasswordChangeEndsTheAccountsOtherSessions(AcceptanceTester $I)
	{
		$userId = $I->haveMember('sessionspassword', self::PASSWORD);
		$I->loginAsMember('sessionspassword', self::PASSWORD);
		$first = $this->probe($I);
		$I->loginAsMember('sessionspassword', self::PASSWORD);
		$second = $this->probe($I);

		$I->probe('act=password');

		$I->assertSame(array($second['HANDLE']), $I->grabColumnFromDatabase(self::TABLE, 'user_session_handle', array('user_session_user' => $userId)));
		$I->assertSame($second['HANDLE'], $this->probe($I)['HANDLE'], 'the session that changed it keeps its record');

		$this->returnAs($I, $first);
		$I->assertSame('0', $this->probe($I)['USER_ID']);
	}

	/**
	 * Guards the fail-open check.
	 */
	public function signingInCarriesOnBeforeTheDatabaseUpdateCreatesTheTable(AcceptanceTester $I)
	{
		$userId = $I->haveMember('sessionsuntabled', self::PASSWORD);
		$I->probe('act=hide');

		try
		{
			$I->loginAsMember('sessionsuntabled', self::PASSWORD);
			$browser = $this->probe($I);

			$I->assertSame((string) $userId, $browser['USER_ID']);
			$I->assertSame('', $browser['HANDLE']);
			$I->assertSame((string) $userId, $this->probe($I)['USER_ID'], 'and stays signed in');
		}
		finally
		{
			$I->probe('act=unhide');
		}
	}

	/**
	 * Guards the database store, which signed the first browser out before as well.
	 */
	public function theSecondSignInSignsTheFirstBrowserOutOnDatabaseSessions(AcceptanceTester $I)
	{
		$this->signInFromTwoBrowsers($I, 'db');
	}

	public function theSecondSignInSignsTheFirstBrowserOutOnFileSessions(AcceptanceTester $I)
	{
		$this->signInFromTwoBrowsers($I, 'files');
	}

	/**
	 * Guards the mode as the only thing that ends other sessions.
	 */
	public function withoutDisallowMultipleLoginsBothBrowsersStaySignedIn(AcceptanceTester $I)
	{
		$this->havePref($I, 'disallowMultiLogin', 0);
		$userId = $I->haveMember('sessionsboth', self::PASSWORD);

		$I->loginAsMember('sessionsboth', self::PASSWORD);
		$first = $this->probe($I);
		$I->loginAsMember('sessionsboth', self::PASSWORD);

		$this->returnAs($I, $first);
		$I->assertSame((string) $userId, $this->probe($I)['USER_ID']);
	}

	public function aLiveSessionOfTheAccountIsEndedAndNoted(AcceptanceTester $I)
	{
		$this->havePref($I, 'disallowMultiLogin', 1);
		$this->havePref($I, 'roll_log_active', 1);
		$userId = $I->haveMember('sessionslive', self::PASSWORD);
		$other = $this->haveSessionOf($I, $userId, 'a', time());
		$lastLog = $this->lastLogId($I);

		$I->loginAsMember('sessionslive', self::PASSWORD);

		$I->dontSeeInDatabase(self::TABLE, array('user_session_handle' => $other));
		$I->assertSame(1, $I->grabNumRecords('e107_dblog', array('dblog_id >' => $lastLog, 'dblog_title' => self::DROPPED)));
	}

	/**
	 * Guards the note, which never reported an idle session.
	 */
	public function anIdleSessionOfTheAccountIsNotNotedAsEnded(AcceptanceTester $I)
	{
		$this->havePref($I, 'disallowMultiLogin', 1);
		$this->havePref($I, 'roll_log_active', 1);
		$userId = $I->haveMember('sessionsidle', self::PASSWORD);
		$this->haveSessionOf($I, $userId, 'b', 1);
		$lastLog = $this->lastLogId($I);

		$I->loginAsMember('sessionsidle', self::PASSWORD);

		$I->assertSame(0, $I->grabNumRecords('e107_dblog', array('dblog_id >' => $lastLog, 'dblog_title' => self::DROPPED)),
			'a session idle for longer than a session lives is collected, not reported as one the sign-in ended');
	}

	public function aSessionFromBeforeRecordsWereKeptIsSignedOutByANewerSignIn(AcceptanceTester $I)
	{
		$this->havePref($I, 'disallowMultiLogin', 1);
		$this->havePref($I, 'session_save_method', 'files');
		$I->haveMember('sessionsolder', self::PASSWORD);

		$I->loginAsMember('sessionsolder', self::PASSWORD);
		$older = $this->probe($I);
		$I->probe('act=unrecord');

		$I->loginAsMember('sessionsolder', self::PASSWORD);

		$this->returnAs($I, $older);
		$I->assertSame('0', $this->probe($I)['USER_ID']);
	}

	/**
	 * A sibling request of the same session may have recorded it already, without its handle reaching the session yet.
	 */
	public function aSessionFromBeforeRecordsWereKeptIsNotSignedOutByAnotherOneBeingRecorded(AcceptanceTester $I)
	{
		$this->havePref($I, 'disallowMultiLogin', 1);
		$userId = $I->haveMember('sessionssibling', self::PASSWORD);
		$I->loginAsMember('sessionssibling', self::PASSWORD);
		$I->probe('act=unrecord');
		$I->haveInDatabase(self::TABLE, array(
			'user_session_handle'  => str_repeat('c', 32),
			'user_session_user'    => $userId,
			'user_session_started' => 0,
			'user_session_seen'    => time(),
			'user_session_ip'      => '',
			'user_session_agent'   => '',
		));

		$I->assertSame((string) $userId, $this->probe($I)['USER_ID']);
	}

	/**
	 * Two browsers sign in as one member with "Disallow multiple logins" on: the first is signed out, the second stays.
	 *
	 * @param AcceptanceTester $I
	 * @param string $method session_save_method for the run
	 */
	private function signInFromTwoBrowsers(AcceptanceTester $I, $method)
	{
		$this->havePref($I, 'disallowMultiLogin', 1);
		$this->havePref($I, 'session_save_method', $method);
		$name = 'sessions'.$method;
		$userId = $I->haveMember($name, self::PASSWORD);

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
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param string|int $value
	 */
	private function havePref(AcceptanceTester $I, $name, $value)
	{
		$was = $I->haveSitePref($name, $value);

		if(!array_key_exists($name, $this->prefsWere))
		{
			$this->prefsWere[$name] = $was;
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $userId
	 * @param string $fill the character the handle repeats
	 * @param int $seen
	 * @return string the handle
	 */
	private function haveSessionOf(AcceptanceTester $I, $userId, $fill, $seen)
	{
		$handle = str_repeat($fill, 32);

		$I->haveInDatabase(self::TABLE, array(
			'user_session_handle'  => $handle,
			'user_session_user'    => $userId,
			'user_session_started' => $seen,
			'user_session_seen'    => $seen,
			'user_session_ip'      => '',
			'user_session_agent'   => '',
		));

		return $handle;
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

	/**
	 * Signs in through the login form with whatever session the browser has, rather than with an empty jar.
	 *
	 * @param AcceptanceTester $I
	 * @param string $name
	 */
	private function signInWithTheForm(AcceptanceTester $I, $name)
	{
		$I->amOnPage('/login.php');
		$I->fillField('username', $name);
		$I->fillField('userpass', self::PASSWORD);
		$I->click('userlogin');
	}

	/**
	 * Empties the jar and puts back the session cookie a browser had, so the next request is that browser's.
	 *
	 * @param AcceptanceTester $I
	 * @param array $browser what {@see AccountSessionsCest::probe()} read in it
	 */
	private function returnAs(AcceptanceTester $I, array $browser)
	{
		$I->resetAllCookies();
		$I->setCookie($browser['SESSION_NAME'], $browser['SESSION_ID'], array('path' => $browser['COOKIE_PATH']));
	}

	/**
	 * @param AcceptanceTester $I
	 * @return array SESSION_NAME, SESSION_ID, COOKIE_PATH, USER_ID, SAVE_METHOD and HANDLE as the application sees them
	 */
	private function probe(AcceptanceTester $I)
	{
		$body = $I->grabProbe();
		$read = array();

		foreach(array('SESSION_NAME', 'SESSION_ID', 'COOKIE_PATH', 'USER_ID', 'SAVE_METHOD', 'HANDLE') as $key)
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
		return <<<'PHP'
<?php
// Fixture for AccountSessionsCest.
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$table = MPREFIX.'user_session';

switch(isset($_GET['act']) ? $_GET['act'] : '')
{
	case 'end':
		e107::getSession()->getAccountSessions()->end((int) $_GET['uid'], (string) $_GET['handle']);
		echo "PROBE_OK end\n";
		break;

	case 'unrecord':
		$_SESSION[e_COOKIE] = implode('.', array_slice(explode('.', $_SESSION[e_COOKIE]), 0, 2));
		e107::getDb()->createQueryBuilder()->delete('user_session')->where('user_session_user', (int) e107::getUser()->getId())->execute();
		echo "PROBE_OK unrecord\n";
		break;

	case 'password':
		$password = 'changed-'.mt_rand();
		$userId = (int) e107::getUser()->getId();
		e107::getDb()->createQueryBuilder()->update('user')->set('user_password', $password)->where('user_id', $userId)->execute();
		e107::getUserSession()->makeUserCookie(array('user_id' => $userId, 'user_password' => $password));
		echo "PROBE_OK password\n";
		break;

	case 'logout':
		e107::getUser()->logout();
		echo "PROBE_OK logout\n";
		break;

	case 'hide':
		e107::getDb()->execute('RENAME TABLE `'.$table.'` TO `'.$table.'_hidden`');
		echo "PROBE_OK hide\n";
		break;

	case 'unhide':
		e107::getDb()->execute('RENAME TABLE `'.$table.'_hidden` TO `'.$table.'`');
		echo "PROBE_OK unhide\n";
		break;

	default:
		$token = explode('.', isset($_SESSION[e_COOKIE]) ? (string) $_SESSION[e_COOKIE] : '');
		echo "SESSION_NAME:", session_name(), "\n";
		echo "SESSION_ID:", session_id(), "\n";
		echo "COOKIE_PATH:", defined('e_HTTP') ? e_HTTP : '/', "\n";
		echo "USER_ID:", (int) e107::getUser()->getId(), "\n";
		echo "SAVE_METHOD:", e107::getSession()->getSaveMethod(), "\n";
		echo "HANDLE:", isset($token[2]) ? $token[2] : '', "\n";
}
PHP;
	}
}
