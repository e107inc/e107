<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A posted etrigger_batch must not be dispatched as a column assignment unless
 * its field segment is one the batch dropdown itself offers, which is what
 * e_admin_form_ui::renderBatchFilter() means by a field carrying 'batch'.
 * e_admin_controller_ui::_handleListBatch() stops otherwise, and
 * e_front_tree_model::batchUpdate() refuses a name outside the identifier
 * grammar rather than letting the query builder throw.
 */
class adminUiBatchTriggerTest extends \Test\Unit
{
	/** @var string prefixed scratch table */
	private $table;

	protected function _before()
	{
		$this->table = MPREFIX . 'admin_batch_guard';
	}

	protected function _after()
	{
		e107::getDb()->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->table . '`');
	}

	private function makeProbe($posted)
	{
		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiBatchProbeFixture.php');

		$request = new e_admin_request('mode=failed&action=list');
		$request->setPosted($posted);

		$probe = new AdminUiBatchProbeFixture();
		$probe->setRequest($request);
		$probe->setFields(array(
			'checkboxes'    => array('title' => '', 'type' => null, 'forced' => '1', 'toggle' => 'e-multiselect'),
			'gen_ip'        => array('title' => 'IP', 'type' => 'ip', 'data' => 'str', 'batch' => true),
			'gen_chardata'  => array('title' => 'Description', 'type' => 'method', 'data' => 'str'),
			'options'       => array('title' => 'Options', 'type' => null, 'forced' => '1'),
		));

		return $probe;
	}

	public function testUndeclaredBatchTriggerNeverReachesBatchUpdate()
	{
		// banlist.php's failed-login list declares a 'delete-all' batch option
		// that names no column; it must not arrive as one.
		$calls = $this->makeProbe(array('e-multiselect' => array(1, 2)))->probe('delete-all');

		$this->assertSame(array(), $calls,
			'A batch trigger that is not a declared field must not reach batchUpdate().');
	}

	public function testDeclaredButUnbatchedFieldNeverReachesBatchUpdate()
	{
		// A column the dropdown never offers, because it carries no 'batch'.
		$calls = $this->makeProbe(array('e-multiselect' => array(1, 2)))->probe('gen_chardata__x');

		$this->assertSame(array(), $calls,
			'Only a field the batch dropdown offers may be assigned through a batch trigger.');
	}

	public function testDeclaredBatchTriggerStillReachesBatchUpdate()
	{
		$calls = $this->makeProbe(array('e-multiselect' => array(1, 2)))->probe('gen_ip__127.0.0.1');

		$this->assertCount(1, $calls, 'A declared field must still be batched.');
		$this->assertSame('gen_ip', $calls[0]['field']);
		$this->assertSame('127.0.0.1', $calls[0]['value']);
		$this->assertSame(array('1', '2'), $calls[0]['ids']);
	}

	private function makeTree()
	{
		$sql = e107::getDb();
		$sql->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->table . '`');
		$sql->gen('CREATE TEMPORARY TABLE `' . $this->table
			. '` (id INT NOT NULL, a VARCHAR(255) NULL)');
		$sql->gen("INSERT INTO `" . $this->table . "` (id, a) VALUES (1, 'orig_a')");

		$tree = new e_front_tree_model();
		$tree->setModelTable('admin_batch_guard');
		$tree->setFieldIdName('id');

		return $tree;
	}

	private function readColumnA()
	{
		$sql = e107::getDb();
		$sql->gen('SELECT a FROM `' . $this->table . '` WHERE id = 1');
		$row = $sql->fetch();

		return $row['a'];
	}

	public function testBatchUpdateRefusesAFieldNameThatIsNotAnIdentifier()
	{
		$tree = $this->makeTree();

		$this->assertFalse($tree->batchUpdate('delete-all', 'x', array(1), null, false),
			'batchUpdate() documents "false on error"; an unquotable field name must not throw.');
		$this->assertSame('orig_a', $this->readColumnA(), 'No query may run for a refused field name.');
	}

	public function testBatchUpdateStillUpdatesAValidField()
	{
		$tree = $this->makeTree();

		$this->assertNotFalse($tree->batchUpdate('a', 'new_a', array(1), null, false));
		$this->assertSame('new_a', $this->readColumnA());
	}
}
