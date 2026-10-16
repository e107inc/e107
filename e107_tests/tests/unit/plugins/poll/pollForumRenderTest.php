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
 * A poll rendered as a forum poll, the way the topic page, the new topic preview and the archived polls page render one.
 */
class pollForumRenderTest extends \Codeception\Test\Unit
{
	use \Test\PollRows;

	protected function _before()
	{
		e107::plugLan('poll', 'global');
		require_once(e_PLUGIN.'poll/poll_class.php');
	}

	public function testAPollRendersAsTheArchivedPollsPageAsksForIt()
	{
		$poll = new poll();

		$text = $poll->render_poll($this->pollRow(array('poll_id' => 9408)), 'forum', 'oldpolls', true);

		$this->assertStringContainsString('Fixture poll', $text);
	}
}
