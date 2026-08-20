<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class userloginAutoBanTest extends \Test\Unit
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

	protected function _before()
	{
		$this->clearBanState();

		$this->userId = e107::getDb()->createQueryBuilder()->insert('user')->insertGetId(array(
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

		e107::getConfig()->setPref('ban_durations', $this->durationBackup);
		$GLOBALS['pref'] = $this->prefBackup;

		$this->clearBanState();
	}

	protected function clearBanState()
	{
		$sql = e107::getDb();
		$sql->createQueryBuilder()->delete('generic')->where('gen_ip', self::TEST_IP)->execute();
		$sql->createQueryBuilder()->delete('banlist')->where('banlist_ip', self::TEST_IP)->execute();
		$sql->createQueryBuilder()->delete('user')->where('user_loginname', self::TEST_USER)->execute();
		$sql->createQueryBuilder()->delete('banlist')->where('banlist_ip', self::EXPIRED_IP)->execute();
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
		return e107::getDb()->createQueryBuilder()->from('generic')
			->where('gen_ip', self::TEST_IP)->where('gen_type', 'failed_login')->count();
	}

	protected function loginBanCount()
	{
		return e107::getDb()->createQueryBuilder()->from('banlist')
			->where('banlist_ip', self::TEST_IP)
			->where('banlist_bantype', eIPHandler::BAN_TYPE_LOGINS)->count();
	}

	protected function autoBannedCount()
	{
		return e107::getDb()->createQueryBuilder()->from('generic')
			->where('gen_ip', self::TEST_IP)->where('gen_type', 'auto_banned')->count();
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

		$userData = e107::getDb()->createQueryBuilder()->select('*')->from('user')
			->where('user_id', (int) $this->userId)->fetchRow();
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
		$userData = e107::getDb()->createQueryBuilder()->select('*')->from('user')
			->where('user_id', (int) $this->userId)->fetchRow();
		$this->lg->validLogin($userData);

		$this->assertSame(0, $this->loginBanCount());
		$this->assertSame(0, $this->autoBannedCount());
	}

	protected function seedFailedLogin($age = 0)
	{
		e107::getDb()->createQueryBuilder()->insert('generic')->values(array(
			'gen_type'      => 'failed_login',
			'gen_datestamp' => time() - $age,
			'gen_user_id'   => 0,
			'gen_ip'        => self::TEST_IP,
			'gen_intdata'   => 0,
			'gen_chardata'  => 'seeded',
		))->execute();
	}

	protected function noteText()
	{
		return e107::getDb()->createQueryBuilder()->select('gen_chardata')->from('generic')
			->where('gen_ip', self::TEST_IP)->where('gen_type', 'failed_login')->fetchOne();
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
		foreach($xml['prefs']['core'] as $pref)
		{
			if($pref['@attributes']['name'] === 'ban_durations')
			{
				$durations = e107::getArrayStorage()->unserialize($pref['@value']);
			}
		}

		$this->assertTrue(is_array($durations));
		$this->assertArrayHasKey(eIPHandler::BAN_TYPE_LOGINS, $durations);
		$this->assertGreaterThan(0, $durations[eIPHandler::BAN_TYPE_LOGINS]);
	}

	public function testABanCarriesAnExpiryWhenADurationIsConfigured()
	{
		for($i = 0; $i <= self::FAIL_LIMIT; $i++)
		{
			$this->seedFailedLogin(60);
		}

		$this->lg->login(self::TEST_USER, 'not the password', 0, '', true);

		$expires = e107::getDb()->createQueryBuilder()->select('banlist_banexpires')->from('banlist')
			->where('banlist_ip', self::TEST_IP)->fetchOne();

		$this->assertSame(1, $this->loginBanCount());
		$this->assertGreaterThan(time(), $expires);
	}

	protected function seedExpiredBan()
	{
		e107::getDb()->createQueryBuilder()->insert('banlist')->valuesTyped(array(
			'banlist_id'         => 0,
			'banlist_ip'         => self::EXPIRED_IP,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_LOGINS,
			'banlist_datestamp'  => time() - 7200,
			'banlist_banexpires' => time() - 60,
			'banlist_admin'      => 1,
			'banlist_reason'     => 'expired',
			'banlist_notes'      => '',
		))->execute();
	}

	protected function expiredBanRows()
	{
		return e107::getDb()->createQueryBuilder()->from('banlist')
			->where('banlist_ip', self::EXPIRED_IP)->count();
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
