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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/event.php,v $
|     $Revision: 1.15 $
|     $Date: 2005-07-06 11:24:20 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(e_PLUGIN."calendar_menu/calendar_shortcodes.php");
if (isset($_POST['viewallevents']))
{
    Header("Location: ".e_PLUGIN."calendar_menu/calendar.php?".$_POST['enter_new_val']);
} 

if (isset($_POST['doit']))
{
    Header("Location: ".e_PLUGIN."calendar_menu/event.php?ne.".$_POST['enter_new_val']);
} 
if (isset($_POST['subs']))
{
    Header("Location: ".e_PLUGIN."calendar_menu/subscribe.php");
} 

$ec_dir		= e_PLUGIN."calendar_menu/";
$lan_file	= $ec_dir."languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."calendar_menu/languages/English.php");
define("PAGE_NAME", EC_LAN_80);

$cal_super	= check_class($pref['eventpost_super']);

require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);

// enter new category into db
if (isset($_POST['ne_cat_create']))
{
    if ($_POST['ne_new_category'] != "")
    {
        $sql->db_Insert("event_cat", "  0, '".$tp->toDB($_POST['ne_new_category'])."', '".$tp->toDB($_POST['ne_new_category_icon'])."', 0, '', '', '', '', '', '', '0','0','".time()."', ''  ");
        header("location:event.php?".$_POST['qs'].".m1");
    } 
    else
    {
        header("location:event.php?".$_POST['qs'].".m3");
    } 
} 
// enter new event into db
if (isset($_POST['ne_insert']) && USER == true)
{
    if ($_POST['ne_event'] == ""){
		header("location:event.php?".$ev_start.".m3");
	}else{
        $tmp			= explode("-", $_POST['start_date']);
        $ev_start		= mktime($_POST['ne_hour'], $_POST['ne_minute'], 0, $tmp[1], $tmp[2], $tmp[0]);
        $tmp			= explode("-", $_POST['end_date']);
        $ev_end			= mktime($_POST['end_hour'], $_POST['end_minute'], 0, $tmp[1], $tmp[2], $tmp[0]);
        $ev_title		= $tp->toDB($_POST['ne_title']);
        $ev_location	= $tp->toDB($_POST['ne_location']);
        $ev_event		= $tp->toDB($_POST['ne_event']);

        if ($_POST['recurring'] == 1){
            $rec_m = $_POST['ne_day'];
            $rec_y = $_POST['ne_month'];
        }else{
            $rec_m = "";
            $rec_y = "";
        } 

        $sql->db_Insert("event", " 0, '$ev_start', '$ev_end', '".$_POST['allday']."', '".$_POST['recurring']."', '".time()."', '$ev_title', '$ev_location', '$ev_event', '".USERID.".".USERNAME."', '".$_POST['ne_email']."', '".$_POST['ne_category']."', '".$_POST['ne_thread']."', '$rec_m', '$rec_y' ");
        $qs = eregi_replace("ne.", "", $_POST['qs']);
        header("location:event.php?".$qs.".m4");
    }
} 

// update event in db
if (isset($_POST['ne_update']) && USER == true)
{
    if ($_POST['ne_event'] == ""){
		header("location:event.php?".$_POST['qs'].".m3");
	}else{
        $tmp			= explode("-", $_REQUEST['start_date']);
        $ev_start		= mktime($_POST['ne_hour'], $_POST['ne_minute'], 0, $tmp[1], $tmp[2], $tmp[0]);
        $tmp			= explode("-", $_POST['end_date']);
        $ev_end			= mktime($_POST['end_hour'], $_POST['end_minute'], 0, $tmp[1], $tmp[2], $tmp[0]);
        $ev_title		= $tp->toDB($_POST['ne_title']);
        $ev_location	= $tp->toDB($_POST['ne_location']);
        $ev_event		= $tp->toDB($_POST['ne_event']);

        if ($_POST['recurring'] == 1){
            $rec_m = $_POST['ne_day'];
            $rec_y = $_POST['ne_month'];
        }else{
            $rec_m = "";
            $rec_y = "";
        } 

        $sql->db_Update("event", "event_start='$ev_start', event_end='$ev_end', event_allday='".$_POST['allday']."', event_recurring='".$_POST['recurring']."', event_datestamp= '".time()."', event_title= '$ev_title', event_location='$ev_location', event_details='$ev_event', event_contact='".$_POST['ne_email']."', event_category='".$_POST['ne_category']."', event_thread='".$_POST['ne_thread']."', event_rec_m='$rec_m', event_rec_y='$rec_y' WHERE event_id='".$_POST['id']."' ");
        $qs = eregi_replace("ed.", "", $_POST['qs']);

        header("location:event.php?".$ev_start.".".$qs.".m5");
    }
} 


require_once(HEADERF);

if (isset($_POST['jump']))
{
		$smarray	= getdate(mktime(0, 0, 0, $_POST['jumpmonth'], 1, $_POST['jumpyear']));
		$month		= $smarray['mon'];
		$year		= $smarray['year'];
} 
else
{
    if(e_QUERY){
		$qs			= explode(".", e_QUERY);
		$action		= $qs[0];
		$ds			= (isset($qs[1]) ? $qs[1] : "");
		$eveid		= (isset($qs[2]) ? $qs[2] : "");
	}
    if ($action == "")
    {
        $nowarray	= getdate();
        $month		= $nowarray['mon'];
        $year		= $nowarray['year'];
    } 
    else
    {
        $smarray	= getdate($action);
        $month		= $smarray['mon'];
        $year		= $smarray['year'];
    } 
} 

if (isset($_POST['confirm']))
{
    if ($sql->db_Delete("event", "event_id='".$_POST['existing']."' "))
    {
        $message = EC_LAN_51; //Event Deleted
    } 
    else
    {
        $message = EC_LAN_109; //Unable to Delete event for some my.erious reason
    } 
} 

if ($action == "de")
{
    $text = "<div style='text-align:center'>
	<b>".EC_LAN_48."</b>
	<br /><br />
	<form method='post' action='".e_SELF."' id='calformz' >
	<input class='button' type='submit' name='cancel' value='".EC_LAN_49."' />
	<input class='button' type='submit' name='confirm' value='".EC_LAN_50."' />
	<input type='hidden' name='existing' value='".$qs[1]."' />
	<input type='hidden' name='subbed' value='no' />
	</form>
	</div>";
    $ns->tablerender(EC_LAN_46, $text); // Confirm Delete Event
    require_once(FOOTERF);
    exit;
} 
if (isset($_POST['cancel']))
{
    $message = EC_LAN_47; 
    // Delete Cancelled
}

// set up data arrays ----------------------------------------------------------------------------------
if ($pref['eventpost_weekstart'] == 'sun')
{
    $days	= Array(EC_LAN_25, EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24);
} 
else
{
    $days	= Array(EC_LAN_19, EC_LAN_20, EC_LAN_21, EC_LAN_22, EC_LAN_23, EC_LAN_24, EC_LAN_25);
} 
$dayslo		= array('1.', '2.', '3.', '4.', '5.', '6.', '7.', '8.', '9.', '10.', '11.', '12.', '13.', '14.', '15.', '16.', '17.', '18.', '19.', '20.', '21.', '22.', '23.', '24.', '25.', '26.', '27.', '28.', '29.', '30.', '31.');
$monthabb	= Array(EC_LAN_JAN, EC_LAN_FEB, EC_LAN_MAR, EC_LAN_APR, EC_LAN_MAY, EC_LAN_JUN, EC_LAN_JUL, EC_LAN_AUG, EC_LAN_SEP, EC_LAN_OCT, EC_LAN_NOV, EC_LAN_DEC);
$months		= array(EC_LAN_0, EC_LAN_1, EC_LAN_2, EC_LAN_3, EC_LAN_4, EC_LAN_5, EC_LAN_6, EC_LAN_7, EC_LAN_8, EC_LAN_9, EC_LAN_10, EC_LAN_11);
// ----------------------------------------------------------------------------------------------------------

if (isset($qs[2]) && $qs[2] == "m1")
{
    $message = EC_LAN_41; //"New category created.";
} 
else if (isset($qs[2]) && $qs[2] == "m2")
{
    $message = EC_LAN_42; //"Event cannot end before it starts.";
} 
else if (isset($qs[2]) && $qs[2] == "m3")
{
    $message = EC_LAN_43; //"You left required field(s) blank.";
} 
else if (isset($qs[2]) && $qs[2] == "m4")
{
    $message = EC_LAN_44; //"New event created and entered into database.";
} elseif (isset($qs[2]) && $qs[2] == "m5")
{
    $message = EC_LAN_45; //"Event updated in database.";
} 

if (isset($message))
{
    $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

// enter new event form
if ($action == "ne" || $action == "ed")
{
    if ($cal_super || check_class($pref['eventpost_admin']))
    {
        if ($action == "ed")
        {
            $sql->db_Select("event", "*", "event_id='".$qs[1]."' ");
            list($null, $ne_start, $ne_end, $allday, $recurring, $ne_datestamp, $ne_title, $ne_location, $ne_event, $ne_author, $ne_email, $ne_category, $ne_thread) = $sql->db_Fetch();

            $smarray = getdate($ne_start);
            $ne_hour = $smarray['hours'];
            $ne_minute = $smarray['minutes'];
            $ne_startdate = date("Y-m-d", $ne_start);

            $smarray = getdate($ne_end);
            $end_hour = $smarray['hours'];
            $end_minute = $smarray['minutes'];
            $ne_enddate = date("Y-m-d", $ne_end);
        } 
        else
        {
            $smarray = getdate($qs[1]);
            $month = $smarray['mon'];
            $year = $smarray['year'];
            $ne_startdate = date("Y-m-d", $qs[1]);

            $ne_hour = $smarray['hours'];
            $ne_minute = $smarray['minutes'];

            $end_hour = $smarray['hours'];
            $end_minute = $smarray['minutes'];
            $ne_enddate = date("Y-m-d", $qs[1]);
        } 

        
		$text = "
		<script type=\"text/javascript\">
		<!--
		function calcheckform(thisform, submitted)
		{
			var testresults=true;

			//category create check
			if(submitted == 'ne_cat_create'){
				if(thisform.ne_new_category.value == ''){
					alert('".EC_LAN_134."');
					return FALSE;
				}else{
					return TRUE;
				}
			}

			//event check
			var temp;
			temp = thisform.start_date.value.split(\"-\");
			var sdate = temp[0] + temp[1] + temp[2] + thisform.ne_hour.options[thisform.ne_hour.selectedIndex].value + thisform.ne_minute.options[thisform.ne_minute.selectedIndex].value
			temp = thisform.end_date.value.split(\"-\");
			var edate = temp[0] + temp[1] + temp[2] + thisform.end_hour.options[thisform.end_hour.selectedIndex].value + thisform.end_minute.options[thisform.end_minute.selectedIndex].value

			testresults=true;

			if (edate <= sdate && !thisform.allday.checked && testresults )
			{
				alert('".EC_LAN_99."');
				testresults=false;
			}
			if ((thisform.ne_title.value=='' || thisform.ne_event.value=='') && testresults)
			{
				alert('".EC_LAN_98."');
				testresults=false;
			}

			if (testresults)
			{
				if (thisform.subbed.value=='no')
				{
					thisformm.subbed.value='yes';
					testresults=true;
				}
			else
				{
					alert('".EC_LAN_113."');
					return false;
				}
			}
			return testresults;
		}
		-->
		</script>";
		
		$text .= "
		<form method='post' action='".e_SELF."' id='linkform' onsubmit='return calcheckform(this, submitted)'>
		<table style='width:98%' class='fborder' >";

        if ($action == "ed")
        {
            $caption = EC_LAN_66; // edit Event 
            
        } elseif ($action == "ne")
        {
            $caption = EC_LAN_28; // Enter New Event
        } 
        else
        {
            $caption = EC_LAN_83;
        } 

        $text .= "
		<tr>
		<td class='forumheader3' style='width:20%'>".EC_LAN_72." </td>
		<td class='forumheader3' style='width:80%'> ".EC_LAN_67." ";

        unset($cal_options);
        unset($cal_attrib);
        $cal_options['firstDay'] = 0;
        $cal_options['showsTime'] = false;
        $cal_options['showOthers'] = true;
        $cal_options['weekNumbers'] = false;
        $cal_options['ifFormat'] = "%Y-%m-%d";
        $cal_attrib['class'] = "tbox";
        $cal_attrib['size'] = "12";
        $cal_attrib['name'] = "start_date";
        $cal_attrib['value'] = $ne_startdate;
        $text .= $cal->make_input_field($cal_options, $cal_attrib);

        $text .= "&nbsp;&nbsp;&nbsp;".EC_LAN_73." ";
        unset($cal_options);
        unset($cal_attrib);
        $cal_options['firstDay'] = 0;
        $cal_options['showsTime'] = false;
        $cal_options['showOthers'] = true;
        $cal_options['weekNumbers'] = false;
        $cal_options['ifFormat'] = "%Y-%m-%d";
        $cal_attrib['class'] = "tbox";
        $cal_attrib['size'] = "12";
        $cal_attrib['name'] = "end_date";
        $cal_attrib['value'] = $ne_enddate;
        $text .= $cal->make_input_field($cal_options, $cal_attrib);
        $text .= "		
		</td>
		</tr>
		<tr>
		<td class='forumheader3' style='width:20%'>".EC_LAN_71." </td>
		<td class='forumheader3' style='width:80%'>
		".EC_LAN_67." <select name='ne_hour' id='ne_hour' class='tbox'>";
        for($count = "00"; $count <= "23"; $count++)
        {
            $val = sprintf("%02d", $count);
            $text .= "<option value='{$val}' ".(isset($ne_hour) && $count == $ne_hour ? "checked='checked'" :"")." >".$val."</option>";
        } 
        $text .= "</select>
		<select name='ne_minute' class='tbox'>";
        for($count = "00"; $count <= "59"; $count++)
        {
            $val = sprintf("%02d", $count);
            $text .= "<option ".(isset($ne_minute) && $count == $ne_minute ? "checked='checked'" :"")." value='{$val}'>".$val."</option>";
        } 
        $text .= "</select>

		&nbsp;&nbsp;".EC_LAN_73." <select name='end_hour' class='tbox'>";
        for($count = "00"; $count <= "23"; $count++)
        {
            $val = sprintf("%02d", $count);
            $text .= "<option ".(isset($end_hour) && $count == $end_hour ? "checked='checked'" :"")." value='{$val}'>".$val."</option>";
        } 
        $text .= "</select>
		<select name='end_minute' class='tbox'>";
        for($count = "00"; $count <= "59"; $count++)
        {
            $val = sprintf("%02d", $count);
            $text .= "<option ".(isset($end_minute) && $count == $end_minute ? "checked='checked'" :"")." value='{$val}'>".$val."</option>";
        } 
        $text .= "</select>";
		$text .= "<br /><input type='checkbox' name='allday' value='1' ".(isset($allday) && $allday == 1 ? "checked='checked'" :"")." />";
        $text .= EC_LAN_64."
		</td>
		</tr>
		<tr>
		<td class='forumheader3' style='width:20%'>".EC_LAN_65."</td>
		<td class='forumheader3' style='width:80%'>";
		$text .= "<input type='checkbox' name='recurring' value='1'  ".(isset($recurring) && $recurring == 1 ? "checked='checked'" : "")." />";
        $text .= EC_LAN_63."
		</td>
		</tr>
		<tr>
		<td class='forumheader3' style='width:20%'>".EC_LAN_70." *</td>
		<td class='forumheader3' style='width:80%'>
		<input class='tbox' type='text' name='ne_title' size='75' value='".(isset($ne_title) ? $ne_title : "")."' maxlength='200' style='width:95%' />
		</td>
		</tr>
		<tr>
		<td class='forumheader3' style='width:20%'>".EC_LAN_52." </td>
		<td class='forumheader3' style='width:80%'>
		<select name='ne_category' class='tbox'>"; 
        // Check if supervisor, if so get all categories, otherwise ju. get those the user is allowed to see
		$cal_arg = ($cal_super ? "" : "find_in_set(event_cat_addclass,'".USERCLASS_LIST."')");
        if ($sql->db_Select("event_cat", "*", $cal_arg)){
            while ($row = $sql->db_Fetch()){
				$text .= "<option value='{$row['event_cat_id']}' ".(isset($ne_category) && $ne_category == $row['event_cat_id'] ? "selected='selected'" :"")." >".$row['event_cat_name']."</option>";
            } 
        }else{
            $text .= "<option value=''>".EC_LAN_91."</option>";
        } 
        $text .= "</select>
		</td>
		</tr>"; 
        // * *BK* Check if the add class is appropriate for adding new categories
        // * *BK* It will default to everybody class when created.  Need to go in to admin categories if
        // * *BK* you want to change read class.
        if (check_class($pref['eventpost_addcat']) && $action != "ed")
        {
            require_once(e_HANDLER."file_class.php");
            $fi = new e_file;
            $imagelist = $fi->get_files(e_PLUGIN."calendar_menu/images", "\.\w{3}$");
            $text .= "<tr>
			<td class='forumheader3' style='width:20%' rowspan='2'>".EC_LAN_53." </td>
			<td class='forumheader3' style='width:80%'>".EC_LAN_54."
			<input class='tbox' type='text' name='ne_new_category' size='30' value='".(isset($ne_new_category) ? $ne_new_category : "")."' maxlength='100' style='width:95%' /> ";
            $text .= "</td></tr>
			<tr><td class='forumheader3' style='width:80%'>".EC_LAN_55;
            $text .= " <input class='tbox' style='width:150px' type='text' id='ne_new_category_icon' name='ne_new_category_icon' />";
            $text .= " <input class='button' type='button' style='width: 45px; cursor:hand;' value='".EC_LAN_90."' onclick='expandit(\"cat_icons\")' />";
            $text .= "<div style='display:none' id='cat_icons'>";

            foreach($imagelist as $img){
                if ($img['fname']){
                    $text .= "<a href=\"javascript:insertext('".$img['fname']."', 'ne_new_category_icon', 'cat_icons')\"><img src='".e_PLUGIN."calendar_menu/images/".$img['fname']."' style='border:0px' alt='' /></a> ";
                } 
            } 
            $text .= "</div>";
            $text .= "<div style='text-align:center'>
			<input class='button' type='submit' name='ne_cat_create' value='".EC_LAN_56."' onclick='submitted=this.name' /></div>
			</td>
			</tr>";
        } 

        $text .= "
		<tr>
		<td class='forumheader3' style='width:20%'>".EC_LAN_32." </td>
		<td class='forumheader3' style='width:80%'>
		<input class='tbox' type='text' name='ne_location' size='60' value='".(isset($ne_location) ? $ne_location : "")."' maxlength='200' style='width:95%' />
		</td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:20%'>".EC_LAN_57." *</td>
		<td class='forumheader3' style='width:80%'>
		<textarea class='tbox' name='ne_event' cols='59' rows='8' style='width:95%'>".(isset($ne_event) ? $ne_event : "")."</textarea>
		</td>
		</tr>"; 
        // * *BK*
        // * *BK* Only display for forum thread if it is required.  No point in being in if not wanted
        // * *BK* or if forums are inactive
        // * *BK*
        if (isset($pref['eventpost_forum']) && $pref['eventpost_forum'] == 1)
        {
            $text .= "
			<tr>
			<td class='forumheader3' style='width:20%'>".EC_LAN_58." </td>
			<td class='forumheader3' style='width:80%'>
			<input class='tbox' type='text' name='ne_thread' size='60' value='".(isset($ne_thread) ? $ne_thread : "")."' maxlength='100' style='width:95%' />
			</td>
			</tr>";
        } 
        // * *BK*
        // * *BK* If the user is logged in and has their email set plus the field is empty then put in
        // * *BK* their email address.  They can always take it out if they want, its not a required field
        if (empty($ne_email) && defined('USEREMAIL'))
        {
            $ne_email = USEREMAIL;
        } 
        $text .= "
		<tr>
		<td class='forumheader3' style='width:20%'>".EC_LAN_59." </td>
		<td class='forumheader3' style='width:80%'>
		<input class='tbox' type='text' name='ne_email' size='60' value='$ne_email' maxlength='150' style='width:95%' />
		</td></tr>
		<tr>
		<td class='forumheader3' colspan='2' >".EC_LAN_105." </td>
		</tr>

		<tr>
		<td class='forumheader' colspan='2' style='text-align:center'>";
        if ($action == "ed"){
            $text .= "<input class='button' type='submit' name='ne_update' value='".EC_LAN_60."' onclick='submitted=this.name' />
			<input type='hidden' name='id' value='".$qs[1]."' />";
        }else{
            $text .= "<input class='button' type='submit' name='ne_insert' value='".EC_LAN_28."' onclick='submitted=this.name' />";
        } 
        $text .= "<input type='hidden' name='qs' value='".e_QUERY."' /></td>
		</tr>
		</table>
		</form>";

        $ns->tablerender($caption, $text);
        require_once(FOOTERF);
        exit;
    } 
    else
    {
        header("location:".e_PLUGIN."calendar_menu/event.php");
        exit;
    } 
} 

// show events
$monthstart		= mktime(0, 0, 0, $month, 1, $year);
$firstdayarray	= getdate($monthstart);
$monthend		= mktime(0, 0, 0, $month + 1, 1, $year) -1 ;
$lastdayarray	= getdate($monthend);

$prevmonth		= ($month-1);
$prevyear		= $year;
if ($prevmonth == 0)
{
    $prevmonth	= 12;
    $prevyear	= ($year-1);
} 
$previous		= mktime(0, 0, 0, $prevmonth, 1, $prevyear);

$nextmonth		= ($month + 1);
$nextyear		= $year;
if ($nextmonth == 13)
{
    $nextmonth	= 1;
    $nextyear	= ($year + 1);
} 
$next			= mktime(0, 0, 0, $nextmonth, 1, $nextyear);

//$todayarray		= getdate();
//$current_month	= $todayarray['mon'];
//$current_year	= $todayarray['year'];
//$current		= mktime(0, 0, 0, $current_month, 1, $current_year);

$nowarray		= getdate();
$nowmonth		= $nowarray['mon'];
$nowyear		= $nowarray['year'];
$nowday			= $nowarray['mday'];
$current		= mktime(0, 0, 0, $nowmonth, 1, $nowyear); 

$prop			= mktime(0, 0, 0, $month, 1, $year);
$next			= mktime(0, 0, 0, $nextmonth, 1, $nextyear);
$py				= $year-1;
$prevlink		= mktime(0, 0, 0, $month, 1, $py);
$ny				= $year + 1;
$nextlink		= mktime(0, 0, 0, $month, 1, $ny);

if (is_readable(THEME."calendar_template.php")) {
	require_once(THEME."calendar_template.php");
	} else {
	require_once(e_PLUGIN."calendar_menu/calendar_template.php");
}

$text2 = "";
// time switch buttons
$text2 .= $tp -> parseTemplate($CALENDAR_TIME_TABLE, FALSE, $calendar_shortcodes);

// navigation buttons
$text2 .= $tp -> parseTemplate($CALENDAR_NAVIGATION_TABLE, FALSE, $calendar_shortcodes);

//$sql2 = new db;
if ($ds == "event"){ 
	$qry = "
	SELECT e.*, ec.*
	FROM #event as e
	LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
	WHERE e.event_id='".$eveid."' 
	";
	$sql2->db_Select_gen($qry);
    $row = $sql2->db_Fetch();
    $event[] = $row;
    $next10_start = $event[0]['event_start'];
	$text2 .= $tp -> parseTemplate($EVENT_EVENT_TABLE_START, FALSE, $calendar_shortcodes);
	$text2 .= show_event($event);
	$text2 .= $tp -> parseTemplate($EVENT_EVENT_TABLE_END, FALSE, $calendar_shortcodes);

}else{
    //$sql2->db_Select("event_cat", "*", "event_cat_id='".$event_true[($c)]."' ");
    //$event_cat = $sql2->db_Fetch();
    //extract($event_cat);

    if ($ds == 'one'){
        $tmp			= getdate($action);
        $selected_day	= $tmp['mday'];
        $selected_mon	= $tmp['mon'];
        $start_time		= $action;
        $end_time		= $action + 86399;
        $cap_title		= " - ".$months[$selected_mon-1]." ".$selected_day;
    }else{
        $start_time		= $monthstart;
        $end_time		= $monthend;
        $cap_title		= '';
    } 
    $extra = " OR e.event_rec_y = {$month} ";

    if ($cal_super){
        $qry = "
		SELECT e.*, ec.*
		FROM #event as e
		LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
		WHERE (e.event_start >= {$start_time} AND e.event_start <= {$end_time})
		OR (e.event_end >= {$start_time} AND e.event_end <= {$end_time})
		OR (e.event_start <= {$start_time} AND e.event_end >= {$end_time})
		{$extra}
		ORDER BY e.event_start ASC";
    }else{
        $qry = "
		SELECT e.*, ec.*
		FROM #event as e
		LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
		WHERE find_in_set(event_cat_class,'".USERCLASS_LIST."') AND
		((e.event_start >= {$start_time} AND e.event_start <= {$end_time})
		OR (e.event_end >= {$start_time} AND e.event_end <= {$end_time})
		OR (e.event_start <= {$start_time} AND e.event_end >= {$end_time})
		{$extra})
		ORDER BY e.event_start ASC";
    } 
    if ($sql->db_Select_gen($qry)){
        while ($row = $sql->db_Fetch()){
            if ($row['event_rec_y'] == $month){
                if (!in_array($row['event_id'], $idArray)){
                    $events[$row['event_rec_m']][] = $row;
                    $idArray[] = $row['event_id'];
                } 
            }else{
                if ($ds == 'one'){
                    if (!isset($idArray) || !is_array($idArray) || !in_array($row['event_id'], $idArray)){
                        $events[$selected_day][] = $row;
                        $idArray[] = $row['event_id'];
                    } 
                }else{
                    $tmp		= getdate($row['event_start']);
					$start_day	= ($tmp['year'] == $year ? $tmp['mday'] : "1");
                    $tmp		= getdate($row['event_end']);
					$end_day	= ($tmp['year'] == $year ? $tmp['mday'] : "31");
                    for ($i = $start_day; $i <= $end_day; $i++){
                        if (!isset($idArray) || !is_array($idArray) || !in_array($row['event_id'], $idArray)){
                            $events[$i][] = $row;
                            $idArray[] = $row['event_id'];
                        } 
                    } 
                } 
            } 
        } 
    } 
} 


// event list
if(isset($events) && is_array($events)){
	$text2 .= $tp -> parseTemplate($EVENT_EVENTLIST_TABLE_START, FALSE, $calendar_shortcodes);
	foreach ($events as $dom => $event){ 
		$text2 .= show_event($event);
	} 
	$text2 .= $tp -> parseTemplate($EVENT_EVENTLIST_TABLE_END, FALSE, $calendar_shortcodes);
}

// event archive
$nextmonth = mktime(0, 0, 0, $month + 1, 1, $year)-1;
if (!isset($next10_start)){
    $next10_start = $nextmonth;
} 
$sql->db_Select("event", "*", "event_start > '{$next10_start}' ORDER BY event_start ASC LIMIT 0,10");
$num = $sql->db_Rows();
if ($num != 0){
	$gen = new convert;
	$archive_events = "";
	while ($events = $sql->db_Fetch()){
		$archive_events .= $tp -> parseTemplate($EVENT_ARCHIVE_TABLE, FALSE, $calendar_shortcodes);
	} 
}else{
	$archive_events = $tp -> parseTemplate($EVENT_ARCHIVE_TABLE_EMPTY, FALSE, $calendar_shortcodes);
} 
$text2 .= $tp -> parseTemplate($EVENT_ARCHIVE_TABLE_START, FALSE, $calendar_shortcodes);
$text2 .= $archive_events;
$text2 .= $tp -> parseTemplate($EVENT_ARCHIVE_TABLE_END, FALSE, $calendar_shortcodes);


$caption = EC_LAN_80; // "Event List";
$ns->tablerender($caption.(isset($cap_title) ? $cap_title : ""), $text2);
require_once(FOOTERF);


function show_event($day_events)
{ 
    $texxt2 = "";
	foreach($day_events as $event)
    {
        global $tp, $cal_super, $_POST, $ds, $thisevent, $EVENT_ID, $EVENT_EVENT_TABLE, $calendar_shortcodes, $event_author_id, $event_author_name;
		$thisevent = $event;
        $gen = new convert;
        if ( ( !isset($_POST['do']) || (isset($_POST['do']) && $_POST['do'] == null)) || (isset($_POST['event_cat_ids']) && $_POST['event_cat_ids'] == "all") || (isset($_POST['event_cat_ids']) && $_POST['event_cat_ids'] == $thisevent['event_cat_id']) ){
			
		//if (($_POST['do'] == null || $_POST['event_cat_ids'] == "all") || ($_POST['event_cat_ids'] == $thisevent['event_cat_id'])){
            $lp = explode(".", $thisevent['event_author']);
            if (ereg("[0-9]+", $lp[0]))
            {
                $event_author_id = $lp[0];
                $event_author_name = $lp[1];
            }
			$text2 = $tp -> parseTemplate($EVENT_EVENT_TABLE, FALSE, $calendar_shortcodes);
        } 
    } 
    return $text2;
} 

function cal_landate($dstamp, $recurring = false, $allday = false)
{
    $long_month_start = 0;
    $long_day_start = 12;
    $now = getdate($dstamp);

    if ($now['wday'] == 0){
        $now['wday'] = 7;
    } 
    $dow = constant("EC_LAN_".($long_day_start + $now['wday']-1));
    $moy = constant("EC_LAN_".($long_month_start + $now['mon']-1));

    if ($recurring == TRUE){
        $today = getdate();
        $now['year'] = $today['year'];
    } 

    if ($allday == TRUE){
        return sprintf("%s %02d %s %d", $dow, $now['mday'], $moy, $now['year']);
    }else{
		if($now['hours'] == 0 && $now['minutes'] == 0){
			return sprintf("%s %02d %s %d", $dow, $now['mday'], $moy, $now['year'], 0, 0);
		}else{
	        return sprintf("%s %02d %s %d - %02d:%02d", $dow, $now['mday'], $moy, $now['year'], $now['hours'], $now['minutes']);
		}
    } 
} 

function headerjs()
{
    global $cal;
    $script = $cal->load_files();
/*
	$script .= "
	<script type=\"text/javascript\">
	<!--
	function calcheckform(thisform)
	{
		var testresults=true;
		var temp;
		temp = thisform.start_date.value.split(\"-\");
		var sdate = temp[0] + temp[1] + temp[2] + thisform.ne_hour.options[thisform.ne_hour.selectedIndex].value + thisform.ne_minute.options[thisform.ne_minute.selectedIndex].value
		temp = thisform.end_date.value.split(\"-\");
		var edate = temp[0] + temp[1] + temp[2] + thisform.end_hour.options[thisform.end_hour.selectedIndex].value + thisform.end_minute.options[thisform.end_minute.selectedIndex].value

		testresults=true;

		if (edate <= sdate && !thisform.allday.checked && testresults )
		{
			alert('".EC_LAN_99."');
			testresults=false;
		}
		if ((thisform.ne_title.value=='' || thisform.ne_event.value=='') && testresults)
		{
			alert('".EC_LAN_98."');
			testresults=false;
		}

		if (testresults)
		{
			if (thisform.subbed.value=='no')
			{
				thisformm.subbed.value='yes';
				testresults=true;
			}
		else
			{
				alert('".EC_LAN_113."');
				return false;
			}
		}
		return testresults;
	}
	-->
	</script>";
*/
	return $script;
} 

?>