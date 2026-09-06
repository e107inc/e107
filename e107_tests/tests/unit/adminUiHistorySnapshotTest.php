<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Admin History records what the table held, not what the edit form's model held.
 *
 * @group core
 */
class adminUiHistorySnapshotTest extends \Test\Unit
{
	/** @var string prefixed scratch table */
	private $table;

	protected function _before()
	{
		$this->table = MPREFIX . 'admin_ui_history_probe';

		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiHistoryProbeFixture.php');

		$sql = e107::getDb();
		$sql->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->table . '`');
		$sql->gen('CREATE TEMPORARY TABLE `' . $this->table
			. '` (probe_id INT NOT NULL, probe_fields VARCHAR(255) NULL, probe_menu VARCHAR(255) NULL)');
		$sql->gen('INSERT INTO `' . $this->table
			. '` (probe_id, probe_fields, probe_menu) VALUES (1, \'{"colour":"red"}\', \'left\')');
	}

	protected function _after()
	{
		e107::getDb()->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->table . '`');
	}

	private function probe(array $inModel, array $written)
	{
		$model = new AdminUiHistorySpyModel('admin_ui_history_probe', 'probe_id', 1, $inModel, $written);

		return new AdminUiHistoryProbeFixture('admin_ui_history_probe', 'probe_id', $model);
	}

	/**
	 * e_customfields::setAdminUIData() nulls the real column in the model so the per-key
	 * pseudo-fields can carry the values into the form, and the observer that calls it runs
	 * before the submit trigger. The snapshot must not see that null.
	 */
	public function testUpdateRecordsTheStoredValueWhereAnObserverNulledTheModel()
	{
		$probe = $this->probe(
			array('probe_id' => 1, 'probe_fields' => null, 'probe_menu' => 'left'),
			array('probe_fields' => '{"colour":"blue"}')
		);

		$probe->probeSubmit();

		$this->assertCount(1, $probe->backups, 'one history record for the one column that changed');
		$this->assertSame('update', $probe->backups[0]['action']);
		$this->assertSame(
			array('probe_fields' => '{"colour":"red"}'),
			$probe->backups[0]['data'],
			'the value the table held, not the null the observer left in the model'
		);
	}

	/**
	 * The snapshot is read before the write, so a column whose new value equals the stored one
	 * is not recorded as a change at all.
	 */
	public function testUpdateRecordsNothingWhenTheStoredValueIsUnchanged()
	{
		$probe = $this->probe(
			array('probe_id' => 1, 'probe_fields' => null, 'probe_menu' => 'left'),
			array('probe_fields' => '{"colour":"red"}')
		);

		$probe->probeSubmit();

		$this->assertSame(array(), $probe->backups);
	}

	/**
	 * A row read back from the table carries every real column, so nothing is left for
	 * backupToHistory()'s field filter to drop and the restore of a delete is whole.
	 */
	public function testSnapshotCarriesEveryStoredColumn()
	{
		$probe = $this->probe(array(), array());

		$this->assertEquals(
			array('probe_id' => 1, 'probe_fields' => '{"colour":"red"}', 'probe_menu' => 'left'),
			$probe->probeSnapshot(1)
		);
	}

	public function testSnapshotOfAMissingRowIsEmpty()
	{
		$probe = $this->probe(array(), array());

		$this->assertSame(array(), $probe->probeSnapshot(99));
	}

	/**
	 * The filter that drops a column whose field definition carries no 'data' attribute is
	 * what loses the Menu tab on a delete restore, so the snapshot must not be filtered.
	 */
	public function testTheStoredSnapshotIsNotFilteredByTheFieldDefinitions()
	{
		$probe = $this->probe(
			array('probe_id' => 1, 'probe_fields' => null, 'probe_menu' => 'left'),
			array('probe_fields' => '{"colour":"blue"}')
		);

		$probe->probeSubmit();

		$this->assertFalse($probe->backups[0]['posted']);
	}

	/**
	 * The tree model has no node for a row on any list page but the first, and the delete goes
	 * ahead regardless. Reading the row by table and id is what lets the archive go ahead too.
	 */
	public function testDeleteWithoutATreeNodeStillArchivesTheStoredRow()
	{
		$probe = new AdminUiHistoryDeleteProbeFixture('admin_ui_history_probe', 'probe_id');

		$probe->ListDeleteTrigger(array(1 => 'delete'));

		$this->assertSame(array(1), $probe->treeStub->deleted, 'the row is deleted either way');
		$this->assertCount(1, $probe->backups);
		$this->assertSame('delete', $probe->backups[0]['action']);
		$this->assertEquals(
			array('probe_id' => 1, 'probe_fields' => '{"colour":"red"}', 'probe_menu' => 'left'),
			$probe->backups[0]['data']
		);
	}

	public function testDeleteOfARowThatIsNotThereArchivesNothing()
	{
		$probe = new AdminUiHistoryDeleteProbeFixture('admin_ui_history_probe', 'probe_id');

		$probe->ListDeleteTrigger(array(99 => 'delete'));

		$this->assertSame(array(), $probe->backups);
	}

	/**
	 * The ordinary delete, with the node the fall-back above does without. What is archived is
	 * still the stored row: the node carries the observer's null, and a column the field
	 * definitions do not declare survives only because the filter is off.
	 */
	public function testDeleteArchivesTheStoredRowRatherThanTheTreeNodesData()
	{
		$node = new AdminUiHistoryNodeStub(array('probe_id' => 1, 'probe_fields' => null));
		$probe = new AdminUiHistoryDeleteProbeFixture('admin_ui_history_probe', 'probe_id', $node);

		$probe->ListDeleteTrigger(array(1 => 'delete'));

		$this->assertSame(array(1), $probe->treeStub->deleted);
		$this->assertCount(1, $probe->backups);
		$this->assertFalse($probe->backups[0]['posted']);
		$this->assertEquals(
			array('probe_id' => 1, 'probe_fields' => '{"colour":"red"}', 'probe_menu' => 'left'),
			$probe->backups[0]['data']
		);
	}

	public function testBatchDeleteArchivesTheStoredRow()
	{
		$node = new AdminUiHistoryNodeStub(array('probe_id' => 1, 'probe_fields' => null));
		$probe = new AdminUiHistoryDeleteProbeFixture('admin_ui_history_probe', 'probe_id', $node);
		$probe->treeStub->deleteReturns = false;

		$probe->probeBatchDelete(array(1));

		$this->assertCount(1, $probe->backups);
		$this->assertSame('delete', $probe->backups[0]['action']);
		$this->assertEquals(
			array('probe_id' => 1, 'probe_fields' => '{"colour":"red"}', 'probe_menu' => 'left'),
			$probe->backups[0]['data']
		);
	}

	/**
	 * The History area deletes its own rows, and those are not archived. The exclusion has to be
	 * tested before the snapshot, or a batch delete there reads every row it is about to drop.
	 */
	public function testTheHistoryTableIsNotSnapshotForItsOwnDeletes()
	{
		$node = new AdminUiHistoryNodeStub(array('history_id' => 1));
		$probe = new AdminUiHistoryDeleteProbeFixture('admin_history', 'history_id', $node);

		$probe->ListDeleteTrigger(array(1 => 'delete'));

		$this->assertSame(array(), $probe->backups);
		$this->assertSame(array(1), $probe->treeStub->deleted);
	}
}
