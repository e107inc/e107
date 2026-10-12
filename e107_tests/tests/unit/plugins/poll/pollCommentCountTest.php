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
class pollCommentCountTest extends \Test\Unit
{
	const POLL_ID = 9407;

	/** @var int[] */
	private $comments = array();

	protected function _before()
	{
		require_once(e_PLUGIN.'poll/poll_class.php');
	}

	protected function _after()
	{
		e107::getDb()->createQueryBuilder()->delete('comments')->whereIn('comment_id', $this->comments)->execute();
	}

	/**
	 * A comment's type is stored as text, and a plugin's type that only starts with a poll's number is not a poll's.
	 */
	public function testAPollCountsOnlyTheCommentsLeftOnPolls()
	{
		foreach(array('4', '4', '4gallery') as $type)
		{
			$this->comments[] = e107::getDb()->createQueryBuilder()->insert('comments')->insertGetId(array(
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
		$text = $poll->render_poll(array(
			'poll_id'              => self::POLL_ID,
			'poll_datestamp'       => time(),
			'poll_start_datestamp' => 0,
			'poll_end_datestamp'   => 0,
			'poll_admin_id'        => 1,
			'poll_title'           => 'Fixture poll',
			'poll_options'         => "Yes\x01No\x01",
			'poll_votes'           => "1\x011",
			'poll_ip'              => '',
			'poll_type'            => 1,
			'poll_comment'         => 1,
			'poll_allow_multiple'  => 0,
			'poll_result_type'     => 0,
			'poll_vote_userclass'  => 0,
			'poll_storage_method'  => 0,
		), 'menu', 'oldpolls', true);

		$this->assertStringContainsString(LAN_COMMENTS.': 2</a>', $text);
	}
}
