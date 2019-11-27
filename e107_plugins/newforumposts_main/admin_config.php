<?php 
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/

require_once ('../../class2.php');
if(!getperms('1'))
{
	header('location:'.e_BASE.'index.php');
	exit();
}

require_once (e_HANDLER.'userclass_class.php');

e107::includeLan(e_PLUGIN.'newforumposts_main/languages/'.e_LANGUAGE.'.php');
require_once (e_ADMIN.'auth.php');

e107::getMessage()->addWarning("This plugin is no longer compatible with e107 v2.x and should be uninstalled.<br />Instead, please enable the <b>forum</b> plugin's <b>newforumposts_menu</b> using the Menu Manager.");

echo e107::getMessage()->render();

require_once (e_ADMIN."footer.php");




/*
$frm = e107::getForm();
$mes = e107::getMessage();





if(isset($_POST['updatesettings']))
{
	$pref['nfp_display'] 	= intval($_POST['nfp_display']);
	$pref['nfp_caption']	= $tp->toDB($_POST['nfp_caption']);
	$pref['nfp_amount'] 	= intval($_POST['nfp_amount']);
	$pref['nfp_layer'] 		= intval(vartrue($_POST['nfp_layer']));
	$pref['nfp_posts'] 		= intval(vartrue($_POST['nfp_posts']));
	$pref['nfp_layer_height'] = intval(($_POST['nfp_layer_height'] ? $_POST['nfp_layer_height'] : 200));
	save_prefs();

}

$ns->tablerender($caption, $mes->render() . $text);

$text = "
	<form method='post' action='".e_SELF."?".e_QUERY."' id='menu_conf_form'>
	<table class='table adminform'>	 
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
	<tr>
		<td>".NFPM_L4."</td>
		<td>".$frm->select('nfp_display', array(0 => NFPM_L5, 1 => NFPM_L6, 2 => NFPM_L7), $pref['nfp_display'])."</td>
	</tr>
	<tr>
		<td>".NFPM_L8.":</td>
		<td>".$frm->text('nfp_caption', $pref['nfp_caption'], '100', array('class' => 'tbox input-text span3'))."</td>
	</tr>
	<tr>
		<td>".NFPM_L9.": </td>
		<td>".$frm->text('nfp_amount', $pref['nfp_amount'], '3')."</td>
	</tr>	 
	<tr>
		<td>".NFPM_L14."</td>
		<td>".$frm->radio_switch('nfp_posts', $pref['nfp_posts'], LAN_YES, LAN_NO)."<span class='field-help'>".NFPM_L15."</span></td>
	</tr>	 
	<tr>
		<td>".NFPM_L10."</td>
		<td>".$frm->radio_switch('nfp_layer', $pref['nfp_layer'], LAN_YES, LAN_NO)."<br />
		".NFPM_L11.": ".$frm->text('nfp_layer_height', $pref['nfp_layer_height'], '3')."</td>
	</tr>
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('updatesettings', LAN_UPDATE, 'update')."
	</div>
	</form>";

$ns->tablerender(NFPM_L12, $text);

require_once (e_ADMIN."footer.php");
*/

