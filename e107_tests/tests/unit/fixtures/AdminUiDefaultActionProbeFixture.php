<?php

/**
 * A controller whose pages are the default action, List, and its ajax page, so any other action names no page of its own.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included;
 * see AdminUiSearchfieldProbeFixture for why the fixture lives in its own file.
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
