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
|     $Source: /cvs_backup/e107_0.7/e107_admin/wmessage.php,v $
|     $Revision: 1.17 $
|     $Date: 2005-02-24 08:10:03 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("M")) {
	header("location:".e_BASE."index.php");
	 exit;
}
$e_sub_cat = 'wmessage';
$e_wysiwyg = "wm_text";

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

if (e_QUERY) {
	$tmp = explode('.', e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	unset($tmp);
}

if (isset($_POST['wm_update'])) {
	$wm_text = $tp->toDB($_POST['wm_text']);
	$message = ($sql->db_Update("generic", "gen_chardata ='$wm_text', gen_intdata='".$_POST['wm_active']."' WHERE gen_id='".$_POST['wm_id']."' ")) ? LAN_UPDATED : LAN_UPDATED_FAILED;
}

if (isset($_POST['wm_insert'])) {
	$wmtext = $tp->toDB($_POST['wm_text']);
	$message = ($sql->db_Insert("generic", "0, 'wmessage', '".time()."', ".USERID.", '', '{$_POST['wm_active']}', '{$wmtext}' ")) ? LAN_CREATED :  LAN_CREATED_FAILED ;
}

if (isset($_POST['updateoptions'])) {
	$pref['wm_enclose'] = $_POST['wm_enclose'];
	$pref['wmessage_sc'] = $_POST['wmessage_sc'];
	save_prefs();
	$message = LAN_SETSAVED;
}

$deltest = array_flip($_POST);
if (preg_match("#(.*?)_delete_(\d+)#", $deltest[$tp->toJS(LAN_DELETE)], $matches)) {
	$delete = $matches[1];
	$del_id = $matches[2];
}

if ($delete && $del_id) {
	$message = ($sql->db_Delete("generic", "gen_id='".$del_id."' ")) ? LAN_DELETED : LAN_DELETED_FAILED ;
}

if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

// Show Existing -------
if ($action == "main" || $action == "") {
	if ($wm_total = $sql->db_Select("generic", "gen_id, gen_intdata, gen_chardata", "gen_type='wmessage' ORDER BY gen_id ASC")) {
		$text = $rs->form_open("post", e_SELF, "myform_{$gen_id}", "", "");
		$text .= "<div style='text-align:center'>
			<table class='fborder' style='".ADMIN_WIDTH."'>
			<tr>
			<td class='fcaption' style='width:5%'>ID</td>
			<td class='fcaption' style='width:70%'>".WMLAN_02."</td>
			<td class='fcaption' style='width:20%'>".WMLAN_03."</td>
			<td class='fcaption' style='width:15%'>".LAN_OPTIONS."</td>
			</tr>";
		while ($row = $sql->db_Fetch()) {
//			extract($row);
			$text .= "<tr><td class='forumheader3' style='width:5%; text-align: center; vertical-align: middle'>";
			//   $text .= $wm_id ? "<img src='".e_IMAGE."link_icons/".$link_button."' alt='' /> ":"";
			$text .= $row['gen_id'];
			$text .= "</td><td style='width:70%' class='forumheader3'>".$row['gen_chardata']."</td>";
			$text .= "</td><td style='width:70%' class='forumheader3'>".r_userclass_name($row['gen_intdata'])."</td>";

			$text .= "</td><td style='width:15%; text-align:center; white-space: nowrap' class='forumheader3'>";
			$text .= $rs->form_button("button", "main_edit_{$row['gen_id']}", LAN_EDIT, "onclick=\"document.location='".e_SELF."?create.edit.{$row['gen_id']}'\"");
			$text .= $rs->form_button("submit", "main_delete_".$row['gen_id'], LAN_DELETE, "onclick=\"return jsconfirm('".$tp->toJS(LAN_CONFIRMDEL." [ ID: {$row['gen_id']} ]")."' )\"");
			$text .= "</td>";
			$text .= "</tr>";
		}

		$text .= "</table></div>";
		$text .= $rs->form_close();
	} else {
		$text .= "<div style='text-align:center'>".LCLAN_61."</div>";
	}
	$ns->tablerender(WMLAN_00, $text);
}

// Create and Edit
if ($action == "create" || $action == "edit") {

	if ($sub_action == "edit"){
		$sql->db_Select("generic", "gen_intdata, gen_chardata", "gen_id = $id");
		$row = $sql->db_Fetch();
	}

	if ($sub_action != 'edit'){
		$preset = $pst->read_preset("admin_wmessage");
		extract($preset);
	}

	$text = "
		<div style='text-align:center'>
		<form method='post' action='".e_SELF."'  id='wmform'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>";

	$text .= "

		<td style='width:20%' class='forumheader3'>".WMLAN_04."</td>
		<td style='width:60%' class='forumheader3'>
		<textarea class='tbox' id='wm_text' name='wm_text' cols='70' rows='18' style='width:95%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this)'>".$row['gen_chardata']."</textarea>
		<br />";

	if(!$pref['wysiwyg']){
		$text .="<input id='helpguest' class='helpbox' type='text' name='helpguest' size='100' />
		<br />
		".display_help("helpguest",FALSE);
	}

	$text .= "
		</td>
		</tr>

		<tr><td class='forumheader3'>".WMLAN_03."</td>
		<td class='forumheader3'>".r_userclass("wm_active", $row['gen_intdata'], "off", "public,guest,nobody,member,admin,classes")."</td></tr>";

	$text .= "
		<tr style='vertical-align:top'>

		<td colspan='2' class='forumheader' style='text-align:center'>";

	$text .= ($sub_action == "edit") ? "<input class='button' type='submit' name='wm_update' value='".LAN_UPDATE."' />" :
	 "<input class='button' type='submit' name='wm_insert' value='".LAN_CREATE."' />" ;
	$text .= "<input type='hidden' name='wm_id' value='".$id."' />";
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
		<td class='forumheader3' style='width:30%;text-align:center'>". ($pref['wm_enclose'] ? "<input type='checkbox' name='wm_enclose' value='1' checked='checked' />" : "<input type='checkbox' name='wm_enclose' value='1' />")."
		</td>
		</tr>
		<tr>

		<td style='width:70%' class='forumheader3'>
		".WMLAN_07."
		</td>
		<td class='forumheader3' style='width:30%;text-align:center'>". ($pref['wmessage_sc'] ? "<input type='checkbox' name='wmessage_sc' value='1' checked='checked' />" : "<input type='checkbox' name='wmessage_sc' value='1' />")."
		</td>
		</tr>

		<tr style='vertical-align:top'>
		<td colspan='2'  style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='updateoptions' value='".LAN_SAVE."' />
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
	$var['opt']['text'] = WMLAN_08;
	$var['opt']['link'] = e_SELF."?opt";

	show_admin_menu(LAN_OPTIONS, $action, $var);
}

require_once("footer.php");
?>