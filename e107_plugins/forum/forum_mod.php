<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_admin.php');

function forum_thread_moderate($p)
{
	global $sql;
	foreach($p as $key => $val) {
		if (preg_match("#(.*?)_(\d+)_x#", $key, $matches))
		{
			$act = $matches[1];
			$id = intval($matches[2]);

			switch($act)
			{
				case 'lock' :
				$sql->db_Update("forum_t", "thread_active=0 WHERE thread_id={$id}");
				return FORLAN_CLOSE;
				break;

				case 'unlock' :
				$sql->db_Update("forum_t", "thread_active=1 WHERE thread_id={$id}");
				return FORLAN_OPEN;
				break;

				case 'stick' :
				$sql->db_Update("forum_t", "thread_s=1 WHERE thread_id={$id}");
				return FORLAN_STICK;
				break;

				case 'unstick' :
				$sql->db_Update("forum_t", "thread_s=0 WHERE thread_id={$id}");
				return FORLAN_UNSTICK;
				break;

				case 'delete' :
				return forum_delete_thread($id);
				break;

			}
		}
	}
}

function forum_delete_thread($thread_id)
{
	global $sql;
	$thread_id = (int)$thread_id;
	require_once(e_PLUGIN.'forum/forum_class.php');
	$f = new e107forum;
	$qry = "
	SELECT t.thread_forum_id, t.thread_parent, t.thread_user, f.forum_sub
	FROM #forum_t AS t
	LEFT JOIN #forum AS f ON t.thread_forum_id = f.forum_id
	WHERE t.thread_id = ".$thread_id;
//	$sql->db_Select("forum_t", "*", "thread_id=".(int)$thread_id);

	if($sql->db_Select_gen($qry))
	{
		$ret = "";
		$row = $sql->db_Fetch();
		if ($row['thread_parent'])
		{
			// post is a reply?
			$sql->db_Delete("forum_t", "thread_id=".$thread_id);
			// dec forum reply count by 1
			$sql->db_Update("forum", "forum_replies=forum_replies-1 WHERE forum_id={$row['thread_forum_id']} AND forum_replies>0");
			// dec thread reply count by 1
			$sql->db_Update("forum_t", "thread_total_replies=thread_total_replies-1 WHERE thread_id={$row['thread_parent']} AND thread_total_replies>0");
			// dec user forum post count by 1
			$tmp = explode(".", $row['thread_user']);
			$uid = intval($tmp[0]);
			if($uid > 0)
			{
				$sql->db_Update("user", "user_forums=user_forums-1 WHERE user_id='".$uid."' AND user_forums>0");
			}
			// update lastpost info
			$f->update_lastpost('thread', $row['thread_parent']);
			$f->update_lastpost('forum', $row['thread_forum_id']);
			$ret = FORLAN_154;
		}
		else
		{
			// post is thread
			// delete poll if there is one
			$sql->db_Delete("poll", "poll_datestamp=".$thread_id);
			//decrement user post counts
			forum_userpost_count("WHERE thread_id = ".$thread_id." OR thread_parent = ".$thread_id, "dec");
			// delete replies and grab how many there were
			$count = $sql->db_Delete("forum_t", "thread_parent=".$thread_id);
			// delete the post itself
			$sql->db_Delete("forum_t", "thread_id=".$thread_id);
			// update thread/reply counts
			$sql->db_Update("forum", "forum_threads = CAST(GREATEST(CAST(forum_threads AS SIGNED) - 1, 0) AS UNSIGNED), forum_replies = CAST(GREATEST(CAST(forum_replies AS SIGNED) - {$count}, 0) AS UNSIGNED) WHERE forum_id=".$row['thread_forum_id']);

			// update lastpost info
			$f->update_lastpost('forum', $row['thread_forum_id']);
			$ret = FORLAN_6.($count ? ", ".$count." ".FORLAN_7."." : ".");
		}

		//If parent forum is a sub, we must update lastpost info of it's parent
		if($row['forum_sub'])
		{
			//Update just the parent itself, if it's a container it will empty everything
			$f->update_lastpost('forum', $row['forum_sub']);
			//Update the parent with last post of either itself or it's subs
			$f->update_subparent_lp($row['forum_sub']);
		}
		return $ret;
	}
}

function forum_userpost_count($where = "", $type = "dec")
{
	global $sql;

	$qry = "
	SELECT thread_user, count(thread_user) AS cnt FROM #forum_t
	{$where}
	GROUP BY thread_user
	";

	if($sql->db_Select_gen($qry))
	{
		$uList = $sql->db_getList();
		foreach($uList as $u)
		{
			$tmp = explode(".", $u['thread_user']);
			$uid = intval($tmp[0]);
			if($uid > 0)
			{
				if("set" == $type)
				{
					$sql->db_Update("user", "user_forums={$u['cnt']} WHERE user_id=".$uid);
				}
				else
				{	// user_forums is unsigned, so underflow will give a very big number
					$sql->db_Update("user", "user_forums = CAST(GREATEST(CAST(user_forums AS SIGNED) - {$u['cnt']}, 0) AS UNSIGNED) WHERE user_id=".$uid);
				}
			}
		}
	}
}
