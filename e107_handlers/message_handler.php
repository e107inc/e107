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

$emessage[1] = "<b>[1]: Unable to read core settings from database - Core settings exist but cannot be unserialized. Attempting to restore core backup ...</b>";
$emessage[2] = "<b>[2]: Unable to read core settings from database - non-existant core settings.</b>";
$emessage[3] = "<b>[3]: Core settings saved - backup made active.</b>";
$emessage[4] = "<b>[4]: No core backup found. Please run the <a href='".e_FILE."resetcore.php'>Reset_Core</a> utility to rebuild your core settings. <br />After rebuilding your core please save a backup from the admin/sql screen.</b>";
$emessage[5] = "[5]: Field(s) have been left blank. Please resubmit the form and fill in the required fields.";
$emessage[6] = "<b>[6]: Unable to form a valid connection to mySQL. Please check that your e107_config.php contains the correct information.</b>";
$emessage[7] = "<b>[7]: mySQL is running but database ($mySQLdefaultdb) couldn't be connected to.<br />Please check it exists and that your e107_config.php contains the correct information.</b>";


function message_handler($mode, $message, $line=0, $file=""){
	global $emessage;
	if(class_exists('e107table'))
	{
		$ns = new e107table;
	}
	switch($mode){
		case "CRITICAL_ERROR":
			$message = is_numeric($message) ? $emessage[$message] : $message;
			echo "<div style='text-align:center; font: 11px verdana, tahoma, arial, helvetica, sans-serif;'><b>CRITICAL_ERROR: </b><br />Line $line $file<br /><br />Error reported as: ".$message."</div>";
		break;
		case "MESSAGE":
			$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		break;
		case "ADMIN_MESSAGE":
			$ns -> tablerender("Admin Message", "<div style='text-align:center'><b>".$message."</b></div>");
		break;
		case "ALERT":
			@require_once(e_HANDLER."textparse/basic.php");
      $etp = new e107_basicparse;
      echo "<script type='text/javascript'>alert(\"".$etp->unentity($emessage[$message])."\"); window.history.go(-1); </script>\n";
		break;
		case "P_ALERT":
			echo "<script type='text/javascript'>alert(\"".$etp->unentity($message)."\"); </script>\n";
		break;
	}
}
?>