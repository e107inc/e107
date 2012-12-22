<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Event calendar plugin - large calendar display
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/calendar.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/**
 *	e107 Event calendar plugin
 *
 * Event calendar plugin - large calendar display
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id$;
 */

if (!defined('e_SINGLE_ENTRY'))
{
	require_once('../../class2.php');
}
$e107 = e107::getInstance();
if (!$e107->isInstalled('calendar_menu')) header('Location: '.e_BASE.'index.php');

if (isset($_POST['viewallevents']))
{
    Header('Location: '.e_PLUGIN.'calendar_menu/event.php?'.intval($_POST['enter_new_val']));
	exit;
} 
if (isset($_POST['doit']))
{
    Header('Location: '.e_PLUGIN.'calendar_menu/event.php?ne.'.intval($_POST['enter_new_val']));
	exit;
}
if (isset($_POST['subs']))
{
    Header('Location: '.e_PLUGIN.'calendar_menu/subscribe.php');
	exit;
} 
if (isset($_POST['printlists']))
{
    Header('Location: '.e_PLUGIN.'calendar_menu/ec_pf_page.php');
	exit();
} 


require_once(e_PLUGIN.'calendar_menu/calendar_shortcodes.php');
$calSc = new event_calendar_shortcodes();

include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'.php');
define('PAGE_NAME', EC_LAN_121);

require_once(e_PLUGIN.'calendar_menu/ecal_class.php');
$ecal_class = new ecal_class;

if (is_readable(THEME.'calendar_template.php')) 
{
	require(THEME.'calendar_template.php');
}
else 
{
	require(e_PLUGIN.'calendar_menu/calendar_template.php');
}


$cat_filter = intval(varset($_POST['event_cat_ids'],-1));
if ($cat_filter == -1) $cat_filter = '*';

require_once(HEADERF);


// get date within area to display
unset($dateArray);
if (e_QUERY)
{
	$qs = explode('.', e_QUERY);	// Get date from query
	$dateArray	= getdate($qs[0]);
}
if (!isset($dateArray))
{	// Show current month
	$dateArray	= $ecal_class->cal_date;
} 

// These are used in the day display loop
$month		= $dateArray['mon'];							// Number of month being shown
$year		= $dateArray['year'];							// Number of year being shown
$nowmonth	= $ecal_class->cal_date['mon'];
$nowyear	= $ecal_class->cal_date['year'];
$nowday		= $ecal_class->cal_date['mday'];


// Set date window for display
$monthstart	= mktime(0, 0, 0, $month, 1, $year);			// Start of month to be shown
$monthend	= mktime(0, 0, 0, $month + 1, 1, $year) - 1;	// End of month to be shown


$calSc->ecalClass = &$ecal_class;
$calSc->setCalDate($dateArray);
$calSc->catFilter = $cat_filter;


//-------------------------------------------------
// 		Start calculating text to display
//-------------------------------------------------

// time switch buttons
$cal_text = $e107->tp->parseTemplate($CALENDAR_TIME_TABLE, FALSE, $calSc);

// navigation buttons
$nav_text = $e107->tp->parseTemplate($CALENDAR_NAVIGATION_TABLE, FALSE, $calSc);

// We'll need virtually all of the event-related fields, so get them regardless. Just cut back on category fields
$ev_list = $ecal_class->get_events($monthstart, $monthend, FALSE, $cat_filter, TRUE, '*', 'event_cat_name,event_cat_icon');

// We create an array $events[] which has a 'primary' index of each day of the current month - 1..31 potentially
// For each day there is then a sub-array entry for each event
// Note that the new class-based retrieval adds an 'is_recent' flag to the data if changed according to the configured criteria
$events = array();
foreach ($ev_list as $row)
{
	$row['startofevent'] = TRUE;			// This sets 'large print' and so on for the first day of an event
	  
	// check for recurring events in this month (could also use is_array($row['event_start']) as a test)
	if($row['event_recurring'] != '0')
	{  // There could be several dates for the same event, if its a daily/weekly event
	    $t_start = $row['event_start'];
		foreach ($t_start as $ev_start)
		{
		// Need to save event, copy marker for date
		  $row['event_start'] = $ev_start;
		  $events[date('j',$ev_start)][] = $row;
		}
	}
	else
	{  // Its a 'normal' event
		$tmp	= date('j',$row['event_start']);		// Day of month for start
		if ($row['event_allday'])
		{
			$tmp2 = $tmp;			// Same day for start and end
		}
		else
		{
			$tmp2	= date('j',$row['event_end']-1);			// Day of month for end - knock off a second to allow for BST and suchlike
		}
		if(($row['event_start']>=$monthstart) && ($row['event_start']<=$monthend))
		{	// Start within month
		  $events[$tmp][] = $row;
		  $tmp++;
		  if ($row['event_end']>$monthend)
		  {  // End outside month
			$tmp2	= date("t", $monthstart); // number of days in this month
		  }
		}
		else
		{	// Start before month
		  $tmp = 1;
		  if ($row['event_end']>$monthend)
		  {  // End outside month
			$tmp2	= date("t", $monthstart); // number of days in this month
		  }
		}
		// Now put in markers for all 'non-start' days within current month
		$row['startofevent'] = FALSE;
		for ($c= $tmp; $c<=$tmp2; $c++) 
		{
		  $events[$c][] = $row;
		}
	}
}



// ****** CAUTION - the category dropdown also used $sql object - take care to avoid interference!

$start		= $monthstart;
$numberdays	= date('t', $start); // number of days in this month

$text = "";
$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_START, FALSE, $calSc);
$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_HEADER_START, FALSE, $calSc);

// Display the column headers
for ($i = 0; $i < 7; $i++)
{
	$calSc->headerDay = $ecal_class->day_offset_string($i);
	$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_HEADER, FALSE, $calSc);
} 
$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_HEADER_END, FALSE, $calSc);


// Calculate number of days to skip before 'real' days on first line of calendar
$firstdayoffset = date('w',$start) - $ecal_class->ec_first_day_of_week;
if ($firstdayoffset < 0) $firstdayoffset+= 7;

for ($i=0; $i<$firstdayoffset; $i++) 
{
	$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_DAY_NON, FALSE, $calSc);
}

$loop = $firstdayoffset;

for ($c = 1; $c <= $numberdays; $c++)
{	// Loop through the number of days in this month
	$calSc->todayStart = $start;			// Start of current day
	$calSc->curDay = $c;					// Current day of month

	$got_ev = array_key_exists($c, $events) && is_array($events[$c]) && count($events[$c]) > 0;		// Flag set if events on this day
  
   // Highlight the current day.
    if ($nowday == $c && $month == $nowmonth && $year == $nowyear)
    {      	//today
		$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_DAY_TODAY, FALSE, $calSc);
	}
	elseif ($got_ev)
	{	//day has events
		$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_DAY_EVENT, FALSE, $calSc);
    } 
    else
    {   // no events and not today
		$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_DAY_EMPTY, FALSE, $calSc);
    } 
	if ($got_ev)
	{
		foreach($events[$c] as $ev)
		{
			if ($ev['startofevent'])
			{
			  $ev['indicat'] = '';
			  $ev['imagesize'] = '8';
			  $ev['fulltopic'] = TRUE;
			}
			else
			{
			  $ev['indicat'] = '';
			  $ev['imagesize'] = '4';
			  $ev['fulltopic'] = FALSE;
			}
			//setScVar('event_calendar_shortcodes', 'event', $ev);			// Give shortcodes the event data
			$calSc->event = $ev;
			$text .= $e107->tp->parseTemplate($CALENDAR_SHOWEVENT, FALSE, $calSc);
		} 
	}
	$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_DAY_END, FALSE, $calSc);

	$loop++;
	if ($loop == 7)
	{
		$loop = 0;
		if($c != $numberdays)
		{
			$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_WEEKSWITCH, FALSE, $calSc);
		}
	}
	$start += 86400;
}

//remainder cells to end the row properly with empty cells
if($loop!=0)
{
	for ($c=$loop; $c<7; $c++) 
	{
		$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_DAY_NON, FALSE, $calSc);
	}
}
$text .= $e107->tp->parseTemplate($CALENDAR_CALENDAR_END, FALSE, $calSc);

$e107->ns->tablerender(EC_LAN_79, $cal_text . $nav_text . $text);

// Claim back memory from key variables
unset($ev_list);
unset($text);

require_once(FOOTERF);

?>