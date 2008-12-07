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
|     $Revision: 1.9 $
|     $Date: 2008-12-07 04:16:38 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
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

include_lan(e_PLUGIN . 'forum/languages/English/lan_forum_viewtopic.php');
include_once (e_PLUGIN . 'forum/forum_class.php');
include_lan(e_PLUGIN . 'forum/templates/forum_icons_template.php');

$forum = new e107forum;
$thread = new e107ForumThread;


$thread->init();

if(isset($_GET['f']))
{
	$thread->processFunction();
	$thread->init();
}

require_once (e_PLUGIN . 'forum/forum_shortcodes.php');

$pm_installed = plugInstalled('pm');


//Only increment thread views if not being viewed by thread starter
if (USER || USER != $threadInfo['thread_user'] || !$thread->noInc)
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

define('e_PAGETITLE', LAN_01 . ' / ' . $e107->tp->toHTML($thread->threadInfo['forum_name'], true, 'no_hook, emotes_off') . " / " . $tp->toHTML($thread->threadInfo['thread_name'], true, 'no_hook, emotes_off'));
$modArray = $forum->forum_getmods($thread->threadInfo['forum_moderators']);
define('MODERATOR', (USER && is_array($modArray) && in_array(USERID, array_keys($modArray))));

if (MODERATOR && isset($_POST['mod']))
{
	require_once (e_PLUGIN . 'forum/forum_mod.php');
	$thread->message = forum_thread_moderate($_POST);
	$thread->threadInfo = $forum->threadGet($thread->threadId);
}

$postList = $forum->PostGet($thread->threadId, $thread->page * $thread->perPage, $thread->perPage);

//var_dump($thread->threadInfo);
require_once (HEADERF);
require_once (e_HANDLER . 'level_handler.php');
$gen = new convert;
if ($thread->message)
{
	$ns->tablerender('', $thread->message, array('forum_viewtopic', 'msg'));
}

if (isset($thread->threadInfo['thread_options']['poll']))
{
	if (!defined('POLLCLASS'))
	{
		include (e_PLUGIN . 'poll/poll_class.php');
	}
	$_qry = 'SELECT * FROM `#polls` WHERE `poll_datestamp` = ' . $thread->threadId;
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
$THREADNAME = $e107->tp->toHTML($thread->threadInfo['thread_name'], true, 'no_hook, emotes_off');
$NEXTPREV = "&lt;&lt; <a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'prev', 'id' => $thread->threadId)) . "'>" . LAN_389 . "</a>";
$NEXTPREV .= ' | ';
$NEXTPREV .= "<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'next', 'id' => $thread->threadId)) . "'>" . LAN_390 . "</a> &gt;&gt;";

if ($pref['forum_track'] && USER)
{
	$img = ($thread->threadInfo['track_userid'] ? IMAGE_track : IMAGE_untrack);
	$url = $e107->url->getUrl('forum', 'thread', array('func' => 'track_toggle', 'id' => $thread->threadId)); 
	$TRACK .= "
			<span id='forum-track-trigger-container'>
			<a href='{$url}' id='forum-track-trigger'>{$img}</a>
			</span>
			<script type='text/javascript'>
				//put this in header_js or as inline script just after the markup above
				$('forum-track-trigger').observe('click', function(e) { 
					e.stop(); 
					new e107Ajax.Updater('forum-track-trigger-container', '{$url}', {
						overlayPage: $(document.body)
					});
				});
			</script>
	";
}

$MODERATORS = LAN_321 . implode(', ', $modArray);

$THREADSTATUS = (!$thread->threadInfo['thread_active'] ? LAN_66 : '');

//$pages = ceil(($threadInfo['thread_total_replies'] + 1) / $perPage);

if ($thread->pages > 1)
{
	$parms = ($thread->threadInfo['thread_total_replies'] + 1) . ",{$perPage},{$topic_from}," . e_SELF . '?' . $this->threadId . '.[FROM],off';
	$GOTOPAGES = $tp->parseTemplate("{NEXTPREV={$parms}}");
}

$BUTTONS = '';
if ($forum->checkPerm($thread->threadInfo['thread_forum_id'], 'post') && $thread->threadInfo['thread_active'])
{
	$BUTTONS .= "<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'rp', 'id' => $thread->threadId)) . "'>" . IMAGE_reply . "</a>";
}
if ($forum->checkPerm($thread->threadInfo['thread_forum_id'], 'thread'))
{
	$BUTTONS .= "<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'nt', 'id' => $thread->threadInfo['thread_forum_id'])) . "'>" . IMAGE_newthread . "</a>";
}

$POLL = $pollstr;

$FORUMJUMP = forumjump();

$forstr = preg_replace("/\{(.*?)\}/e", '$\1', $FORUMSTART);

unset($forrep);
if (!$FORUMREPLYSTYLE) $FORUMREPLYSTYLE = $FORUMTHREADSTYLE;
$alt = false;

$i = $thread->page;
global $postInfo;
foreach ($postList as $postInfo)
{
	$loop_uid = (int)$postInfo['post_user'];
	$i++;

	//TODO: Look into fixing this, to limit to a single query per pageload
	$e_hide_query = "SELECT post_id FROM `#forum_post` WHERE (`post_thread` = {$threadId} AND post_user= " . USERID . ' LIMIT 1';
	$e_hide_hidden = FORLAN_HIDDEN;
	$e_hide_allowed = USER;

	if ($i > 1)
	{
		$alt = !$alt;
		if (isset($FORUMREPLYSTYLE_ALT) && $alt)
		{
			$forrep .= $e107->tp->parseTemplate($FORUMREPLYSTYLE_ALT, true, $forum_shortcodes) . "\n";
		}
		else
		{
			$forrep .= $e107->tp->parseTemplate($FORUMREPLYSTYLE, true, $forum_shortcodes) . "\n";
		}
	}
	else
	{
		$forthr = $e107->tp->parseTemplate($FORUMTHREADSTYLE, true, $forum_shortcodes) . "\n";
	}
}
unset($loop_uid);

if ($forum->checkPerm($thread->threadInfo['thread_forum_id'], 'post') && $thread->threadInfo['thread_active'])
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
$forumstring = $forstr . $forthr . $forrep . $forend;

//If last post came after USERLV and not yet marked as read, mark the thread id as read
if ($thread->threadInfo['thread_lastpost'] > USERLV && (strpos($currentUser['user_plugin_forum_viewed'], '.' . $thread->threadId . '.') === false))
{
	$tst = $forum->threadMarkAsRead($thread->threadId);
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
	return confirm(\"" . $tp->toJS(LAN_409) . "\");
	} else {
	return confirm(\"" . $tp->toJS(LAN_410) . " [ " . $tp->toJS(LAN_411) . "\" + thread + \" ]\");
	}
	}
	</script>";
require_once (FOOTERF);

function showmodoptions()
{
	global $thread_id;
	global $thread_info;
	global $forum_info;
	global $post_info;
	$forum_id = $forum_info['forum_id'];
	if ($post_info['thread_parent'] == false)
	{
		$type = 'thread';
		$ret = "<form method='post' action='" . e_PLUGIN . "forum/forum_viewforum.php?{$forum_id}' id='frmMod_{$forum_id}_{$post_info['thread_id']}'>";
	}
	else
	{
		$type = 'reply';
		$ret = "<form method='post' action='" . e_SELF . "?" . e_QUERY . "' id='frmMod_{$forum_id}_{$post_info['thread_id']}'>";
	}

	$ret .= "
		<div>
		<a href='" . e_PLUGIN . "forum/forum_post.php?edit.{$post_info['thread_id']}.{$topic_from}'>" . IMAGE_admin_edit . "</a>
		<input type='image' " . IMAGE_admin_delete . " name='delete_{$post_info['thread_id']}' value='thread_action' onclick=\"return confirm_('{$type}', {$forum_id}, {$thread_id}, '{$post_info['user_name']}')\" />
		";
	if ($type == 'thread')
	{
		$ret .= "<a href='" . e_PLUGIN . "forum/forum_conf.php?move.{$thread_id}'>" . IMAGE_admin_move2 . "</a>";
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
	if (strpos($FORUMTHREADSTYLE, '{RPG}') == false)
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

class e107ForumThread
{

	var $message, $threadId, $threadInfo, $forumId, $perPage, $noInc;

	function init()
	{
		global $pref, $forum;
		$e107 = e107::getInstance();
		$this->threadId = (int)varset($_GET['id']);
		$this->perPage = (varset($_GET['perpage']) ? (int)$_GET['perpage'] : $pref['forum_postspage']);
		$this->page = (varset($_GET['p']) ? (int)$_GET['p'] : 0);

		//If threadId doesn't exist, or not given, redirect to main forum page
		if (!$this->threadId || !$this->threadInfo = $forum->threadGet($this->threadId))
		{
			header('Location:' . $e107->url->getUrl('forum', 'forum', array('func' => 'main')));
			exit;
		}

		//If not permitted to view forum, redirect to main forum page
		if (!$forum->checkPerm($this->threadInfo['thread_forum_id'], 'view'))
		{
			header('Location:' . $e107->url->getUrl('forum', 'forum', array('func' => 'main')));
			exit;
		}
		$this->noInc = false;
	}

	function processFunction()
	{
		global $forum, $thread;
		$e107 = e107::getInstance();
		if (!isset($_GET['f']))
		{
			return;
		}

		$function = trim($_GET['f']);
		switch ($function)
		{
			case 'post':
				if ($thread_id)
				{
					$post_num = $forum->thread_postnum($thread_id);
					$pages = ceil(($post_num['post_num'] + 1) / $pref['forum_postspage']);
					$topic_from = ($pages - 1) * $pref['forum_postspage'];
					if ($post_num['parent'] != $thread_id)
					{
						header("location: " . e_SELF . "?{$post_num['parent']}.{$topic_from}#post_{$thread_id}");
						exit;
					}
				}
				else
				{
					header('Location:' . $e107->url->getUrl('forum', 'forum', array('func' => 'main')));
					exit;
				}
				break;

			case 'track':
				if (!USER || !isset($_GET['id'])) { return; }
				$forum->track('add', USERID, $_GET['id']);
				
				if(e_AJAX_REQUEST)
				{
					$url = $e107->url->getUrl('forum', 'thread', array('func' => 'untrack', 'id' => $thread->threadId)); 
					echo "<a href='{$url}' id='forum-track-trigger'>".IMAGE_untrack."</a>";
					exit();
				}
				break;

			case 'untrack':
				if (!USER || !isset($_GET['id'])) { return; }
				$forum->track('del', USERID, $_GET['id']);
				if(e_AJAX_REQUEST)
				{
					$url = $e107->url->getUrl('forum', 'thread', array('func' => 'track', 'id' => $thread->threadId)); 
					echo "<a href='{$url}' id='forum-track-trigger'>".IMAGE_track."</a>";
					exit();
				}
				break;

			case 'track_toggle':
				if (!USER || !isset($_GET['id'])) { return; }
				if($thread->threadInfo['track_userid'])
				{
					$forum->track('del', USERID, $_GET['id']);
					$img = IMAGE_untrack;
				}
				else
				{
					$forum->track('add', USERID, $_GET['id']);
					$img = IMAGE_track;
				}
				if(e_AJAX_REQUEST)
				{
					$url = $e107->url->getUrl('forum', 'thread', array('func' => 'track_toggle', 'id' => $thread->threadId)); 
					echo "<a href='{$url}' id='forum-track-trigger'>{$img}</a>";
					exit();
				}
				break;


			case 'last':
//				$pref['forum_postspage'] = ($pref['forum_postspage'] ? $pref['forum_postspage'] : 10);
				$pages = ceil(($this->threadInfo['thread_total_replies'] + 1) / $this->perPage);
				$this->page = ($pages - 1) * $this->perPage;
				break;

			case 'next':
				$next = $forum->threadGetNextPrev('next', $this->threadId, $this->threadInfo['forum_id'], $this->threadInfo['thread_lastpost']);
				if ($next)
				{
					$url = $e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => $next));
					header("location: {$url}");
					exit;
				}
				else
				{
					$this->message = LAN_405;
				}
				break;

			case 'prev':
				$prev = $forum->threadGetNextPrev('prev', $this->threadId, $this->threadInfo['forum_id'], $this->threadInfo['thread_lastpost']);
				if ($prev)
				{
					$url = $e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => $prev));
					header("location: {$url}");
					exit;
				}
				else
				{
					$this->message = LAN_404;
				}
				break;

			case 'report':
				$thread_info = $forum->thread_get_postinfo($thread_id, true);

				if (isset($_POST['report_thread']))
				{
					$report_add = $tp->toDB($_POST['report_add']);
					if ($pref['reported_post_email'])
					{
						require_once (e_HANDLER . "mail.php");
						$report = LAN_422 . SITENAME . " : " . (substr(SITEURL, -1) == "/" ? SITEURL : SITEURL . "/") . $PLUGINS_DIRECTORY . "forum/forum_viewtopic.php?" . $thread_id . ".post\n" . LAN_425 . USERNAME . "\n" . $report_add;
						$subject = LAN_421 . " " . SITENAME;
						sendemail(SITEADMINEMAIL, $subject, $report);
					}
					$sql->db_Insert('generic', "0, 'reported_post', " . time() . ", '" . USERID . "', '{$thread_info['head']['thread_name']}', " . intval($thread_id) . ", '{$report_add}'");
					define("e_PAGETITLE", LAN_01 . " / " . LAN_428);
					require_once (HEADERF);
					$text = LAN_424 . "<br /><br /><a href='forum_viewtopic.php?" . $thread_id . ".post'>" . LAN_429 . "</a";
					$ns->tablerender(LAN_414, $text, array('forum_viewtopic', 'report'));
				}
				else
				{
					$thread_name = $tp->toHTML($thread_info['head']['thread_name'], true, 'no_hook, emotes_off');
					define("e_PAGETITLE", LAN_01 . " / " . LAN_426 . " " . $thread_name);
					require_once (HEADERF);
					$text = "<form action='" . e_PLUGIN . "forum/forum_viewtopic.php?" . e_QUERY . "' method='post'> <table style='width:100%'>
					<tr>
					<td  style='width:50%' >
					" . LAN_415 . ": " . $thread_name . " <a href='" . e_PLUGIN . "forum/forum_viewtopic.php?" . $thread_id . ".post'><span class='smalltext'>" . LAN_420 . " </span>
					</a>
					</td>
					<td style='text-align:center;width:50%'>
					</td>
					</tr>
					<tr>
					<td>" . LAN_417 . "<br />" . LAN_418 . "
					</td>
					<td style='text-align:center;'>
					<textarea cols='40' rows='10' class='tbox' name='report_add'></textarea>
					</td>
					</tr>
					<tr>
					<td colspan='2' style='text-align:center;'><br />
					<input class='button' type='submit' name='report_thread' value='" . LAN_419 . "' />
					</td>
					</tr>
					</table>";
					$ns->tablerender(LAN_414, $text, array('forum_viewtopic', 'report2'));
				}
				require_once (FOOTERF);
				exit;
				break;

		}
	}
}

?>