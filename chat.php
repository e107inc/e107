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
|     $Source: /cvs_backup/e107_0.7/chat.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-27 19:51:37 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);
	
	
$sql->db_Select("menus", "*", "menu_name='chatbox_menu'");
$row = $sql->db_Fetch();
 extract($row);
if (!check_class($menu_class)) {
	$ns->tablerender(LAN_14, "<div style='text-align:center'>".LAN_15."</div>");
	require_once(FOOTERF);
	exit;
}
	
if (strstr(e_QUERY, "fs")) {
	$cgtm = str_replace(".fs", "", e_QUERY);
	$fs = TRUE;
	unset($tmp);
}
	
if (e_QUERY ? $from = e_QUERY : $from = 0);
if (!$view) {
	$view = 30;
}
	
$chat_total = $sql->db_Count("chatbox");
	
if ($fs) {
	$sql->db_Select("chatbox", "*", "cb_id='$cgtm'");
} else {
	$sql->db_Select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT $from, ".$view, $mode = "no_where");
}
$obj2 = new convert;
$aj = new textparse;
	
while ($row = $sql->db_Fetch()) {
	$CHAT_TABLE_DATESTAMP = $obj2->convert_date($row['cb_datestamp'], "long");
	$CHAT_TABLE_NICK = eregi_replace("[0-9]+\.", "", $row['cb_nick']);
	$cb_message = ($row['cb_blocked'] ? LAN_16 : $aj->tpa($row['cb_message']));
	if (!eregi("<a href|<img|&#", $cb_message)) {
		$cb_message = preg_replace("/([^\s]{100})/", "$1\n", $cb_message);
	}
	$CHAT_TABLE_MESSAGE = $cb_message;
	$CHAT_TABLE_FLAG = ($flag ? "forumheader3" : "forumheader4");
	 
	if (!$CHAT_TABLE) {
		if (file_exists(THEME."chat_template.php")) {
			require_once(THEME."chat_template.php");
		} else {
			require_once(e_BASE.$THEMES_DIRECTORY."templates/chat_template.php");
		}
	}
	$textstring .= preg_replace("/\{(.*?)\}/e", '$\1', $CHAT_TABLE);
	$flag = (!$flag ? TRUE : FALSE);
}
	
$textstart = preg_replace("/\{(.*?)\}/e", '$\1', $CHAT_TABLE_START);
$textend = preg_replace("/\{(.*?)\}/e", '$\1', $CHAT_TABLE_END);
$text = $textstart.$textstring.$textend;
	
$ns->tablerender(LAN_11, $text);
	
if (!$fs) {
	require_once(e_HANDLER."np_class.php");
	$ix = new nextprev("chat.php", $from, 30, $chat_total, LAN_12);
}
	
require_once(FOOTERF);
	
?>