<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/themes/templates/templateh.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
if(!is_object($sql)){
	// reinstigate db connection if another connection from third-party script closed it ...
	global $sql, $sDBdbg, $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $CUSTOMFOOTER, $FOOTER;
	$sql = new db;
	$sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
}

unset($fh);
if($e107_popup!=1){
	$custompage = explode(" ", $CUSTOMPAGES);
	if($CUSTOMFOOTER){
		while(list($key, $kpage) = each($custompage)){
			if(strstr(e_SELF, $kpage)){
				$fh = TRUE;
				break;
			}
		}
	}
	parseheader(($fh ? $CUSTOMFOOTER : $FOOTER));
	
	$eTimingStop = explode(' ', microtime());
	global $eTimingStart;
//	echo "<br />time debug: {$eTimingStart[0]},{$eTimingStart[1]}<br />\n";
	$rendertime = number_format((($eTimingStop[0]+$eTimingStop[1])-($eTimingStart[0]+$eTimingStart[1])), 4);
	$db_time    = number_format($db_time,4);
	if($pref['displayrendertime']){ $rinfo .= "Render time: ".$rendertime." second(s); ".$db_time." of that for queries. "; }
	if($pref['displaysql']){ $rinfo .= "DB queries: ".$dbq.". "; }
	if($pref['displaycacheinfo']){ $rinfo .= $cachestring."."; }
	echo ($rinfo ? "\n<div style='text-align:center' class='smalltext'>$rinfo</div>\n" : "");
	if ($sDBdbg) {
		echo "\n<!-- DEBUG -->\n";
		echo "<br /><div class='header'>DB Debug Summary</div><br />\n";
		echo "<div style='text-align:left' class='smalltext'>\n";
		$sql->db_Show_Performance();
		echo "</div><div class='header'>DB Debug Details</div><br />\n";
		echo "<div style='text-align:left' class='smalltext'>\n";
		echo $sDBdbg,"</div>\n";
	}
}

$sql -> db_Close();

// Provide a way to sync user and server time -- see e107.js and class2.php
// This should be done as late as possible in page processing.
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
if (ob_get_level() != 1 ) {
	$oblev=ob_get_level();
	$obdbg = "<div style='text-align:center' class='smalltext'>";
	$obdbg .= "Software defect detected; ob_*() level $oblev at end.</div>";
	if ($oblev > 1) {
		while (ob_get_level() > 1) {
			ob_end_flush();  /* clear extras */
		}
	} else {
		ob_start(); // create one buffer to be completely clean...
	}
	echo $obdbg;
}
ob_end_flush(); // flush primary output -- buffer was opened in class2.php
echo "</body></html>";
?>