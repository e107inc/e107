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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_class.php,v $
|     $Revision: 1.23 $
|     $Date: 2008-12-11 16:02:05 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

class e107forum
{
	var $permList = array();
	var $fieldTypes = array();
	var $userViewed = array();

	function e107forum()
	{
		$this->loadPermList();
		$this->fieldTypes['forum_post']['post_user'] 			= 'int';
		$this->fieldTypes['forum_post']['post_forum'] 			= 'int';
		$this->fieldTypes['forum_post']['post_datestamp'] 		= 'int';
		$this->fieldTypes['forum_post']['post_thread'] 			= 'int';
		$this->fieldTypes['forum_post']['post_options'] 		= 'escape';
		$this->fieldTypes['forum_post']['post_attachments'] 	= 'escape';

		$this->fieldTypes['forum_thread']['thread_user'] 		= 'int';
		$this->fieldTypes['forum_thread']['thread_lastpost'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_lastuser'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_s'] 			= 'int';
		$this->fieldTypes['forum_thread']['thread_forum_id'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_active'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_datestamp']	= 'int';
		$this->fieldTypes['forum_thread']['thread_views'] 		= 'int';
		$this->fieldTypes['forum_thread']['thread_replies'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_options'] 	= 'escape';

		$this->fieldTypes['forum']['forum_lastpost_user']	 	= 'int';

//		var_dump($this->permList);
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
		//var_dump($this->permList);
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
			if(isset($e107->currentUser['user_plugin_forum_views']))
			{
				$this->userViewed = explode('.', $e107->currentUser['user_plugin_forum_viewed']);
			}
		}
		if(is_array($this->userViewed) && in_array($threadId, $this->userViewed))
		{
			return true;
		}
		return false;
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
		//Future option, will just set to true here
		$addUserPostCount = true;
		$result = false;

		$e107 = e107::getInstance();
		$postId = $e107->sql->db_Insert('forum_post', $postInfo);
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
			$threadInfo['WHERE'] = 'thread_id = '.$postInfo['post_thread'];

			$threadInfo['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];
			$threadInfo['_FIELD_TYPES']['thread_total_replies'] = 'cmd';
//			var_dump($threadInfo);
//			exit;
			$result = $e107->sql->db_Update('forum_thread', $threadInfo);

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

			//If we update the thread, then we assume it was a reply, otherwise we've added a reply only.
			$forumInfo['_FIELD_TYPES'] = $this->fieldTypes['forum'];
			if($updateThread)
			{
				$forumInfo['forum_replies'] = 'forum_replies+1';
				$forumInfo['_FIELD_TYPES']['forum_replies'] = 'cmd';
			}
			else
			{
				$forumInfo['forum_threads'] = 'forum_threads+1';
				$forumInfo['_FIELD_TYPES']['forum_threads'] = 'cmd';
			}
			$forumInfo['forum_lastpost_info'] = $postInfo['post_datestamp'].'.'.$postInfo['post_thread'];
			$forumInfo['WHERE'] = 'forum_id = '.$postInfo['post_forum'];
			$result = $e107->sql->db_Update('forum', $forumInfo);
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
		$threadInfo['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];
		if($newThreadId = $e107->sql->db_Insert('forum_thread', $threadInfo))
		{
			$postInfo['_FIELD_TYPES'] = $this->fieldTypes['forum_post'];
			$postInfo['post_thread'] = $newThreadId;
			$newPostId = $this->postAdd($postInfo, false);
			return array('postid' => $newPostId, 'threadid' => $newThreadId);
		}
		return false;
	}

	function threadUpdate($threadInfo, $inc)
	{
		$e107 = e107::getInstance();
		//TODO: Add this
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
			SELECT t.*, f.*, tr.track_userid
			FROM `#forum_thread` AS t
			LEFT JOIN `#forum` AS f ON t.thread_forum_id = f.forum_id
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

	function postGet($threadId, $start, $num)
	{
		$ret = false;
		$e107 = e107::getInstance();
		$qry = '
		SELECT p.*,
		u.user_name, u.user_customtitle, u.user_hideemail, u.user_email, u.user_signature,
		u.user_admin, u.user_image, u.user_join, ue.user_plugin_forum_posts,
		eu.user_name AS edit_name
		FROM `#forum_post` AS p
		LEFT JOIN `#user` AS u ON p.post_user = u.user_id
		LEFT JOIN `#user` AS eu ON p.post_edit_user IS NOT NULL AND p.post_edit_user = eu.user_id
		LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = p.post_user
		WHERE p.post_thread = '.$threadId."
		ORDER BY p.post_datestamp ASC
		LIMIT {$start}, {$num}
		";
		if($e107->sql->db_Select_gen($qry))
		{
			$ret = array();
			while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[] = $row;
			}
		}
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
			$viewed = $e107->currentUser['plugin_forum_user_viewed'];
		}
		else
		{
			$tmp = get_user_data($uid);
			$viewed = $tmp['plugin_forum_user_viewed'];
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
			$tmp['_FILE_TYPES']['post_attachments'] = 'escape';
			$tmp['WHERE'] = 'post_id = '.$id;
			$e107->sql->db_update('forum_post', $tmp);
		}
	}


	function thread_postnum($thread_id)
	{
		global $sql;
		$ret = array();
		$ret['parent'] = $thread_id;
		$query = "
		SELECT ft.thread_id, fp.thread_id as parent
		FROM #forum_t AS t
		LEFT JOIN #forum_t AS ft ON ft.thread_parent = t.thread_parent AND ft.thread_id <= ".intval($thread_id)."
		LEFT JOIN #forum_t as fp ON fp.thread_id = t.thread_parent
		WHERE t.thread_id = ".intval($thread_id)." AND t.thread_parent != 0
		ORDER  BY ft.thread_datestamp ASC
		";
		if($ret['post_num'] = $sql->db_Select_gen($query))
		{
			$row = $sql->db_Fetch(MYSQL_ASSOC);
			$ret['parent'] = $row['parent'];
		}
		return $ret;
	}

	function forumUpdateLastpost($type, $id, $update_threads = FALSE)
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
				$tmp['thread_lastuser_anon'] = $lpInfo['post_user_anon'];
			}
			$tmp['thread_lastpost'] = $lpInfo['post_datestamp'];
			$tmp['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];
			$sql->db_Update('forum_thread', $tmp);

			return $lpInfo;
		}
		if ($type == 'forum') {
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
						//	echo "Updating forum #{$id}<br />";
						$this->update_lastpost('forum', $id, $update_threads);
					}
				}
			}
			else
			{
				$id = (int)$id;
				$lp_info = '';
				$lp_user = 'NULL';
				if($update_threads == true)
				{
					if ($sql2->db_Select('forum_t', 'thread_id', "thread_forum_id = $id AND thread_parent = 0"))
					{
						while ($row = $sql2->db_Fetch(MYSQL_ASSOC))
						{
							$this->update_lastpost('thread', $row['thread_id']);
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

	function forum_markasread($forum_id)
	{
	  global $sql;
	  if ($forum_id != 'all')
	  {
		$forum_id = intval($forum_id);
		$extra = " AND thread_forum_id={$forum_id}";
	  }
	  $qry = "thread_lastpost > ".USERLV." AND thread_parent = 0 {$extra} ";
	  if ($sql->db_Select('forum_t', 'thread_id', $qry))
	  {
		while ($row = $sql->db_Fetch(MYSQL_ASSOC))
		{
		  $u_new .= $row['thread_id'].".";
		}
		$u_new .= USERVIEWED;
		$t = array_unique(explode('.',$u_new));		// Filter duplicates
		$u_new = implode('.',$t);
		$sql->db_Update('user', "user_viewed='{$u_new}' WHERE user_id=".USERID);
		header("location:".e_SELF);
		exit;
	  }
	}

	function threadMarkAsRead($threadId)
	{
		$e107 = e107::getInstance();
		$threadId = (int)$threadId;
		$currentUser['user_plugin_forum_viewed'] = '4..5..6.7.8';
		$_tmp = preg_split('#\.+#', $currentUser['user_plugin_forum_viewed']);
		$_tmp[] = $threadId;
		$viewed = '.'.implode('.', $_tmp).'.';
		unset($_tmp);
		return $e107->sql->db_Update('user_extended', "user_plugin_forum_viewed = '{$viewed}' WHERE user_extended_id = ".USERID);
	}

	function forum_getparents()
	{
		global $sql;
		if ($sql->db_Select('forum', '*', "forum_parent=0 ORDER BY forum_order ASC"))
		{
			while ($row = $sql->db_Fetch(MYSQL_ASSOC)) {
				$ret[] = $row;
			}
			return $ret;
		}
		return FALSE;
	}

	function forum_getmods($uclass = e_UC_ADMIN)
	{
		$e107 = e107::getInstance();
		if($uclass == e_UC_ADMIN || trim($uclass) == '')
		{
			$e107->sql->db_Select('user', 'user_id, user_name','user_admin = 1 ORDER BY user_name ASC');
			while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[$row['user_id']] = $row['user_name'];
			}
		}
		else
		{
			$ret = $e107->user_class->get_users_in_class($uclass, 'user_name', true);
		}
		return $ret;
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

	function forum_getsubs($forum_id = '')
	{
		global $sql;
		$where = ($forum_id != '' && $forum_id != 'bysub' ? "AND forum_sub = ".(int)$forum_id : '');
		$qry = "
		SELECT f.*, u.user_name FROM #forum AS f
		LEFT JOIN #user AS u ON f.forum_lastpost_user = u.user_id
		WHERE forum_sub != 0 {$where}
		ORDER BY f.forum_order ASC
		";
		if ($sql->db_Select_gen($qry))
		{
			while ($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				if($forum_id == "")
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


	function forum_newflag_list()
	{
	  if (!USER) return FALSE;		// Can't determine new threads for non-logged in users
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

		$_newqry = 	"
		SELECT DISTINCT ff.forum_sub, ft.thread_forum_id FROM #forum_t AS ft
		LEFT JOIN #forum AS ff ON ft.thread_forum_id = ff.forum_id
		WHERE thread_parent = 0 AND thread_lastpost > ".USERLV." {$viewed}
		";
		if($sql->db_Select_gen($_newqry))
		{
			while($row = $sql->db_Fetch(MYSQL_ASSOC))
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

	function track($which, $uid, $threadId)
	{
		$e107 = e107::getInstance();
		global $pref;

		if (!varsettrue($pref['forum_track'])) { return false; }

		$threadId = (int)$threadId;
		$uid = (int)$uid;
		$result = false;
		switch($which)
		{
			case 'add':
				$tmp = array();
				$tmp['track_userid'] = $uid;
				$tmp['track_thread'] = $threadId;
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

/*
	function track($uid, $thread_id)
	{
		$thread_id = (int)$thread_id;
		$uid = (int)$uid;
		global $sql;
		return $sql->db_Update("user", "user_realm='".USERREALM."-".$thread_id."-' WHERE user_id=".USERID);
	}
*/

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

	function forum_get_allowed()
	{
		global $sql;
		$qry = "
		SELECT f.forum_id, f.forum_name FROM #forum AS f
		LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent
		WHERE f.forum_parent != 0
		AND fp.forum_class IN (".USERCLASS_LIST.")
		AND f.forum_class IN (".USERCLASS_LIST.")
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

	function thread_update($thread_id, $newvals)
	{
		global $sql, $tp;
		foreach($newvals as $var => $val)
		{
			$var = $tp -> toDB($var);
			$val = $tp -> toDB($val);
			$newvalArray[] = "{$var} = '{$val}'";
		}
		$newString = implode(', ', $newvalArray)." WHERE thread_id=".intval($thread_id);
		return $sql->db_Update('forum_t', $newString);
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
		t.thread_s DESC,
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
			thread_s DESC,
			thread_lastpost {$sort}
			LIMIT 1";
			if ($e107->sql->db_Select_gen($qry))
			{
				$row = $e107->sql->db_Fetch();
				return $row['thread_id'];

			}
			return false;
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
			LIMIT ".intval($from).",".intval($limit);
			if ($sql->db_Select_gen($qry))
			{
				$i = 0;
				while ($row = $sql->db_Fetch(MYSQL_ASSOC))
				{
					$threadList[$i++] = $row['thread_id'];
				}

				if (($id = array_search($thread_id, $threadList)) !== FALSE)
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
		SELECT t.*, u.*, ue.* FROM #forum_t as t
		LEFT JOIN #user AS u
		ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
		LEFT JOIN #user_extended AS ue
		ON SUBSTRING_INDEX(t.thread_user,'.',1) = ue.user_extended_id
		WHERE t.thread_parent = $thread_id
		ORDER by t.thread_datestamp {$sortdir}
		LIMIT ".intval($start).",".intval($limit);
		$ret = array();
		if ($sql->db_Select_gen($qry))
		{
			$i = $array_start;
			while ($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[$i] = $row;
				$i++;
			}
		}
		$qry = "
		SELECT t.*,u.*,ue.* from #forum_t AS t
		LEFT JOIN #user AS u
		ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
		LEFT JOIN #user_extended AS ue
		ON SUBSTRING_INDEX(t.thread_user,'.',1) = ue.user_extended_id
		WHERE t.thread_id = $thread_id
		LIMIT 0,1
		";
		if ($sql->db_Select_gen($qry))
		{
			$row = $sql->db_Fetch(MYSQL_ASSOC);
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
		global $sql, $tp;
		$qry = "
		SELECT t.thread_parent, t.COUNT(*) as thread_replies
		FROM #forum_t AS t
		WHERE t.thread_parent
		IN ".$tp -> toDB($thread_list, true)."
		GROUP BY t.thread_parent
		";
		if ($sql->db_Select_gen($qry))
		{
			while ($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[$row['thread_parent']] = $row['thread_replies'];
			}
		}
		return $ret;
	}

	function threadIncView($id)
	{
		$e107 = e107::getInstance();
		$id = (int)($id);
		return $e107->sql->db_Update('forum_thread', 'thread_views=thread_views+1 WHERE thread_id='.$id);
	}


	function thread_get_postinfo($thread_id, $head = FALSE)
	{
		$thread_id = intval($thread_id);
		global $sql;
		$ret = array();
		$qry = "
		SELECT t.*, u.user_name, u.user_id, u.user_email from #forum_t AS t
		LEFT JOIN #user AS u
		ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
		WHERE t.thread_id = $thread_id
		LIMIT 0,1
		";
		if ($sql->db_Select_gen($qry))
		{
			$ret[0] = $sql->db_Fetch(MYSQL_ASSOC);
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
			ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
			WHERE t.thread_id = ".intval($parent_id)."
			LIMIT 0,1
			";
			if ($sql->db_Select_gen($qry))
			{
				$row = $sql->db_Fetch(MYSQL_ASSOC);
				$ret['head'] = $row;
			}
		}
		return $ret;
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

	function thread_insert($thread_name, $thread_thread, $thread_forum_id, $thread_parent, $thread_poster, $thread_active, $thread_s, $forum_sub)
	{
		$post_time = time();
		global $sql, $tp, $pref, $e107;
		$forum_sub = intval($forum_sub);
		$ip = $e107->getip();
		//Check for duplicate post
		if ($sql->db_Count('forum_t', '(*)', "WHERE thread_thread='{$thread_thread}' and thread_datestamp > ".($post_time - 180)))
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
		$vals = "'0', '{$thread_name}', '{$thread_thread}', '".intval($thread_forum_id)."', '".intval($post_time)."', '".intval($thread_parent)."', '{$thread_post_user}', '0', '".intval($thread_active)."', '$post_time', '$thread_s', '0', '{$post_last_user}', '0'";
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
			$new_userviewed = USERVIEWED.".".($thread_parent ? intval($thread_parent) : $newthread_id);
			$sql->db_Update('user', "user_forums=user_forums+1, user_viewed='{$new_userviewed}' WHERE user_id='".USERID."' ");
		}

		//If post is a reply
		if ($thread_parent)
		{
			$forum_lp_info = $post_time.".".intval($thread_parent);
			$gen = new convert;
			// Update main forum with last post info and increment reply count
			$this->_forum_lp_update("forum_replies", $post_user, $forum_lp_info, $thread_forum_id, $forum_sub);

			// Update head post with last post info and increment reply count
			$sql->db_Update('forum_t', "thread_lastpost={$post_time}, thread_lastuser='{$post_user}', thread_total_replies=thread_total_replies+1 WHERE thread_id = ".intval($thread_parent));

			$parent_thread = $this->thread_get_postinfo($thread_parent);
			global $PLUGINS_DIRECTORY;
			$thread_name = $tp->toText($parent_thread[0]['thread_name']);
			$datestamp = $gen->convert_date($post_time, "long");
			$email_post = $tp->toHTML($thread_thread, TRUE);
			$mail_link = "<a href='".SITEURL.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$thread_parent.".last'>".SITEURL.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$thread_parent.".last</a>";
			if(!isset($pref['forum_eprefix']))
			{
				$pref['forum_eprefix'] = "[forum]";
			}
			//   Send email to originator if 'notify' set
			$email_addy = '';
			if ($pref['email_notify'] && $parent_thread[0]['thread_active'] == 99 && $parent_thread[0]['user_id'] != USERID)
			{
				$gen = new convert;
				$email_name = $parent_thread[0]['user_name'];
				$email_addy = $parent_thread[0]['user_email'];
				$message = LAN_384.SITENAME.".<br /><br />". LAN_382.$datestamp."<br />". LAN_94.": ".$thread_poster['post_user_name']."<br /><br />". LAN_385.$email_post."<br /><br />". LAN_383."<br /><br />".$mail_link;
				include_once(e_HANDLER."mail.php");
				sendemail($email_addy, $pref['forum_eprefix']." '".$thread_name."', ".LAN_381.SITENAME, $message, $email_name);
			}


			//   Send email to all users tracking thread - except the one that's just posted
			if ($pref['forum_track'] && $sql->db_Select("user", "user_id, user_email, user_name", "user_realm REGEXP('-".intval($thread_parent)."-') "))
			{
				include_once(e_HANDLER.'mail.php');
				$message = LAN_385.SITENAME.".<br /><br />". LAN_382.$datestamp."<br />". LAN_94.": ".$thread_poster['post_user_name']."<br /><br />". LAN_385.$email_post."<br /><br />". LAN_383."<br /><br />".$mail_link;
				while ($row = $sql->db_Fetch(MYSQL_ASSOC))
				{	// Don't sent to self, nor to originator of thread if they've got 'notify' set
					if ($row['user_email'] && ($row['user_email'] != $email_addy) && ($row['user_id'] != USERID))	// (May be wrong, but this could be faster than filtering current user in the query)
					{
						sendemail($row['user_email'], $pref['forum_eprefix']." '".$thread_name."', ".LAN_381.SITENAME, $message, $row['user_name']);
					}
				}
			}
		}
		else
		{
			//post is a new thread
			$forum_lp_info = $post_time.".".$newthread_id;
			$this->_forum_lp_update("forum_threads", $post_user, $forum_lp_info, $thread_forum_id, $forum_sub);
		}
		return $newthread_id;
	}

	function post_getnew($count = 50, $userviewed = USERVIEWED)
	{
		global $sql;
		$viewed = "";
		if($userviewed)
		{
			$viewed = preg_replace("#\.+#", ".", $userviewed);
			$viewed = preg_replace("#^\.#", "", $viewed);
			$viewed = preg_replace("#\.$#", "", $viewed);
			$viewed = str_replace(".", ",", $viewed);
		}
		if($viewed != "")
		{
			$viewed = " AND ft.thread_id NOT IN (".$viewed.")";
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
		if($sql->db_Select_gen($qry))
		{
			$ret = $sql->db_getList();
		}
		return $ret;
	}

	function forum_prune($type, $days, $forumArray)
	{
		global $sql;
		$prunedate = time() - (intval($days) * 86400);
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
					$reply_count += $sql->db_Delete("forum_t", "thread_parent='".intval($thread['thread_id'])."'");
					//Delete thread
					$thread_count += $sql->db_Delete("forum_t", "thread_id = '".intval($thread['thread_id'])."'");
					//Delete poll if there is one
					$sql->db_Delete("poll", "poll_datestamp='".intval($thread['thread_id'])."");
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
		if($type == 'make_inactive')
		{
			$pruned = $sql->db_Update("forum_t", "thread_active=0 WHERE thread_lastpost < $prunedate AND thread_parent=0 AND thread_forum_id IN ({$forumList})");
			return FORLAN_8." ".$pruned." ".FORLAN_91;
		}
	}

	function forum_update_counts($forumID, $recalc_threads = false)
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
		if($recalc_threads == true)
		{
			$sql->db_Select("forum_t", "thread_parent, count(*) as replies", "thread_forum_id = $forumID GROUP BY thread_parent");
			$tlist = $sql->db_getList();
			foreach($tlist as $t)
			{
				$tid = $t['thread_parent'];
				$replies = intval($t['replies']);
				$sql->db_Update("forum_t", "thread_total_replies='$replies' WHERE thread_id='$tid'");
			}
		}
	}

	function get_user_counts()
	{
		global $sql;
		$qry = "
		SELECT u.user_id AS uid, count(t.thread_user) AS cnt FROM #forum_t AS t
		LEFT JOIN #user AS u on SUBSTRING_INDEX(t.thread_user,'.',1)  = u.user_id
		WHERE u.user_id > 0
		GROUP BY uid
		";

		if($sql->db_Select_gen($qry))
		{
			$ret = array();
			while($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				$ret[$row['uid']] = $row['cnt'];
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
	function set_crumb($forum_href=false, $thread_title='')
	{
		global $FORUM_CRUMB, $forum_info, $threadInfo, $tp;
		global $BREADCRUMB,$BACKLINK;  // Eventually we should deprecate BACKLINK

		if(is_array($FORUM_CRUMB))
		{
			$search 	= array("{SITENAME}", "{SITENAME_HREF}");
			$replace 	= array(SITENAME, "href='".e_BASE."index.php'");
			$FORUM_CRUMB['sitename']['value'] = str_replace($search, $replace, $FORUM_CRUMB['sitename']['value']);

			$search 	= array("{FORUMS_TITLE}", "{FORUMS_HREF}");
			$replace 	= array(LAN_01, "href='".e_PLUGIN."forum/forum.php'");
			$FORUM_CRUMB['forums']['value'] = str_replace($search, $replace, $FORUM_CRUMB['forums']['value']);

			$search 	= "{PARENT_TITLE}";
			$replace 	= $tp->toHTML($forum_info['parent_name']);
			$FORUM_CRUMB['parent']['value'] = str_replace($search, $replace, $FORUM_CRUMB['parent']['value']);

			if($forum_info['sub_parent'])
			{
				$search 	= array("{SUBPARENT_TITLE}", "{SUBPARENT_HREF}");
				$forum_sub_parent = (substr($forum_info['sub_parent'], 0, 1) == "*" ? substr($forum_info['sub_parent'], 1) : $forum_info['sub_parent']);
				$replace 	= array($forum_sub_parent, "href='".e_PLUGIN."forum/forum_viewforum.php?{$forum_info['forum_sub']}'");
				$FORUM_CRUMB['subparent']['value'] = str_replace($search, $replace, $FORUM_CRUMB['subparent']['value']);
			}
			else
			{
				$FORUM_CRUMB['subparent']['value'] = "";
			}

			$search 	= array("{FORUM_TITLE}", "{FORUM_HREF}");
			$tmpFname = $forum_info['forum_name'];
			if(substr($tmpFname, 0, 1) == "*") { $tmpFname = substr($tmpFname, 1); }
			$replace 	= array($tmpFname,"href='".e_PLUGIN."forum/forum_viewforum.php?{$forum_info['forum_id']}'");
			$FORUM_CRUMB['forum']['value'] = str_replace($search, $replace, $FORUM_CRUMB['forum']['value']);

			if(strlen($thread_title))
			{
				$search 	= array("{THREAD_TITLE}");
				$replace 	= array($thread_title);
				$FORUM_CRUMB['thread']['value'] = str_replace($search, $replace, $FORUM_CRUMB['thread']['value']);
			}
			else
			{
				$FORUM_CRUMB['thread']['value'] = "";
			}

			$FORUM_CRUMB['fieldlist'] = "sitename,forums,parent,subparent,forum,thread";
			$BREADCRUMB = $tp->parseTemplate("{BREADCRUMB=FORUM_CRUMB}", true);

		}
		else
		{
			$dfltsep = " :: ";
			$BREADCRUMB = "<a class='forumlink' href='".e_BASE."index.php'>".SITENAME."</a>".$dfltsep."<a class='forumlink' href='".e_PLUGIN."forum/forum.php'>".LAN_01."</a>".$dfltsep;
			if($forum_info['sub_parent'])
			{
				$forum_sub_parent = (substr($forum_info['sub_parent'], 0, 1) == "*" ? substr($forum_info['sub_parent'], 1) : $forum_info['sub_parent']);
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

	$multilang = array("reply.png","newthread.png","moderator.png","main_admin.png","admin.png");
	$ML = (in_array($filename,$multilang)) ? TRUE : FALSE;

		if(file_exists(THEME.'forum/'.$filename) || is_readable(THEME.'forum/'.e_LANGUAGE."_".$filename))
		{
			$image = ($ML && is_readable(THEME.'forum/'.e_LANGUAGE."_".$filename)) ? THEME.'forum/'.e_LANGUAGE."_".$filename :  THEME.'forum/'.$filename;
		}
		else
		{
			if(defined("IMODE"))
			{
				if($ML)
				{
                	$image = (is_readable(e_PLUGIN."forum/images/".IMODE."/".e_LANGUAGE."_".$filename)) ? e_PLUGIN."forum/images/".IMODE."/".e_LANGUAGE."_".$filename : e_PLUGIN."forum/images/".IMODE."/English_".$filename;
				}
				else
				{
                	$image = e_PLUGIN."forum/images/".IMODE."/".$filename;
				}
			}
			else
			{
				if($ML)
				{
					$image = (is_readable(e_PLUGIN."forum/images/lite/".e_LANGUAGE."_".$filename)) ? e_PLUGIN."forum/images/lite/".e_LANGUAGE."_".$filename : e_PLUGIN."forum/images/lite/English_".$filename;
				}
				else
                {
           			$image = e_PLUGIN."forum/images/lite/".$filename;
				}

			}
		}

	return $image;
}




if (file_exists(THEME.'forum/forum_icons_template.php'))
{
	require_once(THEME.'forum/forum_icons_template.php');
}
else if (file_exists(THEME.'forum_icons_template.php'))
{
	require_once(THEME.'forum_icons_template.php');
}
else
{
	require_once(e_PLUGIN.'forum/templates/forum_icons_template.php');
}
?>
