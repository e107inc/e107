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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/search_news.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-02-12 11:42:23 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'news_id, news_title, news_body, news_extended, news_allow_comments, news_datestamp';
$search_fields = array('news_title', 'news_body', 'news_extended');
$weights = array('1.2', '0.6', '0.6');
$time = time();
$where = "(news_start < ".$time.") AND (news_end=0 OR news_end > ".$time.") AND news_class IN (".USERCLASS_LIST.") AND";
$order = ", news_datestamp DESC";
$no_results = LAN_198;
$ps = $sch -> parsesearch('news', $return_fields, $search_fields, $weights, 'search_news', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_news($row) {
	global $con;
	$res['link'] = ($row['news_allow_comments'] ? "news.php?item.".$row['news_id'] : "comment.php?comment.news.".$row['news_id']);
	$res['title'] = $row['news_title'];
	$res['summary'] = $row['news_body'].$row['news_extended'];
	$res['detail'] = LAN_SEARCH_3.$con -> convert_date($row['news_datestamp'], "long");
	return $res;
}

?>