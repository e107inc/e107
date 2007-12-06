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
|     $Source: /cvs_backup/e107_0.8/e107_admin/e107_update.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-12-06 21:38:20 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
$e_sub_cat = 'database';
require_once("auth.php");
require_once("update_routines.php");


// Carry out core updates
function run_updates($dbupdate) 
{
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


function show_updates($dbupdate) 
{
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
//	  echo "Core2 Check {$func}=>{$rmks}<br />";
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
	$ns->tablerender(LAN_UPDATE, $text);
	return $updates;				// Number of updates to do
}


if ($_POST) 
{
  $message = run_updates($dbupdate);
}


if ($_POST) 
{	// Do plugin updates
  $message = run_updates($dbupdatep);
}

$total_updates = 0;
if (isset($dbupdatep)) 
{	// Show plugin updates done
  $total_updates += show_updates($dbupdatep);
}
// Show core updates done
$total_updates += show_updates($dbupdate);

if ($total_updates == 0)
{  // No updates needed - clear the cache to be sure
  $e107cache->set_sys("nq_admin_updatecheck", time().', 1, '.$e107info['e107_version'], TRUE);
}


require_once("footer.php");

?>