<?php
/*
+ ----------------------------------------------------------------------------+
| 
|     e107 website system
|     Copyright (C) 2008-2016 e107 Inc (e107.org)
|     Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
|
+ ----------------------------------------------------------------------------+
*/

// DIRTY - needs input validation, streaky

require_once("class2.php");
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);


if(!e_AJAX_REQUEST) // Legacy method. 
{	
	$qs = explode("^", str_replace('&amp;', '&', e_QUERY));
	
	if (!$qs[0] || USER == FALSE || $qs[3] > 10 || $qs[3] < 1 || strpos($qs[2], '://') !== false)
	{
		e107::redirect();
		exit;
	}
	
	$table = $tp -> toDB($qs[0]);
	$itemid = intval($qs[1]);
	$returnurl = $tp -> toDB($qs[2]);
	$rate = intval($qs[3]);
	e107::getRate()->submitVote($table,$itemid,$rate);
	header("location:".$returnurl);	
	exit;
}
else // Ajax Used. 
{	
	if($_POST['mode'] == 'thumb')
	{
		if(vartrue($_GET['type']) !='up' && vartrue($_GET['type']) !='down')
		{
			exit;	
		}

		$table 		= $tp->toDB($_GET['table']);
		$itemid		= intval($_GET['id']);
		$type		= $_GET['type'];
		
		if($result = e107::getRate()->submitLike($table,$itemid,$type))
		{
			echo $result;
		}
		else // already liked/disliked 
		{
			exit;
		}	
	
	}
	elseif($_POST['table'])
	{
		$table 		= $tp->toDB($_POST['table']);
		$itemid		= intval($_POST['id']);
		$rate		= intval($_POST['score']) * 2;
		echo 		e107::getRate()->submitVote($table,$itemid,$rate);
	}
	
	exit;		
}
	
	
	
	
	
	
/*	
if ($sql -> db_Select("rate", "*", "rate_table='{$table}' AND rate_itemid='{$itemid}'"))
{
	$row = $sql -> db_Fetch();
	if(strpos($row['rate_voters'], ".".USERID.".") === FALSE)
	{
		$rate_voters = $row['rate_voters'].".".USERID.".";
		$new_rating = $row['rate_rating']+$rate;
		$sql -> db_Update("rate", "rate_votes=rate_votes+1, rate_rating='{$new_rating}', rate_voters='{$rate_voters}' WHERE rate_id='{$row['rate_id']}' ");
		if(!$returnurl)
		{
			$voteStatus = e107::getRate()->renderVotes($rate_voters,($row['rate_votes'] +1)); 
			echo $voteStatus."|".RATELAN_3;	// Thank you for your vote. 
		}
	}
	else
	{
		if($returnurl)
		{
			header("location:".e_BASE."index.php");	
		}
		else
		{
			echo "You already voted!";	
		}
		exit;
	}
}
else
{
	if($sql->db_Insert("rate", " 0, '{$table}', '{$itemid}', '{$rate}', '1', '.".USERID.".' "))
	{
		if(!$returnurl)
		{
			echo RATELAN_3; // Thank you for your vote. 	
		}
	}
	
}
 
 */
 


exit;
	
?>
