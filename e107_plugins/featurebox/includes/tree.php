<?php
/*
* e107 website system
*
* Copyright (c) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Featurebox Category Tree model
*
* $URL$
* $Id$
*/

if (!defined('e107_INIT')) { exit; }

class plugin_featurebox_tree extends e_tree_model
{
	protected $_field_id = 'fb_id';
	/**
	 * Load tree data
	 * TODO - system cache
	 * 
	 * @param integer $category_id
	 * @param array $options
	 * @param boolean $force
	 * @return plugin_featurebox_tree
	 */
	public function load($category_id, $options = array(), $force = false)
	{
		if(!$force && !$this->isEmpty())
		{
			return $this;
		}

		$this->setParam('model_class', 'plugin_featurebox_item')
			->setParam('model_message_stack', 'featurebox');

		$this->updateParams($options);
		
		$order = $this->getParam('random') ? ' ORDER BY rand()' : ' ORDER BY fb_order ASC';
		$limit = $this->getParam('limit') ? ' LIMIT '.intval($this->getParam('from'), 0).','.intval($this->getParam('limit')) : '';
		$ids = $this->getParam('ids') ? preg_replace('/[^0-9,]/', '', $this->getParam('ids')) : '';
		$where = $ids ? ' AND fb_id IN('.$ids.')' : '';
		$qry = 'SELECT SQL_CALC_FOUND_ROWS * FROM #featurebox WHERE fb_category='.intval($category_id).' AND fb_class IN('.USERCLASS_LIST.')'.$where.$order.$limit;
		$this->setParam('db_query', $qry);
		
		parent::loadBatch($force);
		
		return $this;
	}
}