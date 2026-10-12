<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e_parse::toGlyph() with embed set reads the icon's SVG from the Font Awesome folder.
 */
class e_parseGlyphEmbedTest extends \Test\Unit
{
	public function testAnEmbeddedIconCannotReadAFileOutsideTheIconFolder()
	{
		$this->assertFileExists(e_PLUGIN.'hero/images/placeholder-1.svg');

		$html = e107::getParser()->toGlyph('fas-../../../../../../e107_plugins/hero/images/placeholder-1', array('embed' => 1));

		$this->assertStringNotContainsString('<svg', $html);
	}

	public function testAnEmbeddedIconStillReadsTheIconFolder()
	{
		$this->writeAppFile('e107_web/lib/font-awesome/5/svgs/solid/star.svg', '<svg viewBox="0 0 576 512"></svg>');

		$html = e107::getParser()->toGlyph('fas-star', array('embed' => 1));

		$this->assertStringContainsString('<svg class="svg-inline--fa fa-star', $html);
	}
}
