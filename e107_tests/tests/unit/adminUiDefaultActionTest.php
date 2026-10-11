<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * An action that names no page of its own runs the controller's default action in its place, so
 * the default action has to pass the checks the requested action passed, or nothing runs.
 */
class adminUiDefaultActionTest extends \Test\Unit
{
	/** @var e_admin_dispatcher|null registered before the test */
	private $savedDispatcher;

	/** @var array */
	private $savedGet;

	/** @var array */
	private $savedPost;

	protected function _before()
	{
		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiDefaultActionProbeFixture.php');

		$this->savedDispatcher = e107::getRegistry('admin/ui/dispatcher');
		$this->savedGet = $_GET;
		$this->savedPost = $_POST;
	}

	protected function _after()
	{
		e107::setRegistry('admin/ui/dispatcher', $this->savedDispatcher);
		$_GET = $this->savedGet;
		$_POST = $this->savedPost;
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

	public function testTheListAjaxPageIsRefusedOutsideAjaxToAnAdministratorWhoLacksTheListRoute()
	{
		foreach(array('listajax', 'list_ajax', 'LISTAJAX') as $action)
		{
			$this->assertSame('e403', $this->landOn(array(), array(), $action), $action);
		}
	}

	public function testTheListAjaxPageGivesWayToTheListOutsideAjaxForAnAdministratorWhoHoldsTheListRoute()
	{
		$this->assertSame('list', $this->landOn(array('main/list'), array(), 'listajax'));
	}

	public function testTheListAjaxPageIsRefusedOutsideAjaxWhenTheControllerReadsAnEmptyMethodTypeAsAPage()
	{
		$this->assertSame('e403', $this->landOn(array(), array(), 'listajax', 'AdminUiDefaultActionTypedProbeFixture'));
	}

	public function testTheListAjaxPageIsRefusedOutsideAjaxWhenTheControllerIgnoresTheForcedAjaxFlag()
	{
		$this->assertSame('e403', $this->landOn(array(), array(), 'listajax', 'AdminUiDefaultActionAutoAjaxProbeFixture'));
	}

	public function testEveryActionIsRefusedToAHolderOfTheDefaultRouteWhenTheControllerIgnoresTheForcedAjaxFlag()
	{
		foreach(array('list', 'listajax', 'e403') as $action)
		{
			$this->assertSame('e403', $this->landOn(array('main/list'), array(), $action, 'AdminUiDefaultActionAutoAjaxProbeFixture'), $action);
		}
	}

	public function testAnActionNamingOneOfTheControllersOwnMethodsIsRefusedToAnAdministratorWhoLacksTheDefaultRoute()
	{
		foreach(array('dispatch', 'DISPATCH', 'dis_patch', 'getper', 'GET_PER') as $action)
		{
			$this->assertSame('e403', $this->landOn(array(), array(), $action), $action);
		}
	}

	public function testAnActionNamingTheControllersOwnDispatchGivesWayToTheDefaultActionForAnAdministratorWhoHoldsItsRoute()
	{
		$this->assertSame('list', $this->landOn(array('main/list'), array(), 'dispatch'));
	}

	public function testADefaultActionNamingOneOfTheControllersOwnMethodsIsNotFound()
	{
		$controller = $this->controllerFor(array('main/getper'), array(), 'nopage', 'AdminUiDefaultActionProbeFixture');
		$controller->setDefaultAction('getper');

		$this->assertSame('e404', $controller->probe());
	}

	public function testTheObserverDispatchRunsOnceForARequestNamingItself()
	{
		$controller = $this->controllerFor(array('main/list'), array(), 'dispatch', 'AdminUiOwnMethodProbeFixture');

		$controller->dispatchObserver();

		$this->assertSame(array('dispatchObserver'), $controller->ran);
	}

	public function testEachDispatchMethodRunsOnceWhenItIsGivenItsOwnNameAsTheAction()
	{
		foreach(array('dispatchObserver', 'dispatchHeader', 'dispatchPage') as $method)
		{
			$controller = $this->controllerFor(array('main/list'), array(), 'list', 'AdminUiOwnMethodProbeFixture');

			$controller->$method('dispatch');

			$this->assertSame(array($method), $controller->ran, $method);
		}
	}

	public function testTheHeaderOfAnActionIsNeverTheControllersOwnAddHeader()
	{
		$controller = $this->controllerFor(array('main/list'), array(), 'add', 'AdminUiOwnMethodProbeFixture');

		$controller->dispatchHeader();

		$this->assertSame(array('dispatchHeader'), $controller->ran);
	}

	public function testATriggerOfAnActionIsNeverTheControllersOwnSetDefaultTrigger()
	{
		$controller = $this->controllerFor(array('main/list'), array(), 'set', 'AdminUiOwnMethodProbeFixture');
		$controller->getRequest()->setPosted('etrigger_default', 'list');

		$controller->dispatchObserver();

		$this->assertSame(array('dispatchObserver'), $controller->ran);
	}

	/**
	 * @param array|null $granted routes the registered dispatcher grants, or null to register none
	 * @param array $disallow actions the controller refuses
	 * @param string $action the action requested
	 * @param string $controllerClass {@see AdminUiDefaultActionProbeFixture} or a subclass
	 * @return string the action the request is left on
	 */
	private function landOn($granted, $disallow = array(), $action = 'nopage', $controllerClass = 'AdminUiDefaultActionProbeFixture')
	{
		return $this->controllerFor($granted, $disallow, $action, $controllerClass)->probe();
	}

	/**
	 * @param array|null $granted routes the registered dispatcher grants, or null to register none
	 * @param array $disallow actions the controller refuses
	 * @param string $action the action requested
	 * @param string $controllerClass {@see AdminUiDefaultActionProbeFixture} or a subclass
	 * @return AdminUiDefaultActionProbeFixture
	 */
	private function controllerFor($granted, $disallow, $action, $controllerClass)
	{
		e107::setRegistry('admin/ui/dispatcher', $granted === null ? null : new AdminUiDefaultActionDispatcherFixture($granted));

		return new $controllerClass(new e_admin_request('mode=main&action='.$action), $disallow);
	}
}
