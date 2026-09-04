<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class userloginAutoBanTest extends \Codeception\Test\Unit
{
	/** RFC 5737 TEST-NET-2 as eIPHandler stores it. Banning the CLI IP instead would kill the rest of the run. */
	const TEST_IP = '0000:0000:0000:0000:0000:ffff:c633:6401';

	/** A second address, so checkBan()'s per-query cache cannot carry a verdict between tests. */
	const EXPIRED_IP = '0000:0000:0000:0000:0000:ffff:c633:6402';

	const TEST_USER = 'vr9hvictim';
	const TEST_PASS = 'correct horse battery staple';
	const FAIL_LIMIT = 3;

	/** @var userlogin */
	protected $lg;

	/** @var int */
	protected $userId;

	/** @var array */
	protected $prefBackup = array();

	/** @var mixed */
	protected $durationBackup;

	/** @var mixed */
	protected $markerBackup;

	protected function _before()
	{
		$this->clearBanState();

		$this->userId = e107::getDb()->insert('user', array(
			'user_name'      => self::TEST_USER,
			'user_loginname' => self::TEST_USER,
			'user_email'     => self::TEST_USER . '@example.com',
			'user_password'  => md5(self::TEST_PASS),
			'user_join'      => time(),
			'user_ban'       => 0,
			'user_class'     => '',
		));
		$this->assertNotEmpty($this->userId);

		$this->prefBackup = isset($GLOBALS['pref']) ? $GLOBALS['pref'] : array();
		$this->durationBackup = e107::getConfig()->get('ban_durations');
		$this->markerBackup = e107::getConfig()->get('ban_durations_login_default_applied', 0);
		e107::getConfig()->setPref('ban_durations', array(eIPHandler::BAN_TYPE_LOGINS => 1));

		$GLOBALS['pref']['autoban'] = 1;
		$GLOBALS['pref']['failed_login_limit'] = self::FAIL_LIMIT;

		$this->lg = $this->make('userlogin');
		$this->lg->__construct();
		$this->setUserIP($this->lg, self::TEST_IP);
	}

	protected function _after()
	{
		if(!empty($this->userId))
		{
			e107::setRegistry('core/e107/user/' . (int) $this->userId, null);
		}

		e107::getConfig()->set('ban_durations', $this->durationBackup)
			->set('ban_durations_login_default_applied', $this->markerBackup)->save(false, true, false);
		$GLOBALS['pref'] = $this->prefBackup;

		$this->clearBanState();
	}

	protected function clearBanState()
	{
		$sql = e107::getDb();
		$sql->delete('generic', "gen_ip='" . self::TEST_IP . "'");
		$sql->delete('banlist', "banlist_ip='" . self::TEST_IP . "'");
		$sql->delete('user', "user_loginname='" . self::TEST_USER . "'");
		$sql->delete('banlist', "banlist_ip='" . self::EXPIRED_IP . "'");
		e107::getIPHandler()->regenerateFiles();
	}

	protected function setUserIP($login, $ip)
	{
		$property = new ReflectionProperty('userlogin', 'userIP');
		$property->setAccessible(true);
		$property->setValue($login, $ip);
	}

	/**
	 * Reach invalidLogin() from a frame that has a file, the way login() does.
	 * ReflectionMethod::invoke() has none, and logNote() reads one.
	 */
	protected function recordFailure($reason)
	{
		$record = Closure::bind(function ($username, $reason) {
			return $this->invalidLogin($username, $reason);
		}, $this->lg, 'userlogin');

		$record(self::TEST_USER, $reason);
	}

	protected function failedLoginCount()
	{
		return (int) e107::getDb()->count('generic', '(*)',
			"WHERE gen_ip='" . self::TEST_IP . "' AND gen_type='failed_login'");
	}

	protected function loginBanCount()
	{
		return (int) e107::getDb()->count('banlist', '(*)',
			"WHERE banlist_ip='" . self::TEST_IP . "' AND banlist_bantype=" . eIPHandler::BAN_TYPE_LOGINS);
	}

	protected function autoBannedCount()
	{
		return (int) e107::getDb()->count('generic', '(*)',
			"WHERE gen_ip='" . self::TEST_IP . "' AND gen_type='auto_banned'");
	}

	public function testWrongPasswordOnValidAccountIsCounted()
	{
		$this->assertSame(0, $this->failedLoginCount());

		$result = $this->lg->login(self::TEST_USER, 'not the password', 0, '', true);

		$this->assertFalse($result);
		$this->assertSame(1, $this->failedLoginCount());
	}

	public function testWrongPasswordsReachingTheLimitTriggerTheBan()
	{
		for($i = 1; $i <= self::FAIL_LIMIT; $i++)
		{
			$this->lg->login(self::TEST_USER, 'not the password ' . $i, 0, '', true);
		}

		$this->assertSame(self::FAIL_LIMIT, $this->failedLoginCount());
		$this->assertSame(0, $this->loginBanCount());

		$this->lg->login(self::TEST_USER, 'not the password either', 0, '', true);

		$this->assertSame(1, $this->loginBanCount());
		$this->assertSame(1, $this->autoBannedCount());
	}

	protected function seedWhitelist()
	{
		e107::getDb()->insert('banlist', array('data' => array(
			'banlist_id'         => 0,
			'banlist_ip'         => self::TEST_IP,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_WHITELIST,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 1,
			'banlist_reason'     => 'whitelisted',
			'banlist_notes'      => '',
		)));
	}

	public function testAWhitelistedAddressIsNotRecordedAsBanned()
	{
		$this->seedWhitelist();

		for($i = 1; $i <= self::FAIL_LIMIT + 1; $i++)
		{
			$this->lg->login(self::TEST_USER, 'not the password ' . $i, 0, '', true);
		}

		$this->assertSame(self::FAIL_LIMIT + 1, $this->failedLoginCount());
		$this->assertSame(0, $this->loginBanCount());
		$this->assertSame(0, $this->autoBannedCount());
	}

	public function testNoBanIsRaisedWhileFailedLoginBansCannotExpire()
	{
		e107::getConfig()->setPref('ban_durations', array());

		for($i = 0; $i <= self::FAIL_LIMIT; $i++)
		{
			$this->lg->login(self::TEST_USER, 'not the password ' . $i, 0, '', true);
		}

		$this->assertSame(self::FAIL_LIMIT + 1, $this->failedLoginCount());
		$this->assertSame(0, $this->loginBanCount());
		$this->assertSame(0, $this->autoBannedCount());
	}

	public function testDiagnosticModeRecordsNothing()
	{
		$messages = $this->lg->test();

		$this->assertNotEmpty($messages);
		$this->assertSame(0, $this->failedLoginCount());
	}

	public function testANoteIsDiscardedWhenTheAttemptGoesOnToAuthorise()
	{
		$this->lg->login(self::TEST_USER, 'not the password', 0, '', true);
		$this->assertSame(1, $this->failedLoginCount());

		$userData = e107::getDb()->retrieve('user', '*', 'user_id=' . (int) $this->userId);
		$this->lg->validLogin($userData);

		$this->assertSame(0, $this->failedLoginCount());
	}

	public function testAnAttemptThatAuthorisesIsNotItselfBanned()
	{
		for($i = 1; $i < self::FAIL_LIMIT; $i++)
		{
			$this->lg->login(self::TEST_USER, 'not the password ' . $i, 0, '', true);
		}

		$this->lg->login(self::TEST_USER, 'not the password', 0, '', true);
		$userData = e107::getDb()->retrieve('user', '*', 'user_id=' . (int) $this->userId);
		$this->lg->validLogin($userData);

		$this->assertSame(0, $this->loginBanCount());
		$this->assertSame(0, $this->autoBannedCount());
	}

	public function testABanRaisedByAnAttemptThatAuthorisesIsLifted()
	{
		for($i = 1; $i <= self::FAIL_LIMIT; $i++)
		{
			$this->lg->login(self::TEST_USER, 'not the password ' . $i, 0, '', true);
		}

		$this->lg->login(self::TEST_USER, 'not the password', 0, '', true);

		$this->assertSame(1, $this->loginBanCount());

		$userData = e107::getDb()->retrieve('user', '*', 'user_id=' . (int) $this->userId);
		$this->lg->validLogin($userData);

		$this->assertSame(0, $this->loginBanCount());
		$this->assertSame(self::FAIL_LIMIT, $this->failedLoginCount());
	}

	protected function seedFailedLogin($age = 0)
	{
		e107::getDb()->insert('generic', "0, 'failed_login', '".(time() - $age)."', 0, '".self::TEST_IP."', 0, 'seeded'");
	}

	protected function noteText()
	{
		return e107::getDb()->retrieve('generic', 'gen_chardata',
			"gen_ip='" . self::TEST_IP . "' AND gen_type='failed_login'");
	}

	public function testASecondReasonInOneAttemptReplacesTheFirst()
	{
		$this->recordFailure(LOGIN_BAD_PW);
		$this->recordFailure(LOGIN_ABORT);

		$this->assertSame(1, $this->failedLoginCount());
		$this->assertNotFalse(strpos($this->noteText(), 'Alt_auth'));
	}

	public function testTheBanIsDecidedOncePerAttempt()
	{
		for($i = 1; $i <= self::FAIL_LIMIT; $i++)
		{
			$this->seedFailedLogin();
		}

		$this->recordFailure(LOGIN_BAD_PW);
		$this->recordFailure(LOGIN_ABORT);

		$this->assertSame(1, $this->loginBanCount());
		$this->assertSame(1, $this->autoBannedCount());
	}

	public function testFailuresOlderThanTheWindowDoNotBan()
	{
		$stale = userlogin::FAILURE_WINDOW + 60;

		for($i = 0; $i <= self::FAIL_LIMIT; $i++)
		{
			$this->seedFailedLogin($stale);
		}

		$this->lg->login(self::TEST_USER, 'not the password', 0, '', true);

		$this->assertSame(0, $this->loginBanCount());
		$this->assertSame(0, $this->autoBannedCount());
	}

	public function testFailuresInsideTheWindowStillBan()
	{
		for($i = 0; $i <= self::FAIL_LIMIT; $i++)
		{
			$this->seedFailedLogin(60);
		}

		$this->lg->login(self::TEST_USER, 'not the password', 0, '', true);

		$this->assertSame(1, $this->loginBanCount());
	}

	public function testTheShippedInstallDefaultExpiresFailedLoginBans()
	{
		$xml = e107::getXml()->loadXMLfile(e_CORE . "xml/default_install.xml", 'advanced');

		$durations = null;
		$applied = null;
		foreach($xml['prefs']['core'] as $pref)
		{
			if($pref['@attributes']['name'] === 'ban_durations')
			{
				$durations = e107::getArrayStorage()->unserialize($pref['@value']);
			}
			if($pref['@attributes']['name'] === 'ban_durations_login_default_applied')
			{
				$applied = $pref['@value'];
			}
		}

		$this->assertTrue(is_array($durations));
		$this->assertArrayHasKey(eIPHandler::BAN_TYPE_LOGINS, $durations);
		$this->assertGreaterThan(0, $durations[eIPHandler::BAN_TYPE_LOGINS]);
		$this->assertNotEmpty($applied);
	}

	public function testABanCarriesAnExpiryWhenADurationIsConfigured()
	{
		for($i = 0; $i <= self::FAIL_LIMIT; $i++)
		{
			$this->seedFailedLogin(60);
		}

		$this->lg->login(self::TEST_USER, 'not the password', 0, '', true);

		$expires = e107::getDb()->retrieve('banlist', 'banlist_banexpires',
			"banlist_ip='" . self::TEST_IP . "'");

		$this->assertSame(1, $this->loginBanCount());
		$this->assertGreaterThan(time(), $expires);
	}

	protected function seedExpiredBan()
	{
		e107::getDb()->insert('banlist', array('data' => array(
			'banlist_id'         => 0,
			'banlist_ip'         => self::EXPIRED_IP,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_LOGINS,
			'banlist_datestamp'  => time() - 7200,
			'banlist_banexpires' => time() - 60,
			'banlist_admin'      => 1,
			'banlist_reason'     => 'expired',
			'banlist_notes'      => '',
		)));
	}

	protected function expiredBanRows()
	{
		return (int) e107::getDb()->count('banlist', '(*)',
			"WHERE banlist_ip='" . self::EXPIRED_IP . "'");
	}

	/**
	 * Characterises the pre-existing checkBan() expiry that the shipped
	 * ban_durations default relies on; it does not exercise the fix itself.
	 */
	public function testAnExpiredBanLiftsItself()
	{
		$this->seedExpiredBan();
		$this->assertSame(1, $this->expiredBanRows());

		$notBanned = e107::getIPHandler()->checkBan("banlist_ip='" . self::EXPIRED_IP . "'", false, true);

		$this->assertTrue($notBanned);
		$this->assertSame(0, $this->expiredBanRows());
	}

	protected function seedAgedNote($age)
	{
		e107::getDb()->insert('generic', "0, 'failed_login', '".(time() - $age)."', 0, '".self::TEST_IP."', 0, 'aged'");
	}


	public function testHistoryPastRetentionIsPruned()
	{
		$this->seedAgedNote(userlogin::FAILURE_RETENTION + 86400);
		$this->assertSame(1, $this->failedLoginCount());

		$this->lg->login(self::TEST_USER, 'not the password', 0, '', true);

		$this->assertSame(1, $this->failedLoginCount());
	}

	public function testHistoryInsideRetentionIsKept()
	{
		$this->seedAgedNote(userlogin::FAILURE_RETENTION - 86400);

		$this->lg->login(self::TEST_USER, 'not the password', 0, '', true);

		$this->assertSame(2, $this->failedLoginCount());
	}


	protected function liveIndexColumns($name)
	{
		$sql = e107::getDb();
		$sql->gen("SHOW INDEX FROM `#generic`");
		$cols = array();
		while($row = $sql->fetch())
		{
			if($row['Key_name'] === $name) { $cols[(int) $row['Seq_in_index']] = $row['Column_name']; }
		}
		ksort($cols);
		return implode(',', $cols);
	}

	public function testTheShippedSchemaIndexesTheBanCounterQuery()
	{
		$sql = file_get_contents(e_CORE . 'sql/core_sql.php');

		$this->assertNotFalse(strpos($sql, 'KEY gen_type_ip (gen_type,gen_ip)'));
		$this->assertNotFalse(strpos($sql, 'KEY gen_type_ts (gen_type,gen_datestamp)'));
	}

	public function testTheUpgradeRoutineRestoresTheCompositeIndexes()
	{
		require_once(e_ADMIN . 'update_routines.php');

		$sql = e107::getDb();
		$sql->gen("ALTER TABLE `#generic` DROP INDEX `gen_type_ip`");
		$sql->gen("ALTER TABLE `#generic` DROP INDEX `gen_type_ts`");

		$this->assertSame('', $this->liveIndexColumns('gen_type_ip'));
		$this->assertFalse(update_20x_to_latest('check'), 'update_needed() reports by returning false');

		update_20x_to_latest('do');

		$this->assertSame('gen_type,gen_ip', $this->liveIndexColumns('gen_type_ip'));
		$this->assertSame('gen_type,gen_datestamp', $this->liveIndexColumns('gen_type_ts'));
	}

	public function testTheFailedLoginDurationDefaultIsAppliedOnce()
	{
		require_once(e_ADMIN . 'update_routines.php');

		e107::getConfig()->set('ban_durations', array())
			->remove('ban_durations_login_default_applied')->save(false, true, false);

		update_20x_to_latest('do');

		$applied = e107::getPref('ban_durations');
		$this->assertSame(1, (int) $applied[eIPHandler::BAN_TYPE_LOGINS]);
		$this->assertNotEmpty(e107::getPref('ban_durations_login_default_applied'));

		$settled = update_20x_to_latest('check');

		$applied[eIPHandler::BAN_TYPE_LOGINS] = 0;
		e107::getConfig()->set('ban_durations', $applied)->save(false, true, false);

		$this->assertSame($settled, update_20x_to_latest('check'));

		update_20x_to_latest('do');

		$kept = e107::getPref('ban_durations');
		$this->assertSame(0, (int) $kept[eIPHandler::BAN_TYPE_LOGINS]);
	}

	public function testADurationChosenAfterTheDefaultLandedSurvivesTheUpgrade()
	{
		require_once(e_ADMIN . 'update_routines.php');

		e107::getConfig()->set('ban_durations', array(eIPHandler::BAN_TYPE_LOGINS => 2))
			->remove('ban_durations_login_default_applied')->save(false, true, false);

		update_20x_to_latest('do');

		$this->assertNotEmpty(e107::getPref('ban_durations_login_default_applied'));

		e107::getConfig()->set('ban_durations', array(eIPHandler::BAN_TYPE_LOGINS => 0))
			->save(false, true, false);

		update_20x_to_latest('do');

		$kept = e107::getPref('ban_durations');
		$this->assertSame(0, (int) $kept[eIPHandler::BAN_TYPE_LOGINS]);
	}

	public function testUnknownUsernameIsStillCounted()
	{
		$result = $this->lg->login('vr9hnosuchuser', 'not the password', 0, '', true);

		$this->assertFalse($result);
		$this->assertSame(1, $this->failedLoginCount());
	}

	public function testSuccessfulLoginIsNotCounted()
	{
		$result = $this->lg->login(self::TEST_USER, self::TEST_PASS, 0, '', true);

		$this->assertTrue($result);
		$this->assertSame(0, $this->failedLoginCount());
		$this->assertSame(0, $this->loginBanCount());
	}

	public function testBlankPasswordIsNotCounted()
	{
		$result = $this->lg->login(self::TEST_USER, '', 0, '', true);

		$this->assertFalse($result);
		$this->assertSame(0, $this->failedLoginCount());
	}
}
