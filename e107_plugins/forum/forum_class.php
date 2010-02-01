<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Message Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_class.php,v $
 * $Revision: 1.42 $
 * $Date: 2010-02-01 03:41:58 $
 * $Author: mcfly_e107 $
 *
*/

if (!defined('e107_INIT')) { exit; }

class e107forum
{
	var $permList = array();
	var $fieldTypes = array();
	var $userViewed = array();
	var $modArray = array();
	var $e107;

	function e107forum()
	{
		$this->loadPermList();
		$this->fieldTypes['forum_post']['post_user'] 			= 'int';
		$this->fieldTypes['forum_post']['post_forum'] 			= 'int';
		$this->fieldTypes['forum_post']['post_datestamp'] 		= 'int';
		$this->fieldTypes['forum_post']['post_edit_datestamp']	= 'int';
		$this->fieldTypes['forum_post']['post_edit_user']		= 'int';
		$this->fieldTypes['forum_post']['post_thread'] 			= 'int';
		$this->fieldTypes['forum_post']['post_options'] 		= 'escape';
		$this->fieldTypes['forum_post']['post_attachments'] 	= 'escape';

		$this->fieldTypes['forum_thread']['thread_user'] 		= 'int';
		$this->fieldTypes['forum_thread']['thread_lastpost'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_lastuser'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_sticky'] 		= 'int';
		$this->fieldTypes['forum_thread']['thread_forum_id'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_active'] 		= 'int';
		$this->fieldTypes['forum_thread']['thread_datestamp']	= 'int';
		$this->fieldTypes['forum_thread']['thread_views'] 		= 'int';
		$this->fieldTypes['forum_thread']['thread_replies'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_options'] 	= 'escape';

		$this->fieldTypes['forum']['forum_lastpost_user']	 	= 'int';
		$this->e107 = e107::getInstance();
	}

	function loadPermList()
	{
		global $e107;
		if($tmp = $e107->ecache->retrieve_sys('forum_perms'))
		{
			$this->permList = $e107->arrayStorage->ReadArray($tmp);
		}
		else
		{
			$this->getForumPermList();
			$tmp = $e107->arrayStorage->WriteArray($this->permList, false);
			$e107->ecache->set_sys('forum_perms', $tmp);

		}
		unset($tmp);
	}


	function getForumPermList()
	{
		global $e107;

		$this->permList = array();
		$qryList = array();

		$qryList[view] = "
		SELECT f.forum_id
		FROM `#forum` AS f
		LEFT JOIN `#forum` AS fp ON f.forum_parent = fp.forum_id AND fp.forum_class IN (".USERCLASS_LIST.")
		WHERE f.forum_class IN (".USERCLASS_LIST.") AND f.forum_parent != 0 AND fp.forum_id IS NOT NULL
		";

		$qryList[post] = "
		SELECT f.forum_id
		FROM `#forum` AS f
		LEFT JOIN `#forum` AS fp ON f.forum_parent = fp.forum_id AND fp.forum_postclass IN (".USERCLASS_LIST.")
		WHERE f.forum_postclass IN (".USERCLASS_LIST.") AND f.forum_parent != 0 AND fp.forum_id IS NOT NULL
		";

		$qryList[thread] = "
		SELECT f.forum_id
		FROM `#forum` AS f
		LEFT JOIN `#forum` AS fp ON f.forum_parent = fp.forum_id AND fp.forum_threadclass IN (".USERCLASS_LIST.")
		WHERE f.forum_threadclass IN (".USERCLASS_LIST.") AND f.forum_parent != 0 AND fp.forum_id IS NOT NULL
		";

		foreach($qryList as $key => $qry)
		{
			if($e107->sql->db_Select_gen($qry))
			{
				while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
				{
					$this->permList[$key][] = $row['forum_id'];
				}
			}
		}
	}

	function checkPerm($forumId, $type='view')
	{
		return (in_array($forumId, $this->permList[$type]));
	}

	function threadViewed($threadId)
	{
		$e107 = e107::getInstance();
		if(!$this->userViewed)
		{
			if(isset($e107->currentUser['user_plugin_forum_viewed']))
			{
				$this->userViewed = explode(',', $e107->currentUser['user_plugin_forum_viewed']);
			}
		}
		return (is_array($this->userViewed) && in_array($threadId, $this->userViewed));
	}

	function getTrackedThreadList($id, $retType = 'array')
	{
		$e107 = e107::getInstance();
		$id = (int)$id;
		if($e107->sql->db_Select('forum_track', 'track_thread', 'track_userid = '.$id))
		{
			while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[] = $row['track_thread'];
			}
			return ($retType == 'array' ? $ret : implode(',', $ret));
		}
		return false;
	}

	/*
	 * Add a post to the db.
	 *
	 * If threadinfo is given, then we're adding a new thread.
	 * We must get thread_id to provide to postInfo after insertion
	*/
	function postAdd($postInfo, $updateThread = true, $updateForum = true)
	{
//		var_dump($postInfo);
		//Future option, will just set to true here
		$addUserPostCount = true;
		$result = false;

		$e107 = e107::getInstance();
		$info = array();
		$info['_FIELD_TYPES'] = $this->fieldTypes['forum_post'];
		$info['data'] = $postInfo;
		$postId = $e107->sql->db_Insert('forum_post', $info);
		$forumInfo = array();

		if($postId && $updateThread)
		{
			$threadInfo = array();
			if(varset($postInfo['post_user']))
			{
				$threadInfo['thread_lastuser'] = $postInfo['post_user'];
				$threadInfo['thread_lastuser_anon'] = '_NULL_';
				$forumInfo['forum_lastpost_user'] = $postInfo['post_user'];
				$forumInfo['forum_lastpost_user_anon'] = '_NULL_';
			}
			else
			{
				$threadInfo['thread_lastuser'] = 0;
				$threadInfo['thread_lastuser_anon'] = $postInfo['post_user_anon'];
				$forumInfo['forum_lastpost_user'] = 0;
				$forumInfo['forum_lastpost_user_anon'] = $postInfo['post_user_anon'];
			}
			$threadInfo['thread_lastpost'] = $postInfo['post_datestamp'];
			$threadInfo['thread_total_replies'] = 'thread_total_replies + 1';

			$info = array();
			$info['data'] = $threadInfo;
			$info['WHERE'] = 'thread_id = '.$postInfo['post_thread'];
			$info['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];
			$info['_FIELD_TYPES']['thread_total_replies'] = 'cmd';

			$result = $e107->sql->db_Update('forum_thread', $info);

		}

		if(($result || !$updateThread) && $updateForum)
		{
			if(varset($postInfo['post_user']))
			{
				$forumInfo['forum_lastpost_user'] = $postInfo['post_user'];
				$forumInfo['forum_lastpost_user_anon'] = '_NULL_';
			}
			else
			{
				$forumInfo['forum_lastpost_user'] = 0;
				$forumInfo['forum_lastpost_user_anon'] = $postInfo['post_user_anon'];
			}

			$info = array();
			//If we update the thread, then we assume it was a reply, otherwise we've added a reply only.
			$info['_FIELD_TYPES'] = $this->fieldTypes['forum'];
			if($updateThread)
			{
				$forumInfo['forum_replies'] = 'forum_replies+1';
				$info['_FIELD_TYPES']['forum_replies'] = 'cmd';
			}
			else
			{
				$forumInfo['forum_threads'] = 'forum_threads+1';
				$info['_FIELD_TYPES']['forum_threads'] = 'cmd';
			}
			$info['data'] = $forumInfo;
			$info['data']['forum_lastpost_info'] = $postInfo['post_datestamp'].'.'.$postInfo['post_thread'];
			$info['WHERE'] = 'forum_id = '.$postInfo['post_forum'];
			$result = $e107->sql->db_Update('forum', $info);
		}

		if($result && USER && $addUserPostCount)
		{
			$qry = '
			INSERT INTO `#user_extended` (user_extended_id, user_plugin_forum_posts)
			VALUES ('.USERID.', 1)
			ON DUPLICATE KEY UPDATE user_plugin_forum_posts = user_plugin_forum_posts + 1
			';
			$result = $e107->sql->db_Select_gen($qry);
		}
		return $postId;
	}

	function threadAdd($threadInfo, $postInfo)
	{
		$e107 = e107::getInstance();
		$info = array();
		$info['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];
		$info['data'] = $threadInfo;
		if($newThreadId = $e107->sql->db_Insert('forum_thread', $info))
		{
			$postInfo['post_thread'] = $newThreadId;
			$newPostId = $this->postAdd($postInfo, false);
			$this->threadMarkAsRead($newThreadId);
			return array('postid' => $newPostId, 'threadid' => $newThreadId);
		}
		return false;
	}

	function threadUpdate($threadId, $threadInfo)
	{
		$e107 = e107::getInstance();
		$info = array();
		$info['data'] = $threadInfo;
		$info['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];
		$info['WHERE'] = 'thread_id = '.(int)$threadId;
		$e107->sql->db_Update('forum_thread', $info);
	}

	function postUpdate($postId, $postInfo)
	{
		$e107 = e107::getInstance();
		$info = array();
		$info['data'] = $postInfo;
		$info['_FIELD_TYPES'] = $this->fieldTypes['forum_post'];
		$info['WHERE'] = 'post_id = '.(int)$postId;
		$e107->sql->db_Update('forum_post', $info);
	}

	function threadGet($id, $joinForum = true, $uid = USERID)
	{
		global $pref;
		$e107 = e107::getInstance();
		$id = (int)$id;
		$uid = (int)$uid;

		if($joinForum)
		{
			//TODO: Fix query to get only forum and parent info needed, with correct naming
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
		if($e107->sql->db_Select_gen($qry))
		{
			$tmp = $e107->sql->db_Fetch(MYSQL_ASSOC);
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

	function postGet($id, $start, $num)
	{
		$id = (int)$id;
		$ret = false;
		$e107 = e107::getInstance();
		if('post' === $start)
		{
			$qry = '
			SELECT u.user_name, t.thread_active, t.thread_datestamp, t.thread_name, p.* FROM `#forum_post` AS p
			LEFT JOIN `#forum_thread` AS t ON t.thread_id = p.post_thread
			LEFT JOIN `#user` AS u ON u.user_id = p.post_user
			WHERE p.post_id = '.$id;
		}
		else
		{
			$qry = "
				SELECT p.*,
				u.user_name, u.user_customtitle, u.user_hideemail, u.user_email, u.user_signature,
				u.user_admin, u.user_image, u.user_join, ue.user_plugin_forum_posts,
				eu.user_name AS edit_name
				FROM `#forum_post` AS p
				LEFT JOIN `#user` AS u ON p.post_user = u.user_id
				LEFT JOIN `#user` AS eu ON p.post_edit_user IS NOT NULL AND p.post_edit_user = eu.user_id
				LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = p.post_user
				WHERE p.post_thread = {$id}
				ORDER BY p.post_datestamp ASC
				LIMIT {$start}, {$num}
			";
		}
		if($e107->sql->db_Select_gen($qry))
		{
			$ret = array();
			while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[] = $row;
			}
		}
		if('post' === $start) { return $ret[0]; }
		return $ret;
	}


	function threadGetUserPostcount($threadId)
	{
		$threadId = (int)$threadId;
		$e107 = e107::getInstance();
		$ret = false;
		$qry = "
		SELECT post_user, count(post_user) AS post_count FROM `#forum_post`
		WHERE post_thread = {$threadId} AND post_user IS NOT NULL
		GROUP BY post_user
		";
		if($e107->sql->db_Select_gen($qry))
		{
			$ret = array();
			while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[$row['post_user']] = $row['post_count'];
			}
		}
		return $ret;
	}

	function threadGetUserViewed($uid = USERID)
	{
		$e107 = e107::getInstance();
		if($uid == USERID)
		{
			$viewed = $e107->currentUser['user_plugin_forum_viewed'];
		}
		else
		{
			$tmp = get_user_data($uid);
			$viewed = $tmp['user_plugin_forum_viewed'];
			unset($tmp);
		}
		return explode(',', $viewed);
	}

	function postDeleteAttachments($type = 'post', $id='', $f='')
	{
		$e107 = e107::getInstance();
		$id = (int)$id;
		if(!$id) { return; }
		if($type == 'thread')
		{
			if(!$e107->sql->db_Select('forum_post', 'post_id', 'post_attachments IS NOT NULL'))
			{
				return true;
			}
			$postList = array();
			while($row = $e107->sql->dbFetch(MYSQL_ASSOC))
			{
				$postList[] = $row['post_id'];
			}
			foreach($postList as $postId)
			{
				$this->postDeleteAttachment('post', $postId);
			}
		}
		if($type == 'post')
		{
			if(!$e107->sql->db_Select('forum_post', 'post_attachments', 'post_id = '.$id))
			{
				return true;
			}
			$tmp = $e107->sql->db_Fetch(MYSQL_ASSOC);
			$attachments = explode(',', $tmp['post_attachments']);
			foreach($attachments as $k => $a)
			{
				$info = explode('*', $a);
				if('' == $f || $info[1] == $f)
				{
					$fname = e_PLUGIN."forum/attachments/{$info[1]}";
					@unlink($fname);

					//If attachment is an image and there is a thumb, remove it
					if('img' == $info[0] && $info[2])
					{
						$fname = e_PLUGIN."forum/attachments/thumb/{$info[2]}";
						@unlink($fname);
					}
				}
				unset($attachments[$k]);
			}
			$tmp = array();
			if(count($attachments))
			{
				$tmp['post_attachments'] = implode(',', $attachments);
			}
			else
			{
				$tmp['post_attachments'] = '_NULL_';
			}
			$info = array();
			$info['data'] = $tmp;
			$info['_FILE_TYPES']['post_attachments'] = 'escape';
			$info['WHERE'] = 'post_id = '.$id;
			$e107->sql->db_update('forum_post', $info);
		}
	}

	/**
	 * Given threadId and postId, determine which number of post in thread the postid is
	 *
	*/
	function postGetPostNum($threadId, $postId)
	{
		$threadId = (int)$threadId;
		$postId = (int)$postId;
		$e107 = e107::getInstance();
		return $e107->sql->db_Count('forum_post', '(*)', "WHERE post_id <= {$postId} AND post_thread = {$threadId} ORDER BY post_id ASC");
	}

	function forumUpdateLastpost($type, $id, $updateThreads = false)
	{
		global $sql, $tp;
		$sql2 = new db;
		if ($type == 'thread')
		{
			$id = (int)$id;
			$lpInfo = $this->threadGetLastpost($id);
			$tmp = array();
			if($lpInfo['user_name'])
			{
				$tmp['thread_lastuser'] = $lpInfo['post_user'];
				$tmp['thread_lastuser_anon'] = '_NULL_';
			}
			else
			{
				$tmp['thread_lastuser'] = 0;
				$tmp['thread_lastuser_anon'] = ($lpInfo['post_user_anon'] ? $lpInfo['post_user_anon'] : 'Anonymous');
			}
			$tmp['thread_lastpost'] = $lpInfo['post_datestamp'];
			$info = array();
			$info['data'] = $tmp;
			$info['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];
			$info['WHERE'] = 'thread_id = '.$id;
			$sql->db_Update('forum_thread', $info);

			return $lpInfo;
		}
		if ($type == 'forum')
		{
			if ($id == 'all')
			{
				if ($sql->db_Select('forum', 'forum_id', 'forum_parent != 0'))
				{
					while ($row = $sql->db_Fetch(MYSQL_ASSOC))
					{
						$parentList[] = $row['forum_id'];
					}
					foreach($parentList as $id)
					{
						set_time_limit(60);
						$this->forumUpdateLastpost('forum', $id, $updateThreads);
					}
				}
			}
			else
			{
				$id = (int)$id;
				$lp_info = '';
				$lp_user = 'NULL';
				if($updateThreads == true)
				{
					if ($sql2->db_Select('forum_t', 'thread_id', "thread_forum_id = $id AND thread_parent = 0"))
					{
						while ($row = $sql2->db_Fetch(MYSQL_ASSOC))
						{
							set_time_limit(60);
							$this->forumUpdateLastpost('thread', $row['thread_id']);
						}
					}
				}
				if ($sql->db_Select('forum_thread', 'thread_id, thread_lastuser, thread_lastuser_anon, thread_datestamp', 'thread_forum_id='.$id.' ORDER BY thread_datestamp DESC LIMIT 1'))
				{
					$row = $sql->db_Fetch(MYSQL_ASSOC);
					$lp_info = $row['thread_datestamp'].'.'.$row['thread_id'];
					$lp_user = $row['thread_lastuser'];
				}
				if($row['thread_lastuser_anon'])
				{
					$sql->db_Update('forum', "forum_lastpost_user = 0, forum_lastpost_anon = '{$row['thread_lastuser_anon']}', forum_lastpost_info = '{$lp_info}' WHERE forum_id=".$id);
				}
				else
				{
					$sql->db_Update('forum', "forum_lastpost_user = {$lp_user}, forum_lastpost_user_anon = NULL, forum_lastpost_info = '{$lp_info}' WHERE forum_id=".$id);
				}
			}
		}
	}

	function forumMarkAsRead($forum_id)
	{
		$e107 = e107::getInstance();
		$extra = '';
		$newIdList = array();
		if ($forum_id !== 0)
		{
			$forum_id = (int)$forum_id;
			$flist = array();
			$flist[] = $forum_id;
			if($subList = $this->forumGetSubs($forum_id))
			{
				foreach($subList as $sub)
				{
					$flist[] = $sub['forum_id'];
				}
			}
			$forumList = implode(',', $flist);
			$extra = " AND thread_forum_id IN($forumList)";
		}
		$qry = 'thread_lastpost > '.USERLV.$extra;

		if ($e107->sql->db_Select('forum_thread', 'thread_id', $qry))
		{
			while ($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
		  		$newIdList[] = $row['thread_id'];
			}
			if(count($newIdList))
			{
				$this->threadMarkAsRead($newIdList);
			}
		}
		header('location:'.e_SELF);
		exit;
	}

	function threadMarkAsRead($threadId)
	{
		global $currentUser;
		$e107 = e107::getInstance();
		$_tmp = preg_split('#\,+#', $currentUser['user_plugin_forum_viewed']);
		if(!is_array($threadId)) { $threadId = array($threadId); }
		foreach($threadId as $tid)
		{
			$_tmp[] = (int)$tid;
		}
		$tmp = array_unique($tmp);
		$viewed = trim(implode(',', $_tmp), ',');
		return $e107->sql->db_Update('user_extended', "user_plugin_forum_viewed = '{$viewed}' WHERE user_extended_id = ".USERID);
	}

	function forum_getparents()
	{
		global $sql;
		if ($sql->db_Select('forum', '*', 'forum_parent=0 ORDER BY forum_order ASC'))
		{
			while ($row = $sql->db_Fetch(MYSQL_ASSOC)) {
				$ret[] = $row;
			}
			return $ret;
		}
		return FALSE;
	}

	function forumGetMods($uclass = e_UC_ADMIN, $force=false)
	{
		if(count($this->modArray) && !$force)
		{
			return $this->modArray;
		}
		if($uclass == e_UC_ADMIN || trim($uclass) == '')
		{
			$this->e107->sql->db_Select('user', 'user_id, user_name','user_admin = 1 ORDER BY user_name ASC');
			while($row = $this->e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$this->modArray[$row['user_id']] = $row['user_name'];
			}
		}
		else
		{
			$this->modArray = $this->e107->user_class->get_users_in_class($uclass, 'user_name', true);
		}
		return $this->modArray;
	}
	
	function isModerator($uid)
	{
		return ($uid && in_array($uid, array_keys($this->modArray)));
	}

	function forumGetForumList()
	{
		$e107 = e107::getInstance();
		$qry = '
		SELECT f.*, u.user_name FROM `#forum` AS f
		LEFT JOIN `#user` AS u ON f.forum_lastpost_user IS NOT NULL AND u.user_id = f.forum_lastpost_user
		ORDER BY f.forum_order ASC
		';
		if ($e107->sql->db_Select_gen($qry))
		{
			$ret = array();
			while ($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				if(!$row['forum_parent'])
				{
					$ret['parents'][] = $row;
				}
				elseif($row['forum_sub'])
				{
//					$ret['subs'][$row['forum_parent']][$row['forum_sub']][] = $row;
					$ret['subs'][$row['forum_sub']][] = $row;
				}
				else
				{
					$ret['forums'][$row['forum_parent']][] = $row;
				}
			}
			return $ret;
		}
		return false;
	}

	function forum_getforums($type = 'all')
	{
		global $sql;
		$qry = "
		SELECT f.*, u.user_name FROM #forum AS f
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(f.forum_lastpost_user,'.',1) = u.user_id
		WHERE forum_parent != 0 AND forum_sub = 0
		ORDER BY f.forum_order ASC
		";
		if ($sql->db_Select_gen($qry))
		{
			while ($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				if($type == 'all')
				{
					$ret[$row['forum_parent']][] = $row;
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

	function forumGetSubs($forum_id = '')
	{
		global $sql;
		$where = ($forum_id != '' && $forum_id != 'bysub' ? 'AND forum_sub = '.(int)$forum_id : '');
		$qry = "
		SELECT f.*, u.user_name FROM `#forum` AS f
		LEFT JOIN `#user` AS u ON f.forum_lastpost_user = u.user_id
		WHERE forum_sub != 0 {$where}
		ORDER BY f.forum_order ASC
		";
		if ($sql->db_Select_gen($qry))
		{
			while ($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				if($forum_id == '')
				{
					$ret[$row['forum_parent']][$row['forum_sub']][] = $row;
				}
				elseif($forum_id == 'bysub')
				{
					$ret[$row['forum_sub']][] = $row;
				}
				else
				{
					$ret[] = $row;
				}
			}
			return $ret;
		}
		return false;
	}

	/**
	* List of forums with unread threads
	*
	* Get a list of forum IDs that have unread threads.
	* If a forum is a subforum, also ensure the parent is in the list.
	*
	* @return 	type	description
	* @access 	public
	*/
	function forumGetUnreadForums()
	{
		if (!USER) {return false; }		// Can't determine new threads for non-logged in users
		$e107 = e107::getInstance();
		$viewed = '';

		if($e107->currentUser['user_plugin_forum_viewed'])
		{
			$viewed = " AND thread_id NOT IN (".$e107->currentUser['user_plugin_forum_viewed'].")";
		}

		$_newqry = 	'
		SELECT DISTINCT f.forum_sub, ft.thread_forum_id FROM `#forum_thread` AS ft
		LEFT JOIN `#forum` AS f ON f.forum_id = ft.thread_forum_id
		WHERE ft.thread_lastpost > '.USERLV.' '.$viewed;
		if($e107->sql->db_Select_gen($_newqry))
		{
			while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[] = $row['thread_forum_id'];
				if($row['forum_sub'])
				{
					$ret[] = $row['forum_sub'];
				}
			}
			return $ret;
		}
		else
		{
			return false;
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

	function track($which, $uid, $threadId, $force=false)
	{
		$e107 = e107::getInstance();
		global $pref;

		if (!varsettrue($pref['forum_track']) && !$force) { return false; }

		$threadId = (int)$threadId;
		$uid = (int)$uid;
		$result = false;
		switch($which)
		{
			case 'add':
				$tmp = array();
				$tmp['data']['track_userid'] = $uid;
				$tmp['data']['track_thread'] = $threadId;
				$result = $e107->sql->db_Insert('forum_track', $tmp);
				unset($tmp);
				break;

			case 'delete':
			case 'del':
			 	$result = $e107->sql->db_Delete('forum_track', "`track_userid` = {$uid} AND `track_thread` = {$threadId}");
			 	break;

			case 'check':
				$result = $e107->sql->db_Count('forum_track', '(*)', "WHERE `track_userid` = {$uid} AND `track_thread` = {$threadId}");
				break;
		}
		return $result;
	}

	function forum_get($forum_id)
	{
		$forum_id = (int)$forum_id;
		$qry = "
		SELECT f.*, fp.forum_class as parent_class, fp.forum_name as parent_name, fp.forum_id as parent_id, fp.forum_postclass as parent_postclass, sp.forum_name AS sub_parent FROM #forum AS f
		LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent
		LEFT JOIN #forum AS sp ON f.forum_sub = sp.forum_id AND f.forum_sub > 0
		WHERE f.forum_id = {$forum_id}
		";
		global $sql;
		if ($sql->db_Select_gen($qry))
		{
			return $sql->db_Fetch(MYSQL_ASSOC);
		}
		return FALSE;
	}

	function forumGetAllowed($type='view')
	{
		global $sql;
		$forumList = implode(',', $this->permList[$type]);
		$qry = "
		SELECT forum_id, forum_name FROM `#forum`
		WHERE forum_id IN ({$forumList})
		";
		if ($sql->db_Select_gen($qry))
		{
			while($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[$row['forum_id']] = $row['forum_name'];
			}

		}
		return $ret;
	}

	function forumGetThreads($forumId, $from, $view)
	{
		$e107 = e107::getInstance();
		$forumId = (int)$forumId;
		$qry = "
		SELECT t.*, u.user_name, lpu.user_name AS lastpost_username from `#forum_thread` as t
		LEFT JOIN `#user` AS u ON t.thread_user = u.user_id
		LEFT JOIN `#user` AS lpu ON t.thread_lastuser = lpu.user_id
		WHERE t.thread_forum_id = {$forumId}
		ORDER BY
		t.thread_sticky DESC,
		t.thread_lastpost DESC
		LIMIT ".(int)$from.','.(int)$view;

		$ret = array();
		if ($e107->sql->db_Select_gen($qry))
		{
			while ($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[] = $row;
			}
		}
		return $ret;
	}

	function threadGetLastpost($id)
	{
		$e107 = e107::getInstance();
		$id = (int)$id;
		$qry = "
		SELECT p.post_user, p.post_user_anon, p.post_datestamp, p.post_thread, u.user_name FROM `#forum_post` AS p
		LEFT JOIN `#user` AS u ON u.user_id = p.post_user
		WHERE p.post_thread = {$id}
		ORDER BY p.post_datestamp DESC LIMIT 0,1
		";
		if ($e107->sql->db_Select_gen($qry))
		{
			return $e107->sql->db_Fetch(MYSQL_ASSOC);
		}
		return false;
	}

//	function forum_get_topic_count($forum_id)
//	{
//		$e107 = e107::getInstance();
//		return $e107->sql->db_Count('forum_thread', '(*)', 'WHERE thread_forum_id='.(int)$forum_id);
//	}

	function threadGetNextPrev($which, $threadId, $forumId, $lastpost)
	{
//		echo "threadid = $threadId <br />forum id = $forumId <br />";
//		return;
		$e107 = e107::getInstance();
		$threadId = (int)$threadId;
		$forumId = (int)$forumId;
		$lastpost = (int)$lastpost;

		if($which == 'next')
		{
			$dir = '<';
			$sort = 'ASC';
		}
		else
		{
			$dir = '>';
			$sort = 'DESC';
		}

		$qry = "
			SELECT thread_id from `#forum_thread`
			WHERE thread_forum_id = $forumId
			AND thread_lastpost {$dir} $lastpost
			ORDER BY
			thread_sticky DESC,
			thread_lastpost {$sort}
			LIMIT 1";
			if ($e107->sql->db_Select_gen($qry))
			{
				$row = $e107->sql->db_Fetch();
				return $row['thread_id'];

			}
			return false;
	}

	function threadIncView($id)
	{
		$e107 = e107::getInstance();
		$id = (int)$id;
		return $e107->sql->db_Update('forum_thread', 'thread_views=thread_views+1 WHERE thread_id='.$id);
	}

	function _forum_lp_update($lp_type, $lp_user, $lp_info, $lp_forum_id, $lp_forum_sub)
	{
		global $sql;
		$sql->db_Update('forum', "{$lp_type}={$lp_type}+1, forum_lastpost_user='{$lp_user}', forum_lastpost_info = '{$lp_info}' WHERE forum_id='".intval($lp_forum_id)."' ");
		if($lp_forum_sub)
		{
			$sql->db_Update('forum', "forum_lastpost_user = '{$lp_user}', forum_lastpost_info = '{$lp_info}' WHERE forum_id='".intval($lp_forum_sub)."' ");
		}
	}


	function threadGetNew($count = 50, $unread = true, $uid = USERID)
	{
		$e107 = e107::getInstance();
		$viewed = '';
		if($unread)
		{
			$viewed = implode(',', $this->threadGetUserViewed($uid));
			if($viewed != '')
			{
				$viewed = ' AND p.post_forum NOT IN ('.$viewed.')';
			}
		}

		$qry = "
		SELECT ft.*, fp.thread_name as post_subject, fp.thread_total_replies as replies, u.user_id, u.user_name, f.forum_class
		FROM #forum_t AS ft
		LEFT JOIN #forum_t as fp ON fp.thread_id = ft.thread_parent
		LEFT JOIN #user as u ON u.user_id = SUBSTRING_INDEX(ft.thread_user,'.',1)
		LEFT JOIN #forum as f ON f.forum_id = ft.thread_forum_id
		WHERE ft.thread_datestamp > ".USERLV. "
		AND f.forum_class IN (".USERCLASS_LIST.")
		{$viewed}
		ORDER BY ft.thread_datestamp DESC LIMIT 0, ".intval($count);

		$qry = "
		SELECT t.*, u.user_name FROM `#forum_thread` AS t
		LEFT JOIN `#user` AS u ON u.user_id = t.thread_lastuser
		WHERE t.thread_lastpost > ".USERLV. "
		{$viewed}
		ORDER BY t.thread_lastpost DESC LIMIT 0, ".(int)$count;


		if($e107->sql->db_Select_gen($qry))
		{
			$ret = $e107->sql->db_getList();
		}
		return $ret;
	}

	function forumPrune($type, $days, $forumArray)
	{
		$e107 = e107::getInstance();
		$prunedate = time() - (int)$days * 86400;
		$forumList = implode(',', $forumArray);

		if($type == 'delete')
		{
			//Get list of threads to prune
			if ($e107->sql->db_Select('forum_thread', 'thread_id', "thread_lastpost < {$prunedate} AND thread_sticky != 1 AND thread_forum_id IN ({$forumList})"))
			{
				$threadList = $e107->sql->db_getList();
				foreach($threadList as $thread)
				{
					$this->threadDelete($thread['thread_id'], false);
				}
				foreach($forumArray as $fid)
				{
					$this->forumUpdateLastpost('forum', $fid);
					$this->forumUpdateCounts($fid);
				}
				return FORLAN_8." ( ".$thread_count." ".FORLAN_92.", ".$reply_count." ".FORLAN_93." )";
			}
			else
			{
				return FORLAN_9;
			}
		}
		if($type == 'make_inactive')
		{
			$pruned = $e107->sql->db_Update('forum_thread', "thread_active=0 WHERE thread_lastpost < {$prunedate} thread_forum_id IN ({$forumList})");
			return FORLAN_8.' '.$pruned.' '.FORLAN_91;
		}
	}

	function forumUpdateCounts($forumId, $recalcThreads = false)
	{
		$e107 = e107::getInstance();
		if($forumId == 'all')
		{
			$e107->sql->db_Select('forum', 'forum_id', 'forum_parent != 0');
			$flist = $e107->sql->db_getList();
			foreach($flist as $f)
			{
				set_time_limit(60);
				$this->forumUpdateCounts($f['forum_id'], $recalcThreads);
			}
			return;
		}
		$forumId = (int)$forumId;
		$threads = $e107->sql->db_Count('forum_thread', '(*)', 'WHERE thread_forum_id='.$forumId);
		$replies = $e107->sql->db_Count('forum_post', '(*)', 'WHERE post_forum='.$forumId);
		$e107->sql->db_Update('forum', "forum_threads={$threads}, forum_replies={$replies} WHERE forum_id={$forumId}");
		if($recalcThreads == true)
		{
			set_time_limit(60);
			$e107->sql->db_Select('forum_post', 'post_thread, count(post_thread) AS replies', "post_forum={$forumId} GROUP BY post_thread");
			$tlist = $e107->sql->db_getList();
			foreach($tlist as $t)
			{
				$tid = $t['post_thread'];
				$replies = (int)$t['replies'];
				$e107->sql->db_Update('forum_thread', "thread_total_replies={$replies} WHERE thread_id={$tid}");
			}
		}
	}

	function getUserCounts()
	{
		global $sql;
		$qry = "
		SELECT post_user, count(post_user) AS cnt FROM `#forum_post`
		WHERE post_user > 0
		GROUP BY post_user
		";

		if($sql->db_Select_gen($qry))
		{
			$ret = array();
			while($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[$row['post_user']] = $row['cnt'];
			}
			return $ret;
		}
		return FALSE;
	}

	/*
	 * set bread crumb
	 * $forum_href override ONLY applies when template is missing FORUM_CRUMB
	 * $thread_title is needed for post-related breadcrumbs
	 */
	function set_crumb($forum_href=false, $thread_title='', &$templateVar)
	{
		$e107 = e107::getInstance();
		global $FORUM_CRUMB, $forumInfo, $thread;
		global $BREADCRUMB,$BACKLINK;  // Eventually we should deprecate BACKLINK

		if(!$forumInfo) { $forumInfo = $thread->threadInfo; }
//		var_dump($forumInfo);
//		var_dump($thread);

		if(is_array($FORUM_CRUMB))
		{
			$search 	= array('{SITENAME}', '{SITENAME_HREF}');
			$replace 	= array(SITENAME, "href='".$e107->url->getUrl('core:core', 'main', 'action=index')."'");
			$FORUM_CRUMB['sitename']['value'] = str_replace($search, $replace, $FORUM_CRUMB['sitename']['value']);

			$search 	= array('{FORUMS_TITLE}', '{FORUMS_HREF}');
			$replace 	= array(LAN_01, "href='".$e107->url->getUrl('forum', 'forum', 'func=main')."'");
			$FORUM_CRUMB['forums']['value'] = str_replace($search, $replace, $FORUM_CRUMB['forums']['value']);

			$search 	= '{PARENT_TITLE}';
			$replace 	= $e107->tp->toHTML($forumInfo['parent_name']);
			$FORUM_CRUMB['parent']['value'] = str_replace($search, $replace, $FORUM_CRUMB['parent']['value']);

			if($forum_info['forum_sub'])
			{
				$search 	= array('{SUBPARENT_TITLE}', '{SUBPARENT_HREF}');
				$replace 	= array(ltrim($forumInfo['sub_parent'], '*'), "href='".$e107->url->getUrl('forum', 'forum', "func=view&id={$forumInfo['forum_sub']}")."'");
				$FORUM_CRUMB['subparent']['value'] = str_replace($search, $replace, $FORUM_CRUMB['subparent']['value']);
			}
			else
			{
				$FORUM_CRUMB['subparent']['value'] = '';
			}

			$search 	= array('{FORUM_TITLE}', '{FORUM_HREF}');
			$replace 	= array(ltrim($forumInfo['forum_name'], '*'),"href='".$e107->url->getUrl('forum', 'forum', "func=view&id={$forumInfo['forum_id']}")."'");
			$FORUM_CRUMB['forum']['value'] = str_replace($search, $replace, $FORUM_CRUMB['forum']['value']);

			$search 	= array('{THREAD_TITLE}');
			$replace 	= array($thread->threadInfo['thread_name']);
			$FORUM_CRUMB['thread']['value'] = str_replace($search, $replace, $FORUM_CRUMB['thread']['value']);

			$FORUM_CRUMB['fieldlist'] = 'sitename,forums,parent,subparent,forum,thread';
			$BREADCRUMB = $e107->tp->parseTemplate('{BREADCRUMB=FORUM_CRUMB}', true);
		}
		else
		{
			$dfltsep = ' :: ';
			$BREADCRUMB = "<a class='forumlink' href='".e_BASE."index.php'>".SITENAME."</a>".$dfltsep."<a class='forumlink' href='".e_PLUGIN."forum/forum.php'>".LAN_01."</a>".$dfltsep;
			if($forum_info['sub_parent'])
			{
				$forum_sub_parent = (substr($forum_info['sub_parent'], 0, 1) == '*' ? substr($forum_info['sub_parent'], 1) : $forum_info['sub_parent']);
				$BREADCRUMB .= "<a class='forumlink' href='".e_PLUGIN."forum/forum_viewforum.php?{$forum_info['forum_sub']}'>{$forum_sub_parent}</a>".$dfltsep;
			}

			$tmpFname = $forum_info['forum_name'];
			if(substr($tmpFname, 0, 1) == "*") { $tmpFname = substr($tmpFname, 1); }
			if ($forum_href)
			{
				$BREADCRUMB .= "<a class='forumlink' href='".e_PLUGIN."forum/forum_viewforum.php?{$forum_info['forum_id']}'>".$tp->toHTML($tmpFname, TRUE, 'no_hook,emotes_off')."</a>";
			} else
			{
				$BREADCRUMB .= $tmpFname;
			}

			if(strlen($thread_title))
			{
				$BREADCRUMB .= $dfltsep.$thread_title;
			}
		}
		$BACKLINK = $BREADCRUMB;
		$templateVar->BREADCRUMB = $BREADCRUMB;
		$templateVar->BACKLINK = $BACKLINK;
		$templateVar->FORUM_CRUMB = $FORUM_CRUMB;
	}


	function threadDelete($threadId, $updateForumLastpost = true)
	{
		$e107 = e107::getInstance();
		if ($threadInfo = $this->threadGet($threadId))
		{
			// delete poll if there is one
			$e107->sql->db_Delete('poll', 'poll_datestamp='.$threadId);

			//decrement user post counts
			if ($postCount = $this->threadGetUserPostcount($threadId))
			{
				foreach ($postCount as $k => $v)
				{
					$e107->sql->db_Update('user_extended', 'user_plugin_forum_posts=GREATEST(user_plugin_forum_posts-'.$v.',0) WHERE user_id='.$k);
				}
			}

			// delete all posts
			$qry = 'SELECT post_id FROM `#forum_post` WHERE post_thread = '.$threadId;
			if($e107->sql->db_Select_gen($qry))
			{
				$postList = array();
				while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
				{
					$postList[] = $row['post_id'];
				}
				foreach($postList as $postId)
				{
					$this->postDelete($postId, false);
				}
			}

			// delete the thread itself
			$e107->sql->db_Delete('forum_thread', 'thread_id='.$threadId);

			//Delete any thread tracking
			$e107->sql->db_Delete('forum_track', 'track_thread='.$threadId);

			// update forum with correct thread/reply counts
			$e107->sql->db_Update('forum', "forum_threads=GREATEST(forum_threads-1,0), forum_replies=GREATEST(forum_replies-{$threadInfo['thread_total_replies']},0) WHERE forum_id=".$threadInfo['thread_forum_id']);

			if($updateForumLastpost)
			{
				// update lastpost info
				$this->forumUpdateLastpost('forum', $threadInfo['thread_forum_id']);
			}
			return $threadInfo['thread_total_replies'];
		}
	}

	function postDelete($postId, $updateCounts = true)
	{
		$postId = (int)$postId;
		$e107 = e107::getInstance();
		if(!$e107->sql->db_Select('forum_post', '*', 'post_id = '.$postId))
		{
			echo 'NOT FOUND!'; return;
		}
		$row = $e107->sql->db_Fetch(MYSQL_ASSOC);

		//delete attachments if they exist
		if($row['post_attachments'])
		{
			$this->postDeleteAttachments('post', $postId);
		}

		// delete post
		$e107->sql->db_Delete('forum_post', 'post_id='.$postId);

		if($updateCounts)
		{
			//decrement user post counts
			if ($row['post_user'])
			{
				$e107->sql->db_Update('user_extended', 'user_plugin_forum_posts=GREATEST(user_plugin_forum_posts-1,0) WHERE user_id='.$row['post_user']);
			}

			// update thread with correct reply counts
			$e107->sql->db_Update('forum_thread', "thread_total_replies=GREATEST(thread_total_replies-1,0) WHERE thread_id=".$row['post_thread']);

			// update forum with correct thread/reply counts
			$e107->sql->db_Update('forum', "forum_replies=GREATEST(forum_replies-1,0) WHERE forum_id=".$row['post_forum']);

			// update thread lastpost info
			$this->forumUpdateLastpost('thread', $row['post_thread']);

			// update forum lastpost info
			$this->forumUpdateLastpost('forum', $row['post_forum']);
		}
		return $threadInfo['thread_total_replies'];
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
function img_path($filename)
{
	global $pref;

	$multilang = array('reply.png','newthread.png','moderator.png','main_admin.png','admin.png');
	$ML = (in_array($filename,$multilang)) ? TRUE : FALSE;

		if(file_exists(THEME.'forum/'.$filename) || is_readable(THEME.'forum/'.e_LANGUAGE.'_'.$filename))
		{
			$image = ($ML && is_readable(THEME.'forum/'.e_LANGUAGE.'_'.$filename)) ? THEME.'forum/'.e_LANGUAGE."_".$filename :  THEME.'forum/'.$filename;
		}
		else
		{
			if(defined('IMODE'))
			{
				if($ML)
				{
                	$image = (is_readable(e_PLUGIN.'forum/images/'.IMODE.'/'.e_LANGUAGE.'_'.$filename)) ? e_PLUGIN.'forum/images/'.IMODE.'/'.e_LANGUAGE.'_'.$filename : e_PLUGIN.'forum/images/'.IMODE.'/English_'.$filename;
				}
				else
				{
                	$image = e_PLUGIN.'forum/images/'.IMODE.'/'.$filename;
				}
			}
			else
			{
				if($ML)
				{
					$image = (is_readable(e_PLUGIN."forum/images/lite/".e_LANGUAGE.'_'.$filename)) ? e_PLUGIN.'forum/images/lite/'.e_LANGUAGE.'_'.$filename : e_PLUGIN.'forum/images/lite/English_'.$filename;
				}
				else
                {
           			$image = e_PLUGIN.'forum/images/lite/'.$filename;
				}

			}
		}

	return $image;
}




if (file_exists(THEME.'forum/forum_icons_template.php'))
{
	require_once(THEME.'forum/forum_icons_template.php');
}
elseif (file_exists(THEME.'forum_icons_template.php'))
{
	require_once(THEME.'forum_icons_template.php');
}
else
{
	require_once(e_PLUGIN.'forum/templates/forum_icons_template.php');
}
?>
