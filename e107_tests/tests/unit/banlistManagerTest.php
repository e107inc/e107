<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * writeBanListFiles() turns the banlist table into the files eIPHandler
 * prefix-matches every visitor against, and banRetriggerAction() is the cron
 * half of ban retriggering.
 */
class banlistManagerTest extends \Codeception\Test\Unit
{
	/** @var banlistManager */
	private $mgr;

	/** @var array banlist_id values written by this test */
	private $insertedIds = array();

	/** @var string */
	private $retriggerFile = '';

	/** @var mixed the ban_durations pref as found, restored in _after() */
	private $savedDurations = null;

	const HOURS = 6;
	const IP_TRIGGERED = '10.66.66.11';
	const IP_UNTOUCHED = '10.66.66.22';
	const BAN_TYPE = -2;
	const IP_WILDCARD = '10.77.66.*';
	const IP_IN_WILDCARD = '10.77.66.65';
	const IP_WILDCARD_TOKEN = '0000:0000:0000:0000:0000:ffff:0a4d:42';
	const IP_WILDCARD_WHITELISTED = '10.77.99.*';
	const IP_WILDCARD_WHITELISTED_TOKEN = '0000:0000:0000:0000:0000:ffff:0a4d:63';
	const IP_WILDCARD_WHITELISTED_STORED = '10.77.99.';
	const IP_IN_WHITELISTED_WILDCARD = '10.77.99.65';
	const IP_WILDCARD_LEGACY = '10.77.55.*';
	const IP_WILDCARD_LEGACY_TOKEN = '0000:0000:0000:0000:0000:ffff:0a4d:37';
	const WHITELIST_TYPE = 100;
	const LEGACY_TYPE = 0;
	const IP_NOBODY_BANNED = '203.0.113.9';

	protected function _before()
	{
		require_once(e_HANDLER.'iphandler_class.php');

		$this->mgr = new banlistManager();

		$this->retriggerFile = e107::getIPHandler()->getConfigDir().eIPHandler::BAN_FILE_RETRIGGER_NAME.eIPHandler::BAN_FILE_EXTENSION;

		$this->savedDurations = e107::getConfig()->get('ban_durations');
	}

	protected function _after()
	{
		if($this->retriggerFile !== '' && file_exists($this->retriggerFile))
		{
			unlink($this->retriggerFile);
		}

		e107::getConfig()->set('ban_durations', $this->savedDurations);

		if(!empty($this->insertedIds))
		{
			e107::getDb()->delete('banlist', '`banlist_id` IN ('.implode(',', $this->insertedIds).')');
			$this->insertedIds = array();

			// The ban files are generated from the table, and the run under test
			// regenerated them with these rows in. Put them back as they were.
			$this->mgr->writeBanListFiles('ip');
		}
	}

	/**
	 * @param string $ip
	 * @param int $type
	 * @param int $expires
	 * @return int banlist_id
	 */
	private function haveBan($ip, $type = self::BAN_TYPE, $expires = 0)
	{
		$id = e107::getDb()->insert('banlist', array(
			'banlist_id'         => 0,
			'banlist_ip'         => $ip,
			'banlist_bantype'    => $type,
			'banlist_datestamp'  => time() - 3600,
			'banlist_banexpires' => $expires,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'e107help banlist file probe',
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
	 * Queue one ban for retriggering, in the format eIPHandler writes and
	 * splitLogEntry() reads: timestamp, the matched address, negative reason
	 * code, notes.
	 *
	 * @param string $address
	 * @return void
	 */
	private function haveRetriggerEntry($address)
	{
		file_put_contents($this->retriggerFile, time().' '.$address.' '.self::BAN_TYPE." Retrigger: ".self::IP_TRIGGERED."\n");
	}

	/**
	 * The update carries no WHERE, so every row in the table takes the value
	 * computed for the one address that came back: a permanent ban makes the
	 * whole ban list permanent, and a timed one hands lapsed rows a future
	 * expiry, which puts them back into force.
	 */
	public function testRetriggerLeavesEveryOtherBanAlone()
	{
		e107::getConfig()->set('ban_durations', array(self::BAN_TYPE => self::HOURS));

		$untouchedExpiry = time() + 999999;
		$this->haveBan(self::IP_TRIGGERED, self::BAN_TYPE, time() + 60);
		$other = $this->haveBan(self::IP_UNTOUCHED, self::BAN_TYPE, $untouchedExpiry);
		$this->haveRetriggerEntry(self::IP_TRIGGERED);

		self::assertSame(1, $this->mgr->banRetriggerAction(), 'one address was queued, so one should have been actioned');

		self::assertSame($untouchedExpiry, $this->expiryOf($other),
			'a ban nobody retriggered must keep its own expiry');
	}

	/**
	 * An address can carry more than one ban: banlist_ip has an ordinary index
	 * rather than a unique one, and the admin screen writes what it is given.
	 * The queue names the address, so the ban in force is the one to retrigger,
	 * and demanding a single row meant a duplicated address retriggered nothing
	 * at all and lapsed on schedule while the visitor was still knocking.
	 */
	public function testRetriggerReachesTheEnforcedBanWhenAnAddressHasMoreThanOneRow()
	{
		e107::getConfig()->set('ban_durations', array(eIPHandler::BAN_TYPE_MANUAL => self::HOURS));

		$untouchedExpiry = time() + 999999;
		$enforced = $this->haveBan(self::IP_TRIGGERED, eIPHandler::BAN_TYPE_MANUAL, time() + 60);
		$other = $this->haveBan(self::IP_TRIGGERED, self::BAN_TYPE, $untouchedExpiry);
		$this->haveRetriggerEntry(self::IP_TRIGGERED);

		$before = time();

		self::assertSame(1, $this->mgr->banRetriggerAction(),
			'a second row on the address must not stop the ban in force being retriggered');

		self::assertGreaterThanOrEqual($before + (self::HOURS * 3600), $this->expiryOf($enforced),
			'the ban the site enforces for that address has to run for its full duration again');
		self::assertSame($untouchedExpiry, $this->expiryOf($other),
			'the other row is a ban of its own, under a type with no duration configured');
	}

	/**
	 * The ban that stopped the visitor was in force when it stopped them: the
	 * ban file's own check passes over an entry whose time limit has run out.
	 * So where an address carries a lapsed ban and a live one, the live one is
	 * the ban to push out, whatever the two say about precedence, and the
	 * lapsed one is not put back into force on its way past.
	 */
	public function testRetriggerPassesOverALapsedBanForTheLiveOneOnTheSameAddress()
	{
		e107::getConfig()->set('ban_durations', array(
			eIPHandler::BAN_TYPE_MANUAL => self::HOURS,
			self::BAN_TYPE              => self::HOURS,
		));

		$ranOutAnHourAgo = time() - 3600;
		$lapsed = $this->haveBan(self::IP_TRIGGERED, eIPHandler::BAN_TYPE_MANUAL, $ranOutAnHourAgo);
		$live = $this->haveBan(self::IP_TRIGGERED, self::BAN_TYPE, time() + 60);
		$this->haveRetriggerEntry(self::IP_TRIGGERED);

		$before = time();

		self::assertSame(1, $this->mgr->banRetriggerAction(), 'the live ban on that address has to be retriggered');

		self::assertSame($ranOutAnHourAgo, $this->expiryOf($lapsed),
			'a ban that has already run out must not be put back into force');
		self::assertGreaterThanOrEqual($before + (self::HOURS * 3600), $this->expiryOf($live),
			'the ban still in force is the one the visitor was stopped by');
	}

	/**
	 * The ban type is what ban_durations is keyed on, in hours, everywhere else
	 * in this handler. Read under a column that does not exist it leaves nothing
	 * to add, so the address that came back while banned had its ban left
	 * exactly where it was.
	 */
	public function testRetriggerPushesTheBanOutByItsConfiguredDuration()
	{
		e107::getConfig()->set('ban_durations', array(self::BAN_TYPE => self::HOURS));

		$id = $this->haveBan(self::IP_TRIGGERED, self::BAN_TYPE, time() + 60);
		$this->haveRetriggerEntry(self::IP_TRIGGERED);

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
	 * The match tokens of the generated IP ban file, in the order written.
	 *
	 * @return array
	 */
	private function banFileTokens()
	{
		$file = e107::getIPHandler()->getConfigDir().eIPHandler::BAN_FILE_IP_NAME.eIPHandler::BAN_FILE_EXTENSION;
		self::assertFileExists($file, 'writeBanListFiles() wrote no IP ban file');

		$tokens = array();
		foreach(file($file) as $line)
		{
			$parts = explode(' ', trim($line));
			if(count($parts) === 3) $tokens[] = $parts[0];
		}

		return $tokens;
	}

	/**
	 * eIPHandler compares the visitor's encoded address against each entry of
	 * this file, so a wildcard row has to reach it encoded. Written in the
	 * dotted form it was typed in, it prefixes nothing and bans nobody, while
	 * the admin screen goes on showing it as a live ban.
	 *
	 * A legacy row is a ban too. Its stored type is 0 rather than negative, and
	 * it is normalised to BAN_TYPE_UNKNOWN before anything is written, so it
	 * reaches the file on the same terms as every other ban.
	 */
	public function testWildcardBanIsWrittenAsAnEncodedPrefix()
	{
		$this->haveBan(self::IP_WILDCARD);
		$this->haveBan(self::IP_WILDCARD_LEGACY, self::LEGACY_TYPE);

		$this->mgr->writeBanListFiles('ip');

		$tokens = $this->banFileTokens();
		self::assertContains(self::IP_WILDCARD_TOKEN, $tokens,
			'a wildcard ban has to reach the file as the encoded prefix of its range');
		self::assertContains(self::IP_WILDCARD_LEGACY_TOKEN, $tokens,
			'and a legacy wildcard ban with it, since the type it is normalised to is a ban');
		self::assertSame(0, strpos(e107::getIPHandler()->ipEncode(self::IP_IN_WILDCARD), self::IP_WILDCARD_TOKEN),
			'and that prefix has to be the start of every encoded address in the range');
	}

	/**
	 * A whitelist entry is not a ban, and the ban type is the whole of what
	 * tells them apart. Whitelist rows are written into this file first and end
	 * the scan on a match, so a wildcard whitelist row that reaches it encoded
	 * stops every ban inside its range firing, with nothing on the banlist
	 * screen saying so. It keeps the stored form it has always had, which
	 * prefixes no encoded address and exempts nobody.
	 */
	public function testWildcardWhitelistRowIsWrittenAsStored()
	{
		$this->haveBan(self::IP_WILDCARD_WHITELISTED, self::WHITELIST_TYPE);

		$this->mgr->writeBanListFiles('ip');

		$tokens = $this->banFileTokens();
		self::assertNotContains(self::IP_WILDCARD_WHITELISTED_TOKEN, $tokens,
			'a wildcard whitelist row must not reach the file in the form the ban check matches');
		self::assertContains(self::IP_WILDCARD_WHITELISTED_STORED, $tokens,
			'it has to reach the file in the form it was stored in');
		self::assertNotSame(0, strpos(e107::getIPHandler()->ipEncode(self::IP_IN_WHITELISTED_WILDCARD), self::IP_WILDCARD_WHITELISTED_STORED),
			'and that form has to stay inert against the addresses in its range');
	}

	/**
	 * The other direction, and the reason the encoder is choosy. whatIsThis()
	 * calls anything built from hex digits, dots and wildcards an address, so
	 * all of these rows reach the file, and each has to arrive exactly as it
	 * does today: a host name and a plain stored address name no range to
	 * encode, an embedded wildcard would widen the ban to a /8, a wildcard
	 * inside an octet would shrink it to the single address 10.77.66.5, and an
	 * octet of two wildcards is not an octet ipEncode() can read.
	 */
	public function testAnAddressPatternThatCannotBeEncodedIsLeftAsStored()
	{
		$expected = array(
			'bad.cc'      => 'bad.cc',
			'10.66.66.33' => '10.66.66.33',
			'10.*.66.5'   => '10.',
			'10.77.66.5*' => '10.77.66.5',
			'10.77.66.**' => '10.77.66.',
		);
		foreach(array_keys($expected) as $stored)
		{
			$this->haveBan($stored);
		}

		$this->mgr->writeBanListFiles('ip');

		$tokens = $this->banFileTokens();
		foreach($expected as $stored => $token)
		{
			self::assertContains($token, $tokens, $stored.' has to reach the ban file exactly as it did before');
		}

		$visitor = e107::getIPHandler()->ipEncode(self::IP_NOBODY_BANNED);
		foreach($tokens as $token)
		{
			self::assertNotSame(0, strpos($visitor, $token),
				'no entry may match an address nobody banned, and this one does: '.$token);
		}
	}
}
