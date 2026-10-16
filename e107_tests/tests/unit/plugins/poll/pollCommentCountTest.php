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
 * The comment count a poll prints with its results.
 */
class pollCommentCountTest extends \Codeception\Test\Unit
{
	use \Test\PollRows;

	const POLL_ID = 9407;

	/** @var int[] */
	private $comments = array();

	protected function _before()
	{
		require_once(e_PLUGIN.'poll/poll_class.php');
	}

	protected function _after()
	{
		foreach($this->comments as $id)
		{
			e107::getDb()->delete('comments', 'comment_id = '.(int) $id);
		}
	}

	/**
	 * A comment's type is stored as text, and a plugin's type that only starts with a poll's number is not a poll's.
	 */
	public function testAPollCountsOnlyTheCommentsLeftOnPolls()
	{
		foreach(array('4', '4', '4gallery') as $type)
		{
			$this->comments[] = e107::getDb()->insert('comments', array(
				'comment_pid'          => 0,
				'comment_item_id'      => self::POLL_ID,
				'comment_subject'      => 'Subject',
				'comment_author_id'    => 1,
				'comment_author_name'  => 'fixture',
				'comment_author_email' => '',
				'comment_datestamp'    => time(),
				'comment_comment'      => 'Text',
				'comment_blocked'      => 0,
				'comment_ip'           => '127.0.0.1',
				'comment_type'         => $type,
				'comment_lock'         => 0,
				'comment_share'        => 0,
			));
		}

		$poll = new poll();
		$text = $poll->render_poll($this->pollRow(array('poll_id' => self::POLL_ID)), 'menu', 'oldpolls', true);

		$this->assertStringContainsString(LAN_COMMENTS.': 2</a>', $text);
	}
}
