<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/chat.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);
if(e_QUERY ? $from = e_QUERY : $from = 0);
if(Empty($view)){ $view = 30; }
$chat_total = $sql -> db_Count("chatbox");
$text = "";
$sql -> db_Select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT $from, ".$view, $mode="no_where");
$obj2 = new convert;
$aj = new textparse;
while($row = $sql-> db_Fetch()){
	$datestamp = $obj2->convert_date($row['cb_datestamp'], "long");
	$cb_nick = eregi_replace("[0-9]+\.", "", $row['cb_nick']);
	$cb_message = $aj -> tpa($row['cb_message']);
	$cb_message = stripslashes($cb_message);
	$cb_message = preg_replace("/([^s]{100})/", "$1\n", $cb_message);
	$text .= "\n<div class='spacer'>
<img src='".THEME."images/bullet2.gif' alt='bullet' />
<b>".$cb_nick."</b> on ".$datestamp."<br /><i>".$cb_message."</i>
</div>
<br />\n";
	}
$ns -> tablerender(LAN_11, $text);
require_once(e_BASE."classes/np_class.php");
$ix = new nextprev("chat.php", $from, 30, $chat_total, LAN_12);
require_once(FOOTERF);
?>