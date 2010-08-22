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
| 02.11.06 - Cache clear option added
| 10.11.06 - Support for updated templates etc
|
+----------------------------------------------------------------------------+
*/
$eplug_admin = true;		// Make sure we show admin theme
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
if (!getperms("P"))
{
  header("location:".e_BASE."index.php");
  exit;
}


include_lan(e_PLUGIN."calendar_menu/languages/".e_LANGUAGE.".php");


$message = "";
$calendarmenu_text = '';	// Notice removal
$calendarmenu_msg  = '';	// Notice removal

if (isset($_POST['updatesettings'])) {
	$pref['eventpost_admin'] = $_POST['eventpost_admin'];
	$pref['eventpost_adminlog'] = $_POST['eventpost_adminlog'];
	$pref['eventpost_showeventcount'] = $_POST['eventpost_showeventcount'];
	$pref['eventpost_forum'] = $_POST['eventpost_forum'];
	$pref['eventpost_recentshow'] = $_POST['eventpost_recentshow'];
	$pref['eventpost_super'] = $_POST['eventpost_super'];
	$pref['eventpost_menulink'] = $_POST['eventpost_menulink'];
	$pref['eventpost_dateformat'] = $_POST['eventpost_dateformat'];
	$pref['eventpost_fivemins'] = $_POST['eventpost_fivemins'];
	$pref['eventpost_weekstart'] = $_POST['eventpost_weekstart'];
	$pref['eventpost_lenday'] = $_POST['eventpost_lenday'];
	$pref['eventpost_caltime'] = $_POST['eventpost_caltime'];
	$pref['eventpost_datedisplay'] = $_POST['eventpost_datedisplay'];
	$pref['eventpost_timedisplay']	= $_POST['eventpost_timedisplay'];
	$pref['eventpost_timecustom'] = $_POST['eventpost_timecustom'];
	$pref['eventpost_dateevent'] = $_POST['eventpost_dateevent'];
	$pref['eventpost_datenext'] = $_POST['eventpost_datenext'];
	$pref['eventpost_eventdatecustom'] = $_POST['eventpost_eventdatecustom'];
	$pref['eventpost_nextdatecustom'] = $_POST['eventpost_nextdatecustom'];
	$pref['eventpost_mailsubject'] = $_POST['eventpost_mailsubject'];
	$pref['eventpost_mailfrom'] = $_POST['eventpost_mailfrom'];
	$pref['eventpost_mailaddress'] = $_POST['eventpost_mailaddress'];
	$pref['eventpost_asubs'] = $_POST['eventpost_asubs'];
	$pref['eventpost_emaillog'] = $_POST['eventpost_emaillog'];
	save_prefs();
	$e107cache->clear('nq_event_cal');		// Clear cache as well, in case displays changed
	$message = EC_LAN_75; // "Calendar settings updated.";
}

// ****************** FORTHCOMING EVENTS ******************
if (isset($_POST['updateforthcoming']))
{
  $pref['eventpost_menuheading'] = $_POST['eventpost_fe_menuheading'];
  $pref['eventpost_daysforward'] = $_POST['eventpost_fe_daysforward'];
  $pref['eventpost_numevents'] = $_POST['eventpost_fe_numevents'];
  $pref['eventpost_checkrecur'] = $_POST['eventpost_fe_checkrecur'];
  $pref['eventpost_linkheader'] = $_POST['eventpost_fe_linkheader'];
  $pref['eventpost_fe_set'] =  implode(",", $_POST['fe_eventclass']);
  $pref['eventpost_showcaticon'] = $_POST['eventpost_showcaticon'];
  $pref['eventpost_namelink'] = $_POST['eventpost_namelink'];
  save_prefs();
  $e107cache->clear('nq_event_cal');		// Clear cache as well, in case displays changed
  $message = EC_ADLAN_A109; // "Forthcoming Events settings updated.";
}

if (e_QUERY)
{
  $qs = explode(".", e_QUERY);
}

require_once('ecal_class.php');
$ecal_class = new ecal_class;


// ****************** MAINTENANCE ******************
if (isset($_POST['deleteold']) && isset($_POST['eventpost_deleteoldmonths']))
{
  $back_count = $_POST['eventpost_deleteoldmonths'];
  if (($back_count >= 1) && ($back_count <= 12))
  {
    $old_date = intval(mktime(0,0,0,$ecal_class->now_date['mon']-$back_count,1,$ecal_class->now_date['year']));
	$old_string = strftime("%d %B %Y",$old_date);
//	$message = "Back delete {$back_count} months. Oldest date = {$old_string}";
	$qs[0] = "confdel";
	$qs[1] = $old_date;
  }
  else
    $message = EC_ADLAN_A148;
}


if (isset($_POST['cache_clear']))
{
  $qs[0] = "confcache";
}

require_once(e_ADMIN."auth.php");




// Actually delete back events
if (isset($_POST['confirmdeleteold']) && isset($qs[0]) && ($qs[0] == "backdel"))
{
  $old_date = $qs[1];
  $old_string = strftime("%d %B %Y",$old_date);
	// Check both start and end dates to avoid problems with events originally entered under 0.617
  $qry = "event_start < {$old_date} AND event_end < {$old_date} AND event_recurring = 0";
//  $message = "Back delete {$back_count} months. Oldest date = {$old_string}  Query = {$qry}";
	if ($sql -> db_Delete("event",$qry))
	{
  // Add in a log event
	  $ecal_class->cal_log(4,'db_Delete - earlier than {$old_string} (past {$back_count} months)',$qry);
      $message = EC_ADLAN_A146.$old_string.EC_ADLAN_A147;
	}
	else
	{
	  $message = EC_ADLAN_A149." : ".$sql->mySQLresult;
	}

  $qs[0] = "maint";
}


// Actually empty cache
if (isset($_POST['confirmdelcache']) && isset($qs[0]) &&($qs[0] == "cachedel"))
{
  $e107cache->clear('nq_event_cal');
  $message = EC_ADLAN_A163;
  $qs[0] = "maint";			// Re-display maintenance menu
}


// Prompt to delete back events
if(isset($qs[0]) && ($qs[0] == "confdel"))
{
	$old_string = strftime("%d %B %Y",$qs[1]);
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?backdel.{$qs[1]}'>
	<table style='width:97%' class='fborder'>
	<tr>
		<td class='forumheader3' style='width:100%;vertical-align:top;rext-align:center;'>".EC_ADLAN_A150.$old_string." </td>
	</tr>
	<tr><td colspan='2'  style='text-align:center' class='fcaption'><input class='button' type='submit' name='confirmdeleteold' value='".EC_LAN_50."' /></td></tr>
	</table></form></div>";

	$ns->tablerender("<div style='text-align:center'>".EC_LAN_50."</div>", $text);
}


// Prompt to clear cache
if (isset($qs[0]) && ($qs[0] == "confcache"))
{
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?cachedel'>
	<table style='width:97%' class='fborder'>
	<tr>
		<td class='forumheader3' style='width:100%;vertical-align:top;rext-align:center;'>".EC_ADLAN_A162." </td>
	</tr>
	<tr><td colspan='2'  style='text-align:center' class='fcaption'><input class='button' type='submit' name='confirmdelcache' value='".EC_LAN_50."' /></td></tr>
	</table></form></div>";

	$ns->tablerender("<div style='text-align:center'>".EC_LAN_50."</div>", $text);
}


if (isset($message))
{
  $ns->tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}



//category
if(isset($qs[0]) && $qs[0] == "cat")
{
	$calendarmenu_db = new DB;
	$calendarmenu_action = '';
	if (isset($_POST['calendarmenu_action'])) $calendarmenu_action = $_POST['calendarmenu_action'];
	$calendarmenu_edit = FALSE;
	// * If we are updating then update or insert the record
	if ($calendarmenu_action == 'update')
	{
		$calendarmenu_id = $_POST['calendarmenu_id'];
		if ($calendarmenu_id == 0)
		{
			// New record so add it
			// Enumerate fields so it doesn't matter if they're in the wrong order.
			// db_Insert can take an array of key => value pairs
			$calendarmenu_args = array (
			'event_cat_id'			=> 0,
			'event_cat_name'		=> $tp->toDB($_POST['event_cat_name']),
			'event_cat_description'	=> $tp->toDB($_POST['event_cat_description']),
			'event_cat_icon'		=> $_POST['ne_new_category_icon'],
			'event_cat_class'		=> intval($_POST['event_cat_class']),
			'event_cat_subs'		=> intval($_POST['event_cat_subs']),
			'event_cat_force_class'	=> intval($_POST['event_cat_force_class']),
			'event_cat_ahead'		=> intval($_POST['event_cat_ahead']),
			'event_cat_msg1'		=> $tp->toDB($_POST['event_cat_msg1']),
			'event_cat_msg2'		=> $tp->toDB($_POST['event_cat_msg2']),
			'event_cat_notify'		=> intval($_POST['event_cat_notify']),
			'event_cat_lastupdate'	=> intval(time()),
			'event_cat_addclass'	=>  intval($_POST['event_cat_addclass'])
			);

			if ($calendarmenu_db->db_Insert("event_cat", $calendarmenu_args))
			{
			  $calendarmenu_msg .= "<tr><td class='forumheader3' colspan='2'><strong>".EC_ADLAN_A26."</strong></td></tr>";
			}
			else
			{
			  $calendarmenu_msg .= "<tr><td class='forumheader3' colspan='2'><strong>".EC_ADLAN_A27."</strong></td></tr>";
			}
		}
		else
		{
			// Update existing
			$calendarmenu_args = "
			event_cat_name='".$tp->toDB($_POST['event_cat_name'])."',
			event_cat_description='".$tp->toDB($_POST['event_cat_description'])."',
			event_cat_class='".intval($_POST['event_cat_class'])."',
			event_cat_icon='".$_POST['ne_new_category_icon']."',
			event_cat_subs='".intval($_POST['event_cat_subs'])."',
			event_cat_force_class='".intval($_POST['event_cat_force_class'])."',
			event_cat_ahead='".intval($_POST['event_cat_ahead'])."',
			event_cat_msg1='".$tp->toDB($_POST['event_cat_msg1'])."',
			event_cat_msg2='".$tp->toDB($_POST['event_cat_msg2'])."',
			event_cat_notify='".intval($_POST['event_cat_notify'])."',
			event_cat_addclass='".intval($_POST['event_cat_addclass'])."',
			event_cat_lastupdate='".time()."'
			where event_cat_id='$calendarmenu_id'";
			if ($calendarmenu_db->db_Update("event_cat", $calendarmenu_args)){
				// Changes saved
				$calendarmenu_msg .= "<tr><td class='forumheader3' colspan='2'><b>".EC_ADLAN_A28."</b></td></tr>";
			}else{
				$calendarmenu_msg .= "<tr><td class='forumheader3' colspan='2'><b>".EC_ADLAN_A29."</b></td></tr>";
			}
		}
	}
	// We are creating, editing or deleting a record
	if ($calendarmenu_action == 'dothings')
	{
		$calendarmenu_id = $_POST['calendarmenu_selcat'];
		$calendarmenu_do = $_POST['calendarmenu_recdel'];
		$calendarmenu_dodel = false;

		switch ($calendarmenu_do)
		{
			case '1': // Edit existing record
				{
					// We edit the record
					$calendarmenu_db->db_Select("event_cat", "*", "event_cat_id='$calendarmenu_id'");
					$calendarmenu_row = $calendarmenu_db->db_Fetch() ;
					extract($calendarmenu_row);
					$calendarmenu_cap1 = EC_ADLAN_A24;
					$calendarmenu_edit = TRUE;
					break;
				}
			case '2': // New category
				{
					// Create new record
					$calendarmenu_id = 0;
					// set all fields to zero/blank
					$calendar_category_name = "";
					$calendar_category_description = "";
					$calendarmenu_cap1 = EC_ADLAN_A23;
					$calendarmenu_edit = TRUE;
					break;
				}
			case '3':
				{
					// delete the record
					if ($_POST['calendarmenu_okdel'] == '1'){
						if ($calendarmenu_db->db_Select("event", "event_id", " where event_category='$calendarmenu_id'", "nowhere")){
							$calendarmenu_msg .= "<tr><td class='forumheader3' colspan='2'><strong>".EC_ADLAN_A59."</strong></td></tr>";
						}else{
							if ($calendarmenu_db->db_Delete("event_cat", " event_cat_id='$calendarmenu_id'")){
								$calendarmenu_msg .= "<tr><td class='forumheader3' colspan='2'><strong>".EC_ADLAN_A30."</strong></td></tr>";
							}else{
								$calendarmenu_msg .= "<tr><td class='forumheader3' colspan='2'><strong>".EC_ADLAN_A32."</strong></td></tr>";
							}
						}
					}else{
						$calendarmenu_msg .= "<tr><td class='forumheader3' colspan='2'><strong>".EC_ADLAN_A31."</strong></td></tr>";
					}
					$calendarmenu_dodel = TRUE;
					$calendarmenu_edit = FALSE;
				}
		}

		if (!$calendarmenu_dodel)
		{
			require_once(e_HANDLER."file_class.php");


			$calendarmenu_text .= "
			<form id='calformupdate' method='post' action='".e_SELF."?cat'>
			<table style='width:97%;' class='fborder'>
			<tr>
				<td colspan='2' class='fcaption'>{$calendarmenu_cap1}
					<input type='hidden' value='{$calendarmenu_id}' name='calendarmenu_id' />
					<input type='hidden' value='update' name='calendarmenu_action' />
				</td>
			</tr>
			{$calendarmenu_msg}
			<tr>
				<td style='width:20%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A21."</td>
				<td class='forumheader3'><input type='text' style='width:150px' class='tbox' name='event_cat_name' value='{$event_cat_name}' /></td>
			</tr>
			<tr>
				<td style='width:20%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A121."</td>
				<td class='forumheader3'><textarea rows='5' cols='60' class='tbox' name='event_cat_description' >".$event_cat_description."</textarea></td>
			</tr>
			<tr>
				<td style='width:20%' class='forumheader3'>".EC_ADLAN_A80."</td>
				<td style='width:80%' class='forumheader3'>".r_userclass("event_cat_class", $event_cat_class, "off", 'public, nobody, member, admin, classes')."</td>
			</tr>
			<tr>
				<td style='width:20%' class='forumheader3'>".EC_ADLAN_A94."</td>
				<td style='width:80%' class='forumheader3'>".r_userclass("event_cat_addclass", $event_cat_addclass, "off", 'public, nobody, member, admin, classes')."</td>
			</tr>
			<tr>
				<td class='forumheader3' style='width:20%'>".EC_LAN_55."</td><td class='forumheader3' >
					<input class='tbox' style='width:150px' id='caticon' type='text' name='ne_new_category_icon' value='".$event_cat_icon."' />
					<input class='button' type='button' style='width: 45px; cursor:hand;' value='".EC_LAN_90."' onclick='expandit(\"cat_icons\")' />
					<div style='display:none' id='cat_icons'>";
					$fi = new e_file;
					$imagelist = $fi->get_files(e_PLUGIN."calendar_menu/images", "\.\w{3}$");
					foreach($imagelist as $img){
						if ($img['fname']){
							$calendarmenu_text .= "<a href='javascript:insertext(\"{$img['fname']}\", \"caticon\", \"cat_icons\")'><img src='".e_PLUGIN."calendar_menu/images/".$img['fname']."' style='border:0px' alt='' /></a> ";
						}
					}
					$calendarmenu_text .= "
					</div>
				</td>
			</tr>
			<tr>
				<td class='forumheader3' style='width:20%'>".EC_ADLAN_A81."</td>
				<td class='forumheader3'><input type='checkbox' class='tbox' name='event_cat_subs' value='1' ".($event_cat_subs > 0?"checked='checked'":"")." /></td>
			</tr>
			<tr>
				<td class='forumheader3' style='width:20%'>".EC_ADLAN_A86."</td>
				<td class='forumheader3'><select class='tbox' name='event_cat_notify'>
				<option value='0' ".($event_cat_notify == 0?" selected='selected'":"")." >".EC_ADLAN_A87."</option>
				<option value='1' ".($event_cat_notify == 1?" selected='selected'":"")." >".EC_ADLAN_A88."</option>
				<option value='2' ".($event_cat_notify == 2?" selected='selected'":"")." >".EC_ADLAN_A89."</option>
				<option value='3' ".($event_cat_notify == 3?" selected='selected'":"")." >".EC_ADLAN_A90."</option>
				<option value='4' ".($event_cat_notify == 4?" selected='selected'":"")." >".EC_ADLAN_A110."</option>
				<option value='5' ".($event_cat_notify == 5?" selected='selected'":"")." >".EC_ADLAN_A111."</option>
				</select>
				</td>
			</tr>
			<tr>
				<td style='width:20%' class='forumheader3'>".EC_ADLAN_A82."</td>
				<td style='width:80%' class='forumheader3'>".r_userclass("event_cat_force_class", $event_cat_force_class, "off", 'nobody, member, admin, classes')."</td>
			</tr>
			<tr>
				<td class='forumheader3' style='width:20%'>".EC_ADLAN_A83."</td>
				<td class='forumheader3'><input type='text' size='4' maxlength='5' class='tbox' name='event_cat_ahead' value='$event_cat_ahead'  /></td>
			</tr>
			<tr>
				<td class='forumheader3' style='width:20%;vertical-align:top;'>".EC_ADLAN_A84."</td>
				<td class='forumheader3'><textarea rows='5' cols='80' class='tbox' name='event_cat_msg1' >".$event_cat_msg1."</textarea></td>
			</tr>
			<tr>
				<td class='forumheader3' style='width:20%;vertical-align:top;'>".EC_ADLAN_A117."</td>
				<td class='forumheader3'><textarea rows='5' cols='80' class='tbox' name='event_cat_msg2' >".$event_cat_msg2."</textarea></td>
			</tr>
			<tr><td colspan='2' style='text-align:center' class='fcaption'><input type='submit' name='submits' value='".EC_LAN_77."' class='tbox' /></td></tr>
			</table>
			</form>";
		}
	}
	if (!$calendarmenu_edit)
	{
		// Get the category names to display in combo box then display actions available
		$calendarmenu2_db = new DB;
		$calendarmenu_catopt = '';
		if (!isset($calendarmenu_id)) $calendarmenu_id = -1;
		if ($calendarmenu2_db->db_Select("event_cat", "event_cat_id,event_cat_name", " order by event_cat_name", "nowhere"))
		{
			while ($row = $calendarmenu2_db->db_Fetch()){
				//extract($calendarmenu_row);
				$calendarmenu_catopt .= "<option value='".$row['event_cat_id']."' ".($calendarmenu_id == $row['event_cat_id'] ?" selected='selected'":"")." >".$row['event_cat_name']."</option>";
			}
		}
		else
		{
			$calendarmenu_catopt .= "<option value=0'>".EC_ADLAN_A33."</option>";
		}

		$calendarmenu_text .= "
		<form id='calform' method='post' action='".e_SELF."?cat'>

		<table width='97%' class='fborder'>
		<tr>
			<td colspan='2' class='fcaption'>".EC_ADLAN_A11."<input type='hidden' value='dothings' name='calendarmenu_action' /></td>
		</tr>
		{$calendarmenu_msg}
		<tr>
			<td style='width:20%' class='forumheader3'>".EC_ADLAN_A11."</td>
			<td class='forumheader3'><select name='calendarmenu_selcat' class='tbox'>{$calendarmenu_catopt}</select></td>
		</tr>
		<tr>
			<td style='width:20%' class='forumheader3'>".EC_ADLAN_A18."</td>
			<td class='forumheader3'>
				<input type='radio' name='calendarmenu_recdel' value='1' checked='checked' /> ".EC_ADLAN_A13."<br />
				<input type='radio' name='calendarmenu_recdel' value='2' /> ".EC_ADLAN_A14."<br />
				<input type='radio' name='calendarmenu_recdel' value='3' /> ".EC_ADLAN_A15."
				<input type='checkbox' name='calendarmenu_okdel' value='1' />".EC_ADLAN_A16."
			</td>
		</tr>
		<tr>
			<td colspan='2' class='fcaption'><input type='submit' name='submits' value='".EC_ADLAN_A17."' class='tbox' /></td>
		</tr>
		</table>
		</form>";
	}
	if(isset($calendarmenu_text))
	{
	  $ns->tablerender(EC_ADLAN_A19, $calendarmenu_text);
	}
}

// ====================================================
//			FORTHCOMING EVENTS OPTIONS
// ====================================================

if((isset($qs[0]) && $qs[0] == "forthcoming"))
{

if (!isset($pref['eventpost_menuheading'])) $pref['eventpost_menuheading'] = EC_ADLAN_A100;
if (!isset($pref['eventpost_daysforward'])) $pref['eventpost_daysforward'] = 30;
if (!isset($pref['eventpost_numevents']))   $pref['eventpost_numevents'] = 3;
if (!isset($pref['eventpost_checkrecur']))  $pref['eventpost_checkrecur'] = '1';
if (!isset($pref['eventpost_linkheader']))  $pref['eventpost_linkheader'] = '0';
if (!isset($pref['eventpost_namelink']))    $pref['eventpost_namelink'] = '1';

	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?forthcoming'>
	<table style='width:97%' class='fborder'>
	<tr><td style='vertical-align:top;' colspan='2' class='fcaption'>".EC_ADLAN_A100." </td></tr>
	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A108."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_fe_menuheading' size='35' value='".$pref['eventpost_menuheading']."' maxlength='30' />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A101."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_fe_daysforward' size='20' value='".$pref['eventpost_daysforward']."' maxlength='10' />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A102."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_fe_numevents' size='20' value='".$pref['eventpost_numevents']."' maxlength='10' />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A103."<br /><span class='smalltext'><em>".EC_ADLAN_A107."</em></span></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='checkbox' name='eventpost_fe_checkrecur' value='1' ".($pref['eventpost_checkrecur']==1?" checked='checked' ":"")." /></td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A130."<br /></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_namelink' class='tbox'>
			<option value='1' ".($pref['eventpost_namelink']=='1'?" selected='selected' ":"")." > ".EC_ADLAN_A131." </option>
			<option value='2' ".($pref['eventpost_namelink']=='2'?" selected='selected' ":"")." > ".EC_ADLAN_A132." </option>
			</select>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A104."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='checkbox' name='eventpost_fe_linkheader' value='1' ".($pref['eventpost_linkheader']==1?" checked='checked' ":"")." />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A120."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='checkbox' name='eventpost_showcaticon' value='1' ".($pref['eventpost_showcaticon']==1?" checked='checked' ":"")." />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A118."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>";

// Now display all the current categories as checkboxes
	$cal_fe_prefs = array();
    if (isset($pref['eventpost_fe_set'])) $cal_fe_prefs = array_flip(explode(",",$pref['eventpost_fe_set']));
	if (!is_object($calendarmenu2_db)) $calendarmenu2_db = new DB;		// Possible notice here
	if ($calendarmenu2_db->db_Select("event_cat", "event_cat_id,event_cat_name", " order by event_cat_name", "nowhere"))
	{
	  while ($row = $calendarmenu2_db->db_Fetch())
	  {
	    $selected = isset($cal_fe_prefs[$row['event_cat_id']]);
		$text .= "<input type='checkbox' name='fe_eventclass[]' value='".$row['event_cat_id'].($selected == 1?"' checked='checked'":"'")." />".$row['event_cat_name']."<br /> ";
	  }
	}
	else
	{
	  $text .= EC_ADLAN_A119;		// No categories, or error
	}

	$text .= "</td>
	</tr>

	<tr><td colspan='2'  style='text-align:center' class='fcaption'><input class='button' type='submit' name='updateforthcoming' value='".EC_LAN_77."' /></td></tr>
	</table>
	</form>
	</div>";

	$ns->tablerender("<div style='text-align:center'>".EC_ADLAN_A100."</div>", $text);
}   // End of Forthcoming Events Menu Options


// ====================================================
//			MAINTENANCE OPTIONS
// ====================================================

if((isset($qs[0]) && $qs[0] == "maint"))
{
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?maint'>
	<table style='width:97%' class='fborder'>
	<tr><td style='vertical-align:top;' colspan='2' class='fcaption'>".EC_ADLAN_A144." </td></tr>
	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A142." </td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_deleteoldmonths' class='tbox'>
			<option value='12' selected='selected'>12</option>
			<option value='11'>11</option>
			<option value='10'>10</option>
			<option value='9'>9</option>
			<option value='8'>8</option>
			<option value='7'>7</option>
			<option value='6'>6</option>
			<option value='5'>5</option>
			<option value='4'>4</option>
			<option value='3'>3</option>
			<option value='2'>2</option>
			<option value='1'>1</option>
			</select>
			<span class='smalltext'><em>".EC_ADLAN_A143."</em></span>
		</td>
	</tr>
	<tr><td colspan='2'  style='text-align:center' class='fcaption'><input class='button' type='submit' name='deleteold' value='".EC_ADLAN_A145."' /></td></tr>
	</table></form></div><br /><br />";

	$ns->tablerender("<div style='text-align:center'>".EC_ADLAN_A144."</div>", $text);

	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?maint'>
	<table style='width:97%' class='fborder'>
	<tr><td style='vertical-align:top; text-align:center;' colspan='2' class='smalltext'><em>".EC_ADLAN_A160."</em> </td></tr>
	<tr><td colspan='2'  style='text-align:center' class='fcaption'><input class='button' type='submit' name='cache_clear' value='".EC_ADLAN_A161."' /></td></tr>
	</table></form></div>";

	$ns->tablerender("<div style='text-align:center'>".EC_ADLAN_A159."</div>", $text);

}

// ========================================================
//				MAIN OPTIONS MENU
// ========================================================


if(!isset($qs[0]) || (isset($qs[0]) && $qs[0] == "config")){
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='width:97%' class='fborder'>
	<tr><td style='vertical-align:top;' colspan='2' class='fcaption'>".EC_LAN_78." </td></tr>
	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_76." </td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>". r_userclass("eventpost_admin", $pref['eventpost_admin'], "off", 'public, nobody, member, admin, classes')."
		</td>
	</tr>
	";
$text .= "
	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_104." </td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>". r_userclass("eventpost_super", $pref['eventpost_super'], "off",  'public, nobody, member, admin, classes')."
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A134."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_adminlog' class='tbox'>
			<option value='0' ".($pref['eventpost_adminlog']=='0'?" selected='selected' ":"")." >". EC_ADLAN_A87." </option>
			<option value='1' ".($pref['eventpost_adminlog']=='1'?" selected='selected' ":"")." >".EC_ADLAN_A135." </option>
			<option value='2' ".($pref['eventpost_adminlog']=='2'?" selected='selected' ":"")." >".EC_ADLAN_A136." </option>
			</select>
			<span class='smalltext'><em>".EC_ADLAN_A137."</em></span>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A165."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_menulink' class='tbox'>
			<option value='0' ".($pref['eventpost_menulink']=='0'?" selected='selected' ":"")." >".EC_LAN_80." </option>
			<option value='1' ".($pref['eventpost_menulink']=='1'?" selected='selected' ":"")." >".EC_LAN_83." </option>
			</select>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A140."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='checkbox' name='eventpost_showeventcount' value='1' ".($pref['eventpost_showeventcount']==1?" checked='checked' ":"")." /></td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_102."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='checkbox' name='eventpost_forum' value='1' ".($pref['eventpost_forum']==1?" checked='checked' ":"")." /></td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A171."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_recentshow' size='10' value='".$pref['eventpost_recentshow']."' maxlength='5' />
		<span class='smalltext'><em>".EC_ADLAN_A172."</em></span>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_114."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_weekstart' class='tbox'>
			<option value='sun' ".($pref['eventpost_weekstart']=='sun'?" selected='selected' ":"")." >".EC_LAN_115."</option>
			<option value='mon' ".($pref['eventpost_weekstart']=='mon'?" selected='selected' ":"")." >".EC_LAN_116."</option>
			</select>
		</td>
	</tr>
	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_117."<br /></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_lenday' class='tbox'>
			<option value='1' ".($pref['eventpost_lenday']=='1'?" selected='selected' ":"")." > 1 </option>
			<option value='2' ".($pref['eventpost_lenday']=='2'?" selected='selected' ":"")." > 2 </option>
			<option value='3' ".($pref['eventpost_lenday']=='3'?" selected='selected' ":"")." > 3 </option>
			</select>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_118."<br /></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_dateformat' class='tbox'>
			<option value='my' ".($pref['eventpost_dateformat']=='my'?" selected='selected' ":"")." >".EC_LAN_119."</option>
			<option value='ym' ".($pref['eventpost_dateformat']=='ym'?" selected='selected' ":"")." >".EC_LAN_120."</option>
			</select>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A133."<br /></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_datedisplay' class='tbox'>
			<option value='1' ".($pref['eventpost_datedisplay']=='1'?" selected='selected' ":"")." > yyyy-mm-dd </option>
			<option value='2' ".($pref['eventpost_datedisplay']=='2'?" selected='selected' ":"")." > dd-mm-yyyy</option>
			<option value='3' ".($pref['eventpost_datedisplay']=='3'?" selected='selected' ":"")." > mm-dd-yyyy</option>
			</select>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A138."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='checkbox' name='eventpost_fivemins' value='1' ".($pref['eventpost_fivemins']==1?" checked='checked' ":"")." />&nbsp;&nbsp;<span class='smalltext'><em>".EC_ADLAN_A139."</em></span>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A122."<br />
		<span class='smalltext'><em>".EC_ADLAN_A124."</em></span>".$ecal_class->time_string($ecal_class->time_now)."<br />
		<span class='smalltext'><em>".EC_ADLAN_A125."</em></span>".$ecal_class->time_string($ecal_class->site_timedate)."<br />
		<span class='smalltext'><em>".EC_ADLAN_A126."</em></span>".$ecal_class->time_string($ecal_class->user_timedate)."
		</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_caltime' class='tbox'>
			<option value='1' ".($pref['eventpost_caltime']=='1'?" selected='selected' ":"")." > Server </option>
			<option value='2' ".($pref['eventpost_caltime']=='2'?" selected='selected' ":"")." > Site </option>
			<option value='3' ".($pref['eventpost_caltime']=='3'?" selected='selected' ":"")." > User </option>
			</select><br /><span class='smalltext'><em>".EC_ADLAN_A129."</em></span>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A123."<br />
		<span class='smalltext'><em>".EC_ADLAN_A127."</em></span>
		</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_timedisplay' class='tbox'>
			<option value='1' ".($pref['eventpost_timedisplay']=='1'?" selected='selected' ":"")." > 24-hour </option>
			<option value='2' ".($pref['eventpost_timedisplay']=='2'?" selected='selected' ":"")." > 12-hour </option>
			<option value='3' ".($pref['eventpost_timedisplay']=='3'?" selected='selected' ":"")." > Custom </option>
			</select>
            <input class='tbox' type='text' name='eventpost_timecustom' size='20' value='".$pref['eventpost_timecustom']."' maxlength='30' />
			<br /><span class='smalltext'><em>".EC_ADLAN_A128."</em></span>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A166."<br />
		<span class='smalltext'><em>".EC_ADLAN_A169."</em></span>
		</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_dateevent' class='tbox'>
			<option value='1' ".($pref['eventpost_dateevent']=='1'?" selected='selected' ":"")." > dayofweek day month yyyy </option>
			<option value='2' ".($pref['eventpost_dateevent']=='2'?" selected='selected' ":"")." > dyofwk day mon yyyy </option>
			<option value='3' ".($pref['eventpost_dateevent']=='3'?" selected='selected' ":"")." > dyofwk dd-mm-yy </option>
			<option value='0' ".($pref['eventpost_dateevent']=='0'?" selected='selected' ":"")." > Custom </option>
			</select>
            <input class='tbox' type='text' name='eventpost_eventdatecustom' size='20' value='".$pref['eventpost_eventdatecustom']."' maxlength='30' />
			<br /><span class='smalltext'><em>".EC_ADLAN_A168."</em></span>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A167."<br />
		<span class='smalltext'><em>".EC_ADLAN_A170."</em></span>
		</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_datenext' class='tbox'>
			<option value='1' ".($pref['eventpost_datenext']=='1'?" selected='selected' ":"")." > dd month </option>
			<option value='2' ".($pref['eventpost_datenext']=='2'?" selected='selected' ":"")." > dd mon </option>
			<option value='3' ".($pref['eventpost_datenext']=='3'?" selected='selected' ":"")." > month dd </option>
			<option value='4' ".($pref['eventpost_datenext']=='4'?" selected='selected' ":"")." > mon dd </option>
			<option value='0' ".($pref['eventpost_datenext']=='0'?" selected='selected' ":"")." > Custom </option>
			</select>
            <input class='tbox' type='text' name='eventpost_nextdatecustom' size='20' value='".$pref['eventpost_nextdatecustom']."' maxlength='30' />
			<br /><span class='smalltext'><em>".EC_ADLAN_A168."</em></span>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A95."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='checkbox' name='eventpost_asubs' value='1' ".($pref['eventpost_asubs']==1?" checked='checked' ":"")." />&nbsp;&nbsp;<span class='smalltext'><em>".EC_ADLAN_A96."</em></span>
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A92."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_mailfrom' size='60' value='".$pref['eventpost_mailfrom']."' maxlength='100' />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A91."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_mailsubject' size='60' value='".$pref['eventpost_mailsubject']."' maxlength='100' />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A93."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_mailaddress' size='60' value='".$pref['eventpost_mailaddress']."' maxlength='100' />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A114."<br /></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='eventpost_emaillog' class='tbox'>
			<option value='0' ".($pref['eventpost_emaillog']=='0'?" selected='selected' ":"")." >". EC_ADLAN_A87." </option>
			<option value='1' ".($pref['eventpost_emaillog']=='1'?" selected='selected' ":"")." >".EC_ADLAN_A115."  </option>
			<option value='2' ".($pref['eventpost_emaillog']=='2'?" selected='selected' ":"")." >".EC_ADLAN_A116." </option>
			</select>
		</td>
	</tr>

	<tr><td colspan='2'  style='text-align:center' class='fcaption'><input class='button' type='submit' name='updatesettings' value='".EC_LAN_77."' /></td></tr>
	</table>
	</form>
	</div>";

	$ns->tablerender("<div style='text-align:center'>".EC_LAN_78."</div>", $text);
}


function admin_config_adminmenu()
{
		if (e_QUERY) {
			$tmp = explode(".", e_QUERY);
			$action = $tmp[0];
		}
		if (!isset($action) || ($action == ""))
		{
		  $action = "config";
		}
		$var['config']['text'] = EC_ADLAN_A10;
		$var['config']['link'] = "admin_config.php";

		$var['cat']['text'] = EC_ADLAN_A11;
		$var['cat']['link'] ="admin_config.php?cat";

		$var['forthcoming']['text'] = EC_ADLAN_A100;
		$var['forthcoming']['link'] ="admin_config.php?forthcoming";

		$var['maint']['text'] = EC_ADLAN_A141;
		$var['maint']['link'] ="admin_config.php?maint";

		show_admin_menu(EC_ADLAN_A12, $action, $var);
}


require_once(e_ADMIN."footer.php");

?>