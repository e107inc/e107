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

/**
 * The request core reads, built from $_SERVER without touching it.
 */
class RequestTest extends \Codeception\Test\Unit
{
	protected function _before()
	{
		require_once(__DIR__.'/attribute_echo_handler.php');
	}

	public function testABareQueryWordLeads()
	{
		self::assertTrue((new Request('GET', 'logout'))->hasLeadingQueryWord('logout'));
	}

	public function testALeadingQueryWordMayCarryParametersOfItsOwn()
	{
		self::assertTrue((new Request('GET', 'logout&e-token=abc'))->hasLeadingQueryWord('logout'));
	}

	public function testAWordThatOnlyStartsWithTheQueryWordIsNotIt()
	{
		self::assertFalse((new Request('GET', 'logoutx'))->hasLeadingQueryWord('logout'));
		self::assertFalse((new Request('GET', 'logout=1'))->hasLeadingQueryWord('logout'));
	}

	public function testTheQueryWordMustLeadTheQueryString()
	{
		self::assertFalse((new Request('GET', 'x&logout'))->hasLeadingQueryWord('logout'));
	}

	public function testAnEmptyQueryStringOrWordLeadsWithNothing()
	{
		self::assertFalse((new Request('GET', ''))->hasLeadingQueryWord('logout'));
		self::assertFalse((new Request('GET', 'logout'))->hasLeadingQueryWord(''));
	}

	public function testFromGlobalsReadsTheMethodQueryAndHeaders()
	{
		$request = Request::fromGlobals(array(
			'REQUEST_METHOD'  => 'post',
			'QUERY_STRING'    => 'logout',
			'HTTP_REFERER'    => 'http://example.test/news.php',
			'HTTP_X_FORWARDED_PROTO' => 'https',
			'SCRIPT_NAME'     => '/index.php',
		));

		self::assertSame('POST', $request->getMethod());
		self::assertTrue($request->hasLeadingQueryWord('logout'));
		self::assertSame('http://example.test/news.php', $request->getHeaderLine('Referer'));
		self::assertSame('https', $request->getHeaderLine('x-forwarded-proto'));
		self::assertSame('', $request->getHeaderLine('Script-Name'));
	}

	public function testTheContentHeadersArriveWithoutTheHttpPrefix()
	{
		$request = Request::fromGlobals(array(
			'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
			'CONTENT_LENGTH' => '12',
		));

		self::assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
		self::assertSame('12', $request->getHeaderLine('Content-Length'));
	}

	public function testCookiesAndCredentialsAreNotHeadersHere()
	{
		$request = Request::fromGlobals(array(
			'HTTP_COOKIE'              => 'e107cookie=secret',
			'HTTP_AUTHORIZATION'       => 'Bearer secret',
			'HTTP_PROXY_AUTHORIZATION' => 'Basic secret',
		));

		self::assertSame('', $request->getHeaderLine('Cookie'));
		self::assertSame('', $request->getHeaderLine('Authorization'));
		self::assertSame('', $request->getHeaderLine('Proxy-Authorization'));
	}

	public function testAHeaderValueIsReadBackAsAString()
	{
		$request = Request::fromGlobals(array('CONTENT_LENGTH' => 12));

		self::assertSame('12', $request->getHeaderLine('Content-Length'));
	}

	public function testFromGlobalsDefaultsWhatTheServerDidNotSay()
	{
		$request = Request::fromGlobals(array());

		self::assertSame('GET', $request->getMethod());
		self::assertFalse($request->hasLeadingQueryWord('logout'));
		self::assertSame('', $request->getHeaderLine('Referer'));
	}

	public function testAHandlerReadsWhatTheRouteCapturedFromTheRequestAlone()
	{
		$request = new Request('GET', '');
		$routed = $request->withAttribute('id', 42);

		$handler = new AttributeEchoHandler('id');

		self::assertSame('42', $handler->handle($routed)->getPage()->render());
		self::assertNull($request->getAttribute('id'));
	}

	public function testACopyWithAnAttributeKeepsTheRequestsHeaders()
	{
		$routed = (new Request('GET', '', array('Referer' => 'http://example.test/')))->withAttribute('id', 42);

		self::assertSame('http://example.test/', $routed->getHeaderLine('referer'));
	}

	public function testAnAttributeTheRouteDidNotCaptureFallsBackToTheDefault()
	{
		$request = (new Request('GET', ''))->withAttribute('present', null);

		self::assertSame('fallback', $request->getAttribute('absent', 'fallback'));
		self::assertNull($request->getAttribute('present', 'fallback'));
	}
}
