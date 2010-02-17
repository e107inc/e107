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
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms("Z")) 
{
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'plug_manage';
require_once("auth.php");
require_once(e_HANDLER.'plugin_class.php');
require_once(e_HANDLER.'file_class.php');
$plugin = new e107plugin;

$tmp = explode('.', e_QUERY);
$action = $tmp[0];
$id = intval($tmp[1]);

if (isset($_POST['upload'])) 
{
	if (!$_POST['ac'] == md5(ADMINPWCHANGE)) 
	{
		exit;
	}

	extract($_FILES);
	/* check if e_PLUGIN dir is writable ... */
	if(!is_writable(e_PLUGIN))
	{
		/* still not writable - spawn error message */
		$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_39);
	}
	else
	{
		/* e_PLUGIN is writable - continue */
		$pref['upload_storagetype'] = "1";
		require_once(e_HANDLER."upload_handler.php");
		$fileName = $file_userfile['name'][0];
		$fileSize = $file_userfile['size'][0];
		$fileType = $file_userfile['type'][0];

		if(strstr($file_userfile['type'][0], "gzip"))
		{
			$fileType = "tar";
		}
		else if (strstr($file_userfile['type'][0], "zip"))
		{
			$fileType = "zip";
		}
		else
		{
			/* not zip or tar - spawn error message */
			$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_41);
			require_once("footer.php");
			exit;
		}

		if ($fileSize)
		{

			$opref = $pref['upload_storagetype'];
			$pref['upload_storagetype'] = 1;		/* temporarily set upload type pref to flatfile */
			$uploaded = file_upload(e_PLUGIN);
			$pref['upload_storagetype'] = $opref;

			$archiveName = $uploaded[0]['name'];

			/* attempt to unarchive ... */

			if($fileType == "zip")
			{
				require_once(e_HANDLER."pclzip.lib.php");
				$archive = new PclZip(e_PLUGIN.$archiveName);
				$unarc = ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_PLUGIN, PCLZIP_OPT_SET_CHMOD, 0666));
			}
			else
			{
				require_once(e_HANDLER."pcltar.lib.php");
				$unarc = ($fileList = PclTarExtract($archiveName, e_PLUGIN));
			}

			if(!$unarc)
			{
				/* unarc failed ... */
				if($fileType == "zip")
				{
					$error = EPL_ADLAN_46." '".$archive -> errorName(TRUE)."'";
				}
				else
				{
					$error = EPL_ADLAN_47.PclErrorString().", ".EPL_ADLAN_48.intval(PclErrorCode());
				}
				$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_42." ".$archiveName." ".$error);
				require_once("footer.php");
				exit;
			}

			/* ok it looks like the unarc succeeded - continue */

			/* get folder name ... */
			$folderName = substr($fileList[0]['stored_filename'], 0, (strpos($fileList[0]['stored_filename'], "/")));

			if(file_exists(e_PLUGIN.$folderName."/plugin.php")) {
				/* upload is a plugin */
				$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_43);
			}
			else
			{
				/* upload is a menu */
				$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_45);
			}

			/* attempt to delete uploaded archive */
			@unlink(e_PLUGIN.$archiveName);
		}
	}
}


if ($action == 'uninstall')
{
	if(!isset($_POST['uninstall_confirm']))
	{
		show_uninstall_confirm();
		exit;
	}

	$id = intval($id);
	$plug = $plugin->getinfo($id);
	//Uninstall Plugin
	if ($plug['plugin_installflag'] == TRUE ) 
	{
		include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

		$func = $eplug_folder.'_uninstall';
			if (function_exists($func))
			{
			$text .= call_user_func($func);
		}

		if($_POST['delete_tables'])
		{
			if (is_array($eplug_table_names))
			{
				$result = $plugin->manage_tables('remove', $eplug_table_names);
				if ($result !== TRUE)
				{
					$text .= EPL_ADLAN_27.' <b>'.$mySQLprefix.$result.'</b> - '.EPL_ADLAN_30.'<br />';
				}
				else
				{
					$text .= EPL_ADLAN_28."<br />";
				}
			}
		}
		else
		{
			$text .= EPL_ADLAN_49."<br />";
		}

			if (is_array($eplug_prefs))
			{
			$plugin->manage_prefs('remove', $eplug_prefs);
			$text .= EPL_ADLAN_29."<br />";
		}

			if (is_array($eplug_comment_ids))
			{
			$text .= ($plugin->manage_comments('remove', $eplug_comment_ids)) ? EPL_ADLAN_50."<br />" : "";
		}


		if ($eplug_module) 
		{
			$plugin->manage_plugin_prefs('remove', 'modules', $eplug_folder);
		}
			if ($eplug_status)
			{
			$plugin->manage_plugin_prefs('remove', 'plug_status', $eplug_folder);
		}

			if ($eplug_latest)
			{
			$plugin->manage_plugin_prefs('remove', 'plug_latest', $eplug_folder);
		}



		if (is_array($eplug_array_pref))
		{
			foreach($eplug_array_pref as $key => $val)
			{
				$plugin->manage_plugin_prefs('remove', $key, $eplug_folder, $val);
			}
		}

		if (is_array($eplug_sc))
		{
			$plugin->manage_plugin_prefs('remove', 'plug_sc', $eplug_folder, $eplug_sc);
		}

		if (is_array($eplug_bb))
		{
			$plugin->manage_plugin_prefs('remove', 'plug_bb', $eplug_folder, $eplug_bb);
		}

		if (is_array($eplug_user_prefs)) {
			if (!is_object($sql)){ $sql = new db; }
			$sql->db_Select("core", " e107_value", " e107_name='user_entended'");
			$row = $sql->db_Fetch();
			$user_entended = unserialize($row[0]);
			$user_entended = array_values(array_diff($user_entended, array_keys($eplug_user_prefs)));
			if ($user_entended == NULL) {
				$sql->db_Delete("core", "e107_name='user_entended'");
			} else {
				$tmp = addslashes(serialize($user_entended));
				$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
			}
			while (list($key, $e_user_pref) = each($eplug_user_prefs)) {
				unset($user_pref[$key]);
			}
			save_prefs("user");
		}

		if ($eplug_menu_name) {
			$sql->db_Delete('menus', "menu_name='$eplug_menu_name' ");
		}

			if ($eplug_link)
			{
			$plugin->manage_link('remove', $eplug_link_url, $eplug_link_name);
		}

			if ($eplug_userclass)
			{
			$plugin->manage_userclass('remove', $eplug_userclass);
		}

		$plugin -> manage_search('remove', $eplug_folder);

		$plugin -> manage_notify('remove', $eplug_folder);

		$sql->db_Update('plugin', "plugin_installflag=0, plugin_version='{$eplug_version}' WHERE plugin_id='{$id}' ");
		if (isset($pref['plug_installed'][$plug['plugin_path']]))
		{
		  unset($pref['plug_installed'][$plug['plugin_path']]);
		  save_prefs();
		}


		if($_POST['delete_files'])
		{
			include_once(e_HANDLER."file_class.php");
			$fi = new e_file;
			$result = $fi->rmtree(e_PLUGIN.$eplug_folder);
			$text .= ($result ? "<br />All files removed from ".e_PLUGIN.$eplug_folder : '<br />File deletion failed<br />'.EPL_ADLAN_31.' <b>'.e_PLUGIN.$eplug_folder.'</b> '.EPL_ADLAN_32);
		}
		else
		{
			$text .= '<br />'.EPL_ADLAN_31.' <b>'.e_PLUGIN.$eplug_folder.'</b> '.EPL_ADLAN_32;
		}
		$ns->tablerender(EPL_ADLAN_1.' '.$tp->toHtml($eplug_name,"","defs,emotes_off, no_make_clickable"), $text);
		$text = "";
		$plugin -> save_addon_prefs();
	}
}

if ($action == 'install') {
	$plugin->install_plugin(intval($id));
	$plugin ->save_addon_prefs();
}

if ($action == 'upgrade')
{
	$plug = $plugin->getinfo($id);
	include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

	$func = $eplug_folder.'_upgrade';
		if (function_exists($func))
		{
		$text .= call_user_func($func);
	}

		if (is_array($upgrade_alter_tables))
		{
		$result = $plugin->manage_tables('upgrade', $upgrade_alter_tables);
			if (!$result)
			{
			$text .= EPL_ADLAN_9.'<br />';
			}
			else
			{
			$text .= EPL_ADLAN_7."<br />";
		}
	}

	if ($eplug_module) 
	{
		$plugin->manage_plugin_prefs('add', 'modules', $eplug_folder);
		}
		else
		{
		$plugin->manage_plugin_prefs('remove', 'modules', $eplug_folder);
	}

		if ($eplug_status)
		{
		$plugin->manage_plugin_prefs('add', 'plug_status', $eplug_folder);
		}
		else
		{
		$plugin->manage_plugin_prefs('remove', 'plug_status', $eplug_folder);
	}

		if ($eplug_latest)
		{
		$plugin->manage_plugin_prefs('add', 'plug_latest', $eplug_folder);
		}
		else
		{
		$plugin->manage_plugin_prefs('remove', 'plug_latest', $eplug_folder);
	}

	if (is_array($upgrade_add_eplug_sc))
	{
		$plugin->manage_plugin_prefs('add', 'plug_sc', $eplug_folder, $eplug_sc);
	}

	if (is_array($upgrade_remove_eplug_sc))
	{
		$plugin->manage_plugin_prefs('remove', 'plug_sc', $eplug_folder, $eplug_sc);
	}

	if (is_array($upgrade_add_eplug_bb))
	{
		$plugin->manage_plugin_prefs('add', 'plug_bb', $eplug_folder, $eplug_bb);
	}

	if (is_array($upgrade_remove_eplug_bb))
	{
		$plugin->manage_plugin_prefs('remove', 'plug_bb', $eplug_folder, $eplug_bb);
	}

	if (is_array($upgrade_add_prefs)) 
	{
		$plugin->manage_prefs('add', $upgrade_add_prefs);
		$text .= EPL_ADLAN_8.'<br />';
	}

		if (is_array($upgrade_remove_prefs))
		{
		$plugin->manage_prefs('remove', $upgrade_remove_prefs);
	}

	if (is_array($upgrade_add_array_pref))
	{
	  foreach($upgrade_add_array_pref as $key => $val)
	  {
		$plugin->manage_plugin_prefs('add', $key, $eplug_folder, $val);
	  }
	}

	if (is_array($upgrade_remove_array_pref))
	{
	  foreach($upgrade_remove_array_pref as $key => $val)
	  {
		$plugin->manage_plugin_prefs('remove', $key, $eplug_folder, $val);
	  }
	}

	if (is_array($upgrade_add_user_prefs)) {
		if (!is_object($sql)){ $sql = new db; }
		$sql->db_Select("core", " e107_value", " e107_name='user_entended'");
		$row = $sql->db_Fetch();
		$user_entended = unserialize($row[0]);
		while (list($key, $e_user_pref) = each($eplug_user_prefs)) {
			$user_entended[] = $e_user_pref;
		}
		$tmp = addslashes(serialize($user_entended));
		if ($sql->db_Select("core", " e107_value", " e107_name='user_entended'")) {
			$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
		} else {
			$sql->db_Insert("core", "'user_entended', '$tmp' ");
		}
		$text .= EPL_ADLAN_8."<br />";
	}

	if (is_array($upgrade_remove_user_prefs)) {
		if (!is_object($sql)){ $sql = new db; }  
		$sql->db_Select("core", " e107_value", " e107_name='user_entended'");
		$row = $sql->db_Fetch();
		$user_entended = unserialize($row[0]);
		$user_entended = array_values(array_diff($user_entended, $eplug_user_prefs));
		if ($user_entended == NULL) {
			$sql->db_Delete("core", "e107_name='user_entended'");
		} else {
			$tmp = addslashes(serialize($user_entended));
			$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
		}
	}

	$plugin -> manage_search('upgrade', $eplug_folder);
	$plugin -> manage_notify('upgrade', $eplug_folder);

    $eplug_addons = $plugin -> getAddons($eplug_folder);

	$text .= (isset($eplug_upgrade_done)) ? '<br />'.$eplug_upgrade_done : "<br />".LAN_UPGRADE_SUCCESSFUL;
	$sql->db_Update('plugin', "plugin_version ='{$eplug_version}', plugin_addons='{$eplug_addons}' WHERE plugin_id='$id' ");
	$pref['plug_installed'][$plug['plugin_path']] = $eplug_version; 			// Update the version
	save_prefs();			
	$ns->tablerender(EPL_ADLAN_34, $text);

	$plugin -> save_addon_prefs();
}


// Check for new plugins, create entry in plugin table ...

$plugin->update_plugins_table();

// ----------------------------------------------------------
//        render plugin information ...

/* plugin upload form */

if(!is_writable(e_PLUGIN))
{
	$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_44);
} 
else 
{
	$text = "<div style='text-align:center'>
	<form enctype='multipart/form-data' method='post' action='".e_SELF."'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td class='forumheader3' style='width: 50%;'>".EPL_ADLAN_37."</td>
	<td class='forumheader3' style='width: 50%;'>
	<input type='hidden' name='MAX_FILE_SIZE' value='1000000' />
	<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
	<input class='tbox' type='file' name='file_userfile[]' size='50' />
	</td>
	</tr>
	<tr>
	<td colspan='2' style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='upload' value='".EPL_ADLAN_38."' />
	</td>
	</tr>
	</table>
	</form>
	<br />\n";
}
// Uninstall and Install sorting should be fixed once and for all now !
$installed = $plugin->getall(1);
$uninstalled = $plugin->getall(0);

$text .= "<table style='".ADMIN_WIDTH."' class='fborder'>";
$text .= "<tr><td class='fcaption' colspan='3'>".EPL_ADLAN_22."</td></tr>";
$text .= render_plugs($installed);
$text .= "<tr><td class='fcaption' colspan='3'>".EPL_ADLAN_23."</td></tr>";
$text .= render_plugs($uninstalled);


function render_plugs($pluginList)
{
	global $tp;

	if (empty($pluginList)) return '';

	foreach($pluginList as $plug) 
	{
	//Unset any possible eplug_ variables set by last plugin.php
		$defined_vars = array_keys(get_defined_vars());
		foreach($defined_vars as $varname) {
			if (substr($varname, 0, 6) == 'eplug_' || substr($varname, 0, 8) == 'upgrade_') {
				unset($$varname);
			}
		}

		include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

		// See whether plugin needs install - it does if any of the 'configuration' variables are set
 		if ($eplug_conffile || is_array($eplug_table_names) || is_array($eplug_prefs) || is_array($eplug_array_pref) || is_array($eplug_user_prefs) || is_array($eplug_sc) || is_array($eplug_bb) || $eplug_module || $eplug_userclass || $eplug_status || $eplug_latest) {
			$img = (!$plug['plugin_installflag'] ? "<img src='".e_IMAGE."admin_images/uninstalled.png' alt='' />" : "<img src='".e_IMAGE."admin_images/installed.png' alt='' />");
		} else {
			$img = "<img src='".e_IMAGE."admin_images/noinstall.png' alt='' />";
		}

		if ($plug['plugin_version'] != $eplug_version && $plug['plugin_installflag']) {
			$img = "<img src='".e_IMAGE."admin_images/upgrade.png' alt='' />";
		}

		$plugin_icon = $eplug_icon ? "<img src='".e_PLUGIN.$eplug_icon."' alt='' style='border:0px;vertical-align: bottom; width: 32px; height: 32px' />" : E_32_CAT_PLUG;
		if ($eplug_conffile && $plug['plugin_installflag'] == TRUE) {
			$conf_title = LAN_CONFIGURE.' '.$tp->toHtml($eplug_name,"","defs,emotes_off, no_make_clickable");
			$plugin_icon = "<a title='{$conf_title}' href='".e_PLUGIN.$eplug_folder.'/'.$eplug_conffile."' >".$plugin_icon.'</a>';
		}

		$text .= "
		<tr>
		<td class='forumheader3' style='width:160px; text-align:center; vertical-align:top'>
		<table style='width:100%'><tr><td style='text-align:left;width:40px;vertical-align:top'>
		".$plugin_icon."
		</td><td>
		$img <b>".$tp->toHTML($plug['plugin_name'],FALSE,"defs,emotes_off, no_make_clickable")."</b><br />".EPL_ADLAN_11." {$plug['plugin_version']}
		<br />";

		$text .="</td>
		</tr></table>
		</td>
		<td class='forumheader3' style='vertical-align:top'>
		<table cellspacing='3' style='width:98%'>
		<tr><td style='vertical-align:top;width:15%'><b>".EPL_ADLAN_12."</b>:</td><td style='vertical-align:top'><a href='mailto:$eplug_email' title='$eplug_email'>$eplug_author</a>&nbsp;";
        if($eplug_url){
        	$text .= "&nbsp;&nbsp;[ <a href='$eplug_url' title='$eplug_url' >".EPL_WEBSITE."</a> ] ";
		}
		$text .="</td></tr>
		<tr><td style='vertical-align:top'><b>".EPL_ADLAN_14."</b>:</td><td style='vertical-align:top'> $eplug_description&nbsp;";
        if ($eplug_readme) {
			$text .= "[ <a href='".e_PLUGIN.$eplug_folder."/".$eplug_readme."'>".$eplug_readme."</a> ]";
		}

		$text .="</td></tr>
		<tr><td style='vertical-align:top'><b>".EPL_ADLAN_13."</b>:</td><td style='vertical-align:top'><span style='vertical-align:top'> $eplug_compatible&nbsp;</span>";
    	if ($eplug_compliant) {
			$text .= "&nbsp;&nbsp;<img src='".e_IMAGE."generic/valid-xhtml11_small.png' alt='' style='margin-top:0px' />";
		}
		$text .="</td></tr>\n";


		$text .= "</table></td>";
		$text .= "<td class='forumheader3' style='width:70px;text-align:center'>";
    	if ($eplug_conffile || $eplug_module || $eplug_link || is_array($eplug_table_names) || is_array($eplug_prefs) || is_array($eplug_user_prefs) || is_array($eplug_sc) || is_array($eplug_bb) || $eplug_status || $eplug_latest) {
			$text .= ($plug['plugin_installflag'] ? "<input type='button' class='button' onclick=\"location.href='".e_SELF."?uninstall.{$plug['plugin_id']}'\" title='".EPL_ADLAN_1."' value='".EPL_ADLAN_1."' /> " : "<input type='button' class='button' onclick=\"location.href='".e_SELF."?install.{$plug['plugin_id']}'\" title='".EPL_ADLAN_0."' value='".EPL_ADLAN_0."' />");
		} 
		else 
		{
	   		if ($eplug_menu_name) 
			{
				$text .= EPL_NOINSTALL.str_replace("..", "", e_PLUGIN.$plug['plugin_path'])."/ ".EPL_DIRECTORY;
			} 
			else 
			{
				$text .= EPL_NOINSTALL_1.str_replace("..", "", e_PLUGIN.$plug['plugin_path'])."/ ".EPL_DIRECTORY;
				if($plug['plugin_installflag'] == FALSE)
				{
					global $sql;
					$sql->db_Delete('plugin', "plugin_installflag=0 AND (plugin_path='{$plug['plugin_path']}' OR plugin_path='{$plug['plugin_path']}/' )  ");
				}
			}
		}

		if ($plug['plugin_version'] != $eplug_version && $plug['plugin_installflag']) {
			$text .= "<br /><input type='button' class='button' onclick=\"location.href='".e_SELF."?upgrade.{$plug['plugin_id']}'\" title='".EPL_UPGRADE." to v".$eplug_version."' value='".EPL_UPGRADE."' />";
		}

		$text .="</td>";
		$text .= "</tr>";
	}


return $text;
}

$text .= "</table>
	<div style='text-align:center'><br />
	<img src='".e_IMAGE."admin_images/uninstalled.png' alt='' /> ".EPL_ADLAN_23."&nbsp;&nbsp;
	<img src='".e_IMAGE."admin_images/installed.png' alt='' /> ".EPL_ADLAN_22."&nbsp;&nbsp;
	<img src='".e_IMAGE."admin_images/upgrade.png' alt='' /> ".EPL_ADLAN_24."&nbsp;&nbsp;
	<img src='".e_IMAGE."admin_images/noinstall.png' alt='' /> ".EPL_ADLAN_25."</div></div>";

$ns->tablerender(EPL_ADLAN_16, $text);
// ----------------------------------------------------------

require_once("footer.php");
exit;


function show_uninstall_confirm()
{
	global $plugin, $tp, $id, $ns;
	$id = intval($id);
	$plug = $plugin->getinfo($id);

	if ($plug['plugin_installflag'] == TRUE )
	{
		include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');
	}

	if(is_writable(e_PLUGIN.$plug['plugin_path']))
	{
		$del_text = "
		<select class='tbox' name='delete_files'>
		<option value='0'>".LAN_NO."</option>
		<option value='1'>".LAN_YES."</option>
		</select>
		";
	}
	else
	{
		$del_text = "
		".EPL_ADLAN_53."
		<input type='hidden' name='delete_files' value='0' />
		";
	}

	$text = "
	<form action='".e_SELF."?".e_QUERY."' method='post'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
		<td colspan='2' class='forumheader'>".EPL_ADLAN_54." ".$tp->toHtml($eplug_name,"","defs,emotes_off, no_make_clickable")."</td>
	</tr>
	<tr>
		<td class='forumheader3'>".EPL_ADLAN_55."</td>
		<td class='forumheader3'>".LAN_YES."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>
			".EPL_ADLAN_57."<div class='smalltext'>".EPL_ADLAN_58."</div>
		</td>
		<td class='forumheader3'>
			<select class='tbox' name='delete_tables'>
			<option value='1'>".LAN_YES."</option>
			<option value='0'>".LAN_NO."</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class='forumheader3'>".EPL_ADLAN_59."<div class='smalltext'>".EPL_ADLAN_60."</div></td>
		<td class='forumheader3'>{$del_text}</td>
	</tr>
	<tr>
		<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='uninstall_confirm' value=\"".EPL_ADLAN_3."\" />&nbsp;&nbsp;<input class='button' type='submit' name='uninstall_cancel' value='".EPL_ADLAN_62."' onclick=\"location.href='".e_SELF."'; return false;\"/></td>
	</tr>
	</table>
	</form>
	";
	$ns->tablerender(EPL_ADLAN_63." ".$tp->toHtml($eplug_name,"","defs,emotes_off, no_make_clickable"), $text);
	require_once(e_ADMIN."footer.php");
	exit;
}

?>
