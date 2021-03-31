<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2015 e107 Inc
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
+----------------------------------------------------------------------------+
*/
require_once('class2.php');

if (!varset($pref['maintainance_flag']) && !getperms('0')) // Allow main admin to test and view template before going offline.
{
	e107::redirect();
	exit();
}

header('Content-type: text/html; charset=utf-8');
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 3600'); // in seconds

e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

// require_once(e_CORE.'shortcodes/batch/sitedown_shortcodes.php');

$sitedown_shortcodes= e107::getScBatch('sitedown');

if (!isset($SITEDOWN_TABLE))
{
	if (file_exists(THEME.'templates/sitedown_template.php')) //v2.x location. 
	{
		require_once(THEME.'templates/sitedown_template.php');
	}
	elseif (file_exists(THEME.'sitedown_template.php')) //v1.x location
	{
		require_once(THEME.'sitedown_template.php');
	}
	else
	{
		require_once(e_CORE.'templates/sitedown_template.php');
	}
}

echo $tp->parseTemplate($SITEDOWN_TABLE, true, $sitedown_shortcodes);
