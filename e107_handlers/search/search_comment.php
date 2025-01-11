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
 * $Source: /cvs_backup/e107_0.8/e107_handlers/search/search_comment.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

// TODO Rework all of this to v2 standards while maintaining BC.

// advanced 
$advanced_where = "";
if (isset($_GET['type']) && $_GET['type'] != 'all') {
	$advanced_where .= " c.comment_type='".(str_replace('s_', '', $tp -> toDB($_GET['type'])))."' AND";
}

if (isset($_GET['time']) && is_numeric($_GET['time'])) {
	$advanced_where .= " c.comment_datestamp ".($_GET['on'] == 'new' ? '>=' : '<=')." '".(time() - $_GET['time'])."' AND";
}

if (isset($_GET['author']) && $_GET['author'] != '') {
	$advanced_where .= " c.comment_author_name LIKE '%".$tp -> toDB($_GET['author'])."%' AND";
}

//basic
$return_fields = 'c.comment_item_id, c.comment_author_id, comment_author_name, c.comment_datestamp, c.comment_comment, c.comment_type';

foreach($search_prefs['comments_handlers'] as $h_key => $value)
{
	if(check_class($value['class']))
	{
		if($value['dir'] == 'core')
		{
			$path = e_HANDLER . 'search/comments_' . $h_key . '.php';
		}
		else
		{
			if(!e107::isInstalled($value['dir']))
			{
				continue;
			}

			$path = e_PLUGIN . $value['dir'] . '/search/search_comments.php';
		}
		$path = ($value['dir'] == 'core') ? e_HANDLER . 'search/comments_' . $h_key . '.php' : e_PLUGIN . $value['dir'] . '/search/search_comments.php';
		if(is_readable($path)) // TODO Rework this to use e_search.php
		{
			require_once($path);
			$in[] = "'" . $value['id'] . "'";
			$join[] = $comments_table[$h_key];
			$return_fields .= ', ' . $comments_return[$h_key];
		}

	}
}

$search_fields = array('c.comment_comment', 'c.comment_author_name');
$weights = array('1.2', '0.6');
$no_results = "<div class='alert alert-danger'>".LAN_198."</div>"; //LAN_198;
$where = "comment_type IN (".implode(',', $in).") AND".$advanced_where;
$order = array('comment_datestamp' => 'DESC');
$table = "comments AS c ".implode(' ', $join);

$ps = $sch -> parsesearch($table, $return_fields, $search_fields, $weights, 'search_comment', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

	/**
	 * @param $row
	 * @return false|mixed|void
	 */
	function search_comment($row) {
	if (is_callable('com_search_'.$row['comment_type'])) {
		return call_user_func('com_search_'.$row['comment_type'], $row);
	}
}

