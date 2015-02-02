<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once('../../class2.php');

$e107 = e107::getInstance();
$sql = e107::getDb();

if (!$e107->isInstalled('forum'))
{
	header('Location: '.e_BASE.'index.php');
	exit;
}

//TODO: Investigate the queries used here

include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_stats.php');
e107::lan('forum','front');


require_once(e_PLUGIN.'forum/forum_class.php');
$gen = new convert;
$forum = new e107forum;

$barl = (file_exists(THEME.'images/barl.png') ? THEME.'images/barl.png' : e_PLUGIN.'poll/images/barl.png');
$barr = (file_exists(THEME.'images/barr.png') ? THEME.'images/barr.png' : e_PLUGIN.'poll/images/barr.png');
$bar = (file_exists(THEME.'images/bar.png') ? THEME.'images/bar.png' : e_PLUGIN.'poll/images/bar.png');

require_once(HEADERF);

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


$query = "SHOW TABLE STATUS FROM `{$mySQLdefaultdb}`";
$sql->gen($query);
$array = $sql -> db_getList();
foreach($array as $table)
{
	if($table['Name'] == MPREFIX.'forum_post')
	{
		$db_size = $e107->parseMemorySize($table['Data_length']);
		$avg_row_len = $e107->parseMemorySize($table['Avg_row_length']);
		break;
	}
}

$query = "
SELECT ft.thread_id, ft.thread_user, ft.thread_name, ft.thread_total_replies, ft.thread_datestamp, f.forum_class, u.user_name, u.user_id FROM #forum_thread as ft
LEFT JOIN #user AS u ON ft.thread_user = u.user_id
LEFT JOIN #forum AS f ON f.forum_id = ft.thread_forum_id
WHERE ft.thread_active > 0
AND f.forum_class IN (".USERCLASS_LIST.")
ORDER BY ft.thread_total_replies DESC LIMIT 0,10";
$sql->gen($query);
$most_activeArray = $sql->db_getList();

$query = "
SELECT ft.*, f.forum_class, u.user_name, u.user_id FROM #forum_thread as ft
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

// build top posters meanwhile
$top_posters = array();
foreach($top_repliers_data as $poster)
{
	$percent = round(($poster['post_count'] / $total_posts) * 100, 2);
	$top_posters[] = array("user_id" => $poster['user_id'], "user_name" => $poster['user_name'], "user_forums" => $poster['post_count'], "percentage" => $percent);
}
// end build top posters

$ids = implode(',', array_keys($top_repliers_data));

// find topics by top 10 users
$query = "
SELECT COUNT(ft.thread_id) AS thread_count, u.user_id FROM #forum_thread as ft
LEFT JOIN #user AS u ON ft.thread_user = u.user_id
WHERE u.user_id IN ({$ids})
GROUP BY ft.thread_user";
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
	$top_topic_starters[] = array("user_id" => $poster['user_id'], "user_name" => $poster['user_name'], "user_forums" => $poster['thread_count'], "percentage" => $percent);
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


function showBar($perc)
{
	
	return	"<div class='progress'>
    <div class='bar' style='width: ".intval($perc)."%;'></div>
    </div>";

//	<div style='background-image: url($barl); width: 5px; height: 14px; float: left;'></div>
//	<div style='background-image: url($bar); width: ".intval($percentage)."%; height: 14px; float: left;'></div>
//	<div style='background-image: url($barr); width: 5px; height: 14px; float: left;'></div>
	
	
}


$text = "
<div class='spacer'>
<table style='width: 100%;' class='fborder table'>
<tr>
<th class='forumheader' colspan='2'>".LAN_FORUM_6000."</th>
</tr>
	<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6001.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$open_date}</td></tr>
	<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6002.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$open_since}</td></tr>
	<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6003.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$total_posts}</td></tr>
	<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_1007.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$total_topics}</td></tr>
	<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6004.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$total_replies}</td></tr>
	<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6005.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$total_views}</td></tr>
	<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6014.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$postsperday}</td></tr>
	<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6006.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$db_size}</td></tr>
	<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6007.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{$avg_row_len}</td></tr>
</table>
</div>

<div class='spacer'>
<table style='width: 100%;' class='fborder table'>
<tr>
<td class='forumheader' colspan='5'>".LAN_FORUM_0011."</td>
</tr>
<tr>
<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
<th style='width: 40%;' class='fcaption'>".LAN_FORUM_1003."</th>
<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_0003."</th>
<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_FORUM_6009."</th>
<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_DATE."</th>
</tr>
";

$count=1;
foreach($most_activeArray as $ma)
{
	if($ma['user_name'])
	{
		$uinfo = "<a href='".e_BASE."user.php?id.{$ma['user_id']}'>{$ma['user_name']}</a>";
	}
	else
	{
		$tmp = explode(chr(1), $ma['thread_anon']);
		$uinfo = $tp->toHTML($tmp[0]);
	}

	$text .= "
	<tr>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
	<td style='width: 40%;' class='forumheader3'><a href='".e_PLUGIN."forum/forum_viewtopic.php?{$ma['thread_id']}'>{$ma['thread_name']}</a></td>
	<td style='width: 10%; text-align: center;' class='forumheader3'>{$ma['thread_total_replies']}</td>
	<td style='width: 20%; text-align: center;' class='forumheader3'>{$uinfo}</td>
	<td style='width: 20%; text-align: center;' class='forumheader3'>".$gen->convert_date($ma['thread_datestamp'], "forum")."</td>
	</tr>
	";
	$count++;
}
$text .= "</table>
</div>

<div class='spacer'>
<table style='width: 100%;' class='fborder table'>
<tr>
<td class='forumheader' colspan='5'>".LAN_FORUM_6010."</td>
</tr>
<tr>
<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
<th style='width: 40%;' class='fcaption'>".LAN_FORUM_1003."</th>
<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_1005."</th>
<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_FORUM_6009."</th>
<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_DATE."</th>
</tr>
";

$count=1;
foreach($most_viewedArray as $ma)
{
	if($ma['user_name'])
	{
		$uinfo = "<a href='".e_BASE."user.php?id.{$ma['user_id']}'>{$ma['user_name']}</a>";
	}
	else
	{
		$tmp = explode(chr(1), $ma['thread_anon']);
		$uinfo = $tp->toHTML($tmp[0]);
	}

	$text .= "
	<tr>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
	<td style='width: 40%;' class='forumheader3'><a href='".e_PLUGIN."forum/forum_viewtopic.php?{$ma['thread_id']}'>{$ma['thread_name']}</a></td>
	<td style='width: 10%; text-align: center;' class='forumheader3'>{$ma['thread_views']}</td>
	<td style='width: 20%; text-align: center;' class='forumheader3'>{$uinfo}</td>
	<td style='width: 20%; text-align: center;' class='forumheader3'>".$gen->convert_date($ma['thread_datestamp'], "forum")."</td>
	</tr>
	";
	$count++;
}
$text .= "</table>
</div>

<div class='spacer'>
<table style='width: 100%;' class='fborder table'>
<tr>
<td class='forumheader' colspan='5'>".LAN_FORUM_0010."</td>
</tr>
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
	extract($ma);
	$text .= "<tr>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
	<td style='width: 20%;' class='forumheader3'><a href='".e_BASE."user.php?id.$user_id'>$user_name</a></td>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$user_forums</td>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$percentage%</td>
	<td style='width: 50%;' class='forumheader3'>".showBar($percentage)."
	</td>
	</tr>
	";
	$count++;
}
$text .= "</tbody>
</table>
</div>

<div class='spacer'>
<table style='width: 100%;' class='fborder table'>
<tr>
<td class='forumheader' colspan='5'>".LAN_FORUM_6011."</td>
</tr>
<tr>
<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
<th style='width: 20%;' class='fcaption'>".LAN_NAME."</th>
<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_2032."</th>
<th style='width: 10%; text-align: center;' class='fcaption'>%</th>
<th style='width: 50%; text-align: center;' class='fcaption'>&nbsp;</th>
</tr>
";

$count=1;
foreach($top_topic_starters as $ma)
{
	extract($ma);
	$text .= "<tr>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
	<td style='width: 20%;' class='forumheader3'><a href='".e_BASE."user.php?id.$user_id'>$user_name</a></td>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$user_forums</td>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$percentage%</td>
	<td style='width: 50%; text-align: center;' class='forumheader3'>".showBar($percentage)."</td>
	</tr>
	";
	$count++;
}
$text .= "</table>
</div>


<div class='spacer'>
<table style='width: 100%;' class='fborder table'>
<tr>
<td class='forumheader' colspan='5'>".LAN_FORUM_6012."</td>
</tr>
<tr>
<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
<th style='width: 20%;' class='fcaption'>".LAN_NAME."</th>
<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_2032."</th>
<th style='width: 10%; text-align: center;' class='fcaption'>%</th>
<th style='width: 50%; text-align: center;' class='fcaption'>&nbsp;</th>
</tr>
";

$count=1;
foreach($top_repliers as $ma)
{
	extract($ma);
	$text .= "<tr>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
	<td style='width: 20%;' class='forumheader3'><a href='".e_BASE."user.php?id.$user_id'>$user_name</a></td>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$user_forums</td>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$percentage%</td>
	<td style='width: 50%; text-align: center;' class='forumheader3'>".showBar($percentage)."</td>
	</tr>
	";
	$count++;
}
$text .= '</table>
</div>
';

$ns -> tablerender(LAN_FORUM_6013, $text);

require_once(FOOTERF);
?>