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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/search_download.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-03-14 09:05:15 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'd.download_id, d.download_category, d.download_name, d.download_description, d.download_author, d.download_author_website, d.download_datestamp, c.download_category_name';
$search_fields = array('d.download_name', 'd.download_description', 'd.download_author', 'd.download_author_website');
$weights = array('1.2', '0.6', '0.6', '0.4');
$no_results = LAN_198;
$where = "download_active = '1' AND";
$order = array('download_datestamp' => DESC);
$table = "download AS d LEFT JOIN #download_category AS c ON d.download_category = c.download_category_id";

$ps = $sch -> parsesearch($table, $return_fields, $search_fields, $weights, 'search_downloads', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_downloads($row) {
	$res['link'] = "download.php?view.".$row['download_id'];
	$res['title'] = $row['download_name'];
	$res['summary'] = $row['download_description'];
	$res['detail'] = LAN_SEARCH_14." ".$row['download_category_name']." | ".LAN_SEARCH_15." ".$row['download_author'];
	return $res;
}

?>