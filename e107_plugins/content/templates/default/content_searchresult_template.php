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
|     $Revision: 1.1 $
|     $Date: 2005-02-03 23:31:40 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT SEARCHRESULT LIST --------------------------------------------------
$CONTENT_SEARCHRESULT_TABLE = "";
$CONTENT_SEARCHRESULT_TABLE_END = "";
if(!$CONTENT_SEARCHRESULT_TABLE_START){
				$CONTENT_SEARCHRESULT_TABLE_START = "
				<div class='spacer'></div><div style='text-align:center'>
				<table class='fborder' style='width:95%'>";
}
if(!$CONTENT_SEARCHRESULT_TABLE){
				$CONTENT_SEARCHRESULT_TABLE .= "
				<tr>
					<td rowspan='2' class='forumheader3' style='width:5%; white-space:nowrap; text-align:center; vertical-align:top; '>
						{CONTENT_SEARCHRESULT_TABLE_ICON}
					</td>
					<td class='forumheader3' style='width:70%; vertical-align:top;'>
						{CONTENT_SEARCHRESULT_TABLE_HEADING} <i>{CONTENT_SEARCHRESULT_TABLE_SUBHEADING}</i>
					</td>
					<td class='forumheader3' style='width:15%; white-space:nowrap; text-align:right; vertical-align:top; '>
						<span class='smalltext'>{CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS}</span>
					</td>
					<td class='forumheader3' style='width:10%; white-space:nowrap; vertical-align:top;'>
						<span class='smalltext'>{CONTENT_SEARCHRESULT_TABLE_DATE}</span>
					</td>
				</tr>
				<tr>
					<td colspan='3' class='forumheader3' style='width:70%; vertical-align:top;'>
						<span class='smalltext'>{CONTENT_SEARCHRESULT_TABLE_TEXT}</span>
					</td>
				</tr>";
}
if(!$CONTENT_SEARCHRESULT_TABLE_END){
				$CONTENT_SEARCHRESULT_TABLE_END .= "
				</table>
				</div>";
}
// ##### ----------------------------------------------------------------------

?>