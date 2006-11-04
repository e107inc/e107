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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm/pm_update_check.php,v $
|     $Revision: 1.2 $
|     $Date: 2006-11-04 18:33:58 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$dbupdatep['pm_07'] =  LAN_UPDATE_8." .617 ".ADLAN_PM_58." ".LAN_UPDATE_9." .7 ".ADLAN_PM_58;
function update_pm_07($type) {
	global $sql, $mySQLdefaultdb;
	if ($type == 'do') {
			include_once(e_PLUGIN.'pm/pm_update.php');
	} else {
		if ($sql -> db_Select("plugin", "*", "plugin_path = 'pm_menu' AND plugin_installflag='1'")) {
			 if ($sql -> db_Count('pm_messages', '(*)')) {
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}
}

?>		
