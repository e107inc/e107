<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
if(!is_object($aj)){ $aj = new textparse; }
if($cache = retrieve_cache("newforumposts")){
	$text = $aj -> formtparev($cache); 
}else{
	$gen = new convert;
	$sql2 = new db;
	$text = "<span class='smalltext'>";
	if(!$results = $sql -> db_Select_gen("SELECT * FROM ".MPREFIX."forum_t, ".MPREFIX."forum WHERE ".MPREFIX."forum.forum_id=".MPREFIX."forum_t.thread_forum_id ORDER BY ".MPREFIX."forum_t.thread_datestamp DESC LIMIT 0, ".$menu_pref['newforumposts_display'])){
		$text = "<span class='mediumtext'>".NFP_2;
	}else{
		while($row = $sql-> db_Fetch()){
			extract($row);
			if(check_class($forum_class)){
				$poster = substr($thread_user, (strpos($thread_user, ".")+1));
				if(strstr($poster, chr(1))){
					$tmp = explode(chr(1), $poster);
					$poster = $tmp[0];
				}
				$datestamp = $gen->convert_date($thread_datestamp, "short");
			
				if($thread_parent){

					if($cachevar[$thread_parent]){
						$thread_name = $cachevar[$thread_parent];
					}else{
						$tmp = $thread_parent;
						$sql2 -> db_Select("forum_t", "thread_name", "thread_id = $thread_parent");
						list($thread_name) = $sql2 -> db_Fetch();
						$cachevar[$thread_parent] = $thread_name;
					}
					$topic = "[re: <i>$thread_name</i>]";
				}else{
					$tmp = $thread_id;
					$topic = "[thread: <i>$thread_name</i>]";
				}

				$thread_thread = $aj -> tpa($thread_thread);

				if($pref['cb_linkreplace']){
					$thread_thread .= " ";
					$thread_thread = preg_replace("#\>(.*?)\</a\>[\s]#si", ">".$pref['cb_linkc']."</a> ", $thread_thread);
					$thread_thread = $aj -> tpa(strip_tags($thread_thread));
				}

				if(!eregi("<a href|<img|&#", $thread_thread)){
					$message_array = explode(" ", $thread_thread);
					for($i=0; $i<=(count($message_array)-1); $i++){
						if(strlen($message_array[$i]) > 20){
							$message_array[$i] = preg_replace("/([^\s]{20})/", "$1<br />", $message_array[$i]);
						}
					}
					$thread_thread = implode(" ",$message_array);
				}
				if(strlen($thread_thread) > $menu_pref['newforumposts_characters']) {
					$thread_thread = substr($thread_thread, 0, $menu_pref['newforumposts_characters']).$menu_pref['newforumposts_postfix'];
				}

				$text .= "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."forum_viewtopic.php?$thread_forum_id.$tmp'><b>".$poster."</b> on ".$datestamp."</a><br/>";
				if($menu_pref['newforumposts_title']) {
					$text .= $topic."<br />";
				}
				$text .= $thread_thread."<br /><br />";
			}else{
				$results -- ;
			}
		}
	}
	$text = "</span>".preg_replace("/\<br \/\>$/", "", $text);
	if($pref['cachestatus']){
		$cache = $aj -> formtpa($text, "admin");
		set_cache("newforumposts",$cache);
	}
}
if($results){
	$ns -> tablerender($menu_pref['newforumposts_caption'], $text);
}
?>
