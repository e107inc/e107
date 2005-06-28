<?php

if (!isset($SEARCH_TOP_TABLE)) {
	$SEARCH_TOP_TABLE = "<div style='text-align: center'>
	<form id='searchform' name='searchform' method='get' action='".e_SELF."'>
	<table style='width: 95%' class='fborder'><tr>
	<td class='forumheader3' style='width: 40%'>".LAN_199."</td>
	<td class='forumheader3' style='width: 60%; white-space: nowrap'>
	{SEARCH_MAIN_SEARCHFIELD}&nbsp;{SEARCH_MAIN_SUBMIT}&nbsp;{ENHANCED_ICON}
	</td>
	</tr>";
}

if (!isset($SEARCH_ENHANCED)) {
	$SEARCH_ENHANCED = "<tr id='{ENHANCED_DISPLAY_ID}' {ENHANCED_DISPLAY}>
	<td class='forumheader3' style='width: 40%; white-space: nowrap'>{ENHANCED_TEXT}</td>
	<td class='forumheader3' style='width: 60%; white-space: nowrap'>
	{ENHANCED_FIELD}
	</td>
	</tr>";
}

if (!isset($SEARCH_CATS)) {
	$SEARCH_CATS = "<tr>
	<td style='width:30%' class='forumheader3'>".LAN_SEARCH_19."<br />
	{SEARCH_MAIN_CHECKALL} {SEARCH_MAIN_UNCHECKALL}
	</td>
	<td style='width:70%' class='forumheader3'>
	{SEARCH_MAIN_CHECKBOXES}{SEARCH_DROPDOWN}&nbsp;{SEARCH_ADVANCED}
	<br />
	</td>
	</tr>";
}

if (!isset($SEARCH_TYPE)) {
	$SEARCH_TYPE = "<tr id='advanced_type' {SEARCH_TYPE_DISPLAY}>
	<td style='width:30%' class='forumheader3'>".LAN_SEARCH_75.":</td>
	<td style='width:70%' class='forumheader3'>
	{SEARCH_TYPE_SEL}
	<br />
	</td>
	</tr>";
}

if (!isset($SEARCH_ADV)) {
	$SEARCH_ADV = "<tr>
	<td class='forumheader3'>
	{SEARCH_ADV_A}
	</td>
	<td class='forumheader3'>
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

if (!isset($SEARCH_TABLE_MSG)) {
	$SEARCH_TABLE_MSG = "<tr>
	<td class='forumheader3' style='text-align: center' colspan='2'>
	{SEARCH_MESSAGE}
	</td>
	</tr>";
}

if (!isset($SEARCH_BOT_TABLE)) {
	$SEARCH_BOT_TABLE = "<tr style='display: none !important; display: visible'>
	<td style='display: none' colspan='2'></td>
	</tr></table>
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