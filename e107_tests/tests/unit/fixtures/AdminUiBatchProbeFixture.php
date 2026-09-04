<?php

/**
 * Probe subclass exposing e_admin_ui's protected batch-trigger dispatch, paired
 * with a tree model that records batchUpdate() calls instead of running them.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included;
 * see AdminUiSearchfieldProbeFixture for why the fixture lives in its own file.
 */
class AdminUiBatchProbeFixture extends e_admin_ui
{
	/** @var AdminUiBatchSpyTree */
	public $spyTree;

	public function __construct()
	{
		$this->spyTree = new AdminUiBatchSpyTree();
	}

	public function getTreeModel()
	{
		return $this->spyTree;
	}

	/** @var array one entry per batch handler the dispatcher reached */
	public $dispatched = array();

	public function probe($batchTrigger)
	{
		$this->_handleListBatch($batchTrigger);

		return $this->spyTree->batchUpdateCalls;
	}

	public function probeDispatch($batchTrigger)
	{
		$this->_handleListBatch($batchTrigger);

		return $this->dispatched;
	}

	protected function handleListSefgenBatch($selected, $field, $value)
	{
		$this->dispatched[] = array('handler' => 'sefgen', 'field' => $field, 'value' => $value);
	}

	protected function handleListBoolBatch($selected, $field, $value)
	{
		$this->dispatched[] = array('handler' => 'bool', 'field' => $field, 'value' => $value);
	}

	protected function handleListBoolreverseBatch($selected, $field)
	{
		$this->dispatched[] = array('handler' => 'boolreverse', 'field' => $field, 'value' => null);
	}

	public function handleCommaBatch($selected, $field, $value, $type)
	{
		$this->dispatched[] = array('handler' => $type, 'field' => $field, 'value' => $value);
	}
}

/**
 * A screen that overrides the generic batch writer the way
 * {@see forum_admin_ui::handleListBatch()} does, to prove the dispatcher's guard is crossed
 * before any such override is reached.
 */
class AdminUiBatchOverrideProbeFixture extends AdminUiBatchProbeFixture
{
	protected function handleListBatch($selected, $field, $value)
	{
		$this->dispatched[] = array('handler' => 'listBatch', 'field' => $field, 'value' => $value);

		return parent::handleListBatch($selected, $field, $value);
	}
}

/**
 * Stand-in for {@see e_front_tree_model} recording what the batch dispatcher
 * asks it to update. Returns 0 so {@see e_admin_ui::handleListBatch()} skips
 * the success message and the list reload.
 */
class AdminUiBatchSpyTree
{
	/** @var array one entry per batchUpdate() call */
	public $batchUpdateCalls = array();

	public function batchUpdate($field, $value, $ids, $syncvalue = null, $sanitize = true, $session_messages = false)
	{
		$this->batchUpdateCalls[] = array('field' => $field, 'value' => $value, 'ids' => $ids);

		return 0;
	}

	public function setMessages($session_messages = false)
	{
		return $this;
	}

	public function addMessageWarning($message, $session = false)
	{
		return $this;
	}
}

/**
 * A screen that declares its own handler for one field, which the dispatcher looks up before
 * it asks whether the field offers a batch. That lookup is the documented extension point and
 * this fixture pins it.
 */
class AdminUiBatchCustomFieldProbeFixture extends AdminUiBatchProbeFixture
{
	protected function handleListGenChardataBatch($selected, $value)
	{
		$this->dispatched[] = array('handler' => 'genChardata', 'field' => 'gen_chardata', 'value' => $value);
	}
}

/**
 * Stand-in for {@see user_class} with one class in its tree, so the userclass batch cases
 * reach a handler on unfixed code instead of stopping at the manager check.
 */
class AdminUiBatchUserClassStub
{
	/** @var array */
	public $class_tree = array(253 => array('userclass_id' => 253, 'userclass_name' => 'Members',
		'userclass_editclass' => 254));

	public function uc_required_class_list($list = '')
	{
		return array(253 => 'Members');
	}
}

/**
 * Stand-in for {@see e_user} that passes every userclass-manager check.
 */
class AdminUiBatchUserStub
{
	public function checkClass($class, $allowMain = true)
	{
		return true;
	}
}
