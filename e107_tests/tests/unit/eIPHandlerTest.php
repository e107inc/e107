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
		use \Test\BootedCli;

		const RETRIGGER_HOURS = 6;
		const RETRIGGERED_IP = '203.0.113.77';
		const EXPIRED_IP = '203.0.113.78';
		const LIVE_IP = '203.0.113.79';
		const CONTENDED = '203.0.113.71';
		const REASON = 'eIPHandlerTest';

		/** @var eIPHandler */
		protected $ip;

		/** @var array banlist_id values written by this test */
		private $insertedIds = array();

		/** @var mixed the ban_durations pref as found, restored in _after() */
		private $savedDurations = null;

		/** @var mixed the ban_retrigger pref as found, restored in _after() */
		private $savedRetrigger = null;

		/** @var mixed the enable_rdns_on_ban pref as found, restored in _after() */
		private $savedRdnsOnBan = null;

		protected function _before()
		{
			$this->savedDurations = e107::getConfig()->get('ban_durations');
			$this->savedRetrigger = e107::getConfig()->get('ban_retrigger');
			$this->savedRdnsOnBan = e107::getConfig()->get('enable_rdns_on_ban');

			try
			{
				$this->ip = $this->make('eIPHandler');
			} catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load eIPHandler object");
			}
		}

		protected function _after()
		{
			e107::getConfig()->set('ban_durations', $this->savedDurations);
			e107::getConfig()->set('ban_retrigger', $this->savedRetrigger);
			e107::getConfig()->set('enable_rdns_on_ban', $this->savedRdnsOnBan);

			if(e107::getDb()->delete('banlist', "`banlist_ip` = '".$this->ip->ipEncode(self::CONTENDED)."'"))
			{
				$this->ip->regenerateFiles();
			}

			if(!empty($this->insertedIds))
			{
				e107::getDb()->delete('banlist', '`banlist_id` IN ('.implode(',', $this->insertedIds).')');
				$this->insertedIds = array();

				// The retrigger under test regenerated the ban files with these
				// rows in. Put them back as they were.
				$this->ip->regenerateFiles();
			}
		}

		/**
		 * @param string $ip
		 * @param int $type
		 * @param int $expires
		 * @return int banlist_id
		 */
		private function haveRow($ip, $type, $expires)
		{
			$id = e107::getDb()->insert('banlist', array(
				'banlist_id'         => 0,
				'banlist_ip'         => $ip,
				'banlist_bantype'    => $type,
				'banlist_datestamp'  => time() - 60,
				'banlist_banexpires' => $expires,
				'banlist_admin'      => 0,
				'banlist_reason'     => 'eIPHandlerTest retrigger probe',
				'banlist_notes'      => '',
			));
			$this->assertNotEmpty($id, 'could not write the banlist row this test needs');
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
		 * A banned visitor coming back extends the ban that stopped them.
		 * banlist_ip carries an ordinary index rather than a unique one, so
		 * keying the write on the address reaches every row stored under it,
		 * each one by the hours configured for the enforced row's type rather
		 * than its own.
		 */
		public function testRetriggerLeavesAnotherBanOnTheSameAddressAlone()
		{
			$config = e107::getConfig();
			$config->set('ban_retrigger', 1);
			$config->set('ban_durations', array(eIPHandler::BAN_TYPE_MANUAL => self::RETRIGGER_HOURS));

			$laterExpiry = time() + 999999;
			$enforced = $this->haveRow(self::RETRIGGERED_IP, eIPHandler::BAN_TYPE_MANUAL, time() + 60);
			$other = $this->haveRow(self::RETRIGGERED_IP, eIPHandler::BAN_TYPE_FLOOD, $laterExpiry);

			$before = time();
			$this->assertFalse($this->ip->checkBan("`banlist_ip`='".self::RETRIGGERED_IP."'", false, true),
				'the address is banned, so the check has to report it banned');

			$this->assertGreaterThanOrEqual($before + (self::RETRIGGER_HOURS * 3600), $this->expiryOf($enforced),
				'the ban that stopped the visitor has to run for its full duration again');
			$this->assertSame($laterExpiry, $this->expiryOf($other),
				'the other ban on that address is a ban of its own and nothing retriggered it');
		}

		/**
		 * Clearing a ban that has run out is a write on that row. Keyed on the
		 * caller's whole WHERE clause instead, it took every other ban the
		 * clause matched with it, which for the registration screen's
		 * address-or-domain query means one lapsed address ban deleting the
		 * live domain ban beside it.
		 */
		public function testClearingAnExpiredBanLeavesTheLiveOnesItWasLookedUpWith()
		{
			$laterExpiry = time() + 999999;
			$expired = $this->haveRow(self::EXPIRED_IP, eIPHandler::BAN_TYPE_MANUAL, time() - 60);
			$live = $this->haveRow(self::LIVE_IP, eIPHandler::BAN_TYPE_FLOOD, $laterExpiry);

			$query = "`banlist_ip`='".self::EXPIRED_IP."' OR `banlist_ip`='".self::LIVE_IP."'";
			$this->assertTrue($this->ip->checkBan($query, false, true),
				'the ban that was read had expired, so this visitor is not banned by it');

			$this->assertSame(0, (int) e107::getDb()->count('banlist', '(*)', 'WHERE `banlist_id` = '.$expired),
				'the ban that ran out has to be cleared');
			$this->assertSame($laterExpiry, $this->expiryOf($live),
				'a ban that has not run out has to survive the clearing of one that has');
		}


		public function testAdd_ban()
		{
			// $bantype = 1 for manual, 2 for flooding, 4 for multiple logins

			$banDurations = array(
			'0' => 0,
			'-1' => 0, // manually added ban
			'-2' => 0, // flood
			'-3' => 8, // hits
			'-4' => 10, // multi-login
			'-5' => 0, // imported
			'-6' => 0,  // banned user
			'-8' => 0 // unknown
			);

			//set ban duration pref.
			e107::getConfig()->set('ban_durations',$banDurations)->save(false,true, false);

			$result = $this->ip->add_ban(2,"unit test generated ban", '123.123.123.123', 0);
			$this->assertTrue($result);


		}

		/**
		 * The second request arrives where the first runs its reverse-DNS lookup, between looking the address up and adding it.
		 */
		public function testARequestBanningTheSameAddressAlongsideAddsNoSecondRow()
		{
			$address = $this->ip->ipEncode(self::CONTENDED);
			$alongside = '';
			$banAlongside = function() use ($address, &$alongside)
			{
				$alongside = $this->addBanInAnotherRequest($address);
			};
			$this->ip = $this->make('eIPHandler', array(
				'get_host_name' => function() use ($banAlongside)
				{
					$banAlongside();

					return 'host.example.test';
				},
			));
			$this->ip->__construct();
			e107::getConfig()->set('enable_rdns_on_ban', 1);

			$this->assertTrue($this->ip->add_ban(2, self::REASON, $address), 'the first request adds the ban');
			$this->assertStringContainsString('answer:1', $alongside,
				'the request alongside finds the ban being added and reports the address as listed');
			$this->assertSame(1, (int) e107::getDb()->count('banlist', '(*)', "WHERE `banlist_ip` = '".$address."'"),
				'one ban, however many requests asked for it at once');
		}

		/**
		 * Covers both ways out of add_ban() that hold the lock: adding the ban, and finding it listed.
		 */
		public function testAnotherRequestCanBanTheAddressAfreshOnceEarlierOnesHaveAnswered()
		{
			$address = $this->ip->ipEncode(self::CONTENDED);
			$this->ip->__construct();

			$this->assertTrue($this->ip->add_ban(2, self::REASON, $address));
			$this->assertSame(1, $this->ip->add_ban(2, self::REASON, $address), 'the address is listed now');
			e107::getDb()->delete('banlist', "`banlist_ip` = '".$address."'");

			$this->assertStringContainsString('answer:true', $this->addBanInAnotherRequest($address),
				'with the earlier requests answered, another one adds the ban afresh');
		}

		/**
		 * @param string $address encoded, as core's callers pass it
		 * @return string what add_ban() answered in a request of its own, as 'answer:' and its var_export()
		 */
		private function addBanInAnotherRequest($address)
		{
			list($output) = $this->runInBootedCli("echo 'answer:', var_export(e107::getIPHandler()->add_ban(2, '".self::REASON."', '".$address."', 0), true);");

			return implode("\n", $output);
		}


	}
