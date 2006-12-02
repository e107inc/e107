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
|     $Source: /cvs_backup/e107_0.8/e107_admin/footer.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:33:22 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

global $ADMIN_FOOTER, $footer_js, $sql;

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
if (strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE) {
	parse_admin($ADMIN_FOOTER);
}
$eTimingStop = microtime();
global $eTimingStart, $eTraffic;
$rendertime = number_format($eTraffic->TimeDelta( $eTimingStart, $eTimingStop ), 4);
$db_time    = number_format($db_time,4);
$rinfo = '';

if($pref['displayrendertime']){ $rinfo .= "Render time: {$rendertime} second(s); {$db_time} of that for queries. "; }
if($pref['displaysql']){ $rinfo .= "DB queries: ".$sql -> db_QueryCount().". "; }
if(isset($pref['displaycacheinfo']) && $pref['displaycacheinfo']){ $rinfo .= $cachestring."."; }
echo ($rinfo ? "\n<div style='text-align:center' class='smalltext'>{$rinfo}</div>\n" : "");

if($error_handler->debug == true) {
	echo "
	<br /><br />";

	echo "
	<div>
		<h3>PHP Errors:</h3><br />
		".$error_handler->return_errors()."
	</div>
	";
	$tmp = $eTraffic->Display();
	if (strlen($tmp)) {
		$ns->tablerender('Traffic Counters', $tmp);
	}

	$tmp = $db_debug->Show_Performance();
	if (strlen($tmp)) {
		$ns->tablerender('Time Analysis', $tmp);
	}
	$tmp = $db_debug->Show_SQL_Details();
	if (strlen($tmp)) {
		$ns->tablerender('SQL Analysis', $tmp);
	}
}

if (function_exists('theme_foot'))
{
	echo theme_foot();
}

if(isset($footer_js) && is_array($footer_js))
{
	$footer_js = array_unique($footer_js);
	foreach($footer_js as $fname)
	{
		echo "<script type='text/javascript' src='{$fname}'></script>\n";
		$js_included[] = $fname;
	}
}

echo "</body></html>";

$sql->db_Close();

?>
