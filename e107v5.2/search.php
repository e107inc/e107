<?php
if($_POST['searchquery'] == ""){ header("location:index.php"); }
/*
+---------------------------------------------------------------+
|	e107 website system
|	/template.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com	
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
+---------------------------------------------------------------+
| 03/12/02
| + search no longer case sensitive (added by McFly)
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

$ns -> tablerender("Search ".SITENAME, "");

if(IsSet($_POST['searchquery'])){
	$query = $_POST['searchquery'];

	$sql ->db_Select("news", "*", "news_title LIKE '%$query%' OR news_body LIKE '%$query%' OR news_extended LIKE '%$query%' ");
	$text = "";
	while(list($news_id, $news_title, $news_body, $news_datestamp, $news_author, $news_source, $news_url, $news_catagory) = $sql -> db_Fetch()){
		if($sr = parsesearch($news_title." ".$news_body." ".$news_exended, $query)){
			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"comment.php?".$news_id."\">".$sr."</a><br />";
		}
	}
	if($text == ""){ $text = LAN_97; }
	$ns -> tablerender(LAN_98, $text);

	$sql ->db_Select("comments", "*", "comment_comment LIKE '%$query%' ");
	$search_total = $sql -> db_Rows();
	$text = "";
	while(list($comment_id, $comment_item_id, $comment_author, $comment_author_email, $comment_datestamp, $comment_comment, $comment_blocked, $comment_ip, $comment_type) = $sql -> db_Fetch()){
		if($sr = parsesearch($comment_comment, $query)){
			if ($comment_type == 0) {
				$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"comment.php?".$comment_item_id."\">".$sr."</a><br />";
			}
			if ($comment_type == 1) {
				$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"article.php?".$comment_item_id."\">".$sr."</a><br />";
			}
		}
	}
	if($text == ""){ $text = LAN_97; }
	$ns -> tablerender(LAN_99, $text);

	$sql ->db_Select("content", "*", "content_heading LIKE '%$query%' OR content_subheading LIKE '%$query%' OR content_content LIKE '%$query%' ");
	$text = "";
	while(list($content_id, $content_heading, $content_subheading, $content_content, $content_datestamp, $content_author, $content_comment) = $sql -> db_Fetch()){
		$sr = parsesearch($content_heading." ".$content_subheading." ".$content_content, $query);
		$content_author = eregi_replace("[0-9]+\.", "", $content_author);
		$text .= "<a href=\"article.php?".$content_id."\">".$content_heading.": ".$sr."</a><br />";
	}

	if($text == ""){ $text = LAN_97; }
	$ns -> tablerender(LAN_100, $text);

// #############################

	$sql ->db_Select("chatbox", "*", "cb_nick LIKE '%$query%' OR cb_message LIKE '%$query%' ");
	$text = "";
	while(list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip) = $sql -> db_Fetch()){
		$cb_nick = eregi_replace("[0-9]+\.", "", $cb_nick);
		if($sr = parsesearch($cb_nick." ".$cb_message, $query)){
			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"chat.php?".$cb_id."\">$sr</a><br />";
		}
	}
	if($text == ""){ $text = LAN_97; }
	$ns -> tablerender(LAN_101, $text);

// #############################

	$sql ->db_Select("links", "*", "link_name LIKE '%$query%' OR link_description LIKE '%$query%' ");
	$text = "";
	while(list($link_id, $link_name, $link_url, $link_desciption, $link_button, $link_category, $link_refer) = $sql -> db_Fetch()){
		$sr = parsesearch($link_name." ".$link_description, $query);
		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"".$link_url."\">".$link_name."</a><br />";
	}
	if($text == ""){ $text = LAN_97; }
	$ns -> tablerender(LAN_102, $text);


	$sql ->db_Select("forum_t", "*", "thread_name LIKE '%$query%' OR thread_thread LIKE '%$query%' ");
	$text = "";
	while(list($thread_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active) = $sql -> db_Fetch()){
		if($sr = parsesearch($thread_name." ".$thread_thread, $query)){
			if($thread_parent_ != 0){
				$tmp = $thread_parent;
			}else{
				$tmp = $thread_id;
			}
			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"forum.php?view.$thread_forum_id.$tmp\">$sr</a>";
		}
	}
	if($text == ""){ $text = LAN_97; }
	$ns -> tablerender(LAN_103, $text);


}

function parsesearch($text, $match){
	$text = strip_tags($text);
	$rts = eregi_replace("\,|\.|\;|\n", " ",$text);
	$words = explode(" ", $text);
	for($a=0; $a<= count($words); $a++){
		if(eregi($match,$words[$a])){
			$c=$a;
		}
	}
	if(!$c){
		return FALSE;
	}
	$start = $c-10;
	if($start <= 0){
		$start = 0;
	}
	$finish = $c+10;
	$text = eregi_replace($match, "<u><b>$match</b></u>", $text);
	$words = explode(" ", $text);
	$text =  "... ";
	for($a=$start; $a<= $finish; $a++){
		$text .= $words[$a]." ";
	}
	$text .= "... ";
	return $text."<br />";
}


require_once(FOOTERF);
?>