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
$ccaption = UP_LAN_1.$user_name;
	$sql -> db_Select("user", "user_comments", "user_id=".$id."");
	list($user_comments) = $sql -> db_Fetch();
	$ctotal = $user_comments;
	$sql2 = new db;
	if($sql -> db_Select("comments", "*", "comment_author = '".$user_id."' ORDER BY comment_datestamp DESC LIMIT ".$from.", 10 ")){	
		$ctext = "<span class='smalltext'>";
		while($row = $sql-> db_Fetch()){
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
			if(strlen($comment_comment) > $menu_pref['comment_characters']){
				$comment_comment = substr($comment_comment, 0, $menu_pref['comment_characters']).$menu_pref['comment_postfix'];
			}
			
			if($comment_type == "0"){
				$sql2 -> db_Select("news", "news_title, news_class", "news_id = $comment_item_id");
				$row = $sql2 -> db_Fetch(); extract($row);
				if(!$news_class){
					$ctext .= "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."comment.php?comment.news.$comment_item_id'> [ ".UP_LAN_10.":<strong> $news_title </strong>]".UP_LAN_9."".$datestamp."</a>";
					$ctext .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;$comment_comment<br /><br />";
				}
			}
			if($comment_type == "1"){
				$sql2 -> db_Select("content", "*", "content_id=$comment_item_id");
				$row = $sql2 -> db_Fetch(); extract($row);
				if($content_type == 0){
					$tmp = "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."content.php?article.$comment_item_id'> [ ".UP_LAN_10.": <strong>$content_heading </strong>]".UP_LAN_9."".$datestamp."</a>";
				}else if($content_type == 3){
					$tmp = "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."content.php?review.$comment_item_id'> [ ".UP_LAN_10.": <strong>$content_heading </strong>]".UP_LAN_9."".$datestamp."</a>";
				}else if($content_type == 1){
					$tmp = "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."content.php?content.$comment_item_id'> [ ".UP_LAN_10.": <strong>$content_heading </strong>]".UP_LAN_9."".$datestamp."</a>";
				}

				$tmp .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;".($comment_blocked ? CM_L2 : $comment_comment)."<br /><br />";
	
				if(check_class($content_class)){
					$ctext .=$tmp;
				}

			}
			if($comment_type == "2"){
				$ctext .= "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."download.php?view.".$comment_item_id."'>download - <strong>".$poster."</strong>".UP_LAN_9."".$datestamp."</a><br />&nbsp;&nbsp;&nbsp;&nbsp;".$comment_comment."<br /><br />";
			}
			if($comment_type == "4"){
				$sql2 -> db_Select("poll", "*", "poll_id=$comment_item_id");
				$row = $sql2 -> db_Fetch(); extract($row);
				$ctext .= "<img src='".THEME."images/bullet2.gif' alt='' /><a href='".e_BASE."comment.php?comment.poll.".$comment_item_id."'> Poll - <strong>".$poll_title."</strong>".UP_LAN_9."".$datestamp." </a><br />&nbsp;&nbsp;&nbsp;&nbsp;".$comment_comment."<br /><br />";
			}
		}
	}else{
		$ctext = "<span class='mediumtext'>".UP_LAN_7."</span>";
	}

	
$ns -> tablerender($ccaption, $ctext);
require_once(e_HANDLER."np_class.php");
$ix = new nextprev("userposts.php", $from, 10, $ctotal, "Comments", "comments.".$id.""); 
}

if($action == "forums" || isset($_POST['fsearch'])){
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
	$ftext = "<div style='text-align:center'>\n<form method='post' action='".e_SELF."?".e_QUERY."'><table style='width:95%' class='fborder'>\n";
	if(!$sql -> db_Select_gen("".$db_query."")){
		$ftext .= "<span class='mediumtext'>".UP_LAN_8."</span>";
	}else{

	if(!is_object($gen)){
		$gen = new convert;
	}
	while($row = $sql-> db_Fetch()){
			extract($row);
		if(check_class($forum_class)){
				$poster = substr($thread_user, (strpos($thread_user, ".")+1));
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
					$topic = "Re: $thread_name";
				}else{
					$tmp = $thread_id;
					$topic = "Thread: $thread_name";
				}
				$thread_thread = $aj -> tpa($thread_thread);				
				$ftext .= "<tr>
				<td style='width:5%; text-align:center' class='forumheader'><img src='".e_IMAGE."forum/new_small.png' alt='' /></td>
				<td style='width:95%' class='forumheader'><div style='width:50%;float:left;'>
				<a href='".e_BASE."forum_viewtopic.php?$thread_forum_id.$tmp'>".$topic."</a>&nbsp;<span class='smalltext'>(<a href='".e_BASE."forum_viewforum.php?$forum_id'>$forum_name</a>)</span></div>
				<div style='width:50%;float:right;' ><span class='smalltext'>".UP_LAN_11.$datestamp."</span></div></td>
					</tr>
					<tr>
				<td style='width:5%; text-align:center' class='forumheader3'>&nbsp;</td>
				<td colspan='2' class='forumheader3'>".$thread_thread."<br /></td>
					 </tr>";
			}
		}
	}
	$ftext .= "<tr>
	<td colspan='3' class='forumheader' style='text-align:right'><input class='tbox' type='text' name='f_query' size='20' value='' maxlength='50' />
	<input class='button' type='submit' name='fsearch' value='".UP_LAN_12."' />
		</td></tr></table></div>";
$ns -> tablerender($fcaption , $ftext);
	}
require_once(e_HANDLER."np_class.php");
$ix = new nextprev("userposts.php", $from, 10, $ftotal, "Forum Posts", "forums.".$id.""); 

	
require_once(FOOTERF);


?>