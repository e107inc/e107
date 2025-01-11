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

define('E107DB_LAN_1', 'e107 format database');
define('E107DB_LAN_9', 'Password Method:');
define('E107DB_LAN_10', 'Configure e107 db auth');
define('E107DB_LAN_11', 'Check the box against any field you wish to be transferred to the local database:');


define('IMPORTDB_LAN_7', 'MD5 (e107 original)');
define('IMPORTDB_LAN_8', 'e107 salted (option 2.0 on)');


define('LAN_AUTHENTICATE_HELP','This authentication method is to be used with a second E107 database, which may use a different password format to this system. The
  original password is read from the local database, and validated against the storage format of the original system. If it verifies, its converted to the current E107-compatible format and
  stored in the database.');


