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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/log/admin_updateroutine.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-05-01 08:11:53 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!getperms("P")) {
	header("location:".e_BASE."index.php");
	exit;
}
require_once(e_ADMIN."auth.php");
	
//@include_once(LOGPATH."languages/admin/".e_LANGUAGE.".php");
//@include_once(LOGPATH."languages/admin/English.php");


set_time_limit(300);

$us = new updateStats;

$stage = FALSE;
foreach($_POST as $key => $post)
{
	if(strstr($key, "stageConvert"))
	{
		$stage = str_replace("stageConvert_", "", $key);
	}
}

if($stage)
{
	$func = "stage".$stage;
	$us -> $func();
	require_once(e_ADMIN."footer.php");
}
else
{
	$us -> stage0();
}
exit;






class updateStats
{

	var $title;

/* ----------------------------------------------------------------------------------------------------------------------- */

	function stage0()
	{
		$this -> title = "This script will convert your old stats database tables to the new format used in e107 v0.7+.";
		$this -> sdisplay();
	}

/* ----------------------------------------------------------------------------------------------------------------------- */

	function stage1()
	{
		global $sql;
		$this -> title = "Converting stat_counter entries ...";

		$stattotal = $sql -> db_Select("logstats", "*", "log_id='statTotal' ");
		$statunique = $sql -> db_Select("logstats", "*", "log_id='statUnique' ");

		if($sql -> db_Select("logstats", "*", "log_id='pageTotal' "))
		{
			$row = $sql -> db_Fetch();
			$pageTotal = unserialize($row['log_data']);
		}
		else
		{
			$pageTotal = array();
		}

		if(!$sql -> db_Select("stat_counter", "*", "ORDER BY counter_date, counter_url DESC", "nowhere"))
		{
			$this -> title .= "Nothing to update - done";
			$this -> sdisplay();
			return;
		}

		$monthArray = array();
		$totalArray = array();
		while($stat = $sql -> db_Fetch())
		{
			extract($stat);

			/* collate pageTotal */
			$pagename = str_replace(".php", "", $counter_url);
			$totalArray[$pagename]['ttlv'] += $counter_total;
			$totalArray[$pagename]['unqv'] += $counter_unique;
			$stattotal += $counter_total;
			$statunique += $counter_unique;
			
			/* done */

			/* collate monthly totals */
			list($year, $month, $day) = explode("-", $counter_date);
			$monthstore = $year."-".$month;
			if(strstr($pagename, "forum"))
			{
				$pagename = "forum";
			}

			$monthArray[$monthstore]['TOTAL']['ttlv'] += $counter_total;
			$monthArray[$monthstore]['TOTAL']['unqv'] += $counter_unique;
			$monthArray[$monthstore][$counter_url]['ttlv'] += $counter_total;
			$monthArray[$monthstore][$counter_url]['unqv'] += $counter_unique;

			$dailyArray[$counter_date][$pagename] = array('url' => $counter_url, 'ttl' => $counter_total, 'unq' => $counter_unique);
			$dailyTotal[$counter_date]['ttl'] += $counter_total;
			$dailyTotal[$counter_date]['unq'] += $counter_unique;
		}
		
		if(!$sql -> db_Update("logstats", "log_data='$stattotal' WHERE log_id='statTotal' "))
		{
			$sql -> db_Insert("logstats", "0, 'statTotal', '$stattotal' ");
		}
		if(!$sql -> db_Update("logstats", "log_data='$statunique' WHERE log_id='statUnique' "))
		{
			$sql -> db_Insert("logstats", "0, 'statUnique', '$statunique' ");
		}

		$totalarray = serialize($totalArray);

		if(!$sql -> db_Update("logstats", "log_data='$totalarray' WHERE log_id='pageTotal' "))
		{
			$sql -> db_Insert("logstats", "0, 'pageTotal', '$totalarray' ");
		}

		foreach($monthArray as $key => $value)
		{
			$sql -> db_Insert("logstats", "0, '$key', '".serialize($value)."'");
		}

		foreach($dailyArray as $key => $value)
		{
			$data = "";
			foreach($value as $value2)
			{
				$data .= $value2['url']."|".$value2['ttl']."|".$value2['unq'].chr(1);
			}
			$data = $dailyTotal[$key]['ttl'].chr(1).$dailyTotal[$key]['unq'].chr(1) . $data;
			$sql -> db_Insert("logstats", "0, '$key', '$data'");
		}
	
		$sql -> db_Insert("generic", "0, 'stat_update', '".time()."', '".ADMINID."', '', '1', 'stage 1 complete' ");
		$this -> sdisplay();

	}

/* ----------------------------------------------------------------------------------------------------------------------- */

	function stage2()
	{
		global $sql;

		$this -> title = "Converting broswer entries ...";

		if($sql -> db_Select("logstats", "*", "log_id='statBrowser' "))
		{
			$row = $sql -> db_Fetch();
			$browserTotal = unserialize($row['log_data']);
		}
		else
		{
			$browserTotal = array();
		}

		if(!$sql -> db_Select("stat_info", "*", "info_type='1'"))
		{
			$this -> title .= "Nothing to update - done";
			$this -> sdisplay();
			return;
		}
	
		while($stat = $sql -> db_Fetch())
		{
			extract($stat);
			$browserTotal[$info_name] += $info_count;
		}

		$data = serialize($browserTotal);

		if(!$sql -> db_Update("logstats", "log_data='$data' WHERE log_id='statBrowser' "))
		{
			$sql -> db_Insert("logstats", "0, 'statBrowser', '$data' ");
		}
		$sql -> db_Insert("generic", "0, 'stat_update', '".time()."', '".ADMINID."', '', '2', 'stage 2 complete' ");
		$this -> sdisplay();

	}

/* ----------------------------------------------------------------------------------------------------------------------- */

	function stage3()
	{
		global $sql;
		$this -> title = "Converting broswer entries ...";
		if($sql -> db_Select("logstats", "*", "log_id='statOs' "))
		{
			$row = $sql -> db_Fetch();
			$osTotal = unserialize($row['log_data']);
		}
		else
		{
			$osTotal = array();
		}
		if(!$sql -> db_Select("stat_info", "*", "info_type='2'"))
		{
			$this -> title .= "Nothing to update - done";
			$this -> sdisplay();
			return;
		}
		while($stat = $sql -> db_Fetch())
		{
			extract($stat);
			$osTotal[$info_name] += $info_count;
		}
		$data = serialize($osTotal);
		if(!$sql -> db_Update("logstats", "log_data='$data' WHERE log_id='statOs' "))
		{
			$sql -> db_Insert("logstats", "0, 'statOs', '$data' ");
		}
		$sql -> db_Insert("generic", "0, 'stat_update', '".time()."', '".ADMINID."', '', '3', 'stage 3 complete' ");
		$this -> sdisplay();
	}

/* ----------------------------------------------------------------------------------------------------------------------- */

	function stage4()
	{
		global $sql;
		$this -> title = "Converting broswer entries ...";
		if($sql -> db_Select("logstats", "*", "log_id='statDomain' "))
		{
			$row = $sql -> db_Fetch();
			$domTotal = unserialize($row['log_data']);
		}
		else
		{
			$domTotal = array();
		}
		if(!$sql -> db_Select("stat_info", "*", "info_type='4'"))
		{
			$this -> title .= "Nothing to update - done";
			$this -> sdisplay();
			return;
		}
		while($stat = $sql -> db_Fetch())
		{
			extract($stat);
			$domTotal[$info_name] += $info_count;
		}
		$data = serialize($domTotal);
		if(!$sql -> db_Update("logstats", "log_data='$data' WHERE log_id='statDomain' "))
		{
			$sql -> db_Insert("logstats", "0, 'statDomain', '$data' ");
		}
		$sql -> db_Insert("generic", "0, 'stat_update', '".time()."', '".ADMINID."', '', '4', 'stage 4 complete' ");
		$this -> sdisplay();
	}

/* ----------------------------------------------------------------------------------------------------------------------- */

	function stage5()
	{
		global $sql;
		$this -> title = "Converting broswer entries ...";
		if($sql -> db_Select("logstats", "*", "log_id='statScreen' "))
		{
			$row = $sql -> db_Fetch();
			$screenTotal = unserialize($row['log_data']);
		}
		else
		{
			$screenTotal = array();
		}
		if(!$sql -> db_Select("stat_info", "*", "info_type='5'"))
		{
			$this -> title .= "Nothing to update - done";
			$this -> sdisplay();
			return;
		}
		while($stat = $sql -> db_Fetch())
		{
			extract($stat);
			if(!strstr($info_name, "undefined") && !strstr($info_name, "res"))
			{
				$info_name = str_replace(" @ ", "@", $info_name);
				$screenTotal[$info_name] += $info_count;
			}
		}

		$data = serialize($screenTotal);
		if(!$sql -> db_Update("logstats", "log_data='$data' WHERE log_id='statScreen' "))
		{
			$sql -> db_Insert("logstats", "0, 'statScreen', '$data' ");
		}
		$sql -> db_Insert("generic", "0, 'stat_update', '".time()."', '".ADMINID."', '', '5', 'stage 5 complete' ");
		$this -> sdisplay();
	}

/* ----------------------------------------------------------------------------------------------------------------------- */

function stage6()
	{
		global $sql;
		$this -> title = "Converting broswer entries ...";
		if($sql -> db_Select("logstats", "*", "log_id='statReferer' "))
		{
			$row = $sql -> db_Fetch();
			$refTotal = unserialize($row['log_data']);
		}
		else
		{
			$refTotal = array();
		}
		if(!$sql -> db_Select("stat_info", "*", "info_type='6'"))
		{
			$this -> title .= "Nothing to update - done";
			$this -> sdisplay();
			return;
		}
		while($stat = $sql -> db_Fetch())
		{
			extract($stat);
			if(!strstr($info_name, "undefined") && !strstr($info_name, "'"))
			{
				$refTotal[$info_name] += $info_count;
			}
		}
		$data = serialize($refTotal);

		if(!$sql -> db_Update("logstats", "log_data='$data' WHERE log_id='statReferer' "))
		{
			$sql -> db_Insert("logstats", "0, 'statReferer', '$data' ");
		}
		$sql -> db_Insert("generic", "0, 'stat_update', '".time()."', '".ADMINID."', '', '6', 'stage 6 complete' ");
		$this -> sdisplay();
	}

/* ----------------------------------------------------------------------------------------------------------------------- */

	function sdisplay()
	{
		global $ns, $sql;

		if($sql -> db_Select("generic", "*", "gen_type='stat_update' "))
		{
			$gen = $sql -> db_Fetch();
			$stage = $gen['gen_intdata'];
		}

		$statCount = $sql -> db_Count("stat_counter");
		$browserCount = $sql -> db_Count("stat_info", "(*)", "WHERE info_type=1");
		$osCount = $sql -> db_Count("stat_info", "(*)", "WHERE info_type=2");
		$domainCount = $sql -> db_Count("stat_info", "(*)", "WHERE info_type=4");
		$screenCount = $sql -> db_Count("stat_info", "(*)", "WHERE info_type=5");
		$refCount = $sql -> db_Count("stat_info", "(*)", "WHERE info_type=6");
		$lastCount = $sql -> db_Count("stat_last");

		$status = ($sql -> db_Select("generic", "*", "gen_type='stat_update' && gen_intdata=1"));
		$text = "
		<div style='text-align:center; margin-left: auto; margin-right: auto;'>
		<form method='post' action='".e_SELF."'>
		<table class='fborder' style='width: 95%;'>
		<tr>
		<td colspan='3' class='forumheader' style='text-align:center;'>".$this -> title."</td>
		</tr>
		<tr>
		<td style='width: 10%; text-align:center;' class='forumheader3'>
		<img src='".e_PLUGIN."log/images/".($status ? "tick" : "cross").".png' alt='' style='vertical-align: middle;' />
		</td>
		<td style='width: 50%;' class='forumheader3'>".
		($status ? "Entries in stat_counter table converted: $statCount" : "Entries in stat_counter table to convert: $statCount")."
		</td>
		<td style='width: 40%; text-align:center;' class='forumheader3'>
		".
		($status ? "Converted" : "<input class='button' type='submit' name='stageConvert_1' value='Begin conversion' />")."
		</td>
		</tr>
		";

		$status = ($sql -> db_Select("generic", "*", "gen_type='stat_update' && gen_intdata=2"));
		$text .= "<tr>
		<td style='width: 10%; text-align:center;' class='forumheader3'>
		<img src='".e_PLUGIN."log/images/".($status ? "tick" : "cross").".png' alt='' style='vertical-align: middle;' />
		<td style='width: 50%;' class='forumheader3'>".
		($status ? "Browser entries converted: $browserCount" : "Browser entries to convert: $browserCount")."</td>
		<td style='width: 40%; text-align:center;' class='forumheader3'>";
		$text .= ($status ?  "Converted" : "<input class='button' type='submit' name='stageConvert_2' value='Begin conversion' />")."
		</td>
		</tr>
		";

		$status = ($sql -> db_Select("generic", "*", "gen_type='stat_update' && gen_intdata=3"));
		$text .= "<tr>
		<td style='width: 10%; text-align:center;' class='forumheader3'>
		<img src='".e_PLUGIN."log/images/".($status ? "tick" : "cross").".png' alt='' style='vertical-align: middle;' />
		<td style='width: 50%;' class='forumheader3'>".
		($status ? "Operating system entries converted: $osCount" : "Operating system entries to convert: $osCount")."</td>
		<td style='width: 40%; text-align:center;' class='forumheader3'>";
		$text .= ($status ?  "Converted" : "<input class='button' type='submit' name='stageConvert_3' value='Begin conversion' />")."
		</td>
		</tr>
		";

		$status = ($sql -> db_Select("generic", "*", "gen_type='stat_update' && gen_intdata=4"));
		$text .= "<tr>
		<td style='width: 10%; text-align:center;' class='forumheader3'>
		<img src='".e_PLUGIN."log/images/".($status ? "tick" : "cross").".png' alt='' style='vertical-align: middle;' />
		<td style='width: 50%;' class='forumheader3'>".
		($status ? "Domain entries converted: $domainCount" : "Domain entries to convert: $domainCount")."</td>
		<td style='width: 40%; text-align:center;' class='forumheader3'>";
		$text .= ($status ?  "Converted" : "<input class='button' type='submit' name='stageConvert_4' value='Begin conversion' />")."
		</td>
		</tr>
		";

		$status = ($sql -> db_Select("generic", "*", "gen_type='stat_update' && gen_intdata=5"));
		$text .= "<tr>
		<td style='width: 10%; text-align:center;' class='forumheader3'>
		<img src='".e_PLUGIN."log/images/".($status ? "tick" : "cross").".png' alt='' style='vertical-align: middle;' />
		<td style='width: 50%;' class='forumheader3'>".
		($status ? "Screen entries converted: $screenCount" : "Screen entries to convert: $screenCount")."</td>
		<td style='width: 40%; text-align:center;' class='forumheader3'>";
		$text .= ($status ?  "Converted" : "<input class='button' type='submit' name='stageConvert_5' value='Begin conversion' />")."
		</td>
		</tr>
		";

		$status = ($sql -> db_Select("generic", "*", "gen_type='stat_update' && gen_intdata=6"));
		$text .= "<tr>
		<td style='width: 10%; text-align:center;' class='forumheader3'>
		<img src='".e_PLUGIN."log/images/".($status ? "tick" : "cross").".png' alt='' style='vertical-align: middle;' />
		<td style='width: 50%;' class='forumheader3'>".
		($status ? "Referrer entries converted: $refCount" : "Referrer entries to convert: $refCount")."</td>
		<td style='width: 40%; text-align:center;' class='forumheader3'>";
		$text .= ($status ?  "Converted" : "<input class='button' type='submit' name='stageConvert_6' value='Begin conversion' />")."
		</td>
		</tr>

		</table>
		</form>
		</div>
		";

		$ns -> tablerender("Stats Update", $text);
	}

	

}














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