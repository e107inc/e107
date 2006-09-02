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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/admin_config.php,v $
|     $Revision: 1.14 $
|     $Date: 2006-09-02 21:41:18 $
|     $Author: e107coders $
|
| 08.07.06 Modified with extra page for 'Forthcoming Events' menu by steved
| 09.07.06 - Extra option to select level of mailout logging
| 10.07.06 - Add selection of categories for 'forthcoming events' menu
| 12.07.06 - Forthcoming event admin should return to same option page
| 02.08.06 - Optional category icon display added
|
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
if (!getperms("P")) {
	header("location:".e_BASE."index.php");
}
	
	
$lan_file = e_PLUGIN."calendar_menu/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."calendar_menu/languages/English.php");

if (isset($_POST['updatesettings'])) {
	$pref['eventpost_admin'] = $_POST['eventpost_admin'];
	$pref['eventpost_headercss'] = $_POST['eventpost_headercss'];
	$pref['eventpost_daycss'] = $_POST['eventpost_daycss'];
	$pref['eventpost_todaycss'] = $_POST['eventpost_todaycss'];
	$pref['eventpost_evtoday'] = $_POST['eventpost_evtoday'];	
	$pref['eventpost_addcat'] = $_POST['eventpost_addcat'];
	$pref['eventpost_forum'] = $_POST['eventpost_forum'];	
	$pref['eventpost_super'] = $_POST['eventpost_super'];
	$pref['eventpost_dateformat'] = $_POST['eventpost_dateformat'];	
	$pref['eventpost_weekstart'] = $_POST['eventpost_weekstart'];
	$pref['eventpost_lenday'] = $_POST['eventpost_lenday'];			
	$pref['eventpost_mailsubject'] = $_POST['eventpost_mailsubject'];			
	$pref['eventpost_mailfrom'] = $_POST['eventpost_mailfrom'];		
	$pref['eventpost_mailaddress'] = $_POST['eventpost_mailaddress'];
	$pref['eventpost_asubs'] = $_POST['eventpost_asubs'];
	$pref['eventpost_emaillog'] = $_POST['eventpost_emaillog'];
	save_prefs();
	$message = EC_LAN_75; // "Calendar settings updated.";
}

// ****************** UPDATE HERE ******************
if (isset($_POST['updateforthcoming']))
{
  $pref['eventpost_menuheading'] = $_POST['eventpost_fe_menuheading'];
  $pref['eventpost_daysforward'] = $_POST['eventpost_fe_daysforward'];
  $pref['eventpost_numevents'] = $_POST['eventpost_fe_numevents'];
  $pref['eventpost_checkrecur'] = $_POST['eventpost_fe_checkrecur'];
  $pref['eventpost_linkheader'] = $_POST['eventpost_fe_linkheader'];
  $pref['eventpost_fe_set'] =  implode(",", $_POST['fe_eventclass']);
  $pref['eventpost_showcaticon'] = $_POST['eventpost_showcaticon'];
  save_prefs();
  $message = EC_ADLAN_A109; // "Forthcoming Events settings updated.";
}
// ****************** UPDATE ENDS HERE ******************


require_once(e_ADMIN."auth.php");

if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}

if (e_QUERY) {
	$qs = explode(".", e_QUERY);
}

//category
if(isset($qs[0]) && $qs[0] == "cat")
{
	$calendarmenu_db = new DB;
	$calendarmenu_action = $_POST['calendarmenu_action'];
	$calendarmenu_edit = FALSE;
	// * If we are updating then update or insert the record
	if ($calendarmenu_action == 'update')
	{
		$calendarmenu_id = $_POST['calendarmenu_id'];
		if ($calendarmenu_id == 0)
		{ 
			// New record so add it
			$calendarmenu_args = "
			'0',
			'".$_POST['event_cat_name']."',
			'".$_POST['ne_new_category_icon']."',
			'".intval($_POST['event_cat_class'])."',
			'".intval($_POST['event_cat_subs'])."',
			'".intval($_POST['event_cat_force'])."',
			'".intval($_POST['event_cat_ahead'])."',
			'".$_POST['event_cat_msg1']."',
			'".$_POST['event_cat_msg2']."',
			'".intval($_POST['event_cat_notify'])."',
			'0','0','".time()."',
			'".intval($_POST['event_cat_addclass'])."'";
			if ($calendarmenu_db->db_Insert("event_cat", $calendarmenu_args)){
				$calendarmenu_msg .= "<tr><td class='forumheader3' colspan='2'><strong>".EC_ADLAN_A26."</strong></td></tr>";
			}else{
				$calendarmenu_msg .= "<tr><td class='forumheader3' colspan='2'><strong>".EC_ADLAN_A27."</strong></td></tr>";
			} 
		}
		else
		{ 
			// Update existing
			$calendarmenu_args = "
			event_cat_name='".$_POST['event_cat_name']."',
			event_cat_class='".intval($_POST['event_cat_class'])."',
			event_cat_icon='".$_POST['ne_new_category_icon']."',
			event_cat_subs='".intval($_POST['event_cat_subs'])."',
			event_cat_force='".intval($_POST['event_cat_force'])."',
			event_cat_ahead='".intval($_POST['event_cat_ahead'])."',
			event_cat_msg1='".$_POST['event_cat_msg1']."',
			event_cat_msg2='".$_POST['event_cat_msg2']."',
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

		switch ($calendarmenu_do){
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

		if (!$calendarmenu_dodel){
			require_once(e_HANDLER."file_class.php");
			
			
			$calendarmenu_text .= "
			<form id='calformupdate' method='post' action='".e_SELF."?cat'>
			<table style='width:97%;' class='fborder'>
			<tr>
				<td colspan='2' class='fcaption'>$calendarmenu_cap1
					<input type='hidden' value='$calendarmenu_id' name='calendarmenu_id' />
					<input type='hidden' value='update' name='calendarmenu_action' />
				</td>
			</tr>
			$calendarmenu_msg
			<tr>
				<td style='width:20%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A21."</td>
				<td class='forumheader3'><input type='text' class='tbox' name='event_cat_name' value='$event_cat_name' /></td>
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
				<td class='forumheader3' style='width:20%'>".EC_ADLAN_A82."</td>
				<td class='forumheader3'><input type='checkbox' class='tbox' name='event_cat_force' value='1' ".($event_cat_force > 0?"checked='checked'":"")." />
				<br /><em>".EC_ADLAN_A97."</em></td>
			</tr>
			<tr>
				<td class='forumheader3' style='width:20%'>".EC_ADLAN_A83."</td>
				<td class='forumheader3'><input type='text' size='4' maxlength='5' class='tbox' name='event_cat_ahead' value='$event_cat_ahead'  /></td>
			</tr>
			<tr>
				<td class='forumheader3' style='width:20%;vertical-align:top;'>".EC_ADLAN_A84."</td>
				<td class='forumheader3'><textarea rows='5' cols='60' class='tbox' name='event_cat_msg1' >".$event_cat_msg1."</textarea></td>
			</tr>
			<tr>
				<td class='forumheader3' style='width:20%;vertical-align:top;'>".EC_ADLAN_A117."</td>
				<td class='forumheader3'><textarea rows='5' cols='60' class='tbox' name='event_cat_msg2' >".$event_cat_msg2."</textarea></td>
			</tr>			
			<tr><td colspan='2' class='fcaption'><input type='submit' name='submits' value='".EC_ADLAN_A25."' class='tbox' /></td></tr>
			</table>
			</form>";
		} 
	} 
	if (!$calendarmenu_edit)
	{ 
		// Get the category names to display in combo box then display actions available
		$calendarmenu2_db = new DB;
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
		$calendarmenu_msg
		<tr>
			<td style='width:20%' class='forumheader3'>".EC_ADLAN_A11."</td>
			<td class='forumheader3'><select name='calendarmenu_selcat' class='tbox'>$calendarmenu_catopt</select></td>
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

// *************** EDIT STARTS HERE ****************

if((isset($qs[0]) && $qs[0] == "forthcoming"))
{

if (!isset($pref['eventpost_menuheading'])) $pref['eventpost_menuheading'] = EC_ADLAN_A100;
if (!isset($pref['eventpost_daysforward'])) $pref['eventpost_daysforward'] = 30;
if (!isset($pref['eventpost_numevents']))   $pref['eventpost_numevents'] = 3;
if (!isset($pref['eventpost_checkrecur']))  $pref['eventpost_checkrecur'] = '1';
if (!isset($pref['eventpost_linkheader']))  $pref['eventpost_linkheader'] = '0';

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
	if (!is_object($calendarmenu2_db)) $calendarmenu2_db = new DB;
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

// ************** EDIT ENDS HERE ********************


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
	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_100." </td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>". r_userclass("eventpost_addcat", $pref['eventpost_addcat'], "off",  'public, nobody, member, admin, classes')."
		<br /><em>".EC_LAN_101."</em>
		</td>
	</tr>
	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_104." </td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>". r_userclass("eventpost_super", $pref['eventpost_super'], "off",  'public, nobody, member, admin, classes')."
		</td>
	</tr>
	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_84."<br /><span class='smalltext'><em>".EC_LAN_85."</em></span></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_headercss' size='20' value='".$pref['eventpost_headercss']."' maxlength='100' />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_86."<br /><span class='smalltext'><em>".EC_LAN_87."</em></span></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_daycss' size='20' value='".$pref['eventpost_daycss']."' maxlength='100' />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_88."<br /><span class='smalltext'><em>".EC_LAN_89."</em></span></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_todaycss' size='20' value='".$pref['eventpost_todaycss']."' maxlength='100' />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_122."<br /><span class='smalltext'><em>".EC_LAN_89."</em></span></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_evtoday' size='20' value='".$pref['eventpost_evtoday']."' maxlength='100' />
		</td>
	</tr>

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_102."<br /><span class='smalltext'><em>".EC_LAN_103."</em></span></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='checkbox' name='eventpost_forum' value='1' ".($pref['eventpost_forum']==1?" checked='checked' ":"")." /></td>
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
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A95."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='checkbox' name='eventpost_asubs' value='1' ".($pref['eventpost_asubs']==1?" checked='checked' ":"")." /><br /><span class='smalltext'><em>".EC_ADLAN_A96."</em></span>
		</td>
	</tr>
	
	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A92."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_mailfrom' size='40' value='".$pref['eventpost_mailfrom']."' maxlength='100' />
		</td>
	</tr>  

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A91."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_mailsubject' size='40' value='".$pref['eventpost_mailsubject']."' maxlength='100' />
		</td>
	</tr>  

	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_ADLAN_A93."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_mailaddress' size='40' value='".$pref['eventpost_mailaddress']."' maxlength='100' />
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
		if ($action == "") {
			$action = "config";
		}
		$var['config']['text'] = EC_ADLAN_A10;
		$var['config']['link'] = "admin_config.php";
			
		$var['cat']['text'] = EC_ADLAN_A11;
		$var['cat']['link'] ="admin_config.php?cat";
		
		$var['forthcoming']['text'] = EC_ADLAN_A100;
		$var['forthcoming']['link'] ="admin_config.php?forthcoming";

		show_admin_menu(EC_ADLAN_A12, $action, $var);
}


require_once(e_ADMIN."footer.php");

?>