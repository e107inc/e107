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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/forum_stats.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-03-20 18:11:52 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

require_once('../../class2.php');

@include_once e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_stats.php';
@include_once e_PLUGIN.'forum/languages/English/lan_forum_stats.php';
@require_once(e_PLUGIN.'forum/forum_class.php');
$gen = new convert;


$barl = (file_exists(THEME."images/barl.png") ? THEME."images/barl.png" : e_IMAGE."generic/barl.png");
$barr = (file_exists(THEME."images/barr.png") ? THEME."images/barr.png" : e_IMAGE."generic/barr.png");
$bar = (file_exists(THEME."images/bar.png") ? THEME."images/bar.png" : e_IMAGE."generic/bar.png");

require_once(HEADERF);

$total_posts = $sql -> db_Count("forum_t");
$total_topics = $sql -> db_Count("forum_t", "(*)", "WHERE thread_parent=0");
$total_replies = $sql -> db_Count("forum_t", "(*)", "WHERE thread_parent!=0");
$total_views = $sql->db_Count("SELECT sum(thread_views) FROM ".MPREFIX."forum_t", "generic");

$firstpost = $sql -> db_Select("forum_t", "thread_datestamp", "ORDER BY thread_datestamp ASC LIMIT 0,1", "nowhere");
$fp = $sql -> db_Fetch();

$open_ds = $fp['thread_datestamp'];
$open_date = $gen->convert_date($open_ds, "long");
$open_since = $gen -> computeLapse($open_ds);

$query = "SHOW TABLE STATUS FROM $mySQLdefaultdb";
$sql -> db_Select_gen($query);
$array = $sql -> db_getList();
foreach($array as $table)
{
	if($table['Name'] == MPREFIX."forum_t")
	{
		$db_size = parsesize($table['Data_length']);
		$avg_row_len = parsesize($table['Avg_row_length']);
		break;
	}
}


$query = "
SELECT ft.*, user_name FROM #forum_t as ft 
LEFT JOIN #user AS u ON ft.thread_user = u.user_id 
WHERE ft.thread_parent=0
ORDER BY thread_total_replies DESC LIMIT 0,10";
$sql -> db_Select_gen($query);
$most_activeArray = $sql -> db_getList();

$query = "
SELECT ft.*, user_name FROM #forum_t as ft 
LEFT JOIN #user AS u ON ft.thread_user = u.user_id 
WHERE ft.thread_parent=0
ORDER BY thread_views DESC LIMIT 0,10";
$sql -> db_Select_gen($query);
$most_viewedArray = $sql -> db_getList();

$sql->db_Select("user", "user_id, user_name, user_forums", "ORDER BY user_forums DESC LIMIT 0, 10", "no_where");
$posters = $sql -> db_getList();
$top_posters = array();
foreach($posters as $poster)
{
	$percen = round(($poster['user_forums'] / $total_posts) * 100, 2);
	$top_posters[] = array("user_id" => $poster['user_id'], "user_name" => $poster['user_name'], "user_forums" => $poster['user_forums'], "percentage" => $percen);
}

$query = "
SELECT thread_user, COUNT(thread_user) AS ucount, user_name FROM #forum_t as ft 
LEFT JOIN #user AS u ON ft.thread_user = u.user_id 
WHERE ft.thread_parent=0
GROUP BY thread_user 
ORDER BY ucount DESC
LIMIT 0,10";
$sql -> db_Select_gen($query);
$posters = $sql -> db_getList();
$top_topic_starters = array();
foreach($posters as $poster)
{
	$percen = round(($poster['ucount'] / $total_topics) * 100, 2);
	$top_topic_starters[] = array("user_id" => $poster['thread_user'], "user_name" => $poster['user_name'], "user_forums" => $poster['ucount'], "percentage" => $percen);
}

$query = "
SELECT thread_user, COUNT(thread_user) AS ucount, user_name FROM #forum_t as ft 
LEFT JOIN #user AS u ON ft.thread_user = u.user_id 
WHERE ft.thread_parent!=0
GROUP BY thread_user
ORDER BY ucount DESC
LIMIT 0,10";
$sql -> db_Select_gen($query);
$posters = $sql -> db_getList();

$top_repliers = array();
foreach($posters as $poster)
{
	$percen = round(($poster['ucount'] / $total_replies) * 100, 2);
	$top_repliers[] = array("user_id" => $poster['thread_user'], "user_name" => $poster['user_name'], "user_forums" => $poster['ucount'], "percentage" => $percen);
}



$text = "
<div class='spacer'>
<table style='width: 100%;' class='fborder'>
<tr>
<td class='forumheader'>General</td>
</tr>

<tr>
<td class='forumheader3'>
	<table style='width: 100%;'>
	<tr><td style='width: 50%;'><b>Forum opened:</b></td><td style='width: 50%;'>$open_date</td></tr>
	<tr><td style='width: 50%;'><b>Open for:</b></td><td style='width: 50%;'>$open_since</td></tr>
	<tr><td style='width: 50%;'><b>Total posts:</b></td><td style='width: 50%;'>$total_posts</td></tr>
	<tr><td style='width: 50%;'><b>Forum topics:</b></td><td style='width: 50%;'>$total_topics</td></tr>
	<tr><td style='width: 50%;'><b>Forum replies:</b></td><td style='width: 50%;'>$total_replies</td></tr>
	<tr><td style='width: 50%;'><b>Forum thread views:</b></td><td style='width: 50%;'>$total_views</td></tr>

	<tr><td style='width: 50%;'><b>Database size (forum tables only):</b></td><td style='width: 50%;'>$db_size</td></tr>
	<tr><td style='width: 50%;'><b>Average row length in forum table:</b></td><td style='width: 50%;'>$avg_row_len</td></tr>
	</tr>
	</table>
</td>
</tr>
</table>
</div>

<div class='spacer'>
<table style='width: 100%;' class='fborder'>
<tr>
<td class='forumheader' colspan='5'>Most active topics</td>
</tr>
<tr>
<td style='width: 10%; text-align: center;' class='fcaption'>Rank</td>
<td style='width: 40%;' class='fcaption'>Topic</td>
<td style='width: 10%; text-align: center;' class='fcaption'>Replies</td>
<td style='width: 20%; text-align: center;' class='fcaption'>Started by</td>
<td style='width: 20%; text-align: center;' class='fcaption'>Date</td>
</tr>
";

$count=1;
foreach($most_activeArray as $ma)
{
	extract($ma);
	$datestamp = $gen->convert_date($comment_datestamp, "short");
	$text .= "<tr>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
	<td style='width: 40%;' class='forumheader3'><a href='".e_PLUGIN."forum/forum_viewtopic.php?$thread_id'>$thread_name</a></td>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$thread_total_replies</td>
	<td style='width: 20%; text-align: center;' class='forumheader3'><a href='".e_BASE."user.php?id.$thread_user'>$user_name</a></td>
	<td style='width: 20%; text-align: center;' class='forumheader3'>".$gen->convert_date($thread_datestamp, "forum")."</td>
	</tr>
	";
	$count++;
}
$text .= "</table>
</div>

<div class='spacer'>
<table style='width: 100%;' class='fborder'>
<tr>
<td class='forumheader' colspan='5'>Most viewed topics</td>
</tr>
<tr>
<td style='width: 10%; text-align: center;' class='fcaption'>Rank</td>
<td style='width: 40%;' class='fcaption'>Topic</td>
<td style='width: 10%; text-align: center;' class='fcaption'>Views</td>
<td style='width: 20%; text-align: center;' class='fcaption'>Started by</td>
<td style='width: 20%; text-align: center;' class='fcaption'>Date</td>
</tr>
";

$count=1;
foreach($most_viewedArray as $ma)
{
	extract($ma);
	$text .= "<tr>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$count</td>
	<td style='width: 40%;' class='forumheader3'><a href='".e_PLUGIN."forum/forum_viewtopic.php?$thread_id'>$thread_name</a></td>
	<td style='width: 10%; text-align: center;' class='forumheader3'>$thread_views</td>
	<td style='width: 20%; text-align: center;' class='forumheader3'><a href='".e_BASE."user.php?id.$thread_user'>$user_name</a></td>
	<td style='width: 20%; text-align: center;' class='forumheader3'>".$gen->convert_date($thread_datestamp, "forum")."</td>
	</tr>
	";
	$count++;
}
$text .= "</table>
</div>

<div class='spacer'>
<table style='width: 100%;' class='fborder'>
<tr>
<td class='forumheader' colspan='5'>Top posters</td>
</tr>
<tr>
<td style='width: 10%; text-align: center;' class='fcaption'>Rank</td>
<td style='width: 20%;' class='fcaption'>Name</td>
<td style='width: 10%; text-align: center;' class='fcaption'>Posts</td>
<td style='width: 10%; text-align: center;' class='fcaption'>%</td>
<td style='width: 50%; text-align: center;' class='fcaption'>&nbsp;</td>
</tr>
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
	<td style='width: 50%;' class='forumheader3'>

	<div style='background-image: url($barl); width: 5px; height: 14px; float: left;'></div>
	<div style='background-image: url($bar); width: ".$percentage."%; height: 14px; float: left;'></div>
	<div style='background-image: url($barr); width: 5px; height: 14px; float: left;'></div>

	</td>
	</tr>
	";
	$count++;
}
$text .= "
</table>
</div>

<div class='spacer'>
<table style='width: 100%;' class='fborder'>
<tr>
<td class='forumheader' colspan='5'>Top topic starters</td>
</tr>
<tr>
<td style='width: 10%; text-align: center;' class='fcaption'>Rank</td>
<td style='width: 20%;' class='fcaption'>Name</td>
<td style='width: 10%; text-align: center;' class='fcaption'>Posts</td>
<td style='width: 10%; text-align: center;' class='fcaption'>%</td>
<td style='width: 50%; text-align: center;' class='fcaption'>&nbsp;</td>
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
	<td style='width: 50%; text-align: center;' class='forumheader3'>

	<div style='background-image: url($barl); width: 5px; height: 14px; float: left;'></div>
	<div style='background-image: url($bar); width: ".$percentage."%; height: 14px; float: left;'></div>
	<div style='background-image: url($barr); width: 5px; height: 14px; float: left;'></div>
	
	</td>
	</tr>
	";
	$count++;
}
$text .= "</table>
</div>


<div class='spacer'>
<table style='width: 100%;' class='fborder'>
<tr>
<td class='forumheader' colspan='5'>Top repliers</td>
</tr>
<tr>
<td style='width: 10%; text-align: center;' class='fcaption'>Rank</td>
<td style='width: 20%;' class='fcaption'>Name</td>
<td style='width: 10%; text-align: center;' class='fcaption'>Posts</td>
<td style='width: 10%; text-align: center;' class='fcaption'>%</td>
<td style='width: 50%; text-align: center;' class='fcaption'>&nbsp;</td>
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
	<td style='width: 50%; text-align: center;' class='forumheader3'>
	
	<div style='background-image: url($barl); width: 5px; height: 14px; float: left;'></div>
	<div style='background-image: url($bar); width: ".$percentage."%; height: 14px; float: left;'></div>
	<div style='background-image: url($barr); width: 5px; height: 14px; float: left;'></div>
	
	</td>
	</tr>
	";
	$count++;
}
$text .= "</table>
</div>
";






$ns -> tablerender("Forum Statistics", $text);





/*
echo "Posts: $total_posts, topics: $total_topics, replies: $total_replies, views: $total_views<br />";
echo "Opened: $open_date, $open_since ago<br />";
echo "Table size: $db_size, average row length: $avg_row_len<br />";
echo "<pre>"; print_r($most_activeArray); echo "</pre>";
echo "<pre>"; print_r($most_viewedArray); echo "</pre>";
echo "<pre>"; print_r($top_posters); echo "</pre>";
*/

require_once(FOOTERF);






/*
function getdbsize($tdb) {

	$sql_result = "SHOW TABLE STATUS FROM " .$tdb;
	$result = mysql_query($sql_result);
	mysql_close($db);

	if($result) {
		$size = 0;
		while ($data = mysql_fetch_array($result)) {
			$size = $size + $data["Data_length"] + $data["Index_length"];
		}
		return $size;
	}
	else {
		return FALSE;
	}
}
*/

function parsesize($size) {
	$kb = 1024;
	$mb = 1024 * $kb;
	$gb = 1024 * $mb;
	$tb = 1024 * $gb;
	if(!$size)
	{
		return '0';
	}
	if ($size < $kb) {
		return $size." b";
	}
	else if($size < $mb) {
		return round($size/$kb, 2)." kb";
	}
	else if($size < $gb) {
		return round($size/$mb, 2)." mb";
	}
	else if($size < $tb) {
		return round($size/$gb, 2)." gb";
	} else {
		return round($size/$tb, 2)." tb";
	}
}


?>