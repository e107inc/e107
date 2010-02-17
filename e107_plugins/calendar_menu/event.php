<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jali.@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/event.php,v $
|     $Revision$
|     $Date$
|     $Author$
|
| 09.11.06 - Started next batch of mods
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(e_PLUGIN."calendar_menu/calendar_shortcodes.php");

if (isset($_POST['viewallevents']))
{  // Triggered from NAV_BUT_ALLEVENTS
    Header("Location: ".e_PLUGIN."calendar_menu/calendar.php?".$_POST['enter_new_val']);
}

if (isset($_POST['doit']))
{  // Triggered from NAV_BUT_ENTEREVENT
    Header("Location: ".e_PLUGIN."calendar_menu/event.php?ne.".$_POST['enter_new_val']);
}

if (isset($_POST['subs']))
{
    Header("Location: ".e_PLUGIN."calendar_menu/subscribe.php");
}

include_lan(e_PLUGIN."calendar_menu/languages/".e_LANGUAGE.".php");
define("PAGE_NAME", EC_LAN_80);

require_once(e_PLUGIN.'calendar_menu/ecal_class.php');
global $ecal_class;
$ecal_class = new ecal_class;
$cal_super = $ecal_class->cal_super;

require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);

$category_filter = "";
if  ((isset($_POST['event_cat_ids']) && $_POST['event_cat_ids'] != "all"))
{
  $category_filter = " AND (e.event_category = '".intval($_POST['event_cat_ids'])."') ";
}



// Event to add or update
if ((isset($_POST['ne_insert']) || isset($_POST['ne_update'])) && ($cal_super || check_class($pref['eventpost_admin'])))
{  
	if (($_POST['ne_event'] == '') || !isset($_POST['qs']))
	{	// Problem - tell user to go away
		header('location:event.php?'.$ev_start.'.0.m8');
		exit;
	}
	else
	{
		if (!$cal_super)
		{
			$evCat = intval($_POST['ne_category']);
			if ($sql->db_Select('event_cat', 'event_cat_addclass', 'event_cat_id = '.$evCat))
			{
				$row = $sql->db_Fetch(MYSQL_ASSOC);
				if (!check_class($row['event_cat_addclass']))
				{
					header('location:event.php?'.$ev_start.'.0.m8');
					exit;
				}
			}
			else
			{		// Invalid category - definitely go away!
				header('location:'.e_BASE.'index.php');
				exit;
			}
		}
		$ev_start		= $ecal_class->make_date($_POST['ne_hour'], $_POST['ne_minute'],$_POST['start_date']);
		$ev_end			= $ecal_class->make_date($_POST['end_hour'], $_POST['end_minute'],$_POST['end_date']);
		$ev_title		= $tp->toDB($_POST['ne_title']);
		$ev_location	= $tp->toDB($_POST['ne_location']);
		$ev_event		= $tp->toDB($_POST['ne_event']);
		$temp_date 		= getdate($ecal_class->make_date(0,0,$_POST['start_date']));
		if ($_POST['recurring'] == 1)
		{
		  $rec_m = $temp_date['mday'];		// Day of month
		  $rec_y = $temp_date['mon'];			// Month number
		}
		else
		{
		  $rec_m = "";
		  $rec_y = "";
		}
		
		$report_msg = '.m3';
		if (isset($_POST['ne_insert']))
		{  // Bits specific to inserting a new event
			$qry = " 0, '".intval($ev_start)."', '".intval($ev_end)."', '".intval($_POST['allday'])."', '".intval($_POST['recurring'])."', '".time()."', '$ev_title', '$ev_location', '$ev_event', '".USERID.".".USERNAME."', '".$tp -> toDB($_POST['ne_email'])."', '".intval($_POST['ne_category'])."', '".$tp -> toDB($_POST['ne_thread'])."', '".intval($rec_m)."', '".intval($rec_y)."' ";
			$sql->db_Insert("event", $qry);
			$ecal_class->cal_log(1,'db_Insert',$qry, $ev_start);
			$qs = preg_replace("/ne./i", "", $_POST['qs']);	
			$report_msg = '.m4';
		}
		
		if (isset($_POST['ne_update']))
		{  // Bits specific to updating an existing event
			$qry = "event_start='".intval($ev_start)."', event_end='".intval($ev_end)."', event_allday='".intval($_POST['allday'])."', event_recurring='".intval($_POST['recurring'])."', event_datestamp= '".time()."', event_title= '$ev_title', event_location='$ev_location', event_details='$ev_event', event_contact='".$tp -> toDB($_POST['ne_email'])."', event_category='".intval($_POST['ne_category'])."', event_thread='".$tp -> toDB($_POST['ne_thread'])."', event_rec_m='".intval($rec_m)."', event_rec_y='".intval($rec_y)."' WHERE event_id='".intval($_POST['id'])."' ";
			$sql->db_Update("event", $qry);
			$ecal_class->cal_log(2,'db_Update',$qry, $ev_start);
			$qs = preg_replace("/ed./i", "", $_POST['qs']);
			$report_msg = '.m5';
		}
		// Now clear cache  - just do the lot for now - get clever later
		$e107cache->clear('nq_event_cal');
		header("location:event.php?".$ev_start.".".$qs.$report_msg);
	}
}

$action = "";		// Remove notice

require_once(HEADERF);

if (isset($_POST['jump']))
{
  $smarray	= getdate(mktime(0, 0, 0, $_POST['jumpmonth'], 1, $_POST['jumpyear']));
  $month	= $smarray['mon'];
  $year		= $smarray['year'];
}
else
{
    if(e_QUERY)
	{
		$qs			= explode(".", e_QUERY);
		$action		= $qs[0];			// Often a date if just viewing
		$ds			= (isset($qs[1]) ? $qs[1] : "");
		$eveid		= (isset($qs[2]) ? $qs[2] : "");
	}
	
    if ($action == "")
    {
        $month		= $ecal_class->cal_date['mon'];
        $year		= $ecal_class->cal_date['year'];
    }
    else
    {
        $smarray	= getdate($action);
		$day 		= $smarray['mday'];
        $month		= $smarray['mon'];
        $year		= $smarray['year'];
    }
}


if (isset($_POST['confirm']))
{
	$qry = "event_id='".intval($_POST['existing'])."' ";
    if ($sql->db_Delete("event", $qry))
    {
        $message = EC_LAN_51; //Event Deleted
		$ecal_class->cal_log(3,'db_Delete',$qry,$ev_start);
    }
    else
    {
        $message = EC_LAN_109; //Unable to Delete event for some mysterious reason
    }
}


if ($action == "de")
{  // Delete event - show confirmation form
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
{    // Delete Cancelled
    $message = EC_LAN_47;
}


// set up data arrays ----------------------------------------------------------------------------------
// (some of these are only used in the shortcodes)
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

// Messages acknowledging actions
$poss_message = array('m1' => EC_LAN_41, 'm2' => EC_LAN_42, 'm3' => EC_LAN_43, 'm4' => EC_LAN_44, 'm5' => EC_LAN_45, 'm8' => EC_LAN_181);
if (isset($qs[2])) if (isset($poss_message[$qs[2]])) $message = $poss_message[$qs[2]];

if (isset($message))
{
    $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


// enter new event form
if ($action == "ne" || $action == "ed")
{
    if ($ecal_class->cal_super || check_class($pref['eventpost_admin']))
    {
function make_calendar($boxname, $boxvalue)
{
	global $ecal_class, $cal;
	
        unset($cal_options);
        unset($cal_attrib);
        $cal_options['showsTime'] = false;
        $cal_options['showOthers'] = true;
        $cal_options['weekNumbers'] = false;
        $cal_options['ifFormat'] = $ecal_class->dcal_format_string;
        $cal_attrib['class'] = "tbox";
        $cal_attrib['size'] = "12";
        $cal_attrib['name'] = $boxname;
        $cal_attrib['value'] = $boxvalue;
        return $cal->make_input_field($cal_options, $cal_attrib);
}


function make_hourmin($boxname,$cur_hour,$cur_minute)
{
  global $pref;
  if (isset($pref['eventpost_fivemins'])) $incval = 5; else $incval = 1;
  $retval = " <select name='{$boxname}hour' id='{$boxname}hour' class='tbox'>\n";
  for($count = "00"; $count <= "23"; $count++)
  {
    $val = sprintf("%02d", $count);
    $retval .= "<option value='{$val}' ".(isset($cur_hour) && $count == $cur_hour ? "selected='selected'" :"")." >".$val."</option>\n";
  }
  $retval .= "</select>\n
		<select name='{$boxname}minute' class='tbox'>\n";
  for($count = "00"; $count <= "59"; $count+= $incval)
  {
    $val = sprintf("%02d", $count);
    $retval .= "<option ".(isset($cur_minute) && $count == $cur_minute ? "selected='selected'" :"")." value='{$val}'>".$val."</option>\n";
  }
  $retval .= "</select>\n";
  return $retval;
}


        if ($action == "ed")
        {	// Editing existing event - read from database
            $sql->db_Select("event", "*", "event_id='".intval($qs[1])."' ");
            list($null, $ne_start, $ne_end, $allday, $recurring, $ne_datestamp, $ne_title, $ne_location, $ne_event, $ne_author, $ne_email, $ne_category, $ne_thread) = $sql->db_Fetch();

            $smarray = getdate($ne_start);
            $ne_hour = $smarray['hours'];
            $ne_minute = $smarray['minutes'];
            $ne_startdate = $ecal_class->full_date($ne_start);

            $smarray = getdate($ne_end);
            $end_hour = $smarray['hours'];
            $end_minute = $smarray['minutes'];
            $ne_enddate = $ecal_class->full_date($ne_end);
        }
        else
        {	// New event - initialise everything
            $smarray = getdate($qs[1]);
            $month = $smarray['mon'];
            $year = $smarray['year'];
            $ne_startdate = $ecal_class->full_date($qs[1]);

            $ne_hour = $smarray['hours'];
            $ne_minute = $smarray['minutes'];

            $end_hour = $smarray['hours'];
            $end_minute = $smarray['minutes'];
            $ne_enddate = $ecal_class->full_date($qs[1]);
        }

		$text = "
		<script type=\"text/javascript\">
		<!--
		function calcheckform(thisform, submitted,arrstr)
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

			function calcdate(thisval)
			{
			  var temp1;
			  temp1 = thisval.split(\"-\");
			  switch (arrstr)
			  {
			    case 2 : return temp1[2]+temp1[1]+temp1[0];
			    case 3 : return temp1[2]+temp1[0]+temp1[1];
			    default : return temp1[0]+temp1[1]+temp1[2];
			  }
			  return 'Error';
			}
			//event check - dates are text strings
			var sdate = calcdate(thisform.start_date.value);
			var edate = calcdate(thisform.end_date.value);
			if (edate < sdate)
			{  // Update end date if its before start date
			  thisform.end_date.value = thisform.start_date.value;
//			  alert('End date changed');
			}
			sdate = calcdate(thisform.start_date.value) + thisform.ne_hour.options[thisform.ne_hour.selectedIndex].value + thisform.ne_minute.options[thisform.ne_minute.selectedIndex].value;
			edate = calcdate(thisform.end_date.value) + thisform.end_hour.options[thisform.end_hour.selectedIndex].value + thisform.end_minute.options[thisform.end_minute.selectedIndex].value;
//			alert('Format: ' + arrstr + '    Start date: '+ sdate + '    End date: ' + edate);
			
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
					thisform.subbed.value='yes';
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
		<form method='post' action='".e_SELF."' id='linkform' onsubmit='return calcheckform(this, submitted,{$ecal_class->java_format_code})'>
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


        $text .= make_calendar("start_date",$ne_startdate)."&nbsp;&nbsp;&nbsp;".EC_LAN_73." ".make_calendar("end_date",$ne_enddate);
        $text .= "
		</td>
		</tr>
		<tr>
		<td class='forumheader3' style='width:20%'>".EC_LAN_71." </td>
		<td class='forumheader3' style='width:80%'>
		".EC_LAN_67;
		

		$text .= make_hourmin("ne_",$ne_hour,$ne_minute)."&nbsp;&nbsp;".EC_LAN_73.make_hourmin('end_',$end_hour,$end_minute);
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
        // Check if supervisor, if so get all categories, otherwise just get those the user is allowed to see
		$cal_arg = ($ecal_class->cal_super ? "" : "find_in_set(event_cat_addclass,'".USERCLASS_LIST."')");
        if ($sql->db_Select('event_cat', 'event_cat_id, event_cat_name', $cal_arg))
		{
            while ($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				$text .= "<option value='{$row['event_cat_id']}' ".(isset($ne_category) && $ne_category == $row['event_cat_id'] ? "selected='selected'" :"")." >".$row['event_cat_name']."</option>";
            }
        }
		else
		{
            $text .= "<option value=''>".EC_LAN_91."</option>";
        }
        $text .= "</select>
		</td>
		</tr>";

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
        if (empty($ne_email) && ($action == "ne") && defined('USEREMAIL'))
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
        if ($action == "ed")
		{
            $text .= "<input class='button' type='submit' name='ne_update' value='".EC_LAN_60."' onclick='submitted=this.name' />
			<input type='hidden' name='id' value='".$qs[1]."' />";
        }
		else
		{
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
}   // End of "Enter New Event

//-----------------------------------------------
// show events
// $month, $year have the month required
//-----------------------------------------------
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

$prop		= mktime(0, 0, 0, $month, $day, $year);		// Sets start date for new event entry
$next		= mktime(0, 0, 0, $nextmonth, 1, $nextyear);	// Used by nav buttons
$nowmonth	= $ecal_class->cal_date['mon'];
$nowyear	= $ecal_class->cal_date['year'];


$py				= $year-1;
$prevlink		= mktime(0, 0, 0, $month, 1, $py);
$ny				= $year + 1;
$nextlink		= mktime(0, 0, 0, $month, 1, $ny);

if (is_readable(THEME."calendar_template.php")) 
{  // Has to be require
  require(THEME."calendar_template.php");
}
else 
{
  require(e_PLUGIN."calendar_menu/calendar_template.php");
}

$text2 = "";
// time switch buttons
$text2 .= $tp -> parseTemplate($CALENDAR_TIME_TABLE, FALSE, $calendar_shortcodes);

// navigation buttons
$text2 .= $tp -> parseTemplate($CALENDAR_NAVIGATION_TABLE, FALSE, $calendar_shortcodes);


// ****** CAUTION - the category dropdown also used $sql object - take care to avoid interference!

$event = array();
$extra = '';

if ($ds == "event")
{		// Show single event
	$qry = "
	SELECT e.*, ec.*
	FROM #event as e
	LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
	WHERE e.event_id='".intval($eveid)."'
	{$ecal_class->extra_query} 
	";
	$sql2->db_Select_gen($qry);
    $row = $sql2->db_Fetch();
    if ($row['event_recurring']=='1')			// Single event, selected by ID. So day/month must match
    {
	  $row['event_start'] = mktime(0,0,0,$row['event_rec_y'],$row['event_rec_m'],$year);
	  $row['event_end'] = $row['event_start'];   
    }
    $event[] = $row;
    $next10_start = $event[0]['event_start'];
	$text2 .= $tp -> parseTemplate($EVENT_EVENT_TABLE_START, FALSE, $calendar_shortcodes);
	$text2 .= show_event($event);
	$text2 .= $tp -> parseTemplate($EVENT_EVENT_TABLE_END, FALSE, $calendar_shortcodes);

}
else
{
  if ($ds == 'one')
  {  // Show events from one day
    $tmp			= getdate($action);
    $selected_day	= $tmp['mday'];
    $selected_mon	= $tmp['mon'];
    $start_time		= $action;
    $end_time		= $action + 86399;
	$next10_start   = $end_time + 1;
    $cap_title		= " - ".$months[$selected_mon-1]." ".$selected_day;
    $extra = " OR (e.event_rec_y = ".intval($selected_mon)." AND e.event_rec_m = ".intval($selected_day).") ";
  }
  else
  {  // Display whole of selected month
        $start_time		= $monthstart;
        $end_time		= $monthend;
		$next10_start   = $end_time + 1;
        $cap_title		= '';
  $extra = " OR e.event_rec_y = ".intval($month)." ";
  }


    $qry = "
	SELECT e.*, ec.*
	FROM #event as e
	LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id WHERE (e.event_recurring = '0' AND 
	((e.event_start >= ".intval($start_time)." AND e.event_start <= ".intval($end_time).")
	OR (e.event_end >= ".intval($start_time)." AND e.event_end <= ".intval($end_time).")
	OR (e.event_start <= ".intval($start_time)." AND e.event_end >= ".intval($end_time).") )
	{$extra})
	{$category_filter} 
	{$ecal_class->extra_query} 
	ORDER BY e.event_start ASC
	";

// Query generates a list of event IDs in $idarray which meet the criteria.
// $idarray has one primary index location for each day of month, then secondary for events.
  if ($cal_count=$sql->db_Select_gen($qry))
  {
    while ($row = $sql->db_Fetch())
    {
      if ($row['event_recurring']=='1')	// Recurring events
      {
	    if  (($row['event_rec_y'] == $month) && (!in_array($row['event_id'], $idArray)))		// Only allow one instance of each recurring event
        {
		  $tmp = getdate($row['event_start']);
		  $row['event_start'] = mktime($tmp['hours'],$tmp['minutes'],0,$row['event_rec_y'],$row['event_rec_m'],$year);
		  $row['event_end'] = $row['event_start']; 
          $events[$row['event_rec_m']][] = $row;
          $idArray[] = $row['event_id'];
        }
      }
      else
      {
        if ($ds == 'one')
        {
          if (!isset($idArray) || !is_array($idArray) || !in_array($row['event_id'], $idArray))
          {
            $events[$selected_day][] = $row;
            $idArray[] = $row['event_id'];
          }
        }
        else
        {  // Multiple events
            if ($row['event_start'] < intval($start_time))
            {
              $start_day = "1";		// Event starts before this month
            }
            else
            {
              $tmp		= getdate($row['event_start']);
			  $start_day	= $tmp['mday'];
			}
            if ($row['event_end'] < $row['event_start'])
            {  // End date before start date
              $end_day = $start_day;
            }
            else
            {
              if ($row['event_end'] > intval($end_time))
              {
                $end_day = "31";	// Event ends after this month
              }
              else
              {
                $tmp = getdate($row['event_end']);
                $end_day	= $tmp['mday'];
              }
            }
            for ($i = $start_day; $i <= $end_day; $i++)
            {
              if (!isset($idArray) || !is_array($idArray) || !in_array($row['event_id'], $idArray))
              {
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
if(isset($events) && is_array($events))
{
	$text2 .= $tp -> parseTemplate($EVENT_EVENTLIST_TABLE_START, FALSE, $calendar_shortcodes);
	foreach ($events as $dom => $event){
		$text2 .= show_event($event);
	}
	$text2 .= $tp -> parseTemplate($EVENT_EVENTLIST_TABLE_END, FALSE, $calendar_shortcodes);
}


/*
$nextmonth = mktime(0, 0, 0, $month + 1, 1, $year)-1;
if (!isset($next10_start))
{
    $next10_start = $nextmonth;
}
*/

// Show next 10 events after current event/day/month (doesn't show recurring events)
$qry = "
SELECT e.* FROM #event AS e
LEFT JOIN #event_cat AS ec ON e.event_category = ec.event_cat_id
WHERE e.event_start > '".intval($next10_start)."' {$ecal_class->extra_query} {$category_filter}
ORDER BY e.event_start ASC
LIMIT 0, 10
";

$num = $sql->db_Select_gen($qry);
if ($num != 0)
{
	$gen = new convert;
	$archive_events = "";
//	while ($events = $sql->db_Fetch())
	while ($thisevent = $sql->db_Fetch())
	{
		$archive_events .= $tp -> parseTemplate($EVENT_ARCHIVE_TABLE, FALSE, $calendar_shortcodes);
	}
}
else
{
	$archive_events = $tp -> parseTemplate($EVENT_ARCHIVE_TABLE_EMPTY, FALSE, $calendar_shortcodes);
}

$text2 .= $tp -> parseTemplate($EVENT_ARCHIVE_TABLE_START, FALSE, $calendar_shortcodes);
$text2 .= $archive_events;
$text2 .= $tp -> parseTemplate($EVENT_ARCHIVE_TABLE_END, FALSE, $calendar_shortcodes);


$caption = EC_LAN_80; // "Event List";
$ns->tablerender($caption.(isset($cap_title) ? $cap_title : ""), $text2);
require_once(FOOTERF);


// Display one event in a form which can be expanded.
function show_event($day_events)
{
	global $tp, $cal_super, $_POST, $ds, $thisevent, $EVENT_ID, $EVENT_EVENT_TABLE, $calendar_shortcodes, $event_author_id, $event_author_name;
    $text2 = "";
	foreach($day_events as $event)
    {
		$thisevent = $event;
        $gen = new convert;
            $lp = explode(".", $thisevent['event_author'],2);
            if (preg_match("/[0-9]+/", $lp[0]))
            {
                $event_author_id = $lp[0];
                $event_author_name = $lp[1];
            }
			$text2 .= $tp -> parseTemplate($EVENT_EVENT_TABLE, FALSE, $calendar_shortcodes);
    }
    return $text2;
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
