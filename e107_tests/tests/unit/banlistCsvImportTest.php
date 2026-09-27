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

/**
 * banlistManager::importBanlistCsv() ingests admin-supplied files into the ban
 * system, so it must round-trip the banlist_export.php format, refuse rows it
 * cannot vouch for, and leave the table untouched when a run cannot finish.
 */
class banlistCsvImportTest extends \Test\Unit
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
				$used[] = e107::getIPHandler()->ipEncode($ip);
				$used[] = $ip;
			}
			$used = array_merge($used, $this->cleanupIps);
			e107::getDb()->createQueryBuilder()->delete('banlist')
				->whereIn('banlist_ip', $used)
				->orWhereLike('banlist_ip', 'imported-test-%')
				->execute();

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
		return e107::getDb()->createQueryBuilder()->select('*')->from('banlist')
			->where('banlist_ip', $ip)
			->fetchRow();
	}

	/**
	 * Whether the range table the import left behind covers an address, which is the question every page load asks of it.
	 *
	 * @param string $address as a visitor arrives with it
	 * @return bool
	 */
	private function isCovered($address)
	{
		$file = e107::getIPHandler()->getConfigDir() . eIPHandler::BAN_FILE_RANGES_NAME . eIPHandler::BAN_FILE_EXTENSION;
		self::assertFileExists($file, 'the import has to leave a range table behind');

		$set = RangeFile::open($file);
		self::assertNotNull($set, 'the range table has to be one this version reads');

		$segment = $set->find(Address::toHex($address));

		return $segment >= 0 && $set->hits($segment) !== array();
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
		$locked = isset($row['locked']) ? (int) $row['locked'] : -1;

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
		return (int) $db->createQueryBuilder()->selectCount()->from('banlist')
			->where('banlist_ip', $ip)->fetchOne();
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

		self::assertSame(1, isset($row['released']) ? (int) $row['released'] : -1,
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
	 * @param string $statement SQL naming the lock as :name
	 * @return array|false the row it answers with
	 */
	private function lockQuery($db, $statement)
	{
		$db->execute($statement, array('name' => $this->lockName()));

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
		$db->execute('SELECT CONNECTION_ID() AS id');
		$row = $db->fetch();

		return is_array($row) ? (int) $row['id'] : 0;
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
		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => e107::getIPHandler()->ipEncode(self::IP_A),
			'banlist_bantype'    => eIPHandler::BAN_TYPE_MANUAL,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'pre-existing',
			'banlist_notes'      => '',
		))->execute();

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
		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => 'imported-test-old',
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'old import',
			'banlist_notes'      => '',
		))->execute();

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_B . "\n"),
			array('replaceImported' => true));

		self::assertSame(1, $result['imported']);
		self::assertSame(0, $this->countRows(e107::getDb(), 'imported-test-old'),
			'replaceImported swaps the previous batch of imported bans for the file');
		self::assertNotEmpty($this->rowFor(self::IP_B));
	}

	public function testAReplaceImportIsRefusedWhileAnotherRunIsReplacing()
	{
		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => 'imported-test-old',
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'written by the run that is still going',
			'banlist_notes'      => '',
		))->execute();

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
		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => 'imported-test-old',
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'the batch the run is replacing',
			'banlist_notes'      => '',
		))->execute();

		self::assertSame(1, $this->countRows($this->probeDb(), 'imported-test-old'),
			'the batch this run replaces has to be there to start with, so that a count of none later reads as the delete rather than as a read that answered nothing');

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
		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => e107::getIPHandler()->ipEncode(self::IP_A),
			'banlist_bantype'    => eIPHandler::BAN_TYPE_MANUAL,
			'banlist_datestamp'  => time() - 7200,
			'banlist_banexpires' => time() - 3600,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'expired an hour ago',
			'banlist_notes'      => '',
		))->execute();

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_A . "\n"));

		self::assertSame(0, $result['duplicates'],
			'an expired row bans nobody, so reporting the address as already on the list is a false statement');
		self::assertSame(1, $result['imported'],
			'a refreshed blocklist has to reinstate an address whose old ban has run out');
	}

	public function testReimportingAfterARowLapsesSupersedesItRatherThanStackingASecondCopy()
	{
		$encoded = e107::getIPHandler()->ipEncode(self::IP_A);

		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => $encoded,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time() - 7200,
			'banlist_banexpires' => time() - 3600,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'lapsed an hour ago',
			'banlist_notes'      => '',
		))->execute();

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_A . "\n"));

		self::assertSame(1, $result['imported']);
		self::assertSame(array(), $result['warnings']);

		$rows = e107::getDb()->createQueryBuilder()->select('banlist_reason')->from('banlist')
			->where('banlist_ip', $encoded)->fetchAll();

		self::assertCount(1, $rows,
			'a blocklist re-imported on a schedule finds its own lapsed rows every run, and leaving them '
			. 'beside the fresh ones grows the table by the size of the list each time');
		self::assertNotSame('lapsed an hour ago', $rows[0]['banlist_reason'],
			'and the row that survives is the one the file just wrote, not the one it replaced');
	}

	public function testAnUnencodedLegacyRowIsRecognisedAsTheSameAddressTheFileNames()
	{
		$this->cleanupIps[] = self::IP_A;

		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => self::IP_A,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_MANUAL,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'written before addresses were encoded',
			'banlist_notes'      => '',
		))->execute();

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_A . "\n"));

		self::assertSame(1, $result['duplicates'],
			'a site upgraded rather than installed holds the plain spelling, and exporting its own list '
			. 'and importing it back must not give every one of those addresses a second row');
		self::assertSame(0, $result['imported']);
	}

	public function testReplaceModeImportsNothingAtAllWhenAnyLineWasRejected()
	{
		$encoded = e107::getIPHandler()->ipEncode(self::IP_B);

		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => $encoded,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'old import',
			'banlist_notes'      => '',
		))->execute();

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_B . "\n<junk>!!\n"),
			array('replaceImported' => true));

		self::assertSame(0, $result['imported'],
			'replacing is all or nothing: a truncated download that imported its first lines would otherwise install a partial list');
		self::assertSame(str_replace('[x]', 1, BANLAN_IMPORT_REPLACE_INCOMPLETE), $result['fatal'],
			'and the admin has to be told, with the way out, or they read the run as a success');

		$rows = e107::getDb()->createQueryBuilder()->select('banlist_id')->from('banlist')
			->where('banlist_ip', $encoded)->fetchAll();
		self::assertCount(1, $rows,
			'the old batch is hidden from the duplicate check because it was going to be deleted, '
			. 'so a run that keeps it and its own rows both would double every address they share');
	}

	public function testReplaceModeKeepsTheOldBatchAndSaysSoWhenTheFileAddsNothing()
	{
		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => 'imported-test-keep',
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'old import',
			'banlist_notes'      => '',
		))->execute();

		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => e107::getIPHandler()->ipEncode(self::IP_A),
			'banlist_bantype'    => eIPHandler::BAN_TYPE_MANUAL,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'banned by hand, not by the import',
			'banlist_notes'      => '',
		))->execute();

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_A . "\n"),
			array('replaceImported' => true));

		self::assertSame(0, $result['imported']);
		self::assertSame(1, $result['duplicates']);
		self::assertSame(array(), $result['errors']);

		$type = e107::getDb()->createQueryBuilder()->select('banlist_bantype')->from('banlist')
			->where('banlist_ip', 'imported-test-keep')->fetchOne();
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

		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => $encoded,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_TEMPORARY,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'left behind by an import that died',
			'banlist_notes'      => '',
		))->execute();

		$result = $this->mgr->importBanlistCsv($this->haveCsv(self::IP_B . "\n" . self::IP_C . "\n"),
			array('replaceImported' => true));

		self::assertSame(2, $result['imported']);
		self::assertSame(0, $result['duplicates']);

		$rows = e107::getDb()->createQueryBuilder()->select('banlist_bantype')->from('banlist')
			->where('banlist_ip', $encoded)->fetchAll();
		$types = array_map('intval', array_column($rows, 'banlist_bantype'));
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
		self::assertStringNotContainsString('<script>', $row['banlist_reason']);
		self::assertStringNotContainsString("\x01", $row['banlist_notes']);
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
		self::assertSame('reason &quot;quoted&quot;', $this->rowFor(self::IP_A)['banlist_reason']);
	}
	public function testUnterminatedQuoteAbortsAndKeepsPreviousImport()
	{
		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => 'imported-test-safe',
			'banlist_bantype'    => eIPHandler::BAN_TYPE_IMPORTED,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'must survive',
			'banlist_notes'      => '',
		))->execute();

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

		$type = e107::getDb()->createQueryBuilder()->select('banlist_bantype')->from('banlist')
			->where('banlist_ip', 'imported-test-safe')->fetchOne();
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

	public function testImportedWildcardBanReachesTheCompiledRangeTableInEnforceableForm()
	{
		$this->cleanupIps[] = '10.77.66.*';

		$result = $this->mgr->importBanlistCsv($this->haveCsv('10.77.66.*' . "\n"));

		self::assertSame(1, $result['imported']);
		self::assertNotEmpty($this->storedRow('10.77.66.*'),
			'a range is stored as the file wrote it, which is the form the range compiler reads');

		self::assertTrue($this->isCovered(self::IP_A),
			'the import has to regenerate the range table, or the row it wrote bans nobody');
		self::assertFalse($this->isCovered('10.77.67.1'),
			'and the range it compiles to must stop where the imported entry said it did');
	}

	public function testImportedDomainBanCannotMatchAnUnrelatedVisitor()
	{
		$this->cleanupIps[] = '*.de';

		$result = $this->mgr->importBanlistCsv($this->haveCsv('*.de' . "\n"));

		self::assertSame(1, $result['imported']);
		self::assertNotEmpty($this->storedRow('*.de'),
			'a domain ban is not an address and must reach the table as it was written');

		self::assertFalse($this->isCovered('203.0.113.9'),
			'a reverse-DNS ban names no addresses, so importing one may not put any into the range table');
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

	public function testHexOnlyEntryWithNoDotIsRefusedInsteadOfBanningAnIpv6Range()
	{
		foreach(array('dead', '2a', 'f') as $ip)
		{
			$this->cleanupIps[] = $ip;
		}

		$result = $this->mgr->importBanlistCsv($this->haveCsv("dead\n2a\nf\n"));

		self::assertSame(0, $result['imported'],
			'hex digits with neither a dot nor a colon name no address, and stored verbatim a single "f" would ban every link-local visitor');
		self::assertCount(3, $result['errors']);

		self::assertFalse($this->isCovered('dead:beef::1'),
			'and nothing the file named may reach the range table');
	}

	public function testUppercaseIpv6WildcardIsStoredLowercasedInsteadOfMatchingNobody()
	{
		foreach(array('2001:0DB8:XXXX:XXXX:XXXX:XXXX:XXXX:XXXX', '2001:0db8:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx') as $ip)
		{
			$this->cleanupIps[] = $ip;
		}

		$result = $this->mgr->importBanlistCsv($this->haveCsv('2001:0DB8:XXXX:XXXX:XXXX:XXXX:XXXX:XXXX' . "\n"));

		self::assertSame(1, $result['imported']);

		$row = $this->storedRow('2001:0db8:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx');
		self::assertNotEmpty($row);
		self::assertSame('2001:0db8:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx', $row['banlist_ip'],
			'the entry has to reach the table lowercased, or the dedup pass and the next export disagree with it');

		self::assertTrue($this->isCovered('2001:db8::42'),
			'an uppercase range counted as imported and matching nobody is the failure this refuses to repeat');
	}

	/**
	 * @dataProvider entryShapes
	 * @param string $entry as a CSV file writes it
	 * @param string|null $stored banlist_ip it must reach the table as, or null where it must be refused
	 * @param string $why
	 */
	public function testEntryShapesAreStoredAsWrittenOrRefusedOutright($entry, $stored, $why)
	{
		$this->cleanupIps[] = $entry;

		if($stored !== null)
		{
			$this->cleanupIps[] = $stored;
		}

		$result = $this->mgr->importBanlistCsv($this->haveCsv($entry . "\n"));

		if($stored === null)
		{
			self::assertSame(0, $result['imported'], $why);
			self::assertArrayHasKey(1, $result['errors']);

			return;
		}

		self::assertSame(1, $result['imported'], $why);
		self::assertSame(array(), $result['errors']);
		self::assertNotEmpty($this->storedRow($stored), $why);
	}

	/**
	 * @return array entry, stored form or null, and what the row is there to hold
	 */
	public function entryShapes()
	{
		return array(
			'dotted wildcard' => array('10.77.66.*', '10.77.66.*',
				'a trailing-wildcard range is the commonest imported ban there is'),
			'zero-padded octets' => array('214.098.*.*', '214.098.*.*',
				'the zero-padded octet is the form the admin help documents and the one 0.7-era lists are full of'),
			'expanded ipv6 wildcard' => array('2001:0db8:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx', '2001:0db8:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx',
				'an IPv6 range is written as all eight groups, which is what an export of one contains'),
			'ipv6 address shortened to the limit' => array('::1:2:3:4:5:6:7', '0000:0001:0002:0003:0004:0005:0006:0007',
				'a single address is stored encoded however the file spelled it'),
			'reverse-dns pattern' => array('*.de', '*.de',
				'a domain ban bans only the hosts that resolve to it and is stored as written'),
			'email pattern' => array('*@example.com', '*@example.com',
				'the transfer format carries email bans as well as addresses'),
			'0.7-era packed hex' => array('deadbeef', '0000:0000:0000:0000:0000:ffff:dead:beef',
				'eight hex characters is how 0.7 wrote an IPv4 address, and lists that old are the ones being imported'),

			'mid wildcard' => array('10.*.66.5', null,
				'a wildcard with octets behind it names no contiguous range, and read as a prefix it would ban all of 10.0.0.0/8'),
			'mid wildcard ipv6' => array('f*::1', null,
				'the same shape in IPv6, which read as a prefix would ban every address in f000::/4'),
			'partial ipv6 wildcard' => array('2001:db8::*', null,
				'a wildcard standing for a variable number of groups names no fixed range'),
			'partial ipv6 wildcard expanded' => array('2001:0db8:*', null,
				'and neither does one that stops part-way through the eight groups'),
			'truncated encoded prefix' => array('0000:0000:0000:0000:0000:ffff', null,
				'every stored IPv4 address begins with this, so stored verbatim one such line would take the site dark'),
			'hex run with a trailing colon' => array('f*:', null,
				'cut at its wildcard this is a bare run of hex characters, which names no group boundary'),
			'longer hex run with a trailing colon' => array('dead*:', null,
				'the same shape, and the file may not decide how many groups it stands for'),
			'numeric hex run with a trailing colon' => array('2001*:', null,
				'a group that looks like the start of a real prefix is still an incomplete one'),
			'x wildcard with a trailing colon' => array('fx:', null,
				'x is the wildcard an export writes, and it may not shorten a range either'),
			'bare hostname' => array('example.com', null,
				'a host ban is written *.example.com, which is what the admin form accepts too'),
			'hostname of hex characters' => array('bad.cc', null,
				'a hostname is not an address however its characters read, and bare it is not a host pattern'),
		);
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

	public function testABanOnAWhitelistedAddressIsStoredRatherThanCountedAsADuplicate()
	{
		$encoded = e107::getIPHandler()->ipEncode(self::IP_A);

		e107::getDb()->createQueryBuilder()->insert('banlist')->values(array(
			'banlist_ip'         => $encoded,
			'banlist_bantype'    => eIPHandler::BAN_TYPE_WHITELIST,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'office range',
			'banlist_notes'      => '',
		))->execute();

		$csv = '"' . self::IP_A . '","0","0","-5","blocklist",""' . "\n";
		$result = $this->mgr->importBanlistCsv($this->haveCsv($csv), array('useFileExpiry' => true));

		self::assertSame(0, $result['duplicates'],
			'a whitelist entry is not a ban, so reporting the address as already listed tells the admin something untrue');
		self::assertSame(1, $result['imported']);
		self::assertSame(1, (int) e107::getDb()->createQueryBuilder()->selectCount()->from('banlist')
			->where('banlist_ip', $encoded)
			->where('banlist_bantype', eIPHandler::BAN_TYPE_IMPORTED)
			->fetchOne(),
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
