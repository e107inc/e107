<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dun.an 2001-2002
|     http://e107.org
|     jali.@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/subscribe.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-05-01 04:37:02 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
$ec_dir = e_PLUGIN . "calendar_menu/";
$lan_file = $ec_dir . "languages/" . e_LANGUAGE . ".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN . "calendar_menu/languages/English.php");
define("PAGE_NAME", EC_LAN_80);
require_once(HEADERF);
$cal_db = new DB;

if (isset($_POST['upsubs']))
{
    $cal_cats = $_POST['event_list'];
    $cal_subs = $_POST['event_subd'];
    $cal_db->db_Delete("event_subs", "event_userid='" . USERID . "'");
    foreach($cal_cats as $cal_row)
    {
        if ($cal_subs[$cal_row])
        {
            $cal_inargs = "0,'" . USERID . "','" . $cal_row . "'";
            $cal_db->db_Insert("event_subs", $cal_inargs);
        } 
        // print $cal_row .  $cal_subs[$cal_row] . "<br>";
    } 
    $caltext = "<table class='fborder' width='97%'>
<tr><td class='fcaption' >" . EC_LAN_130 . "</td></tr>
<tr><td class='forumheader3' ><a href='calendar.php'>" . EC_LAN_131 . "</a></tr>
<tr><td class='fcaption' >&nbsp;</td></tr></table>";
} 
else
{
    $caltext = "<form id='calsubs' action='" . e_SELF . "' method='post' >
<table class='fborder' width='97%'>
<tr><td class='fcaption' colspan='2'>" . EC_LAN_125 . "</td></tr>
<tr><td class='forumheader2' >" . EC_LAN_126 . "</td><td class='forumheader2' >" . EC_LAN_127 . "</td></tr>"; 
    // Get list of currently subscribed
    $cal_db->db_Select("event_subs", "event_cat", "where event_userid='" . USERID . "'", "nowhere");
    while ($cal_s = $cal_db->db_Fetch())
    {
        extract($cal_s);
        $cal_array[] = $event_cat;
    } // while 
    // Get list of categories that have subscriptions and are visible to this member
    $cal_args = "select * from " . MPREFIX . "event_cat where event_cat_subs>0 and event_cat_force = 0 and find_in_set(event_cat_class,'".USERCLASS_LIST."') ";
    if ($cal_db->db_Select_gen($cal_args))
    { 
        // print $cal_args;
        while ($cal_row = $cal_db->db_Fetch())
        {
            extract($cal_row);
            $caltext .= "<tr>
	<td class='forumheader3' style='width:10%;'><input type='hidden' name='event_list[]' value='" . $event_cat_id . "' />
	<input type='checkbox' class='tbox' value='1' name='event_subd[$event_cat_id]' " . (in_array($event_cat_id, $cal_array)?"checked='checked' ":"") . " /> </td>
	<td class='forumheader3'>$event_cat_name</td></tr>";
        } 
    } 
    else
    {
        $caltext .= "<tr><td class='forumheader3' colspan='2'>" . EC_LAN_128 . "</td></tr>";
    } 
    $caltext .= "<tr><td class='forumheader3' colspan='2'><input class='tbox' type='submit' value='" . EC_LAN_129 . "' name='upsubs' /></td></tr>";
    $caltext .= "</table></form>";
} 
$ns->tablerender(EC_LAN_124, $caltext);
require_once(FOOTERF);

?>