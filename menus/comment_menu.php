<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/menus/comment_menu.php
|
|	©Edwin van der Wal 2003
|	http://e107.org
|	evdwal@xs4all.nl
|	Based on the comment_menu plugin by rufe, que
|
|	Released under the terms and conditions of the	
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
if(!$sql -> db_Select("comments", "*", "comment_id ORDER BY comment_datestamp DESC LIMIT 0, ".$menu_pref['comment_display'])){
	$text = "<span class=\"mediumtext\">No News comments yet</span>";
}else{
	$text = "<span class=\"smalltext\">";
	$sql2 = new db;
	while($row = $sql-> db_Fetch()){
		extract($row);
		$poster = eregi_replace("[0-9]+.", "", $comment_author);
		$gen = new convert;
		$datestamp = $gen->convert_date($comment_datestamp, "short");
		$message_array = explode(" ", $comment_comment);
		for($i=0; $i<=(count($message_array)-1); $i++){
			if(strlen($message_array[$i]) > 30){
				$message_array[$i] = preg_replace("/([^\s]{30})/", "$1<br />", $message_array[$i]);
			}
		}
		$comment_comment = implode(" ", $message_array);
		if(strlen($comment_comment) > $menu_pref['comment_characters']){
			$comment_comment = substr($comment_comment, 0, $menu_pref['comment_characters']).$menu_pref['comment_postfix'];
		}
		$aj = new textparse;
		$comment_comment = $aj -> tpa($comment_comment);
		if($comment_type == "0"){
			$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href=\"comment.php?$comment_item_id\"><b>".$poster."</b> on ".$datestamp."</a>";
			if($menu_pref['comment_title']) {
				$sql2 -> db_Select("news", "news_title", "news_id = $comment_item_id");
				list($news_title) = $sql2 -> db_Fetch();
				$text .= "<br /> [ Re: <i>$news_title</i> ]";
			} 
			$text .= "<br />$comment_comment<br /><br /><br />";
		}
		if($comment_type == "1"){
			$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href=\"article.php?$comment_item_id\"><b>".$poster."</b> on ".$datestamp."</a>";
			if($menu_pref['comment_title']) {
				$sql2 -> db_Select("content", "content_heading", "content_id=$comment_item_id");
				list($article_title) = $sql2->db_Fetch();
				$text .= "<br /> [ Re: <i>$article_title</i> ]";
			} 
			$text .= "<br />$comment_comment <br /><br /><br />";
		}
		if($comment_type_ == "2"){
				//This code is not tested... [edwin]
			$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href=\"download_comment.php?$comment_item_id_.\">download - <b>".$poster."</b> on ".$datestamp."</a><br />".$comment_comment."<br /><br /><br />";
		}
	}

	$text = "</span>".preg_replace("/\<br \/\>$/", "", $text);
}

$ns -> tablerender($menu_pref['comment_caption'], $text);
?>
