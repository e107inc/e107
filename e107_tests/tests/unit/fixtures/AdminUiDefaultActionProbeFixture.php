<?php

/**
 * A controller whose only page is the default action, List, so any other action names no page.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included;
 * see AdminUiBatchProbeFixture for why the fixture lives in its own file.
 */
class AdminUiDefaultActionProbeFixture extends e_admin_controller
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
