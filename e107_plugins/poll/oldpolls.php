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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/poll/oldpolls.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-03-04 08:29:18 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(HEADERF);
require_once(e_HANDLER."comment_class.php");
$cobj = new comment;
$gen = new convert;

@include(e_PLUGIN."poll/languages/".e_LANGUAGE.".php");
@include(e_PLUGIN."poll/languages/English.php");


if(e_QUERY)
{
	$query = "SELECT p.*, u.user_name FROM #poll AS p 
	LEFT JOIN #user AS u ON p.poll_admin_id = u.user_id
	WHERE p.poll_type=1 AND p.poll_id=".e_QUERY;

	if($sql->db_Select_gen($query))
	{

		$row = $sql -> db_Fetch();
		extract($row);

		$optionArray = explode(chr(1), $poll_options);
		$optionArray = array_slice($optionArray, 0, -1);      
		$voteArray = explode(chr(1), $poll_votes);
		$voteArray = array_slice($voteArray, 0, -1);

		$voteTotal = array_sum($voteArray);
		$percentage = array();
		foreach($voteArray as $votes)
		{
			$percentage[] = round(($votes/$voteTotal) * 100, 2);
		}

		$start_datestamp = $gen->convert_date($poll_datestamp, "long");
		$end_datestamp = $gen->convert_date($poll_end_datestamp, "long");

		$text = "<table style='width:100%'>
		<tr>
		<td colspan='2' class='mediumtext' style='text-align:center'>
		<b>".$tp -> toHTML($poll_title)."</b>
		<div class='smalltext'>".POLLAN_35." <a href='".e_BASE."user.php?id.$user_id'>".$user_name."</a>.<br /> ".POLLAN_37." ".$start_datestamp." ".POLLAN_38." ".$end_datestamp.".<br />".POLLAN_26.": $voteTotal</div>
		<br />
		 
		</td>
		</tr>";

		$count = 0;
		foreach($optionArray as $option)
		{
			$text .= "<tr>
			<td style='width:40% 'class='mediumtext'><b>".$tp -> toHTML($option)."</b></td>
			<td class='smalltext'>
			<img src='".THEME."/images/bar.jpg' height='12' width='";
		 
			if (($percentage[$count] * 3) > 180) {
				$perc = 180;
			} else {
				$perc = ($percentage[$count] * 3);
			}
		 
			$text .= $perc."' style='border:1px solid #000;'> ".$percentage[$count]."% [Votes: ".$voteArray[$count]."]</div>
			</td>
			</tr>\n";
			$count++;

		}
	
		if($comment_total = $sql -> db_Select("comments", "*", "comment_item_id=$poll_id AND comment_type=4 ORDER BY comment_datestamp"))
		{
			$text .= "<tr><td colspan='2'><br /><br />";
			while ($row = $sql->db_Fetch()) {
				$text .= $cobj->render_comment($row);
			}
			$text .= "</td></tr>";
		}
	 
		$text .= "</table>";
		$ns->tablerender(POLL_ADLAN01." #".$poll_id, $text);
	}
}

$query = "SELECT p.*, u.user_name FROM #poll AS p 
LEFT JOIN #user AS u ON p.poll_admin_id = u.user_id
WHERE p.poll_type=1 
ORDER BY p.poll_datestamp DESC";

if(!$sql->db_Select_gen($query))
{
	$ns->tablerender(POLLAN_28, "<div style='text-align:center'>".POLLAN_33."</div>");
	require_once(FOOTERF);
	exit;
}

$array = $sql -> db_getList();
$oldpollArray = array_slice($array, 1);

if(!count($oldpollArray))
{
	$ns->tablerender(POLLAN_28, "<div style='text-align:center'>".POLLAN_33."</div>");
	require_once(FOOTERF);
	exit;
}

$text = "<table style='width: 100%;'>
<tr>
<td class='forumheader3' style='width: 50%;'>".POLLAN_34."</td>
<td class='forumheader3' style='width: 20%;'>".POLLAN_35."</td>
<td class='forumheader3' style='width: 30%;'>".POLLAN_36."</td>
</tr>\n";

foreach($oldpollArray as $oldpoll)
{
	extract($oldpoll);
	$from = $gen->convert_date($poll_datestamp, "short");
	$to = $gen->convert_date($poll_end_datestamp, "short");

	$text .= "<tr>
	<td class='forumheader3' style='width: 50%;'><a href='".e_SELF."?$poll_id'>$poll_title</a></td>
	<td class='forumheader3' style='width: 20%;'><a href='".e_BASE."user.php?id.$poll_admin_id'>$user_name</a></td>
	<td class='forumheader3' style='width: 30%;'>$from to $to</td>
	</tr>\n";
}
	
$text .= "</table>";
$ns->tablerender(POLLAN_28, $text);
require_once(FOOTERF);

?>