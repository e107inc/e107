<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - Online menu
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/online/config.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

/**
 *	e107 Online users plugin
 *
 *	Handles the display of users who are online
 *
 *	@package	e107_plugins
 *	@subpackage	online
 *	@version 	$Id$;
 *
 */

$eplug_admin = TRUE;
require_once('../../class2.php');
include_lan(e_PLUGIN.'online/languages/'.e_LANGUAGE.'.php');

if (!getperms('1')) 
{
	header('location:'.e_BASE.'index.php');
	exit ;
}
require_once(e_ADMIN.'auth.php');

$menu_pref = e107::getConfig('menu')->getPref('');
if (!isset($menu_pref['online_ls_caption'])) 
{	// Assume that if one isn't set, none are set
	$menu_pref['online_ls_caption'] = 'LAN_LASTSEEN_1';		//caption for the lastseen_menu
	$menu_pref['online_ls_amount'] = 10;					//amount of records to show in the lastseen_menu
	$menu_pref['online_caption'] = 'LAN_ONLINE_10';			//caption for the online_menu
	$menu_pref['online_show_memberlist'] = true;			//toggle whether to show a simple member list of online members (shwoing user1, user2, user3)
	$menu_pref['online_show_memberlist_extended'] = false;	//toggle whether to show the extended member list of online members (showing 'user viewing page')
}


if (isset($_POST['update_menu'])) 
{
	$temp = array();
	while (list($key, $value) = each($_POST)) 
	{
		if ($value != LAN_UPDATE) 
		{
			$temp[$key] = $value;
		}
	}
	if ($admin_log->logArrayDiffs($temp,$menu_pref,'MISC_02'))
	{
		$menuPref = e107::getConfig('menu');
		//e107::getConfig('menu')->setPref('', $menu_pref);
		//e107::getConfig('menu')->save(false, true, false);
		foreach ($temp as $k => $v)
		{
			$menuPref->setPref($k, $v);
		}
		$menuPref->save(false, true, false);
	}
	$ns->tablerender('', "<div style='text-align:center'><b>".LAN_UPDATED.'</b></div>');
}



$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' id='menu_form'>
<fieldset id='core-menu-config-lastseen'>
<legend>".LAN_ONLINE_ADMIN_1."</legend>

<table cellpadding='0' cellspacing='0' class='adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
<tr><td class='fcaption' colspan='2'>".LAN_ONLINE_ADMIN_1."</td></tr>

<tr>
<td >".LAN_ONLINE_ADMIN_2."</td>
<td class='forumheader3'>
<input class='tbox' type='text' name='online_ls_caption' size='30' value='".$tp->toHTML($menu_pref['online_ls_caption'],"","defs")."' maxlength='200' />
</td>
</tr>

<tr>
<td>".LAN_ONLINE_ADMIN_3."</td>
<td>
<input class='tbox' type='text' name='online_ls_amount' size='3' value='".intval($menu_pref['online_ls_amount'])."' maxlength='3' />
</td>
</tr>
</table>
</fieldset>

<fieldset id='core-menu-config-online'>
<legend>".LAN_ONLINE_ADMIN_4."</legend>
<table cellpadding='0' cellspacing='0' class='adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
<tr><td class='fcaption' colspan='2'>".LAN_ONLINE_ADMIN_4."</td></tr>

<tr>
<td>".LAN_ONLINE_ADMIN_5."</td>
<td>
<input class='tbox' type='text' name='online_caption' size='30' value='".$tp->toHTML($menu_pref['online_caption'],"","defs")."' maxlength='200' />
</td>
</tr>

<tr>
<td>".LAN_ONLINE_ADMIN_6."</td>
<td>
<input type='radio' value='1' id='online_show_memberlist1' name='online_show_memberlist' ".(varsettrue($menu_pref['online_show_memberlist']) ? "checked='checked'" : "")." /> ".LAN_ONLINE_ADMIN_ENABLED."
<input type='radio' value='0' id='online_show_memberlist0' name='online_show_memberlist' ".(varsettrue($menu_pref['online_show_memberlist']) ? "" : "checked='checked'")." /> ".LAN_ONLINE_ADMIN_DISABLED."
</td>
</tr>

<tr>
<td>".LAN_ONLINE_ADMIN_7."</td>
<td>
<input type='radio' value='1' id='online_show_memberlist_extended1' name='online_show_memberlist_extended' ".(varsettrue($menu_pref['online_show_memberlist_extended']) ? "checked='checked'" : "")." /> ".LAN_ONLINE_ADMIN_ENABLED."
<input type='radio' value='0' id='online_show_memberlist_extended0' name='online_show_memberlist_extended' ".(varsettrue($menu_pref['online_show_memberlist_extended']) ? "" : "checked='checked'")." /> ".LAN_ONLINE_ADMIN_DISABLED."
</td>
</tr>
</table>

<div class='buttons-bar center'>
	<input class='button' type='submit' name='update_menu' value='".LAN_UPDATE."' />
</div>

</fieldset>
</form>
</div>";

$ns->tablerender(LAN_ONLINE_ADMIN_0, $text);
require_once(e_ADMIN."footer.php");
?>