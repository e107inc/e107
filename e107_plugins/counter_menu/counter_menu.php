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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/counter_menu/counter_menu.php,v $
|     $Revision: 1.13 $
|     $Date: 2005-08-26 10:53:28 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
if(!defined("e_PLUGIN")){ exit; }
$text = "";
if ($pref['statActivate'])
{
	$pageName = preg_replace("/(\?.*)|(\_.*)|(\.php)/", "", basename (e_SELF));
	$logfile = e_PLUGIN."log/logs/logp_".date("z.Y", time()).".php";
	if(!is_readable($logfile))
	{
		if(ADMIN && !$pref['statCountAdmin'])
		{
			$text = COUNTER_L1;
		}
		$total = 1;
		$unique = 1;
		$siteTotal = 1;
		$siteUnique = 1;
		$totalever = 1;
		$uniqueever = 1;
	} else {
		$text = "";
		require($logfile);
		if($sql -> db_Select("logstats", "*", "log_id='statTotal' OR log_id='statUnique' OR log_id='pageTotal'"))
		{
			while($row = $sql -> db_Fetch())
			{
				if($row['log_id'] == "statTotal")
				{
					$siteTotal += $row['log_data'];
				}
				else if($row['log_id'] == "statUnique")
				{
					$siteUnique += $row['log_data'];
				}
				else
				{
					$dbPageInfo = unserialize($row['log_data']);
					$totalPageEver = ($dbPageInfo[$pageName]['ttlv'] ? $dbPageInfo[$pageName]['ttlv'] : 0);
					$uniquePageEver = ($dbPageInfo[$pageName]['unqv'] ? $dbPageInfo[$pageName]['unqv'] : 0);
				}
			}
		}
		$pageName = preg_replace("/(\?.*)|(\_.*)|(\.php)/", "", basename (e_SELF));
		$total = ($pageInfo[$pageName]['ttl'] ? $pageInfo[$pageName]['ttl'] : 0);
		$unique = ($pageInfo[$pageName]['unq'] ? $pageInfo[$pageName]['unq'] : 0);
		$totalever = ($pageInfo[$pageName]['ttlv'] ? $pageInfo[$pageName]['ttlv'] : 0) + $totalPageEver + $total;
		$uniqueever = ($pageInfo[$pageName]['unqv'] ? $pageInfo[$pageName]['unqv'] : 0) + $uniquePageEver + $unique;
	}
	$text .= "<b>".COUNTER_L2."</b><br />".COUNTER_L3.": $total<br />".COUNTER_L5.": $unique<br /><br />
	<b>".COUNTER_L4."</b><br />".COUNTER_L3.": $totalever<br />".COUNTER_L5.": $uniqueever<br /><br />
	<b>".COUNTER_L6."</b><br />".COUNTER_L3.": $siteTotal<br />".COUNTER_L5.": $siteUnique";
	$ns->tablerender(COUNTER_L7, $text, 'counter');
	unset($dbPageInfo);
}
else
{
	if(ADMIN)
	{
		$text .= "<span class='smalltext'>".COUNTER_L8."</span>";
		$ns->tablerender(COUNTER_L7, $text, 'counter');
	}
}
	
?>