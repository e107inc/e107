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
|     $Revision: 1.4 $
|     $Date: 2005-06-08 20:00:29 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$CONTENT_ARCHIVE_TABLE_START = "";
$CONTENT_ARCHIVE_TABLE = "";
$CONTENT_ARCHIVE_TABLE_END = "";
global $sc_style, $content_shortcodes, $content_pref, $aa, $qs, $row, $CONTENT_ARCHIVE_TABLE_LETTERS;

$sc_style['CONTENT_ARCHIVE_TABLE_AUTHOR']['pre'] = "<tr><td class='forumheader3' colspan='2' style='white-space:nowrap; text-align:left;'>";
$sc_style['CONTENT_ARCHIVE_TABLE_AUTHOR']['post'] = "</td></tr>";

$sc_style['CONTENT_ARCHIVE_TABLE_LETTERS']['pre'] = "<tr><td class='forumheader3' colspan='2'>";
$sc_style['CONTENT_ARCHIVE_TABLE_LETTERS']['post'] = "</td></tr>";

// ##### CONTENT ARCHIVE --------------------------------------------------
if(!$CONTENT_ARCHIVE_TABLE_START){
				$CONTENT_ARCHIVE_TABLE_START = "
				<table class='fborder'>
				{CONTENT_ARCHIVE_TABLE_LETTERS}
				";
}
if(!$CONTENT_ARCHIVE_LETTER){
				$CONTENT_ARCHIVE_LETTER = "";
}
if(!$CONTENT_ARCHIVE_TABLE){
				$CONTENT_ARCHIVE_TABLE = "				
				<tr>
					<td class='forumheader3'>{CONTENT_ARCHIVE_TABLE_HEADING}</td>
					<td class='forumheader3' style='width:5%; white-space:nowrap; text-align:right;'>{CONTENT_ARCHIVE_TABLE_DATE}</td>
				</tr>
				{CONTENT_ARCHIVE_TABLE_AUTHOR}
				\n";
}
if(!$CONTENT_ARCHIVE_TABLE_END){
				$CONTENT_ARCHIVE_TABLE_END = "
				</table>";
}
// ##### ----------------------------------------------------------------------

?>