<?php

/**
 * A list over a scratch table whose init() changes its scope, as banlist.php's white-list mode does.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included;
 * see AdminUiBatchProbeFixture for why the fixture lives in its own file.
 */
class AdminUiScopeProbeFixture extends e_admin_ui
{
	protected $table = 'admin_ui_scope_probe';

	protected $pid = 'probe_id';

	protected $listScope = array('probe_type' => 'ban');

	protected $fields = array(
		'probe_id'   => array('title' => 'ID', 'type' => 'number', 'data' => 'int'),
		'probe_type' => array('title' => 'Type', 'type' => 'text', 'data' => 'str'),
	);

	public function __construct()
	{
		$this->setRequest(new e_admin_request('mode=main&action=edit'));
	}

	public function init()
	{
		$this->listScope = array('probe_type' => 'white');
	}
}

/**
 * The same list, building its own edit model rather than the one e_admin_ui builds.
 */
class AdminUiOwnModelScopeProbeFixture extends AdminUiScopeProbeFixture
{
	public function _setModel()
	{
		$this->_model = new e_admin_model();
		$this->_model->setModelTable($this->table);
		$this->_model->setFieldIdName($this->pid);

		return $this;
	}
}

/**
 * The same list clearing its scope in init(), as the custom page overview does.
 */
class AdminUiClearedScopeProbeFixture extends AdminUiScopeProbeFixture
{
	public function init()
	{
		$this->listScope = '';
	}
}

/**
 * The same list declaring no scope, holding its edit model to its own rows by a condition of its own, as a third-party list may.
 */
class AdminUiOwnConditionProbeFixture extends AdminUiScopeProbeFixture
{
	protected $listScope = '';

	public function init()
	{
		$this->getModel()->setParam('db_where', "AND probe_type = 'ban'");
	}
}
