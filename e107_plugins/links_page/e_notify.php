<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
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