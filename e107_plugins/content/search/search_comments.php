<?php

$comments_title = 'Content';
$comments_type_id = 'pcontent';
$comments_return['content'] = "p.content_id, p.content_heading, p.content_parent";
$comments_table['content'] = "LEFT JOIN #pcontent AS p ON c.comment_type='pcontent' AND p.content_id = c.comment_item_id";
function com_search_pcontent($row) {
	global $con;
	$datestamp = $con -> convert_date($row['comment_datestamp'], "long");
	$res['link'] = e_PLUGIN."content/content.php?content.".$row['content_id'];
	$res['pre_title'] = CONT_SCH_LAN_3.': ';
	$res['title'] = $row['content_heading'];
	$res['summary'] = $row['comment_comment'];
	preg_match("/([0-9]+)\.(.*)/", $row['comment_author'], $user);
	$res['detail'] = LAN_SEARCH_7."<a href='user.php?id.".$user[1]."'>".$user[2]."</a>".LAN_SEARCH_8.$datestamp;
	return $res;
}

?>