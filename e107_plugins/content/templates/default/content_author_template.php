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
|     $Revision: 1.8 $
|     $Date: 2005-07-11 07:47:22 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
global $sc_style, $content_shortcodes;

$sc_style['CONTENT_AUTHOR_TABLE_LASTITEM']['pre'] = "<tr><td class='forumheader3'>".CONTENT_LAN_55." ";
$sc_style['CONTENT_AUTHOR_TABLE_LASTITEM']['post'] = "</td></tr>";

$sc_style['CONTENT_AUTHOR_TABLE_TOTAL']['pre'] = "(";
$sc_style['CONTENT_AUTHOR_TABLE_TOTAL']['post'] = ")";

// ##### CONTENT AUTHOR -------------------------------------------------------
if(!isset($CONTENT_AUTHOR_TABLE_START)){
	$CONTENT_AUTHOR_TABLE_START = "
	<table class='fborder' style='width:98%; text-align:left;'>\n";
}
if(!isset($CONTENT_AUTHOR_TABLE)){
	$CONTENT_AUTHOR_TABLE = "
	<tr>
		<td class='fcaption'>{CONTENT_AUTHOR_TABLE_ICON} {CONTENT_AUTHOR_TABLE_NAME} {CONTENT_AUTHOR_TABLE_TOTAL}</td>
	</tr>
	{CONTENT_AUTHOR_TABLE_LASTITEM}
	";
}
if(!isset($CONTENT_AUTHOR_TABLE_END)){
	$CONTENT_AUTHOR_TABLE_END = "
	</table>\n";
}
// ##### ----------------------------------------------------------------------

?>