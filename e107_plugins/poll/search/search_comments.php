<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */
 
if (!defined('e107_INIT')) { exit; }

$comments_title = LAN_PLUGIN_POLL_NAME;
$comments_type_id = 4;
$comments_return['poll'] = "po.poll_id, po.poll_title";
$comments_table['poll'] = "LEFT JOIN #polls AS po ON c.comment_type=4 AND po.poll_id = c.comment_item_id";

function com_search_4($row) {
	global $con;
	$nick = preg_replace("/[0-9]+\./", "", $row['comment_author']);
	$datestamp = $con -> convert_date($row['comment_datestamp'], "long");
	$res['link'] = e_PLUGIN."poll/oldpolls.php?".$row['poll_id'];
	$res['pre_title'] = 'Posted in reply to poll: ';
	$res['title'] = $row['poll_title'];
	$res['summary'] = $row['comment_comment'];
	$res['detail'] = LAN_SEARCH_7.$nick.LAN_SEARCH_8.$datestamp;
	return $res;
}


