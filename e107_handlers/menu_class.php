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
|     $Revision: 1.8 $
|     $Date: 2009-08-16 16:30:56 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
if(!defined('e107_INIT'))
{
	exit();
}

class e_menu
{
	public $eMenuArea;
	public $eMenuList;
	public $eMenuActive;
	
	function __construct()
	{
		
	}
	
	/**
	 * Init
	 *
	 */
	public function init()
	{
		$menu_layout_field = THEME_LAYOUT != e107::getPref('sitetheme_deflayout') ? THEME_LAYOUT : "";
	  	$menu_data = e107::getCache()->retrieve_sys("menus_".USERCLASS_LIST."_".md5(e_LANGUAGE.$menu_layout_field));
	 	$menu_data = e107::getArrayStorage()->ReadArray($menu_data);
		$eMenuList		= array();
		$eMenuActive	= array();
		$eMenuArea		= array();
	
		if(!is_array($menu_data))
		{
	
			$menu_qry = 'SELECT * FROM #menus WHERE menu_location > 0 AND menu_class IN ('.USERCLASS_LIST.') AND menu_layout = "'.$menu_layout_field.'" ORDER BY menu_location,menu_order';
			if (e107::getDb()->db_Select_gen($menu_qry))
			{
				while ($row = e107::getDb()->db_Fetch())
				{
					$eMenuList[$row['menu_location']][] = $row;
	                $eMenuArea[$row['menu_location']][$row['menu_name']] = 1;
					$eMenuActive[$row['menu_name']]	= $row['menu_name'];
				}
			}
			$menu_data['menu_area'] = $eMenuArea;
			$menu_data['menu_list'] = $eMenuList;
			$menu_data['menu_active'] = $eMenuActive;
			$menu_data = e107::getArrayStorage()->WriteArray($menu_data, false);
			e107::getCache()->set_sys('menus_'.USERCLASS_LIST.'_'.md5(e_LANGUAGE), $menu_data);
	
			unset($menu_data,$menu_layout_field,$menu_qry);
		}
		else
		{
			$eMenuArea 	= $menu_data['menu_area'];
			$eMenuList 	= $menu_data['menu_list'];
			$eMenuActive = $menu_data['menu_active'];
			unset($menu_data);
		}

		$this->eMenuActive = $eMenuActive;
		$this->eMenuArea = $eMenuArea;
		$this->eMenuList = $eMenuList;
	}
	
	function render($parm = '')
	{
		
		global $sql, $ns, $tp, $sc_style;
		global $error_handler;
		$e107 = e107::getInstance();
		
		$eMenuList = $this->eMenuList;
		
		$tmp = explode(':',$parm);
	
		$buffer_output = true;				// Default - return all output.
		if (isset($tmp[1]) && $tmp[1] == 'echo') { $buffer_output = false; }
	
		if (!array_key_exists($tmp[0], $eMenuList)) { return; }
	
		if ($buffer_output)
		{
			ob_start();
		}
	
	    e107::getRender()->eMenuArea = $tmp[0];
	
	
		foreach($eMenuList[$tmp[0]] as $row)
		{
			$pkey = str_replace("/","",$row['menu_path']);
			$show_menu[$pkey] = $row['menu_name'];
	
			if($row['menu_pages'])
			{
				list($listtype, $listpages) = explode('-', $row['menu_pages'], 2);
				$pagelist = explode('|',$listpages);
				$check_url = e_SELF.(e_QUERY ? '?'.e_QUERY : '');
	
				if($listtype == '1')  //show menu
				{
					//$show_menu[$pkey] = FALSE;
					unset($show_menu[$pkey]);
					foreach($pagelist as $p)
					{
						if(substr($p, -1) == '!')
						{
							$p = substr($p, 0, -1);
							if(substr($check_url, strlen($p)*-1) == $p)
							{
								// $show_menu[$pkey] = TRUE;
								$show_menu[$pkey] = $row['menu_name'];
							}
						}
						else
						{
							if(strpos($check_url,$p) !== FALSE)
							{
								// $show_menu[$pkey] = TRUE;
								$show_menu[$pkey] = $row['menu_name'];
							}
						}
					}
				}
				elseif($listtype == '2') //hide menu
				{
				   //	$show_menu[$pkey] = TRUE;
				   $show_menu[$pkey] = $row['menu_name'];
					foreach($pagelist as $p) {
						if(substr($p, -1) == '!')
						{
							$p = substr($p, 0, -1);
							if(substr($check_url, strlen($p)*-1) == $p)
							{
								// $show_menu[$pkey] = FALSE;
								unset($show_menu[$pkey]);
							}
						}
						else
						{
							if(strpos($check_url, $p) !== FALSE)
							{
								// $show_menu[$pkey] = FALSE;
								unset($show_menu[$pkey]);
							}
						}
					}
				}
			}
	     }
	
		 e107::getRender()->eMenuTotal = count($show_menu);
	
		 foreach($show_menu as $mpath=>$mname)
		 {
			  //	$mname = $row['menu_name'];
				if($error_handler->debug == true)
				{
					echo "\n<!-- Menu Start: ".$mname." -->\n";
				}
				$sql->db_Mark_Time($mname);
				if(is_numeric($mpath))
				{
					$sql -> db_Select("page", "*", "page_id='".$mpath."' ");
					$page  = $sql -> db_Fetch();
					$caption = $e107->tp->toHTML($page['page_title'], TRUE, 'parse_sc, constants');
					$text = $e107->tp->toHTML($page['page_text'], TRUE, 'parse_sc, constants');
					e107::getRender()->tablerender($caption, $text);
				}
				else
				{
					if (is_readable(e_PLUGIN.$mpath."/languages/".e_LANGUAGE.".php"))
					{
						include_once(e_PLUGIN.$mpath."/languages/".e_LANGUAGE.".php");
					}
					elseif (is_readable(e_PLUGIN.$mpath."/languages/".e_LANGUAGE."/".e_LANGUAGE.".php"))
					{
						include_once(e_PLUGIN.$mpath."/languages/".e_LANGUAGE."/".e_LANGUAGE.".php");
					}
					elseif (is_readable(e_PLUGIN.$mpath."/languages/English.php"))
					{
						include_once(e_PLUGIN.$mpath."/languages/English.php");
					}
					elseif (is_readable(e_PLUGIN.$mpath."/languages/English/English.php"))
					{
						include_once(e_PLUGIN.$mpath."/languages/English/English.php");
					}
	
					if(file_exists(e_PLUGIN.$mpath."/".$mname.".php"))
					{
						include_once(e_PLUGIN.$mpath."/".$mname.".php");
					}
				}
				$sql->db_Mark_Time("(After ".$mname.")");
				if ($error_handler->debug == true)
				{
					echo "\n<!-- Menu End: ".$mname." -->\n";
				}
	            unset($caption,$text); // clear variables for proceeding menus.
			}
	
	
	         e107::getRender()->eMenuCount = 0;
			 e107::getRender()->eMenuArea = null;
	
	
		if ($buffer_output)
		{
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}
	}
}