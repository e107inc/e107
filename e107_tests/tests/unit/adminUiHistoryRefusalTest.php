<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * An edit archives what it is about to overwrite before it overwrites it, and a
 * failed archive write leaves the record alone rather than reporting the miss
 * with the new values already stored and the old ones nowhere.
 *
 * @group core
 */
class adminUiHistoryRefusalTest extends \Test\Unit
{
	/** @var string prefixed scratch table */
	private $table;

	/** @var string prefixed archive table, a temporary one shadowing the real one */
	private $history;

	protected function _before()
	{
		$this->table = MPREFIX . 'admin_ui_history_probe';
		$this->history = MPREFIX . 'admin_history';

		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiHistoryProbeFixture.php');
		e107::includeLan(e_LANGUAGEDIR . 'English/admin/lan_admin.php');
		e107::getMessage()->reset(false, false, true);

		$sql = e107::getDb();
		$sql->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->table . '`');
		$sql->gen('CREATE TEMPORARY TABLE `' . $this->table
			. '` (probe_id INT NOT NULL, probe_fields VARCHAR(255) NULL, probe_menu VARCHAR(255) NULL)');
		$sql->gen('INSERT INTO `' . $this->table
			. '` (probe_id, probe_fields, probe_menu) VALUES (1, \'{"colour":"red"}\', \'left\')');

		$sql->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->history . '`');
		$sql->gen('CREATE TEMPORARY TABLE `' . $this->history . '` ('
			. 'history_id INT UNSIGNED NOT NULL AUTO_INCREMENT,'
			. 'history_table VARCHAR(64) NOT NULL DEFAULT \'\','
			. 'history_pid VARCHAR(64) NOT NULL DEFAULT \'\','
			. 'history_record_id INT UNSIGNED NOT NULL DEFAULT 0,'
			. 'history_action ENUM(\'delete\',\'restore\',\'update\') NOT NULL,'
			. 'history_data LONGTEXT,'
			. 'history_user_id INT UNSIGNED NOT NULL DEFAULT 0,'
			. 'history_datestamp INT UNSIGNED NOT NULL DEFAULT 0,'
			. 'history_restored INT UNSIGNED NOT NULL DEFAULT 0,'
			. 'PRIMARY KEY (history_id))');
	}

	protected function _after()
	{
		$sql = e107::getDb();
		$sql->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->table . '`');
		$sql->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->history . '`');
		e107::getMessage()->reset(false, false, true);
	}

	/**
	 * Shadows the archive table with one the archive INSERT cannot fit, so the write fails as a
	 * real failing INSERT does rather than being stubbed out.
	 */
	private function shadowArchiveTableTheWriteCannotFit()
	{
		$sql = e107::getDb();
		$sql->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->history . '`');
		$sql->gen('CREATE TEMPORARY TABLE `' . $this->history . '` (history_id INT UNSIGNED NOT NULL)');
	}

	/**
	 * @param array $written what the merge puts in the model, so what the save writes
	 * @param array $posted  what the request carried, which the change set is not read from
	 * @return AdminUiHistoryProbeFixture
	 */
	private function probe(array $written, array $posted = array())
	{
		$model = new AdminUiHistorySpyModel('admin_ui_history_probe', 'probe_id', 1,
			array('probe_id' => 1, 'probe_fields' => '{"colour":"red"}', 'probe_menu' => 'left'), $written);

		$probe = new AdminUiHistoryProbeFixture('admin_ui_history_probe', 'probe_id', $model);
		$probe->posted = array_merge($probe->posted, $posted);

		return $probe;
	}

	/**
	 * @return string what the probe row holds now
	 */
	private function storedFields()
	{
		$row = e107::getDb()->createQueryBuilder()
			->select('probe_fields')->from('admin_ui_history_probe')
			->where('probe_id', 1)
			->fetchRow();

		return $row['probe_fields'];
	}

	public function testAnEditWhoseArchiveWriteFailsLeavesTheRecordAsItWas()
	{
		$probe = $this->probe(array('probe_fields' => '{"colour":"blue"}'));
		$probe->backupAnswer = false;

		$probe->probeSubmit();

		self::assertCount(1, $probe->backups, 'the archive is attempted before the save, not after it');
		self::assertSame('{"colour":"red"}', $this->storedFields(),
			'the values with no copy anywhere must still be the values in the table');
	}

	public function testAnEditWhoseArchiveWriteSucceedsGoesAhead()
	{
		$probe = $this->probe(array('probe_fields' => '{"colour":"blue"}'));

		$probe->probeSubmit();

		self::assertCount(1, $probe->backups);
		self::assertSame('{"colour":"blue"}', $this->storedFields());
	}

	public function testAnEditThatChangesNothingIsNotRefused()
	{
		$probe = $this->probe(array('probe_fields' => '{"colour":"red"}'));
		$probe->backupAnswer = false;

		$probe->probeSubmit();

		self::assertSame(array(), $probe->backups, 'there is nothing to archive, so there is nothing to refuse');
		self::assertSame('{"colour":"red"}', $this->storedFields());
	}

	/**
	 * The UPDATE carries every field the model holds, not the fields the form posted, so a column
	 * an observer changed on its own is overwritten and has to be archived.
	 */
	public function testAColumnTheSaveWritesThatTheRequestNeverPostedIsArchived()
	{
		$probe = $this->probe(array('probe_menu' => 'right'));

		$probe->probeSubmit();

		self::assertCount(1, $probe->backups);
		self::assertSame(array('probe_menu' => 'left'), $probe->backups[0]['data']);
	}

	/**
	 * A custom page posts its fields as an array and stores them as a JSON string. The comparison
	 * is against the value the save will write, so the two forms of the same value are not a change.
	 */
	public function testAPostedArrayWhoseStoredFormIsUnchangedIsNeitherArchivedNorRefused()
	{
		$probe = $this->probe(
			array('probe_fields' => '{"colour":"red"}'),
			array('probe_fields' => array('colour' => 'red'))
		);
		$probe->backupAnswer = false;

		$probe->probeSubmit();

		self::assertSame(array(), $probe->backups);
		self::assertSame('{"colour":"red"}', $this->storedFields());
	}

	/**
	 * A validation failure leaves the model unmerged and nothing reaches the table, so the audit
	 * trail must not gain an entry for a change that did not happen.
	 */
	public function testASaveTheModelRefusesArchivesNothing()
	{
		$probe = $this->probe(
			array('probe_fields' => '{"colour":"blue"}'),
			array('probe_fields' => '{"colour":"blue"}')
		);
		$probe->spyModel->refuseMerge = true;

		$probe->probeSubmit();

		self::assertSame(array(), $probe->backups);
		self::assertSame('{"colour":"red"}', $this->storedFields());
	}

	/**
	 * A row whose stored bytes are not valid UTF-8 cannot be encoded as it stands, and refusing
	 * every change to it would lock the administrator out of the one record they most need to
	 * correct. Substituting the bad bytes keeps a copy of everything else in the row.
	 */
	public function testAnArchiveOfBytesThatAreNotUtf8IsWrittenWithSubstitutes()
	{
		$probe = new AdminUiHistoryArchiveProbeFixture('admin_ui_history_probe', 'probe_id');

		self::assertTrue($probe->probeBackup(1, array('probe_fields' => "caf\xE9")),
			'the bad bytes are substituted rather than refused, so the write goes ahead');

		$row = e107::getDb()->createQueryBuilder()
			->select('history_data')->from('admin_history')
			->where('history_record_id', 1)
			->fetchRow();

		self::assertStringContainsString("\\ufffd", $row['history_data'],
			'the byte that is not UTF-8 is archived as U+FFFD, which json_encode() escapes');

		self::assertNotEmpty(e107::getMessage()->get(E_MESSAGE_WARNING, 'default', true),
			'the copy is not byte-exact, so the administrator is told so');
	}

	public function testTheArchiveTableIsNotArchivedForItsOwnChanges()
	{
		$probe = new AdminUiHistoryArchiveProbeFixture('admin_history', 'history_id');

		self::assertTrue($probe->probeArchive(1, array('history_data' => '{}')));

		$rows = e107::getDb()->createQueryBuilder()
			->select('history_id')->from('admin_history')
			->fetchAll();

		self::assertSame(array(), $rows);
	}

	public function testAnArchiveWriteThatFailsRefusesTheChange()
	{
		$this->shadowArchiveTableTheWriteCannotFit();

		$probe = new AdminUiHistoryArchiveProbeFixture('admin_ui_history_probe', 'probe_id');

		self::assertFalse($probe->probeArchive(1, array('probe_fields' => '{"colour":"red"}')));
	}

	/**
	 * The restore paths redirect, so a refusal the administrator cannot read is a record left
	 * alone for no stated reason. The write's own account of the failure travels with it.
	 */
	public function testARefusedRestoreCarriesItsReasonAcrossTheRedirect()
	{
		$this->shadowArchiveTableTheWriteCannotFit();

		$probe = new AdminUiHistoryArchiveProbeFixture('admin_ui_history_probe', 'probe_id');
		$message = e107::getMessage();

		self::assertFalse($probe->probeArchive(1, array('probe_fields' => '{"colour":"red"}'), 'restore', true));

		self::assertEmpty($message->get(E_MESSAGE_ERROR, 'default', true),
			'nothing is left on the stack the redirect throws away');

		$carried = (array) $message->getSession(E_MESSAGE_ERROR, 'default', true);

		self::assertNotEmpty($carried, 'the refusal survives the redirect');
		self::assertGreaterThan(1, count($carried), 'and so does what the write said about why');
	}

	/**
	 * Restoring redirects too, so the notice that the copy is not byte-exact has to survive it or
	 * it is shown to nobody on the one path that writes the substitutes over the live row.
	 */
	public function testTheSubstitutionNoticeSurvivesARestoreRedirect()
	{
		$probe = new AdminUiHistoryArchiveProbeFixture('admin_ui_history_probe', 'probe_id');
		$message = e107::getMessage();

		self::assertTrue($probe->probeArchive(1, array('probe_fields' => "caf\xE9"), 'restore', true));

		self::assertNotEmpty($message->getSession(E_MESSAGE_WARNING, 'default', true),
			'the administrator still reads it on the page the redirect lands on');
	}
}
