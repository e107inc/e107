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

$advanced['cat']['type'] = 'dropdown';
$advanced['cat']['text'] = LAN_SEARCH_63.':';
$advanced['cat']['list'][] = array('id' => 'all', 'title' => LAN_SEARCH_51);

$advanced_caption['id'] = 'cat';
$advanced_caption['title']['all'] = LNK_SCH_LAN_2;

if ($sql -> db_Select("links_page_cat", "link_category_id, link_category_name")) {
	while ($row = $sql -> db_Fetch()) {
		$advanced['cat']['list'][] = array('id' => $row['link_category_id'], 'title' => $row['link_category_name']);
		$advanced_caption['title'][$row['link_category_id']] = LNK_SCH_LAN_1.' -> '.$row['link_category_name'];
	}
}

$advanced['match']['type'] = 'dropdown';
$advanced['match']['text'] = LAN_SEARCH_52.':';
$advanced['match']['list'][] = array('id' => 0, 'title' => LNK_SCH_LAN_3);
$advanced['match']['list'][] = array('id' => 1, 'title' => LAN_SEARCH_54);

?>