<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/forum_viewtopic.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-27 19:52:49 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
	
require_once('../../class2.php');
	
@include_once e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_viewtopic.php';
@include_once e_PLUGIN.'forum/languages/English/lan_forum_viewtopic.php';
@require_once(e_PLUGIN.'forum/forum_class.php');
if (file_exists(THEME.'forum_design.php')) {
	@include_once(THEME.'forum_design.php');
}
	
$forum = new e107forum;
	
if (IsSet($_POST['fjsubmit'])) {
	header("location:".e_PLUGIN."forum/forum_viewforum.php?".$_POST['forumjump']);
	exit;
}
$highlight_search = FALSE;
if (IsSet($_POST['highlight_search'])) {
	$highlight_search = TRUE;
}
	
if (!e_QUERY) {
	//No paramters given, redirect to forum home
	header("Location:".e_PLUGIN."/forum/forum.php");
	exit;
} else {
	$tmp = explode(".", e_QUERY);
	$thread_id = $tmp[0];
	$from = $tmp[1];
	$action = $tmp[2];
	if (!$from) {
		$from = 0;
	}
	if (!$thread_id || !is_numeric($thread_id)) {
		header("Location:".e_PLUGIN."forum/forum.php");
		exit;
	}
}
	
require_once(e_HANDLER.'textparse/basic.php');
require_once(e_PLUGIN.'forum/forum_shortcodes.php');
$etp = new e107_basicparse;
	
if ($action == "track" && USER) {
	$forum->track($thread_id);
	header("location:".e_SELF."?{$thread_id}.{$from}");
	exit;
}
	
if ($action == "untrack" && USER) {
	$forum->untrack($thread_id);
	header("location:".e_SELF."?{$thread_id}.{$from}");
	exit;
}
	
if ($action == "next") {
	$next = $forum->thread_getnext($thread_id, $from);
	if ($next) {
		header("location:".e_SELF."?{$next}");
		exit;
	} else {
		require_once(HEADERF);
		$ns->tablerender('', 'No next thread');
		require_once(FOOTERF);
		exit;
	}
}
	
if ($action == "prev") {
	$prev = $forum->thread_getprev($thread_id, $from);
	if ($prev) {
		header("location:".e_SELF."?{$prev}");
		exit;
	} else {
		require_once(HEADERF);
		$ns->tablerender('', 'No previous thread');
		require_once(FOOTERF);
		exit;
	}
	 
}
	
if ($action == "report") {
	$thread_info = $forum->thread_get_postinfo($thread_id, TRUE);
	if (IsSet($_POST['report_thread'])) {
		$user = $_POST['user'];
		$report_thread_id = $_POST['report_thread_id'];
		$report_thread_name = $_POST['report_thread_name'];
		if ($pref['reported_post_email']) {
			require_once(e_HANDLER."mail.php");
			$report_add = $tp->tpDB($_POST['report_add']);
			$report = LAN_422.SITENAME." : ".(substr(SITEURL, -1) == "/" ? SITEURL : SITEURL."/")."forum_viewtopic.php?".$forum_id.".".$report_thread_id."#".$thread_id."\n".LAN_425.$user."\n".$report_add;
			$subject = LAN_421." ".SITENAME;
			sendemail(SITEADMINEMAIL, $subject, $report);
		}
		$reported_post = $forum_id."^".$report_thread_id."^".$thread_id."^".$report_thread_name."^".$user;
		$sql->db_Insert("tmp", "'reported_post', '".time()."', '$reported_post' ");
		define("e_PAGETITLE", LAN_01." / ".LAN_428);
		require_once(HEADERF);
		$text = LAN_424."<br /><a href='forum_viewtopic.php?".$report_thread_id."#".$thread_id."'>".LAN_429."</a";
		$ns->tablerender(LAN_414, $text);
	} else {
		$number = $thread_id;
		$report_thread_id = $thread_id;
		$thread_name = $thread_info['head']['thread_name'];
		define("e_PAGETITLE", LAN_01." / ".LAN_426." ".$thread_name);
		require_once(HEADERF);
		$user = (USER ? USERNAME : LAN_194);
		$text = "<form action='".e_PLUGIN."forum/forum_viewtopic.php?".e_QUERY."' method='post'> <table style='width:100%'>
			<tr>
			<td  style='width:50%' >
			".LAN_415.": ".$thread_name." <a href='".e_PLUGIN."forum/forum_viewtopic.php?".$thread_info['head']['thread_id']."#".$number."'><span class='smalltext'>".LAN_420." </span>
			</a>
			</td>
			<td style='text-align:center;width:50%'>
			</td>
			</tr>
			<tr>
			<td>".LAN_417."<br />".LAN_418."
			</td>
			<td style='text-align:center;'>
			<textarea cols='40' rows='10' class='tbox' name='report_add'></textarea>
			</td>
			</tr>
			<tr>
			<td colspan='2' style='text-align:center;'><br />
			<input type ='hidden' name='user' value='$user' />
			<input type ='hidden' name='report_thread_id' value='$report_thread_id' />
			<input type ='hidden' name='report_thread_name' value='$thread_name' />
			<input class='button' type='submit' name='report_thread' value='".LAN_419."' />
			</td>
			</tr>
			</table>";
		$ns->tablerender(LAN_414, $text);
	}
	require_once(FOOTERF);
	exit;
}
$pm_installed = ($pref['pm_title'] ? TRUE : FALSE);
	
$replies = $forum->thread_count($thread_id)-1;
if ($from == 'last') {
	$pref['forum_postspage'] = ($pref['forum_postspage'] ? $pref['forum_postspage'] : 10);
	$pages = ceil($replies/$pref['forum_postspage']);
	$from = ($pages-1) * $pref['forum_postspage'];
}
	
$gen = new convert;
$thread_info = $forum->thread_get($thread_id, $from, $pref['forum_postspage']);
$forum_info = $forum->forum_get($thread_info['head']['thread_forum_id']);
	
	
if (!check_class($forum_info['forum_class'])) {
	header("Location:".e_PLUGIN."forum/forum.php");
	exit;
}
	
$forum->thread_incview($thread_id);
	
define("e_PAGETITLE", LAN_01." / ".$forum_info['forum_name']." / ".$thread_info['head']['thread_name']);
define("MODERATOR", (preg_match("/".preg_quote(ADMINNAME)."/", $forum_info['forum_moderators']) && getperms('A') ? TRUE : FALSE));
	
$message = '';
if (MODERATOR) {
	if ($_POST) {
		require_once(e_PLUGIN.'forum/forum_mod.php');
		$message = forum_thread_moderate($_POST);
	}
}
	
require_once(HEADERF);
require_once(e_HANDLER."level_handler.php");
if ($message) {
	$ns->tablerender("", $message);
}
	
If (IsSet($_POST['pollvote'])) {
	$sql->db_Select("poll", "poll_active, poll_ip", "poll_id='".$_POST['pollid']."' ");
	$row = $sql->db_Fetch();
	extract($row);
	$user_id = ($poll_active == 9 ? getip() : USERID);
	if (!preg_match("/".$user_id."\^/", $poll_ip)) {
		if ($_POST['votea']) {
			$num = "poll_votes_".$_POST['votea'];
			$sql->db_Update("poll", "$num=$num+1, poll_ip='".$poll_ip.$user_id."^' WHERE poll_id='".$_POST['pollid']."' ");
		}
	}
}
	
if (eregi("\[".LAN_430."\]", $thread_info['head']['thread_name'])) {
	if ($sql->db_Select("poll", "*", "poll_datestamp='{$thread_info['head']['thread_id']}'")) {
		list($poll_id, $poll_datestamp, $poll_end_datestamp, $poll_admin_id, $poll_title, $poll_option[0], $poll_option[1], $poll_option[2], $poll_option[3], $poll_option[4], $poll_option[5], $poll_option[6], $poll_option[7], $poll_option[8], $poll_option[9], $votes[0], $votes[1], $votes[2], $votes[3], $votes[4], $votes[5], $votes[6], $votes[7], $votes[8], $votes[9], $poll_ip, $poll_active) = $sql->db_Fetch();
		$user_id = ($poll_active == 9 ? getip() : USERID);
		if (preg_match("/".$user_id."\^/", $poll_ip)) {
			$mode = "voted";
		} elseif($poll_active == 2 && !USER) {
			$mode = "disallowed";
		} else {
			$mode = "notvoted";
		}
		require_once(e_HANDLER."poll_class.php");
		$poll = new poll;
		$pollstr = "<div class='spacer'>".$poll->render_poll($poll_id, $poll_title, $poll_option, $votes, $mode, "forum")."</div>";
	}
}
//Load forum templates
	
if (!$FORUMSTART) {
	if (file_exists(THEME."forum_viewtopic_template.php")) {
		require_once(THEME."forum_viewtopic_template.php");
	} else {
		require_once(e_PLUGIN."forum/templates/forum_viewtopic_template.php");
	}
}
	
	
// get info for main thread -------------------------------------------------------------------------------------------------------------------------------------------------------------------
	
$BREADCRUMB = "<a class='forumlink' href='".e_BASE."index.php'>".SITENAME."</a>-><a class='forumlink' href='".e_PLUGIN."forum/forum.php'>".LAN_01."</a>-><a class='forumlink' href='forum_viewforum.php?".$forum_info['forum_id']."'>".$forum_info['forum_name']."</a>->".$thread_info['head']['thread_name']."XXX";
	
$BACKLINK = "<a class='forumlink' href='".e_BASE."index.php'>".SITENAME."</a>-><a class='forumlink' href='".e_PLUGIN."forum/forum.php'>".LAN_01."</a>-><a class='forumlink' href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_info['forum_id']."'>".$forum_info['forum_name']."</a>";
$THREADNAME = $thread_info['head']['thread_name'];
$NEXTPREV = "&lt;&lt; <a href='".e_SELF."?{$thread_id}.{$forum_info['forum_id']}.prev'>".LAN_389."</a>";
$NEXTPREV .= " | ";
$NEXTPREV .= "<a href='".e_SELF."?{$thread_id}.{$forum_info['forum_id']}.next'>".LAN_390."</a> &gt;&gt;";
	
if ($pref['forum_track'] && USER) {
	$TRACK = (preg_match("/-".$thread_id."-/", USERREALM) ? "<span class='smalltext'><a href='".e_SELF."?".$forum_id.".".$thread_id.".0."."untrack'>".LAN_392."</a></span>" : "<span class='smalltext'><a href='".e_SELF."?".$thread_id.".0."."track'>".LAN_391."</a></span>");
}
	
$MODERATORS = LAN_321.$forum_info['forum_moderators'];
$THREADSTATUS = (!$thread_info['head']['thread_active'] ? LAN_66 : "");
	
	
$pref['forum_postspage'] = ($pref['forum_postspage'] ? $pref['forum_postspage'] : 10);
$pages = ceil($replies/$pref['forum_postspage']);
if ($pages > 1) {
	$currentpage = ($from/$pref['forum_postspage'])+1;
	$prevpage = $from - $pref['forum_postspage'];
	$nextpage = $from + $pref['forum_postspage'];
	$GOTOPAGES = LAN_02." ".($currentpage > 1 ? " <a href='".e_SELF."?".$thread_id.".".$prevpage."'>".LAN_04."</a> " : "");
	for ($a = 0; $a <= ($pages-1); $a++) {
		$GOTOPAGES .= (($a+1) == $currentpage ? "-".($a+1) : "-<a href='".e_SELF."?".$thread_id.".".($a * $pref['forum_postspage'])."'>".($a+1)."</a>");
	}
	$GOTOPAGES .= ($nextpage < $replies ? " <a href='".e_SELF."?".$thread_id.".".$nextpage."'>".LAN_05."</a> " : "");
}
	
if ((ANON || USER) && ($forum_info['forum_class'] != e_UC_READONLY || MODERATOR)) {
	if ($thread_info['head']['thread_active']) {
		$BUTTONS = "<a href='".e_PLUGIN."forum/forum_post.php?rp.".e_QUERY."'>".IMAGE_reply."</a>";
	}
	$BUTTONS .= "<a href='".e_PLUGIN."forum/forum_post.php?nt.".$forum_info['forum_id']."'>".IMAGE_newthread."</a>";
}
	
$FORUMJUMP = forumjump();
	
$forstr = preg_replace("/\{(.*?)\}/e", '$\1', $FORUMSTART);
	
unset($forrep);
if (!$FORUMREPLYSTYLE) $FORUMREPLYSTYLE = $FORUMTHREADSTYLE;
	
for($i = 0; $i < count($thread_info)-1; $i++) {
	unset($post_info);
	$post_info = $thread_info[$i];
	if (!$post_info['thread_user']) {
		// guest
		$tmp = explode(chr(1), $post_info['thread_anon']);
		$ip = $tmp[1];
		$host = gethostbyaddr($ip);
		$post_info['iphost'] = "<div class='smalltext' style='text-align:right'>IP: <a href='".e_ADMIN."userinfo.php?$ip'>$ip ( $host )</a></div>";
		$post_info['anon'] = TRUE;
	} else {
		$post_info['anon'] = FALSE;
	}
	 
	// unset($newflag);
	// if (USER) {
	//  if ($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))) {
	//   $NEWFLAG = IMAGE_new." ";
	//   $u_new .= ".".$thread_id.".";
	//  }
	// }
	$forrep .= $tp->parseTemplate($FORUMREPLYSTYLE, FALSE, $forum_shortcodes);
}
	
	
if ((ANON || USER) && ($forum_class != e_UC_READONLY || MODERATOR) && $thread_info['head']['thread_active'] ) {
	if (!$forum_quickreply) {
		$QUICKREPLY = "<form action='".e_PLUGIN."forum/forum_post.php?rp.".e_QUERY."' method='post'>\n<p>\n".LAN_393.":<br /><textarea cols='60' rows='4' class='tbox' name='post'></textarea><br /><input type='submit' name='fpreview' value='".LAN_394."' class='button' /> &nbsp;\n<input type='submit' name='reply' value='".LAN_395."' class='button' />\n<input type='hidden' name='thread_id' value='$thread_parent' />\n</p>\n</form>";
	} else {
		$QUICKREPLY = $forum_quickreply;
	}
}
	
$forend = preg_replace("/\{(.*?)\}/e", '$\1', $FORUMEND);
$forumstring = $pollstr.$forstr.$forrep.$forend;
	
if ($thread_info['head']['thread_lastpost'] > USERLV && (!ereg("\.{$thread_info['head']['thread_id']}\.", USERVIEWED))) {
	$tst = $forum->thread_markasread($thread_info['head']['thread_id']);
}
	
if ($pref['forum_enclose']) {
	$ns->tablerender(LAN_01, $forumstring);
} else {
	echo $forumstring;
}
	
	
//$u_new = USERVIEWED . $u_new;
//if ($u_new != "") {
// $sql->db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' ");
//}
// end -------------------------------------------------------------------------------------------------------------------------------------------------------------------
	
echo "<script type=\"text/javascript\">
	function confirm_(mode, forum_id, thread_id, thread) {
	if (mode == 'thread') {
	return confirm(\"".$etp->unentity(LAN_409)."\");
	} else {
	return confirm(\"".$etp->unentity(LAN_410)." [ ".$etp->unentity(LAN_411)."\" + thread + \" ]\");
	}
	}
	</script>";
require_once(FOOTERF);
	
function showmodoptions() {
	// return 'work in progress';
	global $thread_id;
	global $thread_info;
	global $forum_info;
	global $post_info;
	if ($post_info['thread_parent'] == FALSE) {
		$type = 'thread';
	} else {
		$type = 'reply';
	}
	 
	$forum_id = $forum_info['forum_id'];
	$ret = "
		<form method='post' action='".e_PLUGIN."forum/forum_viewforum.php?{$forum_id}' id='frmMod_{$forum_id}_{$thread_id}'>
		<div>
		<a href='".e_PLUGIN."forum/forum_post.php?edit.{$post_info['thread_id']}.{$from}'>".IMAGE_admin_edit."</a>
		<input type='image' ".IMAGE_admin_delete." name='delete_{$post_info['thread_id']}' value='thread_action' onclick=\"return confirm_('$type', $forum_id, $thread_id, '{$post_info['user_name']}')\" />
		";
	if ($type == 'thread') {
		$ret .= "<a href='".e_PLUGIN."forum/forum_conf.php?move.".$forum_id.".".$thread_id."'>".IMAGE_admin_move2."</a>";
	}
	$ret .= "
		</div>
		</form>";
	return $ret;
}
function forumjump() {
	global $sql;
	$sql->db_Select("forum", "*", "forum_parent !=0 AND forum_class!='255' ");
	$text .= "<form method='post' action='".e_SELF."'><p>".LAN_65.": <select name='forumjump' class='tbox'>";
	while ($row = $sql->db_Fetch()) {
		extract($row);
		if (check_class($forum_class)) {
			$text .= "\n<option value='".$forum_id."'>".$forum_name."</option>";
		}
	}
	$text .= "</select> <input class='button' type='submit' name='fjsubmit' value='".LAN_03."' />&nbsp;&nbsp;&nbsp;&nbsp;<a href='".e_SELF."?".$_SERVER['QUERY_STRING']."#top'>".LAN_10."</a></p></form>";
	return $text;
}
	
function rpg($user_join, $user_forums) {
	global $FORUMTHREADSTYLE;
	if (strpos($FORUMTHREADSTYLE, '{RPG}') == FALSE) {
		return '';
	}
	// rpg mod by Ikari ( kilokan1@yahoo.it | http://artemanga.altervista.org )
	 
	$lvl_post_mp_cost = 2.5;
	$lvl_mp_regen_per_day = 4;
	$lvl_avg_ppd = 5;
	$lvl_bonus_redux = 5;
	$lvl_user_days = max(1, round((time() - $user_join ) / 86400 ));
	$lvl_ppd = $user_forums / $lvl_user_days;
	if ($user_forums < 1) {
		$lvl_level = 0;
	} else {
		$lvl_level = floor(pow(log10($user_forums ), 3 ) ) + 1;
	}
	if ($lvl_level < 1) {
		$lvl_hp = "0 / 0";
		$lvl_hp_percent = 0;
	} else {
		$lvl_max_hp = floor((pow($lvl_level, (1/4) ) ) * (pow(10, pow($lvl_level+2, (1/3) ) ) ) / (1.5) );
		 
		if ($lvl_ppd >= $lvl_avg_ppd) {
			$lvl_hp_percent = floor((.5 + (($lvl_ppd - $lvl_avg_ppd) / ($lvl_bonus_redux * 2)) ) * 100);
		} else {
			$lvl_hp_percent = floor($lvl_ppd / ($lvl_avg_ppd / 50) );
		}
		if ($lvl_hp_percent > 100) {
			$lvl_max_hp += floor(($lvl_hp_percent - 100) * pi() );
			$lvl_hp_percent = 100;
		} else {
			$lvl_hp_percent = max(0, $lvl_hp_percent);
		}
		$lvl_cur_hp = floor($lvl_max_hp * ($lvl_hp_percent / 100) );
		$lvl_cur_hp = max(0, $lvl_cur_hp);
		$lvl_cur_hp = min($lvl_max_hp, $lvl_cur_hp);
		$lvl_hp = $lvl_cur_hp . '/' . $lvl_max_hp;
	}
	if ($lvl_level < 1) {
		$lvl_mp = '0 / 0';
		$lvl_mp_percent = 0;
	} else {
		$lvl_max_mp = floor((pow($lvl_level, (1/4) ) ) * (pow(10, pow($lvl_level+2, (1/3) ) ) ) / (pi()) );
		$lvl_mp_cost = $user_forums * $lvl_post_mp_cost;
		$lvl_mp_regen = max(1, $lvl_user_days * $lvl_mp_regen_per_day);
		$lvl_cur_mp = floor($lvl_max_mp - $lvl_mp_cost + $lvl_mp_regen);
		$lvl_cur_mp = max(0, $lvl_cur_mp);
		$lvl_cur_mp = min($lvl_max_mp, $lvl_cur_mp);
		$lvl_mp = $lvl_cur_mp . '/' . $lvl_max_mp;
		$lvl_mp_percent = floor($lvl_cur_mp / $lvl_max_mp * 100 );
	}
	if ($lvl_level < 1) {
		$lvl_exp = "0 / 0";
		$lvl_exp_percent = 100;
	} else {
		$lvl_posts_for_next = floor(pow(10, pow($lvl_level, (1/3) ) ) );
		if ($lvl_level == 1) {
			$lvl_posts_for_this = max(1, floor(pow (10, (($lvl_level - 1) ) ) ) );
		} else {
			$lvl_posts_for_this = max(1, floor(pow (10, pow(($lvl_level - 1), (1/3) ) ) ) );
		}
		$lvl_exp = ($user_forums - $lvl_posts_for_this) . "/" . ($lvl_posts_for_next - $lvl_posts_for_this);
		$lvl_exp_percent = floor((($user_forums - $lvl_posts_for_this) / max(1, ($lvl_posts_for_next - $lvl_posts_for_this ) ) ) * 100);
	}
	$rpg_info .= "<div style='padding:2px;'>";
	$rpg_info .= "<b>Level = ".$lvl_level."</b><br />";
	$rpg_info .= "HP = ".$lvl_hp."<br /><img src='".THEME."images/bar.jpg' height='10' alt='' style='border:#345487 1px solid; width:".$lvl_hp_percent."'><br />";
	$rpg_info .= "EXP = ".$lvl_exp."<br /><img src='".THEME."images/bar.jpg' height='10' alt='' style='border:#345487 1px solid; width:".$lvl_exp_percent."'><br />";
	$rpg_info .= "MP = ".$lvl_mp."<br /><img src='".THEME."images/bar.jpg' height='10' alt='' style='border:#345487 1px solid; width:".$lvl_mp_percent."'><br />";
	$rpg_info .= "</div>";
	return $rpg_info;
}
	
?>