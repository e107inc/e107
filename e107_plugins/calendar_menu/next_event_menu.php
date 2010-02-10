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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/next_event_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/**
 *	e107 Event calendar plugin
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }
$e107 = e107::getInstance();

if (!$e107->isInstalled('calendar_menu')) return '';


if (!isset($scal_class) || !is_object($ecal_class)) 
{
	require_once(e_PLUGIN.'calendar_menu/ecal_class.php');
	$ecal_class = new ecal_class;
}

// See if the page is already in the cache
$cache_tag = 'nq_event_cal_next';
if($cacheData = $e107->ecache->retrieve($cache_tag, $ecal_class->max_cache_time))
{
	echo $cacheData;
	return;
}


include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'.php');

e107::getScParser();
require_once(e_PLUGIN.'calendar_menu/calendar_shortcodes.php');
if (is_readable(THEME.'calendar_template.php')) 
{  // Has to be require
	require(THEME.'calendar_template.php');
}
else 
{
	require(e_PLUGIN.'calendar_menu/calendar_template.php');
}

global $pref;

// Values defined through admin pages
$menu_title = varset($pref['eventpost_menuheading'],EC_LAN_140);
$days_ahead = varset($pref['eventpost_daysforward'],30);			// Number of days ahead to go
$show_count = varset($pref['eventpost_numevents'],3);				// Number of events to show
$show_recurring = varset($pref['eventpost_checkrecur'],1);			// Zero to exclude recurring events
$link_in_heading = varset($pref['eventpost_linkheader'],0);			// Zero for simple heading, 1 to have clickable link


$start_time = $ecal_class->cal_timedate;
$end_time = $start_time + (86400 * $days_ahead) - 1;


$cal_text = '';

setScVar('event_calendar_shortcodes', 'ecalClass', &$ecal_class);			// Give shortcodes a pointer to calendar class
//callScFunc('event_calendar_shortcodes','setCalDate', $dateArray);			// Tell shortcodes the date to display
//setScVar('event_calendar_shortcodes', 'catFilter', $cat_filter);			// Category filter

$ev_list = $ecal_class->get_n_events($show_count, $start_time, $end_time, varset($pref['eventpost_fe_set'],FALSE), $show_recurring, 
						'event_id,event_start, event_thread, event_title, event_recurring, event_allday, event_category', 'event_cat_icon');

$cal_totev = count($ev_list);
if ($cal_totev > 0)
{
	foreach ($ev_list as $thisEvent)
	{
		$cal_totev --;    // Can use this to modify inter-event gap
		setScVar('event_calendar_shortcodes', 'numEvents', $cal_totev);				// Number of events to display
		setScVar('event_calendar_shortcodes', 'event', $thisEvent);					// Give shortcodes the event data
		$cal_text .= $e107->tp->parseTemplate($EVENT_CAL_FE_LINE,TRUE);
	}
}
else
{
	if ($pref['eventpost_fe_hideifnone']) return '';
	$cal_text.= EC_LAN_141;
}

$calendar_title = $e107->tp->toHTML($menu_title,FALSE,'TITLE');		// Allows multi-language title, shortcodes
if ($link_in_heading == 1)
{
	$calendar_title = "<a class='forumlink' href='".e_PLUGIN_ABS."calendar_menu/event.php' >".$calendar_title."</a>";
}

// Now handle the data, cache as well
ob_start();					// Set up a new output buffer
$e107->ns->tablerender($calendar_title, $cal_text, 'next_event_menu');
$cache_data = ob_get_flush();			// Get the page content, and display it
$e107->ecache->set($cache_tag, $cache_data);	// Save to cache

unset($ev_list);	

?>