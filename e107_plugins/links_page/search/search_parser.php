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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/search/search_parser.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-15 15:18:41 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

// advanced 
$advanced_where = "";
if (isset($_GET['cat']) && is_numeric($_GET['cat'])) {
	$advanced_where .= " l.link_category='".$_GET['cat']."' AND";
}

if (isset($_GET['match']) && $_GET['match']) {
	$search_fields = array('l.link_name');
} else {
	$search_fields = array('l.link_name', 'l.link_description');
}

// basic
$return_fields = 'l.link_id, l.link_name, l.link_description, l.link_url, l.link_category, l.link_class, c.link_category_name';
$weights = array('1.2', '0.6');
$no_results = LAN_198;
$where = "l.link_class IN (".USERCLASS_LIST.") AND".$advanced_where;
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