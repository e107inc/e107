<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	code adapted from original by Lolo Irie (lolo@touchatou.com)
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("Z")){ header("location:".e_HTTP."index.php"); exit; }
require_once("auth.php");
require_once(e_HANDLER."parser_handler.php");

//	check for new plugins, create entry in plugin table ...
$handle=opendir(e_PLUGIN);
while(false !== ($file = readdir($handle))){
	if($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)){
		$plugin_handle=opendir(e_PLUGIN.$file."/");
		while(false !== ($file2 = readdir($plugin_handle))){
			if($file2 == "plugin.php"){
				include(e_PLUGIN.$file."/".$file2);
				if(!$sql -> db_Select("plugin", "*", "plugin_name='$eplug_name'")){
					if($eplug_conffile && !$eplug_prefs && !$eplug_table_names){
						$sql -> db_Insert("plugin", "0, '$eplug_name', '$eplug_version', '$eplug_folder', 1");	// new plugin, assign entry in plugin table
					}else{
						$sql -> db_Insert("plugin", "0, '$eplug_name', '$eplug_version', '$eplug_folder', 0");	// new plugin, assign entry in plugin table
					}
				}
			}
		}
		closedir($plugin_handle);
	}
}
closedir($handle);

$sql -> db_Select("plugin");
while($row = $sql -> db_fetch()){
	if(!is_dir(e_PLUGIN.$row[plugin_path])){
		$sql -> db_Delete("plugin", "plugin_path='{$row['plugin_path']}'");
	}
}

if(strstr(e_QUERY, "uninstall")){
	$tmp = explode(".", e_QUERY);
	$id = $tmp[1]; unset($tmp);

	$sql -> db_Select("plugin", "*", "plugin_id='$id' ");
	$row = $sql -> db_Fetch(); extract($row);
	$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>
<tr>
<td class='forumheader3' style='text-align:center'>".EPL_ADLAN_2."<br /><br />
<input class='button' type='submit' name='cancel' value='".EPL_CANCEL."' />
<input class='button' type='submit' name='confirm' value='".EPL_ADLAN_1." $plugin_name' />
</td>
</tr>
</table>
<input type='hidden' name='id' value='$id'>
</form>
</div>";
	$ns -> tablerender(EPL_ADLAN_3, $text);
	require_once("footer.php");
	exit;
}

if(IsSet($_POST['cancel'])){
	$ns -> tablerender("", "<div style='text-align:center'>".EPL_ADLAN_4."</div>");
}

if(IsSet($_POST['confirm'])){

	$id = $_POST['id'];
	$sql -> db_Select("plugin", "*", "plugin_id='$id' ");
	$row = $sql -> db_Fetch(); extract($row);
	if($plugin_installflag){
		include(e_PLUGIN.$plugin_path."/plugin.php");
		if(is_array($eplug_tables)){
			while(list($key, $e_table) = each($eplug_table_names)){
				if(!mysql_query("DROP TABLE ".$mySQLprefix.$e_table)){
					$text .= EPL_ADLAN_27." <b>".$mySQLprefix.$e_table."</b> - ".EPL_ADLAN_30."<br />";
					$err_plug = TRUE;
				}
			}
			 if(!$err_plug){
				$text .= EPL_ADLAN_28."<br />";
			}
		}
	
		if(is_array($eplug_prefs)){
			while(list($key, $e_pref) = each($eplug_prefs)){
				unset($pref[$key]);
			}
			save_prefs();
			$text .= EPL_ADLAN_29."<br />";
		}
		if(is_array($eplug_user_prefs)){
			$sql = new db;
			$sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
			$row = $sql -> db_Fetch();
			$user_entended = unserialize($row[0]);
			$user_entended = array_values(array_diff($user_entended, array_keys($eplug_user_prefs)));
			if($user_entended == NULL){
				$sql -> db_Delete("core", "e107_name='user_entended'");
			}else{
				$tmp = addslashes(serialize($user_entended));
				$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
			}
			while(list($key, $e_user_pref) = each($eplug_user_prefs)){
				unset($user_pref[$key]);
			}
			save_prefs("user");
		}
		if($eplug_menu_name){
			$sql -> db_Delete("menus", "menu_name='$eplug_menu_name' ");
		}

		if($eplug_link){
			$sql -> db_Delete("links", "link_name='$eplug_link_name' ");
		}

		if($eplug_userclass){
			$sql -> db_Delete("userclass_classes", "userclass_name='$eplug_userclass' ");
		}

		if(is_array($eplug_parse)){
			$sql -> db_Delete("parser","parser_pluginname='".$eplug_folder."'");
		}

		$sql -> db_Update("plugin", "plugin_installflag=0, plugin_version='$eplug_version' WHERE plugin_id='$id' ");
		$text .= "<br />".EPL_ADLAN_31." <b>".e_PLUGIN.$eplug_folder."</b> ".EPL_ADLAN_32;
		$ns->tablerender(EPL_ADLAN_1." ".$eplug_name, $text);
	}
}

if(strstr(e_QUERY, "install")){
	//	install plugin ...
	$tmp = explode(".", e_QUERY);
	$id = $tmp[1]; unset($tmp);

	$sql -> db_Select("plugin", "*", "plugin_id='$id' ");
	$row = $sql -> db_Fetch(); extract($row);
	if(!$plugin_installflag){
		include(e_PLUGIN.$plugin_path."/plugin.php");


		if(is_array($eplug_tables)){
			while(list($key, $e_table) = each($eplug_tables)){
				if(!mysql_query($e_table)){
					$text .= EPL_ADLAN_18."<br />";
					$err_plug = TRUE;
					break;
				}
			}
			 if(!$err_plug){
				$text .= EPL_ADLAN_19."<br />";
			 }
		}

		if(is_array($eplug_prefs)){
			while(list($key, $e_pref) = each($eplug_prefs)){
				if(!in_array($pref, $e_pref)){
					$pref[$key] = $e_pref;
				}
			}
			save_prefs();
			$text .= EPL_ADLAN_20."<br />";
			
		}
		if(is_array($eplug_user_prefs)){
			$sql = new db;
			$sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
			$row = $sql -> db_Fetch();
			$user_entended = unserialize($row[0]);
			while(list($e_user_pref, $default_value) = each($eplug_user_prefs)){
			    $user_entended[] = $e_user_pref;
				$user_pref['$e_user_pref'] = $default_value;
			}
			save_prefs("user");
			$tmp = addslashes(serialize($user_entended));
			if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
				$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
			}else{
				$sql -> db_Insert("core", "'user_entended', '$tmp' ");
			}
			$text .= EPL_ADLAN_20."<br />";
		}

		if(is_array($eplug_parse)){
			while(list($key, $e_regexp) = each($eplug_parse)){
				register_parser($eplug_folder,$e_regexp);
			}
			$text .= EPL_ADLAN_35."<br />";
		}

		if($eplug_link){
			$path = str_replace("../", "", $eplug_link_url);
			if(!$sql -> db_Select("links", "*", "link_name='$eplug_link_name' ")){
				$sql -> db_Insert("links", "0, '$eplug_link_name', '$path', '', '', '1', '0', '0', '0', '' ");
			}
		}

		if($eplug_userclass){
			$i=1;
			while($sql -> db_Select("userclass_classes", "*", "userclass_id='".$i."' ") && $i<255){
				$i++;
			}
			if($i<255){
				$sql -> db_Insert("userclass_classes", $i.", '".strip_tags(strtoupper($eplug_userclass))."', '$eplug_userclass_description' ");
			}
		}

		$sql -> db_Update("plugin", "plugin_installflag=1 WHERE plugin_id='$id' ");
		$text .= ($eplug_done ? "<br />".$eplug_done : "");
	}else{
		$text = EPL_ADLAN_21;
	}
	
	$ns->tablerender(EPL_ADLAN_33, $text);


}

if(strstr(e_QUERY, "upgrade")){
	$tmp = explode(".", e_QUERY);
	$id = $tmp[1]; unset($tmp);

	$sql -> db_Select("plugin", "*", "plugin_id='$id' ");
	$row = $sql -> db_Fetch(); extract($row);
	include(e_PLUGIN.$plugin_path."/plugin.php");

	if(is_array($upgrade_alter_tables)){
		while(list($key, $e_table) = each($upgrade_alter_tables)){
			if(!mysql_query($e_table)){
				$text .= EPL_ADLAN_9."<br />";
				$err_plug = TRUE;
				break;
			}
		}
		 if(!$err_plug){
			$text .= EPL_ADLAN_7."<br />";
		 }
	}

	if(is_array($eplug_parse)){
		while(list($key, $e_regexp) = each($eplug_parse)){
			$p_added .= register_parser($eplug_folder,$e_regexp);
		}
		if($p_added){
			$text .= EPL_ADLAN_35."<br />";
		}
	}

	if(is_array($upgrade_add_prefs)){
		while(list($key, $e_pref) = each($upgrade_add_prefs)){
			if(!in_array($pref, $e_pref)){
				$pref[$key] = $e_pref;
			}
		}
		save_prefs();
		$text .= EPL_ADLAN_8."<br />";
			
	}			

	if(is_array($upgrade_remove_prefs)){
		while(list($key, $e_pref) = each($upgrade_remove_prefs)){
			unset($pref[$key]);
		}
		save_prefs();
	}
	if(is_array($upgrade_add_user_prefs)){
		$sql = new db;
		$sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
		$row = $sql -> db_Fetch();
		$user_entended = unserialize($row[0]);
		while(list($key, $e_user_pref) = each($eplug_user_prefs)){
		    $user_entended[] = $e_user_pref;
		}
		$tmp = addslashes(serialize($user_entended));
		if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
			$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
		}else{
			$sql -> db_Insert("core", "'user_entended', '$tmp' ");
		}
		$text .= EPL_ADLAN_8."<br />";
	}

	if(is_array($upgrade_remove_user_prefs)){
		$sql = new db;
		$sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
		$row = $sql -> db_Fetch();
		$user_entended = unserialize($row[0]);
		$user_entended = array_values(array_diff($user_entended, $eplug_user_prefs));
		if($user_entended == NULL){
			$sql -> db_Delete("core", "e107_name='user_entended'");
		}else{
			$tmp = addslashes(serialize($user_entended));
			$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
		}
	}

	$text .= "<br />".$eplug_upgrade_done;

	$sql -> db_Update("plugin", "plugin_version ='$eplug_version' WHERE plugin_id='$id' ");

	$ns->tablerender(EPL_ADLAN_34, $text);

}

// ----------------------------------------------------------

//	render plugin information ...
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:95%' class='fborder'>";

$sql -> db_Select("plugin");
while($row = $sql -> db_Fetch()){
	extract($row);
	unset($eplug_name, $eplug_version, $eplug_author, $eplug_logo, $eplug_url, $eplug_email, $eplug_description, $eplug_compatible, $eplug_readme, $eplug_folder, $eplug_table_names);
	include(e_PLUGIN.$plugin_path."/plugin.php");

	if(is_array($eplug_table_names) || is_array($eplug_prefs)  || is_array($eplug_user_prefs) || is_array($eplug_parse)){
		$img = (!$plugin_installflag ? "<img src='".e_IMAGE."generic/uninstalled.png' alt='' />" : "<img src='".e_IMAGE."generic/installed.png' alt='' />");
	}else{
		$img = "<img src='".e_IMAGE."generic/noinstall.png' alt='' />";
	}

	if($plugin_version != $eplug_version && $plugin_installflag){
		$img = "<img src='".e_IMAGE."generic/upgrade.png' alt='' />";
	}

	$text .= "<tr>
	<td class='forumheader3' style='width:30%; text-align:center; vertical-align:middle'>".($eplug_logo && $eplug_logo != "button.png" ? "<img src='".e_PLUGIN.$eplug_folder."/".$eplug_logo."' alt='' /><br /><br />" : "")."
		
	
	$img <b>$plugin_name</b><br />version $plugin_version<br />
	<td class='forumheader3' style='width:70%'><b>".EPL_ADLAN_12."</b>: $eplug_author<br />[ ".EPL_EMAIL.": $eplug_email | ".EPL_WEBSITE.": $eplug_url ]<br />
	<b>".EPL_ADLAN_14."></b>: $eplug_description<br />\n";
	if($eplug_readme){
		$text .= "[ <a href='".e_PLUGIN.$eplug_folder."/".$eplug_readme."'>".$eplug_readme."</a> ]<br />";
	}
	if(is_array($eplug_table_names) || is_array($eplug_prefs)  || is_array($eplug_user_prefs) || is_array($eplug_parse)){
		$text .= "<b>".EPL_OPTIONS."</b>: [ ".($plugin_installflag ? "<a href='".e_SELF."?uninstall.$plugin_id'> ".EPL_ADLAN_1."</a>" : "<a href='".e_SELF."?install.$plugin_id'>".EPL_ADLAN_0."</a>")." ]";
	}else{
		if($eplug_menu_name){
			$text .= EPL_NOINSTALL.str_replace("..", "", e_PLUGIN.$plugin_path)."/ ".EPL_DIRECTORY.";
		}else{
			$text .= EPL_NOINSTALL_1.str_replace("..", "", e_PLUGIN.$plugin_path)."/ ".EPL_DIRECTORY.";
		}
	}
	if($plugin_version != $eplug_version && $plugin_installflag){
		$text .= " [ <a href='".e_SELF."?upgrade.$plugin_id'>".EPL_UPGRADE."</a> ]";
	}
	$text .= "</td>
	</tr>";
}

$text .= "</table>
<br />
<img src='".e_IMAGE."generic/uninstalled.png' alt='' /> ".EPL_ADLAN_23."&nbsp;&nbsp;
<img src='".e_IMAGE."generic/installed.png' alt='' /> ".EPL_ADLAN_22."&nbsp;&nbsp;
<img src='".e_IMAGE."generic/upgrade.png' alt='' /> ".EPL_ADLAN_24."&nbsp;&nbsp;
<img src='".e_IMAGE."generic/noinstall.png' alt='' /> ".EPL_ADLAN_25."</div>";

$ns -> tablerender(Plugins, $text);
// ----------------------------------------------------------





require_once("footer.php");
?>	