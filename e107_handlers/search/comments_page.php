<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/search/comments_page.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

$comments_title = LAN_418;
$comments_type_id = 'page';
$comments_return['page'] = "cp.page_id, cp.page_title";
$comments_table['page'] = "LEFT JOIN #page AS cp ON c.comment_type='page' AND cp.page_id = c.comment_item_id";
function com_search_page($row) {
	global $con;
	$datestamp = $con -> convert_date($row['comment_datestamp'], "long");
	$res['link'] = "page.php?".$row['page_id'];
	$res['pre_title'] = LAN_SEARCH_76.": ";
	$res['title'] = $row['page_title'];
	$res['summary'] = $row['comment_comment'];
	preg_match("/([0-9]+)\.(.*)/", $row['comment_author'], $user);
	$res['detail'] = LAN_SEARCH_7."<a href='user.php?id.".$user[1]."'>".$user[2]."</a>".LAN_SEARCH_8.$datestamp;
	return $res;
}

