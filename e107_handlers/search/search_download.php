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
 * $Source: /cvs_backup/e107_0.8/e107_handlers/search/search_download.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

// advanced 
/*
$advanced_where = "";
if (isset($_GET['cat']) && is_numeric($_GET['cat'])) {
	$advanced_where .= " d.download_category='".$_GET['cat']."' AND";
}

if (isset($_GET['time']) && is_numeric($_GET['time'])) {
	$advanced_where .= " d.download_datestamp ".($_GET['on'] == 'new' ? '>=' : '<=')." '".(time() - $_GET['time'])."' AND";
}

if (isset($_GET['author']) && $_GET['author'] != '') {
	$advanced_where .= " (d.download_author = '".$tp -> toDB($_GET['author'])."') AND";
}

if (isset($_GET['match']) && $_GET['match']) {
	$search_fields = array('d.download_name');
} else {
	$search_fields = array('d.download_name', 'd.download_url', 'd.download_description', 'd.download_author', 'd.download_author_website');
}

// basic
$return_fields = 'd.download_id, d.download_category, download_category_id, d.download_name, d.download_description, d.download_author, d.download_author_website, d.download_datestamp, d.download_class, c.download_category_name, c.download_category_class';
$weights = array('1.2', '0.9', '0.6', '0.6', '0.4');
$no_results = LAN_198;
$where = "download_active > '0' AND d.download_visible IN (".USERCLASS_LIST.") AND c.download_category_class IN (".USERCLASS_LIST.") AND".$advanced_where;
$order = array('download_datestamp' => DESC);
$table = "download AS d LEFT JOIN #download_category AS c ON d.download_category = c.download_category_id";

$ps = $sch -> parsesearch($table, $return_fields, $search_fields, $weights, 'search_downloads', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_downloads($row) {
	global $con;
	$datestamp = $con -> convert_date($row['download_datestamp'], "long");
	$res['link'] = "download.php?view.".$row['download_id'];
	$res['pre_title'] = $row['download_category_name']." | ";
	$res['title'] = $row['download_name'];
	$res['pre_summary'] = "<div class='smalltext'><a href='download.php'>".LAN_197."</a> -> <a href='download.php?list.".$row['download_category_id']."'>".$row['download_category_name']."</a></div>";
	$res['summary'] = $row['download_description'];
	$res['detail'] = LAN_SEARCH_15." ".$row['download_author']." | ".LAN_SEARCH_66.": ".$datestamp;
	return $res;
}
*/
?>