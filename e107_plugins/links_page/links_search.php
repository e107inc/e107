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
|     $Revision: 1.1 $
|     $Date: 2005-03-14 16:03:27 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'link_id, link_name, link_description, link_url';
$search_fields = array('link_name', 'link_description');
$weights = array('1.2', '0.6');
$no_results = LAN_198;
$where = "";
$order = "";

$ps = $sch -> parsesearch('links_page', $return_fields, $search_fields, $weights, 'search_links', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_links($row) {
	$res['link'] = e_PLUGIN."links_page/links.php?".$row['link_id'];
	$res['title'] = $row['link_name'];
	$res['summary'] = $row['link_description'];
	$res['detail'] = "<a href='".e_PLUGIN."links_page/links.php?".$row['link_id']."'>".$row['link_url']."</a>";
	return $res;
}

?>