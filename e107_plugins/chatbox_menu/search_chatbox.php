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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/chatbox_menu/search_chatbox.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-03-08 16:25:05 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'cb_id, cb_nick, cb_message, cb_datestamp';
$search_fields = array('cb_nick', 'cb_message');
$weights = array('1', '1');
$no_results = LAN_198;
$where = "";
$order = array('cb_datestamp' => DESC);

$ps = $sch -> parsesearch('chatbox', $return_fields, $search_fields, $weights, 'search_chatbox', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_chatbox($row) {
	global $con;
	$row['cb_nick'] = eregi_replace("[0-9]+\.", "", $row['cb_nick']);
	$res['link'] = e_PLUGIN."chatbox_menu/chat.php?".$row['cb_id'].".fs";
	$res['title'] = $row['cb_nick'];
	$res['summary'] = $row['cb_message'];
	$res['detail'] = $con -> convert_date($row['cb_datestamp'], "long");
	return $res;
}

?>