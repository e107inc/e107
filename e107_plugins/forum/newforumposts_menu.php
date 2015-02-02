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

global $menu_pref;

$e107 = e107::getInstance();
$tp = e107::getParser();
$sql = e107::getDb();
$gen = new convert;
$pref = e107::getPref();
e107::lan('forum','menu',true);  // English_menu.php or {LANGUAGE}_menu.php

// include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_newforumposts_menu.php');
// include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/'.e_LANGUAGE.'_menu.php');
include_once(e_PLUGIN.'forum/forum_class.php');

$max_age = vartrue($menu_pref['newforumposts_maxage'], 0);
$max_age = $max_age == 0 ? '' : '(t.post_datestamp > '.(time()-(int)$max_age*86400).') AND ';

$forum = new e107forum;
$forumList = implode(',', $forum->getForumPermList('view'));
//TODO: Use query from forum class to get thread list
$qry = "
SELECT
	p.post_user, p.post_id, p.post_datestamp, p.post_user_anon, p.post_entry,
	t.thread_id, t.thread_datestamp, t.thread_name, u.user_name
FROM `#forum_post` as p
LEFT JOIN `#forum_thread` AS t ON t.thread_id = p.post_thread
LEFT JOIN `#user` AS u ON u.user_id = p.post_user
WHERE {$maxage} p.post_forum IN ({$forumList})
ORDER BY p.post_datestamp DESC LIMIT 0, ".$menu_pref['newforumposts_display'];




if($results = $sql->gen($qry))
{
	$text = "<ul>";
	
	while($row = $sql->fetch(MYSQL_ASSOC))
	{
		$datestamp 	= $gen->convert_date($row['post_datestamp'], 'relative');
		$id 		= $row['thread_id'];
		$topic 		= ($row['thread_datestamp'] == $row['post_datestamp'] ?  '' : 'Re:');
		$topic 		.= strip_tags($tp->toHTML($row['thread_name'], true, 'emotes_off, no_make_clickable, parse_bb', '', $pref['menu_wordwrap']));
		
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
		$post = $tp->text_truncate($post, $menu_pref['newforumposts_characters'], $menu_pref['newforumposts_postfix']);

		$url = e107::getUrl()->create('forum/thread/last', $row);
	
		$text .= "<li>";
		
		if ($menu_pref['newforumposts_title'])
		{
			$text .= "<a href='{$url}'>{$topic}</a><br />{$post}<br /><small class='muted'>".LAN_FORUM_MENU_001." {$poster} {$datestamp}</small>";
		}
		else
		{
			$text .= "<a href='{$url}'>".LAN_FORUM_MENU_001."</a> {$poster} <small class='muted'>{$datestamp}</small><br />{$post}<br />";
		}
		
		$text .= "</li>";
		
	}
	
	$text .= "</ul>";
}
else
{
	$text = LAN_FORUM_MENU_002;
}
e107::getRender()->tablerender($menu_pref['newforumposts_caption'], $text, 'nfp_menu');
