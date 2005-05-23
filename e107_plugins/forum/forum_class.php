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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/forum_class.php,v $
|     $Revision: 1.28 $
|     $Date: 2005-05-23 00:26:37 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
class e107forum {

	function thread_postnum($thread_id)
	{
		global $sql;
		$ret = array();
		$ret['parent'] = $thread_id;
		$query = "
		SELECT ft.thread_id, fp.thread_id as parent
		FROM #forum_t AS t
		LEFT JOIN #forum_t AS ft ON ft.thread_parent = t.thread_parent AND ft.thread_id <= {$thread_id}
		LEFT JOIN #forum_t as fp ON fp.thread_id = t.thread_parent
		WHERE t.thread_id = {$thread_id} AND t.thread_parent != 0
		ORDER  BY ft.thread_datestamp ASC
		";
		if($ret['post_num'] = $sql->db_Select_gen($query))
		{
			$row = $sql->db_Fetch();
			$ret['parent'] = $row['parent'];
		}
		return $ret;
	}

	function update_lastpost($type, $id, $update_threads = FALSE)
	{
		global $sql;
		$sql2 = new db;
		if ($type == 'thread')
		{
			$id = intval($id);
			$thread_info = $this->thread_get_lastpost($id);
			list($uid, $uname) = explode(".", $thread_info['thread_user'], 2);
			if ($thread_info)
			{
				if($thread_user['user_name'] != "")
				{
					$thread_lastuser = $uid.".".$thread_info['user_name'];
				}
				else
				{
					$tmp = explode(chr(1), $thread_info['thread_user']);
					$thread_lastuser = $tmp[0];
				}
				$sql->db_Update('forum_t', "thread_lastpost = {$thread_info['thread_datestamp']}, thread_lastuser = '{$thread_lastuser}' WHERE thread_id = {$id}");
			}
			return $thread_info;
		}
		if ($type == 'forum') {
			if ($id == 'all')
			{
				if ($sql->db_Select('forum', 'forum_id', 'forum_parent != 0'))
				{
					while ($row = $sql->db_Fetch())
					{
						$parentList[] = $row['forum_id'];
					}
					foreach($parentList as $id)
					{
						//	echo "Updating forum #{$id}<br />";
						$this->update_lastpost('forum', $id, $update_threads);
					}
				}
			}
			else
			{
				$id = intval($id);
				$forum_lpinfo = "";
				if($update_threads == TRUE)
				{
					if ($sql2->db_Select('forum_t', 'thread_id', "thread_forum_id = $id AND thread_parent = 0"))
					{
						while ($row = $sql2->db_Fetch())
						{
							$this->update_lastpost('thread', $row['thread_id']);
						}
					}
				}
				if ($sql->db_Select("forum_t", "*", "thread_forum_id='$id' ORDER BY thread_datestamp DESC LIMIT 0,1"))
				{
					$row = $sql->db_Fetch();
					$tmp = explode(chr(1), $row['thread_user']);
					$forum_lp_user = $tmp[0];
					$last_id = $row['thread_parent'] ? $row['thread_parent'] : $row['thread_id'];
					$forum_lp_info = $row['thread_datestamp'].".".$last_id;
					$sql->db_Update('forum', "forum_lastpost_user = '{$forum_lp_user}', forum_lastpost_info = '{$forum_lp_info}' WHERE forum_id={$id}");
				}
			}
		}
	}

	function forum_markasread($forum_id) {
		global $sql;
		if ($forum_id != 'all') {
			$forum_id = intval($forum_id);
			$extra = " AND thread_forum_id='$forum_id' ";
		}
		$qry = "thread_lastpost > ".USERLV." AND thread_parent = 0 {$extra} ";
		if ($sql->db_Select('forum_t', 'thread_id', $qry)) {
			while ($row = $sql->db_Fetch()) {
				$u_new .= ".".$row['thread_id'].".";
			}
			$u_new .= USERVIEWED;
			$sql->db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' ");
			header("location:".e_SELF);
			exit;
		}
	}

	function thread_markasread($thread_id)
	{
		global $sql;
		$thread_id = intval($thread_id);
		$u_new = USERVIEWED.".$thread_id";
		return $sql->db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' ");
	}

	function forum_getparents()
	{
		global $sql;
		if ($sql->db_Select('forum', '*', "forum_parent='0' ORDER BY forum_order ASC"))
		{
			while ($row = $sql->db_Fetch()) {
				$ret[] = $row;
			}
			return $ret;
		}
		return FALSE;
	}

	function forum_getforums()
	{
		global $sql;
		$qry = "
		SELECT f.*, u.user_name FROM #forum AS f
		LEFT JOIN #user AS u ON f.forum_lastpost_user = u.user_id
		WHERE forum_parent != 0 AND forum_sub = 0
		ORDER BY f.forum_order ASC
		";
		if ($sql->db_Select_gen($qry))
		{
			while ($row = $sql->db_Fetch())
			{
				$ret[$row['forum_parent']][] = $row;
			}
			return $ret;
		}
		return FALSE;
	}

	function forum_getsubs($forum_id = "")
	{
		global $sql;
		$where = ($forum_id != "" ? "AND forum_sub = {$forum_id}" : "");
		$qry = "
		SELECT f.*, u.user_name FROM #forum AS f
		LEFT JOIN #user AS u ON f.forum_lastpost_user = u.user_id
		WHERE forum_sub != 0 {$where}
		ORDER BY f.forum_order ASC
		";
		if ($sql->db_Select_gen($qry))
		{
			while ($row = $sql->db_Fetch())
			{
				if($forum_id == "")
				{
					$ret[$row['forum_parent']][$row['forum_sub']][] = $row;
				}
				else
				{
					$ret[] = $row;
				}
			}
			return $ret;
		}
		return FALSE;
	}


	function forum_newflag_list()
	{
		global $sql;
		$viewed = "";
		if(USERVIEWED)
		{
			$viewed = preg_replace("#\.+#", ".", USERVIEWED);
			$viewed = preg_replace("#^\.#", "", $viewed);
			$viewed = preg_replace("#\.$#", "", $viewed);
			$viewed = str_replace(".", ",", $viewed);
		}
		if($viewed != "")
		{
			$viewed = " AND thread_id NOT IN (".$viewed.")";
		}
		
		if($sql->db_Select('forum_t', 'distinct thread_forum_id', "thread_parent=0 AND thread_lastpost > '".USERLV."' {$viewed}"))
		{
			while($row = $sql->db_Fetch())
			{
				$ret[] = $row['thread_forum_id'];
			}
			return $ret;
		}
		else
		{
			return FALSE;
		}
	}

	function thread_user($post_info)
	{
		if($post_info['user_name'])
		{
			return $post_info['user_name'];
		}
		else
		{
			$tmp = explode(".", $post_info['thread_user'], 2);
			return $tmp[1];
		}
	}

	function untrack($thread_id, $from)
	{
		$thread_id = intval($thread_id);
		global $sql;
		$tmp = ereg_replace("-".$thread_id."-", "", USERREALM);
		return $sql->db_Update("user", "user_realm='$tmp' WHERE user_id='".USERID."' ");
	}

	function track($thread_id, $from)
	{
		$thread_id = intval($thread_id);
		global $sql;
		return $sql->db_Update("user", "user_realm='".USERREALM."-".$thread_id."-' WHERE user_id='".USERID."' ");
	}

	function forum_get($forum_id)
	{
		$forum_id = intval($forum_id);
		$qry = "
		SELECT f.*, fp.forum_class as parent_class, fp.forum_postclass as parent_postclass, sp.forum_name AS sub_parent FROM #forum AS f
		LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent
		LEFT JOIN #forum AS sp ON f.forum_sub = sp.forum_id AND f.forum_sub > 0
		WHERE f.forum_id = {$forum_id}
		";
		global $sql;
		if ($sql->db_Select_gen($qry))
		{
			return $sql->db_Fetch();
		}
		return FALSE;
	}

	function thread_update($thread_id, $newvals)
	{
		global $sql;
		foreach($newvals as $var => $val)
		{
			$newvalArray[] = "{$var} = '{$val}'";
		}
		$newString = implode(', ', $newvalArray)." WHERE thread_id={$thread_id}";
		return $sql->db_Update('forum_t', $newString);
	}

	function forum_get_topics($forum_id, $from, $view)
	{
		$forum_id = intval($forum_id);
		global $sql;
		$qry = "
		SELECT t.*, u.user_name, lpu.user_name AS lastpost_username from #forum_t as t
		LEFT JOIN #user AS u ON t.thread_user = u.user_id
		LEFT JOIN #user AS lpu ON t.thread_lastuser = lpu.user_id
		WHERE t.thread_forum_id = $forum_id AND t.thread_parent = 0
		ORDER BY
		t.thread_s DESC,
		t.thread_lastpost DESC,
		t.thread_datestamp DESC
		LIMIT {$from},{$view}
		";
		$ret = array();
		if ($sql->db_Select_gen($qry))
		{
			while ($row = $sql->db_Fetch())
			{
				$ret[] = $row;
			}
		}
		return $ret;
	}

	function thread_get_lastpost($forum_id)
	{
		$forum_id = intval($forum_id);
		global $sql;
		if ($sql->db_Count('forum_t', '(*)', "WHERE thread_parent = {$forum_id} "))
		{
			$where = "WHERE t.thread_parent = $forum_id ";
		}
		else
		{
			$where = "WHERE t.thread_id = $forum_id ";
		}
		$qry = "
		SELECT t.thread_user, t.thread_datestamp, u.user_name FROM #forum_t AS t
		LEFT JOIN #user AS u ON t.thread_user = u.user_id
		{$where}
		ORDER BY t.thread_datestamp DESC	LIMIT 0,1
		";
		if ($sql->db_Select_gen($qry))
		{
			return $sql->db_Fetch();
		}
		return FALSE;
	}

	function forum_get_topic_count($forum_id)
	{
		global $sql;
		return $sql->db_Count("forum_t", "(*)", " WHERE thread_forum_id='".$forum_id."' AND thread_parent='0' ");
	}

	function thread_getnext($thread_id, $forum_id, $from = 0, $limit = 100)
	{
		global $sql;
		$forum_id = intval($forum_id);
		global $sql;
		$ftab = MPREFIX.'forum_t';
		while (!$found)
		{
			$qry = "
			SELECT t.thread_id from #forum_t AS t
			WHERE t.thread_forum_id = $forum_id
			AND t.thread_parent = 0
			ORDER BY
			t.thread_s DESC,
			t.thread_lastpost DESC,
			t.thread_datestamp DESC
			LIMIT {$from}, {$limit}
			";
			if ($sql->db_Select_gen($qry))
			{
				$i = 0;
				while ($row = $sql->db_Fetch())
				{
					$threadList[$i++] = $row['thread_id'];
				}
				$id = array_search($thread_id, $threadList);
				if ($id)
				{
					if ($id != 99)
					{
						return $threadList[$id+1];
					}
					else
					{
						return $this->thread_getnext($thread_id, $forum_id, $from+99, 2);
					}
				}
			}
			else
			{
				return FALSE;
			}
			$from += 100;
		}
	}

	function thread_getprev($thread_id, $forum_id, $from = 0, $limit = 100)
	{
		global $sql;
		$forum_id = intval($forum_id);
		global $sql;
		$ftab = MPREFIX.'forum_t';
		while (!$found)
		{
			$qry = "
			SELECT t.thread_id from #forum_t AS t
			WHERE t.thread_forum_id = $forum_id
			AND t.thread_parent = 0
			ORDER BY
			t.thread_s DESC,
			t.thread_lastpost DESC,
			t.thread_datestamp DESC
			LIMIT {$from}, {$limit}
			";
			if ($sql->db_Select_gen($qry))
			{
				$i = 0;
				while ($row = $sql->db_Fetch())
				{
					$threadList[$i++] = $row['thread_id'];
				}
				$id = array_search($thread_id, $threadList);
				if ($id !== FALSE)
				{
					if ($id != 0)
					{
						return $threadList[$id-1];
					}
					else
					{
						if ($from == 0)
						{
							return FALSE;
						}
						return $this->thread_getprev($thread_id, $forum_id, $from-1, 2);
					}
				}
			}
			else
			{
				return FALSE;
			}
			$from += 100;
		}
	}

	function thread_get($thread_id, $start = 0, $limit = 10)
	{
		$thread_id = intval($thread_id);
		global $sql;
		$ftab = MPREFIX.'forum_t';
		$utab = MPREFIX.'user';

		if ($start === "last")
		{
			$tcount = $this->thread_count($thread_id);
			$start = max(0, $tcount-$limit);
		}
		$start = max(0, $start);
		if ($start != 0)
		{
			$array_start = 0;
		}
		else
		{
			$limit--;
			$array_start = 1;
		}
		$sortdir = "ASC";

		$qry = "
		SELECT t.*, u.* FROM #forum_t as t
		LEFT JOIN #user AS u
		ON t.thread_user = u.user_id
		WHERE t.thread_parent = $thread_id
		ORDER by t.thread_datestamp {$sortdir}
		LIMIT {$start},".($limit);
		$ret = array();
		if ($sql->db_Select_gen($qry))
		{
			$i = $array_start;
			while ($row = $sql->db_Fetch())
			{
				$ret[$i] = $row;
				$i++;
			}
		}
		$qry = "
		SELECT t.*,u.* from #forum_t AS t
		LEFT JOIN #user AS u
		ON t.thread_user = u.user_id
		WHERE t.thread_id = $thread_id
		LIMIT 0,1
		";
		if ($sql->db_Select_gen($qry))
		{
			$row = $sql->db_Fetch();
			$ret['head'] = $row;
			if (!array_key_exists(0, $ret))
			{
				$ret[0] = $row;
			}
		}
		return $ret;
	}

	function thread_count($thread_id)
	{
		$thread_id = intval($thread_id);
		global $sql;
		return $sql->db_Count('forum_t', '(*)', "WHERE thread_parent = $thread_id")+1;
	}

	function thread_count_list($thread_list)
	{
		global $sql;
		$qry = "
		SELECT t.thread_parent, t.COUNT(*) as thread_replies
		FROM #forum_t AS t
		WHERE t.thread_parent
		IN {$thread_list}
		GROUP BY t.thread_parent
		";
		if ($sql->db_Select_gen($qry))
		{
			while ($row = $sql->db_Fetch())
			{
				$ret[$row['thread_parent']] = $row['thread_replies'];
			}
		}
		return $ret;
	}

	function thread_incview($thread_id)
	{
		$thread_id = intval($thread_id);
		global $sql;
		return $sql->db_Update("forum_t", "thread_views=thread_views+1 WHERE thread_id='$thread_id' ");
	}


	function thread_get_postinfo($thread_id, $head = FALSE)
	{
		$thread_id = intval($thread_id);
		global $sql;
		$ret = array();
		$qry = "
		SELECT t.*, u.user_name, u.user_id, u.user_email from #forum_t AS t
		LEFT JOIN #user AS u
		ON t.thread_user = u.user_id
		WHERE t.thread_id = $thread_id
		LIMIT 0,1
		";
		if ($sql->db_Select_gen($qry))
		{
			$ret[0] = $sql->db_Fetch();
		}
		else
		{
			return FALSE;
		}
		if ($head == FALSE)
		{
			return $ret;
		}
		$parent_id = $ret[0]['thread_parent'];
		if ($parent_id == 0)
		{
			$ret['head'] = $ret[0];
		}
		else
		{
			$qry = "
			SELECT t.*, u.user_name, u.user_id from #forum_t AS t
			LEFT JOIN #user AS u
			ON t.thread_user = u.user_id
			WHERE t.thread_id = {$parent_id}
			LIMIT 0,1
			";
			if ($sql->db_Select_gen($qry))
			{
				$row = $sql->db_Fetch();
				$ret['head'] = $row;
			}
		}
		return $ret;
	}

	function thread_insert($thread_name, $thread_thread, $thread_forum_id, $thread_parent, $thread_poster, $thread_active, $thread_s)
	{
		$post_time = time();
		global $sql, $tp, $pref, $e107;
		$ip = $e107->getip();
		//Check for duplicate post
		if ($sql->db_Count('forum_t', '(*)', "WHERE thread_thread='$thread_thread' and thread_datestamp > ".($post_time - 180)))
		{
			return -1;
		}
		
		$post_user = $thread_poster['post_userid'].".".$thread_poster['post_user_name'];
		$thread_post_user = $post_user;
		if($thread_poster['post_userid'] == 0)
		{
			$thread_post_user = $post_user.chr(1).$ip;
		}

		$post_last_user = ($thread_parent ? "" : $post_user);
		$vals = "'0', '{$thread_name}', '{$thread_thread}', '{$thread_forum_id}', '{$post_time}', '{$thread_parent}', '{$thread_post_user}', '0', '$thread_active', '$post_time', '$thread_s', '0', '{$post_last_user}', '0'";
		$newthread_id = $sql->db_Insert('forum_t', $vals);
		if(!$newthread_id)
		{
			echo "thread creation failed! <br />
			Values sent were: ".htmlentities($vals)."<br /><br />Please save these values for dev team for troubleshooting.";
			exit;
		}

		// Increment user thread count and set user as viewed this thread
		if (USER)
		{
			$sql->db_Update('user', "user_forums=user_forums+1, user_viewed='".USERVIEWED.".{$newthread_id}.' WHERE user_id='".USERID."' ");
		}

		//If post is a reply
		if ($thread_parent)
		{
			$forum_lp_info = $post_time.".".$thread_parent;
			$gen = new convert;
			// Update main forum with last post info and increment reply count
			$sql->db_Update('forum', "forum_replies=forum_replies+1, forum_lastpost_user='{$post_user}', forum_lastpost_info = '{$forum_lp_info}' WHERE forum_id='$thread_forum_id' ");

			// Update head post with last post info and increment reply count
			$sql->db_Update('forum_t', "thread_lastpost={$post_time}, thread_lastuser='{$post_user}', thread_total_replies=thread_total_replies+1 WHERE thread_id = {$thread_parent}");

			$parent_thread = $this->thread_get_postinfo($thread_parent);
			global $PLUGINS_DIRECTORY;
			$datestamp = $gen->convert_date($post_time, "long");
			$email_post = $tp->toHTML($thread_thread, TRUE);
			$mail_link = SITEURL.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$thread_parent.".last";
			if(!isset($pref['forum_eprefix']))
			{
				$pref['forum_eprefix'] = "[forum]";
			}
			//   Send email to orinator of flagged
			if ($parent_thread[0]['thread_active'] == 99)
			{
				$gen = new convert;
				$email_name = $parent_thread[0]['user_name'];
				$message = LAN_384.SITENAME.".\n\n". LAN_382.$datestamp."\n". LAN_94.": ".$lp_name."\n\n". LAN_385.$email_post."\n\n". LAN_383."\n\n".$mail_link;
				include_once(e_HANDLER."mail.php");
				sendemail($parent_thread[0]['user_email'], $pref['forum_eprefix']." '".$thread_name."', ".LAN_381.SITENAME, $message);
			}

			//   Send email to all users tracking thread
			if ($sql->db_Select("user", "*", "user_realm REGEXP('-".$thread_parent."-') "))
			{
				include_once(e_HANDLER.'mail.php');
				while ($row = $sql->db_Fetch())
				{
					$message = LAN_385.SITENAME.".\n\n". LAN_382.$gen->convert_date(time(), "long")."\n". LAN_94.": ".$lp_name."\n\n". LAN_385.$email_post."\n\n". LAN_383."\n\n".$mail_link;
					if ($row['user_email'])
					{
						sendemail($row['user_email'], $pref['forum_eprefix']." '".$thread_name."', ".LAN_381.SITENAME, $message);
					}
				}
			}
		}
		else
		{
			//post is a new thread
			$forum_lp_info = $post_time.".".$newthread_id;
			$sql->db_Update('forum', "forum_threads=forum_threads+1, forum_lastpost_user='{$post_user}', forum_lastpost_info = '{$forum_lp_info}' WHERE forum_id='$thread_forum_id' ");
		}
		return $newthread_id;
	}

	function post_getnew($count)
	{
		global $sql;
		$qry = "
		SELECT ft.*, fp.thread_name as post_subject, fp.thread_total_replies as replies, u.user_id, u.user_name, f.forum_class
		FROM #forum_t AS ft
		LEFT JOIN #forum_t as fp ON fp.thread_id = ft.thread_parent
		LEFT JOIN #user as u ON u.user_id = ft.thread_user
		LEFT JOIN #forum as f ON f.forum_id = ft.thread_forum_id
		WHERE ft.thread_datestamp > ".USERLV. "
		AND
		f.forum_class IN (".USERCLASS_LIST.")
		ORDER BY ft.thread_datestamp DESC LIMIT 0, 50
		";
		if($sql->db_Select_gen($qry))
		{
			$ret = $sql->db_getList();
		}
		return $ret;
	}

	function forum_prune($type, $days, $forumArray)
	{
		global $sql;
		$prunedate = time() - ($days * 86400);
		$forumList = implode(",", $forumArray);

		if($type == 'delete')
		{
			//Get list of threads to prune
			if ($sql->db_Select("forum_t", "thread_id", "thread_lastpost < $prunedate AND thread_parent=0 AND thread_s != 1 AND thread_forum_id IN ({$forumList})"))
			{
				$threadList = $sql->db_getList();
				foreach($threadList as $thread)
				{
					//Delete all replies
					$reply_count += $sql->db_Delete("forum_t", "thread_parent='{$thread['thread_id']}'");
					//Delete thread
					$thread_count += $sql->db_Delete("forum_t", "thread_id = '{$thread['thread_id']}'");
					//Delete poll if there is one
					$sql->db_Delete("poll", "poll_datestamp='{$thread['thread_id']}");
				}
				foreach($forumArray as $fid)
				{
					$this->update_lastpost('forum', $fid);
					$this->forum_update_counts($fid);
				}
				return FORLAN_8." ( ".$thread_count." ".FORLAN_92.", ".$reply_count." ".FORLAN_93." )";
			}
			else
			{
				return FORLAN_9;
			}
		}
		else
		{
			$pruned = $sql->db_Update("forum_t", "thread_active=0 WHERE thread_lastpost < $prunedate AND thread_parent=0 AND thread_forum_id IN ({$forumList})");
			return FORLAN_8." ".$pruned." ".FORLAN_91;
		}
	}

	function forum_update_counts($forumID)
	{
		global $sql;
		if($forumID == 'all')
		{
			$sql->db_Select('forum', 'forum_id', 'forum_parent != 0');
			$flist = $sql->db_getList();
			foreach($flist as $f)
			{
				$this->forum_update_counts($f['forum_id']);
			}
			return;
		}
		$forumID = intval($forumID);
		$threads = $sql->db_Count("forum_t", "(*)", "WHERE thread_forum_id=$forumID AND thread_parent = 0");
		$replies = $sql->db_Count("forum_t", "(*)", "WHERE thread_forum_id=$forumID AND thread_parent != 0");
		$sql->db_Update("forum", "forum_threads='$threads', forum_replies='$replies' WHERE forum_id='$forumID'");
	}
}


/**
* @return string path to and filename of forum icon image
*
* @param string $filename  filename of forum image
* @param string $eMLANG_folder if specified, indicates its a multilanguage image being processed and
*       gives the subfolder of the image path to the eMLANG_path() function,
*       default = FALSE
* @param string $eMLANG_pref  if specified, indicates that $filename may be overridden by the
*       $pref with $eMLANG_pref as its key if that pref is TRUE, default = FALSE
*
* @desc checks for the existence of a forum icon image in the themes forum folder and if it is found
*  returns the path and filename of that file, otherwise it returns the path and filename of the
*  default forum icon image in e_IMAGES. The additional $eMLANG args if specfied switch the process
*  to the sister multi-language function eMLANG_path().
*
* @access public
*/
function img_path($filename, $eMLANG_folder = FALSE, $eMLANG_pref = FALSE)
{
	global $pref;
	if ($eMLANG_folder)
	{
		return eMLANG_path($filename, $eMLANG_folder);
	}
	else
	{
		if(file_exists(THEME.'forum/'.$filename))
		{
			$image = THEME.'forum/'.$filename;
		}
		else
		{
			if(defined("IMODE"))
			{
				$image = e_PLUGIN."forum/images/".IMODE."/".$filename;
			}
			else
			{
				$image = e_PLUGIN."forum/images/lite/".$filename;
			}
		}
	}
	return $image;
}

function eMLANG_path($file_name, $sub_folder)
{
	if (file_exists(THEME.$sub_folder."/".e_LANGUAGE."/".$file_name))
	{
		return THEME.$sub_folder."/".e_LANGUAGE."/".$file_name;
	}
	if (file_exists(THEME.$sub_folder."/".$file_name))
	{
		return THEME.$sub_folder."/".$file_name;
	}
	if (file_exists(e_IMAGE.$sub_folder."/".e_LANGUAGE."/".$file_name))
	{
		return e_IMAGE.$sub_folder."/".e_LANGUAGE."/".$file_name;
	}
	return e_PLUGIN.$sub_folder."/images/".$file_name;
}

if (file_exists(THEME.'forum/forum_icons_template.php'))
{
	require_once(THEME.'forum/forum_icons_template.php');
}
else
{
	require_once(e_PLUGIN.'forum/templates/forum_icons_template.php');
}
?>