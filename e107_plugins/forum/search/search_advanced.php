<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/search/search_advanced.php,v $
 * $Revision: 1.2 $
 * $Date: 2009-11-17 13:48:46 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$advanced['forum']['type'] = 'dropdown';
$advanced['forum']['text'] = FOR_SCH_LAN_2.':';
$advanced['forum']['list'][] = array('id' => 'all', 'title' => FOR_SCH_LAN_3);

$advanced_caption['id'] = 'forum';
$advanced_caption['title']['all'] = FOR_SCH_LAN_3;

if ($sql -> db_Select_gen("SELECT f.forum_id, f.forum_name FROM #forum AS f LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent WHERE f.forum_parent != 0 AND fp.forum_class IN (".USERCLASS_LIST.") AND f.forum_class IN (".USERCLASS_LIST.")")) {
	while ($row = $sql -> db_Fetch()) {
		$advanced['forum']['list'][] = array('id' => $row['forum_id'], 'title' => $row['forum_name']);
		$advanced_caption['title'][$row['forum_id']] = FOR_SCH_LAN_1.' -> '.$row['forum_name'];
	}
}

$advanced['date']['type'] = 'date';
$advanced['date']['text'] = LAN_SEARCH_50.':';

$advanced['author']['type'] = 'author';
$advanced['author']['text'] = LAN_SEARCH_61.':';

$advanced['match']['type'] = 'dropdown';
$advanced['match']['text'] = LAN_SEARCH_52.':';
$advanced['match']['list'][] = array('id' => 0, 'title' => FOR_SCH_LAN_4);
$advanced['match']['list'][] = array('id' => 1, 'title' => LAN_SEARCH_54);

?>