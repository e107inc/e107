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
| 09.11.06 - Cache support added, templating/shortcode tweaks
+----------------------------------------------------------------------------+
*/


if (!defined('e107_INIT')) { exit; }

global $ecal_dir, $tp;
$ecal_dir	= e_PLUGIN . "calendar_menu/";

global $ecal_class;
require_once($ecal_dir."ecal_class.php");
$ecal_class = new ecal_class;

	$cache_tag = "nq_event_cal_next";

// See if the page is already in the cache
	if($cacheData = $e107cache->retrieve($cache_tag, $ecal_class->max_cache_time))
	{
		echo $cacheData;
		return;
	}

include_lan(e_PLUGIN."calendar_menu/languages/".e_LANGUAGE.".php");

// Values defined through admin pages
$menu_title = $pref['eventpost_menuheading'];
$days_ahead = $pref['eventpost_daysforward'];
$show_count = $pref['eventpost_numevents'];
$show_recurring = $pref['eventpost_checkrecur'];
$link_in_heading = $pref['eventpost_linkheader'];

// Now set defaults for anything not defined
if (!$menu_title) $menu_title = EC_LAN_140;
if (!$days_ahead) $days_ahead = 30;		// Number of days ahead to go
if (!$show_count) $show_count = 3;		// Number of events to show
if (!$show_recurring) $show_recurring = 1;	// Zero to exclude recurring events
if (!$link_in_heading) $link_in_heading = 0;	// Zero for simple heading, 1 to have clickable link


require($ecal_dir."calendar_shortcodes.php");
if (is_readable(THEME."calendar_template.php")) 
{  // Needs to be require in case second
  require(THEME."calendar_template.php");
}
else 
{
  require($ecal_dir."calendar_template.php");
}

$site_time = $ecal_class->cal_timedate;
$end_time = $site_time + (86400 * $days_ahead);


// Build up query bit by bit
    $cal_qry = "SELECT e.event_id, e.event_rec_m, e.event_rec_y, e.event_start, e.event_thread, e.event_title, e.event_recurring, e.event_allday, ec.*
	FROM #event as e LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
	WHERE (((e.event_start >= {$site_time} AND e.event_start < {$end_time}))";

if ($show_recurring > 0)
{  // This won't work properly under some circumstances if $days_ahead is greater than the number of days in the current month plus next month.
   // If that matters, need another test on event_rec_y (which is actually the month) - plus the calculation to generate the values
	$cal_datearray		= $ecal_class->cal_date;
	$first_day			= $cal_datearray['mday'];
	$first_month		= $cal_datearray['mon'];

	$end_date	= mktime(0,0,0,$first_month,$first_day,0) + (86400 * $days_ahead);
	$end_datearray = getdate($end_date);
	$last_month = $end_datearray['mon'];
	$last_day	= $end_datearray['mday'];
	$cal_qry .= " OR ((e.event_recurring = '1') 
				 AND ";
	if ($first_month == $last_month)
	{   // All dates within current month
	  $cal_qry .= "(((e.event_rec_y = {$first_month})    
	             AND  (e.event_rec_m >= {$first_day}) AND (e.event_rec_m < {$last_day})  ) ))";
	}
	else
	{	// Dates overlap one or more months
	$cal_qry .= "(((e.event_rec_y = {$first_month})    AND  (e.event_rec_m >= {$first_day})) 
				  OR ((e.event_rec_y  = {$last_month}) AND  (e.event_rec_m < {$last_day}))";
	$first_month++;
	if ($first_month > 12) $first_month = 1;
	if ($first_month <> $last_month)
	{  // Add a whole month in the middle
	  $cal_qry .= " OR (e.event_rec_y = {$first_month}) ";
	}
	$cal_qry .= "))";
	}
}

$cal_qry .= ')'.$ecal_class->extra_query;	   // Puts in class filter if not calendar admin

if (isset($pref['eventpost_fe_set']))
{
   $cal_qry .= " AND find_in_set(ec.event_cat_id,'".$pref['eventpost_fe_set']."')";
}
	
$cal_qry .= " order by e.event_start LIMIT {$show_count}";


$cal_totev = 0;
$cal_text = '';
$cal_row = array();
global $cal_row, $cal_totev;

$cal_totev = $sql->db_Select_gen($cal_qry);


if ($cal_totev > 0)
{
    while ($cal_row = $sql->db_Fetch())
    {
	  $cal_totev --;    // Can use this to modify inter-event gap
	  $cal_text .= $tp->parseTemplate($EVENT_CAL_FE_LINE,FALSE,$calendar_shortcodes);
	}
}
else
{
  $cal_text.= EC_LAN_141;
}

$calendar_title = $menu_title;
if ($link_in_heading == 1)
{
  $calendar_title = "<a class='forumlink' href='" . e_PLUGIN . "calendar_menu/event.php' >" . $menu_title . "</a>";
}

// Now handle the data, cache as well
ob_start();					// Set up a new output buffer
$ns->tablerender($calendar_title, $cal_text, 'next_event_menu');
$cache_data = ob_get_flush();			// Get the page content, and display it
$e107cache->set($cache_tag, $cache_data);	// Save to cache
	

?>