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

	public function sc_featurebox_category_sef()
	{
		return e107::getParser()->toAttribute(self::address($this->getData()));
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
	 * The string a {FEATUREBOX|x} modifier has to carry to reach one category: its
	 * sef, or its template for as long as the sef is empty.
	 *
	 * @param array $row featurebox_category row
	 * @return string
	 */
	public static function address($row)
	{
		$sef = isset($row['fb_category_sef']) ? trim((string) $row['fb_category_sef']) : '';

		if($sef !== '')
		{
			return $sef;
		}

		return isset($row['fb_category_template']) ? trim((string) $row['fb_category_template']) : '';
	}

	/**
	 * Narrow a value to the grammar a {FEATUREBOX|x} modifier survives.
	 * Menu Manager filters the modifier it writes with /[^\w-]/ and no /u, so an
	 * accented sef is transliterated to ASCII here rather than mangled there.
	 *
	 * @param string $sef
	 * @return string
	 */
	public static function toSef($sef)
	{
		$tp = e107::getParser();

		return trim((string) preg_replace('/[^\w-]+/', '-', $tp->toASCII($tp->toText((string) $sef))), '-');
	}

	/**
	 * Load the category one {FEATUREBOX|x} modifier addresses.
	 * TODO - system cache
	 *
	 * @param string $sef
	 * @param boolean $force
	 * @return plugin_featurebox_category
	 */
	public function loadBySef($sef, $force = false)
	{
		if($force || null === $this->_loaded_data)
		{
			$row = self::findByAddress($sef);

			if($row)
			{
				$this->setData($row);
				$this->_loaded_data = true;
			}
		}
		$this->_loaded_data = false;
		return $this;
	}

	/**
	 * Load category data by layout
	 * TODO - system cache
	 * 
	 * @param string $template
	 * @param boolean $force
	 * @return plugin_featurebox_category
	 * @deprecated v2.4.0 Avoid in new code and migrate existing call sites when refactoring; {@see plugin_featurebox_category::loadBySef()} resolves the same string against the category's sef first. This method remains supported and tested, with no removal planned.
	 */
	public function loadByTemplate($template, $force = false)
	{
		return $this->loadBySef($template, $force);
	}

	/**
	 * The one category an address resolves to, read in id order so that two
	 * categories sharing a template still resolve deterministically. Matched
	 * without regard to case, as the SQL this replaces was under the column's
	 * collation. Selects the whole row so that no SQL names fb_category_sef,
	 * which a site that has taken the files but not the database update lacks.
	 *
	 * @param string $address
	 * @param boolean $visibleOnly true to see only the current user's classes, as the front end does
	 * @return array|null
	 */
	public static function findByAddress($address, $visibleOnly = true)
	{
		$address = trim((string) $address);

		if($address === '')
		{
			return null;
		}

		$qb = e107::getDb()->createQueryBuilder()
			->select('*')->from('featurebox_category')
			->orderBy('fb_category_id', 'ASC');

		if($visibleOnly)
		{
			$qb->whereIn('fb_category_class', array_map('intval', explode(',', USERCLASS_LIST)));
		}

		foreach((array) $qb->fetchAll() as $row)
		{
			if(strcasecmp(self::address($row), $address) === 0)
			{
				return $row;
			}
		}

		return null;
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