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
|     $Revision: 1.3 $
|     $Date: 2005-03-03 19:59:01 $
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
		<div class='smalltext'>".POLL_510." <a href='".e_BASE."user.php?id.$user_id'>".$user_name."</a>.<br /> ".POLL_515.$start_datestamp.POLL_516.$end_datestamp.".<br />".POLL_511." $voteTotal</div>
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
		$ns->tablerender(POLL_184." #".$poll_id, $text);
	}
}















$query = "SELECT p.*, u.user_name FROM #poll AS p 
LEFT JOIN #user AS u ON p.poll_admin_id = u.user_id
WHERE p.poll_type=1 
ORDER BY p.poll_datestamp DESC";

if(!$sql->db_Select_gen($query))
{
	$ns->tablerender(POLL_165, "<div style='text-align:center'>".POLL_509."</div>");
	require_once(FOOTERF);
	exit;
}

$array = $sql -> db_getList();
$oldpollArray = array_slice($array, 1);

$text = "<table style='width: 100%;'>
<tr>
<td class='forumheader3' style='width: 50%;'>Title</td>
<td class='forumheader3' style='width: 20%;'>Posted by</td>
<td class='forumheader3' style='width: 30%;'>Active</td>
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
$ns->tablerender(POLL_165, $text);
require_once(FOOTERF);
exit;





























$sql->db_Select("poll", "*", "poll_active='0' ORDER BY poll_datestamp DESC LIMIT $from, 10");
	
$sql2 = new db;
while (list($poll_id, $poll_datestamp, $poll_end_datestamp, $poll_admin_id, $poll_title, $poll[1], $poll[2], $poll[3], $poll[4], $poll[5], $poll[6], $poll[7], $poll[8], $poll[9], $poll[10], $votes[1], $votes[2], $votes[3], $votes[4], $votes[5], $votes[6], $votes[7], $votes[8], $votes[9], $votes[10], $poll_ip, $poll_active) = $sql->db_Fetch()) {
	 
	$p_total = array_sum($votes);
	if ($p_total > 0) {
		 
		for($counter = 1; $counter <= 10; $counter++) {
			$percen[$counter] = round(($votes[$counter]/$p_total) * 100, 2);
		}
	}
	 
	$obj = new convert;
	$datestamp = $obj->convert_date($poll_datestamp, "long");
	$end_datestamp = $obj->convert_date($poll_end_datestamp, "long");
	 
	$sql2->db_Select("user", "*", "user_id='$poll_admin_id' ");
	$row = $sql2->db_Fetch();
	extract($row);
	 
	$text = "<table style='width:95%'>
		<tr>
		<td colspan='2' class='mediumtext' style='text-align:center'>
		<b>".stripslashes($poll_title)."</b>
		<div class='smalltext'>".LAN_94." <a href='".e_BASE."user.php?id.$user_id'>".$user_name."</a>. ".LAN_99.$datestamp.LAN_100.$end_datestamp.". ".LAN_95." $p_total</div>
		<br />
		 
		</td>
		</tr>";
	$c = 1;
	while ($poll[$c]) {
		$text .= "<tr>
			<td style='width:40% 'class='mediumtext'>
			<b>".stripslashes($poll[$c])."</b>
			</td>
			<td class='smalltext'>
			<img src='".THEME."/images/bar.jpg' height='12' width='";
		 
		if (($percen[$c] * 3) > 180) {
			$perc = 180;
		} else {
			$perc = ($percen[$c] * 3);
		}
		 
		$text .= $perc."' border='1'> ".$percen[$c]."% [Votes: ".$votes[$c]."]</div>
			</td>
			</tr>";
		$c++;
		 
	}
	 
	$field = $poll_id;
	$comtype = 4;
	$comment_total = $sql2->db_Select("comments", "*", "comment_item_id='$field' AND comment_type='$comtype' ORDER BY comment_datestamp");
	if ($comment_total) {
		$text .= "<tr><td colspan='2'>
			<input class='button' type ='button' style=''width: 35px'; cursor:hand' size='30' value='".LAN_97." ($comment_total)' onClick='expandit(this)'>
			<div style='display:none' style=&{head};'>";
		while ($row = $sql2->db_Fetch()) {
			$text .= $cobj->render_comment($row);
		}
		$text .= "</div></td></tr>";
	}
	 
	$text .= "</table>";
	$ns->tablerender(LAN_98." #".$poll_id, $text);
}
	
if ($pref['cachestatus']) {
	$e107cache->set($p_query, ob_get_contents());
}
ob_end_flush(); /* dump collected data */
	
require_once(e_HANDLER."np_class.php");
$ix = new nextprev("oldpolls.php", $from, 10, $poll_total, LAN_96);
	
	
	
require_once(FOOTERF);
?>