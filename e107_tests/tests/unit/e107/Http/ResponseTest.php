<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Http;

use e107\Theme\Page;

/**
 * A response is a page or a redirect, never both, and always carries a real status.
 */
class ResponseTest extends \Test\Unit
{
	public function testAPageAnswersWithItsPageAndNoLocation()
	{
		$page = new Page('Title', function () { return 'body'; });
		$response = Response::page($page);

		self::assertSame(200, $response->getStatusCode());
		self::assertSame($page, $response->getPage());
		self::assertNull($response->getLocation());
	}

	public function testARedirectAnswersWithItsLocationAndNoPage()
	{
		$response = Response::redirect('http://example.test/');

		self::assertSame(303, $response->getStatusCode());
		self::assertSame('http://example.test/', $response->getLocation());
		self::assertNull($response->getPage());
	}

	public function testTheStatusCodeCanBeChosen()
	{
		self::assertSame(302, Response::redirect('/', 302)->getStatusCode());
		self::assertSame(404, Response::page(new Page('', function () { return ''; }), 404)->getStatusCode());
	}

	public function testAStatusOutsideHttpIsRefused()
	{
		$this->expectException(\InvalidArgumentException::class);

		Response::redirect('/', 600);
	}
}
