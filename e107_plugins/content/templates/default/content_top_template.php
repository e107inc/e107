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
|     $Revision: 1.5 $
|     $Date: 2005-06-06 13:28:15 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$CONTENT_TOP_TABLE_START = "";
$CONTENT_TOP_TABLE_END = "";
global $sc_style, $content_shortcodes, $qs, $authordetails, $row, $thisratearray;

$sc_style['CONTENT_TOP_TABLE_ICON']['pre'] = "<td class='content_icon'>";
$sc_style['CONTENT_TOP_TABLE_ICON']['post'] = "</td>";

$sc_style['CONTENT_TOP_TABLE_HEADING']['pre'] = "<tr><td class='content_heading'>";
$sc_style['CONTENT_TOP_TABLE_HEADING']['post'] = "</td></tr>";

$sc_style['CONTENT_TOP_TABLE_AUTHOR']['pre'] = "<tr><td class='content_info'>";
$sc_style['CONTENT_TOP_TABLE_AUTHOR']['post'] = "</td></tr>";

$sc_style['CONTENT_TOP_TABLE_RATING']['pre'] = "<tr><td class='content_rating' style='width:100%; text-align:right;'>";
$sc_style['CONTENT_TOP_TABLE_RATING']['post'] = "</td></tr>";

// ##### CONTENT TOP --------------------------------------------------
if(!$CONTENT_TOP_TABLE_START){
				$CONTENT_TOP_TABLE_START = "
				<table class='content_table'>";
}
if(!$CONTENT_TOP_TABLE){
				$CONTENT_TOP_TABLE = "
					<tr>
						{CONTENT_TOP_TABLE_ICON}
						<td>
							<table style='width:100%;' cellpadding='0' cellspacing='0'>
								{CONTENT_TOP_TABLE_HEADING}
								{CONTENT_TOP_TABLE_AUTHOR}
								{CONTENT_TOP_TABLE_RATING}
							</table>
						</td>
					</tr>
					<tr><td class='content_spacer' colspan='2'></td></tr>
				\n";

}
if(!$CONTENT_TOP_TABLE_END){
				$CONTENT_TOP_TABLE_END = "
				</table>";
}
// ##### ----------------------------------------------------------------------

?>