<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/links_page/links_template.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-17 13:48:43 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

global $sc_style, $link_shortcodes;
if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:97%"); }

// ##### NEXT PREV --------------------------------------------------
if(!isset($LINK_NP_TABLE)){
	$LINK_NP_TABLE = "<div class='nextprev'>{LINK_NEXTPREV}</div>";
}
// ##### ----------------------------------------------------------------------

//general : backlink to link frontpage
$sc_style['LINK_NAVIGATOR']['pre'] = "<td style='text-align:right;'>";
$sc_style['LINK_NAVIGATOR']['post'] = "</td>";

//general : order menu
$sc_style['LINK_SORTORDER']['pre'] = "<td style='text-align:left;'>";
$sc_style['LINK_SORTORDER']['post'] = "</td>";

$sc_style['LINK_CATMENU']['pre'] = "<td style='text-align:left;'>";
$sc_style['LINK_CATMENU']['post'] = "</td>";

$sc_style['LINK_NAVIGATOR_TABLE_PRE']['pre'] = "<table cellpadding='0' cellspacing='0' style='width:100%; margin-bottom:20px;'><tr>";
$sc_style['LINK_NAVIGATOR_TABLE_PRE']['post'] = "";
$sc_style['LINK_NAVIGATOR_TABLE_POST']['pre'] = "";
$sc_style['LINK_NAVIGATOR_TABLE_POST']['post'] = "</tr></table>";

$LINK_NAVIGATOR_TABLE = "{LINK_NAVIGATOR_TABLE_PRE}{LINK_SORTORDER}{LINK_NAVIGATOR}{LINK_NAVIGATOR_TABLE_POST}";



$sc_style['LINK_MANAGE_NEWLINK']['pre'] = "<div style='text-align:right;'>";
$sc_style['LINK_MANAGE_NEWLINK']['post'] = " >></div>";

$LINK_TABLE_MANAGE_START = "
	<form method='post' action='".e_SELF."?".e_QUERY."' id='linkmanagerform' enctype='multipart/form-data'>
	<table class='fborder' style='width:100%;' cellspacing='0' cellpadding='0'>
	<tr>
	<td style='width:15%' class='fcaption'>".LAN_LINKS_MANAGER_5."</td>
	<td style='width:75%' class='fcaption'>".LAN_LINKS_MANAGER_1."</td>
	<td style='width:10%' class='fcaption'>".LAN_LINKS_MANAGER_2."</td>
	</tr>";

$LINK_TABLE_MANAGE = "
	<tr>
	<td style='width:15%; padding-bottom:5px;' class='forumheader3'>{LINK_MANAGE_CAT}</td>
	<td style='width:75%; padding-bottom:5px;' class='forumheader3'>{LINK_MANAGE_ICON} {LINK_MANAGE_NAME}</td>
	<td style='width:10%; padding-bottom:5px; text-align:center; vertical-align:top;' class='forumheader3'>{LINK_MANAGE_OPTIONS}</td>
	</tr>";

$LINK_TABLE_MANAGE_END = "</table></form><br />{LINK_MANAGE_NEWLINK}";



// MAIN TABLE -------------------------------------------------------------------------------
$sc_style['LINK_MAIN_ICON']['pre'] = "<td rowspan='2' class='forumheader3' style='width:2%; text-align:left; padding-right:10px;'>";
$sc_style['LINK_MAIN_ICON']['post'] = "</td>";

$sc_style['LINK_MAIN_HEADING']['pre'] = "";
$sc_style['LINK_MAIN_HEADING']['post'] = "";

$sc_style['LINK_MAIN_DESC']['pre'] = "<tr><td class='forumheader3' colspan='3'>";
$sc_style['LINK_MAIN_DESC']['post'] = "</td></tr>";

$sc_style['LINK_MAIN_NUMBER']['pre'] = "<td class='forumheader' style='width:8%; white-space:nowrap;'>";
$sc_style['LINK_MAIN_NUMBER']['post'] = "</td>";

$sc_style['LINK_MAIN_TOTAL']['pre'] = "";
$sc_style['LINK_MAIN_TOTAL']['post'] = "<br />";


$LINK_MAIN_TABLE_START = "
	<div style='text-align:center'>";

$LINK_MAIN_TABLE = "
	<table class='fborder' style='width:100%; margin-bottom:20px;' cellspacing='0' cellpadding='0'>
	<tr>
		{LINK_MAIN_ICON}
		<td class='fcaption'>{LINK_MAIN_HEADING}</td>
		{LINK_MAIN_NUMBER}
	</tr>
	{LINK_MAIN_DESC}
	</table>";

$LINK_MAIN_TABLE_END = "
		<div style='text-align:right;'>
		{LINK_MAIN_TOTAL}
		</div>
	</div>";

$LINK_MAIN_TABLE_START_ALL = "
	<div style='text-align:center'>";
$LINK_MAIN_TABLE_END_ALL = "
	</div>";




// LINKS ITEM ----------------------------------------------------------------------------

$sc_style['LINK_BUTTON']['pre'] = "<td class='forumheader3' style='width:5%; text-align:center'>";
$sc_style['LINK_BUTTON']['post'] = "</td>";

$sc_style['LINK_COMMENT']['pre'] = "<td class='forumheader3' style='width:5%; text-align:center'>";
$sc_style['LINK_COMMENT']['post'] = "</td>";

$sc_style['LINK_RATING']['pre'] = "<td class='forumheader3' style='width:5%; text-align:center'>";
$sc_style['LINK_RATING']['post'] = "</td>";

$sc_style['LINK_REFER']['pre'] = "<td class='forumheader3' style='width:5%; text-align:center'>";
$sc_style['LINK_REFER']['post'] = "</td>";

$sc_style['LINK_URL']['pre'] = "<span class='smalltext'>";
$sc_style['LINK_URL']['post'] = "</span>";

$sc_style['LINK_DESC']['pre'] = "<span class='smalltext'>";
$sc_style['LINK_DESC']['post'] = "</span>";

$sc_style['LINK_REFER_LAN']['pre'] = "<td class='fcaption' style='text-align:center;width:5%'>";
$sc_style['LINK_REFER_LAN']['post'] = "</td>";

$sc_style['LINK_COMMENT_LAN']['pre'] = "<td class='fcaption' style='width:5%'>";
$sc_style['LINK_COMMENT_LAN']['post'] = "</td>";

$sc_style['LINK_RATING_LAN']['pre'] = "<td class='fcaption' style='width:5%'>";
$sc_style['LINK_RATING_LAN']['post'] = "</td>";

$sc_style['LINK_BUTTON_LAN']['pre'] = "<td class='fcaption' style='width:5%'>";
$sc_style['LINK_BUTTON_LAN']['post'] = "</td>";

$sc_style['LINK_CAT_DESC']['pre'] = "<br /><span class='smalltext'><i>";
$sc_style['LINK_CAT_DESC']['post'] = "</i></span>";

//$LINK_TABLE_CAPTION = LCLAN_ITEM_24."{NAVIGATOR}" ;
$LINK_TABLE_CAPTION = LCLAN_ITEM_24;

$LINK_TABLE_START = "{NAVIGATOR}
	<div style='text-align:center'>
	<table class='fborder' style='".USER_WIDTH.";margin-bottom:20px;'>
	<tr>
	<td colspan='{BUTTON_COLUMN}' class='fcaption'>".LAN_LINKS_32." {LINK_CAT_NAME} {LINK_CAT_TOTAL} {LINK_CAT_DESC} </td>
   	{LINK_RATING_LAN}
	{LINK_COMMENT_LAN}
	{LINK_REFER_LAN}
	</tr>";

$LINK_TABLE = "

	<tr>
		{LINK_BUTTON}
		<td class='forumheader3' style='width:60%'>
			{LINK_NEW} {LINK_APPEND} {LINK_NAME} </a><br />
    {LINK_URL=link}
	{LINK_DESC}

		</td>
    	{LINK_RATING}
	{LINK_COMMENT}
	{LINK_REFER}

	</tr>
	";

$LINK_TABLE_END = "
	</table></div>";



// RATED -----------------------------------------------------------------------------------
$sc_style['LINK_RATED_BUTTON']['pre'] = "<td rowspan='5' class='forumheader3' style='width:10%; text-align:center; padding-right:5px;'>";
$sc_style['LINK_RATED_BUTTON']['post'] = "</td>";

$sc_style['LINK_RATED_NAME']['pre'] = "";
$sc_style['LINK_RATED_NAME']['post'] = "";

$sc_style['LINK_RATED_URL']['pre'] = "<tr><td colspan='2' class='forumheader2' style='line-height:130%;'><i>";
$sc_style['LINK_RATED_URL']['post'] = "</i></td></tr>";

$sc_style['LINK_RATED_REFER']['pre'] = "<td class='forumheader' style='white-space:nowrap;'>";
$sc_style['LINK_RATED_REFER']['post'] = "</td>";

$sc_style['LINK_RATED_DESC']['pre'] = "<tr><td colspan='2' class='forumheader3' style='line-height:130%;'>";
$sc_style['LINK_RATED_DESC']['post'] = "</td></tr>";

$sc_style['LINK_RATED_RATING']['pre'] = "<td colspan='2' class='forumheader' style='line-height:130%; width:25%; white-space:nowrap; text-align:right;'>";
$sc_style['LINK_RATED_RATING']['post'] = "</td>";

$sc_style['LINK_RATED_CATEGORY']['pre'] = "<tr><td colspan='2' class='forumheader2' style='line-height:130%;'><i>";
$sc_style['LINK_RATED_CATEGORY']['post'] = "</i></td></tr>";

$LINK_RATED_TABLE_START = "
	<div style='text-align:center'>
	";

$LINK_RATED_TABLE = "
	<table class='fborder' style='width:100%; margin-bottom:20px;' cellspacing='0' cellpadding='0'>
	<tr>
		{LINK_RATED_BUTTON}
		<td class='fcaption' style='width:75%;'>
			{LINK_RATED_APPEND} {LINK_RATED_NAME} </a>
		</td>
		{LINK_RATED_RATING}
	</tr>
	{LINK_RATED_URL}
	{LINK_RATED_CATEGORY}
	{LINK_RATED_DESC}
	</table>";

$LINK_RATED_TABLE_END = "
	</div>";


$sc_style['LINK_SUBMIT_PRETEXT']['pre'] = "<tr><td colspan='2' style='text-align:center' class='forumheader2'>";
$sc_style['LINK_SUBMIT_PRETEXT']['post'] = "</td></tr>";

// SUBMIT -----------------------------------------------------------------------------------
$LINK_SUBMIT_TABLE = "
	<div style='text-align:center'>
	<form method='post' action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."'>
	<table class='fborder' style='width:100%' cellspacing='0' cellpadding='0'>
	{LINK_SUBMIT_PRETEXT}
	<tr>
		<td class='forumheader3' style='width:30%'>".LCLAN_SL_10."</td>
		<td class='forumheader3' style='width:70%'>{LINK_SUBMIT_CAT}</td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:30%'><u>".LCLAN_SL_11."</u></td>
		<td class='forumheader3' style='width:30%'><input class='tbox' type='text' name='link_name' size='60' value='' maxlength='100' /></td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:30%'><u>".LCLAN_SL_12."</u></td>
		<td class='forumheader3' style='width:30%'><input class='tbox' type='text' name='link_url' size='60' value='' maxlength='200' /></td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:30%'><u>".LCLAN_SL_13."</u></td>
		<td class='forumheader3' style='width:30%'><textarea class='tbox' name='link_description' cols='59' rows='3'></textarea></td>
	</tr>
	<tr>
		<td class='forumheader3' style='width:30%'>".LCLAN_SL_14."</td>
		<td class='forumheader3' style='width:30%'><input class='tbox' type='text' name='link_but' size='60' value='' maxlength='200' /></td>
	</tr>
	<tr>
		<td colspan='2' style='text-align:center' class='forumheader3'><span class='smalltext'>".LCLAN_SL_15."</span></td>
	</tr>
	<tr>
		<td colspan='2' style='text-align:center' class='forumheader'><input class='button' type='submit' name='add_link' value='".LCLAN_SL_16."' /></td>
	</tr>
	</table>
	</form>
	</div>
	";


?>