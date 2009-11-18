<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/default/content_content_template.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-11-18 01:05:28 $
 * $Author: e107coders $
 */

// ##### CONTENT CONTENT ------------------------------------------------------
global $sc_style, $content_shortcodes;

$sc_style['CM_REFER|content']['pre'] = "<br />".CONTENT_LAN_44." ";
$sc_style['CM_REFER|content']['post'] = "";

$sc_style['CM_COMMENT|content']['pre'] = "<br />".CONTENT_LAN_57." ";
$sc_style['CM_COMMENT|content']['post'] = "";

$sc_style['CM_SCORE|content']['pre'] = "<br />".CONTENT_LAN_45." ";
$sc_style['CM_SCORE|content']['post'] = "/100";

$sc_style['CM_RATING|content']['pre'] = "<br />";
$sc_style['CM_RATING|content']['post'] = "";

$sc_style['CM_AUTHOR|content']['pre'] = "<br />".CONTENT_LAN_11." ";
$sc_style['CM_AUTHOR|content']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_PAGENAMES']['pre'] = "<br /><div>".CONTENT_LAN_46."<br />";
$sc_style['CONTENT_CONTENT_TABLE_PAGENAMES']['post'] = "</div>";

$sc_style['CONTENT_CONTENT_TABLE_CUSTOM_TAGS']['pre'] = "<br /><br />";
$sc_style['CONTENT_CONTENT_TABLE_CUSTOM_TAGS']['post'] = "<br /><br />";

$sc_style['CM_SUMMARY|content']['pre'] = "<div>";
$sc_style['CM_SUMMARY|content']['post'] = "<br /><br /></div>";

$sc_style['CM_TEXT|content']['pre'] = "<div>";
$sc_style['CM_TEXT|content']['post'] = "</div>";

$sc_style['CM_IMAGES|content']['pre'] = "<div style='float:left; padding-right:10px;'>";
$sc_style['CM_IMAGES|content']['post'] = "</div>";

$sc_style['CM_SUBHEADING|content']['pre'] = "";
$sc_style['CM_SUBHEADING|content']['post'] = "<br />";

$sc_style['CM_FILE|content']['pre'] = "<br />";
$sc_style['CM_FILE|content']['post'] = "";

$sc_style['CM_DATE|content']['pre'] = CONTENT_LAN_10." ";
$sc_style['CM_DATE|content']['post'] = "";

$sc_style['CM_PARENT|content']['pre'] = "<br />".CONTENT_LAN_9." ";
$sc_style['CM_PARENT|content']['post'] = "";

$sc_style['CM_ICON|content']['pre'] = "<td style='width:10%; white-space:nowrap; vertical-align:top; padding-right:10px;'>";
$sc_style['CM_ICON|content']['post'] = "</td>";

$sc_style['CONTENT_CONTENT_TABLE_INFO_PRE']['pre'] = "<table cellpadding='0' cellspacing='0' style='width:100%; margin-bottom:20px;'><tr>";
$sc_style['CONTENT_CONTENT_TABLE_INFO_PRE']['post'] = "";
$sc_style['CONTENT_CONTENT_TABLE_INFO_POST']['pre'] = "";
$sc_style['CONTENT_CONTENT_TABLE_INFO_POST']['post'] = "</tr></table>";

$sc_style['CONTENT_CONTENT_TABLE_PREV_PAGE']['pre'] = "<div style='clear:both; padding-bottom:20px; padding-top:20px;'><div style='float:left;'>";
$sc_style['CONTENT_CONTENT_TABLE_PREV_PAGE']['post'] = "</div>";
$sc_style['CONTENT_CONTENT_TABLE_NEXT_PAGE']['pre'] = "<div style='float:right;'>";
$sc_style['CONTENT_CONTENT_TABLE_NEXT_PAGE']['post'] = "</div></div>";

$sc_style['CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA']['pre'] = "<td style='vertical-align:top;'>";
$sc_style['CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA']['post'] = "";
$sc_style['CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA']['pre'] = "";
$sc_style['CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA']['post'] = "</td>";

$CONTENT_CONTENT_TABLE = "<table class='fborder' cellpadding='0' cellspacing='0' style='width:100%;'><tr><td>
<div style='clear:both;'>
	{CONTENT_CONTENT_TABLE_INFO_PRE}
		{CM_ICON|content}
		{CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA}
			{CM_SUBHEADING|content}
			{CM_DATE|content} {CM_AUTHOR|content} {CM_EPICONS|content} {CM_EDITICON|content} {CM_PARENT|content} {CM_COMMENT|content} {CM_SCORE|content} {CM_REFER|content}
			{CM_RATING|content}
			{CM_FILE|content}
		{CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA}
	{CONTENT_CONTENT_TABLE_INFO_POST}
	<div style='clear:both;'><br /></div>
	<table class='fborder' cellpadding='0' cellspacing='0' style='width:100%;'><tr><td class='forumheader3'>
		{CM_IMAGES|content}
		{CM_SUMMARY|content}
		{CM_TEXT|content}
		{CONTENT_CONTENT_TABLE_CUSTOM_TAGS}
		{CONTENT_CONTENT_TABLE_PAGENAMES}
		{CONTENT_CONTENT_TABLE_PREV_PAGE}{CONTENT_CONTENT_TABLE_NEXT_PAGE}
	</td></tr></table>
</div>
</td></tr></table>\n";
// ##### ----------------------------------------------------------------------

$CONTENT_CONTENT_TABLE_CUSTOM_START = "<table style='width:100%;margin-left:0;padding-left:0;' cellspacing='0' cellpadding='0' >";

$CONTENT_CONTENT_TABLE_CUSTOM = "
<tr>
	<td style='width:25%;white-space:nowrap; vertical-align:top; line-height:150%;'>
		{CONTENT_CONTENT_TABLE_CUSTOM_KEY}
	</td>
	<td style='width:90%; line-height:150%;'>
		{CONTENT_CONTENT_TABLE_CUSTOM_VALUE}
	</td>
</tr>";

$CONTENT_CONTENT_TABLE_CUSTOM_END = "</table>";

?>