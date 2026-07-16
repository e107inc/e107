<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit(); }
e107::includeLan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_admin.php');

function forum_thread_moderate($p)
{
	$e107 = e107::getInstance();
	$sql = e107::getDb();
	foreach ($p as $key => $val)
	{
		if (preg_match("#(.*?)_(\d+)_x#", $key, $matches))
		{
			$act = $matches[1];
			$id = (int)$matches[2];

			switch ($act)
			{
				case 'lock':
				$sql->createQueryBuilder()->update('forum_thread')
					->set('thread_active', 0)->where('thread_id', $id)->execute();
				return LAN_FORUM_CLOSE;
				break;

				case 'unlock':
				$sql->createQueryBuilder()->update('forum_thread')
					->set('thread_active', 1)->where('thread_id', $id)->execute();
				return LAN_FORUM_OPEN;
				break;

				case 'stick':
				$sql->createQueryBuilder()->update('forum_thread')
					->set('thread_sticky', 1)->where('thread_id', $id)->execute();
				return LAN_FORUM_STICK;
				break;

				case 'unstick':
				$sql->createQueryBuilder()->update('forum_thread')
					->set('thread_sticky', 0)->where('thread_id', $id)->execute();
				return LAN_FORUM_UNSTICK;
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
	$f = new e107forum;
	$ret = $f->threadDelete($threadId);
	return LAN_CANCEL.' and '.$ret.' '.FORLAN_7.'.';
}

function forumDeletePost($postId)
{
	require_once (e_PLUGIN.'forum/forum_class.php');
	$f = new e107forum;
	$ret = $f->postDelete($postId);
	return LAN_CANCEL.' and '.$ret.' '.FORLAN_7.'.';
}

