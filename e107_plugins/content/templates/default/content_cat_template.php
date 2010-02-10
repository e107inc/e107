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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/default/content_cat_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

global $sc_style, $content_shortcodes;

$sc_style['CM_ICON|cat']['pre'] = "<td class='forumheader3' rowspan='5' style='width:5%; white-space:nowrap; padding-right:5px;'>";
$sc_style['CM_ICON|cat']['post'] = "</td>";

$sc_style['CM_AUTHOR|cat']['pre'] = " ";
$sc_style['CM_AUTHOR|cat']['post'] = " ";

$sc_style['CM_SUBHEADING|cat']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_SUBHEADING|cat']['post'] = "<br /></td></tr>";

$sc_style['CM_TEXT|cat']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_TEXT|cat']['post'] = "<br /></td></tr>";

$sc_style['CM_RATING|cat']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_RATING|cat']['post'] = "<br /></td></tr>";

$sc_style['CM_AMOUNT|cat']['pre'] = "(";
$sc_style['CM_AMOUNT|cat']['post'] = ")";

$sc_style['CONTENT_CAT_TABLE_INFO_PRE']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CAT_TABLE_INFO_PRE']['post'] = "";

$sc_style['CONTENT_CAT_TABLE_INFO_POST']['pre'] = "";
$sc_style['CONTENT_CAT_TABLE_INFO_POST']['post'] = "</td></tr>";

// ##### CONTENT CAT ----------------------------------------------------------
if(!isset($CONTENT_CAT_TABLE_START)){
	$CONTENT_CAT_TABLE_START = "";
}
if(!isset($CONTENT_CAT_TABLE)){
	$CONTENT_CAT_TABLE = "
	<table class='fborder' style='width:98%; text-align:left; margin-bottom:5px;'>
	<tr>
		{CM_ICON|cat}
		<td class='fcaption' >{CM_HEADING|cat} {CM_AMOUNT|cat}</td>
	</tr>
	{CONTENT_CAT_TABLE_INFO_PRE}
		{CM_DATE|cat} {CM_AUTHOR|cat} {CM_EPICONS|cat} {CM_COMMENT|cat}
	{CONTENT_CAT_TABLE_INFO_POST}
	{CM_SUBHEADING|cat}
	{CM_TEXT|cat}
	{CM_RATING|cat}
	</table>\n";
}
if(!isset($CONTENT_CAT_TABLE_END)){
	$CONTENT_CAT_TABLE_END = "";
}
// ##### ----------------------------------------------------------------------

$sc_style['CM_ICON|catlist']['pre'] = "<td class='forumheader3' style='width:5%; white-space:nowrap; padding-right:5px;' rowspan='5'>";
$sc_style['CM_ICON|catlist']['post'] = "</td>";

$sc_style['CM_SUBHEADING|catlist']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_SUBHEADING|catlist']['post'] = "</td></tr>";

$sc_style['CM_AUTHOR|catlist']['pre'] = " / ";
$sc_style['CM_AUTHOR|catlist']['post'] = "";

$sc_style['CM_RATING|catlist']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_RATING|catlist']['post'] = "</td></tr>";

$sc_style['CM_TEXT|catlist']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_TEXT|catlist']['post'] = "</td></tr>";

$sc_style['CM_AMOUNT|catlist']['pre'] = "(";
$sc_style['CM_AMOUNT|catlist']['post'] = ")";

$sc_style['CONTENT_CAT_LIST_TABLE_INFO_PRE']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CAT_LIST_TABLE_INFO_PRE']['post'] = "";

$sc_style['CONTENT_CAT_LIST_TABLE_INFO_POST']['pre'] = "";
$sc_style['CONTENT_CAT_LIST_TABLE_INFO_POST']['post'] = "</td></tr>";

// ##### CONTENT CAT_LIST -----------------------------------------------------
if(!isset($CONTENT_CAT_LIST_TABLE)){
	$CONTENT_CAT_LIST_TABLE = "
	<table class='fborder' style='width:98%; text-align:left; margin-bottom:10px;'>
	<tr>
		{CM_ICON|catlist}
		<td class='fcaption'>{CM_HEADING|catlist} {CM_AMOUNT|catlist}</td>
	</tr>
	{CM_SUBHEADING|catlist}
		{CONTENT_CAT_LIST_TABLE_INFO_PRE}
		{CM_DATE|catlist} {CM_AUTHOR|catlist} {CM_EPICONS|catlist} {CM_COMMENT|catlist}
	{CONTENT_CAT_LIST_TABLE_INFO_POST}
	{CM_RATING|catlist}
	{CM_TEXT|catlist}
	</table>\n";
}
// ##### ----------------------------------------------------------------------

$sc_style['CM_ICON|catlistsub']['pre'] = "<td class='forumheader3' style='width:2%; white-space:nowrap; padding-right:5px; ' rowspan='2'>";
$sc_style['CM_ICON|catlistsub']['post'] = "</td>";

$sc_style['CM_SUBHEADING|catlistsub']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_SUBHEADING|catlistsub']['post'] = "</td></tr>";

$sc_style['CM_AMOUNT|catlistsub']['pre'] = "(";
$sc_style['CM_AMOUNT|catlistsub']['post'] = ")";

// ##### CONTENT CAT_LIST SUB -------------------------------------------------
if(!isset($CONTENT_CAT_LISTSUB_TABLE_START)){
	$CONTENT_CAT_LISTSUB_TABLE_START = "";
}
if(!isset($CONTENT_CAT_LISTSUB_TABLE)){
	$CONTENT_CAT_LISTSUB_TABLE = "
	<table class='fborder' style='width:98%; text-align:left; margin-bottom:5px;'>
	<tr>
		{CM_ICON|catlistsub}
		<td class='fcaption'>{CM_HEADING|catlistsub} {CM_AMOUNT|catlistsub}</td>
	</tr>
	{CM_SUBHEADING|catlistsub}
	</table>\n";
}
if(!isset($CONTENT_CAT_LISTSUB_TABLE_END)){
	$CONTENT_CAT_LISTSUB_TABLE_END = "<br />";
}
// ##### ----------------------------------------------------------------------

?>