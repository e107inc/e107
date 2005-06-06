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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/content_submit_type_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-06-06 13:28:15 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
global $sc_style, $content_shortcodes;

$CONTENT_SUBMIT_TYPE_TABLE_START = "";
$CONTENT_SUBMIT_TYPE_TABLE = "";
$CONTENT_SUBMIT_TYPE_TABLE_END = "";

$sc_style['CONTENT_SUBMIT_TYPE_TABLE_ICON']['pre'] = "<td class='content_icon'>";
$sc_style['CONTENT_SUBMIT_TYPE_TABLE_ICON']['post'] = "</td>";

$sc_style['CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING']['pre'] = "<tr><td class='content_subheading'>";
$sc_style['CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING']['post'] = "</td></tr>";

// ##### CONTENT SUBMIT TYPE LIST --------------------------------------------------
if(!$CONTENT_SUBMIT_TYPE_TABLE_START){
				$CONTENT_SUBMIT_TYPE_TABLE_START = "
				<table class='content_table'>\n";
}
if(!$CONTENT_SUBMIT_TYPE_TABLE){
				$CONTENT_SUBMIT_TYPE_TABLE = "
				<tr>
					{CONTENT_SUBMIT_TYPE_TABLE_ICON}
					<td>
						<table style='width:100%;'>
							<tr><td class='content_heading'>{CONTENT_SUBMIT_TYPE_TABLE_HEADING}</td></tr>
							{CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING}
						</table>
					</td>
				</tr>
				<tr><td class='content_spacer'></td></tr>\n";
}
if(!$CONTENT_SUBMIT_TYPE_TABLE_END){
				$CONTENT_SUBMIT_TYPE_TABLE_END = "
				</table>";
}
// ##### ----------------------------------------------------------------------

?>