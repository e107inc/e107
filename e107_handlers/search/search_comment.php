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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/search_comment.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-03-21 22:11:39 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'c.comment_item_id, c.comment_author, c.comment_datestamp, c.comment_comment, c.comment_type';

foreach ($search_prefs['comments_handlers'] as $h_key => $value) {
	if (check_class($value['class'])) {
		$path = ($value['dir'] == 'core') ? e_HANDLER.'search/comments_'.$h_key.'.php' : e_PLUGIN.$value['dir'].'/comments_search.php';
		require_once($path);
		$in[] = "'".$value['id']."'";
		$join[] = $comments_table[$h_key];
		$return_fields .= ', '.$comments_return[$h_key];
	}
}

$search_fields = array('c.comment_comment', 'c.comment_author');
$weights = array('1.2', '0.6');
$no_results = LAN_198;
$where = "comment_type IN (".implode(',', $in).") AND";
$order = array('comment_datestamp' => DESC);
$table = "comments AS c ".implode(' ', $join);

$ps = $sch -> parsesearch($table, $return_fields, $search_fields, $weights, 'search_comment', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_comment($row) {	
	$res = call_user_func('com_search_'.$row['comment_type'], $row);
	return $res;
}

?>