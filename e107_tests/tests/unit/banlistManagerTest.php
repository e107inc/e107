<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * banRetriggerAction() is the cron half of ban retriggering: cron_class calls
 * it from procBanRetrigger(), it reads the action file eIPHandler appends to
 * when a banned address comes back, and it pushes those bans out again.
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
			$this->mgr->writeBanListFiles('ip');
		}

		e107::getConfig()->set('ban_durations', $this->savedDurations);
		e107::getConfig()->set('ban_retrigger', $this->savedRetrigger);
	}

	/**
	 * @param string $ip
	 * @param int $expires
	 * @return int banlist_id
	 */
	private function haveBan($ip, $expires)
	{
		$id = e107::getDb()->insert('banlist', array(
			'banlist_id'         => 0,
			'banlist_ip'         => $ip,
			'banlist_bantype'    => self::BAN_TYPE,
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
	 * Queue one address for retriggering, in the format eIPHandler writes and
	 * splitLogEntry() reads: timestamp, address, negative reason code, notes.
	 *
	 * @param string $ip
	 * @return void
	 */
	private function haveRetriggerEntry($ip)
	{
		file_put_contents($this->retriggerFile, time().' '.$ip.' '.self::BAN_TYPE." Retrigger: ".$ip."\n");
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
		$this->haveBan(self::IP_TRIGGERED, time() + 60);
		$other = $this->haveBan(self::IP_UNTOUCHED, $untouchedExpiry);
		$this->haveRetriggerEntry(self::IP_TRIGGERED);

		$this->mgr->banRetriggerAction();

		self::assertSame($untouchedExpiry, $this->expiryOf($other),
			'a ban nobody retriggered must keep its own expiry');
	}
}
