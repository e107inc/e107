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
require_once("classes/news_class.php");
require_once("classes/comment_class.php");
if(!e_QUERY){
	header("location:".e_HTTP);
}
$qs = explode(".", e_QUERY);
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
	if(!$sql -> db_Select("news", "*", "news_id='$id' ")){
		header("location:".e_HTTP."index.php");
	}else{
		$row = $sql -> db_Fetch();
		extract($row);
		if($news_allow_comments == 1){
			header("location:".e_HTTP."index.php");
		}else{
			require_once(HEADERF);
			$comment_total = $sql -> db_Count("comments", "(*)", " WHERE comment_item_id='$news_id' AND comment_type='0' ");
			$ix = new news;
			$ix -> render_newsitem($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, $news_author, $comment_total, $news_category, $news_datestamp, $news_allow_comments, $news_start, $news_end, $news_active);
			$field = $news_id;
			$comtype = 0;
		}
	}
}else if($table == "poll"){
	if(!$sql -> db_Select("poll", "*", "poll_id='$id' ")){
		header("location:".e_HTTP."index.php");
	}else{
		$row = $sql -> db_Fetch();
		extract($row);
		require_once(HEADERF);
		require_once(e_BASE."plugins/poll.php");
		$field = $poll_id;
		$comtype = 4;
	}
}

$comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$field' AND comment_type='$comtype' ORDER BY comment_datestamp");
if($comment_total){
	while($row = $sql -> db_Fetch()){
		$text .= $cobj -> render_comment($row);
	}
	if(!defined("emessage")){
		$ns -> tablerender(LAN_5, $text);
	}else{
		$ns -> tablerender(LAN_5, "<div style='text-align:center'><b>".emessage."</b></div><br /><br />".$text);
	}
}

$cobj -> form_comment();



require_once(FOOTERF);
?>