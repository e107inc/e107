<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/footer.php,v $
|     $Revision: 1.14 $
|     $Date: 2009-09-13 10:29:56 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
$In_e107_Footer = TRUE;	// For registered shutdown function

global $error_handler, $db_time, $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $ADMIN_FOOTER, $e107, $pref;

//
// SHUTDOWN SEQUENCE
//
// The following items have been carefully designed so page processing will finish properly
// Please DO NOT re-order these items without asking first! You WILL break something ;)
// These letters match the USER footer (that's why there is B.1,B.2)
//
// A Ensure sql and traffic objects exist
// B.1 Clear cache (if over a week old)
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

/*
if(!is_object($sql)){
	// reinstigate db connection if another connection from third-party script closed it ...
	$sql = new db;
	$sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
}
*/

$sql = e107::getDb();
e107::getSingleton('e107_traffic')->Bump('Lost Traffic Counters');


if(varset($e107_popup)!=1){
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
	// B.2 Send footer template, stop timing, send simple page stats
	//
	//NEW - Iframe mod
	if (!defsettrue('e_IFRAME')) 
	{
		parse_admin($ADMIN_FOOTER);
	}

	$eTimingStop = microtime();
	global $eTimingStart;
	$clockTime = e107::getSingleton('e107_traffic')->TimeDelta( $eTimingStart, $eTimingStop );
	$dbPercent = 100.0 * $db_time / $clockTime;
	// Format for display or logging
	$rendertime = number_format($clockTime, 2);	// Clock time during page render
	$db_time    = number_format($db_time,2);		// Clock time in DB render
	$dbPercent  = number_format($dbPercent,0);	// DB as percent of clock
	$memuse     = $e107->get_memory_usage();		// Memory at end, in B/KB/MB/GB ;)
	$rinfo = '';

	if ( function_exists( 'getrusage' ) ) 
	{
		$ru = getrusage();
		$cpuUTime = $ru['ru_utime.tv_sec'] + ($ru['ru_utime.tv_usec'] * 1e-6);
		$cpuSTime = $ru['ru_stime.tv_sec'] + ($ru['ru_stime.tv_usec'] * 1e-6);
		$cpuUStart = $eTimingStartCPU['ru_utime.tv_sec'] + ($eTimingStartCPU['ru_utime.tv_usec'] * 1e-6);
		$cpuSStart = $eTimingStartCPU['ru_stime.tv_sec'] + ($eTimingStartCPU['ru_stime.tv_usec'] * 1e-6);
		$cpuStart = $cpuUStart + $cpuSStart;
		$cpuTot  = $cpuUTime + $cpuSTime;
		$cpuTime = $cpuTot - $cpuStart;
		$cpuPct = 100.0 * $cpuTime / $rendertime; /* CPU load during known clock time */
		// Format for display or logging (Uncomment as needed for logging)
		//$cpuUTime = number_format($cpuUTime, 3);		// User cpu
		//$cpuSTime = number_format($cpuSTime, 3);		// System cpu
		//$cpuTot = number_format($cpuTot, 3);				// Total (User+System)
		$cpuStart = number_format($cpuStart, 3);		// Startup time (i.e. CPU used before class2.php)
		$cpuTime = number_format($cpuTime, 3);			// CPU while we were measuring the clock (cpuTot-cpuStart)
		$cpuPct = number_format($cpuPct,0);	// CPU Load (CPU/Clock)
	}
	//
	// Here's a good place to log CPU usage in case you want graphs and/or your host cares about that
	// e.g. (on a typical vhosted linux host)
	// 
	//	$logname = "/home/mysite/public_html/queryspeed.log";
	//	$logfp = fopen($logname, 'a+'); fwrite($logfp, "$cpuTot,$cpuPct,$cpuStart,$rendertime,$db_time\n"); fclose($logfp);

	if($pref['displayrendertime'])
	{ 
		$rinfo .= CORE_LAN11;
		if (isset($cpuTime)) 
		{
//			$rinfo .= "{$cpuTime} cpu sec ({$cpuPct}% load, {$cpuStart} startup). Clock: ";
			$rinfo .= sprintf(CORE_LAN14,$cpuTime,$cpuPct,$cpuStart);
		}
		$rinfo .= $rendertime.CORE_LAN12.$dbPercent.CORE_LAN13; 
	}
	if($pref['displaysql']){ $rinfo .= CORE_LAN15.$sql -> db_QueryCount().". "; }
	if(isset($pref['display_memory_usage']) && $pref['display_memory_usage']){ $rinfo .= CORE_LAN16.$memuse; }
	if(isset($pref['displaycacheinfo']) && $pref['displaycacheinfo']){ $rinfo .= $cachestring."."; }

	if (function_exists('theme_renderinfo'))
	{
		theme_renderinfo($rinfo);
	} else {
		echo ($rinfo ? "\n<div style='text-align:center' class='e107_foot smalltext'>{$rinfo}</div>\n" : "");
	}

} // End of regular-page footer (the above NOT done for popups)


//
// C Dump all debug and traffic information
//
	if ((ADMIN || $pref['developer']) && E107_DEBUG_LEVEL) {
		global $db_debug;
		echo "\n<!-- DEBUG -->\n<div class='e107_debug dbg_info'>";
		$db_debug->Show_All();
		echo "</div>\n";
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
		echo "<div class='e107_debug qry_notice'><table class='fborder' style='width: 100%;'>
		<tr>
		<td class='fcaption' style='width: 5%;'>ID</td><td class='fcaption' style='width: 95%;'>SQL Queries</td>\n</tr>\n";
		foreach ($queryinfo as $infovalue)
		{
			echo "<tr>\n<td class='forumheader3' style='width: 5%;'>{$c}</td><td class='forumheader3' style='width: 95%;'>{$infovalue}</td>\n</tr>\n";
			$c++;
		}
		echo "</table></div>";
	}

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
		$obdbg = "<div class='e107_debug ob_err' style='text-align:center' class='e107_foot smalltext'>Software defect detected; ob_*() level {$oblev} at end instead of ($oblev_at_start). POPPING EXTRA BUFFERS!</div>";
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
		$obdbg = "<div class='e107_debug ob_err' style='text-align:center' class='e107_foot smalltext'>Software warning; ob_*() level {$oblev_before_start} at start; this page not properly integrated into its wrapper.</div>";
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