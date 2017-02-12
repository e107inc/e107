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



class forum_newforumposts_menu // plugin folder + menu name (without the .php)
{

	private $plugPref = null;
	private $menuPref = null;
	private $forumObj = null;

	function __construct()
	{
		$this->forumObj = new e107forum;
		$this->plugPref = e107::pref('forum'); // general forum preferences.
		$this->menuPref = e107::getMenu()->pref();// ie. popup config details from within menu-manager.

		$this->render();

	}




	function getQuery()
	{
		$max_age = vartrue($this->menuPref['maxage'], 0);
		$max_age = ($max_age == 0) ? '' : '(p.post_datestamp > '.(time()-(int)$max_age*86400).') AND ';

		$forumList = implode(',', $this->forumObj->getForumPermList('view'));

		$qry = "
		SELECT
			p.post_user, p.post_id, p.post_datestamp, p.post_user_anon, p.post_entry,
			t.thread_id, t.thread_datestamp, t.thread_name, u.user_id, u.user_name, u.user_image, u.user_currentvisit, f.forum_sef
		FROM `#forum_post` as p

		LEFT JOIN `#forum_thread` AS t ON t.thread_id = p.post_thread
		LEFT JOIN `#forum` as f ON f.forum_id = t.thread_forum_id
		LEFT JOIN `#user` AS u ON u.user_id = p.post_user
		WHERE {$max_age} p.post_forum IN ({$forumList})
		ORDER BY p.post_datestamp DESC LIMIT 0, ".vartrue($this->menuPref['display'],10);

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

		if($results = $sql->gen($qry))
		{

			if($tp->thumbWidth()  > 250) // Fix for unset image size.
			{
				$tp->setThumbSize(40,40,true);
			}

			$list = "<ul class='media-list newforumposts-menu'>";

			while($row = $sql->fetch())
			{

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
					$list .= "<h4 class='media-header'><a href='{$url}'>{$topic}</a></h4>{$post}<br /><small class='text-muted muted'>".LAN_FORUM_MENU_001." {$poster} {$datestamp}</small>";
				}
				else
				{
					$list .= "<a href='{$url}'>".LAN_FORUM_MENU_001."</a> {$poster} <small class='text-muted muted'>{$datestamp}</small><br />{$post}<br />";
				}

				$list .= "</div></li>";



			}

			$list .= "</ul>";

			$text = $list;
		}
		else
		{
			$text = LAN_FORUM_MENU_002;
		}


		if(!empty($this->menuPref['caption']))
		{
			$caption = !empty($this->menuPref['caption'][e_LANGUAGE])  ? $this->menuPref['caption'][e_LANGUAGE] : $this->menuPref['caption'];
		}
		else
		{
			$caption = LAN_PLUGIN_FORUM_LATESTPOSTS;
		}

	//	e107::debug('menuPref', $this->menuPref);

		$ns->tablerender($caption, $text, 'nfp_menu');

	}

}

new forum_newforumposts_menu;



