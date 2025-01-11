<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	LDAP authorisation for alt_auth plugin - language file
 *
 * $URL$
 * $Id$
 */

/**
 *	e107 Alternate authorisation plugin
 *
 *	@package	e107_plugins
 *	@subpackage	alt_auth
 *	@version 	$Id$;
 */

define('LDAPLAN_1', 'Server address');
define('LDAPLAN_2', 'Base DN or Domain<br />LDAP - Enter BaseDN<br />AD - enter the fqdn eg ad.mydomain.co.uk');
define('LDAPLAN_3', 'LDAP Browsing user<br />Full context of the user who is able to search the directory.');
define('LDAPLAN_4', 'LDAP Browsing password<br />Password for the LDAP Browsing user.');
define('LDAPLAN_5', 'LDAP Version');
define('LDAPLAN_6', 'Configure LDAP auth');
define('LDAPLAN_7', 'eDirectory search filter:');
define('LDAPLAN_8', "This will be used to ensure the username is in the correct tree, <br />e.g. '(objectclass=inetOrgPerson)'");
define('LDAPLAN_9', 'Current search filter will be:');
define('LDAPLAN_10', 'Settings Updated');
define('LDAPLAN_11', 'WARNING:  It appears that the ldap module is not currently available; setting your auth method to LDAP will probably not work!');
define('LDAPLAN_12', 'Server Type');
define('LDAPLAN_13', 'Update settings');
define('LDAPLAN_14', 'OU for AD (e.g. ou=itdept)');


define('SHOW_COPY_HELP', TRUE);
define('SHOW_CONVERSION_HELP', TRUE);
define('LAN_AUTHENTICATE_HELP','This method can be used to authenticate against most LDAP servers, including Novell\'s eDirectory and Microsoft\'s Active Directory. It requires that PHP\'s LDAP extension is loaded. Refer to the wiki for further information.');



