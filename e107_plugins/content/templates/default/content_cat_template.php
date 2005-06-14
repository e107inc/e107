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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_cat_template.php,v $
|     $Revision: 1.15 $
|     $Date: 2005-06-14 08:34:03 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$CONTENT_CAT_TABLE_START = "";
$CONTENT_CAT_TABLE = "";
$CONTENT_CAT_TABLE_END = "";
$CONTENT_CAT_LIST_TABLE = "";
$CONTENT_CAT_LISTSUB_TABLE_START = "";
$CONTENT_CAT_LISTSUB_TABLE = "";
$CONTENT_CAT_LISTSUB_TABLE_END = "";

global $sc_style, $content_shortcodes, $content_cat_icon_path_large;
global $aa, $qs, $row, $content_pref, $datestamp, $tp, $comment_total, $gen, $authordetails, $rater, $crumb, $amount;
global $CONTENT_CAT_TABLE, $CONTENT_CAT_TABLE_INFO_PRE, $CONTENT_CAT_TABLE_INFO_POST;
global $CONTENT_CAT_LIST_TABLE_INFO_PRE, $CONTENT_CAT_LIST_TABLE_INFO_POST;

$sc_style['CONTENT_CAT_TABLE_ICON']['pre'] = "<td class='forumheader3' rowspan='5' style='width:5%; white-space:nowrap;'>";
$sc_style['CONTENT_CAT_TABLE_ICON']['post'] = "</td>";

$sc_style['CONTENT_CAT_TABLE_AUTHORDETAILS']['pre'] = " ";
$sc_style['CONTENT_CAT_TABLE_AUTHORDETAILS']['post'] = " ";

$sc_style['CONTENT_CAT_TABLE_SUBHEADING']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CAT_TABLE_SUBHEADING']['post'] = "<br /></td></tr>";

$sc_style['CONTENT_CAT_TABLE_TEXT']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CAT_TABLE_TEXT']['post'] = "<br /></td></tr>";

$sc_style['CONTENT_CAT_TABLE_RATING']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CAT_TABLE_RATING']['post'] = "<br /></td></tr>";

$sc_style['CONTENT_CAT_TABLE_AMOUNT']['pre'] = "(";
$sc_style['CONTENT_CAT_TABLE_AMOUNT']['post'] = ")";

$sc_style['CONTENT_CAT_TABLE_INFO_PRE']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CAT_TABLE_INFO_PRE']['post'] = "";

$sc_style['CONTENT_CAT_TABLE_INFO_POST']['pre'] = "";
$sc_style['CONTENT_CAT_TABLE_INFO_POST']['post'] = "</td></tr>";

// ##### CONTENT CAT ----------------------------------------------------------
if(!$CONTENT_CAT_TABLE_START){
				$CONTENT_CAT_TABLE_START = "";
}
if(!$CONTENT_CAT_TABLE){
				$CONTENT_CAT_TABLE = "
				<table class='fborder' style='width:98%; text-align:left; margin-bottom:5px;'>
				<tr>
					{CONTENT_CAT_TABLE_ICON}
					<td class='fcaption' >{CONTENT_CAT_TABLE_HEADING} {CONTENT_CAT_TABLE_AMOUNT}</td>
				</tr>

				{CONTENT_CAT_TABLE_INFO_PRE}
					{CONTENT_CAT_TABLE_DATE} {CONTENT_CAT_TABLE_AUTHORDETAILS} {CONTENT_CAT_TABLE_EPICONS} {CONTENT_CAT_TABLE_COMMENT}
				{CONTENT_CAT_TABLE_INFO_POST}

				{CONTENT_CAT_TABLE_SUBHEADING}
				{CONTENT_CAT_TABLE_TEXT}
				{CONTENT_CAT_TABLE_RATING}
				</table>\n";

}
if(!$CONTENT_CAT_TABLE_END){
				$CONTENT_CAT_TABLE_END = "";
}
// ##### ----------------------------------------------------------------------



$sc_style['CONTENT_CAT_LIST_TABLE_ICON']['pre'] = "<td class='forumheader3' style='width:5%; white-space:nowrap; padding-right:5px; ' rowspan='5'>";
$sc_style['CONTENT_CAT_LIST_TABLE_ICON']['post'] = "</td>";

$sc_style['CONTENT_CAT_LIST_TABLE_SUBHEADING']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CAT_LIST_TABLE_SUBHEADING']['post'] = "</td></tr>";

$sc_style['CONTENT_CAT_LIST_TABLE_AUTHORDETAILS']['pre'] = " / ";
$sc_style['CONTENT_CAT_LIST_TABLE_AUTHORDETAILS']['post'] = "";

$sc_style['CONTENT_CAT_LIST_TABLE_RATING']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CAT_LIST_TABLE_RATING']['post'] = "</td></tr>";

$sc_style['CONTENT_CAT_LIST_TABLE_TEXT']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CAT_LIST_TABLE_TEXT']['post'] = "</td></tr>";

$sc_style['CONTENT_CAT_LIST_TABLE_AMOUNT']['pre'] = "(";
$sc_style['CONTENT_CAT_LIST_TABLE_AMOUNT']['post'] = ")";

$sc_style['CONTENT_CAT_LIST_TABLE_INFO_PRE']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CAT_LIST_TABLE_INFO_PRE']['post'] = "";

$sc_style['CONTENT_CAT_LIST_TABLE_INFO_POST']['pre'] = "";
$sc_style['CONTENT_CAT_LIST_TABLE_INFO_POST']['post'] = "</td></tr>";

// ##### CONTENT CAT_LIST -----------------------------------------------------
if(!$CONTENT_CAT_LIST_TABLE){
		$CONTENT_CAT_LIST_TABLE = "
		<table class='fborder' style='width:98%; text-align:left; margin-bottom:10px;'>
		<tr>
			{CONTENT_CAT_LIST_TABLE_ICON}
			<td class='fcaption'>{CONTENT_CAT_LIST_TABLE_HEADING} {CONTENT_CAT_LIST_TABLE_AMOUNT}</td>
		</tr>
		{CONTENT_CAT_LIST_TABLE_SUBHEADING}
		
		{CONTENT_CAT_LIST_TABLE_INFO_PRE}
			{CONTENT_CAT_LIST_TABLE_DATE} {CONTENT_CAT_LIST_TABLE_AUTHORDETAILS} {CONTENT_CAT_LIST_TABLE_EPICONS} {CONTENT_CAT_LIST_TABLE_COMMENT}
		{CONTENT_CAT_LIST_TABLE_INFO_POST}

		{CONTENT_CAT_LIST_TABLE_RATING}
		{CONTENT_CAT_LIST_TABLE_TEXT}
		</table>\n";
}
// ##### ----------------------------------------------------------------------



$sc_style['CONTENT_CAT_LISTSUB_TABLE_ICON']['pre'] = "<td class='forumheader3' style='width:2%; white-space:nowrap; padding-right:5px; ' rowspan='2'>";
$sc_style['CONTENT_CAT_LISTSUB_TABLE_ICON']['post'] = "</td>";

$sc_style['CONTENT_CAT_LISTSUB_TABLE_SUBHEADING']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_CAT_LISTSUB_TABLE_SUBHEADING']['post'] = "</td></tr>";

$sc_style['CONTENT_CAT_LISTSUB_TABLE_AMOUNT']['pre'] = "(";
$sc_style['CONTENT_CAT_LISTSUB_TABLE_AMOUNT']['post'] = ")";

// ##### CONTENT CAT_LIST SUB -------------------------------------------------
if(!$CONTENT_CAT_LISTSUB_TABLE_START){
				$CONTENT_CAT_LISTSUB_TABLE_START = "";
}
if(!$CONTENT_CAT_LISTSUB_TABLE){
				$CONTENT_CAT_LISTSUB_TABLE = "
				<table class='fborder' style='width:98%; text-align:left; margin-bottom:5px;'>
				<tr>
					{CONTENT_CAT_LISTSUB_TABLE_ICON}
					<td class='fcaption'>{CONTENT_CAT_LISTSUB_TABLE_HEADING} {CONTENT_CAT_LISTSUB_TABLE_AMOUNT}</td>
				</tr>
				{CONTENT_CAT_LISTSUB_TABLE_SUBHEADING}
				</table>\n";

}
if(!$CONTENT_CAT_LISTSUB_TABLE_END){
				$CONTENT_CAT_LISTSUB_TABLE_END = "<br />";
}
// ##### ----------------------------------------------------------------------

?>