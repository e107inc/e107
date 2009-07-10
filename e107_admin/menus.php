<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/menus.php,v $
|     $Revision: 1.25 $
|     $Date: 2009-07-10 14:58:17 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("2"))
{
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'menus';


require_once(e_HANDLER."file_class.php");
require_once(e_HANDLER."form_handler.php");


	$frm = new form;
	$men = new menuManager();   // use 1 for dragdrop.





  if(isset($_GET['ajax']))
  {
  	$men->menuSaveAjax();
	exit;
  }

require_once("auth.php");






if($_POST)
{
 //	print_a($_POST);
//	exit;
	$e107cache->clear_sys("menus_");
}



		if ($message != "")
		{
			echo $ns -> tablerender('Updated', "<div style='text-align:center'><b>".$message."</b></div><br /><br />");
		}


		if (strpos(e_QUERY, 'configure') === FALSE)
		{
			$cnt = $sql->db_Select("menus", "*", "menu_location > 0 AND menu_layout = '$curLayout' ORDER BY menu_name "); // calculate height to remove vertical scroll-bar.
			$text = "<iframe id='menu_iframe' src='".e_SELF."?configure.$curLayout' width='100%' style='width: 100%; height: ".(($cnt*90)+600)."px; border: 0px' frameborder='0' scrolling='auto' ></iframe>";
		   	$ns -> tablerender(MENLAN_35, $text, 'menus_config');
		}
		else // Within the IFrame.
		{
            if($menu->debug)
			{
        		echo "<div>
                e_QUERY = ".e_QUERY."<br />
				curLayout = ".$men->curLayout."<br />
				dbLayout   = ".$men->dbLayout."<br />
				</div>";
            }

		    echo $men->menuSelectLayout();
		  	$men->menuRenderPage();

		}

// -----------------------------------------------------------------------------

class menuManager{


        var $menu_areas = array();
        var $curLayout;
        var $menuId;
		var $menuNewLoc;
		var $dragDrop;
		var $menuActivateLoc;
        var $menuActivateIds;
		var $debug;

		function menuManager($dragdrop=FALSE)
		{
        		global $pref, $HEADER,$FOOTER, $NEWSHEADER;
                $this->debug = FALSE;
                $this->dragDrop = $dragdrop;

				
				if($this->dragDrop)
				{
                	$this->debug = TRUE;
				}

                if ($NEWSHEADER)
				{
					$HEADER .= $NEWSHEADER;
				}


                if(isset($_POST['custom_select']))
				{
					$this->curLayout =  $_POST['custom_select'];
				}
				else
				{
                    $tmp = explode('.', e_QUERY);
					$this->curLayout = ($tmp[1]) ? $tmp[1] : $pref['sitetheme_deflayout'];
				}

				$this->dbLayout = ($this->curLayout !=$pref['sitetheme_deflayout']) ? $this->curLayout : "";  //menu_layout is left blank when it's default.






				$this->menuGrabLayout();

	        	$menu_array = $this->parseheader($HEADER.$FOOTER, 'check');

				sort($menu_array, SORT_NUMERIC);
				$menu_check = 'set';
				foreach ($menu_array as $menu_value)
				{
					if ($menu_value != $menu_check)
					{
				   		$this->menu_areas[] = $menu_value;
					}
					$menu_check = $menu_value;
				}


				$this->menuModify();

            	if($_POST['menuActivate'])
				{
                    $this->menuActivateLoc = key($_POST['menuActivate']);
					$this->menuActivateIds = $_POST['menuselect'];
					$this->menuActivate();

				}

				if($_POST['menuSetCustomPages'])
				{
					$this->menuSetCustomPages($_POST['custompages']);
				}

				if(isset($_POST['menuUsePreset']) && $_POST['curLayout'])
				{

					$this->menuSetPreset();
				}

		}

    // -------------------------------------------------------------------------

    function menuGrabLayout()
	{
			global $HEADER,$FOOTER,$CUSTOMHEADER,$CUSTOMFOOTER;

            	if(($this->curLayout == 'legacyCustom' || $this->curLayout=='legacyDefault') && (isset($CUSTOMHEADER) || isset($CUSTOMFOOTER)) )  // 0.6 themes.
				{
				 	if($this->curLayout == 'legacyCustom')
					{
						$HEADER = ($CUSTOMHEADER) ? $CUSTOMHEADER : $HEADER;
						$FOOTER = ($CUSTOMFOOTER) ? $CUSTOMFOOTER : $FOOTER;
					}
				}
				elseif($this->curLayout && $this->curLayout != "legacyCustom" && (isset($CUSTOMHEADER[$this->curLayout]) || isset($CUSTOMHEADER[$this->curLayout]))) // 0.7 themes
				{
				 // 	echo " MODE 0.7";
					$HEADER = ($CUSTOMHEADER[$this->curLayout]) ? $CUSTOMHEADER[$this->curLayout] : $HEADER;
					$FOOTER = ($CUSTOMFOOTER[$this->curLayout]) ? $CUSTOMFOOTER[$this->curLayout] : $FOOTER;
				}
			    elseif($this->curLayout && isset($HEADER[$this->curLayout]) && isset($FOOTER[$this->curLayout])) // 0.8 themes - we use only $HEADER and $FOOTER arrays.
				{
				//  echo " MODE 0.8";
					$HEADER = $HEADER[$this->curLayout];
					$FOOTER = $FOOTER[$this->curLayout];
				}

            // Almost the same code as found in templates/header_default.php  ---------

	}



	// -----------------------------------------------------------------------------

	    function menuModify()
		{
			global $pref,$sql,$admin_log,$ns;

	        if (isset($_POST['menuAct']))
			{
				  foreach ($_POST['menuAct'] as $k => $v)
				  {
					if (trim($v))
					{
					  $this->menuId = $k;
					  list($menu_act, $location, $position, $this->menuNewLoc) = explode(".", $_POST['menuAct'][$k]);
					}
				  }
			}

			if ($menu_act == 'config')
			{
					if($this->menuNewLoc)
					{
						$this->menuNewLoc = ".".$this->menuNewLoc;
					}
					$newurl = $PLUGINS_DIRECTORY.$location."/{$position}{$this->menuNewLoc}.php";
					$newurl = SITEURL.str_replace("//", "/", $newurl);
					echo "<script type='text/javascript'>	top.location.href = '{$newurl}'; </script> ";
					exit;
			}





			if ($menu_act == "sv")
			{
				$this->menuSaveVisibility();

			}

			if ($menu_act == "adv")
			{
				$this->menuVisibilityOptions();
			}

			if ($menu_act == "move")
			{
			 	$this->menuMove();
			}

			if ($menu_act == "deac")
			{
			 	$this->menuDeactivate();
			}

			if ($menu_act == "bot")
			{
				$menu_count = $sql->db_Count("menus", "(*)", " WHERE menu_location='{$location}' AND menu_layout = '".$this->dbLayout."'  ");
				$sql->db_Update("menus", "menu_order=".($menu_count+1)." WHERE menu_order='{$position}' AND menu_location='{$location}' AND menu_layout = '$this->dbLayout'  ");
				$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_location='{$location}' AND menu_order > {$position} AND menu_layout = '".$this->dbLayout."' ");
				$admin_log->log_event('MENU_06',$location.'[!br!]'.$position.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			}

			if ($menu_act == "top")
			{
				$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_location='{$location}' AND menu_order < {$position} AND menu_layout = '".$this->dbLayout."' ",$this->debug);
				$sql->db_Update("menus", "menu_order=1 WHERE menu_id='{$this->menuId}' ");
				$admin_log->log_event('MENU_05',$location.'[!br!]'.$position.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			}

			if ($menu_act == "dec")
			{
				$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_order='".($position+1)."' AND menu_location='{$location}' AND menu_layout = '".$this->dbLayout."' ",$this->debug);
				$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_id='{$this->menuId}' AND menu_location='{$location}' AND menu_layout = '".$this->dbLayout."' ",TRUE);
				$admin_log->log_event('MENU_08',$location.'[!br!]'.$position.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			}

			if ($menu_act == "inc")
			{
				$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_order='".($position-1)."' AND menu_location='{$location}' AND menu_layout = '".$this->dbLayout."' ",$this->debug);
				$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_id='{$this->menuId}' AND menu_location='{$location}' AND menu_layout = '".$this->dbLayout."' ");
				$admin_log->log_event('MENU_07',$location.'[!br!]'.$position.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			}

			if (strpos(e_QUERY, 'configure') === FALSE)
			{  // Scan plugin directories to see if menus to add
			    $this->menuScanMenus();
			}
		}






	// ----------------------------------------------------------------------------

	function menuSetPreset()
	{
		global $pref,$sql,$location,$admin_log;

	    $menuAreas = $this->getMenuPreset();

	    $sql->db_Update("menus", "menu_location='0' WHERE menu_layout = '".$this->dbLayout."' "); // Clear All existing.
		foreach($menuAreas as $val)
		{

			if($sql->db_Select("menus", 'menu_name, menu_path' , "menu_name = '".$val['menu_name']."' LIMIT 1"))
			{
				$row=$sql->db_Fetch();

	        	if(!$sql->db_Update('menus', "menu_order='{$val['menu_order']}', menu_location = ".$val['menu_location'].", menu_class= ".$val['menu_class']." WHERE menu_name='".$val['menu_name']."' AND menu_layout = '".$this->dbLayout."' LIMIT 1 "))
				{
	 				$qry = "
					INSERT into #menus
					(`menu_name`, `menu_location`, `menu_order`, `menu_pages`,`menu_class`, `menu_path`, `menu_layout`)
					VALUES ('{$val['menu_name']}', {$val['menu_location']}, {$val['menu_order']}, '', '{$val['menu_class']}',  '{$row['menu_path']}', '".$this->dbLayout."')
					";
					$sql->db_Select_gen($qry,$this->debug);
				  	$admin_log->log_event('MENU_01',$row['menu_name'].'[!br!]'.$location.'[!br!]'.$menu_count.'[!br!]'.$row['menu_path'],E_LOG_INFORMATIVE,'');

				}
	         }
		}

	}


	// ----------------------------------------------------------------------------

	function menuScanMenus()
	{
		global $sql, $sql2;

			$efile = new e_file;
			$efile->dirFilter = array('/', 'CVS', '.svn', 'languages');
			$fileList = $efile->get_files(e_PLUGIN,"_menu\.php$",'standard',2);
			foreach($fileList as $file)
			{
			  list($parent_dir) = explode('/',str_replace(e_PLUGIN,"",$file['path']));
			  $file['path'] = str_replace(e_PLUGIN,"",$file['path']);
			  $file['fname'] = str_replace(".php","",$file['fname']);
			  $valid_menu = FALSE;
			  $existing_menu = $sql->db_Count("menus", "(*)", "WHERE menu_name='{$file['fname']}'");
			  if (file_exists(e_PLUGIN.$parent_dir."/plugin.xml") ||
				file_exists(e_PLUGIN.$parent_dir."/plugin.php"))
			  {
		//		include(e_PLUGIN.$parent_dir."/plugin.php");
				if (isset($pref['plug_installed'][$parent_dir]))
		//		if ($sql->db_Select("plugin", "*", "plugin_path='".$eplug_folder."' AND plugin_installflag='1' "))
				{  // Its a 'new style' plugin with a plugin.php file, or an even newer one with plugin.xml file - only include if plugin installed
				  $valid_menu = TRUE;		// Whether new or existing, include in list
				}
			  }
			  else
			  {  // Just add the menu anyway
				  $valid_menu = TRUE;
			  }
			  if ($valid_menu)
			  {
			    $menustr .= "&".str_replace(".php", "", $file['fname']);
				if (!$existing_menu)
				{  // New menu to add to list
					  if($sql->db_Insert("menus", " 0, '{$file['fname']}', 0, 0, 0, '' ,'{$file['path']}', ''"))
					  {
					  		// Could do admin logging here - but probably not needed
					  	$message .= "<b>".MENLAN_10." - ".$file['fname']."</b><br />";
					  }
				}
			  }
			}

			//Reorder all menus into 1...x order
			if (!is_object($sql2)) $sql2 = new db;		// Shouldn't be needed
			foreach ($this->menu_areas as $menu_act)
			{
				if ($sql->db_Select("menus", "menu_id", "menu_location={$menu_act} ORDER BY menu_order ASC"))
				{
					$c = 1;
					while ($row = $sql->db_Fetch())
					{
						$sql2->db_Update("menus", "menu_order={$c} WHERE menu_id=".$row['menu_id']);
						$c++;
					}
				}
			}

			$sql->db_Select("menus", "*", "menu_path NOT REGEXP('[0-9]+') ");
			while (list($menu_id, $menu_name, $menu_location, $menu_order) = $sql->db_Fetch(MYSQL_NUM))
			{
				if (stristr($menustr, $menu_name) === FALSE)
				{
					$sql2->db_Delete("menus", "menu_name='$menu_name'");
					$message .= "<b>".MENLAN_11." - ".$menu_name."</b><br />";
				}
			}

			echo $message;
	}

	// ---------------------------------------------------------------------------

	function menuVisibilityOptions()
	{
		global $sql,$ns;
		require_once(e_HANDLER."userclass_class.php");
		$sql->db_Select("menus", "*", "menu_id=".$this->menuId);
		$row = $sql->db_Fetch();
		$listtype = substr($row['menu_pages'], 0, 1);
		$menu_pages = substr($row['menu_pages'], 2);
		$menu_pages = str_replace("|", "\n", $menu_pages);

		$text = "<div style='text-align:center;'>
		<form  method='post' action='".e_SELF."?configure.".$this->curLayout."'>
		<table style='width:40%'>
		<tr>
		<td>
		<input type='hidden' name='menuAct[{$row['menu_id']}]' value='sv.{$row['menu_id']}' />
		".MENLAN_4." ".
		r_userclass('menu_class', $row['menu_class'], "off", "public,member,guest,admin,main,classes,nobody")."
		</td>
		</tr>
		<tr><td><br />";
		$checked = ($listtype == 1) ? " checked='checked' " : "";
		$text .= "<input type='radio' {$checked} name='listtype' value='1' /> ".MENLAN_26."<br />";
		$checked = ($listtype == 2) ? " checked='checked' " : "";
		$text .= "<input type='radio' {$checked} name='listtype' value='2' /> ".MENLAN_27."<br /><br />".MENLAN_28."<br />";
		$text .= "<textarea name='pagelist' cols='60' rows='10' class='tbox'>$menu_pages</textarea>";
		$text .= "
		<tr>
		<td style='text-align:center'><br />
		<input class='button' type='submit' name='class_submit' value='".MENLAN_6."' />
		</td>
		</tr>
		</table>
		</form>
		</div>";
		$caption = MENLAN_7." ".$row['menu_name'];
		$ns->tablerender($caption, $text);
	}



	// -----------------------------------------------------------------------------


	function menuActivate()    // Activate Multiple Menus.
	{
		global $sql, $admin_log, $pref;

		$location = $this->menuActivateLoc;

		$menu_count = $sql->db_Count("menus", "(*)", " WHERE menu_location=".$location." AND menu_layout = '".$this->dbLayout."' ");

		foreach($this->menuActivateIds as $sel_mens)
		{
			//Get info from menu being activated
			if($sql->db_Select("menus", 'menu_name, menu_path' , "menu_id = ".$sel_mens." "))
			{
				$row=$sql->db_Fetch();
				//If menu is not already activated in that area, add the record.

				$query = "SELECT menu_name,menu_path FROM #menus WHERE menu_name='".$row['menu_name']."' AND menu_layout = '".$this->dbLayout."' AND menu_location = ".$location." LIMIT 1 ";

				if(!$sql->db_Select_gen($query, $this->debug))
				{
					$qry = "
					INSERT into #menus
					(`menu_name`, `menu_location`, `menu_order`, `menu_pages`, `menu_path`, `menu_layout`)
					VALUES ('{$row['menu_name']}', {$location}, {$menu_count}, '', '{$row['menu_path']}', '".$this->dbLayout."')
					";
					$sql->db_Select_gen($qry,$this->debug);
					$admin_log->log_event('MENU_01',$row['menu_name'].'[!br!]'.$location.'[!br!]'.$menu_count.'[!br!]'.$row['menu_path'],E_LOG_INFORMATIVE,'');
					$menu_count++;
				}
			}
		}
	}



	// -----------------------------------------------------------------------------


	function menuSetCustomPages($array)
	{
		global $pref;
		$key = key($array);
		$pref['sitetheme_custompages'][$key] = array_filter(explode(" ",$array[$key]));
		save_prefs();
	}


	// ------------------------------------------------------------------------------

	function getMenuPreset()
	{
		global $pref;

		$layout = $this->curLayout;
	 //	print_a($pref['sitetheme_layouts'][$layout]['menuPresets']);
	    if(!isset($pref['sitetheme_layouts'][$layout]['menuPresets']))
		{
	    	return;
		}

		$temp = $pref['sitetheme_layouts'][$layout]['menuPresets']['area'];

		foreach($temp as $key=>$val)
		{
			$iD = $val['@attributes']['id'];
			foreach($val['menu'] as $k=>$v)
			{
				$uclass = (defined(trim($v['@attributes']['userclass']))) ? constant(trim($v['@attributes']['userclass'])) : 0;
				$menuArea[] = array(
					'menu_location' => $iD,
					'menu_order'	=> $k,
					'menu_name'		=> $v['@attributes']['name']."_menu",
					'menu_class'	=> intval($uclass)
				);
			}
		}

	    return $menuArea;

	}


	// ------------------------------------------------------------------------------

	function checkMenuPreset($array,$name)
	{
		if(!is_array($array))
		{
	    	return;
		}
		foreach($array as $key=>$val)
		{
	        if($val['menu_name']==$name)
			{
				return $val['menu_location'];
			}
		}

	    return FALSE;
	}



	// --------------------------------------------------------------------------

	function menuSaveVisibility()
	{
		global $sql, $admin_log;
		$pagelist = explode("\r\n", $_POST['pagelist']);
		for ($i = 0 ; $i < count($pagelist) ; $i++)
		{
			$pagelist[$i] = trim($pagelist[$i]);
		}
		$plist = implode("|", $pagelist);
		$pageparms = $_POST['listtype'].'-'.$plist;
		$pageparms = preg_replace("#\|$#", "", $pageparms);
		$pageparms = (trim($_POST['pagelist']) == '') ? '' : $pageparms;

		if($sql->db_Update("menus", "menu_class='".$_POST['menu_class']."', menu_pages='{$pageparms}' WHERE menu_id=".intval($this->menuId)))
		{
			$admin_log->log_event('MENU_02',$_POST['menu_class'].'[!br!]'.$pageparms.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			$message = "<br />".MENLAN_8."<br />";
		}
		else
		{
	     	$message = "the update failed";

		}

	    echo $message;

	}



	// -----------------------------------------------------------------------

	function menuDeactivate()
	{	// Get current menu name
		global $sql,$admin_log;

		if($sql->db_Select('menus', 'menu_name', 'menu_id='.$this->menuId, 'default'))
		{

			$row = $sql->db_Fetch();
			//Check to see if there is already a menu with location = 0 (to maintain BC)
			if($sql->db_Select('menus', 'menu_id', "menu_name='{$row['menu_name']}' AND menu_location = 0 AND menu_layout ='".$this->dbLayout."' LIMIT 1"))
			{
				//menu_location=0 already exists, we can just delete this record
				$sql->db_Delete('menus', 'menu_id='.$this->menuId);
			}
			else
			{
				//menu_location=0 does NOT exist, let's just convert this to it
				if(!$sql->db_Update("menus", "menu_location=0, menu_order=0, menu_class=0, menu_pages='' WHERE menu_id=".$this->menuId))
				{
	            	$message = "FAILED";
				}
			}
			//Move all other menus up
			$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_location={$location} AND menu_order > {$position} AND menu_layout = '".$this->dbLayout."' ");
			$admin_log->log_event('MENU_04',$row['menu_name'].'[!br!]'.$location.'[!br!]'.$position.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
		}

		echo $message;
	}


	// ----------------------------------------------------------------------

	function menuMove()
	{// Get current menu name

			global $admin_log,$sql;

			if($sql->db_Select('menus', 'menu_name', 'menu_id='.$this->menuId, 'default'))
			{
				$row = $sql->db_Fetch();
				//Check to see if menu is already active in the new area, if not then move it
				if(!$sql->db_Select('menus', 'menu_id', "menu_name='{$row['menu_name']}' AND menu_location = ".$this->menuNewLoc." AND menu_layout='".$this->dbLayout ."' LIMIT 1"))
				{
					$menu_count = $sql->db_Count("menus", "(*)", " WHERE menu_location=".$this->menuNewLoc);
					$sql->db_Update("menus", "menu_location='{$this->menuNewLoc}', menu_order=".($menu_count+1)." WHERE menu_id=".$this->menuId);
					$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_location='{$location}' AND menu_order > {$position} AND menu_layout='".$this->dbLayout ."' ");
				}
				$admin_log->log_event('MENU_03',$row['menu_name'].'[!br!]'.$this->menuNewLoc.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			}
	}


	// =-----------------------------------------------------------------------------


	function menuRenderPage()
	{
		global $sql, $ns, $HEADER, $FOOTER, $frm, $pref, $tp;

		echo "<div id='portal'>";
		$this->parseheader($HEADER);  // $layouts_str;

		$layout = ($this->curLayout);
		$menuPreset = $this->getMenuPreset($layout);


		echo "<div style='text-align:center'>";
		echo $frm->form_open("post", e_SELF."?configure.".$this->curLayout, "menuActivation");
		$text = "<table style='width:80%;margin-left:auto;margin-right:auto'>";


		$text .= "<tr><td style='width:50%;text-align:center;padding-bottom:4px'>".MENLAN_36."...</td><td style='width:50%;padding-bottom:4px;text-align:center'>...".MENLAN_37."</td></tr>";
		$text .= "<tr><td style='width:50%;vertical-align:top;text-align:center'>";

	 	$sql->db_Select("menus", "menu_name, menu_id, menu_pages", "1 GROUP BY menu_name ORDER BY menu_name ASC");

		if(!$this->dragDrop)
		{
			$text .= "<div class='portal-column' id='portal-column-block-list' style='border:1px inset black;height:200px;display:block;overflow:auto;margin-bottom:20px'>";
			$text .= "<table id='core-menumanager-main' style='width:100%;margin-left:auto;margin-right:auto' cellspacing='0' cellpadding='0'>\n";

		}
		else
		{
        	$text .= "<div class='portal-column' id='portal-column-block-list' style='border:1px solid silver'>\n";
		}


		while ($row = $sql->db_Fetch())
		{
			$pdeta = "";
	        $color = ($color == "white") ? "#DDDDDD" : "white";
			if($row['menu_pages'] == "dbcustom")
			{
				$pdeta = MENLAN_42;
			}
			else
			{
				$row['menu_name'] = preg_replace("#_menu$#i", "", $row['menu_name']);
	            if($pnum = $this->checkMenuPreset($menuPreset,$row['menu_name']."_menu"))
				{
		        	$pdeta = MENLAN_39." {$pnum}";
				}
			}

	        if(!$this->dragDrop)
			{
	    		$text .= "<tr style='background-color:$color;color:black'>
				<td style='text-align:left; color:black;'><input type='checkbox' name='menuselect[]' value='{$row['menu_id']}' />".$row['menu_name']."</td>
	            <td style='color:black'> ".$pdeta."&nbsp;</td>
				</tr>\n";
	        }
			else
			{
	            $text .= "<div class='block block-archive' id='block-archive-".$row['menu_id']."' style='border:1px outset black;text-align:left;color:black'>

				<div class='block-toggle'><input type='checkbox' name='menuselect[]' value='{$row['menu_id']}' />".$row['menu_name']."  $pdeta</div>
	            <div class='content'>";
			 	$text .= $this->menuRenderMenu($row,$menu_count);
	  			$text .= "</div></div>\n";
			}
		}
		$text .= (!$this->dragDrop) ? "</table>" : "";
		$text .= "</div>";

		$text .= "</td><td style='width:50%;vertical-align:top;text-align:center'><br />";
		foreach ($this->menu_areas as $menu_act)
		{
			$text .= "<input type='submit' class='button' id='menuActivate_".trim($menu_act)."' name='menuActivate[".trim($menu_act)."]' value='".MENLAN_13." ".trim($menu_act)."' /><br /><br />\n";
		}


	    if($layout)
		{
			if(isset($pref['sitetheme_layouts'][$layout]['menuPresets']))
			{
		    	$text .= "<input type='submit' class='button' name='menuUsePreset' value=\"".MENLAN_40."\" onclick=\"return jsconfirm('".$tp->toJS(MENLAN_41)."')\" /><br /><br />\n";  // Use Menu Presets
				$text .= "<input type='hidden' name='menuPreset' value='".$layout."' />";
			}
			$text .= "<input type='hidden' name='curLayout' value='".$layout."' />";
	    }

		$text .= "</td>";

		$text .= "</tr></table>";
		$ns -> tablerender(MENLAN_22, $text);
        echo $frm->form_close();
		echo "</div>";



		$this->parseheader($FOOTER);
		if($this->debug)
		{
	    	echo "<div id='debug' style='margin-left:0px;border:1px solid silver; overflow:scroll;height:250px'> &nbsp;</div>";
        }
		echo "</div>";
	}





	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function menuSelectLayout()
	{
		global $frm, $pref;

		$text .= "<form  method='post' action='".e_SELF."?configure.".$this->curLayout."'>";
		$text .= "<div style='color:white;background-color:black;width:98%;display:block;padding:15px;text-align:center'>".MENLAN_30." ";
	    $text .= "<select style='color:black' name='custom_select' onchange=\"this.form.submit();\">\n";  // window.frames['menu_iframe'].location=this.options[selectedIndex].value ???

	    $search = array("_","legacyDefault","legacyCustom");
		$replace = array(" ",MENLAN_31,MENLAN_33);


	    foreach($pref['sitetheme_layouts'] as $key=>$val)
		{
			$layoutName = str_replace($search,$replace,$key);
			$layoutName .=($key==$pref['sitetheme_deflayout']) ? " (".MENLAN_31.")" : "";
			$selected = ($this->curLayout == $key || ($key==$pref['sitetheme_deflayout'] && $this->curLayout=='')) ? "selected='selected'" : FALSE;

         //   $val = ".e_SELF."?configure.".";
			$text .= "<option value='$key' {$selected}>".$layoutName."</option>";
		}

	    $text .= "</select>
		</div></form>";
		  return $text;
	}

	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function parseheader($LAYOUT, $check = FALSE)
	{
		//  $tmp = explode("\n", $LAYOUT);
		// Split up using the same function as the shortcode handler
		  $tmp = preg_split('#(\{\S[^\x02]*?\S\})#', $LAYOUT, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		  for ($c = 0; $c < count($tmp); $c++)
		  {
			if (preg_match("/[\{|\}]/", $tmp[$c]))
			{
			  if ($check)
			  {
					if (strstr($tmp[$c], "{MENU="))
					{
					  $str[] = preg_replace("/\{MENU=(.*?)(:.*?)?\}/si", "\\1", $tmp[$c]);
					}
			  }
			  else
			  {
				$this->checklayout($tmp[$c],$this->curLayout);
			  }
			}
			else
			{
			  if (!$check)
			  {
				echo $tmp[$c];
			  }
			}
		  }
		  if ($check)
		  {
			return $str;
		  }
	}

	function checklayout($str)
	{	// Displays a basic representation of the theme
		global $pref, $ns, $PLUGINS_DIRECTORY, $frm, $sc_style, $tp, $menu_order;

	    $menuLayout = ($this->curLayout != $pref['sitetheme_deflayout']) ? $this->curLayout : "";

		if (strstr($str, "LOGO"))
		{
		  echo $tp -> parseTemplate("{LOGO}");
		}
		else if(strstr($str, "SITENAME"))
		{
		  echo "<div style='padding: 2px'>[SiteName]</div>";
		}
		else if (strstr($str, "SITETAG"))
		{
		  echo "<div style='padding: 2px'>[SiteTag]</div>";
		}
		else if (strstr($str, "SITELINKS"))
		{
		  echo "<div style='padding: 2px; text-align: center'>[SiteLinks]</div>";
		}
		else if (strstr($str, "LANGUAGELINKS"))
		{
		  echo "<div class=text style='padding: 2px; text-align: center'>[Language]</div>";
		}
		else if (strstr($str, "CUSTOM"))
		{
		  $cust = preg_replace("/\W*\{CUSTOM=(.*?)(\+.*)?\}\W*/si", "\\1", $str);
		  echo "<div style='padding: 2px'>[".$cust."]</div>";
		}
		// Display embedded Plugin information.
		else if (strstr($str, "PLUGIN"))
		{
			$plug = preg_replace("/\{PLUGIN=(.*?)\}/si", "\\1", $str);
			$plug = trim($plug);
			if (file_exists((e_PLUGIN."{$plug}/{$plug}_config.php")))
			{
			  $link = e_PLUGIN."{$plug}/{$plug}_config.php";
			}

			if(file_exists((e_PLUGIN.$plug."/config.php")))
			{
			  $link = e_PLUGIN.$plug."/config.php";
			}

			$plugtext = ($link) ? "(".MENLAN_34.":<a href='$link' title='".MENLAN_16."'>".MENLAN_16."</a>)" : "(".MENLAN_34.")" ;
			echo "<br />";
			$ns -> tablerender($plug, $plugtext);
		}
		else if (strstr($str, "MENU"))
		{
		  //	$ns = new e107table;
			$menu = preg_replace("/\{MENU=(.*?)(:.*?)?\}/si", "\\1", $str);
			if (isset($sc_style['MENU']['pre']) && strpos($str, 'ret') !== false)
			{
			  echo $sc_style['MENU']['pre'];
			}
			echo "
	        <div class='portal-column' id='portal-column-".$menu."'>

			<div style='text-align:center; font-size:14px' class='fborder'>

			<div class='forumheader'><b>".MENLAN_14."  ".$menu."</b></div></div><br />";
			$text = "&nbsp;";
			$sql9 = new db;
			if ($sql9->db_Count("menus", "(*)", " WHERE menu_location='$menu' AND menu_layout = '".$this->dbLayout."' "))
			{
				unset($text);
				echo $frm->form_open("post", e_SELF."?configure.".$this->curLayout, "frm_menu_".intval($menu));

	            $MODE = 1;

				$sql9->db_Select("menus", "*", "menu_location='$menu' AND menu_layout='".$this->dbLayout."' ORDER BY menu_order");
				$menu_count = $sql9->db_Rows();
				while ($row = $sql9->db_Fetch(MYSQL_ASSOC))
				{
	                    	echo "\n\n\n <!-- Menu Start -->\n\n
					<div class='block' id='block-".$row['menu_id']."'>

					<div class='content'>";

						 echo $this->menuRenderMenu($row,$menu_count);

	                echo "\n</div></div>";
					echo "\n\n\n<!-- Menu end -->\n\n\n";
					echo "<div><br /></div>";


				}

				echo $frm->form_close();
			}
			echo "</div>";
			if(isset($sc_style['MENU']['post']) && strpos($str, 'ret') !== false) {
				echo $sc_style['MENU']['post'];
			}
		}
		else if (strstr($str, "SETSTYLE")) {
			$tmp = explode("=", $str);
			$style = preg_replace("/\{SETSTYLE=(.*?)\}/si", "\\1", $str);
		}
		else if (strstr($str, "SITEDISCLAIMER")) {
			echo "[Sitedisclaimer]";
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

	function menuRenderMenu($row,$menu_count )
	{
		global $ns,$frm,$menu,$menu_info,$menu_act;
	                              //      $menu_count is empty in here
		extract($row);
		if(!$menu_id){ return; }

					$menu_name = preg_replace("#_menu#i", "", $menu_name);
					$vis = ($menu_class || strlen($menu_pages) > 1) ? " <span style='color:red'>*</span> " : "";
					$caption = "<div style='text-align:center'>{$menu_name}{$vis}</div>";
					$menu_info = "{$menu_location}.{$menu_order}";

					$text = "";
					$conf = '';
					if (file_exists(e_PLUGIN."{$menu_path}/{$menu_name}_menu_config.php"))
					{
					  $conf = "config.{$menu_path}.{$menu_name}_menu_config";
					}

					if($conf == '' && file_exists(e_PLUGIN."{$menu_path}/config.php"))
					{
					  $conf = "config.{$menu_path}.config";
					}

					$text .= "<select id='menuAct_".$menu_id."' name='menuAct[$menu_id]' class='tbox' onchange='this.form.submit()' >";
					$text .= $frm->form_option(MENLAN_25, TRUE, " ");
					$text .= $frm->form_option(MENLAN_15, "", "deac.{$menu_info}");

					if ($conf) {
						$text .= $frm->form_option(MENLAN_16, "", $conf);
					}

					if ($menu_order != 1) {
						$text .= $frm->form_option(MENLAN_17, "", "inc.{$menu_info}");
						$text .= $frm->form_option(MENLAN_24, "", "top.{$menu_info}");
					}
					if ($menu_count != $menu_order) {
						$text .= $frm->form_option(MENLAN_18, "", "dec.{$menu_info}");
						$text .= $frm->form_option(MENLAN_23, "", "bot.{$menu_info}");
					}
					foreach ($this->menu_areas as $menu_act) {
						if ($menu != $menu_act) {
							$text .= $frm->form_option(MENLAN_19." ".$menu_act, "", "move.{$menu_info}.".$menu_act);
						}
					}

					$text .= $frm->form_option(MENLAN_20, "", "adv.{$menu_info}");
					$text .= $frm->form_select_close();

					if($this->dragDrop)
					{

				 		$text .= "<div class=\"block-controls\">";
	               		$text .= "<a class=\"block-remove\"><span>x</span></a> <a class=\"block-config\"><span>e</span></a></div>";
	                    $text .= "	<div class=\"config\" style=\"display: none; width: 200px;\">
							<div>config-params</div>
							<div style='float:right'>
								<a href=\"#\" class=\"cancel-button\">cancel</a>
								<a href=\"#\" class=\"save-button\">save</a>
							</div>
						</div>";
	                }

					ob_start();

					$ns->tablerender($caption, $text);
					$THEX = ob_get_contents();

					ob_end_clean();
					return $THEX;

	}

	function menuSaveAjax()
	{
        if(!$this->dragDrop){ return; }

	    global $sql;
	    $query = explode(':', $_POST['value']);
		$loc = intval(str_replace('portal-column-','',$query[0]));

		$srch = array('block-archive-','block-');
		$repl = array('','');
		$blocks = str_replace($srch,$repl,$query[1]);

	    $tmp = explode(',',$blocks);

		$this->menuActivateLoc = $loc;  // location
		$this->menuActivateIds = $tmp;  // array of ids, in order.
		$this->menuActivate();

		echo "<hr />";


	}
}  // end of Class.

require_once("footer.php");

 // -----------------------------------------------------------------------

function headerjs()
{
	global $sql,$pref,$men;

    if(!$men->dragDrop)
	{
    	return;
	}


    $tmp = explode('.', e_QUERY);
	$curLayout = ($tmp[1]) ? $tmp[1] : $pref['sitetheme_deflayout'];
	$dbLayout = ($curLayout !=$pref['sitetheme_deflayout']) ? $curLayout : "";



    if(strpos(e_QUERY, 'configure') !== FALSE )
	{

   		$ret = "

		<!-- load prototype and scriptaculous -->
		<script type=\"text/javascript\" src=\"http://www.google.com/jsapi\"></script>
		<script type=\"text/javascript\">
  			google.load(\"prototype\", \"1.6.0.3\");
  			google.load(\"scriptaculous\", \"1.8.2\");
		</script>

		<!-- load the portal script -->
		<script type=\"text/javascript\" src=\"".e_FILE_ABS."jslib/portal/portal.js\"></script>
	  	<link href=\"".e_FILE_ABS."jslib/portal/portal.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />

		<!-- initiate the portal -->
		<script type=\"text/javascript\">
			var settings = {

            ";


                     //   ," menu_location !=0 AND menu_layout = '{$curLayout}' ORDER BY menu_location,menu_order"
            $sql -> db_Select("menus", "*"," menu_location !=0 AND menu_layout = '".$dbLayout."' ORDER BY menu_location,menu_order");
            while($row = $sql-> db_Fetch())
            {

				$portal[$row['menu_location']][] = "'block-".$row['menu_id']."'";
        	}

			 foreach($portal as $col=>$val)
			 {
             	$ret .= "\t\n'portal-column-".$col."':[".implode(",",$val)."],";
			 }

      	$ret .= "
			 };
			var options = {
			 editorEnabled : true,
			  'saveurl' : '".e_SELF."?ajax=',
			  hoverclass: 'block-hover'
			 };

			var data = {  };

			var portal;

			Event.observe(window, 'load', function() {
				portal = new Portal(settings, options, data);
			}, false);

		</script>";
	}


  /*  	this.options = {
			editorEnabled 	: false,
			portal			: 'portal',
			column			: 'portal-column',
			block			: 'block',
			content			: 'content',
			configElement	: 'config',
			configSave		: 'save-button',
			configCancel	: 'cancel-button',
			handle			: 'handle',
			hoverclass		: false,
			remove			: 'block-remove',
			config			: 'block-config',
			blocklist		: 'portal-column-block-list',
			blocklistlink	: 'portal-block-list-link',
			blocklisthandle : 'block-list-handle',
			saveurl			: false
		}
*/

	return $ret;

}
?>