<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/classes/upload_class.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

$emessage[1] = "<b>[1]: Unable to read core settings from database - Core settings exist but cannot be unserialized. Please run the <a href='".e_BASE."files/resetcore.php'>resetcore</a> utility to rebuild your core settings.</b>";
$emessage[2] = "<b>[2]: Unable to read core settings from database - non-existant core settings.</b>";



function message_handler($mode, $message, $line, $file){
	global $emessage;
	$ns = new table;
	switch($mode){
		case "CRITICAL_ERROR":
			echo "<div style='text-align:center; font: 11px verdana, tahoma, arial, helvetica, sans-serif;'><b>CRITICAL_ERROR: </b><br />Line $line $file<br /><br />Error reported as: ".$emessage[$message]."</div>";
		break;
		case "MESSAGE":
			$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		break;
		case "ADMIN_MESSAGE":
			$ns -> tablerender("Admin Message", "<div style='text-align:center'><b>".$message."</b></div>");
		break;
	}
}
?>