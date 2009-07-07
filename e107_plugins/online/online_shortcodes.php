<?php
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$online_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
/*

//##### LASTSEEN MENU ---------------------------------------------------------

SC_BEGIN LASTSEEN_USERLINK
global $row;
return "<a href='".e_BASE."user.php?id.".$row['user_id']."'>".$row['user_name']."</a>";
SC_END

SC_BEGIN LASTSEEN_DATE
global $gen, $row;
$seen_ago = $gen->computeLapse($row['user_currentvisit'], false, false, true, 'short');
return ($seen_ago ? $seen_ago : "1 ".LANDT_09)." ".LANDT_AGO;
SC_END

//##### ONLINE TRACKING DISABLED ----------------------------------------------

SC_BEGIN ONLINE_TRACKING_DISABLED
global $tp;
return $tp->toHTML(LAN_ONLINE_TRACKING_MESSAGE,TRUE);
SC_END

//##### ONLINE MENU -----------------------------------------------------------

SC_BEGIN ONLINE_GUESTS
return GUESTS_ONLINE;
SC_END

SC_BEGIN ONLINE_MEMBERS
return MEMBERS_ONLINE;
SC_END

SC_BEGIN ONLINE_MEMBERS_LIST
global $menu_pref;
if($menu_pref['online_show_memberlist']){
	return (MEMBERS_ONLINE ? MEMBER_LIST : '');
}
SC_END

SC_BEGIN ONLINE_ONPAGE
return ON_PAGE;
SC_END

SC_BEGIN ONLINE_MEMBER_TOTAL
global $sql, $e107cache;
$total_members = $e107cache->retrieve("online_menu_member_total", 120);
if($total_members == false) {
	$total_members = $sql->db_Count("user","(*)","where user_ban='0'");
	$e107cache->set("online_menu_member_total", $total_members);
}
return $total_members;
SC_END

SC_BEGIN ONLINE_MEMBER_NEWEST
global $sql, $e107cache;
$ret = $e107cache->retrieve("online_menu_member_newest", 120);
if($ret == false) {
	$newest_member_sql = $sql->db_Select("user", "user_id, user_name", "user_ban='0' ORDER BY user_join DESC LIMIT 1");
	$row = $sql->db_Fetch();
	$ret = "<a href='".e_HTTP."user.php?id.".$row['user_id']."'>".$row['user_name']."</a>";
	$e107cache->set("online_menu_member_newest", $ret);
}
return $ret;
SC_END

SC_BEGIN ONLINE_MOST
global $menu_pref;
return intval($menu_pref['most_members_online'] + $menu_pref['most_guests_online']);
SC_END

SC_BEGIN ONLINE_MOST_MEMBERS
global $menu_pref;
return $menu_pref['most_members_online'];
SC_END

SC_BEGIN ONLINE_MOST_GUESTS
global $menu_pref;
return $menu_pref['most_guests_online'];
SC_END

SC_BEGIN ONLINE_MOST_DATESTAMP
global $menu_pref, $gen;
return $gen->convert_date($menu_pref['most_online_datestamp'], "short");
SC_END

//##### ONLINE MEMBER LIST EXTENDED --------------------------------------------------

SC_BEGIN ONLINE_MEMBERS_LIST_EXTENDED
global $ONLINE_MEMBERS_LIST_EXTENDED;
return $ONLINE_MEMBERS_LIST_EXTENDED;
SC_END

SC_BEGIN ONLINE_MEMBER_IMAGE
return "<img src='".e_IMAGE."admin_images/users_16.png' alt='' style='vertical-align:middle' />";
SC_END

SC_BEGIN ONLINE_MEMBER_USER
global $oid, $oname;
return "<a href='".e_BASE."user.php?id.$oid'>$oname</a>";
SC_END

SC_BEGIN ONLINE_MEMBER_PAGE
global $pinfo, $ADMIN_DIRECTORY, $online_location_page;
return (!strstr($pinfo, $ADMIN_DIRECTORY) ? "<a href='".$pinfo."'>".$online_location_page."</a>" : $online_location_page);
SC_END

*/
?>