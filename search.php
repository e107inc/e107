<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/search.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
if($_POST['searchquery'] == ""){ header("location:".e_HTTP."index.php"); }
require_once(HEADERF);

$con = new convert;
$refpage = substr($_SERVER['HTTP_REFERER'], (strrpos($_SERVER['HTTP_REFERER'], "/")+1));
if(!$refpage){
	$refpage = "index.php";
}

if(IsSet($_POST['searchquery'])){
	$query = $_POST['searchquery'];
	if($_POST['searchtype'] == 1 || eregi("index.php", $refpage)){
		$searchtype = 1;
	}else if($_POST['searchtype'] == 2 || eregi("comment.php", $refpage)){
		$searchtype = 2;
	}else  if($_POST['searchtype'] == 3 || eregi("article.php", $refpage)){
		$searchtype = 3;
	}else  if($_POST['searchtype'] == 4 || eregi("article.php", $refpage)){
		$searchtype = 4;
	}else  if($_POST['searchtype'] == 5 || eregi("chat.php", $refpage)){
		$searchtype = 5;
	}else  if($_POST['searchtype'] == 6 || eregi("links.php", $refpage)){
		$searchtype = 6;
	}else  if($_POST['searchtype'] == 7 || eregi("forum.php", $refpage)){
		$searchtype = 7;
	}
}

$text = "<div style=\"text-align:center\"><form method=\"post\" action=\"".e_SELF."\">
<p>
Search for <input class=\"tbox\" type=\"text\" name=\"searchquery\" size=\"20\" value=\"$query\" maxlength=\"50\" />
&nbsp;in <select name=\"searchtype\" class=\"tbox\">";
if($searchtype == 1){
	$text .= "<option value=\"1\" selected>News</option>";
}else{
	$text .= "<option value=\"1\">News</option>";
}

if($searchtype == 2){
	$text .= "<option value=\"2\" selected>Comments</option>";
}else{
	$text .= "<option value=\"2\">Comments</option>";
}

if($searchtype == 3){
	$text .= "<option value=\"3\" selected>Articles</option>";
}else{
	$text .= "<option value=\"3\">Articles</option>";
}

if($searchtype == 4){
	$text .= "<option value=\"4\" selected>Reviews</option>";
}else{
	$text .= "<option value=\"4\">Reviews</option>";
}

if($searchtype == 5){
	$text .= "<option value=\"5\" selected>Chatbox</option>";
}else{
	$text .= "<option value=\"5\">Chatbox</option>";
}

if($searchtype == 6){
	$text .= "<option value=\"6\" selected>Links</option>";
}else{
	$text .= "<option value=\"6\">Links</option>";
}

if($searchtype == 7){
	$text .= "<option value=\"7\" selected>Forum</option>";
}else{
	$text .= "<option value=\"7\">Forum</option>";
}

$text .= "
</select>
<input class=\"button\" type=\"submit\" name=\"searchsubmit\" value=\"".LAN_180."\" />
</p>
</form></div>";
$ns -> tablerender("Search ".SITENAME, $text);



// news item search -------------------------------

if($searchtype == 1){
	unset($text);

	$results = $sql -> db_Select("news", "*", "news_title REGEXP('".$query."') OR news_body REGEXP('".$query."') OR news_extended REGEXP('".$query."') ORDER BY news_id DESC ");
	while(list($news_id, $news_title, $news_body, $news_extended, $news_datestamp) = $sql -> db_Fetch()){

		$datestamp = $con -> convert_date($news_datestamp, "long");

		if(eregi($query, $news_title)){
			$resmain = parsesearch($news_title, $query);
			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"comment.php?".$news_id."\">".$resmain."</a></b><br />
		<span class=\"smalltext\">item posted on ".$datestamp." - 
		Match found in news title</span>
		<br /><br />";
		}else if(eregi($query, $news_body)){
			$resmain = parsesearch($news_body, $query);
			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"comment.php?".$news_id."\">".$news_title."</a></b><br />
		<span class=\"smalltext\">item posted on ".$datestamp." - 
		Match found in news text</span><br />".$resmain."
		<br /><br />";
		}else if(eregi($query, $news_body)){
			$resmain = parsesearch($news_extended, $query);
			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"comment.php?".$news_id."\">".$news_title."</a></b><br />
		<span class=\"smalltext\">item posted on ".$datestamp." - 
		Match found in extended news text</span><br />".$resmain."
		<br /><br />";
		}

	}
	
	if($text == ""){ $text = LAN_97; }
	$text .= "<br /><div style=\"text-align:center\">Search for '$query' on Google? <a href=\"http://www.google.com/search?q=$query\">Click here</a></div>";
	$ns -> tablerender("Search Results (Matches: $results): ".LAN_98, $text);
}

// comments item search -------------------------------

if($searchtype == 2){
	unset($text);
	$sql2 = new db;
	$results = $sql -> db_Select("comments", "*", "comment_comment REGEXP('".$query."') ");
	while(list($comment_id, $comment_item_id, $comment_author, $comment_author_email, $comment_datestamp, $comment_comment, $comment_blocked, $comment_ip, $comment_type) = $sql -> db_Fetch()){
		$nick = eregi_replace("[0-9]+\.", "", $comment_author);
		$datestamp = $con -> convert_date($comment_datestamp, "long");
		$sql2 -> db_Select("news", "*", "news_id='$comment_item_id'");
		list($news_id, $news_title, $news_body, $news_datestamp, $news_author, $news_source, $news_url, $news_catagory) = $sql2 -> db_Fetch();
		$comment_comment = parsesearch($comment_comment, $query);
		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"comment.php?".$news_id."\">".$news_title."</a></b><br />
		<span class=\"smalltext\">posted by ".$nick." on ".$datestamp."</span><br />".$comment_comment."<br /><br />";
	}
	if($text == ""){ $text = LAN_97; }
	$text .= "<br /><div style=\"text-align:center\">Search for '$query' on Google? <a href=\"http://www.google.com/search?q=$query\">Click here</a></div>";
	$ns -> tablerender("Search Results (Matches: $results):".LAN_99, $text);
}

// articles item search -------------------------------

if($searchtype == 3){
	unset($text);
	$results = $sql -> db_Select("content", "*", "content_heading REGEXP('".$query."') OR content_subheading REGEXP('".$query."') OR content_content REGEXP('".$query."') ");
	while(list($content_id, $content_heading, $content_subheading, $content_content, $content_datestamp, $content_author, $content_comment) = $sql -> db_Fetch()){
		$content_heading_ = parsesearch($content_heading, $query);
		if(!$content_heading_){
			$content_heading_ = $content_heading;
		}
		$content_subheading_ = parsesearch($content_subheading, $query);
		if(!$content_subheading_){
			$content_subheading_ = $content_subheading_;
		}
		$content_content_ = parsesearch($content_content, $query);
		$text .= "<br /><img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"article.php?".$content_id.".0\">".$content_heading_."</a></b> <br />".$content_subheading_.$content_content_;
	}
	if($text == ""){ $text = LAN_97; }
	$ns -> tablerender("Search Results (Matches: $results):".LAN_100, $text);
}


// reviews item search -------------------------------

if($searchtype == 4){
	unset($text);
	$results = $sql -> db_Select("content", "*", "content_heading REGEXP('".$query."') OR content_subheading REGEXP('".$query."') OR content_content REGEXP('".$query."') ");
	while(list($content_id, $content_heading, $content_subheading, $content_content, $content_datestamp, $content_author, $content_comment) = $sql -> db_Fetch()){
		$content_heading_ = parsesearch($content_heading, $query);
		if(!$content_heading_){
			$content_heading_ = $content_heading;
		}
		$content_subheading_ = parsesearch($content_subheading, $query);
		if(!$content_subheading_){
			$content_subheading_ = $content_subheading_;
		}
		$content_content_ = parsesearch($content_content, $query);
		$text .= "<br /><img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"article.php?".$content_id.".0\">".$content_heading_."</a></b> <br />".$content_subheading_.$content_content_;
	}
	if($text == ""){ $text = LAN_97; }
	$ns -> tablerender("Search Results (Matches: $results):".LAN_101, $text);
}

// chatbox item search -------------------------------

if($searchtype == 5){
	unset($text);
	$results = $sql -> db_Select("chatbox", "*", "(cb_nick REGEXP('".$query."') OR cb_message REGEXP('".$query."')) AND cb_blocked='0' ");
	while(list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip) = $sql -> db_Fetch()){
		$cb_nick = eregi_replace("[0-9]+\.", "", $cb_nick);

		$cb_nick_ = parsesearch($cb_nick, $query);
		$cb_message_ = parsesearch($cb_message, $query);
		if(!$cb_nick_){
			$cb_nick_ = $cb_nick;
		}
		if(!$cb_message_){
			$cb_message_ = $cb_message;
		}
		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"chat.php?".$cb_id."\">$cb_nick_</a><br />$cb_message_<br /><br />";
	}
	if($text == ""){ $text = LAN_97; }
	$ns -> tablerender("Search Results (Matches: $results):".LAN_101, $text);
}

// links item search -------------------------------

if($searchtype == 6){
	unset($text);
	$results = $sql -> db_Select("links", "*", "link_name REGEXP('".$query."') OR link_description REGEXP('".$query."') ");
	while(list($link_id, $link_name, $link_url, $link_desciption, $link_button, $link_category, $link_refer) = $sql -> db_Fetch()){

		$link_name_ = parsesearch($link_name, $query);
		if(!$link_name_){
			$link_name_ = $link_name;
		}

		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"".$link_url."\">".$link_name."</a><br />";
	}
	if($text == ""){ $text = LAN_97; }
	$ns -> tablerender("Search Results (Matches: $results):".LAN_102, $text);
}

// forum item search -------------------------------

if($searchtype == 7){
	$sql2 = new db;
	unset($text);
	
	$results = $sql -> db_Select("forum_t", "*", "thread_name REGEXP('".$query."') OR thread_thread REGEXP('".$query."')");

	while(list($thread_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active) = $sql -> db_Fetch()){

		$sql2 -> db_Select("forum", "*", "forum_id='$thread_forum_id' ");
		$row = $sql2 -> db_Fetch();
		@extract($row);
		if($forum_active && (!$forum_class || check_class($forum_class))){
			$thread_name = parsesearch($thread_name, $query);
	
			if($thread_name == "......"){
				$thread_name = "No title";
			}

			$thread_thread = parsesearch($thread_thread, $query);
		
			if($thread_parent != 0){
				$tmp = $thread_parent;
			}else{
				$tmp = $thread_id;
			}

			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"forum.php?view.$thread_forum_id.$tmp\">$thread_name</a></b><br />$thread_thread<br /><br />";
		}
	}
	if($text == ""){ $text = LAN_97; }
	$ns -> tablerender("Search Results (Matches: $results):".LAN_103, $text);
}

function parsesearch($text, $match){
	$text = strip_tags($text);
	$temp = stristr($text,$match);
	$pos = strlen($text)-strlen($temp);


	if($pos < 70){
		$text = "...".substr($text, 0, 100)."...";
	}else{
		$text = "...".substr($text, ($pos-70), 140)."...";
	}
	$text = eregi_replace($match, "<u><b>$match</b></u>", $text);	
	
	return($text);
}

require_once(FOOTERF);
?>