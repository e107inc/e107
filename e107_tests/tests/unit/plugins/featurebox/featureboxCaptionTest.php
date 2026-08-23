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
 * Covers the optional 'caption' key of $FEATUREBOX_CATEGORY_TEMPLATE, resolved
 * by {@see featurebox_shortcodes::caption()} for issue #5969.
 */
class featureboxCaptionTest extends \Test\Unit
{
	/** @var featurebox_shortcodes */
	protected $sc;

	/** @var plugin_featurebox_category */
	protected $category;

	/** @var string */
	protected $categoryTitle = 'What "you" get & why';

	protected function _before()
	{
		require_once(e_PLUGIN . 'featurebox/e_shortcode.php');

		$this->sc = new featurebox_shortcodes();

		$this->category = new plugin_featurebox_category();
		$this->category->setData(array(
			'fb_category_id'    => 7,
			'fb_category_title' => $this->categoryTitle,
			'fb_category_icon'  => '{e_PLUGIN}featurebox/images/featurebox_32.png',
			'fb_category_limit' => 3,
		));
	}

	public function testATemplateWithoutACaptionKeepsThePluginName()
	{
		$this->assertSame('Feature Box', $this->sc->caption(array(), $this->category));
		$this->assertSame('Feature Box', $this->sc->caption(array('list_start' => '<div>'), $this->category));
	}

	/**
	 * The reason the key is read with isset() rather than a truthy check: an
	 * empty caption is the only way to ask for the wrapper without a heading.
	 */
	public function testAnEmptyCaptionIsHonouredRatherThanFallingBack()
	{
		$this->assertSame('', $this->sc->caption(array('caption' => ''), $this->category));
	}

	public function testACaptionResolvesTheCategoryShortcodes()
	{
		$rendered = $this->sc->caption(array('caption' => '{FEATUREBOX_CATEGORY_TITLE}'), $this->category);

		$this->assertNotSame('', $rendered);
		$this->assertStringContainsString('get', $rendered);
		$this->assertSame($this->category->toHTML('{FEATUREBOX_CATEGORY_TITLE}'), $rendered);
	}

	/**
	 * The caption reaches the same shortcode surface as 'list_start', which is
	 * what lets a template move a heading it already renders into the wrapper.
	 */
	public function testACaptionResolvesTheSameShortcodesAsListStart()
	{
		$markup = '{FEATUREBOX_CATEGORY_ICON}{FEATUREBOX_CATEGORY_TITLE}';

		$this->assertSame(
			e107::getParser()->parseTemplate($markup, true, $this->category),
			$this->sc->caption(array('caption' => $markup), $this->category)
		);
	}

	public function testAPlainCaptionIsPassedThrough()
	{
		$this->assertSame('Our services', $this->sc->caption(array('caption' => 'Our services'), $this->category));
	}

	/**
	 * BC control: the key is absent from every shipped category template, so
	 * every existing site still renders the caption it renders today.
	 *
	 * Read as source rather than included. {@see e107::getTemplate()} fills its
	 * registry through include_once(), so including a template file here would
	 * leave the loader holding an empty template set for the rest of the process
	 * and break whichever test next rendered a featurebox.
	 */
	public function testNoShippedCategoryTemplateDefinesACaption()
	{
		$files = array(
			e_PLUGIN . 'featurebox/templates/featurebox_category_template.php',
			e_THEME . '_blank/templates/featurebox/featurebox_category_template.php',
		);

		$assignment = '/^\s*\$FEATUREBOX_CATEGORY_TEMPLATE\[[^\]]+\]\[\'%s\'\]/m';

		foreach($files as $file)
		{
			$this->assertFileExists($file);

			$source = file_get_contents($file);

			$this->assertGreaterThan(
				0,
				preg_match_all(sprintf($assignment, 'list_start'), $source),
				$file . ': the scan matched no list_start, so it would not match a caption either'
			);
			$this->assertSame(
				0,
				preg_match_all(sprintf($assignment, 'caption'), $source),
				$file . ' defines a caption key, so this change is no longer a no-op'
			);
		}
	}
}
