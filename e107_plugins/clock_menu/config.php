<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - Clock menu
 *
 */


$eplug_admin = TRUE;
require_once('../../class2.php');
if (!getperms('1')) 
{
	e107::redirect('admin');
	 exit ;
}
require_once(e_ADMIN.'auth.php');
e107::includeLan(e_PLUGIN.'clock_menu/languages/admin/'.e_LANGUAGE.'.php');

$frm = e107::getForm();
$mes = e107::getMessage();
$menu_pref = e107::getConfig('menu')->getPref('');
	
if (isset($_POST['update_menu'])) 
{
	$temp = array();
	while (list($key, $value) = each($_POST)) 
	{
		if ($key != 'update_menu') 
		{
			$temp[$key] = $value;
		}
	}
	if ($_POST['clock_format'] != 1) 
	{
		$temp['clock_format'] = 0;
	}
	if ($admin_log->logArrayDiffs($temp,$menu_pref,'MISC_05'))
	{
		$menuPref = e107::getConfig('menu');
		foreach ($temp as $k => $v)
		{
			$menuPref->setPref($k, $v);
		}
		$menuPref->save(false, true, false);
	}
}
	
$ns->tablerender($caption, $mes->render(). $text);

$text = "
	<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
	<table class='table adminform'>
	<colgroup span='2'>
    	<col class='col-label' />
    	<col class='col-control' />
    </colgroup>
	<tr>
		<td>".CLOCK_AD_L2.": </td>
		<td><input class='tbox' type='text' name='clock_caption' size='20' value='".$menu_pref['clock_caption']."' maxlength='100' />	</td>
	</tr>
	
	<tr>
		<td>".CLOCK_AD_L5.": </td>
		<td>".$frm->checkbox('clock_format', 1, varset($menu_pref['clock_format'], 0))."<span class='field-help'>".CLOCK_AD_L6."</span></td>
	</tr>

	<tr>
		<td>".CLOCK_AD_L7.": </td>
		<td><input class='tbox' type='text' name='clock_dateprefix' size='10' value='".$menu_pref['clock_dateprefix']."' maxlength='50' /><span class='field-help'>".CLOCK_AD_L8."</span></td>
	</tr>
	
	<tr>
		<td>".CLOCK_AD_L9.": </td>
		<td><input class='tbox' type='text' name='clock_datesuffix1' size='10' value='".$menu_pref['clock_datesuffix1']."' maxlength='50' /><span class='field-help'>".CLOCK_AD_L13."</span></td>
	</tr>
	
	<tr>
		<td>".CLOCK_AD_L10.": </td>
		<td><input class='tbox' type='text' name='clock_datesuffix2' size='10' value='".$menu_pref['clock_datesuffix2']."' maxlength='50' /><span class='field-help'>".CLOCK_AD_L13."</span></td>
	</tr>
	
	<tr>
		<td>".CLOCK_AD_L11.": </td>
		<td><input class='tbox' type='text' name='clock_datesuffix3' size='10' value='".$menu_pref['clock_datesuffix3']."' maxlength='50' /><span class='field-help'>".CLOCK_AD_L13."</span></td>
	</tr>
	
	<tr>
		<td>".CLOCK_AD_L12.": </td>
		<td><input class='tbox' type='text' name='clock_datesuffix4' size='10' value='".$menu_pref['clock_datesuffix4']."' maxlength='50' /><span class='field-help'>".CLOCK_AD_L13."</span></td>
	</tr>
	</table>
	
	<div class='buttons-bar center'>
		".$frm->admin_button('update_menu', LAN_UPDATE, 'update')."
	</div>
	</form>
	";

$ns->tablerender(CLOCK_AD_L4, $text);
require_once(e_ADMIN."footer.php");
