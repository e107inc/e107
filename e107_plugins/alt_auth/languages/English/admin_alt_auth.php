<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Language file
 *
 * $URL$
 * $Id$
 * 
 */
 
/**
 *	e107 Alternate authorisation plugin
 *
 *	@package	e107_plugins
 *	@subpackage	alt_auth
 *	@version 	$Id$;
 */

define('LAN_ALT_1', 'Primary authorisation type');
define('LAN_ALT_2', 'Update settings');
define('LAN_ALT_3', 'Choose Alternate Authorisation Type');
define('LAN_ALT_4', 'Configure parameters for');
define('LAN_ALT_5', 'Configure authorisation parameters');
define('LAN_ALT_6', 'Failed connection action');
define('LAN_ALT_7', 'If connection to the primary authorisation type fails (and its not the local e107 DB), how should that be handled?');
define('LAN_ALT_8', 'Secondary authorisation type');
define('LAN_ALT_9', 'This is used if the primary authorisation method cannot find the user');

define('LAN_ALT_10', 'User login name field');
define('LAN_ALT_11', 'User password field');
define('LAN_ALT_12', 'User email field');
define('LAN_ALT_13', 'Hide email? field');
define('LAN_ALT_14', 'User display name field');
define('LAN_ALT_15', 'User real name field');
define('LAN_ALT_16', 'User Custom Title field');
define('LAN_ALT_17', 'Signature field');
define('LAN_ALT_18', 'Avatar field');
define('LAN_ALT_19', 'Photo field');
define('LAN_ALT_20', 'Join date field');
define('LAN_ALT_21', 'Ban status field');
define('LAN_ALT_22', 'Class membership field');
//define('LAN_ALT_23', 'XUP file field');
define('LAN_ALT_24', 'Password salt field');
define('LAN_ALT_25', '(sometimes combined with password for added security)');
define('LAN_ALT_26', 'Database type:');
define('LAN_ALT_27', 'To transfer a field value into the local database, specify the field name in the corresponding box below. (Username and password are always transferred)
		<br />Leave the field blank for it not to be transferred at all');

define('LAN_ALT_29', 'Auth methods');
define('LAN_ALT_30', 'Configure ');
define('LAN_ALT_31', 'Main configuration');
define('LAN_ALT_32', 'Server:');
define('LAN_ALT_33', 'Username:');
define('LAN_ALT_34', 'Password:');
define('LAN_ALT_35', 'Database:');
define('LAN_ALT_36', 'Table:');
define('LAN_ALT_37', 'Username Field:');
define('LAN_ALT_38', 'Password Field:');
define('LAN_ALT_39', 'Table Prefix:');

define('LAN_ALT_40', 'Test database access');
define('LAN_ALT_41', ' (using above credentials)');
define('LAN_ALT_42', 'If a username and password are entered, that user will also be validated');
define('LAN_ALT_43', 'Connection to database successful');
define('LAN_ALT_44', 'Connection to database failed');
define('LAN_ALT_45', 'Username lookup successful');
define('LAN_ALT_46', 'Uername lookup failed');
define('LAN_ALT_47', 'Test');
define('LAN_ALT_48', 'Previous validation');
define('LAN_ALT_49', 'Username');
define('LAN_ALT_50', 'Password');
define('LAN_ALT_51', '(blank)');
define('LAN_ALT_52', 'Authentication failed - ');
define('LAN_ALT_53', 'unknown cause');
define('LAN_ALT_54', 'could not connect to DB / service provider');
define('LAN_ALT_55', 'invalid user');
define('LAN_ALT_56', 'bad password');
define('LAN_ALT_57', 'method not available');
define('LAN_ALT_58', 'Authentification successful');
define('LAN_ALT_59', 'Retrieved parameters:');
define('LAN_ALT_60', 'Extended User Fields');
define('LAN_ALT_61', 'Allow');
define('LAN_ALT_62', 'Field Name');
define('LAN_ALT_63', 'Description');
define('LAN_ALT_64', 'Type');
define('LAN_ALT_65', 'Alternate Authentication');
define('LAN_ALT_66', 'This plugin allows for alternate authentication methods.');
define('LAN_ALT_67', 'Configure Alt auth');
define('LAN_ALT_68', 'Alt auth service is now set up.  You will now need to configure your preferred method.');
define('LAN_ALT_69', '');
define('LAN_ALT_70', 'None');
define('LAN_ALT_71', 'TRUE/FALSE');
define('LAN_ALT_72', 'Upper case');
define('LAN_ALT_73', 'Lower case');
define('LAN_ALT_74', 'Upper first');
define('LAN_ALT_75', 'Upper words');
define('LAN_ALT_76', 'User class restriction (a numeric value - zero or blank for everyone)');
define('LAN_ALT_77', 'Only users in this class (on the database set above) are permitted access');
define('LAN_ALT_78', 'Failed password action');
define('LAN_ALT_79', 'If user exists in primary DB, but enters an incorrect password, how should that be handled?');
define('LAN_ALT_80', "Port: ");






define('IMPORTDB_LAN_2', 'Plain Text');
define('IMPORTDB_LAN_3', 'Joomla salted');
define('IMPORTDB_LAN_4', 'Mambo salted');
define('IMPORTDB_LAN_5', 'SMF (SHA1)');
define('IMPORTDB_LAN_6', 'Generic SHA1');
define('IMPORTDB_LAN_7', 'MD5 (E107 original)');
define('IMPORTDB_LAN_8', 'E107 salted (option 2.0 on)');
define('IMPORTDB_LAN_12', 'PHPBB2/PHPBB3 salted');
define('IMPORTDB_LAN_13', 'WordPress salted');
define('IMPORTDB_LAN_14', 'Magento salted');



define('LAN_ALT_FALLBACK', 'Use secondary authorisation');
define('LAN_ALT_FAIL', 'Failed login');
define('LAN_ALT_UPDATESET', 'Update settings');
define('LAN_ALT_UPDATED','Settings updated');

define('LAN_ALT_AUTH_HELP', 'These are the settings common to all authentication methods, and determine the actions to be taken<br /><br />
	The Extended User Field selection determines which <i>may</i> be added/updated when a user logs in - further configuration is required
	for the specific authentication method.');
define('LAN_ALT_VALIDATE_HELP', 'You can check the settings by using the \'Test Database Access\' section to try and validate a user - this uses exactly 
	the same process as when a user tries to log in, and confirms whether your settings are correct.<br />
	If you have configured some parameters to be copied to the user table on successful login, these are also listed.
	');
define('LAN_ALT_COPY_HELP', 'You can select fields to copy from the remote database into the user database by entering the appropriate names.<br /><br />
	');
define('LAN_ALT_CONVERSION_HELP', 'For some fields, the drop-down box to the right of the field entry box selects a conversion which may be applied to the value
	read from the remote database; if \'none\' is selected, the value is copied as received. Conversions are:<br />
	<b>TRUE/FALSE</b> - the words \'TRUE\' and \'FALSE\' (and their lower/mixed case equivalents) are converted to the Booleans 1 and zero.<br />
	<b>Upper case</b> - All letters are converted to upper case<br />
	<b>Lower case</b> - All letters are converted to lower case<br />
	<b>Upper first</b> - the first character is converted to upper case<br />
	<b>Upper words</b> - the first letter of each word is converted to upper case<br />
	<br />
	<br />');


