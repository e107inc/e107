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
|     $Revision: 1.1 $
|     $Date: 2005-02-03 23:31:40 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT AUTHOR -------------------------------------------------------
if(!$CONTENT_AUTHOR_TABLE_START){
				$CONTENT_AUTHOR_TABLE_START = "
				<div style='text-align:center'>
				<table style='width:95%'>\n";
}
if(!$CONTENT_AUTHOR_TABLE){
				$CONTENT_AUTHOR_TABLE = "
				<tr>
				<td style='width:15%; white-space:nowrap; vertical-align:top;'>{CONTENT_AUTHOR_TABLE_ICON} {CONTENT_AUTHOR_TABLE_NAME}</td>
				<td style='width:15%; white-space:nowrap; vertical-align:top; text-align:center;'><span class='smalltext'>{CONTENT_AUTHOR_TABLE_TOTAL}</span></td>
				<td style='width:70%; text-align:left;'><span class='smalltext'>".CONTENT_LAN_55." {CONTENT_AUTHOR_TABLE_DATE}<br />
					{CONTENT_AUTHOR_TABLE_HEADING}</span></td>
				</tr>
				<tr><td><div class='spacer'><br /></div></td></tr>";
}
if(!$CONTENT_AUTHOR_TABLE_END){
				$CONTENT_AUTHOR_TABLE_END = "
				</table>
				</div>\n";
}
// ##### ----------------------------------------------------------------------

?>