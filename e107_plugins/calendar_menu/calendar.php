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
|
| 11.11.06 - steved - mods for next CVS release
|
+----------------------------------------------------------------------------+
*/ 


require_once("../../class2.php");
require_once(e_PLUGIN."calendar_menu/calendar_shortcodes.php");
if (isset($_POST['viewallevents']))
{
    header("Location: " . e_PLUGIN . "calendar_menu/event.php?" . $_POST['enter_new_val']);
	exit;
} 
if (isset($_POST['doit']))
{
    header("Location: " . e_PLUGIN . "calendar_menu/event.php?ne." . $_POST['enter_new_val']);
	exit;
}
if (isset($_POST['subs']))
{
    header("Location: " . e_PLUGIN . "calendar_menu/subscribe.php");
	exit;
} 

include_lan(e_PLUGIN."calendar_menu/languages/".e_LANGUAGE.".php");
define("PAGE_NAME", EC_LAN_121);

require_once('ecal_class.php');
$ecal_class = new ecal_class;

if (is_readable(THEME."calendar_template.php")) 
{
  require(THEME."calendar_template.php");
}
else 
{
  require(e_PLUGIN."calendar_menu/calendar_template.php");
}

$num = (isset($_POST['num']) && $_POST['num'] ? $_POST['num'] : "");


$category_filter = "";
if  ((isset($_POST['event_cat_ids']) && $_POST['event_cat_ids'] != "all"))
{
  $category_filter = " AND (e.event_category = '".intval($_POST['event_cat_ids'])."') ";
}


require_once(HEADERF);

// get current date information ---------------------------------------------------------------------
$qs = explode(".", e_QUERY);
if($qs[0] == "")
{	// Show current month
  $datearray	= $ecal_class->cal_date;
} 
else
{	// Get date from query
  $datearray	= getdate($qs[0]);
}
  $month	= $datearray['mon'];
  $year		= $datearray['year'];


// set up arrays for calender display ------------------------------------------------------------------
if($pref['eventpost_weekstart'] == 'sun') {
	$week	= Array(EC_LAN_25, EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24);
	} else {
	$week	= Array(EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24, EC_LAN_25);
}	
$months		= Array(EC_LAN_0, EC_LAN_1, EC_LAN_2, EC_LAN_3, EC_LAN_4, EC_LAN_5, EC_LAN_6, EC_LAN_7, EC_LAN_8, EC_LAN_9, EC_LAN_10, EC_LAN_11);
$monthabb	= Array(EC_LAN_JAN, EC_LAN_FEB, EC_LAN_MAR, EC_LAN_APR, EC_LAN_MAY, EC_LAN_JUN, EC_LAN_JUL, EC_LAN_AUG, EC_LAN_SEP, EC_LAN_OCT, EC_LAN_NOV, EC_LAN_DEC);

$days = array(EC_LAN_DAY_1, EC_LAN_DAY_2, EC_LAN_DAY_3, EC_LAN_DAY_4, EC_LAN_DAY_5, EC_LAN_DAY_6, EC_LAN_DAY_7, EC_LAN_DAY_8, EC_LAN_DAY_9, EC_LAN_DAY_10, EC_LAN_DAY_11, EC_LAN_DAY_12, EC_LAN_DAY_13, EC_LAN_DAY_14, EC_LAN_DAY_15, EC_LAN_DAY_16, EC_LAN_DAY_17, EC_LAN_DAY_18, EC_LAN_DAY_19, EC_LAN_DAY_20, EC_LAN_DAY_21, EC_LAN_DAY_22, EC_LAN_DAY_23, EC_LAN_DAY_24, EC_LAN_DAY_25, EC_LAN_DAY_26, EC_LAN_DAY_27, EC_LAN_DAY_28, EC_LAN_DAY_29, EC_LAN_DAY_30, EC_LAN_DAY_31);

// show events-------------------------------------------------------------------------------------------
$monthstart		= mktime(0, 0, 0, $month, 1, $year);		// Start of month to be shown
$firstdayarray	= getdate($monthstart);
$monthend		= mktime(0, 0, 0, $month + 1, 1, $year) - 1;	// End of month to be shown

$prevmonth		= ($month-1);
$prevyear		= $year;
if ($prevmonth == 0)
{
    $prevmonth	= 12;
    $prevyear	= ($year-1);
} 
$previous = mktime(0, 0, 0, $prevmonth, 1, $prevyear);		// Used by nav

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

$prop		= mktime(0, 0, 0, $month, 1, $year);		// Sets start date for new event entry
$nowmonth	= $ecal_class->cal_date['mon'];
$nowyear	= $ecal_class->cal_date['year'];
$nowday		= $ecal_class->cal_date['mday'];

$events = array();

// time switch buttons
$cal_text = $tp -> parseTemplate($CALENDAR_TIME_TABLE, FALSE, $calendar_shortcodes);

// navigation buttons
$nav_text = $tp -> parseTemplate($CALENDAR_NAVIGATION_TABLE, FALSE, $calendar_shortcodes);

// get events from selected
    $qry = "SELECT e.*, ec.*
			FROM #event as e
			LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
			WHERE e.event_id != '' 
			AND ((e.event_start >= ".intval($monthstart)." AND e.event_start <= ".intval($monthend).") 
			OR   (e.event_end >= ".intval($monthstart)." AND e.event_end <= ".intval($monthend).")
			OR	 (e.event_start <= ".intval($monthstart)." AND e.event_end >= ".intval($monthend).")
			OR 	 (e.event_recurring = '1' AND e.event_rec_y = ".intval($month).")) {$category_filter}
			{$ecal_class->extra_query}
			ORDER BY e.event_start";

if ($sql->db_Select_gen($qry))
{
    while ($row = $sql->db_Fetch())
	{
		// check for recurring events in this month
		if($row['event_recurring']=='1')
		{
		  if ($month == $row['event_rec_y'])
		  {	// Change it into an event that happens today
			$row['event_start'] = mktime(0,0,0,$row['event_rec_y'],$row['event_rec_m'],$year);
			$row['event_end'] = $row['event_start'];    
		  }
		  else
		  {  // Effectively create a null event
		    $row['event_start'] = 0;
			$row['event_end'] = 0;
		  }
		}

		$evf	= getdate($row['event_start']);
		$tmp	= $evf['mday'];		// Day of month for start
		$eve	= getdate($row['event_end']);
		$tmp2	= $eve['mday'];		// Day of month for end
		$tmp3	= date("t", $monthstart); // number of days in this month
		if ((($ecal_class->max_recent_show != 0) && (time() - $row['event_datestamp']) <= $ecal_class->max_recent_show)) $row['is_recent'] = TRUE; 

		//1) start in month, end in month
		if(($row['event_start']>=$monthstart && $row['event_start']<=$monthend) && $row['event_end']<=$monthend)
		{
			$events[$tmp][] = $row;
			for ($c=($tmp+1); $c<($tmp2+1); $c++) 
			{
				$row['event_true_end'][$c] = ($c!=$tmp2 ? 1 : 2);
				$events[$c][] = $row;
			}

		//2) start in month, end after month
		}
		elseif(($row['event_start']>=$monthstart && $row['event_start']<=$monthend) && $row['event_end']>=$monthend)
		{
			$events[$tmp][] = $row;
			for ($c=($tmp+1); $c<=$tmp3; $c++)
			{
				$row['event_true_end'][$c] = 1;
				$events[$c][] = $row;
			}

		//3) start before month, end in month
		}
		elseif($row['event_start']<=$monthstart && ($row['event_end']>=$monthstart && $row['event_end']<=$monthend))
		{
			for ($c=1; $c<=$tmp2; $c++)
			{
				$row['event_true_end'][$c] = ($c!=$tmp2 ? 1 : 2);
				$events[$c][] = $row;
			}

		//4) start before month, end after month
		}
		elseif($row['event_start']<=$monthstart && $row['event_end']>=$monthend)
		{
			for ($c=1; $c<=$tmp3; $c++){
				$row['event_true_end'][$c] = 1;
				$events[$c][] = $row;
			}
		}
	}
}


// ****** CAUTION - the category dropdown also used $sql object - take care to avoid interference!

$start		= $monthstart;
$numberdays	= date("t", $start); // number of days in this month

$text = "";
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

if ($pref['eventpost_weekstart'] == 'mon') 
{
  $firstdayoffset = ($firstdayarray['wday'] == 0 ? $firstdayarray['wday']+6 : $firstdayarray['wday']-1);
}
else 
{
  $firstdayoffset = $firstdayarray['wday'] ;
}
for ($c=0; $c<$firstdayoffset; $c++) 
{
  $text .= $tp -> parseTemplate($CALENDAR_CALENDAR_DAY_NON, FALSE, $calendar_shortcodes);
}
$loop = $firstdayoffset;

for ($c = 1; $c <= $numberdays; $c++)
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
		}
		elseif(isset($events[$c]) && is_array($events[$c]) && !empty($events[$c]) && count($events[$c]) > 0)
		{
			//day has events
			$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_DAY_EVENT, FALSE, $calendar_shortcodes);
        } 
        else
        {
            // no events and not today
			$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_DAY_EMPTY, FALSE, $calendar_shortcodes);
        } 
        // if there are events then list them
        if (array_key_exists($c, $events))
		{
            foreach($events[$c] as $ev)
            {
				//if ($event_true_end[$c][$a]){
				if(isset($ev['event_true_end']) && $ev['event_true_end'])
				{
					//$ev['indicat'] = ($ev['event_true_end']==1 ? "->" : "|");
					$ev['indicat'] = "";
					$ev['imagesize'] = "4";
					$ev['fulltopic'] = FALSE;
					$ev['startofevent'] = FALSE;
				}
				else
				{
					$ev['indicat'] = "";
					$ev['imagesize'] = "8";
					$ev['fulltopic'] = TRUE;
					$ev['startofevent'] = TRUE;
				}
				$text .= $tp -> parseTemplate($CALENDAR_SHOWEVENT, FALSE, $calendar_shortcodes);
			} 
        } 
		$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_DAY_END, FALSE, $calendar_shortcodes);
    } 
    $loop++;
    if ($loop == 7)
    {
        $loop = 0;
		if($c != $numberdays)
		{
			$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_WEEKSWITCH, FALSE, $calendar_shortcodes);
		}
    } 
}
//remainder cells to end the row properly with empty cells
if($loop!=0)
{
	$remainder = 7-$loop;
	for ($c=0; $c<$remainder; $c++) 
	{
		$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_DAY_NON, FALSE, $calendar_shortcodes);
	}
}
$text .= $tp -> parseTemplate($CALENDAR_CALENDAR_END, FALSE, $calendar_shortcodes);

$caption	= EC_LAN_79; // "Calendar View";
$nav		= $cal_text . $nav_text . $text;
$ns->tablerender($caption, $nav);

require_once(FOOTERF);

?>