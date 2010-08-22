<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
$In_e107_Footer = TRUE;	// For registered shutdown function

global $eTraffic, $error_handler, $db_time, $sql, $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $ADMIN_FOOTER, $e107, $pref;

//
// SHUTDOWN SEQUENCE
//
// The following items have been carefully designed so page processing will finish properly
// Please DO NOT re-order these items without asking first! You WILL break something ;)
// These letters match the USER footer (that's why there is B.1,B.2)
//
// A Ensure sql and traffic objects exist
// B.1 Clear cache if over a week old
// B.2 Send the footer templated data
// C Dump any/all traffic and debug information
// [end of regular-page-only items]
// D Close the database connection
// E Themed footer code
// F Configured footer scripts
// G Browser-Server time sync script (must be the last one generated/sent)
// H Final HTML (/body, /html)
// I collect and send buffered page, along with needed headers
//

//
// A Ensure sql and traffic objects exist
//

if(!is_object($sql)){
	// reinstigate db connection if another connection from third-party script closed it ...
	$sql = new db;
	$sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
}
if (!is_object($eTraffic)) {
	$eTraffic = new e107_traffic;
	$eTraffic->Bump('Lost Traffic Counters');
}
//
// B.1 Clear cache if over a week old
//
if (ADMIN == TRUE) {
	if ($pref['cachestatus']) {
		if (!$sql->db_Select('generic', '*', "gen_type='empty_cache'"))
		{
			$sql->db_Insert('generic', "0,'empty_cache','".time()."','0','','0',''");
		} else {
			$row = $sql->db_Fetch();
			if (($row['gen_datestamp']+604800) < time()) // If cache not cleared in last 7 days, clear it.
			{
				require_once(e_HANDLER."cache_handler.php");
				$ec = new ecache;
				$ec->clear();
				$sql->db_Update('generic', "gen_datestamp='".time()."' WHERE gen_type='empty_cache'");
			}
		}
	}
}


//
// B.2 Send footer template
//
if(varset($e107_popup)!=1){

if (strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE) {
	parse_admin($ADMIN_FOOTER);
}


	//
	// C Dump all debug and traffic information
	//
	$eTimingStop = microtime();
	global $eTimingStart;
	$rendertime = number_format($eTraffic->TimeDelta( $eTimingStart, $eTimingStop ), 4);
	$db_time    = number_format($db_time,4);
	$rinfo = '';

	if($pref['displayrendertime']){ $rinfo .= CORE_LAN11.$rendertime.CORE_LAN12.$db_time.CORE_LAN13; }
	if($pref['displaysql']){ $rinfo .= CORE_LAN15.$sql -> db_QueryCount().". "; }
	if(isset($pref['display_memory_usage']) && $pref['display_memory_usage']){ $rinfo .= CORE_LAN16.$e107->get_memory_usage(); }
	if(isset($pref['displaycacheinfo']) && $pref['displaycacheinfo']){ $rinfo .= $cachestring."."; }
	echo ($rinfo ? "\n<div style='text-align:center' class='smalltext'>{$rinfo}</div>\n" : "");


	if ((ADMIN || $pref['developer']) && E107_DEBUG_LEVEL) {
		global $db_debug;
		echo "\n<!-- DEBUG -->\n";
		$db_debug->Show_All();
	}

	/*
	changes by jalist 24/01/2005:
	show sql queries
	usage: add ?showsql to query string, must be admin
	*/

	if(ADMIN && isset($queryinfo) && is_array($queryinfo))
	{
		$c=1;
		$mySQLInfo = $sql->mySQLinfo;
		echo "<table class='fborder' style='width: 100%;'>
		<tr>
		<td class='fcaption' style='width: 5%;'>ID</td><td class='fcaption' style='width: 95%;'>SQL Queries</td>\n</tr>\n";
		foreach ($queryinfo as $infovalue)
		{
			echo "<tr>\n<td class='forumheader3' style='width: 5%;'>{$c}</td><td class='forumheader3' style='width: 95%;'>{$infovalue}</td>\n</tr>\n";
			$c++;
		}
		echo "</table>";
	}

} // End of regular-page footer (the above NOT done for popups)

//
// D Close DB connection. We're done talking to underlying MySQL
//
	$sql -> db_Close();  // Only one is needed; the db is only connected once even with several $sql objects

	//
	// Just before we quit: dump quick timer if there is any
	// Works any time we get this far. Not calibrated, but it is quick and simple to use.
	// To use: eQTimeOn(); eQTimeOff();
	//
	$tmp = eQTimeElapsed();
	if (strlen($tmp)) {
		global $ns;
		$ns->tablerender('Quick Admin Timer',"Results: {$tmp} microseconds");
	}

if ($pref['developer']) {
	global $oblev_at_start,$oblev_before_start;
	if (ob_get_level() != $oblev_at_start) {
		$oblev = ob_get_level();
		$obdbg = "<div style='text-align:center' class='smalltext'>Software defect detected; ob_*() level {$oblev} at end instead of ($oblev_at_start). POPPING EXTRA BUFFERS!</div>";
		while (ob_get_level() > $oblev_at_start) {
			ob_end_flush();
		}
		echo $obdbg;
	}
	// 061109 PHP 5 has a bug such that the starting level might be zero or one.
	// Until they work that out, we'll disable this message.
	// Devs can re-enable for testing as needed.
	//
	if (0 && $oblev_before_start != 0) {
		$obdbg = "<div style='text-align:center' class='smalltext'>Software warning; ob_*() level {$oblev_before_start} at start; this page not properly integrated into its wrapper.</div>";
		echo $obdbg;
	}
}

if((ADMIN == true || $pref['developer']) && count($error_handler->errors) && $error_handler->debug == true) 
{
	echo "
	<div class='e107_debug php_err block-text'>
		<h3>PHP Errors:</h3><br />
		".$error_handler->return_errors()."
	</div>
	";
}

//
// E Last themed footer code, usually JS
//
if (function_exists('theme_foot'))
{
	echo theme_foot();
}

//
// F any included JS footer scripts
//
global $footer_js;
if(isset($footer_js) && is_array($footer_js))
{
	$footer_js = array_unique($footer_js);
	foreach($footer_js as $fname)
	{
		echo "<script type='text/javascript' src='{$fname}'></script>\n";
		$js_included[] = $fname;
	}
}

//
// G final JS script keeps user and server time in sync.
//   It must be the last thing created before sending the page to the user.
//
// see e107.js and class2.php
// This must be done as late as possible in page processing.
$_serverTime=time();
$lastSet = isset($_COOKIE['e107_tdSetTime']) ? $_COOKIE['e107_tdSetTime'] : 0;
if (abs($_serverTime - $lastSet) > 120) {
	/* update time delay every couple of minutes.
	* Benefit: account for user time corrections and changes in internet delays
	* Drawback: each update may cause all server times to display a bit different
	*/
	echo "<script type='text/javascript'>\n";
	echo "SyncWithServerTime('{$_serverTime}');
       </script>\n";
}

//
// H Final HTML
//
echo "</body></html>";

//
// I Send the buffered page data, along with appropriate headers
//
$page = ob_get_clean();

$etag = md5($page);
header("Cache-Control: must-revalidate");
header("ETag: {$etag}");

$pref['compression_level'] = 6;
if(strstr(varset($_SERVER["HTTP_ACCEPT_ENCODING"],""), "gzip")) {
	$browser_support = true;
}
if(ini_get("zlib.output_compression") == false && function_exists("gzencode")) {
	$server_support = true;
}
if(varset($pref['compress_output'],false) && $server_support == true && $browser_support == true) {
	$level = intval($pref['compression_level']);
	$page = gzencode($page, $level);
	header("Content-Encoding: gzip", true);
	header("Content-Length: ".strlen($page), true);
	echo $page;
} else {
	header("Content-Length: ".strlen($page), true);
	echo $page;
}

unset($In_e107_Footer);
$e107_Clean_Exit=TRUE;	// For registered shutdown function -- let it know all is well!
?>