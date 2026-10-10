<?php

/**
 * Probe subclasses that run e_admin_form_ui::getList() without an admin request behind it,
 * answering route questions from a dispatcher that refuses one named route.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included;
 * see AdminUiSearchfieldProbeFixture for why the fixture lives in its own file.
 */
class AdminUiInlineEditProbeFixture extends e_admin_ui
{
	protected $pluginName = 'core';
	protected $pluginTitle = 'Inline Edit Probe';
	protected $table = 'inline_probe';
	protected $pid = 'probe_id';
	protected $eventName = 'admin_inline_probe';

	/** @var AdminUiInlineEditDispatcherStub */
	private $dispatcher;

	/** @param string $deniedRoute the one route the dispatcher refuses, '' to refuse none */
	public function __construct($deniedRoute = '')
	{
		$this->dispatcher = new AdminUiInlineEditDispatcherStub($deniedRoute);

		$this->setRequest(new e_admin_request('mode=main&action=list'));
		$this->setFields(array(
			'probe_id'    => array('title' => 'ID', 'type' => 'int', 'data' => 'int'),
			'probe_title' => array('title' => 'Title', 'type' => 'text', 'data' => 'str', 'inline' => true),
			'probe_open'  => array('title' => 'Open', 'type' => 'boolean', 'data' => 'int', 'inline' => true),
			'options'     => array('title' => 'Options', 'type' => null, 'forced' => '1'),
		));
	}

	public function getDispatcher()
	{
		return $this->dispatcher;
	}

	public function getTreeModel()
	{
		return new e_front_tree_model();
	}
}

/**
 * A screen whose list is a tree, where {@see e_admin_form_ui::treePrefix()} decides
 * whether it draws the child icon itself or leaves it to the inline pass.
 */
class AdminUiInlineEditTreeProbeFixture extends AdminUiInlineEditProbeFixture
{
	protected $sortParent = 'probe_parent';
	protected $treePrefix = 'probe_title';

	public function getListModel()
	{
		return new AdminUiInlineEditListModelStub(array('probe_parent' => 4, '_depth' => 2));
	}
}

/**
 * A tree screen that declares inline editing on its prefix column and then refuses to edit
 * it, the one declaration for which neither pass used to draw the child icon.
 */
class AdminUiInlineEditNoeditTreeProbeFixture extends AdminUiInlineEditTreeProbeFixture
{
	public function __construct($deniedRoute = '')
	{
		parent::__construct($deniedRoute);

		$this->setFieldAttr('probe_title', 'noedit', true);
	}
}

/**
 * Keeps the render options {@see e_admin_form_ui::getList()} built instead of rendering them.
 */
class AdminUiInlineEditFormProbe extends e_admin_form_ui
{
	/** @var array the form options of the most recent getList() call */
	public $captured = array();

	public function renderListForm($form_options, $tree_models, $nocontainer = false)
	{
		$this->captured = $form_options;

		return '';
	}

	public function renderBatch($options, $customBatchOptions = array())
	{
		return '';
	}

	/** @return array the field declarations getList() handed to the list renderer */
	public function capturedFields()
	{
		$options = reset($this->captured);

		return $options['fields'];
	}
}

/**
 * Stand-in for {@see e_admin_dispatcher} refusing exactly one route, the way a screen's
 * own permission map denies main/edit while leaving main/list open.
 */
class AdminUiInlineEditDispatcherStub
{
	/** @var string the one route this dispatcher refuses */
	private $denied;

	public function __construct($denied = '')
	{
		$this->denied = $denied;
	}

	public function hasRouteAccess($route)
	{
		return $route !== $this->denied;
	}
}

/**
 * Stand-in for the row {@see e_admin_controller_ui::getListModel()} exposes while a list renders.
 */
class AdminUiInlineEditListModelStub
{
	/** @var array */
	private $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function get($key, $default = null)
	{
		return isset($this->data[$key]) ? $this->data[$key] : $default;
	}
}
