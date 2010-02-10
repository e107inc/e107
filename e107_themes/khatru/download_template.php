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
 * $Source: /cvs_backup/e107_0.8/e107_themes/khatru/download_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

/* set style of download image and thumbnail */
define("DL_IMAGESTYLE", "border:0px");

// ##### CAT TABLE --------------------------------------------------------------------------------

$DOWNLOAD_CAT_TABLE_START = "";

$DOWNLOAD_CAT_PARENT_TABLE .= "
<br /><br />
<table style='width: 100%;'>
<tr>
<td class='forumheader3' style='width: 5%;'>{DOWNLOAD_CAT_MAIN_ICON}</td>
<td class='forumheader3' style='width: 95%;'><h2>{DOWNLOAD_CAT_MAIN_NAME}</h2></td>
</tr>
</table>
<br /><br />

<table style='width: 100%;'>
<tr>
<td colspan='2' class='forumheader' style='width: 70%;'>Category</td>
<td class='forumheader' style='width: 20%; text-align: center;'>Files</td>
</tr>
";

$DOWNLOAD_CAT_CHILD_TABLE .= "
<tr>
<td class='forumheader3' style='width: 5%;'>{DOWNLOAD_CAT_SUB_ICON}</td>
<td class='forumheader3' style='width: 75%;'>{DOWNLOAD_CAT_SUB_NAME}</td>
<td class='forumheader3' style='width: 20%; text-align: center; vertical-align: middle;'>{DOWNLOAD_CAT_SUB_COUNT} ({DOWNLOAD_CAT_SUB_SIZE})<br /><span class='smalltext'>".LAN_dl_6." {DOWNLOAD_CAT_SUB_DOWNLOADED}</span></td>
</tr>
";

$DOWNLOAD_CAT_TABLE_END = "
<tr><td class='forumheader3' colspan='5' style='text-align:right;'>{DOWNLOAD_CAT_SEARCH}</td></tr>
</table>
</div>\n";

// ##### ------------------------------------------------------------------------------------------



// ##### LIST TABLE -------------------------------------------------------------------------------


$DOWNLOAD_LIST_TABLE_START = "

<br /><br />
<table style='width: 100%;'>
<tr>
<td class='forumheader3' style='width: 5%;'>{DOWNLOAD_CATEGORY_ICON}</td>
<td class='forumheader3' style='width: 95%;'><h2>{DOWNLOAD_CATEGORY}</h2></td>
</tr>
</table>
<br /><br />

<div style='text-align:center; margin-left: auto; margin-right: auto;'>
<form method='post' action='".e_SELF."?".e_QUERY."'>
<table style='width:100%'>
<tr>
<td colspan='7' style='text-align:center'>
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
($order == "download_requested" ? "<option value='download_requested' selected='selected'>".LAN_dl_18."</option>" : "<option value='download_requested'>".LAN_dl_18."</option>").
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
<tr class='fcaption'>
<td style='width:35%; text-align:center' class='smalltext'>".LAN_dl_28."</td>
<td style='width:15%; text-align:center' class='smalltext'>".LAN_dl_22."</td>
<td style='width:20%; text-align:center' class='smalltext'>".LAN_dl_24."</td>
<td style='width:10%; text-align:center' class='smalltext'>".LAN_dl_21."</td>
<td style='width:5%; text-align:center' class='smalltext'>".LAN_dl_29."</td>
<td style='width:10%; text-align:center' class='smalltext'>".LAN_dl_12."</td>
<td style='width:5%; text-align:center' class='smalltext'>".LAN_dl_8."</td>
</tr>";


$DOWNLOAD_LIST_TABLE .= "
<tr>
<td class='forumheader3' style='text-align:left;'>{DOWNLOAD_LIST_NEWICON} {DOWNLOAD_LIST_NAME}</td>
<td class='smalltext' style='text-align:center;'>{DOWNLOAD_LIST_DATESTAMP}</td>
<td class='smalltext' style='text-align:center;'>{DOWNLOAD_LIST_AUTHOR}</td>
<td class='smalltext' style='text-align:center;'>{DOWNLOAD_LIST_FILESIZE}</td>
<td class='smalltext' style='text-align:center;'>{DOWNLOAD_LIST_REQUESTED}</td>
<td class='smalltext' style='text-align:center;'>{DOWNLOAD_LIST_RATING}</td>
<td class='smalltext' style='text-align:center;'>{DOWNLOAD_LIST_LINK} {DOWNLOAD_LIST_ICON}</td>
</tr>
";

$DOWNLOAD_LIST_TABLE_END = "
<tr><td class='smalltext' colspan='7' style='text-align:right;'>{DOWNLOAD_LIST_TOTAL_AMOUNT} {DOWNLOAD_LIST_TOTAL_FILES}</td></tr>
</table>
</form>
</div>
";

// ##### ------------------------------------------------------------------------------------------


// ##### VIEW TABLE -------------------------------------------------------------------------------

$DOWNLOAD_VIEW_TABLE_START = "
<div style='text-align:center'>
<table class='fborder' style='width:100%'>
";

$DOWNLOAD_VIEW_TABLE .= "
<tr>
<td colspan='2' class='fcaption' style='text-align:left;'>
{DOWNLOAD_VIEW_NAME}
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>{DOWNLOAD_VIEW_AUTHOR_LAN}</td>
<td style='width:80%' class='forumheader3'>{DOWNLOAD_VIEW_AUTHOR}</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>{DOWNLOAD_VIEW_AUTHOREMAIL_LAN}</td>
<td style='width:80%' class='forumheader3'>{DOWNLOAD_VIEW_AUTHOREMAIL}</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>{DOWNLOAD_VIEW_AUTHORWEBSITE_LAN}</td>
<td style='width:80%' class='forumheader3'>{DOWNLOAD_VIEW_AUTHORWEBSITE}</td>
</tr>

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
";

$DOWNLOAD_VIEW_TABLE_END = "
</table>
</div>
";

// ##### ------------------------------------------------------------------------------------------

// ##### MIRROR LIST -------------------------------------------------------------------------------


$DOWNLOAD_MIRROR_START = "
<div style='text-align:center'>
<table class='fborder' style='width:100%'>
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

$DOWNLOAD_MIRROR = "
<tr>
<td class='forumheader3' style='width: 30%; text-align: center;'>{DOWNLOAD_MIRROR_IMAGE}<br /><br /><div class='smalltext'>{DOWNLOAD_MIRROR_REQUESTS}<br />{DOWNLOAD_TOTAL_MIRROR_REQUESTS}</div></td>
<td class='forumheader3' style='width: 40%'><div class='smalltext'>{DOWNLOAD_MIRROR_DESCRIPTION}</div></td>
<td class='forumheader3' style='width: 20%;; text-align: center;'>{DOWNLOAD_MIRROR_LOCATION}</td>
<td class='forumheader3' style='width: 10%; text-align: center;'><div class='smalltext'>{DOWNLOAD_MIRROR_LINK} {DOWNLOAD_MIRROR_FILESIZE}</div></td>
</tr>
";

$DOWNLOAD_MIRROR_END = "
</table>
</div>
";

// ##### ------------------------------------------------------------------------------------------
?>