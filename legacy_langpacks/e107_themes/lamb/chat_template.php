<?php

// ##### CHAT TABLE -----------------------------------------------------------------------------
if(!$CHAT_TABLE_START){
		$CHAT_TABLE_START = "";
}
if(!$CHAT_TABLE){
		$CHAT_TABLE = "<div class='spacer'>\n<div class='indent'>\n<span class='defaulttext'><img src='".THEME."images/bullet2.gif' alt='bullet' /> \n<b>{CHAT_TABLE_NICK}</b></span>\n<span class='smalltext'>".LAN_13." {CHAT_TABLE_DATESTAMP}</span><br />\n<div class='spacer'>{CHAT_TABLE_MESSAGE}</div>\n</div>\n</div>\n";
}
if(!$CHAT_TABLE_END){
		$CHAT_TABLE_END = "";
}
// ##### ------------------------------------------------------------------------------------------


?>