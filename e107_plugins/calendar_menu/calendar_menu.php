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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/calendar_menu.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-02-28 20:03:48 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
if(!defined("e_PLUGIN")){ exit; }
$ec_dir = e_PLUGIN."calendar_menu/";
//$lan_file = $ec_dir."languages/".e_LANGUAGE.".php";
//include(file_exists($lan_file) ? $lan_file : e_PLUGIN."calendar_menu/languages/English.php");
	
$datearray = getdate();
$current_day = $datearray['mday'];
$current_month = $datearray['mon'];
$current_year = $datearray['year'];
	
// get first and last days of month in unix format---------------------------------------------------
$monthstart = mktime(0, 0, 0, $current_month, 1, $current_year);
$firstdayarray = getdate($monthstart);
$monthend = mktime(0, 0, 0, $current_month+1, 0, $current_year);
$lastdayarray = getdate($monthend);
// ----------------------------------------------------------------------------------------------------------
	
// get events from current month----------------------------------------------------------------------
	
$qry = "
SELECT e.event_rec_y, e.event_rec_y, e.event_start, e.event_end, ec.*
FROM #event as e
LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
WHERE (e.event_start >= {$monthstart} AND e.event_start <= {$monthend}) OR (e.event_end >= {$monthstart} AND e.event_end <= {$monthend}) OR e.event_rec_y = {$current_month}
";

if($sql->db_Select_gen($qry))
{
	while($row = $sql->db_Fetch())
	{
		if($row['event_rec_y'] == $month)
		{
			$events[$row['event_rec_m']][] = $row;
		}
		else
		{
			$tmp = getdate($row['event_start']);
			if($tmp['year'] == $current_year)
			{
				$start_day = $tmp['mday'];
			}
			else
			{
				$start_day = 1;
			}
			$tmp = getdate($row['event_end']);
			if($tmp['year'] == $current_year)
			{
				$end_day = $tmp['mday'];
			}
			else
			{
				$end_day = 31;
			}
			for ($i = $start_day; $i <= $end_day; $i++)
			{
				$events[$i][] = $row;
			}
		}
	}
}

// -----------------------------------------------------------------------------------------------------------
// set up arrays for calender display ------------------------------------------------------------------
$week = Array(EC_LAN_25, EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24);
$months = Array(EC_LAN_0, EC_LAN_1, EC_LAN_2, EC_LAN_3, EC_LAN_4, EC_LAN_5, EC_LAN_6, EC_LAN_7, EC_LAN_8, EC_LAN_9, EC_LAN_10, EC_LAN_11);
$calendar_title = "<a class='forumlink' href='".$ec_dir."calendar.php'>".$months[$datearray[mon]-1]." ".$current_year."</a>";
// -----------------------------------------------------------------------------------------------------------
	
$text = "<div style='text-align:center'>";
if ($events) {
	$text .= EC_LAN_26 . ": ".count($events);
} else {
	$text .= EC_LAN_27;
}

$headercss = ($pref['eventpost_headercss'] ? $pref['eventpost_headercss'] : "forumheader");
$daycss = ($pref['eventpost_daycss'] ? $pref['eventpost_daycss'] : "forumheader3");
$todaycss = ($pref['eventpost_todaycss'] ? $pref['eventpost_todaycss'] : "indent");
	
$start = $monthstart;
	
$text .= "<br /><br />
	<table cellpadding='0' cellspacing='1' style='width:100%' class='fborder'><tr>";
	
foreach($week as $day) {
	$text .= "<td class='$headercss' style='text-align:center'><span class='smalltext'>".$day."</span></td>";
}
$text .= "</tr><tr >";
	
$thismonth = $datearray['mon'];
$thisday = $datearray['mday'];
	
for($c = 0; $c < $firstdayarray['wday']; $c++) {
	$text .= "<td class='$daycss' style='text-align:center'><br /></td>";
}
$loop = $firstdayarray['wday'];
for($c = 1; $c <= 31; $c++) {
	 
	$dayarray = getdate($start+(($c-1) * 86400));
	 
	if ($dayarray['mon'] == $thismonth) {
		if ($thisday == $c) {
			$text .= "<td class='$todaycss' style='text-align:center; width: 15%;'>";
		} else {
			$text .= "<td class='$daycss' style='text-align:center; width: 15%;'>";
		}
		 
		if (array_key_exists($c, $events)) {
			$event_icon = e_PLUGIN."calendar_menu/images/".$events[$c][0]['event_cat_icon'];
			$event_count = count($events[$c]);
			if(file_exists($event_icon))
			{
				$img = "<img style='border:0' src='{$event_icon}' alt='' height='10' width='10'/>";
			}
			else
			{
				$img = $c;
			}
		}
		else
		{
			$img = $c;
			$event_count = 0;
		}
		 
		$linkut = mktime(0 , 0 , 0 , $dayarray['mon'], $c, $datearray['year']);

		if($event_count > 0)
		{
			$title = " title='{$event_count} events' ";
		}
		$text .= "<a {$title} href='".$ec_dir."event.php?".$linkut.".one'>$img</a>";
		 
		$text .= "</td>\n";
		 
		$loop++;
		if ($loop == 7) {
			$loop = 0;
			$text .= "</tr><tr>";
		}
	}
}
	
for($a = ($loop+1); $a <= 7; $a++) {
	$text .= "<td>&nbsp;</td>";
}
	
$text .= "</tr></table></div>";
$ns->tablerender($calendar_title, $text, 'calender_menu');
?>