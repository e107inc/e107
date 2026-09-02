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
}
