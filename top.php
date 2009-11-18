<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc 
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/top.php,v $
|     $Revision: 1.6 $
|     $Date: 2009-11-18 01:04:24 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once ('class2.php');
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

if (!e_QUERY)
{
	$action = 'top';
	$subaction = 'all';
	$from = 0;
	$view = 10;
}
else
{
	$tmp = explode('.', e_QUERY);
	$from = intval($tmp[0]);
	$action = $tmp[1];
	$subaction = $tmp[2];
	$view = ($tmp[3] ? intval($tmp[3]) : 10);
}
if ($action == 'top')
{
	define('e_PAGETITLE', ': '.LAN_8);
} elseif ($action == 'active')
{
	define('e_PAGETITLE', ': '.LAN_7);
}


require_once (HEADERF);
if ($action == 'active')
{
	require_once (e_HANDLER.'userclass_class.php');
	require_once (e_PLUGIN.'forum/forum_class.php');
	$forum = new e107forum;

	$forumList = implode(',', $forum->permList['view']);

	$qry = "
	SELECT
		t.*, u.user_name, ul.user_name AS user_last, f.forum_name
	FROM `#forum_thread` as t
	LEFT JOIN `#forum` AS f ON f.forum_id = t.thread_forum_id
	LEFT JOIN `#user` AS u ON u.user_id = t.thread_user
	LEFT JOIN `#user` AS ul ON ul.user_id = t.thread_lastuser
	WHERE t.thread_forum_id IN ({$forumList})
	ORDER BY t.thread_views DESC
	LIMIT
		{$from}, {$view}
	";

	if ($sql->db_Select_gen($qry))
	{
		$text = "<div style='text-align:center'>\n<table style='width:auto' class='fborder'>\n";
		if (!is_object($gen))
		{
			$gen = new convert;
		}

		$text .= "<tr>
			<td style='width:5%' class='forumheader'>&nbsp;</td>
			<td style='width:45%' class='forumheader'>".LAN_1."</td>
			<td style='width:15%; text-align:center' class='forumheader'>".LAN_2."</td>
			<td style='width:5%; text-align:center' class='forumheader'>".LAN_3."</td>
			<td style='width:5%; text-align:center' class='forumheader'>".LAN_4."</td>
			<td style='width:25%; text-align:center' class='forumheader'>".LAN_5."</td>
			</tr>\n";

		while ($row = $sql->db_Fetch(MYSQL_ASSOC))
		{
			if ($row['user_name'])
			{
				$POSTER = "<a href='".$e107->url->getUrl('core:user', 'main', "func=profile&id={$row['thread_user']}")."'>{$row['user_name']}</a>";
			}
			else
			{
				$POSTER = $row['thread_user_anon'];
			}

			$LINKTOTHREAD = $e107->url->getUrl('forum', 'thread', "func=view&id={$row['thread_id']}");
			$LINKTOFORUM = $e107->url->getUrl('forum', 'forum', "func=view&id={$row['thread_forum_id']}");

			$lastpost_datestamp = $gen->convert_date($row['thread_lastpost'], 'forum');
			if ($row['user_last'])
			{
				$LASTPOST = "<a href='".$e107->url->getUrl('core:user', 'main', "func=profile&id={$row['thread_lastuser']}")."'>{$row['user_last']}</a><br />".$lastpost_datestamp;
			}
			else
			{
				$LASTPOST = $row['thread_lastuser_anon'].'<br />'.$lastpost_datestamp;
			}

			$text .= "<tr>
					<td style='width:5%; text-align:center' class='forumheader3'><img src='".e_PLUGIN_ABS."forum/images/".IMODE."/new_small.png' alt='' /></td>
					<td style='width:45%' class='forumheader3'><b><a href='{$LINKTOTHREAD}'>{$row['thread_name']}</a></b> <span class='smalltext'>(<a href='{$LINKTOFORUM}'>{$row['forum_name']}</a>)</span></td>
					<td style='width:15%; text-align:center' class='forumheader3'>{$POSTER}</td>
					<td style='width:5%; text-align:center' class='forumheader3'>{$row['thread_views']}</td>
					<td style='width:5%; text-align:center' class='forumheader3'>{$row['thread_total_replies']}</td>
					<td style='width:25%; text-align:center' class='forumheader3'>{$LASTPOST}</td>
					</tr>\n";
		}

		$text .= "</table>\n</div>";

		$ns->tablerender(LAN_7, $text, 'nfp');
		require_once (e_HANDLER.'np_class.php');
		$ftotal = $sql->db_Count('forum_thread', '(*)', 'WHERE 1');
		$ix = new nextprev('top.php', $from, $view, $ftotal, '', 'active.forum.'.$view);
	}
}
if ($action == 'top')
{
	require_once (e_HANDLER.'level_handler.php');
	define('IMAGE_rank_main_admin_image', ($pref['rank_main_admin_image'] && file_exists(THEME."forum/".$pref['rank_main_admin_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_main_admin_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/main_admin.png' alt='' />"));
	define('IMAGE_rank_admin_image', ($pref['rank_admin_image'] && file_exists(THEME."forum/".$pref['rank_admin_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_admin_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/admin.png' alt='' />"));
	define('IMAGE_rank_moderator_image', ($pref['rank_moderator_image'] && file_exists(THEME."forum/".$pref['rank_moderator_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_moderator_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/moderator.png' alt='' />"));

	if ($subaction == 'forum' || $subaction == 'all')
	{
		$qry = "
		SELECT ue.*, u.* FROM `#user_extended` AS ue
		LEFT JOIN `#user` AS u ON u.user_id = ue.user_extended_id
		WHERE ue.user_plugin_forum_posts > 0
		ORDER BY ue.user_plugin_forum_posts DESC LIMIT {$from}, {$view}
		";
		//		$top_forum_posters = $sql->db_Select("user", "*", "`user_forums` > 0 ORDER BY user_forums DESC LIMIT ".$from.", ".$view."");
		$text = "
			<div style='text-align:center'>
			<table style='width:95%' class='fborder'>
			<tr>
			<td style='width:10%; text-align:center' class='forumheader3'>&nbsp;</td>
			<td style='width:50%' class='forumheader3'>".TOP_LAN_1."</td>
			<td style='width:10%; text-align:center' class='forumheader3'>".TOP_LAN_2."</td>
			<td style='width:30%; text-align:center' class='forumheader3'>".TOP_LAN_6."</td>
			</tr>\n";
		$counter = 1 + $from;
		if ($e107->sql->db_Select_gen($qry))
		{
			while ($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$ldata = get_level($row['user_id'], $row['user_plugin_forum_posts'], $row['user_comments'], $row['user_chats'], $row['user_visits'], $row['user_join'], $row['user_admin'], $row['user_perms'], $pref);
				$text .= "<tr>
					<td style='width:10%; text-align:center' class='forumheader3'>{$counter}</td>
					<td style='width:50%' class='forumheader3'><a href='".e_HTTP."user.php?id.{$row['user_id']}'>{$row['user_name']}</a></td>
					<td style='width:10%; text-align:center' class='forumheader3'>{$row['user_plugin_forum_posts']}</td>
					<td style='width:30%; text-align:center' class='forumheader3'>".(strstr($ldata[0], "LAN") ? $ldata[1] : $ldata[0])."</td>
					</tr>";
				$counter++;
			}
		}
		$text .= "</table>\n</div>";
		$ns->tablerender(TOP_LAN_0, $text);
		if ($subaction == 'forum')
		{
			require_once (e_HANDLER.'np_class.php');
			$ftotal = $sql->db_Count('user_extended', '(*)', 'WHERE `user_plugin_forum_posts` > 0');
			$ix = new nextprev('top.php', $from, $view, $ftotal, 'Forum Posts', 'top.forum.'.$view);
		}
	}


	if ($subaction == 'comment' || $subaction == 'all')
	{
		$top_forum_posters = $sql->db_Select("user", "*", "`user_comments` > 0 ORDER BY user_comments DESC LIMIT 0, 10");
		$text = "
			<div style='text-align:center'>
			<table style='width:95%' class='fborder'>
			<tr>
			<td style='width:10%; text-align:center' class='forumheader3'>&nbsp;</td>
			<td style='width:50%' class='forumheader3'>".TOP_LAN_1."</td>
			<td style='width:10%; text-align:center' class='forumheader3'>".TOP_LAN_4."</td>
			<td style='width:30%; text-align:center' class='forumheader3'>".TOP_LAN_6."</td>
			</tr>\n";
		$counter = 1;
		while ($row = $sql->db_Fetch())
		{
			extract($row);
			$ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);
			$text .= "<tr>
				<td style='width:10%; text-align:center' class='forumheader3'>{$counter}</td>
				<td style='width:50%' class='forumheader3'><a href='".e_HTTP."user.php?id.{$user_id}'>{$user_name}</a></td>
				<td style='width:10%; text-align:center' class='forumheader3'>{$user_comments}</td>
				<td style='width:30%; text-align:center' class='forumheader3'>".(strstr($ldata[0], "LAN") ? $ldata[1] : $ldata[0])."</td>
				</tr>";
			$counter++;
		}
		$text .= "</table>\n</div>";
		$ns->tablerender(TOP_LAN_3, $text);
	}

	if ($subaction == "chat" || $subaction == "all")
	{
		$top_forum_posters = $sql->db_Select("user", "*", "`user_chats` > 0 ORDER BY user_chats DESC LIMIT 0, 10");
		$text = "
			<div style='text-align:center'>
			<table style='width:95%' class='fborder'>
			<tr>
			<td style='width:10%; text-align:center' class='forumheader3'>&nbsp;</td>
			<td style='width:20%' class='forumheader3'>".TOP_LAN_1."</td>
			<td style='width:10%; text-align:center' class='forumheader3'>".TOP_LAN_2."</td>
			<td style='width:30%; text-align:center' class='forumheader3'>".TOP_LAN_6."</td>
			</tr>\n";
		$counter = 1;
		while ($row = $sql->db_Fetch())
		{
			extract($row);
			$ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);
			$text .= "<tr>
				<td style='width:10%; text-align:center' class='forumheader3'>{$counter}</td>
				<td style='width:50%' class='forumheader3'><a href='".e_HTTP."user.php?id.{$user_id}'>{$user_name}</a></td>
				<td style='width:10%; text-align:center' class='forumheader3'>{$user_chats}</td>
				<td style='width:30%; text-align:center' class='forumheader3'>".(strstr($ldata[0], "LAN") ? $ldata[1] : $ldata[0])."</td>
				</tr>";
			$counter++;
		}
		$text .= "</table>\n</div>";
		$ns->tablerender(TOP_LAN_5, $text);
	}
}
require_once (FOOTERF);
?>