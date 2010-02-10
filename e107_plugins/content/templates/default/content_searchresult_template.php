<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/default/content_searchresult_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

global $sc_style, $content_shortcodes;

$sc_style['CM_ICON|searchresult']['pre'] = "<td class='forumheader3'>";
$sc_style['CM_ICON|searchresult']['post'] = "</td>";

$sc_style['CM_HEADING|searchresult']['pre'] = "<tr><td class='fcaption'>";
$sc_style['CM_HEADING|searchresult']['post'] = "</td></tr>";

$sc_style['CM_SUBHEADING|searchresult']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_SUBHEADING|searchresult']['post'] = "</td></tr>";

$sc_style['CM_DATE|searchresult']['pre'] = CONTENT_LAN_10." ";
$sc_style['CM_DATE|searchresult']['post'] = "";

$sc_style['CM_AUTHOR|searchresult']['pre'] = CONTENT_LAN_11." ";
$sc_style['CM_AUTHOR|searchresult']['post'] = "";

$sc_style['CM_TEXT|searchresult']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_TEXT|searchresult']['post'] = "</td></tr>";

// ##### CONTENT SEARCHRESULT LIST --------------------------------------------------
if(!isset($CONTENT_SEARCHRESULT_TABLE_START)){
	$CONTENT_SEARCHRESULT_TABLE_START = "";
}
if(!isset($CONTENT_SEARCHRESULT_TABLE)){
	$CONTENT_SEARCHRESULT_TABLE .= "
	<table class='fborder' style='width:98%; text-align:left;margin-bottom:5px;'>
		<tr>
			{CM_ICON|searchresult}
			<td>
				<table style='width:100%;' cellpadding='0' cellspacing='0'>
					{CM_HEADING|searchresult}
					{CM_SUBHEADING|searchresult}
					<tr><td class='forumheader3'>{CM_AUTHOR|searchresult} {CM_DATE|searchresult}</td></tr>
					{CM_TEXT|searchresult}
				</table>
			</td>
		</tr>
	</table>\n";
}
if(!isset($CONTENT_SEARCHRESULT_TABLE_END)){
	$CONTENT_SEARCHRESULT_TABLE_END .= "";
}
// ##### ----------------------------------------------------------------------

?>