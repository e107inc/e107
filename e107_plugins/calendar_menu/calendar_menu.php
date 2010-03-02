<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvsroot/e107/e107_0.7/e107_plugins/calendar_menu/calendar_menu.php,v $
|     $Revision$
|     $Date$
|     $Author$
|
| 22.10.06 steved - Various tidying up, additional options supported
| 24.10.06 steved - templated, various cleaning up
| 06.11.06 steved - template file integrated with other calendar files
| 09.11.06 steved - Caching added, other mods to templates etc
+----------------------------------------------------------------------------+
*/


if (!defined('e107_INIT')) { exit; }

$ecal_dir	= e_PLUGIN . "calendar_menu/";
require_once($ecal_dir.'ecal_class.php');
$ecal_class = new ecal_class;

$cache_tag = "nq_event_cal_cal";


// See if the page is already in the cache
	if($cacheData = $e107cache->retrieve($cache_tag, $ecal_class->max_cache_time))
	{
		echo $cacheData;
		return;
	}

include_lan($ecal_dir."languages/".e_LANGUAGE.".php");

global $ecal_dir, $tp;


if (is_readable(THEME."calendar_template.php"))
{
  require(THEME."calendar_template.php");
}
else
{  // Needs to be require - otherwise not loaded if two menus use it
  require($ecal_dir."calendar_template.php");
}

$cal_datearray		= $ecal_class->cal_date;
$cal_current_month	= $cal_datearray['mon'];
$cal_current_year	= $cal_datearray['year'];
$numberdays	= date("t", $ecal_class->cal_timedate); // number of days in this month


$cal_monthstart		= mktime(0, 0, 0, $cal_current_month, 1, $cal_current_year);			// Time stamp for first day of month
$cal_firstdayarray	= getdate($cal_monthstart);
$cal_monthend		= mktime(0, 0, 0, $cal_current_month + 1, 1, $cal_current_year) -1;		// Time stamp for last day of month


    $cal_qry = "SELECT e.event_rec_m, e.event_rec_y, e.event_start, e.event_end, e.event_datestamp, ec.*
	FROM #event as e LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
	WHERE ((e.event_start >= {$cal_monthstart} AND e.event_start <= {$cal_monthend}) OR (e.event_rec_y = {$cal_current_month}))
	{$ecal_class->extra_query} order by e.event_start";


$cal_events = array();
$cal_totev = 0;
if ($cal_totev = $sql->db_Select_gen($cal_qry))
{
	while ($cal_row = $sql->db_Fetch())
	{
		if ($cal_row['event_rec_y'] == $cal_current_month)
		{	// Recurring events
			$cal_start_day = $cal_row['event_rec_m'];
		}
		else
		{	// 'normal' events
			$cal_tmp = getdate($cal_row['event_start']);
			if ($cal_tmp['mon'] == $cal_current_month)
			{
				$cal_start_day = $cal_tmp['mday'];
			}
			else
			{
				$cal_start_day = 1;
			}
		}
		// Mark start day of each event
		$cal_events[$cal_start_day][] = $cal_row['event_cat_icon'];	// Will be overwritten if several events on same day
		if ((($ecal_class->max_recent_show != 0) && (time() - $cal_row['event_datestamp']) <= $ecal_class->max_recent_show)) $cal_events[$cal_start_day]['is_recent'] = TRUE;
	}
}


if ($pref['eventpost_weekstart'] == 'sun')
{
	$cal_week	= array(EC_LAN_25, EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24);
}
else
{
	$cal_week	= array(EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24, EC_LAN_25);
}

$cal_months	= array(EC_LAN_0, EC_LAN_1, EC_LAN_2, EC_LAN_3, EC_LAN_4, EC_LAN_5, EC_LAN_6, EC_LAN_7, EC_LAN_8, EC_LAN_9, EC_LAN_10, EC_LAN_11);


$calendar_title = "<a class='forumlink' href='".e_PLUGIN."calendar_menu/";
if ($pref['eventpost_menulink'] == 1)
{
	$calendar_title .= "calendar.php' >";
}
else
{
	$calendar_title .= "event.php' >";
}
if ($pref['eventpost_dateformat'] == 'my')
{
    $calendar_title .= $cal_months[$cal_datearray['mon']-1] ." ". $cal_current_year . "</a>";
}
else
{
    $calendar_title .= $cal_current_year ." ". $cal_months[$cal_datearray['mon']-1] . "</a>";
}


$cal_text = $CALENDAR_MENU_START;


if ($pref['eventpost_showeventcount']=='1')
{
  if ($cal_totev)
  {
    $cal_text .= EC_LAN_26 . ": " . $cal_totev;
  }
  else
  {
    $cal_text .= EC_LAN_27;
  }
  $cal_text .= "<br /><br />";
}

$cal_start	= $cal_monthstart;		// First day of month as time stamp


// Start the table
$cal_text .= $CALENDAR_MENU_TABLE_START;
// Open header row
$cal_text .= $CALENDAR_MENU_HEADER_START;
// Now do the headings

foreach($cal_week as $cal_day)
{
    $cal_text .= $CALENDAR_MENU_HEADER_FRONT;
	$cal_text .= $tp->text_truncate($cal_day, $pref['eventpost_lenday'], '');
    $cal_text .= $CALENDAR_MENU_HEADER_BACK;
}
$cal_text .= $CALENDAR_MENU_HEADER_END;  // Close off header row, open first date row


$cal_thismonth	= $cal_datearray['mon'];
$cal_thisday	= $cal_datearray['mday'];	// Today


if ($pref['eventpost_weekstart'] == 'mon')
{
    $firstdayoffset = ($cal_firstdayarray['wday'] == 0 ? $cal_firstdayarray['wday'] + 6 : $cal_firstdayarray['wday']-1);
}
else
{
    $firstdayoffset = $cal_firstdayarray['wday'];
}


for ($cal_c = 0; $cal_c < $firstdayoffset; $cal_c++)
{
	$cal_text .= $CALENDAR_MENU_DAY_NON;
}
$cal_loop = $firstdayoffset;

// Now do the days of the month
for($cal_c = 1; $cal_c <= $numberdays; $cal_c++)
{   // Four cases to decode:
	//     	1 - Today, no events
	//		2 - Some other day, no events (or no icon defined)
	//		3 - Today with events (and icon defined)
	//		4 - Some other day with events (and icon defined)
    $cal_dayarray = getdate($cal_start + (($cal_c-1) * 86400));
	$cal_css = 2;		// The default - not today, no events
    if ($cal_dayarray['mon'] == $cal_thismonth)
    {  // Dates match for this month
		$cal_img = $cal_c;		// Default 'image' is the day of the month
		$cal_event_count = 0;
		$title = "";
		if ($cal_thisday == $cal_c) $cal_css = 1;
        $cal_linkut = mktime(0 , 0 , 0 , $cal_dayarray['mon'], $cal_c, $cal_datearray['year']).".one";  // ALways need "one"
        if (array_key_exists($cal_c, $cal_events))
        {	// Events happening today
            $cal_event_icon = "calendar_menu/images/" . $cal_events[$cal_c]['0'];
            $cal_event_count = count($cal_events[$cal_c]);		// See how many events today
            if (!empty($cal_events[$cal_c]['0']) && is_readable(e_PLUGIN.$cal_event_icon))
            {   // Show icon if it exists
				$cal_css += 2;		// Gives 3 for today, 4 for other day
				if ($cal_event_count == 1)
				{
					$title = " title='1 ".EC_LAN_135."' ";
				}
				else
				{
					$title = " title='{$cal_event_count} " . EC_LAN_106 . "' ";
				}
				$cal_img = "<img style='border:0' src='".e_PLUGIN_ABS.$cal_event_icon."' alt='' />";
				//height='10' width='10'
				if (isset($cal_events[$cal_c]['is_recent']) && $cal_events[$cal_c]['is_recent'])
				{
					$cal_css += 2;
				}
            }
		}
        $cal_text .= $CALENDAR_MENU_DAY_START[$cal_css]."<a {$title} href='" . e_PLUGIN_ABS."calendar_menu/event.php?{$cal_linkut}'>{$cal_img}</a>";
        $cal_text .= $CALENDAR_MENU_DAY_END[$cal_css];
        $cal_loop++;
        if ($cal_loop == 7)
        {  // Start next row
            $cal_loop = 0;
			if ($cal_c != $numberdays)
			{
              $cal_text .= $CALENDAR_MENU_WEEKSWITCH;
			}
        }
    }
}

if ($cal_loop != 0)
{
for($cal_a = ($cal_loop + 1); $cal_a <= 7; $cal_a++)
{
	$cal_text .= $CALENDAR_MENU_DAY_NON;
}
}
// Close table
$cal_text .= $CALENDAR_MENU_END;

// Now handle the data, cache as well
ob_start();					// Set up a new output buffer
$ns->tablerender($calendar_title, $cal_text, 'calendar_menu');
$cache_data = ob_get_flush();			// Get the page content, and display it
$e107cache->set($cache_tag, $cache_data);	// Save to cache


?>
