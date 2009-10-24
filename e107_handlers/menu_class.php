<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Menu Class
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/menu_class.php,v $
 * $Revision: 1.15 $
 * $Date: 2009-10-24 07:53:25 $
 * $Author: e107coders $
*/

if(!defined('e107_INIT'))
{
	exit();
}

/**
 * Retrieve and render site menus
 * 
 * @package e107
 * @category e107_handlers
 * @version 1.0
 * @author Cameron
 * @copyright Copyright (c) 2009, e107 Inc.
 *
 */
class e_menu
{
	/**
	 * Runtime cached menu data
	 *
	 * @var array
	 */
	public $eMenuActive = array();
	
	/**
	 * Visibility check cache
	 *
	 * @var array
	 */
	protected $_visibility_cache = array();

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
	}

	/**
	 * Retrieve menus, check visibility against
	 * current user classes and current page url
	 *
	 */
	public function init()
	{
		$menu_layout_field = THEME_LAYOUT!=e107::getPref('sitetheme_deflayout') ? THEME_LAYOUT : "";
		$menu_data = e107::getCache()->retrieve_sys("menus_".USERCLASS_LIST."_".md5(e_LANGUAGE.$menu_layout_field));
		$menu_data = e107::getArrayStorage()->ReadArray($menu_data);
		$eMenuArea = array();
		// $eMenuList = array();
		//	$eMenuActive	= array();  // DEPRECATED
		if(!is_array($menu_data))
		{
			$menu_qry = 'SELECT * FROM #menus WHERE menu_location > 0 AND menu_class IN ('.USERCLASS_LIST.') AND menu_layout = "'.$menu_layout_field.'" ORDER BY menu_location,menu_order';
			if(e107::getDb()->db_Select_gen($menu_qry))
			{
				while($row = e107::getDb()->db_Fetch())
				{
					$eMenuArea[$row['menu_location']][] = $row;
				}
			}
			$menu_data['menu_area'] = $eMenuArea;
			$menuData = e107::getArrayStorage()->WriteArray($menu_data, false);
			e107::getCache()->set_sys('menus_'.USERCLASS_LIST.'_'.md5(e_LANGUAGE.$menu_layout_field), $menuData);
		}
		else
		{
			$eMenuArea = $menu_data['menu_area'];
		}
		$total = array();
		foreach($eMenuArea as $area => $val)
		{
			foreach($val as $row)
			{
				if($this->isVisible($row))
				{
					$path = str_replace("/", "", $row['menu_path']);
					if(!isset($total[$area]))
					{
						$total[$area] = 0;
					}
					$this->eMenuActive[$area][] = $row;
					$total[$area]++;
				}
			}
		}
		e107::getRender()->eMenuTotal = $total;
	}

	/**
	 * Check visibility of a menu against URL
	 * 
	 * @param array $row menu data
	 * @return boolean
	 */
	protected function isVisible($row, $url = '')
	{
		$iD = varset($row['id']);
		
		if(isset($this->_visibility_cache[$iD]))
		{
			return $this->_visibility_cache[$iD];
		}
		
		$show_menu = TRUE;
		if($row['menu_pages'])
		{
			list ($listtype, $listpages) = explode("-", $row['menu_pages'], 2);
			$pagelist = explode("|", $listpages);
			$check_url = $url ? $url : e_SELF.(e_QUERY ? "?".e_QUERY : '');
			switch($listtype)
			{
				case '1': //show menu
					$show_menu = false;
					foreach($pagelist as $p)
					{
						if(substr($p, -1)==='!')
						{
							$p = substr($p, 0, -1);
							$show_menu = true;
							break 2;
						}
						elseif(strpos($check_url, $p) !== false)
						{
							$show_menu = true;
							break 2;
						}
					}
					break;
				case '2': //hide menu
					$show_menu = true;
					foreach($pagelist as $p)
					{
						if(substr($p, -1)=='!')
						{
							$p = substr($p, 0, -1);
							if(substr($check_url, strlen($p)*-1)==$p)
							{
								$show_menu = false;
								break 2;
							}
						}
						elseif(strpos($check_url, $p) !== false)
						{
							$show_menu = false;
							break 2;
						}
					}
					break;
			} //end switch
		} //endif menu_pages
		
		$this->_visibility_cache[$iD] = $show_menu;
		return $show_menu;
	}

	/**
	 * Render menu area
	 *
	 * @param string $parm
	 * @return string
	 */
	public function renderArea($parm = '')
	{
		global $sql, $ns, $tp, $sc_style;
		global $error_handler;
		$e107 = e107::getInstance();
		
		$tmp = explode(':', $parm);
		$buffer_output = true; // Default - return all output.
		if(isset($tmp[1])&&$tmp[1]=='echo')
		{
			$buffer_output = false;
		}
		if(!array_key_exists($tmp[0], $this->eMenuActive))
		{
			return;
		}
		if($buffer_output)
		{
			ob_start();
		}
		e107::getRender()->eMenuArea = $tmp[0];
		foreach($this->eMenuActive[$tmp[0]] as $row)
		{
			$this->renderMenu($row['menu_path'], $row['menu_name'], $row['menu_parms']);
		}
		e107::getRender()->eMenuCount = 0;
		e107::getRender()->eMenuArea = null;
		if($buffer_output)
		{
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}
	}

	/**
	 * Render menu
	 *
	 * @param string $mpath menu path
	 * @param string $mname menu name
	 * @param string $parm menu parameters
	 * @param boolean $return
	 * return string if required
	 */
	public function renderMenu($mpath, $mname, $parm = '', $return = false)
	{
		global $sql; // required at the moment.
		global $ns, $tp, $sc_style, $e107_debug;
		$e107 = e107::getInstance();
		
		if($return)
		{
			ob_start();
		}
		
		if(vartrue($error_handler->debug))
		{
			echo "\n<!-- Menu Start: ".$mname." -->\n";
		}
		e107::getDB()->db_Mark_Time($mname);
		if(is_numeric($mpath))
		{
			$sql->db_Select("page", "*", "page_id=".intval($mpath)." ");
			$page = $sql->db_Fetch();
			$caption = $e107->tp->toHTML($page['page_title'], true, 'parse_sc, constants');
			$text = $e107->tp->toHTML($page['page_text'], true, 'parse_sc, constants');
			e107::getRender()->tablerender($caption, $text);
		}
		else
		{
			e107::loadLanFiles($mpath);

			//include once is not an option anymore
			//e107_include will break many old menus (evel globals), so we'll wait for a while...
			//e107_include(e_PLUGIN.$mpath."/".$mname.".php");
			if(substr($mpath,-1)!='/')
			{
				$mpath .= '/';	
			}
			$e107_debug ? include(e_PLUGIN.$mpath.$mname.'.php') : @include(e_PLUGIN.$mpath.$mname.'.php');
			
			/*if(file_exists(e_PLUGIN.$mpath."/".$mname.".php"))
			{
				include_once (e_PLUGIN.$mpath."/".$mname.".php");
			}*/
		}
		e107::getDB()->db_Mark_Time("(After ".$mname.")");
		if($error_handler->debug==true)
		{
			echo "\n<!-- Menu End: ".$mname." -->\n";
		}
		
		if($return)
		{
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}
	}
}
