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


return [
    'LDAPLAN_1' => "Server address",
    'LDAPLAN_2' => "Base DN or Domain<br />LDAP - Enter BaseDN<br />AD - enter the fqdn eg ad.mydomain.co.uk",
    'LDAPLAN_3' => "LDAP Browsing user<br />Full context of the user who is able to search the directory.",
    'LDAPLAN_4' => "LDAP Browsing password<br />Password for the LDAP Browsing user.",
    'LDAPLAN_5' => "LDAP Version",
    'LDAPLAN_6' => "Configure LDAP auth",
    'LDAPLAN_7' => "eDirectory search filter:",
    'LDAPLAN_8' => "This will be used to ensure the username is in the correct tree, <br />e.g. '(objectclass=inetOrgPerson)'",
    'LDAPLAN_9' => "Current search filter will be:",
    'LDAPLAN_10' => "Settings Updated",
    'LDAPLAN_11' => "WARNING:  It appears that the ldap module is not currently available; setting your auth method to LDAP will probably not work!",
    'LDAPLAN_12' => "Server Type",
    'LDAPLAN_13' => "Update settings",
    'LDAPLAN_14' => "OU for AD (e.g. ou=itdept)",
    'LAN_AUTHENTICATE_HELP' => "This method can be used to authenticate against most LDAP servers, including Novell's eDirectory and Microsoft's Active Directory. It requires that PHP's LDAP extension is loaded. Refer to the wiki for further information.",
];
