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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_search_template.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-06-07 19:37:24 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
global $sc_style, $content_shortcodes;
global $CONTENT_SEARCH_TABLE_SELECT, $CONTENT_SEARCH_TABLE_ORDER, $CONTENT_SEARCH_TABLE_KEYWORD;

// ##### CONTENT SEARCH LIST --------------------------------------------------
$CONTENT_SEARCH_TABLE = "";
if(!$CONTENT_SEARCH_TABLE){
				$CONTENT_SEARCH_TABLE .= "
				<table class='fborder'>
					<tr>
						<td class='forumheader3' style='width:30%; white-space:nowrap;'>
							{CONTENT_SEARCH_TABLE_SELECT}
						</td>
						<td class='forumheader3' style='width:30%; white-space:nowrap;'>
							{CONTENT_SEARCH_TABLE_ORDER}
						</td>
						<td class='forumheader3' style='width:30%; white-space:nowrap;'>
							{CONTENT_SEARCH_TABLE_KEYWORD}
						</td>
					</tr>
				</table>";
}
// ##### ----------------------------------------------------------------------

?>