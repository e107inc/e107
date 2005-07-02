<?php

$comments_title = 'Links';
$comments_type_id = 'links_page';
$comments_return['links_page'] = "l.link_id, l.link_name";
$comments_table['links_page'] = "LEFT JOIN #links_page AS l ON c.comment_type='links_page' AND l.link_id = c.comment_item_id";
function com_search_links_page($row) {
	global $con;
	$datestamp = $con -> convert_date($row['comment_datestamp'], "long");
	$res['link'] = e_PLUGIN."links_page/links.php?comment.".$row['link_id'];
	$res['pre_title'] = 'Posted in reply to link: ';
	$res['title'] = $row['link_name'];
	$res['summary'] = $row['comment_comment'];
	preg_match("/([0-9]+)\.(.*)/", $row['comment_author'], $user);
	$res['detail'] = LAN_SEARCH_7."<a href='user.php?id.".$user[1]."'>".$user[2]."</a>".LAN_SEARCH_8.$datestamp;
	return $res;
}

?>