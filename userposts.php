<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/userposts.php
|
|	©chavo 2004
|	http://e107.org
|	chavo@2sdw.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

if(!USER){ header("location:".e_BASE."index.php"); exit; }
if(!is_object($aj)){ $aj = new textparse; }

$_POST['f_query'] = trim(chop($_POST['f_query']));
if(e_QUERY){
	$tmp = explode(".", e_QUERY);
	$from = $tmp[0];
	$action = $tmp[1];
	$id = $tmp[2];
}else{ header("location:".e_BASE."index.php"); exit; }



$sql -> db_Select("user", "user_name",  "user_id=".$id."");
$row = $sql -> db_Fetch(); extract($row);
$user_id = $id.".".$user_name."";

if($action == "comments"){

	if(!$USERPOSTS_COMMENTS_TABLE){
		if(file_exists(THEME."userposts_template.php")){
      require_once(THEME."userposts_template.php");
    }
  	else{
      require_once(e_BASE.$THEMES_DIRECTORY."templates/userposts_template.php");
    }
	}
	
	$ccaption = UP_LAN_1.$user_name;
	$sql -> db_Select("user", "user_comments", "user_id=".$id."");
	list($user_comments) = $sql -> db_Fetch();
	$ctotal = $user_comments;
	$sql2 = new db;
	if(!$sql -> db_Select("comments", "*", "comment_author = '".$user_id."' ORDER BY comment_datestamp DESC LIMIT ".$from.", 10 ")){	
		$ctext = "<span class='mediumtext'>".UP_LAN_7."</span>";
	}else{
		while($row = $sql -> db_Fetch()){
		extract($row);
			$userposts_comments_table_string .= parse_userposts_comments_table($row);
		}
	}
	$userposts_comments_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE_START);
	$userposts_comments_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE_END);

	$ctext .= $userposts_comments_table_start."".$userposts_comments_table_string."".$userposts_comments_table_end;
	
	$ns -> tablerender($ccaption, $ctext);

	require_once(e_HANDLER."np_class.php");
	$ix = new nextprev("userposts.php", $from, 10, $ctotal, "Comments", "comments.".$id.""); 
}



if($action == "forums" || isset($_POST['fsearch'])){

	if(!$USERPOSTS_FORUM_TABLE){
		if(file_exists(THEME."userposts_template.php")){
      require_once(THEME."userposts_template.php");
    }
  	else{
      require_once(e_BASE.$THEMES_DIRECTORY."templates/userposts_template.php");
    }
	}

	if(isset($_POST['f_query']) && $_POST['f_query'] != ""){
		extract($_POST);
		$fcaption = UP_LAN_11.$user_name;
		$f_query = $_POST['f_query'];
		$db_query = "SELECT * FROM ".MPREFIX."forum_t, ".MPREFIX."forum WHERE ".MPREFIX."forum.forum_id=".MPREFIX."forum_t.thread_forum_id AND ".MPREFIX."forum_t.thread_user='".$user_id."' AND (".MPREFIX."forum_t.thread_name REGEXP('".$f_query."') OR ".MPREFIX."forum_t.thread_thread REGEXP('".$f_query."')) ORDER BY ".MPREFIX."forum_t.thread_datestamp DESC ";
	}else{
		if(!is_object($sql2)){
			$sql2 = new db;
		}
		$ftotal = 0;
		$sql2 -> db_Select_gen("SELECT * FROM ".MPREFIX."forum_t, ".MPREFIX."forum WHERE ".MPREFIX."forum.forum_id=".MPREFIX."forum_t.thread_forum_id AND ".MPREFIX."forum_t.thread_user='".$user_id."'" );
		while($row = $sql2 -> db_Fetch()){
			extract($row);
			if(check_class($forum_class)){
				$ftotal ++;
				$limit_ids[] = $thread_id;
			}
		}
		$limit_ids = implode(",", $limit_ids);
		$fcaption = UP_LAN_0.$user_name;
		$db_query = "SELECT * FROM ".MPREFIX."forum_t, ".MPREFIX."forum WHERE ".MPREFIX."forum.forum_id=".MPREFIX."forum_t.thread_forum_id AND ".MPREFIX."forum_t.thread_user='".$user_id."' AND ".MPREFIX."forum_t.thread_id IN ($limit_ids) ORDER BY ".MPREFIX."forum_t.thread_datestamp DESC LIMIT ".$from.", 10";
	}

	$sql = new db;
	if(!$sql -> db_Select_gen("".$db_query."")){
		$ftext .= "<span class='mediumtext'>".UP_LAN_8."</span>";
	}else{

		if(!is_object($gen)){
			$gen = new convert;
		}
		while($row = $sql-> db_Fetch()){
		extract($row);
			if(check_class($forum_class)){
				$userposts_forum_table_string .= parse_userposts_forum_table($row);
			}
		}
		$userposts_forum_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_START);

		$USERPOSTS_FORUM_SEARCH = "<input class='tbox' type='text' name='f_query' size='20' value='' maxlength='50' /> <input class='button' type='submit' name='fsearch' value='".UP_LAN_12."' />";

		$userposts_forum_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_END);

		$ftext .= $userposts_forum_table_start."".$userposts_forum_table_string."".$userposts_forum_table_end;
	}
	$ns -> tablerender($fcaption, $ftext);

	require_once(e_HANDLER."np_class.php");
	$ix = new nextprev("userposts.php", $from, 10, $ftotal, "Forum Posts", "forums.".$id.""); 
}
	
require_once(FOOTERF);




function parse_userposts_comments_table($row){
			global $USERPOSTS_COMMENTS_TABLE, $pref, $gen, $aj, $menu_pref;
			extract($row);

			$poster = substr($comment_author, (strpos($comment_author, ".")+1));
			$gen = new convert;
			$datestamp = $gen->convert_date($comment_datestamp, "short");

			$comment_comment = $aj -> tpa($comment_comment);

			if($pref['cb_linkreplace']){
				$comment_comment .= " ";
				$comment_comment = preg_replace("#\>(.*?)\</a\>[\s]#si", ">".$pref['cb_linkc']."</a> ", $comment_comment);
				$comment_comment = $aj -> tpa(strip_tags($comment_comment));
			}
	
			if(!eregi("<a href|<img|&#", $thread_thread)){
				$message_array = explode(" ", $comment_comment);
				for($i=0; $i<=(count($message_array)-1); $i++){
					if(strlen($message_array[$i]) > 20){
						$message_array[$i] = preg_replace("/([^\s]{20})/", "$1<br />", $message_array[$i]);
					}
				}
				$comment_comment = implode(" ", $message_array);
			}
			/*
			if(strlen($comment_comment) > $menu_pref['comment_characters']){
				$comment_comment = substr($comment_comment, 0, $menu_pref['comment_characters']).$menu_pref['comment_postfix'];
			}
			*/
			$sql2 = new db;
			if($comment_type == "0"){
				$sql2 -> db_Select("news", "news_title, news_class", "news_id = '".$comment_item_id."' ");
				$row = $sql2 -> db_Fetch(); extract($row);
				if(!$news_class){

					$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/bullet2.gif' alt='' />";
					$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
					$USERPOSTS_COMMENTS_HEADING = $news_title;
					$USERPOSTS_COMMENTS_COMMENT = $comment_comment;
					$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."comment.php?comment.news.$comment_item_id'>";
					$USERPOSTS_COMMENTS_TYPE = "news";
				}
			}
			if($comment_type == "1"){
				$sql2 -> db_Select("content", "*", "content_id=$comment_item_id");
				$row = $sql2 -> db_Fetch(); extract($row);

				if(check_class($content_class)){
					if($content_type == 0){
						$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/bullet2.gif' alt='' />";
						$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
						$USERPOSTS_COMMENTS_HEADING = $content_heading;
						$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."content.php?article.$comment_item_id'>";
						$USERPOSTS_COMMENTS_TYPE = "article";
					}else if($content_type == 3){
						$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/bullet2.gif' alt='' />";
						$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
						$USERPOSTS_COMMENTS_HEADING = $content_heading;
						$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."content.php?review.$comment_item_id'>";
						$USERPOSTS_COMMENTS_TYPE = "review";
					}else if($content_type == 1){
						$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/bullet2.gif' alt='' />";
						$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
						$USERPOSTS_COMMENTS_HEADING = $content_heading;
						$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."content.php?content.$comment_item_id'>";
						$USERPOSTS_COMMENTS_TYPE = "content";
					}
					$USERPOSTS_COMMENTS_COMMENT = ($comment_blocked ? CM_L2 : $comment_comment);
				}
			}
			if($comment_type == "2"){
				$sql2 -> db_Select("download", "*", "download_id=$comment_item_id");
				$row = $sql2 -> db_Fetch(); extract($row);

					$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/bullet2.gif' alt='' />";
					$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
					$USERPOSTS_COMMENTS_HEADING = $download_name;
					$USERPOSTS_COMMENTS_COMMENT = $comment_comment;
					$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."download.php?view.".$comment_item_id."'>";
					$USERPOSTS_COMMENTS_TYPE = "download";
			}
			if($comment_type == "4"){
				$sql2 -> db_Select("poll", "*", "poll_id=$comment_item_id");
				$row = $sql2 -> db_Fetch(); extract($row);

					$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/bullet2.gif' alt='' />";
					$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
					$USERPOSTS_COMMENTS_HEADING = $poll_title;
					$USERPOSTS_COMMENTS_COMMENT = $comment_comment;
					$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."comment.php?comment.poll.".$comment_item_id."'>";
					$USERPOSTS_COMMENTS_TYPE = "poll";
			}

			// added a check for not numeric comment_types (=custom comments for your own plugins)
			if(!is_numeric($comment_type)){
				unset($handle, $heading, $href);
				$handle=opendir(e_PLUGIN);
				while(false !== ($file = readdir($handle))){
					if($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)){
						$plugin_handle=opendir(e_PLUGIN.$file."/");
						while(false !== ($file2 = readdir($plugin_handle))){
							if($file2 == "e_comment.php"){
								require_once(e_PLUGIN.$file."/".$file2);
								if($comment_type == $e_plug_table){
									$plugin_redir = $reply_location;
									list($url, $querystring) = explode("?", $plugin_redir);
									$USERPOSTS_COMMENTS_HEADING = $comment_subject;
									$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".$url."?".$comment_item_id."'>";
								}else{
									$USERPOSTS_COMMENTS_HEADING = $comment_subject;
									$USERPOSTS_COMMENTS_HREF_PRE = "";
								}
							}
						}
					}
				}
				
				$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/bullet2.gif' alt='' />";
				$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
				$USERPOSTS_COMMENTS_COMMENT = $comment_comment;
				$USERPOSTS_COMMENTS_TYPE = $comment_type;
			}
			// end

			return(preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE));
}


function parse_userposts_forum_table($row){
			global $USERPOSTS_FORUM_TABLE, $gen, $aj;
			extract($row);

			$gen = new convert;
			$sql2 = new db;

			$poster = substr($thread_user, (strpos($thread_user, ".")+1));
			$datestamp = $gen->convert_date($thread_datestamp, "short");			

			if($thread_parent){
				
				if($cachevar[$thread_parent]){
					$thread_name = $cachevar[$thread_parent];
				}else{
					$tmp = $thread_parent;
					$sql2 -> db_Select("forum_t", "thread_name", "thread_id = '".$thread_parent."' ");
					list($thread_name) = $sql2 -> db_Fetch();
					$cachevar[$thread_parent] = $thread_name;
				}
				$USERPOSTS_FORUM_TOPIC_PRE = "Re: ";
			}else{
				$tmp = $thread_id;
				$USERPOSTS_FORUM_TOPIC_PRE = "Thread: ";
			}

			$thread_thread = $aj -> tpa($thread_thread);

			$USERPOSTS_FORUM_ICON = "<img src='".e_IMAGE."forum/new_small.png' alt='' />";
			$USERPOSTS_FORUM_TOPIC_HREF_PRE = "<a href='".e_BASE."forum_viewtopic.php?".$thread_forum_id.".".$tmp."'>";
			$USERPOSTS_FORUM_TOPIC = $thread_name;
			$USERPOSTS_FORUM_NAME_HREF_PRE = "<a href='".e_BASE."forum_viewforum.php?".$forum_id."'>";
			$USERPOSTS_FORUM_NAME = $forum_name;
			$USERPOSTS_FORUM_THREAD = $thread_thread;
			$USERPOSTS_FORUM_DATESTAMP = UP_LAN_11." ".$datestamp;

			return(preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE));
}


?>