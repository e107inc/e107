<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/newforumposts_menu.php,v $
 * $Revision: 1.11 $
 * $Date: 2009-11-19 09:37:13 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$e107 = e107::getInstance();
$gen = new convert;

include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_newforumposts_menu.php');
include_once(e_PLUGIN.'forum/forum_class.php');

$max_age = varset($menu_pref['newforumposts_maxage'], 0);
$max_age = $max_age == 0 ? '' : '(t.post_datestamp > '.(time()-(int)$max_age*86400).') AND ';

$forum = new e107forum; 
$forumList = implode(',', $forum->permList['view']);

$qry = "
SELECT
	p.post_user, p.post_id, p.post_datestamp, p.post_user_anon, p.post_entry, 
	t.thread_id, t.thread_datestamp, t.thread_name, u.user_name 
FROM `#forum_post` as p
LEFT JOIN `#forum_thread` AS t ON t.thread_id = p.post_thread
LEFT JOIN `#user` AS u ON u.user_id = p.post_user
WHERE {$maxage} p.post_forum IN ({$forumList})
ORDER BY p.post_datestamp DESC LIMIT 0, ".$menu_pref['newforumposts_display'];

if($results = $e107->sql->db_Select_gen($qry))
{
	while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
	{
		$datestamp = $gen->convert_date($row['post_datestamp'], 'short');
		$id = $row['thread_id'];
		$topic = ($row['thread_datestamp'] == $row['post_datestamp'] ?  '' : 'Re:');
		$topic .= strip_tags($e107->tp->toHTML($row['thread_name'], true, 'emotes_off, no_make_clickable, parse_bb', '', $pref['menu_wordwrap']));
		if($row['post_user_anon'])
		{
			$poster = $row['post_user_anon'];
		}
		else
		{
			if($row['user_name'])
			{
				$poster = "<a href='".$e107->url->getUrl('core:user', 'main', array('func' => 'profile', 'id' => $row['post_user']))."'>{$row['user_name']}</a>";
			}
			else
			{
				$poster = '[deleted]';
			}
		}

		$post = strip_tags($e107->tp->toHTML($row['post_entry'], true, 'emotes_off, no_make_clickable', '', $pref['menu_wordwrap']));
		$post = $e107->tp->text_truncate($post, $menu_pref['newforumposts_characters'], $menu_pref['newforumposts_postfix']);

		$url = $e107->url->getUrl('forum', 'thread', array('func' => 'last', 'id' => $id));
		//TODO legacy bullet is not use here anymore
		//$bullet = "<img src='".THEME_ABS.'images/'.(defined('BULLET') ? BULLET : 'bullet2.gif')."' alt='' />";
		
		
		if ($menu_pref['newforumposts_title'])
		{
			$text .= "<a href='{$url}'>{$topic}</a><br />{$post}<br />".NFP_11." {$poster}<br />{$datestamp}<br /><br />";
		}
		else
		{
			$text .= "<a href='{$url}'>".NFP_11." {$poster}</a><br />{$post}<br />{$datestamp}<br/><br />";
		}
	}
}
else
{
	$text = NFP_2;	

}
$e107->ns->tablerender($menu_pref['newforumposts_caption'], $text, 'nfp_menu');
?>