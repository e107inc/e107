<?php
/*
* e107 website system
*
* Copyright (c) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Featurebox Category model
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/featurebox/includes/category.php,v $
* $Revision: 1.1 $
* $Date: 2009-12-04 18:52:19 $
* $Author: secretr $
*
*/

class plugin_featurebox_category extends e_model
{
	/**
	 * @var plugin_featurebox_tree
	 */
	protected $_tree = null;
	/**
	 * Load category data by layout
	 * 
	 * @param string $layout
	 * @param boolean $force
	 */
	public function loadByLayout($layout, $force = false)
	{
		//TODO
	}
	
	/**
	 * Get items model tree for the current category
	 * TODO - system cache
	 * 
	 * @param boolean $force
	 * @return plugin_featurebox_tree
	 */
	public function getTree($force = false)
	{
		if($force || null === $this->_tree)
		{
			$this->_tree = new plugin_featurebox_tree();
			$options = array(); // TODO options
			$this->_tree->load($this->getId(), $options, $force);
		}
		
		return $this->_tree;
	}
	
	/**
	 * Set item tree
	 * 
	 * @param plugin_featurebox_tree $category_tree
	 * @return plugin_featurebox_category
	 */
	public function setTree($category_tree)
	{
		$this->_tree = $category_tree;
		return $this;
	}
}