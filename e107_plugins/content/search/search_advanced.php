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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/search/search_advanced.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-11-18 01:05:28 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }

$advanced['cat']['type'] = 'dropdown';
$advanced['cat']['text'] = LAN_SEARCH_63.':';
$advanced['cat']['list'][] = array('id' => 'all', 'title' => LAN_SEARCH_51);

$advanced_caption['id'] = 'cat';
$advanced_caption['title']['all'] = CONT_SCH_LAN_2;

if ($sql -> db_Select("pcontent", "content_id, content_heading", "LEFT(content_parent,1)='0'")) {
	while ($row = $sql -> db_Fetch()) {
		$advanced['cat']['list'][] = array('id' => $row['content_id'], 'title' => $row['content_heading']);
		$advanced_caption['title'][$row['content_id']] = $row['content_heading'];
	}
}

$advanced['date']['type'] = 'date';
$advanced['date']['text'] = LAN_SEARCH_68.':';

$advanced['match']['type'] = 'dropdown';
$advanced['match']['text'] = LAN_SEARCH_52.':';
$advanced['match']['list'][] = array('id' => 0, 'title' => LAN_SEARCH_53);
$advanced['match']['list'][] = array('id' => 1, 'title' => LAN_SEARCH_54);

?>