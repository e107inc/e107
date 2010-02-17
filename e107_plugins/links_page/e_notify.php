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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/e_notify.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

if(defined('ADMIN_PAGE') && ADMIN_PAGE === true)
{
	include_lan(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");
	$config_category = NT_LAN_LP_1;
	$config_events = array('linksub' => NT_LAN_LP_2);
}


if (!function_exists('notify_linksub'))
{
	function notify_linksub($data)
	{
		global $nt, $_lanfile;
		include_lan(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");
		foreach ($data as $key => $value)
		{
			$message .= $key.': '.$value.'<br />';
		}
		$nt -> send('linksub', NT_LAN_LP_3, $message);
	}
}

?>