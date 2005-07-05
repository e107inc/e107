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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/calendar_menu.php,v $
|     $Revision: 1.16 $
|     $Date: 2005-07-05 21:31:42 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$ec_dir		= e_PLUGIN . "calendar_menu/";
$lan_file	= e_PLUGIN . "calendar_menu/languages/" . e_LANGUAGE . ".php";
require_once((file_exists($lan_file) ? $lan_file : e_PLUGIN . "calendar_menu/languages/English.php"));

$cal_datearray		= getdate();
$cal_current_day	= $cal_datearray['mday'];
$cal_current_month	= $cal_datearray['mon'];
$cal_current_year	= $cal_datearray['year'];

$cal_monthstart		= mktime(0, 0, 0, $cal_current_month, 1, $cal_current_year);
$cal_firstdayarray	= getdate($cal_monthstart);
$cal_monthend		= mktime(0, 0, 0, $cal_current_month + 1, 1, $cal_current_year) -1;
$cal_lastdayarray	= getdate($cal_monthend); 

if (USER)
{
    $cal_class = "0,253," . USERCLASS;
} 
else
{
    $cal_class = "0,252";
} 

if (check_class($pref['eventpost_super']))
{ 
    $cal_qry = "SELECT e.event_rec_m, e.event_rec_y, e.event_start, e.event_end, ec.*
	FROM #event as e LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
	WHERE (e.event_start >= {$cal_monthstart} AND e.event_start <= {$cal_monthend}) OR (e.event_end >= {$cal_monthstart} AND e.event_end <= {$cal_monthend}) OR e.event_rec_y = {$cal_current_month} order by e.event_start";
} 
else
{ 
    $cal_qry = "SELECT e.event_rec_m, e.event_rec_y, e.event_start, e.event_end, ec.*
	FROM #event as e LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
	WHERE (e.event_start >= {$cal_monthstart} AND e.event_start <= {$cal_monthend}) OR (e.event_end >= {$cal_monthstart} AND e.event_end <= {$cal_monthend}) OR e.event_rec_y = {$cal_current_month}
	and find_in_set(event_cat_class,'" . $cal_class . "') order by e.event_start";
} 

$cal_totev = 0;
if ($sql->db_Select_gen($cal_qry))
{
    while ($cal_row = $sql->db_Fetch())
    {
        $cal_totev ++;
        if ($cal_row['event_rec_y'] == $cal_current_month)
        {
            $cal_events[$cal_row['event_rec_m']][] = $cal_row;
        } 
        else
        {
            $cal_tmp = getdate($cal_row['event_start']);
            if ($cal_tmp['year'] == $cal_current_year)
            {
                $cal_start_day = $cal_tmp['mday'];
            } 
            else
            {
                $cal_start_day = 1;
            } 
            $cal_tmp = getdate($cal_row['event_end']);
            if ($cal_tmp['year'] == $cal_current_year)
            {
                $cal_end_day = $cal_tmp['mday'];
            } 
            else
            {
                $cal_end_day = 31;
            } 
            // Mark each day the event occurs
            for ($i = $cal_start_day; $i <= $cal_end_day; $i++)
            {
                $cal_events[$i][] = $cal_row;
            } 
        } 
    } 
} 

if ($pref['eventpost_weekstart'] == 'sun'){
    $cal_week	= Array(EC_LAN_25, EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24);
	}else{
    $cal_week	= Array(EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24, EC_LAN_25);
} 

$cal_months		= Array(EC_LAN_0, EC_LAN_1, EC_LAN_2, EC_LAN_3, EC_LAN_4, EC_LAN_5, EC_LAN_6, EC_LAN_7, EC_LAN_8, EC_LAN_9, EC_LAN_10, EC_LAN_11); 
if ($pref['eventpost_dateformat'] == 'my'){
    $calendar_title = "<a class='forumlink' href='" . e_PLUGIN . "calendar_menu/event.php' >" . $cal_months[$cal_datearray['mon']-1] . " " . $cal_current_year . "</a>";
	}else{
    $calendar_title = "<a class='forumlink' href='" . e_PLUGIN . "calendar_menu/event.php' >" . $cal_current_year . " " . $cal_months[$cal_datearray['mon']-1] . "</a>";
} 

$cal_text = "<div style='text-align:center'>"; 

if ($cal_totev){
    $cal_text .= EC_LAN_26 . ": " . $cal_totev;
	}else{
    $cal_text .= EC_LAN_27;
} 

$cal_headercss	= (isset($pref['eventpost_headercss']) && $pref['eventpost_headercss']) ? $pref['eventpost_headercss'] : "forumheader";
$cal_daycss		= (isset($pref['eventpost_daycss']) && $pref['eventpost_daycss']) ? $pref['eventpost_daycss'] : "forumheader3";
$cal_todaycss	= (isset($pref['eventpost_todaycss']) && $pref['eventpost_todaycss']) ? $pref['eventpost_todaycss'] : "indent";
$cal_evtoday	= (isset($pref['eventpost_evtoday']) && $pref['eventpost_evtoday']) ? $pref['eventpost_evtoday'] : "indent";
$cal_start		= $cal_monthstart;

$cal_text .= "<br /><br />
<table cellpadding='0' cellspacing='1' style='width:100%' class='fborder'><tr>";
foreach($cal_week as $cal_day)
{
    $cal_text .= "<td class='$cal_headercss' style='text-align:center'><span class='smalltext'>" . substr($cal_day, 0, $pref['eventpost_lenday']) . "</span></td>";
} 
$cal_text .= "</tr><tr >";

$cal_thismonth	= $cal_datearray['mon'];
$cal_thisday	= $cal_datearray['mday']; 

if ($pref['eventpost_weekstart'] == 'mon'){
    $firstdayoffset = ($cal_firstdayarray['wday'] == 0 ? $cal_firstdayarray['wday'] + 6 : $cal_firstdayarray['wday']-1);
	}else{
    $firstdayoffset = $cal_firstdayarray['wday'] ;
} 
for($cal_c = 0; $cal_c < $firstdayoffset; $cal_c++){
    $cal_text .= "<td class='$cal_daycss' style='text-align:center'><br /></td>";
} 
$cal_loop = $firstdayoffset; 

for($cal_c = 1; $cal_c <= 31; $cal_c++)
{
    $cal_dayarray = getdate($cal_start + (($cal_c-1) * 86400));
    if ($cal_dayarray['mon'] == $cal_thismonth)
    {
        if ($cal_thisday == $cal_c)
        {
            $cal_text .= "<td class='$cal_todaycss' style='text-align:center; width: 14.28%;'>";
        } 
        else
        {
            if (isset($cal_events) && array_key_exists($cal_c, $cal_events))
            {
                $cal_text .= "<td class='$cal_evtoday' style='text-align:center; width: 14.28%;'>";
            } 
            else
            {
                $cal_text .= "<td class='$cal_daycss' style='text-align:center; width: 14.28%;'>";
            } 
        } 

        if (isset($cal_events) && array_key_exists($cal_c, $cal_events))
        {
            $cal_event_icon = e_PLUGIN . "calendar_menu/images/" . $cal_events[$cal_c][0]['event_cat_icon'];
            $cal_event_count = count($cal_events[$cal_c]); 
            // *BK* Check if empty because file_exist will return true if icon name is blank, because it finds that the directory exists
            // *BK* It then tries to put in the icon which doesn't exist
            if (!empty($cal_events[$cal_c][0]['event_cat_icon']) && file_exists($cal_event_icon))
            {
                $cal_img = "<img style='border:0' src='{$cal_event_icon}' alt='' height='10' width='10'/>";
            } 
            else
            {
                $cal_img = $cal_c;
            } 
        } 
        else
        {
            $cal_img = $cal_c;
            $cal_event_count = 0;
        } 

        $cal_linkut = mktime(0 , 0 , 0 , $cal_dayarray['mon'], $cal_c, $cal_datearray['year']);
        if ($cal_event_count > 0)
        {
            $title = " title='{$cal_event_count} " . EC_LAN_106 . "' ";
        } 
        else
        { 
            // *BK* Set title to blank (or you can set to 0 events if you wish otherwise it remembers the last value
            $title = "";
        } 
        $cal_text .= "<a {$title} href='" . $ec_dir . "event.php?" . $cal_linkut . ".one'>$cal_img</a>";
        $cal_text .= "</td>\n";
        $cal_loop++;
        if ($cal_loop == 7)
        {
            $cal_loop = 0;
            $cal_text .= "</tr><tr>";
        } 
    } 
} 

for($cal_a = ($cal_loop + 1); $cal_a <= 7; $cal_a++)
{ 
    // *BK* Add class so that empty cells display in the menu
    $cal_text .= "<td class='$cal_daycss' >&nbsp;</td>";
} 

$cal_text .= "</tr></table></div>";
$ns->tablerender($calendar_title, $cal_text, 'calender_menu');

?>