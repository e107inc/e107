<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/oldpolls.php																|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

if(!$from){
	$from = "0";
}

$sql -> db_Select("poll", "*", "poll_active='0' ORDER BY poll_datestamp DESC LIMIT $from, 10");
$poll_total = $sql -> db_Rows();

$ns -> tablerender("<div style=\"text-align:center\">".LAN_92."</div>", "");

echo "<br />";

if($poll_total == 0){
	$text = "<div style=\"text-align:center\">".LAN_93."</div>";
	$ns -> tablerender("", $text);
	require_once(FOOTERF);
	exit;
}

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


$text = "<table style=\"width:95%\">
<tr>
<td colspan=\"2\" class=\"mediumtext\" style=\"text-align:center\">
<b>".$poll_title."</b>
<div class=\"smalltext\">".LAN_94." <a href=\"mailto:$admin_email\">".$admin_name."</a> from ".$datestamp." to ".$end_datestamp.". ".LAN_95." $p_total</div>
<br />

</td>
</tr>";
$c = 1;
while($poll[$c]){
	$text .= "<tr> 
	<td style=\"width:40% \"class=\"mediumtext\">
	<b>".stripslashes($poll[$c])."</b>
	</td>
	<td class=\"smalltext\">
	<img src=\"".THEME."/images/bar.jpg\" height=\"12\" width=\"";
				
	if(($percen[$c]*3) > 180){
		$perc = 180;
	}else{
		$perc = ($percen[$c]*3);
	}			
	
	$text .= $perc."\" border=\"1\"> ".$percen[$c]."% [Votes: ".$votes[$c]."]</div>
	</td>
	</tr>";
	$c++;
}

	$text .= "</table>";
	$ns -> tablerender("", $text);
}

$ix = new nextprev("oldpolls.php", $from, 10, $poll_total, LAN_96);


require_once(FOOTERF);
?>