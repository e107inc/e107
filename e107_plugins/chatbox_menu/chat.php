<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/chatbox_menu/chat.php,v $
 * $Revision: 1.11 $
 * $Date: 2009-11-17 12:59:03 $
 * $Author: marj_nl_fr $
 */

require_once('../../class2.php');
if (!plugInstalled('chatbox_menu')) 
{
	header("Location: ".e_BASE."index.php");
	exit;
}

include_lan(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/".e_LANGUAGE.".php");
require_once(HEADERF);

$sql->db_Select("menus", "*", "menu_name='chatbox_menu'");
$row = $sql->db_Fetch();

if (!check_class($row['menu_class'])) 
{
	$ns->tablerender(CHATBOX_L23, "<div style='text-align:center'>".CHATBOX_L24."</div>");
	require_once(FOOTERF);
	exit;
}

if(!isset($pref['cb_mod']))
{
	$pref['cb_mod'] = e_UC_ADMIN;
}
define("CB_MOD", check_class($pref['cb_mod']));

if($_POST['moderate'] && CB_MOD)
{
	if(isset($_POST['block']))
	{
		foreach(array_keys($_POST['block']) as $k){ $kk[] = intval($k); }
		$blocklist = implode(",", $kk);
		$sql->db_Select_gen("UPDATE #chatbox SET cb_blocked=1 WHERE cb_id IN ({$blocklist})");
	}

	if(isset($_POST['unblock']))
	{
		foreach(array_keys($_POST['unblock']) as $k){ $kk[] = intval($k); }
		$unblocklist = implode(",", $kk);
		$sql->db_Select_gen("UPDATE #chatbox SET cb_blocked=0 WHERE cb_id IN ({$unblocklist})");
	}

	if(isset($_POST['delete']))
	{
		$deletelist = implode(",", array_keys($_POST['delete']));
		$sql -> db_Select_gen("SELECT c.cb_id, u.user_id FROM #chatbox AS c
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(c.cb_nick,'.',1) = u.user_id
		WHERE c.cb_id IN (".$deletelist.")");
		$rowlist = $sql -> db_getList();
		foreach ($rowlist as $row) {
			$sql -> db_Select_gen("UPDATE #user SET user_chats=user_chats-1 where user_id = ".intval($row['user_id']));
		}
		$sql -> db_Select_gen("DELETE FROM #chatbox WHERE cb_id IN ({$deletelist})");
	}
	$e107cache->clear("nq_chatbox");
	$message = CHATBOX_L18;
}

// when coming from search.php
if (strstr(e_QUERY, "fs")) {
	$cgtm = str_replace(".fs", "", e_QUERY);
	$fs = TRUE;
}
// end search

if (e_QUERY ? $from = e_QUERY : $from = 0);

$chat_total = $sql->db_Count("chatbox");

$qry_where = (CB_MOD ? "1" : "cb_blocked=0");

// when coming from search.php calculate page number
if ($fs) {
	$page_count = 0;
	$row_count = 0;
	$sql->db_Select("chatbox", "*", "{$qry_where} ORDER BY cb_datestamp DESC");
	while ($row = $sql -> db_Fetch()) {
		if ($row['cb_id'] == $cgtm) {
			$from = $page_count;
			break;
		}
		$row_count++;
		if ($row_count == 30) {
			$row_count = 0;
			$page_count += 30;
		}
	}
}
// end search

$sql->db_Select("chatbox", "*", "{$qry_where} ORDER BY cb_datestamp DESC LIMIT ".intval($from).", 30");
$obj2 = new convert;

$chatList = $sql->db_getList();
foreach ($chatList as $row)
{
	$CHAT_TABLE_DATESTAMP = $obj2->convert_date($row['cb_datestamp'], "long");
	$CHAT_TABLE_NICK = preg_replace("/[0-9]+\./", "", $row['cb_nick']);
	$cb_message = $tp->toHTML($row['cb_message'], TRUE,'USER_BODY');
	if($row['cb_blocked'])
	{
		$cb_message .= "<br />".CHATBOX_L25;
	}
	if(CB_MOD)
	{
		$cb_message .= "<br /><input type='checkbox' name='delete[{$row['cb_id']}]' value='1' />".CHATBOX_L10;
		if($row['cb_blocked'])
		{
			$cb_message .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' name='unblock[{$row['cb_id']}]' value='1' />".CHATBOX_L7;
		}
		else
		{
			$cb_message .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' name='block[{$row['cb_id']}]' value='1' />".CHATBOX_L9;
		}
	}

	$CHAT_TABLE_MESSAGE = $cb_message;
	$CHAT_TABLE_FLAG = ($flag ? "forumheader3" : "forumheader4");

	if (!$CHAT_TABLE) {
		if (file_exists(THEME."chat_template.php"))
		{
			require_once(THEME."chat_template.php");
		}
		else
		{
			require_once(e_PLUGIN."chatbox_menu/chat_template.php");
		}
	}
	$textstring .= preg_replace("/\{(.*?)\}/e", '$\1', $CHAT_TABLE);
	$flag = (!$flag ? TRUE : FALSE);
}

$textstart = preg_replace("/\{(.*?)\}/e", '$\1', $CHAT_TABLE_START);
$textend = preg_replace("/\{(.*?)\}/e", '$\1', $CHAT_TABLE_END);
$text = $textstart.$textstring.$textend;
if(CB_MOD)
{
	$text = "<form method='post' action='".e_SELF."'>".$text."<input type='submit' class='button' name='moderate' value='".CHATBOX_L13."' /></form>";
}
if($message)
{
	$ns->tablerender("", $message);
}

$ns->tablerender(CHATBOX_L20, $text);


require_once(e_HANDLER."np_class.php");
$ix = new nextprev("chat.php", $from, 30, $chat_total, CHATBOX_L21);

require_once(FOOTERF);
?>