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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/default/content_recent_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-04-10 14:34:39 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT RECENT LIST --------------------------------------------------
global $sc_style, $content_shortcodes;

$sc_style['CM_ICON|recent']['pre'] = "<td class='forumheader3' rowspan='7' style='vertical-align:top; width:10%; white-space:nowrap; padding-right:10px;'>";
$sc_style['CM_ICON|recent']['post'] = "</td>";

$sc_style['CM_DATE|recent']['pre'] = CONTENT_LAN_10." ";
$sc_style['CM_DATE|recent']['post'] = "";

$sc_style['CM_PARENT|recent']['pre'] = CONTENT_LAN_9." ";
$sc_style['CM_PARENT|recent']['post'] = "";

$sc_style['CM_REFER|recent']['pre'] = " (".CONTENT_LAN_44." ";
$sc_style['CM_REFER|recent']['post'] = ")";

$sc_style['CM_AUTHOR|recent']['pre'] = CONTENT_LAN_11." ";
$sc_style['CM_AUTHOR|recent']['post'] = "";

$sc_style['CM_SUBHEADING|recent']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_SUBHEADING|recent']['post'] = "</td></tr>";

$sc_style['CM_SUMMARY|recent']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_SUMMARY|recent']['post'] = "</td></tr>";

$sc_style['CM_TEXT|recent']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_TEXT|recent']['post'] = "</td></tr>";

$sc_style['CM_RATING|recent']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CM_RATING|recent']['post'] = "</td></tr>";

$sc_style['CONTENT_RECENT_TABLE_INFOPRE']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_RECENT_TABLE_INFOPRE']['post'] = "";

$sc_style['CONTENT_RECENT_TABLE_INFOPOST']['pre'] = "";
$sc_style['CONTENT_RECENT_TABLE_INFOPOST']['post'] = "</td></tr>";

if(!isset($CONTENT_RECENT_TABLE_START)){
	$CONTENT_RECENT_TABLE_START = "";
}
if(!isset($CONTENT_RECENT_TABLE)){
	$CONTENT_RECENT_TABLE = "
	<table class='fborder' style='width:98%; text-align:left;margin-bottom:5px;'>
		<tr>
			{CM_ICON|recent}
			<td class='fcaption'>{CM_HEADING|recent} {CM_REFER|recent}</td>
		</tr>
		{CM_SUBHEADING|recent}
		
		{CONTENT_RECENT_TABLE_INFOPRE}
			{CM_DATE|recent} {CM_AUTHOR|recent} {CM_PARENT|recent} {CM_EPICONS|recent} {CM_EDITICON|recent}
		{CONTENT_RECENT_TABLE_INFOPOST}

		{CM_SUMMARY|recent}
		{CM_TEXT|recent}
		{CM_RATING|recent}
	</table>\n";
}
if(!isset($CONTENT_RECENT_TABLE_END)){
	$CONTENT_RECENT_TABLE_END = "";
}
// ##### ----------------------------------------------------------------------

?>