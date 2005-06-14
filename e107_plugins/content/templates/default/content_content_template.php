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
|     $Revision: 1.15 $
|     $Date: 2005-06-14 10:50:47 $
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
global $sc_style, $content_shortcodes, $qs, $row, $content_pref, $array, $gen, $tp, $sql, $plugintable, $rater, $aa, $content_image_path, $content_icon_path, $content_file_path, $custom;

$sc_style['CONTENT_CONTENT_TABLE_REFER']['pre'] = "<br />".CONTENT_LAN_44." ";
$sc_style['CONTENT_CONTENT_TABLE_REFER']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_COMMENT']['pre'] = "<br />".CONTENT_LAN_57." ";
$sc_style['CONTENT_CONTENT_TABLE_COMMENT']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_SCORE']['pre'] = "<br />".CONTENT_LAN_45." ";
$sc_style['CONTENT_CONTENT_TABLE_SCORE']['post'] = "/100";

$sc_style['CONTENT_CONTENT_TABLE_RATING']['pre'] = "<br />";
$sc_style['CONTENT_CONTENT_TABLE_RATING']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_AUTHORDETAILS']['pre'] = "<br />".CONTENT_LAN_11." ";
$sc_style['CONTENT_CONTENT_TABLE_AUTHORDETAILS']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_ICON']['pre'] = "<div style='float:left; padding-right:10px;'>";
$sc_style['CONTENT_CONTENT_TABLE_ICON']['post'] = "</div>";

$sc_style['CONTENT_CONTENT_TABLE_PAGENAMES']['pre'] = "<br /><div>".CONTENT_LAN_46."<br />";
$sc_style['CONTENT_CONTENT_TABLE_PAGENAMES']['post'] = "</div>";

$sc_style['CONTENT_CONTENT_TABLE_CUSTOM_TAGS']['pre'] = "";
$sc_style['CONTENT_CONTENT_TABLE_CUSTOM_TAGS']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_SUMMARY']['pre'] = "<div>";
$sc_style['CONTENT_CONTENT_TABLE_SUMMARY']['post'] = "<br /><br /></div>";

$sc_style['CONTENT_CONTENT_TABLE_TEXT']['pre'] = "<div>";
$sc_style['CONTENT_CONTENT_TABLE_TEXT']['post'] = "</div>";

$sc_style['CONTENT_CONTENT_TABLE_IMAGES']['pre'] = "<div style='float:left; padding-right:10px;'>";
$sc_style['CONTENT_CONTENT_TABLE_IMAGES']['post'] = "</div>";

$sc_style['CONTENT_CONTENT_TABLE_SUBHEADING']['pre'] = "";
$sc_style['CONTENT_CONTENT_TABLE_SUBHEADING']['post'] = "<br />";

$sc_style['CONTENT_CONTENT_TABLE_FILE']['pre'] = "<br />";
$sc_style['CONTENT_CONTENT_TABLE_FILE']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_DATE']['pre'] = CONTENT_LAN_10." ";
$sc_style['CONTENT_CONTENT_TABLE_DATE']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_PARENT']['pre'] = "<br />".CONTENT_LAN_9." ";
$sc_style['CONTENT_CONTENT_TABLE_PARENT']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_INFO_PRE']['pre'] = "<div style='clear:both;'><div style='float:left;'>";
$sc_style['CONTENT_CONTENT_TABLE_INFO_PRE']['post'] = "";
$sc_style['CONTENT_CONTENT_TABLE_INFO_POST']['pre'] = "";
$sc_style['CONTENT_CONTENT_TABLE_INFO_POST']['post'] = "</div></div>";

if(!$CONTENT_CONTENT_TABLE){
				$CONTENT_CONTENT_TABLE .= "
				<div style='clear:both;'>

					{CONTENT_CONTENT_TABLE_INFO_PRE}
						{CONTENT_CONTENT_TABLE_ICON}
						{CONTENT_CONTENT_TABLE_SUBHEADING}
						{CONTENT_CONTENT_TABLE_DATE} {CONTENT_CONTENT_TABLE_AUTHORDETAILS} {CONTENT_CONTENT_TABLE_EPICONS} {CONTENT_CONTENT_TABLE_EDITICON} {CONTENT_CONTENT_TABLE_PARENT} {CONTENT_CONTENT_TABLE_COMMENT} {CONTENT_CONTENT_TABLE_SCORE} {CONTENT_CONTENT_TABLE_REFER}
						{CONTENT_CONTENT_TABLE_RATING}
						{CONTENT_CONTENT_TABLE_FILE}
					{CONTENT_CONTENT_TABLE_INFO_POST}
					<div style='clear:both;'><br /></div>
				
					{CONTENT_CONTENT_TABLE_IMAGES}
					{CONTENT_CONTENT_TABLE_SUMMARY}
					{CONTENT_CONTENT_TABLE_TEXT}
					{CONTENT_CONTENT_TABLE_CUSTOM_TAGS}
					{CONTENT_CONTENT_TABLE_PAGENAMES}
				</div>\n";
}
// ##### ----------------------------------------------------------------------


if(!$CONTENT_CONTENT_TABLE_CUSTOM){
	$CONTENT_CONTENT_TABLE_CUSTOM = "
	<table style='width:98%;margin-left:0;padding-left:0;' ><tr>
		<td style='width:10%;'>
			{CONTENT_CONTENT_TABLE_CUSTOM_KEY}
		</td>
		<td style='width:90%;'>
			{CONTENT_CONTENT_TABLE_CUSTOM_VALUE}
		</td>
	</tr></table>";
}

?>