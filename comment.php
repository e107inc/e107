<?php
/*
+---------------------------------------------------------------+
|	e107 website system|
|	/class.php|
||
|	©Steve Dunstan 2001-2002|
|	http://jalist.com|
|	stevedunstan@jalist.com|
||
|	Released under the terms and conditions of the|
|	GNU General Public License (http://gnu.org).|
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."news_class.php");
require_once(e_HANDLER."comment_class.php");

if(!e_QUERY){
	header("location:".e_BASE."index.php");
	exit;
}

$qs = explode(".", e_QUERY);
$table = $qs[0];
$id = $qs[1];
if(!$id){
	$id = $table;
	$table = "news";
}
$cobj = new comment;
if(IsSet($_POST['commentsubmit'])){
	if(!$sql -> db_Select("news", "news_allow_comments", "news_id='$id' ")){
		header("location:".e_BASE."index.php");
		exit;
	}else{
		$row = $sql -> db_Fetch();
		if(!$row[0] && (ANON===TRUE || USER===TRUE)){
			$cobj -> enter_comment($_POST['author_name'], $_POST['comment'], $table, $id);
			$sql -> db_Delete("cache", "cache_url='comment.php?$table.$id'");
		}
	}
}

if($sql -> db_Select("cache", "*", "cache_url='comment.php?$table.$id' ")){
	$row = $sql -> db_Fetch(); extract($row);
	require_once(HEADERF);
	echo stripslashes($cache_data);
}else{
	if($table == "news"){
		if(!$sql -> db_Select("news", "*", "news_id='$id' ")){
			header("location:".e_BASE."index.php");
			exit;
		}else{

			list($news['news_id'], $news['news_title'], $news['data'], $news['news_extended'], $news['news_datestamp'], $news['admin_id'], $news_category, $news['news_allow_comments'],  $news['news_start'], $news['news_end'], $news['news_class']) = $sql -> db_Fetch();

			if($news['news_allow_comments']){
				header("location:".e_BASE."index.php");
				exit;
			}

			if(!check_class($news['news_class'])){
				header("location:".e_BASE."index.php");
				exit;
			}else{
				require_once(HEADERF);
				ob_start();
				$sql -> db_Select("user", "user_name", "user_id='".$news['admin_id']."' ");
				list($news['admin_name']) = $sql -> db_Fetch();
				$sql -> db_Select("news_category", "*",  "category_id='$news_category' ");
				list($news['category_id'], $news['category_name'], $news['category_icon']) = $sql-> db_Fetch();
				$news['comment_total'] = $sql -> db_Count("comments", "(*)",  "WHERE comment_item_id='".$news['news_id']."' AND comment_type='0' ");
				$ix = new news;
				$ix -> render_newsitem($news);
				$field = $news['news_id'];
				$comtype = 0;			
			}
		}
	}else if($table == "poll"){
		if(!$sql -> db_Select("poll", "*", "poll_id='$id' ")){
			header("location:".e_BASE."index.php");
			exit;
		}else{
			$row = $sql -> db_Fetch();
			extract($row);
			require_once(HEADERF);
//			ob_start();
			require_once(e_PLUGIN."poll_menu/poll_menu.php");
			$field = $poll_id;
			$comtype = 4;
		}
	}

	$comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$field' AND comment_type='$comtype' ORDER BY comment_datestamp");
	if($comment_total){
		while($row = $sql -> db_Fetch()){
			$text .= $cobj -> render_comment($row);
		}
		if(!defined("emessage")){
			$ns -> tablerender(LAN_5, $text);
		}else{
			$ns -> tablerender(LAN_5, "<div style='text-align:center'><b>".emessage."</b></div><br /><br />".$text);
		}
	}

	
	if($pref['cachestatus'] && !strstr(e_QUERY, "poll")){
		$cache = mysql_escape_string(ob_get_contents());
		$sql -> db_Insert("cache", "'comment.php?$table.$field', '".time()."', '$cache' ");
	}
}


if(ADMIN && getperms("B")){
	if(!strstr(e_QUERY, ".")){ $ct = "news."; }
	echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?$ct".e_QUERY."'>".LAN_314."</a></div><br />";
}

$cobj -> form_comment();

require_once(FOOTERF);
?>