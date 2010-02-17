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
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
global $sc_style, $content_shortcodes;

// ##### CONTENT CONTENTMANAGER LIST --------------------------------------------------
if(!isset($CONTENT_CONTENTMANAGER_TABLE_START)){
	$CONTENT_CONTENTMANAGER_TABLE_START = "
	<table class='fborder' style='width:98%; text-align:left;' cellpadding='0' cellspacing='0'>
	<tr>
		<td class='forumheader3'>".CONTENT_ADMIN_ITEM_LAN_57."</td>
		<td class='forumheader3'>".CONTENT_ADMIN_ITEM_LAN_12."</td>
	</tr>\n";
}
if(!isset($CONTENT_CONTENTMANAGER_TABLE)){
	$CONTENT_CONTENTMANAGER_TABLE = "
	<tr>
		<td class='forumheader3'>{CONTENT_CONTENTMANAGER_CATEGORY}</td>
		<td class='forumheader3' style='width:10%;white-space:nowrap;'>{CONTENT_CONTENTMANAGER_ICONEDIT} {CONTENT_CONTENTMANAGER_ICONNEW} {CONTENT_CONTENTMANAGER_ICONSUBM}</td>
	</tr>";
}

if(!isset($CONTENT_CONTENTMANAGER_TABLE_END)){
	$CONTENT_CONTENTMANAGER_TABLE_END = "
	</table>";
}
// ##### ----------------------------------------------------------------------

?>