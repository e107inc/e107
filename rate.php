<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/rate.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).	
+---------------------------------------------------------------+
*/
require_once("class2.php");

$qs = explode("^", e_QUERY);

if(!$qs[0] || USER == FALSE || $qs[3]>10 || $qs[3]<1){
	header("location:".e_BASE."index.php");
	exit;
}

$table = $qs[0];
$itemid = $qs[1];
$returnurl = $qs[2];
$rate = $qs[3];

if($sql -> db_Select("rate", "*", "rate_table='$table' AND rate_itemid='$itemid' ")){
	$row = $sql -> db_Fetch();
	extract($row);
	$rate_voters .= USERID.".";
	$sql -> db_Update("rate", "rate_votes=rate_votes+1, rate_rating=rate_rating+'$rate', rate_voters='$rate_voters' WHERE rate_itemid='$itemid' ");
}else{
	$sql -> db_Insert("rate", " 0, '$table', '$itemid', '$rate', '1', '.".USERID.".' ");
}

header("location:".$returnurl);
exit;

?>