<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/default/content_menu_template.php,v $
 * $Revision: 1.2 $
 * $Date: 2009-11-17 13:23:59 $
 * $Author: marj_nl_fr $
 */

global $sc_style, $content_shortcodes;

//##### MENU TEMPLATE --------------------------------------------------

$sc_style['CM_MENU_SEARCH']['pre'] = "";
$sc_style['CM_MENU_SEARCH']['post'] = "";

$sc_style['CM_MENU_SELECT']['pre'] = "";
$sc_style['CM_MENU_SELECT']['post'] = "";

$sc_style['CM_MENU_ORDER']['pre'] = "";
$sc_style['CM_MENU_ORDER']['post'] = "<br />";

$sc_style['CM_MENU_LINKS_VIEWALLCAT']['pre'] = "";
$sc_style['CM_MENU_LINKS_VIEWALLCAT']['post'] = "<br />";

$sc_style['CM_MENU_LINKS_VIEWALLAUTHOR']['pre'] = "";
$sc_style['CM_MENU_LINKS_VIEWALLAUTHOR']['post'] = "<br />";

$sc_style['CM_MENU_LINKS_VIEWALLITEM']['pre'] = "";
$sc_style['CM_MENU_LINKS_VIEWALLITEM']['post'] = "<br />";

$sc_style['CM_MENU_LINKS_VIEWTOPRATED']['pre'] = "";
$sc_style['CM_MENU_LINKS_VIEWTOPRATED']['post'] = "<br />";

$sc_style['CM_MENU_LINKS_VIEWTOPSCORE']['pre'] = "";
$sc_style['CM_MENU_LINKS_VIEWTOPSCORE']['post'] = "<br />";

$sc_style['CM_MENU_LINKS_VIEWRECENT']['pre'] = "";
$sc_style['CM_MENU_LINKS_VIEWRECENT']['post'] = "<br />";

$sc_style['CM_MENU_LINKS_VIEWSUBMIT']['pre'] = "";
$sc_style['CM_MENU_LINKS_VIEWSUBMIT']['post'] = "<br />";

if(!isset($CONTENT_MENU)){
	$CONTENT_MENU = "
	{CM_MENU_SEARCH}
	{CM_MENU_SELECT}
	{CM_MENU_ORDER}
	{CM_MENU_LINKCAPTION}
	{CM_MENU_LINKS_VIEWALLCAT}
	{CM_MENU_LINKS_VIEWALLAUTHOR}
	{CM_MENU_LINKS_VIEWALLITEM}
	{CM_MENU_LINKS_VIEWTOPRATED}
	{CM_MENU_LINKS_VIEWTOPSCORE}
	{CM_MENU_LINKS_VIEWRECENT}
	{CM_MENU_LINKS_VIEWSUBMIT}

	{CMT_CATEGORY}
	{CMT_RECENT}";
}

//##### CATEGORY LIST --------------------------------------------------

$sc_style['CM_MENU_CATEGORY_ICON']['pre'] = "<td style='width:1%; white-space:nowrap; vertical-align:top; padding-right:5px;'>";
$sc_style['CM_MENU_CATEGORY_ICON']['post'] = "</td>";

$sc_style['CM_MENU_CATEGORY_COUNT']['pre'] = " <span class='smalltext'>(";
$sc_style['CM_MENU_CATEGORY_COUNT']['post'] = ")</span>";

if(!isset($CONTENT_MENU_CATEGORY_START)){
	$CONTENT_MENU_CATEGORY_START = "<br />{CM_MENU_CATEGORY_CAPTION}<br />";
}

if(!isset($CONTENT_MENU_CATEGORY_TABLE)){
	$CONTENT_MENU_CATEGORY_TABLE = "
	<table style='width:100%; text-align:left; border:0;' cellpadding='0' cellspacing='0'>
	<tr>
		{CM_MENU_CATEGORY_ICON}
		<td colspan='2'>{CM_MENU_CATEGORY_HEADING} {CM_MENU_CATEGORY_COUNT}</td>
	</tr>
	</table>";
}

if(!isset($CONTENT_MENU_CATEGORY_END)){
	$CONTENT_MENU_CATEGORY_END = "";
}

//##### RECENT --------------------------------------------------

$sc_style['CM_MENU_RECENT_ICON']['pre'] = "<td style='width:1%; white-space:nowrap; vertical-align:top; padding-right:5px;'>";
$sc_style['CM_MENU_RECENT_ICON']['post'] = "</td>";

$sc_style['CM_MENU_RECENT_HEADING']['pre'] = "";
$sc_style['CM_MENU_RECENT_HEADING']['post'] = "<br />";

$sc_style['CM_MENU_RECENT_DATE']['pre'] = "";
$sc_style['CM_MENU_RECENT_DATE']['post'] = "<br />";

$sc_style['CM_MENU_RECENT_AUTHOR']['pre'] = "";
$sc_style['CM_MENU_RECENT_AUTHOR']['post'] = "<br />";

$sc_style['CM_MENU_RECENT_SUBHEADING']['pre'] = "";
$sc_style['CM_MENU_RECENT_SUBHEADING']['post'] = "<br />";

if(!isset($CONTENT_MENU_RECENT_START)){
	$CONTENT_MENU_RECENT_START = "<br />{CM_MENU_RECENT_CAPTION}<br />";
}
if(!isset($CONTENT_MENU_RECENT_TABLE)){
	$CONTENT_MENU_RECENT_TABLE = "
	<table style='width:100%; text-align:left; border:0; margin-bottom:10px;' cellpadding='0' cellspacing='0'>
	<tr>
		{CM_MENU_RECENT_ICON}
		<td style='width:99%; vertical-align:top;'>
			{CM_MENU_RECENT_HEADING}
			{CM_MENU_RECENT_DATE}
			{CM_MENU_RECENT_AUTHOR}
			{CM_MENU_RECENT_SUBHEADING}
		</td>
	</tr>
	</table>";
}
if(!isset($CONTENT_MENU_RECENT_END)){
	$CONTENT_MENU_RECENT_END = "";
}

// ##### ----------------------------------------------------------------------

?>