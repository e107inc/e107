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
 * Thread-name shortcodes that only a theme's template reaches.
 */
class forumThreadNameTest extends \Test\Unit
{
	use \Test\ForumRows;

	protected function _before()
	{
		e107::lan('forum', 'front', true);

		require_once(e_PLUGIN.'forum/forum_class.php');
	}

	protected function _after()
	{
		$this->dropForumRows();
	}

	public function testTheReplyFormCaptionPrintsAStoredNameAsText()
	{
		require_once(e_PLUGIN.'forum/shortcodes/batch/post_shortcodes.php');

		/** @var plugin_forum_post_shortcodes $sc */
		$sc = $this->make('plugin_forum_post_shortcodes');
		$sc->setVars(array(
			'action' => 'rp', 'thread_id' => 1, 'thread_name' => $this->storedMarkupName(), 'forum_sef' => 'a-forum',
		));

		$this->assertNamePrintedAsText($sc->sc_forum_post_caption());
	}

	public function testTheLastPostNamePrintsAStoredNameAsText()
	{
		global $forum;

		$this->haveForumTables();
		$category = $this->haveForum('e107help probe category');
		$forumId = $this->haveForum('e107help probe forum', $category);
		$thread = $this->haveForumThread($this->storedMarkupName(), $forumId);
		$this->haveForumPost('e107help probe post', $thread, $forumId);

		require_once(e_PLUGIN.'forum/shortcodes/batch/forum_shortcodes.php');

		/** @var forum_shortcodes $sc */
		$sc = $this->make('forum_shortcodes');
		$sc->setVars(array(
			'forum_lastpost_info' => time().'.'.$thread, 'forum_lastpost_user' => 1, 'forum_lastpost_user_anon' => '',
			'forum_sef' => 'e107help-probe-forum', 'user_name' => 'e107help probe',
		));

		$forumWas = $forum;
		$forum = new e107forum();

		try
		{
			$this->assertNamePrintedAsText($sc->sc_lastpost(array('type' => 'name')));
		}
		finally
		{
			$forum = $forumWas;
		}
	}
}
