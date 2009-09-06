<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum Viewtopic
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_viewtopic.php,v $
 * $Revision: 1.21 $
 * $Date: 2009-09-06 04:30:46 $
 * $Author: mcfly_e107 $
 *
*/

require_once ('../../class2.php');

if (isset($_POST['fjsubmit']))
{
	header('location:' . $e107->url->getUrl('forum', 'forum', array('func' => 'view', 'id' => $_POST['forumjump'])));
	exit;
}
$highlight_search = isset($_POST['highlight_search']);

if (!e_QUERY)
{
	//No paramters given, redirect to forum home
	header('Location:' . $e107->url->getUrl('forum', 'forum', array('func' => 'main')));
	exit;
}
e107::getScParser();
include_lan(e_PLUGIN.'forum/languages/English/lan_forum_viewtopic.php');

$forum = new plugin_forum_classes_forumClass;
include_lan(e_PLUGIN.'forum/languages/English/lan_forum_viewtopic.php');
include_lan(e_PLUGIN.'forum/templates/forum_icons_template.php');
$forum->threadNew(varset($_GET['id']));

require_once (e_HANDLER.'level_handler.php');
if (!is_object($e107->userRank)) { $e107->userRank = new e107UserRank; }

require_once (e_PLUGIN.'forum/forum_shortcodes.php');
setScVar('forum_shortcodes', 'thread', $forum->thread);

$pm_installed = e107::isInstalled('pm');

//Only increment thread views if not being viewed by thread starter
if (USER && (USERID != $forum->thread->threadInfo['thread_user'] || $forum->thread->threadInfo['thread_total_replies'] > 0) || !$forum->thread->noInc)
{
	$forum->thread->IncrementViews();
}

define('e_PAGETITLE', LAN_01 . ' / ' . $e107->tp->toHTML($forum->thread->threadInfo['forum_name'], true, 'no_hook, emotes_off') . " / " . $tp->toHTML($thread->threadInfo['thread_name'], true, 'no_hook, emotes_off'));
$forum->modArray = $forum->forumGetMods($thread->threadInfo['forum_moderators']);
define('MODERATOR', (USER && $forum->isModerator(USERID)));
setScVar('forum_shortcodes', 'forum', $forum);

if (MODERATOR && isset($_POST['mod']))
{
	require_once (e_PLUGIN . 'forum/forum_mod.php');
	$forum->thread->message = forum_thread_moderate($_POST);
}

$forum->thread->loadPosts();

$gen = new convert;
if ($forum->thread->message)
{
	$ns->tablerender('', $forum->thread->message, array('forum_viewtopic', 'msg'));
}

if (isset($forum->thread->threadInfo['thread_options']['poll']))
{
	if (!defined('POLLCLASS'))
	{
		include (e_PLUGIN . 'poll/poll_class.php');
	}
	$_qry = 'SELECT * FROM `#polls` WHERE `poll_datestamp` = ' . $forum->thread->threadId;
	$poll = new poll;
	$pollstr = "<div class='spacer'>" . $poll->render_poll($_qry, 'forum', 'query', true) . '</div>';
}
//Load forum templates

if (file_exists(THEME . 'forum_design.php'))
{
	include_once (THEME . 'forum_design.php');
}
if (!$FORUMSTART)
{
	if (file_exists(THEME . 'forum_viewtopic_template.php'))
	{
		require_once (THEME . 'forum_viewtopic_template.php');
	}
	elseif (file_exists(THEME . 'forum_template.php'))
	{
		require_once (THEME . 'forum_template.php');
	}
	else
	{
		require_once (e_PLUGIN . 'forum/templates/forum_viewtopic_template.php');
	}
}

// get info for main thread -------------------------------------------------------------------------------------------------------------------------------------------------------------------

$forum->set_crumb(true); // Set $BREADCRUMB (and BACKLINK)
$THREADNAME = $e107->tp->toHTML($forum->thread->threadInfo['thread_name'], true, 'no_hook, emotes_off');
$NEXTPREV = "&lt;&lt; <a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'prev', 'id' => $thread->threadId)) . "'>" . LAN_389 . "</a>";
$NEXTPREV .= ' | ';
$NEXTPREV .= "<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'next', 'id' => $thread->threadId)) . "'>" . LAN_390 . "</a> &gt;&gt;";

if ($pref['forum_track'] && USER)
{
	$img = ($forum->thread->threadInfo['track_userid'] ? IMAGE_track : IMAGE_untrack);
	$url = $e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => $forum->thread->threadId));
	$TRACK .= "
			<span id='forum-track-trigger-container'>
			<a href='{$url}' id='forum-track-trigger'>{$img}</a>
			</span>
			<script type='text/javascript'>
			e107.runOnLoad(function(){
				$('forum-track-trigger').observe('click', function(e) {
					e.stop();
					new e107Ajax.Updater('forum-track-trigger-container', '{$url}', {
						method: 'post',
						parameters: { //send query parameters here
							'track_toggle': 1
						},
						overlayPage: $(document.body)
					});
				});
			}, document, true);
			</script>
	";
}

$MODERATORS = LAN_321 . implode(', ', $forum->modArray);

$THREADSTATUS = (!$forum->thread->threadInfo['thread_active'] ? LAN_66 : '');

if ($forum->thread->pages > 1)
{
	$parms = ($forum->thread->pages).",1,{$forum->thread->page},url::forum::thread::func=view&id={$forum->thread->threadId}&page=[FROM],off";
	$GOTOPAGES = $tp->parseTemplate("{NEXTPREV={$parms}}");
}

$BUTTONS = '';
if ($forum->checkPerm($forum->thread->threadInfo['thread_forum_id'], 'post') && $forum->thread->threadInfo['thread_active'])
{
	$BUTTONS .= "<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'rp', 'id' => $forum->thread->threadId)) . "'>" . IMAGE_reply . "</a>";
}
if ($forum->checkPerm($forum->thread->threadInfo['thread_forum_id'], 'thread'))
{
	$BUTTONS .= "<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'nt', 'id' => $forum->thread->threadInfo['thread_forum_id'])) . "'>" . IMAGE_newthread . "</a>";
}

$POLL = $pollstr;
$FORUMJUMP = forumjump();

$forstr = preg_replace("/\{(.*?)\}/e", '$\1', $FORUMSTART);

$forthread = $forum->thread->render();

if ($forum->checkPerm($forum->thread->threadInfo['thread_forum_id'], 'post') && $forum->thread->threadInfo['thread_active'])
{
	if (!$forum_quickreply)
	{
		$QUICKREPLY = "
		<form action='" . $e107->url->getUrl('forum', 'thread', array('func' => 'rp', 'id' => $thread->threadId)) . "' method='post'>
		<p>" . LAN_393 . ":<br />
		<textarea cols='60' rows='4' class='tbox' name='post' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'></textarea>
		<br />
		<input type='submit' name='fpreview' value='" . LAN_394 . "' class='button' /> &nbsp;
		<input type='submit' name='reply' value='" . LAN_395 . "' class='button' />
		<input type='hidden' name='thread_id' value='$thread_parent' />
		</p>
		</form>";
	}
	else
	{
		$QUICKREPLY = $forum_quickreply;
	}
}

$forend = preg_replace("/\{(.*?)\}/e", '$\1', $FORUMEND);
$forumstring = $forstr . $forthread . $forend;

//If last post came after USERLV and not yet marked as read, mark the thread id as read
$threadsViewed = explode(',', $currentUser['user_plugin_forum_viewed']);
if ($forum->thread->threadInfo['thread_lastpost'] > USERLV && !in_array($forum->thread->threadId, $threadsViewed))
{
	$tst = $forum->threadMarkAsRead($forum->thread->threadId);
}

require_once (HEADERF);

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
	if (mode == 'Thread') {
	return confirm(\"" . $tp->toJS(LAN_409) . "\");
	} else {
	return confirm(\"" . $tp->toJS(LAN_410) . " [ " . $tp->toJS(LAN_411) . "\" + thread + \" ]\");
	}
	}
	</script>";
require_once (FOOTERF);

function forumjump()
{
	global $forum;
	$jumpList = $forum->forumGetAllowed();
	$text = "<form method='post' action='" . e_SELF . "'><p>" . LAN_65 . ": <select name='forumjump' class='tbox'>";
	foreach ($jumpList as $key => $val)
	{
		$text .= "\n<option value='" . $key . "'>" . $val . "</option>";
	}
	$text .= "</select> <input class='button' type='submit' name='fjsubmit' value='" . LAN_03 . "' />&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . e_SELF . "?" . e_QUERY . "#top' onclick=\"window.scrollTo(0,0);\">" . LAN_10 . "</a></p></form>";
	return $text;
}

function rpg($user_join, $user_forums)
{
	global $FORUMTHREADSTYLE;
	if (strpos($FORUMTHREADSTYLE, '{RPG}') === false)
	{
		return '';
	}
	// rpg mod by Ikari ( kilokan1@yahoo.it | http://artemanga.altervista.org )

	$lvl_post_mp_cost = 2.5;
	$lvl_mp_regen_per_day = 4;
	$lvl_avg_ppd = 5;
	$lvl_bonus_redux = 5;
	$lvl_user_days = max(1, round((time() - $user_join) / 86400));
	$lvl_ppd = $user_forums / $lvl_user_days;
	if ($user_forums < 1)
	{
		$lvl_level = 0;
	}
	else
	{
		$lvl_level = floor(pow(log10($user_forums), 3)) + 1;
	}
	if ($lvl_level < 1)
	{
		$lvl_hp = "0 / 0";
		$lvl_hp_percent = 0;
	}
	else
	{
		$lvl_max_hp = floor((pow($lvl_level, (1 / 4))) * (pow(10, pow($lvl_level + 2, (1 / 3)))) / (1.5));

		if ($lvl_ppd >= $lvl_avg_ppd)
		{
			$lvl_hp_percent = floor((.5 + (($lvl_ppd - $lvl_avg_ppd) / ($lvl_bonus_redux * 2))) * 100);
		}
		else
		{
			$lvl_hp_percent = floor($lvl_ppd / ($lvl_avg_ppd / 50));
		}
		if ($lvl_hp_percent > 100)
		{
			$lvl_max_hp += floor(($lvl_hp_percent - 100) * pi());
			$lvl_hp_percent = 100;
		}
		else
		{
			$lvl_hp_percent = max(0, $lvl_hp_percent);
		}
		$lvl_cur_hp = floor($lvl_max_hp * ($lvl_hp_percent / 100));
		$lvl_cur_hp = max(0, $lvl_cur_hp);
		$lvl_cur_hp = min($lvl_max_hp, $lvl_cur_hp);
		$lvl_hp = $lvl_cur_hp . '/' . $lvl_max_hp;
	}
	if ($lvl_level < 1)
	{
		$lvl_mp = '0 / 0';
		$lvl_mp_percent = 0;
	}
	else
	{
		$lvl_max_mp = floor((pow($lvl_level, (1 / 4))) * (pow(10, pow($lvl_level + 2, (1 / 3)))) / (pi()));
		$lvl_mp_cost = $user_forums * $lvl_post_mp_cost;
		$lvl_mp_regen = max(1, $lvl_user_days * $lvl_mp_regen_per_day);
		$lvl_cur_mp = floor($lvl_max_mp - $lvl_mp_cost + $lvl_mp_regen);
		$lvl_cur_mp = max(0, $lvl_cur_mp);
		$lvl_cur_mp = min($lvl_max_mp, $lvl_cur_mp);
		$lvl_mp = $lvl_cur_mp . '/' . $lvl_max_mp;
		$lvl_mp_percent = floor($lvl_cur_mp / $lvl_max_mp * 100);
	}
	if ($lvl_level < 1)
	{
		$lvl_exp = "0 / 0";
		$lvl_exp_percent = 100;
	}
	else
	{
		$lvl_posts_for_next = floor(pow(10, pow($lvl_level, (1 / 3))));
		if ($lvl_level == 1)
		{
			$lvl_posts_for_this = max(1, floor(pow(10, (($lvl_level - 1)))));
		}
		else
		{
			$lvl_posts_for_this = max(1, floor(pow(10, pow(($lvl_level - 1), (1 / 3)))));
		}
		$lvl_exp = ($user_forums - $lvl_posts_for_this) . "/" . ($lvl_posts_for_next - $lvl_posts_for_this);
		$lvl_exp_percent = floor((($user_forums - $lvl_posts_for_this) / max(1, ($lvl_posts_for_next - $lvl_posts_for_this))) * 100);
	}

	$bar_image = THEME . "images/bar.jpg";
	if (!is_readable($bar_image))
	{
		$bar_image = e_PLUGIN . "forum/images/" . IMODE . "/bar.jpg";
	}

	$rpg_info .= "<div style='padding:2px; white-space:nowrap'>";
	$rpg_info .= "<b>Level = " . $lvl_level . "</b><br />";
	$rpg_info .= "HP = " . $lvl_hp . "<br /><img src='{$bar_image}' alt='' style='border:#345487 1px solid; height:10px; width:" . $lvl_hp_percent . "%'><br />";
	$rpg_info .= "EXP = " . $lvl_exp . "<br /><img src='{$bar_image}' alt='' style='border:#345487 1px solid; height:10px; width:" . $lvl_exp_percent . "%'><br />";
	$rpg_info .= "MP = " . $lvl_mp . "<br /><img src='{$bar_image}' alt='' style='border:#345487 1px solid; height:10px; width:" . $lvl_mp_percent . "%'><br />";
	$rpg_info .= "</div>";
	return $rpg_info;
}
