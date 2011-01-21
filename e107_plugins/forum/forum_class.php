<?php
/*
 * e107 website system
 *
 * Copyright (C) 2002-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum plugin - thread classes
 *
 * $URL$
 * $Id$
 */
if (!defined('e107_INIT')) { exit; }

class e107forum
{
	var $filterNasties = TRUE;


	function e107forum()
	{
		global $pref;
		if (isset($pref['filter_script']) && ($pref['filter_script'] == 0)) $this->filterNasties = FALSE;
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
			$row = $sql->db_Fetch();
			$ret['parent'] = $row['parent'];
		}
		return $ret;
	}

	function update_lastpost($type, $id, $update_threads = FALSE)
	{
		global $sql, $tp;
		$sql2 = new db;
		if ($type == 'thread')
		{
			$id = intval($id);
			$thread_info = $this->thread_get_lastpost($id);
			list($uid, $uname) = explode(".", $thread_info['thread_user'], 2);
			if ($thread_info)
			{
				if($thread_info['user_name'] != "")
				{
					$thread_lastuser = $uid.".".$thread_info['user_name'];
				}
				else
				{
					$tmp = explode(chr(1), $thread_info['thread_user']);
					$thread_lastuser = $tmp[0];
				}
				$sql->db_Update('forum_t', "thread_lastpost = ".intval($thread_info['thread_datestamp']).", thread_lastuser = '".$tp -> toDB($thread_lastuser, true)."' WHERE thread_id = ".$id);
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
				$forum_lp_user = '';
				$forum_lp_info = '';
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
				if ($sql->db_Select("forum_t", "*", "thread_forum_id={$id} ORDER BY thread_datestamp DESC LIMIT 0,1"))
				{
					$row = $sql->db_Fetch();
					$tmp = explode(chr(1), $row['thread_user']);
					$forum_lp_user = $tmp[0];
					$last_id = $row['thread_parent'] ? $row['thread_parent'] : $row['thread_id'];
					$forum_lp_info = $row['thread_datestamp'].".".$last_id;
				}
				$sql->db_Update('forum', "forum_lastpost_user = '{$forum_lp_user}', forum_lastpost_info = '{$forum_lp_info}' WHERE forum_id={$id}");
			}
		}
	}

	function update_subparent_lp($id)
	{
		global $sql;
		if($id == 'all')
		{
			if($sql->db_Select('forum', 'DISTINCT(forum_sub)', 'forum_sub > 0'))
			{
				while($row = $sql->db_Fetch())
				{
					$sublist[] = $row;
				}
			}
			foreach($sublist as $sub)
			{
				$this->update_subparent_lp($sub['forum_sub']);
			}
		}
		else
		{
			$id = (int)$id;
			if($sql->db_Select('forum', 'forum_lastpost_user, forum_lastpost_info', "forum_id = {$id} OR forum_sub = {$id} ORDER BY forum_lastpost_info DESC LIMIT 1"))
			{
				$row = $sql->db_Fetch();
				$sql->db_Update('forum', "forum_lastpost_user = '".$row['forum_lastpost_user']."', forum_lastpost_info ='".$row['forum_lastpost_info']."' WHERE forum_id = {$id}");
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
		while ($row = $sql->db_Fetch())
		{
		  $u_new .= $row['thread_id'].".";
		}
		$u_new .= USERVIEWED;
		$t = array_unique(explode('.',$u_new));		// Filter duplicates
		$u_new = implode('.',$t);
		$sql->db_Update("user", "user_viewed='{$u_new}' WHERE user_id=".USERID);
		header("location:".e_SELF);
		exit;
	  }
	}

	function thread_markasread($thread_id)
	{
		global $sql;
		$thread_id = intval($thread_id);
		$u_new = USERVIEWED.".".$thread_id;
		return $sql->db_Update("user", "user_viewed='$u_new' WHERE user_id=".USERID);
	}

	function forum_getparents()
	{
		global $sql;
		if ($sql->db_Select('forum', '*', "forum_parent=0 ORDER BY forum_order ASC"))
		{
			while ($row = $sql->db_Fetch()) {
				$ret[] = $row;
			}
			return $ret;
		}
		return FALSE;
	}

	function forum_getmods($uclass = e_UC_ADMIN)
	{
		global $sql;
		if($uclass == e_UC_ADMIN || trim($uclass) == '')
		{
			$sql->db_Select('user', 'user_id, user_name',"user_admin = 1");
		}
		else
		{
			$regex = "(^|,)(".str_replace(",", "|", $uclass).")(,|$)";
			$sql->db_Select("user", "user_id, user_name", "user_class REGEXP '{$regex}'");
		}
		while($row = $sql->db_Fetch())
		{
			$ret[$row['user_id']] = $row['user_name'];
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
			while ($row = $sql->db_Fetch())
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

	function forum_getsubs($forum_id = "")
	{
		global $sql;
		$where = ($forum_id != "" && $forum_id != 'bysub' ? "AND forum_sub = ".intval($forum_id) : "");
		$qry = "
		SELECT f.*, u.user_name FROM #forum AS f
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(f.forum_lastpost_user,'.',1) = u.user_id
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
		return FALSE;
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
			while($row = $sql->db_Fetch())
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

	function untrack($thread_id, $from)
	{
		$thread_id = intval($thread_id);
		global $sql;
		$tmp = str_replace("-".$thread_id."-", "", USERREALM);
		return $sql->db_Update("user", "user_realm='$tmp' WHERE user_id=".USERID);
	}

	function track($thread_id, $from)
	{
		$thread_id = intval($thread_id);
		global $sql;
		return $sql->db_Update("user", "user_realm='".USERREALM."-".$thread_id."-' WHERE user_id=".USERID);
	}

	function forum_get($forum_id)
	{
		$sql = new db;
		$forum_id = intval($forum_id);
		$qry = "
		SELECT f.*, fp.forum_class as parent_class, fp.forum_name as parent_name, fp.forum_id as parent_id, fp.forum_postclass as parent_postclass, sp.forum_name AS sub_parent FROM #forum AS f
		LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent
		LEFT JOIN #forum AS sp ON f.forum_sub = sp.forum_id AND f.forum_sub > 0
		WHERE f.forum_id = {$forum_id}
		";
		if ($sql->db_Select_gen($qry))
		{
			return $sql->db_Fetch();
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
			while($row = $sql->db_Fetch())
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

	function forum_get_topics($forum_id, $from, $view)
	{
		$forum_id = intval($forum_id);
		global $sql;
		$qry = "
		SELECT t.*, u.user_name, lpu.user_name AS lastpost_username from #forum_t as t
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
		LEFT JOIN #user AS lpu ON SUBSTRING_INDEX(t.thread_lastuser,'.',1) = lpu.user_id
		WHERE t.thread_forum_id = $forum_id AND t.thread_parent = 0
		ORDER BY
		t.thread_s DESC,
		t.thread_lastpost DESC,
		t.thread_datestamp DESC
		LIMIT ".intval($from).",".intval($view)."
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
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
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
		return $sql->db_Count("forum_t", "(*)", " WHERE thread_forum_id=".intval($forum_id)." AND thread_parent=0 ");
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
			LIMIT ".intval($from).",".intval($limit);
			if ($sql->db_Select_gen($qry))
			{
				$i = 0;
				while ($row = $sql->db_Fetch())
				{
					$threadList[$i++] = $row['thread_id'];
				}

				if (($id = array_search($thread_id, $threadList)) !== FALSE)
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
			LIMIT ".intval($from).",".intval($limit);
			if ($sql->db_Select_gen($qry))
			{
				$i = 0;
				while ($row = $sql->db_Fetch())
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
		global $sql, $tp;
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
			while ($row = $sql->db_Fetch())
			{
				if ($this->filterNasties)
				{
					$row['thread_name'] = $tp->dataFilter($row['thread_name']);
					$row['thread_thread'] = $tp->dataFilter($row['thread_thread']);
				}
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
			$row = $sql->db_Fetch();
			if ($this->filterNasties)
			{
				$row['thread_name'] = $tp->dataFilter($row['thread_name']);
				$row['thread_thread'] = $tp->dataFilter($row['thread_thread']);
			}
			$ret['head'] = $row;
			if (!array_key_exists(0, $ret))
			{
				$ret[0] = $row;
			}
		}
		return $ret;
	}


	/**
	 *	Determine whether current user has access to the specified thread.
	 *
	 *	@param int $thread_id
	 *
	 *	@return boolean TRUE if access allowed, FALSE if denied
	 */
	function thread_get_allowed($thread_id)
	{
		global $sql;
		$thread_id = intval($thread_id);
		$qry = "
		SELECT t.thread_id from #forum_t as t
		JOIN #forum AS f ON f.forum_id = t.thread_forum_id
		LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent
		WHERE t.thread_id = {$thread_id}
		AND f.forum_parent != 0
		AND fp.forum_class IN (".USERCLASS_LIST.")
		AND f.forum_class IN (".USERCLASS_LIST.")
		LIMIT 0,1
		";

		if ($sql->db_Select_gen($qry))
		{
			return TRUE;
		}
		return FALSE;
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
			while ($row = $sql->db_Fetch())
			{
				$ret[$row['thread_parent']] = $row['thread_replies'];
			}
		}
		return $ret;
	}

	function thread_incview($thread_id)
	{
		$thread_id = (int)$thread_id;
		global $sql;
		return $sql->db_Update("forum_t", "thread_views=thread_views+1 WHERE thread_id=".$thread_id);
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
			ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
			WHERE t.thread_id = ".intval($parent_id)."
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


	function _forum_lp_update($lp_type, $lp_user, $lp_info, $lp_forum_id, $lp_forum_sub)
	{
		global $sql;
		$sql->db_Update('forum', "{$lp_type}={$lp_type}+1, forum_lastpost_user='{$lp_user}', forum_lastpost_info = '{$lp_info}' WHERE forum_id=".(int)$lp_forum_id);
		if($lp_forum_sub)
		{
			$sql->db_Update('forum', "forum_lastpost_user = '{$lp_user}', forum_lastpost_info = '{$lp_info}' WHERE forum_id=".(int)$lp_forum_sub);
		}
	}

	function thread_insert($thread_name, $thread_thread, $thread_forum_id, $thread_parent, $thread_poster, $thread_active, $thread_s, $forum_sub)
	{
		global $sql, $tp, $pref, $e107;
		global $PLUGINS_DIRECTORY;

		$forum_info = '';
		$post_time = time();
		$forum_sub = intval($forum_sub);
		$ip = $e107->getip();
		//Check for duplicate post
		if ($sql->db_Count('forum_t', '(*)', "WHERE thread_thread='".$thread_thread."' and thread_datestamp > ".($post_time - 180)))
		{
			return -1;
		}

		$post_user = $thread_poster['post_userid'].".".$tp->toDB($thread_poster['post_user_name']);
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
			$sql->db_Update('user', "user_forums=user_forums+1, user_viewed='{$new_userviewed}' WHERE user_id=".USERID);
		}

		//If post is a reply
		if ($thread_parent)
		{
			$forum_lp_info = $post_time.".".intval($thread_parent);
			$gen = new convert;
			// Update main forum with last post info and increment reply count
			$this->_forum_lp_update("forum_replies", $post_user, $forum_lp_info, $thread_forum_id, $forum_sub);

			// Update head post with last post info and increment reply count
			$sql->db_Update('forum_t', "thread_lastpost={$post_time}, thread_lastuser='{$post_user}', thread_total_replies=thread_total_replies+1 WHERE thread_id = ".(int)$thread_parent);

			$parent_thread = $this->thread_get_postinfo($thread_parent);
			$thread_name = $tp->toText($parent_thread[0]['thread_name']);
			$thread_name = str_replace('&quot;', '"', $thread_name);		// This not picked up by toText();
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
				$sql->db_Select('user', 'user_admin, user_perms, user_class, user_ban', 'user_id = '.$parent_thread[0]['user_id']);
				$thread_owner = $sql->db_Fetch(MYSQL_ASSOC);
				$forum_info = $this->forum_get($parent_thread[0]['thread_forum_id']);

				//Ensure owner is not banned and has permissions to view forum and forum parent
				if(
					$thread_owner['user_ban'] == 0 &&
					check_class($forum_info['forum_class'], $thread_owner['user_class'], $thread_owner) &&
					check_class($forum_info['parent_class'], $thread_owner['user_class'], $thread_owner)
					)
				{
					$gen = new convert;
					$email_name = $parent_thread[0]['user_name'];
					$email_addy = $parent_thread[0]['user_email'];
					$message = LAN_384.SITENAME.".<br /><br />". LAN_382.$datestamp."<br />". LAN_94.": ".$thread_poster['post_user_name']."<br /><br />". LAN_385.$tp->toHTML($email_post, TRUE, 'USER_BODY')."<br /><br />". LAN_383."<br /><br />".$mail_link;
					include_once(e_HANDLER."mail.php");
					sendemail($email_addy, $pref['forum_eprefix']." '".$thread_name."', ".LAN_381.SITENAME, $message, $email_name);
				}
			}

			// Send email to all users tracking thread - except the one that's just posted
			if ($pref['forum_track'] && $sql->db_Select('user', 'user_id, user_ban, user_admin, user_perms, user_class, user_email, user_name', "user_realm REGEXP('-".intval($thread_parent)."-') "))
			{
				include_once(e_HANDLER.'mail.php');
				$message = LAN_385.SITENAME.".<br /><br />". LAN_382.$datestamp."<br />". LAN_94.": ".$thread_poster['post_user_name']."<br /><br />". LAN_385.$tp->toHTML($email_post, TRUE, 'USER_BODY')."<br /><br />". LAN_383."<br /><br />".$mail_link;
				while ($tracker = $sql->db_Fetch(MYSQL_ASSOC))
				{
					// Get forum info if we haven't got it already
					if($forum_info == false)
					{
						$forum_info = $this->forum_get($parent_thread[0]['thread_forum_id']);
					}
					// Don't send to self, nor to originator of thread if they've got 'notify' set
					// Also ensure tracker is not banned and has permissions to view forum and forum parent
					if ($tracker['user_email'] &&
						$tracker['user_email'] != $email_addy &&
						$tracker['user_id'] != USERID &&
						$tracker['user_ban'] == 0 &&
						check_class($forum_info['forum_class'], $tracker['user_class'], $tracker) &&
						check_class($forum_info['parent_class'], $tracker['user_class'], $tracker)
						)
					{
						sendemail($tracker['user_email'], $pref['forum_eprefix']." '".$thread_name."', ".LAN_381.SITENAME, $message, $tracker['user_name']);
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

	//*** added by marj
        function postGetOldestNew($count = 50, $userviewed = USERVIEWED)
        {
                global $sql;
                $viewed = '';
                if($userviewed)
                {
                        $viewed = preg_replace('#\.+#', '.', $userviewed);
                        $viewed = preg_replace('#^\.#', '', $viewed);
                        $viewed = preg_replace('#\.$#', '', $viewed);
                        $viewed = str_replace('.', ',', $viewed);
                }
                if($viewed != '')
                {
                        $viewed = ' AND ft.thread_id NOT IN ('.$viewed.') AND  ft.thread_parent NOT IN ('.$viewed.') ';
                }

                $qry = '
                SELECT ft.*, fp.thread_name as post_subject, fp.thread_total_replies as replies, u.user_id, u.user_name, f.forum_class, f.forum_name
                FROM #forum_t AS ft
                LEFT JOIN #forum_t as fp ON fp.thread_id = ft.thread_parent
                LEFT JOIN #user as u ON u.user_id = SUBSTRING_INDEX(ft.thread_user,".",1)
                LEFT JOIN #forum as f ON f.forum_id = ft.thread_forum_id
                WHERE ft.thread_datestamp > '.USERLV. '
                AND f.forum_class IN ('.USERCLASS_LIST.')
        '.$viewed.'
                ORDER BY ft.thread_datestamp ASC LIMIT 0, '.intval($count);
                if($sql->db_Select_gen($qry))
                {
                        $ret = $sql->db_getList();
                }
                return $ret;
        }
//*** end added by marj

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
					$reply_count += $sql->db_Delete("forum_t", "thread_parent=".$thread['thread_id']);
					//Delete thread
					$thread_count += $sql->db_Delete("forum_t", "thread_id = ".$thread['thread_id']);
					//Delete poll if there is one
					$sql->db_Delete("poll", "poll_datestamp=".$thread['thread_id']);
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
		$sql->db_Update("forum", "forum_threads='$threads', forum_replies='$replies' WHERE forum_id=".$forumID);
		if($recalc_threads == true)
		{
			$sql->db_Select('forum_t', 'thread_id', "thread_forum_id = {$forumID} AND thread_parent=0");
			$tList = $sql->db_getList();
			foreach($tList as $t) {
				$sql->db_Select('forum_t', 'count(*) as replies', "thread_parent = {$t['thread_id']}");
				$row = $sql->db_Fetch(MYSQL_ASSOC);
				$sql->db_Update('forum_t', "thread_total_replies={$row['replies']} WHERE thread_id={$t['thread_id']}");
			}
			die();
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
			while($row = $sql->db_Fetch())
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
	function set_crumb($forum_href=FALSE,$thread_title="")
	{
		global $FORUM_CRUMB,$forum_info,$thread_info,$tp;
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
			$image = ($ML && is_readable(THEME.'forum/'.e_LANGUAGE."_".$filename)) ? THEME_ABS.'forum/'.e_LANGUAGE."_".$filename :  THEME_ABS.'forum/'.$filename;
		}
		else
		{
			if(defined("IMODE"))
			{
				if($ML)
				{
                	$image = (is_readable(e_PLUGIN."forum/images/".IMODE."/".e_LANGUAGE."_".$filename)) ? e_PLUGIN_ABS."forum/images/".IMODE."/".e_LANGUAGE."_".$filename : e_PLUGIN_ABS."forum/images/".IMODE."/English_".$filename;
				}
				else
				{
                	$image = e_PLUGIN_ABS."forum/images/".IMODE."/".$filename;
				}
			}
			else
			{
				if($ML)
				{
					$image = (is_readable(e_PLUGIN."forum/images/lite/".e_LANGUAGE."_".$filename)) ? e_PLUGIN_ABS."forum/images/lite/".e_LANGUAGE."_".$filename : e_PLUGIN_ABS."forum/images/lite/English_".$filename;
				}
				else
                {
           			$image = e_PLUGIN_ABS."forum/images/lite/".$filename;
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
