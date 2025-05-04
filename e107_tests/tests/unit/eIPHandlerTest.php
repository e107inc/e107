<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


class eIPHandlerTest extends \Codeception\Test\Unit
{

	/** @var eIPHandler */
	protected $ip;

	protected function _before()
	{

		try
		{
			$this->ip = $this->make('eIPHandler');
			$this->ip->regenerateFiles();
		}
		catch(Exception $e)
		{
			$this::fail("Couldn't load eIPHandler object");
		}
	}


	protected function _after()
	{
		e107::setRegistry('core/eIPHandler/checkBan', null);
		e107::getSession('eIPHandler')->clearData();
		$this->ip->regenerateFiles();

	}


	/**
	 * Test IPHandler::ipDecode()
	 */
	public function testIpDecode()
	{

		$this->ip->__construct();

		$this::assertEquals("101.102.103.104", $this->ip->ipDecode("101.102.103.104")); // IPv4 returns itself

		$this::assertEquals("10.11.12.13", $this->ip->ipDecode("0000:0000:0000:0000:0000:ffff:0a0b:0c0d")); // IPv6 uncompressed

		$this::assertEquals("201.202.203.204", $this->ip->ipDecode("00000000000000000000ffffc9cacbcc")); // 32-char hex

		// $this::assertEquals("123.123.123.123", $this->ip->ipDecode("::ffff:7b7b:7b7b")); // Fully compressed IPv6 (not supported)

		// 	$this::assertEquals("192.0.2.128", $this->ip->ipDecode("::ffff:c000:0280")); // RFC 4291 short form (not supported)

		//	$this::assertEquals("8.8.8.8", $this->ip->ipDecode("0:0:0:0:0:ffff:808:808")); // Uncompressed mapped with short ints (not supported)

		//	$this::assertEquals("8.8.4.4", $this->ip->ipDecode("::ffff:808:404")); // Double compressed form (not supported)

		//	$this::assertEquals("1.2.3.4", $this->ip->ipDecode("::ffff:1.2.3.4")); // Embedded dot-decimal IPv4 (not supported)
	}


	public function testGetCurrentIP()
	{

		$reflection = new ReflectionClass($this->ip);
		$method = $reflection->getMethod('getCurrentIP');
		$method->setAccessible(true);

		$tests = [
			0 => [
				'server'   => [
					'REMOTE_ADDR' => '123.123.123.123'
				],
				'expected' => '123.123.123.123'
			]
		];

		foreach($tests as $index => $test)
		{
			$this->ip->setIP(null);
			$result = $method->invoke($this->ip, $test['server']); // IP6
			$expected = $this->ip->ipEncode($test['expected']); // convert to IP6.

			$this::assertSame($expected, $result, "Failed on #$index");
		}

	}


	/**
	 * Test IPHandler::add_ban()
	 */
	public function testAdd_ban()
	{

		$this->ip->__construct();

		$banDurations = array(
			'0'  => 0,
			'-1' => 0, // manually added ban
			'-2' => 0, // flood
			'-3' => 8, // hits
			'-4' => 10, // multi-login
			'-5' => 0, // imported
			'-6' => 0,  // banned user
			'-8' => 0 // unknown
		);

	//	e107::getConfig()->set('ban_durations', $banDurations)->save(false, true, false);

		$result = $this->ip->add_ban(2, "unit test generated ban", '123.123.123.123');
		$this::assertTrue($result);
	}

	public function testIsAddressRoutable()
	{

		$testCases = [
			['ip' => '8.8.8.8', 'expected' => true],
			['ip' => '192.168.1.1', 'expected' => false],
			['ip' => '127.0.0.1', 'expected' => false],
			['ip' => '10.0.0.45', 'expected' => false],
			['ip' => '172.20.5.4', 'expected' => false],
			['ip' => '169.254.1.2', 'expected' => false],
			['ip' => '224.0.0.1', 'expected' => false],
			['ip' => '240.0.0.1', 'expected' => false],
			['ip' => '24.300.0.124', 'expected' => false],
			['ip' => '2001:4860:4860::8888', 'expected' => true],
		];

		foreach($testCases as $case)
		{
			$desc = sprintf("%s should %s be routable", $case['ip'], $case['expected'] ? '' : 'not');
			$result = $this->ip->isAddressRoutable($case['ip']);
			$this::assertSame($case['expected'], $result, $desc);
		}

	}


	/**
	 * Test IPHandler::ipEncode()
	 */
	public function testIpEncode()
	{

		$tests = [
			// IPv4 to IPv6-mapped form
			0  => [
				'ip'        => '192.168.1.100',
				'wildCards' => false,
				'div'       => ':',
				'expected'  => '0000:0000:0000:0000:0000:ffff:c0a8:0164'
			],
			// IPv6
			1  => [
				'ip'        => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
				'wildCards' => false,
				'div'       => ':',
				'expected'  => '2001:0db8:85a3:0000:0000:8a2e:0370:7334'
			],
			// IPv6 (shortened)
			2  => [
				'ip'        => '2001:db8::1',
				'wildCards' => false,
				'div'       => ':',
				'expected'  => '2001:0db8:0000:0000:0000:0000:0000:0001'
			],
			// Zero-padded hex (div = '')
			3  => [
				'ip'        => '127.0.0.1',
				'wildCards' => false,
				'div'       => '',
				'expected'  => '00000000000000000000ffff7f000001'
			],
			// Wildcard input: expects encoded hex with xx
			4  => [
				'ip'        => '192.168.1.*',
				'wildCards' => true,
				'div'       => ':',
				'expected'  => '0000:0000:0000:0000:0000:ffff:c0a8:01xx'
			],

			// Invalid input
			5  => [
				'ip'        => 'not.an.ip',
				'wildCards' => false,
				'div'       => ':',
				'expected'  => '0000:0000:0000:0000:0000:ffff:0000:0000'
			],
			6  => [
				'ip'        => '192.168.1.x',
				'wildCards' => true,
				'div'       => ':',
				'expected'  => '0000:0000:0000:0000:0000:ffff:c0a8:01xx'
			],
			7  => [
				'ip'        => '',
				'wildCards' => false,
				'div'       => ':',
				'expected'  => false
			],
			8  => [
				'ip'        => null,
				'wildCards' => false,
				'div'       => ':',
				'expected'  => false
			],
			9  => [
				'ip'        => '*.*.*.*',
				'wildCards' => true,
				'div'       => ':',
				'expected'  => '0000:0000:0000:0000:0000:ffff:xxxx:xxxx'
			],
			10 => [
				'ip'        => '256.300.1.1', // invalid IP, should be 0-255.
				'wildCards' => false,
				'div'       => ':',
				'expected'  => '0000:0000:0000:0000:0000:ffff:10012c:0101' // should be false
			],
			11 => [
				'ip'        => '::',
				'wildCards' => false,
				'div'       => ':',
				'expected'  => '0000:0000:0000:0000:0000:0000:0000:0000'
			],
			12 => [
				'ip'        => 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff',
				'wildCards' => false,
				'div'       => ':',
				'expected'  => 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'
			],
		];

		foreach($tests as $i => $case)
		{
			$result = $this->ip->ipEncode($case['ip'], $case['wildCards'], $case['div']);
			$msg = "Failed on test #$i ({$case['ip']})";
			$this::assertSame($case['expected'], $result, $msg);
		}

	}


	public function testMakeEmailQuery()
	{

		$email = 'cameron@mydomain.co.uk';

		// Test with empty $fieldname
		$result = $this->ip->makeEmailQuery($email, '');
		$expected = ['cameron@mydomain.co.uk', '*@mydomain.co.uk'];
		$this::assertSame($expected, $result);


		// Test with default $fieldname
		$result = $this->ip->makeEmailQuery($email);
		$expected = "`banlist_ip`='cameron@mydomain.co.uk' OR `banlist_ip`='*@mydomain.co.uk'";
		$this::assertSame($expected, $result);


		// Test invalid email
		$result = $this->ip->makeEmailQuery('invalid_email', '');
		$expected = [];
		$this::assertSame($expected, $result);

	}


	public function testMakeDomainQuery()
	{

		// Test valid domain
		$domain = 'mydomain.co.uk';
		$result = $this->ip->makeDomainQuery($domain, '');
		$expected = ['*.uk', '*.co.uk', '*.mydomain.co.uk'];
		$this::assertSame($expected, $result);


		// Test email address
		$result = $this->ip->makeDomainQuery('user@mydomain.co.uk', '');
		$expected = false;
		$this::assertSame($expected, $result);


		// Test invalid domain
		$result = $this->ip->makeDomainQuery('invalid#domain', '');
		$expected = false;
		$this::assertSame($expected, $result);


		// Test with fieldName
		$result = $this->ip->makeDomainQuery('mydomain.co.uk', 'banlist_ip');
		$expected = [
			"(`banlist_ip`='*.uk')",
			"(`banlist_ip`='*.co.uk')",
			"(`banlist_ip`='*.mydomain.co.uk')"
		];

		$this::assertSame($expected, $result);

	}


	// ----


	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @return void
	 */
	public function testCheckBanNoBan()
	{

		$query = "`banlist_ip`='cameron@mydomain.co.uk' OR `banlist_ip`='*@mydomain.co.uk'";

		// Ensure no ban exists
		e107::getDb()->delete('banlist', "`banlist_ip` IN ('cameron@mydomain.co.uk', '*@mydomain.co.uk')");
		$this->ip->regenerateFiles();

		// Clear session cache
		e107::getSession('eIPHandler')->clearData();

		// Test: no ban
		$result = $this->ip->checkBan($query, true, true);
		$this::assertTrue($result);

		// Verify session cache is set
		$cached = e107::getSession('eIPHandler')->get('ban_check_' . md5($query));
		$this::assertIsArray($cached);
		$this::assertTrue($cached['result']);
		$this::assertArrayHasKey('timestamp', $cached);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @return void
	 */
	public function testCheckBanActiveBan()
	{

		$this->ip->add_ban(1, '', 'cameron@mydomain.co.uk');

		// Test: active ban
		$query = "`banlist_ip`='cameron@mydomain.co.uk' OR `banlist_ip`='*@mydomain.co.uk'";
		$result = $this->ip->checkBan($query, true, true);
		$this::assertFalse($result); // ie. banned.

		// Verify session cache is set
		$cached = e107::getSession('eIPHandler')->get('ban_check_' . md5($query));
		$this::assertIsArray($cached);
		$this::assertFalse($cached['result']);
		$this::assertArrayHasKey('timestamp', $cached);


	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @return void
	 */
	public function testCheckBanThrottling()
	{

		$query = "`banlist_ip`='cameron@mydomain.co.uk' OR `banlist_ip`='*@mydomain.co.uk'";

		// Ensure no ban exists
		e107::getDb()->delete('banlist', "`banlist_ip` IN ('cameron@mydomain.co.uk', '*@mydomain.co.uk')");


		// Test: multiple calls within 1 second
		$startTime = microtime(true);
		$result1 = $this->ip->checkBan($query, true, true);
		$result2 = $this->ip->checkBan($query, true, true);
		$endTime = microtime(true);

		$this::assertTrue($result1);
		$this::assertTrue($result2);
		$this::assertLessThan(1, $endTime - $startTime, "Throttling test took too long, in-memory cache may not be working");

		// Verify session cache is set
		$cached = e107::getSession('eIPHandler')->get('ban_check_' . md5($query));
		$this::assertIsArray($cached);
		$this::assertTrue($cached['result']);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @return void
	 */
	public function testCheckBanCacheExpiration()
	{

		$query = "`banlist_ip`='cameron@mydomain.co.uk' OR `banlist_ip`='*@mydomain.co.uk'";

		// Ensure no ban exists
		e107::getDb()->delete('banlist', "`banlist_ip` IN ('cameron@mydomain.co.uk', '*@mydomain.co.uk')");
		$this->ip->regenerateFiles();

		// Set a cached result (no ban)
		e107::getSession('eIPHandler')->set('ban_check_' . md5($query), [
			'result'    => true,
			'timestamp' => time() - 5 // Within 10 seconds
		]);

		// Test: cached result within 10 seconds
		$result = $this->ip->checkBan($query, true, true);
		$this::assertTrue($result);

		// Simulate cache expiration (11 seconds)
		e107::getSession('eIPHandler')->set('ban_check_' . md5($query), [
			'result'    => true,
			'timestamp' => time() - 11
		]);

		// Insert active ban
		e107::getDb()->insert('banlist', [
			'banlist_ip'         => 'cameron@mydomain.co.uk',
			'banlist_bantype'    => 1,
			'banlist_banexpires' => 0
		]);

		// Test: new ban after expiration
		$result = $this->ip->checkBan($query, true, true);
		$this::assertFalse($result);

		// Cleanup
		e107::getDb()->delete('banlist', "`banlist_ip`='cameron@mydomain.co.uk'");
	}
}

