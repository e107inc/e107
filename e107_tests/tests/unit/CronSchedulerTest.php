<?php

/**
 * cronScheduler's request intake, HTTP answer and run stamps.
 *
 * @see cronScheduler
 */
class CronSchedulerTest extends \Test\Unit
{
	/** @var array */
	private $savedGet;

	/** @var array */
	private $savedServer;

	/** @var mixed */
	private $savedPwd;

	protected function _before()
	{
		require_once(e_HANDLER.'cron_class.php');
		require_once(codecept_data_dir('CronSchedulerTestDouble.php'));

		$this->savedGet = $_GET;
		$this->savedServer = $_SERVER;
		$this->savedPwd = e107::getConfig()->get('e_cron_pwd');

		$this->forgetStamps();
	}

	protected function _after()
	{
		$_GET = $this->savedGet;
		$_SERVER = $this->savedServer;
		e107::getConfig()->set('e_cron_pwd', $this->savedPwd);

		$this->forgetStamps();
	}

	private function forgetStamps()
	{
		cronScheduler::clearRefusals();
		@unlink(e_CACHE.'cronLastRun.php');
		@unlink(e_CACHE.'cronLastLoad.php');

		foreach((array) glob(e_CACHE.'cronNotice_*.php') as $notice)
		{
			@unlink($notice);
		}
	}

	/**
	 * @return array
	 */
	public function tokenProvider()
	{
		return array(
			'query string' => array(
				array('token' => 'abc123'), array(), 'abc123',
			),
			'query string wins over argv' => array(
				array('token' => 'fromquery'),
				array('argv' => array('cron.php', 'token=fromargv')),
				'fromquery',
			),
			'argv with token= prefix' => array(
				array(), array('argv' => array('cron.php', 'token=fromargv')), 'fromargv',
			),
			'bare argv' => array(
				array(), array('argv' => array('cron.php', 'bareargv')), 'bareargv',
			),
			'argv is trimmed' => array(
				array(), array('argv' => array('cron.php', " token=padded\n")), 'padded',
			),
			'nothing presented' => array(
				array(), array('argv' => array('cron.php')), '',
			),
			'an array token is no token' => array(
				array('token' => array('x')), array(), '',
			),
			'an empty query token falls through to argv' => array(
				array('token' => ''), array('argv' => array('cron.php', 'fromargv')), 'fromargv',
			),
		);
	}

	/**
	 * @dataProvider tokenProvider
	 */
	public function testTokenFromRequest($get, $server, $expected)
	{
		$this->assertSame($expected, cronScheduler::tokenFromRequest($get, $server));
	}

	public function testHttpResponseShapes()
	{
		list($status, $body) = cronScheduler::httpResponse(true, true);
		$this->assertSame(200, $status);
		$this->assertSame("OK\n", $body);

		list($status, $wrong) = cronScheduler::httpResponse(false, true);
		$this->assertSame(403, $status);

		list($status, $missing) = cronScheduler::httpResponse(false, false);
		$this->assertSame(403, $status);

		$this->assertNotSame($wrong, $missing, 'a wrong token and a missing token are told apart');
		$this->assertStringContainsString('Schedule Tasks', $wrong);
		$this->assertStringContainsString('Schedule Tasks', $missing);
	}

	public function testRefusalStampRoundTrip()
	{
		$this->assertNull(cronScheduler::lastRefusal(), 'no stamp before any refusal');

		$scheduler = new CronSchedulerTestDouble();
		$scheduler->ip = '203.0.113.5';

		$scheduler->refuse(cronScheduler::VIA_HTTP, false);
		$first = cronScheduler::lastRefusal();

		$this->assertSame(1, $first['count']);
		$this->assertSame('missing', $first['token']);
		$this->assertSame(cronScheduler::VIA_HTTP, $first['via']);
		$this->assertSame($first['first'], $first['last']);
		$this->assertGreaterThan(0, $first['first']);

		$scheduler->refuse(cronScheduler::VIA_HTTP, true);
		$second = cronScheduler::lastRefusal();

		$this->assertSame(2, $second['count'], 'a refusal inside the window counts on');
		$this->assertSame($first['first'], $second['first']);
		$this->assertSame('wrong', $second['token']);

		cronScheduler::clearRefusals();
		$this->assertNull(cronScheduler::lastRefusal());
	}

	public function testRefusalCountRollsOverAfterTheWindow()
	{
		$scheduler = new CronSchedulerTestDouble();
		$scheduler->ip = '203.0.113.5';
		$scheduler->refuse(cronScheduler::VIA_CLI, true);

		$stale = cronScheduler::lastRefusal();
		$stale['first'] = time() - cronScheduler::REFUSAL_WINDOW - 1;
		file_put_contents(e_CACHE.'cronRefused.php', cronScheduler::STAMP_PREFIX.json_encode($stale));

		$scheduler->refuse(cronScheduler::VIA_CLI, true);
		$fresh = cronScheduler::lastRefusal();

		$this->assertSame(1, $fresh['count']);
		$this->assertGreaterThan($stale['first'], $fresh['first']);
		$this->assertSame('', $fresh['ip'], 'a command line refusal records no address');
	}

	public function testGarbageStampsReadAsNothing()
	{
		file_put_contents(e_CACHE.'cronRefused.php', 'not a stamp');
		$this->assertNull(cronScheduler::lastRefusal());

		file_put_contents(e_CACHE.'cronRefused.php', cronScheduler::STAMP_PREFIX.'{"first":1,"last":2,"count":3,"via":"telepathy","token":"wrong"}');
		$this->assertNull(cronScheduler::lastRefusal(), 'an unknown entry point is not reported');

		file_put_contents(e_CACHE.'cronRefused.php', cronScheduler::STAMP_PREFIX.'{"first":1,"last":2,"count":3,"via":"http","ip":"<b>x</b>","token":"wrong"}');
		$this->assertSame('', cronScheduler::lastRefusal()['ip'], 'an address that does not parse is dropped');

		file_put_contents(e_CACHE.'cronLastRun.php', cronScheduler::STAMP_PREFIX.'[1,2,3]');
		$this->assertNull(cronScheduler::lastRun());
	}

	public function testLastRunFallsBackToTheLegacyStamp()
	{
		$this->assertNull(cronScheduler::lastRun());

		file_put_contents(e_CACHE.'cronLastLoad.php', '1700000000');
		$this->assertSame(array('time' => 1700000000, 'via' => '', 'ip' => ''), cronScheduler::lastRun());

		$scheduler = new CronSchedulerTestDouble();
		$scheduler->ran(cronScheduler::VIA_CLI);
		$run = cronScheduler::lastRun();

		$this->assertSame(cronScheduler::VIA_CLI, $run['via']);
		$this->assertGreaterThanOrEqual(time() - 5, $run['time']);
	}

	public function testNoticeIsDueThrottlesAndFailsClosed()
	{
		$scheduler = new CronSchedulerTestDouble();

		$this->assertTrue($scheduler->due('unit-test', 3600));
		$this->assertFalse($scheduler->due('unit-test', 3600), 'a repeat inside the interval is not due');
		$this->assertTrue($scheduler->due('unit-test', 0), 'a zero interval is always due');

		$this->assertFalse($scheduler->due('../../unwritable/'.str_repeat('x', 400), 3600),
			'a record that cannot be written means no notice');
	}

	public function testConstructorToleratesAMissingDebugFlag()
	{
		global $_E107;

		$saved = $_E107;
		unset($_E107['debug']);

		try
		{
			$scheduler = new cronScheduler();
			$this->assertInstanceOf('cronScheduler', $scheduler);
		}
		finally
		{
			$_E107 = $saved;
		}
	}

	public function testRunReturnsFalseAndStampsARefusalOnAWrongToken()
	{
		e107::getConfig()->set('e_cron_pwd', 'unit-test-token');
		$_GET = array('token' => 'not-it');

		$scheduler = new CronSchedulerTestDouble();
		$scheduler->ip = '203.0.113.9';

		$this->assertFalse($scheduler->run(cronScheduler::VIA_HTTP));

		$refusal = cronScheduler::lastRefusal();
		$this->assertSame('wrong', $refusal['token']);
		$this->assertSame(cronScheduler::VIA_HTTP, $refusal['via']);
		$this->assertSame('203.0.113.9', $refusal['ip']);
		$this->assertNull(cronScheduler::lastRun());
		$this->assertCount(1, $scheduler->mails, 'the owner is told once');
		$this->assertStringContainsString('over HTTP from 203.0.113.9', $scheduler->mails[0]['message']);
		$this->assertStringNotContainsString('not-it', $scheduler->mails[0]['message']);

		$this->assertFalse($scheduler->run(cronScheduler::VIA_HTTP));
		$this->assertCount(1, $scheduler->mails, 'a repeat inside the interval mails nobody');
	}

	public function testRunReturnsFalseSilentlyOnAMissingToken()
	{
		e107::getConfig()->set('e_cron_pwd', 'unit-test-token');
		$_GET = array();
		unset($_SERVER['argv']);

		$scheduler = new CronSchedulerTestDouble();

		$this->assertFalse($scheduler->run(cronScheduler::VIA_CLI));
		$this->assertSame('missing', cronScheduler::lastRefusal()['token']);
		$this->assertCount(0, $scheduler->mails);
	}

	public function testRunReturnsTrueAndStampsTheRunOnTheRightToken()
	{
		e107::getConfig()->set('e_cron_pwd', 'unit-test-token');
		$_GET = array();
		$_SERVER['argv'] = array('cron.php', 'token=unit-test-token');

		$scheduler = new CronSchedulerTestDouble();
		$scheduler->ip = '203.0.113.9';

		$this->assertTrue($scheduler->run(cronScheduler::VIA_HTTP));

		$run = cronScheduler::lastRun();
		$this->assertSame(cronScheduler::VIA_HTTP, $run['via']);
		$this->assertSame('203.0.113.9', $run['ip']);
		$this->assertFileExists(e_CACHE.'cronLastLoad.php');
		$this->assertCount(0, $scheduler->mails);
	}
}
