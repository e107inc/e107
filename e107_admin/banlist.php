<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Ban List Management
 *
 * $URL$
 * $Id$
 *
*/

/**
 *	e107 Banlist administration
 *
 *	@package	e107
 *	@subpackage	admin
 *	@version 	$Id$;
 */

define('BAN_TIME_FORMAT', "%d-%m-%Y %H:%M");
define('BAN_REASON_COUNT', 7); // Update as more ban reasons added (max 10 supported)


define('BAN_TYPE_MANUAL', 1); 			// Manually entered bans
define('BAN_TYPE_IMPORTED', 5); 		// Imported bans
define('BAN_TYPE_TEMPORARY', 9); 		// Used during CSV import


define('BAN_TYPE_WHITELIST', 100); 		// Entry for whitelist


require_once ('../class2.php');
if(!getperms('4'))
{
	header('location:'.e_BASE.'index.php');
	exit();
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'banlist';
require_once ('auth.php');
require_once (e_HANDLER.'form_handler.php');
$frm = new e_form(true);

require_once(e_HANDLER.'message_handler.php');
$emessage = &eMessage::getInstance();

$action = 'list';
if(e_QUERY)
{
	$tmp = explode('-', e_QUERY); // Use '-' instead of '.' to avoid confusion with IP addresses
	$action = $tmp[0];
	$sub_action = varset($tmp[1], '');
	if($sub_action)
		$sub_action = preg_replace('/[^\w@\.:]*/', '', urldecode($sub_action));
	$id = intval(varset($tmp[2], 0));
	unset($tmp);
}

$images_path = e_IMAGE_ABS.'admin_images/';



if(isset($_POST['update_ban_prefs']))
{
	for($i = 0; $i < BAN_REASON_COUNT; $i ++)
	{
		$pref['ban_messages'][$i] = $tp->toDB(varset($_POST['ban_text_'.($i+1)], ''));
		$pref['ban_durations'][$i] = intval(varset($_POST['ban_time_'.($i+1)], 0));
	}
	save_prefs();
	banlist_adminlog('08', "");
	//$ns->tablerender(BANLAN_9, "<div style='text-align:center'>".BANLAN_33.'</div>');
	$emessage->add(BANLAN_33, E_MESSAGE_SUCCESS);
}


if(isset($_POST['ban_ip']))
{
	$_POST['ban_ip'] = trim($_POST['ban_ip']);
	$new_ban_ip = preg_replace('/[^\w@\.\*]*/', '', urldecode($_POST['ban_ip']));
	if($new_ban_ip != $_POST['ban_ip'])
	{
		$message = BANLAN_27.' '.$new_ban_ip;
		//$ns->tablerender(BANLAN_9, $message);
		$emessage->add(BANLAN_33, $message);
		$_POST['ban_ip'] = $new_ban_ip;
	}

	if(isset($_POST['entry_intent']) && (isset($_POST['add_ban']) || isset($_POST['update_ban'])) && $_POST['ban_ip'] != "" && strpos($_POST['ban_ip'], ' ') === false)
	{
		/*	$_POST['entry_intent'] says why we're here:
		'edit' 	- Editing blacklist
		'add'	- Adding to blacklist
		'whedit' - Editing whitelist
		'whadd'	- Adding to whitelist
*/
		if($e107->whatIsThis($new_ban_ip) == 'ip')
		{
			$new_ban_ip = $e107->IPencode($new_ban_ip); // Normalise numeric IP addresses
		}
		$new_vals = array('banlist_ip' => $new_ban_ip);
		if(isset($_POST['add_ban']))
		{
			$new_vals['banlist_datestamp'] = time();
			if($_POST['entry_intent'] == 'add')
				$new_vals['banlist_bantype'] = BAN_TYPE_MANUAL; // Manual ban
			if($_POST['entry_intent'] == 'whadd')
				$new_vals['banlist_bantype'] = BAN_TYPE_WHITELIST;
		}
		$new_vals['banlist_admin'] = ADMINID;
		if(varsettrue($_POST['ban_reason']))
			$new_vals['banlist_reason'] = $tp->toDB($_POST['ban_reason']);
		$new_vals['banlist_notes'] = $tp->toDB($_POST['ban_notes']);
		if(isset($_POST['ban_time']) && is_numeric($_POST['ban_time']) && ($_POST['entry_intent'] == 'edit' || $_POST['entry_intent'] == 'add'))
		{
			$bt = intval($_POST['ban_time']);
			$new_vals['banlist_banexpires'] = $bt ? time() + ($bt * 60 * 60) : 0;
		}
		if(isset($_POST['add_ban']))
		{ // Insert new value - can just pass an array
			admin_update($sql->db_Insert("banlist", $new_vals), 'insert', false, false, false);
			if($_POST['entry_intent'] == 'add')
			{
				banlist_adminlog('01', $new_vals['banlist_ip']);
			}
			else
			{
				banlist_adminlog('04', $new_vals['banlist_ip']);
			}
		}
		else
		{ // Update existing value
			$qry = '';
			$spacer = '';
			foreach($new_vals as $k => $v)
			{
				$qry .= $spacer."`{$k}`='$v'";
				$spacer = ', ';
			}
			admin_update($sql->db_Update("banlist", $qry." WHERE banlist_ip='".$_POST['old_ip']."'"), 'update', false, false, false);
			if($_POST['entry_intent'] == 'edit')
			{
				banlist_adminlog("09", $new_vals['banlist_ip']);
			}
			else
			{
				banlist_adminlog("10", $new_vals['banlist_ip']);
			}
		}
		unset($ban_ip);
	}
}



// Remove a ban
if(($action == "remove" || $action == "whremove") && varsettrue($_POST['ban_secure']))
//if ($action == "remove")
{
	$sql->db_Delete("generic", "gen_type='failed_login' AND gen_ip='{$sub_action}'");
	admin_update($sql->db_Delete("banlist", "banlist_ip='{$sub_action}'"), 'delete', false, false, false);
	if($action == "remove")
	{
		$action = 'list';
		banlist_adminlog("02", $sub_action);
	}
	else
	{
		$action = 'white';
		banlist_adminlog("05", $sub_action);
	}
}



// Update the ban expiry time/date - timed from now (only done on banlist)
if($action == 'newtime')
{
	$end_time = $id ? time() + ($id * 60 * 60) : 0;
	admin_update($sql->db_Update("banlist", "banlist_banexpires='".intval($end_time)."' WHERE banlist_ip='".$sub_action."'"), 'update', false, false, false);
	banlist_adminlog('03', $sub_action);
	$action = 'list';
}



/**
 *	@todo - eliminate extract();
 */
// Edit modes - get existing entry
if($action == 'edit' || $action == 'whedit')
{
	$sql->db_Select('banlist', '*', "banlist_ip='{$sub_action}'");
	$row = $sql->db_Fetch();
	extract($row);				//FIXME - kill extract()
}
else
{
	unset($banlist_ip, $banlist_reason);
	if(e_QUERY && ($action == 'add' || $action == 'whadd') && strpos($_SERVER["HTTP_REFERER"], "userinfo"))
	{
		$banlist_ip = $sub_action;
	}
}



function ban_time_dropdown($click_js = '', $zero_text = BANLAN_21, $curval = -1, $drop_name = 'ban_time')
{
	global $frm;
	$intervals = array(0, 1, 2, 3, 6, 8, 12, 24, 36, 48, 72, 96, 120, 168, 336, 672);

	$ret = $frm->select_open($drop_name, array('other' => $click_js, 'id' => false));
	$ret .= $frm->option('&nbsp;', '');
	foreach($intervals as $i)
	{
		if($i == 0)
		{
			$words = $zero_text ? $zero_text : BANLAN_21;
		}
		elseif(($i % 24) == 0)
		{
			$words = floor($i / 24).' '.BANLAN_23;
		}
		else
		{
			$words = $i.' '.BANLAN_24;
		}
		$ret .= $frm->option($words, $i, ($curval == $i));
	}
	$ret .= '</select>';
	return $ret;
}



// Character options for import & export
$separator_char = array(1 => ',', 2 => '|');
$quote_char = array(1 => '(none)', 2 => "'", 3 => '"');

function select_box($name, $data, $curval = FALSE)
{
	global $frm;

	$ret = $frm->select_open($name, array('class' => 'tbox', 'id' => false));
	foreach($data as $k => $v)
	{
		$ret .= $frm->option($v, $k, ($curval !== FALSE) && ($curval == $k));
	}
	$ret .= "</select>\n";
	return $ret;
}


// Drop-down box for access counts
function drop_box($box_name, $curval)
{
	global $frm;

	$opts = array(50, 100, 150, 200, 250, 300, 400, 500);
	$ret = $frm->select_open($box_name, array('class' => 'tbox'));
	foreach($opts as $o)
	{
		$ret .= $frm->option($o, $o, ($curval == $o));
	}
	$ret .= "</select>\n";
	return $ret;
}



$text = '';


switch($action)
{
	case 'options':
		if(!getperms("0"))
			exit();
		if(isset($_POST['update_ban_options']))
		{
			$pref['enable_rdns'] = intval($_POST['ban_rdns_on_access']);
			$pref['enable_rdns_on_ban'] = intval($_POST['ban_rdns_on_ban']);
			$pref['ban_max_online_access'] = intval($_POST['ban_access_guest']).','.intval($_POST['ban_access_member']);
			$pref['ban_retrigger'] = intval($_POST['ban_retrigger']);
			save_prefs();
			$emessage->add(LAN_SETSAVED, E_MESSAGE_SUCCESS);
		}

		if(isset($_POST['remove_expired_bans']))
		{
			//FIXME - proper messages
			admin_update($sql->db_Delete('banlist', "`banlist_bantype` < ".BAN_TYPE_WHITELIST." AND `banlist_banexpires` > 0 AND `banlist_banexpires` < ".time()), 'delete', false, false, false);
		}

		list($ban_access_guest, $ban_access_member) = explode(',', varset($pref['ban_max_online_access'], '100,200'));
		$ban_access_member = max($ban_access_guest, $ban_access_member);
		$text = "
			<form method='post' action='".e_SELF."?options'>
				<fieldset id='core-banlist-options'>
					<legend>".BANLAN_72."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".BANLAN_63."</td>
								<td class='control'>
									<div class='auto-toggle-area autocheck'>
										".$frm->checkbox('ban_rdns_on_access', 1, $pref['enable_rdns'] == 1)."
										<div class='field-help'>".BANLAN_65."</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".BANLAN_64."</td>
								<td class='control'>
									<div class='auto-toggle-area autocheck'>
										".$frm->checkbox('ban_rdns_on_ban', 1, $pref['enable_rdns_on_ban'] == 1)."
										<div class='field-help'>".BANLAN_66."</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".BANLAN_67."</td>
								<td class='control'>
									<div class='field-spacer'>".drop_box('ban_access_guest', $ban_access_guest).BANLAN_70."</div>
									<div class='field-spacer'>".drop_box('ban_access_member', $ban_access_member).BANLAN_69."</div>
									<div class='field-help'>".BANLAN_68."</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".BANLAN_71."</td>
								<td class='control'>
									<div class='auto-toggle-area autocheck'>
										".$frm->checkbox('ban_retrigger', 1, $pref['ban_retrigger'] == 1)."
										<div class='field-help'>".BANLAN_73."</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('update_ban_options', LAN_UPDATE, 'update')."
					</div>
				</fieldset>
				<fieldset id='core-banlist-options-ban'>
					<legend>".BANLAN_74."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".BANLAN_75."</td>
								<td class='control'>
									".$frm->admin_button('remove_expired_bans', BANLAN_76, 'delete')."
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</form>
		";
		$e107->ns->tablerender(BANLAN_72, $emessage->render().$text);
		break;

	case 'times':
		if(!getperms("0"))
			exit();
		$text = '';
		if((!isset($pref['ban_messages'])) || !is_array($pref['ban_messages']))
		{
			$pref['ban_messages'] = array_fill(0, BAN_REASON_COUNT - 1, '');
		}
		if((!isset($pref['ban_durations'])) || !is_array($pref['ban_durations']))
		{
			$pref['ban_durations'] = array_fill(0, BAN_REASON_COUNT - 1, 0);
		}

		$text .= "
			<form method='post' action='".e_SELF.'?'.e_QUERY."' id='ban_options'>
				<fieldset id='core-banlist-times'>
					<legend class='e-hideme'>".BANLAN_77."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='3'>
							<col style='width: 20%'></col>
							<col style='width: 65%'></col>
							<col style='width: 15%'></col>
						</colgroup>
						<thead>
							<tr>
								<th>".BANLAN_28."</th>
								<th>".BANLAN_29."<br />".BANLAN_31."</th>
								<th class='center last'>".BANLAN_30."</th>
							</tr>
						</thead>
						<tbody>
		";
		for($i = 0; $i < BAN_REASON_COUNT; $i ++)
		{
			$text .= "
								<tr>
									<td>
										<strong>".constant('BANLAN_10'.$i)."</strong>
										<div class='field-help'>".constant('BANLAN_11'.$i)."</div>
									</td>
									<td class='center'>
										".$frm->textarea('ban_text_'.($i+1), $pref['ban_messages'][$i], 4, 15)."
									</td>
									<td class='center'>".ban_time_dropdown('', BANLAN_32, $pref['ban_durations'][$i], 'ban_time_'.($i+1))."</td>
								</tr>
				";
		}
		$text .= "
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('update_ban_prefs', LAN_UPDATE, 'update')."
					</div>
				</fieldset>
			</form>
			";

		$e107->ns->tablerender(BANLAN_77, $emessage->render().$text);
		break;

	case 'edit':		// Edit an existing ban
	case 'add':			// Add a new ban
	case 'whedit':		// Edit existing whitelist entry
	case 'whadd':		// Add a new whitelist entry
		if (!isset($banlist_reason)) $banlist_reason = '';
		if (!isset($banlist_ip)) $banlist_ip = '';
		if (!isset($banlist_notes)) $banlist_notes = '';
		$page_title = array('edit' => BANLAN_60, 'add' => BANLAN_9, 'whedit' => BANLAN_59, 'whadd' => BANLAN_58);
		$rdns_warn = varsettrue($pref['enable_rdns']) ? '' : '<div class="field-help error">'.BANLAN_12.'</div>';
		$next = ($action == 'whedit' || $action == 'whadd') ? '?white' : '?list';
		// Edit/add form first
		$text .= "
			<form method='post' action='".e_SELF.$next."'>
				<fieldset id='core-banlist-edit'>
					<legend class='e-hideme'>".$page_title[$action]."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>
									".BANLAN_5.":
									<div class='label-note'>
										".BANLAN_13."<a href='".e_ADMIN_ABS."users.php'><img src='".$images_path."users_16.png' alt='' /></a>
									</div>
								</td>
								<td class='control'>
									<input type='hidden' name='entry_intent' value='{$action}' />
									".$frm->text('ban_ip', $e107->ipDecode($banlist_ip), 200)."
									{$rdns_warn}
								</td>
							</tr>
		";

		if(($action == 'add') || ($action == 'whadd') || ($banlist_bantype <= 1) || ($banlist_bantype >= BAN_TYPE_WHITELIST))
		{ // Its a manual or unknown entry - only allow edit of reason on those
			$text .= "
							<tr>
								<td class='label'>".BANLAN_7.": </td>
								<td class='control'>
									".$frm->textarea('ban_reason', $banlist_reason, 4, 50)."
								</td>
							</tr>
			";
		}
		elseif($action == 'edit')
		{
			$text .= "
							<tr>
								<td class='label'>".BANLAN_7.": </td>
								<td class='control'>{$banlist_reason}</td>
							</tr>
			";
		}

		if($action == 'edit')
		{
			$text .= "
							<tr>
								<td class='label'>".BANLAN_28.": </td>
								<td class='control'>".constant('BANLAN_10'.$banlist_bantype)." - ".constant('BANLAN_11'.$banlist_bantype)."</td>
							</tr>
			";
		}

		$text .= "
							<tr>
								<td class='label'>".BANLAN_19.": </td>
								<td class='control'>
									".$frm->textarea('ban_notes', $banlist_notes, 4, 50)."
								</td>
							</tr>
		";

		if($action == 'edit' || $action == 'add')
		{
			$inhelp = (($action == 'edit') ? '<div class="field-help">'.BANLAN_26.($banlist_banexpires ? strftime(BAN_TIME_FORMAT, $banlist_banexpires) : BANLAN_21).'</div>' : '');

			$text .= "
							<tr>
								<td class='label'>".BANLAN_18.": </td>
								<td class='control'>".ban_time_dropdown().$inhelp."</td>
							</tr>
			";
		}

		$text .= "
						</tbody>
					</table>
					<div class='buttons-bar center'>

		";

		/* FORM NOTE EXAMPLE - not needed here as this note is added as label-note (see below)
		$text .= "
			<div class='form-note'>
				".BANLAN_13."<a href='".e_ADMIN_ABS."users.php'><img src='".$images_path."users_16.png' alt='' /></a>
			</div>

		";
		*/

		if($action == 'edit' || $action == 'whedit')
		{
			$text .= "<input type='hidden' name='old_ip' value='{$banlist_ip}' />
				".$frm->admin_button('update_ban', LAN_UPDATE, 'update');
		}
		else
		{
			$text .= $frm->admin_button('add_ban', ($action == 'add' ? BANLAN_8 : BANLAN_53), 'create');
		}

		$text .= "</div>
				</fieldset>
			</form>
		";

		$e107->ns->tablerender($page_title[$action], $emessage->render().$text);
		break; // End of 'Add' and 'Edit'


	case 'transfer':
		$message = '';
		$error = false;
		if(isset($_POST['ban_import']))
		{ // Got a file to import
			require_once (e_HANDLER.'upload_handler.php');
			if(($files = process_uploaded_files(e_UPLOAD, FALSE, array('overwrite' => TRUE, 'max_file_count' => 1, 'file_mask' => 'csv'))) === FALSE)
			{ // Invalid file
				$error = true;
				$message = BANLAN_47;
				$emessage->add($message, E_MESSAGE_ERROR);
			}
			if(empty($files) || varsettrue($files[0]['error']))
			{
				$error = true;
				if(varset($files[0]['message']))
					$emessage->add($files[0]['message'], E_MESSAGE_ERROR);
			}
			if(!$error)
			{ // Got a file of some sort
				$message = process_csv(e_UPLOAD.$files[0]['name'], intval(varset($_POST['ban_over_import'], 0)), intval(varset($_POST['ban_over_expiry'], 0)), $separator_char[intval(varset($_POST['ban_separator'], 1))], $quote_char[intval(varset($_POST['ban_quote'], 3))]);
				banlist_adminlog("07", 'File: '.e_UPLOAD.$files[0]['name'].'<br />'.$message);
			}

		}

		$text = "
			<form method='post' action='".e_ADMIN_ABS."banlist_export.php' id='core-banlist-transfer-form' >
				<fieldset id='core-banlist-transfer-export'>
					<legend>".BANLAN_40."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='3'>
							<col style='width:30%' />
							<col style='width:30%' />
							<col style='width:40%' />
						</colgroup>
						<tbody>
							<tr>
								<th colspan='2'>".BANLAN_36."</th>
								<th>&nbsp;</th>
							</tr>
			";


		for($i = 0; $i < BAN_REASON_COUNT; $i ++)
		{
			$text .= "
									<tr>
									<td colspan='3'>
										".$frm->checkbox("ban_types[{$i}]", $i).$frm->label(constant('BANLAN_10'.$i), "ban_types[{$i}]", $i)."
										<span class='field-help'>(".constant('BANLAN_11'.$i).")</span>
									</td></tr>
			";
		}

		$text .= "<tr>
			<td>".BANLAN_79."</td>
			<td>".select_box('ban_separator', $separator_char).' '.BANLAN_37."</td>
		<td>".select_box('ban_quote', $quote_char).' '.BANLAN_38."</td></tr>";
		$text .= "

						</tbody>
					</table>
					<div class='buttons-bar center'>".$frm->admin_button('ban_export', BANLAN_39, 'export', BANLAN_39)."</div>
				</fieldset>
			</form>
		";

		// Now do the import options
		$text .= "
			<form enctype='multipart/form-data' method='post' action='".e_SELF."?transfer' id='ban_import_form' >
				<fieldset id='core-banlist-transfer-import'>
					<legend>".BANLAN_41."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='3'>
							<col style='width:30%' />
							<col style='width:30%' />
							<col style='width:40%' />
						</colgroup>
						<tbody>
							<tr>
								<th colspan='2'>".BANLAN_42."</th>
								<th>&nbsp;</th>
							</tr>
							<tr>
								<td colspan='3'>".$frm->checkbox('ban_over_import', 1).$frm->label(BANLAN_43, 'ban_over_import', 1)."</td>
							</tr>
							<tr>
								<td colspan='3'>".$frm->checkbox('ban_over_expiry', 1).$frm->label(BANLAN_44, 'ban_over_expiry', 1)."</td>
							</tr>
							<tr>
								<td>".BANLAN_46."</td>
								<td colspan='2'>
									".$frm->file('file_userfile[]', array('size' => '40'))."
								</td>
							</tr>
							<tr>
			<td>".BANLAN_80."</td>
			<td>".select_box('ban_separator', $separator_char).' '.BANLAN_37."</td>
		<td>".select_box('ban_quote', $quote_char).' '.BANLAN_38."</td></tr>
						</tbody>
					</table>
								<div class='buttons-bar center'>
								".$frm->admin_button('ban_import', BANLAN_45, 'import')."
								</div>


				</fieldset>
			</form>
		";

		$e107->ns->tablerender(BANLAN_35, $emessage->render().$text);
		break;

	case 'list':
	case 'white':
	default:
		if(($action != 'list') && ($action != 'white'))
			$action = 'list';

		$edit_action = ($action == 'list' ? 'edit' : 'whedit');
		$del_action = ($action == 'list' ? 'remove' : 'whremove');
		$col_widths = array('list' => array(10, 5, 35, 30, 10, 10), 'white' => array(15, 40, 35, 10));
		$col_titles = array('list' => array(BANLAN_17, BANLAN_20, BANLAN_10, BANLAN_19, BANLAN_18, LAN_OPTIONS), 'white' => array(BANLAN_55, BANLAN_56, BANLAN_19, LAN_OPTIONS));
		$no_values = array('list' => BANLAN_2, 'white' => BANLAN_54);
		$col_defs = array('list' => array('banlist_datestamp' => 0, 'banlist_bantype' => 0, 'ip_reason' => BANLAN_7, 'banlist_notes' => 0, 'banlist_banexpires' => 0, 'ban_options' => 0), 'white' => array('banlist_datestamp' => 0, 'ip_reason' => BANLAN_57, 'banlist_notes' => 0, 'ban_options' => 0));

		$text = "
			<form method='post' action='".e_SELF.'?'.$action."' id='core-banlist-form'>
				<fieldset id='core-banlist'>
					<legend class='e-hideme'>".($action == 'list' ? BANLAN_3 : BANLAN_61)."</legend>
					".$frm->hidden("ban_secure", "1")."
		";

		$filter = ($action == 'white') ? 'banlist_bantype='.BAN_TYPE_WHITELIST : 'banlist_bantype!='.BAN_TYPE_WHITELIST;

		if(!$ban_total = $sql->db_Select("banlist", "*", $filter." ORDER BY banlist_ip"))
		{
			$text .= "<div class='center'>".$no_values[$action]."</div>";
		}
		else
		{
			$text .= "
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='".count($col_widths[$action])."'>
			";
			foreach($col_widths[$action] as $fw)
			{
				$text .= "
								<col style='width:{$fw}%' />
				";
			}
			$text .= "
						</colgroup>
						<thead>
							<tr>
			";
			$cnt = 0;
			foreach($col_titles[$action] as $ct)
			{
				$cnt ++;
				$text .= "<th".(($cnt == count($col_widths[$action])) ? " class='center last'" : "").">{$ct}</th>";
			}
			$text .= "</tr>
						</thead>
						<tbody>";
			while($row = $sql->db_Fetch())
			{
				extract($row);//FIXME - kill extract()
				$banlist_reason = str_replace("LAN_LOGIN_18", BANLAN_11, $banlist_reason);
				$text .= "<tr>";
				foreach($col_defs[$action] as $cd => $fv)
				{
					$row_class = '';
					switch($cd)
					{
						case 'banlist_datestamp':
							$val = ($banlist_datestamp ? strftime(BAN_TIME_FORMAT, $banlist_datestamp) : BANLAN_22);
							break;
						case 'banlist_bantype':
							$val = "<div class='nowrap' title='".constant('BANLAN_11'.$banlist_bantype)."'>".constant('BANLAN_10'.$banlist_bantype)." <a href='#' title='".constant('BANLAN_11'.$banlist_bantype)."' onclick='return false;'><img class='action info S16' src='".e_IMAGE_ABS."admin_images/info_16.png' alt='' /></a></div>";
							break;
						case 'ip_reason':
							$val = $e107->ipDecode($banlist_ip)."<br />".$fv.": ".$banlist_reason;
							break;
						case 'banlist_banexpires':
							$val = ($banlist_banexpires ? strftime(BAN_TIME_FORMAT, $banlist_banexpires).(($banlist_banexpires < time()) ? ' ('.BANLAN_34.')' : '') : BANLAN_21)."<br />".ban_time_dropdown("onchange=\"e107Helper.urlJump('".e_SELF."?newtime-{$banlist_ip}-'+this.value)\"");
							break;
						case 'ban_options':
							$row_class = ' class="center"';
							$val = "
							<a class='action edit' href='".e_SELF."?{$edit_action}-{$banlist_ip}'>".ADMIN_EDIT_ICON."</a>
<input class='action delete no-confirm' name='delete_ban_entry' value='".e_SELF."?{$del_action}-{$banlist_ip}' type='image' src='".ADMIN_DELETE_ICON_PATH."' alt='".LAN_DELETE."' title='".$tp->toJS(LAN_CONFIRMDEL." [".$e107->ipDecode($banlist_ip)."]")."' />";
							break;
						case 'banlist_notes':
						default:
							$val = $row[$cd];
					}

					$text .= "<td{$row_class}>{$val}</td>";
				}
				$text .= '</tr>';
			}
			$text .= "</tbody>
					</table>
					<script type='text/javascript'>
					(function () {
						var del_sel = \$\$('input[name=delete_ban_entry]');
						del_sel.each(function (element) {
							var msg = element.readAttribute('title');
							element.writeAttribute('title', '".LAN_DELETE."').writeAttribute('confirm-msg', msg);
						});
						del_sel.invoke('observe', 'click', function (event) {

							var element = event.element(), msg = element.readAttribute('confirm-msg');
							if(!e107Helper.confirm(msg)) { event.stop(); return; }
							\$('core-banlist-form').writeAttribute('action', element.value).submit();
						});
					}())
					</script>
			";
		}
		$text .= "
				</fieldset>
			</form>
		";

		$e107->ns->tablerender(($action == 'list' ? BANLAN_3 : BANLAN_61), $emessage->render().$text);
	// End of case 'list' and the default case
} // End switch ($action)


require_once ('footer.php');


/**
 *	Admin menu options
 */
function banlist_adminmenu()
{
	$action = (e_QUERY) ? e_QUERY : 'list';

	$var['list']['text'] = BANLAN_14; // List existing bans
	$var['list']['link'] = e_SELF.'?list';
	$var['list']['perm'] = '4';

	$var['add']['text'] = BANLAN_25; // Add a new ban
	$var['add']['link'] = e_SELF.'?add';
	$var['add']['perm'] = '4';

	$var['white']['text'] = BANLAN_52; // List existing whitelist entries
	$var['white']['link'] = e_SELF.'?white';
	$var['white']['perm'] = '4';

	$var['whadd']['text'] = BANLAN_53; // Add a new whitelist entry
	$var['whadd']['link'] = e_SELF.'?whadd';
	$var['whadd']['perm'] = '4';

	$var['transfer']['text'] = BANLAN_35;
	$var['transfer']['link'] = e_SELF.'?transfer';
	$var['transfer']['perm'] = '4';

	if(getperms('0'))
	{
		$var['times']['text'] = BANLAN_15;
		$var['times']['link'] = e_SELF.'?times';
		$var['times']['perm'] = '0';

		$var['options']['text'] = LAN_OPTIONS;
		$var['options']['link'] = e_SELF.'?options';
		$var['options']['perm'] = '0';
	}
	e_admin_menu(BANLAN_16, $action, $var);
}



// Parse the date string used by the import/export - YYYYMMDD_HHMMSS
function parse_date($instr)
{
	if(strlen($instr) != 15)
		return 0;
	return mktime(substr($instr, 9, 2), substr($instr, 11, 2), substr($instr, 13, 2), substr($instr, 4, 2), substr($instr, 6, 2), substr($instr, 0, 4));
}



// Process the imported CSV file, update the database, delete the file.
// Return a message
function process_csv($filename, $override_imports, $override_expiry, $separator = ',', $quote = '"')
{
	global $sql, $pref, $e107, $emessage;
	//  echo "Read CSV: {$filename} separator: {$separator}, quote: {$quote}  override imports: {$override_imports}  override expiry: {$override_expiry}<br />";
	// Renumber imported bans
	if($override_imports)
		$sql->db_Update('banlist', "`banlist_bantype`=".BAN_TYPE_TEMPORARY." WHERE `banlist_bantype` = ".BAN_TYPE_IMPORTED);
	$temp = file($filename);
	$line_num = 0;
	foreach($temp as $line)
	{ // Process one entry
		$line = trim($line);
		$line_num ++;
		if($line)
		{
			$fields = explode($separator, $line);
			$field_num = 0;
			$field_list = array('banlist_bantype' => BAN_TYPE_IMPORTED);
			foreach($fields as $f)
			{
				$f = trim($f);
				if(substr($f, 0, 1) == $quote)
				{
					if(substr($f, - 1, 1) == $quote)
					{ // Strip quotes
						$f = substr($f, 1, - 1); // Strip off the quotes
					}
					else
					{
						$emessage->add(BANLAN_49.$line_num, E_MESSAGE_ERROR);
						return BANLAN_49.$line_num;
					}
				}
				// Now handle the field
				$field_num ++;
				switch($field_num)
				{
					case 1: // IP address
						$field_list['banlist_ip'] = $e107->ipEncode($f);
						break;
					case 2: // Original date of ban
						$field_list['banlist_datestamp'] = parse_date($f);
						break;
					case 3: // Expiry of ban - depends on $override_expiry
						if($override_expiry)
						{
							$field_list['banlist_banexpires'] = parse_date($f);
						}
						else
						{ // Use default ban time from now
							$field_list['banlist_banexpires'] = $pref['ban_durations'][BAN_TYPE_IMPORTED] ? time() + (60 * 60 * $pref['ban_durations'][BAN_TYPE_IMPORTED]) : 0;
						}
						break;
					case 4: // Original ban type - we always ignore this and force to 'imported'
						break;
					case 5: // Ban reason originally generated by E107
						$field_list['banlist_reason'] = $f;
						break;
					case 6: // Any user notes added
						$field_list['banlist_notes'] = $f;
						break;
					default: // Just ignore any others
				}
			}
			$qry = "REPLACE INTO `#banlist` (".implode(',', array_keys($field_list)).") values ('".implode("', '", $field_list)."')";
			//	  echo count($field_list)." elements, query: ".$qry."<br />";
			if(!$sql->db_Select_gen($qry))
			{
				$emessage->add(BANLAN_50.$line_num, E_MESSAGE_ERROR);
				return BANLAN_50.$line_num;
			}
		}
	}
	// Success here - may need to delete old imported bans
	if($override_imports)
		$sql->db_Delete('banlist', "`banlist_bantype` = ".BAN_TYPE_TEMPORARY);
	@unlink($filename); // Delete file once done
	$emessage->add(str_replace('--NUM--', $line_num, BANLAN_51).$filename, E_MESSAGE_SUCCESS);
	return str_replace('--NUM--', $line_num, BANLAN_51).$filename;
}



/**
 *	Log event to admin log
 *
 *	@param string $msg_num - exactly two numeric characters corresponding to a log message
 *	@param string $woffle - information for the body of the log entre
 *
 *	@return none
 */
function banlist_adminlog($msg_num = '00', $woffle = '')
{
	//  if (!varset($pref['admin_log_log']['admin_banlist'],0)) return;
	e107::getAdminLog()->log_event('BANLIST_'.$msg_num, $woffle, E_LOG_INFORMATIVE, '');
}


/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript'>
			//add required core lan - delete confirm message
			(".e_jshelper::toString(LAN_JSCONFIRM).").addModLan('core', 'delete_confirm');
			if(typeof e107Admin == 'undefined') var e107Admin = {}

			/**
			 * OnLoad Init Control
			 */
			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}

?>