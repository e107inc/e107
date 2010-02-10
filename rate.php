<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc 
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/rate.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

// DIRTY - needs input validation, streaky

require_once("class2.php");
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

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
		$sql -> db_Update("rate", "rate_votes=rate_votes+1, rate_rating='{$new_rating}', rate_voters='{$rate_voters}' WHERE rate_id='{$row['rate_id']}' ");
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