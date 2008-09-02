<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/e_help.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-09-02 19:39:12 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

define('ALT_AUTH_PATH', e_PLUGIN.'alt_auth/');
if (!include_lan(ALT_AUTH_PATH.'languages/'.e_LANGUAGE.'/lan_'.e_PAGE)) return 'No help!';

if (e_PAGE == 'alt_auth_conf.php')
{
	$ns -> tablerender('help',LAN_ALT_AUTH_HELP);
}
else
{
	if (!defined('LAN_ALT_VALIDATE_HELP')) include_lan(ALT_AUTH_PATH.'languages/'.e_LANGUAGE.'/lan_alt_auth_conf.php');
	$ns -> tablerender('help',LAN_AUTHENTICATE_HELP.'<br /><br />'.LAN_ALT_VALIDATE_HELP);
}

?>