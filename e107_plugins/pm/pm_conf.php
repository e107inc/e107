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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm/pm_conf.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-09-05 17:00:44 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
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
	$message = "PM settings not found, default values set";
}

$lan_file = e_PLUGIN."pm/languages/admin/".e_LANGUAGE.".php";
include_once(is_readable($lan_file) ? $lan_file : e_PLUGIN."pm/languages/admin/English.php");
	
if (isset($_POST['update_prefs'])) 
{
	foreach($_POST['option'] as $k => $v)
	{
		$pm_prefs[$k] = $v;
	}
	$sysprefs->setArray('pm_prefs');
	$message = "Options updated";
}

if(isset($_POST['addlimit']))
{
	if($sql->db_Select('generic','gen_id',"gen_type = 'pm_limit' AND gen_datestamp = {$_POST['newlimit_class']}"))
	{
		$message = "Limit for selected userclass already exists";
	}
	else
	{
		if($sql->db_Insert('generic',"0, 'pm_limit', '{$_POST['newlimit_class']}', '{$_POST['new_inbox_count']}', '{$_POST['new_outbox_count']}', '{$_POST['new_inbox_size']}', '{$_POST['new_outbox_size']}'"))
		{
			$message = "Limit successfully added";
		}
		else
		{
			$message = "Limit not added - unknown error";
		}
	}
}

if(isset($_POST['updatelimits']))
{
	if($pref['pm_limits'] != $_POST['pm_limits'])
	{
		$pref['pm_limits'] = $_POST['pm_limits'];
		save_prefs();
		$message .= "Limit status updated<br />";
	}
	foreach(array_keys($_POST['inbox_count']) as $id)
	{
		if($_POST['inbox_count'][$id] == "" && $_POST['outbox_count'][$id] == "" && $_POST['inbox_size'][$id] == "" && $_POST['outbox_size'][$id] == "")
		{
			//All entries empty - Remove record
			if($sql->db_Delete('generic',"gen_id = {$id}"))
			{
				$message .= $id." - Limit successfully removed<br />";
			}
			else
			{
				$message .= $id." - Limit not removed - unknown error<br />";
			}
		}
		else
		{
			$sql->db_Update('generic',"gen_user_id = '{$_POST['inbox_count'][$id]}', gen_ip = '{$_POST['outbox_count'][$id]}', gen_intdata = '{$_POST['inbox_size'][$id]}', gen_chardata = '{$_POST['outbox_size'][$id]}' WHERE gen_id = {$id}");
			$message .= $id." - Limit successfully updated<br />";
		}
	}
}

if(isset($message))
{
	$ns->tablerender("", $message);
}


if($action == "main")
{
	$ns->tablerender("PM Options", show_options());
}

if('convert' == $action)
{
	$ns->tablerender("PM Conversion", show_conversion());
}

if($action == "limits")
{
	$ns->tablerender("PM Limits", show_limits());
	$ns->tablerender("Add PM Limit", add_limit());
}

require_once(e_ADMIN."footer.php");

function yes_no($fname)
{
		global $pm_prefs;
		$ret = 
		form::form_radio("option[{$fname}]", "1", ($pm_prefs[$fname] ? "1" : "0"), "", "").LAN_YES." ".
		form::form_radio("option[{$fname}]", "0", ($pm_prefs[$fname] ? "0" : "1"), "", "").LAN_NO;
		return $ret;
		/*
	$ret = "<select class='tbox' name='{$fname}'>\n";
	$sel = ($fval == "1" ? " selected='selected' " : "";
	$ret .= "<option value='1' {$sel}>".LAN_YES."\n";
	$sel = ($fval == "0" ? " selected='selected' " : "";
	$ret .= "<option value='0' {$sel}>".LAN_NO."\n";
	$ret .= "</select>\n";
	return $ret;
*/
}


function show_options()
{
	global $pm_prefs;
	$txt = "
	<form method='post' action='".e_SELF."'>
	<table class='fborder' style='width:95%'>
	<tr>
		<td class='forumheader3' style='width:75%'>Plugin title</td>
		<td class='forumheader3' style='width:25%'>".form::form_text('option[title]', 20, $pm_prefs['title'], 50)."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>Show new PM animation</td>
		<td class='forumheader3' style='width:25%'>".yes_no('animate')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>Show user dropdown</td>
		<td class='forumheader3' style='width:25%'>".yes_no('dropdown')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>READ message timeout</td>
		<td class='forumheader3' style='width:25%'>".form::form_text('option[read_timeout]', 5, $pm_prefs['read_timeout'], 5)."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>UNREAD message timeout</td>
		<td class='forumheader3' style='width:25%'>".form::form_text('option[unread_timeout]', 5, $pm_prefs['unread_timeout'], 5)."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>popup notification on new PM</td>
		<td class='forumheader3' style='width:25%'>".yes_no('popup')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>Popup delay timeout</td>
		<td class='forumheader3' style='width:25%'>".form::form_text('option[popup_delay]', 5, $pm_prefs['popup_delay'], 5)." seconds</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>Restrict PM use to</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[pm_class]', $pm_prefs['pm_class'], 'off', 'members, admin, classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>Enable PM email notifications</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[notify_class]', $pm_prefs['notify_class'], 'off', 'nobody, members, admin, classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>Allow user to request read receipt email notifications</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[receipt_class]', $pm_prefs['receipt_class'], 'off', 'nobody, members, admin, classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>Allow posting of attachments</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[attach_class]', $pm_prefs['attach_class'], 'off', 'nobody, members, admin, classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>Maximum attachment size</td>
		<td class='forumheader3' style='width:25%'>".form::form_text('option[attach_size]', 8, $pm_prefs['attach_size'], 8)." kb</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>Allow sending to all members</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[sendall_class]', $pm_prefs['sendall_class'], 'off', 'nobody, members, admin, classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>Allow sending to multiple receipients</td>
		<td class='forumheader3' style='width:25%'>".r_userclass('option[multi_class]', $pm_prefs['multi_class'], 'off', 'nobody, members, admin, classes')."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:75%'>Enable sending to userclass</td>
		<td class='forumheader3' style='width:25%'>".yes_no('allow_userclass')."</td>
	</tr>
	<tr>
		<td class='forumheader' colspan='2' style='text-align:center'><input type='submit' class='button' name='update_prefs' value='update settings' /></td>
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
			<td colspan='3' class='forumheader3' style='text-align:left'>Limit PM by: 
			<select name='pm_limits' class='tbox'>
		";
		$sel = ($pref['pm_limits'] == 0 ? "selected='selected'" : "");
		$txt .= "<option value='0' {$sel}>Inactive (no limits)</option>\n";

		$sel = ($pref['pm_limits'] == 1 ? "selected='selected'" : "");
		$txt .= "<option value='1' {$sel}>PM counts</option>\n";

		$sel = ($pref['pm_limits'] == 2 ? "selected='selected'" : "");
		$txt .= "<option value='2' {$sel}>PM box sizes</option>\n";

		$txt .= "</select>\n";
		
		$txt .= "
			</td>
		</tr>
		<tr>
			<td class='fcaption'>Userclass</td>
			<td class='fcaption'>Count limits</td>
			<td class='fcaption'>Size limits (in KB)</td>
		</tr>
	";

	if (isset($limitList)) {
		foreach($limitList as $row)
		{
			$txt .= "
			<tr>
			<td class='forumheader3'>".r_userclass_name($row['limit_classnum'])."</td>
			<td class='forumheader3'>
			Inbox: <input type='text' class='tbox' size='5' name='inbox_count[{$row['limit_id']}]' value='{$row['inbox_count']}' /> 
			Outbox: <input type='text' class='tbox' size='5' name='outbox_count[{$row['limit_id']}]' value='{$row['outbox_count']}' /> 
			</td>
			<td class='forumheader3'>
			Inbox: <input type='text' class='tbox' size='5' name='inbox_size[{$row['limit_id']}]' value='{$row['inbox_size']}' /> 
			Outbox: <input type='text' class='tbox' size='5' name='outbox_size[{$row['limit_id']}]' value='{$row['outbox_size']}' /> 
			</td>
			</tr>
			";
		}
	} else {
		$txt .= "
		<tr>
		<td class='forumheader3' colspan='3' style='text-align: center'>There are currently no limits set.</td>
		</tr>
		";
	}

	$txt .= "
	<tr>
	<td class='forumheader' colspan='3' style='text-align:center'>
	<input type='submit' class='button' name='updatelimits' value='Update Limits' />
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
			<td class='fcaption'>Userclass</td>
			<td class='fcaption'>Count limits</td>
			<td class='fcaption'>Size limits (in KB)</td>
		</tr>
	";

	$txt .= "
	<tr>
	<td class='forumheader3'>".r_userclass("newlimit_class", 0, "off", "guest, member, admin, classes, language")."</td>
	<td class='forumheader3'>
		Inbox: <input type='text' class='tbox' size='5' name='new_inbox_count' value='' /> 
		Outbox: <input type='text' class='tbox' size='5' name='new_outbox_count' value='' /> 
	</td>
	<td class='forumheader3'>
		Inbox: <input type='text' class='tbox' size='5' name='new_inbox_size' value='' /> 
		Outbox: <input type='text' class='tbox' size='5' name='new_outbox_size' value='' /> 
	</td>
	</tr>
	<tr>
	<td class='forumheader' colspan='3' style='text-align:center'>
	<input type='submit' class='button' name='addlimit' value='Add New Limit' />
	</td>
	</tr>
	";

	$txt .= "</table></form>";
	return $txt;
}

function show_conversion()
{
	global $sql, $ns;
	if(isset($_POST['convert_delete']))
	{
		$ns->tablerender("PM Conversion", pm_delete());
	}

	if(isset($_POST['convert_convert']))
	{
		$ns->tablerender("PM Conversion", pm_convert());
	}

	$old_count = $sql->db_Count("pm_messages","(*)");
	if(!$old_count)
	{
		return "You do not appear to have any old messages from previous vesions, it is save to uninstall the old plugin";
	}
	else
	{
		$txt = "";
		$txt .= "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?convert'>
		You have {$old_count} messages from the older version, please decide what you would like to do with these messages<br /><br />
		If converting messages, any message successfully converted will be removed from old system.
		<br /> <br /> <br />
		<input type='submit' class='button' name='convert_convert' value='Convert to new PM' /> <br /> <br />
		<input type='submit' class='button' name='convert_delete' value='Discard old messages' />
		</form>
		</div>
		";
		return $txt;
	}
}

function pm_convert_uid($name)
{
	global $uinfo;
	$sqlu =& new db;
	$name = trim($name);
	if(!array_key_exists($uinfo[$name]))
	{
		if($sqlu->db_Select("user", "user_id", "user_name LIKE '{$name}'"))
		{
			$row = $sqlu->db_Fetch();
			$uinfo[$name] = $row['user_id'];
		}
		else
		{
			return FALSE;
		}
	}
	return $uinfo[$name];
}
function pm_convert()
{
	global $sql, $uinfo;
	$sql2 =& new db;
	$count = 0;
	if($sql->db_Select("pm_messages","*"))
	{
		while($row = $sql->db_Fetch())
		{
			$from = pm_convert_uid($row['pm_from_user']);
			$to = pm_convert_uid($row['pm_to_user']);
			$size = strlen($row['pm_message']);
			if($from && $to)
			{
				if($sql2->db_Insert("private_msg", "0, '{$from}', '{$to}', '{$row['pm_sent_datestamp']}', '{$row['pm_rcv_datestamp']}', '{$row['pm_subject']}', '{$row['pm_message']}', '0', '0', '', '', '{$size}'"))
				{
					//Insertion of new PM successful, delete old
					$sql2->db_Delete("pm_messages", "pm_id='{$row['pm_id']}'");
					$count++;
				}
			}
			else
			{
				$ret .= "PM #{$row['pm_id']} not converted <br />";
			}
		}
		$ret .= "<br />{$count} messages converted<br />";
	}
	else
	{
		$ret .= "No records found to convert.";
	}
	return $ret;
}	

function show_menu($action)
{
	global $sql;
	if ($action == "") { $action = "main"; }
	$var['main']['text'] = "Main settings";
	$var['main']['link'] = e_SELF;
	$var['limits']['text'] = "Limits";
	$var['limits']['link'] = e_SELF."?limits";
	if($sql->db_Count("plugin","(*)", "WHERE plugin_path = 'pm_menu'"))
	{
		$var['convert']['text'] = "Conversion";
		$var['convert']['link'] = e_SELF."?convert";
	}
	show_admin_menu("PM Options", $action, $var);
}

function pm_conf_adminmenu() {
	global $action;
	show_menu($action);
}

?>