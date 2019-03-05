<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if(!defined('e107_INIT'))
{
	require_once('../../class2.php');
}

if (!e107::isInstalled('forum'))
{
	e107::redirect();
	exit;
}


class forumStats
{

	private $from = 0;
	private $view = 20;



	function __construct()
	{
		//include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_stats.php');
		//e107::lan('forum','front');
		e107::lan('forum', "front", true);
		e107::css('forum', 'forum.css');
	}


	function init()
	{

		$sql = e107::getDb();
		$tp = e107::getParser();
		$ns = e107::getRender();
		$frm = e107::getForm();


		require_once(e_PLUGIN.'forum/forum_class.php');
		$gen = e107::getDate();

		$forum = new e107forum;

		$total_posts = $sql->count('forum_post');
		$total_topics = $sql->count('forum_thread');
		$total_replies = $total_posts - $total_topics;
		$total_views = 0;

		$query = 'SELECT sum(thread_views) AS total FROM `#forum_thread` ';
		if ($sql->gen($query))
		{
			$row = $sql->fetch();
			$total_views = $row['total'];
		}

		$firstpost = $sql->select('forum_post', 'post_datestamp', 'post_datestamp > 0 ORDER BY post_datestamp ASC LIMIT 0,1', 'default');
		$fp = $sql->fetch();

		$open_ds = $fp['post_datestamp'];
		$open_date = $gen->convert_date($open_ds, 'long');
		$open_since = $gen -> computeLapse($open_ds);
		$open_days = floor((time()-$open_ds) / 86400);
		$postsperday = ($open_days < 1 ? $total_posts : round($total_posts / $open_days));

		global $mySQLdefaultdb;

		$query = "SHOW TABLE STATUS FROM `{$mySQLdefaultdb}`";
		$sql->gen($query);
		$array = $sql -> db_getList();
		foreach($array as $table)
		{
			if($table['Name'] == MPREFIX.'forum_post')
			{
				$db_size = eHelper::parseMemorySize($table['Data_length']);
				$avg_row_len = eHelper::parseMemorySize($table['Avg_row_length']);
				break;
			}
		}

		$query = "
		SELECT ft.thread_id, ft.thread_user, ft.thread_name, ft.thread_total_replies, ft.thread_datestamp, f.forum_sef, f.forum_class, u.user_name, u.user_id FROM #forum_thread as ft
		LEFT JOIN #user AS u ON ft.thread_user = u.user_id
		LEFT JOIN #forum AS f ON f.forum_id = ft.thread_forum_id
		WHERE ft.thread_active > 0
		AND f.forum_class IN (".USERCLASS_LIST.")
		ORDER BY ft.thread_total_replies DESC LIMIT 0,10";

		$sql->gen($query);
		$most_activeArray = $sql->db_getList();

		$query = "
		SELECT ft.*, f.forum_class, f.forum_sef, u.user_name, u.user_id FROM #forum_thread as ft
		LEFT JOIN #user AS u ON ft.thread_user = u.user_id
		LEFT JOIN #forum AS f ON f.forum_id = ft.thread_forum_id
		WHERE f.forum_class IN (".USERCLASS_LIST.")
		ORDER BY ft.thread_views DESC LIMIT 0,10";

		$sql->gen($query);
		$most_viewedArray = $sql->db_getList();

			/*$sql->db_Select("user", "user_id, user_name, user_forums", "ORDER BY user_forums DESC LIMIT 0, 10", "no_where");
			$posters = $sql -> db_getList();
			$top_posters = array();
			foreach($posters as $poster)
			{
				$percen = round(($poster['user_forums'] / $total_posts) * 100, 2);
				$top_posters[] = array("user_id" => $poster['user_id'], "user_name" => $poster['user_name'], "user_forums" => $poster['user_forums'], "percentage" => $percen);
			}*/



		// get all replies
		$query = "
		SELECT COUNT(fp.post_id) AS post_count, u.user_name, u.user_id, fp.post_thread FROM #forum_post as fp
		LEFT JOIN #user AS u ON fp.post_user = u.user_id
		GROUP BY fp.post_user
		ORDER BY post_count DESC LIMIT 0,10";

		$sql->gen($query);
	 	$top_repliers_data = $sql->db_getList('ALL', false, false, 'user_id');
//		$top_repliers_data = $sql->retrieve($query,true);

		// build top posters meanwhile
		$top_posters = array();
		$topReplier = array();
		foreach($top_repliers_data as $poster)
		{
			$percent = round(($poster['post_count'] / $total_posts) * 100, 2);
			$topReplier[] = intval($poster['user_id']);
			$top_posters[] = array("user_id" => $poster['user_id'], "user_name" => vartrue($poster['user_name'],LAN_ANONYMOUS), "user_forums" => $poster['post_count'], "percentage" => $percent);
		}
			// end build top posters

		$ids = implode(',', $topReplier);

		// find topics by top 10 users
		$query = "
		SELECT COUNT(ft.thread_id) AS thread_count, u.user_id FROM #forum_thread as ft
		LEFT JOIN #user AS u ON ft.thread_user = u.user_id
		WHERE u.user_id IN ({$ids})	GROUP BY ft.thread_user";

		$sql->gen($query);
		$top_repliers_data_c = $sql->db_getList('ALL', false, false, 'user_id');

		$top_repliers = array();
		foreach($top_repliers_data as $uid => $poster)
		{
			$poster['post_count'] = $poster['post_count'] - $top_repliers_data_c[$uid]['thread_count'];
			$percent = round(($poster['post_count'] / $total_replies) * 100, 2);
			$top_repliers_sort[$uid] = $poster['post_count'];
			//$top_repliers[$uid] = $poster;
			$top_repliers_data[$uid]['user_forums'] = $poster['post_count'];
			$top_repliers_data[$uid]['percentage'] = $percent;
			//$top_repliers_data[$uid] = array("user_id" => $poster['user_id'], "user_name" => $poster['user_name'], "user_forums" => $poster['post_count'], "percentage" => $percent);
		}

		// sort

		arsort($top_repliers_sort, SORT_NUMERIC);

		// build top repliers
		foreach ($top_repliers_sort as $uid => $c)
		{
			$top_repliers[] = $top_repliers_data[$uid];
		}

		// get all replies
		$query = "
		SELECT COUNT(ft.thread_id) AS thread_count, u.user_name, u.user_id FROM #forum_thread as ft
		LEFT JOIN #user AS u ON ft.thread_user = u.user_id
		GROUP BY ft.thread_user
		ORDER BY thread_count DESC LIMIT 0,10";

		$sql->gen($query);
		$top_topic_starters_data = $sql->db_getList();
		$top_topic_starters = array();

		foreach($top_topic_starters_data as $poster)
		{
			$percent = round(($poster['thread_count'] / $total_topics) * 100, 2);
			$top_topic_starters[] = array("user_id" => $poster['user_id'], "user_name" => vartrue($poster['user_name'],LAN_ANONYMOUS), "user_forums" => $poster['thread_count'], "percentage" => $percent);
		}

			/*
			$query = "
			SELECT SUBSTRING_INDEX(thread_user,'.',1) AS t_user, COUNT(SUBSTRING_INDEX(ft.thread_user,'.',1)) AS ucount, u.user_name, u.user_id FROM #forum_t as ft
			LEFT JOIN #user AS u ON SUBSTRING_INDEX(ft.thread_user,'.',1) = u.user_id
			WHERE ft.thread_parent=0
			GROUP BY t_user
			ORDER BY ucount DESC
			LIMIT 0,10";
			$sql -> db_Select_gen($query);
			$posters = $sql -> db_getList();
			$top_topic_starters = array();
			foreach($posters as $poster)
			{
				$percen = round(($poster['ucount'] / $total_topics) * 100, 2);
				$top_topic_starters[] = array("user_id" => $poster['user_id'], "user_name" => $poster['user_name'], "user_forums" => $poster['ucount'], "percentage" => $percen);
			}*/

			/*
			$query = "
			SELECT SUBSTRING_INDEX(thread_user,'.',1) AS t_user, COUNT(SUBSTRING_INDEX(ft.thread_user,'.',1)) AS ucount, u.user_name, u.user_id FROM #forum_t as ft
			LEFT JOIN #user AS u ON SUBSTRING_INDEX(ft.thread_user,'.',1) = u.user_id
			WHERE ft.thread_parent!=0
			GROUP BY t_user
			ORDER BY ucount DESC
			LIMIT 0,10";
			$sql -> db_Select_gen($query);
			$posters = $sql -> db_getList();

			$top_repliers = array();
			foreach($posters as $poster)
			{
				$percen = round(($poster['ucount'] / $total_replies) * 100, 2);
				$top_repliers[] = array("user_id" => $poster['user_id'], "user_name" => $poster['user_name'], "user_forums" => $poster['ucount'], "percentage" => $percen);
			}
			*/





		$text_0 = "
		<table style='width: 100%;' class='fborder table'>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6001.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$open_date}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6002.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$open_since}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6003.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$total_posts}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_1007.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$total_topics}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6004.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$total_replies}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6005.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$total_views}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6014.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$postsperday}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6006.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$db_size}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6007.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$avg_row_len}</td></tr>
		</table>";




		$text_1 = "
		<table style='width: 100%;' class='fborder table'>
		<thead>
		<tr>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
		<th style='width: 40%;' class='fcaption'>".LAN_FORUM_1003."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_0003."</th>
		<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_FORUM_6009."</th>
		<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_DATE."</th>
		</tr>
		</thead>
		";

		$count=1;

		foreach($most_activeArray as $ma)
		{
			if($ma['user_name'])
			{
				//$uinfo = "<a href='".e_HTTP."user.php ?id.{$ma['user_id']}'>{$ma['user_name']}</a>"; //TODO SEf Url .
				$uparams = array('id' => $ma['user_id'], 'name' => $ma['user_name']);
				$link = e107::getUrl()->create('user/profile/view', $uparams);
				$uinfo = "<a href='".$link."'>".$ma['user_name']."</a>";
			}
			else
			{
				$tmp = explode(chr(1), $ma['thread_anon']);
				$uinfo = $tp->toHTML($tmp[0]);
			}

			$ma['thread_sef'] = eHelper::title2sef($ma['thread_name'],'dashl');
			$url = e107::url('forum','topic', $ma);

			$text_1 .= "
			<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
			<td style='width: 40%;' class='forumheader3'><a href='".$url."'>{$ma['thread_name']}</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{$ma['thread_total_replies']}</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>{$uinfo}</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>".$gen->convert_date($ma['thread_datestamp'], "forum")."</td>
			</tr>
			";

			$count++;
		}

		$text_1 .= "</table>";


		$text_2 = "
		<table style='width: 100%;' class='fborder table'>
		<thead>
		<tr>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
		<th style='width: 40%;' class='fcaption'>".LAN_FORUM_1003."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_1005."</th>
		<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_FORUM_6009."</th>
		<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_DATE."</th>
		</tr>
		</thead>
		";

		$count=1;

		foreach($most_viewedArray as $ma)
		{
			if($ma['user_name'])
			{
				//$uinfo = "<a href='".e_HTTP."user.php ?id.{$ma['user_id']}'>".$ma['user_name']."</a>";  //TODO SEf Url .
				$uparams = array('id' => $ma['user_id'], 'name' => $ma['user_name']);
				$link = e107::getUrl()->create('user/profile/view', $uparams);
				$uinfo = "<a href='".$link."'>".$ma['user_name']."</a>";
			}
			else
			{
				$tmp = explode(chr(1), $ma['thread_anon']);
				$uinfo = $tp->toHTML($tmp[0]);
			}

			$ma['thread_sef'] = eHelper::title2sef($ma['thread_name'],'dashl');
			$url = e107::url('forum','topic', $ma);

			$text_2 .= "
			<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
			<td style='width: 40%;' class='forumheader3'><a href='".$url."'>".$ma['thread_name']."</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>".$ma['thread_views']."</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>".$uinfo."</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>".$gen->convert_date($ma['thread_datestamp'], "forum")."</td>
			</tr>
			";
				$count++;
		}

		$text_2 .= "</table>";




		$text_3 = "
		<table style='width: 100%;' class='fborder table'>
		<thead>
		<tr>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
		<th style='width: 20%;' class='fcaption'>".LAN_NAME."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_2032."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>%</th>
		<th style='width: 50%; text-align: center;' class='fcaption'>&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		";

		$count=1;
		foreach($top_posters as $ma)
		{
			$text_3 .= "<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
			<td style='width: 20%;' class='forumheader3'><a href='".e107::url('user/profile/view', $ma)."'>".$ma['user_name']."</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>".$ma['user_forums']."</td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>".$ma['percentage']."%</td>
			<td style='width: 50%;' class='forumheader3'>".$this->showBar($ma['percentage'])."
			</td>
			</tr>
			";

			$count++;
		}

		$text_3 .= "</tbody>
		</table>
		";


		$text_4 = "
		<table style='width: 100%;' class='fborder table'>
		<thead>
		<tr>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
		<th style='width: 20%;' class='fcaption'>".LAN_NAME."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_2032."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>%</th>
		<th style='width: 50%; text-align: center;' class='fcaption'>&nbsp;</th>
		</tr>
		</thead>
		";

		$count=1;
		foreach($top_topic_starters as $ma)
		{
			$text_4 .= "<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
			<td style='width: 20%;' class='forumheader3'><a href='".e107::url('user/profile/view', $ma)."'>".$ma['user_name']."</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>".$ma['user_forums']."</td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>".$ma['percentage']."%</td>
			<td style='width: 50%; text-align: center;' class='forumheader3'>".$this->showBar($ma['percentage'])."</td>
			</tr>
			";
			$count++;
		}

		$text_4 .= "</table>";


		$text_5 = "
		<table style='width: 100%;' class='fborder table'>
		<thead>
		<tr>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
		<th style='width: 20%;' class='fcaption'>".LAN_NAME."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_2032."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>%</th>
		<th style='width: 50%; text-align: center;' class='fcaption'>&nbsp;</th>
		</tr>
		</thead>
		";

		$count=1;
		foreach($top_repliers as $ma)
		{
			$text_5 .= "
			<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
			<td style='width: 20%;' class='forumheader3'><a href='".e107::url('user/profile/view', $ma)."'>".$ma['user_name']."</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>".$ma['user_forums']."</td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>".$ma['percentage']."%</td>
			<td style='width: 50%; text-align: center;' class='forumheader3'>".$this->showBar($ma['percentage'])."</td>
			</tr>
			";

			$count++;
		}

		$text_5 .= '</table>';


		if(deftrue('BOOTSTRAP'))
		{
			$tabs = array();

			$tabs[0] = array('caption'=>LAN_FORUM_6000, 'text'=>$text_0);
			$tabs[1] = array('caption'=>LAN_FORUM_0011, 'text'=>$text_1);
			$tabs[2] = array('caption'=>LAN_FORUM_6010, 'text'=>$text_2);
			$tabs[3] = array('caption'=>LAN_FORUM_0010, 'text'=>$text_3);
			$tabs[4] = array('caption'=>LAN_FORUM_6011, 'text'=>$text_4);
			$tabs[5] = array('caption'=>LAN_FORUM_6012, 'text'=>$text_5);

			$frm = e107::getForm();

			$breadarray = array(
				array('text'=> e107::pref('forum','title', LAN_PLUGIN_FORUM_NAME), 'url' => e107::url('forum','index') ),
				array('text'=>LAN_FORUM_6013, 'url'=>null)
			);

			$text = $frm->breadcrumb($breadarray);


			$text = "<div id='forum-stats'>". $text . e107::getForm()->tabs($tabs)."</div>";
		}
		else
		{
			$text ="
			<h3>".LAN_FORUM_6000."</h3>". $text_0 .
			"<h3>".LAN_FORUM_0011."</h3>". $text_1 .
			"<h3>".LAN_FORUM_6010."</h3>". $text_2 .
			"<h3>".LAN_FORUM_0010."</h3>".$text_3 .
			"<h3>".LAN_FORUM_6011."</h3>". $text_4 .
			"<h3>".LAN_FORUM_6012."</h3>". $text_5;
		}

		$text .= "<div class='center'>".e107::getForm()->pagination(e107::url('forum','index'), LAN_BACK)."</div>";

		$ns -> tablerender(LAN_FORUM_6013, $text, 'forum-stats');

	}

	function showBar($perc)
	{
		return e107::getForm()->progressBar('prog',$perc);
	}


	function topPosters()               // from top.php - unused.
	{
		$pref = e107::pref('core');
		$rank = e107::getRank();
		$sql = e107::getDb();
		$sql2 = e107::getDb('sql2');
		$ns = e107::getRender();
		$tp = e107::getParser();


		define('IMAGE_rank_main_admin_image', ($pref['rank_main_admin_image'] && file_exists(THEME."forum/".$pref['rank_main_admin_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_main_admin_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/main_admin.png' alt='' />"));
		define('IMAGE_rank_admin_image', ($pref['rank_admin_image'] && file_exists(THEME."forum/".$pref['rank_admin_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_admin_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/admin.png' alt='' />"));
		define('IMAGE_rank_moderator_image', ($pref['rank_moderator_image'] && file_exists(THEME."forum/".$pref['rank_moderator_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_moderator_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/moderator.png' alt='' />"));

		if ($this->subaction == 'forum' || $this->subaction == 'all')
		{
			require_once (e_PLUGIN.'forum/forum_class.php');
			$forum = new e107forum();

			$qry = "
			SELECT ue.*, u.* FROM `#user_extended` AS ue
			LEFT JOIN `#user` AS u ON u.user_id = ue.user_extended_id
			WHERE ue.user_plugin_forum_posts > 0
			ORDER BY ue.user_plugin_forum_posts DESC LIMIT {$this->from}, {$this->view}
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

			$counter = 1 + $this->from;

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
					<td style='width:50%' class='forumheader3'><a href='".e107::url('user/profile/view', 'id='.$row['user_id'].'&name='.$row['user_name'])."'>{$row['user_name']}</a></td>
					<td style='width:10%; text-align:center' class='forumheader3'>{$row['user_plugin_forum_posts']}</td>
					<td style='width:30%; text-align:center' class='forumheader3'>{$r}</td>
					</tr>";

					$counter++;
				}
			}

			$text .= "</table>\n</div>";

			if ($this->subaction == 'forum')
			{
				$ftotal = $sql->count('user', '(*)', 'WHERE `user_forums` > 0');
				$parms = "{$ftotal},{$this->view},{$this->from},".e_SELF.'?[FROM].top.forum.'.$this->view;
				$text .= "<div class='nextprev'>".$tp->parseTemplate("{NEXTPREV={$parms}}").'</div>';
			}

			$ns->tablerender(TOP_LAN_0, $text, 'forum-stats-top');


		}
	}




	function mostActiveTopics()           // from top.php - unused.
	{
		//require_once (e_HANDLER.'userclass_class.php');

		$sql = e107::getDb();
		$tp = e107::getParser();
		$ns = e107::getRender();

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
			{$this->from}, {$this->view}
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
					$POSTER = "<a href='".e107::url('user/profile/view', "name={$row['user_name']}&id={$row['thread_user']}")."'>{$row['user_name']}</a>";
				}
				else
				{
					$POSTER = $row['thread_user_anon'];
				}

			//	$LINKTOTHREAD = e107::url('forum/thread/view', array('id' =>$row['thread_id'])); //$e107->url->getUrl('forum', 'thread', "func=view&id={$row['thread_id']}");
			//	$LINKTOFORUM = e107::url('forum/forum/view', array('id' => $row['thread_forum_id'])); //$e107->url->getUrl('forum', 'forum', "func=view&id={$row['thread_forum_id']}");

				$lastpost_datestamp = $gen->convert_date($row['thread_lastpost'], 'forum');

				if ($row['user_last'])
				{
					$LASTPOST = "<a href='".e107::url('user/profile/view', "name={$row['user_last']}&id={$row['thread_lastuser']}")."'>{$row['user_last']}</a><br />".$lastpost_datestamp;
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

			$ftotal = $sql->count('forum_thread', '(*)', 'WHERE `thread_parent` = 0');
			$parms = "{$ftotal},{$this->view},{$this->from},".e_SELF.'?[FROM].active.forum.'.$this->view;
			$text .= "<div class='nextprev'>".$tp->parseTemplate("{NEXTPREV={$parms}}").'</div>';

			$ns->tablerender(LAN_7, $text, 'forum-stats-active');


		}


	}

}


$frmStats = new forumStats;
require_once(HEADERF);
$frmStats->init();

require_once(FOOTERF);
