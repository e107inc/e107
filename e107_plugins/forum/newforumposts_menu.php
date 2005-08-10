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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/newforumposts_menu.php,v $
|     $Revision: 1.11 $
|     $Date: 2005-08-10 20:24:23 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
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
SELECT tp.thread_name AS parent_name, t.thread_datestamp, t.thread_thread, t.thread_name, t.thread_id, t.thread_user, f.forum_id, f.forum_name, f.forum_class, u.user_name, fp.forum_class FROM #forum_t AS t 
LEFT JOIN #user AS u ON t.thread_user = u.user_id 
LEFT JOIN #forum_t AS tp ON t.thread_parent = tp.thread_id 
LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id 
LEFT JOIN #forum AS fp ON f.forum_parent = fp.forum_id 
WHERE f.forum_class  IN (".USERCLASS_LIST.") 
AND fp.forum_class IN (".USERCLASS_LIST.") 
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
//		extract($forumInfo);
		$datestamp = $gen->convert_date($fi['thread_datestamp'], "short");
		$topic = ($fi['parent_name'] ? "[re: <i>{$fi['parent_name']}</i>]" : "[thread: <i>{$fi['thread_name']}</i>]");
		$topic = strip_tags(preg_replace("#\[\w]|\[\/\w\]#", "", $topic));
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

		$fi['thread_thread'] = strip_tags(preg_replace("#\[\w]|\[\/\w\]#", "", $fi['thread_thread']));
		$fi['thread_thread'] = $tp->toHTML($fi['thread_thread'], FALSE, "emotes_off, no_make_clickable", "", $pref['menu_wordwrap']);

		if (strlen($fi['thread_thread']) > $menu_pref['newforumposts_characters'])
		{
			$fi['thread_thread'] = substr($fi['thread_thread'], 0, $menu_pref['newforumposts_characters']).$menu_pref['newforumposts_postfix'];
		}
				 
		$text .= "<img src='".THEME."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='' /> <a href='".e_PLUGIN."forum/forum_viewtopic.php?{$id}.post'><b>".$poster."</b> on ".$datestamp."</a><br/>";
		if ($menu_pref['newforumposts_title'])
		{
			$text .= $topic."<br />";
		}
		$text .= $fi['thread_thread']."<br /><br />";
	}
}

$ns->tablerender($menu_pref['newforumposts_caption'], $text, 'nfp_menu');
	
?>