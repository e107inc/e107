<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/plugins/poll.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

require_once(e_HANDLER."poll_class.php");

if($sql -> db_Select("poll", "*", "poll_active='1' OR poll_active='2' ")){
	list($poll_id, $poll_datestamp, $poll_end_datestamp, $poll_admin_id, $poll_title, $poll_option[0], $poll_option[1], $poll_option[2], $poll_option[3], $poll_option[4], $poll_option[5], $poll_option[6], $poll_option[7], $poll_option[8], $poll_option[9], $votes[0], $votes[1], $votes[2], $votes[3], $votes[4], $votes[5], $votes[6], $votes[7], $votes[8], $votes[9], $poll_ip, $poll_active) = $sql-> db_Fetch();

	$user_id = ($poll_active == 1 ? getip() : USERID);

	if(preg_match("/".$user_id."\^/", $poll_ip)){
		$mode = "voted";
	}else if($poll_active == 2 && !USER){
		$mode = "disallowed";
	}else{
		$mode = "notvoted";
	}

	If(IsSet($_POST['vote'])){
		if(!strpos($poll_ip, $user_id)){
			if($_POST['votea']){
				$num = "poll_votes_".$_POST['votea'];
				$sql -> db_Update("poll", "$num=$num+1, poll_ip='".$poll_ip.$user_id."^' WHERE poll_id='".$_POST['pollid']."' ");
				$mode = "voted";
				$sql -> db_Select("poll", "*", "poll_active='1' OR poll_active='2' ");
				list($poll_id, $poll_datestamp, $poll_end_datestamp, $poll_admin_id, $poll_title, $poll_option[0], $poll_option[1], $poll_option[2], $poll_option[3], $poll_option[4], $poll_option[5], $poll_option[6], $poll_option[7], $poll_option[8], $poll_option[9], $votes[0], $votes[1], $votes[2], $votes[3], $votes[4], $votes[5], $votes[6], $votes[7], $votes[8], $votes[9], $poll_ip, $poll_active) = $sql-> db_Fetch();
			}
		}
	}

	$poll = new poll;
	$poll -> render_poll($poll_id, $poll_title, $poll_option, $votes, $mode);
}
?>