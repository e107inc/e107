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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/articles_menu/templates/default/content_search_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-03 23:20:38 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT SEARCH LIST --------------------------------------------------
$CONTENT_SEARCH_TABLE = "";
if(!$CONTENT_SEARCH_TABLE){
				$CONTENT_SEARCH_TABLE .= "
				<div style='text-align:center;'>
				<table style='width:98%;'>
					<tr>
						<td style='width:30%; white-space:nowrap; vertical-align:middle;'>
							{CONTENT_SEARCH_TABLE_SELECT}
						</td>
						<td style='width:30%; white-space:nowrap; text-align:center; vertical-align:middle;'>
							{CONTENT_SEARCH_TABLE_ORDER}
						</td>
						<td style='width:30%; white-space:nowrap; text-align:right; vertical-align:middle;'>
							{CONTENT_SEARCH_TABLE_KEYWORD}
						</td>
					</tr>
				</table>
				</div>";
}
// ##### ----------------------------------------------------------------------

?>