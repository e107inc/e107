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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/errorhandler_class.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-27 19:52:27 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
function error_handler($type, $message, $file, $line, $context) {
	$debug_state = "ERROR";
	 
	switch($type) {
		case E_NOTICE:
		if (eregi("NOTICE", $debug_state)) {
			echo "<b>e107 Notice: </b>".$message.", Line <b>".$line."</b> of <b>".$file."</b><br />";
		}
		break;
		case E_WARNING:
		if (eregi("WARNING", $debug_state)) {
			echo "<b>e107 Warning: A non fatal error has occurred </b>".$message.", Line <b>".$line."</b> of <b>".$file."</b><br />";
		}
		break;
		case E_ERROR:
		if (eregi("ERROR", $debug_state)) {
			echo "<b>e107 ERROR: A fatal error has occurred </b>".$message.", Line <b>".$line."</b> of <b>".$file."</b><br />";
		}
		break;
	}
	if (eregi("ECHOSTATE", $debug_state)) {
		echo "<br />Variable state when error occurred: <br>";
		print_r($context);
	}
}
?>