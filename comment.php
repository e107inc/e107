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
|     $Source: /cvs_backup/e107_0.8/comment.php,v $
|     $Revision: 1.4 $
|     $Date: 2007-12-08 14:49:44 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."news_class.php");
require_once(e_HANDLER."comment_class.php");
define("PAGE_NAME", COMLAN_99);

if (!e_QUERY) {
	header("location:".e_BASE."index.php");
	exit;
}

$cobj =& new comment;

$temp_query = explode(".", e_QUERY);
$action = $temp_query[0];			// Usually says 'comment' - may say 'reply'
$table = $temp_query[1];			// Table containing item associated with comment(s)
$id  = intval(varset($temp_query[2], ""));	// ID of item associated with comments (e.g. news ID)
											// For reply with nested comments, its the ID of the comment
$nid = intval(varset($temp_query[3], ""));	// Action - e.g. 'edit'. Or news ID for reply with nested comments
$xid = intval(varset($temp_query[4], ""));	// ID of target comment
global $comment_edit_query;
//$comment_edit_query = '';
//if ($temp_query[3] == 'edit') $comment_edit_query = $temp_query[0].".".$temp_query[1].".".$temp_query[2];
$comment_edit_query = $temp_query[0].".".$temp_query[1].".".$temp_query[2];
unset($temp_query);

if (isset($_POST['commentsubmit']) || isset($_POST['editsubmit'])) 
{
	if(!ANON && !USER)
	{
		header("location: ".e_BASE."index.php");
		exit;
	}

	if($table == "poll") 
	{
		if (!$sql->db_Select("polls", "poll_title", "`poll_id` = '{$id}' AND `poll_comment` = 1")) 
		{
			header("location: ".e_BASE."index.php");
			exit;
		}
	} else if($table == "news") 
	{
		if (!$sql->db_Select("news", "news_allow_comments", "`news_id` = '{$id}' AND `news_allow_comments` = 0")) 
		{
			header("location: ".e_BASE."index.php");
			exit;
		}
	}

//	$pid = (isset($_POST['pid']) ? $_POST['pid'] : 0);
//	$pid = intval($pid);
	$pid = intval(varset($_POST['pid'], 0));

	$editpid = intval(varset($_POST['editpid'], 0));		// ID of the specific comment being edited

	$clean_authorname = $_POST['author_name'];
	$clean_comment = $_POST['comment'];
	$clean_subject = $_POST['subject'];

	$cobj->enter_comment($clean_authorname, $clean_comment, $table, $id, $pid, $clean_subject);
	if ($table == "news") 
	{
		$e107cache->clear("news");
	} 
	else 
	{
		$e107cache->clear("comment.php?{$table}.{$id}");
	}

	if($editpid) 
	{
		$redir = preg_replace("#\.edit.*#si", "", e_QUERY);
		header("Location: ".e_SELF."?{$redir}");
		exit;
	}
}


if (isset($_POST['replysubmit']))
{
	if ($table == "news" && !$sql->db_Select("news", "news_allow_comments", "news_id='{$nid}' ")) 
	{
		header("location:".e_BASE."index.php");
		exit;
	} 
	else 
	{
		$row = $sql->db_Fetch();
		if (!$row['news_id']) {
			$pid = (isset($_POST['pid']) ? $_POST['pid'] : 0);
			$pid = intval($pid);

			$clean_authorname = $_POST['author_name'];
			$clean_comment = $_POST['comment'];
			$clean_subject = $_POST['subject'];

			$cobj->enter_comment($clean_authorname, $clean_comment, $table, $nid, $pid, $clean_subject);
			$e107cache->clear("comment.php?{$table}.{$id}");
		}
		//plugin e_comment.php files
		$plugin_redir = false;
		$e_comment = $cobj->get_e_comment();
		if ($table == $e_comment[$table]['eplug_comment_ids']){
			$plugin_redir = TRUE;
			$reply_location = str_replace("{NID}", $nid, $e_comment[$table]['reply_location']);
		}

		if ($plugin_redir)
		{
			echo "<script type='text/javascript'>document.location.href='{$reply_location}'</script>\n";
			exit;
		} elseif ($table == "news" || $table == "poll")
		{
			echo "<script type='text/javascript'>document.location.href='".e_BASE."comment.php?comment.{$table}.{$nid}'</script>\n";
			exit;
		} elseif($table == "bugtrack")
		{
			echo "<script type='text/javascript'>document.location.href='".e_PLUGIN."bugtracker/bugtracker.php?show.{$nid}'</script>\n";
			exit;
		} elseif($table == "faq")
		{
			echo "<script type='text/javascript'>document.location.href='".e_PLUGIN."faq/faq.php?cat.{$xid}.{$nid}'</script>\n";
			exit;
		} elseif ($table == "content")
		{
			echo "<script type='text/javascript'>document.location.href='".e_BASE."content.php?{$_POST['content_type']}.{$nid}'</script>\n";
			exit;
		} elseif ($table == "download")
		{
			echo "<script type='text/javascript'>document.location.href='".e_BASE."download.php?view.{$nid}'</script>\n";
			exit;
		} elseif ($table == "page")
		{
			echo "<script type='text/javascript'>document.location.href='".e_BASE."page.php?{$nid}'</script>\n";
			exit;
		}
	}
}

$comment_ob_start = FALSE;
if ($action == "reply") 
{
	if (!$pref['nested_comments']) 
	{
		header("Location: ".e_BASE."comment.php?comment.{$table}.{$nid}");
		exit;
	}
	
	$query = "`comment_id` = '{$id}' LIMIT 0,1";
	if ($sql->db_Select("comments", "comment_subject", "`comment_id` = '{$id}'")) 
	{
		list($comments['comment_subject']) = $sql->db_Fetch();
		$subject = $comments['comment_subject'];
		$subject_header = $tp->toHTML($comments['comment_subject']);
	}
	
	if ($subject == "") 
	{
	  if ($table == "news") 
	  {
		if (!$sql->db_Select("news", "news_title", "news_id='{$nid}' ")) 
		{
		  header("location: ".e_BASE."index.php");
		  exit;
		} 
		else 
		{
		  list($news['news_title']) = $sql->db_Fetch();
		  $subject = $news['news_title'];
		  $title = COMLAN_100;
		}
	  } 
	  elseif ($table == "poll") 
	  {
			if (!$sql->db_Select("polls", "poll_title", "poll_id='{$nid}' ")) {
				header("location:".e_BASE."index.php");
				exit;
			} else {
				list($poll['poll_title']) = $sql->db_Fetch();
				$subject = $poll['poll_title'];
				$title = COMLAN_101;
			}
	  } 
	  elseif ($table == "content") 
	  {
			$sql->db_Select("content", "content_heading", "content_id='{$nid}'");
			$subject = $content['content_heading'];
	  } 
	  elseif ($table == "bugtracker") 
	  {
			$sql->db_Select("bugtrack", "bugtrack_summary", "bugtrack_id='{$nid}'");
			$subject = $content['content_heading'];
	  }
	}

	if ($table == "content") 
	{
		$sql->db_Select("content", "content_type", "content_id='{$nid}'");
		list($content['content_type']) = $sql->db_Fetch();
		if ($content['content_type'] == "0") {
			$content_type = "article";
			$title = COMLAN_103;
		} elseif ($content['content_type'] == "3") {
			$content_type = "review";
			$title = COMLAN_104;
		} elseif ($content['content_type'] == "1") {
			$content_type = "content";
			$title = COMLAN_105;
		}
	}

	define('e_PAGETITLE', $title." / ".COMLAN_99." / ".COMLAN_102.$subject."");
	require_once(HEADERF);
} 
else 
{
	if ($cache = $e107cache->retrieve("comment.php?{$table}.{$id}")) 
	{
		require_once(HEADERF);
		echo $cache;
		require_once(FOOTERF);
		exit;
	} 
	else 
	{
		if ($table == "news") 
		{
			/*
			changes by jalist 19/01/05:
			updated db query removed one call
			*/

			if(isset($pref['trackbackEnabled']) && $pref['trackbackEnabled']) 
			{
				$query = "SELECT COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
				LEFT JOIN #user AS u ON n.news_author = u.user_id
				LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
				LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
				WHERE n.news_class REGEXP '".e_CLASS_REGEXP."'
				AND n.news_id={$id}
				AND n.news_allow_comments=0
				GROUP by n.news_id";
			} 
			else 
			{
				$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
				LEFT JOIN #user AS u ON n.news_author = u.user_id
				LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
				WHERE n.news_class REGEXP '".e_CLASS_REGEXP."'
				AND n.news_id={$id}
				AND n.news_allow_comments=0";
			}

			if (!$sql->db_Select_gen($query)) 
			{
				header("location:".e_BASE."index.php");
				exit;
			} 
			else 
			{
				$news = $sql->db_Fetch();
				$subject = $tp->toForm($news['news_title']);
				define("e_PAGETITLE", COMLAN_100." / ".COMLAN_99." / {$subject}");
				require_once(HEADERF);
				ob_start();
				$comment_ob_start = TRUE;
				$ix = new news;
				$ix->render_newsitem($news, "extend"); // extend so that news-title-only news text is displayed in full when viewing comments.
				$field = $news['news_id'];
				$comtype = 0;
			}
		}
		else if($table == "poll") 
		{
			if (!$sql->db_Select("polls", "*", "poll_id='{$id}'")) 
			{
				header("location:".e_BASE."index.php");
				exit;
			} 
			else 
			{
				$row = $sql->db_Fetch();
				$comments_poll = $row['poll_comment'];
				$subject = $row['poll_title'];
				define("e_PAGETITLE", COMLAN_101." / ".COMLAN_99." / ".$subject."");
				require_once(HEADERF);
				require(e_PLUGIN."poll/poll_menu.php");
				$field = $row['poll_id'];
				$comtype = 4;

				if(!$comments_poll)
				{
					require_once(FOOTERF);
					exit;
				}
			}
		}
		require_once(HEADERF);
	}
}

if(isset($pref['trackbackEnabled']) && $pref['trackbackEnabled'] && $table == "news")
{
	echo "<span class='smalltext'><b>".$pref['trackbackString']."</b> ".$e107->http_path.e_PLUGIN."trackback/trackback.php?pid={$id}</span>";
}
$field = ($field ? $field : ($id ? $id : ""));
$width = (isset($width) && $width ? $width : "");
$cobj->compose_comment($table, $action, $field, $width, $subject, $rate=FALSE);



if(isset($pref['trackbackEnabled']) && $pref['trackbackEnabled'] && $table == "news"){
	if($sql->db_Select("trackback", "*", "trackback_pid={$id}"))
	{
		$tbArray = $sql -> db_getList();

		if (file_exists(THEME."trackback_template.php")) {
			require_once(THEME."trackback_template.php");
		} else {
			require_once(e_THEME."templates/trackback_template.php");
		}

		$text = "";

		foreach($tbArray as $trackback)
		{
			extract($trackback);
			$TITLE = $trackback_title;
			$EXCERPT = $trackback_excerpt;
			$BLOGNAME = "<a href='{$trackback_url}' rel='external'>{$trackback_blogname}</a>";
			$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TRACKBACK);
		}

		if($TRACKBACK_RENDER_METHOD)
		{
			$ns->tablerender("<a name='track'></a>".COMLAN_315, $text);
		}
		else
		{
			echo "<a name='track'></a>".$text;
		}
	}
	else
	{
		echo "<a name='track'></a>".COMLAN_316;
	}
	if (ADMIN && getperms("B")) {
		echo "<div style='text-align:right'><a href='".e_PLUGIN."trackback/modtrackback.php?".$id."'>".COMLAN_317."</a></div><br />";
	}
}

if (!strstr(e_QUERY, "poll")) 
{
  $cache = ob_get_contents();
  $e107cache->set("comment.php?{$table}.{$field}", $cache);
}
if ($comment_ob_start) ob_end_flush(); // dump the buffer we started


require_once(FOOTERF);

?>