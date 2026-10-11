<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * An action that names no page runs the controller's default action in its place, so the
 * default action has to pass the checks the requested action passed, or nothing runs.
 */
class adminUiDefaultActionTest extends \Codeception\Test\Unit
{
	/** @var e_admin_dispatcher|null registered before the test */
	private $savedDispatcher;

	/** @var array */
	private $savedGet;

	protected function _before()
	{
		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiDefaultActionProbeFixture.php');

		$this->savedDispatcher = e107::getRegistry('admin/ui/dispatcher');
		$this->savedGet = $_GET;
	}

	protected function _after()
	{
		e107::setRegistry('admin/ui/dispatcher', $this->savedDispatcher);
		$_GET = $this->savedGet;
	}

	public function testTheDefaultActionRunsForAnAdministratorWhoHoldsItsRoute()
	{
		$this->assertSame('list', $this->landOn(array('main/list')));
	}

	public function testTheDefaultActionIsRefusedToAnAdministratorWhoLacksItsRoute()
	{
		$this->assertSame('e403', $this->landOn(array('main/nopage')));
	}

	public function testTheDefaultActionIsRefusedWhenNoDispatcherCanAnswerForItsRoute()
	{
		$this->assertSame('e403', $this->landOn(null));
	}

	public function testTheDefaultActionIsRefusedWhenTheControllerDisallowsIt()
	{
		$this->assertSame('e403', $this->landOn(array('main/list'), array('list')));
	}

	/**
	 * @param array|null $granted routes the registered dispatcher grants, or null to register none
	 * @param array $disallow actions the controller refuses
	 * @return string the action a request for a page the controller lacks is left on
	 */
	private function landOn($granted, $disallow = array())
	{
		e107::setRegistry('admin/ui/dispatcher', $granted === null ? null : new AdminUiDefaultActionDispatcherFixture($granted));

		$controller = new AdminUiDefaultActionProbeFixture(new e_admin_request('mode=main&action=nopage'), $disallow);

		return $controller->probe();
	}
}
