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

define("IMAGE_reply", (file_exists(THEME."forum/reply.png") ? "<img src='".THEME."forum/reply.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/reply.png' alt='' style='border:0' />"));
define("IMAGE_newthread", (file_exists(THEME."forum/newthread.png") ? "<img src='".THEME."forum/newthread.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/newthread.png' alt='' style='border:0' />"));
define("IMAGE_rank_moderator_image", ($pref['rank_moderator_image'] && file_exists(e_IMAGE."forum/".$pref['rank_moderator_image']) ? "<img src='".e_IMAGE."forum/".$pref['rank_moderator_image']."' alt='' />" : "<img src='".e_IMAGE."forum/moderator.png' alt='' />"));
define("IMAGE_rank_main_admin_image", ($pref['rank_main_admin_image'] && file_exists(e_IMAGE."forum/".$pref['rank_main_admin_image']) ? "<img src='".e_IMAGE."forum/".$pref['rank_main_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/main_admin.png' alt='' />"));
define("IMAGE_rank_admin_image", ($pref['rank_admin_image'] && file_exists(e_IMAGE."forum/".$pref['rank_admin_image']) ? "<img src='".e_IMAGE."forum/".$pref['rank_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/admin.png' alt='' />"));
define("IMAGE_profile", (file_exists(THEME."forum/profile.png") ? "<img src='".THEME."forum/profile.png' alt='".LAN_398."' style='border:0' />" : "<img src='".e_IMAGE."forum/profile.png' alt='".LAN_398."' style='border:0' />"));
define("IMAGE_email", (file_exists(THEME."forum/email.png") ? "<img src='".THEME."forum/email.png' alt='".LAN_397."' style='border:0' />" : "<img src='".e_IMAGE."forum/email.png' alt='".LAN_397."' style='border:0' />"));
define("IMAGE_pm", (file_exists(THEME."forum/pm.png") ? "<img src='".THEME."forum/pm.png' alt='".LAN_399."' style='border:0' />" : "<img src='".e_IMAGE."forum/pm.png' alt='".LAN_399."' style='border:0' />"));
define("IMAGE_website", (file_exists(THEME."forum/website.png") ? "<img src='".THEME."forum/website.png' alt='".LAN_396."' style='border:0' />" : "<img src='".e_IMAGE."forum/website.png' alt='".LAN_396."' style='border:0' />"));
define("IMAGE_edit", (file_exists(THEME."forum/edit.png") ? "<img src='".THEME."forum/edit.png' alt='".LAN_400."' style='border:0' />" : "<img src='".e_IMAGE."forum/edit.png' alt='".LAN_400."' style='border:0' />"));
define("IMAGE_quote", (file_exists(THEME."forum/quote.png") ? "<img src='".THEME."forum/quote.png' alt='".LAN_401."' style='border:0' />" : "<img src='".e_IMAGE."forum/quote.png' alt='".LAN_401."' style='border:0' />"));
define("IMAGE_admin_edit", (file_exists(THEME."forum/admin_edit.png") ? "<img src='".THEME."forum/admin_edit.png' alt='".LAN_406."' style='border:0' />" : "<img src='".e_IMAGE."forum/admin_edit.png' alt='".LAN_406."' style='border:0' />"));
define("IMAGE_admin_delete", (file_exists(THEME."forum/admin_delete.png") ? "<img src='".THEME."forum/admin_delete.png' alt='".LAN_407."' style='border:0' />" : "<img src='".e_IMAGE."forum/admin_delete.png' alt='".LAN_407."' style='border:0' />"));
define("IMAGE_admin_move", (file_exists(THEME."forum/admin_move.png") ? "<img src='".THEME."forum/admin_move.png' alt='".LAN_408."' style='border:0' />" : "<img src='".e_IMAGE."forum/admin_move.png' alt='".LAN_408."' style='border:0' />"));
define("IMAGE_new", (file_exists(THEME."forum/new.png") ? "<img src='".THEME."forum/new.png' alt='' style='float:left' />" : "<img src='".e_IMAGE."forum/new.png' alt='' style='float:left' />"));
define("IMAGE_post", (file_exists(THEME."forum/post.png") ? "<img src='".THEME."forum/post.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/post.png' alt='' style='border:0' />"));

if(!e_QUERY){
	header("Location:".e_BASE."forum.php");
	exit;
}else{
	$tmp = explode(".", e_QUERY);
	$forum_id = $tmp[0]; $thread_id = $tmp[1]; $from = $tmp[2]; $action = $tmp[3];
	if(!$from){ $from = 0; }
	if(!$thread_id || !is_numeric($thread_id)){
		header("Location:".e_BASE."forum.php");
		exit;
	}
}
$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."'  LIMIT 1 ");
	$row = $sql-> db_Fetch(); extract($row);
	if($thread_forum_id != $forum_id){
		header("Location:".e_BASE."forum.php");
		exit;
	}

if($action == "track" && USER){
	$sql -> db_Update("user", "user_realm='".USERREALM."-".$thread_id."-' WHERE user_id='".USERID."' ");
	header("location:".e_SELF."?".$forum_id.".".$thread_id);
	exit;
}

if($action == "untrack" && USER){
	$tmp = ereg_replace("-".$thread_id."-", "", USERREALM);
	$sql -> db_Update("user", "user_realm='$tmp' WHERE user_id='".USERID."' ");
	header("location:".e_SELF."?".$forum_id.".".$thread_id);
	exit;
}

$pm_installed = ($pref['pm_title'] ? TRUE : FALSE);

$gen = new convert;
$aj = new textparse();

$sql -> db_Update("forum_t", "thread_views=thread_views+1 WHERE thread_id='$thread_id' ");

$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
$row = $sql-> db_Fetch(); extract($row);

if(($forum_class && !check_class($forum_class)) || ($forum_class == 254 && !USER)){ header("Location:".e_BASE."forum.php"); exit;}

require_once(HEADERF);
require_once(e_HANDLER."level_handler.php");

$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ORDER BY thread_datestamp DESC ");
$row = $sql-> db_Fetch("no_strip"); extract($row);

define("MODERATOR", (preg_match("/".preg_quote(ADMINNAME)."/", $forum_moderators) && getperms("A") ? TRUE : FALSE));

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
		require_once(e_HANDLER."poll_class.php");
		$poll = new poll;
		$pollstr = "<div class='spacer'>".$poll -> render_poll($poll_id, $poll_title, $poll_option, $votes, $mode, "forum")."</div>";
	}
}




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

if(!$FORUMSTART){
	require_once(e_BASE.$THEMES_DIRECTORY."templates/forum_viewtopic_template.php");
}

// get info for main thread -------------------------------------------------------------------------------------------------------------------------------------------------------------------

$thread_name = $aj -> tpa($thread_name);

$BREADCRUMB = "<a class='forumlink' href='".e_BASE."index.php'>".SITENAME."</a> -> <a class='forumlink' href='forum.php'>".LAN_01."</a> -> <a class='forumlink' href='forum_viewforum.php?".$forum_id."'>".$forum_name."</a> -> ".$thread_name;

$BACKLINK = "<a class='forumlink' href='".e_BASE."index.php'>".SITENAME."</a> -> <a class='forumlink' href='forum.php'>".LAN_01."</a> -> <a class='forumlink' href='forum_viewforum.php?".$forum_id."'>".$forum_name."</a>";

$THREADNAME = $thread_name;

$NEXTPREV = ($prevthread ? "&lt;&lt; <a href='".e_SELF."?".$forum_id.".".$prevthread."'>".LAN_389."</a> " : LAN_404." ")."|".($nextthread ? " <a href='".e_SELF."?".$forum_id.".".$nextthread."'>".LAN_390."</a> &gt;&gt;" : " ".LAN_405." ");

if($pref['forum_track'] && USER){
	$TRACK = (preg_match("/-".$thread_id."-/", USERREALM) ? "<span class='smalltext'><a href='".e_SELF."?".$forum_id.".".$thread_id.".0."."untrack'>".LAN_392."</a></span>" : "<span class='smalltext'><a href='".e_SELF."?".$forum_id.".".$thread_id.".0."."track'>".LAN_391."</a></span>");
}

$MODERATORS = LAN_321.$forum_moderators;
$THREADSTATUS = (!$thread_active ? LAN_66 : "");

$replies = $sql -> db_Count("forum_t", "(*)", "WHERE thread_parent='".$thread_id."'");
$pref['forum_postspage'] = ($pref['forum_postspage'] ? $pref['forum_postspage'] : 10);
$pages = ceil($replies/$pref['forum_postspage']);
if($pages>1){
	$currentpage = ($from/$pref['forum_postspage'])+1;
	$prevpage = $from - $pref['forum_postspage'];
	$nextpage = $from + $pref['forum_postspage'];
	$GOTOPAGES = LAN_02." ".($currentpage > 1 ? " <a href='forum_viewtopic.php?".$forum_id.".".$thread_id.".".$prevpage."'>".LAN_04."</a> " : "");
	for($a=0; $a<=($pages-1); $a++){
		$GOTOPAGES .= (($a+1) == $currentpage ? "-".($a+1) : "-<a href='forum_viewtopic.php?".$forum_id.".".$thread_id.".".($a*$pref['forum_postspage'])."'>".($a+1)."</a>");
	}
	$GOTOPAGES .= ($nextpage < $replies ? " <a href='forum_viewtopic.php?".$forum_id.".".$thread_id.".".$nextpage."'>".LAN_05."</a> " : "");
}

if((ANON || USER) && ($forum_class != e_UC_READONLY || MODERATOR)){
	if($thread_active){
		$BUTTONS = "<a href='forum_post.php?rp.".e_QUERY."'>".IMAGE_reply."</a>";
	}
	$BUTTONS .= "<a href='forum_post.php?nt.".$forum_id."'>".IMAGE_newthread."</a>";
}

$post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));
if(strstr($post_author_name, chr(1))){
	$tmp = explode(chr(1), $post_author_name);
	$post_author_name = $tmp[0];
	$ip = $tmp[1];
	$host = gethostbyaddr($ip);
	$iphost = "<div class='smalltext' style='text-align:right'>IP: <a href='".e_ADMIN."userinfo.php?$ip'>$ip ( $host )</a></div>";
}

if(!$post_author_id || !$sql -> db_Select("user", "*", "user_id='".$post_author_id."' ")){	// guest
	$POSTER = "<b>".$post_author_name."</b>";
	$AVATAR = "<br /><span class='smallblacktext'>".LAN_194."</span>";
}else{	// regged member - get member info
	unset($iphost);
	$row = $sql -> db_Fetch(); extract($row);
	$POSTER = "<a href='user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>";
	if($user_image){
		require_once(e_HANDLER."avatar_handler.php");
		$AVATAR = "<div class='spacer'><img src='".avatar($user_image)."' alt='' /></div><br />";
	}else{
		unset($AVATAR);
	}
	
	$JOINED = ($user_perms == "0" ? "" : LAN_06." ".$gen->convert_date($user_join, "forum")."<br />");
	$LOCATION = ($user_location ? LAN_07.": ".$user_location : "");
	$WEBSITE = ($user_homepage ? LAN_08.": ".$user_homepage : "");
	$POSTS = LAN_67." ".$user_forums."<br />";
	$VISITS = LAN_09.": ".$user_visits;

	$ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);
	$MEMBERID = $ldata[0];
	$LEVEL = $ldata[1];

	$SIGNATURE = ($user_signature ? "<br /><hr style='width:15%; text-align:left'><span class='smalltext'>".$aj -> tpa($user_signature) : "");
	$PROFILEIMG = "<a href='user.php?id.".$user_id."'>".IMAGE_profile."</a>";
	$EMAILIMG = (!$user_hideemail ? "<a href='mailto:$user_email'>".IMAGE_email."</a>" : "");

	$PRIVMESSAGE = ($pm_installed && $post_author_id && (!USERCLASS || check_class($pref['pm_userclass'])) ? "<a href='".e_PLUGIN."pm_menu/pm.php?send.$post_author_id'>".IMAGE_pm."</a>" : "");

	$WEBSITEIMG = ($user_homepage && $user_homepage != "http://" ? "<a href='$user_homepage'>".IMAGE_website."</a>" : "");
	$RPG = rpg($user_join, $user_forums);
	
}



$EDITIMG = ($post_author_name == USERNAME && $thread_active ? "<a href='forum_post.php?edit.".$forum_id.".".$thread_id."'>".IMAGE_edit."</a> " : "");
if($thread_active){
	$QUOTEIMG = "<a href='forum_post.php?quote.".$forum_id.".".$thread_id."'>".IMAGE_quote."</a>";
}else{
	$T_ACTIVE = TRUE;
}
if(MODERATOR){
	$MODOPTIONS = "<a href='forum_post.php?edit.".$forum_id.".".$thread_id."'>".IMAGE_admin_edit."</a>\n<a style='cursor:pointer; cursor:hand' onClick=\"confirm_('thread', $forum_id, $thread_id, '')\"'>".IMAGE_admin_delete."</a>\n<a href='".e_ADMIN."forum_conf.php?move.".$forum_id.".".$thread_id."'>".IMAGE_admin_move."</a>";
}

unset($newflag);
if(USER){
	if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
		$NEWFLAG = IMAGE_new." ";
		$u_new = ".".$thread_id.".";
	}
}

$THREADDATESTAMP = "<a id='anchor_$thread_id'>".IMAGE_post."</a> ".$gen->convert_date($thread_datestamp, "forum");
$thread_thread = wrap($thread_thread);
$POST = $aj -> tpa($thread_thread, "forum");
if(ADMIN && $iphost){ $POST .= "<br />".$iphost; }
$TOP = "<a href='".e_SELF."?".e_QUERY."#top'>".LAN_10."</a>";
$FORUMJUMP = forumjump();

$forstr = preg_replace("/\{(.*?)\}/e", '$\1', $FORUMSTART);
$forthr = preg_replace("/\{(.*?)\}/e", '$\1', $FORUMTHREADSTYLE);

// end thread parse -------------------------------------------------------------------------------------------------------------------------------------------------------------------
// begine reply parse -------------------------------------------------------------------------------------------------------------------------------------------------------------------

unset($forrep);
if(!$FORUMREPLYSTYLE) $FORUMREPLYSTYLE = $FORUMTHREADSTYLE;

if($sql -> db_Select("forum_t", "*", "thread_parent='".$thread_id."' ORDER BY thread_datestamp ASC LIMIT ".$from.", ".$pref['forum_postspage'])){
	$sql2 = new db;
	while($row = $sql-> db_Fetch()){
		extract($row);
		$post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
		$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));
		if(strstr($post_author_name, chr(1))){
			$tmp = explode(chr(1), $post_author_name);
			$post_author_name = $tmp[0];
			$ip = $tmp[1];
			$host = gethostbyaddr($ip);
			$iphost = "<div class='smalltext' style='text-align:right'>IP: <a href='".e_ADMIN."userinfo.php?$ip'>$ip ( $host )</a></div>";
		}
		//$post_author_count = $sql -> db_Count("forum_t", "(*)", " WHERE thread_user='$thread_user' OR thread_user='$post_author_name' ");

		if(!$post_author_id || !$sql2 -> db_Select("user", "*", "user_id='".$post_author_id."' ")){	// guest
			$POSTER = "<b>".$post_author_name."</b>";
			$AVATAR = "<br /><span class='smallblacktext'>".LAN_194."</span>";
			unset($JOINED, $LOCATION, $WEBSITE, $POSTS, $VISITS, $MEMBERID, $SIGNATURE, $RPG, $LEVEL, $PRIVMESSAGE, $PROFILEIMG, $EMAILIMG, $WEBSITEIMG);
		}else{	// regged member - get member info
			unset($iphost);
			
			$row = $sql2 -> db_Fetch(); extract($row);
			$POSTER = "<a href='user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>";
			if($user_image){
				require_once(e_HANDLER."avatar_handler.php");
				$AVATAR = "<div class='spacer'><img src='".avatar($user_image)."' alt='' /></div><br />";
			}else{
				unset($AVATAR);
			}
			
			$JOINED = ($user_perms == "0" ? "" : LAN_06.": ".$gen->convert_date($user_join, "forum")."<br />");
			$LOCATION = ($user_location ? LAN_07.": ".$user_location : "");
			$WEBSITE = ($user_homepage ? LAN_08.": ".$user_homepage : "");
			$POSTS = LAN_67." ".$user_forums."<br />";
			$VISITS = LAN_09.": ".$user_visits;
			
			$ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);
			$MEMBERID = $ldata[0];
			$LEVEL = $ldata[1];

			$SIGNATURE = ($user_signature ? "<br /><hr style='width:15%; text-align:left'><span class='smalltext'>".$aj -> tpa($user_signature) : "");
			$PROFILEIMG = "<a href='user.php?id.".$user_id."'>".IMAGE_profile."</a>";
			$EMAILIMG = (!$user_hideemail ? "<a href='mailto:$user_email'>".IMAGE_email."</a>" : "");

			$PRIVMESSAGE = ($pm_installed && $post_author_id && (!USERCLASS || check_class($pref['pm_userclass'])) ? "<a href='".e_PLUGIN."pm_menu/pm.php?send.$post_author_id'>".IMAGE_pm."</a>" : "");

			$WEBSITEIMG = ($user_homepage && $user_homepage != "http://" ? "<a href='$user_homepage'>".IMAGE_website."</a>" : "");
			$RPG = rpg($user_join, $user_forums);
			
		}

		$EDITIMG = ($post_author_name == USERNAME && $thread_active ? "<a href='forum_post.php?edit.".$forum_id.".".$thread_id."'>".IMAGE_edit."</a> " : "");
		if(!$T_ACTIVE){
			$QUOTEIMG = "<a href='forum_post.php?quote.".$forum_id.".".$thread_id."'>".IMAGE_quote."</a>";
		}
		if(MODERATOR){
			$MODOPTIONS = "<a href='forum_post.php?edit.".$forum_id.".".$thread_id."'>".IMAGE_admin_edit."</a>\n<a style='cursor:pointer; cursor:hand' onClick=\"confirm_('reply', $forum_id, $thread_id, '$post_author_name')\"'>".IMAGE_admin_delete."</a>\n<a href='".e_ADMIN."forum_conf.php?move.".$forum_id.".".$thread_id."'>".IMAGE_admin_move."</a>";
		}

		unset($newflag);
		if(USER){
			if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
				$NEWFLAG = IMAGE_new." ";
				$u_new .= ".".$thread_id.".";
			}
		}

		$THREADDATESTAMP = "<a id='anchor_$thread_id'>".IMAGE_post."</a> ".$gen->convert_date($thread_datestamp, "forum");
		$thread_thread = wrap($thread_thread);
		$POST = $aj -> tpa($thread_thread, "forum");
		if(ADMIN && $iphost){ $POST .= "<br />".$iphost; }

		$forrep .= preg_replace("/\{(.*?)\}/e", '$\1', $FORUMREPLYSTYLE);
	}
}

if((ANON || USER) && ($forum_class != e_UC_READONLY || MODERATOR) && !$T_ACTIVE ){
	$QUICKREPLY = "<form action='".e_BASE."forum_post.php?rp.".e_QUERY."' method='post'>\n<p>\n".LAN_393.":<br /><textarea cols='60' rows='4' class='tbox' name='post'></textarea><br /><input type='submit' name='fpreview' value='".LAN_394."' class='button' /> &nbsp;\n<input type='submit' name='reply' value='".LAN_395."' class='button' />\n<input type='hidden' name='thread_id' value='$thread_parent' />\n</p>\n</form>";
}

$forend = preg_replace("/\{(.*?)\}/e", '$\1', $FORUMEND);
$forumstring = (!$from ? $pollstr.$forstr.$forthr.$forrep.$forend : $pollstr.$forstr.$forrep.$forend);
if($pref['forum_enclose']){ $ns -> tablerender(LAN_01, $forumstring); }else{ echo $forumstring; }

$u_new = USERVIEWED . $u_new;
if($u_new != ""){ $sql -> db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' "); }

// end -------------------------------------------------------------------------------------------------------------------------------------------------------------------

function forumjump(){
	global $sql;
	$sql -> db_Select("forum", "*", "forum_parent !=0 AND forum_class!='255' ");
	$text .= "<form method='post' action='".e_SELF."'><p>".LAN_65.": <select name='forumjump' class='tbox'>";
	while($row = $sql -> db_Fetch()){
		extract($row);
		if(($forum_class && check_class($forum_class)) || ($forum_class == 254 && USER) || !$forum_class){
			$text .= "\n<option value='".$forum_id."'>".$forum_name."</option>";
		}
	}
	$text .= "</select> <input class='button' type='submit' name='fjsubmit' value='".LAN_03."' />&nbsp;&nbsp;&nbsp;&nbsp;<a href='".e_SELF."?".$_SERVER['QUERY_STRING']."#top'>".LAN_10."</a></p></form>";
	return $text;
}

function wrap($data){
	$wrapcount = 100;
	$message_array = explode(" ", $data);
	for($i=0; $i<=(count($message_array)-1); $i++){
		if(strlen($message_array[$i]) > $wrapcount){
			if(substr($message_array[$i], 0, 7) == "http://"){
				$url = str_replace("http://", "", $message_array[$i]);  
				$url = explode("/", $url);  
				$url = $url[0];
				$message_array[$i] = "<a href='".$message_array[$i]."'>[".$url."]</a>";
			}else{
				$message_array[$i] = preg_replace("/([^\s]{".$wrapcount."})/", "$1<br />", $message_array[$i]);
			}
		}
	}
	$data = implode(" ",$message_array);
	return $data;
}

function rpg($user_join, $user_forums){
	// rpg mod by Ikari ( kilokan1@yahoo.it | http://artemanga.altervista.org )

	$lvl_post_mp_cost = 2.5;
	$lvl_mp_regen_per_day = 4;
	$lvl_avg_ppd = 5;
	$lvl_bonus_redux = 5;
	$lvl_user_days = max(1, round( ( time() - $user_join ) / 86400 ));
	$lvl_ppd = $user_forums / $lvl_user_days;
	if($user_forums < 1){
		$lvl_level = 0;
	}else{
		$lvl_level = floor( pow( log10( $user_forums ), 3 ) ) + 1;
	}
	if($lvl_level < 1){
		$lvl_hp = "0 / 0";
		$lvl_hp_percent = 0;
	}else{
		$lvl_max_hp = floor( (pow( $lvl_level, (1/4) ) ) * (pow( 10, pow( $lvl_level+2, (1/3) ) ) ) / (1.5) );
					
		if($lvl_ppd >= $lvl_avg_ppd){
			$lvl_hp_percent = floor( (.5 + (($lvl_ppd - $lvl_avg_ppd) / ($lvl_bonus_redux * 2)) ) * 100);
		}else{
			$lvl_hp_percent = floor( $lvl_ppd / ($lvl_avg_ppd / 50) );
		}
		if($lvl_hp_percent > 100){
			$lvl_max_hp += floor( ($lvl_hp_percent - 100) * pi() );
			$lvl_hp_percent = 100;
		}else{
			$lvl_hp_percent = max(0, $lvl_hp_percent);
		}
		$lvl_cur_hp = floor($lvl_max_hp * ($lvl_hp_percent / 100) );
		$lvl_cur_hp = max(0, $lvl_cur_hp);
		$lvl_cur_hp = min($lvl_max_hp, $lvl_cur_hp);
		$lvl_hp = $lvl_cur_hp . '/' . $lvl_max_hp;
	}
	if($lvl_level < 1){
		$lvl_mp = '0 / 0';
		$lvl_mp_percent = 0;
	}else{
		$lvl_max_mp = floor( (pow( $lvl_level, (1/4) ) ) * (pow( 10, pow( $lvl_level+2, (1/3) ) ) ) / (pi()) );
		$lvl_mp_cost = $user_forums * $lvl_post_mp_cost;
		$lvl_mp_regen = max(1, $lvl_user_days * $lvl_mp_regen_per_day);
		$lvl_cur_mp = floor($lvl_max_mp - $lvl_mp_cost + $lvl_mp_regen);
		$lvl_cur_mp = max(0, $lvl_cur_mp);
		$lvl_cur_mp = min($lvl_max_mp, $lvl_cur_mp);
		$lvl_mp = $lvl_cur_mp . '/' . $lvl_max_mp;
		$lvl_mp_percent = floor($lvl_cur_mp / $lvl_max_mp * 100 );
	}
	if($lvl_level < 1){
		$lvl_exp = "0 / 0";
		$lvl_exp_percent = 100;
	}else{
		$lvl_posts_for_next = floor( pow( 10, pow( $lvl_level, (1/3) ) ) );
		if ($lvl_level == 1){ 
			$lvl_posts_for_this = max(1, floor(pow (10, ( ($lvl_level - 1) ) ) ) ); 
		}else{ 
			$lvl_posts_for_this = max(1, floor(pow (10, pow( ($lvl_level - 1), (1/3) ) ) ) ); 
		}
		$lvl_exp = ($user_forums - $lvl_posts_for_this) . "/" . ($lvl_posts_for_next - $lvl_posts_for_this);
		$lvl_exp_percent = floor( ( ($user_forums - $lvl_posts_for_this) / max( 1, ($lvl_posts_for_next - $lvl_posts_for_this ) ) ) * 100);
	}
	$rpg_info .= "<div style='padding:2px;'>";
	$rpg_info .= "<b>Level = ".$lvl_level."</b><br />";
	$rpg_info .= "HP = ".$lvl_hp."<br /><img src='".THEME."images/bar.jpg' height='10' alt='' style='border:#345487 1px solid; width:".$lvl_hp_percent."'><br />";
	$rpg_info .= "EXP = ".$lvl_exp."<br /><img src='".THEME."images/bar.jpg' height='10' alt='' style='border:#345487 1px solid; width:".$lvl_exp_percent."'><br />";
	$rpg_info .= "MP = ".$lvl_mp."<br /><img src='".THEME."images/bar.jpg' height='10' alt='' style='border:#345487 1px solid; width:".$lvl_mp_percent."'><br />";
	$rpg_info .= "</div>";
	return $rpg_info;
}

echo "<script type=\"text/javascript\">
function confirm_(mode, forum_id, thread_id, thread){
	if(mode == 'thread'){
		var x=confirm(\"".LAN_409."\");
	}else{
		var x=confirm(\"".LAN_410." [ ".LAN_411."\" + thread + \" ]\");
	}
	if(x){
		window.location='".e_ADMIN."forum_conf.php?confirm.' + forum_id + '.' + thread_id;
	}
}
</script>";

require_once(FOOTERF);

?>