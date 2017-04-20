<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin - Chatbox
 *
 */

require_once("../../class2.php");

if (!e107::isInstalled('chatbox_menu') || !getperms("P"))
{
	e107::redirect('admin');
	exit;
}

// include_lXXXan( e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/admin_chatbox_menu.php");

e107::lan('chatbox_menu','admin_chatbox_menu');


require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");
$mes = e107::getMessage();
$frm    = e107::getForm();

if (isset($_POST['updatesettings']))
{
	$temp = array();
	$temp['chatbox_posts'] = vartrue($_POST['chatbox_posts'], 5);
	$temp['cb_layer'] = intval($_POST['cb_layer']);
	$temp['cb_layer_height'] = max(varset($_POST['cb_layer_height'], 200), 150);
	$temp['cb_emote'] = intval($_POST['cb_emote']);
	$temp['cb_mod'] = intval($_POST['cb_mod']);
	$temp['cb_user_addon'] = intval($_POST['cb_user_addon']);


	e107::getConfig('core')->setPref($temp)->save(false);
	e107::getCache()->clear("nq_chatbox");

}


if (isset($_POST['prune']))
{
	$chatbox_prune = intval($_POST['chatbox_prune']);
	$prunetime = time() - $chatbox_prune;

	$sql->delete("chatbox", "cb_datestamp < '{$prunetime}' ");
	e107::getLog()->add('CHBLAN_02', $chatbox_prune.', '.$prunetime, E_LOG_INFORMATIVE, '');
	e107::getCache()->clear("nq_chatbox");
	$mes->addSuccess(LAN_AL_CHBLAN_02);
}

if (isset($_POST['recalculate']))
{
	$sql->update("user", "user_chats = 0");
	$qry = "SELECT u.user_id AS uid, count(c.cb_nick) AS count FROM #chatbox AS c
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(c.cb_nick,'.',1) = u.user_id
		WHERE u.user_id > 0
		GROUP BY uid";

	if ($sql->gen($qry))
	{
		$ret = array();
		while($row = $sql -> fetch())
		{
			$list[$row['uid']] = $row['count'];
		}
	}

	foreach($list as $uid => $cnt)
	{
		$sql->update("user", "user_chats = '{$cnt}' WHERE user_id = '{$uid}'");
	}

	e107::getLog()->add('CHBLAN_03','', E_LOG_INFORMATIVE, '');
	$mes->addSuccess(CHBLAN_33);
}


if(!isset($pref['cb_mod']))
{
	$pref['cb_mod'] = e_UC_ADMIN;
}

$text = "
	<form method='post' action='".e_SELF."' id='cbform' >
    <table class='table adminform'>
    	<colgroup>
    		<col class='col-label' />
    		<col class='col-control' />
    	</colgroup>
	<tr>
		<td>".CHBLAN_11.":</td>
		<td>".$frm->select('chatbox_posts', array(5, 10, 15, 20, 25), $pref['chatbox_posts'],'useValues=1')."<span class='field-help'>".CHBLAN_12."</span></td>
	</tr>
	<tr>
		<td>".CHBLAN_32.": </td>
		<td>". r_userclass("cb_mod", $pref['cb_mod'], 'off', "nobody,main,admin, classes")."</td>
	</tr>
	<tr>
		<td>".CHBLAN_36."</td>
		<td>".$frm->radio('cb_layer', array(0 => CHBLAN_37, 1 => str_replace("[x]", $frm->number('cb_layer_height', $pref['cb_layer_height'], 3), CHBLAN_29), 2 => CHBLAN_38), $pref['cb_layer'], array('sep' => '<br />'))."</td>
	</tr>
	";

if($pref['smiley_activate'])
{
	$text .= "<tr>
				  	<td>".CHBLAN_31."?: </td>
					<td>".$frm->radio_switch('cb_emote', $pref['cb_emote'])."</td>
				  </tr>";
}

$text .= "
	<tr>
		<td>".CHBLAN_42."</td>
		<td>".$frm->radio_switch('cb_user_addon', $pref['cb_user_addon'])."</td>
	</tr>
	<tr>
		<td>".LAN_PRUNE.":</td>
		<td class='form-inline'>".CHBLAN_23.$frm->select('chatbox_prune', array(86400 => CHBLAN_24, 604800 => CHBLAN_25, 2592000 => CHBLAN_26, 1 => CHBLAN_27), '', '', true).$frm->admin_button('prune', LAN_PRUNE, 'other')."<span class='field-help'>".CHBLAN_22."</span></td>
	</tr>
	<tr>
		<td>".CHBLAN_34.":</td>
		<td>".$frm->admin_button('recalculate', CHBLAN_35, 'other')."</td>
	</tr>
	</table>

	<div class='buttons-bar center'>
		".$frm->admin_button('updatesettings', LAN_UPDATE, 'update')."
	</div>
	</form>";

$ns->tablerender(CHBLAN_20, $mes->render().$text);

require_once(e_ADMIN."footer.php");

function admin_chatbox_adminmenu()
{
	$mode = varset($_GET['mode'],'main');

	$var['main']['text'] = LAN_PREFS;
	$var['main']['link'] = e_SELF;

	$icon  = e107::getParser()->toIcon(e_PLUGIN."chatbox_menu/images/chatbox_32.png");
	$caption = $icon."<span>".LAN_PLUGIN_CHATBOX_MENU_NAME."</span>";

	e107::getNav()->admin($caption, $mode, $var);
}


?>