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
|     $Revision: 1.5 $
|     $Date: 2005-02-08 17:58:49 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$fields = array('news_title', 'news_body', 'news_extended');
$time = time();
$pre_sql = " (news_start < ".$time.") AND (news_end=0 OR news_end > ".$time.") AND ";
$post_sql = " AND news_class IN (".USERCLASS_LIST.")";
if ($results = $sch -> search_query($query, 'news', $fields, $pre_sql, $post_sql)) {
	$x = 0;
	while ($match = $sql->db_Fetch()) {
		$link = ($match['news_allow_comments'] ? "news.php?item.".$match['news_id'] : "comment.php?comment.news.".$match['news_id']);
		$datestamp = $con->convert_date($match['news_datestamp'], "long");
		$res_title = $sch -> parsesearch($match['news_title'], 2, TRUE, TRUE);
		$sch -> parsesearch($match['news_body'].' '.$match['news_extended'], 1);
		$detail = $sch -> search_detail(LAN_SEARCH_3.$datestamp);
		$output['text'][$x] = $sch -> search_link($res_title, $link).$detail['text'];
		$output['weight'][$x] = $detail['weight'];
		$output['date'][$x] = $match['news_datestamp'];
		$x++;
	}
	$text .= $sch -> search_sort($output['weight'], SORT_DESC, $output['date'], SORT_DESC, $output['text'], SORT_ASC);
} else {
	$text .= LAN_198;
}

?>