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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm_menu/pm_menu.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-27 19:53:13 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
if ((USER && check_class($pref['pm_userclass'])) || ADMINPERMS == "0") {
	require_once(e_PLUGIN."pm_menu/pm_inc.php");
	$caption = ($pref['pm_title'] == "PMLAN_PM") ? PMLAN_PM :
	 $pref['pm_title'];
	$ns->tablerender($caption, pm_show_stats(1), 'pm_menu');
}
?>