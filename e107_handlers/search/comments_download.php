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
	exit;
}

//TODO Rework to v2 standards into e107_plugins/download/e_search.php

$comments_title = defset('LAN_PLUGIN_DOWNLOAD_NAME');
$comments_type_id = '2';
$comments_return['download'] = "d.download_id, d.download_name";
$comments_table['download'] = "LEFT JOIN #download AS d ON c.comment_type=2 AND d.download_id = c.comment_item_id";

/**
 * @param $row
 * @return array
 */
function com_search_2($row)
{

	$datestamp = e107::getParser()->toDate($row['comment_datestamp'], "long");
	$res['link'] = "download.php?view." . $row['download_id'];
	$res['pre_title'] = !empty($row['download_name']) ? defset('LAN_SEARCH_70') . ": " : "";
	$res['title'] = !empty($row['download_name']) ? $row['download_name'] : defset('LAN_SEARCH_9');
	$res['summary'] = $row['comment_comment'];
	preg_match("/([0-9]+)\.(.*)/", $row['comment_author'], $user);
	$res['detail'] = defset('LAN_SEARCH_7') . "<a href='user.php?id." . $user[1] . "'>" . $user[2] . "</a>" . defset('LAN_SEARCH_8') . $datestamp;

	return $res;
}

