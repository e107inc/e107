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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_mod.php,v $
|     $Revision: 1.6 $
|     $Date: 2008-12-18 18:32:54 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
include_lan(e_PLUGIN.'forum/languages/English/lan_forum_admin.php');

function forum_thread_moderate($p)
{
	//	var_dump($_POST);
	//	return;
	$e107 = e107::getInstance();
	global $sql;
	foreach ($p as $key => $val)
	{
		if (preg_match("#(.*?)_(\d+)_x#", $key, $matches))
		{
			$act = $matches[1];
			//			print_a($matches); return;
			$id = (int)$matches[2];

			switch ($act)
			{
				case 'lock':
				$e107->sql->db_Update('forum_thread', 'thread_active=0 WHERE thread_id='.$id);
				return FORLAN_CLOSE;
				break;

				case 'unlock':
				$e107->sql->db_Update('forum_thread', 'thread_active=1 WHERE thread_id='.$id);
				return FORLAN_OPEN;
				break;

				case 'stick':
				$e107->sql->db_Update('forum_thread', 'thread_sticky=1 WHERE thread_id='.$id);
				return FORLAN_STICK;
				break;

				case 'unstick':
				$e107->sql->db_Update('forum_thread', 'thread_sticky=0 WHERE thread_id='.$id);
				return FORLAN_UNSTICK;
				break;

				case 'deleteThread':
				return forumDeleteThread($id);
				break;

				case 'deletePost':
				return forumDeletePost($id);
				break;

			}
		}
	}
}

function forumDeleteThread($threadId)
{
	require_once (e_PLUGIN.'forum/forum_class.php');
	$f = &new e107forum;
	$ret = $f->threadDelete($threadId);
	return FORLAN_6.' and '.$ret.' '.FORLAN_7.'.';
}

function forumDeletePost($postId)
{
	require_once (e_PLUGIN.'forum/forum_class.php');
	$f = &new e107forum;
	$ret = $f->postDelete($postId);
	return FORLAN_6.' and '.$ret.' '.FORLAN_7.'.';
}

?>