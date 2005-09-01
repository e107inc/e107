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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm/pm_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-09-01 20:33:16 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
global $sc_style;

define("PM_READ_ICON", "<img src='".e_PLUGIN."/pm/images/read.png' style='height:32;width:32;border:0' />");
define("PM_UNREAD_ICON", "<img src='".e_PLUGIN."/pm/images/unread.png' style='height:32;width:32;border:0' />");

$PM_VIEW_PM =
"
<table style='width:97%; text-align:center' class='fborder'>
<tr>
	<td>
		<table style='width:100%' class='fborder'>
		<tr>
			<td colspan='2' style='width:100%; text-align:center;' class='fcaption'>{SUBJECT}</td>
		</tr>
		<tr>
			<td style='width:20%; vertical-align:top;' class='forumheader3'>
				<div class='mediumtext'>".LAN_PM_31.": <b>{FROM}</b></div>
				<div class='mediumtext'>".LAN_PM_2.": <b>{TO}</b>
				<br />
				<br />
				</div>
				<div class='smallblacktext'>".LAN_PM_29.":<br />{SENT_TIME}<br /><br /></div>				
				<div class='smallblacktext'>".LAN_PM_30.":<br />{READ_TIME}<br /></div>
				{DELETE}
			</td>
			<td style='width:80%; vertical-align:top' class='forumtable2'>
				{MESSAGE}
				{PM_ATTACHMENTS}
			</td>
		</tr>
		</table>
	</td>
</tr>
{REPLY}
</table>
";

$sc_style['PM_ATTACHMENT_ICON']['pre'] = " ";

$sc_style['PM_ATTACHMENTS']['pre'] = "<br /><div style='vertical-align:bottom; text-align:left;'>";
$sc_style['PM_ATTACHMENTS']['post'] = "</div>";

$sc_style['EMOTES']['pre'] = "
<tr>
	<td class='forumheader3' style='text-align:center'>".LAN_PM_7.": </td>
	<td class='forumheader3'>
";
$sc_style['EMOTES']['post'] = "</td></tr>";

$sc_style['ATTACHMENT']['pre'] = "
<tr>
	<td class='forumheader3' style='text-align:center'>".LAN_PM_8."</td>
	<td class='forumheader3'>
";
$sc_style['ATTACHMENT']['post'] = "</td></tr>";

$sc_style['RECEIPT']['pre'] = "
<tr>
	<td class='forumheader3' style='text-align:center'>".LAN_PM_9."</td>
	<td class='forumheader3'>
";
$sc_style['RECEIPT']['post'] = "</td></tr>";

$PM_SEND_PM = "
<table style'width:95%' class='fborder'>
<tr>
	<td colspan='2' style='width:60%' class='fcaption'>".LAN_PM_1.": </td>
</tr>
<tr>
	<td class='forumheader3'>".LAN_PM_2.": </td>
	<td class='forumheader3' style='text-align:left'>{FORM_TOUSER}<br />{FORM_TOCLASS}</td>
</tr>
<tr>
	<td class='forumheader3'>".LAN_PM_5.": </td>
	<td class='forumheader3'>{FORM_SUBJECT}</td>
</tr>
<tr>
	<td class='forumheader3'>".LAN_PM_6.": </td>
	<td class='forumheader3'>{FORM_MESSAGE}</td>
</tr>
{EMOTES}
{ATTACHMENT}
{RECEIPT}
<tr>
	<td class='forumheader' colspan='2' style='text-align:center;'>{PM_POST_BUTTON}</td>
</tr>
</table>
";

$PM_INBOX_HEADER = "
<table class='fborder' style='width:98%'>
<tr>
	<td class='fcaption'>&nbsp;</td>
	<td class='fcaption'>&nbsp;</td>
	<td class='fcaption'>".LAN_PM_5."</td>
	<td class='fcaption'>".LAN_PM_32."</td>
	<td class='fcaption'>".LAN_PM_31."</td>
	<td class='fcaption'>&nbsp;</td>
</tr>
";

$PM_INBOX_TABLE = "
<tr>
	<td class='forumheader3'>{PM_SELECT}</td>
	<td class='forumheader3'>{PM_READ_ICON}</td>
	<td class='forumheader3'>{PM_SUBJECT=link}{PM_ATTACHMENT_ICON}</td>
	<td class='forumheader3'>{PM_DATE}</td>
	<td class='forumheader3'>{PM_FROM=link}</td>
	<td class='forumheader3'>{PM_BLOCK_USER}<br />{PM_DELETE=inbox}</td>
</tr>
";

$PM_INBOX_EMPTY = "
<tr>
	<td colspan='6' class='forumheader2'>No messages</td>
</tr>
";

$PM_INBOX_FOOTER = "
<tr>
	<td class='forumheader2' colspan='6' style='text-align:left'>
	{DELETE_SELECTED}
	</td>
</tr>
</table>
";

$PM_OUTBOX_HEADER = "
<table class='fborder' style='width:98%'>
<tr>
	<td class='fcaption'>&nbsp;</td>
	<td class='fcaption'>&nbsp;</td>
	<td class='fcaption'>".LAN_PM_5."</td>
	<td class='fcaption'>".LAN_PM_33."</td>
	<td class='fcaption'>".LAN_PM_2."</td>
	<td class='fcaption'>&nbsp;</td>
</tr>
";

$PM_OUTBOX_TABLE = "
<tr>
	<td class='forumheader3'>{PM_SELECT}</td>
	<td class='forumheader3'>{PM_READ_ICON}</td>
	<td class='forumheader3'>{PM_SUBJECT=link}{PM_ATTACHMENT_ICON}</td>
	<td class='forumheader3'>{PM_DATE}</td>
	<td class='forumheader3'>{PM_TO=link}</td>
	<td class='forumheader3'>{PM_DELETE=outbox}</td>
</tr>
";

$PM_OUTBOX_EMPTY = "
<tr>
	<td colspan='6' class='forumheader2'>".LAN_PM_34."</td>
</tr>
";

$PM_OUTBOX_FOOTER = "
<tr>
	<td class='forumheader2' colspan='6' style='text-align:left'>
	{DELETE_SELECTED}
	</td>
</tr>
</table>
";

$PM_SHOW =
"
<table class='fborder' style='width:98%'>
<tr>
	<td class='fcaption' colspan='2'>{PM_SUBJECT}</td>
</tr>
<tr>
	<td rowspan='2' style='width:20%;vertical-align:top'>
		{PM_FROM_TO}
		<br />
		<br />
		<span class='smalltext'>".LAN_PM_29.":<br />{PM_DATE}</span>
		<br />
		<br />
		<span class='smalltext'>".LAN_PM_30.":<br />{PM_READ}</span>
		<br />
		<br />
		{PM_DELETE}
	</td>
	<td style='width:80%;vertical-align:top'>{PM_MESSAGE}<br /><br />{PM_ATTACHMENTS}</td>
</tr>
<tr>
	<td style='vertical-align:bottom;text-align:center'>
	{PM_REPLY}
	</td>
</tr>
</table>
<br />
{PM_SHOWBOX}
";

?>