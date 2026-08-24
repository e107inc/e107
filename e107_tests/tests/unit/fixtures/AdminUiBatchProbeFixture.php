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

	public function probe($batchTrigger)
	{
		$this->_handleListBatch($batchTrigger);

		return $this->spyTree->batchUpdateCalls;
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
}
