<?php

if (!isset($SEARCH_TOP_TABLE)) {
	$SEARCH_TOP_TABLE = "
	<div style='text-align:center'>
	<form name='searchform' method='get' action='".e_SELF."'>
	<table style='width:95%' class='fborder'>\n
	<tr>
	<td class='forumheader3' style='width:30%'>".LAN_199."<br /><span class='smalltext'>".LAN_417."</span></td>
	<td class='forumheader3' style='width:70%'>
	{SEARCH_MAIN_SEARCHFIELD} {SEARCH_MAIN_SUBMIT}
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
	</div>\n";
}

if (!isset($PRE_CHECKBOXES)) {
	$PRE_CHECKBOXES = "<span style='white-space: nowrap; padding-bottom: 7px; padding-top: 7px'>";	/* string thats printed before each category checkbox */
}

if (!isset($POST_CHECKBOXES)) {
	$POST_CHECKBOXES = "</span>\n";	/* string thats printed after each category checkbox */
}

?>