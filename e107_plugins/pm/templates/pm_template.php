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

//global $sc_style;		// Needed for the PM_REPLY shortcode!

if(deftrue('BOOTSTRAP') && deftrue('FONTAWESOME'))
{
	define('PM_READ_ICON', e107::getParser()->toGlyph('fa-envelope'));
	define('PM_UNREAD_ICON', e107::getParser()->toGlyph('fa-envelope-o'));
}
else
{
	if (!defined('PM_READ_ICON')) define('PM_READ_ICON', "<img src='".e_PLUGIN_ABS."pm/images/read.png' class='icon S16' alt='".LAN_PM_111."' />");
	if (!defined('PM_UNREAD_ICON')) define('PM_UNREAD_ICON', "<img src='".e_PLUGIN_ABS."pm/images/unread.png' class='icon S16' alt='".LAN_PM_27."' />");
}
/*
$sc_style['PM_ATTACHMENT_ICON']['pre'] = " ";
$sc_style['PM_ATTACHMENTS']['pre'] = "<div class='alert alert-block alert-info'>";
$sc_style['PM_ATTACHMENTS']['post'] = "</div>";

//$sc_style['PM_NEXTPREV']['pre'] = "<tr><td class='forumheader' colspan='6' style='text-align:left'> ".LAN_PM_59;
//$sc_style['PM_NEXTPREV']['post'] = "</td></tr>";

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
*/

$PM_WRAPPER['PM_ATTACHMENT_ICON']= " {---}";
$PM_WRAPPER['PM_ATTACHMENTS']= "<div class='alert alert-block alert-info'>{---}</div>";

//$sc_style['PM_NEXTPREV']['pre'] = "<tr><td class='forumheader' colspan='6' style='text-align:left'> ".LAN_PM_59;
//$sc_style['PM_NEXTPREV']['post'] = "</td></tr>";

$PM_WRAPPER['PM_EMOTES']= "<tr><td class='forumheader3'>".LAN_PM_7.": </td><td class='forumheader3'>{---}</td></tr>";
$PM_WRAPPER['PM_ATTACHMENT']= "<tr><td class='forumheader3'>".LAN_PM_8.": </td><td class='forumheader3'>{---}</td></tr>";
$PM_WRAPPER['PM_RECEIPT']= "<tr><td class='forumheader3'>".LAN_PM_9.": </td><td class='forumheader3'>{---}</td></tr>";
$PM_WRAPPER['PM_REPLY']= "<tr><td class='forumheader' style='text-align:center'>{---}</td></tr>";

//$PM_SEND_PM = "<div id='pm-send-pm'>
$PM_TEMPLATE['send'] = "<div id='pm-send-pm'>
<table class='table fborder'>
<thead>
<tr>
	<th colspan='2' class='fcaption'>".LAN_PLUGIN_PM_NEW.": </th>
</tr>
</thead>
<tr>
	<td class='forumheader3' style='width: 30%'>".LAN_PM_2.": </td>
	<td class='forumheader3' style='width: 70%; text-align:left'>{PM_FORM_TO}</td>
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

//$PM_INBOX_HEADER = "
$PM_TEMPLATE['inbox']['start'] = "
<table class='table table-striped fborder'>
<thead>
<tr>
	<th class='fcaption' style='width:1%'>&nbsp;</th>
	<th class='fcaption' style='width:1%'>&nbsp;</th>
	<th class='fcaption' style='width:25%'>".LAN_PM_31."</th>
	<th class='fcaption' style='width:auto'>".LAN_PM_5."</th>
	<th class='fcaption' style='width:auto'>".LAN_PM_32."</th>
	<th class='fcaption' style='width:100px'>{PM_COMPOSE: class=block-level}</th>
</tr>
</thead>
	<tbody>
";

//$PM_INBOX_TABLE = "{SETIMAGE: w=30&h=30&crop=1}
$PM_TEMPLATE['inbox']['item'] = "{SETIMAGE: w=30&h=30&crop=1}
<tr class='{PM_STATUS_CLASS}'>
	<td class='forumheader3'>{PM_SELECT}</td>
	<td class='forumheader3'>{PM_ATTACHMENT_ICON}</td>
	<td class='forumheader3'>{PM_AVATAR: shape=circle} {PM_FROM=link}</td>
	<td class='forumheader3'>{PM_SUBJECT=link,inbox}</td>

	<td class='forumheader3'>{PM_DATE}</td>
	<td class='forumheader3' style='text-align: center; white-space: nowrap'>{PM_DELETE=inbox}&nbsp;{PM_BLOCK_USER}</td>
</tr>
";

//$PM_INBOX_EMPTY = "
$PM_TEMPLATE['inbox']['empty'] = "
<tr>
	<td colspan='6' class='forumheader'>".LAN_PM_34."</td>
</tr>
";

//$PM_INBOX_FOOTER = "
$PM_TEMPLATE['inbox']['end'] = "
<tr>
	<td class='forumheader' colspan='3'>
	<input type='hidden' name='pm_come_from' value='inbox' />
	{PM_DELETE_SELECTED}
	</td>
	<td class='forumheader text-right' colspan='3'>
	{PM_NEXTPREV=inbox}
	</td>
</tr>
</tbody>
</table>
";

//$PM_OUTBOX_HEADER = "
$PM_TEMPLATE['outbox']['start'] = "
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

//$PM_OUTBOX_TABLE = "
$PM_TEMPLATE['outbox']['item'] = "
<tr class='{PM_STATUS_CLASS}'>
	<td class='forumheader3'>{PM_SELECT}</td>
	<td class='forumheader3'>{PM_ATTACHMENT_ICON}</td>
	<td class='forumheader3'>{PM_SUBJECT=link,outbox}</td>
		<td class='forumheader3'>{PM_TO=link}</td>
	<td class='forumheader3'>{PM_DATE}</td>
	<td class='forumheader3' style='text-align: center'>{PM_DELETE=outbox}</td>
</tr>
";

//$PM_OUTBOX_EMPTY = "
$PM_TEMPLATE['outbox']['empty'] = "
<tr>
	<td colspan='6' class='forumheader'>".LAN_PM_34."</td>
</tr>
";
/*
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
";*/

//$PM_OUTBOX_FOOTER = "
$PM_TEMPLATE['outbox']['end'] = "
<tr>
	<td class='forumheader' colspan='3'>
	<input type='hidden' name='pm_come_from' value='inbox' />
	{PM_DELETE_SELECTED}
	</td>
	<td class='forumheader text-right' colspan='3'>
	{PM_NEXTPREV=outbox}
	</td>
</tr>
</tbody>
</table>
";



$PM_TEMPLATE['blocked']['start'] = "
<table class='table table-striped fborder'>
<tr>
	<td class='fcaption' style='width:5%'>&nbsp;</td>
	<td class='fcaption' style='width:48%'>".LAN_PM_68."</td>
	<td class='fcaption' style='width:42%'>".LAN_PM_69."</td>
	<td class='fcaption' style='width:5%'>&nbsp;</td>
</tr>
";

$PM_TEMPLATE['blocked']['item'] = "
<tr>
	<td class='forumheader3'>{PM_BLOCKED_SELECT}</td>
	<td class='forumheader3'>{PM_BLOCKED_USER=link}</td>
	<td class='forumheader3'>{PM_BLOCKED_DATE}</td>
	<td class='forumheader3' style='text-align: center'>{PM_BLOCKED_DELETE}</td>
</tr>
";

$PM_TEMPLATE['blocked']['empty'] = "
<tr>
	<td colspan='4' class='forumheader'>".LAN_PM_67."</td>
</tr>
";

$PM_TEMPLATE['blocked']['end'] = "
<tr>
	<td class='forumheader' colspan='4' style='text-align:center'>
	{PM_DELETE_BLOCKED_SELECTED}
	</td>
</tr>
</table>
";



//$PM_SHOW =
$PM_TEMPLATE['show'] =
"<div class='pm-show' style='text-align: center'>
<table class='table table-bordered table-striped fborder'>
<tr>
	<td class='fcaption text-left'>
	<h3>{PM_SUBJECT} <small class='pull-right float-right'>{PM_DATE}</small></h3>
	<small>{PM_FROM_TO}</small>
	<small class='pull-right float-right'>{PM_READ} {PM_DELETE}</small></td>
</tr>
<tr>
	<td class='forumheader3 text-left' style='vertical-align:top'>
	<div class='pm-message'>{PM_MESSAGE}</div>
	{PM_ATTACHMENTS}
	</td>
</tr>
{PM_REPLY}
</table>
</div>
<hr />
";


//$PM_NOTIFY =
$PM_TEMPLATE['notify'] =
"<div>
<h4>".LAN_PM_101."{SITENAME}</h4>
<table class='table table-striped'>
<tr><td>".LAN_PM_102."</td><td>{USERNAME}</td></tr>
<tr><td>".LAN_PM_103."</td><td>{PM_SUBJECT}</td></tr>
<tr><td>".LAN_PM_108."</td><td>{PM_DATE}</td></tr>
<tr><td>".LAN_PM_104."</td><td>{PM_ATTACHMENTS}</td></tr>
</table>
<table class='table'><tr><td class='text-center'><br />
<a class='btn btn-primary btn-lg' href='{PM_URL}'>".LAN_PM_113."</a></td></tr>
</table>
";

