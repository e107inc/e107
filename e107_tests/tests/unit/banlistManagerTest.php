<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

use e107\Ip\Address;
use e107\Ip\RangeFile;
use e107\Ip\RangeLookup;

/**
 * The two halves of banlistManager that act on stored rows: banRetriggerAction(),
 * the cron half of ban retriggering, and writeBanListFiles(), which turns the
 * table into the range table eIPHandler searches for every visitor.
 */
class banlistManagerTest extends \Test\Unit
{
	/** @var banlistManager */
	private $mgr;

	/** @var string */
	private $retriggerFile = '';

	/** @var array banlist_id values written by this test */
	private $insertedIds = array();

	/** @var mixed the ban_durations pref as found, restored in _after() */
	private $savedDurations = null;

	/** @var mixed the ban_retrigger pref as found, restored in _after() */
	private $savedRetrigger = null;

	const HOURS = 6;
	const BAN_TYPE = -2;
	const IP_TRIGGERED = '10.66.66.11';
	const IP_UNTOUCHED = '10.66.66.22';
	const IP_WILDCARD = '10.77.66.*';
	const IP_IN_WILDCARD = '10.77.66.65';
	const IP_WILDCARD_WHITELISTED = '10.77.99.*';
	const IP_IN_WHITELISTED_WILDCARD = '10.77.99.65';
	const IP_WILDCARD_LEGACY = '10.77.55.*';
	const WHITELIST_TYPE = 100;
	const LEGACY_TYPE = 0;
	const USER_TYPE_AS_STORED_BY_USERS_PHP = 6;

	protected function _before()
	{
		require_once(e_HANDLER.'iphandler_class.php');

		$this->mgr = new banlistManager();

		$this->retriggerFile = e107::getIPHandler()->getConfigDir().eIPHandler::BAN_FILE_RETRIGGER_NAME.eIPHandler::BAN_FILE_EXTENSION;

		$this->savedDurations = e107::getConfig()->get('ban_durations');
		$this->savedRetrigger = e107::getConfig()->get('ban_retrigger');
	}

	protected function _after()
	{
		if($this->retriggerFile !== '' && file_exists($this->retriggerFile))
		{
			unlink($this->retriggerFile);
		}

		if(!empty($this->insertedIds))
		{
			e107::getDb()->delete('banlist', '`banlist_id` IN ('.implode(',', $this->insertedIds).')');
			$this->insertedIds = array();

			// The ban files are generated from the table, and the run under test
			// regenerated them with these rows in. Put them back as they were.
			$this->mgr->writeBanListFiles('ip,htaccess,csv');
		}

		putenv('REMOTE_ADDR');
		$config = e107::getConfig();
		$config->set('ban_durations', $this->savedDurations);
		$config->set('ban_retrigger', $this->savedRetrigger);
		$config->save(false, true, false);
	}

	/**
	 * @param string $ip
	 * @param int $expires
	 * @param int $type
	 * @return int banlist_id
	 */
	private function haveBan($ip, $expires, $type = self::BAN_TYPE)
	{
		$id = e107::getDb()->insert('banlist', array(
			'banlist_id'         => 0,
			'banlist_ip'         => $ip,
			'banlist_bantype'    => $type,
			'banlist_datestamp'  => time() - 3600,
			'banlist_banexpires' => $expires,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'e107help retrigger probe',
			'banlist_notes'      => '',
		));

		self::assertNotEmpty($id, 'could not write the banlist row this test needs');
		$this->insertedIds[] = (int) $id;

		return (int) $id;
	}

	/**
	 * @param int $id
	 * @return int banlist_banexpires
	 */
	private function expiryOf($id)
	{
		return (int) e107::getDb()->retrieve('banlist', 'banlist_banexpires', '`banlist_id` = '.(int) $id);
	}

	/**
	 * The compiled range table as the last writeBanListFiles('ip') left it.
	 *
	 * @return RangeFile
	 */
	private function compiledTable()
	{
		$file = e107::getIPHandler()->getConfigDir().eIPHandler::BAN_FILE_RANGES_NAME.eIPHandler::BAN_FILE_EXTENSION;
		self::assertFileExists($file, 'writeBanListFiles() wrote no range table');

		$set = RangeFile::open($file);
		self::assertNotNull($set, 'the range table has to be one this version reads');

		return $set;
	}

	/**
	 * @param RangeLookup $set
	 * @param string $address
	 * @return array banlist_id => enforced type, for the rows covering the address, in lookup order
	 */
	private function rowsCovering(RangeLookup $set, $address)
	{
		$segment = $set->find(Address::toHex($address));
		$rows = array();

		if($segment < 0)
		{
			return $rows;
		}

		foreach($set->hits($segment) as $index)
		{
			$entry = $set->entry($index);
			$rows[$entry['id']] = $entry['type'];
		}

		return $rows;
	}

	/**
	 * The lines of a generated text file, without the PHP guard.
	 *
	 * @param string $name one of the eIPHandler::BAN_FILE_* names
	 * @return string[]
	 */
	private function generatedLines($name)
	{
		$file = e107::getIPHandler()->getConfigDir().$name.eIPHandler::BAN_FILE_EXTENSION;
		self::assertFileExists($file, 'writeBanListFiles() wrote no '.$name.' file');

		$lines = file($file, FILE_IGNORE_NEW_LINES);
		self::assertSame('<?php', $lines[0], 'the file has to stay inert when served');
		self::assertSame('; die();', $lines[1]);

		return array_slice($lines, 2);
	}


	/**
	 * Queue one row for retriggering, in the format eIPHandler writes and
	 * splitLogEntry() reads: timestamp, banlist_id, negative reason code, notes.
	 *
	 * @param int $id
	 * @return void
	 */
	private function haveRetriggerEntry($id)
	{
		file_put_contents($this->retriggerFile, time().' '.$id.' '.self::BAN_TYPE." Retrigger: ".self::IP_TRIGGERED."\n");
	}

	/**
	 * The ban type is what ban_durations is keyed on, everywhere else in this
	 * handler. Reading the duration under a column that does not exist leaves
	 * nothing to add, so the address that came back while banned has its ban
	 * left exactly where it was.
	 */
	public function testRetriggerPushesTheBanOutByItsConfiguredDuration()
	{
		e107::getConfig()->set('ban_retrigger', 1);
		e107::getConfig()->set('ban_durations', array(self::BAN_TYPE => self::HOURS));

		$expiresSoon = time() + 60;
		$id = $this->haveBan(self::IP_TRIGGERED, $expiresSoon);
		$this->haveRetriggerEntry($id);

		$before = time();
		$count = $this->mgr->banRetriggerAction();
		$after = time();

		self::assertSame(1, $count, 'one address was queued, so one should have been actioned');

		$expiry = $this->expiryOf($id);
		self::assertGreaterThanOrEqual($before + (self::HOURS * 3600), $expiry,
			'the ban has to run for its full configured duration from now');
		self::assertLessThanOrEqual($after + (self::HOURS * 3600), $expiry);
	}

	/**
	 * And it has to reach that one ban only. The statement carries no WHERE,
	 * so every row in the table takes the value meant for the address that
	 * came back, which on a site with more than one ban is the whole table
	 * expiring, or not expiring, together.
	 */
	public function testRetriggerLeavesEveryOtherBanAlone()
	{
		e107::getConfig()->set('ban_retrigger', 1);
		e107::getConfig()->set('ban_durations', array(self::BAN_TYPE => self::HOURS));

		$untouchedExpiry = time() + 999999;
		$triggered = $this->haveBan(self::IP_TRIGGERED, time() + 60);
		$other = $this->haveBan(self::IP_UNTOUCHED, $untouchedExpiry);
		$this->haveRetriggerEntry($triggered);

		$this->mgr->banRetriggerAction();

		self::assertSame($untouchedExpiry, $this->expiryOf($other),
			'a ban nobody retriggered must keep its own expiry');
	}

	/**
	 * A visit from inside a wildcard ban has to reach the row that banned it.
	 * banAction() queues what it matched for retriggering and
	 * banRetriggerAction() looks it up; when the queued value was the
	 * truncated prefix the old file held, no row ever equalled it and the ban
	 * never extended (#6205). The queue now carries the row id, which a stored
	 * range with spaces in it could never have been looked up by.
	 */
	public function testAVisitFromInsideAWildcardBanRetriggersThatRow()
	{
		$config = e107::getConfig();
		$config->set('ban_retrigger', 1);
		$config->set('ban_durations', array(self::BAN_TYPE => self::HOURS));
		$config->save(false, true, false);

		$expiresSoon = time() + 60;
		$id = $this->haveBan(self::IP_WILDCARD, $expiresSoon);
		$this->mgr->writeBanListFiles('ip');
		$this->mgr->writeBanMessageFile();

		putenv('REMOTE_ADDR='.self::IP_IN_WILDCARD);
		list($output) = $this->runInBootedCli('echo "REACHED";');
		putenv('REMOTE_ADDR');

		self::assertNotContains('REACHED', $output, 'a visitor inside the banned range has to be stopped');
		self::assertFileExists($this->retriggerFile, 'the banned visit has to be queued for retriggering');
		self::assertStringContainsString(' '.$id.' ', file_get_contents($this->retriggerFile),
			'the queued value has to be the row id, which is what banRetriggerAction() looks up');

		$before = time();
		self::assertSame(1, $this->mgr->banRetriggerAction(), 'the queued visit has to reach one row');
		self::assertGreaterThanOrEqual($before + (self::HOURS * 3600), $this->expiryOf($id),
			'the wildcard ban has to run for its full duration from the visit');
	}

	/**
	 * Every form a row has ever been stored in has to be enforced as the
	 * range it names: dotted and encoded singles, the x-wildcard encoding,
	 * dotted wildcards, CIDR and hyphenated ranges. A 0.7-era row with type
	 * 0 is enforced as an unknown ban, and the positive 6 users.php stores
	 * is enforced as a user ban rather than read as an allow entry. Patterns
	 * that are not addresses are counted, so a list holding only those still
	 * makes ban() run the database checks, and junk is neither.
	 */
	public function testCompiledTableCoversEveryStoredForm()
	{
		$rows = array(
			'10.66.66.33'                                 => array(self::BAN_TYPE, '10.66.66.33'),
			'0000:0000:0000:0000:0000:ffff:0a42:4222'     => array(self::BAN_TYPE, '10.66.66.34'),
			'0000:0000:0000:0000:0000:ffff:0a4d:42xx'     => array(self::BAN_TYPE, self::IP_IN_WILDCARD),
			self::IP_WILDCARD_LEGACY                      => array(self::LEGACY_TYPE, '10.77.55.9'),
			'10.77.70.0/24'                               => array(self::BAN_TYPE, '10.77.70.200'),
			'10.77.71.5-10.77.71.9'                       => array(self::BAN_TYPE, '10.77.71.7'),
			'10.77.56.1'                                  => array(self::USER_TYPE_AS_STORED_BY_USERS_PHP, '10.77.56.1'),
			'2001:db8:6245::/48'                          => array(self::BAN_TYPE, '2001:db8:6245:1::1'),
			self::IP_WILDCARD_WHITELISTED                 => array(self::WHITELIST_TYPE, self::IP_IN_WHITELISTED_WILDCARD),
		);
		$ids = array();

		foreach($rows as $stored => $spec)
		{
			$ids[$stored] = $this->haveBan($stored, 0, $spec[0]);
		}

		$this->haveBan(self::IP_IN_WHITELISTED_WILDCARD, 0);
		$hostRow = $this->haveBan('*.example.invalid', 0);
		$emailRow = $this->haveBan('*@example.invalid', 0);
		$junkRow = $this->haveBan('bad.cc', 0);

		$this->mgr->writeBanListFiles('ip');
		$set = $this->compiledTable();

		$expectedType = array(
			self::LEGACY_TYPE                          => eIPHandler::BAN_TYPE_UNKNOWN,
			self::USER_TYPE_AS_STORED_BY_USERS_PHP     => eIPHandler::BAN_TYPE_USER,
		);

		foreach($rows as $stored => $spec)
		{
			$covering = $this->rowsCovering($set, $spec[1]);
			self::assertArrayHasKey($ids[$stored], $covering, $stored.' has to cover '.$spec[1]);
			$type = isset($expectedType[$spec[0]]) ? $expectedType[$spec[0]] : $spec[0];
			self::assertSame($type, $covering[$ids[$stored]], $stored.' is enforced as the wrong type');
		}

		$inWhitelist = $this->rowsCovering($set, self::IP_IN_WHITELISTED_WILDCARD);
		self::assertSame($ids[self::IP_WILDCARD_WHITELISTED], key($inWhitelist),
			'the whitelist row has to come before the ban on the same address');

		self::assertSame(array(), $this->rowsCovering($set, '10.77.71.10'), 'a hyphenated range ends where it says');
		self::assertSame(array(), $this->rowsCovering($set, '10.66.66.35'), 'a single address covers only itself');
		self::assertGreaterThanOrEqual(2, $set->others(), 'the host and email patterns have to be counted');

		for($i = 0; $i < $set->entryCount(); $i++)
		{
			$entry = $set->entry($i);
			self::assertNotContains($entry['id'], array($hostRow, $emailRow, $junkRow), 'a pattern is not a range');
		}
	}

	/**
	 * Apache reads dotted addresses and CIDR blocks; the encoded colon form
	 * the file used to carry matches nothing there (#6199). A range that is
	 * not a block is written as the blocks that make it up, and a whitelist
	 * row is an allow line.
	 */
	public function testHtaccessFileIsDottedAndInCidrBlocks()
	{
		$this->haveBan('0000:0000:0000:0000:0000:ffff:0a42:4222', 0);
		$this->haveBan('10.77.70.0/24', 0);
		$this->haveBan('10.77.71.5-10.77.71.9', 0);
		$this->haveBan('2001:db8:6245::/48', 0);
		$this->haveBan(self::IP_WILDCARD_WHITELISTED, 0, self::WHITELIST_TYPE);
		$this->haveBan('*.example.invalid', 0);

		$this->mgr->writeBanListFiles('htaccess');
		$lines = $this->generatedLines(eIPHandler::BAN_FILE_HTACCESS);

		foreach(array('deny from 10.66.66.34', 'deny from 10.77.70.0/24', 'deny from 10.77.71.5/32', 'deny from 10.77.71.6/31', 'deny from 10.77.71.8/31', 'deny from 2001:db8:6245::/48', 'allow from 10.77.99.0/24') as $line)
		{
			self::assertContains($line, $lines, $line.' has to be in the .htaccess file');
		}

		foreach($lines as $line)
		{
			self::assertMatchesRegularExpression('#^(allow|deny) from [0-9a-f.:]+(/[0-9]+)?$#', $line, 'not a directive Apache reads: '.$line);
			self::assertStringNotContainsString('0000:0000:0000', $line, 'the encoded form matches nothing in Apache');
			self::assertStringNotContainsString('example.invalid', $line, 'a host pattern is not an address');
		}
	}

	/**
	 * The csv option read a column the table does not have, so every export
	 * said a ban never expired.
	 */
	public function testCsvOptionWritesTheExpiry()
	{
		$expires = time() + 3600;
		$this->haveBan('10.77.72.1', $expires);

		$this->mgr->writeBanListFiles('csv');

		$row = null;
		foreach($this->generatedLines(eIPHandler::BAN_FILE_CSV_NAME) as $line)
		{
			if(strpos($line, '10.77.72.1,') === 0)
			{
				$row = str_getcsv($line);
			}
		}

		self::assertNotNull($row, 'the row has to be exported');
		self::assertSame(eShims::strftime('%Y%m%d_%H%M%S', $expires), $row[2], 'the third field is the expiry');
	}
}
