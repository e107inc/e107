<?php

if (!isset($SEARCH_MAIN_TABLE)) {
	$SEARCH_MAIN_TABLE = "
	<div style='text-align:center'>
	<form name='searchform' method='get' action='".e_SELF."'>
	<table style='width:95%' class='fborder'>\n
	<tr>
	<td class='forumheader3' style='width:30%'>".LAN_199."<br /><span class='smalltext'>".LAN_417."</span></td>
	<td class='forumheader3' style='width:70%'>
	{SEARCH_MAIN_SEARCHFIELD} 
	</td>
	</tr>
	<tr>
	<td style='width:30%' class='forumheader3'>".LAN_200."</td>
	<td style='width:70%' class='forumheader3'>{SEARCH_MAIN_CHECKBOXES}
	<br /><br /> 
	{SEARCH_MAIN_CHECKALL}
	{SEARCH_MAIN_UNCHECKALL}
	<br />
	</td>
	</tr>
	<tr>
	<td colspan='2' class='forumheader' style='text-align:center'>
	{SEARCH_MAIN_SUBMIT}
	</td>
	</tr>
	</table>
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