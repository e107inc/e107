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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/calendar.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-03-18 02:13:57 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/ 
// *BK* Comments or notes added by Barry are prefixed by  *BK*
// get current date information ---------------------------------------------------------------------
define("PAGE_NAME", "Show Calendar");
require_once("../../class2.php");
require_once(HEADERF);
// *
// *  *BK* Set up userclass list that the person belongs to. 0 for everyone, if logged in then also in 254
// *  *BK* Members only 253
// *  *BK* Guest only is 252
// *  *BK* Inactive is 255
if (USER)
{
    $cal_class .= "0,253," . USERCLASS;
} 
else
{
    $cal_class = "0,252";
} 

// *
// *  *BK* See if they in are calendar supervisor class -they will have access to extra things like editing records see all categories etc
// *  *BK* This means a specific userclass can be defined as an administrator of calendar rather than just any person with admin rights
$cal_super = check_class($pref['eventpost_super']);
// *
$num = $_POST['num'];

if (isset($_POST['viewallevents']))
{
    Header("Location: " . e_PLUGIN . "calendar_menu/event.php?" . $_POST['enter_new_val']);
} 

if (isset($_POST['doit']))
{
    Header("Location: " . e_PLUGIN . "calendar_menu/event.php?ne." . $_POST['enter_new_val']);
} 

$ec_dir = e_PLUGIN . "calendar_menu/";
$lan_file = $ec_dir . "languages/" . e_LANGUAGE . ".php";
include(file_exists($lan_file) ? $lan_file : e_PLUGIN . "calendar_menu/languages/English.php");
// new part by cam.
$qs = explode(".", e_QUERY);
$action = $qs[0];
if ($action == "")
{
    $datearray = getdate();
    $month = $datearray['mon'];
    $year = $datearray['year'];
    $day = $datearray['day'];
} 
else
{
    $datearray = getdate($action);
    $month = $datearray['mon'];
    $year = $datearray['year'];
} 
// set up arrays for calender display ------------------------------------------------------------------
$week = Array('S', 'M', 'T', 'W', 'T', 'F', 'S');
$months = Array(EC_LAN_0, EC_LAN_1, EC_LAN_2, EC_LAN_3, EC_LAN_4, EC_LAN_5, EC_LAN_6, EC_LAN_7, EC_LAN_8, EC_LAN_9, EC_LAN_10, EC_LAN_11);
$monthabb = Array(EC_LAN_JAN, EC_LAN_FEB, EC_LAN_MAR, EC_LAN_APR, EC_LAN_MAY, EC_LAN_JUN, EC_LAN_JUL, EC_LAN_AUG, EC_LAN_SEP, EC_LAN_OCT, EC_LAN_NOV, EC_LAN_DEC);
$calendar_title = "<a href='" . e_PLUGIN . "calendar_menu/event.php' class='mmenu'>" . $months[$datearray[mon]-1] . " " . $current_year . "</a>"; 
// show events-------------------------------------------------------------------------------------------
// get first and last days of month in unix format---------------------------------------------------
$monthstart = mktime(0, 0, 0, $month, 1, $year);
$firstdayarray = getdate($monthstart);
// *BK* Make it the end of the last day of the month not the beginning of the last day of the month ie 23:59:59
$monthend = mktime(0, 0, 0, $month + 1, 1, $year) + 83699;
$lastdayarray = getdate($monthend);
// ----------------------------------------------------------------------------------------------------------
// echo current month with links to previous/next months ----------------------------------------
$prevmonth = ($month-1);
$prevyear = $year;
if ($prevmonth == 0)
{
    $prevmonth = 12;
    $prevyear = ($year-1);
} 
$previous = mktime(0, 0, 0, $prevmonth, 1, $prevyear);

$nextmonth = ($month + 1);
$nextyear = $year;
if ($nextmonth == 13)
{
    $nextmonth = 1;
    $nextyear = ($year + 1);
} 
$next = mktime(0, 0, 0, $nextmonth, 1, $nextyear);
$py = $year-1;
$prevlink = mktime(0, 0, 0, $month, 1, $py);
$ny = $year + 1;
$nextlink = mktime(0, 0, 0, $month, 1, $ny);
$cal_text = "<table style='width:98%' class='fborder'>
	<tr>
	<td class='forumheader' style='width:18%; text-align:left'><span class='defaulttext'><a href='" . e_SELF . "?" . $previous . "'>&lt;&lt; " . $months[($prevmonth-1)] . "</a></span></td>
	<td class='fcaption' style='width:64%; text-align:center'><b>" . $months[($month-1)] . " " . $year . "</b></td>
	<td class='forumheader' style='width:185%; text-align:right'><span class='defaulttext'><a href='" . e_SELF . "?" . $next . "'> " . $months[($nextmonth-1)] . " &gt;&gt;</a></span> </td>
	</tr>
	<tr>
	<td class='forumheader3' style='text-align:left'><a href='calendar.php?" . $prevlink . "'>&lt;&lt; " . $py . "</a></td>
	<td class='fcaption' style='text-align:center; vertical-align:middle'>";
for ($ii = 0; $ii < 13; $ii++)
{
    $m = $ii + 1;
    $monthjump = mktime(0, 0, 0, $m, 1, $year);
    $cal_text .= "<a href='calendar.php?" . $monthjump . "'>" . $monthabb[$ii] . "</a> ";
} 
$cal_text .= "</td>
	<td class='forumheader3' style='text-align:right'>
	<a href='calendar.php?" . $nextlink . "'>" . $ny . " &gt;&gt;</a>
	</td>
	</tr>
	</table>";

$cal_text .= "<div style='text-align:center'>";

$prop = mktime(0, 0, 0, $month, 1, $year);

$nowarray = getdate();
$nowmonth = $nowarray['mon'];
$nowyear = $nowarray['year'];
$nowday = $nowarray['mday'];
$current = mktime(0, 0, 0, $nowmonth, 1, $nowyear); 
// #### Check for access.
// ------------ Navigation Buttons. ------------------------------------------------------
$nav_text = "<br />
	<form method='post' action='" . e_SELF . "?" . e_QUERY . "'>
	<table border='0' cellpadding='2' cellspacing='3' class='forumheader3'>
	<tr>
	<td align='right'>
	<select name='event_cat_ids' class='tbox' style='width:140px;' onchange='this.form.submit()' >
	<option value='all'>" . EC_LAN_97 . "</option>";

$event_cat_id = !isset($_POST['event_cat_ids'])? null : $_POST['event_cat_ids'];
$sql->db_Select("event_cat", "*", " find_in_set(event_cat_class,'$cal_class') ");

while ($row = $sql->db_Fetch())
{
    extract($row);
    if ($event_cat_id == $_POST['event_cat_ids'])
    {
        $nav_text .= "<option value='$event_cat_id' selected='selected'>" . $event_cat_name . "</option>";
    } 
    else
    {
        $nav_text .= "<option value='$event_cat_id'>" . $event_cat_name . "</option>";
    } 
} 
$nav_text .= "</select>
	</td>
	<td align='center'>
	<input class='button' type='submit' style='width:140px;' name='viewallevents' value='" . EC_LAN_93 . "' />
	</td>
	</tr>
	<tr>
	<td align='right'>
	<input type='hidden' name='do' value='vc' />
	<input class='button' type='submit' style='width:140px;' name='viewcat' value='" . EC_LAN_92 . "' />
	</td>
	<td align='center'>
	<input type='hidden' name='enter_new_val' value='" . $prop . "' /> ";

if (check_class($pref['eventpost_admin']) || getperms('0'))
{ 
    // start no admin preference
    $nav_text .= "<input class='button' type='submit' style='width:140px;' name='doit' value='" . EC_LAN_94 . "' />";
} 
// end admin preference activated.
$nav_text .= "</td>
	</tr>
	</table>
	</form>
	<br />"; 
// --------------------------------------------------------------------------------
if ($month != $nowmonth || $year != $nowyear)
{
    $nav_text .= " <span class='button' style='width:120px; '><a href='" . e_SELF . "?$current'>" . EC_LAN_40 . "</a></span>";
} 

$nav_text .= "</div><br />"; 
// get events from current month----------------------------------------------------------------------
// *BK* If supervisor then all events can be seen irrespective of userclass
// *BK* Uses mysql find_in_set if not supervisor
if ($cal_super)
{
    $qry = "SELECT e.*, ec.*
			FROM #event as e
			LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
			WHERE ((e.event_start >= {$monthstart} AND e.event_start <= {$monthend}) OR (e.event_end >= {$monthstart} AND e.event_end <= {$monthend}) OR e.event_rec_y = {$month})";
} 
else
{
    $qry = "SELECT e.*, ec.*
			FROM #event as e
			LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
			WHERE ((e.event_start >= {$monthstart} AND e.event_start <= {$monthend}) OR (e.event_end >= {$monthstart} AND e.event_end <= {$monthend}) OR e.event_rec_y = {$month})
			AND find_in_set(event_cat_class,'$cal_class') ";
} 
if ($sql->db_Select_gen($qry))
{
    while ($row = $sql->db_Fetch())
    {
        if ($row['event_rec_y'] == $month)
        {
            $events[$row['event_rec_m']][] = $row;
        } 
        else
        {
            $tmp = getdate($row['event_start']);
            if ($tmp['year'] == $year)
            {
                $start_day = $tmp['mday'];
            } 
            else
            {
                $start_day = 1;
            } 
            $tmp = getdate($row['event_end']);
            if ($tmp['year'] == $year)
            {
                $end_day = $tmp['mday'];
            } 
            else
            {
                $end_day = 31;
            } 
            for ($i = $start_day; $i <= $end_day; $i++)
            {
                $events[$i][] = $row;
            } 
        } 
    } 
} 
// -----------------------------------------------------------------------------------------------------------
$start = $monthstart;
$text .= "<div style='text-align:center'>
	<table cellpadding='0' cellspacing='1' class='fborder' style='background-color:#DDDDDD; width:98%'>
	<tr>";

foreach($week as $day)
{
    $text .= "<td class='fcaption' style='z-index: -1;background-color:black; width:90px;height:20px;text-align:center'>
		<strong>" . $day . "</strong>
		<img src='" . THEME . "images/blank.gif' alt='' height='12%' width='14%' />
		</td>";
} 
$text .= "</tr><tr>";
$calmonth = $datearray['mon'];
$calday = $datearray['mday'];
$calyear = $datearray['year'];

for ($c = 0; $c < $firstdayarray['wday']; $c++)
{
    $text .= "<td style=' width:90px;height:60px;'></td>";
} 

$loop = $firstdayarray['wday'];
for ($c = 1; $c <= 31; $c++)
{
    $dayarray = getdate($start + (($c-1) * 86400));
    $stopp = mktime(24, 0, 0, $calmonth, $c, $calyear);
    $startt = mktime(0, 0, 0, $calmonth, $c, $calyear); 
    // Highlight the current day.
    if ($dayarray['mon'] == $calmonth)
    {
        if ($nowday == $c && $calmonth == $nowmonth && $calyear == $nowyear)
        {
            $text .= "<td  class='forumheader3' style='vertical-align:top; width:90px;height:90px;padding-bottom:0px;padding-right:0px; margin-right:0px'>";
            $text .= "<div style='z-index: 2; position:relative; top:1px; height:10px;padding-right:0px'>
				<b>
				<a href='" . e_PLUGIN . "calendar_menu/event.php?" . $startt . ".one'>" . $c . "</a>
				</b>
				<span class='smalltext'>[" . EC_LAN_95 . "]</span>
				</div>";
        } elseif (array_key_exists($c, $events))
        {
            $text .= "<td class='forumheader3' style='z-index: 1;vertical-align:top;  width:90px;height:90px;padding-bottom:0px;padding-right:0px; margin-right:0px'>";
            $text .= "<span style='z-index: 2; position:relative; top:1px; height:10px;padding-right:0px'>
				<a href='" . e_PLUGIN . "calendar_menu/event.php?" . $startt . ".one'>
				<strong>" . $c . "</strong>
				</a>
				</span>";
        } 
        else
        {
            $text .= "<td class='forumheader2 ' style='z-index: 1;vertical-align:top;  width:90px;height:90px;padding-bottom:0px;padding-right:0px; margin-right:0px'>";
            $text .= "<span style='z-index: 2; position:relative; top:1px; height:10px;padding-right:0px'>
				<a href='" . e_PLUGIN . "calendar_menu/event.php?" . $startt . ".one'>
				<strong>" . $c . "</strong>
				</a>
				</span>";
        } 

        if (array_key_exists($c, $events))
        {
            foreach($events[$c] as $ev)
            {
                $text .= show_event($ev, $c);
            } 
        } 

        $text .= '</td>';
    } 
    $loop++;
    if ($loop == 7)
    {
        $loop = 0;
        $text .= '</tr><tr>';
    } 
} 

$text .= "</tr></table></div>";
$caption = EC_LAN_79; // "Calendar View";
$nav = $cal_text . $nav_text . $text;
$ns->tablerender($caption, $nav);
require_once(FOOTERF);

function show_event($event, $dom)
{
    global $datearray, $ec_dir;
    $ret = "";
    $linkut = mktime(0 , 0 , 0 , $datearray['mon'], $dom, $datearray['year']);
    if (($_POST['do'] == null || $_POST['event_cat_ids'] == "all") || ($_POST['event_cat_ids'] == $event['event_cat_id']))
    {
        if (strlen($event['event_title']) > 10)
        {
            $show_title = substr($event['event_title'], 0, 10) . "...";
        } 
        else
        {
            $show_title = $event['event_title'];
        } 

        $ret = "<br />
			<img style='border:0' src='" . e_PLUGIN . "calendar_menu/images/" . $event['event_cat_icon'] . "' alt='' height='8' width='8' />
			<a title='{$event['event_title']}' href='" . e_PLUGIN . "calendar_menu/event.php?" . $linkut . ".event." . $event['event_id'] . "'>
			<span class='smalltext' style='color:black;' >" . $show_title . "</span>
			</a>";
    } 
    return $ret;
} 

?>