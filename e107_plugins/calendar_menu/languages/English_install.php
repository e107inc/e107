<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * These constants are used solely during install/uninstall - in some cases to set defaults into the database
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/languages/English_install.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

// Install
define('EC_ADINST_LAN_01', "Forthcoming event:\n\n{EC_MAIL_CATEGORY}\n\n{EC_MAIL_TITLE} on {EC_MAIL_HEADING_DATE}{EC_MAIL_TIME_START}\n\n
{EC_MAIL_DETAILS}\n\nFor further details: {EC_EVENT_LINK=Click Here}\n\nor {EC_MAIL_CONTACT} for further information.");
define('EC_ADINST_LAN_02', "Calendar event imminent:\n\n{EC_MAIL_CATEGORY}\n\n{EC_MAIL_TITLE} on {EC_MAIL_HEADING_DATE}{EC_MAIL_TIME_START}\n\n{EC_MAIL_DETAILS}\n\n
For further details see the calendar entry on the web site:\n{EC_MAIL_LINK=Click Here}\n\n {EC_MAIL_CONTACT} for further details");
define('EC_ADINST_LAN_03', 'Default category - mailout messages are used if none defined for any other category');
define('EC_ADINST_LAN_04', 'To activate please go to your menus screen and select the calendar_menu into one of your menu areas.');
define('EC_ADINST_LAN_05', 'Configure Event Calendar');
define('EC_ADINST_LAN_06', 'Default category entered');
define('EC_ADINST_LAN_07', 'Error adding default category');
define('EC_ADINST_LAN_08', 'Default category already in DB');
