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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_manager_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-03 23:31:40 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT CONTENTMANAGER LIST --------------------------------------------------
if(!$CONTENT_CONTENTMANAGER_TABLE_START){
				$CONTENT_CONTENTMANAGER_TABLE_START = "
				<div style='text-align:center'>
				<table class='fborder' style='".ADMIN_WIDTH."'>
				<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_ITEM_LAN_57."</td></tr>\n";
}
if(!$CONTENT_CONTENTMANAGER_TABLE){
				$CONTENT_CONTENTMANAGER_TABLE = "
				<tr>
					<td class='forumheader3'>{CONTENT_CONTENTMANAGER_CATEGORY}</td>
					<td class='forumheader3' style='width:10%; white-space:nowrap; text-align:center;'>{CONTENT_CONTENTMANAGER_ICONEDIT} {CONTENT_CONTENTMANAGER_ICONNEW}</td>
				</tr>";
}
if(!$CONTENT_CONTENTMANAGER_TABLE_END){
				$CONTENT_CONTENTMANAGER_TABLE_END = "
				</table>
				</div>";
}
// ##### ----------------------------------------------------------------------

?>