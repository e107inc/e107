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
|     $Revision: 1.7 $
|     $Date: 2005-06-07 22:02:34 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$CONTENT_SEARCHRESULT_TABLE_START = "";
$CONTENT_SEARCHRESULT_TABLE = "";
$CONTENT_SEARCHRESULT_TABLE_END = "";

global $sc_style, $content_shortcodes, $tp, $gen, $row, $qs, $aa, $content_icon_path, $content_pref, $contenttext;

$sc_style['CONTENT_RECENT_TABLE_ICON']['pre'] = "<td class='forumheader3'>";
$sc_style['CONTENT_RECENT_TABLE_ICON']['post'] = "</td>";

// ##### CONTENT SEARCHRESULT LIST --------------------------------------------------
if(!$CONTENT_SEARCHRESULT_TABLE_START){
				$CONTENT_SEARCHRESULT_TABLE_START = "";
}
if(!$CONTENT_SEARCHRESULT_TABLE){
				$CONTENT_SEARCHRESULT_TABLE .= "
				<table class='fborder' style='width:98%; text-align:left;'>
					<tr>
						{CONTENT_SEARCHRESULT_TABLE_ICON}
						<td>
							<table cellpadding='0' cellspacing='0'>
								<tr><td class='fcaption'>{CONTENT_SEARCHRESULT_TABLE_HEADING}</td></tr>
								<tr><td class='forumheader'>{CONTENT_SEARCHRESULT_TABLE_SUBHEADING}</td></tr>
								<tr><td class='forumheader3'>{CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS} {CONTENT_SEARCHRESULT_TABLE_DATE}</td></tr>
								<tr><td class='forumheader3'>{CONTENT_SEARCHRESULT_TABLE_TEXT}</td></tr>
							</table>
						</td>
					</tr>
					<tr><td class='spacer' colspan='2'></td></tr>
				</table>\n";
}
if(!$CONTENT_SEARCHRESULT_TABLE_END){
				$CONTENT_SEARCHRESULT_TABLE_END .= "";
}
// ##### ----------------------------------------------------------------------

?>