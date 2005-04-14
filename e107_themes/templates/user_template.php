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
|     $Revision: 1.5 $
|     $Date: 2005-04-14 16:40:30 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

global $user_shortcodes;
$EXTENDED_CATEGORY_START = "<tr><td colspan='2' class='forumheader' style='text-align:left'>{EXTENDED_NAME}</td></tr>";

$EXTENDED_CATEGORY_TABLE = "
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


$sc_style['USER_SENDPM']['pre'] = "<tr><td style='width:100%' class='forumheader3'><span style='float:left'>";
$sc_style['USER_SENDPM']['post'] = "</span><span style='float:right;'>".LAN_425."</span></td></tr>";

if($tp->parseTemplate("{USER_SENDPM}", FALSE, $user_shortcodes))
{
	$sendpm = "{USER_SENDPM}";
}
else
{
	$sendpm = "";
}

//$sc_style['USER_PICTURE']['pre'] = "";
//$sc_style['USER_PICTURE']['post'] = $sendpm;

$sc_style['USER_SIGNATURE']['pre'] = "<tr><td colspan='2' class='forumheader3' style='text-align:left'>";
$sc_style['USER_SIGNATURE']['post'] = "</td></tr>";

$sc_style['USER_COMMENTS_LINK']['pre'] = "<tr><td colspan='2' class='forumheader3' style='text-align:left'>";
$sc_style['USER_COMMENTS_LINK']['post'] = "</td></tr>";

$sc_style['USER_FORUM_LINK']['pre'] = "<tr><td colspan='2' class='forumheader3' style='text-align:left'>";
$sc_style['USER_FORUM_LINK']['post'] = "</td></tr>";

$sc_style['USER_UPDATE_LINK']['pre'] = "<tr><td colspan='2' class='forumheader3' style='text-align:center'>";
$sc_style['USER_UPDATE_LINK']['post'] = "</td></tr>";

$span = ($sendpm) ? 5 : 4;
$USER_FULL_TEMPLATE = "
<div style='text-align:center'>
<table style='width:95%' class='fborder'>
<tr>
	<td colspan='2' class='fcaption' style='text-align:center'>".LAN_142." {USER_ID}: {USER_NAME}</td>
</tr>
<tr>
	<td rowspan='{$span}' class='forumheader3' style='width:20%; vertical-align:middle; text-align:center'>
	{USER_PICTURE}
	</td>
	<td class='forumheader3' style='width:100%'>
		<span style='float:left'>{USER_REALNAME_ICON} ".LAN_308."</span>
		<span style='float:right; text-align:right'>{USER_REALNAME}</span>
	</td>
</tr>

<tr>
	<td style='width:100%' class='forumheader3'>
		<span style='float:left'>{USER_EMAIL_ICON} ".LAN_112."</span>
		<span style='float:right; text-align:right'>{USER_EMAIL_LINK}</span>
	</td>
</tr>

<tr>
	<td style='width:100%' class='forumheader3'>
		<span style='float:left'>".LAN_406.":</span>
		<span style='float:right; text-align:right'>{USER_LEVEL}</span>
	</td>
</tr>

<tr>
	<td style='width:100%' class='forumheader3'>
		<span style='float:left'>".LAN_404.":&nbsp;&nbsp;</span>
		<span style='float:right; text-align:right'>{USER_LASTVISIT}<br />{USER_LASTVISIT_LAPSE}</span>
	</td>
</tr>
{$sendpm}
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