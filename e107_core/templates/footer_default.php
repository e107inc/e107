<?php 
/*
* e107 website system
*
* Copyright 2008-2012 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Default footer for user pages
*
* $URL$
* $Id$
*
*/
if (!defined('e107_INIT'))
{
	exit;
}
$In_e107_Footer = TRUE; // For registered shutdown function

$magicSC = e107::getRender()->getMagicShortcodes(); // support for {---TITLE---} etc.

global $error_handler,$db_time,$FOOTER;



// System browser CACHE control - defaults to no cache; override in e107_config or on the fly
// This is temporary solution, we'll implement more flexible way for cache control override
// per page, more investigation needed about cache related headers, browser quirks etc
// Auto-detect from session (registered per page, per user session)
if(!defined('e_NOCACHE'))
{
	define('e_NOCACHE', !e107::canCache());
}

//
// SHUTDOWN SEQUENCE
//
// The following items have been carefully designed so page processing will finish properly
// Please DO NOT re-order these items without asking first! You WILL break something ;)
// These letters match the ADMIN footer (that's why there is B.1,B.2)
//
// A Ensure sql and traffic objects exist
// [Next few ONLY if a regular page; not done for popups]
// B.1 Clear cache (if admin page, not here)
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
$e107 = e107::getInstance();
$sql = e107::getDb();
$pref = e107::getPref();
$tp = e107::getParser();

if (varset($e107_popup) != 1)
{
	//
	// B.1 Clear cache (admin-only)
	//
	
	//
	// B.2 Send footer template, stop timing, send simple page stats
	//
	if(!deftrue('e_IFRAME'))
    {

        $psc = array(
         '</body>'          => '',
         '{THEME}'          => THEME_ABS,
         '{---MODAL---}'    => $LAYOUT['_modal_'],
         '{---HEADER---}'   => $tp->parseTemplate('{HEADER}',true),
         '{---FOOTER---}'   => $tp->parseTemplate('{FOOTER}',true)
        );

	   parseheader($FOOTER, $psc);
    }
    
	$eTimingStop = microtime();
	global $eTimingStart;
	$clockTime = e107::getSingleton('e107_traffic')->TimeDelta($eTimingStart, $eTimingStop);
	$dbPercent = 100.0 * $db_time / $clockTime;
	// Format for display or logging
	$rendertime = number_format($clockTime, 2); // Clock time during page render
	$db_time = number_format($db_time, 2); // Clock time in DB render
	$dbPercent = number_format($dbPercent, 0); // DB as percent of clock
	$memuse = eHelper::getMemoryUsage(); // Memory at end, in B/KB/MB/GB ;)
	$queryCount = $sql->db_QueryCount();
	$rinfo = '';
	$logLine = '';
	if ($pref['log_page_accesses'])
	{ // Collect the first batch of data to log
		$logLine .= "'".($now = time())."','".gmstrftime('%y-%m-%d %H:%M:%S', $now)."','".e107::getIPHandler()->getIP(FALSE)."','".e_PAGE.'?'.e_QUERY."','".$rendertime."','".$db_time."','".$queryCount."','".$memuse."','".$_SERVER['HTTP_USER_AGENT']."','{$_SERVER["REQUEST_METHOD"]}'";
	}
	
	if (function_exists('getrusage') && !empty($eTimingStartCPU))
	{
		$ru = getrusage();
		$cpuUTime = $ru['ru_utime.tv_sec'] + ($ru['ru_utime.tv_usec'] * 1e-6);
		$cpuSTime = $ru['ru_stime.tv_sec'] + ($ru['ru_stime.tv_usec'] * 1e-6);
		$cpuUStart = $eTimingStartCPU['ru_utime.tv_sec'] + ($eTimingStartCPU['ru_utime.tv_usec'] * 1e-6);
		$cpuSStart = $eTimingStartCPU['ru_stime.tv_sec'] + ($eTimingStartCPU['ru_stime.tv_usec'] * 1e-6);
		$cpuStart = $cpuUStart + $cpuSStart;
		$cpuTot = $cpuUTime + $cpuSTime;
		$cpuTime = $cpuTot - $cpuStart;
		$cpuPct = 100.0 * $cpuTime / $rendertime; /* CPU load during known clock time */
		// Format for display or logging (Uncomment as needed for logging)
		//$cpuUTime = number_format($cpuUTime, 3);		// User cpu
		//$cpuSTime = number_format($cpuSTime, 3);		// System cpu
		//$cpuTot = number_format($cpuTot, 3);				// Total (User+System)
		$cpuStart = number_format($cpuStart, 3); // Startup time (i.e. CPU used before class2.php)
		$cpuTime = number_format($cpuTime, 3); // CPU while we were measuring the clock (cpuTot-cpuStart)
		$cpuPct = number_format($cpuPct, 0); // CPU Load (CPU/Clock)
	}
	//
	// Here's a good place to log CPU usage in case you want graphs and/or your host cares about that
	// e.g. (on a typical vhosted linux host)
	//
	//	$logname = "/home/mysite/public_html/queryspeed.log";
	//	$logfp = fopen($logname, 'a+'); fwrite($logfp, "$cpuTot,$cpuPct,$cpuStart,$rendertime,$db_time\n"); fclose($logfp);
	
	if ($pref['displayrendertime'])
	{
		$rinfo .= CORE_LAN11;
		if (isset($cpuTime))
		{
			//			$rinfo .= "{$cpuTime} cpu sec ({$cpuPct}% load, {$cpuStart} startup). Clock: ";
			$rinfo .= sprintf(CORE_LAN14, $cpuTime, $cpuPct, $cpuStart);
		}
		$rinfo .= $rendertime.CORE_LAN12.$dbPercent.CORE_LAN13.'&nbsp;';
	}
	if ($pref['displaysql'])
	{
		$rinfo .= CORE_LAN15.$queryCount.". ";
	}
	if (isset($pref['display_memory_usage']) && $pref['display_memory_usage'])
	{
		$rinfo .= CORE_LAN16.$memuse;
	}
/*	if (isset($pref['displaycacheinfo']) && $pref['displaycacheinfo'])
	{
		$rinfo .= $cachestring.".";
	}
	*/
	if ($pref['log_page_accesses'])
	{
		// Need to log the page info to a text file as CSV data

		$logname = e_LOG."logd_".date("Y-m-d", time()).".csv";
		$logHeader = "Unix time,Date/Time,IP,URL,RenderTime,DbTime,Qrys,Memory-Usage,User-Agent,Request-Method";
			
		$logfp = fopen($logname, 'a+');
		
		if(filesize($logname) == 0 || !is_file($logname))
		{
			fwrite($logfp, $logHeader."\n");	
		}	
		
		fwrite($logfp, $logLine."\n");
		fclose($logfp);
	}
	
	if (function_exists('theme_renderinfo')) 
	{
		theme_renderinfo($rinfo);
	}
	else
	{
		echo($rinfo ? "\n<div class='e-footer-info muted smalltext hidden-print'><small>{$rinfo}</small></div>\n" : "");
	}
	
} // End of regular-page footer (the above NOT done for popups)

//
// C Dump all debug and traffic information
//
if ((ADMIN || $pref['developer']) && E107_DEBUG_LEVEL)
{
	$tmp = array();
	foreach($magicSC as $k=>$v)
	{
		$k = str_replace(array('{','}'),'',$k);
		$tmp[$k] = $v;
	}
	e107::getDebug()->log("<b>Magic Shortcodes</b><small> Replace [  ] with {  }</small><br />".print_a($tmp,true));
	echo "\n<!-- DEBUG -->\n<div class='e-debug debug-info'>";
	e107::getDebug()->Show_All();
	echo "</div>\n";
}

/*
 changes by jalist 24/01/2005:
 show sql queries
 usage: add ?showsql to query string, must be admin
 */

if (ADMIN && isset($queryinfo) && is_array($queryinfo))
{
	$c = 1;
	$mySQLInfo = $sql->mySQLinfo;
	echo "<div class='e-debug query-notice'><table class='fborder table table-striped table-bordered' style='width: 100%;'>
		<tr>
		<th class='fcaption' style='width: 5%;'>ID</th><th class='fcaption' style='width: 95%;'>SQL Queries</th>\n</tr>\n";
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
$sql->db_Close(); // Only one is needed; the db is only connected once even with several $sql objects

//
// Just before we quit: dump quick timer if there is any
// Works any time we get this far. Not calibrated, but it is quick and simple to use.
// To use: eQTimeOn(); eQTimeOff();
//
$tmp = eQTimeElapsed();
if (strlen($tmp))
{
	global $ns;
	$ns->tablerender('Quick Admin Timer', "Results: {$tmp} microseconds");
}

if ($pref['developer'])
{
	global $oblev_at_start,$oblev_before_start;
	if (ob_get_level() != $oblev_at_start)
	{
		$oblev = ob_get_level();
		$obdbg = "<div class='e-debug ob-error'>Software defect detected; ob_*() level {$oblev} at end instead of ($oblev_at_start). POPPING EXTRA BUFFERS!</div>";
		while (ob_get_level() > $oblev_at_start)
		{
			ob_end_flush();
		}
		echo $obdbg;
	}
	// 061109 PHP 5 has a bug such that the starting level might be zero or one.
	// Until they work that out, we'll disable this message.
	// Devs can re-enable for testing as needed.
	//
	if (0 && $oblev_before_start != 0)
	{
		$obdbg = "<div class='e-debug ob-error'>Software warning; ob_*() level {$oblev_before_start} at start; this page not properly integrated into its wrapper.</div>";
		echo $obdbg;
	}
}

if ((ADMIN == true || $pref['developer']) && count($error_handler->errors) && $error_handler->debug == true)
{
	$tmp = $error_handler->return_errors();
	if($tmp)
	{
		echo "
		<div class='e-debug php-errors block-text'>
			<h3>PHP Errors:</h3><br />
			".$tmp."
		</div>
		";
	}
	unset($tmp);
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
// DEPRECATED - use  e107::getJs()->footerFile('{e_PLUGIN}myplug/js/my.js', $zone = 2)
//
global $footer_js;
if (isset($footer_js) && is_array($footer_js))
{
	$footer_js = array_unique($footer_js);
	foreach ($footer_js as $fname)
	{
		echo "<script type='text/javascript' src='{$fname}'></script>\n";
		$js_included[] = $fname;
	}
}

// Load e_footer.php files.
if (!empty($pref['e_footer_list']) && is_array($pref['e_footer_list']))
{
	//ob_start(); // sometimes raw HTML needs to be added at the bottom of every page. eg. <noscript> etc. so allow 'echo' in e_footer files. (but not e_header)

	foreach($pref['e_footer_list'] as $val)
	{		
		$fname = e_PLUGIN.$val."/e_footer.php"; // Do not place inside a function - BC $pref required. . 
		
		if(is_readable($fname))
		{
			$ret = (deftrue('e_DEBUG') || isset($_E107['debug'])) ? include_once($fname) : @include_once($fname);

		}	
	}

//	$e_footer_ouput = ob_get_contents(); // Don't use.
//	ob_end_clean();
	unset($ret);
}

// Load Footer CSS
//
if(deftrue('e_DEVELOPER'))
{
	echo "\n\n<!-- ======= [JSManager] FOOTER: Remaining CSS ======= -->";
}
$CSSORDER = defined('CSSORDER') && deftrue('CSSORDER') ? explode(",",CSSORDER) : array('library','other','core','plugin','theme');  // INLINE CSS in Body not supported by HTML5. .

foreach($CSSORDER as $val)
{
	$cssId = $val."_css";
	e107::getJs()->renderJs($cssId, false, 'css', false);
}

unset($CSSORDER);

e107::getJs()->renderCached('css');

if(deftrue('e_DEVELOPER'))
{
	echo "\n\n<!-- ======= [JSManager] FOOTER: Remaining JS ======= -->";
}
// [JSManager] Load JS Footer Includes by priority
e107::getJs()->renderJs('footer', true);

e107::getJs()->renderCached('js');

// All JavaScript settings are placed in the footer of the page with the library weight so that inline scripts appear
// afterwards.
e107::getJs()->renderJs('settings');

// [JSManager] Load JS Footer inline code by priority
e107::getJs()->renderJs('footer_inline', true);

//
// G final JS script keeps user and server time in sync.
//   It must be the last thing created before sending the page to the user.
//
// see e107.js and class2.php
// This must be done as late as possible in page processing.
$_serverTime = time();
$lastSet = isset($_COOKIE['e107_tdSetTime']) ? intval($_COOKIE['e107_tdSetTime']) : 0;
$_serverPath = e_HTTP;
$_serverDomain = deftrue('MULTILANG_SUBDOMAIN') ? '.'.e_DOMAIN : '';
if (abs($_serverTime - $lastSet) > 120)
{
	/* update time delay every couple of minutes.
	 * Benefit: account for user time corrections and changes in internet delays
	 * Drawback: each update may cause all server times to display a bit different
	 */
	echo "<script type='text/javascript'>\n";
	echo "	SyncWithServerTime('', '{$_serverPath}', '{$_serverDomain}');\n";
	//tdOffset disabled as it can't live together with HTTP_IF_NONE_MATCH (page load speed)
	//echo "	SyncWithServerTime('{$_serverTime}', '{$_serverPath}', '{$_serverDomain}');\n";
    echo "</script>\n";
}

//
// H Final HTML
//
// browser cache control - FIXME - use this value as AJAX requests cache control!
// TODO - create the $bcache string via e107 class method, use it in the canCache() method
$uclist = e107::getUser()->getClassList();
sort($uclist, SORT_NUMERIC);
$bcache = (deftrue('e_NOCACHE') ? time() : e107::getPref('e_jslib_browser_cache')).'.'.implode(',', $uclist); 
echo "\n<!-- ".md5($bcache)." -->\n";

unset($uclist, $bcache);

$show = deftrue('e_POWEREDBY_DISABLE') ? "none" : "block"; // Let search engines find us to increase e107.org ranking - even if hidden. 
//XXX Must not contain IDs or Classes 	
// echo "<div style='text-align:center; display:".$show."; position: absolute; width:99%; height:20px; margin-top:-30px; z-index:30000; opacity:1.0; color: silver'>Proudly powered by <a style='color:silver' href='http://e107.org/' title='e107 Content Management System'>e107</a></div>";
unset($show);
echo "\n</body>\n</html>";

//hook into the end of page (usefull for example for capturing output buffering)
//Load e_output.php files.
if (!empty($pref['e_output_list']) && is_array($pref['e_output_list']))
{
	foreach($pref['e_output_list'] as $val)
	{
		$fname = e_PLUGIN.$val."/e_output.php"; // Do not place inside a function - BC $pref required. . 
		
		if(is_readable($fname))
		{
			$ret = (deftrue('e_DEBUG') || isset($_E107['debug'])) ? include_once($fname) : @include_once($fname);
		}
	}
	unset($ret);
}

//
// I Send the buffered page data, along with appropriate headers
//
//$length = ob_get_length();
//$page = ob_get_clean();


$search = array_keys($magicSC);
$replace = array_values($magicSC);

// New - see class2.php 
$ehd = new e_http_header;
$ehd->setContent('buffer', $search, $replace);
$ehd->send();
// $ehd->debug();

$page = $ehd->getOutput();


// real output
echo $page;



unset($In_e107_Footer);


// Clean session shutdown
e107::getSession()->shutdown(); // moved from the top of footer_default.php to fix https://github.com/e107inc/e107/issues/1446 (session closing before page was complete)
// Shutdown
$e107->destruct();
$e107_Clean_Exit=true;	// For registered shutdown function -- let it know all is well!


