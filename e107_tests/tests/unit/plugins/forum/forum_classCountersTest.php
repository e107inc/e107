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
 */
class forum_classCountersTest extends \Test\Unit
{
	use \Test\ForumRows;

	const GUEST = 'e107help probe guest';

	/** @var e107forum */
	private $forum;

	/** @var int */
	private $categoryId;

	protected function _before()
	{
		$this->haveForumTables();

		require_once(e_PLUGIN.'forum/forum_class.php');
		$this->forum = new e107forum(true);

		$this->categoryId = $this->haveForum('e107help probe category');
	}

	protected function _after()
	{
		$this->dropForumRows();
	}

	public function testDeletingAThreadCountsItOffAForumWhoseReplyCountIsAlreadyLower()
	{
		$forumId = $this->haveForum('e107help probe forum', $this->categoryId);
		$threadId = $this->haveThreadWithReplies($forumId, 2);
		$this->haveForumCounts($forumId, 3, 1);

		$this->forum->threadDelete($threadId);

		self::assertSame(array(2, 0), $this->forumCounts($forumId));
	}

	public function testMovingAThreadCountsItOffAForumWhoseReplyCountIsAlreadyLower()
	{
		$fromId = $this->haveForum('e107help probe forum left', $this->categoryId);
		$toId = $this->haveForum('e107help probe forum joined', $this->categoryId);
		$threadId = $this->haveThreadWithReplies($fromId, 2);
		$this->haveForumCounts($fromId, 3, 0);

		$this->forum->threadMove($threadId, $toId);

		self::assertSame(array(2, 0), $this->forumCounts($fromId));
		self::assertSame(array(1, 2), $this->forumCounts($toId));
	}

	/**
	 * @param int $forumId
	 * @param int $replies
	 * @return int the thread id, its opening post and $replies replies planted
	 */
	private function haveThreadWithReplies($forumId, $replies)
	{
		$threadId = $this->haveForumThread('e107help probe thread', $forumId, 0, self::GUEST);

		for($post = 0; $post <= $replies; $post++)
		{
			$this->haveForumPost('e107help probe post '.$post, $threadId, $forumId, 0);
		}

		e107::getDb()->createQueryBuilder()->update('forum_thread')
			->set('thread_total_replies', $replies)
			->where('thread_id', $threadId)->execute();

		return $threadId;
	}

	/**
	 * @param int $forumId
	 * @param int $threads
	 * @param int $replies
	 * @return void
	 */
	private function haveForumCounts($forumId, $threads, $replies)
	{
		e107::getDb()->createQueryBuilder()->update('forum')
			->set('forum_threads', $threads)->set('forum_replies', $replies)
			->where('forum_id', $forumId)->execute();
	}

	/**
	 * @param int $forumId
	 * @return int[] forum_threads and forum_replies
	 */
	private function forumCounts($forumId)
	{
		$row = e107::getDb()->createQueryBuilder()
			->select('forum_threads', 'forum_replies')->from('forum')
			->where('forum_id', $forumId)->fetchRow();

		return array((int) $row['forum_threads'], (int) $row['forum_replies']);
	}
}
