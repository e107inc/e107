<?php

$comments_title = 'Poll';
$comments_type_id = 4;
$comments_return['poll'] = "po.poll_id, po.poll_title";
$comments_table['poll'] = "LEFT JOIN #polls AS po ON c.comment_type=4 AND po.poll_id = c.comment_item_id";
function com_search_4($row) {
	global $con;
	$nick = eregi_replace("[0-9]+\.", "", $row['comment_author']);
	$datestamp = $con -> convert_date($row['comment_datestamp'], "long");
	$res['link'] = e_PLUGIN."poll/oldpolls.php?".$row['poll_id'];
	$res['title'] = 'Posted in reply to poll: '.$row['poll_title'];
	$res['summary'] = $row['comment_comment'];
	$res['detail'] = LAN_SEARCH_7.$nick.LAN_SEARCH_8.$datestamp;
	return $res;
}

?>