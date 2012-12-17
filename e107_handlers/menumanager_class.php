<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT')) { exit; }



class e_menuManager {


        var $menu_areas = array();
        var $curLayout;
        var $menuId;
		var $menuNewLoc;
		var $dragDrop;
		var $menuActivateLoc;
        var $menuActivateIds;
		var $debug;
		var $menuMessage;
		var $style = 'default';

		function __construct($dragdrop=FALSE)
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
				elseif(isset($_GET['lay']))
				{
                	$this->curLayout =  $_GET['lay'];
				}
				else
				{
					$this->curLayout = varsettrue($_GET['configure'], $pref['sitetheme_deflayout']);
				}

				$this->dbLayout = ($this->curLayout != $pref['sitetheme_deflayout']) ? $this->curLayout : "";  //menu_layout is left blank when it's default.

				if(isset($_POST['menu_id']) || vartrue($_GET['id']))
				{
                	$this->menuId = (isset($_POST['menu_id'])) ? intval($_POST['menu_id']) : intval($_GET['id']);
				}

				if (/*$menu_act == "sv" || */isset($_POST['class_submit']))
				{
					$this->menuSaveVisibility();
				}
				elseif(isset($_POST['parms_submit']))
				{
					$this->menuSaveParameters();
				}

                if (vartrue($_GET['mode']) == "deac")
				{
				 	$this->menuDeactivate();
				}

				if ($_GET['mode'] == "conf")
				{
				 	$this->menuGoConfig();
				}

				$this->menuGrabLayout();

	        	$menu_array = $this->parseheader($HEADER.$FOOTER, 'check');

				if($menu_array)
				{
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
                }

				$this->menuModify();

            	if(vartrue($_POST['menuActivate']))
				{
                    $this->menuActivateLoc = key($_POST['menuActivate']);
					$this->menuActivateIds = $_POST['menuselect'];
					$this->menuActivate();

				}

				if(vartrue($_POST['menuSetCustomPages']))
				{
					$this->menuSetCustomPages($_POST['custompages']);
				}

				if(isset($_POST['menuUsePreset']) && $_POST['curLayout'])
				{

					$this->menuSetPreset();
				}

				$this->menuSetConfigList(); // Update Active MenuConfig List.

		}

// -------------------------------------------------------------------------

	function menuRenderIframe($url='')
	{
        global $ns,$sql;
        if(!$url)
		{
        	$url = e_SELF."?configure=".$this->curLayout;
		}

		$cnt = $sql->db_Select("menus", "*", "menu_location > 0 AND menu_layout = '$curLayout' ORDER BY menu_name "); // calculate height to remove vertical scroll-bar.

		$text = "<object type='text/html' id='menu_iframe' data='".$url."' width='100%' style='overflow:auto;width: 100%; height: ".(($cnt*90)+600)."px; border: 0px' ></object>";
		
		return $text;
	}


	function menuRenderMessage()
	{
	  //	return $this->menuMessage;
	  	$emessage = eMessage::getInstance();
		$text = $emessage->render('menuUi');
	  //	$text .= "ID = ".$this->menuId;
		return $text;
		
	}


	function menuAddMessage($message, $type = E_MESSAGE_INFO, $session = false)
	{
		$emessage = eMessage::getInstance();
 		$emessage->add(array($message, 'menuUi'), $type, $session);
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
				elseif($this->curLayout && $this->curLayout != "legacyCustom" && (isset($CUSTOMHEADER[$this->curLayout]) || isset($CUSTOMFOOTER[$this->curLayout]))) // 0.7 themes
				{
				 // 	echo " MODE 0.7 ".$this->curLayout;
					$HEADER = ($CUSTOMHEADER[$this->curLayout]) ? $CUSTOMHEADER[$this->curLayout] : $HEADER;
					$FOOTER = ($CUSTOMFOOTER[$this->curLayout]) ? $CUSTOMFOOTER[$this->curLayout] : $FOOTER;
				}
			    elseif($this->curLayout && is_array($HEADER) && isset($HEADER[$this->curLayout]) && isset($FOOTER[$this->curLayout])) // 0.8 themes - we use only $HEADER and $FOOTER arrays.
				{
				//  echo " MODE 0.8 ".$this->curLayout;

					$HEADER = $HEADER[$this->curLayout];
					$FOOTER = $FOOTER[$this->curLayout];

				}

            // Almost the same code as found in templates/header_default.php  ---------

	}

    function menuGoConfig()
	{
		if(!$_GET['path'] || ($_GET['mode'] != "conf"))
		{
			return;
		}

		$file = urldecode($_GET['path']).".php";
		$newurl = e_PLUGIN_ABS.$file."?id=".$_GET['id'];

     /*



	  return "<object type='text/html' id='menu_iframe' data='".$newurl."' width='100%' style='overflow:auto;width: 100%; border: 0px' ></object>";

*/
		header("Location: ".$newurl);
		exit;
	 //	echo "URL = ".$newurl;
	  //	$newurl = $PLUGINS_DIRECTORY.$location."/{$position}{$this->menuNewLoc}.php";
	  //	$newurl = SITEURL.str_replace("//", "/", $newurl);
	  //	echo "<script type='text/javascript'>alert($newurl);	top.location.href = '{$newurl}'; </script> ";
	//	exit;


	}

	// -----------------------------------------------------------------------------

	    function menuModify()
		{
			global $pref,$sql,$admin_log,$ns;

			$menu_act = "";

	        if (isset($_POST['menuAct']))
			{
				  foreach ($_POST['menuAct'] as $k => $v)
				  {
					if (trim($v))
					{
					  $this->menuId = intval($k);
					  list($menu_act, $location, $position, $this->menuNewLoc) = explode(".", $_POST['menuAct'][$k]);
					}
				  }
			}


			if ($menu_act == "move")
			{
			 	$this->menuMove();
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

			if (!isset($_GET['configure']))
			{  // Scan plugin directories to see if menus to add
			    $this->menuScanMenus();
			}
		}






	// ----------------------------------------------------------------------------

	function menuSetPreset()
	{
		global $pref,$sql,$location,$admin_log;

	    if(!$menuAreas = $this->getMenuPreset())
		{
        	return FALSE;
		}


	    $sql->db_Update("menus", "menu_location='0' WHERE menu_layout = '".$this->dbLayout."' "); // Clear All existing.
		foreach($menuAreas as $val)
		{

			if($sql->db_Select("menus", 'menu_name, menu_path' , "menu_name = '".$val['menu_name']."' LIMIT 1"))
			{
				$row=$sql->db_Fetch();

	        	if(!$sql->db_Update('menus', "menu_order='{$val['menu_order']}', menu_location = ".$val['menu_location'].", menu_class= ".$val['menu_class']." WHERE menu_name='".$val['menu_name']."' AND menu_layout = '".$this->dbLayout."' LIMIT 1 "))
				{
                	$insert = array(
                        	'menu_id'	=> 0,
							'menu_name' 	=> $val['menu_name'],
							'menu_location'	=> $val['menu_location'],
							'menu_order'	=> $val['menu_order'],
							'menu_class'	=> $val['menu_class'],
							'menu_pages'	=> '',
                            'menu_path'		=> $row['menu_path'],
							'menu_layout'  	=> $this->dbLayout,
							'menu_parms'	=> ''
						);

					$sql->db_Insert("menus",$insert);
				  	$admin_log->log_event('MENU_01',$row['menu_name'].'[!br!]'.$location.'[!br!]'.$menu_count.'[!br!]'.$row['menu_path'],E_LOG_INFORMATIVE,'');

				}
	         }
		}

		return $menuAreas;

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
				if (file_exists(e_PLUGIN.$parent_dir.'/plugin.xml') || file_exists(e_PLUGIN.$parent_dir.'/plugin.php'))
				{
					if (e107::isInstalled($parent_dir))
					{  // Its a 'new style' plugin with a plugin.php file, or an even newer one with plugin.xml file - only include if plugin installed
						$valid_menu = TRUE;		// Whether new or existing, include in list
//						echo "Include {$parent_dir}:{$file['fname']}<br />";
					}
				}
				else  // Just add the menu anyway
				{
					$valid_menu = TRUE;
//					echo "Default Include {$parent_dir}:{$file['fname']}<br />";
				}
				if ($valid_menu)
				{
					$menustr .= "&".str_replace(".php", "", $file['fname']);
					if (!$existing_menu)  // New menu to add to list
					{
                        $insert = array(
                        	'menu_id'	=> 0,
							'menu_name' 	=> $file['fname'],
							'menu_location'	=> 0,
							'menu_order'	=> 0,
							'menu_class'	=> 0,
							'menu_pages'	=> '',
                            'menu_path'		=> $file['path'],
							'menu_layout'  	=> '',
							'menu_parms'	=> ''
						);

   						if($sql->db_Insert("menus",$insert))
						{
					  		// Could do admin logging here - but probably not needed
							$message .= MENLAN_10." - ".$file['fname']."<br />";
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
					$message .= MENLAN_11." - ".$menu_name."<br />";
				}
			}

			$this->menuAddMessage(vartrue($message), E_MESSAGE_INFO);

	}

	// ---------------------------------------------------------------------------


    function menuPresetPerms($val)
	{
		$link_class = strtolower(trim($val));
   		$menu_perm['everyone'] = e_UC_PUBLIC;
		$menu_perm['guest'] = e_UC_GUEST;
	  	$menu_perm['member'] = e_UC_MEMBER;
		$menu_perm['mainadmin'] = e_UC_MAINADMIN;
		$menu_perm['admin'] = e_UC_ADMIN;
		$menu_perm['nobody'] = e_UC_NOBODY;
		$link_class = ($menu_perm[$link_class]) ? $menu_perm[$link_class] : e_UC_PUBLIC;

		return $link_class;
	}

	/**
	 * This one will be greatly extended, allowing menus to offer UI and us 
	 * settings per instance later ($parm variable available for menus - same as shortcode's $parm)
	 */
	function menuInstanceParameters()
	{
		if(!vartrue($_GET['parmsId'])) return;
		$id = intval($_GET['parmsId']);
		$frm = e107::getForm();
		$sql = e107::getDb();
		
		if(!$sql->db_Select("menus", "*", "menu_id=".$id))
		{
        	$this->menuAddMessage("Couldn't Load Menu",E_MESSAGE_ERROR);
            return;
		};
		$row = $sql->db_Fetch();
		
		// TODO lan
		$text = "<div style='text-align:center;'>
		<form  method='post' action='".e_SELF."?lay=".$this->curLayout."'>
        <fieldset id='core-menus-parametersform'>
		<legend>Menu parameters ".$row['menu_name']."</legend>
        <table class='adminform'>
		<tr>
		<td>
		Parameters (query string format):
		".$frm->text('menu_parms', $row['menu_parms'], 900)."
		</td>
		</tr>
		</table>
		<div class='buttons-bar center'>";
        $text .= $frm->admin_button('parms_submit', LAN_SAVE, 'update');
		$text .= "<input type='hidden' name='menu_id' value='".$id."' />
		</div>
		</fieldset>
		</form>
		</div>";
		return $text;
		//$caption = MENLAN_7." ".$row['menu_name'];
		//$ns->tablerender($caption, $text);
	}


	function menuVisibilityOptions()
	{
		if(!vartrue($_GET['vis'])) return;

		global $sql,$ns,$frm;
		require_once(e_HANDLER."userclass_class.php");
		if(!$sql->db_Select("menus", "*", "menu_id=".intval($_GET['vis'])))
		{
        	$this->menuAddMessage("Couldn't Load Menu",E_MESSAGE_ERROR);
            return;
		};
		$row = $sql->db_Fetch();
		$listtype = substr($row['menu_pages'], 0, 1);
		$menu_pages = substr($row['menu_pages'], 2);
		$menu_pages = str_replace("|", "\n", $menu_pages);

		$text = "<div style='text-align:center;'>
		<form  method='post' action='".e_SELF."?lay=".$this->curLayout."&amp;iframe=1'>
        <fieldset id='core-menus-visibilityform'>
		<legend>". MENLAN_7." ".$row['menu_name']."</legend>
        <table class='adminform'>
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
		$text .= "</td></tr>
		</table>
		<div class='buttons-bar center'>";
		//	<input class='button' type='submit' name='class_submit' value='".MENLAN_6."' />
        $text .= $frm->admin_button('class_submit', MENLAN_6, 'update');

		$text .= "<input type='hidden' name='menu_id' value='".intval($_GET['vis'])."' />
		</div>
		</fieldset>
		</form>
		</div>";
		return $text;
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
			if($sql->db_Select("menus", 'menu_name, menu_path' , "menu_id = ".intval($sel_mens)." "))
			{
				$row=$sql->db_Fetch();
				//If menu is not already activated in that area, add the record.
				//$query = "SELECT menu_name,menu_path FROM #menus WHERE menu_name='".$row['menu_name']."' AND menu_layout = '".$this->dbLayout."' AND menu_location = ".$location." LIMIT 1 ";
				//if(!$sql->db_Select_gen($query, $this->debug))
				{

                   $insert = array(
                        	'menu_id'	=> 0,
							'menu_name' 	=> $row['menu_name'],
							'menu_location'	=> $location,
							'menu_order'	=> $menu_count,
							'menu_class'	=> $row['menu_class'],
							'menu_pages'	=> '',
                            'menu_path'		=> $row['menu_path'],
							'menu_layout'  	=> $this->dbLayout,
							'menu_parms'	=> ''
				   );

					$sql->db_Insert("menus",$insert, $this->debug);

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

	    if(!isset($pref['sitetheme_layouts'][$layout]['menuPresets']))
		{
	    	return FALSE;
		}

		$temp = $pref['sitetheme_layouts'][$layout]['menuPresets']['area'];

		foreach($temp as $key=>$val)
		{
			$iD = $val['@attributes']['id'];
			if(varset($val['menu'][1]))  // More than one menu item under <area> in theme.xml.
			{
				foreach($val['menu'] as $k=>$v)
				{
				   //	$uclass = (defined(trim($v['@attributes']['perm']))) ? constant(trim($v['@attributes']['userclass'])) : 0;
					$menuArea[] = array(
						'menu_location' => $iD,
						'menu_order'	=> $k,
						'menu_name'		=> $v['@attributes']['name']."_menu",
						'menu_class'	=> $this->menuPresetPerms($v['@attributes']['perm'])
					);
				}
			}
			else  // Only one menu item under <area> in theme.xml.
			{
			  //	$uclass = (defined(trim($val['menu']['@attributes']['userclass']))) ? constant(trim($val['menu']['@attributes']['userclass'])) : 0;
                $menuArea[] = array(
						'menu_location' => $iD,
						'menu_order'	=> 0,
						'menu_name'		=> $val['menu']['@attributes']['name']."_menu",
						'menu_class'	=> $this->menuPresetPerms($v['@attributes']['perm'])
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
	
	function menuSaveParameters()
	{
		$sql = e107::getDb();
		$parms = $sql->escape(strip_tags($_POST['menu_parms']));
		$check = $sql->db_Update("menus", "menu_parms='".$parms."' WHERE menu_id=".$this->menuId);
		
		if($check)
		{
			// FIXME - menu log
			//$admin_log->log_event('MENU_02',$_POST['menu_parms'].'[!br!]'.$parms.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			$this->menuAddMessage(LAN_SAVED,E_MESSAGE_SUCCESS);
		}
		elseif(false === $check)
		{
            $this->menuAddMessage(LAN_UPDATED_FAILED,E_MESSAGE_ERROR);
		}
		else $this->menuAddMessage(LAN_NOCHANGE_NOTSAVED,E_MESSAGE_INFO);
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
			$this->menuAddMessage($message,E_MESSAGE_SUCCESS);
		}
		else
		{
	     	$message = "the update failed";
            $this->menuAddMessage($message,E_MESSAGE_ERROR);
		}

	}



	// -----------------------------------------------------------------------

	function menuDeactivate()
	{	// Get current menu name
		global $sql,$admin_log;
		
		//echo "FOUND= ".$this->menuId;

		if($sql->db_Select('menus', 'menu_name', 'menu_id='.$this->menuId, 'default'))
		{
			
			$row = $sql->db_Fetch();
			//Check to see if there is already a menu with location = 0 (to maintain BC)
			if($sql->db_Select('menus', 'menu_id', "menu_name='{$row['menu_name']}' AND menu_location = 0 AND menu_layout ='".$this->dbLayout."' LIMIT 1"))
			{
				//menu_location=0 already exists, we can just delete this record
				if(!$sql->db_Delete('menus', 'menu_id='.$this->menuId))
				{
					$message = "Deletion Failed";
				}
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
		else
		{
			$message = "NO CHANGES MADE";	
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
		global $sql, $ns, $HEADER, $FOOTER, $rs, $pref, $tp;

		//FIXME - XHTML cleanup, front-end standards (elist, forms etc)
		echo "<div id='portal'>";
		$this->parseheader($HEADER);  // $layouts_str;
		
		$layout = ($this->curLayout);
		$menuPreset = $this->getMenuPreset($layout);


		echo "<div style='text-align:center'>";
		echo $rs->form_open("post", e_SELF."?configure=".$this->curLayout, "menuActivation");
		$text = "<table style='width:80%;margin-left:auto;margin-right:auto'>";


		$text .= "<tr><td style='width:50%;text-align:center;padding-bottom:4px'>".MENLAN_36."...</td><td style='width:50%;padding-bottom:4px;text-align:center'>...".MENLAN_37."</td></tr>";
		$text .= "<tr><td style='width:50%;vertical-align:top;text-align:center'>";

	 	$sql->db_Select("menus", "menu_name, menu_id, menu_pages, menu_path", "1 GROUP BY menu_name ORDER BY menu_name ASC");

		if(!$this->dragDrop)
		{
			$text .= "<div class='column' id='portal-column-block-list' style='border:1px inset black;height:200px;display:block;overflow:auto;margin-bottom:20px'>";
			$text .= "<table id='core-menumanager-main' style='width:100%;margin-left:auto;margin-right:auto' cellspacing='0' cellpadding='0'>\n";

		}
		else
		{
        	$text .= "<div class='column' id='remove' style='border:1px solid silver'>\n";
		}

        $color = "";
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
	            if($pnum = $this->checkMenuPreset($menuPreset,$row['menu_name'].'_menu'))
				{
		        	$pdeta = MENLAN_39." {$pnum}";
				}
			}

	        if(!$this->dragDrop)
			{
				$menuInf = (strlen($row['menu_path']) > 1) ? ' ('.substr($row['menu_path'],0,-1).')' : '';
	    		$text .= "<tr style='background-color:$color;color:black'>
				<td style='text-align:left; color:black;'><input type='checkbox' id='menuselect-{$row['menu_id']}' name='menuselect[]' value='{$row['menu_id']}' /><label for='menuselect-{$row['menu_id']}'>".$row['menu_name'].$menuInf."</label></td>
	            <td style='color:black'> ".$pdeta."&nbsp;</td>
				</tr>\n";
	        }
			else
			{
				// Menu Choices box. 
	            $text .= "<div class='portlet block block-archive' id='block-".$row['menu_id']."' style='border:1px outset black;text-align:left;color:black'>";
			 	$text .= $this->menuRenderMenu($row, $menu_count,true);
	  			$text .= "</div>\n";
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
			$text .= "<input type='hidden'  name='curLayout' value='".$layout."' />";
	    }
		$text .= "<input type='hidden'  id='dbLayout' value='".$this->dbLayout."' />";
		$text .= "</td>";

		$text .= "</tr></table>";
		$ns -> tablerender(MENLAN_22, $text);
        echo $rs->form_close();
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
		global $rs;
		$pref = e107::getPref();
		
// onchange=\"urljump(this.options[selectedIndex].value);\"

		$text = "<form  method='post' action='".e_SELF."?configure=".$this->curLayout."'>";
		$text .= "<div class='buttons-bar center'>".MENLAN_30." ";
        $text .= "<select name='custom_select' id='menuManagerSelect' >\n";  // window.frames['menu_iframe'].location=this.options[selectedIndex].value ???


	    $search = array("_","legacyDefault","legacyCustom");
		$replace = array(" ",MENLAN_31,MENLAN_33);


	    foreach($pref['sitetheme_layouts'] as $key=>$val)
		{
			$url = "";
			$layoutName = str_replace($search,$replace,$key);
			$layoutName .=($key==$pref['sitetheme_deflayout']) ? " (".MENLAN_31.")" : "";
			$selected = ($this->curLayout == $key || ($key==$pref['sitetheme_deflayout'] && $this->curLayout=='')) ? "selected='selected'" : FALSE;
		
           // $url = e_SELF."?lay=".$key;

			$url = e_SELF."?configure=".$key;
			
			$text .= "<option value='$url' {$selected}>".$layoutName."</option>";
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
		$tmp = preg_split('#(\{\S[^\x02]*?\S\})#', $LAYOUT, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$str = array();
		for($c = 0; $c < count($tmp); $c++)
		{
			if(preg_match("/[\{|\}]/", $tmp[$c]))
			{
				if($check)
				{
					if(strstr($tmp[$c], "{MENU="))
					{
						$matches = array();
						// Match all menu areas, menu number is limited to tinyint(3)
						preg_match_all("/\{MENU=([\d]{1,3})(:[\w\d]*)?\}/", $tmp[$c], $matches);
						$this->menuSetCode($matches, $str);
					}
				}
				else
				{
					$this->checklayout($tmp[$c]);
				}
			}
			else
			{
				if(!$check)
				{
					echo $tmp[$c];
				}
			}
		}
		if($check)
		{
			return $str;
		}
	}
	
	function menuSetCode($matches, &$ret)
	{
		if(!$matches || !varsettrue($matches[1]))
		{
			return;
		}
	
		foreach ($matches[1] as $match) 
		{
			$ret[] = $match;
		}
	}

	function checklayout($str)
	{ // Displays a basic representation of the theme
		global $pref, $ns, $PLUGINS_DIRECTORY, $rs, $sc_style, $tp, $menu_order;
		
		$menuLayout = ($this->curLayout != $pref['sitetheme_deflayout']) ? $this->curLayout : "";
		
		if(strstr($str, "LOGO"))
		{
			echo $tp->parseTemplate("{LOGO}");
		}
		else if(strstr($str, "SITENAME"))
		{
			echo "[SiteName]";
		}
		else if(strstr($str, "SITETAG"))
		{
			echo "<div style='padding: 2px'>[SiteTag]</div>";
		}
		else if(strstr($str, "SITELINKS"))
		{
			echo "<div style='padding: 2px; text-align: center'>[SiteLinks]</div>";
		}
		else if(strstr($str, "LANGUAGELINKS"))
		{
			echo "<div class=text style='padding: 2px; text-align: center'>[Language]</div>";
		}
		else if(strstr($str, "CUSTOM"))
		{
			$cust = preg_replace("/\W*\{CUSTOM=(.*?)(\+.*)?\}\W*/si", "\\1", $str);
			echo "<div style='padding: 2px'>[" . $cust . "]</div>";
		}
		// Display embedded Plugin information.
		else if(strstr($str, "PLUGIN"))
		{
			$plug = preg_replace("/\{PLUGIN=(.*?)\}/si", "\\1", $str);
			$plug = trim($plug);
			if(file_exists((e_PLUGIN . "{$plug}/{$plug}_config.php")))
			{
				$link = e_PLUGIN . "{$plug}/{$plug}_config.php";
			}
			
			if(file_exists((e_PLUGIN . $plug . "/config.php")))
			{
				$link = e_PLUGIN . $plug . "/config.php";
			}
			
			$plugtext = ($link) ? "(" . MENLAN_34 . ":<a href='$link' title='" . LAN_CONFIGURE . "'>" . LAN_CONFIGURE . "</a>)" : "(" . MENLAN_34 . ")";
			echo "<br />";
			$ns->tablerender($plug, $plugtext);
		}
		else if(strstr($str, "MENU"))
		{
			
			$matches = array();
			if(preg_match_all("/\{MENU=([\d]{1,3})(:[\w\d]*)?\}/", $str, $matches))
			{
				$menuText = "";
				foreach($matches[1] as $menu)
				{
					$menu = preg_replace("/\{MENU=(.*?)(:.*?)?\}/si", "\\1", $str);
					if(isset($sc_style['MENU']['pre']) && strpos($str, 'ret') !== false)
					{
						$menuText .= $sc_style['MENU']['pre'];
					}
					
					
					// ---------------
					$menuText .= "\n\n<!-- START AREA ".$menu." -->";
					$menuText .= "
					<div id='start-area-".$menu."'>";
				
					
					
										
					$menuText .= "<div class='fborder forumheader' style='font-weight:bold;display:block;text-align:center; font-size:14px' >
					" . MENLAN_14 . "  " . $menu . "
					</div>
										\n\n";
					
					
			
					$sql9 = new db();
				//	$sql9 = e107::getDb('sql9');
					if($sql9->db_Count("menus", "(*)", " WHERE menu_location='$menu' AND menu_layout = '" . $this->dbLayout . "' "))
					{
						unset($text);
						$menuText .= $rs->form_open("post", e_SELF . "?configure=" . $this->curLayout, "frm_menu_" . intval($menu));
						
						$MODE = 1;
						
						$sql9->db_Select("menus", "*", "menu_location='$menu' AND menu_layout='" . $this->dbLayout . "' ORDER BY menu_order");
						$menu_count = $sql9->db_Rows();
						
						$cl = ($this->dragDrop) ? "'portlet" : "regularMenu";
						
						$menuText .= "\n<div class='column' id='area-".$menu."'>\n\n";
						while($row = $sql9->db_Fetch(MYSQL_ASSOC))
						{
							$menuText .= "\n\n\n <!-- Menu Start ".$row['menu_name']. "-->\n";
							$menuText .= "<div class='{$cl}' id='block-".$row['menu_id']."-".$menu."'>\n";
		
						//	echo "<div class='ggportal'>";
							
						//	$menuText .= "hi there";
							$menuText .= $this->menuRenderMenu($row, $menu_count);
							
						//	echo "\n</div>";
							$menuText .= "\n</div>\n";
							$menuText .= "<!-- Menu end -->\n\n\n";
							// echo "<div><br /></div>";
						
						}
						$menuText .= "\n\n</div>\n\n"; // End Column 
						$menuText .= $rs->form_close();
					}
					else
					{	// placeholder
						$menuText .=  "<div class='column' id='area-" . $menu . "'><!-- --></div>";
					}
					
					$menuText .= "</div><!-- END OF AREA -->\n\n";
					
					// ---------------
					
					
					if(isset($sc_style['MENU']['post']) && strpos($str, 'ret') !== false)
					{
						$menuText .= $sc_style['MENU']['post'];
					}
					
					
				}
			}

			echo $menuText;
		}
		else if(strstr($str, "SETSTYLE"))
		{
			$tmp = explode("=", $str);
			$style = preg_replace("/\{SETSTYLE=(.*?)\}/si", "\\1", $str);
			$this->style = $style;
			
		}
		else if(strstr($str, "SITEDISCLAIMER"))
		{
			echo "[Sitedisclaimer]";
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

	function menuRenderMenu($row,$menu_count,$rep = FALSE)
	{
		global $ns,$rs,$menu,$menu_info,$menu_act;
		global $style;
		$style = $this->style;
		//      $menu_count is empty in here
		//FIXME extract
		extract($row);
		if(!$menu_id){ return; }
		include_once(e_HANDLER.'admin_handler.php');
		$menu_name = preg_replace("#_menu#i", "", $menu_name);
		//TODO we need a CSS class for this
		$vis = ($menu_class || strlen($menu_pages) > 1) ? " <span class='required'>*</span> " : "";
		//DEBUG div not allowed in final tags 	$caption = "<div style='text-align:center'>{$menu_name}{$vis}</div>";
		// use theme render style instead
		
		// Undocumented special parameter 'admin_title'
		$menuParms = array();
		if(!empty($row['menu_parms'])) parse_str($row['menu_parms'], $menuParms);
		if(isset($menuParms['admin_title']) && $menuParms['admin_title'])
		{
			$caption = deftrue($menuParms['admin_title'], $menuParms['admin_title']).$vis;
		}
		elseif(isset($menuParms['title']) && $menuParms['title'])
		{
			$caption = deftrue($menuParms['title'], $menuParms['title']).$vis;
		}
		else $caption = $menu_name.$vis;
		
		$menu_info = "{$menu_location}.{$menu_order}";

		$text = "";
		$conf = '';
		if (file_exists(e_PLUGIN.$menu_path.$menu_name.'_menu_config.php'))
		{
			$conf = $menu_path.$menu_name.'_menu_config';
		}

		if($conf == '' && file_exists(e_PLUGIN."{$menu_path}config.php"))
		{
		  $conf = "{$menu_path}config";
		}

		if(!$this->dragDrop)
		{
			$text .= "<select id='menuAct_".$menu_id."' name='menuAct[$menu_id]' class='tbox' onchange='this.form.submit()' >";
			$text .= $rs->form_option(MENLAN_25, TRUE, " ");
		//	$text .= $rs->form_option(MENLAN_15, "", "deac.{$menu_info}");
	
			if ($conf) 
			{
			//	$text .= $rs->form_option("Configure", "", $conf); // TODO Check LAN availability
			}
	
			if ($menu_order != 1) 
			{
				$text .= $rs->form_option(MENLAN_17, "", "inc.{$menu_info}");
				$text .= $rs->form_option(MENLAN_24, "", "top.{$menu_info}");
			}
			if ($menu_count != $menu_order) 
			{
				$text .= $rs->form_option(MENLAN_18, "", "dec.{$menu_info}");
				$text .= $rs->form_option(MENLAN_23, "", "bot.{$menu_info}");
			}
			foreach ($this->menu_areas as $menu_act) 
			{
				if ($menu != $menu_act) 
				{
					$text .= $rs->form_option(MENLAN_19." ".$menu_act, "", "move.{$menu_info}.".$menu_act);
				}
			}
			
			// Visibility is an action icon now
			//$text .= $rs->form_option(MENLAN_20, "", "adv.{$menu_info}");
			$text .= $rs->form_select_close();
		}

		if($rep == true)
		{	
			$text .= "<div id='check-".$menu_id."'><input type='checkbox' name='menuselect[]' value='{$menu_id}' />".$menu_id."  {$pdeta}</div>
	            <div id='option-".$menu_id."' style='display:none'>";
		}
				
		//DEBUG remove inline style, switch to simple quoted string for title text value
		//TODO hardcoded text
		$text .= '<div class="menuOptions">
		<a class="e-dialog" target="_top" href="'.e_SELF.'?lay='.$this->curLayout.'&amp;vis='.$menu_id.'&amp;iframe=1" title="'.MENLAN_20.'">'.ADMIN_VIEW_ICON.'</a>';

		if($conf)
		{
			$text .= '<a target="_top" href="'.e_SELF.'?lay='.$this->curLayout.'&amp;mode=conf&amp;path='.urlencode($conf).'&amp;id='.$menu_id.'" 
			title="Configure menu">'.ADMIN_CONFIGURE_ICON.'</a>';
		}
		
		$text .= '<a target="_top" href="'.e_SELF.'?lay='.$this->curLayout.'&amp;parmsId='.$menu_id.'" 
		title="Configure parameters">'.ADMIN_EDIT_ICON.'</a>';

		$text .= '<a title="'.LAN_DELETE.'" id="remove-'.$menu_id.'-'.$menu_location.'" class="e-tip delete e-menumanager-delete" href="'.e_SELF.'?configure='.$this->curLayout.'&amp;mode=deac&amp;id='.$menu_id.'">'.ADMIN_DELETE_ICON.'</a>
		
		<span id="status-'.$menu_id.'" style="display:none">'.($rep == true ? "" : "insert").'</span>
		</div>';

		$text .= ($rep == true) ? "</div>" : "";

		
		
		if(!$this->dragDrop)
		{
			
			ob_start();

			$ns->tablerender($caption, $text);
			$THEX = ob_get_contents();
			ob_end_clean();
		
			return $THEX;	
		}
		else
		{
			
			return "
			<div class='portlet-header'>".$caption."</div>
			<div class='portlet-content' >".$text."</div>";		
		}
		
		

	}

	function menuSaveAjax()
	{
	
     
		$this->debug = TRUE;
		
	    global $sql;


		list($tmp,$area) = explode("-",$_POST['area']);
		
		if($_POST['area'] == 'remove')
		{
			list($tmp,$deleteID) = explode("-",$_POST['removeid']);	
			$this->menuId = $deleteID;
			$this->menuDeactivate();		
			echo "Removed {$deleteId}";
			return;
		}

		// Allow deletion by ajax, but not the rest when drag/drop disabled.  

		if(!$this->dragDrop){ return; }

		$this -> dbLayout = $_POST['layout'];
		list($tmp,$insertID) = explode("-",$_POST['insert']);	
		$insert[] = $insertID;

		print_r($_POST);

		if($_POST['mode'] == 'insert'  && count($insert) && $area) // clear out everything before rewriting everything to db. 
		{
		 	$this->menuActivateLoc = $area;  // location
			$this->menuActivateIds = $insert;  // array of ids, in order.
			$this->menuActivate(); 
			
		}
		elseif($_POST['mode'] == 'update')
		{
			$sql->db_Update("menus","menu_location = ".intval($area)." WHERE menu_id = ".intval($insertID)." LIMIT 1",$this->debug);	
		}
		
		$c = 0;
		
		if(count($_POST['list'])<2)
		{
			return;
		}
		
		// resort the menus in this 'Area"
		foreach($_POST['list'] as $val)
		{
			list($b,$id) = explode("-",$val);
			$order[] = $id;
			$sql->db_Update("menus","menu_order = ".$c." WHERE menu_id = ".intval($id)." LIMIT 1",$this->debug);
       		$c++;
		}

		// same for delete etc.

	//	echo "<hr />";


	}

    function menuSetConfigList()
	{
        	global $sql,$pref;

			$sql -> db_Select("menus", "*", "menu_location != 0 ORDER BY menu_path,menu_name");
			while($row = $sql-> db_Fetch())
			{
				$link = "";

	  			extract($row);
				$id = substr($menu_path,0,-1);

				if (file_exists(e_PLUGIN."{$menu_path}{$menu_name}_menu_config.php"))
				{
				    $link = "{$menu_path}{$menu_name}_menu_config.php";
				}

				if(file_exists(e_PLUGIN."{$menu_path}config.php"))
				{
					 $link = "{$menu_path}config.php";
				}

				if($link)
				{
         			$tmp[$id]['name'] = ucwords(str_replace("_menu","",$menu_name));
					if(vartrue($prev) == $id && ($tmp[$id]['name']!=$prev_name))
					{
	                	$tmp[$id]['name'] .= ":".$prev_name;
					}

					$tmp[$id]['link'] = $link;
					$prev = $id;
					$prev_name = $tmp[$id]['name'];
				}
			}

           $pref['menuconfig_list'] = vartrue($tmp);
		   save_prefs();
	}
}  // end of Class.