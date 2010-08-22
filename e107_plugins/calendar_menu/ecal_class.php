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
| Event calendar class for gradual enhancement
| (Some bits may be usefully transferred to common code later)
|
| 11.11.06 	- Add date formatting options
|			- Add notify
|
+----------------------------------------------------------------------------+
*/

/*
Preferences used:
  eventpost_caltime			1 = server, 2 = site, 3 = user
  eventpost_timedisplay		1 = 24 hour, 2 = 12 hour default, 3 = custom
  eventpost_timecustom		String for custom time display

  date() returns formatted date/time string
*/
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
	
	var $max_cache_time;				// Oldest permissible age of any cached pages relating to event calendar
	var $max_recent_show;				// Time in seconds for showing 'recent events'
	
    function ecal_class()
	{  // Constructor
	  global $pref;
	  if (!isset($pref['plug_installed']['calendar_menu']))
	  {
		header('location:'.e_BASE.'index.php');
		exit;
	  }
	  
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

	  if (isset($pref['eventpost_recentshow']) && ($pref['eventpost_recentshow'] != 0))
	  {
	    $this->max_recent_show = 3600 * $pref['eventpost_recentshow'];
	  }
	  else
	  {
	    $this->max_recent_show = 0;
	  }
	  switch ($pref['eventpost_timedisplay'])
	  {
	    case 2 : 
		  $this->time_format_string = "%I:%M %p";      // 12-hour display
		  break;
		case 3 :
		  $this->time_format_string = $pref['eventpost_timecustom'];      // custom display
		  if (isset($this->time_format_string)) break;
		default :
		  $this->time_format_string = "%H%M";      // default to 24-hour display
	  }
	  
	  switch ($pref['eventpost_datedisplay'])
	  {  // Event entry calendar
	    case 2 :
	      $this->cal_format_string = "d-m-Y";
		  $this->dcal_format_string = "%d-%m-%Y";
		  $this->java_format_code = 2;
		  break;
	    case 3 :
	      $this->cal_format_string = "m-d-Y";
		  $this->dcal_format_string = "%m-%d-%Y";
		  $this->java_format_code = 3;
		  break;
	    default :  // 'original' defaults
	      $this->cal_format_string = "Y-m-d";
		  $this->dcal_format_string = "%Y-%m-%d";
		  $this->java_format_code = 1;
	  }
	  
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
	}
	
	function time_string($convtime)
	{  // Returns a time string from a time stamp, formatted as 24-hour, 12-hour or custom as set in prefs
	  return strftime($this->time_format_string, $convtime);
	}

	function event_date_string($convdate)
	{  // Returns a date string from a date stamp, formatted for display in event list
	  return strftime($this->event_date_format_string,$convdate);
	}
	
	
	function next_date_string($convdate)
	{  // Returns a date string from a date stamp, formatted for display in forthcoming event menu
	  return strftime($this->next_date_format_string,$convdate);
	}
	
	
	function full_date($convdate)
	{  // Returns a date as dd-mm-yyyy or yyyy-mm-dd according to prefs (for event entry)
	  return date($this->cal_format_string, $convdate);
	}
	
	function make_date($new_hour, $new_minute, $date_string)
	{   // Turns a date as entered in the calendar into a time stamp (for event entry)
	  global $pref;
      $tmp = explode("-", $date_string);
	  switch ($pref['eventpost_datedisplay'])
	  {
	    case 2 :
          return  mktime($new_hour, $new_minute, 0, $tmp[1], $tmp[0], $tmp[2]);    // dd-mm-yyyy
		case 3 :
          return  mktime($new_hour, $new_minute, 0, $tmp[0], $tmp[1], $tmp[2]);		// mm-dd-yyyy
		default :
          return  mktime($new_hour, $new_minute, 0, $tmp[1], $tmp[2], $tmp[0]);		// yyyy-mm-dd
	  }
	}
	
	function cal_log($event_type, $event_title = '', $event_string='', $event_start=0)
	{  // All calendar-related logging intentionally passed through a single point to maintain control
	   // (so we could also add other info if we wanted)
	   // Event types:
	   //   1 - add event
	   //	2 - edit event
	   //	3 - delete event
	   // 	4 - Bulk delete
	  global $pref, $admin_log, $e_event;
	  
	  $log_titles = array(	'1' => 'Event Calendar - add event',
							'2' => 'Event Calendar - edit event',
							'3' => 'Event Calendar - delete event',
							'4' => 'Event Calendar - Bulk Delete'
							);
// Do the notifies first
	  $cmessage = $log_titles[$event_type]."<br />";
	  if ($event_start > 0)
	    $cmessage .= "Event Start: ".strftime("%d-%B-%Y",$event_start)."<br />";
	  else
	    $cmessage .= "Event Start unknown<br />";
	  $edata_ec = array("cmessage" => $cmessage, "ip" => getip());
	  switch ($event_type)
	  {
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
	  $log_titles = array(	'1' => 'Event Calendar - add event',
							'2' => 'Event Calendar - edit event',
							'3' => 'Event Calendar - delete event',
							'4' => 'Event Calendar - Bulk Delete'
							);
	  $admin_log->log_event($log_titles[$event_type],$event_title."&nbsp;\n".$event_string,4);
	}
  }

?>
