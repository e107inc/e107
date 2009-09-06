<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * forumThread class file
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/classes/forumThread.php,v $
 * $Revision: 1.1 $
 * $Date: 2009-09-06 04:30:46 $
 * $Author: mcfly_e107 $
 *
*/

class plugin_forum_classes_forumThread {

	var $message, $threadId, $forumId, $perPage, $noInc, $pages;
	public $e107, $forum, $postList;

	public function __construct($threadId=false, &$forum)
	{
		$this->e107 = e107::getInstance();
		$this->forum = $forum;
		$this->threadId = $threadId;
		if('post' == varset($_GET['f'])) {
			$this->processFunction();
		}
		$this->init();
		if(isset($_POST['track_toggle']))
		{
			$this->toggle_track();
			exit;
		}
		if($threadId) {
			$this->loadPosts();
		}
//		var_dump($this->forum);
	}

	function init()
	{
//		global $pref, $forum;
		global $pref;
		$this->perPage = (varset($_GET['perpage']) ? (int)$_GET['perpage'] : $pref['forum_postspage']);
		$this->page = (varset($_GET['p']) ? (int)$_GET['p'] : 0);
		if('last' == varset($_GET['f']))
		{
			$this->processFunction();
		}

		//If threadId doesn't exist, or not given, redirect to main forum page
		if (!$this->threadId || !$this->threadInfo = $this->loadThread($this->threadId))
		{
			header('Location:' . $this->e107->url->getUrl('forum', 'forum', array('func' => 'main')));
			exit;
		}

		//If not permitted to view forum, redirect to main forum page
		if (!$this->forum->checkPerm($this->threadInfo['thread_forum_id'], 'view'))
		{
			header('Location:' . $this->e107->url->getUrl('forum', 'forum', array('func' => 'main')));
			exit;
		}
		$this->pages = (int)ceil(($this->threadInfo['thread_total_replies'] + 1) / $this->perPage);
		$this->noInc = false;
	}

	public function loadPosts()
	{
		$this->postList = new plugin_forum_classes_forumPost($this->threadId, $this->page * $this->perPage, $this->perPage);
	}

	function toggle_track()
	{
		if (!USER || !isset($_GET['id'])) { return; }
		if($this->threadInfo['track_userid'])
		{
			$this->forum->track('del', USERID, $_GET['id']);
			$img = IMAGE_untrack;
		}
		else
		{
			$this->forum->track('add', USERID, $_GET['id']);
			$img = IMAGE_track;
		}
		if(e_AJAX_REQUEST)
		{
			$url = $this->e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => $this->threadId));
			echo "<a href='{$url}' id='forum-track-trigger'>{$img}</a>";
			exit();
		}
	}

	function processFunction()
	{
		global $pref;
		if (!isset($_GET['f'])) { return; }

		$function = trim($_GET['f']);
		switch ($function)
		{
			case 'post':
				$postId = varset($_GET['id']);
				$postInfo = $forum->postGet($postId,'post');
				$postNum = $forum->postGetPostNum($postInfo['post_thread'], $postId);
				$postPage = ceil($postNum / $pref['forum_postspage'])-1;
				$url = $this->e107->url->getUrl('forum', 'thread', "func=view&id={$postInfo['post_thread']}&page=$postPage");
				header('location: '.$url);
				exit;
				break;

			case 'last':
				$pages = ceil(($thread->threadInfo['thread_total_replies'] + 1) / $thread->perPage);
				$thread->page = ($pages - 1);
				break;

			case 'next':
				$next = $forum->threadGetNextPrev('next', $this->threadId, $this->threadInfo['forum_id'], $this->threadInfo['thread_lastpost']);
				if ($next)
				{
					$url = $e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => $next));
					header("location: {$url}");
					exit;
				}
				$this->message = LAN_405;
				break;

			case 'prev':
				$prev = $forum->threadGetNextPrev('prev', $this->threadId, $this->threadInfo['forum_id'], $this->threadInfo['thread_lastpost']);
				if ($prev)
				{
					$url = $e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => $prev));
					header("location: {$url}");
					exit;
				}
				$this->message = LAN_404;
				break;

			case 'report':
				$postId = (int)$_GET['id'];
				$postInfo = $forum->postGet($postId, 'post');

				if (isset($_POST['report_thread']))
				{
					$report_add = $e107->tp->toDB($_POST['report_add']);
					if ($pref['reported_post_email'])
					{
						require_once (e_HANDLER . 'mail.php');
						$report = LAN_422 . SITENAME . " : " . (substr(SITEURL, -1) == "/" ? SITEURL : SITEURL . "/") . $PLUGINS_DIRECTORY . "forum/forum_viewtopic.php?" . $thread_id . ".post\n" . LAN_425 . USERNAME . "\n" . $report_add;
						$subject = LAN_421 . " " . SITENAME;
						sendemail(SITEADMINEMAIL, $subject, $report);
					}
					$e107->sql->db_Insert('generic', "0, 'reported_post', " . time() . ", '" . USERID . "', '{$thread_info['head']['thread_name']}', " . intval($thread_id) . ", '{$report_add}'");
					define('e_PAGETITLE', LAN_01 . " / " . LAN_428);
					require_once (HEADERF);
					$text = LAN_424 . "<br /><br /><a href='forum_viewtopic.php?" . $thread_id . ".post'>" . LAN_429 . '</a>';
					$e107->ns->tablerender(LAN_414, $text, array('forum_viewtopic', 'report'));
				}
				else
				{
					$thread_name = $e107->tp->toHTML($postInfo['thread_name'], true, 'no_hook, emotes_off');
					define('e_PAGETITLE', LAN_01 . ' / ' . LAN_426 . ' ' . $thread_name);
					require_once (HEADERF);
					$url = $e107->url->getUrl('forum', 'thread', 'func=post&id='.$postId);
					$actionUrl = $e107->url->getUrl('forum', 'thread', 'func=report&id='.$postId);
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
					$e107->ns->tablerender(LAN_414, $text, array('forum_viewtopic', 'report2'));
				}
				require_once (FOOTERF);
				exit;
				break;

		}

	}

	function loadThread($id, $joinForum = true, $uid = USERID)
	{
		$id = (int)$id;
		$uid = (int)$uid;

		if($joinForum)
		{
			// TODO: Fix query to get only forum and parent info needed, with correct naming
			$qry = '
			SELECT t.*, f.*,
			fp.forum_id as parent_id, fp.forum_name as parent_name,
			sp.forum_id as forum_sub, sp.forum_name as sub_parent,
			tr.track_userid
			FROM `#forum_thread` AS t
			LEFT JOIN `#forum` AS f ON t.thread_forum_id = f.forum_id
			LEFT JOIN `#forum` AS fp ON fp.forum_id = f.forum_parent
			LEFT JOIN `#forum` AS sp ON sp.forum_id = f.forum_sub
			LEFT JOIN `#forum_track` AS tr ON tr.track_thread = t.thread_id AND tr.track_userid = '.$uid.'
			WHERE thread_id = '.$id;
		}
		else
		{
			$qry = '
			SELECT *
			FROM `#forum_thread`
			WHERE thread_id = '.$id;
		}
		if($this->e107->sql->db_Select_gen($qry))
		{
			$tmp = $this->e107->sql->db_Fetch();
			if($tmp)
			{
				if(trim($tmp['thread_options']) != '')
				{
					$tmp['thread_options'] = unserialize($tmp['thread_options']);
				}
				return $tmp;
			}
		}
		return false;
	}

	public function render()
	{
		global $FORUMREPLYSTYLE, $FORUMREPLYSTYLE_ALT, $FORUMTHREADSTYLE, $FORUMDELETEDSTYLE, $FORUMDELETEDSTYLE_ALT;
		global $forum_shortcodes;
		$ret = "";

		if(!$FORUMREPLYSTYLE) $FORUMREPLYSTYLE = $FORUMTHREADSTYLE;

		$alt = false;

		$i = $this->page;
		//var_dump($forum->thread->postList);
		foreach ($this->postList->post as $postInfo)
		{
			if($postInfo['post_options'])
			{
				$postInfo['post_options'] = unserialize($postInfo['post_options']);
			}
			$loop_uid = (int)$postInfo['post_user'];
			$i++;

			//TODO: Look into fixing this, to limit to a single query per pageload
			$e_hide_query = "SELECT post_id FROM `#forum_post` WHERE (`post_thread` = {$threadId} AND post_user= " . USERID . ' LIMIT 1';
			$e_hide_hidden = FORLAN_HIDDEN;
			$e_hide_allowed = USER;

			if ($i > 1)
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
				setScVar('forum_shortcodes', 'postInfo', $postInfo);
				$ret .= $this->e107->tp->parseTemplate($_style, true, $forum_shortcodes) . "\n";
			}
			else
			{
				$postInfo['thread_start'] = true;
				setScVar('forum_shortcodes', 'postInfo', $postInfo);
				$ret .= $this->e107->tp->parseTemplate($FORUMTHREADSTYLE, true, $forum_shortcodes) . "\n";
			}
		}
		unset($loop_uid);
		return $ret;
	}

	public function IncrementViews($id=0)
	{
		$e107 = e107::getInstance();
		$id = ($id ? (int)$id : $this->threadId);
		return $e107->sql->db_Update('forum_thread', 'thread_views=thread_views+1 WHERE thread_id='.$id);
	}


}

