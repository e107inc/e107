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
 * The request a route reads, built from $_SERVER without touching it.
 */
class RequestTest extends \Codeception\Test\Unit
{
	public function testABareFlagIsAFlag()
	{
		self::assertTrue((new Request('GET', 'logout'))->hasFlag('logout'));
	}

	public function testAFlagMayCarryParametersOfItsOwn()
	{
		self::assertTrue((new Request('GET', 'logout&e-token=abc'))->hasFlag('logout'));
	}

	public function testAWordThatOnlyStartsWithTheFlagIsNotTheFlag()
	{
		self::assertFalse((new Request('GET', 'logoutx'))->hasFlag('logout'));
		self::assertFalse((new Request('GET', 'logout=1'))->hasFlag('logout'));
	}

	public function testTheFlagMustLeadTheQueryString()
	{
		self::assertFalse((new Request('GET', 'x&logout'))->hasFlag('logout'));
	}

	public function testAnEmptyQueryStringOrNameIsNoFlag()
	{
		self::assertFalse((new Request('GET', ''))->hasFlag('logout'));
		self::assertFalse((new Request('GET', 'logout'))->hasFlag(''));
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
		self::assertSame('logout', $request->getQueryString());
		self::assertSame('http://example.test/news.php', $request->getHeaderLine('Referer'));
		self::assertSame('https', $request->getHeaderLine('x-forwarded-proto'));
		self::assertSame('', $request->getHeaderLine('Script-Name'));
	}

	public function testCookiesAndCredentialsAreNotHeadersHere()
	{
		$request = Request::fromGlobals(array(
			'HTTP_COOKIE'        => 'e107cookie=secret',
			'HTTP_AUTHORIZATION' => 'Bearer secret',
		));

		self::assertSame('', $request->getHeaderLine('Cookie'));
		self::assertSame('', $request->getHeaderLine('Authorization'));
	}

	public function testFromGlobalsDefaultsWhatTheServerDidNotSay()
	{
		$request = Request::fromGlobals(array());

		self::assertSame('GET', $request->getMethod());
		self::assertSame('', $request->getQueryString());
		self::assertSame('', $request->getHeaderLine('Referer'));
	}
}
