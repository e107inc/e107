<?php

$comments_title = 'News';
$comments_type_id = 0;
$comments_return['news'] = "n.news_title";
$comments_table['news'] = "LEFT JOIN #news AS n ON c.comment_type=0 AND n.news_id = c.comment_item_id";
function com_search_0($row) {
	global $con;
	$nick = eregi_replace("[0-9]+\.", "", $row['comment_author']);
	$datestamp = $con -> convert_date($row['comment_datestamp'], "long");
	$res['link'] = "comment.php?comment.news.".$row['comment_item_id'];
	$res['title'] = $row['news_title'] ? "Posted in reply to news item: ".$row['news_title'] : LAN_SEARCH_9;
	$res['summary'] = $row['comment_comment'];
	$res['detail'] = LAN_SEARCH_7.$nick.LAN_SEARCH_8.$datestamp;
	return $res;
}

?>