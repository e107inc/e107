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
 * $Source: /cvs_backup/e107_0.8/e107_themes/templates/user_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
if (!defined("USER_WIDTH"))
{
	 define("USER_WIDTH", "width:95%");
}

global $user_shortcodes;
//Set this to TRUE if you would like any extended user field that is empty to NOT be shown on the profile page
define("HIDE_EMPTY_FIELDS", FALSE);

$USER_TEMPLATE['extended']['start'] = "<tr><td colspan='2' class='forumheader center'>{EXTENDED_NAME}</td></tr>";

$USER_TEMPLATE['extended']['item'] = "
	<tr>
		<td style='width:30%' class='forumheader3'>{EXTENDED_ICON}{EXTENDED_NAME}
		</td>
		<td style='width:70%' class='forumheader3'>{EXTENDED_VALUE}</td>
	</tr>
	";

$USER_TEMPLATE['extended']['end'] = "";

$USER_TEMPLATE['list']['start'] = "
	<div class='content user-list'>
	<div class='center'>".LAN_USER_56." {TOTAL_USERS}
	<br />
	<br />
	{USER_FORM_START}
	<div class='form-inline'>
	".LAN_SHOW.": {USER_FORM_RECORDS} ".LAN_USER_57." {USER_FORM_ORDER}
	{USER_FORM_SUBMIT}
	</div>
	{USER_FORM_END}
	</div>
	<br />
	<br />
	<table style='".USER_WIDTH."' class='table fborder e-list'>
	<thead>
	<tr>
	<th class='fcaption' style='width:2%'>&nbsp;</th>
	<th class='fcaption' style='width:20%'>".LAN_USER_58."</th>
	<th class='fcaption' style='width:20%'>".LAN_USER_60."</th>
	<th class='fcaption' style='width:20%'>".LAN_USER_59."</th>
	</tr>
	</thead>
	<tbody>
	{SETIMAGE: w=40}
";
$USER_TEMPLATE['list']['end'] = "
</tbody>
</table>
</div>
";

$USER_TEMPLATE['list']['item'] = "
<tr>
	<td class='forumheader3' style='width:2%'>{USER_PICTURE}</td>
	<td class='forumheader3' style='width:20%'>{USER_ID}: {USER_NAME_LINK}</td>
	<td class='forumheader3' style='width:20%'>{USER_EMAIL}</td>
	<td class='forumheader3' style='width:20%'>{USER_JOIN}</td>
</tr>
";

$USER_WRAPPER['view']['USER_SIGNATURE'] = "<tr><td colspan='2' class='forumheader3 left'>{---}</td></tr>";

$USER_WRAPPER['view']['USER_COMMENTS_LINK'] = "<tr><td colspan='2' class='forumheader3 left'>{---}</td></tr>";

$USER_WRAPPER['view']['USER_FORUM_LINK'] = "<tr><td colspan='2' class='forumheader3 left'>{---}</td></tr>";

$USER_WRAPPER['view']['USER_UPDATE_LINK'] = "<tr><td colspan='2' class='forumheader3 center'>{---}</td></tr>";

$USER_WRAPPER['view']['USER_RATING'] = "<tr><td colspan='2' class='forumheader3'><div class='f-left'>".LAN_RATING."</div><div class='f-right'>{---}</div></td></tr>";

$USER_WRAPPER['view']['USER_LOGINNAME'] = " : {---}";

$USER_WRAPPER['view']['USER_COMMENTPOSTS'] = "<tr><td style='width:30%' class='forumheader3'>".LAN_USER_68."</td><td style='width:70%' class='forumheader3'>{---}";

$USER_WRAPPER['view']['USER_COMMENTPER'] = " ( {---}% )</td></tr>";

$main_colspan = e107::getPref('photo_upload') ? "" : " colspan = '2' ";

$USER_WRAPPER['view']['USER_SENDPM'] = "<tr><td colspan='2' class='forumheader3'><div class='f-left'>{---}</div><div class='f-right'>".LAN_USER_62."</div></td></tr>";

// Determine which other bits are installed; let photo span those rows (can't do signature - will vary with user)
$span = 4;
if (e107::getParser()->parseTemplate("{USER_SENDPM}", FALSE, $user_shortcodes)) $span++;
$span = " rowspan='".$span."' ";

$USER_TEMPLATE['view'] = "{SETIMAGE: w=250}
<div class='content user user-legacy'>
<table style='".USER_WIDTH."' class='table fborder'>
<tr>
	<td colspan='2' class='fcaption center'>".LAN_USER_58." {USER_ID} : {USER_NAME}{USER_LOGINNAME}</td>
</tr>
<tr>
	<td {$span} class='forumheader3 center middle' style='width:20%'>{USER_PICTURE}</td>
	<td {$main_colspan} class='forumheader3'>
		<div class='f-left'>{USER_ICON=realname} ".LAN_USER_63."</div>
		<div class='f-right right'>{USER_REALNAME}</div>
	</td>
</tr>

<tr>
	<td  {$main_colspan} class='forumheader3'>
		<div class='f-left'>{USER_ICON=email} ".LAN_USER_60."</div>
		<div class='f-right right'>{USER_EMAIL}</div>
	</td>
</tr>

<tr>
	<td  {$main_colspan} class='forumheader3'>
		<div class='f-left'>{USER_ICON=level} ".LAN_USER_54.":</div>
		<div class='f-right right'>{USER_LEVEL}</div>
	</td>
</tr>

<tr>
	<td  {$main_colspan} class='forumheader3'>
		<div class='f-left'>{USER_ICON=lastvisit} ".LAN_USER_65.":&nbsp;&nbsp;</div>
		<div class='f-right right'>{USER_LASTVISIT}<br />{USER_LASTVISIT_LAPSE}</div>
	</td>
</tr>
{USER_SENDPM}
{USER_RATING}
{USER_SIGNATURE}
{USER_EXTENDED_ALL}
<tr>
	<td colspan='2' class='forumheader'>".LAN_USER_64."</td>
</tr>

<tr>
	<td style='width:30%' class='forumheader3'>".LAN_USER_59."</td>
	<td style='width:70%' class='forumheader3'>{USER_JOIN}<br />{USER_DAYSREGGED}</td>
</tr>

<tr>
	<td style='width:30%' class='forumheader3'>".LAN_USER_66."</td>
	<td style='width:70%' class='forumheader3'>{USER_VISITS}</td>
</tr>

{USER_ADDONS}

{USER_COMMENTPOSTS}
{USER_COMMENTPER}


{USER_UPDATE_LINK}
<tr>
	<td colspan='2' class='forumheader3' style='text-align:center'>
		<table style='width:95%'>
			<tr>
				<td style='width:50%'>{USER_JUMP_LINK=prev}</td>
				<td style='width:50%; text-align:right'>{USER_JUMP_LINK=next}</td>
			</tr>
		</table>
	</td>
</tr>
</table>
</div>    
{PROFILE_COMMENTS}
{PROFILE_COMMENT_FORM}
";

$USER_TEMPLATE['addon'] = "
<tr>
	<td class='forumheader3'>{USER_ADDON_LABEL}</td>
	<td class='forumheader3'>{USER_ADDON_TEXT}</td>
</tr>";

