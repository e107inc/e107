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
 * The viewtopic page puts the Post Reply link and the Quick Reply submit in
 * adjacent rows, so the two need different labels. PR #3937 gave the quick
 * reply its own in 2019 but only on the plain-textarea branch, and the
 * templating of {@see plugin_forum_view_shortcodes::sc_quickreply()} carried
 * the omission across as a test of the forum's quickreply pref. Issue #5647.
 */
class forum_view_shortcodesTest extends \Test\Unit
{
	use \Test\ForumRows;

	/** @var plugin_forum_view_shortcodes */
	private $sc;

	/** @var string the forum's quickreply pref as this test found it */
	private $quickreply;

	protected function _before()
	{
		e107::lan('forum', 'front', true);

		require_once(e_PLUGIN . 'forum/shortcodes/batch/view_shortcodes.php');

		try
		{
			$this->sc = $this->make('plugin_forum_view_shortcodes');
		}
		catch(Exception $e)
		{
			self::fail($e->getMessage());
		}

		$this->sc->setVars(array('thread_forum_id' => 1, 'thread_id' => 2));

		$this->quickreply = e107::getPlugConfig('forum')->get('quickreply', 'default');
	}

	protected function _after()
	{
		e107::getPlugConfig('forum')->set('quickreply', $this->quickreply);
	}

	/**
	 * The editor a forum picks for the quick reply box says nothing about what
	 * the button beside it should read.
	 */
	public function testQuickReplyLabelDiffersFromPostReplyOnEveryEditor()
	{
		foreach(array('default', 'wysiwyg') as $editor)
		{
			e107::getPlugConfig('forum')->set('quickreply', $editor);

			$button = $this->sc->sc_qr_sbutton();

			self::assertStringContainsString("value='" . LAN_FORUM_2007 . "'", $button,
				"quickreply=" . $editor . ": the quick reply submit should carry its own label.");
			self::assertStringNotContainsString("value='" . LAN_FORUM_2006 . "'", $button,
				"quickreply=" . $editor . ": the quick reply submit repeats the Post Reply label.");
		}
	}

	/**
	 * A theme that passes {QR_SBUTTON: value=...} still names the button.
	 */
	public function testTemplateSuppliedLabelStillWins()
	{
		$button = $this->sc->sc_qr_sbutton(array('value' => 'Send it'));

		self::assertStringContainsString("value='Send it'", $button);
	}

	/**
	 * Post Reply can only keep what was typed into the quick reply box if the
	 * click can be caught, which is what the action attribute is for.
	 */
	public function testPostReplyIsAnActionWherePostingIsAllowed()
	{
		$buttons = $this->buttonsx(true);

		self::assertStringContainsString("data-forum-action='postreply'", $buttons);
	}

	/**
	 * A forum this visitor cannot post in offers a dead button, and a dead
	 * button has nothing to carry anywhere.
	 */
	public function testPostReplyIsNoActionWherePostingIsRefused()
	{
		$buttons = $this->buttonsx(false);

		self::assertStringNotContainsString('data-forum-action', $buttons);
	}

	/**
	 * A guest's name stored as typed shows its markup as text.
	 */
	public function testAGuestsNameStoredAsTypedPrintsAsText()
	{
		foreach($this->guestNameShortcodes($this->guestNameAsTyped()) as $printed)
		{
			$this->assertGuestNamePrintedAsText($printed);
		}
	}

	/**
	 * A guest's name stored converted prints as stored, not encoded a second time.
	 */
	public function testAGuestsNameStoredConvertedPrintsAsStored()
	{
		foreach($this->guestNameShortcodes('Ann &amp; Co &lt;3') as $shortcode => $printed)
		{
			self::assertStringContainsString('Ann &amp; Co &lt;3', $printed, $shortcode);
		}
	}

	/**
	 * @param string $stored a guest's name as the columns hold it
	 * @return string[] what each shortcode that names a guest prints, keyed by shortcode
	 */
	private function guestNameShortcodes($stored)
	{
		$this->sc->setScVar('postInfo', array('thread_user_anon' => $stored, 'post_user_anon' => $stored));
		$this->sc->setVars(array('thread_views' => 1, 'thread_total_replies' => 1, 'thread_lastuser_anon' => $stored));

		$printed = array();

		foreach(array('sc_topic_author_name', 'sc_topic_lastpost_author', 'sc_poster') as $shortcode)
		{
			$printed[$shortcode] = $this->sc->$shortcode();
		}

		return $printed;
	}

	/**
	 * {@see plugin_forum_view_shortcodes::sc_buttonsx()} as a viewtopic page
	 * reaches it: a thread the visitor may or may not post in, and no
	 * neighbouring threads to link to.
	 *
	 * @param bool $mayPost
	 * @return string
	 */
	private function buttonsx($mayPost)
	{
		require_once(e_PLUGIN . 'forum/forum_class.php');

		$this->sc->forum = $this->make('e107forum', array(
			'checkPerm'         => $mayPost,
			'threadGetNextPrev' => false,
			'forumGetAllowed'   => array(),
		));

		$this->sc->setVars(array(
			'thread_forum_id' => 1,
			'thread_id'       => 2,
			'thread_active'   => 1,
			'thread_lastpost' => 0,
			'forum_id'        => 1,
		));

		$GLOBALS['thread'] = (object) array('threadId' => 2);

		try
		{
			return $this->sc->sc_buttonsx();
		}
		finally
		{
			unset($GLOBALS['thread']);
		}
	}
}
