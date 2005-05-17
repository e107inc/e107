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
|     $Source: /cvs_backup/e107_0.7/e107_admin/footer.php,v $
|     $Revision: 1.11 $
|     $Date: 2005-05-17 21:26:19 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

global $ADMIN_FOOTER;
if (!defined("e_HTTP")) {
	exit;
}
if (ADMIN == TRUE) {
	if ($pref['cachestatus']) {
		if (!$sql->db_Select('generic', '*', "gen_type='empty_cache'"))
		{
			$sql->db_Insert('generic', "0,'empty_cache',".time().",'','','',''");
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
	global $eTimingStart;
	$rendertime = number_format($eTraffic->TimeDelta( $eTimingStart, $eTimingStop ), 4);
	$db_time    = number_format($db_time,4);
	$rinfo = '';

	if($pref['displayrendertime']){ $rinfo .= "Render time: {$rendertime} second(s); {$db_time} of that for queries. "; }
	if($pref['displaysql']){ $rinfo .= "DB queries: ".$sql -> db_QueryCount().". "; }
	if(isset($pref['displaycacheinfo']) && $pref['displaycacheinfo']){ $rinfo .= $cachestring."."; }
	echo ($rinfo ? "\n<div style='text-align:center' class='smalltext'>{$rinfo}</div>\n" : "");echo "<div style='text-align: center; margin-left: auto; margin-right: auto;'><a href='".e_ADMIN."credits.php'>Credits</a></div>";
?>

</body>
</html>

<?php
$sql->db_Close();
?>