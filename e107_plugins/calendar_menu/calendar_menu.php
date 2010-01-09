<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forthcoming events menu handler for event calendar
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/calendar_menu.php,v $
 * $Revision: 1.11 $
 * $Date: 2010-01-09 12:44:11 $
 * $Author: e107steved $
 */
 
/**
 *	e107 Event calendar plugin
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id: calendar_menu.php,v 1.11 2010-01-09 12:44:11 e107steved Exp $;
 */

if (!defined('e107_INIT')) { exit; }
$e107 = e107::getInstance();
if (!$e107->isInstalled('calendar_menu')) return '';

if (!isset($ecal_class)) 
{
	require_once(e_PLUGIN.'calendar_menu/ecal_class.php');
	$ecal_class = new ecal_class;
}
// See if the page is already in the cache
$cache_tag = 'nq_event_cal_cal';
if($cacheData = $e107->ecache->retrieve($cache_tag, $ecal_class->max_cache_time))
{
	echo $cacheData;
	return;
}
global $pref;
include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'.php');

// Doesn't use shortcodes - rather a specific format for that
//e107::getScParser();
//require_once(e_PLUGIN.'calendar_menu/calendar_shortcodes.php');
if (is_readable(THEME.'calendar_template.php')) 
{  // Has to be require
	require(THEME.'calendar_template.php');
}
else 
{
	require(e_PLUGIN.'calendar_menu/calendar_template.php');
}

$show_recurring = TRUE;		// Could be pref later
$cat_filter = '*';			// Could be another pref later.
$cal_datearray		= $ecal_class->cal_date;
$cal_current_month	= $cal_datearray['mon'];
$cal_current_year	= $cal_datearray['year'];
$numberdays	= date("t", $ecal_class->cal_timedate); // number of days in this month
$cal_monthstart		= gmmktime(0, 0, 0, $cal_current_month, 1, $cal_current_year);			// Time stamp for first day of month
$cal_firstdayarray	= getdate($cal_monthstart);												
$cal_monthend		= gmmktime(0, 0, 0, $cal_current_month + 1, 1, $cal_current_year) -1;		// Time stamp for last day of month
//$cal_thismonth	= $cal_datearray['mon'];
$cal_thisday	= $cal_datearray['mday'];	// Today
$cal_events = array();
$cal_titles = array();
$cal_recent = array();
$cal_totev = 0;

$ev_list = $ecal_class->get_events($cal_monthstart, $cal_monthend, TRUE, $cat_filter, $show_recurring, 
						'event_start, event_thread, event_title, event_recurring, event_allday', 'event_cat_icon');
$cal_totev = count($ev_list);

// Generate an array, one element per day of the month, and assign events to them
foreach ($ev_list as $cal_row)
{
    if (is_array($cal_row['event_start'])) $temp = $cal_row['event_start']; else $temp = array($cal_row['event_start']);
	foreach ($temp as $ts)
	{	// Split up any recurring events
		$cal_start_day = date('j',$ts);		// Day of month for start
		// Mark start day of each event
		$cal_events[$cal_start_day][] = $cal_row['event_cat_icon'];	// Only first is actually used
		if (isset($cal_row['is_recent']))  $cal_recent[$cal_start_day] = TRUE;
		$cal_titles[$cal_start_day][] = $cal_row['event_title'];	// In case titles displayed on mouseover
	}
}

// set up month array for calendar display
$cal_months	= array(EC_LAN_0, EC_LAN_1, EC_LAN_2, EC_LAN_3, EC_LAN_4, EC_LAN_5, EC_LAN_6, EC_LAN_7, EC_LAN_8, EC_LAN_9, EC_LAN_10, EC_LAN_11);
if ($pref['eventpost_dateformat'] == 'my')
{
	$calendar_title = $cal_months[$cal_current_month-1] .' '. $cal_current_year;
}
else
{
	$calendar_title = $cal_current_year .' '. $cal_months[$cal_current_month-1];
}
switch ($pref['eventpost_menulink']) 
{
  case 0 :  $calendar_title = "<a {$CALENDAR_MENU_HDG_LINK_CLASS} href='".e_PLUGIN."calendar_menu/event.php' >".$calendar_title."</a>";
			break;
  case 1 :  $calendar_title = "<a {$CALENDAR_MENU_HDG_LINK_CLASS} href='".e_PLUGIN."calendar_menu/calendar.php' >".$calendar_title."</a>";
			break;
  default : ;
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
// Now do the headings (days of week)
for ($i = 0; $i < 7; $i++)
{
	$cal_day = $ecal_class->day_offset_string($i);
	$cal_text .= $CALENDAR_MENU_HEADER_FRONT;
	$cal_text .= $e107->tp->text_truncate($cal_day, 1, '');		// Unlikely to have room for more than 1 letter
	$cal_text .= $CALENDAR_MENU_HEADER_BACK;
}
$cal_text .= $CALENDAR_MENU_HEADER_END;  // Close off header row, open first date row
// Calculate number of days to skip before 'real' days on first line of calendar
$firstdayoffset = date('w',$cal_start) - $ecal_class->ec_first_day_of_week;
if ($firstdayoffset < 0) $firstdayoffset+= 7;
for ($cal_c = 0; $cal_c < $firstdayoffset; $cal_c++)
{
	$cal_text .= $CALENDAR_MENU_DAY_NON;
}
$cal_loop = $firstdayoffset;
// Now do the days of the month
for($cal_c = 1; $cal_c <= $numberdays; $cal_c++)
{   // Four cases to decode:
	//     	1 - Today, no events
	//		2 - Some other day, no events
	//		3 - Today with events
	//		4 - Some other day with events
	//		5 - Today with recently added events
	//		6 - Some other day with recently added events
//    $cal_dayarray = getdate($cal_start + (($cal_c-1) * 86400));
	$cal_css = 2;		// The default - not today, no events
    $cal_img = $cal_c;		// Default 'image' is the day of the month
    $cal_event_count = 0;
    $title = "";
    if ($cal_thisday == $cal_c) $cal_css = 1;
    $cal_linkut = gmmktime(0 , 0 , 0 , $cal_current_month, $cal_c, $cal_current_year).".one";  // Always need "one"
    if (array_key_exists($cal_c, $cal_events))
    {	// There are events today
		$cal_event_icon = 'calendar_menu/images/'.$cal_events[$cal_c]['0'];		// Icon file could be NULL
		$cal_event_count = count($cal_events[$cal_c]);		// See how many events today
		if ($cal_event_count)
		{   // Show icon if it exists
			$cal_css += 2;		// Gives 3 for today, 4 for other day
			if (isset($pref['eventpost_showmouseover']) && ($pref['eventpost_showmouseover'] == 1))
			{
				$cal_ins = " title='";
				foreach ($cal_titles[$cal_c] as $cur_title)
				{  // New line would be better, but doesn't get displayed
					$title .= $cal_ins.$cur_title;
					$cal_ins = ", ";
				}
				$title .= "'";
			}
			else
			{
				if ($cal_event_count == 1)
				{
					$title = " title='1 ".EC_LAN_135."' ";
				}
				else
				{
					$title = " title='{$cal_event_count} " . EC_LAN_106 . "' ";
				}
			}
			if (is_file(e_PLUGIN.$cal_event_icon)) $cal_img = "<img src='".e_PLUGIN_ABS.$cal_event_icon."' alt='' />";
					//height='10' width='10'
			if (isset($cal_recent[$cal_c]) && $cal_recent[$cal_c])
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
$e107->ecache->set($cache_tag, $cache_data);	// Save to cache
unset($ev_list);
unset($cal_text);
?>