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
|     $Source: /cvs_backup/e107_0.7/e107_admin/plugin.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-01-18 04:30:09 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("Z")){ header("location:".e_BASE."index.php"); exit; }
$e_sub_cat = 'plug_manage';
require_once("auth.php");
require_once(e_HANDLER."parser_handler.php");

//        check for new plugins, create entry in plugin table ...
$handle=opendir(e_PLUGIN);
while(false !== ($file = readdir($handle)))
{
        if($file != "." && $file != ".." && is_dir(e_PLUGIN.$file))
        {
                $plugin_handle=opendir(e_PLUGIN.$file."/");
                while(false !== ($file2 = readdir($plugin_handle)))
                {
                        if($file2 == "plugin.php")
                        {
                                include(e_PLUGIN.$file."/".$file2);
                                if(!$sql -> db_Select("plugin", "*", "plugin_name='$eplug_name'"))
                                {
                                        if(!$eplug_prefs && !$eplug_table_names && !$eplug_user_prefs && !$eplug_parse && !$eplug_userclass && !$eplug_module)
                                        {
                                                // new plugin, assign entry in plugin table, install is not necessary so mark it as intalled
                                                $sql -> db_Insert("plugin", "0, '$eplug_name', '$eplug_version', '$eplug_folder', 1");
                                        }
                                        else
                                        {
                                                // new plugin, assign entry in plugin table, install is necessary
                                                $sql -> db_Insert("plugin", "0, '$eplug_name', '$eplug_version', '$eplug_folder', 0");
                                        }
                                }
                        }
                }
                closedir($plugin_handle);
        }
}
closedir($handle);

$sql -> db_Select("plugin");
while($row = $sql -> db_fetch())
{
        if(!is_dir(e_PLUGIN.$row[plugin_path]))
        {
                $sql -> db_Delete("plugin", "plugin_path='{$row['plugin_path']}'");
        }
}

if(strstr(e_QUERY, "uninstall")){
        $tmp = explode(".", e_QUERY);
        $id = intval($tmp[1]); unset($tmp);

        $sql -> db_Select("plugin", "*", "plugin_id='$id' ");
        $row = $sql -> db_Fetch(); extract($row);
        $text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='".ADMIN_WIDTH."' class='fborder'>
<tr>
<td class='forumheader3' style='text-align:center'>".EPL_ADLAN_2."<br /><br />
<input class='button' type='submit' name='cancel' value='".EPL_CANCEL."' />
<input class='button' type='submit' name='confirm' value='".EPL_ADLAN_1." $plugin_name' />
    <input type='hidden' name='id' value='$id' />
</td>
</tr>
</table>

</form>
</div>";

    $ns -> tablerender(EPL_ADLAN_3.": <b>".$plugin_name." v".$plugin_version."</b>", $text);
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
        if($plugin_installflag){  //Uninstall Plugin
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
			if($eplug_module){
				$mods=explode(",",$pref['modules']);
				foreach($mods as $k => $v){
					if($v == $eplug_folder){unset($mods[$k]);}
				}
				$pref['modules'] = implode(",",$mods);
				save_prefs();
			}

			if($eplug_latest){
				$lats=explode(",",$pref['plug_latest']);
				foreach($lats as $k => $v){
					if($v == $eplug_folder){unset($lats[$k]);}
				}
				$pref['plug_latest'] = implode(",",$lats);
				save_prefs();
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
        //        install plugin ...
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
			if($eplug_module){
				$mods = explode(",",$pref['modules']);
				if(!in_array($eplug_folder,$mods)){
					$mods[]=$eplug_folder;
				}
				$pref['modules'] = implode(",",$mods);
				save_prefs();
			}

			if($eplug_latest){
				$lats = explode(",",$pref['plug_latest']);
				if(!in_array($eplug_folder,$lats)){
					$lats[]=$eplug_folder;
				}
				$pref['plug_latest'] = implode(",",$lats);
				save_prefs();
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
                                if(register_parser($eplug_folder,$e_regexp) == 2){
                                        $text .= EPL_ADLAN_36."<br />";
                                }
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
                                $sql -> db_Insert("userclass_classes", $i.", '".strip_tags(strtoupper($eplug_userclass))."', '{$eplug_userclass_description}' ,".e_UC_PUBLIC);
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

//        render plugin information ...
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='".ADMIN_WIDTH."' class='fborder'>";

$sql -> db_Select("plugin");
while($row = $sql -> db_Fetch()){
        extract($row);
        unset($eplug_compliant,$eplug_module, $eplug_parse, $eplug_name, $eplug_version, $eplug_author, $eplug_icon, $eplug_url, $eplug_email, $eplug_description, $eplug_compatible, $eplug_readme, $eplug_folder, $eplug_table_names, $eplug_userclass);
        include(e_PLUGIN.$plugin_path."/plugin.php");

        if($eplug_conffile || is_array($eplug_table_names) || is_array($eplug_prefs)  || is_array($eplug_user_prefs) || is_array($eplug_parse) || $eplug_module || $eplug_userclass){
                $img = (!$plugin_installflag ? "<img src='".e_IMAGE."generic/uninstalled.png' alt='' />" : "<img src='".e_IMAGE."generic/installed.png' alt='' />");
        }else{
                $img = "<img src='".e_IMAGE."generic/noinstall.png' alt='' />";
        }

        if($plugin_version != $eplug_version && $plugin_installflag){
                $img = "<img src='".e_IMAGE."generic/upgrade.png' alt='' />";
        }

		$plugin_icon = $eplug_icon ? "<img src='".e_PLUGIN.$eplug_icon."' alt='' style='vertical-align: bottom; width: 32px; height: 32px' />" : E_32_CAT_PLUG;
        $text .= "<tr>
        <td class='forumheader3' style='width:30%; text-align:center; vertical-align:top'>".$plugin_icon."<br /><br />


        $img <b>$plugin_name</b><br />version $plugin_version<br /></td>
        <td class='forumheader3' style='width:70%;vertical-align:top'>

        <table cellspacing='3' style='width:97%'>
        <tr><td style='vertical-align:top;width:24%'><b>".EPL_ADLAN_12."</b>:</td><td style='vertical-align:top'><a href='mailto:$eplug_email' title='$eplug_email'>$eplug_author</a>&nbsp;</td></tr>
        <tr><td style='vertical-align:top'><b>".EPL_WEBSITE."</b>:</td><td style='vertical-align:top'> $eplug_url&nbsp;</td></tr>
        <tr><td style='vertical-align:top'><b>".EPL_ADLAN_14."</b>:</td><td style='vertical-align:top'> $eplug_description&nbsp;</td></tr>
        <tr><td style='vertical-align:top'><b>Requires</b>:</td><td style='vertical-align:top'> $eplug_compatible&nbsp; </td></tr>\n";



        if($eplug_readme){
                $text .= "<tr><td><b>&nbsp;</b></td><td>[ <a href='".e_PLUGIN.$eplug_folder."/".$eplug_readme."'>".$eplug_readme."</a> ]</td></tr>";
        }



        if($eplug_conffile || $eplug_module || is_array($eplug_table_names) || is_array($eplug_prefs)  || is_array($eplug_user_prefs) || is_array($eplug_parse)){
                $text .= "<tr><td><b>".EPL_OPTIONS."</b>:</td><td> [ ".($plugin_installflag ? "<a href='".e_SELF."?uninstall.$plugin_id' title='".EPL_ADLAN_1."'> ".EPL_ADLAN_1."</a>" : "<a href='".e_SELF."?install.$plugin_id' title='".EPL_ADLAN_0."'>".EPL_ADLAN_0."</a>")." ]";
        }else{
                if($eplug_menu_name){
                        $text .= EPL_NOINSTALL.str_replace("..", "", e_PLUGIN.$plugin_path)."/ ".EPL_DIRECTORY;
                }else{
                        $text .= EPL_NOINSTALL_1.str_replace("..", "", e_PLUGIN.$plugin_path)."/ ".EPL_DIRECTORY;
                }
        }

        if($plugin_version != $eplug_version && $plugin_installflag){
                $text .= " [ <a href='".e_SELF."?upgrade.$plugin_id' title='".EPL_UPGRADE." to v".$eplug_version."'>".EPL_UPGRADE."</a> ]";
        }
       $text .= "</td></tr>";

        if($eplug_compliant){
                $text .= "<tr><td colspan='2' style='text-align:right'><img src='".e_IMAGE."generic/compliant.gif' alt='' /></td></tr>";
        }

        $text .= "</table>";

        $text .= "</td>
        </tr>";
}

$text .= "</table>
<div><br />
<img src='".e_IMAGE."generic/uninstalled.png' alt='' /> ".EPL_ADLAN_23."&nbsp;&nbsp;
<img src='".e_IMAGE."generic/installed.png' alt='' /> ".EPL_ADLAN_22."&nbsp;&nbsp;
<img src='".e_IMAGE."generic/upgrade.png' alt='' /> ".EPL_ADLAN_24."&nbsp;&nbsp;
<img src='".e_IMAGE."generic/noinstall.png' alt='' /> ".EPL_ADLAN_25."</div></form></div>";

$ns -> tablerender(Plugins, $text);
// ----------------------------------------------------------

require_once("footer.php");
?>