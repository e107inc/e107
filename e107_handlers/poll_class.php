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
@include(e_PLUGIN."poll_menu/languages/".e_LANGUAGE.".php");
@include(e_PLUGIN."poll_menu/languages/English.php");
class poll{	

	function delete_poll($existing){
		$cls = new db;
		if($cls -> db_Delete("poll", " poll_id='".$existing."' ")){
			return  "Poll deleted.";
		}
	}

	function submit_poll($poll_id, $poll_name, $poll_option, $activate, $id=0, $ref="menu"){
		$aj = new textparse;
		$poll_name = $aj -> formtpa($poll_name, "admin");
		foreach($poll_option as $key => $value){
			$poll_option[$key] = $aj -> formtpa($poll_option[$key], "admin");
		}

		$datestamp = time();
		$cls = new db;
		if($activate && $ref == "menu"){
			$cls -> db_Update("poll", "poll_active='0', poll_end_datestamp='$datestamp' WHERE poll_active='1' OR poll_active='2' ");
		}
		if($poll_id){
			$cls -> db_Update("poll", "poll_title='$poll_name', poll_option_1='".$poll_option[0]."', poll_option_2='".$poll_option[1]."', poll_option_3='".$poll_option[2]."', poll_option_4='".$poll_option[3]."', poll_option_5='".$poll_option[4]."', poll_option_6='".$poll_option[5]."', poll_option_7='".$poll_option[6]."', poll_option_8='".$poll_option[7]."', poll_option_9='".$poll_option[8]."', poll_option_10='".$poll_option[9]."', poll_active='$activate' WHERE poll_id='$poll_id' ");
			$message = POLL_11;
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

		global $POLLSTYLE, $sql;
		if(!$POLLSTYLE){
			$POLLSTYLE = "<div style='text-align:center'>\n<br />\n<b><i>{QUESTION}</i></b>\n<hr />\n</div>\n<br />\n{OPTIONS=<b>OPTION</b><br />BAR<br /><span class='smalltext'>PERCENTAGE VOTES</span><br /><br />}\n<br />\n<div style='text-align:center' class='smalltext'>{VOTE_TOTAL} {COMMENTS}\n<br />\n{OLDPOLLS}\n</div>";
		}
		$vote_total = array_sum($votes);
		foreach($poll_option as $key => $value){
			if(empty($value)){
				unset($poll_option[$key]);
			}
		}
		$options = count($poll_option);
		for($count=0; $count<=$options; $count++){
			$poll_option[$count] = (IsSet($poll_option[$count]) ? stripslashes($poll_option[$count]) : "");
			if($vote_total){
				$percentage[$count] = round(($votes[$count]/$vote_total)*100,2);
			}
		}

		if($mode == "preview"){
			$text = "<div style='text-align:center'>\n<table style='width:350px'>\n<tr>\n<td>";
			$mode = "notvoted";
			$preview = TRUE;
		}else if($type == "forum"){
			$text = "<div style='text-align:center'>\n<table style='width:350px' class='fborder'>\n<tr>\n<td class='forumheader3'>";
			$preview = TRUE;
		}

		$sql -> db_Select("poll", "poll_admin_id", "poll_id='$poll_id' ");
		$row = $sql -> db_Fetch(); extract($row);
		$sql -> db_Select("user", "user_name", "user_id=".$poll_admin_id);
		$row = $sql -> db_Fetch(); extract($row);


		$sql = new db;
		$comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$poll_id' AND comment_type='4'");
		
		$search[0] = "/\{QUESTION\}(.*?)/si";
		$replace[0] = stripslashes($poll_question);

		$search[1] = "/\{VOTE_TOTAL\}(.*?)/si";
		$replace[1] = POLL_164.$vote_total;

		$search[2] = "/\{COMMENTS\}(.*?)/si";
		$replace[2] = ($type == "menu" ? " <a href=\"".e_BASE."comment.php?comment.poll.".$poll_id."\">".POLL_500.": ".$comment_total."</a>" : "");

		$search[3] = "/\{OLDPOLLS\}(.*?)/si";
		$replace[3] = ($type == "menu" ? "<a href=\"".e_BASE."oldpolls.php\">".POLL_165."</a>" : "");

		$search[4] = "/\{AUTHOR\}(.*?)/si";
		$replace[4] = POLL_12."<a href='".e_BASE."user.php?id.$poll_admin_id'>$user_name</a>";

		$p_style = preg_replace($search, $replace, $POLLSTYLE);

		preg_match("/\{OPTIONS=(.*?)\}/si", $POLLSTYLE, $res);
		$optionstring = $res[1];

		if($mode == "notvoted"){
			$opt = "<form method='post' action='".e_SELF;
			if(e_QUERY){ $opt .= "?".e_QUERY; }
			$opt .= "'><p>";
		}

		for($count=0; $count<=($options-1); $count++){	

			if($votes[$count] == 0){
				$vt = POLL_6;
			}else if($votes[$count] == 1){
				$vt = POLL_7;
			}else{
				$vt = $votes[$count].POLL_8;
			}

			if($mode == "voted"){
				$search = array("OPTION", "BAR", "PERCENTAGE", "VOTES");
				$replace = array($poll_option[$count], "<img src='".THEME."images/bar.jpg' height='12' width='".($percentage[$count])."%' style='border : 1px solid Black' alt='' />",  $percentage[$count]."% ", "[".$vt."]");
			}else if($mode == "notvoted"){
				$search = array("OPTION", "BAR", "PERCENTAGE", "VOTES", "<br /><br />");
				$replace = array("<input type='radio' name='votea' value='".($count+1)."' /> ".$poll_option[$count], "", "", "", "<br />");
			}else if($mode == "disallowed"){
				$search = array("OPTION", "BAR", "PERCENTAGE", "VOTES");
				$replace = array("<img src='".THEME."images/bullet2.gif' alt='bullet' /> <b>".$poll_option[$count], "", "", "");
			}

			$opt .= str_replace($search, $replace, $optionstring);
		}

		if($mode == "notvoted"){
			$opt .= ($type == "forum" ? "\n<br /></p><div style='text-align:center'>\n<p><input class='button' type='submit' name='pollvote' value='".POLL_4."' /></p></div>\n<p><input type='hidden' name='pollid' value='".$poll_id."' /></p></form>" : "\n<br /></p><div style='text-align:center'>\n<p><input class='button' type='submit' name='vote' value='".POLL_163."' /></p></div>\n<p><input type='hidden' name='pollid' value='".$poll_id."' /></p></form>");
		}

		$text .= preg_replace("/\{OPTIONS=.*\}/si", $opt, $p_style);


		if(MODERATOR && $type == "forum"){
			$qs = explode(".", e_QUERY);
			$forum_id = $qs[0];
			$thread_id = $qs[1];
			$text .= "<br /><div style='text-align:right' class='smallblacktext'>[ ".POLL_9." - <a href='".e_ADMIN."forum_conf.php?delete_poll.".$forum_id.".".$thread_id.".".$poll_id."'>".POLL_10."</a> ]</div>";
		}

		$ns = new e107table;

		$caption = (file_exists(THEME."images/poll_menu.png") ? "<img src='".THEME."images/poll_menu.png' alt='' /> ".POLL_184 : POLL_184);
		if(!$preview && $type != "forum"){
			$ns -> tablerender($caption, $text);
		}else{
			return $text."</td></tr></table></div>";
		}
	}
}
?>