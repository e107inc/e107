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
|     $Source: /cvs_backup/e107_0.7/e107_admin/e107_update.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
$e_sub_cat = 'database';
require_once("auth.php");
require_once("update_routines.php");

function run_updates($dbupdate) {
	global $ns;
	foreach($dbupdate as $func => $rmks) {
		$installed = call_user_func("update_".$func);
		if ((LAN_UPDATE == $_POST[$func]) && !$installed) {
			if (function_exists("update_".$func)) {
				$message .= LAN_UPDATE_7." {$rmks}<br />";
				$error=call_user_func("update_".$func, "do");
				if ($error!='') {
					$message = $error;
				}
			}
		}
	}
	if ($message) {
		$ns->tablerender(LAN_UPDATE, $message);
	}
}

function show_updates($dbupdate, $additional = false) {
	global $ns;
	$text = "<form method='POST' action='".e_SELF."'>
	<div style='width:100%'>
	<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
	<td class='fcaption'>".LAN_UPDATE."</td>
	<td class='fcaption'>".LAN_UPDATE_2."</td>
	</tr>
	";

	$updates = 0;

	foreach($dbupdate as $func => $rmks) {
		if (function_exists("update_".$func)) {
			$text .= "<tr><td class='forumheader3' style='width: 60%'>{$rmks}</td>";
			if (call_user_func("update_".$func)) {
				$text .= "<td class='forumheader3' style='text-align:center; width: 40%'>".LAN_UPDATE_3."</td>";
			} else {
				$updates++;
				$text .= "<td class='forumheader3' style='text-align:center; width: 40%'><input class='button' type='submit' name='{$func}' value='".LAN_UPDATE."' /></td>";
			}
			$text .= "</tr>";
		}
	}

	$text .= "</table></div></form>";
	$ns->tablerender(($additional ? (defined("LAN_UPDATE_11") ? LAN_UPDATE_11 : '.617 to .7 Update Continued') : LAN_UPDATE_10), $text);
}

if ($_POST) {
	$message = run_updates($dbupdate);
}

if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'forum' AND plugin_installflag='1' ")) {
	if(file_exists(e_PLUGIN.'forum/forum_update_check.php'))
	{
		include_once(e_PLUGIN.'forum/forum_update_check.php');
	}
}
if ($sql -> db_Query("SHOW COLUMNS FROM ".MPREFIX."stat_info") && $sql -> db_Select("plugin", "*", "plugin_path = 'log' AND plugin_installflag='1'")) {
	if(file_exists(e_PLUGIN.'log/log_update_check.php'))
	{
		include_once(e_PLUGIN.'log/log_update_check.php');
	}
}

if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'content' AND plugin_installflag='1' "))
{
	if(file_exists(e_PLUGIN.'content/content_update_check.php'))
	{
		include_once(e_PLUGIN.'content/content_update_check.php');
	}
}

if ($sql->db_Select("plugin", "plugin_version", "plugin_path = 'pm' AND plugin_installflag='1' "))
{
	if(file_exists(e_PLUGIN.'pm/pm_update_check.php'))
	{
		include_once(e_PLUGIN.'pm/pm_update_check.php');
	}
}

if ($_POST) {
	$message = run_updates($dbupdatep);
}

if (isset($dbupdatep)) {
	show_updates($dbupdatep, true);
}
show_updates($dbupdate);

require_once("footer.php");

?>