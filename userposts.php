<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/userposts.php,v $
|     $Revision: 1.32 $
|     $Date: 2010-01-21 03:57:44 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."comment_class.php");
$cobj = new comment;
require_once(HEADERF);


$_POST['f_query'] = trim($_POST['f_query']);

$action = 'exit';
if (e_QUERY)
{
  $tmp = explode(".", e_QUERY);
  $from = intval($tmp[0]);			// Always defined
  $action = varset($tmp[1],'exit');
  if (!isset($tmp[2])) $action = 'exit';
  $id = intval(varset($tmp[2],0));
  if ($id <= 0) $action = 'exit';
  if (($id != USERID) && !check_class(varset($pref['memberlist_access'], 253))) $action = 'exit';
  unset($tmp);
}


if ($action == 'exit')
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
		$user_name = $row['user_name'];
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
		$sql->db_Select("user", "user_comments", "user_id=".$id);
		list($user_comments) = $sql->db_Fetch();
		$ctotal = $user_comments;
//		$data = $cobj->getCommentData($amount='10', $from, "comment_author = '".$user_id."'");
		$data = $cobj->getCommentData($amount='10', $from, "SUBSTRING_INDEX(comment_author,'.',1) = '".$id."'");
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

//	$userposts_comments_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE_START);
	$userposts_comments_table_start = $tp->simpleParse($USERPOSTS_COMMENTS_TABLE_START);

//	$userposts_comments_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE_END);
	$userposts_comments_table_end = $tp->simpleParse($USERPOSTS_COMMENTS_TABLE_END);
	
	$ctext .= $userposts_comments_table_start."".$userposts_comments_table_string."".$userposts_comments_table_end;

	$ns->tablerender($ccaption, $ctext);

	$parms = $ctotal.",10,".$from.",".e_SELF."?[FROM].comments.".$id;
	$USERPOSTS_NEXTPREV = $tp->parseTemplate("{NEXTPREV={$parms}}");
//	echo preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_NP_TABLE);
	echo $tp->simpleParse($USERPOSTS_NP_TABLE);
}



if ($action == "forums" || isset($_POST['fsearch']))
{

	if(is_numeric($id))
	{
		$user_id = intval($id);
		$sql->db_Select("user", "user_name", "user_id=".$id);
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
	SELECT ft.*, f.* FROM #forum_t AS ft
	LEFT JOIN #forum AS f ON ft.thread_forum_id = f.forum_id
	LEFT JOIN #forum AS fp ON f.forum_parent = fp.forum_id
	WHERE SUBSTRING_INDEX(ft.thread_user,'.',1) = {$user_id}
	AND f.forum_class IN (".USERCLASS_LIST.")
	AND fp.forum_class IN (".USERCLASS_LIST.")
	{$s_info}
	ORDER BY ft.thread_datestamp DESC LIMIT {$from}, 10
	";

	$total_qry = "
	SELECT COUNT(*) AS count FROM #forum_t AS ft
	LEFT JOIN #forum AS f ON ft.thread_forum_id = f.forum_id
	LEFT JOIN #forum AS fp ON f.forum_parent = fp.forum_id
	WHERE SUBSTRING_INDEX(ft.thread_user,'.',1) = {$user_id}
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
//		$userposts_forum_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_START);
		$userposts_forum_table_start = $tp->simpleParse($USERPOSTS_FORUM_TABLE_START);
		$USERPOSTS_FORUM_SEARCH = "<input class='tbox' type='text' name='f_query' size='20' value='' maxlength='50' /> <input class='button' type='submit' name='fsearch' value='".UP_LAN_12."' />";
//		$userposts_forum_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_END);
		$userposts_forum_table_end = $tp->simpleParse($USERPOSTS_FORUM_TABLE_END);
		$ftext .= $userposts_forum_table_start."".$userposts_forum_table_string."".$userposts_forum_table_end;
	}
	$ns->tablerender($fcaption, $ftext);

	$parms = $ftotal.",10,".$from.",".e_SELF."?[FROM].forums.".$id;
	$USERPOSTS_NEXTPREV = $tp->parseTemplate("{NEXTPREV={$parms}}");
//	echo preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_NP_TABLE);
	echo $tp->simpleParse($USERPOSTS_NP_TABLE);
}

require_once(FOOTERF);




function parse_userposts_comments_table($row)
{
	global $USERPOSTS_COMMENTS_TABLE, $pref, $gen, $tp, $menu_pref, $id, $sql2, $comment_files;

	$gen = new convert;
	$datestamp = $gen->convert_date($row['comment_datestamp'], "short");
	$bullet = '';
	if(defined('BULLET'))
	{
		$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" style="vertical-align: middle;" />';
	}
	elseif(file_exists(THEME.'images/bullet2.gif'))
	{
		$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" style="vertical-align: middle;" />';
	}
	$tVars['USERPOSTS_COMMENTS_ICON']		= $bullet;
	$tVars['USERPOSTS_COMMENTS_DATESTAMP']	= UP_LAN_11." ".$datestamp;
	$tVars['USERPOSTS_COMMENTS_HEADING']	= $row['comment_title'];
	$tVars['USERPOSTS_COMMENTS_COMMENT']	= $row['comment_comment'];
	$tVars['USERPOSTS_COMMENTS_HREF_PRE']	= "<a href='".$row['comment_url']."'>";
	$tVars['USERPOSTS_COMMENTS_TYPE']		= $row['comment_type'];

//	return(preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE));
	return($tp->simpleParse($USERPOSTS_COMMENTS_TABLE, $tVars));
}


function parse_userposts_forum_table($row)
{
	global $USERPOSTS_FORUM_TABLE, $gen, $tp, $id;
	extract($row);

	$gen = new convert;
	$sql2 = new db;

	$poster = substr($thread_user, (strpos($thread_user, ".")+1));
	$datestamp = $gen->convert_date($thread_datestamp, "short");
	$tVars['DATESTAMP'] = $datestamp;

	if ($thread_parent)
	{
		if ($cachevar[$thread_parent])
		{
			$thread_name = $cachevar[$thread_parent];
		}
		else
		{
			$sql2->db_Select("forum_t", "thread_name", "thread_id = ".intval($thread_parent));
			list($thread_name) = $sql2->db_Fetch();
			$cachevar[$thread_parent] = $thread_name;
		}
		$tVars['USERPOSTS_FORUM_TOPIC_PRE'] = UP_LAN_15.": ";
	}
	else
	{
		$tVars['USERPOSTS_FORUM_TOPIC_PRE'] = UP_LAN_2.": ";
	}

	$tmp = $thread_id;
	$thread_thread = $tp->toHTML($thread_thread, TRUE, "USER_BODY", $id);

	$tVars['USERPOSTS_FORUM_ICON'] = "<img src='".e_PLUGIN."forum/images/".IMODE."/new_small.png' alt='' />";
	$tVars['USERPOSTS_FORUM_TOPIC_HREF_PRE'] = "<a href='".e_PLUGIN."forum/forum_viewtopic.php?".$tmp.".post'>";
	$tVars['USERPOSTS_FORUM_TOPIC'] = $thread_name;
	$tVars['USERPOSTS_FORUM_NAME_HREF_PRE'] = "<a href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_id."'>";
	$tVars['USERPOSTS_FORUM_NAME'] = $forum_name;
	$tVars['USERPOSTS_FORUM_THREAD'] = $thread_thread;
	$tVars['USERPOSTS_FORUM_DATESTAMP'] = UP_LAN_11." ".$datestamp;

//	return(preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE));
	return($tp->simpleParse($USERPOSTS_FORUM_TABLE, $tVars));
}

?>
