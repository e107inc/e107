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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/forum/newforumposts_menu.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-06-08 19:15:08 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

global $tp;
$gen = new convert;

if (file_exists(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_newforumposts_menu.php"))
{
	include_once(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_newforumposts_menu.php");
}
else
{
	include_once(e_PLUGIN."forum/languages/English/lan_newforumposts_menu.php");
}

$query2 = "
SELECT tp.thread_name AS parent_name, t.thread_datestamp , t.thread_thread, t.thread_name, t.thread_id, t.thread_user, f.forum_id, f.forum_name, f.forum_class, u.user_name, fp.forum_class FROM #forum_t AS t
LEFT JOIN #user AS u ON t.thread_user = u.user_id
LEFT JOIN #forum_t AS tp ON t.thread_parent = tp.thread_id
LEFT JOIN #forum AS f ON (f.forum_id = t.thread_forum_id
AND f.forum_class IN (".USERCLASS_LIST."))
LEFT JOIN #forum AS fp ON f.forum_parent = fp.forum_id
WHERE fp.forum_class IN (".USERCLASS_LIST.")
ORDER BY t.thread_datestamp DESC LIMIT 0, ".$menu_pref['newforumposts_display'];

$results = $sql->db_Select_gen($query2);

if(!$results)
{
	// no posts yet ..
	$text = NFP_2;
}
else
{
	$text = "";
	$forumArray = $sql->db_getList();
	foreach($forumArray as $fi)
	{
		$datestamp = $gen->convert_date($fi['thread_datestamp'], "short");
		$topic = ($fi['parent_name'] ? "Re: <i>{$fi['parent_name']}</i>" : "<i>{$fi['thread_name']}</i>");
		$topic = strip_tags($tp->toHTML($topic, TRUE, "emotes_off, no_make_clickable, parse_bb", "", $pref['menu_wordwrap']));
		$id = $fi['thread_id'];

		if($fi['user_name'])
		{
			$poster = $fi['user_name'];
		}
		else
		{
			$x = explode(chr(1), $fi['thread_user']);
			$tmp = explode(".", $x[0], 2);
			if($tmp[1])
			{
				$poster = $tmp[1];
			}
			else
			{
				$poster = "[deleted]";
			}
		}

		$fi['thread_thread'] = strip_tags($tp->toHTML($fi['thread_thread'], TRUE, "emotes_off, no_make_clickable", "", $pref['menu_wordwrap']));

		$fi['thread_thread'] = $tp->text_truncate($fi['thread_thread'], $menu_pref['newforumposts_characters'], $menu_pref['newforumposts_postfix']);

		if ($menu_pref['newforumposts_title'])
		{
			$text .= "<img src='".THEME_ABS."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='' /> <a href='".e_PLUGIN."forum/forum_viewtopic.php?{$id}.post'>".$topic."</a><br />".$fi['thread_thread']."<br />".NFP_11." ".$poster."<br />".$datestamp."<br/><br />";
		}
		else
		{
			$text .= "<img src='".THEME_ABS."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='' /> <a href='".e_PLUGIN."forum/forum_viewtopic.php?{$id}.post'>".NFP_11." ".$poster."</a><br />".$fi['thread_thread']."<br />".$datestamp."<br/><br />";
		}
	}
}

$ns->tablerender($menu_pref['newforumposts_caption'], $text, 'nfp_menu');

?>