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
|     $Source: /cvs_backup/e107_0.7/e107_themes/templates/user_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-03-21 04:25:38 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

$EXTENDED_START = "<tr><td colspan='2' class='forumheader'>".LAN_410."</td></tr>";

$EXTENDED_TABLE = "
	<tr>
		<td style='width:40%' class='forumheader3'>
			{EXTENDED_ICON}&nbsp;
			{EXTENDED_NAME}
		</td>
		<td style='width:60%' class='forumheader3'>{EXTENDED_VALUE}</td>
	</tr>
	";
	
$EXTENDED_END = "";
//		$datestamp = $gen->convert_date($user_join, "forum");

$USER_SHORT_TEMPLATE = "
<tr>
	<td class='forumheader3' style='width:2%'>{USER_ICON_LINK}</td>
	<td class='forumheader' style='width:20%'>{USER_ID}: {USER_NAME_LINK}</td>
	<td class='forumheader3' style='width:20%'>{USER_EMAIL}</td>
	<td class='forumheader3' style='width:20%'>{USER_JOIN}</td>
</tr>
";

$span = $pm_installed ? 6 : 5;

$sc_style['USER_PICTURE']['pre'] = "<tr><td rowspan='{$span}' class='forumheader3' style='width:20%; vertical-align:middle; text-align:center'>";
$sc_style['USER_PICTURE']['post'] = "</td></tr>";

$sc_style['USER_SENDPM']['pre'] = "
	<tr>
		<td style='width:80%' class='forumheader3' colspan='2'>
			<table style='width:100%'>
				<tr>
					<td style='width:30%'>";
$sc_style['USER_SENDPM']['post'] = " ".LAN_425."</td></tr></table></td></tr>";

$sc_style['USER_SIGNATURE']['pre'] = "<tr><td colspan='2' class='forumheader3' style='text-align:left'>";
$sc_style['USER_SIGNATURE']['post'] = "</td></tr>";

$sc_style['USER_COMMENTS_LINK']['pre'] = "<tr><td colspan='2' class='forumheader3' style='text-align:left'>";
$sc_style['USER_COMMENTS_LINK']['post'] = "</td></tr>";

$sc_style['USER_FORUM_LINK']['pre'] = "<tr><td colspan='2' class='forumheader3' style='text-align:left'>";
$sc_style['USER_FORUM_LINK']['post'] = "</td></tr>";

$sc_style['USER_UPDATE_LINK']['pre'] = "<tr><td colspan='2' class='forumheader3' style='text-align:center'>";
$sc_style['USER_UPDATE_LINK']['post'] = "</td></tr>";

$USER_FULL_TEMPLATE = "
<div style='text-align:center'>
<table style='width:95%' class='fborder'>
<tr>
	<td colspan='2' class='fcaption' style='text-align:center'>".LAN_142." {USER_ID}: {USER_NAME}</td>
</tr>
{USER_PICTURE}
<tr>
<td style='width:80%' class='forumheader3'>
	<table style='width:100%'>
		<tr>
			<td style='width:30%'>{USER_REALNAME_ICON} ".LAN_308."</td>
			<td style='width:70%; text-align:right'>{USER_REALNAME}</td>
		</tr>
	</table>
</td>
</tr>

<tr>
	<td style='width:80%' class='forumheader3'>
		<table style='width:100%'>
			<tr>
				<td style='width:30%'>{USER_EMAIL_ICON} ".LAN_112."</td>
				<td style='width:70%; text-align:right'>{USER_EMAIL_LINK}</td>
			</tr>
		</table>
	</td>
</tr>

<tr>
	<td style='width:80%' class='forumheader3'>
		<table style='width:100%'>
			<tr>
				<td style='width:30%'>{USER_BIRTHDAY_ICON} ".LAN_118."</td>
				<td style='width:70%; text-align:right'>{USER_BIRTHDAY}</td>
			</tr>
		</table>
	</td>
</tr>
{USER_SENDPM}
{USER_SIGNATURE}
{USER_EXTENDED_ALL}
<tr>
	<td colspan='2' class='forumheader'>".LAN_403."</td>
</tr>
<tr>
	<td style='width:30%' class='forumheader3'>".LAN_145."</td>
	<td style='width:70%' class='forumheader3'>{USER_JOIN}<br />{USER_DAYSREGGED}</td>
</tr>
<tr>
	<td style='width:30%' class='forumheader3'>".LAN_147."</td>
	<td style='width:70%' class='forumheader3'>{USER_CHATPOSTS} ( {USER_CHATPER}% )</td>
</tr>

<tr>
	<td style='width:30%' class='forumheader3'>".LAN_148."</td>
	<td style='width:70%' class='forumheader3'>{USER_COMMENTPOSTS} ( {USER_COMMENTPER}% )</td>
</tr>
{USER_COMMENTS_LINK}

<tr>
	<td style='width:30%' class='forumheader3'>".LAN_149."</td>
	<td style='width:70%' class='forumheader3'>{USER_FORUMPOSTS} ( {USER_FORUMPER}% )</td>
</tr>
{USER_FORUM_LINK}
<tr>
	<td style='width:30%' class='forumheader3'>".LAN_146."</td>
	<td style='width:70%' class='forumheader3'>{USER_VISITS}</td>
</tr>
<tr>
	<td style='width:30%' class='forumheader3'>".LAN_404."</td>
	<td style='width:70%' class='forumheader3'>{USER_LASTVISIT}</td>
</tr>
<tr>
	<td style='width:30%' class='forumheader3'>".LAN_406."</td>
	<td style='width:70%' class='forumheader3'>{USER_LEVEL}</td>
</tr>
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
";
?>