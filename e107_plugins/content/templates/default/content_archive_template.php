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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_archive_template.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-06-06 13:28:15 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$CONTENT_ARCHIVE_TABLE_START = "";
$CONTENT_ARCHIVE_TABLE = "";
$CONTENT_ARCHIVE_TABLE_END = "";
global $sc_style, $content_shortcodes, $content_pref, $aa, $qs, $row;

// ##### CONTENT ARCHIVE --------------------------------------------------
if(!$CONTENT_ARCHIVE_TABLE_START){
				$CONTENT_ARCHIVE_TABLE_START = "
				<table class='content_table'>";
}
if(!$CONTENT_ARCHIVE_TABLE){
				$CONTENT_ARCHIVE_TABLE = "
				<tr><td class='content_heading' colspan='2'>{CONTENT_ARCHIVE_TABLE_HEADING}</td></tr>
				<tr>
					<td class='content_info' style='white-space:nowrap; text-align:left;'>{CONTENT_ARCHIVE_TABLE_AUTHOR}</td>
					<td class='content_info' style='width:5%; white-space:nowrap; text-align:right;'>{CONTENT_ARCHIVE_TABLE_DATE}</td>
				</tr>
				<tr><td class='content_spacer' colspan='2'></td></tr>
				\n";
}
if(!$CONTENT_ARCHIVE_TABLE_END){
				$CONTENT_ARCHIVE_TABLE_END = "
				</table>";
}
// ##### ----------------------------------------------------------------------

?>