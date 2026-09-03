<?php

/**
 * Probe subclass recording what the submit path hands to
 * {@see e_admin_controller_ui::backupToHistory()}, and exposing the snapshot read on its own.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included;
 * see AdminUiSearchfieldProbeFixture for why the fixture lives in its own file.
 */
class AdminUiHistoryProbeFixture extends e_admin_controller_ui
{
	/** @var array one entry per backupToHistory() call */
	public $backups = array();

	/** @var AdminUiHistorySpyModel */
	public $spyModel;

	/** @var array stands in for the request's posted data */
	public $posted = array('etrigger_submit' => 'update');

	public function __construct($table, $pid, $spyModel)
	{
		$this->table = $table;
		$this->pid = $pid;
		$this->spyModel = $spyModel;
	}

	public function getModel()
	{
		return $this->spyModel;
	}

	public function getPosted($key = null, $default = null)
	{
		if($key === null)
		{
			return $this->posted;
		}

		return isset($this->posted[$key]) ? $this->posted[$key] : $default;
	}

	public function probeSubmit()
	{
		return $this->_manageSubmit();
	}

	public function probeSnapshot($id)
	{
		return $this->historySnapshot($this->table, $this->pid, $id);
	}

	protected function backupToHistory($table, $pid, $id, $action, $data, $posted = true)
	{
		$this->backups[] = array(
			'table'  => $table,
			'pid'    => $pid,
			'id'     => $id,
			'action' => $action,
			'data'   => $data,
			'posted' => $posted,
		);

		return true;
	}
}

/**
 * A model holding what an observer left in it, whose save() writes the posted values to the table.
 */
class AdminUiHistorySpyModel
{
	private $pid;
	private $id;
	private $inModel;
	private $written;
	private $table;
	private $saved = false;

	public function __construct($table, $pid, $id, array $inModel, array $written)
	{
		$this->table = $table;
		$this->pid = $pid;
		$this->id = $id;
		$this->inModel = $inModel;
		$this->written = $written;
	}

	public function getData()
	{
		return $this->saved ? array_merge($this->inModel, $this->written) : $this->inModel;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getFieldIdName()
	{
		return $this->pid;
	}

	public function setPostedData($data)
	{
		return $this;
	}

	public function save($force = false, $forceSave = false)
	{
		$update = e107::getDb()->createQueryBuilder()->update($this->table);

		foreach($this->written as $field => $value)
		{
			$update->setTyped($field, $value, 'string');
		}

		$update->where($this->pid, $this->id)->execute();
		$this->saved = true;

		return true;
	}

	public function hasError()
	{
		return true;
	}

	public function setMessages($session = false)
	{
		return $this;
	}
}

/**
 * Probe recording what the single-delete trigger archives, with a tree model of its own.
 */
class AdminUiHistoryDeleteProbeFixture extends e_admin_ui
{
	/** @var array one entry per backupToHistory() call */
	public $backups = array();

	/** @var AdminUiHistoryTreeStub */
	public $treeStub;

	public function __construct($table, $pid, $node = null)
	{
		$this->table = $table;
		$this->pid = $pid;
		$this->treeStub = new AdminUiHistoryTreeStub($node);
		$this->_tree_model = $this->treeStub;
	}

	public function getPosted($key = null, $default = null)
	{
		return $default;
	}

	public function probeBatchDelete(array $selected)
	{
		return $this->handleListDeleteBatch($selected);
	}

	protected function backupToHistory($table, $pid, $id, $action, $data, $posted = true)
	{
		$this->backups[] = array(
			'table'  => $table,
			'pid'    => $pid,
			'id'     => $id,
			'action' => $action,
			'data'   => $data,
			'posted' => $posted,
		);

		return true;
	}
}

/**
 * A tree model that has no node for the id, as it has none on any list page but the first.
 */
class AdminUiHistoryTreeStub
{
	/** @var array ids passed to delete() */
	public $deleted = array();

	/** @var bool what delete() answers, as the tree model answers false for a row it could not remove */
	public $deleteReturns = true;

	private $node;

	public function __construct($node = null)
	{
		$this->node = $node;
	}

	public function getNode($id)
	{
		return $this->node;
	}

	public function delete($id)
	{
		$this->deleted[] = $id;

		return $this->deleteReturns;
	}

	public function setMessages($session = false)
	{
		return $this;
	}
}

/**
 * A tree node holding what an observer left in it, as the delete paths read it.
 */
class AdminUiHistoryNodeStub
{
	private $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function getData()
	{
		return $this->data;
	}
}
