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

require_once(e_HANDLER."userclass_class.php");
$query = ($pref['nfp_posts'] ? "thread_lastpost" : "thread_datestamp");
$lan_file=e_PLUGIN."newforumposts_main/languages/".e_LANGUAGE.".php";
if(file_exists($lan_file)){
	require_once($lan_file);
} else {
	require_once(e_PLUGIN."newforumposts_main/languages/English.php");
}
$results = $sql -> db_Select_gen("SELECT * FROM ".MPREFIX."forum_t, ".MPREFIX."forum WHERE ".MPREFIX."forum.forum_id=".MPREFIX."forum_t.thread_forum_id AND ".MPREFIX."forum_t.thread_parent=0 ORDER BY ".MPREFIX."forum_t.$query DESC LIMIT 0, ".$pref['nfp_amount']);
	$text = "<div style='text-align:center'>\n<table style='width:auto' class='fborder'>\n";
	if(!is_object($sql2)){
		$sql2 = new db;
	}
	if(!is_object($gen)){
		$gen = new convert;
	}

	$text .= "<tr>
		<td style='width:5%' class='forumheader'>&nbsp;</td>
		<td style='width:45%' class='forumheader'>".LAN_1."</td>
		<td style='width:15%; text-align:center' class='forumheader'>".LAN_2."</td>
		<td style='width:5%; text-align:center' class='forumheader'>".LAN_3."</td>
		<td style='width:5%; text-align:center' class='forumheader'>".LAN_4."</td>
		<td style='width:25%; text-align:center' class='forumheader'>".LAN_5."</td>
		</tr>\n";

	while($row = $sql -> db_Fetch()){
		extract($row);
		if(check_class($forum_class)){
			$sql2 -> db_Select("forum_t", "*", "thread_parent='$thread_id' ORDER BY $query DESC");
			list($null, $null, $null, $null, $r_datestamp, $null, $r_user) = $sql2 -> db_Fetch();
			$r_id = substr($r_user, 0, strpos($r_user, "."));
			$r_name = substr($r_user, (strpos($r_user, ".")+1));

			if(strstr($r_name, chr(1))){
				$tmp = explode(chr(1), $r_name);
				$r_name = $tmp[0];
			}

			$r_datestamp = $gen->convert_date($r_datestamp, "forum");

			$post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
			$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));
			if(strstr($post_author_name, chr(1))){ 
				$tmp = explode(chr(1), $post_author_name); 
				$post_author_name = $tmp[0];
			}

			$replies = $sql2 -> db_Select("forum_t", "*", "thread_parent=$thread_id");

			$text .= "<tr>
			<td style='width:5%; text-align:center' class='forumheader3'><img src='".e_IMAGE."forum/new_small.png' alt='' /></td>
			<td style='width:45%' class='forumheader3'><b><a href='".e_BASE."forum_viewtopic.php?$forum_id.$thread_id'>$thread_name</a></b> <span class='smalltext'>(<a href='".e_BASE."forum_viewforum.php?$forum_id'>$forum_name</a>)</span></td>
			<td style='width:15%; text-align:center' class='forumheader3'>".(USER ? "<a href='".e_BASE."user.php?id.$post_author_id'>" : "")."$post_author_name".(USER ? "</a>" :"")."</td>
			<td style='width:5%; text-align:center' class='forumheader3'>$thread_views</td>
			<td style='width:5%; text-align:center' class='forumheader3'>$replies</td>
			<td style='width:25%; text-align:center' class='forumheader3'>".($replies ? "<b>".(USER && $r_id > 0 ? "<a href='".e_BASE."user.php?id.$r_id'>" : "")."$r_name".(USER && $r_id > 0 ? "</a>" : "")."</b><br /><span class='smalltext'>$r_datestamp</span>" : "-")."</td>
			</tr>\n";
		}else{
			$results --;
		}
	}

	$total_topics = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent='0' ");
	$total_replies = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent!='0' ");
	$total_views = $sql -> db_Count("SELECT sum(thread_views) FROM ".MPREFIX."forum_t", "generic");

	$text .= "<tr>\n<td colspan='6' style='text-align:center' class='forumheader2'>
	<span class='smalltext'>".LAN_6.": <b>$total_topics</b> | ".LAN_4.": <b>$total_replies</b> | ".LAN_3.": <b>$total_views</b></span>
	</td>\n</tr>\n";

	$text .= "</table>\n</div>";


	$text = ($pref['nfp_layer'] ? "<div style='border : 0; padding : 4px; width : auto; height : ".$pref['nfp_layer_height']."px; overflow : auto; '>".$text."</div>" : $text);
if($results){
	$ns -> tablerender($pref['nfp_caption'], $text, "nfp");
}
?>