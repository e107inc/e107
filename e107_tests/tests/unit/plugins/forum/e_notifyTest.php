<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * The forum's notifications, whose HTML body names the thread each one is about.
 */
class e_notifyTest extends \Test\Unit
{
	use \Test\ForumRows;

	/** @var int */
	private $forumId;

	/** @var int */
	private $threadId;

	/** @var int */
	private $postId;

	protected function _before()
	{
		$this->haveForumTables();

		$category = $this->haveForum('e107help probe category');
		$this->forumId = $this->haveForum('e107help probe forum', $category);
		$this->threadId = $this->haveForumThread($this->storedMarkupName(), $this->forumId);
		$this->postId = $this->haveForumPost('e107help probe post', $this->threadId, $this->forumId);

		require_once(e_HANDLER.'notify_class.php');
		require_once(e_PLUGIN.'forum/e_notify.php');
	}

	protected function _after()
	{
		$this->dropForumRows();
	}

	/**
	 * @return array each event that names a thread, and what its caller passes it
	 */
	public function events()
	{
		return array(
			array('user_forum_topic_created', 'post'),
			array('user_forum_topic_created_probationary', 'post'),
			array('user_forum_topic_moved', 'moved'),
			array('user_forum_topic_deleted', 'row'),
			array('user_forum_post_created', 'post'),
			array('user_forum_post_updated', 'post'),
			array('user_forum_post_deleted', 'thread'),
		);
	}

	/**
	 * @dataProvider events
	 * @param string $event
	 * @param string $shape
	 */
	public function testANotificationPrintsTheThreadNameAsText($event, $shape)
	{
		$message = '';
		$notify = $this->make('forum_notify', array('sendForAuthor' => function ($author, $id, $subject, $body) use (&$message)
		{
			$message = $body;
		}));
		$notify->$event($this->eventData($shape));

		$this->assertNamePrintedAsText($message);
	}

	/**
	 * @param string $shape
	 * @return array
	 */
	private function eventData($shape)
	{
		$row = array(
			'thread_id'   => $this->threadId,
			'thread_name' => $this->storedMarkupName(),
			'thread_sef'  => 'probe',
			'forum_id'    => $this->forumId,
			'forum_name'  => 'e107help probe forum',
			'forum_sef'   => 'probe',
		);

		switch($shape)
		{
			case 'post':
				return array('post_id' => $this->postId, 'thread_sef' => 'probe');

			case 'thread':
				return array('thread_id' => $this->threadId, 'post_thread' => $this->threadId, 'post_id' => $this->postId, 'post_entry' => 'e107help probe post');

			case 'moved':
				return array('old_thread' => $row, 'new_thread' => $row);
		}

		return $row;
	}
}
