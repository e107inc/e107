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
|     $Revision: 1.2 $
|     $Date: 2008-12-23 20:31:30 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

define('ALT_AUTH_PATH', e_PLUGIN.'alt_auth/');

if (e_PAGE == 'alt_auth_conf.php')
{
	include_lan(ALT_AUTH_PATH.'languages/'.e_LANGUAGE.'/admin_alt_auth.php');
	$ns -> tablerender('help',LAN_ALT_AUTH_HELP);
}
else
{
	include_lan(ALT_AUTH_PATH.'languages/'.e_LANGUAGE.'/admin_'.e_PAGE);
	if (!defined('LAN_ALT_VALIDATE_HELP')) include_lan(ALT_AUTH_PATH.'languages/'.e_LANGUAGE.'/admin_alt_auth.php');
	$ns -> tablerender('help',LAN_AUTHENTICATE_HELP.'<br /><br />'.(defined('SHOW_COPY_HELP') ? LAN_ALT_COPY_HELP : '').(defined('SHOW_CONVERSION_HELP') ? LAN_ALT_CONVERSION_HELP : '').LAN_ALT_VALIDATE_HELP);
}

?>