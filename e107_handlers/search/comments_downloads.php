<?php

$comments_title = 'Downloads';
$comments_type_id = '2';
$comments_return['download'] = "d.download_id, d.download_name";
$comments_table['download'] = "LEFT JOIN #download AS d ON c.comment_type=2 AND d.download_id = c.comment_item_id";
function com_search_2($row) {
	global $con;
	$nick = eregi_replace("[0-9]+\.", "", $row['comment_author']);
	$datestamp = $con -> convert_date($row['comment_datestamp'], "long");
	$res['link'] = "download.php?view.".$row['download_id'];
	$res['title'] = $row['download_name'] ? "Posted to download item: ".$row['download_name'] : LAN_SEARCH_9;
	$res['summary'] = $row['comment_comment'];
	$res['detail'] = LAN_SEARCH_7.$nick.LAN_SEARCH_8.$datestamp;
	return $res;
}

?>