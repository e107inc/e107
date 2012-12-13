<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Event calendar plugin - admin functions
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/admin_config.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/**
 *	e107 Event calendar plugin
 *
 * Event calendar plugin - admin functions
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id$;
 */

$eplug_admin = true;		// Make sure we show admin theme
$e_sub_cat = 'event_calendar';
require_once('../../class2.php');
if (!getperms('P')) 
{
  header('location:'.e_BASE.'index.php');
  exit;
}
	
	
include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'_admin_calendar_menu.php');

require_once(e_HANDLER.'form_handler.php');
$frm = new e_form();

$sql = e107::getDb();
$uc = e107::getUserClass();		// Userclass object pointer
$message = '';
$calendarmenu_text = '';
$calendarmenu_msg  = '';
$calPref = e107::pref('calendar_menu');

/**
 * Given an array of name => format, reads the $_POST variable of each name, applies the specified formatting, 
 * identifies changes, writes back the changes, makes admin log entry
 *
 *	@param array $prefList - each key is the name of a pref; value is an integer representing its type
 *	@param array $oldPref  - array of current pref values
 *	@param string $logRef  - used as title if any changes to be logged
 *
 *	@return - none
 */
function logPrefChanges(&$prefList, &$oldPref, $logRef)
{
	$admin_log = e107::getAdminLog();
	$calNew = e107::getPlugConfig('calendar_menu');		// Initialize calendar_menu prefs.
	$tp = e107::getParser();
	$prefChanges = array();
	$mes = eMessage::getInstance();

	foreach ($prefList as $prefName => $process)
	{
		switch ($process)
		{
			case 0 :
				$temp = varset($_POST[$prefName],'');
				break;
			case 1 :
				$temp = intval(varset($_POST[$prefName],0));
				break;
			case 2 :
				$temp = $tp->toDB(varset($_POST[$prefName],''));
				break;
			case 3 :			// Array of integers - turn into comma-separated string
				$tmp = array();
				foreach ($_POST[$prefName] as $v)
				{
					$tmp[] = intval($v);
				}
				$temp = implode(",", $tmp);
				unset($tmp);
				break;
		}
		if (!isset($oldPref[$prefName]) || ($temp != $oldPref[$prefName]))
		{	// Change to process
			$oldPref[$prefName] = $temp;
			$calNew->set($prefName, $temp);
			$prefChanges[] = $prefName.' => '.$temp;
		}
	}
	if (count($prefChanges))
	{
		$result = $calNew->save();
		if ($result === TRUE)
		{
			// Do admin logging
			$logString = implode('[!br!]', $prefChanges);
			$admin_log->log_event($logRef,$logString,'');
			$mes->add('Calendar prefs updated', E_MESSAGE_SUCCESS);	
		}
		elseif ($result === FALSE)
		{
			$mes->add('Error saving calendar prefs', E_MESSAGE_ERROR);		
		}
		else
		{		// Should never happen
			$mes->add('Unexpected result: '.$result, E_MESSAGE_INFO);

		}
	}
}


$prefSettings = array(
	'updateOptions' => array(
		'eventpost_admin' =>  1,			// Integer
		'eventpost_super' => 1,				// Integer
		'eventpost_adminlog' =>  1,			// Integer
		'eventpost_menulink' => 1,			// Integer
		'eventpost_showmouseover' => 1,		// Integer
		'eventpost_showeventcount' =>  1,	// Integer
		'eventpost_forum' => 1,				// Integer
		'eventpost_recentshow' => 2,		// String ('LV' or an integer)
		'eventpost_weekstart' => 1, 		// Integer
		'eventpost_lenday' => 1,			// Integer
		'eventpost_dateformat' => 2,		// String ('my' or 'ym')
		'eventpost_datedisplay' => 1,		// Integer
		'eventpost_fivemins' => 1,			// Integer
		'eventpost_editmode' => 1,			// Integer
		'eventpost_caltime' => 1,			// Integer
		'eventpost_timedisplay'	=> 1,		// Integer
		'eventpost_timecustom' => 2,		// String
		'eventpost_dateevent' => 1,			// Integer
		'eventpost_eventdatecustom' => 2,	// String
		'eventpost_datenext' => 1,			// Integer
		'eventpost_nextdatecustom' => 2,	// String
		'eventpost_printlists' => 1,		// Integer
		'eventpost_asubs' => 1,				// Integer
		'eventpost_mailfrom' => 2,			// String
		'eventpost_mailsubject' => 2,		// String
		'eventpost_mailaddress' => 2,		// String
		'eventpost_emaillog' => 1			// Integer
		),
	'updateForthcoming' => array(
		'eventpost_menuheading' => 2,		// String
		'eventpost_daysforward' => 1,		// Integer
		'eventpost_numevents' => 1,			// Integer
		'eventpost_checkrecur' =>1, 		// Integer
		'eventpost_linkheader' => 1,		// Integer
		'eventpost_fe_set' => 3,  			// Array of class values
		'eventpost_fe_hideifnone' => 1,		// Integer
		'eventpost_fe_showrecent' => 1,		// Integer
		'eventpost_showcaticon' => 1,		// Integer
		'eventpost_namelink' => 1			// Integer
		)
);
if (isset($_POST['updatesettings'])) 
{
	logPrefChanges($prefSettings['updateOptions'], $calPref, 'EC_ADM_06');
	$e107cache->clear('nq_event_cal');		// Clear cache as well, in case displays changed
	$message = EC_ADLAN_A204; 				// "Calendar settings updated.";
}


// ****************** FORTHCOMING EVENTS ******************
if (isset($_POST['updateforthcoming']))
{
	logPrefChanges($prefSettings['updateForthcoming'], $calPref, 'EC_ADM_07');
	$e107cache->clear('nq_event_cal');		// Clear cache as well, in case displays changed
	$message = EC_ADLAN_A109; 				// "Forthcoming Events settings updated.";
}


$action = 'config';		// Default action - show preferences
if (e_QUERY) 
{
	$ec_qs = explode('.', e_QUERY);
	$action = preg_replace('#\W#', '',$ec_qs[0]);
}

require_once('ecal_class.php');
$ecal_class = new ecal_class;


// ****************** MAINTENANCE ******************
if (isset($_POST['deleteold']) && isset($_POST['eventpost_deleteoldmonths']))
{
	$back_count = intval($_POST['eventpost_deleteoldmonths']);
	if (($back_count >= 1) && ($back_count <= 12))
	{
		$old_date = intval(mktime(0,0,0,$ecal_class->now_date['mon']-$back_count,1,$ecal_class->now_date['year']));
		$old_string = strftime("%d %B %Y",$old_date);
	//	$message = "Back delete {$back_count} months. Oldest date = {$old_string}";
		$action = 'confdel';
		$ec_qs[1] = $old_date;
	}
	else
		$message = EC_ADLAN_A148;
}


if (isset($_POST['cache_clear']))
{
	$action = 'confcache';
}


//-------------------------------------------------

require_once(e_ADMIN.'auth.php');

if (!defined('USER_WIDTH')){ define('USER_WIDTH','width:auto'); }



// Actually delete back events
if (isset($_POST['confirmdeleteold']) && ($action == 'backdel'))
{
	$old_date = intval($ec_qs[1]);
	$old_string = strftime("%d %B %Y",$old_date);
	// Check both start and end dates to avoid problems with events originally entered under 0.617
	$qry = "event_start < {$old_date} AND event_end < {$old_date} AND event_recurring = 0";
	//  $message = "Back delete {$back_count} months. Oldest date = {$old_string}  Query = {$qry}";
	if ($sql -> db_Delete('event',$qry))
	{
		// Add in a log event
		$ecal_class->cal_log(4,"db_Delete - earlier than {$old_string} (past {$back_count} months)",$qry);
		$message = EC_ADLAN_A146.$old_string.EC_ADLAN_A147;
	}
	else
	{
		$message = EC_ADLAN_A149." : ".$sql->mySQLresult;
	}

	$action = 'maint';
}


// Actually empty cache
if (isset($_POST['confirmdelcache']) && ($action == 'cachedel'))
{
  $e107cache->clear('nq_event_cal');
  $message = EC_ADLAN_A163;
  $action = 'maint';			// Re-display maintenance menu
}


// Prompt to delete back events
if ($action == 'confdel')
{
	$old_string = strftime("%d %B %Y",$ec_qs[1]);
	$text = "
	<form method='post' action='".e_SELF."?backdel.{$ec_qs[1]}'>
	<table class='table adminform'>
	<tr>
		<td>".EC_ADLAN_A150.$old_string."</td>
	</tr>
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('confirmdeleteold', EC_ADLAN_A205, 'delete')."
	</div>
	</form>
	</div>";
	
	$ns->tablerender(EC_ADLAN_A205, $text); 
}


// Prompt to clear cache
if ($action == 'confcache')
{
	$text = "
	<form method='post' action='".e_SELF."?cachedel'>
	<table class='table adminform'>
	<tr>
		<td>".EC_ADLAN_A162." </td>
	</tr>
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('confirmdelcache', EC_ADLAN_A205, 'delete')."
	</form>";
	
	$ns->tablerender(EC_ADLAN_A205, $text);
}


// Just delete odd email subscriptions
if (isset($ec_qs[2]) && isset($ec_qs[3]) && ($action == 'subs') && ($ec_qs[2] == 'del') && is_numeric($ec_qs[3]))
{
	if ($sql->db_Delete('event_subs',"event_subid='{$ec_qs[3]}'"))
		$message = EC_ADLAN_A180.$ec_qs[3];
	else
		$message = EC_ADLAN_A181.$ec_qs[3];
}


if (isset($message) && ($message != "")) 
{
	$ns->tablerender('', "<div style='text-align:center'><b>{$message}</b></div>"); // TODO v2 style
	$message = '';
}



//category
$ecal_send_email = 0;
if($action == 'cat')
{
// This uses two hidden fields, preset from the category selection menu:
//	  calendarmenu_action
//		'update' - to create or update a record (actually save the info)
//		'dothings' - create/edit/delete just triggered - $calendarmenu_do = $_POST['calendarmenu_recdel']; has action 1, 2, 3
//    calendarmenu_id - the number of the category - zero indicates a new category
// We may also have $_POST['send_email_1'] or $_POST['send_email_2'] set to generate a test email as well as doing update/save

	if (is_readable(THEME.'ec_mailout_template.php')) 
	{  // Has to be require
		require(THEME.'ec_mailout_template.php');
	}
	else 
	{
		require(e_PLUGIN.'calendar_menu/ec_mailout_template.php');
	}
	$calendarmenu_db = new DB;
	$calendarmenu_action = '';
	if (isset($_POST['calendarmenu_action'])) $calendarmenu_action = $_POST['calendarmenu_action'];
	$calendarmenu_edit = FALSE;
	// * If we are updating then update or insert the record
	if ($calendarmenu_action == 'update')
	{
		$calendarmenu_id = intval($_POST['calendarmenu_id']);
		$calPars = array();
		$calPars['event_cat_name']			= $tp->toDB($_POST['event_cat_name']);
		$calPars['event_cat_description']	= $tp->toDB($_POST['event_cat_description']);
		$calPars['event_cat_icon']			= $tp->toDB($_POST['ne_new_category_icon']);
		$calPars['event_cat_class']			= intval($_POST['event_cat_class']);
		$calPars['event_cat_subs']			= intval($_POST['event_cat_subs']);
		$calPars['event_cat_force_class']	= intval($_POST['event_cat_force_class']);
		$calPars['event_cat_ahead']			= intval($_POST['event_cat_ahead']);
		$calPars['event_cat_msg1']			= $tp->toDB($_POST['event_cat_msg1']);
		$calPars['event_cat_msg2']			= $tp->toDB($_POST['event_cat_msg2']);
		$calPars['event_cat_notify']		= intval($_POST['event_cat_notify']);
		$calPars['event_cat_lastupdate']	= intval(time());
		$calPars['event_cat_addclass']		= intval($_POST['event_cat_addclass']);
		if ($calendarmenu_id == 0)
		{ 	// New record so add it
			if ($calendarmenu_db->db_Insert("event_cat", $calPars))
			{
				$calendarmenu_msg .= "<tr><td colspan='2'><strong>".EC_ADLAN_A26."</strong></td></tr>";
				$admin_log->log_event(EC_ADM_08,$calPars['event_cat_name'],'');
			}
			else
			{
				$calendarmenu_msg .= "<tr><td colspan='2'><strong>".EC_ADLAN_A27."</strong></td></tr>";
			} 
		}
		else
		{ 	// Update existing
			if ($calendarmenu_db->db_UpdateArray("event_cat", $calPars, 'WHERE `event_cat_id` = '.$calendarmenu_id))
			{ 	// Changes saved
				$calendarmenu_msg .= "<tr><td colspan='2'><b>".EC_ADLAN_A28."</b></td></tr>";
				$admin_log->log_event(EC_ADM_09,'ID: '.$calendarmenu_id.', '.$calPars['event_cat_name'],'');
			}
			else
			{
				$calendarmenu_msg .= "<tr><td colspan='2'><b>".EC_ADLAN_A29."</b></td></tr>";
			} 
		}
		// Now see if we need to send a test email
	  if (isset($_POST['send_email_1'])) $ecal_send_email = 1;
	  if (isset($_POST['send_email_2'])) $ecal_send_email = 2;
	  if ($ecal_send_email != 0)
	  {
		$calendarmenu_action = 'dothings';    // This forces us back to category edit screen
		$_POST['calendarmenu_selcat'] = $calendarmenu_id;   // Record number to use
		$_POST['calendarmenu_recdel'] = '1';		// This forces re-read of the record
	  }
	} 
	
	
	// We are creating, editing or deleting a record
	if ($calendarmenu_action == 'dothings')
	{
		$calendarmenu_id = intval($_POST['calendarmenu_selcat']);
		$calendarmenu_do = intval($_POST['calendarmenu_recdel']);
		$calendarmenu_dodel = false;

		switch ($calendarmenu_do)
		{
			case '1': // Edit existing record
				// We edit the record
				$calendarmenu_db->db_Select('event_cat', '*', 'event_cat_id='.$calendarmenu_id);
				$calendarmenu_row = $calendarmenu_db->db_Fetch() ;
				extract($calendarmenu_row);
				$calendarmenu_cap1 = EC_ADLAN_A24;
				$calendarmenu_edit = TRUE;
				if ($ecal_send_email != 0)
				{  // Need to send a test email
				  // First, set up a dummy event
				  $thisevent = array('event_start' => $ecal_class->time_now, 'event_end' => ($ecal_class->time_now)+3600,
									 'event_title' => 'Test event', 'event_details' => EC_ADLAN_A191,
									 'event_cat_name' => $event_cat_name, 'event_location' => EC_ADLAN_A192,
									 'event_contact' => USEREMAIL, 
									 'event_thread' => SITEURL.'dodgypage',
									 'event_id' => '6');
				
				// *************** SEND EMAIL HERE **************
				  require_once(e_PLUGIN.'calendar_menu/calendar_shortcodes.php');
				  require_once(e_HANDLER . 'mail.php');
				  switch ($ecal_send_email)
				  {
					case 1 : $cal_msg = $event_cat_msg1;
							  break;
					case 2 : $cal_msg = $event_cat_msg2;
							 break;
				  }
				  $cal_msg = $tp -> parseTemplate($cal_msg, TRUE);
				  $cal_title = $tp -> parseTemplate($calPref['eventpost_mailsubject'], TRUE);
				  $user_email = USEREMAIL;
				  $user_name  = USERNAME;
				  $send_result = sendemail($user_email, $cal_title, $cal_msg, $user_name, $calPref['eventpost_mailaddress'], $calPref['eventpost_mailfrom']); 
				  if ($send_result)
					$calendarmenu_msg .= "<tr><td colspan='2'><strong>".EC_ADLAN_A187.$ecal_send_email."</strong></td></tr>";
				  else
					$calendarmenu_msg .= "<tr><td colspan='2'><strong>".EC_ADLAN_A188.$ecal_send_email."</strong></td></tr>";
				}
				break;

			case '2': // New category
				// Create new record
				$calendarmenu_id = 0; 
				// set all fields to zero/blank
				$calendar_category_name = '';
				$calendar_category_description = '';
				$calendarmenu_cap1 = EC_ADLAN_A23;
				$calendarmenu_edit = TRUE;
				$event_cat_name = '';		// Define some variables for notice removal
				$event_cat_description = '';
				$event_cat_class = e_UC_MEMBER;
				$event_cat_addclass = e_UC_ADMIN;
				$event_cat_icon = '';
				$event_cat_subs = 0;
				$event_cat_notify = 0;
				$event_cat_force_class = '';
				$event_cat_ahead = 5;
				$event_cat_msg1 = '';
				$event_cat_msg2 = '';
				break;

			case '3':		// Delete record
				if ($_POST['calendarmenu_okdel'] == '1')
				{
					if ($calendarmenu_db->db_Select('event', 'event_id', 'event_category='.$calendarmenu_id, 'nowhere'))
					{
						$calendarmenu_msg .= "<tr><td colspan='2'><strong>".EC_ADLAN_A59."</strong></td></tr>";
					}
					else
					{
						if ($calendarmenu_db->db_Delete('event_cat', 'event_cat_id='.$calendarmenu_id))
						{
							$admin_log->log_event(EC_ADM_10,'ID: '.$calendarmenu_id,'');
							$calendarmenu_msg .= "<tr><td colspan='2'><strong>".EC_ADLAN_A30."</strong></td></tr>";
						}
						else
						{
						  $calendarmenu_msg .= "<tr><td colspan='2'><strong>".EC_ADLAN_A32."</strong></td></tr>";
						}
					} 
				}
				else
				{
					$calendarmenu_msg .= "<tr><td colspan='2'><strong>".EC_ADLAN_A31."</strong></td></tr>";
				} 
				$calendarmenu_dodel = TRUE;
				$calendarmenu_edit = FALSE;
				break;
		} 

		if (!$calendarmenu_dodel)
		{
			//require_once(e_HANDLER.'file_class.php');
			
			$calendarmenu_text .= "
			<form id='calformupdate' method='post' action='".e_SELF."?cat'>
			<fieldset id='plugin-ecal-categories'>
			<table class='table adminform'>
			<colgroup span='2'>
				<col style='width:20%;' class='col-label' />
				<col style='width:80%;' class='col-control' />
			</colgroup>
			<tr>
				<th colspan='2' style='text-align:center'>{$calendarmenu_cap1}
					<input type='hidden' value='{$calendarmenu_id}' name='calendarmenu_id' />
					<input type='hidden' value='update' name='calendarmenu_action' />
				</th>
			</tr>
			{$calendarmenu_msg}
			<tr>
				<td>".EC_ADLAN_A21."</td> 
				<td><input type='text' style='width:150px' class='tbox' name='event_cat_name' value='{$event_cat_name}' /></td>
			</tr>
			<tr>
				<td style='vertical-align:top;' class='forumheader3'>".EC_ADLAN_A121."</td>
				<td><textarea rows='5' cols='60' class='tbox' name='event_cat_description' >".$event_cat_description."</textarea></td>
			</tr>
			<tr>
				<td>".EC_ADLAN_A80."</td>
				<td>".$uc->uc_dropdown('event_cat_class', $event_cat_class, 'public, nobody, member, admin, classes')."</td>
			</tr>	
			<tr>
				<td>".EC_ADLAN_A94."</td>
				<td>".$uc->uc_dropdown('event_cat_addclass', $event_cat_addclass, 'public, nobody, member, admin, classes')."</td>
			</tr>			
			<tr>
				<td>".EC_ADLAN_A219."</td>
				<td>";
					//<input class='tbox' style='width:150px' id='caticon' type='text' name='ne_new_category_icon' value='".$event_cat_icon."' />
					//<input class='button' type='button' style='width: 45px; cursor:pointer;' value='".EC_ADLAN_A220."' onclick='expandit(\"cat_icons\")' />
					//<div style='display:none' id='cat_icons'>";
					/*
					$fi = new e_file;
					$imagelist = $fi->get_files(e_PLUGIN.'calendar_menu/images', "\.\w{3}$");
					foreach($imagelist as $img)
					{
						if ($img['fname'])
						{
							$calendarmenu_text .= "<a href='javascript:insertext(\"{$img['fname']}\", \"caticon\", \"cat_icons\")'><img src='".e_PLUGIN."calendar_menu/images/".$img['fname']."' alt='' /></a> ";
						} 
					}
					*/
					$calendarmenu_text .= $frm->iconpicker('ne_new_category_icon', $event_cat_icon, LAN_SELECT);
					$calendarmenu_text .= "
				</td>
			</tr>
			<tr>
				<td>".EC_ADLAN_A81."</td>
				<td><input type='checkbox' class='tbox' name='event_cat_subs' value='1' ".($event_cat_subs > 0?"checked='checked'":'')." /></td>
			</tr>
			<tr>
				<td>".EC_ADLAN_A86."</td>
				<td><select class='tbox' name='event_cat_notify'>
				<option value='0' ".($event_cat_notify == 0 ? " selected='selected'":'')." >".EC_ADLAN_A87."</option>
				<option value='1' ".($event_cat_notify == 1 ? " selected='selected'":'')." >".EC_ADLAN_A88."</option>
				<option value='2' ".($event_cat_notify == 2 ? " selected='selected'":'')." >".EC_ADLAN_A89."</option>
				<option value='3' ".($event_cat_notify == 3 ? " selected='selected'":'')." >".EC_ADLAN_A90."</option>
				<option value='4' ".($event_cat_notify == 4 ? " selected='selected'":'')." >".EC_ADLAN_A110."</option>
				<option value='5' ".($event_cat_notify == 5 ? " selected='selected'":'')." >".EC_ADLAN_A111."</option>
				</select>		
				</td>
			</tr>
			<tr>
				<td>".EC_ADLAN_A82."</td>
				<td>".$uc->uc_dropdown('event_cat_force_class', $event_cat_force_class, 'nobody, member, admin, classes')."</td>
			</tr>			
			<tr>
				<td>".EC_ADLAN_A83."</td>
				<td><input type='text' size='4' maxlength='5' class='tbox' name='event_cat_ahead' value='{$event_cat_ahead}'  /></td>
			</tr>
			<tr>
				<td style='vertical-align:top;'>".EC_ADLAN_A84;
		if ($calendarmenu_do == 1) 
		  $calendarmenu_text .= "<br /><br /><br /><input type='submit' name='send_email_1' value='".EC_ADLAN_A186."' class='tbox' />"; // TODO / FIX v2 style
		$calendarmenu_text .= "</td>
				<td><textarea rows='5' cols='80' class='tbox' name='event_cat_msg1' >".$event_cat_msg1."</textarea>";
		if ($event_cat_name != EC_DEFAULT_CATEGORY)
		$calendarmenu_text .= "<br /><span class='field-help'><em>".EC_ADLAN_A189."</em></span>";
		$calendarmenu_text .= "
				</td>
			</tr>
			<tr>
				<td style='vertical-align:top;'>".EC_ADLAN_A117;
		if ($calendarmenu_do == 1) 
		  $calendarmenu_text .= "<br /><br /><br /><input type='submit' name='send_email_2' value='".EC_ADLAN_A186."' class='tbox' />"; // TODO / FIX v2 style
		$calendarmenu_text .= "</td>
				<td><textarea rows='5' cols='80' class='tbox' name='event_cat_msg2' >".$event_cat_msg2."</textarea>";
		if ($event_cat_name != EC_DEFAULT_CATEGORY)
		$calendarmenu_text .= "<br /><span class='field-help'><em>".EC_ADLAN_A189."</em></span>";
		$calendarmenu_text .= "
				</td>
			</tr>		
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('submits', LAN_UPDATE, 'update')."
			</div>
			</fieldset>
			</form>";
		} 
	} 
	if (!$calendarmenu_edit)
	{ 
		// Get the category names to display in combo box then display actions available
		$calendarmenu2_db = new DB;
		$calendarmenu_catopt = '';
		if (!isset($calendarmenu_id)) $calendarmenu_id = -1;
		if ($calendarmenu2_db->db_Select('event_cat', 'event_cat_id,event_cat_name', ' order by event_cat_name', 'nowhere'))
		{
			while ($row = $calendarmenu2_db->db_Fetch())
			{
				$calendarmenu_catopt .= "<option value='".$row['event_cat_id']."' ".($calendarmenu_id == $row['event_cat_id'] ?" selected='selected'":"")." >".$row['event_cat_name']."</option>";
			} 
		}
		else
		{
			$calendarmenu_catopt .= "<option value=0'>".EC_ADLAN_A33."</option>";
		} 

		$emessage = eMessage::getInstance();
		echo $emessage->render().$text;

		$calendarmenu_text .= "
		<form id='calform' method='post' action='".e_SELF."?cat'>
		
		<table class='table adminform'>
		{$calendarmenu_msg}
		<tr>
			<td>".EC_ADLAN_A11."<input type='hidden' value='dothings' name='calendarmenu_action' /></td>
			<td><select name='calendarmenu_selcat' class='tbox'>{$calendarmenu_catopt}</select></td>
		</tr>
		<tr>
			<td>".EC_ADLAN_A18."</td>
			<td>
				<input type='radio' name='calendarmenu_recdel' value='1' checked='checked' /> ".EC_ADLAN_A13."<br />
				<input type='radio' name='calendarmenu_recdel' value='2' /> ".EC_ADLAN_A14."<br />
				<input type='radio' name='calendarmenu_recdel' value='3' /> ".EC_ADLAN_A15."
				<input type='checkbox' name='calendarmenu_okdel' value='1' />".EC_ADLAN_A16."
			</td>
		</tr>
		</table>
		<div class='buttons-bar center'>
		".$frm->admin_button('submits', EC_ADLAN_A17, 'submit')."
		</div>
		</form>";
	}
	if(isset($calendarmenu_text))
	{
	  $ns->tablerender(EC_ADLAN_1." - ".EC_ADLAN_A19, $calendarmenu_text);
	}
}

// ====================================================
//			FORTHCOMING EVENTS OPTIONS
// ====================================================

if($action == 'forthcoming')
{

	if (!isset($calPref['eventpost_menuheading'])) $calPref['eventpost_menuheading'] = EC_ADLAN_A100;
	if (!isset($calPref['eventpost_daysforward'])) $calPref['eventpost_daysforward'] = 30;
	if (!isset($calPref['eventpost_numevents']))   $calPref['eventpost_numevents'] = 3;
	if (!isset($calPref['eventpost_checkrecur']))  $calPref['eventpost_checkrecur'] = '1';
	if (!isset($calPref['eventpost_linkheader']))  $calPref['eventpost_linkheader'] = '0';
	if (!isset($calPref['eventpost_namelink']))    $calPref['eventpost_namelink'] = '1';

	$emessage = eMessage::getInstance();
	echo $emessage->render().$text;

	$text = "
	<form method='post' action='".e_SELF."?forthcoming'>
	<fieldset id='plugin-ecal-forthcoming'>
	<table class='table adminform'>
	<colgroup>
		<col style='width:40%;' class='col-label' />
		<col style='width:60%;' class='col-control' />
	</colgroup>
	<thead>
	<tr>
		<td>".EC_ADLAN_A108."</td>
		<td><input class='tbox' type='text' name='eventpost_menuheading' size='35' value='".$calPref['eventpost_menuheading']."' maxlength='30' />
		</td>
	</tr>
	</thead>

	<tbody>
	<tr>
		<td>".EC_ADLAN_A101."</td>
		<td><input class='tbox' type='text' name='eventpost_daysforward' size='20' value='".$calPref['eventpost_daysforward']."' maxlength='10' />
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A102."</td>
		<td><input class='tbox' type='text' name='eventpost_numevents' size='20' value='".$calPref['eventpost_numevents']."' maxlength='10' />
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A103."</td>
		<td><input class='tbox' type='checkbox' name='eventpost_checkrecur' value='1' ".($calPref['eventpost_checkrecur']==1?" checked='checked' ":"")." /></td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A107."</td>
		<td><input class='tbox' type='checkbox' name='eventpost_fe_hideifnone' value='1' ".($calPref['eventpost_fe_hideifnone']==1?" checked='checked' ":"")." /></td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A199."</td>
		<td><input class='tbox' type='checkbox' name='eventpost_fe_showrecent' value='1' ".($calPref['eventpost_fe_showrecent']==1?" checked='checked' ":"")." /></td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A130."<br /></td>
		<td>
			<select name='eventpost_namelink' class='tbox'>
			<option value='1' ".($calPref['eventpost_namelink']=='1'?" selected='selected' ":"")." > ".EC_ADLAN_A131." </option>
			<option value='2' ".($calPref['eventpost_namelink']=='2'?" selected='selected' ":"")." > ".EC_ADLAN_A132." </option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td>".EC_ADLAN_A104."</td>
		<td><input class='tbox' type='checkbox' name='eventpost_linkheader' value='1' ".($calPref['eventpost_linkheader']==1?" checked='checked' ":"")." />
		</td>
	</tr>
	
	<tr>
		<td>".EC_ADLAN_A120."</td>
		<td><input class='tbox' type='checkbox' name='eventpost_showcaticon' value='1' ".($calPref['eventpost_showcaticon']==1?" checked='checked' ":"")." />
		</td>
	</tr>
	
	<tr>
		<td>".EC_ADLAN_A118."</td>
		<td>";

// Now display all the current categories as checkboxes
	$cal_fe_prefs = array();
    if (isset($calPref['eventpost_fe_set'])) $cal_fe_prefs = array_flip(explode(",",$calPref['eventpost_fe_set']));
	if (!isset($calendarmenu2_db) || !is_object($calendarmenu2_db)) $calendarmenu2_db = new DB;		// Possible notice here
	if ($calendarmenu2_db->db_Select("event_cat", "event_cat_id,event_cat_name", " WHERE (event_cat_name != '".EC_DEFAULT_CATEGORY."') order by event_cat_name", "nowhere"))
	{
	  while ($row = $calendarmenu2_db->db_Fetch())
	  {
	    $selected = isset($cal_fe_prefs[$row['event_cat_id']]);
		$text .= "<input type='checkbox' name='eventpost_fe_set[]' value='".$row['event_cat_id'].($selected == 1?"' checked='checked'":"'")." />".$row['event_cat_name']."<br /> ";
	  } 
	}
	else
	{
	  $text .= EC_ADLAN_A119;		// No categories, or error
	} 
  
	$text .= "</td>
	</tr>
	</tbody>
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('updateforthcoming', LAN_UPDATE, 'update')."
	</div>
	</fieldset>
	</form>
	</div>";
	
	$ns->tablerender(EC_ADLAN_1." - ".EC_ADLAN_A100, $text); // TODO
}   // End of Forthcoming Events Menu Options


// ====================================================
//			MAINTENANCE OPTIONS
// ====================================================

if(($action == 'maint'))
{
	$emessage = eMessage::getInstance();
	echo $emessage->render().$text;

	$text = "
	<form method='post' action='".e_SELF."?maint'>
	<fieldset id='plugin-ecal-maintenance'>
	<table class='table adminform'>
	<tr>
		<td style='width:40%;vertical-align:top;'>".EC_ADLAN_A142." </td>
		<td style='width:60%;vertical-align:top;'>
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
			<span class='field-help'><em>".EC_ADLAN_A143."</em></span>
		</td>
	</tr>
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('deleteold', EC_ADLAN_A145, 'delete')."
	</div>
	</fieldset>
	</form>
	<br /><br />";
	
	$ns->tablerender(EC_ADLAN_1." - ".EC_ADLAN_A141, $text);

	$text = "
	<form method='post' action='".e_SELF."?maint'>
	<fieldset id='plugin-ecal-cache'>
	<table class='table adminform'>
	<tr>
		<td colspan='2' class='smalltext'><em>".EC_ADLAN_A160."</em> </td>
	</tr>
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('cache_clear', EC_ADLAN_A161, 'delete')."
	</fieldset>
	</form>";
	
	$ns->tablerender(EC_ADLAN_1." - ".EC_ADLAN_A159, $text);

}

// ====================================================
//			SUBSCRIPTIONS OPTIONS
// ====================================================

if($action == 'subs')
{
	$emessage = eMessage::getInstance();
	echo $emessage->render().$text;

	$from = 0;
	$amount = 20;		// Number per page - could make configurable later if required
	if (isset($ec_qs[1])) $from = intval($ec_qs[1]);

	$num_entry = $sql->db_Count("event_subs", "(*)", "");		// Just count the lot

	$qry = "SELECT es.*, u.user_id, u.user_name, u.user_class, ec.event_cat_id, ec.event_cat_name, ec.event_cat_class FROM `#event_subs` AS es 
                     LEFT JOIN `#user` AS u ON es.event_userid = u.user_id
					 LEFT JOIN `#event_cat` AS ec ON es.event_cat = ec.event_cat_id
					 ORDER BY u.user_id
					 LIMIT {$from}, {$amount} ";

	$text = "
	<form method='post' action='".e_SELF."?subs.".$from."'>
	<fieldset id='plugin-ecal-subscriptions'>
	<table class='table adminform'>
	<colgroup>
		<col style='width:10%; vertical-align:top;' />
		<col style='width:20%; vertical-align:top;' />
		<col style='width:30%; vertical-align:top;' />
		<col style='width:30%; vertical-align:top;' />
		<col style='width:10%; vertical-align:top;' />
	</colgroup>";
	
  	if (!$sql->db_Select_gen($qry))
	{
	  $text .= "<tbody><tr><td colspan='5'>".EC_ADLAN_A174."</td></tr>";
	  $num_entry = 0;
	}
	else
	{
		$text .= "<thead><tr><td>".EC_ADLAN_A175.'</td><td>'.EC_ADLAN_A176."</td>
			<td>".EC_ADLAN_A177."</td><td>".EC_ADLAN_A178.'</td><td>'.EC_ADLAN_A179.'</td></tr></thead><tbody>';
		while ($row = $sql->db_Fetch())
		{
	  // Columns - UID, User name, Category name, Action
			$problems = "";
			if (!isset($row['user_id']) || ($row['user_id'] == 0) || (!isset($row['user_name'])) || ($row['user_name'] == ""))
			  $problems = EC_ADLAN_A198;
			if (!check_class($row['event_cat_class'],$row['user_class']))
			{
			  if ($problems != "") $problems .= "<br />";
			  $problems .= EC_ADLAN_A197;
			}
			$text .= "
				<tr>
				<td>".$row['user_id']."</td>
				<td>".$row['user_name']."</td>
				<td>".$row['event_cat_name']."</td>
				<td>".$problems."</td>
				<td style='text_align:center'><a href='".e_SELF."?".$action.".".$from.".del.".$row['event_subid']."'>
				  <img src='".e_IMAGE_ABS."admin_images/delete_16.png' alt='".LAN_DELETE."' title='".LAN_DELETE."' /></a></td>
				</tr>";
		}  // End while  // TODO / FIX admin_images to ad_links constant? 

		// Next-Previous. ==========================
		if ($num_entry > $amount) 
		{
			$parms = "{$num_entry},{$amount},{$from},".e_SELF."?".$action.'.[FROM]';
			$text .= "<tr><td colspan='5' style='text-align:center'>".$tp->parseTemplate("{NEXTPREV={$parms}}".'</td></tr>');
		}
	}
	$text .= "</tbody></table></fieldset></form></div>";

	$text .= "&nbsp;&nbsp;&nbsp;".str_replace("--NUM--", $num_entry, EC_ADLAN_A182);
	
	$ns->tablerender(EC_ADLAN_1." - ".EC_ADLAN_A173, $text);
}




// ========================================================
//				MAIN OPTIONS MENU
// ========================================================
if($action == 'config')
{
	function select_day_start($val)
	{
		if ($val == 'sun') $val = 0; elseif ($val == 'mon') $val = 1;	// Legacy values
		$ret = "<select name='eventpost_weekstart' class='tbox'>\n";
		foreach (array(EC_LAN_18,EC_LAN_12,EC_LAN_13,EC_LAN_14,EC_LAN_15,EC_LAN_16,EC_LAN_17) as $k => $v)
		{
			$sel = ($val == $k) ? " selected='selected'" : '';
			$ret .= "<option value='{$k}'{$sel}>{$v}</option>\n";
		}
		$ret .= "</select>\n";
		return $ret;
	}

		$emessage = eMessage::getInstance();
		echo $emessage->render().$text;

	$text = "
	<form method='post' action='".e_SELF."'>
	<fieldset id='plugin-ecal-prefs'>
	<table class='table adminform'>
	<colgroup>
		<col style='width:40%;' />
		<col style='width:60%;' />
	</colgroup>
	<tr>
		<td>".EC_ADLAN_A208." </td>
		<td>". $uc->uc_dropdown('eventpost_admin', $calPref['eventpost_admin'], 'public, nobody, member, admin, classes')."
		</td>
	</tr>
	";
$text .= "
	<tr>
		<td>".EC_ADLAN_A211." </td>
		<td>". $uc->uc_dropdown('eventpost_super', $calPref['eventpost_super'],  'public, nobody, member, admin, classes')."
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A134."</td>
		<td>
			<select name='eventpost_adminlog' class='tbox'>
			<option value='0' ".($calPref['eventpost_adminlog']=='0'?" selected='selected' ":"")." >". EC_ADLAN_A87." </option>
			<option value='1' ".($calPref['eventpost_adminlog']=='1'?" selected='selected' ":"")." >".EC_ADLAN_A135." </option>
			<option value='2' ".($calPref['eventpost_adminlog']=='2'?" selected='selected' ":"")." >".EC_ADLAN_A136." </option>
			</select>
			<span class='field-help'>".EC_ADLAN_A137."</span>
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A165."</td>
		<td>
			<select name='eventpost_menulink' class='tbox'>
			<option value='0' ".($calPref['eventpost_menulink']=='0'?" selected='selected' ":"")." >".EC_ADLAN_A209." </option>
			<option value='1' ".($calPref['eventpost_menulink']=='1'?" selected='selected' ":"")." >".EC_ADLAN_A210." </option>
			<option value='2' ".($calPref['eventpost_menulink']=='2'?" selected='selected' ":"")." >".EC_ADLAN_A185." </option>
			</select>
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A183."</td>
		<td><input class='tbox' type='checkbox' name='eventpost_showmouseover' value='1' ".($calPref['eventpost_showmouseover']==1?" checked='checked' ":"")." />
		<span class='field-help'>".EC_ADLAN_A184."</span></td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A140."</td>
		<td><input class='tbox' type='checkbox' name='eventpost_showeventcount' value='1' ".($calPref['eventpost_showeventcount']==1?" checked='checked' ":"")." /></td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A213."</td>
		<td>
		  <input class='tbox' type='checkbox' name='eventpost_forum' value='1' ".($calPref['eventpost_forum']==1?" checked='checked' ":"")." />
		  		<span class='field-help'>".EC_ADLAN_A22."</span>
		  </td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A171."</td>
		<td><input class='tbox' type='text' name='eventpost_recentshow' size='10' value='".$calPref['eventpost_recentshow']."' maxlength='5' />
		<span class='field-help'>".EC_ADLAN_A172."</span>
		</td>
	</tr>  

	<tr>
		<td>".EC_ADLAN_A212."</td>
		<td>".select_day_start($calPref['eventpost_weekstart'])."</td>
	</tr>
	<tr>
		<td>".EC_ADLAN_A214."<br /></td>
		<td>
			<select name='eventpost_lenday' class='tbox'>
			<option value='1' ".($calPref['eventpost_lenday']=='1'?" selected='selected' ":"")." > 1 </option>
			<option value='2' ".($calPref['eventpost_lenday']=='2'?" selected='selected' ":"")." > 2 </option>
			<option value='3' ".($calPref['eventpost_lenday']=='3'?" selected='selected' ":"")." > 3 </option>
			</select>
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A215."<br /></td>
		<td>
			<select name='eventpost_dateformat' class='tbox'>
			<option value='my' ".($calPref['eventpost_dateformat']=='my'?" selected='selected' ":"")." >".EC_ADLAN_A216."</option>
			<option value='ym' ".($calPref['eventpost_dateformat']=='ym'?" selected='selected' ":"")." >".EC_ADLAN_A217."</option>
			</select>
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A133."<br /></td>
		<td>
			<select name='eventpost_datedisplay' class='tbox'>
			<option value='1' ".($calPref['eventpost_datedisplay']=='1'?" selected='selected' ":"")." > yyyy-mm-dd</option>
			<option value='2' ".($calPref['eventpost_datedisplay']=='2'?" selected='selected' ":"")." > dd-mm-yyyy</option>
			<option value='3' ".($calPref['eventpost_datedisplay']=='3'?" selected='selected' ":"")." > mm-dd-yyyy</option>
			<option value='4' ".($calPref['eventpost_datedisplay']=='4'?" selected='selected' ":"")." > yyyy.mm.dd</option>
			<option value='5' ".($calPref['eventpost_datedisplay']=='5'?" selected='selected' ":"")." > dd.mm.yyyy</option>
			<option value='6' ".($calPref['eventpost_datedisplay']=='6'?" selected='selected' ":"")." > mm.dd.yyyy</option>
			<option value='7' ".($calPref['eventpost_datedisplay']=='7'?" selected='selected' ":"")." > yyyy/mm/dd</option>
			<option value='8' ".($calPref['eventpost_datedisplay']=='8'?" selected='selected' ":"")." > dd/mm/yyyy</option>
			<option value='9' ".($calPref['eventpost_datedisplay']=='9'?" selected='selected' ":"")." > mm/dd/yyyy</option>
			</select>
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A138."</td>
		<td><input class='tbox' type='checkbox' name='eventpost_fivemins' value='1' ".($calPref['eventpost_fivemins']==1?" checked='checked' ":"")." />&nbsp;&nbsp;<span class='field-help'><em>".EC_ADLAN_A139."</em></span>
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A200."<br /></td>
		<td>
			<select name='eventpost_editmode' class='tbox'>
			<option value='0' ".($calPref['eventpost_editmode']=='0'?" selected='selected' ":"")." >".EC_ADLAN_A201."</option>
			<option value='1' ".($calPref['eventpost_editmode']=='1'?" selected='selected' ":"")." >".EC_ADLAN_A202."</option>
			<option value='2' ".($calPref['eventpost_editmode']=='2'?" selected='selected' ":"")." >".EC_ADLAN_A203."</option>
			</select>
		</td>
	</tr>

	
	<tr>
		<td>".EC_ADLAN_A122."<br />
		<span class='field-help'>".EC_ADLAN_A124."</span>".$ecal_class->time_string($ecal_class->time_now)."<br />
		<span class='field-help'>".EC_ADLAN_A125."</span>".$ecal_class->time_string($ecal_class->site_timedate)."<br />
		<span class='field-help'>".EC_ADLAN_A126."</span>".$ecal_class->time_string($ecal_class->user_timedate)."
		</td>
		<td>
			<select name='eventpost_caltime' class='tbox'>
			<option value='1' ".($calPref['eventpost_caltime']=='1'?" selected='selected' ":'')." > Server </option>
			<option value='2' ".($calPref['eventpost_caltime']=='2'?" selected='selected' ":'')." > Site </option>
			<option value='3' ".($calPref['eventpost_caltime']=='3'?" selected='selected' ":'')." > User </option>
			</select><br /><span class='field-help'>".EC_ADLAN_A129."</span>
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A123."<br />
		<span class='field-help'>".EC_ADLAN_A127."</span>
		</td>
		<td>
			<select name='eventpost_timedisplay' class='tbox'>
			<option value='1' ".($calPref['eventpost_timedisplay']=='1'?" selected='selected' ":'')." > 24-hour hhmm </option>
			<option value='4' ".($calPref['eventpost_timedisplay']=='4'?" selected='selected' ":'')." > 24-hour hh:mm </option>
			<option value='2' ".($calPref['eventpost_timedisplay']=='2'?" selected='selected' ":'')." > 12-hour </option>
			<option value='3' ".($calPref['eventpost_timedisplay']=='3'?" selected='selected' ":'')." > Custom </option>
			</select>
            <input class='tbox' type='text' name='eventpost_timecustom' size='20' value='".$calPref['eventpost_timecustom']."' maxlength='30' />
			<br /><span class='field-help'>".EC_ADLAN_A128."</span>
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A166."<br />
		<span class='field-help'>".EC_ADLAN_A169."</span>
		</td>
		<td>
			<select name='eventpost_dateevent' class='tbox'>
			<option value='1' ".($calPref['eventpost_dateevent']=='1'?" selected='selected' ":'')." > dayofweek day month yyyy </option>
			<option value='2' ".($calPref['eventpost_dateevent']=='2'?" selected='selected' ":'')." > dyofwk day mon yyyy </option>
			<option value='3' ".($calPref['eventpost_dateevent']=='3'?" selected='selected' ":'')." > dyofwk dd-mm-yy </option>
			<option value='0' ".($calPref['eventpost_dateevent']=='0'?" selected='selected' ":'')." > Custom </option>
			</select>
            <input class='tbox' type='text' name='eventpost_eventdatecustom' size='20' value='".$calPref['eventpost_eventdatecustom']."' maxlength='30' />
			<br /><span class='field-help'>".EC_ADLAN_A168."</span>
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A167."<br />
		<span class='field-help'>".EC_ADLAN_A170."</span>
		</td>
		<td>
			<select name='eventpost_datenext' class='tbox'>
			<option value='1' ".($calPref['eventpost_datenext']=='1'?" selected='selected' ":'')." > dd month </option>
			<option value='2' ".($calPref['eventpost_datenext']=='2'?" selected='selected' ":'')." > dd mon </option>
			<option value='3' ".($calPref['eventpost_datenext']=='3'?" selected='selected' ":'')." > month dd </option>
			<option value='4' ".($calPref['eventpost_datenext']=='4'?" selected='selected' ":'')." > mon dd </option>
			<option value='0' ".($calPref['eventpost_datenext']=='0'?" selected='selected' ":'')." > Custom </option>
			</select>
            <input class='tbox' type='text' name='eventpost_nextdatecustom' size='20' value='".$calPref['eventpost_nextdatecustom']."' maxlength='30' />
			<br /><span class='field-help'>".EC_ADLAN_A168."</span>
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A193."<br /></td>
		<td>
			<select name='eventpost_printlists' class='tbox'>";
	$listOpts = array( '0' => EC_ADLAN_A194, '1' => EC_ADLAN_A195);
	if (e107::isInstalled('pdf')) { $listOpts['2'] = EC_ADLAN_A196; }
	foreach ($listOpts as $v => $t)
	{
		$s = $calPref['eventpost_printlists'] == $v ? " selected='selected'" : '';
		$text .= "<option value='{$v}'{$s}>{$t}</option>\n";
	}
	$text .= "
			</select>
		</td>
	</tr>

	<tr>
		<td>".EC_ADLAN_A95."</td>
		<td><input class='tbox' type='checkbox' name='eventpost_asubs' value='1' ".($calPref['eventpost_asubs']==1?" checked='checked' ":'')." />&nbsp;&nbsp;<span class='field-help'><em>".EC_ADLAN_A96."</em></span>
		</td>
	</tr>
	
	<tr>
		<td>".EC_ADLAN_A92."</td>
		<td><input class='tbox' type='text' name='eventpost_mailfrom' size='60' value='".$calPref['eventpost_mailfrom']."' maxlength='100' />
		</td>
	</tr>  

	<tr>
		<td>".EC_ADLAN_A91."</td>
		<td><input class='tbox' type='text' name='eventpost_mailsubject' size='60' value='".$calPref['eventpost_mailsubject']."' maxlength='100' />
		</td>
	</tr>  

	<tr>
		<td>".EC_ADLAN_A93."</td>
		<td><input class='tbox' type='text' name='eventpost_mailaddress' size='60' value='".$calPref['eventpost_mailaddress']."' maxlength='100' />
		</td>
	</tr>  

	<tr>
		<td>".EC_ADLAN_A114."<br /></td>
		<td>
			<select name='eventpost_emaillog' class='tbox'>
			<option value='0' ".($calPref['eventpost_emaillog']=='0'?" selected='selected' ":"")." >". EC_ADLAN_A87." </option>
			<option value='1' ".($calPref['eventpost_emaillog']=='1'?" selected='selected' ":"")." >".EC_ADLAN_A115."  </option>
			<option value='2' ".($calPref['eventpost_emaillog']=='2'?" selected='selected' ":"")." >".EC_ADLAN_A116." </option>
			</select>
		</td>
	</tr>
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('updatesettings', LAN_UPDATE, 'update')."
	</div>
	</fieldset>
	</form>
	";
	
	$ns->tablerender(EC_ADLAN_1." - ".EC_ADLAN_A207, $text);
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
		
		$var['subs']['text'] = EC_ADLAN_A173;
		$var['subs']['link'] ="admin_config.php?subs";
		
		show_admin_menu(EC_ADLAN_A12, $action, $var);
}


require_once(e_ADMIN."footer.php");

?>