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
if(!is_object($sql))
{
	// reinstigate db connection if another connection from third-party script closed it ...
	global $sql, $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $CUSTOMFOOTER, $FOOTER;
	$sql = new db;
	$sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
}

unset($fh);
if($e107_popup!=1)
{
	$custompage = explode(" ", $CUSTOMPAGES);
	if($CUSTOMFOOTER)
	{
		while(list($key, $kpage) = each($custompage))
		{
			if(strstr(e_SELF, $kpage))
			{
				$fh = TRUE;
				break;
			}
		}
	}
	parseheader(($fh ? $CUSTOMFOOTER : $FOOTER));

	if($pref['displayrendertime'])
	{
		global $timing_start;
		$timing_stop = explode(' ', microtime());
		$rendertime = number_format((($timing_stop[0]+$timing_stop[1])-($timing_start[0]+$timing_start[1])), 4);
		$rinfo .= "Render time: ".$rendertime." second(s). ";
	}
	if($pref['displaysql'])
	{
		global $dbq;
		$rinfo .= "DB queries: ".$dbq.". ";
	}
	if($pref['displaycacheinfo'])
	{ 
		global $cachestring;
		$rinfo .= $cachestring."."; 
	}
	echo ($rinfo ? "<div style='text-align:center' class='smalltext'>$rinfo</div>" : "");
}
echo "</body>
</html>";

$sql -> db_Close();
ob_end_flush();
?>