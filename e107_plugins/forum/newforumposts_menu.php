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

		private $plugPref;
		private $menuPref;
		private $forumObj;
		private $total = array();
		private $cacheTag = 'nfpCache';
		private $cacheTime = 1; // cache time in minutes.

		function __construct()
		{
			$this->plugPref = e107::pref('forum'); // general forum preferences.
//			$this->menuPref = e107::getMenu()->pref();// ie. popup config details from within menu-manager.
			$menuPrefs = e107::getMenu()->pref();// ie. popup config details from within menu-manager.
			$this->forumObj = new e107forum;

			if(is_string($menuPrefs))
			{
				parse_str($menuPrefs, $this->menuPref);
			}
			else
			{
				$this->menuPref = $menuPrefs;
			}

//			echo "<hr><hr><hr>";
//			var_dump($this->menuPref);

			// Set some defaults ...
			if (!isset($this->menuPref['title'])) $this->menuPref['title'] = "";
			if (empty($this->menuPref['display'])) $this->menuPref['display'] = 10;
			if (empty($this->menuPref['maxage'])) $this->menuPref['maxage'] = 0;
			if (empty($this->menuPref['characters'])) $this->menuPref['characters'] = 120;
			if (empty($this->menuPref['postfix'])) $this->menuPref['postfix'] = '...';
			if (!isset($this->menuPref['scroll'])) $this->menuPref['scroll'] = "";
			if (empty($this->menuPref['layout'])) $this->menuPref['layout'] = 'default';

//			echo "<hr><hr><hr>";
//			var_dump($this->menuPref);

            $this->cacheTag .= "_".$this->menuPref['layout'];

            if($text = e107::getCache()->retrieve($this->cacheTag, $this->cacheTime, true))
            {
                e107::getDebug()->log("New Forum Posts Menu Cache Rendered");
                $caption = $this->getCaption();
                e107::getRender()->tablerender($caption, $text, 'nfp_menu');
                return null;
            }

/*
			$sql = e107::getDb();

			$this->total['topics'] = $sql->count("forum_thread");
			$this->total['replies'] = $sql->count("forum_post");

			if($sql->gen("SELECT sum(thread_views) as sum FROM #forum_thread"))
			{
				$tmp = $sql->fetch();
				$this->total['views'] = intval($tmp["sum"]);
			}
*/
			$this->render();

		}

		private function getQuery()
		{
			$max_age = vartrue($this->menuPref['maxage'], 0);

			$viewPerm = $this->forumObj->getForumPermList('view');

			// if forumlist is empty (no forum categories created yet), return false;
			if(empty($viewPerm))
			{
				return false;
			}

			$limit = (int) vartrue($this->menuPref['display'], 10);
			$classList = array_map('intval', explode(',', USERCLASS_LIST));

			$qb = e107::getDb('nfp')->createQueryBuilder();

			$this->menuPref['layout'] = vartrue($this->menuPref['layout'], 'default');
			switch($this->menuPref['layout'])
			{
				case "minimal":
				case "default":

					$qb->select(
						'p.post_user', 'p.post_id', 'p.post_datestamp', 'p.post_user_anon', 'p.post_entry',
						't.*',
						'u.user_id', 'u.user_name', 'u.user_image', 'u.user_currentvisit',
						'lu.user_name as thread_lastuser_username',
						'f.forum_name', 'f.forum_sef'
					)
						->from('forum_post', 'p')
						->leftJoin('forum_thread', 't', $qb->expr()->compareColumns('t.thread_id', 'p.post_thread'))
						->leftJoin('forum', 'f', $qb->expr()->compareColumns('f.forum_id', 't.thread_forum_id'))
						->leftJoin('user', 'u', $qb->expr()->compareColumns('u.user_id', 'p.post_user'))
						->leftJoin('user', 'lu', $qb->expr()->compareColumns('t.thread_lastuser', 'lu.user_id'));

					if($max_age != 0)
					{
						$qb->where('p.post_datestamp', '>', time() - (int) $max_age * 86400);
					}

					$qb->whereIn('p.post_forum', $viewPerm)
						->orderBy('p.post_datestamp', 'DESC')
						->setFirstResult(0)->setMaxResults($limit);
					break;

				 // standardized field names.  thread_user_[user table fields without the '_')
				default:
					$qb->select(
						't.thread_id', 't.thread_name', 't.thread_datestamp', 't.thread_user', 't.thread_views', 't.thread_lastpost', 't.thread_lastuser', 't.thread_total_replies', 't.thread_active',
						'MAX(p.post_id) AS post_id',
						'f.forum_id', 'f.forum_name', 'f.forum_class', 'f.forum_sef',
						'u.user_name as thread_user_username',
						'u.user_image as thread_user_userimage',
						'u.user_currentvisit as thread_user_usercurrentvisit',
						'fp.forum_class', 'fp.forum_sef as forum_parent_sef',
						'lp.user_name AS thread_lastuser_username'
					)
						->from('forum_thread', 't')
						->leftJoin('forum_post', 'p', $qb->expr()->compareColumns('t.thread_id', 'p.post_thread'))
						->leftJoin('user', 'u', $qb->expr()->compareColumns('t.thread_user', 'u.user_id'))
						->leftJoin('user', 'lp', $qb->expr()->compareColumns('t.thread_lastuser', 'lp.user_id'))
						->leftJoin('forum', 'f', $qb->expr()->compareColumns('f.forum_id', 't.thread_forum_id'))
						->leftJoin('forum', 'fp', $qb->expr()->compareColumns('f.forum_parent', 'fp.forum_id'))
						->whereColumn('f.forum_id', 't.thread_forum_id')
						->whereIn('f.forum_class', $classList)
						->whereIn('fp.forum_class', $classList)
						->groupBy('t.thread_id')
						->orderBy('t.thread_lastpost', 'DESC')
						->setFirstResult(0)->setMaxResults($limit);
			}

			return $qb;
		}

		private function render()
		{
			$tp = e107::getParser();
		//	$pref = e107::getPref();

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

			if($qry)
			{
				if($results = $qry->fetchAll())
				{
					/*	if($tp->thumbWidth()  > 250) // Fix for unset image size.
					{
						$tp->setThumbSize(40,40,true);
					}*/

					$sc = e107::getScBatch('view', 'forum')->setScVar('param',$param);

//					$list = $tp->parseTemplate($template['start'], true);
					$text = $tp->parseTemplate($template['start'], true);

					foreach($results as $row)
					{
//						var_dump ($row);
//						echo "<hr>";

						$row['thread_sef'] = $this->forumObj->getThreadSef($row);

						$sc->setScVar('postInfo', $row);
						$sc->setVars($row);
//						$list .= $tp->parseTemplate($template['item'], true, $sc);
						$text .= $tp->parseTemplate($template['item'], true, $sc);

						++$total_topics;						
						$total_views += $row['thread_views'];						
						$total_replies += $row['thread_total_replies'];						

					}

//					$TOTALS = array('TOTAL_TOPICS'=>$this->total['topics'], 'TOTAL_VIEWS'=>$this->total['views'], 'TOTAL_REPLIES'=>$this->total['replies']);
					$TOTALS = array('TOTAL_TOPICS'=>$total_topics, 'TOTAL_VIEWS'=>$total_views, 'TOTAL_REPLIES'=>$total_replies);

//					$list .= $tp->parseTemplate($template['end'], true, $TOTALS);
					$text .= $tp->parseTemplate($template['end'], true, $TOTALS);
//
//					$text = $list;
				}
				else
				{
					$text = LAN_FORUM_MENU_002;
				}
			}
			else
			{
				$text = LAN_FORUM_MENU_016;
			}
//var_dump ($text);
            $caption = $this->getCaption();

			if(!empty($this->menuPref['scroll']))
			{
				$text = "<div class='newforumposts-menu-scroll' style='border: 0; width: auto; height: ".intval($this->menuPref['scroll'])."px; overflow: auto; '>".$text."</div>";
			}
		//	e107::debug('menuPref', $this->menuPref);

		    e107::getCache()->set($this->cacheTag, $text, true);

			$ns->tablerender($caption, $text, 'nfp_menu');

		}

        private function getCaption()
        {
            if (!empty($this->menuPref['caption']))
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

            if (empty($caption))
            {
                $caption = LAN_PLUGIN_FORUM_LATESTPOSTS;
            }

            return $caption;
        }

	}

}

new forum_newforumposts_menu;