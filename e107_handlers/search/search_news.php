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
|     $Revision: 1.7 $
|     $Date: 2005-02-10 00:51:07 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'news_id, news_title, news_body, news_extended, news_allow_comments, news_datestamp';
$search_fields = array('news_title', 'news_body', 'news_extended');
$weights = array('1.2', '0.6', '0.6');
$time = time();
$where = "(news_start < ".$time.") AND (news_end=0 OR news_end > ".$time.") AND news_class IN (".USERCLASS_LIST.") AND";
$order = ", news_datestamp DESC";
if ($results = $sch -> search_query('news', $return_fields, $search_fields, $weights, $where, $order)) {
	while ($match = $sql->db_Fetch()) {
		$link = ($match['news_allow_comments'] ? "news.php?item.".$match['news_id'] : "comment.php?comment.news.".$match['news_id']);
		$datestamp = $con->convert_date($match['news_datestamp'], "long");
		$parse = array($match['news_title'], $match['news_body'].$match['news_extended']);
		$text .= $sch -> parsesearch($parse, $link, LAN_SEARCH_3.$datestamp, $match['relevance']);
	}
} else {
	$text .= LAN_198;
}

?>