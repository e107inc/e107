<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The figure a File Inspector scan reports and its admin page polls. The scan
 * and the poll are separate requests, so this store is the only thing that
 * carries a percentage between them.
 */
class e_file_inspector_progressTest extends \Test\Unit
{
	const OWNER = 990001;
	const OTHER_OWNER = 990002;

	/** @var e_file_inspector_progress */
	private $progress;

	/** @var e_file_inspector_progress */
	private $otherProgress;

	protected function _before()
	{
		require_once(e_HANDLER.'e_file_inspector.php');

		$this->progress = new e_file_inspector_progress(self::OWNER);
		$this->otherProgress = new e_file_inspector_progress(self::OTHER_OWNER);

		$this->progress->discard();
		$this->otherProgress->discard();
	}

	protected function _after()
	{
		$this->progress->discard();
		$this->otherProgress->discard();
	}

	public function testNoScanOnRecordReadsAsComplete()
	{
		$this->assertSame(e_file_inspector_progress::COMPLETE, $this->progress->percentComplete());
	}

	public function testRecordedPercentageComesBack()
	{
		$this->progress->record(37);

		$this->assertSame(37, $this->progress->percentComplete());
	}

	public function testARecordReplacesTheOneBeforeIt()
	{
		$this->progress->record(10);
		$this->progress->record(20);

		$this->assertSame(20, $this->progress->percentComplete());
		$this->assertSame(1, $this->countRows(self::OWNER));
	}

	public function testOneUsersScanIsInvisibleToAnother()
	{
		$this->progress->record(42);

		$this->assertSame(42, $this->progress->percentComplete());
		$this->assertSame(e_file_inspector_progress::COMPLETE, $this->otherProgress->percentComplete());
	}

	public function testCompletionLeavesNothingBehind()
	{
		$this->progress->record(50);
		$this->progress->record(e_file_inspector_progress::COMPLETE);

		$this->assertSame(0, $this->countRows(self::OWNER));
		$this->assertSame(e_file_inspector_progress::COMPLETE, $this->progress->percentComplete());
	}

	public function testDiscardRemovesARunningScan()
	{
		$this->progress->record(15);
		$this->progress->discard();

		$this->assertSame(0, $this->countRows(self::OWNER));
	}

	/**
	 * The row carries the current time, so class2.php's five-minute sweep of the
	 * `tmp` table only reaches a scan that has stopped reporting.
	 */
	public function testARecordIsStampedWithTheTimeItWasMade()
	{
		$before = time();
		$this->progress->record(5);

		$row = e107::getDb()->createQueryBuilder()
			->select('tmp_time')->from('tmp')
			->where('tmp_ip', e_file_inspector_progress::TMP_KEY_PREFIX.self::OWNER)
			->fetchRow();

		$this->assertGreaterThanOrEqual($before, (int) $row['tmp_time']);
	}

	/**
	 * @param int $ownerId
	 * @return int
	 */
	private function countRows($ownerId)
	{
		$rows = e107::getDb()->createQueryBuilder()
			->select('tmp_ip')->from('tmp')
			->where('tmp_ip', e_file_inspector_progress::TMP_KEY_PREFIX.$ownerId)
			->fetchAll();

		return count($rows);
	}
}
