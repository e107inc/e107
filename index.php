<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/index.php
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
require_once("classes/news_class.php");
require_once(HEADERF);

if(file_exists("install.php")){ echo "<div class=\"installe\" style=\"text-align:center\"><b>*** Please delete install.php from your server ***</b><br />if you do not there is a potential security risk to your website</div><br /><br />"; }

$ix = new news;

if(eregi("cat", e_QUERY)){
	$qs = explode(".", e_QUERY);
	$category = $qs[1];
	if($category != 0){
		$gen = new convert;
		$sql2 = new db;
		$sql -> db_Select("news_category", "*", "category_id='$category'");
		list($category_id, $category_name, $category_icon) = $sql-> db_Fetch();
		if(eregi("images", $category_icon)){
			$category_icon = THEME.$category_icon;
		}else{
			$category_icon = e_HTTP.$category_icon;
		}
		$count = $sql -> db_SELECT("news", "*",  "news_category='$category' ORDER BY news_datestamp DESC");
		while(list($news_id, $news_title, $news_body, $news_extended, $news_datestamp, $news_author, $news_source, $news_url, $news_category, $news_allow_comments) = $sql-> db_Fetch()){
			if($news_title == ""){ $news_title = "Untitled"; }
			$datestamp = $gen->convert_date($news_datestamp, "short");
			$news_body = strip_tags(substr($news_body, 0, 100))." ...";
			$comment_total = $sql2 -> db_Count("comments", "(*)",  "WHERE comment_item_id='$news_id' AND comment_type='0' ");
			$text .= "<div class=\"mediumtext\">
			<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> ";

			if($news_allow_comments){
				$text .= $news_title;
			}else{
				$text .= "<a href=\"comment.php?".$news_id."\">".$news_title."</a>";
			}
			$text .= "<br />
			On ".$datestamp." (".LAN_99.": ";
			if($news_allow_comments){
				$text .= COMMENTOFFSTRING.")";
			}else{
				$text .= $comment_total.")";
			}
			$text .= "</div>
			".$news_body."
			<br /><br />\n";
		}
		$text = "<img src=\"$category_icon\" alt=\"\" /><br />".
		LAN_307.$count."
		<br /><br />".$text;
		$ns -> tablerender(LAN_82." '".$category_name."'", $text);
		require_once(FOOTERF);
		exit;
	}
}


if(eregi("extend", e_QUERY)){
	$qs = explode(".", e_QUERY);
	$extend_id = $qs[1];
	$sql -> db_Select("news", "*", "news_id='$extend_id' ");
	$row = $sql -> db_Fetch(); extract($row);
	$comment_total = $sql -> db_Count("comments", "(*)", " WHERE comment_item_id='$news_id' AND comment_type='0' ");

	$ix -> render_newsitem($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, $news_author, $comment_total, $news_category, $news_datestamp, $news_allow_comments, $news_start, $news_end, $news_active, $mode="extend");

	require_once(FOOTERF);
	exit;
}

if(!e_QUERY || eregi("cat", e_QUERY)){ $from = 0; }else{ $from = e_QUERY; }
if(Empty($order)){ $order = "news_datestamp"; }

// ---> wmessage
$sql -> db_Select("wmessage");
list($wm_guest, $guestmessage, $wm_active1) = $sql-> db_Fetch();
list($wm_member, $membermessage, $wm_active2) = $sql-> db_Fetch();
list($wm_admin, $adminmessage, $wm_active3) = $sql-> db_Fetch();
if(ADMIN == TRUE && $wm_active3){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>Administrators</b><br />".$adminmessage."</div>");
}else if(USER == TRUE && $wm_active2){
	$ns -> tablerender("", "<div style=\"text-align:center\">".$membermessage."</div>");
}else if(USER == FALSE && $wm_active1){
	$ns -> tablerender("", "<div style=\"text-align:center\">".$guestmessage."</div>");
}
// ---> wmessage end



$news_total = $sql -> db_Count("news");
if(!$sql -> db_Select("news", "*", "news_active=0 AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") ORDER BY ".$order." DESC LIMIT $from,".ITEMVIEW)){
	echo "<br /><br /><div style=\"text-align:center\"><b>".LAN_83."</b></div><br /><br />";
}else{
	$sql2 = new db;	
	while($row = $sql -> db_Fetch()){
		extract($row);
		if(!$news_active){
			$comment_total = $sql2 -> db_Count("comments", "(*)",  "WHERE comment_item_id='$news_id' AND comment_type='0' ");
			$ix -> render_newsitem($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, $news_author, $comment_total, $news_category, $news_datestamp, $news_allow_comments, $news_start, $news_end, $news_active);
		}
	}
}

$ix = new nextprev("index.php", $from, ITEMVIEW, $news_total, LAN_84);

require_once(FOOTERF);
?>