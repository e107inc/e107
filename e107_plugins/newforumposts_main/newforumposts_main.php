<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/article.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

if($sql -> db_Select_gen("SELECT * FROM ".MPREFIX."forum_t, ".MPREFIX."forum WHERE ".MPREFIX."forum.forum_id=".MPREFIX."forum_t.thread_forum_id AND ".MPREFIX."forum_t.thread_parent=0 ORDER BY ".MPREFIX."forum_t.thread_datestamp DESC LIMIT 0, ".$pref['nfp_amount'])){
	$text = "<div style='text-align:center'>\n<table style='width:auto' class='fborder'>\n";
	if(!is_object($sql2)){
		$sql2 = new db;
	}
	if(!is_object($gen)){
		$gen = new convert;
	}

	$text .= "<tr>
		<td style='width:5%' class='forumheader'>&nbsp;</td>
		<td style='width:45%' class='forumheader'>Thread</td>
		<td style='width:15%; text-align:center' class='forumheader'>Poster</td>
		<td style='width:5%; text-align:center' class='forumheader'>Views</td>
		<td style='width:5%; text-align:center' class='forumheader'>Replies</td>
		<td style='width:25%; text-align:center' class='forumheader'>Lastpost</td>
		</tr>\n";

	while($row = $sql -> db_Fetch()){
		extract($row);
		if(check_class($forum_class)){
			$sql2 -> db_Select("forum_t", "*", "thread_parent='$thread_id' ORDER BY thread_datestamp DESC");
			list($null, $null, $null, $null, $r_datestamp, $null, $r_user) = $sql2 -> db_Fetch();
			$r_id = substr($r_user, 0, strpos($r_user, "."));
			$r_name = substr($r_user, (strpos($r_user, ".")+1));
			$r_datestamp = $gen->convert_date($r_datestamp, "forum");

			$post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
			$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));

			$replies = $sql2 -> db_Select("forum_t", "*", "thread_parent=$thread_id");

			$text .= "<tr>
			<td style='width:5%; text-align:center; border-bottom-style: dotted; border-bottom-width: 1; padding:3px'><img src='".e_IMAGE."forum/new_small.png' alt='' /></td>
			<td style='width:45%; border-bottom-style: dotted; border-bottom-width: 1'><b><a href='".e_BASE."forum_viewtopic.php?$forum_id.$thread_id'>$thread_name</a></b> <span class='smalltext'>(<a href='".e_BASE."forum_viewforum.php?$forum_id'>$forum_name</a></span>}</td>
			<td style='width:15%; text-align:center; border-bottom-style: dotted; border-bottom-width: 1'><a href='".e_BASE."user.php?id.$post_author_id'>$post_author_name</a></td>
			<td style='width:5%; text-align:center; border-bottom-style: dotted; border-bottom-width: 1'>$thread_views</td>
			<td style='width:5%; text-align:center; border-bottom-style: dotted; border-bottom-width: 1'>$replies</td>
			<td style='width:25%; text-align:center; border-bottom-style: dotted; border-bottom-width: 1'>".($replies ? "<b><a href='".e_BASE."user.php?id.$r_id'>$r_name</a></b><br /><span class='smalltext'>$r_datestamp</span>" : "-")."</td>
			</tr>\n";
		}
	}

	$total_topics = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent='0' ");
	$total_replies = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent!='0' ");
	$total_views = $sql -> db_Count("SELECT sum(thread_views) FROM ".MPREFIX."forum_t", "generic");

	$text .= "<tr>\n<td colspan='6' style='text-align:center' class='smalltext'>
	Threads: <b>$total_topics</b> | Replies: <b>$total_replies</b> | Views: <b>$total_views</b>
	</td>\n</tr>\n";

	$text .= "</table>\n</div>";

	$ns -> tablerender($pref['nfp_caption'], $text);

}














?>