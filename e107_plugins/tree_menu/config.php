<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - Tree menu
 *
 * $URL$
 * $Id$
 *
*/

$eplug_admin = TRUE;
require_once('../../class2.php');
include_lan(e_PLUGIN.'tree_menu/languages/'.e_LANGUAGE.'.php');

if (!getperms('4')) 
{
	header('location:'.e_BASE.'index.php');
	exit ;
}
require_once(e_ADMIN.'auth.php');
$mes = e107::getMessage();
$frm = e107::getForm();

$menu_pref = e107::getConfig('menu')->getPref('');

if (isset($_POST['update_menu'])) 
{
	$temp = array();
	foreach($_POST as $key => $value) 
	{
		if ($value != TREE_L2) 
		{
			$temp[$key] = $value;
		}
	}

	if ($admin_log->logArrayDiffs($temp,$menu_pref,'MISC_01'))
	{
		$menuPref = e107::getConfig('menu');
		foreach ($temp as $k => $v)
		{
			$menuPref->setPref($k, $v);
		}
		$menuPref->save(false, true, false);
	}
	$ns->tablerender($caption, $mes->render() . $text);
}
	
$text = "
<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
<table class='table adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
<tr>
	<td>".TREE_L6."</td>
	<td><input class='tbox' type='text' name='tm_class1' size='40' value='".$menu_pref['tm_class1']."' maxlength='20' /></td>
</tr>
<tr>
	<td>".TREE_L7."</td>
	<td><input class='tbox' type='text' name='tm_class2' size='40' value='".$menu_pref['tm_class2']."' maxlength='20' /></td>
</tr>
<tr>
	<td>".TREE_L8."</td>
	<td><input class='tbox' type='text' name='tm_class3' size='40' value='".$menu_pref['tm_class3']."' maxlength='20' /></td>
</tr>
<tr>
	<td>".TREE_L9."</td>
	<td>".$frm->radio_switch('tm_spacer', $menu_pref['tm_spacer'], LAN_YES, LAN_NO)."</td>
</tr>
</table>
<div class='buttons-bar center'>
	".$frm->admin_button('update_menu', LAN_UPDATE, 'update')."
</div>
</form>";

$ns->tablerender(TREE_L1, $text);
	
require_once(e_ADMIN."footer.php");

?>