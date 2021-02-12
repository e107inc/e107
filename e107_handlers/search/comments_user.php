<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

$comments_title = ADLAN_0;
$comments_type_id = 'profile';
$comments_return['user'] = "u.user_name";
$comments_table['user'] = "LEFT JOIN #user AS u ON c.comment_type='profile' AND u.user_id = c.comment_item_id";
function com_search_profile($row) {
	global $con;
	$datestamp = $con -> convert_date($row['comment_datestamp'], "long");
	$res['link'] = "user.php?id.".$row['comment_item_id'];
	$res['pre_title'] = LAN_SEARCH_77.": ";
	$res['title'] = $row['user_name'];
	$res['summary'] = $row['comment_comment'];
	preg_match("/([0-9]+)\.(.*)/", $row['comment_author'], $user);
	$res['detail'] = LAN_SEARCH_7."<a href='user.php?id.".$user[1]."'>".$user[2]."</a>".LAN_SEARCH_8.$datestamp;
	return $res;
}

