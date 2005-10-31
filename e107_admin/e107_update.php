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
|     $Revision: 1.10 $
|     $Date: 2005-10-31 20:44:02 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
$e_sub_cat = 'database';
require_once("auth.php");
require_once("update_routines.php");

if ($_POST) {
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
}

if ($message) {
	$ns->tablerender("&nbsp;", $message);
}

$text = "
	<form method='POST' action='".e_SELF."'>
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
		$text .= "<tr><td class='forumheader3'>{$rmks}</td>";
		if (call_user_func("update_".$func)) {
			$text .= "<td class='forumheader3' style='text-align:center'>".LAN_UPDATE_3."</td>";
		} else {
			$updates++;
			$text .= "<td class='forumheader3' style='text-align:center'><input class='button' type='submit' name='{$func}' value='".LAN_UPDATE."' /></td>";
		}
		$text .= "</tr>";
	}
}

$text .= "</table></div></form>";
$ns->tablerender(LAN_UPDATE_10, $text);
require_once("footer.php");
?>
