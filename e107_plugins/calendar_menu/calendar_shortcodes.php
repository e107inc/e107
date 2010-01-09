<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Shortcodes for event calendar
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/calendar_shortcodes.php,v $
 * $Revision: 1.19 $
 * $Date: 2010-01-09 12:06:09 $
 * $Author: e107steved $
 *
*/

/**
 *	e107 Event calendar plugin
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id: calendar_shortcodes.php,v 1.19 2010-01-09 12:06:09 e107steved Exp $;
 */

/*
TODO:
	1.	Good way of reading categories
	2. Have 'currentMonth' flag (means 'current day' if $ds == 'one') ?
	3. Check whether $prop should be calculated better
*/

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'.php');	
register_shortcode('event_calendar_shortcodes', true);
initShortcodeClass('event_calendar_shortcodes');

/*
Navigation Shortcodes
---------------------
EC_PREV_MONTH
EC_CURRENT_MONTH
EC_NEXT_MONTH
EC_PREV_YEAR
EC_NEXT_YEAR
EC_MONTH_LIST

Navigation Buttons
------------------
EC_NAV_BUT_ALLEVENTS
EC_NAV_BUT_VIEWCAT
EC_NAV_BUT_SUBSCRIPTION
EC_NAV_BUT_ENTEREVENT
EC_NAV_LINKCURRENTMONTH
EC_NAV_BUT_PRINTLISTS

Shortcodes for 'big' calendar display
-------------------------------------
EC_CALENDAR_CALENDAR_HEADER_DAY
EC_CALENDAR_CALENDAR_DAY_EVENT_HEADING
EC_CALENDAR_CALENDAR_DAY_TODAY_HEADING
EC_CALENDAR_CALENDAR_DAY_EMPTY_HEADING
EC_CALENDAR_CALENDAR_RECENT_ICON

Shortcodes for Event List
-------------------------
EC_EVENTLIST_CAPTION

Return event information
------------------------
EC_EVENT_LOCATION - event location
EC_EVENT_RECENT_ICON
EC_SHOWEVENT_IMAGE
EC_SHOWEVENT_INDICAT
EC_SHOWEVENT_HEADING
EC_IF_ALLDAY
EC_IF_SAMEDAY
EC_IFNOT_SAMEDAY
EC_IFNOT_ALLDAY
EC_EVENT_HEADING_DATE - date for heading
EC_EVENT_DATE_START - date for body
EC_EVENT_TIME_START
EC_EVENT_DATE_END
EC_EVENT_TIME_END
EC_EVENT_EVENT_DATE_TIME
EC_EVENT_TITLE
EC_EVENT_CAT_ICON
EC_EVENT_ID
EC_EVENT_DISPLAYSTYLE
EC_EVENT_DETAILS
EC_EVENT_CATEGORY
EC_EVENT_AUTHOR
EC_EVENT_CONTACT
EC_EVENT_THREAD
EC_EVENT_OPTIONS
EC_EC_EVENT_LINK
EC_EVENT_SHORT_DATE

Event Archive
-------------
EC_EVENTARCHIVE_CAPTION
EC_EVENTARCHIVE_DATE
EC_EVENTARCHIVE_DETAILS
EC_EVENTARCHIVE_HEADING
EC_EVENTARCHIVE_EMPTY

Forthcoming Events menu
-----------------------
EC_NEXT_EVENT_RECENT_ICON
EC_NEXT_EVENT_TIME
EC_NEXT_EVENT_DATE
EC_NEXT_EVENT_TITLE
EC_NEXT_EVENT_ICON
EC_NEXT_EVENT_GAP


Shortcodes for event calendar mailout
-------------------------------------
EC_MAIL_HEADING_DATE - event start date, optional parameter to format date (intended for headings etc)
EC_MAIL_DATE_START - event start date, optional parameter to format date  (intended for body text)
EC_MAIL_DATE_START_ALLDAY - returns date only for all day events, otherwise empty string
EC_MAIL_DATE_START_TIMED - returns date only for 'timed' events, otherwise empty string
EC_MAIL_TIME_START - event start time
EC_MAIL_DATE_END - event end date (empty string if same as start date)
EC_MAIL_TIME_END - time at which event ends (empty string if all day)
EC_MAIL_TITLE - title of event
EC_MAIL_ID - event ID (in database)
EC_MAIL_DETAILS - event details
EC_MAIL_CATEGORY - event category text
EC_MAIL_LOCATION - use EC_EVENT_LOCATION
EC_MAIL_CONTACT - event contact
EC_MAIL_THREAD - forum thread
EC_MAIL_LINK - link to event detail on web site
EC_MAIL_SHORT_DATE - short date (day, month) for event start
EC_MAIL_SUBJECT - subject for mailout

List printing
-------------
EC_PR_LIST_TITLE
EC_PR_CAT_LIST
EC_PR_CHANGE_YEAR
EC_PR_CHANGE_MONTH
EC_NOW_TIME
EC_NOW_DATE
EC_PR_LIST_START
EC_PR_LIST_END
EC_PRINT_BUTTON
EC_IF_PRINT
EC_IFNOT_PRINT
EC_IF_DISPLAY
EC_IFNOT_DISPLAY
EC_IF_PDF
EC_IFNOT_PDF
*/
class event_calendar_shortcodes
{
	protected $e107;

	public 	$event;			// Current event being displayed
	public 	$ecalClass;		// Pointer to event calendar class
	public	$headerDay = 0;	// Day number for header
	public	$todayStart;	// Start of current day
	public	$curDay;		// Current day of month (1..31)
	public	$numEvents = 0;	// Number of events to be expected in certain list formats
	public	$catFilter = '*';	// Event category filter
	public	$eventDisplayCodes = '';	// Set to be an array of options
	public	$ecOutputType = '';	// Used by printing routines
	public	$changeFlags = array();	// Used by printing routines
	public	$printVars = array();	// USed by printing routine

	private $months	= array(EC_LAN_0, EC_LAN_1, EC_LAN_2, EC_LAN_3, EC_LAN_4, EC_LAN_5, EC_LAN_6, 
						EC_LAN_7, EC_LAN_8, EC_LAN_9, EC_LAN_10, EC_LAN_11);		// 'Long' month names
	private $monthabb = array(EC_LAN_JAN, EC_LAN_FEB, EC_LAN_MAR, EC_LAN_APR, EC_LAN_MAY, EC_LAN_JUN, 
						EC_LAN_JUL, EC_LAN_AUG, EC_LAN_SEP, EC_LAN_OCT, EC_LAN_NOV, EC_LAN_DEC);		// 'Short' month names
	private $days = array(EC_LAN_DAY_1, EC_LAN_DAY_2, EC_LAN_DAY_3, EC_LAN_DAY_4, EC_LAN_DAY_5, EC_LAN_DAY_6, EC_LAN_DAY_7, 
						EC_LAN_DAY_8, EC_LAN_DAY_9, EC_LAN_DAY_10, EC_LAN_DAY_11, EC_LAN_DAY_12, EC_LAN_DAY_13, EC_LAN_DAY_14, 
						EC_LAN_DAY_15, EC_LAN_DAY_16, EC_LAN_DAY_17, EC_LAN_DAY_18, EC_LAN_DAY_19, EC_LAN_DAY_20, EC_LAN_DAY_21, 
						EC_LAN_DAY_22, EC_LAN_DAY_23, EC_LAN_DAY_24, EC_LAN_DAY_25, EC_LAN_DAY_26, EC_LAN_DAY_27, EC_LAN_DAY_28, 
						EC_LAN_DAY_29, EC_LAN_DAY_30, EC_LAN_DAY_31);			// Days of month (numbers)

	private	$nowDay;	// Today
	private	$nowMonth;
	private	$nowYear;

	private $day;		// Day of month - often not used
	private $month;		// Month to display
	private $year;		// Year to display

	private $previous;	// Previous month - date stamp
	private	$next;		// Next month - date stamp

	private $monthStart;
	private $monthEnd;
	
	private $prevMonth;
	private $nextMonth;
	
	private $prevLink;	// Previous year
	private $py;
	private $nextLink;	// Next year
	private $ny;
	
	private $prop;		// Start date for new event entry
	private	$ds = '';	// Display type for some shortcodes (mostly event listing)

	private	$ourDB;		// For when we need a DB object


	public function __construct()
	{
		$this->e107 = e107::getInstance();
	}


	/**
	 * Set the current date for calendar display
	 *
	 * Routine then calculates various values needed for shortcodes
	 *
	 * @param array $curDate - As returned by getdate()
	 *
	 * @return BOOLEAN TRUE
	 */
	public function setCalDate($curDate)
	{
		$this->ds = varset($curDate['ds'],'');

		$this->day = varset($curDate['mday'], 0);				// Day number being shown - rarely relevant
		$this->month = $curDate['mon'];							// Number of month being shown
		$this->year	= $curDate['year'];							// Number of year being shown
		$this->monthStart	= mktime(0, 0, 0, $curDate['mon'], 1, $curDate['year']);			// Start of month to be shown
		$this->monthEnd	= mktime(0, 0, 0, $curDate['mon'] + 1, 1, $curDate['year']) - 1;	// End of month to be shown
		
		
		// Calculate date code for previous month
		$this->prevMonth = $curDate['mon']-1;
		$prevYear	= $curDate['year'];
		if ($this->prevMonth == 0)
		{
			$this->prevMonth = 12;
			$prevYear--;
		} 
		$this->previous = mktime(0, 0, 0, $this->prevMonth, 1, $prevYear);		// Previous month - Used by nav

		// Calculate date code for next month
		$this->nextMonth = $curDate['mon'] + 1;
		$nextYear	= $curDate['year'];
		if ($this->nextMonth == 13)
		{
			$this->nextMonth	= 1;
			$nextYear++;
		} 
		$this->next = mktime(0, 0, 0, $this->nextMonth, 1, $nextYear);		// Next month - used by nav


		$this->py	= $curDate['year']-1;									// Number of previous year for nav
		$this->prevLink = mktime(0, 0, 0, $curDate['mon'], 1, $this->py);
		$this->ny	= $curDate['year'] + 1;								// Number of next year for nav
		$this->nextLink = mktime(0, 0, 0, $curDate['mon'], 1, $this->ny);

		$this->prop		= gmmktime(0, 0, 0, $curDate['mon'], $curDate['mday'], $curDate['year']);		// Sets start date for new event entry

		$this->nowMonth	= $this->ecalClass->cal_date['mon'];
		$this->nowYear	= $this->ecalClass->cal_date['year'];
		$this->nowDay	= $this->ecalClass->cal_date['mday'];
		return TRUE;
	}

	// Navigation shortcodes
	public function sc_ec_prev_month($parm = '')
	{
		return "<a href='".e_SELF."?".$this->previous."'>&lt;&lt; ".$this->months[($this->prevMonth-1)]."</a>";
	}

	public function sc_ec_next_month($parm = '')
	{
		return "<a href='".e_SELF."?".$this->next."'> ".$this->months[($this->nextMonth-1)]." &gt;&gt;</a>";
	}


	public function sc_ec_current_month($parm = '')
	{
		global $pref;
		if($pref['eventpost_dateformat'] == 'my') 
		{
			return $this->months[($this->month-1)].' '.$this->year;
		} 
			return $this->year.' '.$this->months[($this->month-1)];
	}


	public function sc_ec_prev_year($parm = '')
	{
		return "<a href='".e_SELF."?".$this->prevLink."'>&lt;&lt; ".$this->py."</a>";
	}

	public function sc_ec_next_year($parm = '')
	{
		return "<a href='".e_SELF."?".$this->nextLink."'>".$this->ny." &gt;&gt;</a>";
	}


	public function sc_ec_month_list($parm = '')
	{
		$ret = '';
		for ($ii = 0; $ii < 12; $ii++)
		{
			$monthJump = mktime(0, 0, 0, $ii+1, 1, $this->year);
			$ret .= "<a href='".e_SELF."?".$monthJump."'>".$this->monthabb[$ii]."</a> &nbsp;";
		}
		return $ret;
	}


	// Navigation buttons
	public function sc_ec_nav_but_allevents($parm = '')
	{
		$allevents = (e_PAGE == "event.php" ? EC_LAN_96 : EC_LAN_93);
		return "<input class='button' type='submit' style='width:140px;' name='viewallevents' value='".$allevents."' title='".$allevents."' />";
	}

	public function sc_ec_nav_but_viewcat($parm = '')
	{
		return "<input type='hidden' name='do' value='vc' />";
	}

	public function sc_ec_nav_but_subscription($parm = '')
	{
		global $pref;
		if (isset($pref['eventpost_asubs']) && ($pref['eventpost_asubs']>0) && USER)
		{
			return "<input class='button' type='submit' style='width:140px;' name='subs' value='".EC_LAN_123."' />";
		}
		return '';
	}

	public function sc_ec_nav_but_enterevent($parm = '')
	{
		global $pref;
		$ret = "<input type='hidden' name='enter_new_val' value='".$this->prop."' />";
		if ($this->ecalClass->cal_super || check_class($pref['eventpost_admin']))
		{
			$ret .= "<input class='button' type='submit' style='width:140px;' name='doit' value='".EC_LAN_94."' />";
		}
		return $ret;
	}

	public function sc_ec_nav_linkcurrentmonth($parm = '')
	{
		$ret = '';
		if ($this->month != $this->nowMonth || $this->year != $this->nowYear || $this->ds == 'one')
		{	// Just jump to current page without a query part - that will default to today
			$ret = "<input class='button' type='button' style='width:140px;' name='cur' value='".EC_LAN_40."' onclick=\"javascript:document.location='".e_SELF."'\" />";
		}
		return $ret;
	}

	public function sc_ec_nav_but_printlists($parm = '')
	{
		global $pref;
		if (isset($pref['eventpost_printlists']) && ($pref['eventpost_printlists']>0) && USER)
		{
		  return "<input class='button' type='submit' style='width:140px;' name='printlists' value='".EC_LAN_164."' />";
		}
	}

	// Categories listing
	public function sc_ec_nav_categories($parm = '')
	{
		global $pref;
		if ($this->ourDB == NULL)
		{
			$this->ourDB = new db;
		}
		($parm == 'nosubmit') ? $insert = '' : $insert = "onchange='this.form.submit()'";
		$ret = "<select name='event_cat_ids' class='tbox' style='width:140px;' {$insert} >\n<option value='all'>".EC_LAN_97."</option>\n";

		$cal_arg = ($this->ecalClass->cal_super ? '' : " find_in_set(event_cat_class,'".USERCLASS_LIST."') AND ");
		$cal_arg .= "(event_cat_name != '".EC_DEFAULT_CATEGORY."') ";
		$this->ourDB->db_Select("event_cat", "*", $cal_arg);
		while ($row = $this->ourDB->db_Fetch())
		{
			$selected = ($row['event_cat_id'] == $this->catFilter) ? " selected='selected'" : '';
			$ret .= "<option class='tbox' value='".$row['event_cat_id']."'{$selected}>".$row['event_cat_name']."</option>\n";
		}
		$ret .= "</select>\n";
		return $ret;
	}
	

// Event information shortcodes
//-----------------------------


	public function sc_ec_event_location($parm = '')
	{
		return $this->event['event_location'];
	}

	public function sc_ec_event_recent_icon()
	{
		return $this->sc_ec_calendar_calendar_recent_icon();
	}



	public function sc_ec_if_allday($parm= '')
	{
		if (!$this->event['event_allday']) return '';
		if (trim($parm) == '') return '';
		return $this->e107->tp->parseTemplate('{'.$parm.'}');
	}

	public function sc_ec_ifnot_allday($parm= '')
	{
		if ($this->event['event_allday']) return '';
		if (trim($parm) == '') return '';
		return $this->e107->tp->parseTemplate('{'.$parm.'}');
	}

	public function sc_ec_ifnot_sameday($parm= '')
	{
		if (intval($this->event['event_end']/86400) == intval($this->event['event_start']/86400)) return '';
		if (!$this->event['event_allday']) return '';
		if (trim($parm) == '') return;
		return $this->e107->tp->parseTemplate('{'.$parm.'}');
	}

	public function sc_ec_if_sameday($parm= '')
	{
		if (intval($this->event['event_end']/86400) != intval($this->event['event_start']/86400)) return '';
		if (!$this->event['event_allday']) return '';
		if (trim($parm) == '') return;
		return $this->e107->tp->parseTemplate('{'.$parm.'}');
	}



// Event mailout shortcodes
//--------------------------
	public function sc_ec_mail_heading_date($parm)
	{
		if (isset($parm) && ($parm !== ""))
		{
			return strftime($parm,$this->event['event_start']);
		}
		else
		{
			return $this->ecalClass->event_date_string($this->event['event_start']);
		}
	}


	public function sc_ec_mail_date_start($parm)
	{
		return $this->sc_ec_mail_heading_date($parm);
	}


	public function sc_ec_mail_date_start_allday($parm)
	{
		if ($this->event['event_allday'] != 1) return '';
		return $this->sc_ec_mail_heading_date($parm);
	}


	public function sc_ec_mail_date_start_timed($parm)
	{
		if ($this->event['event_allday'] == 1) return '';
		return $this->sc_ec_mail_heading_date($parm);
	}


	public function sc_ec_mail_time_start($parm)
	{
		if ($this->event['event_allday'] == 1) return '';
		return $this->ecalClass->time_string($this->event['event_start']);
	}


	public function sc_ec_mail_date_end($parm = '')
	{
		if ($this->event['event_allday'] ||($this->event['event_end'] == $this->event['event_start'])) return '';
		if ($parm !== '')
		{
			return strftime($parm,$this->event['event_end']);
		}
		return $this->ecalClass->event_date_string($this->event['event_end']);
	}



	public function sc_ec_mail_time_end($parm = '')
	{
		if ($this->event['event_allday'] ||($this->event['event_end'] == $this->event['event_start'])) return '';
		$endds = $ecal_class->time_string($this->event['event_end']);
		return $endds;
	}


	public function sc_ec_mail_title($parm = '')
	{
		return $this->event['event_title'];
	}


	public function sc_ec_mail_id($parm = '')
	{
		return 'calevent'.$this->event['event_id'];
	}


	public function sc_ec_mail_details($parm = '')
	{
		return $this->e107->tp->toHTML($this->event['event_details'], TRUE,'E_BODY');
	}



	public function sc_ec_mail_category($parm = '')
	{
		return $this->event['event_cat_name'];
	}




	public function sc_ec_mail_contact($parm = '')
	{
		if ($this->event['event_contact'] == '') return '';
		return $this->e107->tp->toHTML($this->event['event_contact'],TRUE,'LINKTEXT');
	}


	public function sc_ec_mail_thread($parm = '')
	{
		return $this->event['event_thread'];
	}


	public function sc_ec_mail_link($parm = '')
	{
		$cal_dayarray = getdate($this->event['event_start']);
		$cal_linkut = mktime(0 , 0 , 0 , $cal_dayarray['mon'], $cal_dayarray['mday'], $cal_dayarray['year']).".one";  // ALways need "one"
		return ' '.SITEURLBASE.e_PLUGIN_ABS.'calendar_menu/event.php?'.$cal_linkut.' ';
	}


	public function sc_ec_mail_short_date($parm = '')
	{
		return $this->ecalClass->next_date_string($this->event['event_start']);
	}


	// Codes can be used to return a LAN to help with multi-language
	public function sc_ec_mail_subject($parm = '')
	{
		return EC_MAILOUT_SUBJECT;
	}


//------------------------------------------
// CALENDAR CALENDAR - 'Big' calendar
//------------------------------------------
	public function sc_ec_calendar_calendar_header_day($parm = '')
	{
		global $pref;
		if(isset($pref['eventpost_lenday']) && $pref['eventpost_lenday'])
		{
		  return "<strong>".$this->e107->tp->text_truncate($this->headerDay,$pref['eventpost_lenday'],'')."</strong>";
		}
		else
		{
		  return "<strong>".$this->headerDay."</strong>";
		}
	}


	public function sc_ec_calendar_calendar_day_today_heading()
	{
		return "<b><a href='".e_PLUGIN_ABS."calendar_menu/event.php?".$this->todayStart."'>".$this->days[($this->curDay-1)]."</a></b> <span class='smalltext'>[".EC_LAN_TODAY."]</span>";
	}


	public function sc_ec_calendar_calendar_day_event_heading()
	{
		return "<a href='".e_PLUGIN_ABS."calendar_menu/event.php?".$this->todayStart.".one'>".$this->days[($this->curDay-1)]."</a>";
	}


	public function sc_ec_calendar_calendar_day_empty_heading()
	{
		return "<a href='".e_PLUGIN_ABS."calendar_menu/event.php?".$this->todayStart."'>".$this->days[($this->curDay-1)]."</a>";
	}


	public function sc_ec_calendar_calendar_recent_icon()
	{
		if (!isset($this->event['is_recent'])) return '';
		if (!$this->event['startofevent']) return '';		// Only display on first day of multi-day events
		if (is_readable(EC_RECENT_ICON))
		{
			return "<img src='".EC_RECENT_ICON_ABS."' alt='' /> ";
		}
		return "R";
	}


	public function sc_ec_event_page_title()
	{
		switch ($this->ds)
		{
			case 'one' : return EC_LAN_80.': '.$this->day.' '.$this->months[$this->month-1];
//			case 'event' : return EC_LAN_122.': '.$this->day.' '.$this->months[$this->month-1];
			case 'event' : return EC_LAN_122;
			default : return EC_LAN_80;
		}
	}


	public function sc_ec_showevent_image()
	{
		//TODO review bullet
		$img = '';
		if($this->event['event_cat_icon'] && file_exists(e_PLUGIN.'calendar_menu/images/'.$this->event['event_cat_icon']))
		{
			$img = "<img style='border:0' src='".e_PLUGIN_ABS.'calendar_menu/images/'.$this->event['event_cat_icon']."' alt='' height='".$this->event['imagesize']."' width='".$this->event['imagesize']."' />";
		}
		elseif(defined('BULLET'))
		{
			$img = '<img src="'.THEME_ABS.'images/'.BULLET.'" alt="" class="icon" />';
		}
		elseif(file_exists(THEME.'images/bullet2.gif'))
		{
			$img = '<img src="'.THEME_ABS.'images/bullet2.gif" alt="" class="icon" />';
		}
		return $img;
	}


	public function sc_ec_showevent_indicat()
	{
		return $this->event['indicat'];
	}



	public function sc_ec_showevent_heading()
	{
		$linkut = mktime(0 , 0 , 0 , $this->month, $this->curDay, $this->year);
		$show_title = $this->e107->tp->toHTML($this->event['event_title'],FALSE,'TITLE');	// Remove entities in case need to truncate
		if(isset($this->event['fulltopic']) && !$this->event['fulltopic'])
		{
		  $show_title = $this->e107->tp->text_truncate($show_title, 10, '...');
		}
		if($this->event['startofevent'])
		{
		  return "<b><a title='{$this->event['event_title']}' href='".e_PLUGIN_ABS.'calendar_menu/event.php?'.$linkut.'.event.'.$this->event['event_id']."'><span class='mediumtext'>".$show_title."</span></a></b>";
		}
		else
		{
		  return "<a title='{$this->event['event_title']}' href='".e_PLUGIN_ABS.'calendar_menu/event.php?'.$linkut.'.event.'.$this->event['event_id']."'><span class='smalltext'>".$show_title."</span></a>";
		}
	}


	public function sc_ec_eventlist_caption()
	{
		$ret = '';
		if ($this->ds == 'one')
		{
			$ret = EC_LAN_111.$this->months[$this->month-1].' '.$this->day;
		}
		elseif ($this->ds != 'event')
		{
			$ret = EC_LAN_112.$this->months[$this->month-1];
		}
		return $ret;
	}


//---------------------------------------------------
// 	EVENT SHOWEVENT (Detail of individual events)
//---------------------------------------------------

	public function sc_ec_event_heading_date()
	{
		return $this->ecalClass->event_date_string($this->event['event_start']);
	}

	// Same code as previous
	public function sc_ec_event_date_start()
	{
		return $this->ecalClass->event_date_string($this->event['event_start']);
	}


	public function sc_ec_event_time_start()
	{
		if ($this->event['event_allday'] == 1) return '';
		return $this->ecalClass->time_string($this->event['event_start']);
	}


	public function sc_ec_event_date_end()
	{
		if ($this->event['event_end'] == $this->event['event_start']) return '';
		return $this->ecalClass->event_date_string($this->event['event_end']);
	}


	public function sc_ec_event_time_end()
	{
		if ($this->event['event_allday'] ||($this->event['event_end'] == $this->event['event_start'])) return '';
		return $this->ecalClass->time_string($this->event['event_end']);
	}


	public function sc_ec_event_title()
	{
		return $this->event['event_title'];
	}


	public function sc_ec_event_cat_icon()
	{
		if ($this->event['event_cat_icon'] && is_readable(e_PLUGIN.'calendar_menu/images/'.$this->event['event_cat_icon']))
		{
			return "<img src='".e_PLUGIN_ABS."calendar_menu/images/".$this->event['event_cat_icon']."' alt='' /> ";
		}
		return '';
	}


	public function sc_ec_event_id()
	{
		return 'calevent'.$this->event['event_id'];
	}


	public function sc_ec_event_displaystyle()
	{	// Returns initial state of expandable blocks
		if (($this->ds=='event') || ($this->ds=='one'))
		{
			return '';	// Let block display
		}
		return 'display: none; ';
	}


	/**
	 * Display class for event display block - to manage expansion/contraction
	 * When displaying a single event, or a single day's events, block to be expanded
	 * For event lists, block to be contracted
	 * 
	 * @param int $param - optional supplementary list of classes to apply
	 *
	 * @return string - 
	 */
	public function sc_ec_event_displayclass($parm='')
	{	
		if (($this->ds=='event') || ($this->ds=='one'))
		{	// Single event or one day's events - block expanded
			return " class='{$parm}'";
		}
		return " class='e-show-if-js e-hideme {$parm}'";	// Block contracted
//		return " class='e-hide-if-js e-showme {$parm}'";	// Block contracted
	}


	public function sc_ec_event_details()
	{
		return $this->e107->tp->toHTML($this->event['event_details'], TRUE, 'BODY');
	}


	public function sc_ec_event_category()
	{
		return $this->event['event_cat_name'];
	}


	public function sc_ec_event_author()
	{
		$lp = explode(".", $this->event['event_author'],2);		// Split into userid.username
		if (preg_match("/[0-9]+/", $lp[0]))
		{
			$event_author_id = $lp[0];
			$event_author_name = $lp[1];
		}
		if(USER)
		{
			return "<a href='".e_HTTP."user.php?id.".$event_author_id."'>".$event_author_name."</a>";
		}
		return $event_author_name;
	}


	public function sc_ec_event_contact()
	{
		if ($this->event['event_contact'] == '') return '';
		$tm = $this->event['event_contact'];
		if (strpos($tm,'[') === FALSE)
		{	// Add a bbcode if none exists
			$tm = '[link=mailto:'.trim($tm).']'.substr($tm,0,strpos($tm,'@')).'[/link]';
		}
		return $this->e107->tp->toHTML($tm,TRUE,'LINKTEXT');	// Return obfuscated email link
	}


	public function sc_ec_event_thread()
	{
		if (isset($this->event['event_thread']) && ($this->event['event_thread'] != ''))
		{
		return "<a href='{$this->event['event_thread']}'><img src='".e_IMAGE_ABS."admin_images/forums_32.png' alt='' style='border:0; vertical-align:middle;' width='16' height='16' /></a> <a href='{$this->event['event_thread']}'>".EC_LAN_39."</a>";
		}
		return '';
	}


	public function sc_ec_event_options()
	{
		global $pref;
		$event_author_name = strstr(varset($this->event['event_author'],'0.??'),'.');
		if (USERNAME == $event_author_name || $this->ecalClass->cal_super || check_class($pref['eventpost_admin']))
		{
			return "<a href='".e_PLUGIN_ABS."calendar_menu/event.php?ed.".$this->event['event_id']."'><img class='icon S16' src='".e_IMAGE_ABS."admin_images/edit_16.png' title='".EC_LAN_35."' alt='".EC_LAN_35 . "'/></a>&nbsp;&nbsp;<a href='".e_PLUGIN_ABS.'calendar_menu/event.php?de.'.$this->event['event_id']."'><img style='border:0;' src='".e_IMAGE_ABS."admin_images/delete_16.png' title='".EC_LAN_36."' alt='".EC_LAN_36."'/></a>";
		}
	}


	public function sc_ec_ec_event_link()
	{
		$cal_dayarray = getdate($this->event['event_start']);
		$cal_linkut = mktime(0 , 0 , 0 , $cal_dayarray['mon'], $cal_dayarray['mday'], $cal_dayarray['year']).'.one';  // ALways need "one"
		return ' '.e_PLUGIN_ABS.'calendar_menu/event.php?'.$cal_linkut.' ';
	}


	public function sc_ec_event_event_date_time()
	{
		$et = 0;
		if (intval($this->event['event_end']/86400) == intval($this->event['event_start']/86400)) $et += 1;
		if ($this->event['event_allday']) $et += 2;
		if (is_array($this->eventDisplayCodes))
		{
			return $this->e107->tp->parseTemplate($this->eventDisplayCodes[$et]);
		}
		return '--** No template set **--';
	}


	public function sc_ec_event_short_date()
	{
		return $this->ecalClass->next_date_string($this->event['event_start']);
	}


//------------------------------------------
// EVENT ARCHIVE (list of next events at bottom of event list)
//------------------------------------------

	public function sc_ec_eventarchive_caption()
	{
		if ($this->numEvents == 0) 
		{
			return EC_LAN_137;
		}
		return str_replace('-NUM-', $this->numEvents, EC_LAN_62);
	}


	public function sc_ec_eventarchive_date()
	{
		$startds = $this->ecalClass->event_date_string($this->event['event_start']);
		return "<a href='".e_PLUGIN_ABS."calendar_menu/event.php?".$this->event['event_start'].'.event.'.$this->event['event_id']."'>".$startds."</a>";
	}


	public function sc_ec_eventarchive_details()
	{
		$number = 40;
		$rowtext = $this->e107->tp->toHTML($this->event['event_details'], TRUE, 'BODY');
		$rowtext = strip_tags($rowtext);
		$words = explode(' ', $rowtext);
		$ret = implode(' ', array_slice($words, 0, $number));
		if(count($words) > $number)
		{
			$ret .= ' '.EC_LAN_133.' ';
		}
		return $ret;
	}


	public function sc_ec_eventarchive_empty()
	{
		return EC_LAN_37;
	}


	public function sc_ec_eventarchive_heading()
	{
		return $this->event['event_title'];
	}



//   FORTHCOMING EVENTS MENU
//---------------------------

	function sc_ec_next_event_recent_icon()
	{
		global $pref;
		if (!$pref['eventpost_fe_showrecent']) return;
		if (!isset($this->event['is_recent'])) return;
		if (is_readable(EC_RECENT_ICON))
		{
			return "<img src='".EC_RECENT_ICON_ABS."' alt='' /> ";
		}
		return '';
	}


	public function sc_ec_next_event_time()
	{
		if ($this->event['event_allday'] != 1) 
		{
		return $this->ecalClass->time_string($this->event['event_start']);
		}
		return '';
	}


	public function sc_ec_next_event_date()
	{
		return $this->ecalClass->next_date_string($this->event['event_start']);
	}


	public function sc_ec_next_event_title()
	{
		global $pref;
		if (isset($pref['eventpost_namelink']) && ($pref['eventpost_namelink'] == '2') && (isset($this->event['event_thread']) && ($this->event['event_thread'] != '')))
		{
			$fe_event_title = "<a href='".$this->event['event_thread']."'>";
		}
		else
		{ 
			$fe_event_title = "<a href='".e_PLUGIN_ABS."calendar_menu/event.php?".$this->event['event_start'].".event.".$this->event['event_id']."'>";
		}
		$fe_event_title .= $this->event['event_title']."</a>";
		return $fe_event_title;
	}


	public function sc_ec_next_event_icon()
	{
		global $pref;
		$fe_icon_file = '';
		if ($pref['eventpost_showcaticon'] == 1)
		{
			if($this->event['event_cat_icon'] && is_readable(e_PLUGIN.'calendar_menu/images/'.$this->event['event_cat_icon']))
			{
				$fe_icon_file = e_PLUGIN_ABS.'calendar_menu/images/'.$this->event['event_cat_icon'];
			}
			elseif(defined('BULLET'))
			{
				$fe_icon_file = THEME_ABS.'images/'.BULLET;
			}
			elseif(file_exists(THEME.'images/bullet2.gif'))
			{
				$fe_icon_file = THEME_ABS.'images/bullet2.gif';
			}
		}
		return $fe_icon_file;
	}


	public function sc_ec_next_event_gap()
	{
		if ($this->numEvents) return '<br /><br />'; 	// Return a newline as a gap on all but last item
		return '';
	}




// Specific to the 'listings' page
//--------------------------------

	public function sc_ec_pr_list_title()
	{
		return $this->printVars['lt'];
	}



	public function sc_ec_pr_cat_list()
	{
		if (is_array($this->printVars['cat']))
		{
		return implode(', ',$this->printVars['cat']);
		}
		return $this->printVars['cat'];
	}

	public function sc_ec_pr_change_year()
	{
		if (!$this->changeFlags['yc']) return '';
		$thisevent_start_date = $this->ecalClass->gmgetdate($this->event['event_start']);
		return $thisevent_start_date['year'];
	}

	public function sc_ec_pr_change_month()
	{
		if (!$this->changeFlags['mc']) return '';
		$thisevent_start_date = $this->ecalClass->gmgetdate($this->event['event_start']);
		return $thisevent_start_date['month'];
	}


	public function sc_ec_pr_list_start($parm = '')
	{
		if ($parm)
		{
			return $this->ecalClass->event_date_string($this->printVars['sd']);
		}
		return strftime($parm,$this->printVars['sd']);
	}

	public function sc_ec_pr_list_end($parm = '')
	{
		if ($parm)
		{
			return $this->ecalClass->event_date_string($this->printVars['ed']);
		}
		return strftime($parm,$this->printVars['ed']);
	}


	public function sc_ec_now_date($parm = '')
	{
		if ($parm == '') return $this->ecalClass->event_date_string(time());
		return strftime($parm,time());
	}

	public function sc_ec_now_time($parm = '')
	{
		if ($parm == '') return $this->ecalClass->time_string(time());
		return strftime($parm,time());
	}


	public function sc_ec_print_button()
	{
		if ($this->printVars['ot'] != 'print') return;
		return "<input type='button' value='".EC_LAN_162."' onClick='window.print()' />";
	}


	public function sc_ec_if_print($parm = '')
	{
		if ($this->printVars['ot'] != 'print') return;
		if (trim($parm) == '') return;
		return $this->e107->tp->parseTemplate('{'.$parm.'}');
	}

	public function sc_ec_ifnot_print($parm = '')
	{
		if ($this->printVars['ot'] == 'print') return;
		if (trim($parm) == '') return;
		return $this->e107->tp->parseTemplate('{'.$parm.'}');
	}

	public function sc_ec_if_display($parm = '')
	{
		if ($this->printVars['ot'] != 'display') return;
		if (trim($parm) == '') return;
		return $this->e107->tp->parseTemplate('{'.$parm.'}');
	}

	public function sc_ec_ifnot_display($parm = '')
	{
		if ($this->printVars['ot'] == 'display') return;
		if (trim($parm) == '') return;
		return $this->e107->tp->parseTemplate('{'.$parm.'}');
	}

	public function sc_ec_if_pdf($parm = '')
	{
		if ($this->printVars['ot'] != 'pdf') return;
		if (trim($parm) == '') return;
		return $this->e107->tp->parseTemplate('{'.$parm.'}');
	}

	public function sc_ec_ifnot_pdf($parm = '')
	{
		if ($this->printVars['ot'] == 'pdf') return;
		if (trim($parm) == '') return;
		return $this->e107->tp->parseTemplate('{'.$parm.'}');
	}

}	// END - shortcode class


?>
