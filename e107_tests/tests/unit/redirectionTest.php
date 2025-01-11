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


}
