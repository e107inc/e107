<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/forum.php
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

if(strstr(e_QUERY, "untrack")){
	$tmp1 = explode(".", e_QUERY);
	$tmp = str_replace("-".$tmp1[1]."-", "", USERREALM);
	$sql -> db_Update("user", "user_realm='$tmp' WHERE user_id='".USERID."' ");
	header("location:".e_SELF."?track");
	exit;
}

if(e_QUERY == "mark.all.as.read"){
	$sql -> db_Select("forum_t", "thread_id", "thread_datestamp > '".USERLV."' ");
	while($row = $sql -> db_Fetch()){
		extract($row);
		$u_new .= ".".$thread_id.".";
	}
	$u_new .= USERVIEWED;
	$sql -> db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' ");
	header("location:".e_SELF);
	exit;
}

if(strstr(e_QUERY, "mfar")){
	$tmp = explode(".", e_QUERY);
	$forum_id = $tmp[1];
	$sql -> db_Select("forum_t", "thread_id", "thread_forum_id='$forum_id' AND thread_datestamp > '".USERLV."' ");
	while($row = $sql -> db_Fetch()){
		extract($row);
		$u_new .= ".".$thread_id.".";
	}
	$u_new .= USERVIEWED;
	$sql -> db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' ");
	header("location:".e_SELF);
	exit;
}



define("IMAGE_e", (file_exists(THEME."forum/e.png") ? "<img src='".THEME."forum/e.png' alt='' />" : "<img src='".e_IMAGE."forum/e.png' alt='' />"));
define("IMAGE_nonew_small", (file_exists(THEME."forum/nonew_small.png") ? "<img src='".THEME."forum/nonew_small.png' alt='' />" : "<img src='".e_IMAGE."forum/nonew_small.png' alt='' />"));
define("IMAGE_new_small", (file_exists(THEME."forum/new_small.png") ? "<img src='".THEME."forum/new_small.png' alt='' />" : "<img src='".e_IMAGE."forum/new_small.png' alt='' />"));
define("IMAGE_closed_small", (file_exists(THEME."forum/closed_small.png") ? "<img src='".THEME."forum/closed_small.png' alt='' />" : "<img src='".e_IMAGE."forum/closed_small.png' alt='' />"));
define("IMAGE_new", (file_exists(THEME."forum/new.png") ? "<img src='".THEME."forum/new.png' alt='".LAN_199."' style='border:0' />" : "<img src='".e_IMAGE."forum/new.png' alt='".LAN_199."' style='border:0' />"));
define("IMAGE_nonew", (file_exists(THEME."forum/nonew.png") ? "<img src='".THEME."forum/nonew.png' alt='' />" : "<img src='".e_IMAGE."forum/nonew.png' alt='' />"));
define("IMAGE_post", (file_exists(THEME."forum/post2.png") ? "<img src='".THEME."forum/post2.png' alt='' style='border:0; vertical-align:bottom' />" : "<img src='".e_IMAGE."forum/post2.png' alt='' style='border:0; vertical-align:bottom' />"));

$gen = new convert;

$FORUMTITLE = LAN_46;
$THREADTITLE = LAN_47;
$REPLYTITLE = LAN_48;
$LASTPOSTITLE = LAN_49;
$INFOTITLE = LAN_191;
$LOGO = IMAGE_e;
$NEWTHREADTITLE = LAN_424;
$POSTEDTITLE = LAN_423;
$NEWIMAGE = IMAGE_new_small;
$TRACKTITLE = LAN_397;

$total_topics = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent='0' ");
$total_replies = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent!='0' ");
$total_members = $sql -> db_Count("user");
$newest_member = $sql -> db_Select("user", "*", "ORDER BY user_join DESC LIMIT 0,1", $mode="no_where");
list($nuser_id, $nuser_name)  = $sql -> db_Fetch();
$member_users = $sql -> db_Select("online", "*", "online_location REGEXP('forum.php') AND online_user_id!='0' ");
$guest_users = $sql -> db_Select("online", "*", "online_location REGEXP('forum.php') AND online_user_id='0' ");
$users = $member_users+$guest_users;

$ICONKEY = "
<table style='width:100%'>\n<tr>
<td style='width:2%'>".IMAGE_new_small."</td>
<td style='width:10%'><span class='smallblacktext'>".LAN_79."</span></td>
<td style='width:2%'>".IMAGE_nonew_small."</td>
<td style='width:10%'><span class='smallblacktext'>".LAN_80."</span></td>
<td style='width:2%'>".IMAGE_closed_small."</td>
<td style='width:10%'><span class='smallblacktext'>".LAN_394."</span></td>
</tr>\n</table>\n";

$SEARCH = "
<form method='post' action='search.php'>
<p>
<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />
<input class='button' type='submit' name='searchsubmit' value='".LAN_180."' />
</p>
</form>\n";

$PERMS = 
  (USER == TRUE || ANON == TRUE ? LAN_204." - ".LAN_206." - ".LAN_208 : LAN_205." - ".LAN_207." - ".LAN_209);

if(USER == TRUE){
	$total_new_threads = $sql -> db_Count("forum_t", "(*)", "WHERE thread_datestamp>'".USERLV."' ");
	if(USERVIEWED != ""){
		$tmp = explode("..", USERVIEWED);
		$total_read_threads = count($tmp);
	}else{
		$total_read_threads = 0;
	}

	$INFO = 
	LAN_30." ".USERNAME."<br />";
	$sql -> db_Select("user", "*",  "user_name='".USERNAME."' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$lastvisit_datestamp = $gen->convert_date($user_lastvisit, "long");
	$datestamp = $gen->convert_date(time(), "long");
	if(!$total_new_threads){
		$INFO .= LAN_31;
	}else if($total_new_threads == 1){
		$INFO .= LAN_32;
	}else{
		$INFO .= LAN_33." ".$total_new_threads." ".LAN_34." ";
	}
	$INFO .= LAN_35;
	if($total_new_threads == $total_read_threads && $total_new_threads !=0 && $total_read_threads >= $total_new_threads){
		$INFO .= LAN_198;
		$allread = TRUE;
	}else if($total_read_threads != 0){
		$INFO .= " (".LAN_196.$total_read_threads.LAN_197.")";
	}

	$INFO .= "<br />
	".LAN_36." ".$lastvisit_datestamp."<br />
	".LAN_37." ".$datestamp.LAN_38.$pref['timezone'];
}else{
	$INFO .= "";
	if(ANON == TRUE){
		$INFO .= LAN_410."<br />".LAN_44;
	}else if(USER == FALSE){
		$INFO .= LAN_410."<br />".LAN_45;
	}
}

if(USER && $allread != TRUE && $total_new_threads && $total_new_threads >= $total_read_threads){
	$INFO .= "<br /><a href='".e_SELF."?mark.all.as.read'>".LAN_199."</a>".(e_QUERY != "new" ? ", <a href='".e_SELF."?new'>".LAN_421."</a>" : "");
}

if(USERREALM && USER && e_QUERY != "track"){
	$INFO .= "<br /><a href='".e_SELF."?track'>".LAN_393."</a>";
}

$FORUMINFO .= LAN_192.($total_topics+$total_replies)." ".LAN_404." ($total_topics ".($total_topics == 1 ? LAN_411 : LAN_413).", $total_replies ".($total_replies == 1 ? LAN_412 : LAN_414).").<br />".$users." ".($users == 1 ? LAN_415 : LAN_416)." (".$member_users." ".($member_users==1 ? LAN_417 : LAN_419).", ".$guest_users." ".($guest_users == 1 ? LAN_418 : LAN_420).")<br />


".LAN_42.$total_members."<br />".LAN_41."<a href='".e_BASE."user.php?id.".$nuser_id."'>".$nuser_name."</a>.\n";

if(!$FORUM_MAIN_START){
	$FORUM_MAIN_START = "<div style='text-align:center'>\n<table style='width:95%' class='fborder' border=1>\n<tr>\n<td colspan='2' style='width:60%; text-align:center' class='fcaption'>{FORUMTITLE}</td>\n<td style='width:10%; text-align:center' class='fcaption'>{THREADTITLE}</td>\n<td style='width:10%; text-align:center' class='fcaption'>{REPLYTITLE}</td>\n<td style='width:20%; text-align:center' class='fcaption'>{LASTPOSTITLE}</td>\n</tr>";
}
if(!$FORUM_MAIN_PARENT){
	$FORUM_MAIN_PARENT = " <tr>\n<td colspan='5' class='forumheader'>{PARENTNAME} {PARENTSTATUS}</td>\n</tr>";
}
if(!$FORUM_MAIN_FORUM){
	$FORUM_MAIN_FORUM = "<tr>\n<td style='width:5%; text-align:center' class='forumheader2'>{NEWFLAG}</td>\n<td style='width:55%' class='forumheader2'>{FORUMNAME}<br /><span class='smallblacktext'>{FORUMDESCRIPTION}</span></td>\n<td style='width:10%; text-align:center' class='forumheader3'>{THREADS}</td>\n<td style='width:10%; text-align:center' class='forumheader3'>{REPLIES}</td>\n<td style='width:20%; text-align:center' class='forumheader3'><span class='smallblacktext'>{LASTPOST}</span></td>\n</tr>";
}
if(!$FORUM_MAIN_END){
	$FORUM_MAIN_END = "</table>\n<div class='spacer'>\n<table style='width:95%' class='fborder'>\n<tr>\n<td colspan='2' style='width:60%' class='fcaption'>{INFOTITLE}</td>\n</tr>\n<tr>\n<td rowspan='2' style='width:5%; text-align:center' class='forumheader3'>{LOGO}</td>\n<td style='width:auto' class='forumheader3'>{INFO}</td>\n</tr>\n<tr>\n<td style='width:95%' class='forumheader3'>{FORUMINFO}</td>\n</tr>\n</table>\n</div>\n<div class='spacer'>\n<table class='fborder' style='width:95%'>\n<tr>\n<td class='forumheader3' style='text-align:center; width:33%'>{ICONKEY}</td>\n<td style='text-align:center; width:33%' class='forumheader3'>{SEARCH}</td>\n<td style='width:33%; text-align:center; vertical-align:middle' class='forumheader3'><span class='smallblacktext'>{PERMS}</span>\n</td>\n</tr>\n</table>\n</div>\n";
}

if(!$FORUM_NEWPOSTS_START){
	$FORUM_NEWPOSTS_START = "<div style='text-align:center'>\n<div class='spacer'>\n<table style='width:95%' class='fborder'>\n<tr>\n<td style='width:3%' class='fcaption'>&nbsp;</td>\n<td style='width:60%' class='fcaption'>{NEWTHREADTITLE}</td>\n<td style='width:27%; text-align:center' class='fcaption'>{POSTEDTITLE}</td>\n</tr>";
}

if(!$FORUM_NEWPOSTS_MAIN){
	$FORUM_NEWPOSTS_MAIN .= "<tr>\n<td style='width:3%' class='forumheader3'>{NEWIMAGE}</td>\n<td style='width:60%' class='forumheader3'>{NEWSPOSTNAME}</td>\n<td style='width:27%; text-align:center' class='forumheader3'>{STARTERTITLE}</td>\n</tr>";
}

if(!$FORUM_NEWPOSTS_END){
	$FORUM_NEWPOSTS_END .= "</table></div></div>";
}

if(!$FORUM_TRACK_START){
	$FORUM_TRACK_START = "<div style='text-align:center'>\n<div class='spacer'>\n<table style='width:95%' class='fborder'>\n<tr>\n<td colspan='3' style='width:60%' class='fcaption'>{TRACKTITLE}</td>\n</tr>\n";

if(!$FORUM_TRACK_MAIN){
	$FORUM_TRACK_MAIN = "<tr>
	<td style='text-align:center; vertical-align:middle; width:6%'  class='forumheader3'>{NEWIMAGE}</td>
			<td style='vertical-align:middle; text-align:left; width:70%'  class='forumheader3'><span class='mediumtext'>{TRACKPOSTNAME}</span></td>
			<td style='vertical-align:middle; text-align:center; width:24%'  class='forumheader3'><span class='mediumtext'>{UNTRACK}</td>
			</tr>";
		}
	}

if(!$FORUM_TRACK_END){
	$FORUM_TRACK_END = "</table>\n</div>\n</div>";
}



require_once(HEADERF);
$sql2 = new db;

if(!$sql -> db_Select("forum", "*", "forum_parent='0' ORDER BY forum_order ASC")){
	$ns -> tablerender(PAGE_NAME, "<div style='text-align:center'>".LAN_51."</div>");
	require_once(FOOTERF);
	exit;
}

while($row = $sql-> db_Fetch()){
	$status = parse_parent($row);
	extract($row);
	$PARENTSTATUS = $status[0];
	if($status[1]){
		$PARENTNAME = $forum_name;
		$forum_string .= preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_MAIN_PARENT);
		$forums = $sql2 -> db_Select("forum", "*", "forum_parent='".$forum_id."' ORDER BY forum_order ASC ");
		if(!$forums && $status[1]){
			$text .= "<td colspan='5' style='text-align:center' class='forumheader3'>".LAN_52."</td>";
		}else if($status[1]){
			while($row = $sql2-> db_Fetch()){
				extract($row);
				if($forum_class == e_UC_ADMIN && ADMIN){
					$forum_string .= parse_forum($row, LAN_406);
				}else if($forum_class == e_UC_MEMBER && USER){
					$forum_string .= parse_forum($row, LAN_407);
				}else if($forum_class == e_UC_READONLY){
					$forum_string .= parse_forum($row, LAN_408);
				}else if($forum_class && check_class($forum_class)){
					$forum_string .= parse_forum($row, LAN_409);
				}else if(!$forum_class){
					$forum_string .= parse_forum($row);
				}
			}
			if($FORUM_MAIN_PARENT_END){
				$forum_string .= preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_MAIN_PARENT_END);
			}
		}
	}
}

function parse_parent($row){
	extract($row);
	if($forum_class == e_UC_NOBODY){
		$status[0] = "{ ".LAN_398." )";
		$status[1] = FALSE;
	}else if($forum_class == e_UC_MEMBER && !USER){
		$status[1] = FALSE;
	}else if($forum_class == e_UC_MEMBER && USER){
		$status[0] = "( ".LAN_401." )";
		$status[1] = TRUE;
	}else if($forum_class == e_UC_READONLY){
		$status[0] = "( ".LAN_405." )";
		$status[1] = TRUE;
	}else if($forum_class){
		if(check_class($forum_class)){
			$status[0] = "( ".LAN_399." )";
			$status[1] = TRUE;
		}else{
			$status[1] = FALSE;
		}
	}else{
		$status[0] = "";
		$status[1] = TRUE;
	}
	return ($status);
}

function parse_forum($row, $restricted_string=""){
	global $FORUM_MAIN_FORUM, $gen;
	extract($row);
	$sql = new db;
	if(USER){
		if($sql -> db_Select("forum_t", "*", "thread_forum_id='$forum_id' AND thread_datestamp > '".USERLV."' ")){
			while(list($nthread_id) = $sql -> db_Fetch()){
				if(!ereg("\.".$nthread_id."\.", USERVIEWED)){ $newflag = TRUE; }
			}
		}
	}
	$NEWFLAG = ($newflag ? "<a href='".e_SELF."?mfar.$forum_id'>".IMAGE_new."</a></td>" : IMAGE_nonew);
	$FORUMNAME = "<a href='".e_BASE."forum_viewforum.php?$forum_id'>$forum_name</a>";
	$FORUMDESCRIPTION = $forum_description.($restricted_string ? "<br /><span class='smalltext'><i>$restricted_string</i></span>" : "");;
	$THREADS = $forum_threads;
	$REPLIES = $forum_replies;
	if($forum_lastpost){
		$sql -> db_Select("forum_t", "*", "thread_forum_id='$forum_id' ORDER BY thread_datestamp DESC LIMIT 0,1");
		$row = $sql -> db_Fetch();
		extract($row);
		$lastpost_author_id = substr($forum_lastpost, 0, strpos($forum_lastpost, "."));
		$lastpost_author_name = substr($forum_lastpost, (strpos($forum_lastpost, ".")+1));
		$lastpost_datestamp = substr($lastpost_author_name, (strrpos($lastpost_author_name, ".")+1));
		$lastpost_author_name = str_replace(".".$lastpost_datestamp, "", $lastpost_author_name);
		$lastpost_datestamp = $gen->convert_date($lastpost_datestamp, "forum");
		$LASTPOST = $lastpost_datestamp."<br />".($lastpost_author_id ? "<a href='".e_BASE."user.php?id.$lastpost_author_id'>$lastpost_author_name</a> " : $lastpost_author_name).
		($thread_parent ?  " <a href='".e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_parent."'>".IMAGE_post."</a>" : " <a href='".e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_id."'>".IMAGE_post."</a>")."
		</span></td>";
	}else{
		$LASTPOST = "-";
	}
	return(preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_MAIN_FORUM));
}


if(e_QUERY == "track"){
	$tmp = explode("-", USERREALM);
	foreach($tmp as $key => $value){
		if($value){
			$sql -> db_Select("forum_t", "*", "thread_id='".$value."' ");
			$row = $sql -> db_Fetch(); extract($row);
			$NEWIMAGE = IMAGE_nonew_small;
			if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
				$NEWIMAGE = IMAGE_new_small;
			}else if($sql2 -> db_SELECT("forum_t", "*", "thread_parent='$thread_id' AND thread_datestamp > '".USERLV."' ")){
				while(list($nthread_id) = $sql2 -> db_Fetch()){
					if(!ereg("\.".$nthread_id."\.", USERVIEWED)){
						$NEWIMAGE = IMAGE_new_small;
					}
				}
			}
			$sql -> db_Select("forum_t", "*",  "thread_id='".$tmp[$key]."' ORDER BY thread_s DESC, thread_lastpost DESC, thread_datestamp DESC");
			$row = $sql -> db_Fetch(); extract($row);
			$result = preg_split("/\]/", $thread_name);
			$TRACKPOSTNAME = ($result[1] ? $result[0]."] <a href='".e_BASE."forum_viewtopic.php?".$thread_forum_id.".".$thread_id."'>".ereg_replace("\[.*\]", "", $thread_name)."</a>" : "<a href='".e_BASE."forum_viewtopic.php?".$thread_forum_id.".".$thread_id."'>".$thread_name."</a>");
			$UNTRACK = "<a href='".e_SELF."?untrack.".$thread_id."'>".LAN_392."</a>";
			$forum_trackstring .= preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_TRACK_MAIN);
		}
	}
	
	$forum_track_start = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_TRACK_START);
	$forum_track_end = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_TRACK_END);

	if($pref['forum_enclose']){ $ns -> tablerender($pref['forum_title'], $forum_track_start.$forum_trackstring.$forum_track_end); }else{ echo $forum_track_start.$forum_trackstring.$forum_track_end; }
}

if(e_QUERY == "new"){
	$sql3 = new db;
	if($forum_posts = $sql -> db_Select("forum_t", "*", "thread_datestamp>".USERLV." ORDER BY thread_datestamp DESC LIMIT 0,50")){
		while($row = $sql -> db_Fetch()){
			extract($row);
			$sql2 -> db_Select("forum", "*", "forum_id=$thread_forum_id");
			$row = $sql2 -> db_Fetch(); extract($row);

			if(check_class($forum_class)){
				if(!ereg("\.".$thread_id."\.", USERVIEWED)){
					$np = TRUE;
					$author_id = substr($thread_user , 0, strpos($thread_user , "."));
					$author_name = substr($thread_user , (strpos($thread_user , ".")+1));
					$datestamp = $gen->convert_date($thread_datestamp, "forum");
					$STARTERTITLE = "<a href='".e_BASE."user.php?id.$author_id'>$author_name</a><br />".$datestamp;
					$iid = $thread_id;

					if($thread_parent){
						$ttemp = $thread_id;
						$sql2 -> db_Select("forum_t", "*", "thread_id=$thread_parent ");
						$row = $sql2 -> db_Fetch(); extract($row);
						$replies = $sql3 -> db_Count("forum_t", "(*)", "WHERE thread_parent='".$thread_id."'");
						$pref['forum_postspage'] = ($pref['forum_postspage'] ? $pref['forum_postspage'] : 10);
						$pages = ((ceil($replies/$pref['forum_postspage']) -1) * $pref['forum_postspage']);		
						$NEWSPOSTNAME = "<a href='".e_BASE."forum_viewtopic.php?$thread_forum_id.$thread_id".($pages ? ".$pages" : "")."#".$iid."'>Re: $thread_name</a>";
					}else{
						$NEWSPOSTNAME = "<a href='".e_BASE."forum_viewtopic.php?$thread_forum_id.$thread_id'>$thread_name</a>";
					}
					$forum_newstring .= preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_NEWPOSTS_MAIN);
				}
			}
		}
		if(!$np){
			$NEWSPOSTNAME = LAN_198;
			$forum_newstring = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_NEWPOSTS_MAIN);
		}

		

		$forum_new_start = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_NEWPOSTS_START);
		$forum_new_end = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_NEWPOSTS_END);

		if($pref['forum_enclose']){ $ns -> tablerender($pref['forum_title'], $forum_new_start.$forum_newstring.$forum_new_end); }else{ echo $forum_new_start.$forum_newstring.$forum_new_end; }
	}
}



$forum_main_start = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_MAIN_START);
$forum_main_end = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_MAIN_END);
if($pref['forum_enclose']){ $ns -> tablerender($pref['forum_title'], $forum_main_start.$forum_string.$forum_main_end); }else{ echo $forum_main_start.$forum_string.$forum_main_end; }
require_once(FOOTERF);

?>







