<?php

if (!defined('e107_INIT')) { exit; }

// ##### CHAT TABLE -----------------------------------------------------------------------------


if(empty($CHAT_TABLE_START))
{
		$CHAT_TABLE_START = "
		<br /><table class='table table-striped' style='width:100%'>";
}

if(empty($CHAT_TABLE))
{
		//TODO review bullet
		$CHAT_TABLE = "<tr><td>
		<div class='spacer'>
			<div class='{CHAT_TABLE_FLAG}'>
				<b>{CHAT_TABLE_NICK}</b> ".CHATBOX_L22." {CHAT_TABLE_DATESTAMP}
				<br />
				<div class='defaulttext'><i>{CHAT_TABLE_MESSAGE}</i></div>
			</div>
		</div></td></tr>";

}



if(empty($CHAT_TABLE_END))
{
		$CHAT_TABLE_END = "
		</table>";
}


