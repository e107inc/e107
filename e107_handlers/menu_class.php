<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (c) e107 Inc. 2001-2009
|     http://e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/menu_class.php,v $
|     $Revision: 1.9 $
|     $Date: 2009-08-16 23:58:31 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT'))
{
	exit ();
}


class e_menu
{
	public $eMenuActive = array();
  //  public $eMenuList = array();

	function __construct()
	{
	}

	/**
	* Init
	*
	*/


	public function init()
	{
		$menu_layout_field = THEME_LAYOUT != e107 :: getPref('sitetheme_deflayout') ? THEME_LAYOUT : "";
		$menu_data = e107 :: getCache()->retrieve_sys("menus_".USERCLASS_LIST."_".md5(e_LANGUAGE.$menu_layout_field));
		$menu_data = e107 :: getArrayStorage()->ReadArray($menu_data);

		$eMenuArea = array();
		// $eMenuList = array();
		//	$eMenuActive	= array();  // DEPRECATED

		if (!is_array($menu_data))
		{
			$menu_qry = 'SELECT * FROM #menus WHERE menu_location > 0 AND menu_class IN ('.USERCLASS_LIST.') AND menu_layout = "'.$menu_layout_field.'" ORDER BY menu_location,menu_order';
			if (e107 :: getDb()->db_Select_gen($menu_qry))
			{
				while ($row = e107 :: getDb()->db_Fetch())
				{
					$eMenuArea[$row['menu_location']][] = $row;
				}
			}

			$menu_data['menu_area'] = $eMenuArea;

			$menuData = e107 :: getArrayStorage()->WriteArray($menu_data,false);
			e107 :: getCache()->set_sys('menus_'.USERCLASS_LIST.'_'.md5(e_LANGUAGE.$menu_layout_field),$menuData);
		}
		else
		{
			$eMenuArea 	= $menu_data['menu_area'];
		}
		$total = array();
		foreach ($eMenuArea as $area => $val)
		{
			foreach ($val as $row)
			{
				if ($this->isVisible($row))
				{
					$path = str_replace("/","",$row['menu_path']);
					if (!isset ($total[$area]))
					{
						$total[$area] = 0;
					}
					$this->eMenuActive[$area][] = $row;
					$total[$area]++;
				}
			}
		}
		e107 :: getRender()->eMenuTotal = $total;
	}


	private function isVisible($row)
	{
		$show_menu = TRUE;

		if ($row['menu_pages'])
		{
			list($listtype,$listpages) = explode("-",$row['menu_pages'],2);
			$pagelist = explode("|",$listpages);
			$check_url = e_SELF.(e_QUERY ? "?".e_QUERY : '');

			switch ($listtype)
			{
				case '1' :  //show menu

					$show_menu = false;
					foreach ($pagelist as $p)
					{
						if (substr($p,- 1) === '!')
						{
							$p = substr($p,0,- 1);
							$show_menu = TRUE;
							break 2;
						}
						elseif (strpos($check_url,$p) !== FALSE)
						{
							$show_menu = TRUE;
							break 2;
						}
					}
					break;
				case '2' :  //hide menu

					$show_menu = TRUE;
					foreach ($pagelist as $p)
					{
						if (substr($p,- 1) == '!')
						{
							$p = substr($p,0,- 1);
							if (substr($check_url,strlen($p) * - 1) == $p)
							{
								$show_menu = FALSE;
								break 2;
							}
						}
						elseif (strpos($check_url,$p) !== FALSE)
						{
							$show_menu = FALSE;
							break 2;
						}
					}
					break;
			} //end switch

		} //endif menu_pages

		return $show_menu;
	}


	public function renderArea($parm = '')
	{
		global $sql,$ns,$tp,$sc_style;
		global $error_handler;
		$e107 = e107 :: getInstance();
		$tmp = explode(':',$parm);
		$buffer_output = true;  // Default - return all output.

		if (isset ($tmp[1]) && $tmp[1] == 'echo')
		{
			$buffer_output = false;
		}
		if (!array_key_exists($tmp[0],$this->eMenuActive))
		{
			return;
		}
		if ($buffer_output)
		{
			ob_start();
		}
		e107 :: getRender()->eMenuArea = $tmp[0];
		foreach ($this->eMenuActive[$tmp[0]] as $row)
		{
			$this->renderMenu($row['menu_path'],$row['menu_name'],$row['menu_parms']);
		}
		e107 :: getRender()->eMenuCount = 0;
		e107 :: getRender()->eMenuArea = null;
		if ($buffer_output)
		{
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}
	}


	public function renderMenu($mpath,$mname,$parm = '')
	{
		global $sql; // required at the moment.
		global $ns,$tp,$sc_style;
		$e107 = e107 :: getInstance();

		if ($error_handler->debug == true)
		{
			echo "\n<!-- Menu Start: ".$mname." -->\n";
		}

		e107 :: getDB()->db_Mark_Time($mname);

		if (is_numeric($mpath))
		{
			$sql->db_Select("page","*","page_id='".$mpath."' ");
			$page = $sql->db_Fetch();
			$caption = $e107->tp->toHTML($page['page_title'],true,'parse_sc, constants');
			$text = $e107->tp->toHTML($page['page_text'],true,'parse_sc, constants');
			e107 :: getRender()->tablerender($caption,$text);
		}
		else
		{
			if (is_readable(e_PLUGIN.$mpath."/languages/".e_LANGUAGE.".php"))
			{
				include_once (e_PLUGIN.$mpath."/languages/".e_LANGUAGE.".php");
			}
			elseif (is_readable(e_PLUGIN.$mpath."/languages/".e_LANGUAGE."/".e_LANGUAGE.".php"))
			{
				include_once (e_PLUGIN.$mpath."/languages/".e_LANGUAGE."/".e_LANGUAGE.".php");
			}
			elseif (is_readable(e_PLUGIN.$mpath."/languages/English.php"))
			{
				include_once (e_PLUGIN.$mpath."/languages/English.php");
			}
			elseif (is_readable(e_PLUGIN.$mpath."/languages/English/English.php"))
			{
				include_once (e_PLUGIN.$mpath."/languages/English/English.php");
			}
			if (file_exists(e_PLUGIN.$mpath."/".$mname.".php"))
			{
				include_once (e_PLUGIN.$mpath."/".$mname.".php");
			}
		}

		e107 :: getDB()->db_Mark_Time("(After ".$mname.")");
		if ($error_handler->debug == true)
		{
			echo "\n<!-- Menu End: ".$mname." -->\n";
		}

	}


}
