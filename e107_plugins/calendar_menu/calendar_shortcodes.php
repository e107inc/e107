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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/calendar_shortcodes.php,v $
 * $Revision: 1.14 $
 * $Date: 2009-11-18 01:05:23 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$calendar_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
/*
// TIME SWITCH BUTTONS ------------------------------------------------
SC_BEGIN EC_PREV_MONTH
	global $previous, $months, $prevmonth;
	return "<a href='".e_SELF."?".$previous."'>&lt;&lt; ".$months[($prevmonth-1)]."</a>";
SC_END

SC_BEGIN EC_NEXT_MONTH
	global $next, $months, $nextmonth;
	return "<a href='".e_SELF."?".$next."'> ".$months[($nextmonth-1)]." &gt;&gt;</a>";
SC_END

SC_BEGIN EC_CURRENT_MONTH
	global $EC_CURRENT_MONTH, $pref, $months, $month, $year;
	if($pref['eventpost_dateformat'] == 'my') 
	{
	  $EC_CURRENT_MONTH = $months[($month-1)]." ".$year;
	} 
	else 
	{
	  $EC_CURRENT_MONTH = $year." ".$months[($month-1)];
	}
	return $EC_CURRENT_MONTH;
SC_END

SC_BEGIN EC_PREV_YEAR
	global $prevlink, $py;
	return "<a href='".e_SELF."?".$prevlink."'>&lt;&lt; ".$py."</a>";
SC_END

SC_BEGIN EC_NEXT_YEAR
	global $nextlink, $ny;
	return "<a href='".e_SELF."?".$nextlink."'>".$ny." &gt;&gt;</a>";
SC_END

SC_BEGIN EC_MONTH_LIST
	global $EC_MONTH_LIST, $year, $monthjump, $monthabb;
	$EC_MONTH_LIST = "";
	for ($ii = 0; $ii < 12; $ii++)
	{
    	$m = $ii + 1;
    	$monthjump = mktime(0, 0, 0, $m, 1, $year);
		$EC_MONTH_LIST .= "<a href='".e_SELF."?".$monthjump."'>".$monthabb[$ii]."</a> &nbsp;";
	}
	return $EC_MONTH_LIST;
SC_END



// NAVIGATION BUTTONS ------------------------------------------------
SC_BEGIN EC_NAV_BUT_ALLEVENTS
	$allevents = (e_PAGE == "event.php" ? EC_LAN_96 : EC_LAN_93);
	return "<input class='button' type='submit' style='width:140px;' name='viewallevents' value='".$allevents."' title='".$allevents."' />";
SC_END

SC_BEGIN EC_NAV_BUT_VIEWCAT
	//return "<input type='hidden' name='do' value='vc' /><input class='button' type='submit' style='width:140px;' name='viewcat' value='".EC_LAN_92."' />";
	return "<input type='hidden' name='do' value='vc' />";
SC_END

SC_BEGIN EC_NAV_BUT_SUBSCRIPTION
	global $pref;
	if (isset($pref['eventpost_asubs']) && ($pref['eventpost_asubs']>0) && USER)
	{
	  return "<input class='button' type='submit' style='width:140px;' name='subs' value='".EC_LAN_123."' />";
	}
SC_END

SC_BEGIN EC_NAV_BUT_PRINTLISTS
	global $pref;
	if (isset($pref['eventpost_printlists']) && ($pref['eventpost_printlists']>0) && USER)
	{
	  return "<input class='button' type='submit' style='width:140px;' name='printlists' value='".EC_LAN_164."' />";
	}
SC_END

SC_BEGIN EC_NAV_BUT_ENTEREVENT
	global $EC_NAV_BUT_ENTEREVENT, $pref, $prop, $cal_super;
	$EC_NAV_BUT_ENTEREVENT = "<input type='hidden' name='enter_new_val' value='".$prop."' />";
	if ($cal_super || check_class($pref['eventpost_admin']))
	{
    	$EC_NAV_BUT_ENTEREVENT .= "<input class='button' type='submit' style='width:140px;' name='doit' value='".EC_LAN_94."' />";
	}
	return $EC_NAV_BUT_ENTEREVENT;
SC_END

SC_BEGIN EC_NAV_LINKCURRENTMONTH
	global $EC_NAV_LINKCURRENTMONTH, $month, $nowmonth, $year, $nowyear, $current, $ds;
	$EC_NAV_LINKCURRENTMONTH = "";
	if ($month != $nowmonth || $year != $nowyear || $ds == 'one'){
		$EC_NAV_LINKCURRENTMONTH = "<input class='button' type='button' style='width:140px;' name='cur' value='".EC_LAN_40."' onclick=\"javascript:document.location='".e_SELF."?$current'\" />";
	}
	return $EC_NAV_LINKCURRENTMONTH;
SC_END

SC_BEGIN EC_NAV_CATEGORIES
	global $EC_NAV_CATEGORIES, $sql, $pref, $_POST, $cal_super, $cat_filter;
	(isset($parm) && ($parm == 'nosubmit')) ? $insert = '' : $insert = "onchange='this.form.submit()'";
	$EC_NAV_CATEGORIES = "<select name='event_cat_ids' class='tbox' style='width:140px;' {$insert} >\n<option value='all'>".EC_LAN_97."</option>\n";
	$event_cat_id = ( isset($_POST['event_cat_ids']) && $_POST['event_cat_ids'] ? $_POST['event_cat_ids'] : null);

	$cal_arg = ($cal_super ? "" : " find_in_set(event_cat_class,'".USERCLASS_LIST."') AND ");
	$cal_arg .= "(event_cat_name != '".EC_DEFAULT_CATEGORY."') ";
	$sql->db_Select("event_cat", "*", $cal_arg);
	while ($row = $sql->db_Fetch()){
 	   if ($row['event_cat_id'] == $cat_filter){
 	       $EC_NAV_CATEGORIES .= "<option class='tbox' value='".$row['event_cat_id']."' selected='selected'>".$row['event_cat_name']."</option>\n";
  	  }else{
    	    $EC_NAV_CATEGORIES .= "<option value='".$row['event_cat_id']."'>".$row['event_cat_name']."</option>\n";
    	}
	}
	$EC_NAV_CATEGORIES .= "</select>\n";
	return $EC_NAV_CATEGORIES;
SC_END



// CALENDAR SHOWEVENT ------------------------------------------------------------
SC_BEGIN EC_SHOWEVENT_IMAGE
	//TODO review bullet
	global $ev;

	$img = '';
	if($ev['event_cat_icon'] && file_exists(e_PLUGIN."calendar_menu/images/".$ev['event_cat_icon']))
	{
	  $img = "<img style='border:0' src='".e_PLUGIN_ABS."calendar_menu/images/".$ev['event_cat_icon']."' alt='' height='".$ev['imagesize']."' width='".$ev['imagesize']."' />";
	}
	elseif(defined('BULLET'))
	{
		$img = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
	}
	elseif(file_exists(THEME.'images/bullet2.gif'))
	{
		$img = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
	}
	return $img;
SC_END

SC_BEGIN EC_SHOWEVENT_INDICAT
	global $ev;
	return $ev['indicat'];
SC_END

SC_BEGIN EC_SHOWEVENT_HEADING
	global $ev, $datearray, $c, $tp;
	$linkut = mktime(0 , 0 , 0 , $datearray['mon'], $c, $datearray['year']);
	$show_title = $tp->toHTML($ev['event_title'],FALSE,'TITLE');	// Remove entities in case need to truncate
	if(isset($ev['fulltopic']) && !$ev['fulltopic'])
	{
	  $show_title = $tp->text_truncate($show_title, 10, "...");
	}
	if($ev['startofevent'])
	{
	  return "<b><a title='{$ev['event_title']}' href='".e_PLUGIN."calendar_menu/event.php?".$linkut.".event.".$ev['event_id']."'><span class='mediumtext'>".$show_title."</span></a></b>";
	}
	else
	{
	  return "<a title='{$ev['event_title']}' href='".e_PLUGIN."calendar_menu/event.php?".$linkut.".event.".$ev['event_id']."'><span class='smalltext'>".$show_title."</span></a>";
	}
SC_END



//------------------------------------------
// CALENDAR CALENDAR - 'Big' calendar
//------------------------------------------
SC_BEGIN EC_CALENDAR_CALENDAR_HEADER_DAY
	global $day, $pref, $tp;
	if(isset($pref['eventpost_lenday']) && $pref['eventpost_lenday'])
	{
	  return "<strong>".$tp->text_truncate($day,$pref['eventpost_lenday'],'')."</strong>";
	}
	else
	{
 	  return "<strong>".$day."</strong>";
	}
SC_END

SC_BEGIN EC_CALENDAR_CALENDAR_DAY_TODAY_HEADING
	global $startt, $c, $days;
	return "<b><a href='".e_PLUGIN."calendar_menu/event.php?".$startt."'>".$days[($c-1)]."</a></b> <span class='smalltext'>[".EC_LAN_TODAY."]</span>";
SC_END

SC_BEGIN EC_CALENDAR_CALENDAR_DAY_EVENT_HEADING
	global $startt, $c, $days;
	return "<a href='".e_PLUGIN."calendar_menu/event.php?".$startt.".one'>".$days[($c-1)]."</a>";
SC_END

SC_BEGIN EC_CALENDAR_CALENDAR_DAY_EMPTY_HEADING
	global $startt, $c, $days;
	return "<a href='".e_PLUGIN."calendar_menu/event.php?".$startt."'>".$days[($c-1)]."</a>";
SC_END

SC_BEGIN EC_CALENDAR_CALENDAR_RECENT_ICON
  global $ev;
  if (!isset($ev['is_recent'])) return "";
  if (!$ev['startofevent']) return "";		// Only display on first day of multi-day events
//  $recent_icon = e_PLUGIN_ABS."calendar_menu/images/recent_icon.png";
  $recent_icon = EC_RECENT_ICON;
  if (file_exists($recent_icon))
	{
	  return "<img src='".$recent_icon."' alt='' /> ";
	}
  return "R";
SC_END



//------------------------------------------
// EVENT ARCHIVE (list of next events at bottom of event list)
//------------------------------------------
SC_BEGIN EC_EVENTARCHIVE_CAPTION
	global $EC_EVENTARCHIVE_CAPTION, $num;
	if ($num == 0) 
	{
		$EC_EVENTARCHIVE_CAPTION = EC_LAN_137;
	}
	else
	{
		$EC_EVENTARCHIVE_CAPTION = str_replace("-NUM-", $num, EC_LAN_62);
	}
	return $EC_EVENTARCHIVE_CAPTION;
SC_END

SC_BEGIN EC_EVENTARCHIVE_DATE
	global $EC_EVENTARCHIVE_DATE, $thisevent, $ecal_class;
	$startds = $ecal_class->event_date_string($thisevent['event_start']);
	$EC_EVENTARCHIVE_DATE = "<a href='event.php?".$thisevent['event_start'].".event.".$thisevent['event_id']."'>".$startds."</a>";
	return $EC_EVENTARCHIVE_DATE;
SC_END

SC_BEGIN EC_EVENTARCHIVE_DETAILS
	global $EC_EVENTARCHIVE_DETAILS, $thisevent, $tp;
	$number = 40;
	$rowtext = $tp->toHTML($thisevent['event_details'], TRUE, "nobreak");
	$rowtext = strip_tags($rowtext);
	$words = explode(" ", $rowtext);
	$EC_EVENTARCHIVE_DETAILS = implode(" ", array_slice($words, 0, $number));
	if(count($words) > $number){
		$EC_EVENTARCHIVE_DETAILS .= " ".EC_LAN_133." ";
	}
	return $EC_EVENTARCHIVE_DETAILS;
SC_END

SC_BEGIN EC_EVENTARCHIVE_EMPTY
	global $EC_EVENTARCHIVE_EMPTY;
	return EC_LAN_37;
SC_END

SC_BEGIN EC_EVENTARCHIVE_HEADING
	global $EC_EVENTARCHIVE_HEADING, $thisevent;
	$EC_EVENTARCHIVE_HEADING = $thisevent['event_title'];
	return $EC_EVENTARCHIVE_HEADING;
SC_END



//------------------------------------------
// 				EVENT LIST
//------------------------------------------
SC_BEGIN EC_EVENTLIST_CAPTION
	global $EC_EVENTLIST_CAPTION, $ds, $months, $selected_mon, $selected_day, $monthstart;
	if ($ds == 'one')
	{
		$EC_EVENTLIST_CAPTION = EC_LAN_111.$months[$selected_mon-1]." ".$selected_day;
	}
    elseif ($ds != 'event')
    {
		$EC_EVENTLIST_CAPTION = EC_LAN_112.$months[date("m", $monthstart)-1];
	}
	return $EC_EVENTLIST_CAPTION;
SC_END


//------------------------------------------
// 	EVENT SHOWEVENT (Detail of individual events in Event List)
//------------------------------------------
// Some of these shortcodes also used by big calendar

SC_BEGIN EC_EVENT_RECENT_ICON
  global $thisevent;
  if (!isset($thisevent['is_recent'])) return;
  $recent_icon = EC_RECENT_ICON;
  if (file_exists($recent_icon))
  {
	return "<img src='".$recent_icon."' alt='' /> ";
  }
  return "";
SC_END

SC_BEGIN EC_EVENT_HEADING_DATE
	global $thisevent, $ecal_class;
	$startds = $ecal_class->event_date_string($thisevent['event_start']);
    return $startds;
SC_END

SC_BEGIN EC_EVENT_DATE_START
	global $thisevent, $ecal_class;
	$startds = $ecal_class->event_date_string($thisevent['event_start']);
    return $startds;
SC_END

SC_BEGIN EC_EVENT_TIME_START
	global $thisevent, $ecal_class;
	if ($thisevent['event_allday'] == 1) return "";
	$startds = $ecal_class->time_string($thisevent['event_start']);
    return $startds;
SC_END

SC_BEGIN EC_EVENT_DATE_END
	global $thisevent, $ecal_class;
//	if (intval($thisevent['event_end']/86400) == intval($thisevent['event_start']/86400)) return "";  // No end date if same day
//	if ($thisevent['event_allday'] ||($thisevent['event_end'] == $thisevent['event_start'])) return "";
	if ($thisevent['event_end'] == $thisevent['event_start']) return "";
	$endds = $ecal_class->event_date_string($thisevent['event_end']);
	return $endds;
SC_END

SC_BEGIN EC_EVENT_TIME_END
	global $thisevent, $ecal_class;
	if ($thisevent['event_allday'] ||($thisevent['event_end'] == $thisevent['event_start'])) return "";
	$endds = $ecal_class->time_string($thisevent['event_end']);
	return $endds;
SC_END

SC_BEGIN EC_EVENT_TITLE
	global $thisevent, $tp;
	return $thisevent['event_title'];
SC_END

SC_BEGIN EC_EVENT_CAT_ICON
  global $thisevent;
  if ($thisevent['event_cat_icon'] && file_exists(e_PLUGIN."calendar_menu/images/".$thisevent['event_cat_icon']))
  {
	return "<img src='".e_PLUGIN_ABS."calendar_menu/images/".$thisevent['event_cat_icon']."' alt='' /> ";
  }
  else
  {
	return "";
  }
SC_END

SC_BEGIN EC_EVENT_ID
	global $thisevent;
	return "calevent".$thisevent['event_id'];
SC_END

SC_BEGIN EC_EVENT_DISPLAYSTYLE
	global $EC_EVENT_DISPLAYSTYLE, $ds;
	if (($ds=="event") || ($ds=="one")){
		$EC_EVENT_DISPLAYSTYLE = "show";
	}else{
		$EC_EVENT_DISPLAYSTYLE = "none";
	}
	return $EC_EVENT_DISPLAYSTYLE;
SC_END

SC_BEGIN EC_EVENT_DETAILS
	global $thisevent, $tp;
	return $tp->toHTML($thisevent['event_details'], TRUE, 'BODY');
SC_END

SC_BEGIN EC_EVENT_CATEGORY
	global $EC_EVENT_CATEGORY, $thisevent;
	$EC_EVENT_CATEGORY = $thisevent['event_cat_name'];
	return $EC_EVENT_CATEGORY;
SC_END

SC_BEGIN EC_EVENT_LOCATION
	global $EC_EVENT_LOCATION, $thisevent;
	if ($thisevent['event_location'] == "")
	{
	  $EC_EVENT_LOCATION = "";
	}
	else
	{
	  $EC_EVENT_LOCATION = $thisevent['event_location'];
	}
	return $EC_EVENT_LOCATION;
SC_END

SC_BEGIN EC_EVENT_AUTHOR
	global $thisevent;
    $lp = explode(".", $thisevent['event_author'],2);
    if (preg_match("/[0-9]+/", $lp[0]))
    {
      $event_author_id = $lp[0];
      $event_author_name = $lp[1];
    }
	if(USER)
	{
	  $EC_EVENT_AUTHOR = "<a href='".e_BASE."user.php?id.".$event_author_id."'>".$event_author_name."</a>";
	}
	else
	{
	  $EC_EVENT_AUTHOR = $event_author_name;
	}
	return $EC_EVENT_AUTHOR;
SC_END

SC_BEGIN EC_EVENT_CONTACT
	global $EC_EVENT_CONTACT, $thisevent,$tp;
	if ($thisevent['event_contact'] == "")
	{
	  $EC_EVENT_CONTACT = "";
	}
	else
	{
	  $tm = $thisevent['event_contact'];
	  if (strpos($tm,'[') === FALSE)
	  {
	    $tm = '[link=mailto:'.trim($tm).']'.substr($tm,0,strpos($tm,'@')).'[/link]';
	  }
	  $EC_EVENT_CONTACT = $tp->toHTML($tm,TRUE,'LINKTEXT');
	}
	return $EC_EVENT_CONTACT;
SC_END

SC_BEGIN EC_EVENT_THREAD
  global  $thisevent, $ec_images_path;
  return (isset($thisevent['event_thread']) && ($thisevent['event_thread'] != "")) ? "<a href='{$thisevent['event_thread']}'><img src='".$ec_images_path."admin_images/forums_32.png' alt='' style='border:0; vertical-align:middle;' width='16' height='16' /></a> <a href='{$thisevent['event_thread']}'>".EC_LAN_39."</a>" : "";
SC_END

SC_BEGIN EC_EVENT_OPTIONS
	global $EC_EVENT_OPTIONS, $thisevent, $event_author_name, $cal_super, $pref, $ec_images_path;
	if (USERNAME == $event_author_name || $cal_super || check_class($pref['eventpost_admin']))
	{
	  $EC_EVENT_OPTIONS = "<a href='event.php?ed.".$thisevent['event_id']."'><img class='icon S16' src='".e_IMAGE."admin_images/edit_16.png' title='".EC_LAN_35."' alt='".EC_LAN_35 . "'/></a>&nbsp;&nbsp;<a href='".e_PLUGIN."calendar_menu/event.php?de.".$thisevent['event_id']."'><img style='border:0;' src='".e_IMAGE."admin_images/delete_16.png' title='".EC_LAN_36."' alt='".EC_LAN_36."'/></a>";
	}
	return $EC_EVENT_OPTIONS;
SC_END

SC_BEGIN EC_EC_EVENT_LINK
  global $thisevent, $PLUGINS_DIRECTORY;
  $cal_dayarray = getdate($thisevent['event_start']);
  $cal_linkut = mktime(0 , 0 , 0 , $cal_dayarray['mon'], $cal_dayarray['mday'], $cal_dayarray['year']).".one";  // ALways need "one"
//  return " ".SITEURL.$PLUGINS_DIRECTORY. "calendar_menu/event.php?".$cal_linkut." ";
  return " ".$pref['siteurl'].$PLUGINS_DIRECTORY. "calendar_menu/event.php?".$cal_linkut." ";
SC_END


SC_BEGIN EC_EVENT_EVENT_DATE_TIME
  global $thisevent, $tp, $EVENT_EVENT_DATETIME;
  $et = 0;
  if (intval($thisevent['event_end']/86400) == intval($thisevent['event_start']/86400)) $et += 1;
  if ($thisevent['event_allday']) $et += 2;
  return $tp->parseTemplate($EVENT_EVENT_DATETIME[$et]);
SC_END


SC_BEGIN EC_EVENT_SHORT_DATE
  global $thisevent, $ecal_class;
  return $ecal_class->next_date_string($thisevent['event_start']);
SC_END


SC_BEGIN EC_IFNOT_ALLDAY
  global $thisevent, $tp;
  if ($thisevent['event_allday']) return;
  if (trim($parm) == "") return;
  return $tp->parseTemplate('{'.$parm.'}');
SC_END

SC_BEGIN EC_IF_ALLDAY
  global $thisevent, $tp;
  if (!$thisevent['event_allday']) return;
  if (trim($parm) == "") return;
  return $tp->parseTemplate('{'.$parm.'}');
SC_END


SC_BEGIN EC_IFNOT_SAMEDAY
  global $thisevent, $tp;
  if (intval($thisevent['event_end']/86400) == intval($thisevent['event_start']/86400)) return "";
  if (!$thisevent['event_allday']) return;
  if (trim($parm) == "") return;
  return $tp->parseTemplate('{'.$parm.'}');
SC_END

SC_BEGIN EC_IF_SAMEDAY
  global $thisevent, $tp;
  if (intval($thisevent['event_end']/86400) != intval($thisevent['event_start']/86400)) return "";
  if (!$thisevent['event_allday']) return;
  if (trim($parm) == "") return;
  return $tp->parseTemplate('{'.$parm.'}');
SC_END


//   FORTHCOMING EVENTS MENU
//--------------------------------------------

SC_BEGIN EC_NEXT_EVENT_RECENT_ICON
  global $cal_row;
  if (!$pref['eventpost_fe_showrecent']) return;
  if (!isset($cal_row['is_recent'])) return;
  $recent_icon = EC_RECENT_ICON;
  if (file_exists($recent_icon))
	{
	  return "<img src='".$recent_icon."' alt='' /> ";
	}
  return "";
SC_END

SC_BEGIN EC_NEXT_EVENT_TIME
  global $cal_row, $ecal_class;
  if ($cal_row['event_allday'] != 1) return $ecal_class->time_string($cal_row['event_start']); else return '';
SC_END

SC_BEGIN EC_NEXT_EVENT_DATE
  global $cal_row, $ecal_class;
  return $ecal_class->next_date_string($cal_row['event_start']);
SC_END

SC_BEGIN EC_NEXT_EVENT_TITLE
  global $pref, $cal_row;
  if (isset($pref['eventpost_namelink']) && ($pref['eventpost_namelink'] == '2') && (isset($cal_row['event_thread']) && ($cal_row['event_thread'] != "")))
  {
    $fe_event_title = "<a href='".$cal_row['event_thread']."'>";
  }
  else
  { 
    $fe_event_title = "<a href='".e_PLUGIN."calendar_menu/event.php?".$cal_row['event_start'].".event.".$cal_row['event_id']."'>";
  }
  $fe_event_title .= $cal_row['event_title']."</a>";
  return $fe_event_title;
SC_END

SC_BEGIN EC_NEXT_EVENT_ICON
  global $pref, $cal_row;
  $fe_icon_file = "";
  if ($pref['eventpost_showcaticon'] == 1)
  {
		if($cal_row['event_cat_icon'] && file_exists($ecal_dir."images/".$cal_row['event_cat_icon']))
		{
		  $fe_icon_file = $ecal_dir."images/".$cal_row['event_cat_icon'];
		}
		elseif(defined('BULLET'))
		{
			$fe_icon_file = THEME.'images/'.BULLET;
		}
		elseif(file_exists(THEME.'images/bullet2.gif'))
		{
			$fe_icon_file = THEME.'images/bullet2.gif';
		}
  }
  return $fe_icon_file;
SC_END

SC_BEGIN EC_NEXT_EVENT_GAP
  global $cal_totev;
  if ($cal_totev) return "<br /><br />"; else return "";
SC_END


// Event mailout shortcodes
//--------------------------
SC_BEGIN EC_MAIL_HEADING_DATE
	global $thisevent, $ecal_class;
  if (isset($parm) && ($parm !== ""))
  {
    return strftime($parm,$thisevent['event_start']);
  }
  else
  {
    return $ecal_class->event_date_string($thisevent['event_start']);
  }
SC_END

SC_BEGIN EC_MAIL_DATE_START
	global $thisevent, $ecal_class;
  if (isset($parm) && ($parm !== ""))
  {
    return strftime($parm,$thisevent['event_start']);
  }
  else
  {
    return $ecal_class->event_date_string($thisevent['event_start']);
  }
SC_END

SC_BEGIN EC_MAIL_TIME_START
	global $thisevent, $ecal_class;
	if ($thisevent['event_allday'] == 1) return "";
	$startds = $ecal_class->time_string($thisevent['event_start']);
    return $startds;
SC_END

SC_BEGIN EC_MAIL_DATE_END
	global $thisevent, $ecal_class;
	if ($thisevent['event_allday'] ||($thisevent['event_end'] == $thisevent['event_start'])) return "";
  if (isset($parm) && ($parm !== ""))
  {
    return strftime($parm,$thisevent['event_end']);
  }
  else
  {
    return $ecal_class->event_date_string($thisevent['event_end']);
  }
SC_END

SC_BEGIN EC_MAIL_TIME_END
	global $thisevent, $ecal_class;
	if ($thisevent['event_allday'] ||($thisevent['event_end'] == $thisevent['event_start'])) return "";
	$endds = $ecal_class->time_string($thisevent['event_end']);
	return $endds;
SC_END

SC_BEGIN EC_MAIL_TITLE
	global $thisevent;
	return $thisevent['event_title'];
SC_END


SC_BEGIN EC_MAIL_ID
	global $thisevent;
	return "calevent".$thisevent['event_id'];
SC_END


SC_BEGIN EC_MAIL_DETAILS
	global $EVENT_DETAILS, $thisevent, $tp;
	return $tp->toHTML($thisevent['event_details'], TRUE,'BODY, no_make_clickable');
SC_END


SC_BEGIN EC_MAIL_CATEGORY
	global $EVENT_CATEGORY, $thisevent;
	$EVENT_CATEGORY = $thisevent['event_cat_name'];
	return $EVENT_CATEGORY;
SC_END

SC_BEGIN EC_MAIL_LOCATION
	global $EVENT_LOCATION, $thisevent;
	if ($thisevent['event_location'] == "")
	{
	  $EVENT_LOCATION = "";
	}
	else
	{
	  $EVENT_LOCATION = $thisevent['event_location'];
	}
	return $EVENT_LOCATION;
SC_END


SC_BEGIN EC_MAIL_CONTACT
	global $MAIL_CONTACT, $thisevent,$tp;
	if ($thisevent['event_contact'] == "")
	{
	  $MAIL_CONTACT = "";
	}
	else
	{
	  $MAIL_CONTACT = $tp->toHTML($thisevent['event_contact'],TRUE,"LINKTEXT");
	}
	return $MAIL_CONTACT;
SC_END

SC_BEGIN EC_MAIL_THREAD
  global $thisevent;
  return (isset($thisevent['event_thread']) && ($thisevent['event_thread'] != "")) ? $thisevent['event_thread'] : "";
SC_END

SC_BEGIN EC_MAIL_LINK
  global $thisevent, $PLUGINS_DIRECTORY, $pref;
  $cal_dayarray = getdate($thisevent['event_start']);
  $cal_linkut = mktime(0 , 0 , 0 , $cal_dayarray['mon'], $cal_dayarray['mday'], $cal_dayarray['year']).".one";  // ALways need "one"
//  return " ".SITEURL.$PLUGINS_DIRECTORY. "calendar_menu/event.php?".$cal_linkut." ";
  return " ".$pref['siteurl'].$PLUGINS_DIRECTORY. "calendar_menu/event.php?".$cal_linkut." ";
SC_END


SC_BEGIN EC_MAIL_SHORT_DATE
  global $thisevent, $ecal_class;
  return $ecal_class->next_date_string($thisevent['event_start']);
SC_END

// Codes can be used to return a LAN to help with multi-language
SC_BEGIN EC_MAIL_SUBJECT
  return EC_MAILOUT_SUBJECT;
SC_END


// Specific to the 'listings' page
//--------------------------------
SC_BEGIN EC_PR_LIST_TITLE
  global $ec_list_title;
  return $ec_list_title;
SC_END


SC_BEGIN EC_PR_CAT_LIST
  global $ec_category_list;
  if (is_array($ec_category_list))
    return implode(", ",$ec_category_list);
  else
    return $ec_category_list;
SC_END


SC_BEGIN EC_PR_CHANGE_YEAR
  global $ec_year_change, $thisevent_start_date;
  if ($ec_year_change) return $thisevent_start_date['year']; 
SC_END


SC_BEGIN EC_PR_CHANGE_MONTH
  global $ec_month_change, $thisevent_start_date;
  if ($ec_month_change) return $thisevent_start_date['month']; 
SC_END


SC_BEGIN EC_PR_LIST_START
  global $ecal_class, $ec_start_date;
  if (isset($parm) && ($parm !== ""))
  {
    return strftime($parm,$ec_start_date);
  }
  else
  {
    return $ecal_class->event_date_string($ec_start_date);
  }
SC_END


SC_BEGIN EC_PR_LIST_END
  global $ecal_class, $ec_end_date;
  if (isset($parm) && ($parm !== ""))
    return strftime($parm,$ec_end_date);
  else
    return $ecal_class->event_date_string($ec_end_date);
SC_END


SC_BEGIN EC_NOW_DATE
  global $ecal_class;
  if (isset($parm) && ($parm !== ""))
    return strftime($parm,time());
  else
    return $ecal_class->event_date_string(time());
SC_END


SC_BEGIN EC_NOW_TIME
  global $ecal_class;
  if (isset($parm) && ($parm !== ""))
    return strftime($parm,time());
  else
    return $ecal_class->time_string(time());
SC_END


SC_BEGIN EC_PRINT_BUTTON
  global $ec_output_type;
  if ($ec_output_type != 'print') return;
  return "<input type='button' value='".EC_LAN_162."' onClick='window.print()' />";
SC_END


SC_BEGIN EC_IF_PRINT
  global $ec_output_type, $tp;
  if ($ec_output_type != 'print') return;
  if (trim($parm) == "") return;
  return $tp->parseTemplate('{'.$parm.'}');
SC_END

SC_BEGIN EC_IFNOT_PRINT
  global $ec_output_type, $tp;
  if ($ec_output_type == 'print') return;
  if (trim($parm) == "") return;
  return $tp->parseTemplate('{'.$parm.'}');
SC_END

SC_BEGIN EC_IF_DISPLAY
  global $ec_output_type, $tp;
  if ($ec_output_type != 'display') return;
  if (trim($parm) == "") return;
  return $tp->parseTemplate('{'.$parm.'}');
SC_END

SC_BEGIN EC_IFNOT_DISPLAY
  global $ec_output_type, $tp;
  if ($ec_output_type == 'display') return;
  if (trim($parm) == "") return;
  return $tp->parseTemplate('{'.$parm.'}');
SC_END

SC_BEGIN EC_IF_PDF
  global $ec_output_type, $tp;
  if ($ec_output_type != 'pdf') return;
  if (trim($parm) == "") return;
  return $tp->parseTemplate('{'.$parm.'}');
SC_END

SC_BEGIN EC_IFNOT_PDF
  global $ec_output_type, $tp;
  if ($ec_output_type == 'pdf') return;
  if (trim($parm) == "") return;
  return $tp->parseTemplate('{'.$parm.'}');
SC_END

SC_BEGIN EC_PDF_OPTS
  global $ec_pdf_options;
  $ec_pdf_options = $parm;
SC_END

*/
?>