<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT')) { exit; }

// MOVED TO user/e_search.php

/* 

// advanced 
$advanced_where = "";
if (isset($_GET['time']) && is_numeric($_GET['time'])) {
	$advanced_where .= " user_join ".($_GET['on'] == 'new' ? '>=' : '<=')." '".(time() - $_GET['time'])."' AND";
}

// basic
$return_fields = 'user_id, user_name, user_email, user_signature, user_join';
$search_fields = array('user_name', 'user_signature');
$weights = array('1.2', '0.6', '0.6', '0.6', '0.6');
$no_results = LAN_198;
$where = $advanced_where;
$order = array('user_join' => DESC);

$ps = $sch -> parsesearch('user', $return_fields, $search_fields, $weights, 'search_user', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_user($row) {

	$tp = e107::getParser();
	$res['link'] = e107::getUrl()->create('user/profile/view', array('id' => $row['user_id'], 'name' => $row['user_name']));//"user.php?id.".$row['user_id'];
	$res['pre_title'] = $row['user_id']." | ";
	$res['title'] = $row['user_name'];
	$res['summary'] = $row['user_signature'] ?  LAN_SEARCH_72.": ".$row['user_signature'] : LAN_SEARCH_73;
	$res['detail'] = LAN_SEARCH_74.": ".$tp->toDate($row['user_join'], "long");
	return $res;
}

?>