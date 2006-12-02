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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/debug_handler.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:33:43 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

//
// IMPORTANT Info for devs who want to add to and/or use debug definitions!
//
// MAKING NEW DEBUG DEFS
// The debug levels are Single Bit Binary Values. i.e, 1,2,4,8,16...
// In the table below, if you want to define a new value, pick one of
// the "FILLIN" items and give it the name and definition you need
//
// USING DEBUG DEFINITIONS
// Since these are Bit Values, **never** test using < or > comparisons. Always
// test using boolean operations, such as
//    if (E107_DBG_PATH)
//    if (E107_DBG_SQLQUERIES | E107_DBG_SQLDETAILS)
// Since constants are defined for all possible bits, you should never need to use a number value like
//    if (E107_DEBUG_LEVEL & 256)
// And there's never a reason to use
//    if (E107_DEBUG_LEVEL > 254)

if (!defined('e107_INIT')) { exit; }

//
// If debugging enabled, set it all up
// If no debugging, then E107_DEBUG_LEVEL will be zero
//
if (strstr(e_MENU, "debug") || isset($_COOKIE['e107_debug_level'])) {
	$e107_debug = new e107_debug;
	require_once(e_HANDLER.'db_debug_class.php');
	$db_debug = new e107_db_debug;
	$e107_debug->set_error_reporting();
	$e107_debug_level = $e107_debug->debug_level;
	define('E107_DEBUG_LEVEL', $e107_debug_level);
} else {
	define('E107_DEBUG_LEVEL', 0);
}

// 
// Define all debug constants -- each one will be zero or a value
// They all have different values and can be 'or'ed together
//

// Basic levels
define('E107_DBG_BASIC',		(E107_DEBUG_LEVEL & 1));       // basics: worst php errors, sql errors, etc
define('E107_DBG_SQLQUERIES',	(E107_DEBUG_LEVEL & 2));       // display all sql queries
define('E107_DBG_TRAFFIC',		(E107_DEBUG_LEVEL & 4));       // display traffic counters
define('E107_DBG_FILLIN8',		(E107_DEBUG_LEVEL & 8));       // fill in what it is
define('E107_DBG_FILLIN16',		(E107_DEBUG_LEVEL & 16));      // fill in what it is
define('E107_DBG_FILLIN32',		(E107_DEBUG_LEVEL & 32));      // fill in what it is
define('E107_DBG_FILLIN64',		(E107_DEBUG_LEVEL & 64));      // fill in what it is
define('E107_DBG_FILLIN128',	(E107_DEBUG_LEVEL & 128));     // fill in what it is

// Gory detail levels
define('E107_DBG_TIMEDETAILS',(E107_DEBUG_LEVEL &   256));    // detailed time profile
define('E107_DBG_SQLDETAILS',	(E107_DEBUG_LEVEL &   512));    // detailed sql analysis
define('E107_DBG_PATH',     	(E107_DEBUG_LEVEL &  1024));    // show e107 predefined paths
define('E107_DBG_BBSC',     	(E107_DEBUG_LEVEL &  2048));    // Show BBCode/ Shortcode usage in postings
define('E107_DBG_SC',       	(E107_DEBUG_LEVEL &  4096));    // Dump (inline) SC filenames as used
define('E107_DBG_FILLIN8192',	(E107_DEBUG_LEVEL &  8192));    // fill in what it is
define('E107_DBG_DEPRECATED', (E107_DEBUG_LEVEL & 16384));    // Show use of deprecated functions
define('E107_DBG_ALLERRORS',	(E107_DEBUG_LEVEL & 32768));   // show ALL errors//...

class e107_debug {

	var $debug_level = 1;
	//
	// DEBUG SHORTCUTS
	//
	var $aDebugShortcuts = array(
		'all'		 	  => 255,     // all basics
		'basic'			=> 255,     // all basics
		'b'				  => 255,     // all basics
		'warn'			=> 1,       // just warnings, parse errrors, etc
		'showsql'		=> 2,       // sql basics
		'counts'		=> 4,       // traffic counters

		'detail'		=> 32767,   // all details
		'd' 			  => 32767,   // all details
		'time' 			=> 256,     // time details
		'sql' 			=> 512,     // sql details
		'paths' 		=> 1024,		// dump path strings
		'bbsc' 			=> 2048,		// show bb and sc details
		'sc'			  => 4096,   		// Shortcode paths dumped inline
		'deprecated'	=> 16384,   // show if code is using deprecated functions
		'notice'		=> 32768,   // you REALLY don't want all this, do you?
		'everything'=> 65535,
	);

	function e107_debug() {
		if (preg_match('/debug(=?)(.*?),?(\+|stick|-|unstick|$)/', e_MENU, $debug_param) || isset($_COOKIE['e107_debug_level'])) {
			if (isset($_COOKIE['e107_debug_level'])) {
				$dVal = substr($_COOKIE['e107_debug_level'],6);
			}
			if (preg_match('/debug(=?)(.*?),?(\+|stick|-|unstick|$)/', e_MENU)) {
				$dVal = $debug_param[1] == '=' ? $debug_param[2] : 'everything';
			}
			if (isset($debug_param[3]))
			{
				if ($debug_param[3] == '+' || $debug_param[3] == 'stick')
				{
					cookie('e107_debug_level', 'level='.$dVal, time() + 86400);
				}
				if ($debug_param[3] == '-' || $debug_param[3] == 'unstick')
				{
					cookie('e107_debug_level', '', time() - 3600);
				}
			}

			if (isset($this->aDebugShortcuts[$dVal])) {
				$this->debug_level = $this->aDebugShortcuts[$dVal];
			} else {
				$this->debug_level = $dVal;
			}
		}
	}

	function set_error_reporting() {
	}
}
?>