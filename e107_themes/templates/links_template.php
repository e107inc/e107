<?php

// ##### MAIN TABLE -------------------------------------------------------------------------------
if(!$LINK_MAIN_TABLE_START){
		$LINK_MAIN_TABLE_START = "
		<div style='text-align:center'>
		<table class='fborder' style='width:95%'>\n";
}
if(!$LINK_MAIN_TABLE){   
		$LINK_MAIN_TABLE .= "
		<tr>
		<td class='forumheader3' style='background-color:transparent; border:#517DB1 1px solid; width:10%; text-align:center' rowspan='2'>
			{LINK_MAIN_ICON}
		</td>
		<td class='forumheader' style='width:90%'>
			{LINK_MAIN_HEADING}
		</td>
		</tr>
		<tr>
		<td class='forumheader3'>
			{LINK_MAIN_DESC}
			<span class='smalltext'>(
				{LINK_MAIN_NUMBER}
			)</span>
		</td>
		</tr>\n";

}
if(!$LINK_MAIN_TABLE_END){
		$LINK_MAIN_TABLE_END = "
		<tr><td class='forumheader3' colspan='2' style='text-align:right;'>{LINK_MAIN_TOTAL}</td></tr>
		<tr><td class='forumheader3' colspan='2' style='text-align:right;'>{LINK_MAIN_SHOWALL}</td></tr>
		</table>
		</div>\n";
}
// ##### ------------------------------------------------------------------------------------------


// ##### CATEGORY LIST ----------------------------------------------------------------------------
if(!$LINK_CAT_TABLE_START){
		$LINK_CAT_TABLE_START = "
		<div style='text-align:center'>
		<table class='fborder' cellspacing='0' cellpadding='0' style='width:95%'><tr><td>\n";
}
if(!$LINK_CAT_TABLE){   
		$LINK_CAT_TABLE .= "
		<table class='fborder' style='width:100%'>
		<tr>
		<td rowspan='2' class='forumheader3' style='background-color:transparent; border:#517DB1 1px solid; width:10%; text-align:center'>
			{LINK_CAT_BUTTON}
		</td>
		<td class='forumheader' style='width:90%'>
			{LINK_CAT_APPEND}{LINK_CAT_NAME}</a> <i>{LINK_CAT_URL}</i>
		</td>
		<td class='forumheader' style='white-space:nowrap;'>
			{LINK_CAT_REFER}
		</td>
		</tr>
		<tr>
		<td colspan='2' class='forumheader3' style='line-height:130%;'>
			{LINK_CAT_DESC}<br />
		</td>
		</tr>
		</table>\n";

}
if(!$LINK_CAT_TABLE_END){
		$LINK_CAT_TABLE_END = "
		</td>
		</tr>
		<tr>
			<td class='forumheader3' colspan='2' style='text-align:right;'>
				{LINK_CAT_SUBMIT}
			</td>
		</tr>		
		</table>
		</div>\n";
}
// ##### ------------------------------------------------------------------------------------------


?>