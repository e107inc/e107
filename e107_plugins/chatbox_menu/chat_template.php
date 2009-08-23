<?php

if (!defined('e107_INIT')) { exit; }

// ##### CHAT TABLE -----------------------------------------------------------------------------
if(!$CHAT_TABLE_START){
		$CHAT_TABLE_START = "
		<br /><table style='width:100%'><tr><td>";
}
if(!$CHAT_TABLE)
{
//TODO review bullet
		$CHAT_TABLE = "
		<div class='spacer'>
			<div class='{CHAT_TABLE_FLAG}'>
				<img src='".THEME."images/bullet2.gif' alt='bullet' />
				<b>{CHAT_TABLE_NICK}</b> ".CHATBOX_L22." {CHAT_TABLE_DATESTAMP}
				<br />
				<div class='defaulttext'><i>{CHAT_TABLE_MESSAGE}</i></div>
			</div>
		</div>";

}
if(!$CHAT_TABLE_END){
		$CHAT_TABLE_END = "
		</td></tr></table>";
}
// ##### ------------------------------------------------------------------------------------------


?>