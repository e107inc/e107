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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/default/content_archive_template.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-17 13:23:59 $
 * $Author: marj_nl_fr $
 */

global $sc_style, $content_shortcodes;

$sc_style['CM_AUTHOR|archive']['pre'] = "<tr><td class='forumheader3' colspan='2' style='white-space:nowrap; text-align:left;'>".CONTENT_LAN_11." ";
$sc_style['CM_AUTHOR|archive']['post'] = "</td></tr>";

$sc_style['CONTENT_ARCHIVE_TABLE_LETTERS']['pre'] = "<div style='margin-bottom:20px;'>";
$sc_style['CONTENT_ARCHIVE_TABLE_LETTERS']['post'] = "</div>";

$sc_style['CONTENT_NEXTPREV']['pre'] = "<div class='nextprev'>";
$sc_style['CONTENT_NEXTPREV']['post'] = "</div>";

// ##### CONTENT ARCHIVE --------------------------------------------------
if(!isset($CONTENT_ARCHIVE_TABLE_START)){
	$CONTENT_ARCHIVE_TABLE_START = "
	{CONTENT_ARCHIVE_TABLE_LETTERS}
	<table class='fborder'>";
}
if(!isset($CONTENT_ARCHIVE_TABLE)){
	$CONTENT_ARCHIVE_TABLE = "				
	<tr>
		<td class='fcaption'>{CM_HEADING|archive}</td>
		<td class='fcaption' style='width:5%; white-space:nowrap; text-align:right;'>{CM_DATE|archive}</td>
	</tr>
	{CM_AUTHOR|archive}
	\n";
}
if(!isset($CONTENT_ARCHIVE_TABLE_END)){
	$CONTENT_ARCHIVE_TABLE_END = "
	</table>{CONTENT_NEXTPREV}";
}
// ##### ----------------------------------------------------------------------

?>