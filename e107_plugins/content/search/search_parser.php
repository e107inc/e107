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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/search/search_parser.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-15 15:18:41 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

// advanced 
$advanced_where = "";
if (isset($_GET['cat']) && is_numeric($_GET['cat'])) {
	$advanced_where .= " content_parent='".$_GET['cat']."' AND";
}

if (isset($_GET['time']) && is_numeric($_GET['time'])) {
	$advanced_where .= " content_datestamp ".($_GET['on'] == 'new' ? '>=' : '<=')." '".(time() - $_GET['time'])."' AND";
}

if (isset($_GET['match']) && $_GET['match']) {
	$search_fields = array('content_heading');
} else {
	$search_fields = array('content_heading', 'content_subheading', 'content_summary', 'content_text');
}

// basic
$return_fields = 'content_id, content_heading, content_subheading, content_summary, content_text, content_datestamp, content_parent, content_author';
$weights = array('1.2', '0.9', '0.6', '0.6');
$no_results = LAN_198;
$where = "content_class IN (".USERCLASS_LIST.") AND".$advanced_where;
$order = array('content_datestamp' => DESC);

$ps = $sch -> parsesearch('pcontent', $return_fields, $search_fields, $weights, 'search_content', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_content($row) {
	global $con;
	$res['link'] = e_PLUGIN."content/content.php?content.".$row['content_id'];
	$res['pre_title'] = "";
	$res['title'] = $row['content_heading'];
	$res['summary'] = $row['content_summary'].' '.$row['content_text'];
	$res['detail'] = LAN_SEARCH_3.$con -> convert_date($row['content_datestamp'], "long");
	return $res;
}

?>