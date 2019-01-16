<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum e_list Handler
 *
*/
if (!defined('e107_INIT')) { exit; }

//TODO: Investigate queries - needs some more sorting
class list_forum
{
	function __construct($parent)
	{
		$this->parent = $parent;
	}

	function getListData()
	{
		$list_caption = $this->parent->settings['caption'];
		$list_display = ($this->parent->settings['open'] ? "" : "none");

		$bullet = $this->parent->getBullet($this->parent->settings['icon']);

		if($this->parent->mode == 'new_page' || $this->parent->mode == 'new_menu' )
		{	// New posts since last visit, up to limit
			$lvisit = $this->parent->getlvisit();
			$qry = "
			SELECT t.thread_name AS parent_name, t.thread_id as parent_id,
			f.forum_id, f.forum_name, f.forum_class, f.forum_sef,
			u.user_name, lp.user_name AS lp_name, 
			t.thread_id, t.thread_views as tviews, t.thread_name, t.thread_datestamp, t.thread_user,
			tp.post_thread, tp.post_user, t.thread_lastpost, t.thread_lastuser, t.thread_total_replies
			FROM #forum_thread AS t
			LEFT JOIN #forum_post AS tp ON t.thread_id = tp.post_thread
			LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id
			LEFT JOIN #user AS u ON tp.post_user = u.user_id
			LEFT JOIN #user AS lp ON t.thread_lastuser = lp.user_id
			WHERE find_in_set(forum_class, '".USERCLASS_LIST."')
			AND t.thread_lastpost > {$lvisit}
			ORDER BY tp.post_datestamp DESC LIMIT 0,".intval($this->parent->settings['amount']);

		}
		else
		{	// Most recently updated threads up to limit
			$qry = "
			SELECT t.thread_id, t.thread_name AS parent_name, t.thread_datestamp, t.thread_user, t.thread_views, t.thread_lastpost, 
			t.thread_lastuser, t.thread_total_replies, f.forum_id, f.forum_name, f.forum_class, f.forum_sef, u.user_name, lp.user_name AS lp_name
			FROM #forum_thread AS t
			LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id
			LEFT JOIN #user AS u ON t.thread_user = u.user_id
			LEFT JOIN #user AS lp ON t.thread_lastuser = lp.user_id
			WHERE find_in_set(f.forum_class, '".USERCLASS_LIST."')
			ORDER BY t.thread_lastpost DESC LIMIT 0,".intval($this->parent->settings['amount']);
		}


		if(!$results = $this->parent->e107->sql->gen($qry))
		{
			$list_data = LIST_FORUM_2;
		}
		else
		{
			$forumArray = $this->parent->e107->sql->db_getList();
			$path = e_PLUGIN."forum/";

			foreach($forumArray as $forumInfo)
			{
				extract($forumInfo);

				$record = array();

/* Fixes #3601 Removed unused vars, fixed userid extraction

				//last user
				$r_id = substr($thread_lastuser, 0, strpos($thread_lastuser, "."));
				$r_name = substr($thread_lastuser, (strpos($thread_lastuser, ".")+1));
				if (strstr($thread_lastuser, chr(1))) {
					$tmp = explode(chr(1), $thread_lastuser);
					$r_name = $tmp[0];
				}
				$thread_lastuser = $r_id;

				//user
				$u_id = substr($thread_user, 0, strpos($thread_user, "."));
				$u_name = substr($thread_user, (strpos($thread_user, ".")+1));
				$thread_user = $u_id;
*/

				if (isset($thread_anon)) 
				{
					/*
					$tmp = explode(chr(1), $thread_anon);
					$thread_user = $tmp[0];
					$thread_user_ip = $tmp[1];
					*/
					$thread_user = $thread_anon;
				}
				
				$gen = new convert;
				$r_datestamp = $gen->convert_date($thread_lastpost, "short");
				if($thread_total_replies)
				{
					$LASTPOST = "";
					if($lp_name)
					{
						//$LASTPOST = "<a href='".e_HTTP."user.php ?id.{$thread_lastuser}'>$lp_name</a>";
						$uparams = array('id' => $thread_lastuser, 'name' => $lp_name);
						$link = e107::getUrl()->create('user/profile/view', $uparams);
						$LASTPOST = "<a href='".$link."'>".$lp_name."</a>";
					}
					else
					{
						if($thread_lastuser{0} == "0")
						{
							$LASTPOST = substr($thread_lastuser, 2);
						}
						else
						{
							//$LASTPOST = NFPM_L16;
						}
					}
					$LASTPOST .= " ".LIST_FORUM_6." <span class='smalltext'>$r_datestamp</span>";
				}
				else
				{
					$LASTPOST = " - ";
					$LASTPOSTDATE = '';
				}

				if($parent_name == '')
				{
					$parent_name = $thread_name;
				}
				$rowheading	= $this->parent->parse_heading($parent_name);
				//$lnk = ($parent_id ? $thread_id.".post" : $thread_id);
				//"<a href='".e_HTTP."user.php ?id.$thread_user'>$user_name</a>"
				$uparams = array('id' => $thread_user, 'name' => $user_name);
				$link = e107::getUrl()->create('user/profile/view', $uparams);
				$userlink = "<a href='".$link."'>".$user_name."</a>";
				//$record['heading'] = "<a href='".$path."forum_viewtopic.php?$lnk'>".$rowheading."</a>";
				$record['heading'] = '<a href="'.e107::url('forum', 'topic', array('thread_id' => $thread_id, 'thread_sef' => eHelper::title2sef($parent_name), 'forum_sef' => $forum_sef)).'">'.$rowheading.'</a>';
				$record['author'] = ($this->parent->settings['author'] ? ($thread_anon ? $thread_user : $userlink) : "");
				//$record['category'] = ($this->parent->settings['category'] ? "<a href='".$path."forum_viewforum.php?$forum_id'>$forum_name</a>" : "");
				$record['category'] = ($this->parent->settings['category'] ? '<a href="'.e107::url('forum', 'forum', array('forum_sef' => $forum_sef)).'">'.$forum_name.'</a>' : "");
				$record['date'] = ($this->parent->settings['date'] ? $this->parent->getListDate($thread_datestamp) : "");
				$record['icon'] = $bullet;
				$VIEWS = $thread_views;
				$REPLIES = $thread_total_replies;
				if($thread_total_replies)
				{
					$record['info'] = "[ ".LIST_FORUM_3." ".$VIEWS.", ".LIST_FORUM_4." ".$REPLIES.", ".LIST_FORUM_5." ".$LASTPOST." ]";
				}
				else
				{
					$record['info'] = "[ ".LIST_FORUM_3." ".intval($tviews)." ]";
				}

				$list_data[] = $record;
			}
		}
		//return array with 'records', (global)'caption', 'display'
		return array(
			'records'=>$list_data,
			'caption'=>$list_caption,
			'display'=>$list_display
		);
	}
}

?>