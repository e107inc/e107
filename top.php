<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Top page
 *
 * $URL$
 * $Id$
 *
*/
require_once('class2.php');

$ns = e107::getRender();
$pref = e107::getPref();
$sql = e107::getDb();

if(!defined('IMODE')) define('IMODE', 'lite'); // BC

e107::coreLan('top');

$action = 'top';
$subaction = 'all';
$from = 0;
$view = 10;

if (e_QUERY)
{
	$tmp = explode('.', e_QUERY);
	$from = intval(varset($tmp[0], 0));
	$action = varset($tmp[1], 'top');
	$subaction = varset($tmp[2], 'all');
	$view = (isset($tmp[3]) ? intval($tmp[3]) : 10);
}
if ($action == 'top')
{
	define('e_PAGETITLE', LAN_8);
} 
elseif ($action == 'active')
{
	define('e_PAGETITLE', LAN_7);
}
else
{
	e107::redirect();
	exit;
}	


require_once(HEADERF);
if ($action == 'active')
{
	require_once (e_HANDLER.'userclass_class.php');
	require_once (e_PLUGIN.'forum/forum_class.php');
	$forum = new e107forum();

	$forumList = implode(',', $forum->getForumPermList('view'));

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

	if ($sql->gen($qry))
	{
		$text = "<div>\n<table style='width:auto' class='table fborder'>\n";
		$gen = e107::getDate();

		$text .= "<tr>
			<th style='width:5%' class='forumheader'>&nbsp;</th>
			<th style='width:45%' class='forumheader'>".LAN_1."</th>
			<th style='width:15%; text-align:center' class='forumheader'>".LAN_2."</th>
			<th style='width:5%; text-align:center' class='forumheader'>".LAN_3."</th>
			<th style='width:5%; text-align:center' class='forumheader'>".LAN_4."</th>
			<th style='width:25%; text-align:center' class='forumheader'>".LAN_5."</th>
			</tr>\n";

		while ($row = $sql->fetch())
		{
			if ($row['user_name'])
			{
				$POSTER = "<a href='".e107::getUrl()->create('user/profile/view', "name={$row['user_name']}&id={$row['thread_user']}")."'>{$row['user_name']}</a>";
			}
			else
			{
				$POSTER = $row['thread_user_anon'];
			}

			$LINKTOTHREAD = e107::getUrl()->create('forum/thread/view', array('id' =>$row['thread_id'])); //$e107->url->getUrl('forum', 'thread', "func=view&id={$row['thread_id']}");
			$LINKTOFORUM = e107::getUrl()->create('forum/forum/view', array('id' => $row['thread_forum_id'])); //$e107->url->getUrl('forum', 'forum', "func=view&id={$row['thread_forum_id']}");

			$lastpost_datestamp = $gen->convert_date($row['thread_lastpost'], 'forum');
			if ($row['user_last'])
			{
				$LASTPOST = "<a href='".e107::getUrl()->create('user/profile/view', "name={$row['user_last']}&id={$row['thread_lastuser']}")."'>{$row['user_last']}</a><br />".$lastpost_datestamp;
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

		$ftotal = $sql->db_Count('forum_thread', '(*)', 'WHERE `thread_parent` = 0');
		$parms = "{$ftotal},{$view},{$from},".e_SELF.'?[FROM].active.forum.'.$view;
		$text .= "<div class='nextprev'>".$tp->parseTemplate("{NEXTPREV={$parms}}").'</div>';
		$ns->tablerender(LAN_7, $text, 'nfp');
		/*
		require_once (e_HANDLER.'np_class.php');
		$ftotal = $sql->db_Count('forum_thread', '(*)', 'WHERE 1');
		$ix = new nextprev('top.php', $from, $view, $ftotal, '', 'active.forum.'.$view);
		*/
	}
}
if ($action == 'top')
{
	//require_once (e_HANDLER.'level_handler.php');
	$rank = e107::getRank();

	define('IMAGE_rank_main_admin_image', ($pref['rank_main_admin_image'] && file_exists(THEME."forum/".$pref['rank_main_admin_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_main_admin_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/main_admin.png' alt='' />"));
	define('IMAGE_rank_admin_image', ($pref['rank_admin_image'] && file_exists(THEME."forum/".$pref['rank_admin_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_admin_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/admin.png' alt='' />"));
	define('IMAGE_rank_moderator_image', ($pref['rank_moderator_image'] && file_exists(THEME."forum/".$pref['rank_moderator_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_moderator_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/moderator.png' alt='' />"));

	if ($subaction == 'forum' || $subaction == 'all')
	{
		require_once (e_PLUGIN.'forum/forum_class.php');
		$forum = new e107forum();

		$qry = "
		SELECT ue.*, u.* FROM `#user_extended` AS ue
		LEFT JOIN `#user` AS u ON u.user_id = ue.user_extended_id
		WHERE ue.user_plugin_forum_posts > 0
		ORDER BY ue.user_plugin_forum_posts DESC LIMIT {$from}, {$view}
		";
		//		$top_forum_posters = $sql->db_Select("user", "*", "`user_forums` > 0 ORDER BY user_forums DESC LIMIT ".$from.", ".$view."");
		$text = "
			<div>
			<table style='width:95%' class='table table-striped fborder'>
			<tr>
			<th style='width:10%; text-align:center' class='forumheader3'>&nbsp;</th>
			<th style='width:50%' class='forumheader3'>".TOP_LAN_1."</th>
			<th style='width:10%; text-align:center' class='forumheader3'>".TOP_LAN_2."</th>
			<th style='width:30%; text-align:center' class='forumheader3'>".TOP_LAN_6."</th>
			</tr>\n";
		$counter = 1 + $from;
		$sql2 = e107::getDb('sql2');
		if ($sql2->gen($qry))
		{
			while ($row = $sql2->fetch())
			{
				//$ldata = get_level($row['user_id'], $row['user_plugin_forum_posts'], $row['user_comments'], $row['user_chats'], $row['user_visits'], $row['user_join'], $row['user_admin'], $row['user_perms'], $pref);
				$ldata = $rank->getRanks($row, (USER && $forum->isModerator(USERID)));

				if(vartrue($ldata['special']))
				{
					$r = $ldata['special'];
				}
				else
				{
					$r = $ldata['pic'] ? $ldata['pic'] : defset($ldata['name'], $ldata['name']);
				}
				if(!$r) $r = 'n/a';
				$text .= "<tr>
					<td style='width:10%; text-align:center' class='forumheader3'>{$counter}</td>
					<td style='width:50%' class='forumheader3'><a href='".e107::getUrl()->create('user/profile/view', 'id='.$row['user_id'].'&name='.$row['user_name'])."'>{$row['user_name']}</a></td>
					<td style='width:10%; text-align:center' class='forumheader3'>{$row['user_plugin_forum_posts']}</td>
					<td style='width:30%; text-align:center' class='forumheader3'>{$r}</td>
					</tr>";
				$counter++;
			}
		}
		$text .= "</table>\n</div>";
		if ($subaction == 'forum') 
		{
			//$ftotal = $sql->db_Count('user', '(*)', 'WHERE `user_forums` > 0');
			$ftotal = $sql->count('user_extended', '(*)', 'WHERE `user_plugin_forum_posts` > 0');
			$parms = "{$ftotal},{$view},{$from},".e_SELF.'?[FROM].top.forum.'.$view;
			$text .= "<div class='nextprev'>".$tp->parseTemplate("{NEXTPREV={$parms}}").'</div>';
		}
		$ns->tablerender(TOP_LAN_0, $text);
		/*
		if ($subaction == 'forum')
		{
			require_once (e_HANDLER.'np_class.php');
			$ftotal = $sql->db_Count('user_extended', '(*)', 'WHERE `user_plugin_forum_posts` > 0');
			$ix = new nextprev('top.php', $from, $view, $ftotal, 'Forum Posts', 'top.forum.'.$view);
		}*/
	}


	if ($subaction == 'comment' || $subaction == 'all')
	{
		$top_forum_posters = $sql->select("user", "*", "`user_comments` > 0 ORDER BY user_comments DESC LIMIT 0, 10");
		$text = "
			<div style='text-align:center'>
			<table style='width:95%' class='fborder'>
			<tr>
			<td style='width:10%; text-align:center' class='forumheader3'>&nbsp;</td>
			<td style='width:50%' class='forumheader3'>".TOP_LAN_1."</td>
			<td style='width:10%; text-align:center' class='forumheader3'>".LAN_COMMENTS."</td>
			<td style='width:30%; text-align:center' class='forumheader3'>".TOP_LAN_6."</td>
			</tr>\n";
		$counter = 1;
		if($top_forum_posters)
		{
			while ($row = $sql->fetch())
			{
				// TODO - Custom ranking (comments), LANs
				$ldata = $rank->getRanks($row);
				if(vartrue($ldata['special']))
				{
					$r = $ldata['special'];
				}
				else
				{
					$r = $ldata['pic'] ? $ldata['pic'] : defset($ldata['name'], $ldata['name']);
				}
				if(!$r) $r = 'n/a';
				$text .= "<tr>
					<td style='width:10%; text-align:center' class='forumheader3'>{$counter}</td>
					<td style='width:50%' class='forumheader3'><a href='".e107::getUrl()->create('user/profile/view', 'id='.$row['user_id'].'&name='.$row['user_name'])."'>{$row['user_name']}</a></td>
					<td style='width:10%; text-align:center' class='forumheader3'>{$row['user_comments']}</td>
					<td style='width:30%; text-align:center' class='forumheader3'>{$r}</td>
					</tr>";
				$counter++;
			}
		}
		else
		{
			$text .= "
				<tr>
					<td class='forumheader3' colspan='4'>No results</td>
				</tr>";
		}
		$text .= "</table>\n</div>";
		$ns->tablerender(TOP_LAN_3, $text);
	}
	 
	if ($subaction == 'chat' || $subaction == 'all') 
	{
		$top_forum_posters = $sql->select("user", "*", "`user_chats` > 0 ORDER BY user_chats DESC LIMIT 0, 10");
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
		if($top_forum_posters)
		{
			while ($row = $sql->fetch())
			{
				// TODO - Custom ranking (chat), LANs
				$ldata = $rank->getRanks($row);
				if(vartrue($ldata['special']))
				{
					$r = $ldata['special'];
				}
				else
				{
					$r = $ldata['pic'] ? $ldata['pic'] : defset($ldata['name'], $ldata['name']);
				}
				if(!$r) $r = 'n/a';
				$text .= "<tr>
					<td style='width:10%; text-align:center' class='forumheader3'>{$counter}</td>
					<td style='width:50%' class='forumheader3'><a href='".e107::getUrl()->create('user/profile/view', 'id='.$row['user_id'].'&name='.$row['user_name'])."'>{$row['user_name']}</a></td>
					<td style='width:10%; text-align:center' class='forumheader3'>{$row['user_chats']}</td>
					<td style='width:30%; text-align:center' class='forumheader3'>{$r}</td>
					</tr>";
				$counter++;
			}

		}
		else
		{
			$text .= "
				<tr>
					<td class='forumheader3' colspan='4'>No results</td>
				</tr>";
		}
		$text .= "</table>\n</div>";
		$ns->tablerender(TOP_LAN_5, $text);
	}
}
require_once(FOOTERF);
?>