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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_author_template.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-05-15 14:45:14 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
global $sc_style, $content_shortcodes, $authordetails, $i, $type, $type_id, $gen, $row, $totalcontent, $CONTENT_AUTHOR_TABLE_DATE;

$CONTENT_AUTHOR_TABLE_START = "";
$CONTENT_AUTHOR_TABLE_END = "";

$sc_style['CONTENT_AUTHOR_TABLE_DATE']['pre'] = CONTENT_LAN_55." ";
$sc_style['CONTENT_AUTHOR_TABLE_DATE']['post'] = " ";

// ##### CONTENT AUTHOR -------------------------------------------------------
if(!$CONTENT_AUTHOR_TABLE_START){
				$CONTENT_AUTHOR_TABLE_START = "
				<table class='content_table'>\n";
}
if(!$CONTENT_AUTHOR_TABLE){
				$CONTENT_AUTHOR_TABLE = "
				<tr>
					<td class='content_heading'>{CONTENT_AUTHOR_TABLE_ICON} {CONTENT_AUTHOR_TABLE_NAME}</td>
					<td class='content_info' style='text-align:right; white-space:nowrap;'>{CONTENT_AUTHOR_TABLE_TOTAL}</td>
				</tr>
				<tr>
					<td class='content_info' colspan='2'>{CONTENT_AUTHOR_TABLE_DATE} : {CONTENT_AUTHOR_TABLE_HEADING}</td>
				</tr>
				<tr><td class='content_spacer'></td></tr>";
}
if(!$CONTENT_AUTHOR_TABLE_END){
				$CONTENT_AUTHOR_TABLE_END = "
				</table>\n";
}
// ##### ----------------------------------------------------------------------

?>