<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/pm/pm_conf.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-11-17 13:48:45 $
 * $Author: marj_nl_fr $
 */

$retrieve_prefs[] = 'pm_prefs';
$eplug_admin = TRUE;
require_once("../../class2.php");
require_once(e_PLUGIN."pm/pm_class.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");

if (!getperms("P"))
{
	header("location:".e_BASE."index.php");
	exit;
}

$action = e_QUERY;

require_once(e_ADMIN."auth.php");

if($action == "")
{
	$action = "main";
}

$pm_prefs = $sysprefs->getArray("pm_prefs");

//pm_prefs record not found in core table, set to defaults and create record
if(!is_array($pm_prefs))
{
	require_once(e_PLUGIN."pm/pm_default.php");
	$pm_prefs = pm_set_default_prefs();
	$sysprefs->setArray('pm_prefs');
	$message = ADLAN_PM_3;
}

$lan_file = e_PLUGIN."pm/languages/admin/".e_LANGUAGE.".php";
// include_once(is_readable($lan_file) ? $lan_file : e_PLUGIN."pm/languages/admin/English.php");
	
if (isset($_POST['update_prefs'])) 
{
	foreach($_POST['option'] as $k => $v)
	{
		$pm_prefs[$k] = $v;
	}
	$sysprefs->setArray('pm_prefs');
	$message = ADLAN_PM_4;
}

if(isset($_POST['addlimit']))
{
	if($sql->db_Select('generic','gen_id',"gen_type = 'pm_limit' AND gen_datestamp = {$_POST['newlimit_class']}"))
	{
		$message = ADLAN_PM_5;
	}
	else
	{
		if($sql->db_Insert('generic',"0, 'pm_limit', '{$_POST['newlimit_class']}', '{$_POST['new_inbox_count']}', '{$_POST['new_outbox_count']}', '{$_POST['new_inbox_size']}', '{$_POST['new_outbox_size']}'"))
		{
			$message = ADLAN_PM_6;
		}
		else
		{
			$message = ADLAN_PM_7;
		}
	}
}

if(isset($_POST['updatelimits']))
{
	if($pref['pm_limits'] != $_POST['pm_limits'])
	{
		$pref['pm_limits'] = $_POST['pm_limits'];
		save_prefs();
		$message .= ADLAN_PM_8."<br />";
	}
	foreach(array_keys($_POST['inbox_count']) as $id)
	{
		if($_POST['inbox_count'][$id] == "" && $_POST['outbox_count'][$id] == "" && $_POST['inbox_size'][$id] == "" && $_POST['outbox_size'][$id] == "")
		{
			//All entries empty - Remove record
			if($sql->db_Delete('generic',"gen_id = {$id}"))
			{
				$message .= $id.ADLAN_PM_9."<br />";
			}
			else
			{
				$message .= $id.ADLAN_PM_10."<br />";
			}
		}
		else
		{
			$sql->db_Update('generic',"gen_user_id = '{$_POST['inbox_count'][$id]}', gen_ip = '{$_POST['outbox_count'][$id]}', gen_intdata = '{$_POST['inbox_size'][$id]}', gen_chardata = '{$_POST['outbox_size'][$id]}' WHERE gen_id = {$id}");
			$message .= $id.ADLAN_PM_11."<br />";
		}
	}
}

if(isset($message))
{
	$ns->tablerender("", $message);
}


if($action == "main")
{
	$ns->tablerender(ADLAN_PM_12, show_options());
}

if($action == "limits")
{
	$ns->tablerender(ADLAN_PM_14, show_limits());
	$ns->tablerender(ADLAN_PM_15, add_limit());
}

require_once(e_ADMIN."footer.php");

function yes_no($fname)
{
		global $pm_prefs;
		$ret = 
		form::form_radio("option[{$fname}]", "1", ($pm_prefs[$fname] ? "1" : "0"), "", "").LAN_YES." ".
		form::form_radio("option[{$fname}]", "0", ($pm_prefs[$fname] ? "0" : "1"), "", "").LAN_NO;
		return $ret;
}


function show_options()
{
	global $pm_prefs;
	$txt = "
	<form method='post' action='".e_SELF."'>
	<table class='fborder' style='width:95%'>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_16."</td>
		<td class='forumheader3' style='width:25%'>".form::form_text('option[title]', 20, $pm_prefs['title'], 50)."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_17."</td>
		<td class='forumheader3' style='width:25%'>".yes_no('animate')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_18."</td>
		<td class='forumheader3' style='width:25%'>".yes_no('dropdown')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_19."</td>
		<td class='forumheader3' style='width:25%'>".form::form_text('option[read_timeout]', 5, $pm_prefs['read_timeout'], 5)."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_20."</td>
		<td class='forumheader3' style='width:25%'>".form::form_text('option[unread_timeout]', 5, $pm_prefs['unread_timeout'], 5)."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_21."</td>
		<td class='forumheader3' style='width:25%'>".yes_no('popup')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_22."</td>
		<td class='forumheader3' style='width:25%'>".form::form_text('option[popup_delay]', 5, $pm_prefs['popup_delay'], 5)." ".ADLAN_PM_44."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_23."</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[pm_class]', $pm_prefs['pm_class'], 'off', 'member,admin,classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_24."</td>
		<td class='forumheader3' style='width:25%'>".form::form_text('option[perpage]', 5, $pm_prefs['perpage'], 5)."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_25."</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[notify_class]', $pm_prefs['notify_class'], 'off', 'nobody,member,admin,classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_26."</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[receipt_class]', $pm_prefs['receipt_class'], 'off', 'nobody,member,admin,classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_27."</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[attach_class]', $pm_prefs['attach_class'], 'off', 'nobody,member,admin,classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_28."</td>
		<td class='forumheader3' style='width:25%'>".form::form_text('option[attach_size]', 8, $pm_prefs['attach_size'], 8)." kB</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_29."</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[sendall_class]', $pm_prefs['sendall_class'], 'off', 'nobody,member,admin,classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_30."</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[multi_class]', $pm_prefs['multi_class'], 'off', 'nobody,member,admin,classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>".ADLAN_PM_31."</td>
		<td class='forumheader3' style='width:25%'>".yes_no('allow_userclass')."</td>
	</tr>
	<tr>
		<td class='forumheader' colspan='2' style='text-align:center'><input type='submit' class='button' name='update_prefs' value='".ADLAN_PM_32."' /></td>
	</tr>
	</table>
	</form>
	";
	return $txt;
}

function show_limits()
{
	global $sql, $pref;
	if($sql->db_Select('userclass_classes','userclass_id, userclass_name'))
	{
		$classList = $sql->db_getList();
	}
	if($sql->db_Select("generic", "gen_id as limit_id, gen_datestamp as limit_classnum, gen_user_id as inbox_count, gen_ip as outbox_count, gen_intdata as inbox_size, gen_chardata as outbox_size", "gen_type = 'pm_limit'"))
	{
		while($row = $sql->db_Fetch())
		{
			$limitList[$row['limit_classnum']] = $row;
		}
	}
	$txt = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table class='fborder' style='width:95%'>
		<tr>
			<td colspan='3' class='forumheader3' style='text-align:left'>".ADLAN_PM_45." 
			<select name='pm_limits' class='tbox'>
		";
		$sel = ($pref['pm_limits'] == 0 ? "selected='selected'" : "");
		$txt .= "<option value='0' {$sel}>".ADLAN_PM_33."</option>\n";

		$sel = ($pref['pm_limits'] == 1 ? "selected='selected'" : "");
		$txt .= "<option value='1' {$sel}>".ADLAN_PM_34."</option>\n";

		$sel = ($pref['pm_limits'] == 2 ? "selected='selected'" : "");
		$txt .= "<option value='2' {$sel}>".ADLAN_PM_35."</option>\n";

		$txt .= "</select>\n";
		
		$txt .= "
			</td>
		</tr>
		<tr>
			<td class='fcaption'>".ADLAN_PM_36."</td>
			<td class='fcaption'>".ADLAN_PM_37."</td>
			<td class='fcaption'>".ADLAN_PM_38."</td>
		</tr>
	";

	if (isset($limitList)) {
		foreach($limitList as $row)
		{
			$txt .= "
			<tr>
			<td class='forumheader3'>".r_userclass_name($row['limit_classnum'])."</td>
			<td class='forumheader3'>
			".ADLAN_PM_39."<input type='text' class='tbox' size='5' name='inbox_count[{$row['limit_id']}]' value='{$row['inbox_count']}' /> 
			".ADLAN_PM_40."<input type='text' class='tbox' size='5' name='outbox_count[{$row['limit_id']}]' value='{$row['outbox_count']}' /> 
			</td>
			<td class='forumheader3'>
			".ADLAN_PM_39."<input type='text' class='tbox' size='5' name='inbox_size[{$row['limit_id']}]' value='{$row['inbox_size']}' /> 
			".ADLAN_PM_40."<input type='text' class='tbox' size='5' name='outbox_size[{$row['limit_id']}]' value='{$row['outbox_size']}' /> 
			</td>
			</tr>
			";
		}
	} else {
		$txt .= "
		<tr>
		<td class='forumheader3' colspan='3' style='text-align: center'>".ADLAN_PM_41."</td>
		</tr>
		";
	}

	$txt .= "
	<tr>
	<td class='forumheader' colspan='3' style='text-align:center'>
	<input type='submit' class='button' name='updatelimits' value='".ADLAN_PM_42."' />
	</td>
	</tr>
	";

	$txt .= "</table></form>";
	return $txt;
}

function add_limit()
{
	global $sql, $pref;
	if($sql->db_Select('userclass_classes','userclass_id, userclass_name'))
	{
		$classList = $sql->db_getList();
	}
	if($sql->db_Select("generic", "gen_id as limit_id, gen_datestamp as limit_classnum, gen_user_id as inbox_count, gen_ip as outbox_count, gen_intdata as inbox_size, gen_chardata as outbox_size", "gen_type = 'pm_limit'"))
	{
		while($row = $sql->db_Fetch())
		{
			$limitList[$row['limit_classnum']] = $row;
		}
	}
	$txt = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table class='fborder' style='width:95%'>
		<tr>
			<td class='fcaption'>".ADLAN_PM_36."</td>
			<td class='fcaption'>".ADLAN_PM_37."</td>
			<td class='fcaption'>".ADLAN_PM_38."</td>
		</tr>
	";

	$txt .= "
	<tr>
	<td class='forumheader3'>".r_userclass("newlimit_class", 0, "off", "guest,member,admin,classes,language")."</td>
	<td class='forumheader3'>
		".ADLAN_PM_39."<input type='text' class='tbox' size='5' name='new_inbox_count' value='' /> 
		".ADLAN_PM_40."<input type='text' class='tbox' size='5' name='new_outbox_count' value='' /> 
	</td>
	<td class='forumheader3'>
		".ADLAN_PM_39."<input type='text' class='tbox' size='5' name='new_inbox_size' value='' /> 
		".ADLAN_PM_40."<input type='text' class='tbox' size='5' name='new_outbox_size' value='' /> 
	</td>
	</tr>
	<tr>
	<td class='forumheader' colspan='3' style='text-align:center'>
	<input type='submit' class='button' name='addlimit' value='".ADLAN_PM_43."' />
	</td>
	</tr>
	";

	$txt .= "</table></form>";
	return $txt;
}

function show_menu($action)
{
	global $sql;
	if ($action == "") { $action = "main"; }
	$var['main']['text'] = ADLAN_PM_54;
	$var['main']['link'] = e_SELF;
	$var['limits']['text'] = ADLAN_PM_55;
	$var['limits']['link'] = e_SELF."?limits";
	show_admin_menu(ADLAN_PM_12, $action, $var);
}

function pm_conf_adminmenu() {
	global $action;
	show_menu($action);
}

?>
