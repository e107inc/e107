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
|     $Source: /cvs_backup/e107_0.7/comment.php,v $
|     $Revision: 1.10 $
|     $Date: 2005-01-27 19:51:37 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."news_class.php");
require_once(e_HANDLER."comment_class.php");
	
if (!e_QUERY) {
	header("location:".e_BASE."index.php");
	exit;
}
$qs = explode(".", e_QUERY);
$action = $qs[0];
$table = $qs[1];
$id = $qs[2];
$nid = $qs[3];
$xid = $qs[4];
$cobj = new comment;
$aj = new textparse;
	
if (IsSet($_POST['commentsubmit'])) {
	if (!$sql->db_Select("news", "news_allow_comments", "news_id='$id' ") && $table == "news") {
		header("location:".e_BASE."index.php");
		exit;
	} else {
		$row = $sql->db_Fetch();
		if (!$row[0] && (ANON === TRUE || USER === TRUE)) {
			if (!$pid) {
				$pid = 0;
			}
			$cobj->enter_comment($_POST['author_name'], $_POST['comment'], $table, $id, $pid, $_POST['subject']);
			if ($table == "news") {
				$e107cache->clear("news");
			} else {
				$e107cache->clear("comment.php?$table.$id");
			}
		}
	}
}
if (IsSet($_POST['replysubmit'])) {
	if ($table == "news" && !$sql->db_Select("news", "news_allow_comments", "news_id='$nid' ")) {
		header("location:".e_BASE."index.php");
		exit;
	} else {
		$row = $sql->db_Fetch();
		if (!$row[0] && (ANON === TRUE || USER === TRUE)) {
			$pid = $_POST[pid];
			$cobj->enter_comment($_POST['author_name'], $_POST['comment'], $table, $nid, $pid, $_POST['subject']);
			$e107cache->clear("comment.php?$table.$id");
		}
		$plugin_redir = FALSE;
		$handle = opendir(e_PLUGIN);
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)) {
				$plugin_handle = opendir(e_PLUGIN.$file."/");
				while (false !== ($file2 = readdir($plugin_handle))) {
					if ($file2 == "e_comment.php") {
						require_once(e_PLUGIN.$file."/".$file2);
						if ($table == $e_plug_table) {
							$plugin_redir = TRUE;
							break 2;
						}
					}
				}
			}
		}
		if ($plugin_redir) {
			header("location:".$reply_location."");
			exit;
		} elseif ($table == "news" || $table == "poll") {
			header("location:".e_BASE."comment.php?comment.".$table.".".$nid."");
			exit;
		} elseif($table == "bugtrack") {
			header("location:".e_PLUGIN."bugtracker/bugtracker.php?show.".$nid."");
			exit;
		} elseif($table == "faq") {
			header("location:".e_PLUGIN."faq/faq.php?cat.".$xid.".".$nid."");
			exit;
		} elseif ($table == "content") {
			header("location:".e_BASE."content.php?".$_POST['content_type'].".".$nid."");
			exit;
		} elseif ($table == "download") {
			header("location:".e_BASE."download.php?view.".$nid."");
			exit;
		}
	}
}
if ($action == "reply") {
	if (!$pref['nested_comments']) {
		header("location:".e_BASE."comment.php?comment.".$table.".".$nid."");
		exit;
	}
	$query = "comment_id='$id' LIMIT 0,1";
	if ($sql->db_Select("comments", "comment_subject", "comment_id='$id'")) {
		list($comments['comment_subject']) = $sql->db_Fetch();
		$subject = $aj->formtpa($comments['comment_subject']);
	}
	if ($subject == "") {
		if ($table == "news") {
			if (!$sql->db_Select("news", "news_title", "news_id='$nid' ")) {
				header("location:".e_BASE."index.php");
				exit;
			} else {
				list($news['news_title']) = $sql->db_Fetch();
				$subject = $news['news_title'];
				$title = LAN_100;
			}
		} elseif ($table == "poll") {
			if (!$sql->db_Select("poll", "poll_title", "poll_id='$nid' ")) {
				header("location:".e_BASE."index.php");
				exit;
			} else {
				list($poll['poll_title']) = $sql->db_Fetch();
				$subject = $poll['poll_title'];
				$title = LAN_101;
			}
		} elseif ($table == "content") {
			$sql->db_Select("content", "content_heading", "content_id='$nid'");
			$subject = $content['content_heading'];
		} elseif ($table == "bugtracker") {
			$sql->db_Select("bugtrack", "bugtrack_summary", "bugtrack_id='$nid'");
			$subject = $content['content_heading'];
		}
	}
	if ($table == "content") {
		$sql->db_Select("content", "content_type", "content_id='$nid'");
		list($content['content_type']) = $sql->db_Fetch();
		if ($content['content_type'] == "0") {
			$content_type = "article";
			$title = LAN_103;
		} elseif ($content['content_type'] == "3") {
			$content_type = "review";
			$title = LAN_104;
		} elseif ($content['content_type'] == "1") {
			$content_type = "content";
			$title = LAN_105;
		}
	}
	 
	define(e_PAGETITLE, $title." / ".LAN_99." / ".LAN_102.$subject."");
	require_once(HEADERF);
} else {
	 
	 
	if ($cache = $e107cache->retrieve("comment.php?$table.$id")) {
		require_once(HEADERF);
		echo $cache;
		require_once(FOOTERF);
		exit;
	} else {
		if ($table == "news") {
			/*
			changes by jalist 19/01/05:
			updated db query removed one call
			*/
			 
			 
			$query = "SELECT #news.*, user_id, user_name, user_customtitle, category_name, category_icon FROM #news
				LEFT JOIN #user ON #news.news_author = #user.user_id
				LEFT JOIN #news_category ON #news.news_category = #news_category.category_id
				WHERE news_id=$id";
			 
			if (!$sql->db_Select_gen($query)) {
				header("location:".e_BASE."index.php");
				exit;
			} else {
				$news = $sql->db_Fetch();
				if ($news['news_allow_comments']) {
					header("location:".e_BASE."index.php");
					exit;
				}
				if (!check_class($news['news_class'])) {
					header("location:".e_BASE."index.php");
					exit;
				} else {
					$subject = $aj->formtpa($news['news_title']);
					define(e_PAGETITLE, LAN_100." / ".LAN_99." / ".$subject."");
					require_once(HEADERF);
					ob_start();
					$ix = new news;
					$ix->render_newsitem($news, "default");
					$field = $news['news_id'];
					$comtype = 0;
				}
			}
		}
		else if($table == "poll") {
			if (!$sql->db_Select("poll", "*", "poll_id='$id' ")) {
				header("location:".e_BASE."index.php");
				exit;
			} else {
				$row = $sql->db_Fetch();
				extract($row);
				if ($poll_comment == 0) {
					header("location:".e_BASE."index.php");
				}
				$subject = $poll_title;
				define(e_PAGETITLE, LAN_101." / ".LAN_99." / ".$subject."");
				require_once(HEADERF);
				require_once(e_PLUGIN."poll_menu/poll_menu.php");
				$field = $poll_id;
				$comtype = 4;
			}
		}
		require_once(HEADERF);
		$query = ($pref['nested_comments'] ? "comment_item_id='$field' AND comment_type='$comtype' AND comment_pid='0' ORDER BY comment_datestamp" :
		"SELECT #comments.*, user_id, user_name, user_image, user_signature, user_join, user_comments, user_location FROM #comments
			LEFT JOIN #user ON #comments.comment_author = #user.user_id WHERE comment_item_id='$field' AND comment_type='$comtype'  ORDER BY comment_datestamp");
	}
}
	
$comment_total = $sql->db_Select_gen($query);
if ($comment_total) {
	$width = 0;
	while ($row = $sql->db_Fetch()) {
		if ($pref['nested_comments']) {
			$text = $cobj->render_comment($row, $table, $action, $id, $width, $subject);
			$ns->tablerender(LAN_5, $text);
		} else {
			$text .= $cobj->render_comment($row, $table, $action, $id, $width, $subject);
		}
	}
	if (!$pref['nested_comments']) {
		$ns->tablerender(LAN_5, $text);
	}
}
	
	
if (ADMIN && getperms("B")) {
	if (!strstr(e_QUERY, ".")) {
		$ct = "news.";
	}
	echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?".$table.".".$id."'>".LAN_314."</a></div><br />";
}
	
$cobj->form_comment($action, $table, $id, $subject, $content_type);
	
if (!strstr(e_QUERY, "poll")) {
	$cache = ob_get_contents();
	$e107cache->set("comment.php?{$table}.{$field}", $cache);
}
	
require_once(FOOTERF);
	
?>