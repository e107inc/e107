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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/search_user.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-03-21 22:11:39 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$return_fields = 'user_id, user_name, user_email, user_homepage, user_location, user_signature, user_join';
$search_fields = array('user_name', 'user_email', 'user_homepage', 'user_location', 'user_signature');
$weights = array('1.2', '0.6', '0.6', '0.6', '0.6');
$no_results = LAN_198;
$where = "";
$order = array('user_join' => DESC);

$ps = $sch -> parsesearch('user', $return_fields, $search_fields, $weights, 'search_user', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_user($row) {
	global $con;
	$res['link'] = "user.php?id.".$row['user_id'];
	$res['pre_title'] = $row['user_id']." | ";
	$res['title'] = $row['user_name'];
	$res['summary'] = $row['user_signature'] ? "Signature: ".$row['user_signature'] : "No Signature.";
	$res['detail'] = "Signed up on: ".$con -> convert_date($row['user_join'], "long");
	return $res;
}

?>