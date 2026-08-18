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
 * Covers the optional 'caption' key of the $POLL_TEMPLATE 'oldpolls' section,
 * resolved by {@see poll_shortcodes::caption()} for issue #5983.
 */
class pollCaptionTest extends \Test\Unit
{
	/** @var poll_shortcodes */
	protected $sc;

	/** @var string */
	protected $pollTitle = 'Which "theme" do you prefer & why?';

	protected function _before()
	{
		require_once(e_PLUGIN . 'poll/poll_class.php');

		$this->sc = new poll_shortcodes();
		$this->sc->setVars(array(
			'poll_id'    => 42,
			'poll_title' => $this->pollTitle,
		));
	}

	public function testATemplateWithoutACaptionKeepsThePluginNameAndPollId()
	{
		$this->assertSame('Poll #42', $this->sc->caption(array(), 'oldpolls', 'Poll #42'));
		$this->assertSame('Poll #42', $this->sc->caption(array('oldpolls' => array()), 'oldpolls', 'Poll #42'));
	}

	/**
	 * The reason the key is read with isset() rather than a truthy check: an
	 * empty caption is the only way to ask for the wrapper without a heading.
	 */
	public function testAnEmptyCaptionIsHonouredRatherThanFallingBack()
	{
		$tmpl = array('oldpolls' => array('caption' => ''));

		$this->assertSame('', $this->sc->caption($tmpl, 'oldpolls', 'Poll #42'));
	}

	public function testAPlainCaptionIsPassedThrough()
	{
		$tmpl = array('oldpolls' => array('caption' => 'From the archive'));

		$this->assertSame('From the archive', $this->sc->caption($tmpl, 'oldpolls', 'Poll #42'));
	}

	public function testACaptionResolvesThePollShortcodes()
	{
		$rendered = $this->sc->caption(array('oldpolls' => array('caption' => '{QUESTION}')), 'oldpolls', 'Poll #42');

		$this->assertNotSame('', $rendered);
		$this->assertStringContainsString('theme', $rendered);
		$this->assertSame($this->sc->sc_question(), $rendered);
	}

	/**
	 * The caption reaches the same shortcode surface as 'results', which is what
	 * lets a template move a heading it already renders into the wrapper.
	 */
	public function testACaptionResolvesTheSameShortcodesAsTheResultsSection()
	{
		$markup = 'Poll: {QUESTION}';

		$this->assertSame(
			e107::getParser()->parseTemplate($markup, true, $this->sc),
			$this->sc->caption(array('oldpolls' => array('caption' => $markup)), 'oldpolls', 'Poll #42')
		);
	}

	/**
	 * BC control: the key is absent from every shipped poll template, so every
	 * existing site still renders the caption it renders today.
	 *
	 * Read as source rather than included. {@see e107::getTemplate()} fills its
	 * registry through include_once(), so including a template file here would
	 * leave the loader holding an empty template set for the rest of the process
	 * and break whichever test next rendered a poll.
	 */
	public function testNoShippedPollTemplateDefinesAnOldpollsCaption()
	{
		$file = e_PLUGIN . 'poll/templates/poll_template.php';

		$this->assertFileExists($file);

		$source = file_get_contents($file);
		$assignment = '/^\s*\$POLL_TEMPLATE\[\'%s\'\]\[\'%s\'\]/m';

		$this->assertGreaterThan(
			0,
			preg_match_all(sprintf($assignment, 'results', 'start'), $source),
			$file . ': the scan matched no section key, so it would not match a caption either'
		);
		$this->assertSame(
			0,
			preg_match_all(sprintf($assignment, 'oldpolls', 'caption'), $source),
			$file . ' defines an oldpolls caption, so this change is no longer a no-op'
		);
	}
}
