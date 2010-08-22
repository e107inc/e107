<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

set_time_limit(300);

$us = new updateStats;

for ($i = 1; $i <= 7; $i++) {
	if (!$sql -> db_Select("generic", "*", "gen_type='stat_update' && gen_intdata=".$i)) {
		if (!$sql -> db_Select("stat_info", "*", "info_type='99' && info_count='".$i."'")) {
			$func = "stage".$i;
			$us -> $func();
			$sql -> db_Insert("stat_info", "'Stats Update Stage ".$i." Complete', '".$i."', '99'");
		}
	} else {
		$sql -> db_Insert("stat_info", "'Stats Update Stage ".$i." Complete', '".$i."', '99'");
		$sql -> db_Delete("generic", "gen_type='stat_update' && gen_intdata=".$i);
	}
}

if (!$sql -> db_Select("logstats", "*", "log_id='statQuery'")) {
	$sql -> db_Insert("logstats", "0, 'statQuery', ''");
}

class updateStats {

	function stage1() {
		global $sql;
		// Converting stat_counter entries
	
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
	}

/* ----------------------------------------------------------------------------------------------------------------------- */

	function stage2()
	{
		// Converting browser entries
		global $sql;

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
	}

/* ----------------------------------------------------------------------------------------------------------------------- */

	function stage3()
	{
		// Operating system entries
		global $sql;
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
	}

/* ----------------------------------------------------------------------------------------------------------------------- */

	function stage4()
	{
		// Domain entries to convert 
		global $sql;
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
	}

/* ----------------------------------------------------------------------------------------------------------------------- */

	function stage5()
	{
		// Screen entries to convert
		global $sql;
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
	}

/* ----------------------------------------------------------------------------------------------------------------------- */

function stage6()
	{
		// Converting referrer entries
		global $sql;
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
			return;
		}
		while($stat = $sql -> db_Fetch())
		{
			extract($stat);
			if(!strstr($info_name, "undefined") && !strstr($info_name, "'"))
			{
				$refTotal[$info_name]['url'] = $info_name;
				$refTotal[$info_name]['ttl'] += $info_count;
			}
		}
		$data = serialize($refTotal);

		if(!$sql -> db_Update("logstats", "log_data='$data' WHERE log_id='statReferer' "))
		{
			$sql -> db_Insert("logstats", "0, 'statReferer', '$data' ");
		}
	}
	
	
	/* ----------------------------------------------------------------------------------------------------------------------- */

	function stage7()
	{
		// Correcting referrer entries
		global $sql;
		$sql -> db_Select("logstats", "*", "log_id='statReferer'");
		$row = $sql -> db_Fetch();
		$refTotal = unserialize($row['log_data']);

		foreach ($refTotal as $key => $ref) {
			if (!is_array($ref)){
				unset($refTotal['key']);
				$refTotal[$key]['url'] = $key;
				$refTotal[$key]['ttl'] = $ref;
			}
		}
		$data = serialize($refTotal);

		if(!$sql -> db_Update("logstats", "log_data='$data' WHERE log_id='statReferer' "))
		{
			$sql -> db_Insert("logstats", "0, 'statReferer', '$data' ");
		}
	}
}

?>