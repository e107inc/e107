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
	global $sql, $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $CUSTOMFOOTER, $FOOTER;
	$sql = new db;
	$sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
}

$custompage = explode(" ", $CUSTOMPAGES);
if(in_array (e_PAGE, $custompage) && $CUSTOMFOOTER ? parseheader($CUSTOMFOOTER) : parseheader($FOOTER)) ; 

$timing_stop = explode(' ', microtime());
$rendertime = number_format((($timing_stop[0]+$timing_stop[1])-($timing_start[0]+$timing_start[1])), 4);
if($pref['displayrendertime']){ $rinfo .= "Render time: ".$rendertime." second(s). "; }
if($pref['displaysql']){ $rinfo .= "DB queries: ".$dbq.". "; }
if($pref['displaycacheinfo']){ $rinfo .= $cachestring."."; }
echo ($rinfo ? "<div style='text-align:center' class='smalltext'>$rinfo</div>" : "");

if($pref['log_activate']){
	echo "
<!-- log -->
<script type=\"text/javascript\">
<!--
var ref=\"\"+escape(top.document.referrer);
var colord = window.screen.colorDepth; 
var res = window.screen.width + \"x\" + window.screen.height;
var eself = document.location;


document.write(\"<img src='".e_PLUGIN."log/log.php?referer=\"+ref+\"&amp;color=\"+colord+\"&amp;eself=\"+eself+\"&amp;res=\"+res+\"' style='float:left; border:0' alt='' />\");\n
//-->
</script>";
}
echo "</body>
</html>";

$sql -> db_Close();
ob_end_flush();
?>