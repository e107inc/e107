<?php 
/*
 * e107 website system
 *
 * Copyright (C) 2008-2019 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin Footer
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/footer.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */
if (!defined('e107_INIT'))
{
	exit;
}
$In_e107_Footer = TRUE; // For registered shutdown function

global $error_handler,$db_time,$ADMIN_FOOTER;

// Legacy fix - call header if not already done, mainly fixing left side menus to work proper
if(!deftrue('e_ADMIN_UI') && !deftrue('ADMIN_AREA'))
{
	// close the old buffer
	$content =  ob_get_contents();
	ob_get_clean();
	// open new
	ob_start();
	require_once(e_ADMIN.'header.php');
	echo $content;
}

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

$e107 = e107::getInstance();
$sql = e107::getDb();
e107::getSingleton('e107_traffic')->Bump('Lost Traffic Counters');
$pref = e107::getPref();

if (varset($e107_popup) != 1)
{
	//
	// B.1 Clear cache if over a week old
	//
	if (ADMIN === TRUE)
	{
		if ($pref['cachestatus'])
		{
			if (!$sql->select('generic', '*', "gen_type='empty_cache'"))
			{
				$sql->insert('generic', "0,'empty_cache','".time()."','0','','0',''");
			}
			else
			{
				$row = $sql->fetch();
				if (($row['gen_datestamp'] + 604800) < time()) // If cache not cleared in last 7 days, clear it.
				{
					require_once (e_HANDLER."cache_handler.php");
					$ec = new ecache;
					$ec->clear();
					$sql->update('generic', "gen_datestamp='".time()."' WHERE gen_type='empty_cache'");
				}
			}
		}
	}
	
	//
	// B.2 Send footer template, stop timing, send simple page stats
	//
	//NEW - Iframe mod
	if (!deftrue('e_IFRAME'))
	{
		parse_admin($ADMIN_FOOTER);
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
	$rinfo = '';
	
	if (function_exists('getrusage') && !empty($eTimingStartCPU))
	{
		$ru = getrusage();
		$cpuUTime = $ru['ru_utime.tv_sec'] + ($ru['ru_utime.tv_usec'] * (1e-6));
		$cpuSTime = $ru['ru_stime.tv_sec'] + ($ru['ru_stime.tv_usec'] * (1e-6));
		$cpuUStart = $eTimingStartCPU['ru_utime.tv_sec'] + ($eTimingStartCPU['ru_utime.tv_usec'] * (1e-6));
		$cpuSStart = $eTimingStartCPU['ru_stime.tv_sec'] + ($eTimingStartCPU['ru_stime.tv_usec'] * (1e-6));
		$cpuStart = $cpuUStart + $cpuSStart;
		$cpuTot = $cpuUTime + $cpuSTime;
		$cpuTime = $cpuTot - $cpuStart;
		$cpuPct = 100.0 * $cpuTime / $rendertime; /* CPU load during known clock time */
		
		// Format for display or logging (Uncomment as needed for logging)
		// User cpu
		//$cpuUTime = number_format($cpuUTime, 3);
		// System cpu
		//$cpuSTime = number_format($cpuSTime, 3);
		// Total (User+System)
		//$cpuTot = number_format($cpuTot, 3);
		
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
		$rinfo .= $rendertime.CORE_LAN12.$dbPercent.CORE_LAN13;
	}
	if ($pref['displaysql'])
	{
		$rinfo .= CORE_LAN15.$sql->db_QueryCount().". ";
	}
	if (!empty($pref['display_memory_usage']))
	{
		$rinfo .= CORE_LAN16.$memuse;
	}
	if (!empty($pref['displaycacheinfo']) && !empty($cachestring))
	{
		$rinfo .= $cachestring.".";
	}
	
	if (function_exists('theme_renderinfo'))
	{
		theme_renderinfo($rinfo);
	}
	else
	{

		if(!deftrue('e_IFRAME'))
		{
			echo($rinfo ? "\n<div class='e-footer-info muted center' style='padding-bottom:20px; margin-top:10px'><small>{$rinfo}</small></div>\n" : "");
		}
	}
	
} // End of regular-page footer (the above NOT done for popups)

//
// C Dump all debug and traffic information
//
if ((ADMIN || $pref['developer']) && E107_DEBUG_LEVEL)
{
	// global $db_debug;
	echo "\n<!-- DEBUG -->\n<div class='e-debug debug-info'>";
	e107::getDebug()->Show_All();
	echo "</div>\n";
}

/*
 changes by jalist 24/01/2005:
 show sql queries
 usage: add ?showsql to query string, must be admin
 */

// XXX - Too old? Something using this? 
if (ADMIN && isset($queryinfo) && is_array($queryinfo))
{
	$c = 1;
	$mySQLInfo = $sql->mySQLinfo;
	echo "<div class='e-debug query-notice'>
		<table class='fborder table table-bordered table-striped' style='width: 100%;'>
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
	e107::getRender()->tablerender('Quick Admin Timer', "Results: {$tmp} microseconds");
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
// DEPRECATED - use  e107::js('footer', '{e_PLUGIN}myplug/js/my.js', $zone = 2)
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
if (is_array($pref['e_footer_list']))
{
//	ob_start();
	
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


// [JSManager] Load JS Footer Includes by priority
e107::getJs()->renderJs('footer', true);

// [JSManager] Load JS Footer inline code by priority


//
// G final JS script keeps user and server time in sync.
//   It must be the last thing created before sending the page to the user.
//

// All JavaScript settings are placed in the footer of the page with the library weight so that inline scripts appear afterwards.
e107::getJs()->renderJs('settings');
e107::getJs()->renderJs('footer_inline', true);

//
// H Final HTML
//
echo "</body></html>";

//
// I Send the buffered page data, along with appropriate headers
//
$tmp = array();

$magicSC = e107::getRender()->getMagicShortcodes(); // support for {---TITLE---} etc.

$tmp['search'] = array_keys($magicSC);
$tmp['replace'] = array_values($magicSC);

$e_js =  e107::getJs();

$tmp0 = $e_js->renderJs('library_css', false, 'css', true);
if($tmp0)
{
	$tmp['search'][] = '<!-- footer_library_css -->';
	$tmp['replace'][] = $tmp0;
}

// Other CSS - from unknown location, different from core/theme/plugin location or backward compatibility
$tmp1 = $e_js->renderJs('other_css', false, 'css', true);
if($tmp1) 
{
	$tmp['search'][] = '<!-- footer_other_css -->';
	$tmp['replace'][] = $tmp1;
}

// Core CSS
$tmp1 = $e_js->renderJs('core_css', false, 'css', true);
if($tmp1) 
{
	$tmp['search'][] = '<!-- footer_core_css -->';
	$tmp['replace'][] = $tmp1;
}


// Plugin CSS
$tmp1 = $e_js->renderJs('plugin_css', false, 'css', true);
if($tmp1) 
{
	$tmp['search'][] = '<!-- footer_plugin_css -->';
	$tmp['replace'][] = $tmp1;
}

//echo "<!-- Theme css -->\n";
$tmp1 = $e_js->renderJs('theme_css', false, 'css', true);
if($tmp1) 
{
	$tmp['search'][] = '<!-- footer_theme_css -->';
	$tmp['replace'][] = $tmp1;
}

// Inline CSS - not sure if this should stay at all!
$tmp1 = $e_js->renderJs('inline_css', false, 'css', true);
if($tmp1) 
{
	$tmp['search'][] = '<!-- footer_inline_css -->';
	$tmp['replace'][] = $tmp1;
}

// New - see class2.php
$ehd = new e_http_header;
if($tmp)
{
	$ehd->setContent('buffer',$tmp['search'],$tmp['replace']);
}
else
{
	$ehd->setContent('buffer');
}
unset($tmp1, $tmp1);
$ehd->send();
$page = $ehd->getOutput();
// $ehd->debug();

// real output
echo $page;

unset($In_e107_Footer);


// Clean session shutdown
e107::getSession()->shutdown();
// Shutdown
$e107->destruct();
$e107_Clean_Exit = TRUE; // For registered shutdown function -- let it know all is well!