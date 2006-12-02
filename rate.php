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
|     $Source: /cvs_backup/e107_0.8/rate.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:33:09 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

// DIRTY - needs input validation, streaky

require_once("class2.php");

$qs = explode("^", e_QUERY);

if (!$qs[0] || USER == FALSE || $qs[3] > 10 || $qs[3] < 1 || strpos($qs[2], '://') !== false)
{
	header("location:".e_BASE."index.php");
	exit;
}
	
$table = $tp -> toDB($qs[0]);
$itemid = intval($qs[1]);
$returnurl = $tp -> toDB($qs[2]);
$rate = intval($qs[3]);
	
if ($sql -> db_Select("rate", "*", "rate_table='{$table}' AND rate_itemid='{$itemid}'"))
{
	$row = $sql -> db_Fetch();
	if(strpos($row['rate_voters'], ".".USERID.".") === FALSE)
	{
		$rate_voters = $row['rate_voters'].".".USERID.".";
		$new_rating = $row['rate_rating']+$rate;
		$sql -> db_Update("rate", "rate_votes=rate_votes+1, rate_rating='{$new_rating}', rate_voters='{$rate_voters}' WHERE rate_itemid='{$itemid}' ");
	}
	else
	{
		header("location:".e_BASE."index.php");
		exit;
	}
}
else
{
	$sql->db_Insert("rate", " 0, '{$table}', '{$itemid}', '{$rate}', '1', '.".USERID.".' ");
}

header("location:".$returnurl);
exit;
	
?>