<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once('../../class2.php');
if (!e107::isInstalled('chatbox_menu')) 
{
	e107::redirect();
	exit;
}

e107::lan('chatbox_menu',e_LANGUAGE);

require_once(HEADERF);
$mes = e107::getMessage();
$sql->select("menus", "*", "menu_name='chatbox_menu'");
$row = $sql->fetch();

if (!check_class(intval($row['menu_class'])))
{
	$mes->addError(CHATBOX_L24); 
	$ns->tablerender(LAN_ERROR, $mes->render());
	require_once(FOOTERF);
	exit;
}

if(!isset($pref['cb_mod']))
{
	$pref['cb_mod'] = e_UC_ADMIN;
}


define("CB_MOD", check_class($pref['cb_mod']));


if(!empty($_POST['moderate']) && CB_MOD)
{
	if(isset($_POST['block']))
	{
		foreach(array_keys($_POST['block']) as $k){ $kk[] = intval($k); }
		$blocklist = implode(",", $kk);
		$sql->gen("UPDATE #chatbox SET cb_blocked=1 WHERE cb_id IN ({$blocklist})");
	}

	if(isset($_POST['unblock']))
	{
		foreach(array_keys($_POST['unblock']) as $k){ $kk[] = intval($k); }
		$unblocklist = implode(",", $kk);
		$sql->gen("UPDATE #chatbox SET cb_blocked=0 WHERE cb_id IN ({$unblocklist})");
	}

	if(isset($_POST['delete']))
	{
		$deletelist = implode(",", array_keys($_POST['delete']));
		$sql -> db_Select_gen("SELECT c.cb_id, u.user_id FROM #chatbox AS c
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(c.cb_nick,'.',1) = u.user_id
		WHERE c.cb_id IN (".$deletelist.")");
		$rowlist = $sql -> db_getList();
		foreach ($rowlist as $row) {
			$sql->gen("UPDATE #user SET user_chats=user_chats-1 where user_id = ".intval($row['user_id']));
		}
		$sql->gen("DELETE FROM #chatbox WHERE cb_id IN ({$deletelist})");
	}
	e107::getCache()->clear("nq_chatbox");
	$mes->addSuccess(CHATBOX_L18);
}

// when coming from search.php

$fs = false;

if (strstr(e_QUERY, "fs")) 
{
	$cgtm = intval(str_replace(".fs", "", e_QUERY));
	$fs = true;
}
// end search

if (e_QUERY ? $from = intval(e_QUERY) : $from = 0);

$chat_total = $sql->count('chatbox');

$qry_where = (CB_MOD ? "1" : "cb_blocked=0");

// when coming from search.php calculate page number
if ($fs) 
{
	$page_count = 0;
	$row_count = 0;
	$sql->select("chatbox", "*", "{$qry_where} ORDER BY cb_datestamp DESC");
	while ($row = $sql->fetch()) 
	{
		if ($row['cb_id'] == $cgtm) 
		{
			$from = $page_count;
			break;
		}
		$row_count++;
		if ($row_count == 30) 
		{
			$row_count = 0;
			$page_count += 30;
		}
	}
}
// end search

$sql->select("chatbox", "*", "{$qry_where} ORDER BY cb_datestamp DESC LIMIT ".intval($from).", 30");
$obj2 = new convert;

$chatList = $sql->db_getList();
$frm = e107::getForm();
$vars = array();
$flag = false;

if (empty($CHAT_TABLE))
{
	if (file_exists(THEME."chat_template.php"))
	{
		require_once(THEME."chat_template.php");
	}
	else
	{
		require_once(e_PLUGIN."chatbox_menu/chat_template.php");
	}
}

$textstring = '';

foreach ($chatList as $row)
{
	$vars['CHAT_TABLE_DATESTAMP'] = $tp->toDate($row['cb_datestamp'], "relative");
	$vars['CHAT_TABLE_NICK'] = preg_replace("/[0-9]+\./", "", $row['cb_nick']);

	$cb_message = $tp->toHTML($row['cb_message'], TRUE,'USER_BODY');

	if($row['cb_blocked'])
	{
		$cb_message .= "<br />".CHATBOX_L25;
	}

	if(CB_MOD)
	{
		$id = $row['cb_id'];
		$cb_message .= "<div class='checkbox'>";

		$cb_message .= $frm->checkbox('delete['.$id.']',1, false, array('inline'=>true,'label'=>LAN_DELETE));

		if($row['cb_blocked'])
		{
			$cb_message .= $frm->checkbox('unblock['.$id.']',1, false, array('inline'=>true, 'label'=> CHATBOX_L7));
		}
		else
		{
			$cb_message .= $frm->checkbox('block['.$id.']',1, false,  array('inline'=>true, 'label'=> CHATBOX_L9));
		}

		$cb_message .= "</div>";
	}

	$vars['CHAT_TABLE_MESSAGE'] = $cb_message;
	$vars['CHAT_TABLE_FLAG'] = ($flag ? "forumheader3" : "forumheader4");


//	$textstring .= preg_replace("/\{(.*?)\}/e", '$\1', $CHAT_TABLE);
	$textstring .= $tp->parseTemplate($CHAT_TABLE, true, $vars);
	$flag = (!$flag ? true : false);
}


//print_a($CHAT_TABLE);


//$textstart = preg_replace("/\{(.*?)\}/e", '$\1', $CHAT_TABLE_START);
//$textend = preg_replace("/\{(.*?)\}/e", '$\1', $CHAT_TABLE_END);

$textstart = $tp->parseTemplate($CHAT_TABLE_START, true, $vars);
$textend = $tp->parseTemplate($CHAT_TABLE_END, true, $vars);
$text = $textstart.$textstring.$textend;

if(CB_MOD)
{
	$text = "<form method='post' action='".e_SELF."'>".$text."<input type='submit' class='btn btn-default btn-secondary button' name='moderate' value='".CHATBOX_L13."' /></form>";
}

$parms = "{$chat_total},30,{$from},".e_SELF.'?[FROM]';
$text .= "<div class='nextprev'>".$tp->parseTemplate("{NEXTPREV={$parms}}").'</div>';


$ns->tablerender(CHATBOX_L20, $mes->render().$text);



require_once(FOOTERF);
?>