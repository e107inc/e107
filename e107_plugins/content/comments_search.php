<?php

$comments_title = 'Content';
$comments_type_id = 1;
$comments_return['content'] = "p.content_id, p.content_heading, p.content_parent";
$comments_table['content'] = "LEFT JOIN #pcontent AS p ON c.comment_type=1 AND p.content_id = c.comment_item_id";
function com_search_1($row) {
	global $con;
	$nick = eregi_replace("[0-9]+\.", "", $row['comment_author']);
	$datestamp = $con -> convert_date($row['comment_datestamp'], "long");
	$type = explode('.', $row['content_parent']);
	$res['link'] = e_PLUGIN."content/content.php?type.".$type[0].".content.".$row['content_id'];
	$res['title'] = 'Posted in reply to item: '.$row['content_heading'];
	$res['summary'] = $row['comment_comment'];
	$res['detail'] = LAN_SEARCH_7.$nick.LAN_SEARCH_8.$datestamp;
	return $res;
}

?>