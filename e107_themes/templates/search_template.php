<?php

// ##### MAIN TABLE -------------------------------------------------------------------------------
if(!isset($SEARCH_MAIN_TABLE)){
		$SEARCH_MAIN_TABLE = "
		<div style='text-align:center'>
		<form name='searchform' method='post' action='".e_SELF."'>
		<table style='width:95%' class='fborder'>\n
			<tr>
				<td class='forumheader2' style='width:20%'>".LAN_199." </td>
				<td class='forumheader3' style='width:80%'>
					{SEARCH_MAIN_SEARCHFIELD} <span class='smalltext'>".LAN_417."</span>
				</td>
			</tr>
			<tr>
				<td style='width:20%' class='forumheader2'>".LAN_200."</td>
				<td style='width:80%' class='forumheader3'>{SEARCH_MAIN_CHECKBOXES}
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
// ##### ------------------------------------------------------------------------------------------

?>