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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_mod.php,v $
|     $Revision: 1.5 $
|     $Date: 2008-12-15 00:29:20 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT'))
{
	exit;
}
include_lan(e_PLUGIN.'forum/languages/English/lan_forum_admin.php');

function forum_thread_moderate($p)
{
	//	var_dump($_POST);
	//	return;
	$e107 = e107::getInstance();
	global $sql;
	foreach ($p as $key => $val)
	{
		if (preg_match("#(.*?)_(\d+)_x#", $key, $matches))
		{
			$act = $matches[1];
//			print_a($matches); return;
			$id = (int)$matches[2];

			switch ($act)
			{
				case 'lock':
					$e107->sql->db_Update('forum_thread', 'thread_active=0 WHERE thread_id='.$id);
					return FORLAN_CLOSE;
					break;

				case 'unlock':
					$e107->sql->db_Update('forum_thread', 'thread_active=1 WHERE thread_id='.$id);
					return FORLAN_OPEN;
					break;

				case 'stick':
					$e107->sql->db_Update('forum_thread', 'thread_sticky=1 WHERE thread_id='.$id);
					return FORLAN_STICK;
					break;

				case 'unstick':
					$e107->sql->db_Update('forum_thread', 'thread_sticky=0 WHERE thread_id='.$id);
					return FORLAN_UNSTICK;
					break;

				case 'deleteThread':
					return forumDeleteThread($id);
					break;

				case 'deletePost':
					return forumDeletePost($id);
					break;

			}
		}
	}
}

function forumDeleteThread($threadId)
{
	require_once (e_PLUGIN.'forum/forum_class.php');
	$e107 = e107::getInstance();
	$f = &new e107forum;
	if ($threadInfo = $f->threadGet($threadId))
	{
		// delete poll if there is one
		$e107->sql->db_Delete('poll', 'poll_datestamp='.$threadId);

		//decrement user post counts
		if ($postCount = $f->threadGetUserPostcount($threadId))
		{
			foreach ($postCount as $k => $v)
			{
				$e107->sql->db_Update('user_extended', 'user_plugin_forum_posts=GREATEST(user_plugin_forum_posts-'.$v.',0) WHERE user_id='.$k);
			}
		}

		// delete all posts
		$e107->sql->db_Delete('forum_post', 'post_thread='.$threadId);

		// delete the thread itself
		$e107->sql->db_Delete('forum_thread', 'thread_id='.$threadId);

		//Delete any thread tracking
		$e107->sql->db_Delete('forum_track', 'track_thread='.$threadId);

		// update forum with correct thread/reply counts
		$e107->sql->db_Update('forum', "forum_threads=GREATEST(forum_threads-1,0), forum_replies=GREATEST(forum_replies-{$threadInfo['thread_total_replies']},0) WHERE forum_id=".$threadInfo['thread_forum_id']);

		// update lastpost info
		$f->forumUpdateLastpost('forum', $threadInfo['thread_forum_id']);
		return FORLAN_6.' and '.$threadInfo['thread_total_replies'].' '.FORLAN_7.'.';
	}
}

function forumDeletePost($postId)
{
	$postId = (int)$postId;
	require_once (e_PLUGIN.'forum/forum_class.php');
	$e107 = e107::getInstance();
	$f = &new e107forum;
	if(!$e107->sql->db_Select('forum_post', '*', 'post_id = '.$postId))
	{
		echo 'NOT FOUND!'; return;
	}
	$row = $e107->sql->db_Fetch(MYSQL_ASSOC);

	//decrement user post counts
	if ($row['post_user'])
	{
		$e107->sql->db_Update('user_extended', 'user_plugin_forum_posts=GREATEST(user_plugin_forum_posts-1,0) WHERE user_id='.$row['post_user']);
	}

	//delete attachments if they exist
	if($row['post_attachments'])
	{
		$f->postDeleteAttachments('post', $postId);
	}

	// delete post
	$e107->sql->db_Delete('forum_post', 'post_id='.$postId);

	// update thread with correct reply counts
	$e107->sql->db_Update('forum_thread', "thread_total_replies=GREATEST(thread_total_replies-1,0) WHERE thread_id=".$row['post_thread']);

	// update forum with correct thread/reply counts
	$e107->sql->db_Update('forum', "forum_replies=GREATEST(forum_replies-1,0) WHERE forum_id=".$row['post_forum']);

	// update thread lastpost info
	$f->forumUpdateLastpost('thread', $row['post_thread']);

	// update forum lastpost info
	$f->forumUpdateLastpost('forum', $row['post_forum']);
	return FORLAN_6.' and '.$threadInfo['thread_total_replies'].' '.FORLAN_7.'.';

}


?>