<?php


class redirectionTest extends \Codeception\Test\Unit
{

	/** @var redirection */
	protected $rd;

	protected function _before()
	{

		try
		{
			$this->rd = $this->make('redirection');
		}

		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}

	}

	/*		public function testRedirect()
			{

			}

			public function testGetPreviousUrl()
			{

			}

			public function testGo()
			{

			}

			public function testCheckMaintenance()
			{

			}

			public function testSetPreviousUrl()
			{

			}

			public function testRedirectPrevious()
			{

			}

			public function testGetSelfExceptions()
			{

			}

			public function testGetCookie()
			{

			}

			public function testCheckMembersOnly()
			{

			}

			public function testSetCookie()
			{

			}

			public function testClearCookie()
			{

			}

			public function testGetSelf()
			{

			}*/


	public function testRedirectHost()
	{

		// List of test cases with various server setups and expected outcomes
		$testCases = [

			// Case 1: Redirect from HTTP to HTTPS

		/*	1 => [
				'server'   => [
					'HTTP_HOST'    => 'example.com',
					'SERVER_PORT'  => 80,
					'PHP_SELF'     => '/index.php',
					'QUERY_STRING' => 'foo=bar'
				],
				'prefUrl'  => 'https://example.com',
				'adminDir' => '/e107_admin', // Simulating admin area constant
				'expected' => 'https://example.com/index.php?foo=bar'
			],*/

			// Case 2: Redirect due to port mismatch (non-standard port)

			2 => [
				'server'   => [
					'HTTP_HOST'    => 'example.com',
					'SERVER_PORT'  => 80,
					'PHP_SELF'     => '',
					'QUERY_STRING' => ''
				],
				'prefUrl'  => 'https://example.com/',
				'adminDir' => '/e107_admin', // Simulating admin area constant
				'expected' => 'https://example.com'
			],

			// Case 3: Remove "www." subdomain
			3 => [
				'server'   => [
					'HTTP_HOST'    => 'www.example.com',
					'SERVER_PORT'  => 443,
					'PHP_SELF'     => '/',
					'QUERY_STRING' => ''
				],
				'prefUrl'  => 'https://example.com',
				'adminDir' => '/e107_admin', // Simulating admin area constant
				'expected' => 'https://example.com'
			],

			// Case 4: Add "www." subdomain

			4 => [
				'server'   => [
					'HTTP_HOST'    => 'example.com',
					'SERVER_PORT'  => 443,
					'PHP_SELF'     => '',
					'QUERY_STRING' => ''
				],
				'prefUrl'  => 'https://www.example.com',
				'adminDir' => '/e107_admin', // Simulating admin area constant
				'expected' => 'https://www.example.com'
			],

			// Case 5: No redirect needed (everything matches)
			5 => [
				'server'   => [
					'HTTP_HOST'    => 'example.com',
					'SERVER_PORT'  => 443,
					'PHP_SELF'     => '/home',
					'QUERY_STRING' => ''
				],
				'prefUrl'  => 'https://example.com',
				'adminDir' => '/e107_admin', // Simulating admin area constant
				'expected' => false // No redirect
			],

			// Case 6: No redirect in admin area
			6 => [
				'server'       => [
					'HTTP_HOST'    => 'example.com',
					'SERVER_PORT'  => 443,
					'PHP_SELF'     => '/e107_admin/dashboard',
					'QUERY_STRING' => ''
				],
				'prefUrl'      => 'https://example.com',
				'adminDir' => '/e107_admin', // Simulating admin area constant
				'expected'     => false // No redirect because it's an admin area
			]
		];

		foreach($testCases as $index => $testCase)
		{
			$redirectUrl = $this->rd->host($testCase['server'], $testCase['prefUrl'], $testCase['adminDir']);

			self::assertSame(
				$testCase['expected'],
				$redirectUrl,
				"Failed test case #{$index}. Expected: " . var_export($testCase['expected'], true) . " but got: " . var_export($redirectUrl, true)
			);
		}


	}

	public function testRedirectStaticDomain()
	{

		$result = $this->rd->redirectStaticDomain();
		$this->assertEmpty($result);

		$this->rd->domain = 'static1.e107.org';
		$this->rd->staticDomains = ['https://static1.e107.org', 'https://static2.e107.org'];

		$this->rd->self = 'https://static1.e107.org/blogs';
		$this->rd->siteurl = 'https://e107.org/';

		$result = $this->rd->redirectStaticDomain();

		$this->assertSame("https://e107.org/blogs", $result);

	}


	/**
	 * The predicate redirection::go() now applies to every destination it is
	 * handed. It has to be weaker than verifyDestinationUrl(): most of what the
	 * tree passes go() is relative to the document rather than to the root, and
	 * none of that can leave the site.
	 */
	public function testLeavesThisSiteAllowsEverythingRelative()
	{
		$onSite = array(
			'/news.php?extend.1',
			'news.php?extend.1',
			'e107_plugins/forum/forum.php?f=rules',
			'./e107_plugins/download/download.php?view.1',
			'../index.php',
			'?route=system/xup/login',
			'#anchor',
			SITEURL,
			SITEURL.'news.php',
			SITEURLBASE.'/news.php',
		);

		foreach($onSite as $url)
		{
			self::assertFalse($this->rd->leavesThisSite($url), "Should not count as off site: $url");
		}
	}

	public function testLeavesThisSiteCatchesEveryOffsiteSpelling()
	{
		$offSite = array(
			'https://evil.example/phish',
			'HTTP://evil.example/phish',
			'//evil.example/phish',
			'/\\evil.example/phish',
			'\\\\evil.example/phish',
			'https:/\\evil.example/phish',
			'javascript:alert(1)',
			'data:text/html;base64,PHNjcmlwdD4=',
		);

		foreach($offSite as $url)
		{
			self::assertTrue($this->rd->leavesThisSite($url), "Should count as off site: $url");
		}
	}

	/**
	 * A URL parser deletes every ASCII tab, LF and CR from its input and trims
	 * leading C0 controls and space before it looks for a scheme or an authority,
	 * so a predicate that reads character 0 of the raw string is walked by one
	 * keystroke. PHP's header() rejects only LF and CR, so the tab and the space
	 * reach the client.
	 */
	public function testLeavesThisSiteCatchesTheCharactersAUrlParserDeletes()
	{
		$offSite = array(
			"/\t/evil.example/phish",
			"\t//evil.example/phish",
			"\thttps://evil.example/phish",
			"ht\ttps://evil.example/phish",
			' //evil.example/phish',
			' https://evil.example/phish',
			"/\n/evil.example/phish",
			"\r\nhttps://evil.example/phish",
			"\x01//evil.example/phish",
			" java\tscript:alert(1)",
		);

		foreach($offSite as $url)
		{
			self::assertTrue($this->rd->leavesThisSite($url),
				'Should count as off site: '.var_export($url, true));
		}
	}

	/**
	 * The same family through the stricter predicate. A destination a visitor
	 * named has no reason to carry a character a URL parser deletes, so these are
	 * refused outright rather than normalised and followed.
	 */
	public function testVerifyDestinationUrlRefusesTheCharactersAUrlParserDeletes()
	{
		$refused = array(
			"/\t/evil.example/phish",
			" /\t/evil.example",
			"/\n/evil.example/phish",
			"\t/news.php",
			' /news.php',
			"/news.php\r\nSet-Cookie: x=1",
		);

		foreach($refused as $dest)
		{
			self::assertFalse($this->rd->verifyDestinationUrl($dest),
				'Should be refused: '.var_export($dest, true));
		}
	}

	/**
	 * Shape of the API rather than a regression: in the unit environment
	 * SITEURLBASE carries a path ("https://localhost/e107"), so the prefix test
	 * this replaced already refused a host that merely starts with the site's.
	 * The root-shaped case, where the prefix test did accept it, is measured in
	 * acceptance where SITEURLBASE has no path.
	 *
	 * @see \RedirectGoOffsiteCest::verifyDestinationUrlComparesTheHostNotThePrefix()
	 */
	public function testLeavesThisSiteComparesTheHostNotThePrefix()
	{
		$siteHost = parse_url(SITEURLBASE, PHP_URL_HOST);
		self::assertNotEmpty($siteHost);

		self::assertTrue($this->rd->leavesThisSite('https://'.$siteHost.'.evil.example/phish'));
		self::assertFalse($this->rd->verifyDestinationUrl('https://'.$siteHost.'.evil.example/phish'));
	}

}
