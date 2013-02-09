<?php 
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL$
 * $Id$
*/

require_once ('../../class2.php');
if(!getperms('1'))
{
	header('location:'.e_BASE.'index.php');
	exit();
}

require_once (e_HANDLER.'userclass_class.php');

include_lan(e_PLUGIN.'newforumposts_main/languages/'.e_LANGUAGE.'.php');
require_once (e_ADMIN.'auth.php');
require_once (e_HANDLER.'message_handler.php');


$frm = e107::getForm();
$mes = e107::getMessage(); 

if(isset($_POST['updatesettings']))
{
	$pref['nfp_display'] = $_POST['nfp_display'];
	$pref['nfp_caption'] = $_POST['nfp_caption'];
	$pref['nfp_amount'] = $_POST['nfp_amount'];
	$pref['nfp_layer'] = vartrue($_POST['nfp_layer']);
	$pref['nfp_posts'] = vartrue($_POST['nfp_posts']);
	$pref['nfp_layer_height'] = ($_POST['nfp_layer_height'] ? $_POST['nfp_layer_height'] : 200);
	save_prefs();
	//$message = "".NFPM_L13."";
}

/*
if(vartrue($message))
{
	$ns->tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}
*/
$ns->tablerender($caption, $mes->render() . $text);

$text = "
	<form method='post' action='".e_SELF."?".e_QUERY."' id='menu_conf_form'>
	<table class='table adminform'>
	 
	<tr>
	<td>".NFPM_L4."</td>
	<td>
	<select class='tbox' name='nfp_display'>".($pref['nfp_display'] == "0" ? "<option value='0' selected='selected'>".NFPM_L5."</option>" : "<option value='0'>".NFPM_L5."</option>").($pref['nfp_display'] == "1" ? "<option value='1' selected='selected'>".NFPM_L6."</option>" : "<option value='1'>".NFPM_L6."</option>").($pref['nfp_display'] == "2" ? "<option value='2' selected='selected'>".NFPM_L7."</option>" : "<option value='2'>".NFPM_L7."</option>")."</select>
	</td>
	</tr>
	 
	<tr>
	<td>".NFPM_L8.": </td>
	<td>
	<input class='tbox' type='text' name='nfp_caption' size='20' value='".$pref['nfp_caption']."' maxlength='100' />
	</td>
	</tr>
	 
	<tr>
	<td>".NFPM_L9.": </td>
	<td>
	<input class='tbox' type='text' name='nfp_amount' size='6' value='".$pref['nfp_amount']."' maxlength='3' />
	</td>
	</tr>
	 
	<tr>
	<td>".NFPM_L14."</td>
	<td>".(vartrue($pref['nfp_posts']) ? "<input type='checkbox' name='nfp_posts' value='1' checked='checked' />" : "<input type='checkbox' name='nfp_posts' value='1' />")."<span class='field-help'>".NFPM_L15."</span>
	</td>
	</tr>
	 
	<tr>
	<td>".NFPM_L10.": </td>
	<td>".(vartrue($pref['nfp_layer']) ? "<input type='checkbox' name='nfp_layer' value='1' checked='checked' />" : "<input type='checkbox' name='nfp_layer' value='1' />")."&nbsp;&nbsp;".NFPM_L11.": <input class='tbox' type='text' name='nfp_layer_height' size='8' value='".vartrue($pref['nfp_layer_height'])."' maxlength='3' />
	</td>
	</tr>
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('updatesettings', LAN_UPDATE, 'update')."
	</div>
	</form>	";
$ns->tablerender(NFPM_L12, $text);

require_once (e_ADMIN."footer.php");
?>
