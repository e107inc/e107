<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Theme;

/**
 * The body waits for render(), because the theme header sets the table style it is drawn in.
 */
class PageTest extends \Test\Unit
{
	public function testTheBodyIsNotRenderedUntilAskedFor()
	{
		$calls = 0;
		$page = new Page('Title', function () use (&$calls) { $calls++; return 'body'; });

		self::assertSame('Title', $page->getTitle());
		self::assertSame(0, $calls);

		self::assertSame('body', $page->render());
		self::assertSame(1, $calls);
	}

	public function testAPageWithNoCaptionIsDrawnAsItIs()
	{
		$page = $this->page();

		self::assertNull($page->getCaption());
		self::assertSame('default', $page->getMode());
	}

	public function testACaptionComesOnACopyAndLeavesTheOriginalUnboxed()
	{
		$page = $this->page();

		$boxed = $page->withCaption('Caption', 'logout');

		self::assertNotSame($page, $boxed);
		self::assertSame('Caption', $boxed->getCaption());
		self::assertSame('logout', $boxed->getMode());
		self::assertSame('Title', $boxed->getTitle());
		self::assertSame('body', $boxed->render());
		self::assertNull($page->getCaption());
	}

	public function testACaptionIsDrawnInTheDefaultModeUnlessToldOtherwise()
	{
		self::assertSame('default', $this->page()->withCaption('Caption')->getMode());
	}

	/**
	 * @return Page
	 */
	private function page()
	{
		return new Page('Title', function () { return 'body'; });
	}
}
