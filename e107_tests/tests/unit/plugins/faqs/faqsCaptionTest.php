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
 * Covers the optional 'caption' key of the $FAQS_TEMPLATE 'search' and 'add'
 * sections, resolved by {@see faqs_shortcodes::caption()} for issue #5983.
 */
class faqsCaptionTest extends \Test\Unit
{
	/** @var faqs_shortcodes */
	protected $sc;

	protected function _before()
	{
		require_once(e_PLUGIN . 'faqs/faqs_shortcodes.php');

		$this->sc = new faqs_shortcodes();
	}

	public function testATemplateWithoutACaptionKeepsTheConstant()
	{
		$this->assertSame('FAQ Search', $this->sc->caption(array(), 'search', 'FAQ Search'));
		$this->assertSame('FAQ Search', $this->sc->caption(array('search' => array()), 'search', 'FAQ Search'));
		$this->assertSame('FAQ', $this->sc->caption(array('add' => array('start' => '<div>')), 'add', 'FAQ'));
	}

	/**
	 * The top-level $FAQS_TEMPLATE['caption'] belongs to the listing page and
	 * must not leak into a section that defines none of its own.
	 */
	public function testTheListingCaptionIsNotReadBySections()
	{
		$tmpl = array('caption' => '{FAQ_CAPTION}');

		$this->assertSame('FAQ Search', $this->sc->caption($tmpl, 'search', 'FAQ Search'));
		$this->assertSame('FAQ', $this->sc->caption($tmpl, 'add', 'FAQ'));
	}

	/**
	 * The reason the key is read with isset() rather than a truthy check: an
	 * empty caption is the only way to ask for the wrapper without a heading.
	 */
	public function testAnEmptyCaptionIsHonouredRatherThanFallingBack()
	{
		$tmpl = array('search' => array('caption' => ''));

		$this->assertSame('', $this->sc->caption($tmpl, 'search', 'FAQ Search'));
	}

	public function testAPlainCaptionIsPassedThrough()
	{
		$tmpl = array('search' => array('caption' => 'Search the answers'));

		$this->assertSame('Search the answers', $this->sc->caption($tmpl, 'search', 'FAQ Search'));
	}

	/**
	 * The caption reaches the same shortcode surface as the neighbouring keys,
	 * which is what lets a template move a heading into the wrapper.
	 */
	public function testACaptionResolvesTheSameShortcodesAsTheBatch()
	{
		$markup = '{FAQ_CAPTION} <small>{SITENAME}</small>';
		$tmpl = array('add' => array('caption' => $markup));

		$this->assertSame(
			e107::getParser()->parseTemplate($markup, true, $this->sc),
			$this->sc->caption($tmpl, 'add', 'FAQ')
		);
	}

	/**
	 * BC control: neither key is defined by any shipped template, so every
	 * existing site still renders the caption it renders today.
	 *
	 * Read as source rather than included. {@see e107::getTemplate()} fills its
	 * registry through include_once(), so including a template file here would
	 * leave the loader holding an empty template set for the rest of the process
	 * and break whichever test next rendered an FAQ.
	 */
	public function testNoShippedFaqsTemplateDefinesASectionCaption()
	{
		$file = e_PLUGIN . 'faqs/templates/faqs_template.php';

		$this->assertFileExists($file);

		$source = file_get_contents($file);
		$assignment = '/^\s*\$FAQS_TEMPLATE\[\'%s\'\]\[\'%s\'\]/m';

		$this->assertGreaterThan(
			0,
			preg_match_all(sprintf($assignment, 'all', 'start'), $source),
			$file . ': the scan matched no section key, so it would not match a caption either'
		);

		foreach(array('search', 'add') as $section)
		{
			$this->assertSame(
				0,
				preg_match_all(sprintf($assignment, $section, 'caption'), $source),
				$file . ' defines a ' . $section . ' caption, so this change is no longer a no-op'
			);
		}
	}
}
