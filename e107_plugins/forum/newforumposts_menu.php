<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT'))  exit;

e107::lan('forum','menu',true);  // English_menu.php or {LANGUAGE}_menu.php
include_once(e_PLUGIN.'forum/forum_class.php');


if(!class_exists('forum_newforumposts_menu'))
{
	class forum_newforumposts_menu // plugin folder + menu name (without the .php)
	{

		private $plugPref = null;
		private $menuPref = null;
		private $forumObj = null;
		private $total = array();

		function __construct()
		{
			$this->forumObj = new e107forum;
			$this->plugPref = e107::pref('forum'); // general forum preferences.
			$this->menuPref = e107::getMenu()->pref();// ie. popup config details from within menu-manager.

			// Set some defaults ...
			if (!isset($this->menuPref['title'])) $this->menuPref['title'] = "";
			if (empty($this->menuPref['display'])) $this->menuPref['display'] = 10;
			if (empty($this->menuPref['maxage'])) $this->menuPref['maxage'] = 0;
			if (empty($this->menuPref['characters'])) $this->menuPref['characters'] = 120;
			if (empty($this->menuPref['postfix'])) $this->menuPref['postfix'] = '...';
			if (!isset($this->menuPref['scroll'])) $this->menuPref['scroll'] = "";
			if (empty($this->menuPref['layout'])) $this->menuPref['layout'] = 'default';


			$sql = e107::getDb();

			$this->total['topics'] = $sql->count("forum_thread");
			$this->total['replies'] = $sql->count("forum_post");

			$sql->gen("SELECT sum(thread_views) as sum FROM #forum_thread");
			$tmp = $sql->fetch();
			$this->total['views'] = intval($tmp["sum"]);


			$this->render();

		}




		function getQuery()
		{
			$max_age = vartrue($this->menuPref['maxage'], 0);
			$max_age = ($max_age == 0) ? '' : '(p.post_datestamp > '.(time()-(int)$max_age*86400).') AND ';

			$forumList = implode(',', $this->forumObj->getForumPermList('view'));

			$qry = '';

			$this->menuPref['layout'] = vartrue($this->menuPref['layout'], 'default');
			switch($this->menuPref['layout'])
			{
				case "minimal":
				case "default":

					$qry = "
					SELECT
					p.post_user, p.post_id, p.post_datestamp, p.post_user_anon, p.post_entry,
					t.*,
					u.user_id, u.user_name, u.user_image, u.user_currentvisit,
					lu.user_name as thread_lastuser_username, 
					f.forum_name, f.forum_sef
					FROM `#forum_post` as p
					LEFT JOIN `#forum_thread` AS t ON t.thread_id = p.post_thread
					LEFT JOIN `#forum` as f ON f.forum_id = t.thread_forum_id
					LEFT JOIN `#user` AS u ON u.user_id = p.post_user
					LEFT JOIN `#user` AS lu ON t.thread_lastuser = lu.user_id
					WHERE {$max_age} p.post_forum IN ({$forumList})
					ORDER BY p.post_datestamp DESC LIMIT 0, ".vartrue($this->menuPref['display'],10);
					break;

				 // standardized field names.  thread_user_[user table fields without the '_')
				default:
					 $qry = "
					SELECT t.thread_id, t.thread_name, t.thread_datestamp, t.thread_user, t.thread_views, t.thread_lastpost, t.thread_lastuser, t.thread_total_replies, t.thread_active, 
					MAX(p.post_id) AS post_id,
					f.forum_id, f.forum_name, f.forum_class, f.forum_sef, 
					u.user_name as thread_user_username,  
					u.user_image as thread_user_userimage, 
					u.user_currentvisit as thread_user_usercurrentvisit,
					fp.forum_class,  fp.forum_sef as forum_parent_sef,
					lp.user_name AS thread_lastuser_username
					FROM #forum_thread AS t
					LEFT JOIN #forum_post AS p ON t.thread_id = p.post_thread
					LEFT JOIN #user AS u ON t.thread_user = u.user_id
					LEFT JOIN #user AS lp ON t.thread_lastuser = lp.user_id
					LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id
					LEFT JOIN #forum AS fp ON f.forum_parent = fp.forum_id
					WHERE f.forum_id = t.thread_forum_id AND f.forum_class IN (".USERCLASS_LIST.")
					AND fp.forum_class IN (".USERCLASS_LIST.") 
					GROUP BY t.thread_id  
					ORDER BY t.thread_lastpost DESC LIMIT 0, ".vartrue($this->menuPref['display'],10);
					}


			return $qry;
		}



		// TODO: cache menu.
		function render()
		{
			$tp = e107::getParser();
			$sql = e107::getDb('nfp');
			$pref = e107::getPref();

			$qry = $this->getQuery();
			$ns = e107::getRender();


			$list = null;
			$text = null;

			$layout = 'minimal';

			if (!empty($this->menuPref['title']) && intval($this->menuPref['title']) === 1) // legacy pref value
			{
				$layout = 'default';
			}

			if(!empty($this->menuPref['layout'])) // @see e_menu
			{
				$layout = $this->menuPref['layout'];
			}

			$template = e107::getTemplate('forum','newforumposts_menu',$layout);




			$param = array();

			foreach($this->menuPref as $k=>$v)
			{
				$param['nfp_'.$k] = $v;
			}



			if($results = $sql->gen($qry))
			{

			/*	if($tp->thumbWidth()  > 250) // Fix for unset image size.
				{
					$tp->setThumbSize(40,40,true);
				}*/

				$sc = e107::getScBatch('view', 'forum')->setScVar('param',$param);

				$list = $tp->parseTemplate($template['start'], true);

				while($row = $sql->fetch())
				{
					$row['thread_sef'] = $this->forumObj->getThreadSef($row);



					$sc->setScVar('postInfo', $row);
					$sc->setVars($row);
					$list .= $tp->parseTemplate($template['item'], true, $sc);


	/*
					$datestamp 	= $tp->toDate($row['post_datestamp'], 'relative');
					$id 		= $row['thread_id'];
					$topic 		= ($row['thread_datestamp'] == $row['post_datestamp'] ?  '' : 'Re:');
					$topic 		.= strip_tags($tp->toHTML($row['thread_name'], true, 'emotes_off, no_make_clickable, parse_bb', '', $pref['menu_wordwrap']));

					$row['thread_sef'] = $this->forumObj->getThreadSef($row);

					if($row['post_user_anon'])
					{
						$poster = $row['post_user_anon'];
					}
					else
					{
						if($row['user_name'])
						{
							$poster = "<a href='".e107::getUrl()->create('user/profile/view', array('name' => $row['user_name'], 'id' => $row['post_user']))."'>{$row['user_name']}</a>";
						}
						else
						{
							$poster = '[deleted]';
						}
					}

					$post = strip_tags($tp->toHTML($row['post_entry'], true, 'emotes_off, no_make_clickable', '', $pref['menu_wordwrap']));
					$post = $tp->text_truncate($post, varset($this->menuPref['characters'],120), varset($this->menuPref['postfix'],'...'));

					// Count previous posts for calculating proper (topic) page number for the current post.
					//	$postNum = $sql2->count('forum_post', '(*)', "WHERE post_id <= " . $row['post_id'] . " AND post_thread = " . $row['thread_id'] . " ORDER BY post_id ASC");
					//	$postPage = ceil($postNum / vartrue($this->plugPref['postspage'], 10)); // Calculate (topic) page number for the current post.
					//	$thread = $sql->retrieve('forum_thread', '*', 'thread_id = ' . $row['thread_id']); 	// Load thread for passing it to e107::url().

					// Create URL for post.
					// like: e107_plugins/forum/forum_viewtopic.php?f=post&id=1
					$url = e107::url('forum', 'topic', $row, array(
						'query'    => array(
							'f' => 'post',
							'id'    => intval($row['post_id']) // proper page number
						),
					));


					$list .= "<li class='media'>";

					$list .= "<div class='media-left'>";
					$list .= "<a href='".$url."'>".$tp->toAvatar($row, array('shape'=>'circle'))."</a>";
					$list .= "</div>";

					$list .= "<div class='media-body'>";

					if (!empty($this->menuPref['title']))
					{
						$list .= "<h4 class='media-heading'><a href='{$url}'>{$topic}</a></h4>{$post}<br /><small class='text-muted muted'>".LAN_FORUM_MENU_001." {$poster} {$datestamp}</small>";
					}
					else
					{
						$list .= "<a href='{$url}'>".LAN_FORUM_MENU_001."</a> {$poster} <small class='text-muted muted'>{$datestamp}</small><br />{$post}<br />";
					}

					$list .= "</div></li>";
	*/


				}

				$TOTALS = array('TOTAL_TOPICS'=>$this->total['topics'], 'TOTAL_VIEWS'=>$this->total['views'], 'TOTAL_REPLIES'=>$this->total['replies']);

				$list .= $tp->parseTemplate($template['end'], true, $TOTALS);

				$text = $list;
			}
			else
			{
				$text = LAN_FORUM_MENU_002;
			}


			if(!empty($this->menuPref['caption']))
			{
				if (array_key_exists(e_LANGUAGE, $this->menuPref['caption']))
				{
					// Language key exists
					$caption = vartrue($this->menuPref['caption'][e_LANGUAGE], LAN_PLUGIN_FORUM_LATESTPOSTS);
				}
				elseif (is_array($this->menuPref['caption']))
				{
					// Language key not found
					$keys = array_keys($caption = $this->menuPref['caption']);
					// Just first language key from the list
					$caption = vartrue($this->menuPref['caption'][$keys[0]], LAN_PLUGIN_FORUM_LATESTPOSTS);
				}
				else
				{
					// No multilan array, just plain text
					$caption = vartrue($this->menuPref['caption'], LAN_PLUGIN_FORUM_LATESTPOSTS);
				}
				//$caption = !empty($this->menuPref['caption'][e_LANGUAGE])  ? $this->menuPref['caption'][e_LANGUAGE] : $this->menuPref['caption'];
			}


			if(empty($caption))
			{
				$caption = LAN_PLUGIN_FORUM_LATESTPOSTS;
			}

			if(!empty($this->menuPref['scroll']))
			{
				$text = "<div class='newforumposts-menu-scroll' style='border: 0; width: auto; height: ".intval($this->menuPref['scroll'])."px; overflow: auto; '>".$text."</div>";
			}
		//	e107::debug('menuPref', $this->menuPref);

			$ns->tablerender($caption, $text, 'nfp_menu');

		}

	}
}


new forum_newforumposts_menu;



