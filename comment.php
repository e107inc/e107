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
|     $Revision: 1.61 $
|     $Date: 2010-01-15 21:10:18 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."news_class.php");
require_once(e_HANDLER."comment_class.php");
define("PAGE_NAME", COMLAN_99);

if (!e_QUERY) 
{
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
$comment_edit_query = $temp_query[0].".".$temp_query[1].".".$temp_query[2];
unset($temp_query);

$redirectFlag = 0;
if (isset($_POST['commentsubmit']) || isset($_POST['editsubmit'])) 
{	// New comment, or edited comment, being posted.
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
	} 
	elseif($table == "news") 
	{
	  if (!$sql->db_Select("news", "news_allow_comments", "`news_id` = '{$id}' AND `news_allow_comments` = 0")) 
	  {
		header("location: ".e_BASE."index.php");
		exit;
	  }
	}

	$pid = intval(varset($_POST['pid'], 0));				// ID of the specific comment being edited (nested comments - replies)

	$editpid = intval(varset($_POST['editpid'], 0));		// ID of the specific comment being edited (in-line comments)

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
	  $redirectFlag = $id;
/*		$redir = preg_replace("#\.edit.*#si", "", e_QUERY);
		header("Location: ".e_SELF."?{$redir}");
		exit;  */
	}
}


if (isset($_POST['replysubmit']))
{	// Reply to nested comment being posted
	if ($table == "news" && !$sql->db_Select("news", "news_allow_comments", "news_id='{$nid}' ")) 
	{
	  header("location:".e_BASE."index.php");
	  exit;
	} 
	else 
	{
	  $row = $sql->db_Fetch();
	  if (!$row['news_id']) 
	  {
		$pid = (isset($_POST['pid']) ? $_POST['pid'] : 0);
		$pid = intval($pid);

		$clean_authorname = $_POST['author_name'];
		$clean_comment = $_POST['comment'];
		$clean_subject = $_POST['subject'];

		$cobj->enter_comment($clean_authorname, $clean_comment, $table, $nid, $pid, $clean_subject);
		$e107cache->clear("comment.php?{$table}.{$id}");
	  }
	  $redirectFlag = $nid;
	}
}

if ($redirectFlag)
{	// Need to go back to original page

// Check plugin e_comment.php files
  $plugin_redir = false;
  $e_comment = $cobj->get_e_comment();
  if ($table == $e_comment[$table]['eplug_comment_ids'])
  {
	$plugin_redir = TRUE;
	$reply_location = str_replace("{NID}", $redirectFlag, $e_comment[$table]['reply_location']);
  }

  if ($plugin_redir)
  {
	echo "<script type='text/javascript'>document.location.href='{$reply_location}'</script>\n";
	exit;
  } 
  else
  {
	switch ($table)
	{
	  case "news" :
	  case "poll" :
		echo "<script type='text/javascript'>document.location.href='".e_HTTP."comment.php?comment.{$table}.{$redirectFlag}'</script>\n";
		exit;
		break;
	  case "download" :
		echo "<script type='text/javascript'>document.location.href='".e_HTTP."download.php?view.{$redirectFlag}'</script>\n";
		exit;
		break;
	  case "page" :
		echo "<script type='text/javascript'>document.location.href='".e_HTTP."page.php?{$redirectFlag}'</script>\n";
		exit;
		break;
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
	  switch ($table)
	  {
		case "news" :
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
		case "poll" :
		  if (!$sql->db_Select("polls", "poll_title", "poll_id='{$nid}' ")) 
		  {
			header("location:".e_BASE."index.php");
			exit;
		  } 
		  else 
		  {
			list($poll['poll_title']) = $sql->db_Fetch();
			$subject = $poll['poll_title'];
			$title = COMLAN_101;
		  }
		  break;
		case 'download' :
		  if ($sql->db_Select('download','download_name',"download_id={$nid} "))
		  {
			$row = $sql->db_Fetch();
			$subject = $row['download_name'];
			$title = COMLAN_106;
		  }
		  else
		  {
			header("location:".e_BASE."index.php");
			exit;
		  }
		  break;
	  }
	}
	define('e_PAGETITLE', $title." / ".COMLAN_99." / ".COMLAN_102.$subject."");
	require_once(HEADERF);
} 
elseif ($action == 'comment')
{  //  Default code if not reply

	// Check cache
	if ($cache = $e107cache->retrieve("comment.php?{$table}.{$id}")) 
	{
	  require_once(HEADERF);
	  echo $cache;
	  require_once(FOOTERF);
	  exit;
	} 
	else 
	{
	  switch ($table)
	  {
		case "news" :
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
		  }
		  break;
		case "poll" :
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
			$poll_to_show = $id;				// Need to pass poll number through to display routine
			require_once(HEADERF);
			require(e_PLUGIN."poll/poll_menu.php");
			$field = $row['poll_id'];
			if(!$comments_poll)
			{
			  require_once(FOOTERF);
			  exit;
			}
		  }
		  break;
		case 'download' :
		  if ($sql->db_Select('download','download_name',"download_id={$id} "))
		  {
			$row = $sql->db_Fetch();
			$subject = $row['download_name'];
			$title = COMLAN_106;
			$field = $id;
			require_once(HEADERF);
		  }
		  else
		  {
			header("location:".e_BASE."index.php");
			exit;
		  }
		  break;
		default :		// Hope its a plugin table
			$e_comment = $cobj->get_e_comment();
			if ($table == $e_comment[$table]['eplug_comment_ids'])
			{
				if ($sql->db_Select($table,$e_comment[$table]['db_title'],$e_comment[$table]['db_id']."={$id} "))
				{
				  $row = $sql->db_Fetch();
				  $subject = $row[$e_comment[$table]['db_title']];
				  $title = $e_comment[$table]['plugin_name'];
				  $field = $id;
				  require_once(HEADERF);
				}
				else
				{
				  header("location:".e_BASE."index.php");
				  exit;
				}
			}
			else
			{	// Error - emit some debug code
				require_once(HEADERF);
				if (E107_DEBUG_LEVEL)
				{
					echo "Comment error: {$table}  Field: {$e_comment['db_id']}  ID {$id}   Title: {$e_comment['db_title']}<br />";
					echo "<pre>";
					var_dump($e_comment);
					echo "</pre>"; 
				}
				else
				{
					header('location:'.e_BASE.'index.php');
					exit;
				}
			}
	  }
	}
}
else
{	// Invalid action - just exit
	header("location:".e_BASE."index.php");
	exit;
}

if(isset($pref['trackbackEnabled']) && $pref['trackbackEnabled'] && $table == "news")
{
  echo "<span class='smalltext'><b>".$pref['trackbackString']."</b> ".SITEURLBASE.e_PLUGIN_ABS."trackback/trackback.php?pid={$id}</span>";
}

$field = ($field ? $field : ($id ? $id : ""));			// ID of associated source item
$width = (isset($width) && $width ? $width : "");
$cobj->compose_comment($table, $action, $field, $width, $subject, $rate=FALSE);



if(isset($pref['trackbackEnabled']) && $pref['trackbackEnabled'] && $table == "news")
{
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
		echo "<div style='text-align:right'><a href='".e_PLUGIN_ABS."trackback/modtrackback.php?".$id."'>".COMLAN_317."</a></div><br />";
	}
}


//if (!strstr(e_QUERY, "poll")) 
// If output buffering started, cache the result
if ($comment_ob_start) 
{
	$cache = ob_get_contents();
	$e107cache->set("comment.php?{$table}.{$field}", $cache);
	ob_end_flush(); // dump the buffer we started
}


require_once(FOOTERF);

?>