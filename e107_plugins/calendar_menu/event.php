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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/event.php,v $
|     $Revision: 1.12 $
|     $Date: 2009-10-29 11:35:59 $
|     $Author: marj_nl_fr $
|
+----------------------------------------------------------------------------+
*/
require_once('../../class2.php');
if (!isset($pref['plug_installed']['calendar_menu'])) header("Location: ".e_BASE."index.php");

if (isset($_POST['viewallevents']))
{  // Triggered from NAV_BUT_ALLEVENTS
    Header("Location: ".e_PLUGIN."calendar_menu/calendar.php?".$_POST['enter_new_val']);
}

if (isset($_POST['doit']))
{  // Triggered from NAV_BUT_ENTEREVENT
    Header("Location: ".e_PLUGIN."calendar_menu/event.php?ne.".$_POST['enter_new_val']);
	exit;
}

if (isset($_POST['subs']))
{
    Header("Location: ".e_PLUGIN."calendar_menu/subscribe.php");
}

if (isset($_POST['printlists']))
{
    Header("Location: " . e_PLUGIN . "calendar_menu/ec_pf_page.php");
} 

include_lan(e_PLUGIN."calendar_menu/languages/".e_LANGUAGE.".php");
define("PAGE_NAME", EC_LAN_80);

require_once(e_PLUGIN.'calendar_menu/ecal_class.php');
global $ecal_class;
if (!is_object($ecal_class)) $ecal_class = new ecal_class;
$cal_super = $ecal_class->cal_super;

require_once(e_PLUGIN."calendar_menu/calendar_shortcodes.php");
require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);

$cat_filter = intval(varset($_POST['event_cat_ids'],-1));
if ($cat_filter == -1) $cat_filter = '*';
$mult_count = 0;


// $e_wysiwyg = $pref['eventpost_editmode'] == 2 ? 'ne_event' : '';


// Array links db field names to internal variables
$ev_fields = array(
	'event_id' 		=> 'id',
	'event_start' 	=> 'ev_start',
	'event_end' 	=> 'ev_end',
	'event_allday' 	=> 'ev_allday',
	'event_recurring' => 'recurring',
	'event_title' 	=> 'ev_title',
	'event_location' => 'ev_location',
	'event_details' => 'ev_event',
//	'event_author' 	=> 'ne_author',			- not needed - its always the user creating the event
	'event_contact' => 'ev_email',
	'event_category' => 'ev_category',
	'event_thread' 	=> 'ev_thread'
);

//--------------------------------------
// Event to add or update
//--------------------------------------
if ((isset($_POST['ne_insert']) || isset($_POST['ne_update'])) && ($cal_super  || check_class($pref['eventpost_admin'])))
{  
  $ev_start		= $ecal_class->make_date($_POST['ne_hour'], $_POST['ne_minute'],$_POST['start_date']);
  if (($_POST['ne_event'] == "") || !isset($_POST['qs']))
  {	// Problem - tell user to go away - fields are blank (mostly checked by JS)
	header("location:event.php?".$ev_start.".0.m3");
  }
  elseif (!isset($_POST['ne_category']) || (intval($_POST['ne_category']) == 0))
  {
	header("location:event.php?".$ev_start.".0.m6");
  }
  else
  {
	$ev_end			= $ecal_class->make_date($_POST['end_hour'], $_POST['end_minute'],$_POST['end_date']);
    $ev_title		= $tp->toDB($_POST['ne_title']);
    $ev_location	= $tp->toDB($_POST['ne_location']);
    $ev_event		= $tp->toDB($_POST['ne_event']);
	$ev_email		= $tp -> toDB($_POST['ne_email']);
	$ev_category	= intval($_POST['ne_category']);
	$ev_thread		= $tp -> toDB($_POST['ne_thread']);
	$temp_date 		= getdate($ecal_class->make_date(0,0,$_POST['start_date']));
	$ev_allday		= intval($_POST['allday']);
	$recurring		= intval($_POST['ec_recur_type']);
	if ($recurring >= 100) $recurring += intval($_POST['ec_recur_week']) - 100;
	// 
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
      $qs = preg_replace("/ne./i", "", $_POST['qs']);	
	  if ($_POST['ec_gen_multiple'])
	  {
	    $mult_count = $ecal_class->gen_recur($ev_start,$ev_end,$recurring,$ev_start,$ev_end);
	  }
	  if ($mult_count <= 1)
	  {
		$qry = " 0, '".intval($ev_start)."', '".intval($ev_end)."', '".$ev_allday."', '".$recurring."', '".time()."', '$ev_title', '$ev_location', '$ev_event', '".USERID.".".USERNAME."', '".$ev_email."', '".$ev_category."', '".$ev_thread."', '".intval($rec_m)."', '".intval($rec_y)."' ";
        $sql->db_Insert("event", $qry);

		$id = mysql_insert_id();
		$data = array('method'=>'create', 'table'=>'event', 'id'=>$id, 'plugin'=>'calendar_menu', 'function'=>'dbCalendarCreate');
		$e_event->triggerHook($data);

		$ecal_class->cal_log(1,'db_Insert',$qry, $ev_start);
		$report_msg = '.m4';
	  }
	}
	
	if (isset($_POST['ne_update']))
	{  // Bits specific to updating an existing event
		$qry = "event_start='".intval($ev_start)."', event_end='".intval($ev_end)."', event_allday='".$ev_allday."', event_recurring='".$recurring."', event_datestamp= '".time()."', event_title= '$ev_title', event_location='$ev_location', event_details='$ev_event', event_contact='".$ev_email."', event_category='".$ev_category."', event_thread='".$ev_thread."', event_rec_m='".intval($rec_m)."', event_rec_y='".intval($rec_y)."' WHERE event_id='".intval($_POST['id'])."' ";
        $sql->db_Update("event", $qry);

		$data = array('method'=>'update', 'table'=>'event', 'id'=>intval($_POST['id']), 'plugin'=>'calendar_menu', 'function'=>'dbCalendarUpdate');
		$e_event->triggerHook($data);

		$ecal_class->cal_log(2,'db_Update',$qry, $ev_start);
        $qs = preg_replace("/ed./i", "", $_POST['qs']);
		$report_msg = '.m5';
	}
	if ($mult_count <= 1)
	{
	// Now clear cache  - just do the lot for now - get clever later
	$e107cache->clear('nq_event_cal');
    header("location:event.php?".$ev_start.".".$qs.$report_msg);
	}
  }
}



$action = "";		// Remove notice

require_once(HEADERF);

if ($mult_count > 1)
{	// Need to handle writing of multiple events - display confirmation form
	$message = str_replace('-NUM-', count($mult_count), EC_LAN_88);
	$text = "
		<form method='post' action='".e_SELF."?mc.{$ev_start}.{$ev_end}' id='mulconf'><table style='width:98%' class='fborder' >
		<colgroup><col style='width:30%' /><col style='width:70%' /></colgroup>
		<tr><td class='forumheader3 warning' colspan='2'>".$message."<br />".EC_LAN_89."</td></tr>";
	if ($ev_allday)
	{
      $text .= "
		<tr><td class='forumheader3' >".EC_LAN_173." </td><td class='forumheader3'> ".$ecal_class->event_date_string($ev_start)." ".EC_LAN_175."</td></tr>
		<tr><td class='forumheader3' >".EC_LAN_174." </td><td class='forumheader3'> ".$ecal_class->event_date_string($ev_end)." ".EC_LAN_175."</td></tr>";
	}
	else
	{
      $text .= "
		<tr><td class='forumheader3' >".EC_LAN_173." </td><td class='forumheader3'> ".$ecal_class->event_date_string($ev_start)." ".$ecal_class->time_string($ev_start)." "."</td></tr>
		<tr><td class='forumheader3' >".EC_LAN_174." </td><td class='forumheader3'> ".$ecal_class->event_date_string($ev_end)." ".$ecal_class->time_string($ev_end)." "."</td></tr>";
	}
    $text .= "
		<tr><td class='forumheader3'>".EC_LAN_176."</td><td class='forumheader3'>".$ecal_class->get_recur_text($recurring)."</td></tr>
		<tr><td class='forumheader3'>".EC_LAN_70."</td><td class='forumheader3'>".$ev_title."</td></tr>
		<tr><td class='forumheader3'>".EC_LAN_52."</td><td class='forumheader3'>".$ecal_class->get_category_text($ev_category)."</td></tr>
		<tr><td class='forumheader3'>".EC_LAN_32."</td><td class='forumheader3'>".$ev_location."</td></tr>
		<tr><td class='forumheader3'>".EC_LAN_57."</td><td class='forumheader3'>".$ev_event."</td></tr>";
		
    // Only display for forum thread/link if required.  No point if not wanted
    if (isset($pref['eventpost_forum']) && $pref['eventpost_forum'] == 1)
    {
      $text .= "<tr><td class='forumheader3'>".EC_LAN_58." </td><td class='forumheader3'>".$ev_thread."</td></tr>";
    }
    $text .= "<tr><td class='forumheader3'>".EC_LAN_59."</td><td class='forumheader3'>".$ev_email."</td></tr>
		<tr><td class='forumheader' colspan='2' style='text-align:center'>
            <input class='button' type='submit' name='mc_cancel' value='".EC_LAN_177."' />
            <input class='button' type='submit' name='mc_accept' value='".EC_LAN_178."' />
			<input type='hidden' name='qs' value='".e_QUERY."' />";
	foreach ($ev_fields as $k => $v)
	{
	  $text .= "<input type='hidden' name='ev_{$k}' value='".$$v."' />";
	}
	$text .= "</td></tr></table></form>";

        $ns->tablerender(EC_LAN_179, $text);
        require_once(FOOTERF);
        exit;
}

if (isset($_POST['jump']))
{
  $smarray	= getdate(mktime(0, 0, 0, $_POST['jumpmonth'], 1, $_POST['jumpyear']));
  $month	= $smarray['mon'];
  $year		= $smarray['year'];
}
else
{
  if (e_QUERY)
	{
		$qs			= explode(".", e_QUERY);
		$action		= $qs[0];			// Often a date if just viewing
		$ds			= varset($qs[1],"");
		$eveid		= varset($qs[2], "");
	}

    if ($action == "")
    {
        $month		= $ecal_class->cal_date['mon'];
        $year		= $ecal_class->cal_date['year'];
    }
    else
    {
        if (is_numeric($action)) $smarray = getdate($action); else $smarray = getdate($ds);
        $month		= $smarray['mon'];
        $year		= $smarray['year'];
    }
}


if ($cal_super || check_class($pref['eventpost_admin']))
{  // Bits relating to 'delete event', and generation of multiple events
  if ($action == 'mc')
  {
    if (isset($_POST['mc_cancel']))
	{
	  $message = EC_LAN_179;
	}
	elseif (isset($_POST['mc_accept']))
	{   // Go for it! Write lots of events
	  // Start by reading all the info from the hidden fields
	  $wr_record = array();
	  foreach ($ev_fields as $k => $v)
	  {
		$wr_record[$k] = $tp->toDB($_POST['ev_'.$k]);
	  }
	  $wr_record['event_author'] = USERID.".".USERNAME;
	  $wr_record['event_datestamp'] = time();
      $mult_count = $ecal_class->gen_recur($wr_record['event_start'],$wr_record['event_end'],$wr_record['event_recurring'],$wr_record['event_start'],$wr_record['event_end']);
	  $wr_record['event_recurring'] = 0;		// Individual events are non-recurring!
	  
	// Now write all the entries
	  $wc = 0;
	  foreach ($mult_count as $mc)
	  {
		$wr_record['event_start'] = $mc;
		$wr_record['event_end'] = merge_date_time($mc,$wr_record['event_end']);
//		echo "Write record: ".$wr_record['event_start']." to ".$wr_record['event_end']."<br />";
      if ($sql->db_Insert("event", $wr_record)) $wc++;
	  }
	  $ecal_class->cal_log(5,'db_Insert',$qry, $ev_start);

	  $message = str_replace('-NUM-',$wc,EC_LAN_41);
	  if ($wc != count($mult_count)) $message .= "<br /><br />".(count($mult_count)-$wc)." ".EC_LAN_180;
	}
	$action = '';
  }
  
  
  if (isset($_POST['confirm']))
  {
	$qry = "event_id='".intval($_POST['existing'])."' ";
    if ($sql->db_Delete("event", $qry))
    {
        $message = EC_LAN_51; //Event Deleted

		$data = array('method'=>'delete', 'table'=>'event', 'id'=>$_POST['existing'], 'plugin'=>'calendar_menu', 'function'=>'dbEventDelete');
		$message .= $e_event->triggerHook($data);

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
}  // End - if ($cal_super)


// set up data arrays ----------------------------------------------------------------------------------
// (these used in the nav shortcodes)
$monthabb	= Array(EC_LAN_JAN, EC_LAN_FEB, EC_LAN_MAR, EC_LAN_APR, EC_LAN_MAY, EC_LAN_JUN, EC_LAN_JUL, EC_LAN_AUG, EC_LAN_SEP, EC_LAN_OCT, EC_LAN_NOV, EC_LAN_DEC);
$months		= array(EC_LAN_0, EC_LAN_1, EC_LAN_2, EC_LAN_3, EC_LAN_4, EC_LAN_5, EC_LAN_6, EC_LAN_7, EC_LAN_8, EC_LAN_9, EC_LAN_10, EC_LAN_11);
// ----------------------------------------------------------------------------------------------------------

// Messages acknowledging actions
$poss_message = array('m1' => EC_LAN_41, 'm2' => EC_LAN_42, 'm3' => EC_LAN_43, 'm4' => EC_LAN_44, 'm5' => EC_LAN_45,
					  'm6' => EC_LAN_145, 'm7' => 'Could have saved -NUM- events');
if (isset($qs[2])) if (isset($poss_message[$qs[2]]))
{
 $message = $poss_message[$qs[2]];
 $ec = varset($qs[3],0);
 if ($ec) $message = str_replace('-NUM-',$ec,$message);
}

if (isset($message))
{
    $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


function merge_date_time($date, $time)
{
  return ((86400*intval($date/86400)) + ($time % 86400));
}

//-------------------------------------
// 		enter new event form
//-------------------------------------
if ($action == "ne" || $action == "ed")
{
  if ($ecal_class->cal_super || check_class($pref['eventpost_admin']))
  {
	function make_calendar($boxname, $boxvalue)
	{
	  global $ecal_class, $cal;
	
        unset($cal_options);
        unset($cal_attrib);
        $cal_options['firstDay'] = $ecal_class->ec_first_day_of_week;
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

	function recur_select($curval)
	{
	  global $ecal_class;
	  while ($curval > 150) { $curval -= 100; }		// Could have values up to about 406
	  $ret = "<select class='tbox' name='ec_recur_type' onchange=\"proc_recur(this.value);\">";
	  foreach ($ecal_class->recur_type as $k => $v)
	  {
	    $selected = ($curval == $k) ? " selected = 'selected'" : "";
	    $ret .= "<option value='{$k}'{$selected}>{$v}</option>\n";
	  }
	  $ret .= "</select>\n";
	  return $ret;
	}
	
	
	function recur_week_select($curval)
	{
	  global $ecal_class;
	  $disp = $curval < 100 ? " style='display:none;'" : "";
	  $curval -= intval($curval % 10);		// Should make it an exact multiple of 100
	  $ret = "<span id='rec_week_sel'{$disp}><select class='tbox' name='ec_recur_week'>";
	  foreach ($ecal_class->recur_week as $k => $v)
	  {
	    $selected = ($curval == $k) ? " selected = 'selected'" : "";
	    $ret .= "<option value='{$k}'{$selected}>{$v}</option>\n";
	  }
	  $ret .= "</select></span>\n";
	  return $ret;
	}


	switch ($action)
	{
      case "ed" :	// Editing existing event - read from database
        $sql->db_Select("event", "*", "event_id='".intval($qs[1])."' ");
        list($null, $ne_start, $ne_end, $allday, $recurring, $ne_datestamp, $ne_title, $ne_location, $ne_event, $ne_author, $ne_email, $ne_category, $ne_thread) = $sql->db_Fetch();

        $smarray = $ecal_class->gmgetdate($ne_start);
        $ne_hour = $smarray['hours'];
        $ne_minute = $smarray['minutes'];
        $ne_startdate = $ecal_class->full_date($ne_start);

        $smarray = $ecal_class->gmgetdate($ne_end);
        $end_hour = $smarray['hours'];
        $end_minute = $smarray['minutes'];
        $ne_enddate = $ecal_class->full_date($ne_end);

        $caption = EC_LAN_66; // edit Event
        break;
		
	  case "ne" :	// New event - initialise everything
        $smarray = $ecal_class->gmgetdate($qs[1]);
        $month = $smarray['mon'];
        $year = $smarray['year'];
        $ne_startdate = $ecal_class->full_date($qs[1]);

        $ne_hour = $smarray['hours'];
        $ne_minute = $smarray['minutes'];

        $end_hour = $smarray['hours'];
        $end_minute = $smarray['minutes'];
        $ne_enddate = $ecal_class->full_date($qs[1]);
		$recurring = 0;
        $caption = EC_LAN_28; // Enter New Event

	  default :
        $caption = EC_LAN_83;
	}


	$text = "
		<script type=\"text/javascript\">
		<!--
		function check_mult(val)
		{
		  if (val == true)
		  {
		    alert('".EC_LAN_87."');
		  }
		}
		
		function proc_recur(rec_value)
		{
		  if(document.getElementById('rec_week_sel')) 
		  {
			target=document.getElementById('rec_week_sel').style;
			if (rec_value >= 100)
		    {
			  target.display = '';
//		      alert('show');
			}
			else
			{
			  target.display = 'none';
//		      alert('hide');
			}
		  }
		  if(document.getElementById('gen_multiple')) 
		  {
			target=document.getElementById('gen_multiple').style;
			if (rec_value > 0)
		    {
			  target.display = '';
			}
			else
			{
			  target.display = 'none';
			}
		  }
		}
		
		function calcheckform(thisform, submitted,arrstr)
		{
			var testresults=true;

			function calcdate(thisval)
			{
			  var temp1;
			  temp1 = thisval.split(\"{$ecal_class->date_separator}\");
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
		<table style='width:98%' class='fborder' ><colgroup><col style='width:20%' /><col style='width:80%' /></colgroup>";

    $text .= "
		<tr><td class='forumheader3'>".EC_LAN_72." </td><td class='forumheader3'> ".EC_LAN_67." ";

    $text .= make_calendar("start_date",$ne_startdate)."&nbsp;&nbsp;&nbsp;".EC_LAN_73." ".make_calendar("end_date",$ne_enddate);
    $text .= "</td></tr>
		<tr><td class='forumheader3'>".EC_LAN_71." </td><td class='forumheader3'>".EC_LAN_67;

	$text .= make_hourmin("ne_",$ne_hour,$ne_minute)."&nbsp;&nbsp;".EC_LAN_73.make_hourmin('end_',$end_hour,$end_minute);
	$text .= "<br /><input type='checkbox' name='allday' value='1' ".(isset($allday) && $allday == 1 ? "checked='checked'" :"")." />";
    $text .= EC_LAN_64."
		</td></tr>

		<tr><td class='forumheader3'>".EC_LAN_65."</td><td class='forumheader3'>";
	$text .= recur_week_select($recurring)."&nbsp;&nbsp;".recur_select($recurring);
	$disp = $recurring && ($action == 'ne') ? '' : " style='display:none;'";
	$text .= "<span id='gen_multiple'{$disp}><input type='checkbox' name='ec_gen_multiple' value='1' onchange=\"check_mult(this.checked);\"/>".EC_LAN_86."</span>";
    $text .= "<br /><span class='smalltext'>".EC_LAN_63."</span>
		</td></tr>
		<tr><td class='forumheader3'>".EC_LAN_70." *</td><td class='forumheader3'>
		<input class='tbox' type='text' name='ne_title' size='75' value='".(isset($ne_title) ? $ne_title : "")."' maxlength='200' style='width:95%' />
		</td></tr>

		<tr><td class='forumheader3'>".EC_LAN_52." </td><td class='forumheader3'>
		<select name='ne_category' class='tbox'>";
        // Check if supervisor, if so get all categories, otherwise just get those the user is allowed to see
		// Always exclude the default categories
	$cal_arg = ($ecal_class->cal_super ? "" : "find_in_set(event_cat_addclass,'".USERCLASS_LIST."') AND ");
	$cal_arg .= "(event_cat_name != '".EC_DEFAULT_CATEGORY."') ";
    if ($sql->db_Select("event_cat", "*", $cal_arg))
		{
            while ($row = $sql->db_Fetch())
			{
				$text .= "<option value='{$row['event_cat_id']}' ".(isset($ne_category) && $ne_category == $row['event_cat_id'] ? "selected='selected'" :"")." >".$row['event_cat_name']."</option>";
            }
        }
		else
		{
            $text .= "<option value=''>".EC_LAN_91."</option>";
        }
        $text .= "</select>
		</td></tr>";

		switch ($pref['eventpost_editmode'])
		{
		  case 1  : 
			$insertjs = "rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";
			break;
		  case 2  : 
			$insertjs = "rows='25' ";
			break;
		  default : $insertjs = "rows='15' ";
		}

        $text .= "
		<tr><td class='forumheader3'>".EC_LAN_32." </td><td class='forumheader3'>
		<input class='tbox' type='text' name='ne_location' size='60' value='".(isset($ne_location) ? $ne_location : "")."' maxlength='200' style='width:95%' />
		</td></tr>

		<tr><td class='forumheader3'>".EC_LAN_57." *</td><td class='forumheader3'>
		<textarea class='tbox' id='ne_event' name='ne_event' cols='59' style='width:95%' {$insertjs}>".(isset($ne_event) ? $ne_event : "")."</textarea>";
		if ($pref['eventpost_editmode'] == 1)
		{
		  // Show help
		  require_once(e_HANDLER."ren_help.php");
		  $text .= "<br />".display_help("helpb", 'event');
		}
		
		$text .= "</td></tr>";

        // Only display for forum thread/link if required.  No point if not wanted
        if (isset($pref['eventpost_forum']) && $pref['eventpost_forum'] == 1)
        {
            $text .= "
			<tr><td class='forumheader3'>".EC_LAN_58." </td><td class='forumheader3'>
			<input class='tbox' type='text' name='ne_thread' size='60' value='".(isset($ne_thread) ? $ne_thread : "")."' maxlength='100' style='width:95%' />
			</td></tr>";
        }

        // If the user is logged in and has their email set plus the field is empty then put in
        // their email address.  They can always take it out if they want, its not a required field
        if (empty($ne_email) && ($action == "ne") && defined('USEREMAIL'))
        {
            $ne_email = USEREMAIL;
        }
        $text .= "
		<tr><td class='forumheader3'>".EC_LAN_59." </td><td class='forumheader3'>
		<input class='tbox' type='text' name='ne_email' size='60' value='$ne_email' maxlength='150' style='width:95%' />
		</td></tr>";

		//triggerHook
		$hid = ($action=='ed' ? intval($qs[1]) : '');
		$data = array('method'=>'form', 'table'=>'event', 'id'=>$hid, 'plugin'=>'calendar_menu', 'function'=>'CalendarCreate');
		$hooks = $e_event->triggerHook($data);
		if(!empty($hooks))
		{
			$text .= "<tr><td class='forumheader3' colspan='2' >".LAN_HOOKS." </td></tr>";
			foreach($hooks as $hook)
			{
				if(!empty($hook))
				{
					$text .= "
					<tr>
					<td class='forumheader3'>".$hook['caption']."</td>
					<td class='forumheader3'>".$hook['text']."</td>
					</tr>";
				}
			}
		}

		$text .= "
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
}   // End of "Enter New Event"


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

if ($ds == 'one')
{
  $prop		= gmmktime(0, 0, 0, $month, $smarray['mday'], $year);		// Sets start date for new event entry - exact day of month
}
else
{
  $prop		= gmmktime(0, 0, 0, $month, 1, $year);		// Sets start date for new event entry - beginning of month
}
$next		= mktime(0, 0, 0, $nextmonth, 1, $nextyear);	// Used by nav buttons
$nowmonth	= $ecal_class->cal_date['mon'];
$nowyear	= $ecal_class->cal_date['year'];


$py				= $year-1;
$prevlink		= mktime(0, 0, 0, $month, 1, $py);
$ny				= $year + 1;
$nextlink		= mktime(0, 0, 0, $month, 1, $ny);

if (is_readable(THEME.'calendar_template.php')) 
{  // Has to be require
  require(THEME.'calendar_template.php');
}
else 
{
  require(e_PLUGIN.'calendar_menu/calendar_template.php');
}

$text2 = "";
// time switch buttons
$text2 .= $tp -> parseTemplate($CALENDAR_TIME_TABLE, FALSE, $calendar_shortcodes);

// navigation buttons
$text2 .= $tp -> parseTemplate($CALENDAR_NAVIGATION_TABLE, FALSE, $calendar_shortcodes);


// ****** CAUTION - the category dropdown also used $sql object - take care to avoid interference!

$ev_list = array();


if ($ds == "event")
{		// Show single event - bit of a special case
	$ec_err = FALSE;
	$qry = "
	SELECT e.*, ec.event_cat_name,ec.event_cat_icon
	FROM #event as e
	LEFT JOIN #event_cat as ec ON e.event_category = ec.event_cat_id
	WHERE e.event_id='".intval($eveid)."'
	{$ecal_class->extra_query} 
	";
	$sql2->db_Select_gen($qry);
    $thisevent = $sql2->db_Fetch();
	// Recurring events - $action has the actual date required (no time) - could be one of a potentially large number of dates it referred to
    if ($thisevent['event_recurring']>='1')			// Single event, selected by ID. So day/month must match
    {
	  $temp_arr = $ecal_class->gen_recur($thisevent['event_start'],$thisevent['event_end'],$thisevent['event_recurring'],$action,$action+86400);  // Array of start times - hopefully just one!
	  if (count($temp_arr) == 1)
	  {
	    $thisevent['event_start'] = $temp_arr[0];
	    $thisevent['event_end'] = merge_date_time($action,$thisevent['event_end']);
	  }
	  else
	  {   // Error
	    $ec_err = TRUE;
	  }
    }
    $next10_start = $thisevent['event_start'] +1;
	$text2 .= $tp -> parseTemplate($EVENT_EVENT_TABLE_START, FALSE, $calendar_shortcodes);
	if ($ec_err) $text2.= "Software Error<br />"; else $text2 .= $tp -> parseTemplate($EVENT_EVENT_TABLE, TRUE, $calendar_shortcodes);
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
  }
  else
  {  // Display whole of selected month
    $start_time		= $monthstart;
    $end_time		= $monthend;
	$next10_start   = $end_time + 1;
    $cap_title		= '';
  }

//  echo "Start: ".$start_time."  End: ".$end_time."  Cat_filter: ".$cat_filter."<br />";
	// We'll need virtually all of the event-related fields, so get them regardless
  $ev_list = $ecal_class->get_events($start_time, $end_time, FALSE, $cat_filter, TRUE, '*', 'event_cat_name,event_cat_icon');

//  echo count($ev_list)." records found<br />";
  
// Now go through and multiply up any recurring records
  $tim_arr = array();
  foreach ($ev_list as $k=>$event)
  {
    if (is_array($event['event_start']))
	{
	  foreach ($event['event_start'] as $t)
	  {
	    $tim_arr[$t] = $k;
	  }
	}
	else
	{
	  $tim_arr[$event['event_start']] = $k;
	}
  }
  
  // Add a sort in here
  ksort($tim_arr);

// display event list for current month
  if(count($tim_arr))
  {
	$text2 .= $tp -> parseTemplate($EVENT_EVENTLIST_TABLE_START, FALSE, $calendar_shortcodes);
	foreach ($tim_arr as $tim => $ptr)
	{
	  $ev_list[$ptr]['event_start'] = $tim;
//	  $text2 .= show_event($ev_list[$ptr]);
	  $thisevent = $ev_list[$ptr];
	  $text2 .= $tp -> parseTemplate($EVENT_EVENT_TABLE, TRUE, $calendar_shortcodes);
	}
	$text2 .= $tp -> parseTemplate($EVENT_EVENTLIST_TABLE_END, FALSE, $calendar_shortcodes);
  }
}


// Now display next 10 events
//echo "Next 10 start: ".$next10_start."<br />";
$ev_list = $ecal_class->get_n_events(10, $next10_start, $next10_start+86400000, $cat_filter, TRUE, 
						'event_id,event_start, event_title', 'event_cat_name, event_cat_icon');


$num = count($ev_list);
if ($num != 0)
{
  $archive_events = "";
  foreach ($ev_list as $thisevent)
  {
//    echo "Event start: ".$thisevent['event_start']."<br />";
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

// Claim back memory no longer required
unset($ev_list);
unset($text2);
unset($tim_arr);

require_once(FOOTERF);



function headerjs()
{
  global $cal;
  $script = $cal->load_files();
  return $script;
}

?>
