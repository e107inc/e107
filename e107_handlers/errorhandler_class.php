<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/error_handler_class.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
function error_handler($type, $message, $file, $line, $context){
	$debug_state = "ERROR";

	switch($type){
		case E_NOTICE:
			if(eregi("NOTICE", $debug_state)){ echo "<b>e107 Notice: </b>".$message.", Line <b>".$line."</b> of <b>".$file."</b><br />"; }
		break;
		case E_WARNING:
			if(eregi("WARNING", $debug_state)){ echo "<b>e107 Warning: A non fatal error has occurred </b>".$message.", Line <b>".$line."</b> of <b>".$file."</b><br />"; }
		break;
		case E_ERROR:
			if(eregi("ERROR", $debug_state)){ echo "<b>e107 ERROR: A fatal error has occurred </b>".$message.", Line <b>".$line."</b> of <b>".$file."</b><br />"; }
		break;
	}
	if(eregi("ECHOSTATE", $debug_state)){
		echo "<br />Variable state when error occurred: <br>";
		print_r($context);
	}
}
?>