<?php
require_once("class2.php");
require_once(HEADERF);

if($_SERVER['QUERY_STRING']){ $from = $_SERVER['QUERY_STRING']; }else{ $from = 0; }
if(Empty($view)){ $view = 30; }

$chat_total = $sql -> db_Count("chatbox");
$text = "";
$sql -> db_Select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT $from, ".$view, $mode="no_where");
$obj2 = new convert;
while($row = $sql-> db_Fetch($mySQLresult)){
	$datestamp = $obj2->convert_date($row['cb_datestamp'], "long");
	$cb_nick = eregi_replace("[0-9]+\.", "", $row['cb_nick']);
	$text .= "\n<div class=\"spacer\">
<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" />
<b>".$cb_nick."</b> on ".$datestamp."<br /><i>".$row['cb_message']."</i>
</div>
<br />\n";
	}

$ns -> tablerender(LAN_11, $text);

$ix = new nextprev("chat.php", $from, 30, $chat_total, LAN_12);


require_once(FOOTERF);
?>