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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/content_search.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-03-21 22:11:47 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'content_id, content_heading, content_subheading, content_summary, content_text, content_datestamp, content_parent, content_author';
$search_fields = array('content_heading', 'content_subheading', 'content_summary', 'content_text');
$weights = array('1.2', '0.9', '0.6', '0.6');
$no_results = LAN_198;
$where = "";
$order = array('content_datestamp' => DESC);

$ps = $sch -> parsesearch('pcontent', $return_fields, $search_fields, $weights, 'search_content', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_content($row) {
	global $con;
	$type = explode('.', $row['content_parent']);
	$res['link'] = e_PLUGIN."content/content.php?type.".$type[0].".content.".$row['content_id'];
	$res['pre_title'] = "Item: ";
	$res['title'] = $row['content_heading'];
	$res['summary'] = $row['content_summary'].' '.$row['content_text'];
	$res['detail'] = LAN_SEARCH_3.$con -> convert_date($row['content_datestamp'], "long");
	return $res;
}

?>