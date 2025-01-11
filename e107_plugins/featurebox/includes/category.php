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
* $Revision$
* $Date$
* $Author$
*
*/

if (!defined('e107_INIT')) { exit; }

class plugin_featurebox_category extends e_model
{
	/**
	 * @var plugin_featurebox_tree
	 */
	protected $_tree = null;
	
	/**
	 * Data loaded check 
	 * @var boolean 
	 */
	protected $_loaded_data = null;
	
	/**
	 * @see e_model::_field_id
	 * @var string
	 */
	protected $_field_id = 'fb_category_id';
	
	/**
	 * @see e_model::_db_table
	 * @var string
	 */
	protected $_db_table = 'featurebox_category';
	
	/**
	 * Parameter (single string format):
	 * - alt: return title as tag attribute text
	 * @param string $parm
	 * @return string
	 */
	public function sc_featurebox_category_title($parm)
	{
		return ($parm == 'alt' ? e107::getParser()->toAttribute($this->get('fb_category_title')) : e107::getParser()->toHTML($this->get('fb_category_title'), false, 'TITLE'));
	}
	
	/**
	 * Parameter (single string format):
	 * - src: return image src URL only
	 * 
	 * @param string $parm
	 * @return string
	 */
	public function sc_featurebox_category_icon($parm)
	{
		if(!$this->get('fb_category_icon'))
		{
			return '';
		}
		$tp = e107::getParser();
		
		$src = $tp->replaceConstants($this->get('fb_category_icon'), 'full');
		if($parm == 'src')
		{
			return $src;
		}
		return '<img src="'.$src.'" alt="'.$tp->toAttribute($this->get('fb_category_title')).'" class="icon featurebox" />';
	}
	
	public function sc_featurebox_category_template()
	{
		return $this->get('fb_category_template');
	}
	
	public function sc_featurebox_category_limit()
	{
		return $this->get('fb_category_limit');
	}
	
	public function sc_featurebox_category_total()
	{
		return $this->getParam('total', 0);
	}

	public function sc_featurebox_category_all()
	{
		return $this->getItemTree()->getTotal();
	}
	
	public function sc_featurebox_category_cols()
	{
		return $this->getParam('cols', 1);
	}

	public function sc_featurebox_nav_counter()
	{
		return $this->getParam('counter', 1);
	}

	public function sc_featurebox_nav_active()
	{
		return $this->getParam('active') ? ' active' : '';
	}

	public function sc_featurebox_category_emptyfill()
	{
		return $this->getParam('no_fill_empty', 0);
	}
	
	/**
	 * Load category data by layout
	 * TODO - system cache
	 * 
	 * @param string $template
	 * @param boolean $force
	 * @return plugin_featurebox_category
	 */
	public function loadByTemplate($template, $force = false)
	{
		if($force || null === $this->_loaded_data)
		{
			if(e107::getDb()->select('featurebox_category', '*', 'fb_category_class IN ('.USERCLASS_LIST.') AND fb_category_template=\''.e107::getParser()->toDB($template).'\''))
			{
				$this->setData(e107::getDb()->fetch());
				$this->_loaded_data = true;
			}
		}
		$this->_loaded_data = false;
		return $this;
	}
	
	/**
	 * Get items model tree for the current category
	 * TODO - system cache
	 * 
	 * @param boolean $force
	 * @return plugin_featurebox_tree
	 */
	public function getItemTree($force = false)
	{
		if($force || null === $this->_tree)
		{
			$this->_tree = new plugin_featurebox_tree();
			$options = array(
				'limit' => $this->getParam('limit', $this->get('fb_category_limit')),
				'from' => $this->getParam('from', 0),
				'random' => $this->getParam('random', $this->get('fb_category_random')),
				'ids' => $this->getParam('ids', '')
			);
			$this->_tree->load($this->getId(), $force, $options);
		}
		
		return $this->_tree;
	}
	
	/**
	 * Set item tree
	 * 
	 * @param plugin_featurebox_tree $item_tree
	 * @return plugin_featurebox_category
	 */
	public function setItemTree($item_tree)
	{
		$this->_tree = $item_tree;
		return $this;
	}
}