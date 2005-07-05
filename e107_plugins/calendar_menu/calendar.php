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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/calendar.php,v $
|     $Revision: 1.12 $
|     $Date: 2005-07-05 21:31:42 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/ 


require_once("../../class2.php");
require_once(e_PLUGIN."calendar_menu/calendar_shortcodes.php");
if (isset($_POST['viewallevents']))
{
    Header("Location: " . e_PLUGIN . "calendar_menu/event.php?" . $_POST['enter_new_val']);
} 
if (isset($_POST['doit']))
{
    Header("Location: " . e_PLUGIN . "calendar_menu/event.php?ne." . $_POST['enter_new_val']);
}
if (isset($_POST['subs']))
{
    Header("Location: " . e_PLUGIN . "calendar_menu/subscribe.php");
} 

$ec_dir		= e_PLUGIN . "calendar_menu/";
$lan_file	= $ec_dir . "languages/" . e_LANGUAGE . ".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN . "calendar_menu/languages/English.php");
define("PAGE_NAME", EC_LAN_121);

if (is_readable(THEME."calendar_template.php")) {
	require_once(THEME."calendar_template.php");
	} else {
	require_once(e_PLUGIN."calendar_menu/calendar_template.php");
}

$cal_super	= check_class($pref['eventpost_super']);
$num		= $_POST['num'];

require_once(HEADERF);

// get current date information ---------------------------------------------------------------------
$qs = explode(".", e_QUERY);
if($qs[0] == "")
{
    $datearray	= getdate();
    $month		= $datearray['mon'];
    $year		= $datearray['year'];
    $day		= $datearray['day'];
} 
else
{
    $datearray	= getdate($qs[0]);
    $month		= $datearray['mon'];
    $year		= $datearray['year'];
}

// set up arrays for calender display ------------------------------------------------------------------
if($pref['eventpost_weekstart'] == 'sun') {
	$week	= Array(EC_LAN_25, EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24);
	} else {
	$week	= Array(EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24, EC_LAN_25);
}	
$months		= Array(EC_LAN_0, EC_LAN_1, EC_LAN_2, EC_LAN_3, EC_LAN_4, EC_LAN_5, EC_LAN_6, EC_LAN_7, EC_LAN_8, EC_LAN_9, EC_LAN_10, EC_LAN_11);
$monthabb	= Array(EC_LAN_JAN, EC_LAN_FEB, EC_LAN_MAR, EC_LAN_APR, EC_LAN_MAY, EC_LAN_JUN, EC_LAN_JUL, EC_LAN_AUG, EC_LAN_SEP, EC_LAN_OCT, EC_LAN_NOV, EC_LAN_DEC);

// show events-------------------------------------------------------------------------------------------
$monthstart		= mktime(0, 0, 0, $month, 1, $year);
$firstdayarray	= getdate($monthstart);
$monthend		= mktime(0, 0, 0, $month + 1, 1, $year) - 1;
$lastdayarray	= getdate($monthend);

$prevmonth		= ($month-1);
$prevyear		= $year;
if ($prevmonth == 0)
{
    $prevmonth	= 12;
    $prevyear	= ($year-1);
} 
$previous = mktime(0, 0, 0, $prevmonth, 1, $prevyear);

$nextmonth		= ($month + 1);
$nextyear		= $year;
if ($nextmonth == 13)
{
    $nextmonth	= 1;
    $nextyear	= ($year + 1);
} 
$next			= mktime(0, 0, 0, $nextmonth, 1, $nextyear);
$py				= $year-1;
$prevlink		= mktime(0, 0, 0, $month, 1, $py);
$ny				= $year + 1;
$nextlink		= mktime(0, 0, 0, $month, 1, $ny);

$prop		= mktime(0, 0, 0, $month, 1, $year);
$nowarray	= getdate();
$nowmonth	= $nowarray['mon'];
$nowyear	= $nowarray['year'];
$nowday		= $nowarray['mday'];
$current	= mktime(0, 0, 0, $nowmonth, 1, $nowyear); 

// time switch buttons
$cal_text = $tp -> parseTemplate($CALENDAR_TIME_TABLE, FALSE, $calendar_shortcodes);


// navigation buttons
$nav_text = $tp -> parseTemplate($CALENDAR_NAVIGATION_TABLE, FALSE, $calendar_shortcodes);

// get events from current month
if ($cal_super)
{
    $qry = "SELECT e.*, ec.*
			FROM #event as e
			LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
			WHERE e.event_id != ''
			ORDER BY e.event_start";
			//WHERE ((e.event_start >= {$monthstart} AND e.event_start <= {$monthend}) OR (e.event_end >= {$monthstart} AND e.event_end <= {$monthend}) OR e.event_rec_y = {$month})
} 
else
{
    $qry = "SELECT e.*, ec.*
			FROM #event as e
			LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
			WHERE e.event_id != ''
			AND find_in_set(event_cat_class,'".USERCLASS_LIST."') 
			ORDER BY e.event_start";
			//WHERE ((e.event_start >= {$monthstart} AND e.event_start <= {$monthend}) OR (e.event_end >= {$monthstart} AND e.event_end <= {$monthend}) OR e.event_rec_y = {$month})
} 

if ($sql->db_Select_gen($qry))
{
    while ($row = $sql->db_Fetch())
    {
		/* changes by lisa_ */
		$evf = getdate($row['event_start']);
		$tmp = $evf['mday'];
		$eve = getdate($row['event_end']);
		$tmp2 = $eve['mday'];
		$tmp3 = date("t", $monthstart); // number of days in this month

		// check for recurring events in this month
		if($row['event_recurring']=='1' && $month == $row['event_rec_y']){
			$event_start = mktime(0,0,0,$row['event_rec_y'],$row['event_rec_m'],$year);
		}
		//1) start in month, end in month
		if(($row['event_start']>=$monthstart && $row['event_start']<=$monthend) && $row['event_end']<=$monthend){
			//$cevent_title[$tmp][] = $event_title;
			//$event_true[$tmp][] = $event_start;
			//$cevent_cat[$tmp][] = $event_category;
			//$cevent_start[$tmp][] = $event_start;
			$events[$tmp][] = $row;
			for ($c=($tmp+1); $c<($tmp2+1); $c++) {
				$event_true_end[$c][] = ($c!=$tmp2 ? 1 : 2);
				//$cevent_title[$c][] = $event_title;
				//$cevent_cat[$c][] = $event_category;
				//$cevent_start[$c][] = $event_start;
				$events[$c][] = $row;
			}

		//2) start in month, end after month
		}elseif(($row['event_start']>=$monthstart && $row['event_start']<=$monthend) && $row['event_end']>=$monthend){
			//$cevent_title[$tmp][] = $event_title;
			//$event_true[$tmp][] = $event_start;
			//$cevent_cat[$tmp][] = $event_category;
			//$cevent_start[$tmp][] = $event_start;
			$events[$tmp][] = $row;
			for ($c=($tmp+1); $c<=$tmp3; $c++){
				//$event_true_end[$c][] = 1;
				$row['event_true_end'] = 1;
				//$cevent_title[$c][] = $event_title;
				//$cevent_cat[$c][] = $event_category;
				//$cevent_start[$c][] = $event_start;	
				$events[$c][] = $row;
			}

		//3) start before month, end in month
		}elseif($row['event_start']<=$monthstart && ($row['event_end']>=$monthstart && $row['event_end']<=$monthend)){
			for ($c=1; $c<=$tmp2; $c++){
				//$event_true_end[$c][] = ($c!=$tmp2 ? 1 : 2);
				$row['event_true_end'] = ($c!=$tmp2 ? 1 : 2);
				//$cevent_title[$c][] = $event_title;
				//$cevent_cat[$c][] = $event_category;
				//$cevent_start[$c][] = $event_start;
				$events[$c][] = $row;
			}

		//4) start before month, end after month
		}elseif($row['event_start']<=$monthstart && $row['event_end']>=$monthend){
			for ($c=1; $c<=$tmp3; $c++){
				//$event_true_end[$c][] = 1;
				$row['event_true_end'] = 1;
				//$cevent_title[$c][] = $event_title;
				//$cevent_cat[$c][] = $event_category;
				//$cevent_start[$c][] = $event_start;
				$events[$c][] = $row;
			}
		}
		/* end lisa_ */


		/*
		if ($row['event_rec_y'] == $month){
			$events[$row['event_rec_m']][] = $row;
		}else{
			$tmp = getdate($row['event_start']);
			if ($tmp['year'] == $year){
				$start_day = $tmp['mday'];
			}else{
				$start_day = 1;
			} 
			$tmp = getdate($row['event_end']);
			if ($tmp['year'] == $year){
				$end_day = $tmp['mday'];
			}else{
				$end_day = 31;
			} 
			for ($i = $start_day; $i <= $end_day; $i++){
				$events[$i][] = $row;
			}
		}
		*/
	}
}

$start		= $monthstart;
$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_START, FALSE, $calendar_shortcodes);
$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_HEADER_START, FALSE, $calendar_shortcodes);
foreach($week as $day)
{
	$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_HEADER, FALSE, $calendar_shortcodes);
} 
$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_HEADER_END, FALSE, $calendar_shortcodes);

$calmonth	= $datearray['mon'];
$calday		= $datearray['mday'];
$calyear	= $datearray['year'];

if ($pref['eventpost_weekstart'] == 'mon') {
	$firstdayoffset = ($firstdayarray['wday'] == 0 ? $firstdayarray['wday']+6 : $firstdayarray['wday']-1);
	} else {
	$firstdayoffset = $firstdayarray['wday'] ;
}
for ($c=0; $c<$firstdayoffset; $c++) {
	$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_DAY_NON, FALSE, $calendar_shortcodes);
}
$loop = $firstdayoffset;

for ($c = 1; $c <= 31; $c++)
{
    $dayarray	= getdate($start + (($c-1) * 86400));
    $stopp		= mktime(24, 0, 0, $calmonth, $c, $calyear);
    $startt		= mktime(0, 0, 0, $calmonth, $c, $calyear); 
    // Highlight the current day.
    if ($dayarray['mon'] == $calmonth)
    {
        if ($nowday == $c && $calmonth == $nowmonth && $calyear == $nowyear)
        {
        	//today
			$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_DAY_TODAY, FALSE, $calendar_shortcodes);
        //}elseif (array_key_exists($c, $events)){
		}elseif(count($events[$c]) > 0){
			//day has events
			$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_DAY_EVENT, FALSE, $calendar_shortcodes);
        } 
        else
        {
            // no events and not today
			$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_DAY_EMPTY, FALSE, $calendar_shortcodes);
        } 
        // if there are events then list them
        if (array_key_exists($c, $events)){
            foreach($events[$c] as $ev)
            {
				//if ($event_true_end[$c][$a]){
				if($ev['event_true_end']){
					//$ev['indicat'] = ($ev['event_true_end']==1 ? "->" : "|");
					$ev['indicat'] = "";
					$ev['imagesize'] = "4";
					$ev['fulltopic'] = FALSE;
					$ev['startofevent'] = FALSE;
				}else{
					$ev['indicat'] = "";
					$ev['imagesize'] = "8";
					$ev['fulltopic'] = TRUE;
					$ev['startofevent'] = TRUE;
				}
				
				if (($_POST['do'] == null || $_POST['event_cat_ids'] == "all") || ($_POST['event_cat_ids'] == $event['event_cat_id']))
				{
					$text .= $tp -> parseTemplate($CALENDAR_SHOWEVENT, FALSE, $calendar_shortcodes);
				} 
			} 
        } 
		$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_DAY_END, FALSE, $calendar_shortcodes);
    } 
    $loop++;
    if ($loop == 7)
    {
        $loop = 0;
		$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_WEEKSWITCH, FALSE, $calendar_shortcodes);
    } 
} 
$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_END, FALSE, $calendar_shortcodes);

$caption	= EC_LAN_79; // "Calendar View";
$nav		= $cal_text . $nav_text . $text;
$ns->tablerender($caption, $nav);

require_once(FOOTERF);

?>