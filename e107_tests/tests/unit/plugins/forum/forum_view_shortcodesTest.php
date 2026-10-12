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
 * The viewtopic page puts the Post Reply link and the Quick Reply box in
 * adjacent rows, so a mis-click on the link used to drop everything typed.
 * Issue #5647.
 */
class forum_view_shortcodesTest extends \Codeception\Test\Unit
{
	use \Test\ForumRows;

	/** @var plugin_forum_view_shortcodes */
	private $sc;

	/** @var array hide.bb's globals as this test found them, null for one that was not set */
	private $hideGlobals = array();

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
	}

	/**
	 * The latest-posts menu keeps what it renders for a minute and shows it to
	 * every viewer of the same address with the same classes, so its excerpt
	 * leaves a [hide] block out even where the page has revealed one to its own
	 * viewer.
	 *
	 * @dataProvider hiddenBlocks
	 * @param string $entry
	 */
	public function testTheLatestPostsExcerptLeavesOutAHiddenBlock($entry)
	{
		require_once(e_PLUGIN . 'forum/forum_class.php');

		$this->sc->setVars(array('post_entry' => $entry));
		$this->revealHiddenBlocks();

		try
		{
			$page = e107::getParser()->toHTML($entry, true);
			$excerpt = $this->sc->sc_post_content(array('truncate' => 500));
		}
		finally
		{
			$this->restoreHiddenBlocks();
		}

		self::assertStringContainsString('SECRET', $page, 'the page did not reveal the block, so nothing was measured');
		self::assertStringContainsString('before', $excerpt);
		self::assertStringContainsString('after', $excerpt);
		self::assertStringNotContainsString('SECRET', $excerpt);
	}

	/**
	 * Each spelling the topic page takes for a [hide] block, among them blocks
	 * holding a tag the page reads differently from how it is spelt.
	 *
	 * @return array
	 */
	public function hiddenBlocks()
	{
		return array(
			'plain'                           => array('before [hide]SECRET[/hide] after'),
			'a parameter'                     => array('before [hide=x]SECRET[/hide] after'),
			'numbered, across lines'          => array("before\n[hide2]\nSECRET\n[/hide2]\nafter"),
			'nested, stored as HTML'          => array('[html]<p>before [hide]a [hide]b[/hide] SECRET[/hide] after</p>[/html]'),
			'in a post stored as HTML'        => array('[html]<p>before [hide]SECRET[/hide] after</p>[/html]'),
			'upper case, stored as HTML'      => array('[html]<p>before [HIDE=x]SECRET[/HIDE] after</p>[/html]'),
			'a closer inside [code] inside'   => array('[html]<p>before [hide]type [code][/hide][/code] SECRET[/hide] after</p>[/html]'),
			'an opener inside another tag'    => array('before [hide]SECRET [b=[hide]x[/b][/hide] after'),
			'a newline inside an opener'      => array("before [hide]SECRET [hide\n]x[/hide] after"),
			'a closer inside another tag'     => array('[html]<p>before [hide]x [b=[/hide] SECRET [/hide] after</p>[/html]'),
			'an opener inside a closer'       => array('before [/hide x[hide]SECRET[/hide] after'),
		);
	}

	/**
	 * The topic page prints a box where a block was, so the excerpt keeps the
	 * text either side of it apart rather than joining the two into one word.
	 */
	public function testTheLatestPostsExcerptKeepsTheTextEitherSideOfAHiddenBlockApart()
	{
		require_once(e_PLUGIN . 'forum/forum_class.php');

		$this->sc->setVars(array('post_entry' => 'before[hide]SECRET[/hide]after'));

		$excerpt = $this->sc->sc_post_content(array('truncate' => 500));

		self::assertStringContainsString('before after', $excerpt);
		self::assertStringNotContainsString('SECRET', $excerpt);
	}

	/**
	 * A [hide] that is never closed is printed as typed on the topic page, and
	 * the text after it is not a hidden block.
	 */
	public function testTheLatestPostsExcerptKeepsTheTextAfterAnUnclosedHide()
	{
		require_once(e_PLUGIN . 'forum/forum_class.php');

		$this->sc->setVars(array('post_entry' => 'before [hide] after'));

		self::assertStringContainsString('after', $this->sc->sc_post_content(array('truncate' => 500)));
	}

	/**
	 * Set hide.bb's globals as the topic page sets them for a viewer who has
	 * replied, with a query that finds a row.
	 */
	private function revealHiddenBlocks()
	{
		$reveal = array(
			'sql'            => e107::getDb(),
			'e_hide_query'   => 'SELECT 1',
			'e_hide_hidden'  => 'hidden',
			'e_hide_allowed' => true,
		);

		foreach($reveal as $name => $value)
		{
			$this->hideGlobals[$name] = array_key_exists($name, $GLOBALS) ? array($GLOBALS[$name]) : null;
			$GLOBALS[$name] = $value;
		}
	}

	/**
	 * Put hide.bb's globals back as {@see revealHiddenBlocks()} found them.
	 */
	private function restoreHiddenBlocks()
	{
		foreach($this->hideGlobals as $name => $was)
		{
			if($was === null)
			{
				unset($GLOBALS[$name]);
				continue;
			}

			$GLOBALS[$name] = $was[0];
		}
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
