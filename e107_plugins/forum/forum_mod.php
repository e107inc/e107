<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_mod.php,v $
 * $Revision: 1.8 $
 * $Date: 2009-11-18 01:05:36 $
 * $Author: e107coders $
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