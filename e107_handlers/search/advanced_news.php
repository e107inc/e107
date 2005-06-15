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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/advanced_news.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-15 15:18:40 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

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