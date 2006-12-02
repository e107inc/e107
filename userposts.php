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
|     $Source: /cvs_backup/e107_0.8/userposts.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:33:10 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."comment_class.php");
$cobj = new comment;
require_once(HEADERF);

if (!USER) {
	header("location:".e_BASE."index.php");
	exit;
}

$_POST['f_query'] = trim($_POST['f_query']);

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

if ($action == "comments")
{
	if(is_numeric($id))
	{
		$sql->db_Select("user", "user_name", "user_id=".$id);
		$row = $sql->db_Fetch();
		extract($row);
		$user_id = $id.".".$user_name."";
	}
	else
	{
		$user_name = UP_LAN_16.$id;
	}

	if (!$USERPOSTS_COMMENTS_TABLE)
	{
		if (file_exists(THEME."userposts_template.php"))
		{
			require_once(THEME."userposts_template.php");
		}
		else
		{
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
		$data = $cobj->getCommentData($amount='10', $from, "comment_author = '".$user_id."'");
	}
	else
	{
		require_once(e_HANDLER."encrypt_handler.php");
		$dip = decode_ip($id);
		$ccaption = UP_LAN_1.$dip;
		$data = $cobj->getCommentData($amount='10', $from, "comment_ip = '".$id."'");
	}

	if(empty($data) || !is_array($data)){
		$ctext = "<span class='mediumtext'>".UP_LAN_7."</span>";
	}

	global $row;
	foreach($data as $row){
		$userposts_comments_table_string .= parse_userposts_comments_table($row);
	}

	$userposts_comments_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE_START);
	$userposts_comments_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE_END);

	$ctext .= $userposts_comments_table_start."".$userposts_comments_table_string."".$userposts_comments_table_end;

	$ns->tablerender($ccaption, $ctext);

	$parms = $ctotal.",10,".$from.",".e_SELF."?[FROM].comments.".$id;
	$USERPOSTS_NEXTPREV = $tp->parseTemplate("{NEXTPREV={$parms}}");
	echo preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_NP_TABLE);
}



if ($action == "forums" || isset($_POST['fsearch']))
{

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

	if (!$USERPOSTS_FORUM_TABLE)
	{
		if (file_exists(THEME."userposts_template.php"))
		{
			require_once(THEME."userposts_template.php");
		}
		else
		{
			require_once(e_BASE.$THEMES_DIRECTORY."templates/userposts_template.php");
		}
	}

	$s_info = "";
	if (isset($_POST['f_query']) && $_POST['f_query'] != "")
	{
		$f_query = $tp -> toDB($_POST['f_query']);
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
		$render = $sql -> db_getList();
		foreach ($render as $row)
		{
			$userposts_forum_table_string .= parse_userposts_forum_table($row);
		}
		$userposts_forum_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_START);
		$USERPOSTS_FORUM_SEARCH = "<input class='tbox' type='text' name='f_query' size='20' value='' maxlength='50' /> <input class='button' type='submit' name='fsearch' value='".UP_LAN_12."' />";
		$userposts_forum_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_END);
		$ftext .= $userposts_forum_table_start."".$userposts_forum_table_string."".$userposts_forum_table_end;
	}
	$ns->tablerender($fcaption, $ftext);

	$parms = $ftotal.",10,".$from.",".e_SELF."?[FROM].forums.".$id;
	$USERPOSTS_NEXTPREV = $tp->parseTemplate("{NEXTPREV={$parms}}");
	echo preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_NP_TABLE);
}

require_once(FOOTERF);




function parse_userposts_comments_table($row)
{
	global $USERPOSTS_COMMENTS_TABLE, $pref, $gen, $tp, $menu_pref, $id, $sql2, $comment_files;

	$gen = new convert;
	$datestamp = $gen->convert_date($row['comment_datestamp'], "short");
	$USERPOSTS_COMMENTS_ICON		= "<img src='".THEME."images/".BULLET."' alt='' />";
	$USERPOSTS_COMMENTS_DATESTAMP	= UP_LAN_11." ".$datestamp;
	$USERPOSTS_COMMENTS_HEADING		= $row['comment_title'];
	$USERPOSTS_COMMENTS_COMMENT		= $row['comment_comment'];
	$USERPOSTS_COMMENTS_HREF_PRE	= "<a href='".$row['comment_url']."'>";
	$USERPOSTS_COMMENTS_TYPE		= $row['comment_type'];

	return(preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE));
}


function parse_userposts_forum_table($row)
{
	global $USERPOSTS_FORUM_TABLE, $gen, $tp, $id;
	extract($row);

	$gen = new convert;
	$sql2 = new db;

	$poster = substr($thread_user, (strpos($thread_user, ".")+1));
	$datestamp = $gen->convert_date($thread_datestamp, "short");
	$DATESTAMP = $datestamp;

	if ($thread_parent)
	{
		if ($cachevar[$thread_parent])
		{
			$thread_name = $cachevar[$thread_parent];
		}
		else
		{
			$tmp = $thread_parent;
			$sql2->db_Select("forum_t", "thread_name", "thread_id = '".intval($thread_parent)."' ");
			list($thread_name) = $sql2->db_Fetch();
			$cachevar[$thread_parent] = $thread_name;
		}
		$USERPOSTS_FORUM_TOPIC_PRE = UP_LAN_15.": ";
	}
	else
	{
		$tmp = $thread_id;
		$USERPOSTS_FORUM_TOPIC_PRE = UP_LAN_2.": ";
	}

	$thread_thread = $tp->toHTML($thread_thread, TRUE, "", $id);

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