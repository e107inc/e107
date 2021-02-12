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

// Some password-related strings moved to admin_alt_auth.php for generic use
//define('IMPORTDB_LAN_1', 'Database type');
//define('IMPORTDB_LAN_2', 'Plain Text');
//define('IMPORTDB_LAN_3', 'Joomla salted');
//define('IMPORTDB_LAN_4', 'Mambo salted');
//define('IMPORTDB_LAN_5', 'SMF (SHA1)');
//define('IMPORTDB_LAN_6', 'Generic SHA1');
//define('IMPORTDB_LAN_7', 'MD5 (E107 original)');
//define('IMPORTDB_LAN_8', 'E107 salted (option 2.0 on)');
define('IMPORTDB_LAN_9', 'Password Method:');
define('IMPORTDB_LAN_10', 'Configure imported database password type');
define('IMPORTDB_LAN_11', 'This option is to be used when you have imported some other user-based system into E107. 
							It allows you to accept passwords encoded in the selected non-standard format. 
							Each user\'s password is converted to E107 format when they log in.');
//define('IMPORTDB_LAN_12', 'PHPBB2/PHPBB3 salted');
//define('IMPORTDB_LAN_13', 'WordPress salted');
//define('IMPORTDB_LAN_14', 'Magento salted');


define('LAN_AUTHENTICATE_HELP','This authentication method is to be used <i>only</i> when you have imported a user database into E107, and the password is in an incompatible format. The
  original password is read from the local database, and validated against the storage format of the original system. If it verifies, its converted to the current E107-compatible format and
  stored in the database. After a while you can usually disable the alt-auth plugin, since active users will all have their passwords stored in a compatible format.');


