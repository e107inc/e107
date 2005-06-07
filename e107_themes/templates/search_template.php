<?php

if (!isset($SEARCH_COMPACT_TABLE_TOP)) {
	$SEARCH_COMPACT_TABLE_TOP = "<div style='text-align:center'>
	<form id='searchform' method='get' action='".e_SELF."'>
	<table style='width:95%' class='fborder'>
	<tr>
	<td class='forumheader3' style='width:30%'>{SEARCH_DROPDOWN}</td>
	<td class='forumheader3' style='width:70%; white-space: nowrap; text-align: right'>
	{SEARCH_MAIN_SEARCHFIELD} {SEARCH_MAIN_SUBMIT}
	</td>
	</tr>";
}

if (!isset($SEARCH_COMPACT_TABLE_MSG)) {
	$SEARCH_COMPACT_TABLE_MSG = "<tr>
	<td class='forumheader3' style='text-align: center' colspan='2'>
	{SEARCH_MESSAGE}
	</td>
	</tr>";
}

if (!isset($SEARCH_COMPACT_TABLE_BOT)) {
	$SEARCH_COMPACT_TABLE_BOT = "</table>
	</form>
	</div>";
}

if (!isset($SEARCH_TOP_TABLE)) {
	$SEARCH_TOP_TABLE = "
	<div style='text-align:center'>
	<form id='searchform' method='get' action='".e_SELF."'>
	<table style='width:95%' class='fborder'>\n
	<tr>
	<td class='forumheader3' style='width:30%'>".LAN_199."</td>
	<td class='forumheader3' style='width:70%; white-space: nowrap'>
	{SEARCH_MAIN_SEARCHFIELD} {SEARCH_MAIN_SUBMIT}<br />{SEARCH_MESSAGE}
	</td>
	</tr>";
}

if (!isset($SEARCH_CAT_TABLE)) {
	$SEARCH_CAT_TABLE = "
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

if (!isset($SEARCH_BOT_TABLE)) {
	$SEARCH_BOT_TABLE = "</table>
	</form>
	</div>";
}

if (!isset($PRE_CHECKBOXES)) {
	$PRE_CHECKBOXES = "<span style='white-space: nowrap; padding-bottom: 7px; padding-top: 7px'>";	/* string thats printed before each category checkbox */
}

if (!isset($POST_CHECKBOXES)) {
	$POST_CHECKBOXES = "</span>\n";	/* string thats printed after each category checkbox */
}

?>