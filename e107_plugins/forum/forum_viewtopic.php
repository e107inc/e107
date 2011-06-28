<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum View Topic
 *
 * $URL$
 * $Id$
 *
*/

require_once ('../../class2.php');

$e107 = e107::getInstance();
$tp = e107::getParser();
$ns = e107::getRender();

if (!$e107->isInstalled('forum'))
{
	header('Location: '.e_BASE.'index.php');
	exit;
}

if (isset($_POST['fjsubmit']))
{
	header('location:' . $e107->url->getUrl('forum', 'forum', array('func' => 'view', 'id' => $_POST['forumjump'])));
	exit;
}
$highlight_search = isset($_POST['highlight_search']);

if (!e_QUERY)
{
	//No parameters given, redirect to forum home
	header('Location:' . $e107->url->getUrl('forum', 'forum', array('func' => 'main')));
	exit;
}

include_lan(e_PLUGIN . 'forum/languages/'.e_LANGUAGE.'/lan_forum_viewtopic.php');
include_once (e_PLUGIN . 'forum/forum_class.php');
include_once(e_PLUGIN . 'forum/templates/forum_icons_template.php');

$forum = new e107forum();
$thread = new e107ForumThread();

if (isset($_GET['last']))
{
	$_GET['f'] = 'last';
}

if(isset($_GET['f']) && $_GET['f'] == 'post')
{
	$thread->processFunction();
}

$thread->init();

if(isset($_POST['track_toggle']))
{
	$thread->toggle_track();
	exit;
}

if(isset($_GET['f']))
{
	$ret = $thread->processFunction();
	if($ret) {
		require_once(HEADERF);
		echo $ret;
		require_once(FOOTERF);
		exit;
	}
	if($_GET['f'] != 'last') { $thread->init(); }
}

//e107::getScParser();
//require_once (e_PLUGIN . 'forum/forum_shortcodes.php');
//setScVar('forum_shortcodes', 'thread', $thread);
e107::getScBatch('view', 'forum')->setScVar('thread', $thread);

$pm_installed = plugInstalled('pm');

//Only increment thread views if not being viewed by thread starter
if (USER && (USERID != $thread->threadInfo['thread_user'] || $thread->threadInfo['thread_total_replies'] > 0) || !$thread->noInc)
{
	$forum->threadIncview($thread->threadInfo['thread_id']);
}

define('e_PAGETITLE', $tp->toHTML($thread->threadInfo['thread_name'], true, 'no_hook, emotes_off').' / '.$e107->tp->toHTML($thread->threadInfo['forum_name'], true, 'no_hook, emotes_off').' / '.LAN_01);
$forum->modArray = $forum->forumGetMods($thread->threadInfo['forum_moderators']);
define('MODERATOR', (USER && $forum->isModerator(USERID)));

e107::getScBatch('view', 'forum')->setScVar('forum', $forum);
//var_dump(e107::getScBatch('forum', 'forum'));

//setScVar('forum_shortcodes', 'forum', $forum);

if (MODERATOR && isset($_POST['mod']))
{
	require_once (e_PLUGIN . 'forum/forum_mod.php');
	$thread->message = forum_thread_moderate($_POST);
	$thread->threadInfo = $forum->threadGet($thread->threadId);
}

$num = $thread->page ? $thread->page - 1 : 0;
$postList = $forum->PostGet($thread->threadId, $num * $thread->perPage, $thread->perPage);

$gen = new convert;
if ($thread->message)
{
	//$ns->tablerender('', $thread->message, array('forum_viewtopic', 'msg'));
	e107::getMessage()->add($thread->message);
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
// FIXME - new template paths!
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
$tVars = new e_vars;
$forum->set_crumb(true, '', $tVars); // Set $BREADCRUMB (and BACKLINK)
//$tVars->BREADCRUMB = $crumbs['breadcrumb'];
//$tVars->BACKLINK = $tVars->BREADCRUMB;
//$tVars->FORUM_CRUMB = $crumbs['forum_crumb'];
$tVars->THREADNAME = $e107->tp->toHTML($thread->threadInfo['thread_name'], true, 'no_hook, emotes_off');
$tVars->NEXTPREV = "&lt;&lt; <a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'prev', 'id' => $thread->threadId)) . "'>" . LAN_389 . "</a>";
$tVars->NEXTPREV .= ' | ';
$tVars->NEXTPREV .= "<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'next', 'id' => $thread->threadId)) . "'>" . LAN_390 . "</a> &gt;&gt;";

if ($forum->prefs->get('track') && USER)
{
	$img = ($thread->threadInfo['track_userid'] ? IMAGE_track : IMAGE_untrack);
	$url = $e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => $thread->threadId));
	$tVars->TRACK .= "
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

$tVars->MODERATORS = LAN_321 . implode(', ', $forum->modArray);

$tVars->THREADSTATUS = (!$thread->threadInfo['thread_active'] ? LAN_66 : '');

if ($thread->pages > 1)
{
	if(!$thread->page) $thread->page = 1;
	$url = rawurlencode(e107::getUrl()->getUrl('forum', 'thread', array('func' => 'view', 'id' => $thread->threadId, 'page' => '[FROM]')));
	$parms = "total={$thread->pages}&type=page&current={$thread->page}&url=".$url."&caption=off";
	$tVars->GOTOPAGES = $tp->parseTemplate("{NEXTPREV={$parms}}");
/*
	$parms = ($thread->pages).",1,{$thread->page},url::forum::thread::func=view&id={$thread->threadId}&page=[FROM],off";
	$tVars->GOTOPAGES = $tp->parseTemplate("{NEXTPREV={$parms}}");*/
}

$tVars->BUTTONS = '';
if ($forum->checkPerm($thread->threadInfo['thread_forum_id'], 'post') && $thread->threadInfo['thread_active'])
{
	$tVars->BUTTONS .= "<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'rp', 'id' => $thread->threadId)) . "'>" . IMAGE_reply . "</a>";
}
if ($forum->checkPerm($thread->threadInfo['thread_forum_id'], 'thread'))
{
	$tVars->BUTTONS .= "<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'nt', 'id' => $thread->threadInfo['thread_forum_id'])) . "'>" . IMAGE_newthread . "</a>";
}

$tVars->POLL = $pollstr;

$tVars->FORUMJUMP = forumjump();

$tVars->MESSAGE = $thread->message;

$forstr = $tp->simpleParse($FORUMSTART, $tVars);

unset($forrep);
if (!$FORUMREPLYSTYLE) $FORUMREPLYSTYLE = $FORUMTHREADSTYLE;
$alt = false;

$i = $thread->page;
foreach ($postList as $postInfo)
{
	if($postInfo['post_options'])
	{
		$postInfo['post_options'] = unserialize($postInfo['post_options']);
	}
	$loop_uid = (int)$postInfo['post_user'];
	$tnum = $i;
	$i++;

	//TODO: Look into fixing this, to limit to a single query per pageload
	$threadId = $thread->threadInfo['thread_id'];
	$e_hide_query = "SELECT post_id FROM `#forum_post` WHERE (`post_thread` = {$threadId} AND post_user= " . USERID . ' LIMIT 1';
	$e_hide_hidden = FORLAN_HIDDEN;
	$e_hide_allowed = USER;

	if ($tnum > 1)
	{
		$postInfo['thread_start'] = false;
		$alt = !$alt;

		if($postInfo['post_status'])
		{
			$_style = (isset($FORUMDELETEDSTYLE_ALT) && $alt ? $FORUMDELETEDSTYLE_ALT : $FORUMDELETEDSTYLE);
		}
		else
		{
			$_style = (isset($FORUMREPLYSTYLE_ALT) && $alt ? $FORUMREPLYSTYLE_ALT : $FORUMREPLYSTYLE);
		}
//		setScVar('forum_shortcodes', 'postInfo', $postInfo);
		e107::getScBatch('view', 'forum')->setScVar('postInfo', $postInfo);
		$forrep .= $e107->tp->parseTemplate($_style, true, $forum_shortcodes) . "\n";
	}
	else
	{
		$postInfo['thread_start'] = true;
		e107::getScBatch('view', 'forum')->setScVar('postInfo', $postInfo);
		$forthr = $e107->tp->parseTemplate($FORUMTHREADSTYLE, true, $forum_shortcodes) . "\n";
	}
}
unset($loop_uid);

if ($forum->checkPerm($thread->threadInfo['thread_forum_id'], 'post') && $thread->threadInfo['thread_active'])
{
	if (!$forum_quickreply)
	{
		$tVars->QUICKREPLY = "
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
		$tVars->QUICKREPLY = $forum_quickreply;
	}
}

$forend = $tp->simpleParse($FORUMEND, $tVars);

$forumstring = $forstr . $forthr . $forrep . $forend;

//If last post came after USERLV and not yet marked as read, mark the thread id as read
$threadsViewed = explode(',', $currentUser['user_plugin_forum_viewed']);
if ($thread->threadInfo['thread_lastpost'] > USERLV && !in_array($thread->threadId, $threadsViewed))
{
	$tst = $forum->threadMarkAsRead($thread->threadId);
}

require_once (HEADERF);

if ($forum->prefs->get('enclose'))
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

function showmodoptions()
{
	global $thread, $postInfo;

	$e107 = e107::getInstance();
	$forum_id = $thread->threadInfo['forum_id'];
	if ($postInfo['thread_start'])
	{
		$type = 'Thread';
		$ret = "<form method='post' action='" . $e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => $postInfo['post_thread']))."' id='frmMod_{$postInfo['post_forum']}_{$postInfo['post_thread']}'>";
		$delId = $postInfo['post_thread'];
	}
	else
	{
		$type = 'Post';
		$ret = "<form method='post' action='" . e_SELF . '?' . e_QUERY . "' id='frmMod_{$postInfo['post_thread']}_{$postInfo['post_id']}'>";
		$delId = $postInfo['post_id'];
	}

	$ret .= "
		<div>
		<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'edit', 'id' => $postInfo['post_id']))."'>" . IMAGE_admin_edit . "</a>
		<input type='image' " . IMAGE_admin_delete . " name='delete{$type}_{$delId}' value='thread_action' onclick=\"return confirm_('{$type}', {$postInfo['post_forum']}, {$postInfo['post_thread']}, '{$postInfo['user_name']}')\" />
		<input type='hidden' name='mod' value='1'/>
		";
	if ($type == 'Thread')
	{
		$ret .= "<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'move', 'id' => $postInfo['post_id']))."'>" . IMAGE_admin_move2 . "</a>";
	}
	else
	{
		$ret .= "<a href='" . $e107->url->getUrl('forum', 'thread', array('func' => 'split', 'id' => $postInfo['post_id']))."'>" . IMAGE_admin_split . '</a>';

	}
	$ret .= "
		</div>
		</form>";
	return $ret;
}

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

class e107ForumThread
{

	public $message;
	public $threadId;
	public $forumId;
	public $perPage;
	public $noInc;
	public $pages;

	function init()
	{
		global $forum;
		$e107 = e107::getInstance();
		$this->threadId = (int)varset($_GET['id']);
		$this->perPage = (varset($_GET['perpage']) ? (int)$_GET['perpage'] : $forum->prefs->get('postspage'));
		$this->page = (varset($_GET['p']) ? (int)$_GET['p'] : 1);

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
		$this->pages = ceil(($this->threadInfo['thread_total_replies']) / $this->perPage);
		$this->noInc = false;
	}

	function toggle_track()
	{
		global $forum, $thread;
		$e107 = e107::getInstance();
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
			$url = $e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => $thread->threadId));
			echo "<a href='{$url}' id='forum-track-trigger'>{$img}</a>";
			exit();
		}
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
				$postId = varset($_GET['id']);
				$postInfo = $forum->postGet($postId,'post');
				$postNum = $forum->postGetPostNum($postInfo['post_thread'], $postId);
				$postPage = ceil($postNum / $forum->prefs->get('postspage'));
				$url = $e107->url->getUrl('forum', 'thread', "func=view&id={$postInfo['post_thread']}&page=$postPage&raw");
				header('location: '.$url);
				exit;
				break;

			case 'last':
				$pages = ceil(($thread->threadInfo['thread_total_replies']) / $thread->perPage);
				$thread->page = $_GET['p'] = $pages;
				break;

			case 'next': // FIXME - nextprev thread detection not working
				$next = $forum->threadGetNextPrev('next', $this->threadId, $this->threadInfo['forum_id'], $this->threadInfo['thread_lastpost']);
				if ($next)
				{
					$url = $e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => $next, 'raw' => true));
					header("location: {$url}");
					exit;
				}
				$this->message = LAN_405;
				break;

			case 'prev': // FIXME - nextprev thread detection not working
				$prev = $forum->threadGetNextPrev('prev', $this->threadId, $this->threadInfo['forum_id'], $this->threadInfo['thread_lastpost']);
				if ($prev)
				{
					$url = $e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => $prev, 'raw' => true));
					header("location: {$url}");
					exit;
				}
				$this->message = LAN_404;
				break;

			case 'report':
				$threadId = (int)$_GET['id'];
				$postId = (int)$_GET['post'];
				$postInfo = $forum->postGet($postId, 'post');

				if (isset($_POST['report_thread']))
				{
					$report_add = $e107->tp->toDB($_POST['report_add']);
					if ($forum->prefs->get('reported_post_email'))
					{
						require_once (e_HANDLER . 'mail.php');
						$report = LAN_422 . SITENAME . " : " . (substr(SITEURL, -1) == "/" ? SITEURL : SITEURL . "/") . $e107->getFolder('plugins') . "forum/forum_viewtopic.php?" . $this->threadId . ".post\n" . LAN_425 . USERNAME . "\n" . $report_add;
						$subject = LAN_421 . " " . SITENAME;
						sendemail(SITEADMINEMAIL, $subject, $report);
					}
					// no reference of 'head' $threadInfo['head']['thread_name']
					$e107->sql->db_Insert('generic', "0, 'reported_post', " . time() . ", '" . USERID . "', '{$this->threadInfo['thread_name']}', " . intval($this->threadId) . ", '{$report_add}'");
					define('e_PAGETITLE', LAN_01 . " / " . LAN_428);
					$url = $e107->url->getUrl('forum', 'thread', 'func=post&id='.$postId);
					$text = LAN_424 . "<br /><br /><a href='{$url}'>" . LAN_429 . '</a>';
					return $e107->ns->tablerender(LAN_414, $text, array('forum_viewtopic', 'report'), true);
				}
				else
				{
					$thread_name = $e107->tp->toHTML($postInfo['thread_name'], true, 'no_hook, emotes_off');
					define('e_PAGETITLE', LAN_01 . ' / ' . LAN_426 . ' ' . $thread_name);
					$url = $e107->url->getUrl('forum', 'thread', 'func=post&id='.$postId);
					$actionUrl = $e107->url->getUrl('forum', 'thread', "func=report&id={$threadId}&post={$postId}");
					$text = "<form action='".$actionUrl."' method='post'>
					<table style='width:100%'>
					<tr>
					<td  style='width:50%' >
					" . LAN_415 . ': ' . $thread_name . " <a href='".$url."'><span class='smalltext'>" . LAN_420 . " </span>
					</a>
					</td>
					<td style='text-align:center;width:50%'>
					</td>
					</tr>
					<tr>
					<td>" . LAN_417 . "<br />" . LAN_418 . "
					</td>
					</tr>
					<tr>
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
					return $e107->ns->tablerender(LAN_414, $text, array('forum_viewtopic', 'report2'), true);
				}
//				require_once (FOOTERF);
				exit;
				break;

		}
	}
}

?>