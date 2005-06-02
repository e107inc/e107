<?php
/*
+ ----------------------------------------------------------------------------+
e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/index.php,v $
|     $Revision: 1.12 $
|     $Date: 2005-06-02 04:30:04 $
|     $Author: sweetas $
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
		$up_pref = $PLUGINS_DIRECTORY.'content/content.php?type.'.$pref['frontpage'];
	} else if (strpos($pref['frontpage'], '.')===FALSE) {
		if (!preg_match("#/$#",$pref['frontpage'])) {
			$up_pref = $pref['frontpage'].'.php';
		}
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
} else if ($pref['frontpage']['all']) {
	header('location: '.((strpos($pref['frontpage']['all'], 'http') === FALSE) ? e_BASE : '').$pref['frontpage']['all'].$query);
	exit;
} else if (ADMIN) {
	header('location: '.((strpos($pref['frontpage']['254'], 'http') === FALSE) ? e_BASE : '').$pref['frontpage']['254'].$query);
	exit;
} else if (USER) {
	require_once(e_HANDLER.'userclass_class.php');
	$class_list = get_userclass_list();
	foreach ($class_list as $fp_class) {
		if (check_class($fp_class['userclass_id'])) {
			header('location: '.((strpos($pref['frontpage'][$fp_class['userclass_id']], 'http') === FALSE) ? e_BASE : '').$pref['frontpage'][$fp_class['userclass_id']].$query);
			exit;
		}
	}
	header('location: '.((strpos($pref['frontpage']['253'], 'http') === FALSE) ? e_BASE : '').$pref['frontpage']['253'].$query);
	exit;
} else {
	header('location: '.((strpos($pref['frontpage']['252'], 'http') === FALSE) ? e_BASE : '').$pref['frontpage']['252'].$query);
	exit;
}

?>