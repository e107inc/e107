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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/log/update_routine.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-03-29 23:52:49 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
set_time_limit(180);
require_once(HEADERF);

$sql -> db_Select_gen("TRUNCATE TABLE #logstats");

$infoArray['statBrowser'] = 1;
$infoArray['statOs'] = 2;
$infoArray['statScreen'] = 5;
$infoArray['statDomain'] = 0;
$infoArray['statReferer'] = 0;
$infoArray['statQuery'] = 0;

$search = array(" @ ", "'");
$replace = array("@", "");
foreach($infoArray as $_key => $_value)
{
	if($_value)
	{
		$sql -> db_Select_gen("SELECT * FROM #stat_info WHERE info_type=$_value");
		$array = array();
		
		while($stat = $sql -> db_Fetch())
		{
			extract($stat);
			$info_name = str_replace($search, $replace, $info_name);
			$array[$info_name] += $info_count;
		}

		$data = serialize($array);
		$data = str_replace($search, $replace, $data);

		$sql -> db_Insert("logstats", "0, '$_key', '$data'");
		unset($array);
	}
	else
	{
		$sql -> db_Insert("logstats", "0, '$_key', '' ");
	}
}


if(!$sql -> db_Select("stat_counter", "*", "ORDER BY counter_date, counter_url DESC", "nowhere"))
{
	echo "Nothing to update";
	require_once(FOOTERF);
	exit;
}

$monthArray = array();
$totalArray = array();
while($stat = $sql -> db_Fetch())
{

	extract($stat);
	list($year, $month, $day) = explode("-", $counter_date);
	$monthstore = $month."-".substr($year, -2);
	list($url, $ext) = explode(".", $counter_url);
	if(strstr($url, "forum"))
	{
		$url = "forum";
	}
	$monthArray[$monthstore][$url] = array ("ttlv" => $counter_total, "unqv" => $counter_unique);

	if(array_key_exists($url, $totalArray))
	{
		$totalArray[$url]['ttlv'] += $counter_total;
		$totalArray[$url]['unqv'] += $counter_unique;
	}
	else
	{
		$totalArray[$url] = array("ttl" => 0, "unq" => 0, "ttlv" => $counter_total, 'unqv' => $counter_unique);
	}
}

foreach($monthArray as $key => $value)
{
	$sql -> db_Insert("logstats", "0, '$key', '".serialize($value)."'");
}

//$date = date("j.m.y", time());
//$sql -> db_Insert("logstats", "0, '$date', '".serialize($totalArray)."'");


$sql -> db_Select("stat_counter");
$dailyArray = array();
while($stat = $sql -> db_Fetch())
{
	extract($stat);
	list($url, $ext) = explode(".", $counter_url);
	list($year, $month, $day) = explode("-", $counter_date);
	$storedate = "$day.$month.$year";
	$dailyArray[$storedate][$url] = array("ttlv" => $counter_total, "unqv" => $counter_unique);
}

foreach($dailyArray as $key => $value)
{
	foreach($value as $key2 => $value2)
	{
		$array[$key2] = array("ttlv" => $value2['ttlv'], "unqv" => $value2['unqv']);
	}
	$tmp = serialize($array);
	$sql -> db_Insert("logstats", "0, '$key', '$tmp' ");
	unset($array);
}

echo "Finished updating.";

require_once(FOOTERF);
?>