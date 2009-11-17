<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_update_check.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-17 13:48:44 $
 * $Author: marj_nl_fr $
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
