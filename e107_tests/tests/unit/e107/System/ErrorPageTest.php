<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\System;

use e107\Http\Request;
use e107\Http\Response;

/**
 * An error page answers with its status and is drawn in the box themes already style for that status.
 */
class ErrorPageTest extends \Test\Unit
{
	public function testTheResponseCarriesTheStatusThePageIsFor()
	{
		foreach(array(403, 404, 500) as $status)
		{
			$response = $this->respond($status);

			self::assertSame($status, $response->getStatusCode());
			self::assertFalse($response->isRedirect());
		}
	}

	public function testTheThemeDrawsThePageInOneBoxInTheModeOfItsStatus()
	{
		$page = $this->respond(404)->getPage();

		self::assertSame('Error', $page->getTitle());
		self::assertSame('Error', $page->getCaption());
		self::assertSame('error_page_404', $page->getMode());
		self::assertSame('error_page_500', $this->respond(500)->getPage()->getMode());
	}

	public function testTheBodyIsRenderedForTheStatusAndOnlyWhenAskedFor()
	{
		$rendered = array();
		$page = $this->respond(403, $rendered)->getPage();
		self::assertSame(array(), $rendered);

		self::assertSame('body for 403', $page->render());
		self::assertSame(array(403), $rendered);
	}

	public function testAStatusThatIsNotAnErrorIsRefused()
	{
		foreach(array(200, 302, 399, 600) as $status)
		{
			try
			{
				new ErrorPage($status, 'Error', function () { return ''; });
			}
			catch(\InvalidArgumentException $refused)
			{
				self::assertStringContainsString((string) $status, $refused->getMessage());

				continue;
			}

			self::fail('The status '.$status.' was accepted.');
		}
	}

	/**
	 * @param int $status
	 * @param array $rendered collects the status of each body rendered
	 * @return Response
	 */
	private function respond($status, array &$rendered = array())
	{
		$handler = new ErrorPage($status, 'Error', function ($statusCode) use (&$rendered)
		{
			$rendered[] = $statusCode;

			return 'body for '.$statusCode;
		});

		return $handler->handle(new Request('GET', ''));
	}
}
