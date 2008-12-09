<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|    	Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/languages/English/admin_alt_auth.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-12-09 20:40:54 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
define('LAN_ALT_1', 'Current authorisation type');
define('LAN_ALT_2', 'Update settings');
define('LAN_ALT_3', 'Choose Alternate Authorisation Type');
define('LAN_ALT_4', 'Configure parameters for');
define('LAN_ALT_5', 'Configure authorisation parameters');
define('LAN_ALT_6', 'Failed connection action');
define('LAN_ALT_7', 'If connection to the alternate method fails, how should that be handled?');
define('LAN_ALT_8', 'User not found action');
define('LAN_ALT_9', 'If username is not found using alternate method, how should that be handled?');

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
define('LAN_ALT_23', 'XUP file field');
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
define('LAN_ALT_49', 'Username = ');
define('LAN_ALT_50', 'Password = ');
define('LAN_ALT_51', '(blank)');
define('LAN_ALT_52', 'Authentication failed - ');
define('LAN_ALT_53', 'unknown cause');
define('LAN_ALT_54', 'could not connect to DB');
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
define('LAN_ALT_70', '');

define('LAN_ALT_FALLBACK', 'Use e107 user table');
define('LAN_ALT_FAIL', 'Failed login');
define('LAN_ALT_UPDATESET', 'Update settings');
define('LAN_ALT_UPDATED','Settings updated');

define('LAN_ALT_AUTH_HELP', 'These are the settings common to all authentication methods, and determine the actions to be taken<br /><br />
	The Extended User Field selection determines which <i>may</i> be added/updated when a user logs in - further configuration is required
	for the specific authentication method.');
define('LAN_ALT_VALIDATE_HELP', 'You can check the settings by using the \'Test Database Access\' section to try and validate a user - this uses exactly 
	the same process as when a user tries to log in, and confirms whether your settings are correct
	');


?>
