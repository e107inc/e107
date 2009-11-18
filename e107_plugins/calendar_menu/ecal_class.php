<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/ecal_class.php,v $
 * $Revision: 1.11 $
 * $Date: 2009-11-18 01:05:23 $
 * $Author: e107coders $
 */
 
/*
|
| Event calendar class:
|	Pre-calculates/caches many useful values
|	Implements functions used in most of the code
|
*/

/*
Preferences used:
  eventpost_caltime			1 = server, 2 = site, 3 = user
  eventpost_timedisplay		1 = 24 hour hhmm, 2 = 12 hour default, 3 = custom, 4 = 24 hour hh:mm
  eventpost_timecustom		String for custom time display
*/

if (!defined('e107_INIT')) { exit; }

if (!defined("EC_DEFAULT_CATEGORY")) { define('EC_DEFAULT_CATEGORY',"Default"); }

  class ecal_class
  {
  // Useful time/date variables - set up on creation, and available externally
  // (All the times and dates are consistent, being derived from $time_now, which is the time the constructor was
  // called - probably doesn't matter, but may help someone.
	var $time_now;						// Current time/date stamp
	var $site_timedate;					// Site time/date stamp - adjusted for time zone
	var $user_timedate;					// Time/date based on user's time zone
	var $cal_timedate;					// Time/date stamp used by event calendar (user set)
	var $now_date;						// Time/date array from $time_now
	var $site_date;						// Time/date array from $site_timedate
	var $cal_date ;						// Time/date array from $cal_timedate
	
	var $cal_super;						// True if current user is a calendar supervisor
	var $extra_query;					// Extra bit of mysql query used for non-supervisor (read) queries
	
	var $time_format_string;			// String to format times on the site
	var $cal_format_string;				// String to format the displayed date on event entry ("Y-m-d" or "d-m-Y")
	var $dcal_format_string;			// Format string to pass to DHTML calendar
	var $java_format_code;				// Code to pass to Javascript re date format
	
	var $event_date_format_string;		// String to format the date in the event calendar
	var $next_date_format_string;		// String to format the date in the 'forthcoming event' menu
	
	var $date_separator = '-';			// Used for separating off fields on date entry
	
	var $max_cache_time;				// Oldest permissible age of any cached pages relating to event calendar
	var $max_recent_show;				// Time in seconds for highlighting 'recent events' (0 = disable)
	var $cat_text_cache = array();		// Used to cache category text as read
	
	var $ec_first_day_of_week = 0;		// First day of the week
	var $days = array(EC_LAN_25, EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24);	// Array Sunday..Saturday
	var $recur_type = array('0' => 'no', '1' => 'annual', '2' => 'biannual', '3' =>'quarterly', '4' => 'monthly', '5' => 'four weekly', 
					'6' => 'fortnightly', '7' => 'weekly', '8' => 'daily', 
					'100' => 'Sunday in month',
					'101' => 'Monday in month',
					'102' => 'Tuesday in month',
					'103' => 'Wednesday in month',
					'104' => 'Thursday in month',
					'105' => 'Friday in month',
					'106' => 'Saturday in month'
					);
	var $recur_week = array('100' => 'First', '200' => 'Second', '300' => 'Third', '400' => 'Fourth');


    function ecal_class()
	{  // Constructor
	  global $pref;
	  if (!isset($pref['plug_installed']['calendar_menu']))
	  {
		header('location:'.e_BASE.'index.php');
		exit;
	  }

		// Get all the times in terms of 'clock time' - i.e. allowing for TZ, DST, etc
		// All the times in the DB should be 'absolute' - so if we compare with 'clock time' it should work out.
	  $this->time_now = time();
	  $this->site_timedate = $this->time_now + ($pref['time_offset'] * 3600);			// Check sign of offset
	  $this->user_timedate = $this->time_now + TIMEOFFSET;
	  switch ($pref['eventpost_caltime'])
	  {
	    case 1 :
	      $this->cal_timedate  = $this->site_timedate;		// Site time
		  break;
		case 2 :
	      $this->cal_timedate  = $this->user_timedate;		// User
		  break;
		default :
	      $this->cal_timedate = $this->time_now;			// Server time - default
	  }
	  $this->now_date  = getdate($this->time_now);
	  $this->site_date = getdate($this->site_timedate);	// Array with h,m,s, day, month year etc
	  $this->cal_date  = getdate($this->cal_timedate);
	  
	  $this->max_cache_time = $this->site_date['minutes'] + 60*$this->site_date['hours'];
	  
	  $this->cal_super = check_class($pref['eventpost_super']);
	  if ($this->cal_super) $this->extra_query = ""; else $this->extra_query = " AND find_in_set(event_cat_class,'".USERCLASS_LIST."')";

	  $this->max_recent_show = 0;
	  if (isset($pref['eventpost_recentshow']))
	  {
	    if ($pref['eventpost_recentshow'] == 'LV')
		{
		  if (USER) $this->max_recent_show = time() - USERLV;
		}
		else
		{
	      $this->max_recent_show = 3600 * $pref['eventpost_recentshow'];
		}
	  }

	  switch ($pref['eventpost_timedisplay'])
	  {
	    case 2 : 
		  $this->time_format_string = "%I:%M %p";      // 12-hour display
		  break;
		case 3 :
		  $this->time_format_string = $pref['eventpost_timecustom'];      // custom display
		  if (isset($this->time_format_string)) break;
	    case 4 : 
		  $this->time_format_string = "%H:%M";      // 24-hour display with separator
		  break;
		default :
		  $this->time_format_string = "%H%M";      // default to 24-hour display
	  }
	  
	  if (!isset($pref['eventpost_datedisplay'])) $pref['eventpost_datedisplay'] = 1;
	  $temp = $pref['eventpost_datedisplay'];
	  if ($temp >3) 
	  {
	    $temp-= 3;
		$this->date_separator = '.';
		if ($temp > 3)
		{
		  $temp -= 3;
		  $this->date_separator = '/';
		}
	  }
	  switch ($temp)
	  {  // Event entry calendar
	    case 2 :
	      $this->cal_format_string = "d".$this->date_separator."m".$this->date_separator."Y";
		  $this->dcal_format_string = "%d".$this->date_separator."%m".$this->date_separator."%Y";
		  $this->java_format_code = 2;
		  break;
	    case 3 :
	      $this->cal_format_string = "m".$this->date_separator."d".$this->date_separator."Y";
		  $this->dcal_format_string = "%m".$this->date_separator."%d".$this->date_separator."%Y";
		  $this->java_format_code = 3;
		  break;
	    default :  // 'original' defaults
	      $this->cal_format_string = "Y".$this->date_separator."m".$this->date_separator."d";
		  $this->dcal_format_string = "%Y".$this->date_separator."%m".$this->date_separator."%d";
		  $this->java_format_code = 1;
	  }
	  
	  if (!isset($pref['eventpost_dateevent'])) $pref['eventpost_dateevent'] = 1;
	  switch ($pref['eventpost_dateevent'])
	  {  // Event list date display
		case 0 :
		  $this->event_date_format_string = $pref['eventpost_eventdatecustom'];
		  break;
	    case 2 : 
		  $this->event_date_format_string = "%a %d %b %Y";
		  break;
	    case 3 : 
		  $this->event_date_format_string = "%a %d-%m-%y";
		  break;
	    default : 
		  $this->event_date_format_string = "%A %d %B %Y";
	  }
	  
	  if (!isset($pref['eventpost_datenext'])) $pref['eventpost_datenext'] = 1;
	  switch ($pref['eventpost_datenext'])
	  {  // Forthcoming event date display
	    case 0 : 
		  $this->next_date_format_string = $pref['eventpost_nextdatecustom'];
		  break;
	    case 2 : 
		  $this->next_date_format_string = "%d %b";
		  break;
	    case 3 : 
		  $this->next_date_format_string = "%B %d";
		  break;
	    case 4 : 
		  $this->next_date_format_string = "%b %d";
		  break;
	    default : 
		  $this->next_date_format_string = "%d %B";
	  }
	  
	  switch (varset($pref['eventpost_weekstart'],'sun'))
	  {
		case  'sun' : $this->ec_first_day_of_week = 0; break;
		case  'mon' : $this->ec_first_day_of_week = 1; break;
		case 0 :
		case 1 :
		case 2 :
		case 3 :
		case 4 :
		case 5 :
		case 6 :
		  $this->ec_first_day_of_week = $pref['eventpost_weekstart']; break;
		default :
		  $this->ec_first_day_of_week = 1;
	  }

	}
	
	function time_string($convtime)
	{  // Returns a time string from a time stamp, formatted as 24-hour, 12-hour or custom as set in prefs
	  return gmstrftime($this->time_format_string, $convtime);
	}

	function event_date_string($convdate)
	{  // Returns a date string from a date stamp, formatted for display in event list
	  return gmstrftime($this->event_date_format_string,$convdate);
	}
	
	
	function next_date_string($convdate)
	{  // Returns a date string from a date stamp, formatted for display in forthcoming event menu
	  return gmstrftime($this->next_date_format_string,$convdate);
	}
	
	
	function full_date($convdate)
	{  // Returns a date as dd-mm-yyyy or yyyy-mm-dd according to prefs (for event entry)
	  return gmdate($this->cal_format_string, $convdate);
	}
	
	function make_date($new_hour, $new_minute, $date_string)
	{   // Turns a date as entered in the calendar into a time stamp (for event entry)
      $tmp = explode($this->date_separator, $date_string);
	  switch ($this->java_format_code)
	  {
	    case 2 :
          return  gmmktime($new_hour, $new_minute, 0, $tmp[1], $tmp[0], $tmp[2]);    // dd-mm-yyyy
		case 3 :
          return  gmmktime($new_hour, $new_minute, 0, $tmp[0], $tmp[1], $tmp[2]);		// mm-dd-yyyy
		default :
          return  gmmktime($new_hour, $new_minute, 0, $tmp[1], $tmp[2], $tmp[0]);		// yyyy-mm-dd
	  }
	}
	
	// Return day of week string relative to the start of the week
	function day_offset_string($doff)
	{
	  return $this->days[($doff+$this->ec_first_day_of_week) % 7];
	}


	function cal_log($event_type, $event_title = '', $event_string='', $event_start=0)
	{  // All calendar-related logging intentionally passed through a single point to maintain control
	   // (so we could also add other info if we wanted)
	   // Event types:
	   //   1 - add event
	   //	2 - edit event
	   //	3 - delete event
	   // 	4 - Bulk delete
	   //	5 - add multiple events
	  global $pref, $admin_log, $e_event, $PLUGINS_DIRECTORY, $e107;
	  
	  $log_titles = array(	'1' => 'EC_ADM_01',
							'2' => 'EC_ADM_02',
							'3' => 'EC_ADM_03',
							'4' => 'EC_ADM_04',
							'5' => 'EC_ADM_05',
							);
// Do the notifies first
	  $cmessage = $log_titles[$event_type]."<br />";
	  if ($event_start > 0)
	  {
	    $cmessage .= "Event Start: ".strftime("%d-%B-%Y",$event_start)."<br />";
	    $cmessage .= "Event Link:  ".$pref['siteurl'].$PLUGINS_DIRECTORY. "calendar_menu/event.php?".$event_start." <br />";
	  }
	  else
	    $cmessage .= "Event Start unknown<br />";
	  $edata_ec = array("cmessage" => $cmessage, "ip" => $e107->getip());
	  switch ($event_type)
	  {
	    case 5 :
	    case 1 : $e_event -> trigger("ecalnew", $edata_ec);
				 break;
	    case 2 :
		case 3 :
		case 4 : $e_event -> trigger("ecaledit", $edata_ec);
				 break;
	  }

	  switch ($pref['eventpost_adminlog'])
	  {
	    case 1 : if ($event_type == '1') return;
		case 2 : break;   // Continue
		default : return;   // Invalid or undefined option
	  }
	  $log_detail = array(	'1' => 'Event Calendar - add event '.strftime("%d-%B-%Y",$event_start),
							'2' => 'Event Calendar - edit event '.strftime("%d-%B-%Y",$event_start),
							'3' => 'Event Calendar - delete event '.strftime("%d-%B-%Y",$event_start),
							'4' => 'Event Calendar - Bulk Delete',
							'5' => 'Event Calendar - multiple add '.strftime("%d-%B-%Y",$event_start)
							);
	  $admin_log->log_event($log_titles[$event_type],$event_title."&nbsp;\n".$log_detail[$event_type]."\n".$event_string,'');
	}



	function get_category_text($ev_cat)
	{
	  global $sql;
	  if (!isset($this->cat_text_cache[$ev_cat]))
	  {
	    $sql->db_Select('event_cat','event_cat_name',"event_cat_id='{$ev_cat}'");
	    $row = $sql->db_Fetch();
	    $this->cat_text_cache[$ev_cat] = $row['event_cat_name'];
	  }
	  return $this->cat_text_cache[$ev_cat];
	}


	// Implements a version of getdate that expects a GMT date and doesn't do TZ/DST adjustments
	// time() -date('Z') gives the correction to 'null out' the TZ and DST adjustments that getdate() does
	function gmgetdate($date)
	{
	  return getdate($date-date('Z'));
	}

//------------------------------------------------
//		Recurring event handling
//------------------------------------------------

// Generate a list of recurring events based on a 'first event' date, an interval and start/finish times
// Returns an array of times
	function gen_recur_regular($first_event, $last_event, $interval, $start_time, $end_time)
	{
	  if ($last_event < $end_time) $end_time = $last_event;
	  $ret = array();
	  $first_event = $first_event + ceil(($start_time-$first_event)/$interval)*$interval;
	  while ($first_event <= $end_time)
	  {
	    $ret[] = $first_event;
		$first_event += $interval;
	  }
	  return $ret;
	}


	function add_dates($main_date,$adder)
	{  // Adds an offset of months and years to a date
	  if ($adder['mon'])
	  {
	    $main_date['mon'] += $adder['mon'];
		if ($main_date['mon'] > 12)
		{
		  $main_date['mon'] -= 12;
		  $main_date['year']++;
		}
	  }
	  if ($adder['year']) $main_date['year'] += $adder['year'];
	  return $main_date;
	}

	
// Generate a list of recurring events based on a 'first event' date, an interval type and start/finish window
// For day number, '0' = 'Sunday'
	function gen_recur($first_event, $last_event, $interval_type, $start_time, $end_time)
	{
	  if ($last_event < $end_time) $end_time = $last_event;
	  $ret = array();
	  $week_offset = 0;
	  if ($interval_type >= 100)
	  {
	    $week_offset = intval($interval_type /100);
		$day_number = $interval_type % 10;				// Gives 0..6 in practice; potentially 0..9
		$interval_type = 100;
	  }
	  if ($first_event > $end_time) return $ret;

	  $interval = array('5' => 28*86400, '6' => 14*86400, '7' => 7*86400, '8' => 86400);
	  // Do the easy ones first
	  if (array_key_exists($interval_type, $interval)) return $this->gen_recur_regular($first_event, $last_event, $interval[$interval_type], $start_time, $end_time);

// We're messing around with months and years here
	  $inc_array['year'] = 0;
	  $inc_array['mon'] = 0;

	// Find the first date which is within, or close to, scope (N.B. may not be one)
	  $event = $this->gmgetdate($first_event);
	  $temp = $this->gmgetdate($start_time);
	  $event['year'] = $temp['year'];		// Use the year from the start window
	  if ($event['mon'] > $temp['mon']) $event['year']--;		// Handle situation where event later in year than desired window

	  switch ($interval_type)
	  {
	    case 1 :	// Annual
		  $inc_array['year'] = 1;
		  break;
		case 2 :	// Biannual
		  $inc_array['mon'] = 6;
		  break;
		case 3 :	// Quarterly
		  $inc_array['mon'] = 3;
		  break;
		case 4 :	// Monthly
		  $inc_array['mon'] = 1;
		  break;
		case 100 :	// Monthly on nth Sunday in month
		case 101 :	// Monthly on nth Monday in month
		case 102 :	// Monthly on nth Tuesday in month
		case 103 :	// Monthly on nth Wednesday in month
		case 104 :	// Monthly on nth Thursday in month
		case 105 :	// Monthly on nth Friday in month
		case 106 :	// Monthly on nth Saturday in month
//		  echo "Specific day of month: ".$day_number."<br />";
		  $inc_array['mon'] = 1;
		  $event['mon'] = $temp['mon'];
		  $event['year'] = $temp['year'];
		  $event['mday'] = 1;		// Start calculating from first day of each month
		  break;
		default :
		  return FALSE;		// Invalid interval type
	  }

//	  echo "First date: ".$event['mon']."-".$event['year']."<br />";
	  // Now loop through using the increment - we may discard a few, but getting clever may be worse!
	  $cont = TRUE;
	  
	  do {
	    $tstamp = gmmktime($event['hours'],$event['minutes'],$event['seconds'],$event['mon'],$event['mday'],$event['year']);
		if ($interval_type >= 100)
		{	// $tstamp has the first of the month
//		  $dofwk = gmdate('w',$tstamp);
		  $day_diff = $day_number - gmdate('w',$tstamp);
		  if ($day_diff <0) $day_diff += 7;
		  $day_diff += (7 * $week_offset) - 7;
//		  echo "Day difference = ".$day_diff."  Stamp=".$tstamp."  Week day: ".$dofwk."<br />";
		  $tstamp += $day_diff*86400;
		}
		if ($tstamp >= $start_time)
		{
		  if ($tstamp <= $end_time)
		  {
		    $ret[] = $tstamp;
		  }
		  else
		  {
		    $cont = FALSE;
		  }
		}
		$event = $this->add_dates($event,$inc_array);
	  } while ($cont);
	  
	  return $ret;
	}


	// Generate comma separated list of fields for table, with optional alias prefix.
	function gen_field_list($table, $list, $must_have = '')
	{
	  if ($list == '*') return $table ? $table.".*" : '*';
	  $ret = '';
	  $pad = '';
	  $temp = explode(',',$list);
	  for ($i = 0; $i < count($temp); $i++) $temp[$i] = trim($temp[$i]);
	  if ($must_have)
	  {
	    $mharr = explode(',',$must_have);
		foreach ($mharr as $mh)
		{
		  if (!in_array(trim($mh), $temp)) $temp[] = trim($mh);
		}
	  }
	  foreach ($temp as $fld)
	  {
		if ($fld)
		{
		  if ($table) $fld = $table.'.'.$fld;
		  $ret .= $pad.$fld;
		  $pad = ', ';
		}
	  }
	  return $ret;
	}

// Read a list of events between start and end dates
// If $start_only is TRUE, only searches based on the start date/time
// Potential option to hook in other routines later
	function get_events($start_time, $end_time, $start_only=FALSE, $cat_filter=0, $inc_recur=FALSE, $event_fields='*', $cat_fields='*')
	{
	  global $sql;
	  
	  $ret = array();
	  if ($cat_filter === FALSE) return $ret;
	  $cat_lj = '';
	  $category_filter = '';
	  $extra = '';
	  $so = '';

	  $event_fields = $this->gen_field_list('e',$event_fields,'event_start,event_end,event_datestamp');
	  if ($cat_fields) 
	  {
	    $cat_fields = ', '.$this->gen_field_list('ec',$cat_fields);
		$cat_lj = ' LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id ';
	  }

	  if ($cat_filter && ($cat_filter != '*')) $category_filter = " AND find_in_set(e.event_category, '".$cat_filter."') ";
	  if ($inc_recur) $extra = " OR (e.event_recurring >'0' AND (e.event_start < ".intval($end_time)." AND e.event_end >= ".intval($start_time).")) ";
	  
	  $so = $start_only ? 'start' : 'end';
	  $qry = "SELECT {$event_fields}{$cat_fields} FROM #event as e {$cat_lj}
		WHERE (
		(e.event_recurring = '0' AND ((e.event_{$so} >= ".intval($start_time)." AND e.event_start < ".intval($end_time).")))
		{$extra})
		{$category_filter} 
		{$this->extra_query} 
		ORDER BY e.event_start ASC
	  ";
	  
	  if ($sql->db_Select_gen($qry))
	  {
	    while ($row = $sql->db_Fetch())
		{
		  // Always add the 'is_recent' marker if required
		  if ((($this->max_recent_show != 0) && (time() - $row['event_datestamp']) <= $this->max_recent_show)) $row['is_recent'] = TRUE; 
		  if ($row['event_recurring'] == 0)
		  {
		    $ret[] = $row;
		  }
		  else
		  {  // Recurring events to handle
		    $temp = $this->gen_recur($row['event_start'],$row['event_end'],$row['event_recurring'],$start_time,$end_time);
			if (count($temp)) 
			{
			  $row['event_start'] = $temp;		// Have an array of start times
			  $ret[] = $row;
			}
		  }
		}
	  }
	  return $ret;
	}


	// Function to return up to a maximum number of events between a given start and end date
	// It always uses the event start date only
	// It tries to keep the actual number of events in memory to a minimum by discarding when it can.
	// Once there are $num_events read, it pulls in the $end_time to speed up checks
	// $cat_filter = FALSE is 'no categories' - returns an empty array.
	// $cat_filter = '*' means 'all categories'
	// otherwise $cat_filter mst be a comma-separated list of category IDs.
	function get_n_events($num_event, $start_time, $end_time, $cat_filter='*', $inc_recur=FALSE, $event_fields='*', $cat_fields='*')
	{
	  global $sql;

	  $ret = array();
	  if ($cat_filter === FALSE) return $ret;			// Empty category

	  $cat_lj = '';
	  $category_filter = '';
	  $extra = '';

	  $event_fields = $this->gen_field_list('e',$event_fields,'event_start,event_end,event_datestamp,event_recurring');
	  if ($cat_fields) 
	  {
	    $cat_fields = ', '.$this->gen_field_list('ec',$cat_fields);
		$cat_lj = ' LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id ';
	  }
	  
	  if ($cat_filter != '*') $category_filter = " AND find_in_set(e.event_category, '".$cat_filter."') ";
	  if ($inc_recur) $extra = " OR (e.event_recurring >'0' AND (e.event_start <= ".intval($end_time)." AND e.event_end >= ".intval($start_time).")) ";
	
	  $qry = "SELECT {$event_fields}{$cat_fields} FROM #event as e {$cat_lj}
		WHERE (
		(e.event_recurring = '0' AND (e.event_start >= ".intval($start_time)." AND e.event_start <= ".intval($end_time).") )
		{$extra})
		{$category_filter} 
		{$this->extra_query} 
		ORDER BY e.event_start ASC
	  ";
	  
//	echo "get_n_events Query: ".$qry."<br />";
	
	  if ($sql->db_Select_gen($qry))
	  {
	    while ($row = $sql->db_Fetch())
		{
		  // Always add the 'is_recent' marker if required
		  if ((($this->max_recent_show != 0) && (time() - $row['event_datestamp']) <= $this->max_recent_show)) $row['is_recent'] = TRUE; 
		  unset($temp);
		  if ($row['event_recurring'] == 0)
		  {	
//		    echo "Standard:  ".$row['event_start']."  ".$row['event_title']."<br />";
		    $temp = array($row['event_start']);
		  }
		  else
		  {  // Recurring events to handle
//		  echo "Recurring: ".$row['event_start']."  ".$row['event_title']."  -   ".$row['event_recurring']."  -  ";
		    $temp = $this->gen_recur($row['event_start'],$row['event_end'],$row['event_recurring'],$start_time,$end_time);
//			echo count($temp)."results generated<br />";
		  }

		  if (count($temp)) 
		  {  // We have one or more events to add to the array
		    foreach ($temp as $ts)
			{
//		    echo "Process:  ".$ts."  ".$row['event_start']."  ".$row['event_title']."  ".$end_time."<br />";
			  if ($ts <= $end_time)   // We may have pulled in $end_time from the value passed initially
			  {
			    $row['event_start'] = $ts;			// Fill this in - may be a recurring event
//		  echo "Add: ".$row['event_start']."  ".$row['event_title']."<br />";
		  
			    if ((count($ret) == 0) || ($ts > $ret[count($ret)-1]['event_start']))
				{  // Can just add on end
//				  echo "Add at end<br />";
				  $ret[] = $row;
				}
				else
				{  // Find a slot
			      $i = count($ret);
			      while (($i > 0) && ($ret[$i-1]['event_start'] > $ts)) $i--;
				  // $i has the number of the event before which to insert this new event.
				  if ($i == 0)
				  {
				    array_unshift($ret,$row);	// Just insert at beginning
//					echo "Insert at front<br />";
				  }
				  else
				  {  // Proper insert needed just before element $i
//				    $tmp = array_unshift(array_slice($ret, $i),$row);
//					array_splice($ret, $i, count($ret), $tmp); 
					array_splice($ret, $i, count($ret), array_merge(array($row),array_slice($ret, $i))); 
//					echo "Insert at ".$i."<br />";
				  }
				}
			  }
			  if (count($ret) > $num_event)
			  {  // Knock one off the end
//			    echo "Delete, count is ".count($ret)."<br />";
			    if ($ret[count($ret)-1]['event_start'] < $end_time) $end_time = $ret[count($ret)-1]['event_start'];	// Pull in end time if we can
				array_pop($ret);
			  }
			}
		  }
		}
	  }
	  return $ret;
	} // End - function get_n_events()


	function get_recur_text($recurring)
	{
	  if ($recurring >= 100)
	  {
	    return $this->recur_week[100*intval($recurring/100)]." ".$this->recur_type[100+($recurring % 10)];
	  }
	  else
	  {
	    return $this->recur_type[$recurring];
	  }
	}
	  
  }// End - class definition

?>
