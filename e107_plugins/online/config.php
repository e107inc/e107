<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - Online menu
 *
*/

$eplug_admin = TRUE;
require_once('../../class2.php');
e107::includeLan(e_PLUGIN.'online/languages/'.e_LANGUAGE.'.php');

if (!getperms('1')) 
{
	e107::redirect('admin');
	exit ;
}
require_once(e_ADMIN.'auth.php');

$mes = e107::getMessage();
$frm = e107::getForm();

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
		$mes->addSuccess(LAN_SAVED);
	}
	//$ns->tablerender('', "<div style='text-align:center'><b>".LAN_UPDATED.'</b></div>');
	$ns->tablerender($caption, $mes->render() . $text);

	echo $mes->render();
}

$menu_pref = e107::getConfig('menu')->getPref('');

if (!isset($menu_pref['online_ls_caption'])) 
{	// Assume that if one isn't set, none are set
	$menu_pref['online_ls_caption'] = 'LAN_LASTSEEN_1';		//caption for the lastseen_menu
	$menu_pref['online_ls_amount'] = 10;					//amount of records to show in the lastseen_menu
	$menu_pref['online_caption'] = 'LAN_ONLINE_4';			//caption for the online_menu
	$menu_pref['online_show_memberlist'] = true;			//toggle whether to show a simple member list of online members (shwoing user1, user2, user3)
	$menu_pref['online_show_memberlist_extended'] = false;	//toggle whether to show the extended member list of online members (showing 'user viewing page')
	$menu_pref['online_show_guests'] = true;
}




$text = "
<form method='post' action='".e_REQUEST_URI."' id='menu_form'>
<fieldset id='core-menu-config-lastseen'>
<legend>".LAN_ONLINE_ADMIN_1."</legend>
<table class='table adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
<tr>
	<td colspan='2'>".LAN_ONLINE_ADMIN_1."</td></tr>
<tr>
	<td>".LAN_ONLINE_ADMIN_2.":</td>
	<td><input class='tbox' type='text' name='online_ls_caption' size='30' value='".$tp->toHTML($menu_pref['online_ls_caption'],"","defs")."' maxlength='200' /></td>
</tr>
<tr>
	<td>".LAN_ONLINE_ADMIN_3.":</td>
	<td><input class='tbox' type='text' name='online_ls_amount' size='3' value='".intval($menu_pref['online_ls_amount'])."' maxlength='3' /></td>
</tr>
</table>
</fieldset>


<fieldset id='core-menu-config-online'>
<legend>".LAN_ONLINE_ADMIN_4."</legend>
<table class='table adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
<tr>
	<td colspan='2'>".LAN_ONLINE_ADMIN_4."</td>
</tr>
<tr>
	<td>".LAN_ONLINE_ADMIN_5.":</td>
	<td><input class='tbox' type='text' name='online_caption' size='30' value='".$tp->toHTML($menu_pref['online_caption'],"","defs")."' maxlength='200' /></td>
</tr>
<tr>
	<td>".LAN_ONLINE_ADMIN_10."</td>
	<td>".$frm->radio_switch('online_show_guests', $menu_pref['online_show_guests'])."</td>
</tr>
<tr>
	<td>".LAN_ONLINE_ADMIN_6."</td>
	<td>".$frm->radio_switch('online_show_memberlist', $menu_pref['online_show_memberlist'])."<span class='field-help'>".LAN_ONLINE_ADMIN_8."</span></td>
</tr>
<tr>
	<td>".LAN_ONLINE_ADMIN_7."</td>
	<td>".$frm->radio_switch('online_show_memberlist_extended', $menu_pref['online_show_memberlist_extended'])."<span class='field-help'>".LAN_ONLINE_ADMIN_9."</span></td>
</tr>
</table>

<div class='buttons-bar center'>
	".$frm->admin_button('update_menu', LAN_UPDATE, 'update')."
</div>
</fieldset>
</form>";

$ns->tablerender(LAN_ONLINE_ADMIN_4." - ".LAN_ONLINE_ADMIN_1, $mes->render() . $text);
require_once(e_ADMIN."footer.php");
