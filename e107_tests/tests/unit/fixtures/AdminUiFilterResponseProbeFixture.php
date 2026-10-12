<?php

/**
 * A user list whose custom query selects every column of the row, answering the ajax filter for a posted term.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included.
 */
class AdminUiFilterResponseProbeFixture extends e_admin_ui
{
	protected $pluginName = 'core';
	protected $table = 'user';
	protected $pid = 'user_id';
	protected $listQry = 'SELECT u.* FROM `#user` AS u';

	/** @var string */
	private $term;

	/**
	 * @param array  $fields the list's field declarations
	 * @param string $term   the posted search term
	 */
	public function __construct(array $fields, $term)
	{
		$this->term = $term;
		$this->fields = $fields;
		$this->setRequest(new e_admin_request('mode=main&action=filter'));
		$this->parseAliases();
	}

	public function getPosted($key = null, $default = null)
	{
		return $key === 'searchquery' ? $this->term : $default;
	}

	public function getTreeModel()
	{
		return new e_front_tree_model();
	}

	/**
	 * @return string the filter's answer
	 */
	public function probe()
	{
		return $this->renderAjaxFilterResponse($this->listQry);
	}

	/**
	 * @param string      $filterOptions what the filter dropdown posts
	 * @param string|null $sortField     the column the list is asked to be sorted by
	 * @return string the list query for the term
	 */
	public function listQuery($filterOptions = '', $sortField = null)
	{
		$this->getRequest()->setAction('list');
		$this->getRequest()->setQuery('searchquery', $this->term);
		$this->getRequest()->setQuery('filter_options', $filterOptions);
		$this->getRequest()->setQuery('field', $sortField);

		return $this->_modifyListQry(false, false, false, false, $this->listQry);
	}
}
