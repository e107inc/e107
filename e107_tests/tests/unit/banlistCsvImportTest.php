<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * banlistManager::importBanlistCsv() ingests admin-supplied files into the ban
 * system, so it must round-trip the banlist_export.php format, refuse rows it
 * cannot vouch for, and leave the table untouched when a run cannot finish.
 */
class banlistCsvImportTest extends \Codeception\Test\Unit
{
	/** @var banlistManager */
	private $mgr;

	/** @var array paths of CSV files written by a test */
	private $files = array();

	/** @var array extra banlist_ip values a test wrote, cleaned up in _after() */
	private $cleanupIps = array();

	/** @var mixed the ban_durations pref as found, restored in _after() */
	private $savedDurations = null;

	/** @var e_db|null second connection standing in for a concurrent import, released in _after() */
	private $lockProbe = null;

	/** @var eIPHandler|null the singleton as found, put back in _after() where a test stood in front of it */
	private $savedIpHandler = null;

	/** @var int the session the importer speaks on, read in _before() where no run is part-way through a result set */
	private $importerConnectionId = 0;

	/** @var bool whether the probe took the lock and still owes it back */
	private $probeHoldsLock = false;

	const IP_A = '10.77.66.1';
	const IP_B = '10.77.66.2';
	const IP_C = '10.77.66.3';

	protected function _before()
	{
		require_once(e_HANDLER . 'iphandler_class.php');

		$this->mgr = new banlistManager();
		$this->savedDurations = e107::getConfig()->get('ban_durations');
		$this->importerConnectionId = $this->connectionId(e107::getDb());
	}

	protected function _after()
	{
		try
		{
			$this->restoreTheIpHandler();

			foreach($this->files as $file)
			{
				if(file_exists($file))
				{
					unlink($file);
				}
			}
			$this->files = array();

			$used = array();
			foreach(array(self::IP_A, self::IP_B, self::IP_C) as $ip)
			{
				$used[] = "'" . e107::getIPHandler()->ipEncode($ip) . "'";
				$used[] = "'" . $ip . "'";
			}
			foreach($this->cleanupIps as $ip)
			{
				$used[] = "'" . e107::getDb()->escape($ip) . "'";
			}
			e107::getDb()->delete('banlist', '`banlist_ip` IN (' . implode(',', $used) . ") OR `banlist_ip` LIKE 'imported-test-%'");

			$this->mgr->writeBanListFiles('ip,htaccess');

			e107::getConfig()->set('ban_durations', $this->savedDurations);
		}
		finally
		{
			$this->releaseProbeLock();
		}
	}

	/**
	 * @param string $content
	 * @return string path to a CSV file holding $content
	 */
	private function haveCsv($content)
	{
		$file = tempnam(sys_get_temp_dir(), 'e107ban');
		file_put_contents($file, $content);
		$this->files[] = $file;

		return $file;
	}

	/**
	 * @param string $ip un-encoded IPv4 address
	 * @return array|false the banlist row for $ip
	 */
	private function rowFor($ip)
	{
		return $this->storedRow(e107::getIPHandler()->ipEncode($ip));
	}

	/**
	 * @param string $ip banlist_ip exactly as it is expected to have been stored
	 * @return array|false the banlist row holding $ip verbatim
	 */
	private function storedRow($ip)
	{
		if(!e107::getDb()->select('banlist', '*', "`banlist_ip` = '" . e107::getDb()->escape($ip) . "'"))
		{
			return false;
		}

		return e107::getDb()->fetch();
	}

	/**
	 * @return e_db a connection of its own, standing in for the second admin's request
	 */
	private function probeDb()
	{
		if($this->lockProbe === null)
		{
			$this->lockProbe = e107::getDb('banlistReplaceLockProbe');
			$probeConnectionId = $this->connectionId($this->lockProbe);

			self::assertGreaterThan(0, $this->importerConnectionId,
				'a connection that cannot name its own session answers every question with a zero, and two of those look identical');
			self::assertGreaterThan(0, $probeConnectionId,
				'the same for the probe, which would otherwise prove nothing by matching nothing');
			self::assertNotSame($this->importerConnectionId, $probeConnectionId,
				'a probe sharing the importer connection would re-take the lock rather than block it, and prove nothing');
		}

		return $this->lockProbe;
	}

	/**
	 * @return int 1 where the probe took the lock, 0 where a run already holds it, -1 where the server did not say
	 */
	private function probeAsksForTheLock()
	{
		$row = $this->lockQuery($this->probeDb(), 'SELECT GET_LOCK(:name, 0) AS locked');
		$locked = (is_array($row) && isset($row['locked'])) ? (int) $row['locked'] : -1;

		if($locked === 1)
		{
			$this->probeHoldsLock = true;
		}

		return $locked;
	}

	/**
	 * @param e_db $db connection to ask, which decides whose view of the table the count is
	 * @param string $ip banlist_ip as stored
	 * @return int how many rows hold it
	 */
	private function countRows($db, $ip)
	{
		return (int) $db->select('banlist', 'banlist_id', "`banlist_ip` = '" . $db->escape($ip) . "'");
	}

	/**
	 * Hold the replace-import lock on a connection of its own.
	 */
	private function takeProbeLock()
	{
		self::assertSame(1, $this->probeAsksForTheLock(),
			'the standing run has to hold the lock before the second import is asked for');
	}

	private function releaseProbeLock()
	{
		if(!$this->probeHoldsLock)
		{
			return;
		}

		$this->probeHoldsLock = false;
		$row = $this->lockQuery($this->lockProbe, 'SELECT RELEASE_LOCK(:name) AS released');

		self::assertSame(1, (is_array($row) && isset($row['released'])) ? (int) $row['released'] : -1,
			'the probe has to give the lock back, or a later test in this class fails for a reason that names nothing about itself');
	}

	/**
	 * Put a stand-in at the eIPHandler singleton, so $onRegenerate runs at the point the importer regenerates its files.
	 *
	 * @param callable $onRegenerate
	 */
	private function standInFrontOfTheIpHandler($onRegenerate)
	{
		require_once(__DIR__ . '/fixtures/BanlistImportRegenerateProbeFixture.php');

		$this->savedIpHandler = e107::getIPHandler();
		e107::setRegistry('core/e107/singleton/eIPHandler',
			new BanlistImportRegenerateProbe($this->savedIpHandler, $onRegenerate));
	}

	private function restoreTheIpHandler()
	{
		if($this->savedIpHandler === null)
		{
			return;
		}

		$handler = $this->savedIpHandler;
		$this->savedIpHandler = null;
		e107::setRegistry('core/e107/singleton/eIPHandler', $handler);
	}

	/**
	 * @param e_db $db handle to run it on
	 * @param string $statement SQL taking the lock name as :name
	 * @return array|false the row it answers with
	 */
	private function lockQuery($db, $statement)
	{
		$db->db_Query(array(
			'PREPARE' => $statement,
			'BIND'    => array('name' => array('value' => $this->lockName(), 'type' => PDO::PARAM_STR)),
		), null, 'db_Select');

		return $db->fetch();
	}

	/**
	 * @return string the name the importer derives, restated here so these tests still reach their own assertions against an importer that takes no lock at all
	 */
	private function lockName()
	{
		return 'e107_banlist_replace_import_'
			. md5(e107::getMySQLConfig('defaultdb').'/'.e107::getMySQLConfig('prefix'));
	}

	/**
	 * @param e_db $db
	 * @return int the server-side connection this handle speaks on
	 */
	private function connectionId($db)
	{
		return (int) $db->retrieve('SELECT CONNECTION_ID()');
	}

	/**
	 * @return string path of the IP ban file eIPHandler consults on every page load
	 */
	private function banFile()
	{
		return e107::getIPHandler()->getConfigDir()
			. eIPHandler::BAN_FILE_IP_NAME . eIPHandler::BAN_FILE_EXTENSION;
	}

	public function testImportRoundTripsTheExportFormat()
	{
		$csv = '"' . self::IP_A . '","20240102_030405","20990102_030405","-1","Manual ban","Some notes"' . "\n"
			. '"' . self::IP_B . '","0","0","-2","Flood ban",""' . "\n";

		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv), array(
			'useFileExpiry' => true,
			'adminId'       => 42,
		));

		self::assertSame('', $result['fatal']);
		self::assertSame(array(), $result['errors']);
		self::assertSame(2, $result['imported']);

		$row = $this->rowFor(self::IP_A);
		self::assertNotEmpty($row, 'the first CSV row has to land in the banlist table');
		self::assertEquals(eIPHandler::BAN_TYPE_IMPORTED, $row['banlist_bantype'],
			'imported rows always take BAN_TYPE_IMPORTED, whatever the file claims');
		self::assertEquals(mktime(3, 4, 5, 1, 2, 2024), $row['banlist_datestamp']);
		self::assertEquals(mktime(3, 4, 5, 1, 2, 2099), $row['banlist_banexpires']);
		self::assertEquals(42, $row['banlist_admin']);
		self::assertSame('Manual ban', $row['banlist_reason']);
		self::assertSame('Some notes', $row['banlist_notes']);

		$row = $this->rowFor(self::IP_B);
		self::assertNotEmpty($row);
		self::assertEquals(0, $row['banlist_banexpires'], "an expiry of '0' means the ban never expires");
	}

	public function testDefaultExpiryComesFromTheBanDurationsPref()
	{
		e107::getConfig()->set('ban_durations', array(eIPHandler::BAN_TYPE_IMPORTED => 6));

		$before = time();
		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_A . ",20990102_030405\n"));
		$after = time();

		self::assertSame(1, $result['imported']);

		$row = $this->rowFor(self::IP_A);
		self::assertGreaterThanOrEqual($before + (6 * 3600), $row['banlist_banexpires'],
			'without useFileExpiry, the expiry comes from the ban_durations pref, not the file');
		self::assertLessThanOrEqual($after + (6 * 3600), $row['banlist_banexpires']);
	}

	public function testDuplicateEntriesAreSkipped()
	{
		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => e107::getIPHandler()->ipEncode(self::IP_A),
			'banlist_bantype'    => eIPHandler::BAN_TYPE_MANUAL,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'pre-existing',
			'banlist_notes'      => '',
		));

		$csv = self::IP_A . "\n" . self::IP_B . "\n" . self::IP_B . "\n";
		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv));

		self::assertSame(1, $result['imported']);
		self::assertSame(2, $result['duplicates'],
			'one duplicate against the table, one duplicate within the file');

		$row = $this->rowFor(self::IP_A);
		self::assertSame('pre-existing', $row['banlist_reason'],
			'a duplicate row must not overwrite the ban that is already there');
	}

	public function testReplaceModeSwapsOldImportedRowsForTheFile()
	{
		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => 'imported-test-old',
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'old import',
			'banlist_notes'      => '',
		));

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_B . "\n"),
			array('replaceImported' => true));

		self::assertSame(1, $result['imported']);
		self::assertSame(0, $this->countRows(e107::getDb(), 'imported-test-old'),
			'replaceImported swaps the previous batch of imported bans for the file');
		self::assertNotEmpty($this->rowFor(self::IP_B));
	}

	public function testAReplaceImportIsRefusedWhileAnotherRunIsReplacing()
	{
		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => 'imported-test-old',
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'written by the run that is still going',
			'banlist_notes'      => '',
		));

		$this->takeProbeLock();
		$queriesBefore = e107::getDb()->queryCount();

		try
		{
			$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_B . "\n"),
				array('replaceImported' => true));
			$queriesDuring = e107::getDb()->queryCount() - $queriesBefore;
		}
		finally
		{
			$this->releaseProbeLock();
		}

		self::assertSame(0, $result['imported']);
		self::assertNotEmpty($this->storedRow('imported-test-old'),
			"a refused run deletes none of the running import's rows");
		self::assertEmpty($this->rowFor(self::IP_B),
			'and writes none of its own, so the file can be imported again as it stands');
		self::assertSame(BANLAN_IMPORT_REPLACE_BUSY, $result['fatal'],
			'the second replace-import is refused and told why, rather than snapshotting rows the first run is still writing');
		self::assertSame(1, $queriesDuring,
			'one statement runs in that window and it is the lock: the run asks before it reads, where a run that snapshots first is the run that deletes rows the standing one has just written');
	}

	public function testTheReplaceLockIsStillHeldWhenTheReplacedRowsHaveGone()
	{
		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => 'imported-test-old',
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'the batch the run is replacing',
			'banlist_notes'      => '',
		));

		self::assertSame(1, $this->countRows($this->probeDb(), 'imported-test-old'),
			'the batch this run replaces has to be there to start with, so that a count of none later reads as the delete rather than as a read that answered nothing');
		self::assertSame(1, $this->probeAsksForTheLock(),
			"the name has to be free before this run starts, or the refusal below is a lock somebody else left behind and this test passes on a run that took none");
		$this->releaseProbeLock();

		$written = e107::getIPHandler()->ipEncode(self::IP_B);
		$seen = array();
		$this->standInFrontOfTheIpHandler(function() use (&$seen, $written)
		{
			$seen['lock'] = $this->probeAsksForTheLock();

			if($seen['lock'] === 1)
			{
				$this->releaseProbeLock();
			}

			$seen['written'] = $this->countRows($this->probeDb(), $written);
			$seen['replaced'] = $this->countRows($this->probeDb(), 'imported-test-old');
		});

		try
		{
			$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_B . "\n"),
				array('replaceImported' => true));
		}
		finally
		{
			$this->restoreTheIpHandler();
		}

		self::assertSame(1, $result['imported']);
		self::assertArrayHasKey('lock', $seen,
			'the run has to reach the point where it regenerates the files, or this test looks at nothing');
		self::assertSame(1, $seen['written'],
			"the probe has to be reading the table at that moment, which the run's own new row proves, so that the count below is an absence and not a failed read");
		self::assertSame(0, $seen['replaced'],
			'the replaced batch has to be gone by then, so what follows is a reading taken after the delete');
		self::assertSame(0, $seen['lock'],
			'and the lock has to be refused there, because a run that lets go before its delete leaves the window this lock closes');
	}

	public function testTheReplaceLockIsGivenBackWhetherTheImportFinishedOrAborted()
	{
		$finished = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_B . "\n"), array('replaceImported' => true));

		self::assertSame(1, $finished['imported'],
			'the run that has to give the lock back is a run that took it');

		$this->takeProbeLock();
		$this->releaseProbeLock();

		$aborted = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_C . "\n<junk>!!\n"), array('replaceImported' => true));

		self::assertSame(str_replace('[x]', 1, BANLAN_IMPORT_REPLACE_INCOMPLETE), $aborted['fatal'],
			'and the second run has to have left by the abort path rather than the normal return');

		$this->takeProbeLock();
		$this->releaseProbeLock();
	}

	public function testTheImporterTakesTheLockNameThisInstallDerives()
	{
		$method = new ReflectionMethod('banlistManager', 'replaceLockName');
		$method->setAccessible(true);

		self::assertSame($this->lockName(), $method->invoke($this->mgr),
			'the probe takes the name this test derives, so it has to be the name the importer takes');
		self::assertLessThanOrEqual(64, strlen($this->lockName()),
			'MySQL refuses a lock name over 64 bytes, and every import would then read as a concurrent one');
	}

	public function testAnExpiredRowDoesNotMakeAnImportedAddressADuplicate()
	{
		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => e107::getIPHandler()->ipEncode(self::IP_A),
			'banlist_bantype'    => eIPHandler::BAN_TYPE_MANUAL,
			'banlist_datestamp'  => time() - 7200,
			'banlist_banexpires' => time() - 3600,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'expired an hour ago',
			'banlist_notes'      => '',
		));

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_A . "\n"));

		self::assertSame(0, $result['duplicates'],
			'an expired row bans nobody, so reporting the address as already on the list is a false statement');
		self::assertSame(1, $result['imported'],
			'a refreshed blocklist has to reinstate an address whose old ban has run out');
	}

	public function testReimportingAfterARowLapsesSupersedesItRatherThanStackingASecondCopy()
	{
		$encoded = e107::getIPHandler()->ipEncode(self::IP_A);

		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => $encoded,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time() - 7200,
			'banlist_banexpires' => time() - 3600,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'lapsed an hour ago',
			'banlist_notes'      => '',
		));

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_A . "\n"));

		self::assertSame(1, $result['imported']);
		self::assertSame(array(), $result['warnings']);

		$reasons = array();
		if(e107::getDb()->select('banlist', 'banlist_reason', "`banlist_ip` = '" . e107::getDb()->escape($encoded) . "'"))
		{
			while($row = e107::getDb()->fetch())
			{
				$reasons[] = $row['banlist_reason'];
			}
		}

		self::assertCount(1, $reasons,
			'a blocklist re-imported on a schedule finds its own lapsed rows every run, and leaving them '
			. 'beside the fresh ones grows the table by the size of the list each time');
		self::assertNotSame('lapsed an hour ago', $reasons[0],
			'and the row that survives is the one the file just wrote, not the one it replaced');
	}

	public function testAnUnencodedLegacyRowIsRecognisedAsTheSameAddressTheFileNames()
	{
		$this->cleanupIps[] = self::IP_A;

		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => self::IP_A,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_MANUAL,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'written before addresses were encoded',
			'banlist_notes'      => '',
		));

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_A . "\n"));

		self::assertSame(1, $result['duplicates'],
			'a site upgraded rather than installed holds the plain spelling, and exporting its own list '
			. 'and importing it back must not give every one of those addresses a second row');
		self::assertSame(0, $result['imported']);
	}

	public function testReplaceModeImportsNothingAtAllWhenAnyLineWasRejected()
	{
		$encoded = e107::getIPHandler()->ipEncode(self::IP_B);

		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => $encoded,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'old import',
			'banlist_notes'      => '',
		));

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_B . "\n<junk>!!\n"),
			array('replaceImported' => true));

		self::assertSame(0, $result['imported'],
			'replacing is all or nothing: a truncated download that imported its first lines would otherwise install a partial list');
		self::assertSame(str_replace('[x]', 1, BANLAN_IMPORT_REPLACE_INCOMPLETE), $result['fatal'],
			'and the admin has to be told, with the way out, or they read the run as a success');

		self::assertSame(1, (int) e107::getDb()->select('banlist', '*', "`banlist_ip` = '" . e107::getDb()->escape($encoded) . "'"),
			'the old batch is hidden from the duplicate check because it was going to be deleted, '
			. 'so a run that keeps it and its own rows both would double every address they share');
	}

	public function testReplaceModeKeepsTheOldBatchAndSaysSoWhenTheFileAddsNothing()
	{
		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => 'imported-test-keep',
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'old import',
			'banlist_notes'      => '',
		));

		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => e107::getIPHandler()->ipEncode(self::IP_A),
			'banlist_bantype'    => eIPHandler::BAN_TYPE_MANUAL,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'banned by hand, not by the import',
			'banlist_notes'      => '',
		));

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_A . "\n"),
			array('replaceImported' => true));

		self::assertSame(0, $result['imported']);
		self::assertSame(1, $result['duplicates']);
		self::assertSame(array(), $result['errors']);

		$type = e107::getDb()->retrieve('banlist', 'banlist_bantype', "`banlist_ip` = 'imported-test-keep'");
		self::assertEquals(eIPHandler::BAN_TYPE_IMPORTED, $type,
			'a file that adds nothing must not wipe the previous imported bans');
		self::assertContains(BANLAN_IMPORT_REPLACE_NOTHING, $result['warnings'],
			'an admin who ticked Replace and is told nothing has no way to tell the old batch survived');
	}

	/**
	 * A row sitting at BAN_TYPE_TEMPORARY was left there by a pre-2.3 import that
	 * died mid-run, and it bans nobody. A replace import is the admin's way out of
	 * that state: it leaves the dead row where it found it, because the row is not
	 * the run's to delete, and it bans the address again rather than counting it
	 * as already held.
	 * @see https://github.com/e107inc/e107/issues/6115
	 */
	public function testReplaceModeLeavesAPreExistingTemporaryRowAlone()
	{
		$encoded = e107::getIPHandler()->ipEncode(self::IP_C);

		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => $encoded,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_TEMPORARY,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'left behind by an import that died',
			'banlist_notes'      => '',
		));

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_B . "\n" . self::IP_C . "\n"),
			array('replaceImported' => true));

		self::assertSame(2, $result['imported']);
		self::assertSame(0, $result['duplicates']);

		$types = array();
		if(e107::getDb()->select('banlist', 'banlist_bantype', "`banlist_ip` = '" . e107::getDb()->escape($encoded) . "'"))
		{
			while($row = e107::getDb()->fetch())
			{
				$types[] = (int) $row['banlist_bantype'];
			}
		}
		sort($types);

		self::assertSame(array(eIPHandler::BAN_TYPE_TEMPORARY, eIPHandler::BAN_TYPE_IMPORTED), $types,
			'a row the run never parked is not the run\'s to delete, and one that bans nobody '
			. 'must not stop the import that would ban the address again');
	}

	public function testHostileRowsAreRejectedLineByLine()
	{
		$csv = "<script>alert(1)</script>\n"
			. "\n"
			. self::IP_A . ",garbage-date\n"
			. self::IP_B . ",0,0,-5,a,b,c\n"
			. "not valid because of spaces\n";

		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv));

		self::assertSame(0, $result['imported']);
		self::assertSame(array(1, 3, 4, 5), array_keys($result['errors']),
			'every bad line is reported under its own line number and the blank line is not');
	}

	public function testReasonAndNotesAreFilteredBeforeStorage()
	{
		$csv = '"' . self::IP_A . '","0","0","-5","<script>alert(1)</script>bad","line1' . "\x01" . 'line2"' . "\n";

		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv));

		self::assertSame(1, $result['imported']);

		$row = $this->rowFor(self::IP_A);
		self::assertSame('alert(1)bad', $row['banlist_reason'],
			'markup is stripped from the reason before it is stored');
		self::assertSame('line1 line2', $row['banlist_notes'],
			'control characters are collapsed to spaces before storage');
	}

	public function testLatin1ReasonSurvivesInsteadOfBeingBlanked()
	{
		$csv = '"' . self::IP_A . '","0","0","-5","caf' . "\xE9" . ' spam","' . "\xE9" . 'tienne"' . "\n";

		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv));

		self::assertSame(1, $result['imported']);

		$row = $this->rowFor(self::IP_A);
		self::assertSame("caf\xC3\xA9 spam", $row['banlist_reason'],
			'a reason exported from a latin1 site must not be silently replaced with an empty string');
		self::assertSame("\xC3\xA9tienne", $row['banlist_notes']);
	}

	public function testOversizeFileIsRefusedOutright()
	{
		$file = $this->haveCsv(str_repeat('a', banlistManager::CSV_IMPORT_MAX_BYTES + 1));

		$result = $this->mgr->importBanlistCsv($file);

		self::assertNotSame('', $result['fatal']);
		self::assertSame(0, $result['imported']);
	}

	public function testPipeSeparatorAndNoQuoteModeParse()
	{
		$csv = self::IP_A . '|20240102_030405|0|-5|reason "quoted"|notes' . "\n";

		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv), array(
			'separator' => '|',
			'quote'     => '',
		));

		self::assertSame(1, $result['imported']);

		$row = $this->rowFor(self::IP_A);
		self::assertSame('reason &quot;quoted&quot;', $row['banlist_reason']);
	}
	public function testUnterminatedQuoteAbortsAndKeepsPreviousImport()
	{
		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => 'imported-test-safe',
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'must survive',
			'banlist_notes'      => '',
		));

		$csv = self::IP_A . ',0,0,-5,ok,ok' . "\n"
			. self::IP_B . ',0,0,-5,"unterminated,note' . "\n"
			. self::IP_C . ',0,0,-5,ok,ok' . "\n";

		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv), array('replaceImported' => true));

		self::assertNotSame('', $result['fatal'], 'an unbalanced quote must abort, not silently drop the rows it swallowed');
		self::assertStringNotContainsString(str_replace('[x]', 1, BANLAN_IMPORT_ROLLBACK_FAILED), $result['fatal'],
			'the undo either removes the rows the run wrote or names how many of them it could not');
		self::assertSame(0, $result['imported']);
		self::assertEmpty($this->rowFor(self::IP_A),
			'the first line was written before the quote was met, so the undo has to take it back out');
		self::assertEmpty($this->rowFor(self::IP_C), 'and the line after the unbalanced one was never reached');

		$type = e107::getDb()->retrieve('banlist', 'banlist_bantype', "`banlist_ip` = 'imported-test-safe'");
		self::assertEquals(eIPHandler::BAN_TYPE_IMPORTED, $type,
			'an aborted replace run reaches no delete at all, so the previous imported bans are still there untouched');
	}

	public function testPreEpochExpiryIsRejectedNotStoredAsPermanent()
	{
		$csv = '"' . self::IP_A . '","0","19690101_000000","-5","",""' . "\n";

		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv), array('useFileExpiry' => true));

		self::assertSame(0, $result['imported'],
			'a pre-1970 expiry clamps to 0 in an unsigned column, which reads as never-expiring, so it must be refused');
		self::assertArrayHasKey(1, $result['errors']);
	}

	public function testImportedWildcardBanReachesTheBanFileInEnforceableForm()
	{
		$encoded = e107::getIPHandler()->ipEncode('10.77.66.*', true);
		$this->cleanupIps[] = $encoded;

		$result = $this->mgr->importBanlistCsv($this->haveCsv('10.77.66.*' . "\n"));

		self::assertSame(1, $result['imported']);

		$token = (string) substr($encoded, 0, strpos($encoded, 'x'));

		self::assertStringContainsString($token, file_get_contents($this->banFile()),
			'a dotted wildcard must be stored encoded, or the ban file carries a token no visitor can match');
		self::assertStringStartsWith($token, e107::getIPHandler()->ipEncode(self::IP_A),
			'and the token must be a prefix of an address inside the banned range');
	}

	public function testReimportingAnExportedWildcardBanDoesNotCollapseItToOneHost()
	{
		$encoded = e107::getIPHandler()->ipEncode('10.77.66.*', true);
		$this->cleanupIps[] = $encoded;
		$this->cleanupIps[] = e107::getIPHandler()->ipEncode('10.77.66.0');

		$result = $this->mgr->importBanlistCsv($this->haveCsv($encoded . "\n"));

		self::assertSame(1, $result['imported']);
		self::assertNotEmpty($this->storedRow($encoded),
			'an exported /24 ban must survive re-import, not collapse to the single host 10.77.66.0');
	}

	public function testImportedDomainBanCannotMatchAnUnrelatedVisitor()
	{
		$this->cleanupIps[] = '*.de';
		$this->cleanupIps[] = e107::getIPHandler()->ipEncode('*.de', true);

		$result = $this->mgr->importBanlistCsv($this->haveCsv('*.de' . "\n"));

		self::assertSame(1, $result['imported']);
		self::assertNotEmpty($this->storedRow('*.de'),
			'a domain ban is not an address and must reach the table as it was written');

		$visitor = e107::getIPHandler()->ipEncode('203.0.113.9');
		foreach(file($this->banFile(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
		{
			self::assertStringStartsNotWith((string) strtok($line, ' '), $visitor,
				'no ban file token may prefix an address the imported file never mentioned');
		}
	}

	public function testHexOnlyHostnameIsNotMistakenForAnAddress()
	{
		$this->cleanupIps[] = 'bad.cc';

		$result = $this->mgr->importBanlistCsv($this->haveCsv('bad.cc' . "\n"));

		self::assertSame(1, $result['imported']);
		self::assertNotEmpty($this->storedRow('bad.cc'),
			'a hostname whose every character happens to be a hex digit is still a hostname');
	}

	public function testHostnameTooLongToPrefixAnEncodedAddressIsStillImported()
	{
		$this->cleanupIps[] = 'deadbeef';

		$result = $this->mgr->importBanlistCsv($this->haveCsv('deadbeef' . "\n"));

		self::assertSame(1, $result['imported'],
			'the fifth character of an encoded address is always a colon, so five hex characters cannot prefix one');
		self::assertNotEmpty($this->storedRow('deadbeef'));
	}

	public function testDomainBanCutAtALiteralXIsStoredAndNamesNoAddress()
	{
		$this->cleanupIps[] = 'example.com';

		$result = $this->mgr->importBanlistCsv($this->haveCsv('example.com' . "\n"));

		self::assertSame(1, $result['imported'],
			'trimWildcard() cuts a hostname at its first literal x, and the "e" it leaves is no reason to refuse a domain ban');
		self::assertNotEmpty($this->storedRow('example.com'));

		$visitor = e107::getIPHandler()->ipEncode('e123::9');
		foreach(file($this->banFile(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
		{
			self::assertStringStartsNotWith((string) strtok($line, ' '), $visitor,
				'and no ban file token may be cut out of a hostname, which is how a domain ban came to ban e000::/4');
		}
	}

	public function testHexOnlyEntryWithNoDotIsRefusedInsteadOfBanningAnIpv6Range()
	{
		$this->cleanupIps[] = 'dead';

		$result = $this->mgr->importBanlistCsv($this->haveCsv('dead' . "\n"));

		self::assertSame(0, $result['imported'],
			'hex digits with neither a dot nor a colon reach the ban file as an IPv6 prefix, and a single "f" would ban every link-local visitor');
		self::assertArrayHasKey(1, $result['errors']);

		$this->mgr->writeBanListFiles('ip,htaccess');

		$visitor = e107::getIPHandler()->ipEncode('dead:beef::1');
		foreach(file($this->banFile(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
		{
			self::assertStringStartsNotWith((string) strtok($line, ' '), $visitor,
				'no ban file token may prefix an address the imported file never named');
		}
	}

	public function testMidWildcardBanIsRefusedInsteadOfBanningEverythingInFrontOfIt()
	{
		$this->cleanupIps[] = '10.*.66.5';
		$this->cleanupIps[] = e107::getIPHandler()->ipEncode('10.*.66.5', true);

		$result = $this->mgr->importBanlistCsv($this->haveCsv('10.*.66.5' . "\n"));

		self::assertSame(0, $result['imported'],
			'the encoder cuts the stored value at the first wildcard, so this entry would ban all of 10.0.0.0/8');
		self::assertArrayHasKey(1, $result['errors']);

		$this->mgr->writeBanListFiles('ip,htaccess');

		$visitor = e107::getIPHandler()->ipEncode('10.0.0.7');
		foreach(file($this->banFile(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
		{
			self::assertStringStartsNotWith((string) strtok($line, ' '), $visitor,
				'no ban file token may prefix an address outside the range the imported file named');
		}
	}

	public function testZeroPaddedWildcardBanIsStoredEncodedSoItCanStillMatch()
	{
		$encoded = e107::getIPHandler()->ipEncode('214.098.*.*', true);
		$this->cleanupIps[] = $encoded;
		$this->cleanupIps[] = '214.098.*.*';

		$result = $this->mgr->importBanlistCsv($this->haveCsv('214.098.*.*' . "\n"));

		self::assertSame(1, $result['imported']);
		self::assertNotEmpty($this->storedRow($encoded),
			'the zero-padded octet is the form the admin help documents and the one 0.7-era lists are full of');

		$token = (string) substr($encoded, 0, strpos($encoded, 'x'));
		self::assertStringStartsWith($token, e107::getIPHandler()->ipEncode('214.98.1.2'),
			'and the stored token has to prefix the addresses the admin asked to ban');
	}

	public function testIpv6WildcardTheEncoderCannotExpressIsRefusedRatherThanNarrowed()
	{
		$this->cleanupIps[] = '2001:db8::*';
		$this->cleanupIps[] = e107::getIPHandler()->ipEncode('2001:db8::*', true);

		$result = $this->mgr->importBanlistCsv($this->haveCsv('2001:db8::*' . "\n"));

		self::assertSame(0, $result['imported'],
			'hexdec() reads a wildcard group as zero, so this would ban one host and leave the subnet open');
		self::assertArrayHasKey(1, $result['errors']);
		self::assertEmpty($this->storedRow(e107::getIPHandler()->ipEncode('2001:db8::*', true)),
			'an entry the encoder cannot represent must not reach the table as a different ban');
	}

	public function testTruncatedIpv6PrefixIsRefusedInsteadOfBanningEveryIpv4Visitor()
	{
		$this->cleanupIps[] = '0000:0000:0000:0000:0000:ffff';

		$result = $this->mgr->importBanlistCsv($this->haveCsv('0000:0000:0000:0000:0000:ffff' . "\n"));

		self::assertSame(0, $result['imported'],
			'every stored IPv4 address begins with this prefix, so one such line would take the site dark');
		self::assertArrayHasKey(1, $result['errors']);

		$this->mgr->writeBanListFiles('ip,htaccess');

		$visitor = e107::getIPHandler()->ipEncode('203.0.113.9');
		foreach(file($this->banFile(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
		{
			self::assertStringStartsNotWith((string) strtok($line, ' '), $visitor,
				'no ban file token may prefix an address the imported file never named');
		}
	}

	public function testIpv6TheEncoderWidensToNineGroupsIsRefusedInsteadOfStoredUnmatchable()
	{
		$encoded = e107::getIPHandler()->ipEncode('::1:2:3:4:5:6:7', true);
		$this->cleanupIps[] = $encoded;
		$this->cleanupIps[] = '::1:2:3:4:5:6:7';

		$result = $this->mgr->importBanlistCsv($this->haveCsv('::1:2:3:4:5:6:7' . "\n"));

		self::assertSame(0, $result['imported'],
			'seven groups either side of a "::" encode to nine, and 44 characters cannot prefix a 39-character address');
		self::assertArrayHasKey(1, $result['errors']);
		self::assertEmpty($this->storedRow($encoded),
			'a ban that can never match must not be counted as imported');
	}

	public function testExpandedIpv6WildcardSurvivesBecauseRawStorageAlreadyBansItsRange()
	{
		$this->cleanupIps[] = '2001:0db8:*';

		$result = $this->mgr->importBanlistCsv($this->haveCsv('2001:0db8:*' . "\n"));

		self::assertSame(1, $result['imported'],
			'the zero-padded expanded spelling is the IPv6 range ban the admin help documents');
		self::assertNotEmpty($this->storedRow('2001:0db8:*'));

		self::assertStringContainsString('2001:0db8:', file_get_contents($this->banFile()));
		self::assertStringStartsWith('2001:0db8:', e107::getIPHandler()->ipEncode('2001:db8::42'),
			'and what is left of it at the wildcard has to prefix the addresses the admin asked to ban');
	}

	public function testUppercaseIpv6WildcardIsStoredLowercasedInsteadOfMatchingNobody()
	{
		foreach(array('2001:0DB8:*', '2001:0db8:*', '2001:0DB8:42XX', '2001:0db8:42xx') as $ip)
		{
			$this->cleanupIps[] = $ip;
		}

		$result = $this->mgr->importBanlistCsv($this->haveCsv("2001:0DB8:*\n2001:0DB8:42XX\n"));

		self::assertSame(2, $result['imported']);

		$row = $this->storedRow('2001:0db8:*');
		self::assertNotEmpty($row);
		self::assertSame('2001:0db8:*', $row['banlist_ip'],
			'the entry has to reach the table lowercased, not merely the ban file, or the dedup pass and the next export disagree with it');

		$banFile = file_get_contents($this->banFile());
		self::assertStringContainsString('2001:0db8: ', $banFile,
			'the address each visitor is compared against is lowercase, so an uppercase entry stored as written is reported as imported and bans nobody');
		self::assertStringContainsString('2001:0db8:42 ', $banFile,
			'and an uppercase X is the wildcard an export writes, so it has to survive the same way');
	}

	public function testMidWildcardIpv6BanIsRefusedInsteadOfBanningEverythingInFrontOfIt()
	{
		$this->cleanupIps[] = 'f*::1';

		$result = $this->mgr->importBanlistCsv($this->haveCsv('f*::1' . "\n"));

		self::assertSame(0, $result['imported'],
			'the stored value is cut at its first wildcard, so this entry would ban every address in f000::/4 to ban one');
		self::assertArrayHasKey(1, $result['errors']);

		$this->mgr->writeBanListFiles('ip,htaccess');

		$visitor = e107::getIPHandler()->ipEncode('fe80::1');
		foreach(file($this->banFile(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
		{
			self::assertStringStartsNotWith((string) strtok($line, ' '), $visitor,
				'no ban file token may prefix an address outside the range the imported file named');
		}
	}

	public function testImportedBackslashIsStoredEncodedSoTheNextExportStaysImportable()
	{
		$csv = '"' . self::IP_A . '","0","0","-5","path C:\\Users x","ok"' . "\n";

		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv));

		self::assertSame(1, $result['imported']);
		self::assertSame('path C:&#092;Users x', $this->rowFor(self::IP_A)['banlist_reason'],
			'a literal backslash would escape the closing quote of the next export, which the importer then refuses whole');
	}

	public function testDateColumnsArriveWrappedInMarkupFromTheExportPageAndStillImport()
	{
		$stamp = mktime(3, 4, 5, 1, 2, 2024);
		$expiry = mktime(3, 4, 5, 1, 2, 2099);
		$tp = e107::getParser();

		$csv = '"' . self::IP_A . '","' . $tp->toDate($stamp, '%Y%m%d_%H%M%S') . '","'
			. $tp->toDate($expiry, '%Y%m%d_%H%M%S') . '","-1","Manual ban",""' . "\n";

		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv), array('useFileExpiry' => true));

		self::assertSame('', $result['fatal']);
		self::assertSame(array(), $result['errors']);
		self::assertSame(1, $result['imported'],
			'e107_admin/banlist_export.php renders both stamps through e_parse::toDate(), which wraps its output in a span, so every row of a real export carries markup in these two columns');

		$row = $this->rowFor(self::IP_A);
		self::assertEquals($stamp, $row['banlist_datestamp']);
		self::assertEquals($expiry, $row['banlist_banexpires']);
	}

	public function testColonAfterAWildcardCannotSmuggleABareHexTokenIntoTheBanFile()
	{
		foreach(array('f*:', 'dead*:', '2001*:', 'fx:') as $ip)
		{
			$this->cleanupIps[] = $ip;
		}

		$result = $this->mgr->importBanlistCsv($this->haveCsv("f*:\ndead*:\n2001*:\nfx:\n"));

		self::assertSame(0, $result['imported'],
			'each of these cuts at its wildcard to a bare run of hex characters, which names no group boundary and so bans every address underneath it');
		self::assertCount(4, $result['errors']);

		$this->mgr->writeBanListFiles('ip,htaccess');

		$lines = file($this->banFile(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach(array('fe80::1', 'dead::1', '2001:db8::1') as $address)
		{
			$visitor = e107::getIPHandler()->ipEncode($address);
			foreach($lines as $line)
			{
				self::assertStringStartsNotWith((string) strtok($line, ' '), $visitor,
					'no ban file token may prefix an address the imported file never named');
			}
		}
	}

	public function testABanOnAWhitelistedAddressIsStoredRatherThanCountedAsADuplicate()
	{
		$encoded = e107::getIPHandler()->ipEncode(self::IP_A);

		e107::getDb()->insert('banlist', array(
			'banlist_ip'         => $encoded,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_WHITELIST,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'office range',
			'banlist_notes'      => '',
		));

		$csv = '"' . self::IP_A . '","0","0","-5","blocklist",""' . "\n";
		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv), array('useFileExpiry' => true));

		self::assertSame(0, $result['duplicates'],
			'a whitelist entry is not a ban, so reporting the address as already listed tells the admin something untrue');
		self::assertSame(1, $result['imported']);
		self::assertSame(1, (int) e107::getDb()->select('banlist', '*',
			"`banlist_ip` = '" . e107::getDb()->escape($encoded) . "' AND `banlist_bantype` = " . eIPHandler::BAN_TYPE_IMPORTED),
			'the ban has to be on the list for the day the whitelist entry is taken off it');
	}

	public function testTruncatedReasonKeepsItsLastCompleteCharacter()
	{
		$whole = str_repeat('a', 253) . "\xC3\xA9";
		$split = str_repeat('b', 254) . "\xC3\xA9";
		$csv = '"' . self::IP_A . '","0","0","-5","' . $whole . ' and more text past the limit",""' . "\n"
			. '"' . self::IP_B . '","0","0","-5","' . $split . ' and more text past the limit",""' . "\n";

		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv));

		self::assertSame(2, $result['imported']);
		self::assertSame($whole, $this->rowFor(self::IP_A)['banlist_reason'],
			'the 255th byte ends a complete character here, so nothing needs trimming back to keep the column valid UTF-8');
		self::assertSame(str_repeat('b', 254), $this->rowFor(self::IP_B)['banlist_reason'],
			'the 255-byte cut lands inside the last character here, and half a character is not stored');
	}

	/**
	 * @dataProvider importMessagePlaceholders
	 * @param string $constant a LAN key the import reports through
	 * @param array $placeholders every token its call site fills in
	 */
	public function testImportMessagesCarryThePlaceholdersTheirCallSitesFill($constant, array $placeholders)
	{
		self::assertTrue(defined($constant), $constant . ' has to be in the banlist language file');

		foreach($placeholders as $placeholder)
		{
			self::assertStringContainsString($placeholder, constant($constant),
				'a message missing its placeholder drops the number it was written to carry');
		}
	}

	/**
	 * @return array
	 */
	public function importMessagePlaceholders()
	{
		return array(
			array('BANLAN_IMPORT_TOO_LARGE', array('[x]')),
			array('BANLAN_IMPORT_LINE_SKIPPED', array('[x]', '[y]')),
			array('BANLAN_IMPORT_DUPLICATES', array('[x]')),
			array('BANLAN_IMPORT_MORE_SKIPPED', array('[x]')),
			array('BANLAN_IMPORT_REPLACE_INCOMPLETE', array('[x]')),
			array('BANLAN_IMPORT_ROLLBACK_FAILED', array('[x]')),
			array('BANLAN_IMPORT_LAPSED_KEPT', array('[x]')),
			array('BANLAN_IMPORT_REPLACE_KEPT', array('[x]')),
			array('BANLAN_IMPORT_LOG_SUMMARY', array('[file]', '[imported]', '[duplicates]', '[rejected]')),
		);
	}
}
