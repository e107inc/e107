<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/menus/newforumposts_menu.php
|
|	©Edwin van der Wal 2003
|	http://e107.org
|	evdwal@xs4all.nl
|	Based on the newforumposts on www.e107.org
|
|	Released under the terms and conditions of the	
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
$gen = new convert;
$text = "<span class=\"smalltext\">";
if(!$sql -> db_Select("forum_t", "*", " ORDER BY thread_datestamp DESC LIMIT 0, ".$menu_pref['newforumposts_display'], $mode = "no_where")){
	$text = "<span class=\"mediumtext\">No posts yet";
}else{
	while(list($thread_id_, $thread_name_, $thread_thread_, $thread_forum_id_, $thread_datestamp_, $thread_parent_, $thread_user_) = $sql-> db_Fetch()){
		$poster = eregi_replace("[0-9]+.", "", $thread_user_);
		$datestamp = $gen->convert_date($thread_datestamp_, "short");
		if($thread_parent_ != 0){
			$tmp = $thread_parent_;
			$sqltmp = new db;
			$sqltmp -> db_Select("forum_t","thread_name","thread_id = $thread_parent_");
			list($thread_name_) = $sqltmp -> db_Fetch();
			$topic = "[replies to: <i>$thread_name_</i>]";
		}else{
			$tmp = $thread_id_;
			$topic = "[new thread: <i>".$thread_name_."</i>]";
		}

		$message_array = explode(" ", $thread_thread_);
		for($i=0; $i<=(count($message_array)-1); $i++){
			if(strlen($message_array[$i]) > 30){
				$message_array[$i] = preg_replace("/([^\s]{30})/", "$1<br />", $message_array[$i]);
			}
		}
		$thread_thread_ = implode(" ",$message_array);
		if(strlen($thread_thread_) > $menu_pref['newforumposts_characters']) {
			$thread_thread_ = substr($thread_thread_, 0, $menu_pref['newforumposts_characters']).$postfix;
		}
		
		$aj = new textparse;
		$thread_thread_ = $aj -> tpa($thread_thread_);

		$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href=\"forum_viewtopic.php?$thread_forum_id_.$tmp\"><b>".$poster."</b> on ".$datestamp."</a><br/>";
		if($menu_pref['newforumposts_title']) {
			$text .= $topic."<br />";
		}
		$text .= $thread_thread_."<br /><br /><br />";
	}
}
$text = "</span>".preg_replace("/\<br \/\>$/", "", $text);
$ns -> tablerender($menu_pref['newforumposts_caption'], $text);
?>
