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

if(deftrue('BOOTSTRAP') && deftrue('FONTAWESOME'))
{
	define('PM_INBOX_ICON', e107::getParser()->toGlyph('fa-inbox'));
	// Icon candidate to stacked fontawesome icons...
	define('PM_OUTBOX_ICON', e107::getParser()->toGlyph('fa-inbox').e107::getParser()->toGlyph('fa-arrow-up'));
	// Icon candidate to animated fontawesome icons...
	define('NEWPM_ANIMATION', e107::getParser()->toGlyph('fa-envelope'));
}
else
{
	if (!defined('PM_INBOX_ICON')) define('PM_INBOX_ICON', "<img src='".e_PLUGIN_ABS."pm/images/mail_get.png' class='icon S16' alt='".LAN_PLUGIN_PM_INBOX."' title='".LAN_PLUGIN_PM_INBOX."' />");
	if (!defined('PM_OUTBOX_ICON')) define('PM_OUTBOX_ICON', "<img src='".e_PLUGIN_ABS."pm/images/mail_send.png' class='icon S16' alt='".LAN_PLUGIN_PM_OUTBOX."' title='".LAN_PLUGIN_PM_OUTBOX."' />");
	if (!defined('NEWPM_ANIMATION')) define('NEWPM_ANIMATION', "<img src='".e_PLUGIN_ABS."pm/images/newpm.gif' alt='' />");
}

//define('PM_SEND_LINK', LAN_PLUGIN_PM_NEW);
/*
$sc_style['PM_SEND_PM_LINK']['pre'] = "<br /><br />";
$sc_style['PM_SEND_PM_LINK']['post'] = "";

$sc_style['PM_INBOX_FILLED']['pre'] = "[";
$sc_style['PM_INBOX_FILLED']['post'] = "%]";

$sc_style['PM_OUTBOX_FILLED']['pre'] = "[";
$sc_style['PM_OUTBOX_FILLED']['post'] = "%]";

$sc_style['PM_NEWPM_ANIMATE']['pre'] = "<a href='".e_PLUGIN_ABS."pm/pm.php?inbox'>";
$sc_style['PM_NEWPM_ANIMATE']['post'] = "</a>";

$sc_style['PM_BLOCKED_SENDERS_MANAGE']['pre'] = "<br />[ <a href='".e_PLUGIN_ABS."pm/pm.php?blocked'>";
$sc_style['PM_BLOCKED_SENDERS_MANAGE']['post'] = '</a> ]';
*/
//$PM_MENU_WRAPPER['PM_SEND_PM_LINK']= "<br /><br />{---}";
$PM_MENU_WRAPPER['PM_SEND_PM_LINK']= "<a class='btn btn-mini btn-xs btn-default btn-secondary' href='{---}'>".LAN_PLUGIN_PM_NEW."</a>";
$PM_MENU_WRAPPER['PM_INBOX_FILLED']=$PM_MENU_WRAPPER['PM_OUTBOX_FILLED']= "[{---}%]";
$PM_MENU_WRAPPER['PM_NEWPM_ANIMATE']= "<a href='".e_PLUGIN_ABS."pm/pm.php?inbox'>{---}</a>";
$PM_MENU_WRAPPER['PM_BLOCKED_SENDERS_MANAGE']= "<br />[ <a href='".e_PLUGIN_ABS."pm/pm.php?blocked'>{---}</a> ]";

//	$pm_menu_template = "
	$PM_MENU_TEMPLATE = "
	<a href='".e_PLUGIN_ABS."pm/pm.php?inbox'>".PM_INBOX_ICON."</a>
	<a href='".e_PLUGIN_ABS."pm/pm.php?inbox'>".LAN_PLUGIN_PM_INBOX."</a>
	{PM_NEWPM_ANIMATE}
	<br />
	{PM_INBOX_TOTAL} ".LAN_PM_36.", {PM_INBOX_UNREAD} ".LAN_PM_37." {PM_INBOX_FILLED}
	<br />
	<a href='".e_PLUGIN_ABS."pm/pm.php?outbox'>".PM_OUTBOX_ICON."</a>
	<a href='".e_PLUGIN_ABS."pm/pm.php?outbox'>".LAN_PLUGIN_PM_OUTBOX."</a><br />
	{PM_OUTBOX_TOTAL} ".LAN_PM_36.", {PM_OUTBOX_UNREAD} ".LAN_PM_37." {PM_OUTBOX_FILLED}
	{PM_SEND_PM_LINK}
	{PM_BLOCKED_SENDERS_MANAGE}
	";


