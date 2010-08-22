<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$dbupdatep['log_07'] =  LAN_UPDATE_8." .617 statistics ".LAN_UPDATE_9." .7 statistics";
function update_log_07($type) {
	global $sql, $mySQLdefaultdb;
	if ($type == 'do') {
			include_once(e_PLUGIN.'log/log_update.php');
	} else {
		if ($sql -> db_Query("SHOW COLUMNS FROM ".MPREFIX."stat_info") && $sql -> db_Select("plugin", "*", "plugin_path = 'log' AND plugin_installflag='1'")) {
			 if ($sql -> db_Count('stat_info','(*)',"WHERE info_type='99'") < 7) {
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