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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/content_type_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-05-15 14:45:14 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$CONTENT_TYPE_TABLE_START = "";
$CONTENT_TYPE_TABLE_SUBMIT = "";
$CONTENT_TYPE_TABLE_MANAGER = "";
$CONTENT_TYPE_TABLE_LINE = "";
$CONTENT_TYPE_TABLE_END = "";

global $sc_style, $content_shortcodes, $contenttotal, $row, $tp;

$sc_style['CONTENT_TYPE_TABLE_TOTAL']['pre'] = "";
$sc_style['CONTENT_TYPE_TABLE_TOTAL']['post'] = " ";

$sc_style['CONTENT_TYPE_TABLE_HEADING']['pre'] = "";
$sc_style['CONTENT_TYPE_TABLE_HEADING']['post'] = "";

$sc_style['CONTENT_TYPE_TABLE_SUBHEADING']['pre'] = "<tr><td class='content_subheading' colspan='2'>";
$sc_style['CONTENT_TYPE_TABLE_SUBHEADING']['post'] = "</td></tr>";

// ##### CONTENT TYPE LIST --------------------------------------------------
if(!$CONTENT_TYPE_TABLE_START){
				$CONTENT_TYPE_TABLE_START = "
				<table class='content_table'>\n";
}
if(!$CONTENT_TYPE_TABLE){
				$CONTENT_TYPE_TABLE = "
				<tr>
					<td class='content_icon'>{CONTENT_TYPE_TABLE_ICON}</td>
					<td>
						<table style='width:100%;'>
							<tr>
								<td class='content_heading'>{CONTENT_TYPE_TABLE_HEADING}</td>
								<td class='content_info' style='text-align:right;'>{CONTENT_TYPE_TABLE_TOTAL} {CONTENT_TYPE_TABLE_TOTAL_LAN}</td>
							</tr>
							{CONTENT_TYPE_TABLE_SUBHEADING}
						</table>
					</td>
				</tr>
				<tr><td class='content_spacer' colspan='2'></td></tr>\n";
}
if(!$CONTENT_TYPE_TABLE_SUBMIT){
				$CONTENT_TYPE_TABLE_SUBMIT = "
				<tr>
					<td class='content_icon'>{CONTENT_TYPE_TABLE_SUBMIT_ICON}</td>
					<td>
						<table>
							<tr><td class='content_heading' colspan='2'>{CONTENT_TYPE_TABLE_SUBMIT_HEADING}</td></tr>
							<tr><td class='content_subheading' colspan='2'>{CONTENT_TYPE_TABLE_SUBMIT_SUBHEADING}</td></tr>
						</table>
					</td>
				</tr>
				<tr><td class='content_spacer' colspan='2'></td></tr>\n";
}
if(!$CONTENT_TYPE_TABLE_MANAGER){
				$CONTENT_TYPE_TABLE_MANAGER = "
				<tr>
					<td class='content_icon'>{CONTENT_TYPE_TABLE_MANAGER_ICON}</td>
					<td>
						<table>
							<tr><td class='content_heading' colspan='2'>{CONTENT_TYPE_TABLE_MANAGER_HEADING}</td></tr>
							<tr><td class='content_subheading' colspan='2'>{CONTENT_TYPE_TABLE_MANAGER_SUBHEADING}</td></tr>
						</table>
					</td>
				</tr>
				<tr><td class='content_spacer' colspan='2'></td></tr>\n";
}
if(!$CONTENT_TYPE_TABLE_LINE){
				$CONTENT_TYPE_TABLE_LINE = "<tr><td class='content_spacer' colspan='2' style='border-top:1px solid #000;'></td></tr>";
}
if(!$CONTENT_TYPE_TABLE_END){
				$CONTENT_TYPE_TABLE_END = "
				</table>";
}
// ##### ----------------------------------------------------------------------

?>