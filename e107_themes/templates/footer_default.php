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
|     $Source: /cvs_backup/e107_0.7/e107_themes/templates/footer_default.php,v $
|     $Revision: 1.16 $
|     $Date: 2005-03-13 10:59:53 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
global $eTraffic,$db_time,$sql, $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $CUSTOMFOOTER, $FOOTER;

if(!is_object($sql)){
        // reinstigate db connection if another connection from third-party script closed it ...
        $sql = new db;
        $sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
}
if (!is_object($eTraffic)) {
    $eTraffic = new e107_traffic;
    $eTraffic->Bump('Lost Traffic Counters');
}

unset($fh);
if($e107_popup!=1){
        parseheader(($ph ? $cust_footer : $FOOTER));
        $eTimingStop = microtime();
        global $eTimingStart;
        $rendertime = number_format($eTraffic->TimeDelta( $eTimingStart, $eTimingStop ), 4);
        $db_time    = number_format($db_time,4);
        $rinfo = '';

        if($pref['displayrendertime']){ $rinfo .= "Render time: ".$rendertime." second(s); ".$db_time." of that for queries. "; }
        if($pref['displaysql']){ $rinfo .= "DB queries: ".$sql -> db_QueryCount().". "; }
        if(isset($pref['displaycacheinfo']) && $pref['displaycacheinfo']){ $rinfo .= $cachestring."."; }
        echo ($rinfo ? "\n<div style='text-align:center' class='smalltext'>$rinfo</div>\n" : "");
        if (ADMIN && E107_DEBUG_LEVEL) {
                global $db_debug,$ns;
                echo "\n<!-- DEBUG -->\n";
                if (!isset($ns)) {
                    echo "Why did ns go away?<br/>";
                    $ns = new e107table;
                }
                
                $tmp = $eTraffic->Display();
                if (strlen($tmp)) {
                    $ns->tablerender('Traffic Counters',$tmp);
                }
                $tmp = $db_debug->Show_Performance();
                if (strlen($tmp)) {
                    $ns->tablerender('Time Analysis',$tmp);
                }
                $tmp = $db_debug->Show_SQL_Details();
                if (strlen($tmp)) {
                    $ns->tablerender('SQL Analysis',$tmp);
                }
				$tmp = $db_debug->Show_SC_BB();
                if (strlen($tmp)) {
                    $ns->tablerender('Shortcodes / BBCode',$tmp);
                }
        }
}

$sql -> db_Close();

/*
changes by jalist 24/01/2005:
show sql queries
usage: add ?showsql to query string, must be admin
*/

if(is_array($queryinfo) && ADMIN)
{
	$c=1;
	$mySQLInfo = $sql->mySQLinfo;
	echo "<table class='fborder' style='width: 100%;'>
	<tr>
	<td class='fcaption' style='width: 5%;'>ID</td><td class='fcaption' style='width: 95%;'>SQL Queries</td>\n</tr>\n";
	foreach ($queryinfo as $infovalue)
	{
		echo "<tr>\n<td class='forumheader3' style='width: 5%;'>$c</td><td class='forumheader3' style='width: 95%;'>".$infovalue."</td>\n</tr>\n";
		$c++;
	}
	echo "</table>";
}

//
// Just before we quit: dump quick timer if there is any
// Works any time we get this far. Not calibrated, but it is quick and simple to use.
// To use: eQTimeOn(); eQTimeOff();
//
$tmp = eQTimeElapsed();
if (strlen($tmp)) {
	global $ns;
	$ns->tablerender('Quick Admin Timer',"Results: $tmp microseconds");
}

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
global $start_ob_level;
if (ob_get_level() != $start_ob_level ) {
        $oblev=ob_get_level();
        $obdbg = "<div style='text-align:center' class='smalltext'>";
        $obdbg .= "Software defect detected; ob_*() level $oblev at end.</div>";
        if ($oblev > $start_ob_level) {
                while (ob_get_level() > $start_ob_level) {
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