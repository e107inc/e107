<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/forum_viewtopic.php
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

if(!e_QUERY){
	header("Location:".e_HTTP."forum.php");
}else{
	$tmp = explode(".", e_QUERY);
	$forum_id = $tmp[0]; $thread_id = $tmp[1]; $from = $tmp[2];
	if(!$from){ $from = 0; }
}

$captionlinkcolour = "#fff";
$gen = new convert;
$aj = new textparse();

$sql -> db_Update("forum_t", "thread_views=thread_views+1 WHERE thread_id='$thread_id' ");

$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
$row = $sql-> db_Fetch(); extract($row);

if(!$forum_active || $forum_class && !check_class($forum_class)){ header("Location:".e_HTTP."forum.php"); }

require_once(HEADERF);

$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ORDER BY thread_datestamp DESC ");
$row = $sql-> db_Fetch(); extract($row);

echo "<table style=\"width:100%\" class=\"fborder\">
<tr>
<td  colspan=\"2\" class=\"fcaption\"><a style=\"color:$captionlinkcolour\" href=\"".e_HTTP."index.php\">".SITENAME."</a> -> <a style=\"color:$captionlinkcolour\" href=\"forum.php\">Forums</a> -> <a style=\"color:$captionlinkcolour\" href=\"forum_viewforum.php?".$forum_id."\">".$forum_name."</a> -> ".$thread_name."</td>
</tr><tr>
<td style=\"width:80%; vertical-align:bottom\">".LAN_321.$forum_moderators;
	
echo "</td><td style=\"width:20%; text-align:right\">";

if(ANON || USER){
	if($thread_active){
		echo "<a href=\"forum_post.php?rp.".e_QUERY."\"><img src=\"themes/shared/forum/reply.png\" alt=\"\" style=\"border:0\" /></a>";
	}
	echo "<a href=\"forum_post.php?nt.".$forum_id."\"><img src=\"themes/shared/forum/newthread.png\" alt=\"\" style=\"border:0\" /></a>";
}

echo "</td></tr><tr><td colspan=\"2\">";

if(!$thread_active){
	echo "<div class=\"mediumtext\"  style=\"text-align:center\"><b>".LAN_66."</b></div>";
}

$text = "<table style=\"width:100%\" class=\"fborder\">
<tr>
<td style=\"width:20%; text-align:center\" class=\"fcaption\">Author</td>
<td style=\"width:80%; text-align:center\" class=\"fcaption\">Post</td>
</tr>";

$post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));

$starter_count = $sql -> db_Count("forum_t", "(*)", " WHERE thread_user='$thread_user' OR thread_user='$post_author_name' ");

$newflag = FALSE;
if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
	$newflag = TRUE;
}
if($newflag == TRUE){
	$starter_info = "<img src=\"themes/shared/forum/new.png\" alt=\"\" /><br />";
	$u_new .= ".".$thread_id.".";
}

$user_new = ereg_replace("\.".$thread_id."\.", ".", $user_new);

if(!$post_author_id){
	$starter_info .= "<b>".$post_author_name."</b><br /><span class=\"smallblacktext\">".LAN_194."</span>";
}else{
	
	$sql -> db_Select("user", "*", "user_id='".$post_author_id."' ");
	$row = $sql -> db_Fetch(); extract($row);

	$starter_info .= "<div class=\"mediumtext\"><a href=\"user.php?id.".$post_author_id."\"><b>".$post_author_name."</b></a></div>";
	if($user_image){
		if(ereg("avatar_", $user_image)){
			$avatarlist[0] = "";
			$handle=opendir(e_BASE."themes/shared/avatars/");
			while ($file = readdir($handle)){
				if($file != "." && $file != ".."){
					$avatarlist[] = $file;
				}
			}
			$user_image = e_HTTP."themes/shared/avatars/".$avatarlist[substr(strrchr($user_image, "_"), 1)];
		}
		$starter_info .= "<div class=\"spacer\"><img src=\"".$user_image."\" alt=\"\" /></div>";
	}

	$starter_info .= "<div class=\"smallblacktext\">";
	if(eregi($user_name, $forum_moderators)){
		$starter_info .= "<b><u>".LAN_193."</u></b><br /><br />";	
	}else{
		$user_join = $gen->convert_date($user_join, "short");
		$starter_info .= LAN_195."#".$user_id."<br />joined $user_join<br /><br />";
	}
	$text .= "</div>";
}

$thread_datestamp = $gen->convert_date($thread_datestamp, "forum");
if($post_author_id){ $starter_info .= LAN_67.": $starter_count"; }

$post_info = "<div class=\"smallblacktext\" style=\"text-align:right\"><img src=\"themes/shared/forum/post.png\" alt=\"\" /> ".LAN_322.$thread_datestamp."</div>";
$post_info .= $aj -> tpa($thread_thread, $mode="off");

if($user_signature){
	$user_signature = $aj -> tpa($user_signature);
	$post_info .= "<br /><hr style=\"width:70%; text-align:left\" />".$user_signature;
}

if($post_author_id){
	$option_info .= "<a href=\"user.php?id.".$user_id."\"><img src=\"themes/shared/forum/profile.png\" alt=\"\" style=\"border:0\" /></a> ";
}

if(!$user_hideemail && $post_author_id){
	$option_info .= "<a href=\"mailto:$user_email\"><img src=\"themes/shared/forum/email.png\" alt=\"\" style=\"border:0\" /></a> ";
}
if($user_homepage && $user_homepage != "http://"){
	$option_info .= "<a href=\"$user_homepage\"><img src=\"themes/shared/forum/website.png\" alt=\"\" style=\"border:0\" /></a> ";
}

if($post_author_name == USERNAME && $thread_active){
	$option_info .= "<a href=\"forum_post.php?quote.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>
	<a href=\"forum_post.php?edit.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/edit.png\" alt=\"\" style=\"border:0\" /></a></td>";
}else if($thread_active){
	$option_info .= "<a href=\"forum_post.php?quote.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>";
}

if(eregi(ADMINNAME, $forum_moderators)){
	$post_info .= "<div class=\"smallblacktext\" style=\"text-align:right\">[ moderator - <a href=\"forum_post.php?edit.".$forum_id.".".$thread_id."\">".LAN_68."</a> - 
	<a href=\"admin/forum_conf.php?delete.".$forum_id.".".$thread_id."\">".LAN_69."</a> -
	<a href=\"admin/forum_conf.php?move.".$forum_id.".".$thread_id."\">".LAN_70."</a> ]</div>";
}


$text .= "<tr> 
<td class=\"forumheader3\" style=\"vertical-align:top\">".$starter_info."</td>
<td class=\"forumheader3\" style=\"vertical-align:top\">".$post_info."</td>
</tr>
<tr> 
<td class=\"forumheader3\"><span class=\"smallblacktext\"><a href=\"".e_SELF."?".$_SERVER['QUERY_STRING']."#top\">Back to top</a></span></td>
<td class=\"forumheader3\" style=\"vertical-align:top\">".$option_info."</td>
</tr>";

unset($starter_info, $post_info, $option_info);

$ta = $thread_active;


//@^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^@//

if($sql -> db_Select("forum_t", "*", "thread_parent='".$thread_id."' ORDER BY thread_datestamp ASC")){
	$sql2 = new db;
	while($row = $sql-> db_Fetch()){
		extract($row);

		$post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
		$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));

		$starter_count = $sql2 -> db_Count("forum_t", "(*)", " WHERE thread_user='$thread_user' OR thread_user='$post_author_name' ");

		$newflag = FALSE;
		if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
			$newflag = TRUE;
		}
		if($newflag == TRUE){
			$starter_info = "<img src=\"themes/shared/forum/new.png\" alt=\"\" /><br />";
			$u_new .= ".".$thread_id.".";
		}
		$user_new = ereg_replace("\.".$thread_id."\.", ".", $user_new);

		if(!$post_author_id){
			$starter_info .= "<b>".$post_author_name."</b><br /><span class=\"smallblacktext\">".LAN_194."</span>";
			unset($user_email, $user_signature, $user_homepage);
		}else{
		
			$sql2 -> db_Select("user", "*", "user_id='".$post_author_id."' ");
			$row = $sql2 -> db_Fetch(); extract($row);

			$starter_info .= "<div class=\"mediumtext\"><a href=\"user.php?id.".$post_author_id."\"><b>".$post_author_name."</b></a></div>";
			if($user_image){
				if(ereg("avatar_", $user_image)){
					$avatarlist[0] = "";
					$handle=opendir(e_BASE."themes/shared/avatars/");
					while ($file = readdir($handle)){
						if($file != "." && $file != ".."){
							$avatarlist[] = $file;
						}
					}
					$user_image = e_HTTP."themes/shared/avatars/".$avatarlist[substr(strrchr($user_image, "_"), 1)];
				}
				$starter_info .= "<div class=\"spacer\"><img src=\"".$user_image."\" alt=\"\" /></div>";
			}
		
			$starter_info .= "<div class=\"smallblacktext\">";
			if(eregi($user_name, $forum_moderators)){
				$starter_info .= "<b><u>".LAN_193."</u></b><br /><br />";	
			}else{
				$user_join = $gen->convert_date($user_join, "forum");
				$starter_info .= LAN_195."#$user_id<br />joined $user_join<br /><br />";
			}
			$starter_info .= LAN_67.": ".$starter_count."</div>";
		}

		$post_info = "<div class=\"smallblacktext\" style=\"text-align:right\"><img src=\"themes/shared/forum/post.png\" alt=\"\" /> ".LAN_322.$gen->convert_date($thread_datestamp, "forum")."</div>";
		$post_info .= $aj -> tpa($thread_thread, $mode="off");

		if($user_signature){
			$user_signature = $aj -> tpa($user_signature);
			$post_info .= "<br /><hr style=\"width:30%; text-align:left\" />".$user_signature;
		}

		if($post_author_id){
			$option_info .= "<a href=\"user.php?id.".$user_id."\"><img src=\"themes/shared/forum/profile.png\" alt=\"\" style=\"border:0\" /></a> ";
		}

		if(!$user_hideemail && $post_author_id){
			$option_info .= "<a href=\"mailto:$user_email\"><img src=\"themes/shared/forum/email.png\" alt=\"\" style=\"border:0\" /></a> ";
		}
		if($user_homepage && $user_homepage != "http://"){
			$option_info .= "<a href=\"$user_homepage\"><img src=\"themes/shared/forum/website.png\" alt=\"\" style=\"border:0\" /></a> ";
		}

		if($post_author_name == USERNAME && $thread_active){
			$option_info .= "<a href=\"forum_post.php?quote.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>
			<a href=\"forum_post.php?edit.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/edit.png\" alt=\"\" style=\"border:0\" /></a></td>";
		}else if($thread_active){
			$option_info .= "<a href=\"forum_post.php?quote.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>";
		}

		if(eregi(ADMINNAME, $forum_moderators)){
			$post_info .= "<div class=\"smallblacktext\" style=\"text-align:right\">[ moderator - <a href=\"forum_post.php?edit.".$forum_id.".".$thread_id."\">".LAN_68."</a> - 
			<a href=\"admin/forum_conf.php?delete.".$forum_id.".".$thread_id."\">".LAN_69."</a> -
			<a href=\"admin/forum_conf.php?move.".$forum_id.".".$thread_id."\">".LAN_70."</a> ]</div>";
		}

		$text .= "<tr> 
		<td class=\"forumheader3\" style=\"vertical-align:top\">".$starter_info."</td>
		<td class=\"forumheader3\" style=\"vertical-align:top\">".$post_info."</td>
		</tr>
		<tr> 
		<td class=\"forumheader3\"><span class=\"smallblacktext\"><a href=\"".e_SELF."?".$_SERVER['QUERY_STRING']."#top\">Back to top</a></span></td>
		<td class=\"forumheader3\" style=\"vertical-align:top\">".$option_info."</td>
		</tr>";
		unset($starter_info, $post_info, $option_info);
	}
}


echo $text;

$text = "<table style=\"width:100%\">
<tr>
<td style=\"width:50%\">";
$text .= forumjump();
$text .= "</td>
<td style=\"width:50%; text-align:right\">";


if(ANON || USER){
	if($ta){
		$text .= "<a href=\"forum_post.php?rp.".e_QUERY."\"><img src=\"themes/shared/forum/reply.png\" alt=\"\" style=\"border:0\" /></a>";
	}
	$text .=  "<a href=\"forum_post.php?nt.".$forum_id."\"><img src=\"themes/shared/forum/newthread.png\" alt=\"\" style=\"border:0\" /></a>";
}else{
	$text .= LAN_59;
}
	$text .= "</td>
	</tr>
	</table>";

	echo $text;
	
	echo "</td></tr></table>";	

//	$sql -> db_Update("user", "user_new='$user_new' WHERE user_sess='".session_id()."' ");


$u_new = USERVIEWED . $u_new;
if($u_new != ""){ $sql -> db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' "); }



require_once(FOOTERF);
function forumjump(){
	$sql = new db;
	$sql -> db_Select("forum", "*", "forum_parent !=0 AND forum_active='1'");
	$c=0;
	$text .= "<form method=\"post\" action=\"".e_SELF."\"><p>Jump: <select name=\"forumjump\" class=\"tbox\">";
	while($row = $sql -> db_Fetch()){
		extract($row);
		if(!$forum_class || check_class($forum_class)){
			$text .= "\n<option>".$forum_name."</option>";
		}
	}
	$text .= "</select> <input class=\"button\" type=\"submit\" name=\"fjsubmit\" value=\"Go\" />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"".e_SELF."?".$_SERVER['QUERY_STRING']."#top\">Back to top</a></p></form>";
	return $text;
}
?>