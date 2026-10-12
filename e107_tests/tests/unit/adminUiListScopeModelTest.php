<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The edit model loads a row only under the list scope that holds when the model is asked for. An
 * e_admin addon's config() runs in the e_admin_ui constructor and may build the model before init()
 * sets or clears the scope, and a controller may build its own model; neither may load a row the list
 * leaves out, nor miss one it shows. A controller that declares no scope keeps the condition it set on its model.
 */
class adminUiListScopeModelTest extends \Test\Unit
{
	/** @var string prefixed scratch table */
	private $table;

	protected function _before()
	{
		$this->table = MPREFIX . 'admin_ui_scope_probe';

		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiScopeProbeFixture.php');

		$sql = e107::getDb();
		$sql->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->table . '`');
		$sql->gen('CREATE TEMPORARY TABLE `' . $this->table . '` (probe_id INT NOT NULL, probe_type VARCHAR(20) NOT NULL)');
		$sql->gen('INSERT INTO `' . $this->table . "` (probe_id, probe_type) VALUES (1, 'ban'), (2, 'white')");
	}

	protected function _after()
	{
		e107::getDb()->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->table . '`');
	}

	public function testAModelBuiltBeforeInitLoadsUnderTheScopeInitSets()
	{
		$probe = new AdminUiScopeProbeFixture();
		$probe->getModel();
		$probe->init();

		$this->assertSame(2, (int) $probe->getModel()->load(2, true)->getId(), 'A row of the scope init() set did not load.');
		$this->assertSame(0, (int) $probe->getModel()->load(1, true)->getId(), 'A row of the scope init() replaced still loaded.');
	}

	public function testAModelBuiltBeforeInitLoadsEveryRowOnceInitClearsTheScope()
	{
		$probe = new AdminUiClearedScopeProbeFixture();
		$probe->getModel();
		$probe->init();

		$this->assertSame(2, (int) $probe->getModel()->load(2, true)->getId(), 'A row outside the scope init() cleared did not load.');
	}

	public function testAModelTheControllerBuiltLoadsUnderTheScope()
	{
		$probe = new AdminUiOwnModelScopeProbeFixture();

		$this->assertSame(0, (int) $probe->getModel()->load(2, true)->getId(), 'A row outside the scope loaded into the model the controller built.');
		$this->assertSame(1, (int) $probe->getModel()->load(1, true)->getId(), 'A row of the scope did not load into the model the controller built.');
	}

	public function testAControllerWithNoScopeKeepsTheConditionItSetOnItsModel()
	{
		$probe = new AdminUiOwnConditionProbeFixture();
		$probe->init();

		$this->assertSame(0, (int) $probe->getModel()->load(2, true)->getId(), 'A row outside the condition the controller set on its model loaded.');
		$this->assertSame(1, (int) $probe->getModel()->load(1, true)->getId(), 'A row of the condition the controller set on its model did not load.');
	}
}
