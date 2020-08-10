<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once('../../class2.php');
$e107 = e107::getInstance();
if (!$e107->isInstalled('forum')) 
{
	e107::redirect('admin');
	exit;
}

$ns = e107::getRender();
$tp = e107::getParser();

require_once(e_PLUGIN.'forum/forum_class.php');
$forum = new e107forum;

e107::lan('forum', 'admin');
//include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_conf.php');

$e_sub_cat = 'forum';

if(!USER || !isset($_GET['f']) || !isset($_GET['id']))
{
	header('location:'.$e107::getUrl()->create('/'), array(), array('encode' => false, 'full' => 1));
	exit;
}

$id = (int)$_GET['id'];
$action = $_GET['f'];

$qry = "
SELECT t.*, f.*, fp.forum_id AS forum_parent_id FROM #forum_thread as t
LEFT JOIN #forum AS f ON t.thread_forum_id = f.forum_id
LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent
WHERE t.thread_id = {$thread_id}
";

$threadInfo = $forum->threadGet($id);
$modList = $forum->forumGetMods($threadInfo->forum_moderators);

//var_dump($threadInfo);
//var_dump($modList);

//If user is not a moderator of indicated forum, redirect to index page
if(!in_array(USERID, array_keys($modList)))
{
	header('location:'.$e107::getUrl()->create('/'), array(), array('encode' => false, 'full' => 1));
	exit;
}

require_once(HEADERF);

if (isset($_POST['deletepollconfirm'])) 
{
	$sql->delete("poll", "poll_id='".intval($thread_parent)."' ");
	$sql->select("forum_thread", "*", "thread_id='".$thread_id."' ");
	$row = $sql->fetch();
	 extract($row);
	$thread_name = str_replace("[poll] ", "", $thread_name);
	$sql->update("forum_thread", "thread_name='$thread_name' WHERE thread_id='$thread_id' ");
	$message = LAN_FORUM_5001;
	$url = e_PLUGIN."forum/forum_viewtopic.php?".$thread_id;
}



// Moved to forum_post.php
/*
if (isset($_POST['move']))
{
//	print_a($_POST);
	require_once(e_PLUGIN.'forum/forum_class.php');
	$forum = new e107forum;

	$newThreadTitle = '';
	if($_POST['rename_thread'] == 'add')
	{
		$newThreadTitle = '['.LAN_FORUM_5021.']';
		$newThreadTitleType = 0;
	}
	elseif($_POST['rename_thread'] == 'rename' && trim($_POST['newtitle']) != '')
	{
		$newThreadTitle = $tp->toDB($_POST['newtitle']);
		$newThreadTitleType = 1;
	}

	$threadId = $_GET['id'];
	$toForum = $_POST['forum_move'];

	$forum->threadMove($threadId, $toForum, $newThreadTitle, $newThreadTitleType);

	$message = LAN_FORUM_5005;// XXX _URL_ thread name
	$url = $e107::getUrl()->create('forum/thread/view', 'id='.$threadId);
}

if (isset($_POST['movecancel']))
{
	require_once(e_PLUGIN.'forum/forum_class.php');
	$forum = new e107forum;
	$postInfo = $forum->postGet($id, 0, 1);

	$message = LAN_FORUM_5006;
//	$url = e_PLUGIN."forum/forum_viewforum.php?".$info['forum_id'];
	$url = $e107::getUrl()->create('forum/forum/view', 'id='.$postInfo[0]['post_forum']);// XXX _URL_ thread name
}



*/



if ($message)
{
	$text = "<div style='text-align:center'>".$message."
		<br />
		<a href='$url'>".LAN_FORUM_5007.'</a>
		</div>';
	$ns->tablerender(LAN_FORUM_5008, $text);
	require_once(FOOTERF);
	exit;
}

if ($action == "delete_poll")
{
	$text = "<div style='text-align:center'>
		".LAN_FORUM_5009."
		<br /><br />
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<input class='btn btn-default btn-secondary button' type='submit' name='deletecancel' value='".LAN_CANCEL."' />
		<input class='btn btn-default btn-secondary button' type='submit' name='deletepollconfirm' value='".LAN_FORUM_5010."' />
		</form>
		</div>";
	$ns->tablerender(LAN_UI_DELETE_LABEL, $text);
	require_once(FOOTERF);
	exit;
}
/*
if ($action == 'move')
{
	$postInfo = $forum->postGet($id, 0, 1);
	
	$frm = e107::getForm();
	
	$qry = "
	SELECT f.forum_id, f.forum_name, fp.forum_name AS forum_parent, sp.forum_name AS sub_parent
	FROM `#forum` AS f
	LEFT JOIN `#forum` AS fp ON f.forum_parent = fp.forum_id
	LEFT JOIN `#forum` AS sp ON f.forum_sub = sp.forum_id
	WHERE f.forum_parent != 0
	AND f.forum_id != ".(int)$threadInfo['thread_forum_id']."
	ORDER BY f.forum_parent ASC, f.forum_sub, f.forum_order ASC
	";
	
	$sql->gen($qry);
	$fList = $sql->db_getList();
		
	$text = "
		<form class='forum-horizontal' method='post' action='".e_SELF.'?'.e_QUERY."'>
		<div style='text-align:center'>
		<table class='table table-striped' style='".ADMIN_WIDTH."'>
		<tr>
		<td>".LAN_FORUM_5019.": </td>
		<td>
		<select name='forum_move' class='tbox'>";


	foreach($fList as $f)
	{
		if(substr($f['forum_name'], 0, 1) != '*')
		{
			$f['sub_parent'] = ltrim($f['sub_parent'], '*');
			$for_name = $f['forum_parent'].' > ';
			$for_name .= ($f['sub_parent'] ? $f['sub_parent'].' > ' : '');
			$for_name .= $f['forum_name'];
			$text .= "<option value='{$f['forum_id']}'>".$for_name."</option>";
		}
	}
	$text .= "</select>
		</td>
		</tr>
		<tr>
		<td >".LAN_FORUM_5026."</td>
		<td><div class='radio'>
		".$frm->radio('rename_thread','none',true, 'label='.LAN_FORUM_5022)."
		".$frm->radio('rename_thread', 'add', false, array('label'=> LAN_ADD.' ['.LAN_FORUM_5021.'] '.LAN_FORUM_5024)). "
		<div class='form-inline'>".$frm->radio('rename_thread','rename', false, array('label'=>LAN_FORUM_5025))."
		".$frm->text('newtitle', $tp->toForm($threadInfo['thread_name']), 250)."
		</div>
		</div></td>
		</tr>
		</table>
		<div class='center'>
		<input class='btn btn-primary button' type='submit' name='move' value='".LAN_FORUM_5019."' />
		<input class='btn btn-default button' type='submit' name='movecancel' value='".LAN_CANCEL."' />
		</div>
		
		</div>
		</form>";
	
		
		$threadName = $tp->toHTML($threadInfo['thread_name'], true);
		$threadText = $tp->toHTML($postInfo[0]['post_entry'], true);
		
	$text .= "<h3>".$threadName."</h3><div>".$threadText."</div>"; // $e107->ns->tablerender(, ), '', true).$ns->tablerender('', $text, '', true);
	$ns->tablerender(LAN_FORUM_5019, $text);

}
*/




require_once(FOOTERF);
