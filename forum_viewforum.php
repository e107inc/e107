<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/forum_viewforum.php
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
if(IsSet($_POST['fjsubmit'])){
	header("location:".e_BASE."forum_viewforum.php?".$_POST['forumjump']);
	exit;
}
if(!e_QUERY){
	header("Location:".e_BASE."forum.php");
	exit;
}else{
	$tmp = explode(".", e_QUERY);
	$forum_id = $tmp[0]; $from = $tmp[1];
	if(!$from){ $from = 0; }
}

define("IMAGE_newthread", (file_exists(THEME."forum/newthread.png") ? "<img src='".THEME."forum/newthread.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/newthread.png' alt='' style='border:0' />"));
define("IMAGE_new_small", (file_exists(THEME."forum/new_small.png") ? "<img src='".THEME."forum/new_small.png' alt='' />" : "<img src='".e_IMAGE."forum/new_small.png' alt='' />"));
define("IMAGE_nonew_small", (file_exists(THEME."forum/nonew_small.png") ? "<img src='".THEME."forum/nonew_small.png' alt='' />" : "<img src='".e_IMAGE."forum/nonew_small.png' alt='' />"));
define("IMAGE_new_popular", (file_exists(THEME."forum/new_popular.gif") ? "<img src='".THEME."forum/new_popular.gif' alt='' />" : "<img src='".e_IMAGE."forum/new_popular.gif' alt='' />"));
define("IMAGE_nonew_popular", (file_exists(THEME."forum/nonew_popular.gif") ? "<img src='".THEME."forum/nonew_popular.gif' alt='' />" : "<img src='".e_IMAGE."forum/nonew_popular.gif' alt='' />"));
define("IMAGE_sticky", (file_exists(THEME."forum/sticky.png") ? "<img src='".THEME."forum/sticky.png' alt='' />" : "<img src='".e_IMAGE."forum/sticky.png' alt='' />"));
define("IMAGE_stickyclosed", (file_exists(THEME."forum/stickyclosed.png") ? "<img src='".THEME."forum/stickyclosed.png' alt='' />" : "<img src='".e_IMAGE."forum/stickyclosed.png' alt='' />"));
define("IMAGE_announce", (file_exists(THEME."forum/announce.png") ? "<img src='".THEME."forum/announce.png' alt='' />" : "<img src='".e_IMAGE."forum/announce.png' alt='' />"));
define("IMAGE_closed_small", (file_exists(THEME."forum/closed_small.png") ? "<img src='".THEME."forum/closed_small.png' alt='' />" : "<img src='".e_IMAGE."forum/closed_small.png' alt='' />"));
define("IMAGE_admin_unstick", (file_exists(THEME."forum/admin_unstick.png") ? "<img src='".THEME."forum/admin_unstick.png' alt='".LAN_398."' style='border:0' />" : "<img src='".e_IMAGE."forum/admin_unstick.png' alt='".LAN_398."' style='border:0' />"));
define("IMAGE_admin_lock", (file_exists(THEME."forum/admin_lock.png") ? "<img src='".THEME."forum/admin_lock.png' alt='".LAN_399."' style='border:0' />" : "<img src='".e_IMAGE."forum/admin_lock.png' alt='".LAN_399."' style='border:0' />"));
define("IMAGE_admin_unlock", (file_exists(THEME."forum/admin_unlock.png") ? "<img src='".THEME."forum/admin_unlock.png' alt='".LAN_400."' style='border:0' />" : "<img src='".e_IMAGE."forum/admin_unlock.png' alt='".LAN_400."' style='border:0' />"));
define("IMAGE_admin_stick", (file_exists(THEME."forum/admin_stick.png") ? "<img src='".THEME."forum/admin_stick.png' alt='".LAN_401."' style='border:0' />" : "<img src='".e_IMAGE."forum/admin_stick.png' alt='".LAN_401."' style='border:0' />"));
define("IMAGE_admin_move", (file_exists(THEME."forum/admin_move.png") ? "<img src='".THEME."forum/admin_move.png' alt='".LAN_402."' style='border:0' />" : "<img src='".e_IMAGE."forum/admin_move.png' alt='".LAN_402."' style='border:0' />"));

$STARTERTITLE = LAN_54;
	$THREADTITLE = LAN_53;
	$REPLYTITLE = LAN_55;
	$LASTPOSTITLE = LAN_57;
	$VIEWTITLE = LAN_56;

if(!$FORUM_VIEW_START){
	require_once(e_BASE.$THEMES_DIRECTORY."templates/forum_viewforum_template.php");
}

$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
$row = $sql-> db_Fetch(); extract($row);
define("e_PAGETITLE", LAN_01." / ".$row['forum_name']);

if($forum_class && !check_class($forum_class) || !$forum_parent){ header("Location:".e_BASE."forum.php"); exit;}

define("MODERATOR", (preg_match("/".preg_quote(ADMINNAME)."/", $forum_moderators) && getperms("A") ? TRUE : FALSE));

$member_users = $sql -> db_Select("online", "*", "online_location REGEXP('forum_viewforum.php.$forum_id') AND online_user_id!='0' ");
$guest_users = $sql -> db_Select("online", "*", "online_location REGEXP('forum_viewforum.php.$forum_id') AND online_user_id='0' ");
$users = $member_users+$guest_users;


require_once(HEADERF);

$view=25;
$topics = $sql -> db_Count("forum_t", "(*)", " WHERE thread_forum_id='".$forum_id."' AND thread_parent='0' ");
if($topics > $view){
	$a = $topics/$view;
	$r = explode(".", $a);
	if($r[1] != 0 ? $pages = ($r[0]+1) : $pages = $r[0]);
}else{
	$pages = FALSE;
}

$sql -> db_Select("forum", "*", "forum_id='".$forum_parent."' ");
$row_par = $sql-> db_Fetch();
$parent_title = $row_par['forum_name'];

if($pages){

	$THREADPAGES = LAN_316.": ";
	if($pages > 10){
		$current = ($from/$view)+1;
		for($c=0; $c<=2; $c++){
			$THREADPAGES .= ($view*$c == $from ? "<span style='text-decoration: underline;'>".($c+1)."</span> " : "<a href='".e_SELF."?".$forum_id.".".($view*$c)."'>".($c+1)."</a> ");
		}
		if($current >=3 && $current <= 5){
			for($c=3; $c<=$current; $c++){
				$THREADPAGES .= ($view*$c == $from ? "<span style='text-decoration: underline;'>".($c+1)."</span> " : "<a href='".e_SELF."?".$forum_id.".".($view*$c)."'>".($c+1)."</a> ");
			}
		}else if($current >= 6){
			$text .= " ... ";
			for($c=($current-2); $c<=$current; $c++){
				$THREADPAGES .= ($view*$c == $from ? "<span style='text-decoration: underline;'>".($c+1)."</span> " : "<a href='".e_SELF."?".$forum_id.".".($view*$c)."'>".($c+1)."</a> ");
			}
		}
		$text .= " ... ";
		$tmp = $pages-3;
		for($c=$tmp; $c<=($pages-1); $c++){
			$THREADPAGES .= ($view*$c == $from ? "<span style='text-decoration: underline;'>".($c+1)."</span> " : "<a href='".e_SELF."?".$forum_id.".".($view*$c)."'>".($c+1)."</a> ");
		}
	}else{
		for($c=0; $c < $pages; $c++){
			if($view*$c == $from ? $THREADPAGES .= "<b>".($c+1)."</b> " : $THREADPAGES .= "<a href='".e_SELF."?".$forum_id.".".($view*$c)."'>".($c+1)."</a> ");
		}
	}
	$THREADPAGES .= "<br />";
}

if((ANON || USER) && ($forum_class != e_UC_READONLY || MODERATOR)){
	$NEWTHREADBUTTON = "<a href='".e_BASE."forum_post.php?nt.".$forum_id."'>".IMAGE_newthread."</a>";
}

$BREADCRUMB = "<a class='forumlink' href='index.php'>".SITENAME."</a> >> <a class='forumlink' href='forum.php'>".LAN_01."</a> >> <b>".$forum_name."</b>";
$FORUMTITLE = $forum_name;
$MODERATORS = LAN_404.": ".$forum_moderators;
$BROWSERS = $users." ".($users == 1 ? LAN_405 : LAN_406)." (".$member_users." ".($member_users==1 ? LAN_407 : LAN_409).", ".$guest_users." ".($guest_users == 1 ? LAN_408 : LAN_410).")";


$ICONKEY = "
<table style='width:100%'>
<tr>
<td style='vertical-align:center; text-align:center; width:2%'>".IMAGE_new_small."</td>
<td style='width:10%' class='smallblacktext'>".LAN_79."</td>
<td style='vertical-align:center; text-align:center; width:2%'>".IMAGE_nonew_small."</td>
<td style='width:10%' class='smallblacktext'>".LAN_80."</td>
<td style='vertical-align:center; text-align:center; width:2%'>".IMAGE_sticky."</td>
<td style='width:10%' class='smallblacktext'>".LAN_202."</td>
<td style='vertical-align:center; text-align:center; width:2%'>".IMAGE_announce."</td>
<td style='width:10%' class='smallblacktext'>".LAN_396."</td>
</tr>
<tr>
<td style='vertical-align:center; text-align:center; width:2%'>".IMAGE_new_popular."</td>
<td style='width:2%' class='smallblacktext'>".LAN_79." ".LAN_395."</td>
<td style='vertical-align:center; text-align:center; width:2%'>".IMAGE_nonew_popular."</td>
<td style='width:10%' class='smallblacktext'>".LAN_80." ".LAN_395."</td>
<td style='vertical-align:center; text-align:center; width:2%'>".IMAGE_stickyclosed."</td>
<td style='width:10%' class='smallblacktext'>".LAN_203."</td>
<td style='vertical-align:center; text-align:center; width:2%'>".IMAGE_closed_small."</td>
<td style='width:10%' class='smallblacktext'>".LAN_81."</td>
</tr>
</table>";

$SEARCH = "
<form method='post' action='search.php'>
<p>
<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />
<input class='button' type='submit' name='searchsubmit' value='".LAN_180."' />
<input type='hidden' name='searchtype' value='7' />
</p>
</form>";

$PERMS = 
(USER == TRUE || ANON == TRUE ? LAN_204." - ".LAN_206." - ".LAN_208 : LAN_205." - ".LAN_207." - ".LAN_209);

$sticky_threads = 0;$stuck = FALSE;
$reg_threads = 0;$unstuck = FALSE;
if($sql -> db_Select("forum_t", "*",  "thread_forum_id='".$forum_id."' AND thread_parent='0' ORDER BY thread_s DESC, thread_lastpost DESC, thread_datestamp DESC LIMIT $from, $view")){
	$sql2 = new db; $sql3 = new db; $gen = new convert;
	while($row= $sql -> db_Fetch()){
		if($row['thread_s']){
			$sticky_threads ++;
		}
		if($sticky_threads == "1" && !$stuck){
			$forum_view_forum .= "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>".LAN_411."</b></span></td></tr>";
			$stuck = TRUE;
		}
		if(!$row['thread_s']){
			$reg_threads ++;
		}
		if($reg_threads == "1" && !$unstuck && $stuck){
			$forum_view_forum .= "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>".LAN_412."</b></span></td></tr>";
			$unstuck = TRUE;
		}
		$forum_view_forum .= parse_thread($row);
	}
}else{
	$forum_view_forum = "<tr><td colspan='6' style='text-align:center' class='forumheader2'><br /><span class='mediumtext'><b>".LAN_58."</b></span><br /><br /></td></tr>";
}

$sql -> db_Select("forum", "*", "forum_parent !=0 AND forum_class!='255' ");
$FORUMJUMP = "<form method='post' action='".e_SELF."'><p>".LAN_403.": <select name='forumjump' class='tbox'>";
while($row = $sql -> db_Fetch()){
	extract($row);
	if(check_class($forum_class)){
		$FORUMJUMP .= "\n<option value='".$forum_id."'>".$forum_name."</option>";
	}
}
$FORUMJUMP .= "</select> <input class='button' type='submit' name='fjsubmit' value='".LAN_03."' /></p></form>";
$TOPLINK = "<a href='".e_SELF."?".$_SERVER['QUERY_STRING']."#top'>".LAN_02."</a>";

$forum_view_start = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_START);
$forum_view_end = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_END);
if($pref['forum_enclose']){ $ns -> tablerender($pref['forum_title'], $forum_view_start.$forum_view_forum.$forum_view_end); }else{ echo $forum_view_start.$forum_view_forum.$forum_view_end; }
require_once(FOOTERF);


function parse_thread($row){
	global $sql2, $sql3, $FORUM_VIEW_FORUM, $gen, $aj, $pref, $forum_id;
	extract($row);
	$VIEWS = $thread_views;
	$REPLIES = $sql2 -> db_Count("forum_t", "(*)", " WHERE thread_parent='$thread_id' ");
	if($REPLIES){
		$sql2 -> db_Select("forum_t", "*", "thread_parent='$thread_id' ORDER BY thread_datestamp DESC");
		list($null, $null, $null, $null, $r_datestamp, $null, $r_user) = $sql2 -> db_Fetch();
		$r_id = substr($r_user, 0, strpos($r_user, "."));
		$r_name = substr($r_user, (strpos($r_user, ".")+1));

		if(strstr($r_name, chr(1))){ $tmp = explode(chr(1), $r_name); $r_name = $tmp[0]; }

		$r_datestamp = $gen->convert_date($r_datestamp, "forum");
		if(!$r_id ? $LASTPOST = $r_name."<br />".$r_datestamp : $LASTPOST = "<a href='".e_BASE."user.php?id.".$r_id."'>".$r_name."</a><br />".$r_datestamp);

	}else{
		$REPLIES = LAN_317;
		$LASTPOST = " - ";
	}

	$post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
	$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));
	if(strstr($post_author_name, chr(1))){ $tmp = explode(chr(1), $post_author_name); $post_author_name = $tmp[0]; }
	

	$newflag = FALSE;
	if(USER){
		if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
			$newflag = TRUE;
		}else if($sql3 -> db_SELECT("forum_t", "*", "thread_parent='$thread_id' AND thread_datestamp > '".USERLV."' ")){
			while(list($nthread_id) = $sql3 -> db_Fetch()){
				if(!ereg("\.".$nthread_id."\.", USERVIEWED)){
					$newflag = TRUE;
				}
			}
		}
	}

	$THREADDATE = $gen->convert_date($thread_datestamp, "forum");
	$ICON = ($newflag ? IMAGE_new_small : IMAGE_nonew_small);
	if($replies >= $pref['forum_popular'] && $replies != "None"){
		$ICON = ($newflag ? IMAGE_new_popular : IMAGE_nonew_popular);
	}

	if($thread_s == 1){
		$ICON = ($thread_active ? IMAGE_sticky : IMAGE_stickyclosed);
	}else if($thread_s == 2){
		$ICON = IMAGE_announce;
	}else if(!$thread_active){
		$ICON = IMAGE_closed_small;
	}

	$thread_name = strip_tags($aj -> tpa($thread_name));
	$result = preg_split("/\]/", $thread_name);
	$THREADNAME = ($result[1] ? $result[0]."] <a href='".e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_id."'>".ereg_replace("\[.*\]", "", $thread_name)."</a>" : "<a href='".e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_id."'>".$thread_name."</a>");

	

	$pages = ceil($REPLIES/$pref['forum_postspage']);
	if($pages>1){
		$PAGES = LAN_316." [ ";
		for($a=0; $a<=($pages-1); $a++){
			$PAGES .= "-<a href='".e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_id.".".($a*$pref['forum_postspage'])."'>".($a+1)."</a>";
		}
		$PAGES .= " ]";
	}

	if(MODERATOR){
		
		if($thread_s == 1){
			$ADMIN_ICONS = "<a href='".e_ADMIN."forum_conf.php?unstick.".$forum_id.".".$thread_id."'>".IMAGE_admin_unstick."</a> ";
			if($thread_active){
				$ADMIN_ICONS .= "<a href='".e_ADMIN."forum_conf.php?close.".$forum_id.".".$thread_id."'>".IMAGE_admin_lock."</a> ";
			}else{
				$ADMIN_ICONS .= "<a href='".e_ADMIN."forum_conf.php?open.".$forum_id.".".$thread_id."'>".IMAGE_admin_unlock."</a> ";
			}
		}else{
			$ADMIN_ICONS = "<a href='".e_ADMIN."forum_conf.php?stick.".$forum_id.".".$thread_id."'>".IMAGE_admin_stick."</a> ";
			if($thread_active){
				$ADMIN_ICONS .= "<a href='".e_ADMIN."forum_conf.php?close.".$forum_id.".".$thread_id."'>".IMAGE_admin_lock."</a> ";
			}else{
				$ADMIN_ICONS .= "<a href='".e_ADMIN."forum_conf.php?open.".$forum_id.".".$thread_id."'>".IMAGE_admin_unlock."</a> ";
			}
		}
		$ADMIN_ICONS .= "<a href='".e_ADMIN."forum_conf.php?move.".$forum_id.".".$thread_id."'>".IMAGE_admin_move."</a></div>";
	}
			
	$text .= "</td>
	<td style='vertical-align:top; text-align:center; width:20%' class='forumheader3'>".$THREADDATE."<br />";
	$POSTER = (!$post_author_id ? $post_author_name :  "<a href='".e_BASE."user.php?id.".$post_author_id."'>".$post_author_name."</a>");
	return(preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_FORUM));
}


function forumjump(){
	global $sql;
	$sql -> db_Select("forum", "*", "forum_parent !=0 AND forum_class!='255' ");
	$FORUMJUMP = "<form method='post' action='".e_SELF."'><p>".LAN_403.": <select name='forumjump' class='tbox'>";
	while($row = $sql -> db_Fetch()){
		extract($row);
		if(check_class($forum_class)){
			$FORUMJUMP .= "\n<option value='".$forum_id."'>".$forum_name."</option>";
		}
	}
	$FORUMJUMP .= "</select> <input class='button' type='submit' name='fjsubmit' value='".LAN_03."' />&nbsp;&nbsp;&nbsp;&nbsp;<a href='".e_SELF."?".$_SERVER['QUERY_STRING']."#top'>".LAN_02."</a></p></form>";
	return ($FORUMJUMP);
}

?>