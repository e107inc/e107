<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/chatbox_menu/chat.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-07-11 14:39:27 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (file_exists(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/".e_LANGUAGE.".php")) {
	include_once(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/".e_LANGUAGE.".php");
} else {
	include_once(e_PLUGIN."chatbox_menu/languages/English/English.php");
}
require_once(HEADERF);

$sql->db_Select("menus", "*", "menu_name='chatbox_menu'");
$row = $sql->db_Fetch();

if (!check_class($row['menu_class'])) {
	$ns->tablerender(LAN_14, "<div style='text-align:center'>".LAN_15."</div>");
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
		$blocklist = implode(",", array_keys($_POST['block']));
		$sql->db_Select_gen("UPDATE #chatbox SET cb_blocked=1 WHERE cb_id IN ({$blocklist})", true);
	}

	if(isset($_POST['unblock']))
	{
		$unblocklist = implode(",", array_keys($_POST['unblock']));
		$sql->db_Select_gen("UPDATE #chatbox SET cb_blocked=0 WHERE cb_id IN ({$unblocklist})", true);
	}
	
	if(isset($_POST['delete']))
	{
		$deletelist = implode(",", array_keys($_POST['delete']));
		$sql->db_Select_gen("DELETE FROM #chatbox WHERE cb_id IN ({$deletelist})", true);
	}
	$e107cache->clear("chatbox");
	$message = CHATBOX_L18;
}

if (e_QUERY ? $from = e_QUERY : $from = 0);

$chat_total = $sql->db_Count("chatbox");

$qry_where = (CB_MOD ? "1" : "cb_blocked=0");

$sql->db_Select("chatbox", "*", "{$qry_where} ORDER BY cb_datestamp DESC LIMIT $from, 30");
$obj2 = new convert;

$chatList = $sql->db_getList();
foreach ($chatList as $row)
{
	$CHAT_TABLE_DATESTAMP = $obj2->convert_date($row['cb_datestamp'], "long");
	$CHAT_TABLE_NICK = eregi_replace("[0-9]+\.", "", $row['cb_nick']);
	$cb_message = $tp->toHTML($row['cb_message']);
	if($row['cb_blocked'])
	{
		$cb_message .= "<br />".LAN_16;
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
			require_once(e_BASE.$THEMES_DIRECTORY."templates/chat_template.php");
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
	
$ns->tablerender(LAN_11, $text);


require_once(e_HANDLER."np_class.php");
$ix = new nextprev("chat.php", $from, 30, $chat_total, LAN_12);

require_once(FOOTERF);
?>