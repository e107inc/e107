<?php
// search module for Forum.

if($results = $sql -> db_Select("forum_t", "*", "thread_name REGEXP('".$query."') OR thread_thread REGEXP('".$query."')")){
	$c = 0;
	$sql2 = new db;
	while(list($thread_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active, $thread_lastpost, $thread_s) = $sql -> db_Fetch()){

		$sql2 -> db_Select("forum", "*", "forum_id='$thread_forum_id' ");
		$row = $sql2 -> db_Fetch();
		@extract($row);
		if(check_class($forum_class)){
	
			if($thread_parent){
				$sql3 = new db;
				$sql3 -> db_Select("forum_t", "thread_name", "thread_id='$thread_parent'");
				list($forum_t['thread_name']) = $sql3 -> db_Fetch();
				$thread_name = parsesearch($forum_t['thread_name'], $query);
			}else{
				$thread_name = parsesearch($thread_name, $query);
			}

			$thread_thread = parsesearch($thread_thread, $query);
		
			if($thread_parent != 0){
				$tmp = $thread_parent;
			}else{
				$tmp = $thread_id;
			}
			$action = "forum_viewtopic.php?".$thread_forum_id.".".$tmp."";
			$text .= "<form method='post' action='$action' name='forum_".$c."'>
				<input type='hidden' name='highlight_search' value='1'><input type='hidden' name='search_query' value='$query'><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='javascript:this.forum_".$c.".submit()'>$thread_name</a></b></form><br />$thread_thread<br /><br />";
				$c ++;
		}else{
			$results = $results -1;
		}
	}
}else{
	$text .= LAN_198;
}
?>