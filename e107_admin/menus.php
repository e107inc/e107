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
|     $Revision: 1.22 $
|     $Date: 2009-07-09 08:31:37 $
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
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."file_class.php");
$frm = new form;

if($_POST)
{
//	print_a($_POST);
//	exit;
	$e107cache->clear_sys("menus_");
}

$menus_equery = explode('.', e_QUERY);
$curLayout = $menus_equery[1];

if (isset($_POST['custom_select']))
{
	$curLayout = $_POST['custom_select'];
	//header("location:".e_SELF."?".$_POST['custom_select']);
	//exit;
}
 else if (!isset($curLayout))
{
	$curLayout = $pref['sitetheme_deflayout'];
}


/*if ($curLayout == '' || $curLayout == 'default_layout')
{
	$menus_header = is_array($HEADER) ? $HEADER[$pref['sitetheme_deflayout']] : $HEADER;
	$menus_footer = is_array($FOOTER) ? $FOOTER[$pref['sitetheme_deflayout']] : $FOOTER;
}
else if ($curLayout == 'legacyCustom')
{
	$menus_header = $CUSTOMHEADER ? $CUSTOMHEADER : $HEADER;
	$menus_footer = $CUSTOMFOOTER ? $CUSTOMFOOTER : $FOOTER;
}
else if ($curLayout == 'newsheader_layout')
{
	$menus_header = $NEWSHEADER ? $NEWSHEADER : $HEADER;
	$menus_footer = $FOOTER;
}
elseif(is_array($HEADER) || is_array($FOOTER))   // 0.8 themes
{
	$menus_header = (is_array($HEADER) && isset($HEADER[$curLayout])) ? $HEADER[$curLayout] : $HEADER;
	$menus_footer = (is_array($FOOTER) && isset($FOOTER[$curLayout])) ? $FOOTER[$curLayout] : $FOOTER;

}
else
{
	$menus_header = $CUSTOMHEADER[$curLayout] ? $CUSTOMHEADER[$curLayout] :	$HEADER;
	$menus_footer = $CUSTOMFOOTER[$curLayout] ? $CUSTOMFOOTER[$curLayout] : $FOOTER;
}
*/
   // Almost the same code as found in templates/header_default.php
	if(($curLayout == 'legacyCustom' || $curLayout=='legacyDefault') && (isset($CUSTOMHEADER) || isset($CUSTOMFOOTER)) )  // 0.6 themes.
	{
	 	if($curLayout == 'legacyCustom')
		{
			$HEADER = ($CUSTOMHEADER) ? $CUSTOMHEADER : $HEADER;
			$FOOTER = ($CUSTOMFOOTER) ? $CUSTOMFOOTER : $FOOTER;
		}
	}
	elseif($curLayout && $curLayout != "legacyCustom" && (isset($CUSTOMHEADER[$curLayout]) || isset($CUSTOMHEADER[$curLayout]))) // 0.7 themes
	{
	  //	echo " MODE 0.7";
		$HEADER = ($CUSTOMHEADER[$curLayout]) ? $CUSTOMHEADER[$curLayout] : $HEADER;
		$FOOTER = ($CUSTOMFOOTER[$curLayout]) ? $CUSTOMFOOTER[$curLayout] : $FOOTER;
	}
    elseif($curLayout && isset($HEADER[$curLayout]) && isset($FOOTER[$curLayout])) // 0.8 themes - we use only $HEADER and $FOOTER arrays.
	{
	  //	echo " MODE 0.8";
		$HEADER = $HEADER[$curLayout];
		$FOOTER = $FOOTER[$curLayout];
	}

	if (e_PAGE == 'news.php' && isset($NEWSHEADER))
	{
	   //	parseheader($NEWSHEADER);
	}
	else
	{
      //	parseheader($HEADER);
	}


 $layouts_str = $HEADER.$FOOTER;
 $menus_header = $HEADER;
 $menus_footer = $FOOTER;


if ($NEWSHEADER)
{
	$layouts_str .= $NEWSHEADER;
}

$menu_array = parseheader($layouts_str, 'check');


sort($menu_array, SORT_NUMERIC);
$menu_check = 'set';
foreach ($menu_array as $menu_value)
{
	if ($menu_value != $menu_check)
	{
		$menu_areas[] = $menu_value;
	}
	$menu_check = $menu_value;
}

// Cams Bit ----------- Activate Multiple Menus ---
if($_POST['menuActivate'])
{
	menuActivate();

}

if($_POST['menuSetCustomPages'])
{
	menuSetCustomPages($_POST['custompages']);
}






if(isset($_POST['menuUsePreset']) && $_POST['curLayout'])
{
	global $pref;
	$layout = ($_POST['curLayout'] != $pref['sitetheme_deflayout']) ? $_POST['curLayout'] : "";
    $menuAreas = getMenuPreset($_POST['curLayout']);

    $sql->db_Update("menus", "menu_location='0' WHERE menu_layout = '$layout' "); // Clear All existing.
	foreach($menuAreas as $val)
	{

		if($sql->db_Select("menus", 'menu_name, menu_path' , "menu_name = '".$val['menu_name']."' LIMIT 1"))
		{
			$row=$sql->db_Fetch();

        	if(!$sql->db_Update('menus', "menu_order='{$val['menu_order']}', menu_location = ".$val['menu_location'].", menu_class= ".$val['menu_class']." WHERE menu_name='".$val['menu_name']."' AND menu_layout = '$layout' LIMIT 1 "))
			{
 				$qry = "
				INSERT into #menus
				(`menu_name`, `menu_location`, `menu_order`, `menu_pages`,`menu_class`, `menu_path`, `menu_layout`)
				VALUES ('{$val['menu_name']}', {$val['menu_location']}, {$val['menu_order']}, '', '{$val['menu_class']}',  '{$row['menu_path']}', '{$layout}')
				";
				$sql->db_Select_gen($qry);
			  	$admin_log->log_event('MENU_01',$row['menu_name'].'[!br!]'.$location.'[!br!]'.$menu_count.'[!br!]'.$row['menu_path'],E_LOG_INFORMATIVE,'');

			}
         }
	}



}

// =============

if (isset($_POST['menuAct']))
{
  foreach ($_POST['menuAct'] as $k => $v)
  {
	if (trim($v))
	{
	  $id = $k;
	  list($menu_act, $location, $position, $newloc) = explode(".", $_POST['menuAct'][$k]);
	}
  }
}

if ($menu_act == 'config')
{
	if($newloc)
	{
		$newloc = ".".$newloc;
	}
	$newurl = $PLUGINS_DIRECTORY.$location."/{$position}{$newloc}.php";
	$newurl = SITEURL.str_replace("//", "/", $newurl);
	echo "<script type='text/javascript'>	top.location.href = '{$newurl}'; </script> ";
	exit;
}

if ($menu_act == "adv")
{
	menuVisibilityOptions();
}



unset($message);

if ($menu_act == "sv")
{
	menuSaveVisibility();

}




if ($menu_act == "move")
{
 	menuMove();
}

if ($menu_act == "deac")
{
 	menuDeactivate();
}

if ($menu_act == "bot")
{
	$menu_count = $sql->db_Count("menus", "(*)", " WHERE menu_location='{$location}' AND menu_layout = '$curLayout'  ");
	$sql->db_Update("menus", "menu_order=".($menu_count+1)." WHERE menu_order='{$position}' AND menu_location='{$location}' AND menu_layout = '$curLayout'  ");
	$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_location='{$location}' AND menu_order > {$position} AND menu_layout = '$curLayout' ");
	$admin_log->log_event('MENU_06',$location.'[!br!]'.$position.'[!br!]'.$id,E_LOG_INFORMATIVE,'');
}

if ($menu_act == "top")
{
	$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_location='{$location}' AND menu_order < {$position} AND menu_layout = '$curLayout' ");
	$sql->db_Update("menus", "menu_order=1 WHERE menu_id='{$id}' ");
	$admin_log->log_event('MENU_05',$location.'[!br!]'.$position.'[!br!]'.$id,E_LOG_INFORMATIVE,'');
}

if ($menu_act == "dec")
{
	$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_order='".($position+1)."' AND menu_location='{$location}' AND menu_layout = '$curLayout' ");
	$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_id='{$id}' AND menu_location='{$location}' AND menu_layout = '$curLayout' ");
	$admin_log->log_event('MENU_08',$location.'[!br!]'.$position.'[!br!]'.$id,E_LOG_INFORMATIVE,'');
}

if ($menu_act == "inc")
{
	$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_order='".($position-1)."' AND menu_location='{$location}' AND menu_layout = '$curLayout' ");
	$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_id='{$id}' AND menu_location='{$location}' AND menu_layout = '$curLayout' ");
	$admin_log->log_event('MENU_07',$location.'[!br!]'.$position.'[!br!]'.$id,E_LOG_INFORMATIVE,'');
}

if (strpos(e_QUERY, 'configure') === FALSE)
{  // Scan plugin directories to see if menus to add
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
	foreach ($menu_areas as $menu_act)
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
}

if ($message != "")
{
	echo $ns -> tablerender('Updated', "<div style='text-align:center'><b>".$message."</b></div><br /><br />");
}


if (strpos(e_QUERY, 'configure') === FALSE)
{
	$cnt = $sql->db_Select("menus", "*", "menu_location > 0 AND menu_layout = '$curLayout' ORDER BY menu_name "); // calculate height to remove vertical scroll-bar.
    $text .= "COUNT = ".$cnt;

	$text = "<iframe id='menu_iframe' src='".e_SELF."?configure.$curLayout' width='100%' style='width: 100%; height: ".(($cnt*80)+600)."px; border: 0px' frameborder='0' scrolling='auto' ></iframe>";
	echo $ns -> tablerender(MENLAN_35, $text, 'menus_config');
}
else // Within the IFrame.
{

    echo menuSelectLayout();
	menuRenderPage();

}


// ---------------------------------------------------------------------------

function menuVisibilityOptions()
{
	global $sql,$ns,$id;
	require_once(e_HANDLER."userclass_class.php");
	$sql->db_Select("menus", "*", "menu_id=".$id);
	$row = $sql->db_Fetch();
	$listtype = substr($row['menu_pages'], 0, 1);
	$menu_pages = substr($row['menu_pages'], 2);
	$menu_pages = str_replace("|", "\n", $menu_pages);

	$text = "<div style='text-align:center;'>
	<form  method='post' action='".e_SELF."?configure.".$curLayout."'>
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

function menuActivate()
{
	global $sql, $admin_log, $pref;

	$location = key($_POST['menuActivate']);

    $layout = ($_POST['curLayout'] != $pref['sitetheme_deflayout']) ? $_POST['curLayout'] : "";

	$menu_count = $sql->db_Count("menus", "(*)", " WHERE menu_location=".$location." AND menu_layout = '$layout' ");

	foreach($_POST['menuselect'] as $sel_mens)
	{
		//Get info from menu being activated
		if($sql->db_Select("menus", 'menu_name, menu_path' , "menu_id = ".$sel_mens." "))
		{
			$row=$sql->db_Fetch();
			//If menu is not already activated in that area, add the record.

			if(!$sql->db_Select('menus', 'menu_name,menu_path', " menu_name='".$row['menu_name']."' AND menu_layout = '$layout' AND menu_location = ".$location." LIMIT 1 "))
			{
				$qry = "
				INSERT into #menus
				(`menu_name`, `menu_location`, `menu_order`, `menu_pages`, `menu_path`, `menu_layout`)
				VALUES ('{$row['menu_name']}', {$location}, {$menu_count}, '', '{$row['menu_path']}', '{$layout}')
				";
				$sql->db_Select_gen($qry);
				$admin_log->log_event('MENU_01',$row['menu_name'].'[!br!]'.$location.'[!br!]'.$menu_count.'[!br!]'.$row['menu_path'],E_LOG_INFORMATIVE,'');
				$menu_count++;
			}
		}
	}
}

function menuSetCustomPages($array)
{
	global $pref;
	$key = key($array);
	$pref['sitetheme_custompages'][$key] = array_filter(explode(" ",$array[$key]));
	save_prefs();
}

function getMenuPreset($layout)
{
	global $pref;

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
	global $sql, $admin_log,$id;
	$pagelist = explode("\r\n", $_POST['pagelist']);
	for ($i = 0 ; $i < count($pagelist) ; $i++)
	{
		$pagelist[$i] = trim($pagelist[$i]);
	}
	$plist = implode("|", $pagelist);
	$pageparms = $_POST['listtype'].'-'.$plist;
	$pageparms = preg_replace("#\|$#", "", $pageparms);
	$pageparms = (trim($_POST['pagelist']) == '') ? '' : $pageparms;

	if($sql->db_Update("menus", "menu_class='".$_POST['menu_class']."', menu_pages='{$pageparms}' WHERE menu_id=".intval($id)))
	{
		$admin_log->log_event('MENU_02',$_POST['menu_class'].'[!br!]'.$pageparms.'[!br!]'.$id,E_LOG_INFORMATIVE,'');
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
	global $sql,$admin_log,$id,$curLayout;

	if($sql->db_Select('menus', 'menu_name', 'menu_id='.$id, 'default'))
	{
		$row = $sql->db_Fetch();
		//Check to see if there is already a menu with location = 0 (to maintain BC)
		if($sql->db_Select('menus', 'menu_id', "menu_name='{$row['menu_name']}' AND menu_location = 0 AND menu_layout ='".$curLayout."' LIMIT 1"))
		{
			//menu_location=0 already exists, we can just delete this record
			$sql->db_Delete('menus', 'menu_id='.$id);
		}
		else
		{
			//menu_location=0 does NOT exist, let's just convert this to it
			if(!$sql->db_Update("menus", "menu_location=0, menu_order=0, menu_class=0, menu_pages='' WHERE menu_id=".$id))
			{
            	$message = "FAILED";
			}
		}
		//Move all other menus up
		$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_location={$location} AND menu_order > {$position} AND menu_layout = '".$curLayout."' ");
		$admin_log->log_event('MENU_04',$row['menu_name'].'[!br!]'.$location.'[!br!]'.$position.'[!br!]'.$id,E_LOG_INFORMATIVE,'');
	}

	echo $message;
}


// ----------------------------------------------------------------------

function menuMove()
{// Get current menu name

		global $id,$admin_log,$sql,$newloc, $curLayout;

		if($sql->db_Select('menus', 'menu_name', 'menu_id='.$id, 'default'))
		{
			$row = $sql->db_Fetch();
			//Check to see if menu is already active in the new area, if not then move it
			if(!$sql->db_Select('menus', 'menu_id', "menu_name='{$row['menu_name']}' AND menu_location = ".$newloc." AND menu_layout='".$curLayout ."' LIMIT 1"))
			{
				$menu_count = $sql->db_Count("menus", "(*)", " WHERE menu_location=".$newloc);
				$sql->db_Update("menus", "menu_location='{$newloc}', menu_order=".($menu_count+1)." WHERE menu_id=".$id);
				$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_location='{$location}' AND menu_order > {$position} AND menu_layout='".$curLayout ."' ");
			}
			$admin_log->log_event('MENU_03',$row['menu_name'].'[!br!]'.$newloc.'[!br!]'.$id,E_LOG_INFORMATIVE,'');
		}
}


// =-----------------------------------------------------------------------------


function menuRenderPage()
{
	global $sql, $ns, $menus_header, $menus_footer, $frm, $curLayout, $pref, $tp, $menu_areas;

	parseheader($menus_header);  // $layouts_str;

	$layout = ($curLayout) ? $curLayout : $pref['sitetheme_deflayout'];
	$menuPreset = getMenuPreset($layout);


	echo "<div style='text-align:center'>";
	echo $frm->form_open("post", e_SELF."?configure.".$curLayout, "menuActivation");
	$text = "<table style='width:80%;margin-left:auto;margin-right:auto'>";

 	$sql->db_Select("menus", "menu_name, menu_id, menu_pages", "1 GROUP BY menu_name ORDER BY menu_name ASC");
	$text .= "<tr><td style='width:50%;text-align:center;padding-bottom:4px'>".MENLAN_36."...</td><td style='width:50%;padding-bottom:4px;text-align:center'>...".MENLAN_37."</td></tr>";
	$text .= "<tr><td style='width:50%;vertical-align:top;text-align:center'>";


	$text .= "<div style='border:1px inset black;height:200px;display:block;overflow:auto;margin-bottom:20px'>
	<table style='width:100%;margin-left:auto;margin-right:auto' cellspacing='0' cellpadding='0'>\n";
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
            if($pnum = checkMenuPreset($menuPreset,$row['menu_name']."_menu"))
			{
	        	$pdeta = MENLAN_39." {$pnum}";
			}
		}


		$text .= "<tr style='background-color:$color;color:black'>
			<td style='text-align:left; color:black;'><input type='checkbox' name='menuselect[]' value='{$row['menu_id']}' />".$row['menu_name']."</td>
            <td style='color:black'> ".$pdeta."&nbsp;</td>
			</tr>\n";

	}
	$text .= "</table></div>";

	$text .= "</td><td style='width:50%;vertical-align:top;text-align:center'><br />";
	foreach ($menu_areas as $menu_act)
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
	echo $ns -> tablerender(MENLAN_22, $text);
	echo $frm->form_close();
	echo "</div>";

	parseheader($menus_footer);
}





//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function menuSelectLayout()
{
	global $frm, $curLayout, $menus_equery, $pref, $HEADER,  $CUSTOMHEADER, $CUSTOMFOOTER, $CUSTOMPAGES;

	$text .= "<form  method='post' action='".e_SELF."?configure.".$curLayout."'>";
	$text .= "<div style='color:white;background-color:black;width:99%;left:0px;position:relative;display:block;padding:15px;text-align:center'>".MENLAN_30." ";
    $text .= "<select style='color:black' name='custom_select' onchange=\"this.form.submit()\">\n";

    $search = array("_","legacyDefault","legacyCustom");
	$replace = array(" ",MENLAN_31,MENLAN_33);


    foreach($pref['sitetheme_layouts'] as $key=>$val)
	{
		$layoutName = str_replace($search,$replace,$key);
		$layoutName .=($key==$pref['sitetheme_deflayout']) ? " (".MENLAN_31.")" : "";
		$selected = ($curLayout == $key || ($key==$pref['sitetheme_deflayout'] && $curLayout=='')) ? "selected" : FALSE;
    	$text .= "<option value='$key' {$selected}>".$layoutName."</option>";
	}

    $text .= "</select></div></form>";
	  return $text;


	if(is_array($HEADER))
	{
        foreach($HEADER as $key=>$val)
		{
			$default = ($curLayout == $key || $curLayout == 'default_layout' ) ? "selected" : FALSE;
			$deftext = ($key==$pref['sitetheme_deflayout']) ? " (".MENLAN_31.")" : "";
			$diz = str_replace("_"," ",$key);
        	$text .= $frm->form_option(ucwords($diz).$deftext, $default, $key);
 		}
	}
	else
	{
    	$text .= $frm->form_option(MENLAN_31, '', '');
	}

    if($CUSTOMHEADER && !is_array($CUSTOMHEADER))
	{
    	$CUSTOMHEADER = array('legacyCustom'=>$CUSTOMHEADER);
	}

	if($CUSTOMFOOTER && !is_array($CUSTOMFOOTER))
	{
    	$CUSTOMFOOTER = array('legacyCustom'=> $CUSTOMFOOTER);

	}

	if(is_array($CUSTOMHEADER) || is_array($CUSTOMFOOTER))
	{
		$customlist = (is_array($CUSTOMHEADER)) ? array_merge($CUSTOMHEADER,$CUSTOMFOOTER) : $CUSTOMFOOTER;
        foreach($customlist as $key=>$val)
		{
			$selected = ($curLayout == $key || $curLayout == 'default_layout' ) ? "selected='selected'" : "";
			$deftext = ($key==$pref['sitetheme_deflayout']) ? " (".MENLAN_31.")" : "";
			$diz = ($key == "legacyCustom") ? MENLAN_33 : str_replace("_"," ",$key);
			$text .= "<option value='$key' $selected style='color:black'>".ucwords($diz).$deftext."</option>\n";
           //	$text .= $frm->form_option(ucwords($diz).$deftext, $default, $key);
 		}
	}



     $text .= "</select>";

    // TO-DO Saving of the custompage preference for each layout.
   /*
	if($curLayout && ($curLayout != $pref['sitetheme_deflayout']))
	{

		if(!isset($pref['sitetheme_custompages'][$curLayout]))
		{
        	if(isset($pref['sitetheme_layouts'][$curLayout]['customPages']))
			{
            	$custPages = $pref['sitetheme_layouts'][$curLayout]['customPages'];
			}
			elseif(isset($CUSTOMPAGES[$curLayout]) && $curLayout !='legacyCustom')
			{
            	$custPages = $CUSTOMPAGES[$curLayout];
			}
			elseif($CUSTOMPAGES)
			{
            	$custPages = $CUSTOMPAGES;

			}

		   	menuSetCustomPages(array($curLayout=>$custPages));
		}
		else
		{
        	$custPages = $pref['sitetheme_custompages'][$curLayout];
		}

    	$text .= "<div style='padding:10px'>Displays on these pages: <input type='text' style='width:80%;color:black;background-color:white' name='custompages[".$curLayout."]' value=\"".implode(" ",$custPages)."\" />
        <input type='submit' name='menuSetCustomPages' value='".LAN_SAVE."' />
		</div>";
	}*/
	 $text .= "</div>
		</form>";
	  return $text;

}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function parseheader($LAYOUT, $check = FALSE)
{
	global $curLayout;
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
		checklayout($tmp[$c],$curLayout);
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

function checklayout($str,$curLayout)
{	// Displays a basic representation of the theme
	global $pref, $menu_areas, $ns, $PLUGINS_DIRECTORY, $frm, $sc_style, $tp, $menu_order, $menus_equery;

    $menuLayout = ($curLayout != $pref['sitetheme_deflayout']) ? $curLayout : "";

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
		$ns = new e107table;
		$menu = preg_replace("/\{MENU=(.*?)(:.*?)?\}/si", "\\1", $str);
		if (isset($sc_style['MENU']['pre']) && strpos($str, 'ret') !== false)
		{
		  echo $sc_style['MENU']['pre'];
		}
		echo "<div style='text-align:center; font-size:14px' class='fborder'><div class='forumheader'><b>".MENLAN_14."  ".$menu."</b></div></div><br />";
		$text = "&nbsp;";
		$sql9 = new db;
		if ($sql9->db_Count("menus", "(*)", " WHERE menu_location='$menu' AND menu_layout = '$menuLayout' "))
		{
			unset($text);
			echo $frm->form_open("post", e_SELF."?configure.".$curLayout, "frm_menu_".intval($menu));

			$sql9->db_Select("menus", "*", "menu_location='$menu' AND menu_layout='$menuLayout' ORDER BY menu_order");
			$menu_count = $sql9->db_Rows();
			while (list($menu_id, $menu_name, $menu_location, $menu_order, $menu_class, $menu_pages, $menu_path) = $sql9->db_Fetch(MYSQL_NUM)) {
				$menu_name = preg_replace("#_menu#i", "", $menu_name);
				$vis = ($menu_class || strlen($menu_pages) > 1) ? " <span style='color:red'>*</span> " :
				"";
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

				$text .= "<select id='menuAct_$menu_id' name='menuAct[$menu_id]' class='tbox' onchange='this.form.submit()' >";
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
				foreach ($menu_areas as $menu_act) {
					if ($menu != $menu_act) {
						$text .= $frm->form_option(MENLAN_19." ".$menu_act, "", "move.{$menu_info}.".$menu_act);
					}
				}
				$text .= $frm->form_option(MENLAN_20, "", "adv.{$menu_info}");
				$text .= $frm->form_select_close();
				$ns->tablerender($caption, $text);
				echo "<div><br /></div>";
			}
			echo $frm->form_close();
		}
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
require_once("footer.php");



   /*
	if ($CUSTOMPAGES)
	{
		if ($menu_act != 'adv')
		{
			$text = "<form  method='post' action='".e_SELF."?configure.".$curLayout."'><div style='width: 100%'>
			<table class='fborder' style='".ADMIN_WIDTH."'>
			<tr>
			<td class='forumheader3' style='width: 90%'>
			".MENLAN_30."
			</td>
			<td class='forumheader3' style='width: 10%; text-align: center;'>";

			$text .= $frm->form_select_open('custom_select', 'onchange="this.form.submit()"');

			if ($curLayout == '' || $curLayout == 'default_layout')
			{
				$text .= $frm->form_option(MENLAN_31, 'selected', 'default_layout');
			}
			else
			{
				$text .= $frm->form_option(MENLAN_31, FALSE, 'default_layout');
			}

			if ($NEWSHEADER)
			{
				if ($curLayout == 'newsheader_layout')
				{
					$text .= $frm->form_option(MENLAN_32, 'selected', 'newsheader_layout');
				}
				else
				{
					$text .= $frm->form_option(MENLAN_32, FALSE, 'newsheader_layout');
				}
			}

			if ($CUSTOMPAGES)
			{
				if (is_array($CUSTOMPAGES))
				{
					foreach ($CUSTOMPAGES as $custom_pages_key => $custom_pages_value)
					{
						if ($curLayout == $custom_pages_key)
						{
							$text .= $frm->form_option($custom_pages_key, 'selected', $custom_pages_key);
						}
						else
						{
							$text .= $frm->form_option($custom_pages_key, FALSE, $custom_pages_key);
						}
					}
				}
				else
				{
					if ($curLayout == 'custom_layout')
					{
						$text .= $frm->form_option(MENLAN_33, 'selected', 'custom_layout');
					}
					else
					{
						$text .= $frm->form_option(MENLAN_33, FALSE, 'custom_layout');
					}
				}
			}

			$text .= $frm->form_select_close();

			$text .= "</td>
			</tr>
			</table></div>
			</form>";

			$ns->tablerender(MENLAN_29, $text);
		}
	}
*/
?>