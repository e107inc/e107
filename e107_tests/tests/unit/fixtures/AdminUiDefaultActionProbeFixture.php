<?php

/**
 * A controller whose pages are the default action, List, and its ajax page, so any other action names no page of its own.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included;
 * see AdminUiBatchProbeFixture for why the fixture lives in its own file.
 */
class AdminUiDefaultActionProbeFixture extends e_admin_controller_ui
{
	/**
	 * @param e_admin_request $request
	 * @param array $disallow actions the controller refuses
	 */
	public function __construct($request, $disallow = array())
	{
		$this->disallow = $disallow;

		parent::__construct($request, new e_admin_response());

		$this->setDefaultAction('list');
	}

	public function ListPage()
	{
		return '';
	}

	public function ListAjaxPage()
	{
		return '';
	}

	/**
	 * @return string the action the request is left on
	 */
	public function probe()
	{
		$this->_preDispatch();

		return $this->getRequest()->getAction();
	}
}

/**
 * A controller whose method names read an empty type as a page, as the documented types allow.
 */
class AdminUiDefaultActionTypedProbeFixture extends AdminUiDefaultActionProbeFixture
{
	public function toMethodName($action_name, $type = 'page', $ajax = null)
	{
		return parent::toMethodName($action_name, $type ? $type : 'page', $ajax);
	}
}

/**
 * A controller whose method names take the ajax infix from the request alone, whatever flag {@see e_admin_controller::toMethodName()} is given.
 */
class AdminUiDefaultActionAutoAjaxProbeFixture extends AdminUiDefaultActionProbeFixture
{
	public function toMethodName($action_name, $type = 'page', $ajax = null)
	{
		return parent::toMethodName($action_name, $type);
	}
}

/**
 * A controller with pages for the actions add and set that records each run of {@see e_admin_controller::addHeader()}, {@see e_admin_controller::setDefaultTrigger()} and the three dispatch methods, and enters each dispatch method once.
 */
class AdminUiOwnMethodProbeFixture extends AdminUiDefaultActionProbeFixture
{
	/** @var string[] */
	public $ran = array();

	/**
	 * @param e_admin_request $request
	 * @param array $disallow actions the controller refuses
	 */
	public function __construct($request, $disallow = array())
	{
		parent::__construct($request, $disallow);

		$this->setParam('enable_triggers', true);
	}

	public function AddPage()
	{
		return '';
	}

	public function SetPage()
	{
		return '';
	}

	public function addHeader($content = null)
	{
		$this->ran[] = __FUNCTION__;

		return parent::addHeader($content);
	}

	public function setDefaultTrigger($triggers)
	{
		$this->ran[] = __FUNCTION__;

		return parent::setDefaultTrigger($triggers);
	}

	public function dispatchObserver($action = null)
	{
		return $this->entersOnce(__FUNCTION__) ? parent::dispatchObserver($action) : $this;
	}

	public function dispatchHeader($action = null)
	{
		return $this->entersOnce(__FUNCTION__) ? parent::dispatchHeader($action) : $this;
	}

	public function dispatchPage($action = null)
	{
		return $this->entersOnce(__FUNCTION__) ? parent::dispatchPage($action) : '';
	}

	protected function checkRequestToken()
	{
		return true;
	}

	/**
	 * @param string $method
	 * @return bool whether this is the method's first run
	 */
	private function entersOnce($method)
	{
		$this->ran[] = $method;

		return count(array_keys($this->ran, $method, true)) === 1;
	}
}

/**
 * A dispatcher that grants exactly the routes it is given.
 */
class AdminUiDefaultActionDispatcherFixture extends e_admin_dispatcher
{
	/** @var array */
	private $granted;

	public function __construct(array $granted)
	{
		$this->granted = $granted;
	}

	public function hasRouteAccess($route)
	{
		return in_array($route, $this->granted, true);
	}
}
