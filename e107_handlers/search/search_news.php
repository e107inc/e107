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
|     $Revision: 1.3 $
|     $Date: 2005-02-07 03:47:38 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

e107_require(e_HANDLER."search_class.php");
$sch = new e_search;

$keywords = explode(' ',$query);
$fields = array('news_title', 'news_body', 'news_extended');
$time = time();
$pre_query = " (news_start < ".$time.") AND (news_end=0 OR news_end > ".$time.") AND ";
$post_query = " AND news_class IN (".USERCLASS_LIST.")";

$x=0;
if ($results = $sch -> search_query($keywords, 'news', $fields, $pre_query, $post_query)) {
	while ($match = $sql->db_Fetch()) {
			$link = ($match['news_allow_comments'] ? "news.php?item.".$match['news_id'] : "comment.php?comment.news.".$match['news_id']);
			$datestamp = $con->convert_date($match['news_datestamp'], "long");
			
			$output_text = '';
			$res_title = $sch -> parsesearch($match['news_title'], $keywords, 2, TRUE);
			$output_text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='".$link."'>".$res_title['text']."</a></b><br />";
			
			$res_body = $sch -> parsesearch($match['news_body'], $keywords, 1);
			$output_text .= $res_body['text']."<br />";
			
			$weight = $res_title['weight'] + $res_body['weight'];
			$output_text .= "<span class='smalltext'>".LAN_SEARCH_3.$datestamp." Relevance: ".$weight."</span><br /><br />";

			$output['text'][] = $output_text;
			$output['weight'][] = $weight;
			$output['date'][] = $match['news_datestamp'];
			$x++;
	}
}

array_multisort($output['weight'], SORT_DESC, $output['date'], SORT_DESC, $output['text'], SORT_ASC);
foreach ($output['weight'] as $output_id => $output_weight) {
	$text .= $output['text'][$output_id];
}

if (!$results) {
	$text .= LAN_198;
}
?>