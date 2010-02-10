<?php
/*
* e107 website system
*
* Copyright (c) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Featurebox Item model
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/featurebox/includes/item.php,v $
* $Revision$
* $Date$
* $Author$
*
*/

if (!defined('e107_INIT')) { exit; }

// TODO - sc_* methods
class plugin_featurebox_item extends e_model
{
	/**
	 * @see e_model::_field_id
	 * @var string
	 */
	protected $_field_id = 'fb_id';
	
	/**
	 * @see e_model::_db_table
	 * @var string
	 */
	protected $_db_table = 'featurebox';
	
	/**
	 * @var plugin_featurebox_category
	 */
	protected $_category = null;

	/**
	 * Parameter list (GET string format):
	 * - alt: return title as tag attribute text
	 * - url: add url tag to the output (only if 'fb_imageurl' is available)
	 * 
	 * @param string $parm
	 * @return string
	 */
	public function sc_featurebox_title($parm = '')
	{
		parse_str($parm, $parm);
		$tp = e107::getParser();
		if(isset($parm['alt']))
		{
			return $tp->toAttribute($this->get('fb_title'));
		}
		
		$ret = $tp->toHTML($this->get('fb_title'), false, 'TITLE');
		if(isset($parm['url']) && $this->get('fb_imageurl'))
		{
			return '<a id="featurebox-titleurl-"'.$this->getId().' href="'.$tp->replaceConstants($this->get('fb_imageurl'), 'full').'" title="'.$tp->toAttribute($this->get('fb_title')).'" rel="'.$tp->toAttribute(vartrue($parm['rel'], 'external')).'">'.$ret.'</a>';
		}
		
		return $ret;
	}
	
	public function sc_featurebox_text()
	{
		return e107::getParser()->toHTML($this->get('fb_text'), true, 'BODY');
	}
	
	/**
	 * Parameter list (GET string format):
	 * - src: return image src URL only
	 * - nourl: force no url tag
	 * 
	 * @param string $parm
	 * @return string
	 */
	public function sc_featurebox_image($parm = '')
	{
		if(!$this->get('fb_image'))
		{
			return '';
		}
		parse_str($parm, $parm);
		$tp = e107::getParser();
		
		$src = $tp->replaceConstants($this->get('fb_image'), 'full');

		if(isset($parm['src']))
		{
			return $src;
		}
		$tag = '<img id="featurebox-image-'.$this->getId().'" src="'.$src.'" alt="'.$tp->toAttribute($this->get('fb_title')).'" class="featurebox" />';
		if(isset($parm['nourl']) || !$this->get('fb_imageurl'))
		{
			return $tag;
		}
		return '<a id="featurebox-imageurl-'.$this->getId().'" href="'.$tp->replaceConstants($this->get('fb_imageurl'), 'full').'" title="'.$tp->toAttribute($this->get('fb_title')).'" rel="'.$tp->toAttribute(vartrue($parm['rel'], 'external')).'">'.$tag.'</a>';
	}
	
	/**
	 * Item counter number (starting from 1)
	 */
	public function sc_featurebox_counter()
	{
		return $this->getParam('counter', 1);
	}
	
	/**
	 * Item limit number
	 */
	public function sc_featurebox_limit()
	{
		return $this->getParam('limit', 0);
	}
	
	/**
	 * Number of items (real) currently loaded
	 */
	public function sc_featurebox_total()
	{
		return $this->getParam('total', 0);
	}
	
	/**
	 * Total Number of items (no matter of the limit)
	 */
	public function sc_featurebox_all()
	{
		return $this->getCategory()->sc_featurebox_category_all();
	}
	
	/**
	 * Number of items per column
	 */
	public function sc_featurebox_cols()
	{
		return $this->getParam('cols', 1);
	}
	
	/**
	 * Item counter number inside a column (1 to sc_featurebox_cols)
	 */
	public function sc_featurebox_colcount()
	{
		return $this->getParam('col_counter', 1);
	}
	
	/**
	 * Column counter
	 */
	public function sc_featurebox_colscount()
	{
		return $this->getParam('cols_counter', 1);
	}
	
	/**
	 * Set current category
	 * @param plugin_featurebox_category $category
	 * @return plugin_featurebox_item
	 */
	public function setCategory($category)
	{
		$this->_category = $category; 
		return $this;
	}

	/**
	 * Get Category model instance
	 * @return plugin_featurebox_category
	 */
	public function getCategory()
	{
		if(null === $this->_category)
		{
			$this->_category = new plugin_featurebox_category();
			$this->_category->load($this->get('fb_category'));
		}
		return $this->_category;
	}
	
	/**
	 * Magic call - category shortcodes
	 * 
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, $arguments)
	{
		if (strpos($method, "sc_featurebox_") === 0)
		{
			return call_user_func_array(array($this->getCategory(), $method), $arguments);
		}
	}
}



