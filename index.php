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
|     $Source: /cvs_backup/e107_0.7/index.php,v $
|     $Revision: 1.15 $
|     $Date: 2005-08-29 16:13:03 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

require_once('class2.php');

if (!is_array($pref['frontpage'])) {
	if (!$pref['frontpage']) {
		$up_pref = 'news.php';
	} else if ($pref['frontpage'] == 'links') {
		$up_pref = $PLUGINS_DIRECTORY.'links_page/links.php';
	} else if ($pref['frontpage'] == 'forum') {
		$up_pref = $PLUGINS_DIRECTORY.'forum/forum.php';
	} else if (is_numeric($pref['frontpage'])) {
		$up_pref = $PLUGINS_DIRECTORY.'content/content.php?content.'.$pref['frontpage'];
	} else if (substr($pref['frontpage'], -1) != '/' && strpos($pref['frontpage'], '.') === FALSE) {
		$up_pref = $pref['frontpage'].'.php';
	} else {
		$up_pref = $pref['frontpage'];
	}
	unset($pref['frontpage']);
	$pref['frontpage']['all'] = $up_pref;
	save_prefs();
}

$query = (e_QUERY && e_QUERY != '' ? '?'.e_QUERY : '');

if ($pref['membersonly_enabled'] && !USER) {
	header('location: '.e_LOGIN);
	exit;
} else if (isset($pref['frontpage']['all']) && $pref['frontpage']['all']) {
	$location = ((strpos($pref['frontpage']['all'], 'http') === FALSE) ? e_BASE : '').$pref['frontpage']['all'].$query;
} else if (ADMIN) {
	$location =  ((strpos($pref['frontpage']['254'], 'http') === FALSE) ? e_BASE : '').$pref['frontpage']['254'].$query;
} else if (USER) {
	require_once(e_HANDLER.'userclass_class.php');
	$class_list = get_userclass_list();
	foreach ($class_list as $fp_class) {
		$inclass = false;
		if (!$inclass && check_class($fp_class['userclass_id'])) {
			$location = ((strpos($pref['frontpage'][$fp_class['userclass_id']], 'http') === FALSE) ? e_BASE : '').$pref['frontpage'][$fp_class['userclass_id']].$query;
			$inclass = true;
		}
	}
	$location = ((strpos($pref['frontpage']['253'], 'http') === FALSE) ? e_BASE : '').$pref['frontpage']['253'].$query;
} else {
	$location = ((strpos($pref['frontpage']['252'], 'http') === FALSE) ? e_BASE : '').$pref['frontpage']['252'].$query;
}

// handle redirect and include front page methods
if(isset($pref['frontpage_method']) && $pref['frontpage_method'] == "include") {
	if($location == "news.php") {
		require_once("news.php");
	} elseif ($location == PLUGINS_DIRECTORY."forum/forum.php") {
		require_once($PLUGINS_DIRECTORY."forum/forum.php");
	} elseif (preg_match('/^page\.php\?([0-9]?)$/', $location)) {
		$e_QUERY = preg_match('/^page\.php\?([0-9]?)$/', $location);
		require_once("page.php");
	} else {
		header("Location: {$location}");
		exit();
	}
} else {
	header("Location: {$location}");
	exit();
}

?>