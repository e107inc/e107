<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/classes/poll_class.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
class poll{	

	function delete_poll($existing){
		$cls = new db;
		if($cls -> db_Delete("poll", " poll_id='".$existing."' ")){
			return  "Poll deleted.";
		}
	}

	function submit_poll($poll_id, $poll_name, $poll_option, $activate, $id=0, $ref="menu"){
		$datestamp = time();
		$cls = new db;
		if($activate && $ref == "menu"){
			$cls -> db_Update("poll", "poll_active='0', poll_end_datestamp='$datestamp' WHERE poll_active='1' OR poll_active='2' ");
			$message = "Poll entered into database and made active.";
		}else{
			$message = "Poll entered into database.";
		}
		if($poll_id){
			$cls -> db_Update("poll", "poll_title='$poll_name', poll_option_1='".$poll_option[0]."', poll_option_2='".$poll_option[1]."', poll_option_3='".$poll_option[2]."', poll_option_4='".$poll_option[3]."', poll_option_5='".$poll_option[4]."', poll_option_6='".$poll_option[5]."', poll_option_7='".$poll_option[6]."', poll_option_8='".$poll_option[7]."', poll_option_9='".$poll_option[8]."', poll_option_10='".$poll_option[9]."', poll_active='$activate' WHERE poll_id='$poll_id' ");
			$message = "Poll updated in database.";
		}else{
			if($id){
				$datestamp = $id;
			}
			$cls -> db_Insert("poll", "'0', '$datestamp', '0', '".ADMINID."', '$poll_name', '".$poll_option[0]."', '".$poll_option[1]."', '".$poll_option[2]."', '".$poll_option[3]."', '".$poll_option[4]."', '".$poll_option[5]."', '".$poll_option[6]."', '".$poll_option[7]."', '".$poll_option[8]."', '".$poll_option[9]."', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '^', '$activate' ");
		}
		unset($_POST['poll_id']);
		return $message;
	}

	function render_poll($poll_id, $poll_question, $poll_option, $votes, $mode, $type="menu"){

		$poll_question = stripslashes($poll_question);
		$vote_total = array_sum($votes);
		foreach($poll_option as $key => $value){
			if(empty($value)){
				unset($poll_option[$key]);
			}
		}
		$options = count($poll_option);
		for($count=0; $count<=$options; $count++){
			if(IsSet($poll_option[$count])){
				$poll_option[$count] = stripslashes($poll_option[$count]);
			}else{
				unset($poll_option[$count]);
			}
			if($vote_total){
				$percentage[$count] = round(($votes[$count]/$vote_total)*100,2);
			}
		}


		if($mode == "preview"){
			echo "<div style='text-align:center'>\n<table style='width:350px'>\n<tr>\n<td>";
			$mode = "notvoted";
			$preview = TRUE;
		}else if($type == "forum"){
			echo "<div style='text-align:center'>\n<table style='width:350px'>\n<tr>\n<td>";
			$preview = TRUE;
		}


		$text = "<div style=\"text-align:center\">\n<br />\n<b><i>".$poll_question."</i></b>\n<hr />\n</div>\n<br />";	 // print poll question
	
		switch ($mode){

			case "voted":
				for($count=0; $count<=($options-1); $count++){
					$text .= "<b>".$poll_option[$count]."</b>\n<br />\n<img src=\"".THEME."images/bar.jpg\" height=\"12\" width=\"".($percentage[$count])."%\" style=\"border : 1px solid Black\" alt=\"\" />\n<br />";
					$text .= "<span class=\"smalltext\">".$percentage[$count]."% ";
					if($votes[$count] == 0){
						$vt = "No votes";
					}else if($votes[$count] == 1){
						$vt = "1 vote";
					}else{
						$vt = $votes[$count]." votes";
					}
					$text .= "[".$vt."]</span><br /><br />";	
				}
			break;

			case "notvoted":
				$text .= "<form method='post' action='".e_SELF;
				if(e_QUERY){ $text .= "?".e_QUERY; }
				$text .= "'><p>";
				for($count=0; $count<=($options-1); $count++){
					
					
					$text .= "\n<input type=\"radio\" name=\"votea\" value=\"".($count+1)."\" />\n<b>".$poll_option[$count]."</b>\n<br />";
				}

				if($type == "forum"){
					$text .= "\n<br /></p><div style=\"text-align:center\">\n<p><input class=\"button\" type=\"submit\" name=\"pollvote\" value=\"".LAN_163."\" /></p></div>\n<p><input type='hidden' name='pollid' value='".$poll_id."' /></p></form>";
				}else{
					$text .= "\n<br /></p><div style=\"text-align:center\">\n<p><input class=\"button\" type=\"submit\" name=\"vote\" value=\"".LAN_163."\" /></p></div>\n<p><input type='hidden' name='pollid' value='".$poll_id."' /></p></form>";
				}
			break;

			case "disallowed":
				for($count=1; $count<=$options; $count++){
					$text .= "<img src='".THEME."images/bullet3.gif' alt='bullet' /> <b>".$poll_option[$count]."</b><br />\n";
				}
				$text .= "\n<br /><div style=\"text-align:center\">".LAN_387."</div><br />";
			break;
		}

		$vote_total = ($vote_total ? $vote_total : 0);
	
		$sql = new db;
		$comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$poll_id' AND comment_type='4'");

		$text .= "<div style=\"text-align:center\" class=\"smalltext\">
		".LAN_164.$vote_total;

		if($type == "menu"){
			$text .= " <a href=\"comment.php?poll.".$poll_id."\">Comments: ".$comment_total."</a><br />\n[ <a href=\"oldpolls.php\">".LAN_165."</a> ]";
		}
		$text .= "</div>";

		if(MODERATOR && $type == "forum"){
			$qs = explode(".", e_QUERY);
			$forum_id = $qs[0];
			$thread_id = $qs[1];
			$text .= "<br /><div style='text-align:right' class='smallblacktext'>[ moderator - <a href='".e_ADMIN."forum_conf.php?delete_poll.".$forum_id.".".$thread_id.".".$poll_id."'>delete poll only</a> ]</div>";
		}

		$ns = new table;

		$caption = (file_exists(THEME."images/poll_menu.png") ? "<img src='".THEME."images/poll_menu.png' alt='' /> ".LAN_184 : LAN_184);
		$ns -> tablerender($caption, $text);

		if($preview){
			echo "</td></tr></table></div>";
		}

	}
}
?>