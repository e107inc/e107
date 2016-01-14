<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - Comment menu
 *
 */

$eplug_admin = TRUE;
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
	
include_lan(e_PLUGIN."comment_menu/languages/".e_LANGUAGE.".php");
if (!getperms("1")) 
{
	e107::redirect('admin');
	exit() ;
}
require_once(e_ADMIN."auth.php");
$frm = e107::getForm();
$mes = e107::getMessage();
$menu_config = e107::getConfig('menu'); 

if (isset($_POST['update_menu'])) 
{
	$temp = $old = $menu_config->getPref();
	
	$tp = e107::getParser();
	while (list($key, $value) = each($_POST)) 
	{
		if ($value != LAN_UPDATE) 
		{
			$temp[$tp->toDB($key)] = $tp->toDB($value);
		}
	}
	if (!$_POST['comment_title']) 
	{
		$temp['comment_title'] = 0;
	}
	
	$menu_config->setPref($temp);
	if ($admin_log->logArrayDiffs($old, $menu_config->getPref(), 'MISC_04'))
	{
		if($menu_config->save(false))
		{
			$mes->addSuccess();
		}
	}
	else
	{
		$mes->addInfo(LAN_NO_CHANGE);
	}
}

$text = "
	<form method='post' action='".e_SELF."?".e_QUERY."' id='plugin-menu-config-form'>
	<table class='table adminform'>
	<colgroup span='2'>
    	<col class='col-label' />
    	<col class='col-control' />
    </colgroup>
	<tr>
		<td>".CM_L3.":</td>
		<td><input class='tbox' type='text' name='comment_caption' size='20' value='".$menu_config->get('comment_caption')."' maxlength='100' /></td>
	</tr>
	 
	<tr>
		<td>".CM_L4.":</td>
		<td><input class='tbox' type='text' name='comment_display' size='20' value='".$menu_config->get('comment_display')."' maxlength='2' /></td>
	</tr>
	 
	<tr>
		<td>".CM_L5.":</td>
		<td><input class='tbox' type='text' name='comment_characters' size='20' value='".$menu_config->get('comment_characters')."' maxlength='4' /></td>
	</tr>
	 
	<tr>
		<td>".CM_L6.":</td>
		<td><input class='tbox' type='text' name='comment_postfix' size='30' value='".$menu_config->get('comment_postfix')."' maxlength='200' /></td>
	</tr>
	 
	<tr>
		<td>".CM_L7.":</td>
		<td><input type='checkbox' name='comment_title' value='1'".($menu_config->get('comment_title') ? ' checked="checked"' : '')." /></td>
	</tr>
	</table>

	<div class='buttons-bar center'>
		".$frm->admin_button('update_menu', LAN_UPDATE, 'update')."
	</div>	
	</form>";
	
$ns->tablerender(CM_L8, $mes->render() . $text);

require_once(e_ADMIN."footer.php");
?>