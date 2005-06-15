<?php

if (!isset($SEARCH_TOP_TABLE)) {
	$SEARCH_TOP_TABLE = "<div style='text-align: center'>
	<form id='searchform' method='get' action='".e_SELF."'>
	<table style='width: 95%' class='fborder'>";
}

if (!isset($SEARCH_STANDARD)) {
	$SEARCH_STANDARD = "<tr>
	<td class='forumheader3' style='width: 30%; white-space: nowrap'>{SEARCH_DROPDOWN}&nbsp;{SEARCH_ADVANCED}</td>
	<td class='forumheader3' style='width: 70%; white-space: nowrap; text-align: right'>
	{SEARCH_MAIN_SEARCHFIELD}&nbsp;{SEARCH_MAIN_SUBMIT}{ENHANCED_ICON}
	</td>
	</tr>";
}

if (!isset($SEARCH_NON_STANDARD)) {
	$SEARCH_NON_STANDARD = "<tr>
	<td class='forumheader3' style='width: 30%; border-top: 0px'>".LAN_199."</td>
	<td class='forumheader3' style='width: 70%; white-space: nowrap; border-top: 0px'>
	{SEARCH_MAIN_SEARCHFIELD}&nbsp;{SEARCH_MAIN_SUBMIT}{ENHANCED_ICON}
	</td>
	</tr>";
}

if (!isset($SEARCH_NON_STANDARD_CATS)) {
	$SEARCH_NON_STANDARD_CATS = "
	<tr>
	<td style='width:30%' class='forumheader3'>".LAN_SEARCH_19."<br />
	{SEARCH_MAIN_CHECKALL}
	{SEARCH_MAIN_UNCHECKALL}
	</td>
	<td style='width:70%' class='forumheader3'>
	{SEARCH_MAIN_CHECKBOXES}
	<br />
	</td>
	</tr>";
}

if (!isset($SEARCH_ENHANCED)) {
	$SEARCH_ENHANCED = "<tr id='{ENHANCED_DISPLAY_ID}' {ENHANCED_DISPLAY}>
	<td class='forumheader3' style='width: 30%; white-space: nowrap'>{ENHANCED_TEXT}</td>
	<td class='forumheader3' style='width: 70%; white-space: nowrap; text-align: right'>
	{ENHANCED_FIELD}
	</td>
	</tr>";
}

if (!isset($SEARCH_TABLE_MSG)) {
	$SEARCH_TABLE_MSG = "<tr>
	<td class='forumheader3' style='text-align: center' colspan='2'>
	{SEARCH_MESSAGE}
	</td>
	</tr>";
}

if (!isset($SEARCH_ADV_STANDARD)) {
	$SEARCH_ADV_STANDARD = "<tr>
	<td class='forumheader3'>
	{SEARCH_ADV_A}
	</td>
	<td class='forumheader3' style='text-align: right'>
	{SEARCH_ADV_B}
	</td>
	</tr>";
}

if (!isset($SEARCH_ADV_COMBO)) {
	$SEARCH_ADV_COMBO = "<tr>
	<td class='forumheader3' colspan='2'>
	{SEARCH_ADV_TEXT}
	</td>
	</tr>";
}

if (!isset($SEARCH_BOT_TABLE)) {
	$SEARCH_BOT_TABLE = "<tr><td colspan='2'></td></tr></table>
	</form>
	</div>";
}

if (!isset($PRE_CHECKBOXES)) {
	$PRE_CHECKBOXES = "<span style='white-space: nowrap; padding-bottom: 7px; padding-top: 7px'>";	/* string thats printed before each category checkbox */
}

if (!isset($POST_CHECKBOXES)) {
	$POST_CHECKBOXES = "</span>";	/* string thats printed after each category checkbox */
}

?>