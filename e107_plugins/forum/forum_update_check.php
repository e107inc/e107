<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $URL$
 * $Id$
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
	$vc = !version_compare($pref['plug_installed']['forum'], '2.0', '<');
	return $vc;
}
?>
