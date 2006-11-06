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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/ecal_class.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-11-06 22:30:23 $
|     $Author: e107coders $
|
| Event calendar class for gradual enhancement
| (Some bits may be usefully transferred to common code later)
|
| 03.11.06 - Add cache time for later
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

	var $max_cache_time;				// Oldest permissible age of any cached pages relating to event calendar

    function ecal_class()
	{  // Constructor
	  global $pref;

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
	  {
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
	}

	function time_string($convtime)
	{  // Returns a time string from a time stamp, formatted as 24-hour, 12-hour or custom as set in prefs
	  return strftime($this->time_format_string, $convtime);
	}

	function full_date($convdate)
	{  // Returns a date as dd-mm-yyyy or yyyy-mm-dd according to prefs
	  return date($this->cal_format_string, $convdate);
	}

	function make_date($new_hour, $new_minute, $date_string)
	{   // Turns a date as entered in the calendar into a time stamp
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

	function cal_log($event_type, $event_title = '', $event_string='')
	{  // All calendar-related logging intentionally passed through a single point to maintain control
	   // (so we could also add other info if we wanted)
	   // Event types:
	   //   1 - add event
	   //	2 - edit event
	   //	3 - delete event
	   // 	4 - Bulk delete
	  global $pref, $admin_log;
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
