<?php
// search module for comments.

	$sql2 = new db;
	$results = $sql -> db_Select("comments", "*", "comment_comment REGEXP('".$query."') OR comment_author REGEXP('".$query."') ");
		while(list($comment_id, $comment_pid, $comment_item_id, $comment_subject, $comment_author, $comment_author_email, $comment_datestamp, $comment_comment, $comment_blocked, $comment_ip, $comment_type) = $sql -> db_Fetch()){
			$c = 0;
			$nick = eregi_replace("[0-9]+\.", "", $comment_author);
			$datestamp = $con -> convert_date($comment_datestamp, "long");
			if($comment_type == 6){	// bugtracker comment
				$comment_comment = parsesearch($comment_comment, $query);
				$sql2 -> db_Select("bugtrack", "*", "bugtrack_id='$comment_item_id'");
				$row = $sql2 -> db_Fetch(); extract($row);
				$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='".e_PLUGIN."bugtracker/bugtracker.php?show.".$bugtrack_id."'>".$bugtrack_summary."</a></b><br />\n<span class='smalltext'>posted by ".$nick." on ".$datestamp."</span><br />".$comment_comment."<br /><br />";
			}else{
				if($sql2 -> db_Select("news", "*", "news_id='$comment_item_id'")){
					list($news_id, $news_title, $news_body, $news_datestamp, $news_author, $news_source, $news_url, $news_category, $news_allow_comments) = $sql2 -> db_Fetch();
					if($news_allow_comments == 0){
						$comment_comment = parsesearch($comment_comment, $query);
						$text .= "<form method='post' action='comment.php?comment.news.".$news_id."' id='news_comment".$c."'>
				\n<input type='hidden' name='highlight_search' value='1' /><input type='hidden' name='search_query' value='$query' /><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='javascript:document.getElementById(\"news_comment".$c."\").submit()'>".($news_title ? $news_title : "Untitled")."</a></form></b><br />\n<span class='smalltext'>posted by ".$nick." on ".$datestamp."</span><br />".$comment_comment."<br /><br />";
					}else{
						$results = $results -1;
					}
				}
			}
		}
	if(!$results){
		$text .= LAN_198;
	}
?>