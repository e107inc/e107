<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/plugins/poll.php															|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
define(PT, $poll_title);
$sql -> db_Select("poll", "*", "poll_active='1'");
list($poll_id, $poll_datestamp, $poll_end_datestamp, $poll_admin_id, $poll_title_, $poll_option[1], $poll_option[2], $poll_option[3], $poll_option[4], $poll_option[5], $poll_option[6], $poll_option[7], $poll_option[8], $poll_option[9], $poll_option[10], $votes[1], $votes[2], $votes[3], $votes[4], $votes[5], $votes[6], $votes[7], $votes[8], $votes[9], $votes[10], $poll_ip, $poll_active) = $sql-> db_Fetch();
$vote_ip = explode("^", $poll_ip);

$user_ip = getip();

for($a=0; $a<count($vote_ip)-1; $a++){
	if($vote_ip[$a] == $user_ip){
		$mode = "voted";
	}
}

If(IsSet($_POST['vote'])){

	if(!eregi($user_ip, $poll_ip)){
		if($_POST['votea'] != "") {
			$num = "poll_votes_".$_POST['votea'];
			if ($poll_ip != "" && $poll_ip != "0") {
				$new_ip = $poll_ip.$user_ip."^";
			}
			else {
				$new_ip = $user_ip."^";
			}
			$sql3 = new db;
			$sql3 -> db_Update("poll", "$num=$num+1, poll_ip='$new_ip' WHERE poll_active='1' ");
			$mode = "voted";
		}
	}
}

$po = new poll;

if(!$sql -> db_Select("poll", "*", "poll_active='1' ")){
	$text = "<div style=\"text-align:center\">".LAN_162."</div>";
}else{
	list($poll_id, $poll_datestamp, $poll_end_datestamp, $poll_admin_id, $poll_title_, $poll_option[1], $poll_option[2], $poll_option[3], $poll_option[4], $poll_option[5], $poll_option[6], $poll_option[7], $poll_option[8], $poll_option[9], $poll_option[10], $votes[1], $votes[2], $votes[3], $votes[4], $votes[5], $votes[6], $votes[7], $votes[8], $votes[9], $votes[10], $poll_ip, $poll_active) = $sql-> db_Fetch();

	if($mode == "voted"){
		$po -> render_poll($poll_id, $poll_title_, $poll_option[1], $poll_option[2], $poll_option[3], $poll_option[4], $poll_option[5], $poll_option[6], $poll_option[7], $poll_option[8], $poll_option[9], $poll_option[10], "voted", $votes[1], $votes[2], $votes[3], $votes[4], $votes[5], $votes[6], $votes[7], $votes[8], $votes[9], $votes[10]);
	}else{
		$po -> render_poll($poll_id, $poll_title_, $poll_option[1], $poll_option[2], $poll_option[3], $poll_option[4], $poll_option[5], $poll_option[6], $poll_option[7], $poll_option[8], $poll_option[9], $poll_option[10], "notvoted", $votes[1], $votes[2], $votes[3], $votes[4], $votes[5], $votes[6], $votes[7], $votes[8], $votes[9], $votes[10]);
	}
}

class poll{
	
	function render_poll($poll_id, $poll_name, $poll_option_1, $poll_option_2, $poll_option_3, $poll_option_4, $poll_option_5, $poll_option_6, $poll_option_7, $poll_option_8, $poll_option_9, $poll_option_10, $mode = "normal", $vote_1 = 0, $vote_2 = 0, $vote_3 = 0, $vote_4 = 0, $vote_5 = 0, $vote_6 = 0, $vote_7 = 0, $vote_8 = 0, $vote_9 = 0, $vote_10 = 0){
		global $poll_title;
	$options = 0;
	$poll_name = stripslashes($poll_name);
	for($count=1; $count<=10; $count++){
		$var = "poll_option_".$count;
		$var2 = $$var;
		$var3 = "vote_".$count;
		$poll[$count] = stripslashes($var2);
		$votes[$count] = $$var3;
		if($var2 != ""){
			$options++;
		}
	}

	$p_total = array_sum($votes);
	if($mode == "normal" || $mode=="voted"){

		$counter = 1;
		if($p_total > 0){
			for($counter=1; $counter<=10; $counter++){
				$percen[$counter] = round(($votes[$counter]/$p_total)*100,2);
			}
		}
	}

	$text = "<div style=\"text-align:center\">
	<br />
	<b><i>".$poll_name."</i></b>
	<hr />
	</div>
	<br />";

	if($mode == "normal" || $mode=="notvoted"){
		$text .= "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
		<p>";
	}
	for($counter=1; $counter<=$options; $counter++){
	
		if ($mode == "normal" || $mode=="preview") {
			$text .= "<input type=\"radio\" name=\"votea\" value=\"$counter\" />
<b>".stripslashes($poll[$counter])."</b>
<br />
<span class=\"smalltext\">
<img src=\"".THEME."images/bar.jpg\" height=\"12\" width=\"".($percen[$counter]*1.5)."\" style=\"border : 1px solid Black\" alt=\"\" />
</span>
<br />";
		}
		if ($mode == "voted") {
			$text .= "<b>".stripslashes($poll[$counter])."</b>
<br />
<img src=\"".THEME."images/bar.jpg\" height=\"12\" width=\"".($percen[$counter]*1.5)."\" style=\"border : 1px solid Black\" alt=\"\" />
<br />";
		}
		if ($mode == "notvoted") {
			$text .= "<input type=\"radio\" name=\"votea\" value=\"$counter\" />
<b>".stripslashes($poll[$counter])."</b>";
		}

		if($percen[$counter] == ""){
			$percen[$counter] = 0;
		}
		if($votes[$counter] == ""){
			$votes[$counter] = 0;
		}

		if ($mode == "normal" || $mode=="voted" || $mode=="preview") {
			$text .= "<span class=\"smalltext\">".$percen[$counter]."% ";
			if($votes[$counter] == 0){
				$vt = "No votes";
			}else if($votes[$counter] == 1){
				$vt = "1 vote";
			}else{
				$vt = $votes[$counter]." votes";
			}
				
			$text .= "[".$vt."]
</span>";
		}
		$text .= "<br /><br />";		
	}

	$text .= "<br />";

	if ($mode == "normal" || $mode=="notvoted"){
		$text .= "\n</p>
<p style=\"text-align:center\">
<input  style=\"text-align:center\" class=\"button\" type=\"submit\" name=\"vote\" value=\"".LAN_163."\" />
</p>
";
	}
	$text .= "";

	if ($mode == "normal" || $mode=="notvoted"){
		$text .= "</form>";
	}

	if($p_total == ""){
		$p_total = 0;
	}
	
	$sql = new db;
	$comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$poll_id' AND comment_type='4'");


	$text .= "<div style=\"text-align:center\">
	".LAN_164." $p_total
	<br />
	<a href=\"comment.php?poll.".$poll_id."\">Comments: ".$comment_total."</a><br />
	[ <a href=\"oldpolls.php\">".LAN_165."</a> ]
	</div>";

	$ps = new table;
	$ps -> tablerender(LAN_184, $text);


	}
}


?>