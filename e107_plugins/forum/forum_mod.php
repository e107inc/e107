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
|     $Revision: 1.3 $
|     $Date: 2008-12-09 21:46:14 $
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
					$e107->sql->db_Update('forum_thread', 'thread_s=1 WHERE thread_id='.$id);
					return FORLAN_STICK;
					break;

				case 'unstick':
					$e107->sql->db_Update('forum_thread', 'thread_s=0 WHERE thread_id='.$id);
					return FORLAN_UNSTICK;
					break;

				case 'deleteThread':
					return forumDeleteThread($id);
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

?>