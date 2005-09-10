<?php
include_once(e_HANDLER.'shortcode_handler.php');
$calendar_shortcodes = e_shortcode::parse_scbatch(__FILE__);
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
	if ($pref['eventpost_asubs']>0) {
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
	global $NAV_LINKCURRENTMONTH, $month, $nowmonth, $year, $nowyear, $current;
	$NAV_LINKCURRENTMONTH = "";
	if ($month != $nowmonth || $year != $nowyear){
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
	if($ev['event_cat_icon'] && file_exists(e_PLUGIN."calendar_menu/images/".$ev['event_cat_icon'])){
		$img = "<img style='border:0' src='".e_PLUGIN."calendar_menu/images/".$ev['event_cat_icon']."' alt='' height='".$ev['imagesize']."' width='".$ev['imagesize']."' />";
	}else{
		$img = "<img src='".THEME."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='' style='border:0; vertical-align:middle;' />";
	}
	return $img;
	//return "<img style='border:0' src='".e_PLUGIN."calendar_menu/images/".$ev['event_cat_icon']."' alt='' height='".$ev['imagesize']."' width='".$ev['imagesize']."' />";
SC_END

SC_BEGIN SHOWEVENT_INDICAT
	global $SHOWEVENT_INDICAT, $ev;
	return $ev['indicat'];
SC_END

SC_BEGIN SHOWEVENT_HEADING
	global $SHOWEVENT_HEADING, $ev, $datearray, $c;
	$linkut = mktime(0 , 0 , 0 , $datearray['mon'], $c, $datearray['year']);
	if(isset($ev['fulltopic']) && $ev['fulltopic']){
		$show_title = $ev['event_title'];
	}else{
		if (strlen($ev['event_title']) > 10){
			$show_title = substr($ev['event_title'], 0, 10) . "...";
		}else{
			$show_title = $ev['event_title'];
		}
	}
	if($ev['startofevent']){
		return "<b><a title='{$ev['event_title']}' href='".e_PLUGIN."calendar_menu/event.php?".$linkut.".event.".$ev['event_id']."'><span class='mediumtext' style='color:black;' >".$show_title."</span></a></b>";
	}else{
		return "<a title='{$ev['event_title']}' href='".e_PLUGIN."calendar_menu/event.php?".$linkut.".event.".$ev['event_id']."'><span class='smalltext' style='color:black;' >".$show_title."</span></a>";
	}
SC_END



// CALENDAR CALENDAR ------------------------------------------------------------
SC_BEGIN CALENDAR_CALENDAR_HEADER_DAY
	global $CALENDAR_CALENDAR_HEADER_DAY, $day, $pref, $week;
	if(isset($pref['eventpost_lenday']) && $pref['eventpost_lenday']){
		return "<strong>".substr($day,0,$pref['eventpost_lenday'])."</strong><img src='".THEME."images/blank.gif' alt='' height='12%' width='14%' />";
	}else{
 		return "<strong>".$day."</strong><img src='".THEME."images/blank.gif' alt='' height='12%' width='14%' />";
	}
SC_END

SC_BEGIN CALENDAR_CALENDAR_DAY_TODAY_HEADING
	global $CALENDAR_CALENDAR_DAY_TODAY_HEADING, $startt, $c, $days;
	//return "<b><a href='".e_PLUGIN."calendar_menu/event.php?".$startt.".one'>".$days[($c-1)]."</a></b> <span class='smalltext'>[".EC_LAN_TODAY."]</span>";
	return "<b><a href='".e_PLUGIN."calendar_menu/event.php?".$startt."'>".$days[($c-1)]."</a></b> <span class='smalltext'>[".EC_LAN_TODAY."]</span>";
SC_END

SC_BEGIN CALENDAR_CALENDAR_DAY_EVENT_HEADING
	global $CALENDAR_CALENDAR_DAY_EVENT_HEADING, $startt, $c, $days;
	//return "<a href='".e_PLUGIN."calendar_menu/event.php?".$startt.".one'>".$days[($c-1)]."</a>";
	return "<a href='".e_PLUGIN."calendar_menu/event.php?".$startt."'>".$days[($c-1)]."</a>";
SC_END

SC_BEGIN CALENDAR_CALENDAR_DAY_EMPTY_HEADING
	global $CALENDAR_CALENDAR_DAY_EMPTY_HEADING, $startt, $c, $days;
	//return "<a href='".e_PLUGIN."calendar_menu/event.php?".$startt.".one'>".$days[($c-1)]."</a>";
	return "<a href='".e_PLUGIN."calendar_menu/event.php?".$startt."'>".$days[($c-1)]."</a>";
SC_END


// EVENT LIST ------------------------------------------------
SC_BEGIN EVENTLIST_CAPTION
	global $EVENTLIST_CAPTION, $ds, $months, $selected_mon, $dayslo, $selected_day, $monthstart;
	if ($ds == 'one'){
		$EVENTLIST_CAPTION = EC_LAN_111.$months[$selected_mon-1]." ".$dayslo[$selected_day-1];
	} elseif ($ds != 'event'){
		$EVENTLIST_CAPTION = EC_LAN_112.$months[date("m", $monthstart)-1];
	}
	return $EVENTLIST_CAPTION;
SC_END



// EVENT ARCHIVE ------------------------------------------------------------
SC_BEGIN EVENTARCHIVE_CAPTION
	global $EVENTARCHIVE_CAPTION, $num;
	if($num == 0){
		$EVENTARCHIVE_CAPTION = str_replace("-NUM-", "", EC_LAN_62);
	}else{
		$EVENTARCHIVE_CAPTION = str_replace("-NUM-", $num, EC_LAN_62);
	}
	return $EVENTARCHIVE_CAPTION;
SC_END

SC_BEGIN EVENTARCHIVE_DATE
	global $EVENTARCHIVE_DATE, $gen, $events;
	$startds = cal_landate($events['event_start'], $recurring = false, $allday = false);
	//$startds = $gen->convert_date($events['event_start'], "long");
	$EVENTARCHIVE_DATE = "<a href='event.php?".$events['event_start'].".event.".$events['event_id']."'>".$startds."</a>";
	return $EVENTARCHIVE_DATE;
SC_END

SC_BEGIN EVENTARCHIVE_DETAILS
	global $EVENTARCHIVE_DETAILS, $events, $tp;
	$number = 40;
	$rowtext = $tp->toHTML($events['event_details'], TRUE, "nobreak");
	$rowtext = strip_tags($rowtext);
	$words = explode(" ", $rowtext);
	$EVENTARCHIVE_DETAILS = implode(" ", array_slice($words, 0, $number));
	if(count($words) > $number){
		$EVENTARCHIVE_DETAILS .= " ".EC_LAN_133." ";
	}
	return $EVENTARCHIVE_DETAILS;
	//return $events['event_details'];
SC_END

SC_BEGIN EVENTARCHIVE_EMPTY
	global $EVENTARCHIVE_EMPTY;
	return EC_LAN_37;
SC_END

SC_BEGIN EVENTARCHIVE_HEADING
	global $EVENTARCHIVE_HEADING, $events;
	$EVENTARCHIVE_HEADING = "";
	$EVENTARCHIVE_HEADING = $events['event_title'];
	return $EVENTARCHIVE_HEADING;
SC_END




// EVENT SHOWEVENT ------------------------------------------------------------
SC_BEGIN EVENT_HEADING
	global $EVENT_HEADING, $thisevent;
	$EVENT_HEADING = "";
	if ($thisevent['event_cat_icon'] && file_exists(e_PLUGIN."calendar_menu/images/".$thisevent['event_cat_icon'])){
		$EVENT_HEADING = "<img style='border:0' src='".e_PLUGIN."calendar_menu/images/".$thisevent['event_cat_icon']."' alt='' /> ".$thisevent['event_title'] ;
	}else{
		//$EVENT_HEADING = EC_LAN_57." ".$thisevent['event_title'];
		$EVENT_HEADING = $thisevent['event_title'];
	}
	return $EVENT_HEADING;
	//return $thisevent['event_title'];
SC_END

SC_BEGIN EVENT_DATE_START
	global $EVENT_DATE_START, $thisevent;
	if ($thisevent['event_allday'] == 0){
	if ($thisevent['event_start'] > $thisevent['event_end']){
	$thisevent['event_end'] = $thisevent['event_start'];
	}
	}
	$startds	= cal_landate($thisevent['event_start'], $thisevent['event_recurring'], $thisevent['event_allday']);
	$endds		= cal_landate($thisevent['event_end'], $thisevent['event_recurring'], $thisevent['event_allday']);
	if ($thisevent['event_allday']){
		$EVENT_DATE_START = "<b>".EC_LAN_68."</b> ".$startds;
	}else{
		$EVENT_DATE_START = "<b>".EC_LAN_29."</b> ".$startds;
	}
	return $EVENT_DATE_START;
SC_END

SC_BEGIN EVENT_DATE_END
	global $EVENT_DATE_END, $thisevent;
	if ($thisevent['event_allday'] == 0){
	if ($thisevent['event_start'] > $thisevent['event_end']){
	$thisevent['event_end'] = $thisevent['event_start'];
	}
	}
	$startds	= cal_landate($thisevent['event_start'], $thisevent['event_recurring'], $thisevent['event_allday']);
	$endds		= cal_landate($thisevent['event_end'], $thisevent['event_recurring'], $thisevent['event_allday']);
	if ($thisevent['event_allday'] || $startds == $endds){
		$EVENT_DATE_END = "";
	}else{
		$EVENT_DATE_END = "<b>".EC_LAN_69."</b> ".$endds;
	}
	return $EVENT_DATE_END;
SC_END

SC_BEGIN EVENT_ID
	global $EVENT_ID, $thisevent;
	return "calevent".$thisevent['event_id'];
SC_END

SC_BEGIN EVENT_DISPLAYSTYLE
	global $EVENT_DISPLAYSTYLE, $ds;
	if ($ds=="event"){
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
	global $EVENT_CATEGORY, $thisevent, $tp;
	if ($thisevent['event_cat_icon'] && file_exists(e_PLUGIN."calendar_menu/images/".$thisevent['event_cat_icon'])){
		$EVENT_CATEGORY = "<img style='border:0' src='".e_PLUGIN."calendar_menu/images/".$thisevent['event_cat_icon']."' alt='' /> ".$thisevent['event_cat_name'];
	}else{
		$EVENT_CATEGORY = $thisevent['event_cat_name'];
	}
	return $EVENT_CATEGORY;
SC_END

SC_BEGIN EVENT_LOCATION
	global $EVENT_LOCATION, $thisevent;
	if ($thisevent['event_location'] == ""){
		//$EVENT_LOCATION = EC_LAN_38;
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
		$EVENT_CONTACT = $tp->toHTML($thisevent['event_contact'],"noreplace");
	}
	return $EVENT_CONTACT;
SC_END

SC_BEGIN EVENT_THREAD
	global $EVENT_THREAD, $thisevent;
	return ($thisevent['event_thread'] ? "<a href='{$thisevent['event_thread']}'><img src='".e_PLUGIN."forum/images/e.png' alt='' style='border:0; vertical-align:middle;' width='16' height='16' /> ".EC_LAN_39."</a>" : "");
SC_END

SC_BEGIN EVENT_OPTIONS
	global $EVENT_OPTIONS, $thisevent, $event_author_name, $cal_super;
	if (USERNAME == $event_author_name || $cal_super){
		$EVENT_OPTIONS = "<a href='event.php?ed.".$thisevent['event_id']."'><img style='border:0;' src='".e_IMAGE."admin_images/edit_16.png' title='".EC_LAN_35."' alt='".EC_LAN_35 . "'/></a>&nbsp;&nbsp;<a href='".e_PLUGIN."calendar_menu/event.php?de.".$thisevent['event_id']."'><img style='border:0;' src='".e_IMAGE."admin_images/delete_16.png' title='".EC_LAN_36."' alt='".EC_LAN_36."'/></a>";
	}
	return $EVENT_OPTIONS;
SC_END

*/
?>