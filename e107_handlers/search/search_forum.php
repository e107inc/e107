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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/search_forum.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-02-15 11:25:50 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'tp.thread_name AS parent_name, t.thread_id, t.thread_name, t.thread_thread, t.thread_forum_id, t.thread_parent, t.thread_datestamp, t.thread_user, u.user_id, u.user_name, f.forum_class';
$search_fields = array('t.thread_name', 't.thread_thread');
$weights = array('1.2', '0.6');
$no_results = LAN_198;
$where = "";
$order = array('thread_datestamp' => DESC);
$table = "forum_t AS t LEFT JOIN #user AS u ON t.thread_user = u.user_id 
		LEFT JOIN #forum AS f ON t.thread_forum_id = f.forum_id
		LEFT JOIN #forum_t AS tp ON t.thread_parent = tp.thread_id";

$ps = $sch -> parsesearch($table, $return_fields, $search_fields, $weights, 'search_forum', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_forum($row) {
	global $con;
	$datestamp = $con -> convert_date($row['thread_datestamp'], "long");
	if (check_class($row['forum_class'])) {
		if ($row['thread_parent']) {
			$title = $row['parent_name'];
		} else {
			$title = $row['thread_name'];
		}
		if ($row['thread_parent'] != 0) {
			$link_id = $row['thread_parent'];
		} else {
			$link_id = $row['thread_id'];
		}
		$res['link'] = e_PLUGIN."forum/forum_viewtopic.php?".$link_id;
		$res['title'] = $title ? "As part of thread: ".$title : LAN_SEARCH_9;
		
		$res['summary'] = $row['thread_thread'];
		$res['detail'] = LAN_SEARCH_7.$row['user_name'].LAN_SEARCH_8.$datestamp;
	} else {
		$res['omit_result'] = TRUE;
	}
	return $res;
}

/*
if ($results = $sql->db_Select("forum_t", "*", "thread_name REGEXP('".$query."') OR thread_thread REGEXP('".$query."')")) {
	$c = 0;
	$sql2 = new db;
	while (list($thread_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active, $thread_lastpost, $thread_s) = $sql->db_Fetch()) {
		 
		$sql2->db_Select("forum", "*", "forum_id='$thread_forum_id' ");
		$row = $sql2->db_Fetch();
		@extract($row);
		if (check_class($forum_class)) {
			 
			if ($thread_parent) {
				$sql3 = new db;
				$sql3->db_Select("forum_t", "thread_name", "thread_id='$thread_parent'");
				list($forum_t['thread_name']) = $sql3->db_Fetch();
				$thread_name = parsesearch($forum_t['thread_name'], $query);
			} else {
				$thread_name = parsesearch($thread_name, $query);
			}
			 
			$thread_thread = parsesearch($thread_thread, $query);
			 
			if ($thread_parent != 0) {
				$tmp = $thread_parent;
			} else {
				$tmp = $thread_id;
			}
			$action = "forum_viewtopic.php?".$thread_forum_id.".".$tmp."";
			$text .= "<form method='post' action='$action' id='forum_".$c."'>
				<input type='hidden' name='highlight_search' value='1' /><input type='hidden' name='search_query' value='$query' /><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='javascript:document.getElementById(\"forum_".$c."\").submit()'>$thread_name</a></b></form><br />$thread_thread<br /><br />";
			$c ++;
		} else {
			$results = $results -1;
		}
	}
} else {
	$text .= LAN_198;
}
*/
?>