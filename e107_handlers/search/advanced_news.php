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
$advanced['cat']['text'] = LAN_SEARCH_55.':';
$advanced['cat']['list'][] = array('id' => 'all', 'title' => LAN_SEARCH_51);

$advanced_caption['id'] = 'cat';
$advanced_caption['title']['all'] = LAN_SEARCH_56;

if ($sql -> db_Select("news_category", "category_id, category_name")) {
	while($row = $sql -> db_Fetch()) {
		$advanced['cat']['list'][] = array('id' => $row['category_id'], 'title' => $row['category_name']);
		$advanced_caption['title'][$row['category_id']] = 'News -> '.$row['category_name'];
	}
}

$advanced['date']['type'] = 'date';
$advanced['date']['text'] = LAN_SEARCH_50.':';

$advanced['match']['type'] = 'dropdown';
$advanced['match']['text'] = LAN_SEARCH_52.':';
$advanced['match']['list'][] = array('id' => 0, 'title' => LAN_SEARCH_53);
$advanced['match']['list'][] = array('id' => 1, 'title' => LAN_SEARCH_54);

?>