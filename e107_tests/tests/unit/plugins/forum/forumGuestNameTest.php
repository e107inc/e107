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
 * A guest names themselves when they post, and a site keeps every name stored
 * before names were converted on the way in, so each place that prints one
 * prints the stored name as text.
 */
class forumGuestNameTest extends \Test\Unit
{
	use \Test\ForumRows;

	protected function _before()
	{
		e107::lan('forum', 'front', true);

		require_once(e_PLUGIN.'forum/forum_class.php');
	}

	protected function _after()
	{
		unset($GLOBALS['forum']);
	}

	/**
	 * A thread list's starter and last poster, and a subforum's last poster.
	 */
	public function testTheThreadListPrintsAGuestsNameAsText()
	{
		require_once(e_PLUGIN.'forum/shortcodes/batch/viewforum_shortcodes.php');

		$name = $this->guestNameAsTyped();
		$row = array(
			'thread_id'                => 1,
			'thread_name'              => 'Thread',
			'thread_views'             => 1,
			'thread_total_replies'     => 1,
			'thread_lastpost'          => time(),
			'thread_user'              => 0,
			'thread_user_anon'         => $name,
			'thread_lastuser'          => 0,
			'thread_lastuser_anon'     => $name,
			'lastpost_username'        => '',
			'lastpost_id'              => 2,
			'user_name'                => '',
			'forum_sef'                => 'forum',
			'forum_lastpost_user'      => 0,
			'forum_lastpost_user_anon' => $name,
			'forum_lastpost_info'      => time().'.1',
		);

		$sc = new plugin_forum_viewforum_shortcodes();
		$sc->setVars($row);

		$this->assertGuestNamePrintedAsText($sc->sc_poster());
		$this->assertGuestNamePrintedAsText($sc->sc_lastpost());
		$this->assertGuestNamePrintedAsText($sc->sc_sub_lastpost());
	}

	/**
	 * The forum index's last post column.
	 */
	public function testTheForumIndexPrintsAGuestsNameAsText()
	{
		require_once(e_PLUGIN.'forum/shortcodes/batch/forum_shortcodes.php');

		$GLOBALS['forum'] = $this->make('e107forum', array(
			'threadGetLastpost' => array('post_thread' => 1, 'post_id' => 2, 'thread_sef' => 'thread', 'thread_name' => 'Thread'),
		));

		$sc = new forum_shortcodes();
		$sc->setVars(array(
			'forum_sef'                => 'forum',
			'forum_lastpost_info'      => time().'.1',
			'forum_lastpost_user'      => 0,
			'forum_lastpost_user_anon' => $this->guestNameAsTyped(),
			'user_name'                => '',
		));

		$this->assertGuestNamePrintedAsText($sc->sc_lastpost());
		$this->assertGuestNamePrintedAsText($sc->sc_lastpost(array('type' => 'username')));
	}

	/**
	 * Quoting a guest's post offers their name as the text it is, in the box and to the rich-text editor alike.
	 */
	public function testTheQuoteFormOffersAGuestsNameAsText()
	{
		require_once(e_PLUGIN.'forum/shortcodes/batch/post_shortcodes.php');

		$vars = array(
			'action'         => 'quote',
			'post_entry'     => 'Quoted words',
			'user_name'      => '',
			'post_user_anon' => 'Ann <b',
		);

		$sc = new plugin_forum_post_shortcodes();
		$sc->setVars($vars);
		$sc->setScVar('forum', $this->make('e107forum', array('prefs' => $this->make('e_plugin_pref', array('get' => 'bbcode')))));
		self::assertStringContainsString('[quote=Ann &lt;b]', $sc->sc_forum_post_textarea());

		$sc = new plugin_forum_post_shortcodes();
		$sc->setVars($vars);
		$editor = $sc->sc_forum_post_textarea();
		self::assertStringContainsString('[quote=Ann &amp;lt;b]', $editor);
		self::assertStringNotContainsString('[quote=Ann &lt;b]', $editor);
	}
}
