<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * User posts page
 *
 * $URL$
 * $Id$
 *
*/
require_once('class2.php');

e107::coreLan('userposts');

require_once(e_HANDLER.'comment_class.php');
$cobj = new comment();

$e107 = e107::getInstance();
$sql = e107::getDb();
$pref = e107::getPref();
$tp = e107::getParser();
$ns = e107::getRender();

require_once(HEADERF);

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
if(isset($_POST['fsearch']))
{
	$action = 'forums';
}

if ($action == 'exit')
{
	e107::redirect();
	exit;
}

if ($action == "comments")
{
		//$sql->db_Select("user", "user_name", "user_id=".$id);
		//$row = $sql->db_Fetch();
		if($id == e107::getUser()->getId())
		{
			$user_name = USERNAME;
		}
		else
		{
			$user_name = e107::getSystemUser($id, false)->getName(LAN_ANONYMOUS);
		}


	// new template engine - override in THEME/templates/userposts_template.php
	$USERPOSTS_TEMPLATE = e107::getCoreTemplate('userposts');

	$sql2 = e107::getDb('sql2');
	if($user_name)
	{
	//	$ccaption = UP_LAN_1.$user_name;
		$ccaption = str_replace('[x]', $user_name, UP_LAN_1);
		/*$sql->db_Select("user", "user_comments", "user_id=".$id);
		$row = $sql->db_Fetch();
		$ctotal = $row['user_comments'];*/
		$ctotal = e107::getSystemUser($id, false)->getValue('comments', 0); // user_* getter shorthand
		$data = $cobj->getCommentData(10, $from, 'comment_author_id ='.$id);
	}
	else // posts by IP currently disabled (see Query filtering - top of the page)
	{
		e107::redirect();
		exit;
		/*$dip = $id;
		if (strlen($dip) == 8)
		{  // Legacy decode (IPV4 address as it used to be stored - hex string)
		  $hexip = explode('.', chunk_split($dip, 2, '.'));
		  $dip = hexdec($hexip[0]). '.' . hexdec($hexip[1]) . '.' . hexdec($hexip[2]) . '.' . hexdec($hexip[3]);

		}
		$ccaption = UP_LAN_1.$dip;
		$data = $cobj->getCommentData($amount='10', $from, "comment_ip = '".$id."'");
		$data = $cobj->getCommentData(10, $from, 'comment_ip ='.$tp->toDB($user_ip));*/
	}

	$ctext = '';
	if(empty($data) || !is_array($data))
	{
		$ctext = "<span class='mediumtext'>".UP_LAN_7."</span>";
	}

	else
	{
		$userposts_comments_table_string = '';
		foreach($data as $row)
		{
			$userposts_comments_table_string .= parse_userposts_comments_table($row, $USERPOSTS_TEMPLATE['comments_table']);
		}

		$parms = $ctotal.",10,".$from.",".e_REQUEST_SELF."?[FROM].comments.".$id;
		$nextprev = $ctotal ? $tp->parseTemplate("{NEXTPREV={$parms}}") : '';
		if($nextprev) $nextprev = str_replace('{USERPOSTS_NEXTPREV}', $nextprev, $USERPOSTS_TEMPLATE['np_table']);
		$vars = new e_vars(array(
			'NEXTPREV' => $nextprev
		));

		// preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_TEMPLATE['comments_table_start']);
		$userposts_comments_table_start = $tp->simpleParse($USERPOSTS_TEMPLATE['comments_table_start'], $vars);
		// preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_TEMPLATE['comments_table_end'])
		$userposts_comments_table_end = $tp->simpleParse($USERPOSTS_TEMPLATE['comments_table_end'], $vars);

		$ctext .= $userposts_comments_table_start.$userposts_comments_table_string.$userposts_comments_table_end;

	}
	$ns->tablerender($ccaption, $ctext);
}



elseif ($action == 'forums')
{
	require_once (e_PLUGIN.'forum/forum_class.php');
	$forum = new e107forum();

	$forumList = implode(',', $forum->getForumPermList('view'));


	/*if(is_numeric($id))
	{
		$uinfo = e107::user($id);
		$fcaption = UP_LAN_0.' '.$uinfo['user_name'];
	}
	else
	{
		$user_name = 0;
	}*/
	if($id == e107::getUser()->getId())
	{
		$user_name = USERNAME;
	}
	else
	{
		$user_name = e107::getSystemUser($id, false)->getName(LAN_ANONYMOUS);
	}

	if(!$user_name)
	{
		e107::redirect();
		exit;
	}

//	$fcaption = UP_LAN_0.' '.$user_name;
	$fcaption = str_replace('[x]', $user_name, UP_LAN_0);
/*
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
	}*/
	// new template engine - override in THEME/templates/userposts_template.php
	$USERPOSTS_TEMPLATE = e107::getCoreTemplate('userposts');

	$s_info = '';
	$_POST['f_query'] = trim(varset($_POST['f_query']));
	if ($_POST['f_query'] !== '')
	{
		$f_query = $tp->toDB($_POST['f_query']);
		$s_info = "AND (t.thread_name REGEXP('".$f_query."') OR p.post_entry REGEXP('".$f_query."'))";
		$fcaption = UP_LAN_12.' '.$user_name;
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

	$debug = deftrue('e_DEBUG');

	$sqlp = e107::getDb('posts');

	if (!$sqlp->gen($qry))
	{
		$ftext .= "<span class='mediumtext'>".UP_LAN_8.'</span>';
	}
	else
	{
		$gen = e107::getDateConvert();
		$vars = new e_vars();

		$userposts_forum_table_string = '';
		while($row = $sqlp->fetch())
		{

			if(empty($row))
			{
				continue; 
			}

			$datestamp = $gen->convert_date($row['post_datestamp'], 'short');
			if ($row['thread_datestamp'] == $row['post_datestamp'])
			{
				$vars->USERPOSTS_FORUM_TOPIC_PRE = UP_LAN_2.': ';
			}
			else
			{
				$vars->USERPOSTS_FORUM_TOPIC_PRE = UP_LAN_15.': ';
			}


			$row['forum_sef'] = $forum->getForumSef($row);
			$row['thread_sef'] = $forum->getThreadSef($row);

			$forumUrl = e107::url('forum', 'forum', $row);

			$postNum = $forum->postGetPostNum($row['post_thread'], $row['post_id']);
			$postPage = ceil($postNum / $forum->prefs->get('postspage'));

			$postUrl = e107::url('forum', 'topic', $row, array('query' => array('p' => $postPage), 'fragment' => 'post-' . $row['post_id']));

			$vars->USERPOSTS_FORUM_ICON = "<img src='".e_PLUGIN."forum/images/".IMODE."/new_small.png' alt='' />";
			$vars->USERPOSTS_FORUM_TOPIC_HREF_PRE = "<a href='".$postUrl."'>"; //$e107->url->getUrl('forum', 'thread', "func=post&id={$row['post_id']}")
			$vars->USERPOSTS_FORUM_TOPIC = $tp->toHTML($row['thread_name'], true, 'USER_BODY', $id); 
			$vars->USERPOSTS_FORUM_NAME_HREF_PRE = "<a href='".$forumUrl."'>"; //$e107->url->getUrl('forum', 'forum', "func=view&id={$row['post_forum']}")
			$vars->USERPOSTS_FORUM_NAME = $tp->toHTML($row['forum_name'], true, 'USER_BODY', $id);
			$vars->USERPOSTS_FORUM_THREAD = $tp->toHTML($row['post_entry'], true, 'USER_BODY', $id);
			$vars->USERPOSTS_FORUM_DATESTAMP = UP_LAN_11." ".$datestamp;

			//$userposts_forum_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE);
			$userposts_forum_table_string .= $tp->simpleParse($USERPOSTS_TEMPLATE['forum_table'], $vars);
		}

		$vars->emptyVars();

		$ftotal = $sqlp->foundRows();

		$parms = $ftotal.",10,".$from.",".e_REQUEST_SELF."?[FROM].forums.".$id;
		$vars->NEXTPREV = $ftotal ? $tp->parseTemplate("{NEXTPREV={$parms}}") : '';
		if($vars->NEXTPREV) $vars->NEXTPREV =  str_replace('{USERPOSTS_NEXTPREV}', $vars->NEXTPREV, $USERPOSTS_TEMPLATE['np_table']);
		$vars->USERPOSTS_FORUM_SEARCH_VALUE = htmlspecialchars($_POST['f_query'], ENT_QUOTES, CHARSET);
		$vars->USERPOSTS_FORUM_SEARCH_FIELD = "<input class='tbox input' type='text' name='f_query' size='20' value='{$vars->USERPOSTS_FORUM_SEARCH_VALUE}' maxlength='50' />";
		$vars->USERPOSTS_FORUM_SEARCH_BUTTON = "<input class='btn btn-default btn-secondary button' type='submit' name='fsearch' value='".UP_LAN_12."' />";
		$vars->USERPOSTS_FORUM_SEARCH = "<input class='tbox' type='text' name='f_query' size='20' value='{$vars->USERPOSTS_FORUM_SEARCH_VALUE}' maxlength='50' /> <input class='btn btn-default btn-secondary button' type='submit' name='fsearch' value='".UP_LAN_12."' />";

		// $userposts_forum_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_START);
		$userposts_forum_table_start = $tp->simpleParse($USERPOSTS_TEMPLATE['forum_table_start'], $vars);
		//$userposts_forum_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_FORUM_TABLE_END);
		$userposts_forum_table_end = $tp->simpleParse($USERPOSTS_TEMPLATE['forum_table_end'], $vars);

		$ftext = $userposts_forum_table_start.$userposts_forum_table_string.$userposts_forum_table_end;
	}

	$ns->tablerender($fcaption, $ftext);
	/*$ftotal = $e107->sql->total_results;
	$parms = $ftotal.",10,".$from.",".e_SELF."?[FROM].forums.".$id;
	$USERPOSTS_NEXTPREV = $tp->parseTemplate("{NEXTPREV={$parms}}");
	echo preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_NP_TABLE);*/
}
else
{
	e107::redirect();
	exit;
}

require_once(FOOTERF);


function parse_userposts_comments_table($row, $template)
{
//	global $USERPOSTS_COMMENTS_TABLE, $pref, $gen, $tp, $id, $sql2, $comment_files;

	$gen = e107::getDateConvert();
	$datestamp = $gen->convert_date($row['comment_datestamp'], "short");
	$bullet = '';
	if(defined('BULLET'))
	{
		$bullet = '<img src="'.THEME_ABS.'images/'.BULLET.'" alt="" class="icon" />';
	}
	elseif(file_exists(THEME.'images/bullet2.gif'))
	{
		$bullet = '<img src="'.THEME_ABS.'images/bullet2.gif" alt="" class="icon" />';
	}
	$vars = new e_vars();

	$vars->USERPOSTS_COMMENTS_ICON		= $bullet;
	$vars->USERPOSTS_COMMENTS_DATESTAMP	= UP_LAN_11." ".$datestamp;
	$vars->USERPOSTS_COMMENTS_HEADING	= $row['comment_title'];
	$vars->USERPOSTS_COMMENTS_COMMENT	= $row['comment_comment'];
	$vars->USERPOSTS_COMMENTS_HREF_PRE	= "<a href='".$row['comment_url']."'>";
	$vars->USERPOSTS_COMMENTS_TYPE		= $row['comment_type'];

	//return(preg_replace("/\{(.*?)\}/e", '$\1', $USERPOSTS_COMMENTS_TABLE));
	return e107::getParser()->simpleParse($template, $vars);
}

?>
