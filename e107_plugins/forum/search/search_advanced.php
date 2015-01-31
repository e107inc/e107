<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/search/search_advanced.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

$advanced['forum']['type'] = 'dropdown';
$advanced['forum']['text'] = FOR_SCH_LAN_2.':';
$advanced['forum']['list'][] = array('id' => 'all', 'title' => LAN_PLUGIN_FORUM_ALLFORUMS);

$advanced_caption['id'] = 'forum';
$advanced_caption['title']['all'] = LAN_PLUGIN_FORUM_ALLFORUMS;

if ($sql -> db_Select_gen("SELECT f.forum_id, f.forum_name FROM #forum AS f LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent WHERE f.forum_parent != 0 AND fp.forum_class IN (".USERCLASS_LIST.") AND f.forum_class IN (".USERCLASS_LIST.")")) {
	while ($row = $sql -> db_Fetch()) {
		$advanced['forum']['list'][] = array('id' => $row['forum_id'], 'title' => $row['forum_name']);
		$advanced_caption['title'][$row['forum_id']] = LAN_PLUGIN_FORUM_NAME.' -> '.$row['forum_name'];
	}
}

$advanced['date']['type'] = 'date';
$advanced['date']['text'] = LAN_DATE_POSTED.':';

$advanced['author']['type'] = 'author';
$advanced['author']['text'] = LAN_SEARCH_61.':';

$advanced['match']['type'] = 'dropdown';
$advanced['match']['text'] = LAN_SEARCH_52.':';
$advanced['match']['list'][] = array('id' => 0, 'title' => FOR_SCH_LAN_4);
$advanced['match']['list'][] = array('id' => 1, 'title' => LAN_SEARCH_54);

?>