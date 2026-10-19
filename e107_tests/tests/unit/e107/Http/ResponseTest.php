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
		$page = $this->page();
		$response = Response::page($page);

		self::assertSame(200, $response->getStatusCode());
		self::assertSame($page, $response->getPage());
		self::assertFalse($response->isRedirect());
		self::assertSame('', $response->getHeaderLine('Location'));
	}

	public function testARedirectAnswersWithItsLocationAndNoPage()
	{
		$response = Response::redirect('http://example.test/');

		self::assertSame(303, $response->getStatusCode());
		self::assertTrue($response->isRedirect());
		self::assertSame('http://example.test/', $response->getHeaderLine('Location'));
		self::assertNull($response->getPage());
	}

	public function testHeaderNamesAreCaseInsensitive()
	{
		$response = Response::redirect('/');

		self::assertSame('/', $response->getHeaderLine('location'));
		self::assertSame('/', $response->getHeaderLine('LOCATION'));
		self::assertSame('', $response->getHeaderLine('Cache-Control'));
	}

	public function testEveryRedirectStatusIsAccepted()
	{
		foreach(array(301, 302, 303, 307, 308) as $status)
		{
			self::assertSame($status, Response::redirect('/', $status)->getStatusCode());
		}
	}

	public function testARedirectRefusesAnEmptyUrl()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('A redirect needs a URL.');

		Response::redirect('');
	}

	public function testARedirectRefusesAStatusThatIsNotARedirect()
	{
		foreach(array(200, 201, 304) as $status)
		{
			$this->assertRefused(function () use ($status) { Response::redirect('/', $status); }, $status);
		}
	}

	public function testAPageRefusesARedirectOrABodilessStatus()
	{
		foreach(array(100, 204, 205, 302, 304) as $status)
		{
			$this->assertRefused(function () use ($status) { Response::page($this->page(), $status); }, $status);
		}
	}

	public function testAPageMayCarryAnErrorStatus()
	{
		self::assertSame(404, Response::page($this->page(), 404)->getStatusCode());
	}

	public function testAStatusOutsideHttpIsRefused()
	{
		$this->assertRefused(function () { Response::page($this->page(), 600); }, 600);
	}

	/**
	 * @return Page
	 */
	private function page()
	{
		return new Page('Title', function () { return 'body'; });
	}

	/**
	 * @param callable $build
	 * @param int $status
	 * @return void
	 */
	private function assertRefused(callable $build, $status)
	{
		try
		{
			call_user_func($build);
		}
		catch(\InvalidArgumentException $refused)
		{
			self::assertStringContainsString((string) $status, $refused->getMessage());

			return;
		}

		self::fail('The status '.$status.' was accepted.');
	}
}
