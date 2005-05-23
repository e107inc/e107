<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/userposts.php,v $
|     $Revision: 1.13 $
|     $Date: 2005-05-23 02:44:00 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);
	
if (!USER) {
	header("location:".e_BASE."index.php");
	exit;
}
	
$_POST['f_query'] = trim(chop($_POST['f_query']));

if (e_QUERY)
{
	list($from, $action, $id) = explode(".", e_QUERY);
	$id = intval($id);
	$from = intval($from);
}
else
{
	header("location:".e_BASE."index.php");
	exit;
}

if(!defined("BULLET"))
{
	 define("BULLET", "bullet2.gif");
}

if ($action == "comments") {
	if(is_numeric($id))
	{
		$sql->db_Select("user", "user_name", "user_id=".$id."");
		$row = $sql->db_Fetch();
		extract($row);
		$user_id = $id.".".$user_name."";
	}
	else
	{
		$user_name = UP_LAN_16.$id;
	}

	if (!$USERPOSTS_COMMENTS_TABLE) {
		if (file_exists(THEME."userposts_template.php")) {
			require_once(THEME."userposts_template.php");
		} else {
			require_once(e_BASE.$THEMES_DIRECTORY."templates/userposts_template.php");
		}
	}
	 
	
	$sql2 = new db;
	if(is_numeric($id))
	{
		$ccaption = UP_LAN_1.$user_name;
		$sql->db_Select("user", "user_comments", "user_id=".$id."");
		list($user_comments) = $sql->db_Fetch();
		$ctotal = $user_comments;
		$blah = $sql->db_Select("comments", "*", "comment_author = '".$user_id."' ORDER BY comment_datestamp DESC LIMIT ".$from.", 10 ");
	}
	else
	{
		require_once(e_HANDLER."encrypt_handler.php");
		$dip = decode_ip($id);
		$ccaption = UP_LAN_1.$dip;
		$blah = $sql->db_Select("comments", "*", "comment_ip = '".$id."' ORDER BY comment_datestamp DESC LIMIT ".$from.", 10 ");
	}
	
	if (!$blah) {
		$ctext = "<span class='mediumtext'>".UP_LAN_7."</span>";
	} else {
		while ($row = $sql->db_Fetch())
		{
			extract($row);
			$userposts_comments_table_string .= parse_userposts_comments_table($row);
		}
	}
	$userposts_comments_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE_START);
	$userposts_comments_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE_END);
	 
	$ctext .= $userposts_comments_table_start."".$userposts_comments_table_string."".$userposts_comments_table_end;
	 
	$ns->tablerender($ccaption, $ctext);
	 
	require_once(e_HANDLER."np_class.php");
	$ix = new nextprev("userposts.php", $from, 10, $ctotal, UP_LAN_13, "comments.".$id."");
}
	
	
	
if ($action == "forums" || isset($_POST['fsearch'])) {
	 
	if(is_numeric($id))
	{
		$user_id = intval($id);
		$sql->db_Select("user", "user_name", "user_id=".$id."");
		$row = $sql->db_Fetch();
		$fcaption = UP_LAN_0." ".$row['user_name'];
	}
	else
	{
		$user_name = 0;
	}

	if (!$USERPOSTS_FORUM_TABLE) {
		if (file_exists(THEME."userposts_template.php")) {
			require_once(THEME."userposts_template.php");
		} else {
			require_once(e_BASE.$THEMES_DIRECTORY."templates/userposts_template.php");
		}
	}

	$s_info = "";
	if (isset($_POST['f_query']) && $_POST['f_query'] != "")
	{
		$f_query = $_POST['f_query'];
		$s_info = "AND (ft.thread_name REGEXP('".$f_query."') OR ft.thread_thread REGEXP('".$f_query."'))";
		$fcaption = UP_LAN_12." ".$row['user_name'];
	}
	$qry = "
	SELECT f.*, ft.* FROM #forum_t AS ft
	LEFT JOIN #forum AS f ON ft.thread_forum_id = f.forum_id
	LEFT JOIN #forum AS fp ON f.forum_parent = fp.forum_id
	WHERE ft.thread_user LIKE '{$user_id}.%'
	AND f.forum_class IN (".USERCLASS_LIST.")
	AND fp.forum_class IN (".USERCLASS_LIST.") 
	{$s_info}
	ORDER BY ft.thread_datestamp DESC LIMIT {$from}, 10
	";	

	$total_qry = "
	SELECT COUNT(*) AS count FROM #forum_t AS ft
	LEFT JOIN #forum AS f ON ft.thread_forum_id = f.forum_id
	LEFT JOIN #forum AS fp ON f.forum_parent = fp.forum_id
	WHERE ft.thread_user LIKE '{$user_id}.%'
	AND f.forum_class IN (".USERCLASS_LIST.")
	AND fp.forum_class IN (".USERCLASS_LIST.") 
	{$s_info}
	";	

	$ftotal = 0;
	if($sql->db_Select_gen($total_qry))
	{
		$row = $sql->db_Fetch();
		$ftotal = $row['count'];
	}

	if (!$sql->db_Select_gen($qry))
	{
		$ftext .= "<span class='mediumtext'>".UP_LAN_8."</span>";
	}
	else
	{
		if (!is_object($gen))
		{
			$gen = new convert;
		}
		while ($row = $sql->db_Fetch())
		{
			$userposts_forum_table_string .= parse_userposts_forum_table($row);
		}
		$userposts_forum_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_START);
		$USERPOSTS_FORUM_SEARCH = "<input class='tbox' type='text' name='f_query' size='20' value='' maxlength='50' /> <input class='button' type='submit' name='fsearch' value='".UP_LAN_12."' />";
		$userposts_forum_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_END);
		$ftext .= $userposts_forum_table_start."".$userposts_forum_table_string."".$userposts_forum_table_end;
	}
	$ns->tablerender($fcaption, $ftext);
	 
	require_once(e_HANDLER."np_class.php");
	$ix = new nextprev("userposts.php", $from, 10, $ftotal, UP_LAN_14, "forums.".$id."");
}
	
require_once(FOOTERF);
	
	
	
	
function parse_userposts_comments_table($row) {
	global $USERPOSTS_COMMENTS_TABLE, $pref, $gen, $tp, $menu_pref;
	extract($row);
	 
	$poster = substr($comment_author, (strpos($comment_author, ".")+1));
	$gen = new convert;
	$datestamp = $gen->convert_date($comment_datestamp, "short");
	$DATESTAMP = $datestamp;
	 
	$comment_comment = $tp->toHTML($comment_comment);
	
	$sql2 = new db;
	if ($comment_type == "0") {
		$sql2->db_Select("news", "news_title, news_class", "news_id = '".$comment_item_id."' ");
		$row = $sql2->db_Fetch();
		 extract($row);
		if (!$news_class) {
			 
			$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/".BULLET."' alt='' />";
			$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
			$USERPOSTS_COMMENTS_HEADING = $news_title;
			$USERPOSTS_COMMENTS_COMMENT = $comment_comment;
			$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."comment.php?comment.news.$comment_item_id'>";
			$USERPOSTS_COMMENTS_TYPE = "news";
		}
	}
	if ($comment_type == "1") {
		$sql2->db_Select("content", "*", "content_id=$comment_item_id");
		$row = $sql2->db_Fetch();
		 extract($row);
		 
		if (check_class($content_class)) {
			if ($content_type == 0) {
				$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/".BULLET."' alt='' />";
				$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
				$USERPOSTS_COMMENTS_HEADING = $content_heading;
				$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."content.php?article.$comment_item_id'>";
				$USERPOSTS_COMMENTS_TYPE = "article";
			}
			else if($content_type == 3) {
				$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/".BULLET."' alt='' />";
				$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
				$USERPOSTS_COMMENTS_HEADING = $content_heading;
				$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."content.php?review.$comment_item_id'>";
				$USERPOSTS_COMMENTS_TYPE = "review";
			}
			else if($content_type == 1) {
				$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/".BULLET."' alt='' />";
				$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
				$USERPOSTS_COMMENTS_HEADING = $content_heading;
				$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."content.php?content.$comment_item_id'>";
				$USERPOSTS_COMMENTS_TYPE = "content";
			}
			$USERPOSTS_COMMENTS_COMMENT = ($comment_blocked ? CM_L2 : $comment_comment);
		}
	}
	if ($comment_type == "2") {
		$sql2->db_Select("download", "*", "download_id=$comment_item_id");
		$row = $sql2->db_Fetch();
		 extract($row);
		 
		$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/".BULLET."' alt='' />";
		$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
		$USERPOSTS_COMMENTS_HEADING = $download_name;
		$USERPOSTS_COMMENTS_COMMENT = $comment_comment;
		$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."download.php?view.".$comment_item_id."'>";
		$USERPOSTS_COMMENTS_TYPE = "download";
	}
	if ($comment_type == "4") {
		$sql2->db_Select("poll", "*", "poll_id=$comment_item_id");
		$row = $sql2->db_Fetch();
		 extract($row);
		 
		$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/".BULLET."' alt='' />";
		$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
		$USERPOSTS_COMMENTS_HEADING = $poll_title;
		$USERPOSTS_COMMENTS_COMMENT = $comment_comment;
		$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_BASE."comment.php?comment.poll.".$comment_item_id."'>";
		$USERPOSTS_COMMENTS_TYPE = "poll";
	}

	if ($comment_type == "5") {
		$sql2->db_Select("documentation", "*", "doc_id=$comment_item_id");
		$row = $sql2->db_Fetch();
		 extract($row);
		 
		$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/".BULLET."' alt='' />";
		$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
		$USERPOSTS_COMMENTS_HEADING = $doc_title;
		$USERPOSTS_COMMENTS_COMMENT = $comment_comment;
		$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".e_PLUGIN."documentation/documentation.php?".$comment_item_id."'>";
		$USERPOSTS_COMMENTS_TYPE = "documentation";
	}
	 
	// added a check for not numeric comment_types (=custom comments for your own plugins)
	if (!is_numeric($comment_type)) {
		unset($handle, $heading, $href);
		$handle = opendir(e_PLUGIN);
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)) {
				$plugin_handle = opendir(e_PLUGIN.$file."/");
				while (false !== ($file2 = readdir($plugin_handle))) {
					if ($file2 == "e_comment.php") {
						require_once(e_PLUGIN.$file."/".$file2);
						if ($comment_type == $e_plug_table) {
							$plugin_redir = $reply_location;
							list($url, $querystring) = explode("?", $plugin_redir);
							$USERPOSTS_COMMENTS_HEADING = $comment_subject;
							$USERPOSTS_COMMENTS_HREF_PRE = "<a href='".$url."?".$comment_item_id."'>";
						} else {
							$USERPOSTS_COMMENTS_HEADING = $comment_subject;
							$USERPOSTS_COMMENTS_HREF_PRE = "";
						}
					}
				}
			}
		}
		 
		$USERPOSTS_COMMENTS_ICON = "<img src='".THEME."images/".BULLET."' alt='' />";
		$USERPOSTS_COMMENTS_DATESTAMP = UP_LAN_9." ".$datestamp;
		$USERPOSTS_COMMENTS_COMMENT = $comment_comment;
		$USERPOSTS_COMMENTS_TYPE = $comment_type;
	}
	// end
	 
	return(preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE));
}
	
	
function parse_userposts_forum_table($row) {
	global $USERPOSTS_FORUM_TABLE, $gen, $tp;
	extract($row);
	 
	$gen = new convert;
	$sql2 = new db;
	 
	$poster = substr($thread_user, (strpos($thread_user, ".")+1));
	$datestamp = $gen->convert_date($thread_datestamp, "short");
	$DATESTAMP = $datestamp;
	 
	if ($thread_parent) {
		 
		if ($cachevar[$thread_parent]) {
			$thread_name = $cachevar[$thread_parent];
		} else {
			$tmp = $thread_parent;
			$sql2->db_Select("forum_t", "thread_name", "thread_id = '".$thread_parent."' ");
			list($thread_name) = $sql2->db_Fetch();
			$cachevar[$thread_parent] = $thread_name;
		}
		$USERPOSTS_FORUM_TOPIC_PRE = UP_LAN_15.": ";
	} else {
		$tmp = $thread_id;
		$USERPOSTS_FORUM_TOPIC_PRE = UP_LAN_2.": ";
	}
	 
	$thread_thread = $tp->toHTML($thread_thread);
	 
	$USERPOSTS_FORUM_ICON = "<img src='".e_PLUGIN."forum/images/".IMODE."/new_small.png' alt='' />";
	$USERPOSTS_FORUM_TOPIC_HREF_PRE = "<a href='".e_PLUGIN."forum/forum_viewtopic.php?".$tmp."'>";
	$USERPOSTS_FORUM_TOPIC = $thread_name;
	$USERPOSTS_FORUM_NAME_HREF_PRE = "<a href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_id."'>";
	$USERPOSTS_FORUM_NAME = $forum_name;
	$USERPOSTS_FORUM_THREAD = $thread_thread;
	$USERPOSTS_FORUM_DATESTAMP = UP_LAN_11." ".$datestamp;
	 
	return(preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE));
}
	
	
?>