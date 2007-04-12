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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/default/content_score_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-04-12 12:19:40 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

global $sc_style, $content_shortcodes;

$sc_style['CM_ICON|score']['pre'] = "<td class='forumheader3' rowspan='3' style='width:5%; white-space:nowrap;'>";
$sc_style['CM_ICON|score']['post'] = "</td>";

$sc_style['CM_HEADING|score']['pre'] = "";
$sc_style['CM_HEADING|score']['post'] = "";

$sc_style['CM_AUTHOR|score']['pre'] = "<tr><td class='forumheader3' colspan='2'>".CONTENT_LAN_11." ";
$sc_style['CM_AUTHOR|score']['post'] = "</td></tr>";

$sc_style['CM_SCORE|score']['pre'] = "<td class='fcaption' style='width:20%; white-space:nowrap; text-align:right;'>";
$sc_style['CM_SCORE|score']['post'] = "</td>";

$sc_style['CONTENT_NEXTPREV']['pre'] = "<div class='nextprev'>";
$sc_style['CONTENT_NEXTPREV']['post'] = "</div>";

// ##### CONTENT TOP --------------------------------------------------
if(!isset($CONTENT_SCORE_TABLE_START)){
	$CONTENT_SCORE_TABLE_START = "";
}
if(!isset($CONTENT_SCORE_TABLE)){
	$CONTENT_SCORE_TABLE = "
	<table class='fborder' style='width:98%; text-align:left; margin-bottom:5px;'>
	<tr>
		{CM_ICON|score}
		<td class='fcaption'>{CM_HEADING|score}</td>
		{CM_SCORE|score}
	</tr>
	{CM_AUTHOR|score}
	</table>\n";
}
if(!isset($CONTENT_SCORE_TABLE_END)){
	$CONTENT_SCORE_TABLE_END = "";
}
// ##### ----------------------------------------------------------------------

?>