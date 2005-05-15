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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_searchresult_template.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-05-15 14:45:15 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$CONTENT_SEARCHRESULT_TABLE_START = "";
$CONTENT_SEARCHRESULT_TABLE = "";
$CONTENT_SEARCHRESULT_TABLE_END = "";
global $sc_style, $content_shortcodes, $tp, $gen, $row, $type, $type_id, $aa, $content_icon_path, $content_pref, $contenttext;
// ##### CONTENT SEARCHRESULT LIST --------------------------------------------------
if(!$CONTENT_SEARCHRESULT_TABLE_START){
				$CONTENT_SEARCHRESULT_TABLE_START = "";
}
if(!$CONTENT_SEARCHRESULT_TABLE){
				$CONTENT_SEARCHRESULT_TABLE .= "
				<table class='content_table'>
					<tr>
						<td class='content_icon'>{CONTENT_SEARCHRESULT_TABLE_ICON}</td>
						<td>
							<table>
								<tr><td class='content_heading'>{CONTENT_SEARCHRESULT_TABLE_HEADING}</td></tr>
								<tr><td class='content_subheading'>{CONTENT_SEARCHRESULT_TABLE_SUBHEADING}</td></tr>
								<tr><td class='content_info'>{CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS} {CONTENT_SEARCHRESULT_TABLE_DATE}</td></tr>
								<tr><td class='content_text'>{CONTENT_SEARCHRESULT_TABLE_TEXT}</td></tr>
							</table>
						</td>
					</tr>
					<tr><td class='content_spacer' colspan='2'></td></tr>
				</table>\n";
}
if(!$CONTENT_SEARCHRESULT_TABLE_END){
				$CONTENT_SEARCHRESULT_TABLE_END .= "";
}
// ##### ----------------------------------------------------------------------

?>