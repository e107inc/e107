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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/links_template.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-06-26 20:16:57 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// MAIN TABLE -------------------------------------------------------------------------------
global $sc_style, $link_shortcodes;

$sc_style['LINK_MAIN_ICON']['pre'] = "<td rowspan='2' class='forumheader3' style='width:5%; text-align:center; padding-right:5px;'>";
$sc_style['LINK_MAIN_ICON']['post'] = "</td>";

$sc_style['LINK_MAIN_HEADING']['pre'] = "";
$sc_style['LINK_MAIN_HEADING']['post'] = "";

$sc_style['LINK_MAIN_DESC']['pre'] = "<tr><td class='forumheader3' colspan='2'>";
$sc_style['LINK_MAIN_DESC']['post'] = "</td></tr>";

$sc_style['LINK_MAIN_NUMBER']['pre'] = "<td class='forumheader' style='width:30%; white-space:nowrap;'>";
$sc_style['LINK_MAIN_NUMBER']['post'] = "</td>";

$sc_style['LINK_MAIN_TOTAL']['pre'] = "";
$sc_style['LINK_MAIN_TOTAL']['post'] = "<br />";

$sc_style['LINK_MAIN_SHOWALL']['pre'] = "";
$sc_style['LINK_MAIN_SHOWALL']['post'] = "<br />";

$sc_style['LINK_MAIN_TOPREFER']['pre'] = "";
$sc_style['LINK_MAIN_TOPREFER']['post'] = "<br />";

$sc_style['LINK_MAIN_TOPRATED']['pre'] = "";
$sc_style['LINK_MAIN_TOPRATED']['post'] = "<br />";


$LINK_MAIN_TABLE_START = "
	<div style='text-align:center'>";

$LINK_MAIN_TABLE = "
	<table class='fborder' style='width:100%; margin-bottom:20px;' cellspacing='0' cellpadding='0'>
	<tr>
		{LINK_MAIN_ICON}
		<td class='fcaption' style='width:90%'>{LINK_MAIN_HEADING}</td>
		{LINK_MAIN_NUMBER}
	</tr>
	{LINK_MAIN_DESC}
	</table>";

$LINK_MAIN_TABLE_END = "
		<div class='smalltext' style='text-align:right;'>
		{LINK_MAIN_TOTAL}
		{LINK_MAIN_SHOWALL}
		{LINK_MAIN_TOPREFER}
		{LINK_MAIN_TOPRATED}
		</div>
	</div>";


// CATEGORY LIST ----------------------------------------------------------------------------
global $sc_style, $link_shortcodes;

//general backlink to link frontpage
$sc_style['LINK_BACKLINK']['pre'] = "<div class='smalltext' style='text-align:right;'>";
$sc_style['LINK_BACKLINK']['post'] = "</div>";

$sc_style['LINK_CAT_SORTORDER']['pre'] = "<table class='fborder' style='width:100%' cellspacing='0' cellpadding='0'><tr><td class='forumheader' colspan='3'>";
$sc_style['LINK_CAT_SORTORDER']['post'] = "</td></tr></table><br />";

$sc_style['LINK_CAT_BUTTON']['pre'] = "<td rowspan='4' class='forumheader3' style='width:10%; text-align:center; padding-right:5px;'>";
$sc_style['LINK_CAT_BUTTON']['post'] = "</td>";

$sc_style['LINK_CAT_NAME']['pre'] = "";
$sc_style['LINK_CAT_NAME']['post'] = "";

$sc_style['LINK_CAT_URL']['pre'] = "<tr><td colspan='2' class='forumheader2' style='line-height:130%;'><i>";
$sc_style['LINK_CAT_URL']['post'] = "</i></td></tr>";

$sc_style['LINK_CAT_REFER']['pre'] = "<td class='forumheader' style='white-space:nowrap;'>";
$sc_style['LINK_CAT_REFER']['post'] = "</td>";

$sc_style['LINK_CAT_DESC']['pre'] = "<tr><td colspan='2' class='forumheader3' style='line-height:130%;'>";
$sc_style['LINK_CAT_DESC']['post'] = "</td></tr>";

$sc_style['LINK_CAT_RATING']['pre'] = "<tr><td colspan='2' class='forumheader' style='line-height:130%;'>";
$sc_style['LINK_CAT_RATING']['post'] = "</td></tr>";

$sc_style['LINK_CAT_SUBMIT']['pre'] = "<div class='smalltext' style='text-align:right;'>";
$sc_style['LINK_CAT_SUBMIT']['post'] = "</div>";


$LINK_CAT_TABLE_START = "
	<div style='text-align:center'>
	{LINK_CAT_SORTORDER}";

$LINK_CAT_TABLE = "
	<table class='fborder' style='width:100%; margin-bottom:20px;' cellspacing='0' cellpadding='0'>
	<tr>
		{LINK_CAT_BUTTON}
		<td class='fcaption' style='width:90%'>
			{LINK_CAT_NEW} {LINK_CAT_APPEND} {LINK_CAT_NAME} </a>
		</td>
		{LINK_CAT_REFER}
	</tr>
	{LINK_CAT_URL}
	{LINK_CAT_DESC}
	{LINK_CAT_RATING}
	</table>";

$LINK_CAT_TABLE_END = "
	{LINK_CAT_SUBMIT}
	{LINK_BACKLINK}
	</div>";




$sc_style['LINK_RATED_BUTTON']['pre'] = "<td rowspan='4' class='forumheader3' style='width:10%; text-align:center; padding-right:5px;'>";
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

//TOP RATED LINKS
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
	{LINK_RATED_DESC}		
	</table>";

$LINK_RATED_TABLE_END = "
	{LINK_BACKLINK}
	</div>";


// SUBMIT -----------------------------------------------------------------------------------
$LINK_SUBMIT_TABLE = "
	<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table class='fborder' style='width:100%' cellspacing='0' cellpadding='0'>
	<tr><td colspan='2' style='text-align:center' class='forumheader2'>".LCLAN_SL_9."</td></tr>
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
		<td class='forumheader3' style='width:30%'><input class='tbox' type='text' name='link_button' size='60' value='' maxlength='200' /></td>
	</tr>
	<tr>
		<td colspan='2' style='text-align:center' class='forumheader3'><span class='smalltext'>".LCLAN_SL_15."</span></td>
	</tr>
	<tr>
		<td colspan='2' style='text-align:center' class='forumheader'><input class='button' type='submit' name='add_link' value='".LCLAN_SL_16."' /></td>
	</tr>
	</table>
	</form>
	</div>";


?>