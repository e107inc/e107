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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_top_template.php,v $
|     $Revision: 1.9 $
|     $Date: 2005-07-06 13:48:39 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

global $sc_style, $content_shortcodes, $qs, $authordetails, $row, $thisratearray;

$sc_style['CONTENT_TOP_TABLE_ICON']['pre'] = "<td class='forumheader3' rowspan='3' style='width:5%; white-space:nowrap;'>";
$sc_style['CONTENT_TOP_TABLE_ICON']['post'] = "</td>";

$sc_style['CONTENT_TOP_TABLE_HEADING']['pre'] = "";
$sc_style['CONTENT_TOP_TABLE_HEADING']['post'] = "";

$sc_style['CONTENT_TOP_TABLE_AUTHOR']['pre'] = "<tr><td class='forumheader3' colspan='2'>".CONTENT_LAN_11." ";
$sc_style['CONTENT_TOP_TABLE_AUTHOR']['post'] = "</td></tr>";

$sc_style['CONTENT_TOP_TABLE_RATING']['pre'] = "<td class='fcaption' style='width:20%; white-space:nowrap; text-align:right;'>";
$sc_style['CONTENT_TOP_TABLE_RATING']['post'] = "</td>";

// ##### CONTENT TOP --------------------------------------------------
if(!isset($CONTENT_TOP_TABLE_START)){
				$CONTENT_TOP_TABLE_START = "
				";
}
if(!isset($CONTENT_TOP_TABLE)){
				$CONTENT_TOP_TABLE = "
					<table class='fborder' style='width:98%; text-align:left; margin-bottom:5px;'>
					<tr>
						{CONTENT_TOP_TABLE_ICON}
						<td class='fcaption'>{CONTENT_TOP_TABLE_HEADING}</td>
						{CONTENT_TOP_TABLE_RATING}
					</tr>
					{CONTENT_TOP_TABLE_AUTHOR}
					</table>\n";
}
if(!isset($CONTENT_TOP_TABLE_END)){
				$CONTENT_TOP_TABLE_END = "
				";
}
// ##### ----------------------------------------------------------------------

?>