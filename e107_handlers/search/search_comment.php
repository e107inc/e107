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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/search_comment.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-02-15 11:25:49 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'comment_item_id, comment_author, comment_datestamp, comment_comment, comment_type';
$search_fields = array('comment_comment', 'comment_author');
$weights = array('1.2', '0.6');
$no_results = LAN_198;
$where = "";
$order = array('comment_datestamp' => DESC);
$sql2 = new db;

$ps = $sch -> parsesearch('comments', $return_fields, $search_fields, $weights, 'search_comment', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_comment($row) {
	global $con, $sql2;
	$nick = eregi_replace("[0-9]+\.", "", $row['comment_author']);
	$datestamp = $con -> convert_date($row['comment_datestamp'], "long");
	
	if($row['comment_type'] == 6){
		$res['link'] = e_PLUGIN."bugtracker/bugtracker.php?show.".$bugtrack_id;
		$res['title'] = $bugtrack_summary;
	} else {
		if($sql2 -> db_Select("news", "news_title, news_allow_comments", "news_id='".$row['comment_item_id']."'")){
			$news_row = $sql2 -> db_Fetch();
			$res['link'] = "comment.php?comment.news.".$row['comment_item_id'];
			$res['title'] = $news_row['news_title'] ? "Posted in reply to news item: ".$news_row['news_title'] : LAN_SEARCH_9;
		}
	}
	
	$res['summary'] = $row['comment_comment'];
	$res['detail'] = LAN_SEARCH_7.$nick.LAN_SEARCH_8.$datestamp;
	return $res;
}





/*
if($row['comment_type'] == 6){        // bugtracker comment
	$row['comment_comment'] = parsesearch($row['comment_comment'], $query);
	$sql2 -> db_Select("bugtrack", "*", "bugtrack_id='".$row['comment_item_id']."'");
	$row = $sql2 -> db_Fetch(); extract($row);
	$text .= "<a href='".e_PLUGIN."bugtracker/bugtracker.php?show.".$bugtrack_id."'>".$bugtrack_summary."</a>";
}else{
	if($sql2 -> db_Select("news", "*", "news_id='".$row['comment_item_id']."'")){
		list($news_id, $news_title, $news_body, $news_datestamp, $news_author, $news_source, $news_url, $news_category, $news_allow_comments) = $sql2 -> db_Fetch();
		if($news_allow_comments == 0){
			$row['comment_comment'] = parsesearch($row['comment_comment'], $query);
			$text .= "<a href='javascript:document.getElementById(\"news_comment".$c."\").submit()'>".($news_title ? $news_title : LAN_SEARCH_9)."</a>";
		}else{
			$results = $results -1;
		}
	}
}
*/

?>