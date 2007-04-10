<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/default/content_archive_template.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-04-10 14:34:39 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

global $sc_style, $content_shortcodes;

$sc_style['CM_AUTHOR|archive']['pre'] = "<tr><td class='forumheader3' colspan='2' style='white-space:nowrap; text-align:left;'>".CONTENT_LAN_11." ";
$sc_style['CM_AUTHOR|archive']['post'] = "</td></tr>";

$sc_style['CONTENT_ARCHIVE_TABLE_LETTERS']['pre'] = "<div style='margin-bottom:20px;'>";
$sc_style['CONTENT_ARCHIVE_TABLE_LETTERS']['post'] = "</div>";

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
	</table>";
}
// ##### ----------------------------------------------------------------------

?>