<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/index.php																	|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);
$ix = new news;
if(eregi("cat", $_SERVER['QUERY_STRING'])){
	$qs = explode(".", $_SERVER['QUERY_STRING']);
	$category = $qs[1];
	if($category != 0){
		unset($text);
		$sql -> db_Select("news_category", "*", "category_id='$category'");
		list($category_id, $category_name, $category_icon) = $sql-> db_Fetch();
		$sql -> db_SELECT("news", "*",  "news_category='$category' ORDER BY news_datestamp DESC");
		while(list($news_id, $news_title, $news_body, $news_extended, $news_datestamp, $news_author, $news_source, $news_url, $news_category, $news_allow_comments) = $sql-> db_Fetch()){
			if($news_title == ""){ $news_title = "Untitled"; }
			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"comment.php?".$news_id."\">".$news_title."</a><br />\n";
		}
		$ns -> tablerender("<div style=\"text-align:center\">".LAN_82." '".$category_name."'</div>", $text);
		require_once(FOOTERF);
		exit;
	}
}
if(eregi("extend", $_SERVER['QUERY_STRING'])){
	$qs = explode(".", $_SERVER['QUERY_STRING']);
	$extend_id = $qs[1];
	$sql -> db_Select("news", "*", "news_id='$extend_id' ");
	list($news_id, $news_title, $news_body, $news_extended, $news_datestamp, $news_author, $news_source, $news_url, $news_category) = $sql-> db_Fetch();
	$sql2 = new db;
	$sql2 -> db_Select("news_category", "*", "category_id='$news_category' ");
	list($category_id, $category_name, $category_icon) = $sql2-> db_Fetch();
	$comment_total = $sql -> db_Count("comments", "(*)", " WHERE comment_item_id='$news_id' AND comment_type='0' ");

	$ix -> render_newsitem($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, $news_author, $comment_total, $category_id, $news_datestamp, $news_allow_comments, $mode="extend");

	require_once(FOOTERF);
	exit;
}

if(!$_SERVER['QUERY_STRING'] || eregi("cat", $_SERVER['QUERY_STRING'])){ $from = 0; }else{ $from = $_SERVER['QUERY_STRING']; }
if(Empty($order)){ $order = "news_datestamp"; }

$sql -> db_Select("wmessage");
list($wm_text, $wm_active) = $sql-> db_Fetch();
if($wm_active == 1){
	$ns -> tablerender("", "<div class=\"border\" style=\"text-align:center\">".$wm_text."</div>");
}

$news_total = $sql -> db_Count("news");

if(!$sql -> db_Select("news", "*", "ORDER BY ".$order." DESC LIMIT $from,".ITEMVIEW, $mode="no_where")){
	echo "<div style=\"text-align:center\"><b>".LAN_83."</b></div>";
}else{
	$sql2 = new db;
	while(list($news_id, $news_title, $news_body, $news_extended, $news_datestamp, $news_author, $news_source, $news_url, $news_category, $news_allow_comments) = $sql-> db_Fetch()){

		if(eregi("http://", $news_url)){
			$news_url = "<a href=\"$news_url\">$news_url</a>";
		}else if(eregi("www", $news_url)){
			$news_url = "<a href=\"http://$news_url\">$news_url</a>";
		}

		$sql2 -> db_Select("news_category", "*", "category_id='$news_category' ");
		list($category_id, $category_name, $category_icon) = $sql2-> db_Fetch();
		$comment_total = $sql2 -> db_Select("comments", "*",  "comment_item_id='$news_id' AND comment_type='0' ");
		$news_title=stripslashes($news_title);
		$news_body=stripslashes($news_body);
		$ix -> render_newsitem($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, $news_author, $comment_total, $category_id, $news_datestamp, $news_allow_comments);
	}
}

$ix = new nextprev("index.php", $from, ITEMVIEW, $news_total, LAN_84);

require_once(FOOTERF);
?>