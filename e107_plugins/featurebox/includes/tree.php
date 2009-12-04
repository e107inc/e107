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
* $Source: /cvs_backup/e107_0.8/e107_plugins/featurebox/includes/tree.php,v $
* $Revision: 1.1 $
* $Date: 2009-12-04 18:52:19 $
* $Author: secretr $
*
*/

class plugin_featurebox_tree extends e_tree_model
{
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
		
		$this->setParams(array(
			'model_class' => 'plugin_featurebox_item',
			'model_message_stack' => 'featurebox'
		));
		
		// TODO - options -> limit, random; set param 'db_query'
		
		parent::load($force);
		
		return $this;
	}
}