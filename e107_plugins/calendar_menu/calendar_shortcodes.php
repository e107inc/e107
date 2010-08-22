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
| 10.11.06 - mods for next CVS release
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$calendar_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
/*
// TIME SWITCH BUTTONS ------------------------------------------------
SC_BEGIN PREV_MONTH
	global $PREV_MONTH, $previous, $months, $prevmonth;
	return "<a href='".e_SELF."?".$previous."'>&lt;&lt; ".$months[($prevmonth-1)]."</a>";
SC_END

SC_BEGIN NEXT_MONTH
	global $NEXT_MONTH, $next, $months, $nextmonth;
	return "<a href='".e_SELF."?".$next."'> ".$months[($nextmonth-1)]." &gt;&gt;</a>";
SC_END

SC_BEGIN CURRENT_MONTH
	global $CURRENT_MONTH, $pref, $months, $month, $year;
	if($pref['eventpost_dateformat'] == 'my') {
		$CURRENT_MONTH = $months[($month-1)]." ".$year;
	} else {
		$CURRENT_MONTH = $year." ".$months[($month-1)];
	}
	return $CURRENT_MONTH;
SC_END

SC_BEGIN PREV_YEAR
	global $PREV_YEAR, $prevlink, $py;
	return "<a href='".e_SELF."?".$prevlink."'>&lt;&lt; ".$py."</a>";
SC_END

SC_BEGIN NEXT_YEAR
	global $NEXT_YEAR, $nextlink, $ny;
	return "<a href='".e_SELF."?".$nextlink."'>".$ny." &gt;&gt;</a>";
SC_END

SC_BEGIN MONTH_LIST
	global $MONTH_LIST, $year, $monthjump, $monthabb;
	$MONTH_LIST = "";
	for ($ii = 0; $ii < 12; $ii++)
	{
    	$m = $ii + 1;
    	$monthjump = mktime(0, 0, 0, $m, 1, $year);
		$MONTH_LIST .= "<a href='".e_SELF."?".$monthjump."'>".$monthabb[$ii]."</a> &nbsp;";
	}
	return $MONTH_LIST;
SC_END



// NAVIGATION BUTTONS ------------------------------------------------
SC_BEGIN NAV_BUT_ALLEVENTS
	global $NAV_BUT_ALLEVENTS;
	$allevents = (e_PAGE == "event.php" ? EC_LAN_96 : EC_LAN_93);
	return "<input class='button' type='submit' style='width:140px;' name='viewallevents' value='".$allevents."' title='".$allevents."' />";
SC_END

SC_BEGIN NAV_BUT_VIEWCAT
	global $NAV_BUT_VIEWCAT;
	//return "<input type='hidden' name='do' value='vc' /><input class='button' type='submit' style='width:140px;' name='viewcat' value='".EC_LAN_92."' />";
	return "<input type='hidden' name='do' value='vc' />";
SC_END

SC_BEGIN NAV_BUT_SUBSCRIPTION
	global $NAV_BUT_SUBSCRIPTION, $pref;
	if (($pref['eventpost_asubs']>0) && USER)
	{
		$NAV_BUT_SUBSCRIPTION = "<input class='button' type='submit' style='width:140px;' name='subs' value='".EC_LAN_123."' />";
	}
	return $NAV_BUT_SUBSCRIPTION;
SC_END

SC_BEGIN NAV_BUT_ENTEREVENT
		global $NAV_BUT_ENTEREVENT, $pref, $prop;
	$NAV_BUT_ENTEREVENT = "<input type='hidden' name='enter_new_val' value='".$prop."' />";
	if (check_class($pref['eventpost_admin']) || getperms('0')){
    	// start no admin preference
    	$NAV_BUT_ENTEREVENT .= "<input class='button' type='submit' style='width:140px;' name='doit' value='".EC_LAN_94."' />";
	}
	return $NAV_BUT_ENTEREVENT;
SC_END

SC_BEGIN NAV_LINKCURRENTMONTH
	global $NAV_LINKCURRENTMONTH, $month, $nowmonth, $year, $nowyear, $current, $ds;
	$NAV_LINKCURRENTMONTH = "";
	if ($month != $nowmonth || $year != $nowyear || $ds == 'one'){
		$NAV_LINKCURRENTMONTH = "<input class='button' type='button' style='width:140px;' name='cur' value='".EC_LAN_40."' onclick=\"javascript:document.location='".e_SELF."?$current'\" />";
	}
	return $NAV_LINKCURRENTMONTH;
SC_END

SC_BEGIN NAV_CATEGORIES
	global $NAV_CATEGORIES, $sql, $pref, $_POST, $cal_super;
	$NAV_CATEGORIES = "<select name='event_cat_ids' class='tbox' style='width:140px;' onchange='this.form.submit()' ><option class='tbox' value='all'>".EC_LAN_97."</option>";
	$event_cat_id = ( isset($_POST['event_cat_ids']) && $_POST['event_cat_ids'] ? $_POST['event_cat_ids'] : null);

	$cal_arg = ($cal_super ? "" : " find_in_set(event_cat_class,'".USERCLASS_LIST."') ");
	$sql->db_Select("event_cat", "*", $cal_arg);
	while ($row = $sql->db_Fetch()){
 	   if ($row['event_cat_id'] == $event_cat_id){
 	       $NAV_CATEGORIES .= "<option class='tbox' value='".$row['event_cat_id']."' selected='selected'>".$row['event_cat_name']."</option>";
  	  }else{
    	    $NAV_CATEGORIES .= "<option value='".$row['event_cat_id']."'>".$row['event_cat_name']."</option>";
    	}
	}
	$NAV_CATEGORIES .= "</select>";
	return $NAV_CATEGORIES;
SC_END



// CALENDAR SHOWEVENT ------------------------------------------------------------
SC_BEGIN SHOWEVENT_IMAGE
	global $SHOWEVENT_IMAGE, $ev;
	$img = '';
	if($ev['event_cat_icon'] && file_exists(e_PLUGIN."calendar_menu/images/".$ev['event_cat_icon']))
	{
	  $img = "<img style='border:0' src='".e_PLUGIN_ABS."calendar_menu/images/".$ev['event_cat_icon']."' alt='' height='".$ev['imagesize']."' width='".$ev['imagesize']."' />";
	}
	elseif(defined('BULLET'))
	{
		$img = '<img src="'.THEME.'images/'.BULLET.'" alt="" style="vertical-align: middle;" />';
	}
	elseif(file_exists(THEME.'images/bullet2.gif'))
	{
		$img = '<img src="'.THEME.'images/bullet2.gif" alt="" style="vertical-align: middle;" />';
	}
	return $img;
	//return "<img style='border:0' src='".e_PLUGIN_ABS."calendar_menu/images/".$ev['event_cat_icon']."' alt='' height='".$ev['imagesize']."' width='".$ev['imagesize']."' />";
SC_END

SC_BEGIN SHOWEVENT_INDICAT
	global $SHOWEVENT_INDICAT, $ev;
	return $ev['indicat'];
SC_END

SC_BEGIN SHOWEVENT_HEADING
	global $SHOWEVENT_HEADING, $ev, $datearray, $c, $tp;
	$linkut = mktime(0 , 0 , 0 , $datearray['mon'], $c, $datearray['year']);
	if(isset($ev['fulltopic']) && $ev['fulltopic'])
	{  // Used on first day
		$show_title = $ev['event_title'];
	}
	else
	{
	  $show_title = $tp->text_truncate($ev['event_title'], 10, "...");
	}
	if($ev['startofevent'])
	{
	  if (isset($ev['is_recent']))
	  {
	    return "<b><a title='{$ev['event_title']}' href='".e_PLUGIN."calendar_menu/event.php?".$linkut.".event.".$ev['event_id']."'><span class='mediumtext'>".$show_title."</span></a></b>";
	  }
	  else
	  {
	    return "<b><a title='{$ev['event_title']}' href='".e_PLUGIN."calendar_menu/event.php?".$linkut.".event.".$ev['event_id']."'><span class='mediumtext'>".$show_title."</span></a></b>";
	  }
	}
	else
	{
	  return "<a title='{$ev['event_title']}' href='".e_PLUGIN."calendar_menu/event.php?".$linkut.".event.".$ev['event_id']."'><span class='smalltext'>".$show_title."</span></a>";
	}
SC_END

SC_BEGIN CALENDAR_CALENDAR_RECENT_ICON
  global $ev;
  if (!isset($ev['is_recent'])) return "";
//  $recent_icon = e_IMAGE_ABS."generic/".IMODE."/new.png";
  if (file_exists(e_IMAGE."generic/".IMODE."/new.png"))
	{
	  return "<img style='border:0' src='".e_IMAGE_ABS."generic/".IMODE."/new.png"."' alt='' /> ";
	}
  return "R";
SC_END



// CALENDAR CALENDAR ------------------------------------------------------------
SC_BEGIN CALENDAR_CALENDAR_HEADER_DAY
	global $CALENDAR_CALENDAR_HEADER_DAY, $day, $pref, $week, $tp;
	if(isset($pref['eventpost_lenday']) && $pref['eventpost_lenday'])
	{
	  return "<strong>".$tp->text_truncate($day,$pref['eventpost_lenday'],'')."</strong>";
	}
	else
	{
 	  return "<strong>".$day."</strong>";
	}
SC_END

SC_BEGIN CALENDAR_CALENDAR_DAY_TODAY_HEADING
	global $CALENDAR_CALENDAR_DAY_TODAY_HEADING, $startt, $c, $days;
	return "<b><a href='".e_PLUGIN."calendar_menu/event.php?".$startt."'>".$days[($c-1)]."</a></b> <span class='smalltext'>[".EC_LAN_TODAY."]</span>";
SC_END

SC_BEGIN CALENDAR_CALENDAR_DAY_EVENT_HEADING
	global $CALENDAR_CALENDAR_DAY_EVENT_HEADING, $startt, $c, $days;
	return "<a href='".e_PLUGIN."calendar_menu/event.php?".$startt.".one'>".$days[($c-1)]."</a>";
SC_END

SC_BEGIN CALENDAR_CALENDAR_DAY_EMPTY_HEADING
	global $CALENDAR_CALENDAR_DAY_EMPTY_HEADING, $startt, $c, $days;
	return "<a href='".e_PLUGIN."calendar_menu/event.php?".$startt."'>".$days[($c-1)]."</a>";
SC_END


// EVENT LIST ------------------------------------------------
SC_BEGIN EVENTLIST_CAPTION
	global $EVENTLIST_CAPTION, $ds, $months, $selected_mon, $dayslo, $selected_day, $monthstart;
	if ($ds == 'one')
	{
		$EVENTLIST_CAPTION = EC_LAN_111.$months[$selected_mon-1]." ".$selected_day;
	}
    elseif ($ds != 'event')
    {
		$EVENTLIST_CAPTION = EC_LAN_112.$months[date("m", $monthstart)-1];
	}
	return $EVENTLIST_CAPTION;
SC_END



// EVENT ARCHIVE ------------------------------------------------------------
SC_BEGIN EVENTARCHIVE_CAPTION
	global $EVENTARCHIVE_CAPTION, $num;
	if ($num == 0) 
	{
		$EVENTARCHIVE_CAPTION = EC_LAN_137;
	}
	else
	{
		$EVENTARCHIVE_CAPTION = str_replace("-NUM-", $num, EC_LAN_62);
	}
	return $EVENTARCHIVE_CAPTION;
SC_END

SC_BEGIN EVENTARCHIVE_DATE
	global $EVENTARCHIVE_DATE, $thisevent, $ecal_class;
	$startds = $ecal_class->event_date_string($thisevent['event_start']);
	$EVENTARCHIVE_DATE = "<a href='event.php?".$thisevent['event_start'].".event.".$thisevent['event_id']."'>".$startds."</a>";
	return $EVENTARCHIVE_DATE;
SC_END

SC_BEGIN EVENTARCHIVE_DETAILS
	global $EVENTARCHIVE_DETAILS, $thisevent, $tp;
	$number = 40;
	$rowtext = $tp->toHTML($thisevent['event_details'], TRUE, "nobreak");
	$rowtext = strip_tags($rowtext);
	$words = explode(" ", $rowtext);
	$EVENTARCHIVE_DETAILS = implode(" ", array_slice($words, 0, $number));
	if(count($words) > $number){
		$EVENTARCHIVE_DETAILS .= " ".EC_LAN_133." ";
	}
	return $EVENTARCHIVE_DETAILS;
SC_END

SC_BEGIN EVENTARCHIVE_EMPTY
	global $EVENTARCHIVE_EMPTY;
	return EC_LAN_37;
SC_END

SC_BEGIN EVENTARCHIVE_HEADING
	global $EVENTARCHIVE_HEADING, $thisevent;
	$EVENTARCHIVE_HEADING = $thisevent['event_title'];
	return $EVENTARCHIVE_HEADING;
SC_END




// EVENT SHOWEVENT ------------------------------------------------------------

SC_BEGIN EVENT_RECENT_ICON
  global $thisevent, $ecal_class;
  if (($ecal_class->max_recent_show == 0) || (time() - $thisevent['event_datestamp']) > $ecal_class->max_recent_show) return "";
// Can use the generic icon, or a calendar-specific one  
//  $recent_icon = e_IMAGE."generic/".IMODE."/new.png";
  if (file_exists(e_IMAGE."generic/".IMODE."/new.png"))
	{
	  return "<img style='border:0' src='".e_IMAGE_ABS."generic/".IMODE."/new.png"."' alt='' /> ";
	}
  return "";
SC_END

SC_BEGIN EVENT_HEADING_DATE
	global $thisevent, $ecal_class;
	$startds = $ecal_class->event_date_string($thisevent['event_start']);
    return $startds;
SC_END

SC_BEGIN EVENT_DATE_START
	global $thisevent, $ecal_class;
	$startds = $ecal_class->event_date_string($thisevent['event_start']);
    return $startds;
SC_END

SC_BEGIN EVENT_TIME_START
	global $thisevent, $ecal_class;
	if ($thisevent['event_allday'] == 1) return "";
	$startds = $ecal_class->time_string($thisevent['event_start']);
    return $startds;
SC_END

SC_BEGIN EVENT_DATE_END
	global $thisevent, $ecal_class;
	if ($thisevent['event_allday'] ||($thisevent['event_end'] == $thisevent['event_start'])) return "";
	$endds = $ecal_class->event_date_string($thisevent['event_end']);
	return $endds;
SC_END

SC_BEGIN EVENT_TIME_END
	global $thisevent, $ecal_class;
	if ($thisevent['event_allday'] ||($thisevent['event_end'] == $thisevent['event_start'])) return "";
	$endds = $ecal_class->time_string($thisevent['event_end']);
	return $endds;
SC_END

SC_BEGIN EVENT_TITLE
	global $thisevent;
	return $thisevent['event_title'];
SC_END

SC_BEGIN EVENT_CAT_ICON
  global $thisevent;
  if ($thisevent['event_cat_icon'] && file_exists(e_PLUGIN."calendar_menu/images/".$thisevent['event_cat_icon']))
	{
	  return "<img style='border:0' src='".e_PLUGIN_ABS."calendar_menu/images/".$thisevent['event_cat_icon']."' alt='' /> ";
	}
	else
	{
		return "";
	}
SC_END

SC_BEGIN EVENT_ID
	global $thisevent;
	return "calevent".$thisevent['event_id'];
SC_END

SC_BEGIN EVENT_DISPLAYSTYLE
	global $EVENT_DISPLAYSTYLE, $ds;
	if (($ds=="event") || ($ds=="one")){
		$EVENT_DISPLAYSTYLE = "show";
	}else{
		$EVENT_DISPLAYSTYLE = "none";
	}
	return $EVENT_DISPLAYSTYLE;
SC_END

SC_BEGIN EVENT_DETAILS
	global $EVENT_DETAILS, $thisevent, $tp;
	return $tp->toHTML($thisevent['event_details'], TRUE);
SC_END

SC_BEGIN EVENT_CATEGORY
	global $EVENT_CATEGORY, $thisevent;
		$EVENT_CATEGORY = $thisevent['event_cat_name'];
	return $EVENT_CATEGORY;
SC_END

SC_BEGIN EVENT_LOCATION
	global $EVENT_LOCATION, $thisevent;
	if ($thisevent['event_location'] == ""){
		$EVENT_LOCATION = "";
	}else{
		$EVENT_LOCATION = $thisevent['event_location'];
	}
	return $EVENT_LOCATION;
SC_END

SC_BEGIN EVENT_AUTHOR
	global $EVENT_AUTHOR, $event_author_id, $event_author_name;
	if(USER){
		$EVENT_AUTHOR = "<a href='".e_BASE."user.php?id.".$event_author_id."'>".$event_author_name."</a>";
	}else{
		$EVENT_AUTHOR = $event_author_name;
	}
	return $EVENT_AUTHOR;
SC_END

SC_BEGIN EVENT_CONTACT
	global $EVENT_CONTACT, $thisevent,$tp;
	if ($thisevent['event_contact'] == ""){
		//$EVENT_CONTACT = EC_LAN_38; // Not Specified ;
	$EVENT_CONTACT = "";
	}else{
		$EVENT_CONTACT = $tp->toHTML($thisevent['event_contact'],TRUE);
	}
	return $EVENT_CONTACT;
SC_END

SC_BEGIN EVENT_THREAD
  global $EVENT_THREAD, $thisevent;
  return (isset($thisevent['event_thread']) && ($thisevent['event_thread'] != "")) ? "<a href='{$thisevent['event_thread']}'><img src='".e_PLUGIN_ABS."forum/images/".IMODE."/e.png' alt='' style='border:0; vertical-align:middle;' width='16' height='16' /></a> <a href='{$thisevent['event_thread']}'>".EC_LAN_39."</a>" : "";
SC_END

SC_BEGIN EVENT_OPTIONS
	global $EVENT_OPTIONS, $thisevent, $event_author_name, $cal_super;
	if (USERNAME == $event_author_name || $cal_super){
		$EVENT_OPTIONS = "<a href='event.php?ed.".$thisevent['event_id']."'><img style='border:0;' src='".e_IMAGE_ABS."admin_images/edit_16.png' title='".EC_LAN_35."' alt='".EC_LAN_35 . "'/></a>&nbsp;&nbsp;<a href='".e_PLUGIN."calendar_menu/event.php?de.".$thisevent['event_id']."'><img style='border:0;' src='".e_IMAGE_ABS."admin_images/delete_16.png' title='".EC_LAN_36."' alt='".EC_LAN_36."'/></a>";
	}
	return $EVENT_OPTIONS;
SC_END




SC_BEGIN NEXT_EVENT_TIME
  global $cal_row, $ecal_class;
  if ($cal_row['event_allday'] != 1) return $ecal_class->time_string($cal_row['event_start']); else return '';
SC_END

SC_BEGIN NEXT_EVENT_DATE
  global $cal_row, $ecal_class;
  return $ecal_class->next_date_string($cal_row['event_start']);
SC_END

SC_BEGIN NEXT_EVENT_TITLE
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

SC_BEGIN NEXT_EVENT_ICON
	global $pref, $cal_row, $ecal_dir;
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

SC_BEGIN NEXT_EVENT_GAP
  global $cal_totev;
  if ($cal_totev) return "<br /><br />"; else return "";
SC_END


*/
?>