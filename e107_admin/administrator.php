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
 * $Revision: 1.14 $
 * $Date: 2009-08-28 16:10:58 $
 * $Author: marj_nl_fr $
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
    global $sql, $emessage, $e107, $frm;


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
							".renderperms($row['user_perms'],$row['user_id'],"words")."
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
	$e107->ns->tablerender(ADMSLAN_13, $emessage->render().$text);
}

function edit_administrator($row)
{
    global $sql, $e107, $pref, $frm;
	$lanlist = explode(",",e_LANLIST);

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
	//XXX Lan - General
	$text .= "
								<div class='field-section'>
									<h4>".ADMSLAN_74."</h4>
	";
	$text .= checkb("1", $a_perms, ADMSLAN_19);			// Alter site preferences
	$text .= checkb("2", $a_perms, ADMSLAN_20);			// Alter Menus
	$text .= checkb("3", $a_perms, ADMSLAN_21);			// Modify administrator permissions
	$text .= checkb("4", $a_perms, ADMSLAN_22);			// Moderate users/bans etc
	$text .= checkb("5", $a_perms, ADMSLAN_23);			// create/edit custom pages
	$text .= checkb("J", $a_perms, ADMSLAN_41);			// create/edit custom menus
	$text .= checkb("Q", $a_perms, ADMSLAN_24);			// Manage download categories
	$text .= checkb("6", $a_perms, ADMSLAN_25);			// Upload /manage files
	$text .= checkb("Y", $a_perms, ADMSLAN_67);			// file inspector
	$text .= checkb("O", $a_perms, ADMSLAN_68);			// notify
	$text .= checkb("7", $a_perms, ADMSLAN_26);			// Oversee news categories
//	$text .= checkb("8", $a_perms, ADMSLAN_27);			// Oversee link categories
	$text .= checkb("C", $a_perms, ADMSLAN_64);			// Clear Cache - Previously moderate chatbox
	$text .= checkb("9", $a_perms, ADMSLAN_28);			// Take site down for maintenance
	$text .= checkb("W", $a_perms, ADMSLAN_65);			// Configure mail settings and mailout

	$text .= checkb("D", $a_perms, ADMSLAN_29);			// Manage banners
//	$text .= checkb("E", $a_perms, ADMSLAN_30);			// Configure news feed headlines - now plugin
	$text .= checkb("F", $a_perms, ADMSLAN_31);			// Configure emoticons
	$text .= checkb("G", $a_perms, ADMSLAN_32);			// Configure front page content
	$text .= checkb("S", $a_perms, ADMSLAN_33);			// Configure system logs  (previously log/stats - now plugin)
	$text .= checkb("T", $a_perms, ADMSLAN_34);			// Configure meta tags
	$text .= checkb("V", $a_perms, ADMSLAN_35);			// Configure public file uploads
	$text .= checkb("X", $a_perms, ADMSLAN_66);			// Configure Search
	$text .= checkb("A", $a_perms, ADMSLAN_36);			// Configure Image Settings (Previously Moderate forums - NOW PLUGIN)
	$text .= checkb("B", $a_perms, ADMSLAN_37);			// Moderate comments
	$text .= checkb("H", $a_perms, ADMSLAN_39);			// Post news
	$text .= checkb("I", $a_perms, ADMSLAN_40);			// Post links

//	$text .= checkb("K", $a_perms, ADMSLAN_42);					// Post reviews    - NOW PLUGIN
	$text .= checkb("L", $a_perms, ADMSLAN_43);			// Configure URLs
	$text .= checkb("R", $a_perms, ADMSLAN_44);			// Post downloads
	$text .= checkb("U", $a_perms, ADMSLAN_45);			// Schedule Tasks
	$text .= checkb("M", $a_perms, ADMSLAN_46);			// Welcome message
	$text .= checkb("N", $a_perms, ADMSLAN_47);			// Moderate submitted news

	$text .= "
								</div>
								<div class='field-section'>
									<h4>".ADLAN_CL_7."</h4>";
	$text .= checkb("Z", $a_perms, ADMSLAN_62);			// Plugin Manager

	$sql->db_Select("plugin", "*", "plugin_installflag='1'");
	while ($row = $sql->db_Fetch())
	{
		$text .= checkb("P".$row['plugin_id'], $a_perms, LAN_PLUGIN." - ".$e107->tp->toHTML($row['plugin_name'] ,FALSE , 'RAWTEXT,defs'));
	}
	$text .= "
								</div>";
// Language Rights.. --------------
	if($pref['multilanguage'])
	{
		sort($lanlist);
		$text .= "
								<div class='field-section'>
									<h4>".ADLAN_132."</h4>";

		$text .= checkb($pref['sitelanguage'], $a_perms, $pref['sitelanguage']);
		foreach($lanlist as $langval)
		{
			//$langname = $langval;
			$langval = ($langval == $pref['sitelanguage']) ? "" : $langval;
			if ($langval)
	   		{
				$text .= checkb($langval, $a_perms, $langval);
			}
		}
		$text .= "
								</div>";
	}
	// -------------------------

	if (getperms('0'))
	{
		$text .= "
								<div class='field-section'>
									<h4>".ADMSLAN_58."</h4>";
		$text .= checkb("0", $a_perms, ADMSLAN_58);
		$text .= "
								</div>";
	}

	$text .= "
								<div class='field-section'>
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

	$e107->ns->tablerender(ADMSLAN_52, $text);
}
require_once("footer.php");






function checkb($arg, $perms, $label='')
{
	global $frm;
	$par = "<div class='field-spacer'>";
	$par .= $frm->checkbox('perms[]', $arg, getperms($arg, $perms));
	if ($label)
	{
		$par .= $frm->label($label,'perms[]', $arg);
	}
	$par .= "</div>";

	return $par;
}


function renderperms($perm, $id)
{
	global $pref, $pt, $e107;
	if($perm == "0")
	{
   		return ADMSLAN_58;
	}
    $sql2 = e107::getDb('sql2');
	$lanlist = explode(",",e_LANLIST);


	if(!$pt)
	{
    	$pt["1"] = ADMSLAN_19;
		$pt["2"] = ADMSLAN_20;
		$pt["3"] = ADMSLAN_21;
		$pt["4"] = ADMSLAN_22;	// Moderate users/bans etc
		$pt["5"] = ADMSLAN_23;	// create/edit custom pages
        $pt["J"] = ADMSLAN_41;	// create/edit custom menus

		$pt["Q"] = ADMSLAN_24;	// Manage download categories
		$pt["6"] = ADMSLAN_25;	// Upload /manage files
		$pt["Y"] = ADMSLAN_67;	// file inspector
		$pt["O"] = ADMSLAN_68;	// notify
		$pt["7"] = ADMSLAN_26;
		$pt["8"] = ADMSLAN_27;
		$pt["C"] = ADMSLAN_64;
		$pt["9"] = ADMSLAN_28;
		$pt["W"] = ADMSLAN_65;
    	$pt["D"] = ADMSLAN_29;
		$pt["E"] = ADMSLAN_30;
		$pt["F"] = ADMSLAN_31;
		$pt["G"] = ADMSLAN_32;
		$pt["S"] = ADMSLAN_33;
		$pt["T"] = ADMSLAN_34;
		$pt["V"] = ADMSLAN_35;
		$pt["X"] = ADMSLAN_66;
		$pt["A"] = ADMSLAN_36;	// Configure Image Settings
		$pt["B"] = ADMSLAN_37;
		$pt["H"] = ADMSLAN_39;
		$pt["I"] = ADMSLAN_40;
		$pt["L"] = ADMSLAN_43;
		$pt["R"] = ADMSLAN_44;
		$pt["U"] = ADMSLAN_45;
		$pt["M"] = ADMSLAN_46;
		$pt["N"] = ADMSLAN_47;
		$pt["Z"] = ADMSLAN_62;


		$sql2->db_Select("plugin", "*", "plugin_installflag='1'");
		while ($row2 = $sql2->db_Fetch())
		{
			$pt[("P".$row2['plugin_id'])] = LAN_PLUGIN." - ".$e107->tp->toHTML($row2['plugin_name'], FALSE, 'RAWTEXT,defs');
		}
	}

	$tmp = explode(".", $perm);
		$langperm = "";
		foreach($tmp as $pms)
		{
			if(in_array($pms, $lanlist))
			{
				$langperm .= $pms."&nbsp;";
			}
			else
			{
				$permtxt[] = $pms;
                if($pt[$pms])
				{
		   			$ptext[] = $pt[$pms];
				}
			}
		}

	$ret = implode(" ",$permtxt);
	if($pref['multilanguage'])
	{
		$ret .= $langperm;
	}

	$text = "
		<div onclick=\"e107Helper.toggle('id_{$id}')\" class='e-pointer' title='".ADMSLAN_71."'>{$ret}</div>
		<div id='id_{$id}' class='e-hideme'><ul><li>".implode("</li><li>",$ptext)."</li></ul></div>
	";

    return $text;
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
			('".LAN_JSCONFIRM."').addModLan('core', 'delete_confirm');
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}
?>