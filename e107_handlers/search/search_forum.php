<?php
// search module for Forum.

if($results = $sql -> db_Select("forum_t", "*", "thread_name REGEXP('".$query."') OR thread_thread REGEXP('".$query."')")){
	$sql2 = new db;
	while(list($thread_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active, $thread_lastpost, $thread_s) = $sql -> db_Fetch()){

		$sql2 -> db_Select("forum", "*", "forum_id='$thread_forum_id' ");
		$row = $sql2 -> db_Fetch();
		@extract($row);
		if(!$forum_class || check_class($forum_class)){
			$thread_name = parsesearch($thread_name, $query);
	
			if($thread_name == "......"){
				$thread_name = "No title";
			}

			$thread_thread = parsesearch($thread_thread, $query);
		
			if($thread_parent != 0){
				$tmp = $thread_parent;
			}else{
				$tmp = $thread_id;
			}

			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"forum_viewtopic.php?$thread_forum_id.$tmp\">$thread_name</a></b><br />$thread_thread<br /><br />";
		}
	}
}else{
	$text .= LAN_198;
}
?>