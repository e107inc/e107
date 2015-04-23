<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	PM plugin - template file
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/pm/pm_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */


/**
 *	e107 Private messenger plugin
 *
 *	@package	e107_plugins
 *	@subpackage	pm
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }

global $sc_style;		// Needed for the PM_REPLY shortcode!

if (!defined('PM_READ_ICON')) define('PM_READ_ICON', "<img src='".e_PLUGIN_ABS."pm/images/read.png' class='icon S16' alt='".LAN_PM_111."' />");
if (!defined('PM_UNREAD_ICON')) define('PM_UNREAD_ICON', "<img src='".e_PLUGIN_ABS."pm/images/unread.png' class='icon S16' alt='".LAN_PM_27."' />");

$sc_style['PM_ATTACHMENT_ICON']['pre'] = " ";

$sc_style['PM_ATTACHMENTS']['pre'] = "<br /><div style='vertical-align:bottom; text-align:left;'>";
$sc_style['PM_ATTACHMENTS']['post'] = "</div>";

$sc_style['PM_NEXTPREV']['pre'] = "<tr><td class='forumheader' colspan='6' style='text-align:left'> ".LAN_PM_59;
$sc_style['PM_NEXTPREV']['post'] = "</td></tr>";

$sc_style['PM_EMOTES']['pre'] = "
<tr>
	<td class='forumheader3'>".LAN_PM_7.": </td>
	<td class='forumheader3'>
";
$sc_style['PM_EMOTES']['post'] = "</td></tr>";

$sc_style['PM_ATTACHMENT']['pre'] = "
<tr>
	<td class='forumheader3'>".LAN_PM_8.": </td>
	<td class='forumheader3'>
";
$sc_style['PM_ATTACHMENT']['post'] = "</td></tr>";

$sc_style['PM_RECEIPT']['pre'] = "
<tr>
	<td class='forumheader3'>".LAN_PM_9.": </td>
	<td class='forumheader3'>
";
$sc_style['PM_RECEIPT']['post'] = "</td></tr>";

$sc_style['PM_REPLY']['pre'] = "<tr>
	<td class='forumheader' style='text-align:center' colspan='2'>
";
	
$sc_style['PM_REPLY']['post'] = "</td>
	</tr>
";

$PM_SEND_PM = "<div id='pm-send-pm'>
<table class='table fborder'>
<tr>
	<td colspan='2' class='fcaption'>".LAN_PM_1.": </td>
</tr>
<tr>
	<td class='forumheader3' style='width: 30%'>".LAN_PM_2.": </td>
	<td class='forumheader3' style='width: 70%; text-align:left'>{PM_FORM_TOUSER}<br />
	<div class='form-inline'>{PM_FORM_TOCLASS}</div></td>
</tr>
<tr>
	<td class='forumheader3'>".LAN_PM_5.": </td>
	<td class='forumheader3'>{PM_FORM_SUBJECT}</td>
</tr>
<tr>
	<td class='forumheader3'>".LAN_PM_6.": </td>
	<td class='forumheader3'>{PM_FORM_MESSAGE}</td>
</tr>
{PM_EMOTES}
{PM_ATTACHMENT}
{PM_RECEIPT}
<tr>
	<td class='forumheader' colspan='2' style='text-align:center;'>{PM_POST_BUTTON}</td>
</tr>
</table>
</div>
";

$PM_INBOX_HEADER = "
<table class='table table-striped fborder'>
<thead>
<tr>
	<th class='fcaption' style='width:1%'>&nbsp;</th>
	<th class='fcaption' style='width:1%'>&nbsp;</th>
	<th class='fcaption' style='width:38%'>".LAN_PM_5."</th>
	<th class='fcaption' style='width:22%'>".LAN_PM_31."</th>
	<th class='fcaption' style='width:30%'>".LAN_PM_32."</th>
	<th class='fcaption' style='width:8%'>&nbsp;</th>
</tr>
</thead>
	<tbody>
";

$PM_INBOX_TABLE = "
<tr>
	<td class='forumheader3'>{PM_SELECT}</td>
	<td class='forumheader3'>{PM_READ_ICON}</td>
	<td class='forumheader3'>{PM_SUBJECT=link,inbox}{PM_ATTACHMENT_ICON}</td>
	<td class='forumheader3'>{PM_FROM=link}</td>
	<td class='forumheader3'>{PM_DATE}</td>
	<td class='forumheader3' style='text-align: center; white-space: nowrap'>{PM_DELETE=inbox}&nbsp;{PM_BLOCK_USER}</td>
</tr>
";

$PM_INBOX_EMPTY = "
<tr>
	<td colspan='6' class='forumheader'>".LAN_PM_34."</td>
</tr>
";

$PM_INBOX_FOOTER = "
<tr>
	<td class='forumheader' colspan='6' style='text-align:center'>
	<input type='hidden' name='pm_come_from' value='inbox' />
	{PM_DELETE_SELECTED}
	</td>
</tr>
{PM_NEXTPREV=inbox}
</tbody>
</table>
";

$PM_OUTBOX_HEADER = "
<table class='table table-striped fborder'>
<thead>
<tr>
	<th class='fcaption' style='width:1%'>&nbsp;</th>
	<th class='fcaption' style='width:1%'>&nbsp;</th>
	<th class='fcaption' style='width:38%'>".LAN_PM_5."</th>
	<th class='fcaption' style='width:22%'>".LAN_PM_2."</th>
	<th class='fcaption' style='width:30%'>".LAN_PM_33."</th>
	<th class='fcaption' style='width:8%'>&nbsp;</th>
</tr>
</thead>
<tbody>
";

$PM_OUTBOX_TABLE = "
<tr>
	<td class='forumheader3'>{PM_SELECT}</td>
	<td class='forumheader3'>{PM_READ_ICON}</td>
	<td class='forumheader3'>{PM_SUBJECT=link,outbox}{PM_ATTACHMENT_ICON}</td>
		<td class='forumheader3'>{PM_TO=link}</td>
	<td class='forumheader3'>{PM_DATE}</td>
	<td class='forumheader3' style='text-align: center'>{PM_DELETE=outbox}</td>
</tr>
";

$PM_OUTBOX_EMPTY = "
<tr>
	<td colspan='6' class='forumheader'>".LAN_PM_34."</td>
</tr>
";

$PM_OUTBOX_FOOTER = "
<tr>
	<td class='forumheader' colspan='6' style='text-align:center'>
	<input type='hidden' name='pm_come_from' value='outbox' />
	{PM_DELETE_SELECTED}
	</td>
</tr>
{PM_NEXTPREV=outbox}
</tbody>
</table>
";


$PM_BLOCKED_HEADER = "
<table class='table table-striped fborder'>
<tr>
	<td class='fcaption' style='width:5%'>&nbsp;</td>
	<td class='fcaption' style='width:48%'>".LAN_PM_68."</td>
	<td class='fcaption' style='width:42%'>".LAN_PM_69."</td>
	<td class='fcaption' style='width:5%'>&nbsp;</td>
</tr>
";

$PM_BLOCKED_TABLE = "
<tr>
	<td class='forumheader3'>{PM_BLOCKED_SELECT}</td>
	<td class='forumheader3'>{PM_BLOCKED_USER=link}</td>
	<td class='forumheader3'>{PM_BLOCKED_DATE}</td>
	<td class='forumheader3' style='text-align: center'>{PM_BLOCKED_DELETE}</td>
</tr>
";

$PM_BLOCKED_EMPTY = "
<tr>
	<td colspan='4' class='forumheader'>".LAN_PM_67."</td>
</tr>
";

$PM_BLOCKED_FOOTER = "
<tr>
	<td class='forumheader' colspan='4' style='text-align:center'>
	{PM_DELETE_BLOCKED_SELECTED}
	</td>
</tr>
</table>
";



$PM_SHOW =
"<div style='text-align: center'>
<table class='table fborder'>
<tr>
	<td class='fcaption text-left' colspan='2'>{PM_SUBJECT}</td>
</tr>
<tr>
	<td class='forumheader3 text-left' style='width:20%; vertical-align:top'>
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
	<td class='forumheader3 text-left' style='width:80%; vertical-align:top'>{PM_MESSAGE}<br /><br />{PM_ATTACHMENTS}</td>
</tr>
{PM_REPLY}
</table>
</div>
";

?>