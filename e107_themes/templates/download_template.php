<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_themes/templates/download_template.php,v $
|     $Revision: 1.20 $
|     $Date: 2010-01-21 03:57:44 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%"); }


/* set style of download image and thumbnail */
define("DL_IMAGESTYLE","border:0px");

// ##### CAT TABLE --------------------------------------------------------------------------------
if(!isset($DOWNLOAD_CAT_TABLE_START))
{
                $DOWNLOAD_CAT_TABLE_START = "
                <div style='text-align:center'>
                <table class='fborder' style='".USER_WIDTH."'>\n
                <tr>
                <td style='width:3%; text-align:center' class='fcaption'>&nbsp;</td>
                <td style='width:60%; text-align:center' class='fcaption'>".LAN_dl_19."</td>
                <td style='width:10%; text-align:center' class='fcaption'>".LAN_dl_20."</td>
                <td style='width:17%; text-align:center' class='fcaption'>".LAN_dl_21."</td>
                <td style='width:10%; text-align:center' class='fcaption'>".LAN_dl_77."</td>
                </tr>";
}
if(!isset($DOWNLOAD_CAT_PARENT_TABLE))
{
                $DOWNLOAD_CAT_PARENT_TABLE = "
                <tr>
                <td colspan='5' class='forumheader' style='text-align:left; font-weight:bold;'>
                        {DOWNLOAD_CAT_MAIN_ICON} {DOWNLOAD_CAT_MAIN_NAME}
                </td>
                </tr>";
}

if(!isset($DOWNLOAD_CAT_CHILD_TABLE))
{
                $DOWNLOAD_CAT_CHILD_TABLE = "
                <tr>
                <td class='forumheader3'>
                        {DOWNLOAD_CAT_SUB_ICON}
                </td>
                <td class='forumheader3'>
                        {DOWNLOAD_CAT_SUB_NEW_ICON} {DOWNLOAD_CAT_SUB_NAME}<br />
                        <span class='smalltext'>
                        {DOWNLOAD_CAT_SUB_DESCRIPTION}
                        </span>
                </td>
                <td class='forumheader3' style='text-align:center;'>
                        {DOWNLOAD_CAT_SUB_COUNT}
                </td>
                <td class='forumheader3' style='text-align:center;'>
                        {DOWNLOAD_CAT_SUB_SIZE}
                </td>
                <td class='forumheader3' style='text-align:center;'>
                        {DOWNLOAD_CAT_SUB_DOWNLOADED}
                </td>
                </tr>
                {DOWNLOAD_CAT_SUBSUB}
                ";

}

if(!isset($DOWNLOAD_CAT_SUBSUB_TABLE))
{
	$DOWNLOAD_CAT_SUBSUB_TABLE = "
	<tr>
	<td class='forumheader3'>
		&nbsp;
		</td>
		<td class='forumheader3' style='width:100%'>
			<table>
			<tr>
				<td class='forumheader3' style='border:0'>".
				LAN_dl_42."
				</td>
				<td class='forumheader3' style='border:0'>
				{DOWNLOAD_CAT_SUBSUB_ICON}
				</td>
				<td class='forumheader3' style='border:0; width: 100%'>
					{DOWNLOAD_CAT_SUBSUB_NEW_ICON} {DOWNLOAD_CAT_SUBSUB_NAME}<br />
					<span class='smalltext'>
					{DOWNLOAD_CAT_SUBSUB_DESCRIPTION}
					</span>
				</td>
			</tr>
			</table>
		</td>

	<td class='forumheader3' style='text-align:center;'>
		{DOWNLOAD_CAT_SUBSUB_COUNT}
	</td>
	<td class='forumheader3' style='text-align:center;'>
		{DOWNLOAD_CAT_SUBSUB_SIZE}
	</td>
	<td class='forumheader3' style='text-align:center;'>
		{DOWNLOAD_CAT_SUBSUB_DOWNLOADED}
	</td>
	</tr>";
}

if(!isset($DOWNLOAD_CAT_TABLE_END))
{
                $DOWNLOAD_CAT_TABLE_END = "
                <tr><td class='forumheader3' colspan='5' style='text-align:right;'>{DOWNLOAD_CAT_NEWDOWNLOAD_TEXT}</td></tr>
                <tr><td class='forumheader3' colspan='5' style='text-align:right;'>{DOWNLOAD_CAT_SEARCH}</td></tr>
                </table>
                </div>\n";
}
// ##### ------------------------------------------------------------------------------------------



// ##### LIST TABLE -------------------------------------------------------------------------------
if(!isset($DOWNLOAD_LIST_TABLE_START))
{
                $DOWNLOAD_LIST_TABLE_START = "
                <div style='text-align:center'>
                <form method='post' action='".e_SELF."?".e_QUERY."'>
                <table class='fborder' style='".USER_WIDTH."'>\n
                <tr>
        <td colspan='7' style='text-align:center' class='forumheader'>
                        <span class='defaulttext'>".LAN_dl_37."</span>
                        <select name='view' class='tbox'>".
                        ($view == 5 ? "<option selected='selected'>5</option>" : "<option>5</option>").
                        ($view == 10 ? "<option selected='selected'>10</option>" : "<option>10</option>").
                        ($view == 15 ? "<option selected='selected'>15</option>" : "<option>15</option>").
                        ($view == 20 ? "<option selected='selected'>20</option>" : "<option>20</option>").
                        ($view == 50 ? "<option selected='selected'>50</option>" : "<option>50</option>")."
                        </select>
                        &nbsp;
                        <span class='defaulttext'>".LAN_dl_38."</span>
                        <select name='order' class='tbox'>".
                        ($order == "download_datestamp" ? "<option value='download_datestamp' selected='selected'>".LAN_dl_22."</option>" : "<option value='download_datestamp'>".LAN_dl_22."</option>").
                        ($order == "download_requested" ? "<option value='download_requested' selected='selected'>".LAN_dl_18."</option>" : "<option value='download_requested'>".LAN_dl_77."</option>").
                        ($order == "download_name" ? "<option value='download_name' selected='selected'>".LAN_dl_23."</option>" : "<option value='download_name'>".LAN_dl_23."</option>").
                        ($order == "download_author" ? "<option value='download_author' selected='selected'>".LAN_dl_24."</option>" : "<option value='download_author'>".LAN_dl_24."</option>")."
                        </select>
                        &nbsp;
                        <span class='defaulttext'>".LAN_dl_39."</span>
                        <select name='sort' class='tbox'>".
                        ($sort == "ASC" ? "<option value='ASC' selected='selected'>".LAN_dl_25."</option>" : "<option value='ASC'>".LAN_dl_25."</option>").
                        ($sort == "DESC" ? "<option value='DESC' selected='selected'>".LAN_dl_26."</option>" : "<option value='DESC'>".LAN_dl_26."</option>")."
                        </select>
                        &nbsp;
                        <input class='button' type='submit' name='goorder' value='".LAN_dl_27."' />
        </td>
                </tr>
        <tr>
        <td style='width:35%; text-align:center' class='fcaption'>".LAN_dl_28."</td>
        <td style='width:15%; text-align:center' class='fcaption'>".LAN_dl_22."</td>
        <td style='width:20%; text-align:center' class='fcaption'>".LAN_dl_24."</td>
        <td style='width:10%; text-align:center' class='fcaption'>".LAN_dl_21."</td>
        <td style='width:5%; text-align:center' class='fcaption'>".LAN_dl_29."</td>
        <td style='width:10%; text-align:center' class='fcaption'>".LAN_dl_12."</td>
        <td style='width:5%; text-align:center' class='fcaption'>".LAN_dl_8."</td>
        </tr>";

}

if(!isset($DOWNLOAD_LIST_TABLE))
{
		$DOWNLOAD_LIST_TABLE = "
		<tr>
		<td class='forumheader3' style='text-align:left;'>
		{DOWNLOAD_LIST_NEWICON} {DOWNLOAD_LIST_NAME}
		</td>
		<td class='forumheader3' style='text-align:center;'>
		{DOWNLOAD_LIST_DATESTAMP}
		</td>
		<td class='forumheader3' style='text-align:center;'>
		{DOWNLOAD_LIST_AUTHOR}
		</td>
		<td class='forumheader3' style='text-align:center;'>
		{DOWNLOAD_LIST_FILESIZE}
		</td>
		<td class='forumheader3' style='text-align:center;'>
		{DOWNLOAD_LIST_REQUESTED}
		</td>
		<td class='forumheader3' style='text-align:center;'>
		{DOWNLOAD_LIST_RATING}
		</td>
		<td class='forumheader3' style='text-align:center;'>
		{DOWNLOAD_LIST_LINK} {DOWNLOAD_LIST_ICON}</a>
		</td>
		</tr>";
}

if(!isset($DOWNLOAD_LIST_TABLE_END))
{
		$DOWNLOAD_LIST_TABLE_END = "
		<tr><td class='forumheader3' colspan='7' style='text-align:right;'>{DOWNLOAD_LIST_TOTAL_AMOUNT} {DOWNLOAD_LIST_TOTAL_FILES}</td></tr>
		</table>
		</form>
		</div>\n";
}
// ##### ------------------------------------------------------------------------------------------


// ##### VIEW TABLE -------------------------------------------------------------------------------

$DL_VIEW_PAGETITLE = PAGE_NAME." / {DOWNLOAD_CATEGORY} / {DOWNLOAD_VIEW_NAME}";
$DL_VIEW_CAPTION = "{DOWNLOAD_VIEW_CAPTION}";

if(!isset($DL_VIEW_NEXTPREV))
{
		$DL_VIEW_NEXTPREV = "
		<div style='text-align:center'>
			<table style='".USER_WIDTH."'>
			<tr>
			<td style='width:40%;'>{DOWNLOAD_VIEW_PREV}</td>
			<td style='width:20%; text-align: center;'>{DOWNLOAD_BACK_TO_LIST}</td>
			<td style='width:40%; text-align: right;'>{DOWNLOAD_VIEW_NEXT}</td>
			</tr>
			</table>
		</div>\n";
}

// Only renders the following rows when data is present.
$sc_style['DOWNLOAD_VIEW_AUTHOR_LAN']['pre'] = "<tr><td style='width:20%' class='forumheader3'>";
$sc_style['DOWNLOAD_VIEW_AUTHOR_LAN']['post'] = "</td>";

$sc_style['DOWNLOAD_VIEW_AUTHOR']['pre'] = "<td style='width:80%' class='forumheader3'>";
$sc_style['DOWNLOAD_VIEW_AUTHOR']['post'] = "</td></tr>";

$sc_style['DOWNLOAD_VIEW_AUTHOREMAIL_LAN']['pre'] = "<tr><td style='width:20%' class='forumheader3'>";
$sc_style['DOWNLOAD_VIEW_AUTHOREMAIL_LAN']['post'] = "</td>";

$sc_style['DOWNLOAD_VIEW_AUTHOREMAIL']['pre'] = "<td style='width:80%' class='forumheader3'>";
$sc_style['DOWNLOAD_VIEW_AUTHOREMAIL']['post'] = "</td></tr>";

$sc_style['DOWNLOAD_VIEW_AUTHORWEBSITE_LAN']['pre'] = "<tr><td style='width:20%' class='forumheader3'>";
$sc_style['DOWNLOAD_VIEW_AUTHORWEBSITE_LAN']['post'] = "</td>";

$sc_style['DOWNLOAD_VIEW_AUTHORWEBSITE']['pre'] = "<td style='width:80%' class='forumheader3'>";
$sc_style['DOWNLOAD_VIEW_AUTHORWEBSITE']['post'] = "</td></tr>";

$sc_style['DOWNLOAD_REPORT_LINK']['pre'] = "<tr><td style='width:20%' class='forumheader3' colspan='2'>";
$sc_style['DOWNLOAD_REPORT_LINK']['post'] = "</td></tr>";

if(!isset($DOWNLOAD_VIEW_TABLE))
{
		$DOWNLOAD_VIEW_TABLE = "
        <div style='text-align:center'>
		<table class='fborder' style='".USER_WIDTH."'>\n
		<tr>
		<td colspan='2' class='fcaption' style='text-align:left;'>
		{DOWNLOAD_VIEW_NAME}
		</td>
		</tr>

		{DOWNLOAD_VIEW_AUTHOR_LAN}
		{DOWNLOAD_VIEW_AUTHOR}

		{DOWNLOAD_VIEW_AUTHOREMAIL_LAN}
		{DOWNLOAD_VIEW_AUTHOREMAIL}

		{DOWNLOAD_VIEW_AUTHORWEBSITE_LAN}
		{DOWNLOAD_VIEW_AUTHORWEBSITE}

		<tr>
		<td style='width:20%' class='forumheader3'>{DOWNLOAD_VIEW_DESCRIPTION_LAN}</td>
		<td style='width:80%' class='forumheader3'>{DOWNLOAD_VIEW_DESCRIPTION}</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>{DOWNLOAD_VIEW_IMAGE_LAN}</td>
		<td style='width:80%' class='forumheader3'>{DOWNLOAD_VIEW_IMAGE}</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>{DOWNLOAD_VIEW_FILESIZE_LAN}</td>
		<td style='width:80%' class='forumheader3'>{DOWNLOAD_VIEW_FILESIZE}</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>{DOWNLOAD_VIEW_DATE_LAN}</td>
		<td style='width:80%' class='forumheader3'>{DOWNLOAD_VIEW_DATE=long}</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>{DOWNLOAD_VIEW_REQUESTED_LAN}</td>
		<td style='width:80%' class='forumheader3'>{DOWNLOAD_VIEW_REQUESTED}</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>{DOWNLOAD_VIEW_LINK_LAN}</td>
		<td style='width:80%' class='forumheader3'>{DOWNLOAD_VIEW_LINK}</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>{DOWNLOAD_VIEW_RATING_LAN}</td>
		<td style='width:80%' class='forumheader3'>{DOWNLOAD_VIEW_RATING}</td>
		</tr>

		{DOWNLOAD_REPORT_LINK}
		</table>
		<div style='text-align:right; ".USER_WIDTH."; margin-left: auto; margin-right: auto'>{DOWNLOAD_ADMIN_EDIT}</div>
		</div>\n";
}

// ##### ------------------------------------------------------------------------------------------

// ##### MIRROR LIST -------------------------------------------------------------------------------

if(!isset($DOWNLOAD_MIRROR_START))
{
	$DOWNLOAD_MIRROR_START = "
	<div style='text-align:center'>
	<table class='fborder' style='".USER_WIDTH."'>
	<tr>
	<td class='fcaption' colspan='4'>{DOWNLOAD_MIRROR_REQUEST}</td>
	</tr>
	<tr>
	<td class='forumheader' style='width: 30%; text-align: center;'>{DOWNLOAD_MIRROR_HOST_LAN}</td>
	<td class='forumheader' style='width: 40%;'>{DOWNLOAD_MIRROR_DESCRIPTION_LAN}</td>
	<td class='forumheader' style='width: 20%; text-align: center;'>{DOWNLOAD_MIRROR_LOCATION_LAN}</td>
	<td class='forumheader' style='width: 10%; text-align: center;'>{DOWNLOAD_MIRROR_GET_LAN}</td>
	</tr>
	";
}

if(!isset($DOWNLOAD_MIRROR))
{
	$DOWNLOAD_MIRROR = "
	<tr>
	<td class='forumheader3' style='width: 30%; text-align: center;'>{DOWNLOAD_MIRROR_IMAGE}<br /><br /><div class='smalltext'>{DOWNLOAD_MIRROR_REQUESTS}<br />{DOWNLOAD_TOTAL_MIRROR_REQUESTS}</div></td>
	<td class='forumheader3' style='width: 40%'><div class='smalltext'>{DOWNLOAD_MIRROR_DESCRIPTION}</div></td>
	<td class='forumheader3' style='width: 20%;; text-align: center;'>{DOWNLOAD_MIRROR_LOCATION}</td>
	<td class='forumheader3' style='width: 10%; text-align: center;'><div class='smalltext'>{DOWNLOAD_MIRROR_LINK} {DOWNLOAD_MIRROR_FILESIZE}</div></td>
	</tr>
	";
}

if(!isset($DOWNLOAD_MIRROR_END))
{
	$DOWNLOAD_MIRROR_END = "
	</table>
	</div>
	";
}

// ##### ------------------------------------------------------------------------------------------
?>