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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/links_search.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-03-21 22:11:47 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'l.link_id, l.link_name, l.link_description, l.link_url, l.link_category, c.link_category_name';
$search_fields = array('l.link_name', 'l.link_description');
$weights = array('1.2', '0.6');
$no_results = LAN_198;
$where = "";
$order = "";
$table = "links_page AS l LEFT JOIN #links_page_cat AS c ON l.link_category = c.link_category_id";

$ps = $sch -> parsesearch($table, $return_fields, $search_fields, $weights, 'search_links', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_links($row) {
	$res['link'] = e_PLUGIN."links_page/links.php?".$row['link_id'];
	$res['pre_title'] = $row['link_category_name']." | ";
	$res['title'] = $row['link_name'];
	$res['summary'] = $row['link_description'];
	$res['detail'] = "<a href='".e_PLUGIN."links_page/links.php?".$row['link_id']."'>".$row['link_url']."</a>";
	return $res;
}

?>