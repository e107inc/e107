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
 * Covers the optional 'slideshow_caption' key of $GALLERY_TEMPLATE, resolved by
 * {@see gallery_shortcodes::slideshowCaption()} for issue #5983.
 */
class gallerySlideshowCaptionTest extends \Test\Unit
{
	/** @var gallery_shortcodes */
	protected $sc;

	protected function _before()
	{
		require_once(e_PLUGIN . 'gallery/e_shortcode.php');

		$this->sc = new gallery_shortcodes();
	}

	public function testATemplateWithoutACaptionKeepsThePluginName()
	{
		$this->assertSame('Gallery', $this->sc->slideshowCaption(array()));
		$this->assertSame('Gallery', $this->sc->slideshowCaption(array('slideshow_wrapper' => '<div></div>')));
	}

	/**
	 * The reason the key is read with isset() rather than a truthy check: an
	 * empty caption is the only way to ask for the wrapper without a heading.
	 */
	public function testAnEmptyCaptionIsHonouredRatherThanFallingBack()
	{
		$this->assertSame('', $this->sc->slideshowCaption(array('slideshow_caption' => '')));
	}

	public function testAPlainCaptionIsPassedThrough()
	{
		$this->assertSame('Our work', $this->sc->slideshowCaption(array('slideshow_caption' => 'Our work')));
	}

	/**
	 * The caption reaches the same shortcode surface as 'slideshow_wrapper',
	 * which is what lets a template move a heading into the wrapper.
	 */
	public function testACaptionResolvesTheSameShortcodesAsSlideshowWrapper()
	{
		$markup = '{SITENAME}';

		$this->assertSame(
			e107::getParser()->parseTemplate($markup),
			$this->sc->slideshowCaption(array('slideshow_caption' => $markup))
		);
	}

	/**
	 * {@see gallery_shortcodes::sc_gallery_slideshow()} lowercases the template
	 * keys before reading them, so a legacy uppercase key still resolves.
	 */
	public function testAnUppercasedKeyStillResolves()
	{
		$this->assertSame('Our work', $this->sc->slideshowCaption(array('SLIDESHOW_CAPTION' => 'Our work')));
	}

	/**
	 * BC control: the key is absent from every shipped gallery template, so
	 * every existing site still renders the caption it renders today.
	 *
	 * Read as source rather than included. {@see e107::getTemplate()} fills its
	 * registry through include_once(), so including a template file here would
	 * leave the loader holding an empty template set for the rest of the process
	 * and break whichever test next rendered a gallery.
	 */
	public function testNoShippedGalleryTemplateDefinesASlideshowCaption()
	{
		$files = array(
			e_PLUGIN . 'gallery/templates/gallery_template.php',
			e_THEME . 'bootstrap3/templates/gallery/gallery_template.php',
		);

		$assignment = '/^\s*\$GALLERY_TEMPLATE\[\'%s\'\]/m';

		foreach($files as $file)
		{
			$this->assertFileExists($file);

			$source = file_get_contents($file);

			$this->assertGreaterThan(
				0,
				preg_match_all(sprintf($assignment, 'slideshow_wrapper'), $source),
				$file . ': the scan matched no slideshow_wrapper, so it would not match a caption either'
			);
			$this->assertSame(
				0,
				preg_match_all(sprintf($assignment, 'slideshow_caption'), $source),
				$file . ' defines a slideshow_caption key, so this change is no longer a no-op'
			);
		}
	}
}
