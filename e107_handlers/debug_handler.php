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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/debug_handler.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-01-29 18:31:22 $
|     $Author: mrpete $
+----------------------------------------------------------------------------+
*/  


class e107_debug {
	
	var $debug_level = 1;
	//
	// DEBUG SHORTCUTS
	//
	var $aDebugShortcuts = array(
	'all' 	=>   255,	// all basics
	'basic'	=>	  255,	// all basics
	'b'		=>	  255,	// all basics
	'showsql'=>     2,	// sql basics
	'counts' =>     4,	// traffic counters
	'detail' => 32767,	// all details
	'd' 		=> 32767,	// all details
	'time' 	=>   256,	// time details
	'sql' 	=>   512,	// sql details
	'warn'	=>     1,   // just warnings, parse errrors, etc
	'notice'	=> 32768,	// you REALLY don't want all this, do you?
	'everything' => 65535,
	);
	
	function e107_debug() {
		if (preg_match('/debug=(.*)/', e_MENU, $debug_param)) {
			$dVal = $debug_param[1];
			if (isset($this->aDebugShortcuts[$dVal])) {
				$this->debug_level = $this->aDebugShortcuts[$dVal];
			} else {
				$this->debug_level = $dVal;
			}
		}
	}
		
	function set_error_reporting() {
		if ($this->debug_level & 32768) {
			error_reporting(E_ALL);
		} else {
			error_reporting(E_ERROR | E_WARNING | E_PARSE);
		}
	}
}     

//
// Process 'command line' to determine debug level on this page
//

if (strstr(e_MENU, "debug")) {
	$e107_debug = new e107_debug;
	require_once(e_HANDLER.'db_debug_class.php');
	$db_debug = new e107_db_debug;
	$e107_debug->set_error_reporting();
	$e107_debug_level = $e107_debug->debug_level;
	define('E107_DEBUG_LEVEL',$e107_debug_level);
} else {
	define('E107_DEBUG_LEVEL',0);
}
 
//
// DEBUG LEVEL BREAKDOWN
// Some bits for different debug levels (all intended for ADMIN only)
// 8 bits for each set of details
// 1...128 = basics
// 256...32768 = gory details
// Thus:
// 255 = all basics
// 65535 = all basic and all gory details

// Basic levels
define('E107_DBG_BASIC',(E107_DEBUG_LEVEL & 1));         // basics: worst php errors, sql errors, etc
define('E107_DBG_SQLQUERIES',(E107_DEBUG_LEVEL & 2));    // display all sql queries
define('E107_DBG_TRAFFIC',(E107_DEBUG_LEVEL & 4));       // display traffic counters
define('E107_DBG_FILLIN8',(E107_DEBUG_LEVEL & 8));       // fill in what it is
define('E107_DBG_FILLIN16',(E107_DEBUG_LEVEL & 16));         // fill in what it is
define('E107_DBG_FILLIN32',(E107_DEBUG_LEVEL & 32));         // fill in what it is
define('E107_DBG_FILLIN64',(E107_DEBUG_LEVEL & 64));         // fill in what it is
define('E107_DBG_FILLIN128',(E107_DEBUG_LEVEL & 128));         // fill in what it is

// Gory detail levels
define('E107_DBG_TIMEDETAILS',(E107_DEBUG_LEVEL & 256)); // detailed time profile
define('E107_DBG_SQLDETAILS',(E107_DEBUG_LEVEL & 512));  // detailed sql analysis
define('E107_DBG_FILLIN1024',(E107_DEBUG_LEVEL & 1024));         // fill in what it is
define('E107_DBG_FILLIN2048',(E107_DEBUG_LEVEL & 2048));         // fill in what it is
//...
define('E107_DBG_ALLERRORS',(E107_DEBUG_LEVEL & 32768));     // show ALL errors//...


?>