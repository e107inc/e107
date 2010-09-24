<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
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
+----------------------------------------------------------------------------+
*/

// Experimental e-token
if(!empty($_POST) && !isset($_POST['e-token']))
{
	// set e-token so it can be processed by class2
	$_POST['e-token'] = '';
}

require_once("../class2.php");
if (!getperms("M")) {
	header("location:".e_BASE."index.php");
	 exit;
}
$e_sub_cat = 'wmessage';
$e_wysiwyg = "data";

require_once(e_HANDLER."preset_class.php");
$pst = new e_preset;
$pst->form = "wmform";
$pst->page = "wmessage.php?create";
$pst->id = "admin_wmessage";
require_once("auth.php");
$pst->save_preset();  // save and render result

require_once(e_HANDLER.'form_handler.php');
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER."ren_help.php");

$rs = new form;

$action = '';
if (e_QUERY) 
{
	$tmp = explode('.', e_QUERY);
	$action = $tmp[0];
	$sub_action = varset($tmp[1], '');
	$id = intval(varset($tmp[2], 0));
	unset($tmp);
}

if($_POST)
{
	$e107cache->clear("wmessage");
}

if (isset($_POST['wm_update'])) 
{
	$data = $tp->toDB($_POST['data']);
	$wm_title = $tp->toDB($_POST['wm_caption']);
	$message = ($sql->db_Update("generic", "gen_chardata ='$data',gen_ip ='$wm_title', gen_intdata='".$_POST['wm_active']."' WHERE gen_id='".$_POST['wm_id']."' ")) ? LAN_UPDATED : LAN_UPDATED_FAILED;
}

if (isset($_POST['wm_insert'])) 
{
	$wmtext = $tp->toDB($_POST['data']);
	$wmtitle = $tp->toDB($_POST['wm_caption']);
	$message = ($sql->db_Insert("generic", "0, 'wmessage', '".time()."', ".USERID.", '{$wmtitle}', '{$_POST['wm_active']}', '{$wmtext}' ")) ? LAN_CREATED :  LAN_CREATED_FAILED ;
}

if (isset($_POST['updateoptions'])) 
{
	$pref['wm_enclose'] = $_POST['wm_enclose'];
	$pref['wmessage_sc'] = $_POST['wmessage_sc'];
	save_prefs();
	$message = LAN_SETSAVED;
}

if (isset($_POST['main_delete'])) 
{
	$del_id = array_keys($_POST['main_delete']);
	$message = ($sql->db_Delete("generic", "gen_id='".$del_id[0]."' ")) ? LAN_DELETED : LAN_DELETED_FAILED ;
}

if (isset($message)) 
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

// Show Existing -------
if ($action == "main" || $action == "") 
{
	if ($wm_total = $sql->db_Select("generic", "*", "gen_type='wmessage' ORDER BY gen_id ASC")) 
	{
		$wmList = $sql->db_getList();
		$text = $rs->form_open('post', e_SELF, 'myform_wmessage', '', '');
		$text .= "<div style='text-align:center'>
			<table class='fborder' style='".ADMIN_WIDTH."'>
			<tr>
			<td class='fcaption' style='width:5%'>ID</td>
			<td class='fcaption' style='width:70%'>".WMLAN_02."</td>
			<td class='fcaption' style='width:20%'>".WMLAN_03."</td>
			<td class='fcaption' style='width:15%'>".LAN_OPTIONS."</td>
			</tr>";
		foreach($wmList as $row) 
		{
			$text .= "
			<tr>
				<td class='forumheader3' style='width:5%; text-align: center; vertical-align: middle'>".$row['gen_id']."</td>
				<td style='width:70%' class='forumheader3'>".strip_tags($tp->toHTML($row['gen_ip']))."</td>
				<td style='width:70%' class='forumheader3'>".r_userclass_name($row['gen_intdata'])."</td>
            	<td style='width:15%; text-align:center; white-space: nowrap' class='forumheader3'>
					<a href='".e_SELF."?create.edit.{$row['gen_id']}'>".ADMIN_EDIT_ICON."</a>
					<input type='image' title='".LAN_DELETE."' name='main_delete[".$row['gen_id']."]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".LAN_CONFIRMDEL." [ID: {$row['gen_id']} ]')\"/>
				</td>
			</tr>";
		}

		$text .= "</table></div>";
		$text .= "<input type='hidden' name='e-token' value='".e_TOKEN."' />".$rs->form_close();
	} else {
		$text .= "<div style='text-align:center'>".WMLAN_09."</div>";
	}
	$ns->tablerender(WMLAN_00, $text);
}

// Create and Edit
if ($action == "create" || $action == "edit")
{

	if ($sub_action == "edit")
	{
		$sql->db_Select("generic", "gen_intdata, gen_ip, gen_chardata", "gen_id = $id");
		$row = $sql->db_Fetch();
	}

	if ($sub_action != 'edit')
	{
		$preset = $pst->read_preset("admin_wmessage");
		if (is_array($preset))
		{
			extract($preset);
		}
	}

	$text = "
		<div style='text-align:center'>
		<form method='post' action='".e_SELF."'  id='wmform'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		";

	$text .= "
		<tr>
		<td style='width:20%' class='forumheader3'>".WMLAN_10."</td>
		<td style='width:60%' class='forumheader3'>
		<input type='text' class='tbox' id='wm_caption' name='wm_caption' maxlength='80' style='width:95%' value=\"".$tp->toForm($row['gen_ip'])."\" />
		</td>
		</tr>";

	$text .= "<tr>
		<td style='width:20%' class='forumheader3'>".WMLAN_04."</td>
		<td style='width:60%' class='forumheader3'>
		<textarea class='tbox' id='data' name='data' cols='70' rows='15' style='width:95%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this)'>".$tp->toForm($row['gen_chardata'])."</textarea>
		<br />";

		$text .= display_help("helpb", "admin");


	$text .= "
		</td>
		</tr>
		<tr><td class='forumheader3'>".WMLAN_03."</td>
		<td class='forumheader3'>".r_userclass("wm_active", $row['gen_intdata'], "off", "public,guest,nobody,member,admin,main,classes")."</td></tr>";

	$text .= "
		<tr style='vertical-align:top'>
		<td colspan='2' class='forumheader' style='text-align:center'>";

	$text .= ($sub_action == "edit") ? "<input class='button' type='submit' name='wm_update' value='".LAN_UPDATE."' />" :
	 "<input class='button' type='submit' name='wm_insert' value='".LAN_CREATE."' />" ;
	$text .= "<input type='hidden' name='wm_id' value='".$id."' />
	<input type='hidden' name='e-token' value='".e_TOKEN."' />";
	$text .= "</td>
		</tr>
		</table>
		</form>
		</div>";
	$ns->tablerender(WMLAN_01, $text);
}


if ($action == "opt") {
	global $pref, $ns;
	$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>

		<td style='width:70%' class='forumheader3'>
		".WMLAN_05."<br />
		<span class='smalltext'>".WMLAN_06."</span>
		</td>
		<td class='forumheader3' style='width:30%;text-align:center'>". (varset($pref['wm_enclose'], 0) ? "<input type='checkbox' name='wm_enclose' value='1' checked='checked' />" : "<input type='checkbox' name='wm_enclose' value='1' />")."
		</td>
		</tr>
		<tr>

		<td style='width:70%' class='forumheader3'>
		".WMLAN_07."
		</td>
		<td class='forumheader3' style='width:30%;text-align:center'>". (varset($pref['wmessage_sc'], 0) ? "<input type='checkbox' name='wmessage_sc' value='1' checked='checked' />" : "<input type='checkbox' name='wmessage_sc' value='1' />")."
		</td>
		</tr>

		<tr style='vertical-align:top'>
		<td colspan='2'  style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='updateoptions' value='".LAN_SAVE."' />
		<input type='hidden' name='e-token' value='".e_TOKEN."' />
		</td>
		</tr>

		</table>
		</form>
		</div>";

	$ns->tablerender(WMLAN_00.": ".LAN_PREFS, $text);


}

function wmessage_adminmenu() {
	global $action;
	if ($action == "") {
		$action = "main";
	}
	$var['main']['text'] = WMLAN_00;
	$var['main']['link'] = e_SELF;
	$var['create']['text'] = WMLAN_01;
	$var['create']['link'] = e_SELF."?create";
	$var['opt']['text'] = LAN_PREFS;
	$var['opt']['link'] = e_SELF."?opt";

	show_admin_menu(LAN_OPTIONS, $action, $var);
}

require_once("footer.php");
?>