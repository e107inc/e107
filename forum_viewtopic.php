<?php
if(IsSet($_POST['fjsubmit'])){
	header("location:forum_viewforum.php?".$_POST['forumjump']);
	exit;
}
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
define("FTHEME", (file_exists(THEME."forum/newthread.png")) ? THEME."forum/" : "themes/shared/forum/");

if(!e_QUERY){
	header("Location:".e_HTTP."forum.php");
	exit;
}else{
	$tmp = explode(".", e_QUERY);
	$forum_id = $tmp[0]; $thread_id = $tmp[1]; $from = $tmp[2]; $action = $tmp[3];
	if(!$from){ $from = 0; }
	if(!$thread_id){
		header("Location:".e_HTTP."forum.php");
		exit;
	}
}

if($action == "track"){
	$sql -> db_Update("user", "user_realm='".USERREALM.".".$thread_id.".' WHERE user_id='".USERID."' ");
	header("location:".e_SELF."?".$forum_id.".".$thread_id);
	exit;
}

if($action == "untrack"){
	$tmp = ereg_replace(".".$thread_id.".", "", USERREALM);
	$sql -> db_Update("user", "user_realm='$tmp' WHERE user_id='".USERID."' ");
	header("location:".e_SELF."?".$forum_id.".".$thread_id);
	exit;
}

$gen = new convert;
$aj = new textparse();

$sql -> db_Update("forum_t", "thread_views=thread_views+1 WHERE thread_id='$thread_id' ");

$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
$row = $sql-> db_Fetch(); extract($row);

if(!$forum_active || $forum_class && !check_class($forum_class)){ header("Location:".e_HTTP."forum.php"); exit;}

require_once(HEADERF);

$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ORDER BY thread_datestamp DESC ");
$row = $sql-> db_Fetch("no_strip"); extract($row);

if(preg_match("/".preg_quote(ADMINNAME)."/", $forum_moderators) && getperms("A")){
	define("MODERATOR", TRUE);
}else{
	define("MODERATOR", FALSE);
}

If(IsSet($_POST['pollvote'])){
	$sql -> db_Select("poll", "poll_active, poll_ip", "poll_id='".$_POST['pollid']."' ");
	$row = $sql -> db_Fetch(); extract($row);
	$user_id = ($poll_active == 9 ? getip() : USERID);
	if(!preg_match("/".$user_id."\^/", $poll_ip)){
		if($_POST['votea']){
			$num = "poll_votes_".$_POST['votea'];
			$sql -> db_Update("poll", "$num=$num+1, poll_ip='".$poll_ip.$user_id."^' WHERE poll_id='".$_POST['pollid']."' ");
		}
	}
}

if(eregi("\[poll\]", $thread_name)){

	if($sql -> db_Select("poll", "*", "poll_datestamp='$thread_id' ")){

		list($poll_id, $poll_datestamp, $poll_end_datestamp, $poll_admin_id, $poll_title, $poll_option[0], $poll_option[1], $poll_option[2], $poll_option[3], $poll_option[4], $poll_option[5], $poll_option[6], $poll_option[7], $poll_option[8], $poll_option[9], $votes[0], $votes[1], $votes[2], $votes[3], $votes[4], $votes[5], $votes[6], $votes[7], $votes[8], $votes[9], $poll_ip, $poll_active) = $sql-> db_Fetch();

		$user_id = ($poll_active == 9 ? getip() : USERID);
		if(preg_match("/".$user_id."\^/", $poll_ip)){
			$mode = "voted";
		}else if($poll_active == 2 && !USER){
			$mode = "disallowed";
		}else{
			$mode = "notvoted";
		}
		require_once(e_BASE."classes/poll_class.php");
		$poll = new poll;
		$poll -> render_poll($poll_id, $poll_title, $poll_option, $votes, $mode, "forum");
	}
}

echo "<table style='width:100%' class='fborder'>
<tr>
<td  colspan='2' class='fcaption'>
<a class='forumlink' href='".e_HTTP."index.php'>".SITENAME."</a> -> <a class='forumlink' href='forum.php'>Forums</a> -> <a class='forumlink' href='forum_viewforum.php?".$forum_id."'>".$forum_name."</a> -> ".$thread_name."
</td>
</tr>

<tr>
<td class='forumheader' colspan='2'>
<table cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='smalltext'>";

$sql -> db_Select("forum_t", "thread_id",  "thread_forum_id='".$forum_id."' AND thread_parent='0' ORDER BY thread_s ASC, thread_lastpost ASC, thread_datestamp ASC");
$c = 0;
while($row = $sql -> db_Fetch()){
	$array[$c] = $row['thread_id'];
	if($row['thread_id'] == $thread_id){
		$prevthread = $array[$c-1];
		$row = $sql -> db_Fetch();
		$nextthread = $row['thread_id'];
		break;
	}
	$c++;
}

echo ($prevthread ? "&lt;&lt; <a href='".e_SELF."?".$forum_id.".".$prevthread."'>".LAN_389."</a> " : "No previous thread ")."|".($nextthread ? " <a href='".e_SELF."?".$forum_id.".".$nextthread."'>".LAN_390."</a> &gt;&gt;" : " No next thread")."

</td>
<td style='text-align:right'>&nbsp;";

if($pref['forum_track'][1]){
	if(preg_match("/\.".$thread_id."\./", USERREALM)){
		echo "<span class='smalltext'><a href='".e_SELF."?".$forum_id.".".$thread_id.".0."."untrack'>".LAN_392."</a></span>";
	}else{
		echo "<span class='smalltext'><a href='".e_SELF."?".$forum_id.".".$thread_id.".0."."track'>".LAN_391."</a></span>";
	}
}
echo "</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style='width:80%; vertical-align:bottom'>".LAN_321.$forum_moderators;
	
echo "</td><td style='width:20%; text-align:right'>";

if(ANON || USER){
	if($thread_active){
		echo "<a href='forum_post.php?rp.".e_QUERY."'><img src='".FTHEME."reply.png' alt='' style='border:0' /></a>";
	}
	echo "<a href='forum_post.php?nt.".$forum_id."'><img src='".FTHEME."newthread.png' alt='' style='border:0' /></a>";
}

echo "</td></tr><tr><td colspan='2'>";

if(!$thread_active){
	echo "<div class='mediumtext'  style='text-align:center'><b>".LAN_66."</b></div>";
}

$post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));

$starter_count = $sql -> db_Count("forum_t", "(*)", " WHERE thread_user='$thread_user' OR thread_user='$post_author_name' ");

$newflag = FALSE;
if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
	$newflag = TRUE;
}
if($newflag == TRUE){
	$flag = "<img src='".FTHEME."new.png' alt='' /> ";
	$u_new .= ".".$thread_id.".";
}

$user_new = ereg_replace("\.".$thread_id."\.", ".", $user_new);

if(!$post_author_id){
	$poster = "<b>".$post_author_name."</b>";
	$starter_info = "<span class='smallblacktext'>".LAN_194."</span>";
}else{
	
	$sql -> db_Select("user", "*", "user_id='".$post_author_id."' ");
	$row = $sql -> db_Fetch(); extract($row);

	$poster .= "<a href='user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>";

	if($user_image){
		require_once(e_BASE."classes/avatar_handler.php");
		$user_image = avatar($user_image);
		$starter_info .= "<div class='spacer'><img src='".$user_image."' alt='' /></div>";
	}

	$starter_info .= "<div class='smallblacktext'>";
	if(preg_match("/(^|\s)".preg_quote($user_name)."/", $forum_moderators)){
		$starter_info .= "<img src='".FTHEME."moderator.png' alt='' style='border:0' /><br /><br />";
	}else{
		$user_join = $gen->convert_date($user_join, "forum");
		$starter_info .= LAN_195."#".$user_id."<br />joined $user_join<br /><br />";
	}
	$starter_info .= "</div>";
}

$thread_datestamp = $gen->convert_date($thread_datestamp, "forum");
if($post_author_id){ $starter_info .= LAN_67.": $starter_count"; }

$date_info = "<div class='smallblacktext'><img src='".FTHEME."post.png' alt='' /> ".$thread_datestamp."</div>";


$post_info .= $aj -> tpa($thread_thread, $mode="off");

$post_info = wrap($post_info);

if($user_signature){
	$user_signature = $aj -> tpa($user_signature);
	$post_info .= "<br /><hr style='width:70%; text-align:left' /><span class='smalltext'>".$user_signature."</span>";
}

if($post_author_id){
	$option_info .= "<a href='user.php?id.".$user_id."'><img src='".FTHEME."profile.png' alt='profile' style='border:0' /></a> ";
}

if(!$user_hideemail && $post_author_id){
	$option_info .= "<a href='mailto:$user_email'><img src='".FTHEME."email.png' alt='email' style='border:0' /></a> ";
}
if($user_homepage && $user_homepage != "http://"){
	$option_info .= "<a href='$user_homepage'><img src='".FTHEME."website.png' alt='website' style='border:0' /></a> ";
}

if($post_author_name == USERNAME && $thread_active){
	$user_opt = "<a href='forum_post.php?edit.".$forum_id.".".$thread_id."'><img src='".FTHEME."edit.png' alt='edit' style='border:0' /></a>";
}

$user_opt .= " <a href='forum_post.php?quote.".$forum_id.".".$thread_id."'><img src='".FTHEME."quote.png' alt='quote' style='border:0' /></a>";

if(MODERATOR){
	$mod_info .= "<div style='text-align:right'><a href='forum_post.php?edit.".$forum_id.".".$thread_id."'><img src='".FTHEME."admin_edit.png' alt='moderator: edit' style='border:0' /></a>
	<a href='".e_ADMIN."forum_conf.php?delete.".$forum_id.".".$thread_id."'><img src='".FTHEME."admin_delete.png' alt='moderator: delete' style='border:0' /></a>
	<a href='".e_ADMIN."forum_conf.php?move.".$forum_id.".".$thread_id."'><img src='".FTHEME."admin_move.png' alt='moderator: move' style='border:0' /></a></div>";
}

$text = "<table style='width:100%' class='fborder'>
<tr>
<td style='width:20%; text-align:center' class='fcaption'>Author</td>
<td style='width:80%; text-align:center' class='fcaption'>Post</td>
</tr>
<tr>
<td class='forumheader' style='vertical-align:middle'>";
if($flag){
	$text .= "<table cellspacing='0' cellpadding='0' style='width:100%'>
	<tr>
	<td style='width:2%'>".$flag."</td>
	<td style='text-align:left' class='forumheader'>&nbsp;".$poster."</td>
	</tr>
	</table>";
}else{
	$text .=  $poster;
}

$text .=  "</td>
<td class='forumheader' style='vertical-align:middle'>
<table cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td>".$date_info."</td>
<td style='text-align:right'>".$user_opt."</td>
</tr>
</table>
</td>
</tr>
<tr>
<td class='forumheader3' style='vertical-align:top'>".$starter_info."</td>
<td class='forumheader3' style='vertical-align:top'>".$post_info."</td>
</tr>
<tr> 
<td class='finfobar'><span class='smallblacktext'><a href='".e_SELF."?".$_SERVER['QUERY_STRING']."#top'>Back to top</a></span></td>
<td class='finfobar' style='vertical-align:top'>
<table cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td>".$option_info."</td>
<td style='text-align:right'>".$mod_info."</td>
</tr>
</table>
</td>
</tr>
<tr>
<td colspan='2'></td>
</tr>";

unset($starter_info, $post_info, $option_info);

$ta = $thread_active;


//@^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^@//

if($sql -> db_Select("forum_t", "*", "thread_parent='".$thread_id."' ORDER BY thread_datestamp ASC")){
	$sql2 = new db;
	while($row = $sql-> db_Fetch("no_strip")){
		extract($row);

		$post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
		$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));

		$starter_count = $sql2 -> db_Count("forum_t", "(*)", " WHERE thread_user='$thread_user' OR thread_user='$post_author_name' ");

		$newflag = FALSE;
		if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
			$newflag = TRUE;
		}
		if($newflag == TRUE){
			$poster_info = "<img src='".FTHEME."new.png' alt='' /> ";
			$u_new .= ".".$thread_id.".";
		}
		$user_new = ereg_replace("\.".$thread_id."\.", ".", $user_new);

		if(!$post_author_id){
			$poster_info = "<b>".$post_author_name."</b>";
			$starter_info = "<span class='smallblacktext'>".LAN_194."</span>";
			unset($user_email, $user_signature, $user_homepage);
		}else{
		
			$sql2 -> db_Select("user", "*", "user_id='".$post_author_id."' ");
			$row = $sql2 -> db_Fetch(); extract($row);

			$poster_info = "<a href='user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>";
			if($user_image){
				require_once(e_BASE."classes/avatar_handler.php");
				$user_image = avatar($user_image);
				$starter_info .= "<div class='spacer'><img src='".$user_image."' alt='' /></div>";
			}

			$starter_info .= "<div class='smallblacktext'>";
			if(preg_match("/(^|\s)".preg_quote($post_author_name)."/", $forum_moderators)){
				$starter_info .= "<img src='".FTHEME."moderator.png' alt='' style='border:0' /><br /><br />";
			}else{
				$user_join = $gen->convert_date($user_join, "forum");
				$starter_info .= LAN_195."#$user_id<br />joined $user_join<br /><br />";
			}
			$starter_info .= LAN_67.": ".$starter_count."</div>";
		}

		$date_info = "<div class='smallblacktext'><img src='".FTHEME."post.png' alt='' /> ".$gen->convert_date($thread_datestamp, "forum")."</div>";
		$post_info .= $aj -> tpa($thread_thread, $mode="off");
		$post_info = wrap($post_info);

		if($user_signature){
			$user_signature = $aj -> tpa($user_signature);
			$post_info .= "<br /><hr style='width:70%; text-align:left' /><span class='smalltext'>".$user_signature."</span>";
		}

		if($post_author_id){
			$option_info .= "<a href='user.php?id.".$user_id."'><img src='".FTHEME."profile.png' alt='profile' style='border:0' /></a> ";
		}

		if(!$user_hideemail && $post_author_id){
			$option_info .= "<a href='mailto:$user_email'><img src='".FTHEME."email.png' alt='email' style='border:0' /></a> ";
		}
		if($user_homepage && $user_homepage != "http://"){
			$option_info .= "<a href='$user_homepage'><img src='".FTHEME."website.png' alt='website' style='border:0' /></a> ";
		}

		$user_opt = ($post_author_name == USERNAME && $thread_active ? "<a href='forum_post.php?edit.".$forum_id.".".$thread_id."'><img src='".FTHEME."edit.png' alt='edit' style='border:0' /></a>" : "");

		$user_opt .= " <a href='forum_post.php?quote.".$forum_id.".".$thread_id."'><img src='".FTHEME."quote.png' alt='quote' style='border:0' /></a>";

		if(MODERATOR){
			$mod_info = "<a href='forum_post.php?edit.".$forum_id.".".$thread_id."'><img src='".FTHEME."admin_edit.png' alt='edit' style='border:0' /></a> 
			<a href='".e_ADMIN."forum_conf.php?delete.".$forum_id.".".$thread_id."'><img src='".FTHEME."admin_delete.png' alt='delete' style='border:0' /></a>";
		}

		$text .= "<tr>
		<td class='forumheader' style='vertical-align:middle'>";
		
		if($flag){
			$text .= "<table cellspacing='0' cellpadding='0' style='width:100%'>
			<tr>
			<td style='width:2%'>".$flag."</td>
			<td style='text-align:left' class='forumheader'>&nbsp;".$poster_info."</td>
			</tr>
			</table>";
		}else{
			$text .=  $poster_info;
		}
		
		
		$text .= "</td>
		<td class='forumheader' style='vertical-align:middle'>
		<table cellspacing='0' cellpadding='0' style='width:100%'>
		<tr>
		<td>".$date_info."</td>
		<td style='text-align:right'>".$user_opt."</td>
		</tr>
		</table>
		</td>
		</tr>
		<tr>
		<td class='forumheader3' style='vertical-align:top'>".$starter_info."</td>
		<td class='forumheader3' style='vertical-align:top'>".$post_info."</td>
		</tr>
		<tr> 
		<td class='finfobar'><span class='smallblacktext'><a href='".e_SELF."?".$_SERVER['QUERY_STRING']."#top'>Back to top</a></span></td>
		<td class='finfobar' style='vertical-align:top'>
		<table cellspacing='0' cellpadding='0' style='width:100%'>
		<tr>
		<td>".$option_info."</td>
		<td style='text-align:right'>".$mod_info."</td>
		</tr>
		</table>	
		</tr>
		<tr>
		<td colspan='2'></td>
		</tr>";
		unset($starter_info, $post_info, $option_info);
	}
}


echo $text;

$text = "<table style='width:100%'>
<tr>
<td style='width:50%'>";
$text .= forumjump();
$text .= "</td>
<td style='width:50%; text-align:right'>";


if(ANON || USER){
	if($ta){
		$text .= "<a href='forum_post.php?rp.".e_QUERY."'><img src='".FTHEME."reply.png' alt='' style='border:0' /></a>";
	}
	$text .=  "<a href='forum_post.php?nt.".$forum_id."'><img src='".FTHEME."newthread.png' alt='' style='border:0' /></a>";
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
	$text .= "<form method='post' action='".e_SELF."'><p>Jump: <select name='forumjump' class='tbox'>";
	while($row = $sql -> db_Fetch()){
		extract($row);
		if(!$forum_class || check_class($forum_class)){
			$text .= "\n<option value='".$forum_id."'>".$forum_name."</option>";
		}
	}
	$text .= "</select> <input class='button' type='submit' name='fjsubmit' value='Go' />&nbsp;&nbsp;&nbsp;&nbsp;<a href='".e_SELF."?".$_SERVER['QUERY_STRING']."#top'>Back to top</a></p></form>";
	return $text;
}

function wrap($data){
	$wrapcount = 80;
	$message_array = explode(" ", $data);
	for($i=0; $i<=(count($message_array)-1); $i++){
		if(strlen($message_array[$i]) > $wrapcount){
			$message_array[$i] = preg_replace("/([^\s]{".$wrapcount."})/", "$1<br />", $message_array[$i]);
		}
	}
	$data = implode(" ",$message_array);
	return $data;
}


?>