<?php
// $Id$
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Banner template 
 */

if (!defined('e107_INIT')) { exit; }
if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:98%"); }

// ##### LOGIN TABLE -------------------------------------------------------------------------------
global $BANNER_LOGIN_TABLE;
if(!isset($BANNER_LOGIN_TABLE))
{
		$BANNER_LOGIN_TABLE = "
		<div style='align:center'>\n
		<form method='post' action='".e_SELF."'>\n
		<table style='width:40%'>
		<tr>
			<td style='width:15%' class='defaulttext'>".BANNERLAN_16." </td>
			<td>{BANNER_LOGIN_TABLE_LOGIN}\n</td>
		</tr>
		<tr>
			<td style='width:15%' class='defaulttext'>".LAN_PASSWORD." </td>
			<td>{BANNER_LOGIN_TABLE_PASSW}\n</td>
		</tr>
		<tr>
			<td style='width:15%'></td>
			<td>{BANNER_LOGIN_TABLE_SUBMIT}</td>
		</tr>
		</table>
		</form>
		</div>";

}
// ##### ------------------------------------------------------------------------------------------

// ##### BANNER TABLE -----------------------------------------------------------------------------
if(!isset($BANNER_TABLE_START))
{
		$BANNER_TABLE_START = "
		<table class='table fborder' style='".USER_WIDTH.";' >
		<tr><th colspan='7' style='text-align:center' class='fcaption'>".BANNERLAN_21."</th></tr>
		<tr>
			<th class='forumheader' style='text-align:center'>".BANNERLAN_22."</th>
			<th class='forumheader' style='text-align:center'>".BANNERLAN_23."</th>
			<th class='forumheader' style='text-align:center'>".BANNERLAN_24."</th>
			<th class='forumheader' style='text-align:center'>".BANNERLAN_25."</th>
			<th class='forumheader' style='text-align:center'>".BANNERLAN_26."</th>
			<th class='forumheader' style='text-align:center'>".BANNERLAN_27."</th>
			<th class='forumheader' style='text-align:center'>".BANNERLAN_28."</th>
		</tr>";
}
if(!isset($BANNER_TABLE))
{
		$BANNER_TABLE = "
		<tr>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_CLIENTNAME}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_BANNER_ID}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_BANNER_CLICKS}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_CLICKPERCENTAGE}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_BANNER_IMPRESSIONS}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_IMPRESSIONS_PURCHASED}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_IMPRESSIONS_LEFT}</td>
		</tr>
		<tr>
			<td class='forumheader3' style='text-align:center'>&nbsp;</td>
			<td colspan='2' class='forumheader3' style='text-align:center'>{BANNER_TABLE_ACTIVE}</td>
			<td colspan='4' class='forumheader3' style='text-align:center'>{BANNER_TABLE_STARTDATE} {BANNER_TABLE_ENDDATE}</td>";

			if(!empty($BANNER_TABLE_IP)) // review
			{
				$BANNER_TABLE .= "
				</tr>
				<tr>
					<td class='forumheader3'>".BANNERLAN_35.": {BANNER_TABLE_IP_LAN}</td>
					<td colspan='6' class='forumheader3'>{BANNER_TABLE_IP}</td>";
			}

		$BANNER_TABLE .= "
		</tr>
		<tr><td colspan='7'>&nbsp;</td></tr>";
}
if(!isset($BANNER_TABLE_END))
{
		$BANNER_TABLE_END = "
		</table>";
}
// ##### ------------------------------------------------------------------------------------------

// ##### BANNER MENU -----------------------------------------------------------------------------
if(!isset($BANNER_MENU_START))
{
	$BANNER_MENU_START = "<div style='text-align:center;'>";
}
if(!isset($BANNER_MENU))
{
	$BANNER_MENU = "{BANNER}<br /><br />";
}
if(!isset($BANNER_MENU_END))
{
	$BANNER_MENU_END = "</div>";
}





// ##### ------------------------------------ v2.x ------------------------------------------------------


$BANNER_TEMPLATE['menu']['start'] =		"<div class='banner-menu text-center'>{SETIMAGE: w=800}";
$BANNER_TEMPLATE['menu']['item'] =		"<div class='banner-menu-item'>{BANNER}</div>";
$BANNER_TEMPLATE['menu']['end'] = 		"</div>"; 


