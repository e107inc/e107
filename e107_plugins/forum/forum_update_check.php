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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_update_check.php,v $
|     $Revision: 1.3 $
|     $Date: 2008-12-18 22:03:45 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$dbupdatep['forum_08'] =  LAN_UPDATE_8.' 0.7.x forums '.LAN_UPDATE_9.' 0.8 forums';
//	print_a($pref);
function update_forum_08($type)
{
	global $sql, $mySQLdefaultdb, $pref;
	if($type == 'do')
	{
		if(!isset($_POST['updateall']))
		{
			include_once(e_PLUGIN.'forum/forum_update.php');
			return;
		}
	}
	return !version_compare($pref['plug_installed']['forum'], '3.0', '<');
}
?>
