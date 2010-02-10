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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/content_type_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

global $sc_style, $content_shortcodes;

$sc_style['CM_AMOUNT|type']['pre'] = "";
$sc_style['CM_AMOUNT|type']['post'] = " ";

$sc_style['CM_HEADING|type']['pre'] = "";
$sc_style['CM_HEADING|type']['post'] = "";

$sc_style['CM_SUBHEADING|type']['pre'] = "";
$sc_style['CM_SUBHEADING|type']['post'] = "";

$sc_style['CONTENT_TYPE_TABLE_LINK']['pre'] = "<br /><span class='smalltext'>";
$sc_style['CONTENT_TYPE_TABLE_LINK']['post'] = "</span>";

// ##### CONTENT TYPE LIST --------------------------------------------------
if(!isset($CONTENT_TYPE_TABLE_START)){
	$CONTENT_TYPE_TABLE_START = "
	<table class='fborder' style='width:98%; text-align:left;'>\n";
}
if(!isset($CONTENT_TYPE_TABLE)){
	$CONTENT_TYPE_TABLE = "
	<tr>
		<td class='forumheader3' style='width:5%; white-space:nowrap; padding-bottom:5px;' rowspan='2'>{CM_ICON|type}</td>
		<td class='fcaption'>{CM_HEADING|type}{CONTENT_TYPE_TABLE_LINK}</td>
		<td class='forumheader' style='width:5%; white-space:nowrap; text-align:right;'>{CM_AMOUNT|type}</td>
	</tr>
	<tr><td class='forumheader2' colspan='2'>{CM_SUBHEADING|type}<br /></td></tr>\n";
}
if(!isset($CONTENT_TYPE_TABLE_MANAGER)){
	$CONTENT_TYPE_TABLE_MANAGER = "
	<tr>
		<td class='forumheader3' style='width:5%; white-space:nowrap; padding-bottom:5px;' rowspan='2'>{CM_ICON|manager_link}</td>
		<td class='fcaption' colspan='2'>{CM_HEADING|manager_link}</td>
	</tr>
	<tr><td class='forumheader2' colspan='2'>".CONTENT_LAN_68."</td></tr>\n";
}
if(!isset($CONTENT_TYPE_TABLE_LINE)){
	$CONTENT_TYPE_TABLE_LINE = "";
}
if(!isset($CONTENT_TYPE_TABLE_END)){
	$CONTENT_TYPE_TABLE_END = "
	</table>";
}
// ##### ----------------------------------------------------------------------

?>