<?php

// ##### CATEGORY TABLE ---------------------------------------------------------------------------
if(!$CONTENT_CATEGORY_TABLE_START){
		$CONTENT_CATEGORY_TABLE_START = "
		<div style='text-align:center'>
		<table class='fborder' style='width:95%'>\n";
}
if(!$CONTENT_CATEGORY_TABLE){   
		$CONTENT_CATEGORY_TABLE .= "
		<tr>
		<td class='forumheader3' style='background-color:transparent; border:#517DB1 1px solid; width:10%; text-align:center' rowspan='2'>
			{CONTENT_CATEGORY_ICON}
		</td>
		<td class='forumheader' style='width:90%'>
			{CONTENT_CATEGORY_HEADING}
		</td>
		</tr>
		<tr>
		<td class='forumheader3'>
			{CONTENT_CATEGORY_SUBHEADING}
			<span class='smalltext'>(
				{CONTENT_CATEGORY_NUMBER}
			)</span>
		</td>
		</tr>\n";
}
if(!$CONTENT_CATEGORY_TABLE_END){
		$CONTENT_CATEGORY_TABLE_END = "
		</table>
		</div>\n";
}
// ##### ------------------------------------------------------------------------------------------


// ##### RECENT LIST ------------------------------------------------------------------------------
if(!$CONTENT_RECENT_TABLE_START){
		$CONTENT_RECENT_TABLE_START = "
		<div style='text-align:center'>
		<table class='fborder' style='width:95%'>\n";
}
if(!$CONTENT_RECENT_TABLE){   
		$CONTENT_RECENT_TABLE .= "
		<tr>
		<td rowspan='2' class='forumheader3' style='background-color:transparent; border:#517DB1 1px solid; width:10%; text-align:center'>
			{CONTENT_RECENT_ICON}
		</td>
		<td class='forumheader' style='width:90%'>
			{CONTENT_RECENT_HEADING}
		</td>
		</tr>
		<tr>
		<td class='forumheader3' style='line-height:130%;'>
			{CONTENT_RECENT_SUBHEADING}
			{CONTENT_RECENT_SUMMARY}
			{CONTENT_RECENT_AUTHOR}, {CONTENT_RECENT_DATE}<br />
			{CONTENT_RECENT_RATING}
		</td>
		</tr>\n";
}
if(!$CONTENT_RECENT_TABLE_END){
		$CONTENT_RECENT_TABLE_END = "
		<tr>
			<td colspan='2'>
			<div style='text-align:right'><a href='".e_SELF."?article'><< ".LAN_37."</a></div>
			</td>
		</tr>		
		</table>
		</div>\n";
}
// ##### ------------------------------------------------------------------------------------------


// ##### ARCHIVE ----------------------------------------------------------------------------------
if(!$CONTENT_ARCHIVE_TABLE_START){
		$CONTENT_ARCHIVE_TABLE_START = "
		<div style='text-align:center'>
		<table class='fborder' style='width:95%'>\n";
}
if(!$CONTENT_ARCHIVE_TABLE){
		$CONTENT_ARCHIVE_TABLE = "
		<tr>
			<td class='forumheader3' style='background-color:transparent; border:#517DB1 1px solid; width:10%; text-align:center'>
				{CONTENT_ARCHIVE_ICON}
			</td>
			<td class='forumheader' style='width:90%'>
				{CONTENT_ARCHIVE_HEADING}
				({CONTENT_ARCHIVE_DATESTAMP})
			</td>
		</tr>
		";
}
if(!$CONTENT_ARCHIVE_TABLE_END){
		$CONTENT_ARCHIVE_TABLE_END = "
		</table>
		</div>\n";
}
// ##### ------------------------------------------------------------------------------------------


// ##### ARTICLE ----------------------------------------------------------------------------------
if(!$CONTENT_ARTICLE_TABLE_START){
		$CONTENT_ARTICLE_TABLE_START = "
		<div style='text-align:center'>
		<table class='fborder' style='width:95%' border='0'>\n";
}
if(!$CONTENT_ARTICLE_TABLE){
		$CONTENT_ARTICLE_TABLE = "
		<tr>
			<td rowspan='3' style='width:5%; white-space:nowrap;'>
				{CONTENT_ARTICLE_CAT_ICON}
			</td>
			<td>
				{CONTENT_ARTICLE_HEADING}
			</td>
		</tr>
		<tr>
			<td>
				{CONTENT_ARTICLE_SUBHEADING}
			</td>
		</tr>
		<tr>
			<td>
				{CONTENT_ARTICLE_AUTHOR} {CONTENT_ARTICLE_DATESTAMP}<br /><br />
			</td>
		</tr>
		<tr>
			<td colspan='2'>
				{CONTENT_ARTICLE_CONTENT}<br />
				{CONTENT_ARTICLE_PAGES}<br />
			</td>
		</tr>
		<tr>
			<td colspan='2' style='text-align:right'>
				{CONTENT_ARTICLE_EMAILPRINT}
			</td>
		</tr>
		<tr>
			<td colspan='2' style='text-align:right'>
				{CONTENT_ARTICLE_LINK_CAT}<br />
				{CONTENT_ARTICLE_LINK_MAIN}
			</td>
		</tr>
		";
}
if(!$CONTENT_ARTICLE_TABLE_END){
		$CONTENT_ARTICLE_TABLE_END = "
		</table>
		</div>\n";
}
// ##### ------------------------------------------------------------------------------------------


// ##### ARTICLE RATING ---------------------------------------------------------------------------
if(!$CONTENT_ARTICLE_RATING_TABLE_START){
		$CONTENT_ARTICLE_RATING_TABLE_START = "
		<div style='text-align:center'>
		<table class='fborder' style='width:95%'>\n";
}
if(!$CONTENT_ARTICLE_RATING_TABLE){
		$CONTENT_ARTICLE_RATING_TABLE = "
		<tr>
			<td>
				{CONTENT_ARTICLE_RATING}
			</td>
		</tr>
		";
}
if(!$CONTENT_ARTICLE_RATING_TABLE_END){
		$CONTENT_ARTICLE_RATING_TABLE_END = "
		</table>
		</div>\n";
}
// ##### ------------------------------------------------------------------------------------------


?>