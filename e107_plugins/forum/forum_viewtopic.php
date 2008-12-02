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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_viewtopic.php,v $
|     $Revision: 1.5 $
|     $Date: 2008-12-02 21:34:18 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

require_once('../../class2.php');


if (isset($_POST['fjsubmit']))
{
	header('location:'.$e107->url->getUrl('forum', 'forum', array('func' => 'view', 'id'=>$_POST['forumjump'])));
	exit;
}
$highlight_search = isset($_POST['highlight_search']);

if (!e_QUERY)
{
	//No paramters given, redirect to forum home
	header('Location:'.$e107->url->getUrl('forum', 'forum', array('func' => 'main')));
	exit;
}

include_lan(e_PLUGIN.'forum/languages/English/lan_forum_viewtopic.php');
include_once(e_PLUGIN.'forum/forum_class.php');

$forum = new e107forum;

if(isset($_REQUEST['f']))
{
	processFunction();
}

$threadId = (int)varset($_GET['id']);
$perPage = (varset($_REQUEST['perpage']) ? (int)$_REQUEST['perpage'] : $pref['forum_postspage']);
$page = (varset($_REQUEST['p']) ? (int)$_REQUEST['p'] : 0);

//If threadId doesn't exist, or not given, redirect to main forum page
if (!$threadId || !$threadInfo = $forum->threadGet($threadId))
{
	header('Location:'.$e107->url->getUrl('forum', 'forum', array('func' => 'main')));
	exit;
}

//If not permitted to view forum, redirect to main forum page
if(!$forum->checkPerm($threadInfo['thread_forum_id'], 'view'))
{
	header('Location:'.$e107->url->getUrl('forum', 'forum', array('func' => 'main')));
	exit;
}

require_once(e_PLUGIN.'forum/forum_shortcodes.php');

$pm_installed = plugInstalled('pm');


//Only increment thread views if not being viewed by thread starter
if(!USER || USER != $threadInfo['thread_user'])
{
	$forum->threadIncview($threadId);
}

//print_a($postList);
//if(intval($thread_info['head']['thread_forum_id']) == 0)
//{
//	require_once(HEADERF);
//	$ns->tablerender(LAN_01, FORLAN_104, array('forum_viewtopic', '104'));
//	require_once(FOOTERF);
//	exit;
//}

//$forum_info = $forum->forum_get($thread_info['head']['thread_forum_id']);
//
//if (!check_class($forum_info['forum_class']) || !check_class($forum_info['parent_class'])) {
//	header("Location:".e_PLUGIN."forum/forum.php");
//	exit;
//}

define('e_PAGETITLE', LAN_01.' / '.$e107->tp->toHTML($threadInfo['forum_name'], true, 'no_hook, emotes_off')." / ".$tp->toHTML($threadInfo['thread_name'], true, 'no_hook, emotes_off'));
//define("MODERATOR", (preg_match("/".preg_quote(ADMINNAME)."/", $forum_info['forum_moderators']) && getperms('A') ? TRUE : FALSE));
define('MODERATOR', ($forum_info['forum_moderators'] != '' && check_class($forum_info['forum_moderators'])));
$modArray = $forum->forum_getmods($threadInfo['forum_moderators']);
//var_dump($modArray);

$message = '';
if (MODERATOR && isset($_POST['mod']))
{
	require_once(e_PLUGIN.'forum/forum_mod.php');
	$message = forum_thread_moderate($_POST);
	$threadInfo = $forum->threadGet($threadId);
}

$postList = $forum->PostGet($threadId, $page * $perPage, $perPage);

var_dump($threadInfo);
require_once(HEADERF);
require_once(e_HANDLER.'level_handler.php');
$gen = new convert;
if ($message)
{
	$ns->tablerender('', $message, array('forum_viewtopic', 'msg'));
}

if(isset($threadInfo['thread_options']['poll']))
{
	if(!defined('POLLCLASS')) { include(e_PLUGIN.'poll/poll_class.php'); }
	$_qry = 'SELECT * FROM `#polls` WHERE `poll_datestamp` = '.$threadId;
	$poll = new poll;
	$pollstr = "<div class='spacer'>".$poll->render_poll($_qry, 'forum', 'query', true).'</div>';
}
//Load forum templates

if (file_exists(THEME.'forum_design.php')) { include_once(THEME.'forum_design.php'); }
if (!$FORUMSTART)
{
	if (file_exists(THEME.'forum_viewtopic_template.php'))
	{
		require_once(THEME.'forum_viewtopic_template.php');
	}
	elseif (file_exists(THEME.'forum_template.php'))
	{
		require_once(THEME.'forum_template.php');
	}
	else
	{
		require_once(e_PLUGIN.'forum/templates/forum_viewtopic_template.php');
	}
}

//$forum_info['forum_name'] = $e107->tp->toHTML($threadInfo['forum_name'], true, 'no_hook,emotes_off');

// get info for main thread -------------------------------------------------------------------------------------------------------------------------------------------------------------------

//$e107->url->getUrl('forum', 'thread', array('func' => 'next', 'id' => $threadId))
$forum->set_crumb(true); // Set $BREADCRUMB (and BACKLINK)
$THREADNAME = $e107->tp->toHTML($threadInfo['thread_name'], true, 'no_hook, emotes_off');
$NEXTPREV = "&lt;&lt; <a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'prev', 'id' => $threadId))."'>".LAN_389."</a>";
$NEXTPREV .= ' | ';
$NEXTPREV .= "<a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'next', 'id' => $threadId))."'>".LAN_390."</a> &gt;&gt;";

if ($pref['forum_track'] && USER)
{
	if($threadInfo['track_userid'])
	{
		$TRACK = "<span class='smalltext'><a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'untrack', 'id' => $threadId))."'>".LAN_392."</a></span>";
	}
	else
	{
		$TRACK = "<span class='smalltext'><a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'track', 'id' => $threadId))."'>".LAN_391."</a></span>";
	}
}

$MODERATORS = LAN_321.implode(', ', $modArray);

$THREADSTATUS = (!$threadInfo['thread_active'] ? LAN_66 : '');

$pages = ceil(($threadInfo['thread_total_replies']+1) / $perPage);

if ($pages > 1)
{
	$parms = ($threadInfo['thread_total_replies']+1).",{$perPage},{$topic_from},".e_SELF.'?'.$thread_id.'.[FROM],off';
	$GOTOPAGES = $tp->parseTemplate("{NEXTPREV={$parms}}");
}

$BUTTONS = '';
if($forum->checkPerm($threadInfo['thread_forum_id'], 'post') && $threadInfo['thread_active'])
{
	$BUTTONS .= "<a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'rp', 'id' => $threadId))."'>".IMAGE_reply."</a>";
}
if($forum->checkPerm($threadInfo['thread_forum_id'], 'thread'))
{
	$BUTTONS .= "<a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'nt', 'id' => $threadInfo['thread_forum_id']))."'>".IMAGE_newthread."</a>";
}

$POLL = $pollstr;

$FORUMJUMP = forumjump();

$forstr = preg_replace("/\{(.*?)\}/e", '$\1', $FORUMSTART);

unset($forrep);
if (!$FORUMREPLYSTYLE) $FORUMREPLYSTYLE = $FORUMTHREADSTYLE;
$alt = false;

$i=$page;
global $postInfo;
foreach($postList as $postInfo)
{
//	print_a($postInfo);
	$loop_uid = (int)$postInfo['post_user'];
	$i++;

	//TODO: Look into fixing this, to limit to a single query per pageload
	$e_hide_query = "SELECT post_id FROM `#forum_post` WHERE (`post_thread` = {$threadId} AND post_user= ".USERID.' LIMIT 1';
	$e_hide_hidden = FORLAN_HIDDEN;
	$e_hide_allowed = USER;

	if($i > 1)
	{
		$alt = !$alt;
		if(isset($FORUMREPLYSTYLE_ALT) && $alt)
		{
			$forrep .= $tp->parseTemplate($FORUMREPLYSTYLE_ALT, true, $forum_shortcodes)."\n";
		}
		else
		{
			$forrep .= $tp->parseTemplate($FORUMREPLYSTYLE, true, $forum_shortcodes)."\n";
		}
	}
	else
	{
		$forthr = $tp->parseTemplate($FORUMTHREADSTYLE, true, $forum_shortcodes)."\n";
	}
}
unset($loop_uid);

if($forum->checkPerm($threadInfo['thread_forum_id'], 'post') && $threadInfo['thread_active'])
{
	if (!$forum_quickreply)
	{
		$QUICKREPLY = "<form action='".$e107->url->getUrl('forum', 'thread', array('func' => 'rp', 'id' => $threadId))."' method='post'>\n<p>\n".LAN_393.":<br /><textarea cols='60' rows='4' class='tbox' name='post' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'></textarea><br /><input type='submit' name='fpreview' value='".LAN_394."' class='button' /> &nbsp;\n<input type='submit' name='reply' value='".LAN_395."' class='button' />\n<input type='hidden' name='thread_id' value='$thread_parent' />\n</p>\n</form>";
	}
	else
	{
		$QUICKREPLY = $forum_quickreply;
	}
}

$forend = preg_replace("/\{(.*?)\}/e", '$\1', $FORUMEND);
$forumstring = $forstr.$forthr.$forrep.$forend;


//If last post came after USERLV and not yet marked as read, mark the thread id as read
if ($threadInfo['thread_lastpost'] > USERLV && (strpos($currentUser['user_plugin_forum_viewed'], '.'.$threadId.'.') === false))
{
	$tst = $forum->threadMarkAsRead($threadId);
}

if ($pref['forum_enclose'])
{
	$ns->tablerender(LAN_01, $forumstring, array('forum_viewtopic', 'main'));
}
else
{
	echo $forumstring;
}


// end -------------------------------------------------------------------------------------------------------------------------------------------------------------------

echo "<script type=\"text/javascript\">
	function confirm_(mode, forum_id, thread_id, thread) {
	if (mode == 'thread') {
	return confirm(\"".$tp->toJS(LAN_409)."\");
	} else {
	return confirm(\"".$tp->toJS(LAN_410)." [ ".$tp->toJS(LAN_411)."\" + thread + \" ]\");
	}
	}
	</script>";
require_once(FOOTERF);

function showmodoptions()
{
	global $thread_id;
	global $thread_info;
	global $forum_info;
	global $post_info;
	$forum_id = $forum_info['forum_id'];
	if ($post_info['thread_parent'] == FALSE)
	{
		$type = 'thread';
		$ret = "<form method='post' action='".e_PLUGIN."forum/forum_viewforum.php?{$forum_id}' id='frmMod_{$forum_id}_{$post_info['thread_id']}'>";
	}
	else
	{
		$type = 'reply';
		$ret = "<form method='post' action='".e_SELF."?".e_QUERY."' id='frmMod_{$forum_id}_{$post_info['thread_id']}'>";
	}

	$ret .= "
		<div>
		<a href='".e_PLUGIN."forum/forum_post.php?edit.{$post_info['thread_id']}.{$topic_from}'>".IMAGE_admin_edit."</a>
		<input type='image' ".IMAGE_admin_delete." name='delete_{$post_info['thread_id']}' value='thread_action' onclick=\"return confirm_('{$type}', {$forum_id}, {$thread_id}, '{$post_info['user_name']}')\" />
		";
	if ($type == 'thread')
	{
		$ret .= "<a href='".e_PLUGIN."forum/forum_conf.php?move.{$thread_id}'>".IMAGE_admin_move2."</a>";
	}
	$ret .= "
		</div>
		</form>";
	return $ret;
}

function forumjump()
{

	global $forum;
	$jumpList = $forum->forum_get_allowed();
	$text = "<form method='post' action='".e_SELF."'><p>".LAN_65.": <select name='forumjump' class='tbox'>";
	foreach($jumpList as $key => $val)
	{
		$text .= "\n<option value='".$key."'>".$val."</option>";
	}
	$text .= "</select> <input class='button' type='submit' name='fjsubmit' value='".LAN_03."' />&nbsp;&nbsp;&nbsp;&nbsp;<a href='".e_SELF."?".e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".LAN_10."</a></p></form>";
	return $text;
}

function rpg($user_join, $user_forums)
{
	global $FORUMTHREADSTYLE;
	if (strpos($FORUMTHREADSTYLE, '{RPG}') == FALSE)
	{
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

	$bar_image = THEME."images/bar.jpg";
	if(!is_readable($bar_image))
	{
		$bar_image = e_PLUGIN."forum/images/".IMODE."/bar.jpg";
	}

	$rpg_info .= "<div style='padding:2px; white-space:nowrap'>";
	$rpg_info .= "<b>Level = ".$lvl_level."</b><br />";
	$rpg_info .= "HP = ".$lvl_hp."<br /><img src='{$bar_image}' alt='' style='border:#345487 1px solid; height:10px; width:".$lvl_hp_percent."%'><br />";
	$rpg_info .= "EXP = ".$lvl_exp."<br /><img src='{$bar_image}' alt='' style='border:#345487 1px solid; height:10px; width:".$lvl_exp_percent."%'><br />";
	$rpg_info .= "MP = ".$lvl_mp."<br /><img src='{$bar_image}' alt='' style='border:#345487 1px solid; height:10px; width:".$lvl_mp_percent."%'><br />";
	$rpg_info .= "</div>";
	return $rpg_info;
}

function processFunction()
{
if($topic_from === 'post')
{
	if($thread_id)
	{
		$post_num = $forum->thread_postnum($thread_id);
		$pages = ceil(($post_num['post_num']+1)/$pref['forum_postspage']);
		$topic_from = ($pages-1) * $pref['forum_postspage'];
		if($post_num['parent'] != $thread_id)
		{
			header("location: ".e_SELF."?{$post_num['parent']}.{$topic_from}#post_{$thread_id}");
			exit;
		}
	}
	else
	{
		header('Location:'.$e107->url->getUrl('forum', 'forum', array('func' => 'main')));
		exit;
	}
}

if ($action == 'track' && USER)
{
	$forum->track($thread_id);
	header("location:".e_SELF."?{$thread_id}.{$topic_from}");
	exit;
}

if ($action == 'untrack' && USER)
{
	$forum->untrack($thread_id);
	header("location:".e_SELF."?{$thread_id}.{$topic_from}");
	exit;
}

if ($action == 'next')
{
	$next = $forum->thread_getnext($thread_id, $topic_from);
	if ($next)
	{
		header("location:".e_SELF."?{$next}");
		exit;
	}
	else
	{
		require_once(HEADERF);
		$ns->tablerender('', LAN_405, array('forum_viewtopic', '405'));
		require_once(FOOTERF);
		exit;
	}
}

if ($action == 'prev')
{
	$prev = $forum->thread_getprev($thread_id, $topic_from);
	if ($prev)
	{
		header("location:".e_SELF."?{$prev}");
		exit;
	}
	else
	{
		require_once(HEADERF);
		$ns->tablerender('', LAN_404, array('forum_viewtopic', '404'));
		require_once(FOOTERF);
		exit;
	}
}

if ($action == 'report')
{
	$thread_info = $forum->thread_get_postinfo($thread_id, TRUE);

	if (isset($_POST['report_thread']))
	{
		$report_add = $tp -> toDB($_POST['report_add']);
		if ($pref['reported_post_email'])
		{
			require_once(e_HANDLER."mail.php");
			$report = LAN_422.SITENAME." : ".(substr(SITEURL, -1) == "/" ? SITEURL : SITEURL."/").$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$thread_id.".post\n".LAN_425.USERNAME."\n".$report_add;
			$subject = LAN_421." ".SITENAME;
			sendemail(SITEADMINEMAIL, $subject, $report);
		}
		$sql->db_Insert('generic', "0, 'reported_post', ".time().", '".USERID."', '{$thread_info['head']['thread_name']}', ".intval($thread_id).", '{$report_add}'");
		define("e_PAGETITLE", LAN_01." / ".LAN_428);
		require_once(HEADERF);
		$text = LAN_424."<br /><br /><a href='forum_viewtopic.php?".$thread_id.".post'>".LAN_429."</a";
		$ns->tablerender(LAN_414, $text, array('forum_viewtopic', 'report'));
	}
	else
	{
		$thread_name = $tp -> toHTML($thread_info['head']['thread_name'], TRUE, 'no_hook, emotes_off');
		define("e_PAGETITLE", LAN_01." / ".LAN_426." ".$thread_name);
		require_once(HEADERF);
		$text = "<form action='".e_PLUGIN."forum/forum_viewtopic.php?".e_QUERY."' method='post'> <table style='width:100%'>
			<tr>
			<td  style='width:50%' >
			".LAN_415.": ".$thread_name." <a href='".e_PLUGIN."forum/forum_viewtopic.php?".$thread_id.".post'><span class='smalltext'>".LAN_420." </span>
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
			<input class='button' type='submit' name='report_thread' value='".LAN_419."' />
			</td>
			</tr>
			</table>";
		$ns->tablerender(LAN_414, $text, array('forum_viewtopic', 'report2'));
	}
	require_once(FOOTERF);
	exit;
}


if ($topic_from === 'last')
{
	$pref['forum_postspage'] = ($pref['forum_postspage'] ? $pref['forum_postspage'] : 10);
	$pages = ceil(($threadInfo['thread_total_replies']+1)/$pref['forum_postspage']);
	$topic_from = ($pages-1) * $pref['forum_postspage'];
}


}

?>