<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/oldpolls.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);
require_once(e_HANDLER."comment_class.php");
$sql2 = new db; $cobj = new comment;
$from = (!e_QUERY ? $from = 0 : $from = e_QUERY);

$poll_total = $sql -> db_Count("poll", "(*)", "WHERE poll_active=0 AND poll_end_datestamp !=0");

$p_query = e_PAGE.(e_QUERY ? "?".e_QUERY : "");

if($cache = retrieve_cache($p_query)){
	echo $cache;
	require_once(e_HANDLER."np_class.php");
	$ix = new nextprev("oldpolls.php", $from, 10, $poll_total, LAN_96);
	require_once(FOOTERF);
	exit;
}

ob_start();

if(!$poll_total){
	$ns -> tablerender(LAN_92, "<div style='text-align:center'>".LAN_93."</div>");
	require_once(FOOTERF);
	exit;
}

$sql -> db_Select("poll", "*", "poll_active='0' ORDER BY poll_datestamp DESC LIMIT $from, 10");

$sql2 = new db;
while(list($poll_id, $poll_datestamp, $poll_end_datestamp, $poll_admin_id, $poll_title, $poll[1], $poll[2], $poll[3], $poll[4], $poll[5], $poll[6], $poll[7], $poll[8], $poll[9], $poll[10], $votes[1], $votes[2], $votes[3], $votes[4], $votes[5], $votes[6], $votes[7], $votes[8], $votes[9], $votes[10], $poll_ip, $poll_active) = $sql-> db_Fetch()){
	
	$p_total = array_sum($votes);
	if ($p_total > 0){
			
		for($counter=1; $counter<=10; $counter++){
			$percen[$counter] = round(($votes[$counter]/$p_total)*100,2);
		}
	}

	$obj = new convert;
	$datestamp = $obj -> convert_date($poll_datestamp, "long");
	$end_datestamp = $obj -> convert_date($poll_end_datestamp, "long");

	$sql2 -> db_Select("user", "*",  "user_id='$poll_admin_id' ");
	$row = $sql2 -> db_Fetch();
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
	while($poll[$c]){
		$text .= "<tr> 
		<td style='width:40% 'class='mediumtext'>
		<b>".stripslashes($poll[$c])."</b>
		</td>
		<td class='smalltext'>
		<img src='".THEME."/images/bar.jpg' height='12' width='";
					
		if(($percen[$c]*3) > 180){
			$perc = 180;
		}else{
			$perc = ($percen[$c]*3);
		}			
		
		$text .= $perc."' border='1'> ".$percen[$c]."% [Votes: ".$votes[$c]."]</div>
		</td>
		</tr>";
		$c++;

	}

	$field = $poll_id;
	$comtype = 4;
	$comment_total = $sql2 -> db_Select("comments", "*",  "comment_item_id='$field' AND comment_type='$comtype' ORDER BY comment_datestamp");
	if($comment_total){
		$text .= "<tr><td colspan='2'>
		<input class='button' type ='button' style=''width: 35px'; cursor:hand' size='30' value='".LAN_97." ($comment_total)' onClick='expandit(this)'>
		<div style='display:none' style=&{head};'>";
		while($row = $sql2 -> db_Fetch()){
			$text .= $cobj -> render_comment($row);
		}
		$text .= "</div></td></tr>";
	}

	$text .= "</table>";
	$ns -> tablerender(LAN_98." #".$poll_id, $text);
}

if($pref['cachestatus']){
	set_cache($p_query,ob_get_contents());
}


require_once(e_HANDLER."np_class.php");
$ix = new nextprev("oldpolls.php", $from, 10, $poll_total, LAN_96);



require_once(FOOTERF);
?>