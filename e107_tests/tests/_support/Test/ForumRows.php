<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

use e107;

/**
 * The forum rows a unit test needs, planted and taken away again.
 *
 * A forum is only readable when its category is readable too, and nothing the
 * forum renders reaches a thread whose forum it cannot read, so the smallest
 * useful fixture is a category, a forum under it, and a thread in that forum.
 */
trait ForumRows
{
	/** @var array[] table and id of every row planted, newest first */
	private $forumRows = array();

	/** @var bool whether this test installed the forum plugin for its tables */
	private $forumRowsInstalledPlugin = false;

	/**
	 * Makes sure the forum tables are there, installing the plugin if another test dropped them.
	 *
	 * @return void
	 */
	protected function haveForumTables()
	{
		if(!e107::getDb()->gen("SHOW TABLES LIKE '".MPREFIX."forum'"))
		{
			e107::getPlugin()->install('forum');
			$this->forumRowsInstalledPlugin = true;
		}
	}

	/**
	 * @param string $name
	 * @param int $parent 0 for a category, or the id one is returned under
	 * @param int $class the user class that may read it
	 * @return int the new forum id
	 */
	protected function haveForum($name, $parent = 0, $class = e_UC_PUBLIC)
	{
		return $this->haveForumRow('forum', 'forum_id', array(
			'forum_name'        => $name,
			'forum_sef'         => \eHelper::title2sef($name, 'dashl'),
			'forum_description' => '',
			'forum_parent'      => (int) $parent,
			'forum_datestamp'   => time(),
			'forum_class'       => (int) $class,
			'forum_postclass'   => (int) $class,
			'forum_threadclass' => (int) $class,
			'forum_order'       => 1,
			'forum_options'     => '',
		));
	}

	/**
	 * @param string $name
	 * @param int $forumId
	 * @param int $userId 0 for a guest, whose name is $anonName
	 * @param string $anonName
	 * @return int the new thread id
	 */
	protected function haveForumThread($name, $forumId, $userId = 1, $anonName = '')
	{
		return $this->haveForumRow('forum_thread', 'thread_id', array(
			'thread_name'          => $name,
			'thread_forum_id'      => (int) $forumId,
			'thread_active'        => 1,
			'thread_views'         => 3,
			'thread_datestamp'     => time(),
			'thread_lastpost'      => time(),
			'thread_user'          => (int) $userId,
			'thread_user_anon'     => $anonName,
			'thread_lastuser'      => (int) $userId,
			'thread_lastuser_anon' => $anonName,
			'thread_total_replies' => 0,
			'thread_options'       => '',
		));
	}

	/**
	 * @param string $entry
	 * @param int $threadId
	 * @param int $forumId
	 * @param int $userId
	 * @return int the new post id
	 */
	protected function haveForumPost($entry, $threadId, $forumId, $userId = 1)
	{
		return $this->haveForumRow('forum_post', 'post_id', array(
			'post_entry'     => $entry,
			'post_thread'    => (int) $threadId,
			'post_forum'     => (int) $forumId,
			'post_datestamp' => time(),
			'post_user'      => (int) $userId,
			'post_ip'        => '127.0.0.1',
		));
	}

	/**
	 * Drops every row planted, and the tables again if this test installed the plugin for them.
	 *
	 * @return void
	 */
	protected function dropForumRows()
	{
		$sql = e107::getDb();

		foreach($this->forumRows as $row)
		{
			$sql->delete($row['table'], $row['key']." = ".$row['id']);
		}

		$this->forumRows = array();
		$this->forgetForumPerms();

		if($this->forumRowsInstalledPlugin)
		{
			e107::getPlugin()->uninstall('forum');
			$this->forumRowsInstalledPlugin = false;
		}
	}

	/**
	 * Takes the permission list off its cache, which was built before these rows existed.
	 *
	 * @return void
	 */
	protected function forgetForumPerms()
	{
		e107::getCache()->setMD5(e_LANGUAGE.USERCLASS_LIST)->clear('forum_perms');
	}

	/**
	 * @param string $table
	 * @param string $key the table's primary key, for the tidy-up
	 * @param array $fields
	 * @return int the new row's id
	 */
	private function haveForumRow($table, $key, array $fields)
	{
		$id = e107::getDb()->insert($table, $fields);

		self::assertNotEmpty($id, "the fixture could not plant a ".$table." row");

		array_unshift($this->forumRows, array('table' => $table, 'key' => $key, 'id' => (int) $id));
		$this->forgetForumPerms();

		return (int) $id;
	}
}
