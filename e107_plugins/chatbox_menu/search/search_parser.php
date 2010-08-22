<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$cb_count =  $sql -> db_Count('chatbox');

// advanced 
$advanced_where = "";
if (isset($_GET['time']) && is_numeric($_GET['time'])) {
	$advanced_where .= " cb_datestamp ".($_GET['on'] == 'new' ? '>=' : '<=')." '".(time() - $_GET['time'])."' AND";
}

if (isset($_GET['author']) && $_GET['author'] != '') {
	$advanced_where .= " cb_nick LIKE '%".$tp -> toDB($_GET['author'])."%' AND";
}

// basic
$return_fields = 'cb_id, cb_nick, cb_message, cb_datestamp';
$search_fields = array('cb_nick', 'cb_message');
$weights = array('1', '1');
$no_results = LAN_198;
$where = $advanced_where;
$order = array('cb_datestamp' => DESC);

$ps = $sch -> parsesearch('chatbox', $return_fields, $search_fields, $weights, 'search_chatbox', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_chatbox($row) {
	global $con, $cb_count;
	preg_match("/([0-9]+)\.(.*)/", $row['cb_nick'], $user);
	$res['link'] = e_PLUGIN."chatbox_menu/chat.php?".$row['cb_id'].".fs";
	$res['pre_title'] = LAN_SEARCH_7;
	$res['title'] = $user[2];
	$res['summary'] = $row['cb_message'];
	$res['detail'] = $con -> convert_date($row['cb_datestamp'], "long");
	return $res;
}

?>