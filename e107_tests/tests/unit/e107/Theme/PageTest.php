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
class PageTest extends \Codeception\Test\Unit
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
}
