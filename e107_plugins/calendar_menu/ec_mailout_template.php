<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Event calendar mailout - template file
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/ec_mailout_template.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-12-20 22:47:31 $
 * $Author: e107steved $
 */

/**
 *	e107 Event calendar plugin
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id: ec_mailout_template.php,v 1.5 2009-12-20 22:47:31 e107steved Exp $;
 */

/*
This template is used during the subscription mailouts - it is inserted at the front of the text
defined for each category.
Main purpose is to define the 'pre' and 'post' styles, but it can be used much as any E107 template

Language constants should be in the English_mailer.php file
*/

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'_mailer.php');

global $sc_style;

$sc_style['EC_MAIL_HEADING_DATE']['pre'] = '';
$sc_style['EC_MAIL_HEADING_DATE']['post'] = '';

$sc_style['EC_MAIL_SHORT_DATE']['pre'] = '';
$sc_style['EC_MAIL_SHORT_DATE']['post'] = '';

$sc_style['EC_MAIL_TITLE']['pre'] = '';
$sc_style['EC_MAIL_TITLE']['post'] = '';

$sc_style['EC_MAIL_ID']['pre'] = '';
$sc_style['EC_MAIL_ID']['post'] = '';

$sc_style['EC_MAIL_DETAILS']['pre'] = '';
$sc_style['EC_MAIL_DETAILS']['post'] = '';

$sc_style['EC_MAIL_LOCATION']['pre'] = LAN_EC_MAIL_100.' ';
$sc_style['EC_MAIL_LOCATION']['post'] = '';

$sc_style['EC_MAIL_AUTHOR']['pre'] = LAN_EC_MAIL_101.' ';
$sc_style['EC_MAIL_AUTHOR']['post'] = '';

$sc_style['EC_MAIL_CONTACT']['pre'] = LAN_EC_MAIL_102.' ';
$sc_style['EC_MAIL_CONTACT']['post'] = '';

$sc_style['EC_MAIL_THREAD']['pre'] = '';
$sc_style['EC_MAIL_THREAD']['post'] = '';

$sc_style['EC_MAIL_LINK']['pre'] = '';
$sc_style['EC_MAIL_LINK']['post'] = '';

$sc_style['EC_MAIL_CATEGORY']['pre'] = '';
$sc_style['EC_MAIL_CATEGORY']['post'] = '';

$sc_style['EC_MAIL_DATE_START']['pre'] = '';
$sc_style['EC_MAIL_DATE_START']['post'] = '';
$sc_style['EC_MAIL_DATE_START_ALLDAY']['pre'] = LAN_EC_MAIL_103.' ';
$sc_style['EC_MAIL_DATE_START_ALLDAY']['post'] = '';
$sc_style['EC_MAIL_DATE_START_TIMED']['pre'] = LAN_EC_MAIL_104.' ';
$sc_style['EC_MAIL_DATE_START_TIMED']['post'] = '';

$sc_style['EC_MAIL_TIME_START']['pre'] = LAN_EC_MAIL_105;
$sc_style['EC_MAIL_TIME_START']['post'] = '';

$sc_style['EC_MAIL_DATE_END']['pre'] = LAN_EC_MAIL_106.' ';
$sc_style['EC_MAIL_DATE_END']['post'] = '';

$sc_style['EC_MAIL_TIME_END']['pre'] = LAN_EC_MAIL_105;
$sc_style['EC_MAIL_TIME_END']['post'] = '';


?>
