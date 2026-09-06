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
 * The forum statistics page works out its top repliers by taking each
 * poster's thread-opening posts off their post count. The two figures come
 * from two separate queries keyed on user id, and the second set is sparse:
 *
 *   SELECT COUNT(fp.post_id) AS post_count, u.user_name, u.user_id, ...
 *     FROM #forum_post as fp LEFT JOIN #user AS u ON fp.post_user = u.user_id
 *
 *   SELECT COUNT(ft.thread_id) AS thread_count, u.user_id
 *     FROM #forum_thread as ft LEFT JOIN #user AS u ON ft.thread_user = u.user_id
 *
 * A member who has only ever replied appears in the first and not the second.
 * A post whose author has since been deleted is keyed on the empty string,
 * because the LEFT JOIN hands back NULL for the user id, and no thread count
 * is ever keyed that way either.
 */
class forumStatsTest extends \Test\Unit
{
	/** @var forumStats */
	private $stats;

	public function _before()
	{
		if(!class_exists('forumStats', false))
		{
			// The page file defines the class and then renders the page, the
			// way pluginsTest loads it. Only the class is wanted here.
			e107::getConfig()->setPref('plug_installed/forum', '2.0');

			ob_start();
			require_once(e_PLUGIN.'forum/forum_stats.php');
			ob_end_clean();
		}

		$this->stats = new forumStats();
	}

	/**
	 * A member with posts but no threads has no thread count to subtract.
	 * Reading one anyway warned, and on PHP 8 the warning is loud.
	 */
	public function testAMemberWhoOnlyEverRepliedKeepsEveryPost()
	{
		$posters = array(
			7 => array('user_id' => 7, 'user_name' => 'replier', 'post_count' => 30),
			8 => array('user_id' => 8, 'user_name' => 'starter', 'post_count' => 20),
		);

		// Only user 8 has ever opened a thread.
		$threadCounts = array(
			8 => array('user_id' => 8, 'thread_count' => 12),
		);

		$result = $this->stats->buildTopRepliers($posters, $threadCounts, 100);

		$this->assertCount(2, $result);
		$this->assertEquals(7, $result[0]['user_id'],
			'30 replies beats 8, so the replier leads.');
		$this->assertEquals(30, $result[0]['user_forums'],
			'No threads means nothing to take off.');
		$this->assertEquals(30, $result[0]['percentage']);
		$this->assertEquals(8, $result[1]['user_forums'],
			'20 posts less 12 thread openings is 8 replies.');
		$this->assertEquals(8, $result[1]['percentage']);
	}

	/**
	 * Delete a member and their posts stay behind. The LEFT JOIN returns NULL
	 * for the author, which becomes the empty string once the result set is
	 * keyed on it, and that key is never present in the thread counts.
	 */
	public function testPostsByADeletedMemberDoNotDerailTheTable()
	{
		$posters = array(
			''  => array('user_id' => null, 'user_name' => null, 'post_count' => 9),
			8   => array('user_id' => 8, 'user_name' => 'starter', 'post_count' => 20),
		);

		$threadCounts = array(
			8 => array('user_id' => 8, 'thread_count' => 12),
		);

		$result = $this->stats->buildTopRepliers($posters, $threadCounts, 100);

		$this->assertCount(2, $result);
		$this->assertEquals(9, $result[0]['user_forums'],
			'The orphaned posts are still replies and still count.');
		$this->assertEquals(8, $result[1]['user_forums']);
	}

	/**
	 * The ordinary case still has to come out right.
	 */
	public function testThreadOpeningPostsAreTakenOffAndTheRestSorted()
	{
		$posters = array(
			1 => array('user_id' => 1, 'post_count' => 50),
			2 => array('user_id' => 2, 'post_count' => 40),
			3 => array('user_id' => 3, 'post_count' => 30),
		);

		$threadCounts = array(
			1 => array('thread_count' => 45),
			2 => array('thread_count' => 5),
			3 => array('thread_count' => 0),
		);

		$result = $this->stats->buildTopRepliers($posters, $threadCounts, 70);

		$this->assertEquals(array(35, 30, 5), array(
			$result[0]['user_forums'],
			$result[1]['user_forums'],
			$result[2]['user_forums'],
		), 'Sorted on replies, not on posts.');

		$this->assertEquals(50, $result[0]['percentage'], '35 of 70 replies is 50%.');
	}

	/**
	 * A forum whose threads have never been replied to has posts but no
	 * replies, so the share of all replies has no denominator. On PHP 8 that
	 * is a DivisionByZeroError, which takes the whole page down.
	 *
	 * @see e107_tests/tests/acceptance/0052_ForumFeedParentClassCest.php,
	 *      whose fixture has to plant a reply to get past this.
	 */
	public function testAForumWithNoRepliesYetStillRenders()
	{
		$posters = array(
			1 => array('user_id' => 1, 'post_count' => 3),
			2 => array('user_id' => 2, 'post_count' => 1),
		);

		$threadCounts = array(
			1 => array('thread_count' => 3),
			2 => array('thread_count' => 1),
		);

		$result = $this->stats->buildTopRepliers($posters, $threadCounts, 0);

		$this->assertCount(2, $result);
		$this->assertEquals(0, $result[0]['user_forums']);
		$this->assertEquals(0, $result[0]['percentage'],
			'No replies at all is 0%, not a fatal error.');
	}
}
