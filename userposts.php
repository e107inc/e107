<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2009 e107 Inc 
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/userposts.php,v $
|     $Revision: 1.11 $
|     $Date: 2009-08-23 10:57:50 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/
require_once('class2.php');
require_once(e_HANDLER.'comment_class.php');
$cobj = new comment;
require_once(HEADERF);


$_POST['f_query'] = trim($_POST['f_query']);

$action = 'exit';
if (e_QUERY)
{
  $tmp = explode('.', e_QUERY);
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
		$row = $sql->db_Fetch();
		$ctotal = $row['user_comments'];
		$data = $cobj->getCommentData($amount='10', $from, "comment_author = '".$user_id."'");
	}
	else
	{
		$dip = $id;
		if (strlen($dip) == 8)
		{  // Legacy decode (IPV4 address as it used to be stored - hex string)
		  $hexip = explode('.', chunk_split($dip, 2, '.'));
		  $dip = hexdec($hexip[0]). '.' . hexdec($hexip[1]) . '.' . hexdec($hexip[2]) . '.' . hexdec($hexip[3]);

		}
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



if ($action == 'forums' || isset($_POST['fsearch']))
{
	require_once (e_PLUGIN.'forum/forum_class.php');
	$forum = new e107forum;

	$forumList = implode(',', $forum->permList['view']);


	if(is_numeric($id))
	{
		$uinfo = get_user_data($id);
		$fcaption = UP_LAN_0.' '.$uinfo['user_name'];
	}
	else
	{
		$user_name = 0;
	}

	if (!$USERPOSTS_FORUM_TABLE)
	{
		if (file_exists(THEME.'userposts_template.php'))
		{
			require_once(THEME.'userposts_template.php');
		}
		else
		{
			require_once(e_BASE.$THEMES_DIRECTORY.'templates/userposts_template.php');
		}
	}

	$s_info = '';
	if (isset($_POST['f_query']) && $_POST['f_query'] != '')
	{
		$f_query = $tp -> toDB($_POST['f_query']);
		$s_info = "AND (t.thread_name REGEXP('".$f_query."') OR p.post_entry REGEXP('".$f_query."'))";
		$fcaption = UP_LAN_12.' '.$row['user_name'];
	}
	$qry = "
	SELECT SQL_CALC_FOUND_ROWS p.*, t.*, f.* FROM `#forum_post` AS p
	LEFT JOIN `#forum_thread` AS t ON t.thread_id = p.post_thread
	LEFT JOIN `#forum` AS f ON f.forum_id = p.post_forum
	WHERE p.post_user = {$id}
	AND p.post_forum IN ({$forumList})
	{$s_info}
	ORDER BY p.post_datestamp DESC LIMIT {$from}, 10
	";

	if (!$sql->db_Select_gen($qry))
	{
		$ftext .= "<span class='mediumtext'>".UP_LAN_8.'</span>';
	}
	else
	{
		if (!is_object($gen))
		{
			$gen = new convert;
		}
		while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
		{
//			var_dump($row);
			$datestamp = $gen->convert_date($row['post_datestamp'], 'short');
			if ($row['thread_datestamp'] = $row['post_datestamp'])
			{
				$USERPOSTS_FORUM_TOPIC_PRE = UP_LAN_2.': ';
			}
			else
			{
				$USERPOSTS_FORUM_TOPIC_PRE = UP_LAN_12.': ';
			}
			$USERPOSTS_FORUM_ICON = "<img src='".e_PLUGIN."forum/images/".IMODE."/new_small.png' alt='' />";
			$USERPOSTS_FORUM_TOPIC_HREF_PRE = "<a href='".$e107->url->getUrl('forum', 'thread', "func=post&id={$row['post_id']}")."'>";
			$USERPOSTS_FORUM_TOPIC = $tp->toHTML($row['thread_name'], true, 'USER_BODY', $id);
			$USERPOSTS_FORUM_NAME_HREF_PRE = "<a href='".$e107->url->getUrl('forum', 'forum', "func=view&id={$row['post_forum']}")."'>";
			$USERPOSTS_FORUM_NAME = $tp->toHTML($row['forum_name'], true, 'USER_BODY', $id);
			$USERPOSTS_FORUM_THREAD = $tp->toHTML($row['post_entry'], true, 'USER_BODY', $id);
			$USERPOSTS_FORUM_DATESTAMP = UP_LAN_11." ".$datestamp;

			$userposts_forum_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE);
		}
		$userposts_forum_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_START);
		$USERPOSTS_FORUM_SEARCH = "<input class='tbox' type='text' name='f_query' size='20' value='' maxlength='50' /> <input class='button' type='submit' name='fsearch' value='".UP_LAN_12."' />";
		$userposts_forum_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_END);
		$ftext .= $userposts_forum_table_start."".$userposts_forum_table_string."".$userposts_forum_table_end;
	}
	$ns->tablerender($fcaption, $ftext);
	$ftotal = $e107->sql->total_results;
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
	$bullet = '';
	if(defined('BULLET'))
	{
		$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
	}
	elseif(file_exists(THEME.'images/bullet2.gif'))
	{
		$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
	}
	$USERPOSTS_COMMENTS_ICON		= $bullet;
	$USERPOSTS_COMMENTS_DATESTAMP	= UP_LAN_11." ".$datestamp;
	$USERPOSTS_COMMENTS_HEADING		= $row['comment_title'];
	$USERPOSTS_COMMENTS_COMMENT		= $row['comment_comment'];
	$USERPOSTS_COMMENTS_HREF_PRE	= "<a href='".$row['comment_url']."'>";
	$USERPOSTS_COMMENTS_TYPE		= $row['comment_type'];

	return(preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE));
}

?>