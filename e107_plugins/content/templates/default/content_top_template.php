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
|     $Revision: 1.6 $
|     $Date: 2005-06-07 19:37:24 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$CONTENT_TOP_TABLE_START = "";
$CONTENT_TOP_TABLE_END = "";
global $sc_style, $content_shortcodes, $qs, $authordetails, $row, $thisratearray;

$sc_style['CONTENT_TOP_TABLE_ICON']['pre'] = "<tr><td class='forumheader3' rowspan='3'>";
$sc_style['CONTENT_TOP_TABLE_ICON']['post'] = "</td>";

$sc_style['CONTENT_TOP_TABLE_HEADING']['pre'] = "";
$sc_style['CONTENT_TOP_TABLE_HEADING']['post'] = "";

$sc_style['CONTENT_TOP_TABLE_AUTHOR']['pre'] = "";
$sc_style['CONTENT_TOP_TABLE_AUTHOR']['post'] = "";

$sc_style['CONTENT_TOP_TABLE_RATING']['pre'] = "";
$sc_style['CONTENT_TOP_TABLE_RATING']['post'] = "";

// ##### CONTENT TOP --------------------------------------------------
if(!$CONTENT_TOP_TABLE_START){
				$CONTENT_TOP_TABLE_START = "
				<table class='fborder' style='width:98%; text-align:left;'>";
}
if(!$CONTENT_TOP_TABLE){
				$CONTENT_TOP_TABLE = "
					<tr>
						{CONTENT_TOP_TABLE_ICON}
						<td class='fcaption'>{CONTENT_TOP_TABLE_HEADING}</td>
					</tr>
					<tr><td class='forumheader3'>{CONTENT_TOP_TABLE_AUTHOR}</td></tr>
					<tr><td class='forumheader3' style='width:100%; text-align:right;'>{CONTENT_TOP_TABLE_RATING}</td></tr>\n";
}
if(!$CONTENT_TOP_TABLE_END){
				$CONTENT_TOP_TABLE_END = "
				</table>";
}
// ##### ----------------------------------------------------------------------

?>