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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/default/content_top_template.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-04-10 14:34:39 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

global $sc_style, $content_shortcodes;

$sc_style['CM_ICON|top']['pre'] = "<td class='forumheader3' rowspan='3' style='width:5%; white-space:nowrap;'>";
$sc_style['CM_ICON|top']['post'] = "</td>";

$sc_style['CM_HEADING|top']['pre'] = "";
$sc_style['CM_HEADING|top']['post'] = "";

$sc_style['CM_AUTHOR|top']['pre'] = "<tr><td class='forumheader3' colspan='2'>".CONTENT_LAN_11." ";
$sc_style['CM_AUTHOR|top']['post'] = "</td></tr>";

$sc_style['CM_RATING|top']['pre'] = "<td class='fcaption' style='width:20%; white-space:nowrap; text-align:right;'>";
$sc_style['CM_RATING|top']['post'] = "</td>";

// ##### CONTENT TOP --------------------------------------------------
if(!isset($CONTENT_TOP_TABLE_START)){
	$CONTENT_TOP_TABLE_START = "";
}
if(!isset($CONTENT_TOP_TABLE)){
	$CONTENT_TOP_TABLE = "
	<table class='fborder' style='width:98%; text-align:left; margin-bottom:5px;'>
	<tr>
		{CM_ICON|top}
		<td class='fcaption'>{CM_HEADING|top}</td>
		{CM_RATING|top}
	</tr>
	{CM_AUTHOR|top}
	</table>\n";
}
if(!isset($CONTENT_TOP_TABLE_END)){
	$CONTENT_TOP_TABLE_END = "";
}
// ##### ----------------------------------------------------------------------

?>