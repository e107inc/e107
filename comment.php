<?php
/*
+---------------------------------------------------------------+
|	e107 website system|
|	/class.php|
||
|	©Steve Dunstan 2001-2002|
|	http://jalist.com|
|	stevedunstan@jalist.com|
||
|	Released under the terms and conditions of the|
|	GNU General Public License (http://gnu.org).|
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);
require_once("classes/comment_class.php");
if(!$_SERVER['QUERY_STRING']){
	header("location:".$_SERVER['HTTP_REFERER']);
}
$qs = explode(".", $_SERVER['QUERY_STRING']);
$table = $qs[0];
$id = $qs[1];
if(!$id){
	$id = $table;
	$table = "news";
}
$cobj = new comment;
if(IsSet($_POST['commentsubmit'])){
	$cobj -> enter_comment($_POST['author_name'], $_POST['comment'], $table, $id);
}

if($table == "news"){
	if(!$sql -> db_Select($table, "*", $table."_id='$id' ")){
		header("location:index.php");
	}else{
		$row = $sql -> db_Fetch();
		extract($row);
		if($news_allow_comments == 1){
			die();
		}else{
			$comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$news_id' AND comment_type='0'");
			$ix = new news;
			$ix -> render_newsitem($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, $news_author, $comment_total, $category_id, $news_datestamp, $news_allow_comments);
			$field = $news_id;
			$comtype = 0;
		}
	}
}else if($table == "poll"){
	if(!$sql -> db_Select($table, "*", $table."_id='$id' ")){
		die();
	}else{
		$row = $sql -> db_Fetch();
		extract($row);
		require_once("plugins/poll.php");
		$field = $poll_id;
		$comtype = 4;
	}
}


$comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$field' AND comment_type='$comtype' ORDER BY comment_datestamp");
if($comment_total){
	while($row = $sql -> db_Fetch()){
		$text .= $cobj -> render_comment($row);
	}
	$ns -> tablerender(LAN_5, $text);
}

$cobj -> form_comment();



require_once(FOOTERF);
?>