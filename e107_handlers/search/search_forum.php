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
|     $Revision: 1.7 $
|     $Date: 2005-03-14 09:09:23 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'tp.thread_name AS parent_name, t.thread_id, t.thread_name, t.thread_thread, t.thread_forum_id, t.thread_parent, t.thread_datestamp, t.thread_user, u.user_id, u.user_name, f.forum_class';
$search_fields = array('t.thread_name', 't.thread_thread');
$weights = array('1.2', '0.6');
$no_results = LAN_198;
$where = "f.forum_class IN (".USERCLASS_LIST.") AND";
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
	if ($row['thread_parent']) {
		$title = $row['parent_name'];
	} else {
		$title = $row['thread_name'];
	}

	$link_id = $row['thread_id'];

	$res['link'] = e_PLUGIN."forum/forum_viewtopic.php?".$link_id.".post";
	$res['title'] = $title ? "As part of thread: ".$title : LAN_SEARCH_9;	
	$res['summary'] = $row['thread_thread'];
	$res['detail'] = LAN_SEARCH_7.$row['user_name'].LAN_SEARCH_8.$datestamp;
	return $res;
}

?>