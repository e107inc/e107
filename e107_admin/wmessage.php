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
|     $Source: /cvs_backup/e107_0.8/e107_admin/wmessage.php,v $
|     $Revision: 1.6 $
|     $Date: 2009-08-28 16:11:00 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("M")) 
{
	header("location:".e_BASE."index.php");
	 exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'wmessage';

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
$frm = new e_form;

$action == '';
if (e_QUERY) 
{
	$tmp = explode('.', e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
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
	$wmId = intval($_POST['wm_id']);
	welcome_adminlog('02', $wmId, $wm_title);
	$message = ($sql->db_Update("generic", "gen_chardata ='{$data}',gen_ip ='{$wm_title}', gen_intdata='".$_POST['wm_active']."' WHERE gen_id=".$wmId." ")) ? LAN_UPDATED : LAN_UPDATED_FAILED;
}

if (isset($_POST['wm_insert'])) 
{
	$wmtext = $tp->toDB($_POST['data']);
	$wmtitle = $tp->toDB($_POST['wm_caption']);
	welcome_adminlog('01', 0, $wmtitle);
	$message = ($sql->db_Insert("generic", "0, 'wmessage', '".time()."', ".USERID.", '{$wmtitle}', '{$_POST['wm_active']}', '{$wmtext}' ")) ? LAN_CREATED :  LAN_CREATED_FAILED ;
}

if (isset($_POST['updateoptions'])) 
{
	$changed = FALSE;
	foreach (array('wm_enclose','wmessage_sc') as $opt)
	{
		$temp = intval($_POST[$opt]);
		if ($temp != $pref[$opt])
		{
			$pref[$opt] = $temp;
			$changed = TRUE;
		}
	}
	if ($changed)
	{
		save_prefs();
		welcome_adminlog('04', 0, $pref['wm_enclose'].', '.$pref['wmessage_sc']);
		$message = LAN_SETSAVED;
	}
}

if (isset($_POST['main_delete'])) 
{
	$del_id = array_keys($_POST['main_delete']);
	welcome_adminlog('03', $wmId, '');
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
		$text = $rs->form_open("post", e_SELF, "myform_{$gen_id}", "", "");
		$text .= "<div style='text-align:center'>
            <table cellpadding='0' cellspacing='0' class='adminlist'>
			<colgroup span='4'>
				<col style='width:5%' />
				<col style='width:60%' />
				<col style='width:20%' />
				<col style='width:10%' />
   			</colgroup>
			<thead>
			<tr>
				<th>ID</th>
				<th>".WMLAN_02."</th>
				<th class='center'>".WMLAN_03."</th>
				<th class='center'>".LAN_OPTIONS."</th>
			</tr>
			</thead>
			<tbody>";

		foreach($wmList as $row) 
		{
			$text .= "
			<tr>
				<td class='center' style='text-align: center; vertical-align: middle'>".$row['gen_id']."</td>
				<td>".strip_tags($tp->toHTML($row['gen_ip']))."</td>
				<td>".r_userclass_name($row['gen_intdata'])."</td>
            	<td class='center nowrap'>
					<a href='".e_SELF."?create.edit.{$row['gen_id']}'>".ADMIN_EDIT_ICON."</a>
					<input type='image' title='".LAN_DELETE."' name='main_delete[".$row['gen_id']."]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".LAN_CONFIRMDEL." [ID: {$row['gen_id']} ]')\"/>
				</td>
			</tr>";
		}

		$text .= "</tbody></table></div>";
		$text .= $rs->form_close();
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
		extract($preset);
	}

	$text = "
		<div style='text-align:center'>
		<form method='post' action='".e_SELF."'  id='wmform'>
		<fieldset id='code-wmessage-create'>
        <table cellpadding='0' cellspacing='0' class='adminform'>
		<colgroup span='2'>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		";

	$text .= "
		<tr>
		<td>".WMLAN_10."</td>
		<td>
		<input type='text' class='tbox' id='wm_caption' name='wm_caption' maxlength='80' style='width:95%' value=\"".$tp->toForm($row['gen_ip'])."\" />
		</td>
		</tr>";

	$text .= "<tr>
		<td>".WMLAN_04."</td>
		<td>
		<textarea class='e-wysiwyg tbox' id='data' name='data' cols='70' rows='15' style='width:95%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this)'>".$tp->toForm($row['gen_chardata'])."</textarea>
		<br />";

		$text .= display_help("helpb", "admin");


	$text .= "
		</td>
		</tr>
		<tr><td>".WMLAN_03."</td>
		<td>".r_userclass("wm_active", $row['gen_intdata'], "off", "public,guest,nobody,member,admin,classes")."</td></tr>
		</table>";

	$text .= "
		<div class='buttons-bar center'>";

	if($sub_action == "edit")
	{
    	$text .= $frm->admin_button('wm_update', LAN_UPDATE, 'update');
	}
	else
	{
    	$text .= $frm->admin_button('wm_insert', LAN_CREATE);
	}

	$text .= "<input type='hidden' name='wm_id' value='".$id."' />";
	$text .= "</div>
		</fieldset>
		</form>
		</div>";
	$ns->tablerender(WMLAN_01, $text);
}


if ($action == "opt") {
	global $pref, $ns;
	$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<fieldset id='code-wmessage-options'>
        <table cellpadding='0' cellspacing='0' class='adminform'>
		<colgroup span='2'>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tr>

		<td>
		".WMLAN_05."<br />
		<span class='smalltext'>".WMLAN_06."</span>
		</td>
		<td>". ($pref['wm_enclose'] ? "<input type='checkbox' name='wm_enclose' value='1' checked='checked' />" : "<input type='checkbox' name='wm_enclose' value='1' />")."
		</td>
		</tr>
		<tr>

		<td>
		".WMLAN_07."
		</td>
		<td>". ($pref['wmessage_sc'] ? "<input type='checkbox' name='wmessage_sc' value='1' checked='checked' />" : "<input type='checkbox' name='wmessage_sc' value='1' />")."
		</td>
		</tr>
		</table>

		<div class='buttons-bar center'>";

		$text .= $frm->admin_button('updateoptions', LAN_SAVE);
        $text .= "
		</div>
		</fieldset>
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



// Log event to admin log
function welcome_adminlog($msg_num='00', $id=0, $woffle='')
{
  global $pref, $admin_log;
//  if (!varset($pref['admin_log_log']['admin_welcome'],0)) return;
	$msg = '';
	if ($id) $msg = 'ID: '.$id;
	if ($woffle)
	{
		if ($msg) $msg .= '[!br!]';
		$msg .= $woffle;
	}
	$admin_log->log_event('WELCOME_'.$msg_num,$msg,E_LOG_INFORMATIVE,'');
}


?>