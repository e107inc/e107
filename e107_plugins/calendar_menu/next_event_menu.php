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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/next_event_menu.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-09-02 21:41:18 $
|     $Author: e107coders $
|
| 12.07.06 - Initial release for beta testing
| 26.07.06 - First release
| 02.08.06 - Optional display of category icon added
| 03.08.06 - Height/width of category icons removed
|
| Note: Recurring events nominally supported, but not tested. Query looks OK.
| The 'days ahead' value must be less than 59 for this to work reliably
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

// Values defined through admin pages
$menu_title = $pref['eventpost_menuheading'];
$days_ahead = $pref['eventpost_daysforward'];
$show_count = $pref['eventpost_numevents'];
$show_recurring = $pref['eventpost_checkrecur'];
$show_cat_icon = $pref['eventpost_showcaticon'];
$link_in_heading = $pref['eventpost_linkheader'];

// Now set defaults for anything not defined
if (!$menu_title) $menu_title = EC_LAN_140;
if (!$days_ahead) $days_ahead = 30;		// Number of days ahead to go
if (!$show_count) $show_count = 3;		// Number of events to show
if (!$show_recurring) $show_recurring = 1;	// Zero to exclude recurring events
if (!$show_cat_icon) $show_cat_icon = 0;		// Zero to turn off
if (!$link_in_heading) $link_in_heading = 0;	// Zero for simple heading, 1 to have clickable link


$ec_dir		= e_PLUGIN . "calendar_menu/";
// We don't actually need language file
//$lan_file	= $ec_dir . "languages/" . e_LANGUAGE . ".php";
//require_once((file_exists($lan_file) ? $lan_file : $ec_dir. "languages/English.php"));

$time_now = time();
$site_time = $time_now + ($pref['time_offset'] * 3600);			// Check sign of offset
$end_time = $site_time + (86400 * $days_ahead);

if (USER)
{
    $cal_class = e_UC_PUBLIC.", ".e_UC_MEMBER.", " . USERCLASS;
} 
else
{
    $cal_class = e_UC_PUBLIC.", ".e_UC_GUEST;
} 

// Build up query bit by bit

    $cal_qry = "SELECT e.event_id, e.event_rec_m, e.event_rec_y, e.event_start, e.event_title, e.event_recurring, e.event_allday, ec.*
	FROM #event as e LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
	WHERE (((e.event_start >= {$site_time} AND e.event_start < {$end_time}))";

if ($show_recurring > 0)
{  // This won't work properly under some circumstances if $days_ahead is greater than the number of days in the current month plus next month.
   // If that matters, need another test on event_rec_y (which is actually the month) - plus the calculation to generate the values
	$cal_datearray		= getdate($site_time);
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

$cal_qry .= ')';

if (!check_class($pref['eventpost_super']))
{
	$cal_qry .= " AND find_in_set(ec.event_cat_class,'" . $cal_class . "')";
}

if (isset($pref['eventpost_fe_set']))
{
   $cal_qry .= " AND find_in_set(ec.event_cat_id,'".$pref['eventpost_fe_set']."')";
}
	
	$cal_qry .= " order by e.event_start LIMIT {$show_count}";


$cal_totev = 0;
$cal_text = '';

$cal_totev = $sql->db_Select_gen($cal_qry);

if ($cal_totev > 0)
{
    while ($cal_row = $sql->db_Fetch())
    {
	  if ($show_cat_icon == 1)
	  {
	    if($cal_row['event_cat_icon'] && file_exists($ec_dir."images/".$cal_row['event_cat_icon']))
		{
		  $cal_text .= "<img style='border:0' src='".$ec_dir."images/".$cal_row['event_cat_icon']."' alt='' />";
		}
		else
		{
		  $cal_text .= "<img src='".THEME."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='' style='border:0; vertical-align:middle;' />";
		}
	  }
	  if ($cal_row['event_allday'] == 1)
	    $cal_text .= strftime("%d %B",$cal_row['event_start']);
	  else
	    $cal_text .= strftime("%d %B, %H%M",$cal_row['event_start']);
	  $cal_text .= "<br />
<a href=".e_PLUGIN."calendar_menu/event.php?".$cal_row['event_start'].".event.".$cal_row['event_id'].">
<strong>".$cal_row['event_title']."</strong></a><br />
";	
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

$ns->tablerender($calendar_title, $cal_text, 'next_event_menu');

?>