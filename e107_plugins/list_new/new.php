<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	©Steve Dunstan 2001-2003
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../../class2.php");

if(!USER){
	header("location:".e_BASE."index.php");
	exit;
}

require_once(HEADERF);
@include(e_PLUGIN."list_new/languages/".e_LANGUAGE.".php");
@include(e_PLUGIN."list_new/languages/English.php");

$bullet = "<img src='".THEME."images/bullet2.gif' alt='bullet' /> ";

$sql2 = new db;
$lvisit = USERLV;
if($news_items = $sql -> db_Select("news", "*", "news_datestamp>$lvisit  ORDER BY news_datestamp DESC LIMIT 0,10")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		if(check_class($news_class)){
			$str .= "$bullet<a href='".e_BASE."comment.php?$news_id'>$news_title<br />";
		}
	}
}else{
	$str = LIST_4;
}

$text = "<div style='text-align:center'>
<table style='width:95%' class='fborder'>
<tr>
<td class='fcaption'>".LIST_1." ($news_items)</td>
</tr>
<td class='forumheader3'>".$str."</td>
</tr>\n";

unset($str);
if($comments = $sql -> db_Select("comments", "*", "comment_datestamp>$lvisit ORDER BY comment_datestamp DESC LIMIT 0,10")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		switch($comment_type){
			case 0:	// news
				$sql2 -> db_Select("news", "*", "news_id=$comment_item_id ");
				$row = $sql2 -> db_Fetch(); extract($row);
				if(check_class($news_class)){
					$str .= $bullet."[ News item ] Re: <a href='".e_BASE."comment.php?$comment_item_id'>$news_title</a><br />";
				}
			break;

			case 1:	//	article, review or content page

				//	find out whether article, review or content page ...
				$sql2 -> db_Select("content", "content_heading, content_type", "content_id=$comment_item_id ");
				$row = $sql2 -> db_Fetch(); extract($row);
				
				switch($content_type){
					case 0:	//	article
						$str .= $bullet."[ Article ] Re: <a href='".e_BASE."content.php?article.$comment_item_id'>$content_heading</a><br />";
					break;
					case 1:	//	content page
						$str .= $bullet."[ Content ] Re: <a href='".e_BASE."content.php?content.$comment_item_id'>$content_heading</a><br />";
					break;
					case 3:	//	review
						$str .= $bullet."[ Review ] Re: <a href='".e_BASE."content.php?review.$comment_item_id.'>$content_heading</a><br />";
					break;
				}
			break;




			case 2: //	downloads
				$sql2 -> db_Select("download", "download_name", "download_id=$comment_item_id ");
				$row = $sql2 -> db_Fetch(); extract($row);
				$str .= $bullet."[ Download ] Re: <a href='".e_BASE."download.php?view.$comment_item_id'>$download_name</a><br />";
			break;

			case 3: //	faq
				$sql2 -> db_Select("faq", "faq_question", "faq_id=$comment_item_id ");
				$row = $sql2 -> db_Fetch(); extract($row);
				$str .= $bullet."[ Download ] Re: <a href='".e_BASE."faq.php?view.$comment_item_id'>$faq_question</a><br />";
			break;

			case 4:	//	poll comment
				$sql2 -> db_Select("poll", "*", "poll_id=$comment_item_id ");
				$row = $sql2 -> db_Fetch(); extract($row);
				$str .= $bullet."[ Poll ] Re: <a href='".e_BASE."comment.php?poll.$comment_item_id'>$poll_title</a><br />";
			break;

			
			case 5: //	docs
				$str .= $bullet."[ Download ] Re: <a href='http://e107.org/docs/main.php?$comment_item_id'>Doc $comment_item_id</a><br />";
			break;

			case 6:	//	bugtracker
				$sql2 -> db_Select("bugtrack", "bugtrack_summary", "bugtrack_id=$comment_item_id ");
				$row = $sql2 -> db_Fetch(); extract($row);
				$str .= $bullet."[ Bugtracker ] Re: <a href='".e_PLUGIN."bugtracker/bugtracker.php?show.$comment_item_id'>$bugtrack_summary</a><br />";
			break;

		}
	}
}else{
	$str = LIST_4;
}

$text .= "
<tr>
<td class='fcaption'>".LIST_2." ($comments)</td>
</tr>
<td class='forumheader3'>".$str."</td>
</tr>\n";

unset($str);
if($chatbox_posts = $sql -> db_Select("chatbox", "*", "cb_datestamp>$lvisit ORDER BY cb_datestamp DESC LIMIT 0,50")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		$cb_id = substr($cb_nick , 0, strpos($cb_nick , "."));
		$cb_nick = substr($cb_nick , (strpos($cb_nick , ".")+1));
		$cb_message = str_replace("<br />", "", $aj -> tpa($cb_message));
		$str .= $bullet."[ <a href='".e_BASE."user.php?id.$cb_id'>$cb_nick</a> ] $cb_message<br />";
	}
}else{
	$str = LIST_4;
}

$text .= "
<tr>
<td class='fcaption'>".LIST_5." ($chatbox_posts)</td>
</tr>
<td class='forumheader3'>".$str."</td>
</tr>\n";

unset($str);
if($forum_posts = $sql -> db_Select("forum_t", "*", "thread_datestamp>$lvisit ORDER BY thread_datestamp DESC LIMIT 0,50")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		$sql2 -> db_Select("forum", "*", "forum_id=$thread_forum_id");
		$row = $sql2 -> db_Fetch(); extract($row);
		if(check_class($forum_class)){

			$cb_id = substr($thread_user , 0, strpos($thread_user , "."));
			$cb_nick = substr($thread_user , (strpos($thread_user , ".")+1));

			if($thread_parent){
				$ttemp = $thread_id;
				$sql2 -> db_Select("forum_t", "*", "thread_id=$thread_parent ");
				$row = $sql2 -> db_Fetch(); extract($row);
				$str .= $bullet."[ <a href='".e_BASE."forum_viewforum.php?$forum_id'>$forum_name</a> ] Re: <a href='".e_BASE."forum_viewtopic.php?$thread_forum_id.$thread_id#$ttemp'>$thread_name</a><br />";
			}else{
				$str .= $bullet."[ <a href='".e_BASE."forum_viewforum.php?$forum_id'>$forum_name</a> ] <a href='".e_BASE."forum_viewtopic.php?$thread_forum_id.$thread_id'>$thread_name</a><br />";
			}
		}
	}
}else{
	$str = LIST_4;
}

$text .= "
<tr>
<td class='fcaption'>".LIST_6." ($forum_posts)</td>
</tr>
<td class='forumheader3'>".$str."</td>
</tr>\n";

unset($str);
if($new_members = $sql -> db_Select("user", "*", "user_join>$lvisit ORDER BY user_join DESC LIMIT 0,30")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		$str .= "$bullet<a href='".e_BASE."user.php?id.$user_id'>$user_name</a><br />";
	}
}else{
	$str = LIST_4;
}

$text .= "
<tr>
<td class='fcaption'>".LIST_7." ($new_members)</td>
</tr>
<td class='forumheader3'>".$str."</td>
</tr>\n";


















$text .= "</table>\n</div>";
$ns -> tablerender(LIST_3, $text);

/*
			$time = USERLV;
			$new_news = $sql -> db_Count("news", "(*)", "WHERE news_datestamp>'".$time."' "); if(!$new_news){ $new_news = "no"; }
			$new_comments = $sql -> db_Count("comments", "(*)", "WHERE comment_datestamp>'".$time."' "); if(!$new_comments){ $new_comments = "no"; }
			$new_chat = $sql -> db_Count("chatbox", "(*)", "WHERE cb_datestamp>'".$time."' "); if(!$new_chat){ $new_chat = "no"; }
			$new_forum = $sql -> db_Count("forum_t", "(*)", "WHERE thread_datestamp>'".$time."' "); if(!$new_forum){ $new_forum = "no"; }
			$new_users = $sql -> db_Count("user", "(*)", "WHERE user_join>'".$time."' "); if(!$new_users){ $new_users = "no"; }
*/


require_once(FOOTERF);
?>