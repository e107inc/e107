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
|     $Revision: 1.26 $
|     $Date: 2005-02-19 17:06:34 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("Z")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'plug_manage';
require_once("auth.php");
require_once(e_HANDLER.'plugin_class.php');
$plugin = new e107plugin;

$tmp = explode('.', e_QUERY);
$action = $tmp[0];
$id = intval($tmp[1]);

if (isset($_POST['upload'])) {
	if (!$_POST['ac'] == md5(ADMINPWCHANGE)) {
		exit;
	}

//	echo "<pre>"; print_r($_FILES); echo "</pre>"; exit;

	extract($_FILES);
	/* check if e_PLUGIN dir is writable ... */
	if(!is_writable(e_PLUGIN)) {
		/* still not writable - spawn error message */
		$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_39);
	} else {
		/* e_PLUGIN is writable - continue */
		$pref['upload_storagetype'] = "1";
		require_once(e_HANDLER."upload_handler.php");
		$fileName = $file_userfile['name'][0];
		$fileSize = $file_userfile['size'][0];
		$fileType = $file_userfile['type'][0];
		
		if($fileType != "application/x-zip-compressed" && $fileType != "application/x-gzip-compressed" && $fileType != "application/x-zip") {
			/* not zip or tar - spawn error message */
			$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_41);
			require_once("footer.php");
			exit;
		}

		if ($fileSize) {

			$opref = $pref['upload_storagetype'];
			$pref['upload_storagetype'] = 1;		/* temporarily set upload type pref to flatfile */
			$uploaded = file_upload(e_PLUGIN);
			$pref['upload_storagetype'] = $opref;

			$archiveName = $uploaded[0]['name'];	/* update name - it'll have been renamed by upload handler */

			/* attempt to unarchive ... */

			if($fileType == "application/x-zip-compressed" || $fileType == "application/x-zip") {
				require_once(e_HANDLER."pclzip.lib.php");
				$archive = new PclZip(e_PLUGIN.$archiveName);
				if($archive->extract(PCLZIP_OPT_PATH, e_PLUGIN)) {
					$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_43);
				} else {
					$error = "PCLZIP extract error: ".$archive -> errorName(true);
					$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_42." ".$archiveName." ".$error);
				}
			} else {
				require_once(e_HANDLER."pcltar.lib.php");
				if(PclTarExtract($archiveName, e_PLUGIN)) {
					$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_43);
				} else {
					$error = "PCLTAR extract error: ".PclErrorString().", code: ".intval(PclErrorCode());
					$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_42." ".$archiveName." ".$error);
				}
			}
			/* attempt to delete uploaded archive */
			@unlink(e_PLUGIN.$archiveName);
		}
	}
}

if ($action == 'uninstall') {
	$plug = $plugin->getinfo($id);
	$text = "
		<div style='text-align:center'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='forumheader3' style='text-align:center'>".EPL_ADLAN_2."<br /><br />
		<input class='button' type='submit' name='cancel' value='".EPL_CANCEL."' />
		<input class='button' type='submit' name='confirm' value='".EPL_ADLAN_1." {$plug['plugin_name']}' />
		<input type='hidden' name='id' value='{$id}' />
		</td>
		</tr>
		</table>
		</form>
		</div>
		";
	$ns->tablerender(EPL_ADLAN_3.": <b>".$plug['plugin_name']." v".$plug['plugin_version']."</b>", $text);
	require_once("footer.php");
	exit;
}

if (isset($_POST['cancel'])) {
	$ns->tablerender("", "<div style='text-align:center'>".EPL_ADLAN_4."</div>");
}

if (isset($_POST['confirm'])) {
	$id = intval($_POST['id']);
	$plug = $plugin->getinfo($id);
	//Uninstall Plugin
	if ($plug['plugin_installflag'] == TRUE ) {
		include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

		$func = $eplug_folder.'_uninstall';
		if (function_exists($func)) {
			$text .= call_user_func($func);
		}

		if (is_array($eplug_table_names)) {
			$result = $plugin->manage_tables('remove', $eplug_table_names);
			if ($result !== TRUE) {
				$text .= EPL_ADLAN_27.' <b>'.$mySQLprefix.$result.'</b> - '.EPL_ADLAN_30.'<br />';
			} else {
				$text .= EPL_ADLAN_28."<br />";
			}
		}

		if (is_array($eplug_prefs)) {
			$plugin->manage_prefs('remove', $eplug_prefs);
			$text .= EPL_ADLAN_29."<br />";
		}

		if ($eplug_module) {
			$plugin->manage_plugin_prefs('remove', 'modules', $eplug_folder);
		}

		if ($eplug_status) {
			$plugin->manage_plugin_prefs('remove', 'plug_status', $eplug_folder);
		}

		if ($eplug_latest) {
			$plugin->manage_plugin_prefs('remove', 'plug_latest', $eplug_folder);
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
			$sql = new db;
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

		if ($eplug_link) {
			$plugin->manage_link('remove', '', $eplug_link_name);
		}

		if ($eplug_userclass) {
			$plugin->manage_userclass('remove', $eplug_userclass);
		}

		$sql->db_Update('plugin', "plugin_installflag=0, plugin_version='{$eplug_version}' WHERE plugin_id='{$id}' ");
		$text .= '<br />'.EPL_ADLAN_31.' <b>'.e_PLUGIN.$eplug_folder.'</b> '.EPL_ADLAN_32;
		$ns->tablerender(EPL_ADLAN_1.' '.$eplug_name, $text);
	}
}

if ($action == 'install') {
	// install plugin ...
	$plug = $plugin->getinfo($id);

	if ($plug['plugin_installflag'] == FALSE) {
		include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

		$func = $eplug_folder.'_install';
		if (function_exists($func)) {
			$text .= call_user_func($func);
		}

		if (is_array($eplug_tables)) {
			$result = $plugin->manage_tables('add', $eplug_tables);
			if ($result === TRUE) {
				$text .= EPL_ADLAN_19.'<br />';
				//success
			} else {
				$text .= EPL_ADLAN_18.'<br />';
				//fail
			}
		}

		if (is_array($eplug_prefs)) {
			$plugin->manage_prefs('add', $eplug_prefs);
			$text .= EPL_ADLAN_20.'<br />';
		}

		if ($eplug_module === TRUE) {
			$plugin->manage_plugin_prefs('add', 'modules', $eplug_folder);
		}

		if ($eplug_status === TRUE) {
			$plugin->manage_plugin_prefs('add', 'plug_status', $eplug_folder);
		}

		if ($eplug_latest === TRUE) {
			$plugin->manage_plugin_prefs('add', 'plug_latest', $eplug_folder);
		}


		if (is_array($eplug_sc)) {
			$plugin->manage_plugin_prefs('add', 'plug_sc', $eplug_folder, $eplug_sc);
		}

		if (is_array($eplug_bb)) {
			$plugin->manage_plugin_prefs('add', 'plug_bb', $eplug_folder, $eplug_bb);
		}

		if (is_array($eplug_user_prefs)) {
			$sql = new db;
			$sql->db_Select("core", " e107_value", " e107_name='user_entended'");
			$row = $sql->db_Fetch();
			$user_entended = unserialize($row[0]);
			while (list($e_user_pref, $default_value) = each($eplug_user_prefs)) {
				$user_entended[] = $e_user_pref;
				$user_pref['$e_user_pref'] = $default_value;
			}
			save_prefs("user");
			$tmp = addslashes(serialize($user_entended));
			if ($sql->db_Select("core", " e107_value", " e107_name='user_entended'")) {
				$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
			} else {
				$sql->db_Insert("core", "'user_entended', '$tmp' ");
			}
			$text .= EPL_ADLAN_20."<br />";
		}

		if ($eplug_link === TRUE && $eplug_link_url != '' && $eplug_link_name != '') {
			$plugin->manage_link('add', $eplug_link_url, $eplug_link_name);
		}

		if ($eplug_userclass) {
			$plugin->manage_userclass('add', $eplug_userclass, $eplug_userclass_description);
		}

		$sql->db_Update('plugin', "plugin_installflag=1 WHERE plugin_id='$id' ");
		$text .= ($eplug_done ? "<br />".$eplug_done : "");
	} else {
		$text = EPL_ADLAN_21;
	}
	$ns->tablerender(EPL_ADLAN_33, $text);
}

if ($action == 'upgrade') {
	$plug = $plugin->getinfo($id);
	include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

	$func = $eplug_folder.'_upgrade';
	if (function_exists($func)) {
		$text .= call_user_func($func);
	}

	if (is_array($upgrade_alter_tables)) {
		$result = $plugin->manage_tables('add', $upgrade_alter_tables);
		if (!$result) {
			$text .= EPL_ADLAN_9.'<br />';
		} else {
			$text .= EPL_ADLAN_7."<br />";
		}
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

	if (is_array($upgrade_add_prefs)) {
		$plugin->manage_prefs('add', $upgrade_add_prefs);
		$text .= EPL_ADLAN_8.'<br />';
	}

	if (is_array($upgrade_remove_prefs)) {
		$plugin->manage_prefs('remove', $upgrade_add_prefs);
	}

	if (is_array($upgrade_add_user_prefs)) {
		$sql = new db;
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
		$sql = new db;
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

	$text .= '<br />'.$eplug_upgrade_done;
	$sql->db_Update('plugin', "plugin_version ='{$plug['eplug_version']}' WHERE plugin_id='$id' ");
	$ns->tablerender(EPL_ADLAN_34, $text);
}


// Check for new plugins, create entry in plugin table ...
$handle = opendir(e_PLUGIN);
while (false !== ($file = readdir($handle))) {
	if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)) {
		$plugin_handle = opendir(e_PLUGIN.$file."/");
		while (false !== ($file2 = readdir($plugin_handle))) {
			if ($file2 == "plugin.php") {
				include(e_PLUGIN.$file."/".$file2);
				if (!$sql->db_Select("plugin", "*", "plugin_name='$eplug_name'")) {
					if (!$eplug_prefs && !$eplug_table_names && !$eplug_user_prefs && !$eplug_sc && !$eplug_userclass && !$eplug_module && !$eplug_bb && !$eplug_latest && !$eplug_status) {
						// new plugin, assign entry in plugin table, install is not necessary so mark it as intalled
						$sql->db_Insert("plugin", "0, '$eplug_name', '$eplug_version', '$eplug_folder', 1");
					} else {
						// new plugin, assign entry in plugin table, install is necessary
						$sql->db_Insert("plugin", "0, '$eplug_name', '$eplug_version', '$eplug_folder', 0");
					}
				}
			}
		}
		closedir($plugin_handle);
	}
}
closedir($handle);

$sql->db_Select("plugin");
while ($row = $sql->db_fetch()) {
	if (!is_dir(e_PLUGIN.$row['plugin_path'])) {
		$sql->db_Delete('plugin', "plugin_path='{$row['plugin_path']}'");
	}
}

// ----------------------------------------------------------
//        render plugin information ...



/* plugin upload form */

if(!is_writable(e_PLUGIN)) {
	$ns->tablerender(EPL_ADLAN_40, EPL_ADLAN_44);
} else {
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

$text .= "<table style='".ADMIN_WIDTH."' class='fborder'>";


$pluginList = $plugin->getall();

foreach($pluginList as $plug) {
	//Unset any possible eplug_ variables set by last plugin.php
	$defined_vars = array_keys(get_defined_vars());
	foreach($defined_vars as $varname) {
		if (substr($varname, 0, 6) == 'eplug_' || substr($varname, 0, 8) == 'upgrade_') {
			unset($$varname);
		}
	}
	include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

	if ($eplug_conffile || is_array($eplug_table_names) || is_array($eplug_prefs) || is_array($eplug_user_prefs) || is_array($eplug_sc) || is_array($eplug_bb) || $eplug_module || $eplug_userclass || $eplug_status || $eplug_latest) {
		$img = (!$plug['plugin_installflag'] ? "<img src='".e_IMAGE."generic/uninstalled.png' alt='' />" : "<img src='".e_IMAGE."generic/installed.png' alt='' />");
	} else {
		$img = "<img src='".e_IMAGE."generic/noinstall.png' alt='' />";
	}

	if ($plug['plugin_version'] != $eplug_version && $plug['plugin_installflag']) {
		$img = "<img src='".e_IMAGE."generic/upgrade.png' alt='' />";
	}

	$plugin_icon = $eplug_icon ? "<img src='".e_PLUGIN.$eplug_icon."' alt='' style='border:0px;vertical-align: bottom; width: 32px; height: 32px' />" :
	 E_32_CAT_PLUG;
	if ($eplug_conffile && $plug['plugin_installflag'] == TRUE) {
		$conf_title = EPL_CONFIGURE.' '.$eplug_name;
		$plugin_icon = "<a title='{$conf_title}' href='".e_PLUGIN.$eplug_folder.'/'.$eplug_conffile."' >".$plugin_icon.'</a>';
	}

	$text .= "
		<tr>
		<td class='forumheader3' style='width:30%; text-align:center; vertical-align:top'>".$plugin_icon."
		<br />
		<br />
		$img <b>{$plug['plugin_name']}</b><br />".EPL_ADLAN_11." {$plug['plugin_version']}
		<br />
		</td>
		<td class='forumheader3' style='width:70%;vertical-align:top'>
		<table cellspacing='3' style='width:97%'>
		<tr><td style='vertical-align:top;width:24%'><b>".EPL_ADLAN_12."</b>:</td><td style='vertical-align:top'><a href='mailto:$eplug_email' title='$eplug_email'>$eplug_author</a>&nbsp;</td></tr>
		<tr><td style='vertical-align:top'><b>".EPL_WEBSITE."</b>:</td><td style='vertical-align:top'> $eplug_url&nbsp;</td></tr>
		<tr><td style='vertical-align:top'><b>".EPL_ADLAN_14."</b>:</td><td style='vertical-align:top'> $eplug_description&nbsp;</td></tr>
		<tr><td style='vertical-align:top'><b>".EPL_ADLAN_13."</b>:</td><td style='vertical-align:top'> $eplug_compatible&nbsp; </td></tr>\n";
	if ($eplug_readme) {
		$text .= "<tr><td><b>&nbsp;</b></td><td>[ <a href='".e_PLUGIN.$eplug_folder."/".$eplug_readme."'>".$eplug_readme."</a> ]</td></tr>";
	}

	if ($eplug_conffile || $eplug_module || is_array($eplug_table_names) || is_array($eplug_prefs) || is_array($eplug_user_prefs) || is_array($eplug_sc) || is_array($eplug_bb) || $eplug_status || $eplug_latest) {
		$text .= "<tr><td><b>".EPL_OPTIONS."</b>:</td><td> [ ".($plug['plugin_installflag'] ? "<a href='".e_SELF."?uninstall.{$plug['plugin_id']}' title='".EPL_ADLAN_1."'> ".EPL_ADLAN_1."</a>" : "<a href='".e_SELF."?install.{$plug['plugin_id']}' title='".EPL_ADLAN_0."'>".EPL_ADLAN_0."</a>")." ]";
	} else {
		if ($eplug_menu_name) {
			$text .= EPL_NOINSTALL.str_replace("..", "", e_PLUGIN.$plug['plugin_path'])."/ ".EPL_DIRECTORY;
		} else {
			$text .= EPL_NOINSTALL_1.str_replace("..", "", e_PLUGIN.$plug['plugin_path'])."/ ".EPL_DIRECTORY;
		}
	}

	if ($plug['plugin_version'] != $eplug_version && $plug['plugin_installflag']) {
		$text .= " [ <a href='".e_SELF."?upgrade.{$plug['plugin_id']}' title='".EPL_UPGRADE." to v".$eplug_version."'>".EPL_UPGRADE."</a> ]";
	}
	$text .= "</td></tr>";

	if ($eplug_compliant) {
		$text .= "<tr><td colspan='2' style='text-align:right'><img src='".e_IMAGE."generic/compliant.gif' alt='' /></td></tr>";
	}

	$text .= "</table></td></tr>";
}

$text .= "</table>
	<div><br />
	<img src='".e_IMAGE."generic/uninstalled.png' alt='' /> ".EPL_ADLAN_23."&nbsp;&nbsp;
	<img src='".e_IMAGE."generic/installed.png' alt='' /> ".EPL_ADLAN_22."&nbsp;&nbsp;
	<img src='".e_IMAGE."generic/upgrade.png' alt='' /> ".EPL_ADLAN_24."&nbsp;&nbsp;
	<img src='".e_IMAGE."generic/noinstall.png' alt='' /> ".EPL_ADLAN_25."</div></div>";

$ns->tablerender(EPL_ADLAN_16, $text);
// ----------------------------------------------------------

require_once("footer.php");
?>