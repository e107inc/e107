<?php

// ##### CHAT TABLE -----------------------------------------------------------------------------
if(!$CHAT_TABLE_START){
		$CHAT_TABLE_START = "
		<br /><table style='width:100%'><tr><td>";
}
if(!$CHAT_TABLE){
		$CHAT_TABLE = "\n
		<div class='spacer'>
			<div class='{CHAT_TABLE_FLAG}'>
				<img src='".THEME."images/bullet2.gif' alt='bullet' /> \n<b>{CHAT_TABLE_NICK}</b> ".LAN_13." {CHAT_TABLE_DATESTAMP}<br />
				<div class='defaulttext'><i>{CHAT_TABLE_MESSAGE}</i></div>\n
			</div>
		</div>\n";

}
if(!$CHAT_TABLE_END){
		$CHAT_TABLE_END = "
		</td></tr></table>";
}
// ##### ------------------------------------------------------------------------------------------


?>