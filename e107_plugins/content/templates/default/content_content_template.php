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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_content_template.php,v $
|     $Revision: 1.11 $
|     $Date: 2005-06-07 19:37:24 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT CONTENT ------------------------------------------------------
$CONTENT_CONTENT_TABLE_START = "";
$CONTENT_CONTENT_TABLE = "";
$CONTENT_CONTENT_TABLE_END = "";
$CONTENT_CONTENT_TABLE_CUSTOM = "";
$CONTENT_CONTENT_TABLE_CUSTOM_PRE = "";
$CONTENT_CONTENT_TABLE_CUSTOM_PRE2 = "";
global $sc_style, $content_shortcodes, $qs, $row, $content_pref, $gen, $tp, $sql, $plugintable, $rater, $aa, $content_image_path, $content_icon_path, $content_file_path, $custom;

$sc_style['CONTENT_CONTENT_TABLE_REFER']['pre'] = "(".CONTENT_LAN_44." ";
$sc_style['CONTENT_CONTENT_TABLE_REFER']['post'] = ")";

$sc_style['CONTENT_CONTENT_TABLE_COMMENT']['pre'] = "<br />".CONTENT_LAN_57." ";
$sc_style['CONTENT_CONTENT_TABLE_COMMENT']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_SCORE']['pre'] = "<br />".CONTENT_LAN_45." ";
$sc_style['CONTENT_CONTENT_TABLE_SCORE']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_RATING']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CONTENT_TABLE_RATING']['post'] = "</td></tr>";

$sc_style['CONTENT_CONTENT_TABLE_AUTHORDETAILS']['pre'] = "<br />".CONTENT_LAN_11." ";
$sc_style['CONTENT_CONTENT_TABLE_AUTHORDETAILS']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_ICON']['pre'] = "<td class='forumheader3' rowspan='4' style='vertical-align:top; width:10%; white-space:nowrap;'>";
$sc_style['CONTENT_CONTENT_TABLE_ICON']['post'] = "</td>";

$sc_style['CONTENT_CONTENT_TABLE_PAGENAMES']['pre'] = "<tr><td class='forumheader3' colspan='2'>".CONTENT_LAN_46."<br />";
$sc_style['CONTENT_CONTENT_TABLE_PAGENAMES']['post'] = "</td></tr>";

$sc_style['CONTENT_CONTENT_TABLE_CUSTOM_TAGS']['pre'] = "";
$sc_style['CONTENT_CONTENT_TABLE_CUSTOM_TAGS']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_SUMMARY']['pre'] = "<tr><td class='forumheader3' colspan='2'>";
$sc_style['CONTENT_CONTENT_TABLE_SUMMARY']['post'] = "</td></tr>";

$sc_style['CONTENT_CONTENT_TABLE_TEXT']['pre'] = "<tr><td class='forumheader3' colspan='2'>";
$sc_style['CONTENT_CONTENT_TABLE_TEXT']['post'] = "</td></tr>";

$sc_style['CONTENT_CONTENT_TABLE_IMAGES']['pre'] = "<td class='forumheader3' rowspan='4'>";
$sc_style['CONTENT_CONTENT_TABLE_IMAGES']['post'] = "</td>";

$sc_style['CONTENT_CONTENT_TABLE_SUBHEADING']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CONTENT_TABLE_SUBHEADING']['post'] = "</td></tr>";

$sc_style['CONTENT_CONTENT_TABLE_FILE']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CONTENT_TABLE_FILE']['post'] = "</td></tr>";

$sc_style['CONTENT_CONTENT_TABLE_DATE']['pre'] = CONTENT_LAN_10." ";
$sc_style['CONTENT_CONTENT_TABLE_DATE']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_PARENT']['pre'] = "<br />".CONTENT_LAN_9." ";
$sc_style['CONTENT_CONTENT_TABLE_PARENT']['post'] = "";

if(!$CONTENT_CONTENT_TABLE){
				$CONTENT_CONTENT_TABLE .= "
				<table class='fborder' style='width:98%;'>
				<tr>
					{CONTENT_CONTENT_TABLE_ICON}
					<td class='fcaption'>{CONTENT_CONTENT_TABLE_HEADING} {CONTENT_CONTENT_TABLE_REFER}</td>
				</tr>
				{CONTENT_CONTENT_TABLE_SUBHEADING}
				<tr><td class='forumheader3'>{CONTENT_CONTENT_TABLE_DATE} {CONTENT_CONTENT_TABLE_AUTHORDETAILS} {CONTENT_CONTENT_TABLE_EPICONS} {CONTENT_CONTENT_TABLE_EDITICON} {CONTENT_CONTENT_TABLE_PARENT} {CONTENT_CONTENT_TABLE_COMMENT} {CONTENT_CONTENT_TABLE_SCORE}</td></tr>
				{CONTENT_CONTENT_TABLE_RATING}
				{CONTENT_CONTENT_TABLE_FILE}
				</table><br />
				<table class='fborder' style='width:98%;'>
				{CONTENT_CONTENT_TABLE_SUMMARY}
				{CONTENT_CONTENT_TABLE_IMAGES}
				{CONTENT_CONTENT_TABLE_TEXT}
				{CONTENT_CONTENT_TABLE_CUSTOM_TAGS}
				{CONTENT_CONTENT_TABLE_PAGENAMES}
				</table>\n";
}
// ##### ----------------------------------------------------------------------

if(!$CONTENT_CONTENT_TABLE_CUSTOM){
	$CONTENT_CONTENT_TABLE_CUSTOM = "
	<tr>
		<td class='forumheader3' style='width:5%;'>
			{CONTENT_CONTENT_TABLE_CUSTOM_KEY}
		</td>
		<td class='forumheader3' style='width:95%;'>
			{CONTENT_CONTENT_TABLE_CUSTOM_VALUE}
		</td>
	</tr>";
}

?>