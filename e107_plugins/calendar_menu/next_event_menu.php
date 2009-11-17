<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/next_event_menu.php,v $
 * $Revision: 1.6 $
 * $Date: 2009-11-17 12:53:08 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

global $ecal_dir, $tp;
$ecal_dir	= e_PLUGIN.'calendar_menu/';

global $e107, $ecal_class, $calendar_shortcodes;
require_once($ecal_dir.'ecal_class.php');
if (!is_object($ecal_class)) $ecal_class = new ecal_class;

$cache_tag = 'nq_event_cal_next';

// See if the page is already in the cache
if($cacheData = $e107->ecache->retrieve($cache_tag, $ecal_class->max_cache_time))
{
	echo $cacheData;
	return;
}

include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'.php');

// Values defined through admin pages
$menu_title = varset($pref['eventpost_menuheading'],EC_LAN_140);
$days_ahead = varset($pref['eventpost_daysforward'],30);			// Number of days ahead to go
$show_count = varset($pref['eventpost_numevents'],3);				// Number of events to show
$show_recurring = varset($pref['eventpost_checkrecur'],1);			// Zero to exclude recurring events
$link_in_heading = varset($pref['eventpost_linkheader'],0);			// Zero for simple heading, 1 to have clickable link


require($ecal_dir.'calendar_shortcodes.php');
if (is_readable(THEME.'calendar_template.php')) 
{  // Has to be require
	require(THEME.'calendar_template.php');
}
else 
{
	require(e_PLUGIN.'calendar_menu/calendar_template.php');
}

$start_time = $ecal_class->cal_timedate;
$end_time = $start_time + (86400 * $days_ahead) - 1;


$cal_totev = 0;
$cal_text = '';
$cal_row = array();
global $cal_row, $cal_totev;


$ev_list = $ecal_class->get_n_events($show_count, $start_time, $end_time, varset($pref['eventpost_fe_set'],FALSE), $show_recurring, 
						'event_id,event_start, event_thread, event_title, event_recurring, event_allday, event_category', 'event_cat_icon');

$cal_totev = count($ev_list);
if ($cal_totev > 0)
{
  foreach ($ev_list as $cal_row)
  {
    $cal_totev --;    // Can use this to modify inter-event gap
    $cal_text .= $tp->parseTemplate($EVENT_CAL_FE_LINE,TRUE,$calendar_shortcodes);
  }
}
else
{
  if ($pref['eventpost_fe_hideifnone']) return '';
  $cal_text.= EC_LAN_141;
}

$calendar_title = $tp->toHTML($menu_title,FALSE,'TITLE');		// Allows multi-language title, shortcodes
if ($link_in_heading == 1)
{
	$calendar_title = "<a class='forumlink' href='".e_PLUGIN_ABS."calendar_menu/event.php' >".$calendar_title."</a>";
}

// Now handle the data, cache as well
ob_start();					// Set up a new output buffer
$ns->tablerender($calendar_title, $cal_text, 'next_event_menu');
$cache_data = ob_get_flush();			// Get the page content, and display it
$e107->ecache->set($cache_tag, $cache_data);	// Save to cache

unset($ev_list);	

?>