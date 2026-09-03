<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Ip\Range;
use e107\Ip\RangeFile;
use e107\Ip\RangeSet;
use e107\Reflection\ReflectionMethod;
use e107\Reflection\ReflectionProperty;

class eIPHandlerTest extends \Test\Unit
{
	const VISITOR = '198.51.100.77';
	const NEIGHBOUR = '198.51.100.78';
	const TEST_NET = '198.51.100.0/24';
	const STRANGER = '203.0.113.9';
	const OUTSIDER = '203.0.113.99';
	const REASON = 'eIPHandlerTest';

	/** @var eIPHandler */
	protected $ip;

	/** @var string[] scratch directories to remove */
	private $scratchDirs = array();

	/** @var mixed the enable_rdns pref as found */
	private $savedRdns = null;

	protected function _before()
	{
		$this->savedRdns = e107::getConfig()->get('enable_rdns');
		unset($_SERVER['REMOTE_ADDR']);

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
		unset($_SERVER['REMOTE_ADDR']);
		putenv('REMOTE_ADDR');
		e107::getConfig()->set('enable_rdns', $this->savedRdns);

		e107::getDb()->createQueryBuilder()->delete('banlist')->where('banlist_reason', self::REASON)->execute();

		$this->ip->regenerateFiles();

		foreach($this->scratchDirs as $dir)
		{
			array_map('unlink', glob($dir.'*'));
			rmdir($dir);
		}
	}

	/**
	 * @param string $ip stored as given
	 * @param int $type
	 * @param int $expires
	 * @return int banlist_id
	 */
	private function haveRow($ip, $type, $expires = 0)
	{
		$id = e107::getDb()->insert('banlist', array(
			'banlist_id'         => 0,
			'banlist_ip'         => $ip,
			'banlist_bantype'    => $type,
			'banlist_datestamp'  => time() - 60,
			'banlist_banexpires' => $expires,
			'banlist_admin'      => 0,
			'banlist_reason'     => self::REASON,
			'banlist_notes'      => '',
		));
		self::assertNotEmpty($id, 'could not write the banlist row this test needs');

		return (int) $id;
	}

	/**
	 * A compiled range table in a directory of its own, for constructing the
	 * handler without touching the site's files or the database.
	 *
	 * @param array[] $rows each: text, id, type, expires
	 * @param int $others rows that are patterns rather than addresses
	 * @return string the directory, with a trailing slash
	 */
	private function haveTable(array $rows, $others = 0)
	{
		$dir = sys_get_temp_dir().'/e107_iph_'.uniqid('', true).'/';
		mkdir($dir, 0777, true);
		$this->scratchDirs[] = $dir;

		$set = new RangeSet();
		foreach($rows as $row)
		{
			$set->add(Range::fromString($row[0]), $row[1], $row[2], $row[3], $row[0]);
		}
		for($i = 0; $i < $others; $i++)
		{
			$set->addOther();
		}
		$set->compile();
		file_put_contents($dir.eIPHandler::BAN_FILE_RANGES_NAME.eIPHandler::BAN_FILE_EXTENSION, RangeFile::render($set));

		return $dir;
	}

	/**
	 * @param string $name
	 * @return mixed a private property of the handler under test
	 */
	private function handlerProperty($name)
	{
		$property = new ReflectionProperty(eIPHandler::class, $name);

		return $property->getValue($this->ip);
	}

	/**
	 * @param string $visitor REMOTE_ADDR for the child
	 * @return string[] the child's output lines
	 */
	private function bootAs($visitor)
	{
		putenv('REMOTE_ADDR='.$visitor);
		list($output) = $this->runInBootedCli('echo "REACHED";');
		putenv('REMOTE_ADDR');

		return $output;
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

		$method = new ReflectionMethod($this->ip, 'getCurrentIP');

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

		$result = $this->ip->add_ban(2, self::REASON, '123.123.123.123');
		$this::assertTrue($result);
	}

	/**
	 * A visitor inside a whitelisted range is let through however many bans
	 * cover the same address, and the whitelist row is the one remembered, so
	 * the log names it. The directory handed to the constructor is used as
	 * given: realpath() strips the trailing slash every file name is built on.
	 */
	public function testWhitelistedVisitorIsLetThroughAndRemembered()
	{
		$dir = $this->haveTable(array(
			array(self::TEST_NET, 1, eIPHandler::BAN_TYPE_WHITELIST, 0),
			array(self::VISITOR, 2, eIPHandler::BAN_TYPE_MANUAL, 0),
		));
		$_SERVER['REMOTE_ADDR'] = self::VISITOR;

		$this->ip->__construct($dir);

		self::assertSame(self::VISITOR, $this->ip->getIP(true));
		self::assertSame(self::TEST_NET, $this->handlerProperty('matchAddress'), 'the whitelist row has to be the match');
		self::assertFalse($this->handlerProperty('clearBan'));
		self::assertSame(rtrim(realpath($dir), '/').'/', $this->ip->getConfigDir());
	}

	/**
	 * An address no range covers is neither banned nor remembered.
	 */
	public function testUnlistedVisitorIsLetThrough()
	{
		$dir = $this->haveTable(array(array(self::TEST_NET, 1, eIPHandler::BAN_TYPE_MANUAL, 0)));
		$_SERVER['REMOTE_ADDR'] = self::STRANGER;

		$this->ip->__construct($dir);

		self::assertSame('', $this->handlerProperty('matchAddress'));
		self::assertFalse($this->handlerProperty('clearBan'));
	}

	/**
	 * An expired ban does not stop the visitor, but its row is remembered so
	 * ban() can delete it once the database is open, by id rather than by an
	 * address no wildcard row equals (#6205). A whitelist row over the same
	 * address is still the match.
	 */
	public function testExpiredBanIsRememberedForClearing()
	{
		$dir = $this->haveTable(array(array(self::VISITOR, 5, eIPHandler::BAN_TYPE_FLOOD, time() - 5)));
		$_SERVER['REMOTE_ADDR'] = self::VISITOR;

		$this->ip->__construct($dir);

		$expired = $this->handlerProperty('clearBan');
		self::assertSame(5, $expired['id'], 'the expired row is remembered by id');
		self::assertSame(self::VISITOR, $expired['ip']);
		self::assertSame('', $this->handlerProperty('matchAddress'));

		$dir = $this->haveTable(array(
			array('10.77.66.*', 6, eIPHandler::BAN_TYPE_FLOOD, time() - 5),
			array('10.77.0.0/16', 7, eIPHandler::BAN_TYPE_WHITELIST, 0),
		));
		$_SERVER['REMOTE_ADDR'] = '10.77.66.65';
		$this->ip = $this->make('eIPHandler');

		$this->ip->__construct($dir);

		$expired = $this->handlerProperty('clearBan');
		self::assertSame(6, $expired['id']);
		self::assertSame('10.77.0.0/16', $this->handlerProperty('matchAddress'));
	}

	/**
	 * A file that is not a range table, such as the prefix-token file older
	 * versions wrote under a similar name, leaves the visitor unbanned rather
	 * than stopping every request, and the site regenerates on the next
	 * database-backed check.
	 */
	public function testAForeignTableIsAnEmptyOne()
	{
		$dir = sys_get_temp_dir().'/e107_iph_'.uniqid('', true).'/';
		mkdir($dir, 0777, true);
		$this->scratchDirs[] = $dir;
		file_put_contents($dir.eIPHandler::BAN_FILE_RANGES_NAME.eIPHandler::BAN_FILE_EXTENSION, "<?php\n; die();\n0000:0000:0000:0000:0000:ffff:c633:644d -1 0\n");
		$_SERVER['REMOTE_ADDR'] = self::VISITOR;

		$this->ip->__construct($dir);

		self::assertSame('', $this->handlerProperty('matchAddress'));
		self::assertFalse($this->handlerProperty('rangeSetLoaded'), 'ban() has to know there is nothing loaded');
	}

	/**
	 * A ban row stored in dotted form bans the address it names. The old
	 * prefix scan compared it against the encoded visitor address and
	 * matched nobody, while the banlist screen listed it as live (#6245).
	 */
	public function testDottedIpv4BanRowBansTheAddress()
	{
		$this->haveRow(self::VISITOR, eIPHandler::BAN_TYPE_MANUAL);
		$this->ip->regenerateFiles();

		self::assertNotContains('REACHED', $this->bootAs(self::VISITOR), 'the dotted row has to ban the address it names');
		self::assertContains('REACHED', $this->bootAs(self::NEIGHBOUR), 'and nobody else');
	}

	/**
	 * A visit from inside an expired wildcard ban deletes that row. ban()
	 * used to delete by an address rebuilt from the truncated token, which
	 * no wildcard row equals, so the row stayed and cost one dead DELETE per
	 * request (#6205).
	 */
	public function testExpiredWildcardBanIsDeletedByBan()
	{
		$id = $this->haveRow('10.77.66.*', eIPHandler::BAN_TYPE_FLOOD, time() - 10);
		$this->ip->regenerateFiles();

		self::assertContains('REACHED', $this->bootAs('10.77.66.65'), 'an expired ban stops nobody');
		self::assertSame(0, e107::getDb()->createQueryBuilder()->from('banlist')->where('banlist_id', $id)->count(),
			'the expired row has to be gone after the visit');
	}

	/**
	 * The whitelist exists so the site's own people cannot be locked out by
	 * an automatic ban. add_ban() only ever looked for a whitelist row
	 * equal to the address, so a whitelisted range protected nobody in it.
	 * The addresses are passed encoded, as every caller in core passes them.
	 */
	public function testAddBanRefusesAnAddressInsideAWhitelistedRange()
	{
		$this->haveRow(self::TEST_NET, eIPHandler::BAN_TYPE_WHITELIST);
		$this->ip->regenerateFiles();
		$_SERVER['REMOTE_ADDR'] = self::STRANGER;
		$this->ip->__construct();
		$inside = $this->ip->ipEncode(self::VISITOR);
		$outside = $this->ip->ipEncode(self::OUTSIDER);

		self::assertFalse($this->ip->add_ban(2, self::REASON, $inside), 'an address inside a whitelisted range cannot be banned');
		self::assertSame(0, e107::getDb()->createQueryBuilder()->from('banlist')->where('banlist_ip', $inside)->count());
		self::assertTrue($this->ip->add_ban(2, self::REASON, $outside), 'an address outside it can');
		self::assertSame(1, e107::getDb()->createQueryBuilder()->from('banlist')->where('banlist_ip', $outside)->count());
	}

	/**
	 * A ban list holding only host patterns is still a ban list. ban() skips
	 * the reverse-DNS check when it believes the list is empty, and the old
	 * file counted only address rows, so a host-only list was never checked.
	 * The table here holds one pattern and no address, which the old file
	 * could not express at all.
	 */
	public function testHostnameOnlyBanlistStillRunsTheDomainCheck()
	{
		$asked = array();
		$this->ip = $this->make('eIPHandler', array(
			'get_host_name' => function($address) use (&$asked)
			{
				$asked[] = $address;

				return 'host.example.test';
			},
		));
		$dir = $this->haveTable(array(), 1);
		e107::getConfig()->set('enable_rdns', 1);
		$_SERVER['REMOTE_ADDR'] = self::STRANGER;
		$this->ip->__construct($dir);

		$this->ip->ban();

		self::assertSame(array($this->ip->getIP()), $asked, 'the reverse-DNS check has to run for a host-only list');
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
	 * @group runs-in-separate-process
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
	 * @group runs-in-separate-process
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

		// Cleanup
		e107::getDb()->delete('banlist');
		$this->ip->regenerateFiles();
	}

	/**
	 * @group runs-in-separate-process
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
	 * @group runs-in-separate-process
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @return void
	 */
	public function testCheckBanCacheExpiration()
	{
		e107::getSession('eIPHandler')->clearData();
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
			'timestamp' => time() - 370 // must be more than 360 @see checkban() line $cached['timestamp'] <= 360
		]);

		$this->ip->add_ban(1,"nothing", 'cameron@mydomain.co.uk');

		// Test: new ban after expiration
		$result = $this->ip->checkBan($query, true, true);
		$this::assertFalse($result);

		// Cleanup
		e107::getDb()->delete('banlist', "`banlist_ip`='cameron@mydomain.co.uk'");
	}
}

