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
	$itemid = (int) $qs[1];
	$returnurl = $tp -> toDB($qs[2]);
	$rate = (int) $qs[3];
	e107::getRate()->submitVote($table,$itemid,$rate);
	e107::redirect($returnurl);
	exit;
}
else // Ajax Used. 
{	
	if($_POST['mode'] == 'thumb')
	{
		if(vartrue($_GET['type']) !== 'up' && vartrue($_GET['type']) !== 'down')
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
	
	
