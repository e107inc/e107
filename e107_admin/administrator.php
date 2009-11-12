<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administrators Management
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/administrator.php,v $
 * $Revision: 1.16 $
 * $Date: 2009-11-12 01:53:16 $
 * $Author: e107coders $
 *
*/

require_once('../class2.php');
if (!getperms('3'))
{
	header('Location:'.SITEURL.'index.php');
	exit;
}

if(isset($_POST['go_back']))
{ //return to listing - clear all posted data
	header('Location:'.e_ADMIN_ABS.e_PAGE);
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'admin';
require_once('auth.php');


require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."message_handler.php");
$frm = new e_form(true);
$emessage = &eMessage::getInstance();

$action = '';
$sub_action = -1;
if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];					// Used when called from elsewhere
	$sub_action = varset($tmp[1],-1);	// User ID
	unset($tmp);
}


if (isset($_POST['update_admin']))
{	// Permissions updated
	$modID = intval($_POST['a_id']);
	if ($modID == 0)
	{
		exit;
	}
	$sql->db_Select("user", "*", "user_id=".$modID);
	$row = $sql->db_Fetch();
	$a_name = $row['user_name'];

	$perm = "";

	foreach($_POST['perms'] as $value)
	{
		$value = $tp->toDB($value);
		if ($value == "0")
		{
			if (!getperms('0')) { $value = ""; break; }
			$perm = "0"; break;
		}

		if ($value)
		{
			$perm .= $value.".";
		}
    }

	admin_update($sql->db_Update("user", "user_perms='{$perm}' WHERE user_id='{$modID}' "), 'update', sprintf(ADMSLAN_2, $tp->toDB($_POST['ad_name'])), false, false);
	$logMsg = str_replace(array('--ID--', '--NAME--'),array($modID, $a_name),ADMSLAN_72).$perm;
	$admin_log->log_event('ADMIN_01',$logMsg,E_LOG_INFORMATIVE,'');
	unset($modID, $ad_name, $a_perms);
}


if (isset($_POST['edit_admin']) || $action == "edit")
{
	$edid = array_keys($_POST['edit_admin']);
    $theid = intval(($sub_action < 0) ? $edid[0] : $sub_action);
	if ((!$sql->db_Select("user", "*", "user_id=".$theid))
		|| !($row = $sql->db_Fetch()))
	{
		$emessage->add("Couldn't find user ID: {$theid}, {$sub_action}, {$edid[0]}", E_MESSAGE_DEBUG);	// Debug code - shouldn't be executed
	}
}


if (isset($_POST['del_admin']) && count($_POST['del_admin']))
{
	$delid = array_keys($_POST['del_admin']);
	$aID = intval($delid[0]);
	$sql->db_Select("user", "*", "user_id= ".$aID);
	$row = $sql->db_Fetch();

	if ($row['user_id'] == 1)
	{	// Can't delete main admin
		$text = $row['user_name']." ".ADMSLAN_6."
		<br /><br />
		<a href='".e_ADMIN_ABS."administrator.php'>".ADMSLAN_4."</a>";

		$emessage->add($text, E_MESSAGE_ERROR);
		$ns->tablerender(LAN_ERROR, $emessage->render());

		require_once("footer.php");
		exit;
	}

	admin_update($sql -> db_Update("user", "user_admin=0, user_perms='' WHERE user_id= ".$aID), 'update', ADMSLAN_61, LAN_DELETED_FAILED, false);
	$logMsg = str_replace(array('--ID--', '--NAME--'),array($aID, $row['user_name']),ADMSLAN_73);
	$admin_log->log_event('ADMIN_02',$logMsg,E_LOG_INFORMATIVE,'');
}


if(isset($_POST['edit_admin']) || $action == "edit")
{
	edit_administrator($row);
}
else
{
   show_admins();
}


function show_admins()
{
	$sql = e107::getDb();
	$frm = e107::getForm();
	$ns = e107::getRender();
	$mes = e107::getMessage();
	

	require_once(e_HANDLER."user_handler.php");
	$prm = new e_userperms;
	
	
	$sql->db_Select("user", "*", "user_admin='1'");

	$text = "
	<form action='".e_SELF."' method='post' id='del_administrator'>
		<fieldset id='core-administrator-list'>
			<legend class='e-hideme'>".ADMSLAN_13."</legend>
			<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='4'>
					<col style='width:  5%'></col>
					<col style='width: 20%'></col>
					<col style='width: 65%'></col>
					<col style='width: 10%'></col>
				</colgroup>
				<thead>
					<tr>
						<th>ID</th>
						<th>".ADMSLAN_56."</th>
						<th>".ADMSLAN_18."</th>
						<th class='center last'>".LAN_OPTIONS."</th>
					</tr>
				</thead>
				<tbody>

	";

	while ($row = $sql->db_Fetch())
	{
		//$permtxt = "";
		$text .= "
					<tr>
						<td>".$row['user_id']."</td>
						<td><a href='".$e107->url->getUrl('core:user', 'main', "func=profile&id={$row['user_id']}")."'>".$row['user_name']."</a></td>
						<td>
							".$prm->renderperms($row['user_perms'],$row['user_id'],"words")."
						</td>
						<td class='center'>
		";
		if($row['user_id'] != "1")
		{
    		$text .= "
							".$frm->submit_image("edit_admin[{$row['user_id']}]", 'edit', 'edit', LAN_EDIT)."
							".$frm->submit_image("del_admin[{$row['user_id']}]", 'del', 'delete', $e107->tp->toJS(ADMSLAN_59."? [".$row['user_name']."]"))."

			";
    	}

		$text .= "
						</td>
					</tr>
		";
	}

	$text .= "
				</tbody>
			</table>
			".$frm->hidden('del_administrator_confirm','1')."
		</fieldset>
	</form>

	";
	$ns->tablerender(ADMSLAN_13, $mes->render().$text);
}

function edit_administrator($row)
{
    global $pref;
	$lanlist = explode(",",e_LANLIST);
	require_once(e_HANDLER."user_handler.php");
	$prm = new e_userperms;
	$ns = e107::getRender();
	$sql = e107::getDb();
	$frm = e107::getForm();
	

	$a_id = $row['user_id'];
	$ad_name = $row['user_name'];
	$a_perms = $row['user_perms'];

	$text = "
		<form method='post' action='".e_SELF."' id='myform'>
			<fieldset id='core-administrator-edit'>
				<legend class='e-hideme'>".ADMSLAN_52."</legend>
				<table cellpadding='0' cellspacing='0' class='adminform'>
					<colgroup span='2'>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
						<tr>
							<td class='label'>".ADMSLAN_16.": </td>
							<td class='control'>
								".$ad_name."
								<input type='hidden' name='ad_name' size='60' value='{$ad_name}' />
							</td>
						</tr>
						<tr>
							<td class='label'>".ADMSLAN_18."</td>
							<td class='control'>

	";
			
	$groupedList = $prm->getPermList('grouped');
		
	foreach($groupedList as $section=>$list)
	{
		$text .= "\t\t<div class='field-section'><h4>".$prm->renderSectionDiz($section)."</h4>"; //XXX Lan - General	
		foreach($list as $key=>$diz)
		{
			$text .= $prm->checkb($key, $a_perms, $diz);			
		}
		$text .= "</div>";
	}

	$text .= "<div class='field-section'>
		".$frm->admin_button('check_all', 'jstarget:perms', 'action', LAN_CHECKALL)."
		".$frm->admin_button('uncheck_all', 'jstarget:perms', 'action', LAN_UNCHECKALL)."
		</div>
	</td>
	</tr>
			</tbody>
				</table>
				<div class='buttons-bar center'>
					<input type='hidden' name='a_id' value='{$a_id}' />
					".$frm->admin_button('update_admin', ADMSLAN_52, 'update')."
					".$frm->admin_button('go_back', ADMSLAN_70)."
				</div>
			</fieldset>
		</form>
	";

	$ns->tablerender(ADMSLAN_52, $text);
}
require_once("footer.php");





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
			('".LAN_JSCONFIRM."').addModLan('core', 'delete_confirm');
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}
?>