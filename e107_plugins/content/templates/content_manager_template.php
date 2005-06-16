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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/content_manager_template.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-06-16 12:05:18 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
global $sc_style, $content_shortcodes, $row;

// ##### CONTENT CONTENTMANAGER LIST --------------------------------------------------
if(!$CONTENT_CONTENTMANAGER_TABLE_START){
				$CONTENT_CONTENTMANAGER_TABLE_START = "
				<table class='fborder' style='width:98%; text-align:left;' cellpadding='0' cellspacing='0'>
				<tr>
					<td class='forumheader3'>".CONTENT_ADMIN_ITEM_LAN_57."</td>
					<td class='forumheader3'>".CONTENT_ADMIN_ITEM_LAN_12."</td>
				</tr>\n";
}
if(!$CONTENT_CONTENTMANAGER_TABLE){
				$CONTENT_CONTENTMANAGER_TABLE = "
				<tr>
					<td class='forumheader3'>{CONTENT_CONTENTMANAGER_CATEGORY}</td>
					<td class='forumheader3' style='width:10%;'>{CONTENT_CONTENTMANAGER_ICONEDIT} {CONTENT_CONTENTMANAGER_ICONNEW}</td>
				</tr>";
}
if(!$CONTENT_CONTENTMANAGER_TABLE_END){
				$CONTENT_CONTENTMANAGER_TABLE_END = "
				</table>";
}
// ##### ----------------------------------------------------------------------

?>