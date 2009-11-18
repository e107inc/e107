<?php 
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * News frontend
 *
 * $Source: /cvs_backup/e107_0.8/index.php,v $
 * $Revision: 1.8 $
 * $Date: 2009-11-18 01:04:24 $
 * $Author: e107coders $
 */

require_once ('class2.php');

if (file_exists('index_include.php'))
{
	include ('index_include.php');
}

$query = (e_QUERY && e_QUERY != '' && !$_GET['elan']) ? '?'.e_QUERY : '';
$location = '';

if ($pref['membersonly_enabled'] && !USER)
{
	header('location: '.e_LOGIN);
	exit;
}

$class_list = explode(',', USERCLASS_LIST);

if (isset($pref['frontpage']['all']) && $pref['frontpage']['all'])
{ // 0.7 method
	$location = ((strpos($pref['frontpage']['all'], 'http') === FALSE) ? e_BASE : '').$pref['frontpage']['all'].$query;
}
else
{ // This is the 'new' method - assumes $pref['frontpage'] is an ordered list of rules
	foreach ($pref['frontpage'] as $fk=>$fp)
	{
		if (in_array($fk, $class_list))
		{
			// Debateable whether we should append $query - we may be redirecting to a custom page, for example
			if (strpos($fp, '{') !== FALSE)
			{
				$location = $tp->replaceConstants($fp).$query;
			}
			else
			{
				$location = ((strpos($fp, 'http') === FALSE) ? e_BASE : '').$fp.$query;
			}
		break;
		}
	}
}

if (!$location)
{ // Try and use the 'old' method (this bit can go later)
	if (ADMIN)
	{
		$location = ((strpos($pref['frontpage'][e_UC_ADMIN], 'http') === FALSE) ? e_BASE : '').$pref['frontpage'][e_UC_ADMIN].$query;
	}
	elseif (USER)
	{ // This is the key bit - what to do for a 'normal' logged in user
		// We have USERCLASS_LIST - comma separated. Also e_CLASS_REGEXP
		foreach ($class_list as $fp_class)
		{
			$inclass = false;
			if (!$inclass && check_class($fp_class['userclass_id']))
			{
				$location = ((strpos($pref['frontpage'][$fp_class['userclass_id']], 'http') === FALSE) ? e_BASE : '').$pref['frontpage'][$fp_class['userclass_id']].$query;
				$inclass = true;
			}
		}
		$location = $location ? $location : ((strpos($pref['frontpage'][e_UC_MEMBER], 'http') === FALSE) ? e_BASE : '').$pref['frontpage'][e_UC_MEMBER].$query;
	}
	else
	{
		$location = ((strpos($pref['frontpage'][e_UC_GUEST], 'http') === FALSE) ? e_BASE : '').$pref['frontpage'][e_UC_GUEST].$query;
	}
}

if (!trim($location))
	$location = 'news.php';
	
list($page, $str) = explode("?", $location."?"); // required to prevent infinite looping when queries are used on index.php.
if ($page == "index.php") // Welcome Message is the front-page.
{
	require_once (HEADERF);
	require_once (FOOTERF);
	exit;
}
else
{ // redirect to different frontpage.
	header("Location: {$location}");
}
exit();


?>