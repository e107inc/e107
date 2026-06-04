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


	public function testIsCapturable()
	{
		$capturable = array(
			'/news.php?extend.1',
			'/index.php',
			'/',
			'/blog/my-post-title/', // SEF, no extension
			'/page/system/2',
		);

		foreach($capturable as $url)
		{
			self::assertTrue($this->rd->isCapturable($url), "Expected capturable: $url");
		}

		$notCapturable = array(
			'/e107_media/x/thumb.jpg',
			'/theme/style.css',
			'/js/bootstrap.bundle.min.js',
			'/css/bootstrap.css.map', // the #5218 source-map case
			'/fonts/icon.woff2',
			'/files/manual.pdf',
		);

		foreach($notCapturable as $url)
		{
			self::assertFalse($this->rd->isCapturable($url), "Expected NOT capturable: $url");
		}
	}

	public function testIsCapturableRejectsEmbeddedRequests()
	{
		// A page rendered inside an <iframe> or modal (e.g. the menu manager's
		// ?configure= layout view) must never be remembered as a post-login
		// destination, or the user is returned to the bare embedded view with no
		// way to navigate. Two independent guards cover this: the browser's
		// Sec-Fetch-Dest Fetch Metadata header, and e107's own iframe/dialog
		// request markers (for clients that send no Fetch Metadata).
		$page = '/e107_admin/menus.php';
		$serverBackup = $_SERVER;

		try
		{
			// Baseline: a plain top-level GET of an admin page is capturable.
			unset($_SERVER['HTTP_SEC_FETCH_DEST']);
			self::assertTrue($this->rd->isCapturable($page), 'a plain admin page view should be capturable');

			// A top-level document navigation stays capturable...
			$_SERVER['HTTP_SEC_FETCH_DEST'] = 'document';
			self::assertTrue($this->rd->isCapturable($page), 'Sec-Fetch-Dest: document is a top-level navigation');

			// ...but an iframe / frame / fetch / asset sub-request never is.
			foreach(array('iframe', 'frame', 'empty', 'image', 'script', 'object', 'embed') as $dest)
			{
				$_SERVER['HTTP_SEC_FETCH_DEST'] = $dest;
				self::assertFalse($this->rd->isCapturable($page), "Sec-Fetch-Dest: $dest must not be capturable");
			}
			unset($_SERVER['HTTP_SEC_FETCH_DEST']);

			// Belt for clients that send no Fetch Metadata: the request markers
			// e107 uses to enter iframe / dialog rendering are refused by URL alone.
			self::assertFalse($this->rd->isCapturable('/e107_admin/menus.php?configure=3_column'), 'the menu-manager iframe view (?configure=) must not be capturable');
			self::assertFalse($this->rd->isCapturable('/e107_admin/theme.php?mode=main&iframe=1&action=info'), 'an ?iframe=1 admin view must not be capturable');
			self::assertFalse($this->rd->isCapturable('/e107_admin/cpage.php?mode=dialog&action=preview&id=3'), 'an ?mode=dialog dialog view must not be capturable');
			self::assertFalse($this->rd->isCapturable('/e107_admin/image.php?action=dialog&iframe=1'), 'an ?action=dialog dialog view must not be capturable');

			// A normal admin page with an unrelated query is still capturable.
			self::assertTrue($this->rd->isCapturable('/e107_admin/users.php?mode=main&action=list'), 'a normal admin query must remain capturable');
		}
		finally
		{
			$_SERVER = $serverBackup;
		}
	}

	public function testLoginDestinationTokenSkipsAssets()
	{
		// Regression for issue #5218: a missing asset must never be remembered as a destination.
		self::assertSame('', $this->rd->getLoginDestinationToken('/e107_plugins/estate/media/prop/thm/14-1-0-349.jpeg'));
		self::assertSame('', $this->rd->getLoginDestinationToken('/themes/bootstrap.bundle.min.js.map'));

		// A real page is remembered.
		self::assertNotSame('', $this->rd->getLoginDestinationToken('/news.php?extend.1'));
	}

	public function testLoginDestinationRoundTrip()
	{
		$token = $this->rd->getLoginDestinationToken('/news.php?extend.1');
		self::assertNotSame('', $token);
		self::assertSame('/news.php?extend.1', $this->rd->verifyDestination($token));
	}

	public function testVerifyDestinationRejectsTamperAndGarbage()
	{
		$token = $this->rd->getLoginDestinationToken('/news.php?extend.1');
		self::assertNotSame('', $token);

		$tampered = substr($token, 0, -4) . 'AAAA';
		self::assertFalse($this->rd->verifyDestination($tampered));
		self::assertFalse($this->rd->verifyDestination('not-a-token'));
		self::assertFalse($this->rd->verifyDestination(''));
	}

	public function testVerifyDestinationRejectsExpired()
	{
		// Sign directly with a negative TTL to force an already-expired token.
		$expired = e107::getJWT()->encode(array('dest' => '/news.php'), -100);
		self::assertFalse($this->rd->verifyDestination($expired));
	}

	public function testVerifyDestinationEnforcesSameOrigin()
	{
		$offsite = array(
			'https://evil.example/phish',
			'//evil.example/phish',
			'/\\evil.example/phish',
			'\\\\evil.example/phish',
			'javascript:alert(1)',
			'not/rooted/relative',
		);

		foreach($offsite as $dest)
		{
			$token = e107::getJWT()->encode(array('dest' => $dest), 600);
			self::assertFalse($this->rd->verifyDestination($token), "Should reject off-site: $dest");
		}

		// Same-origin relative and absolute are accepted.
		$relToken = e107::getJWT()->encode(array('dest' => '/profile.php'), 600);
		self::assertSame('/profile.php', $this->rd->verifyDestination($relToken));

		$abs = SITEURL . 'news.php';
		$absToken = e107::getJWT()->encode(array('dest' => $abs), 600);
		self::assertSame($abs, $this->rd->verifyDestination($absToken));
	}

	public function testVerifyDestinationAllowsTrustedHost()
	{
		// A destination on a host configured in the `trusted_hosts` pref (e107inc/e107#5639)
		// is accepted, while an unrelated third-party host is still rejected.
		$cfg = e107::getConfig();
		$originalTrusted = $cfg->get('trusted_hosts');

		$cfg->set('trusted_hosts', array('staging.example.test'));

		try
		{
			$good = 'https://staging.example.test/members/area?x=1';
			$goodToken = e107::getJWT()->encode(array('dest' => $good), 600);
			self::assertSame($good, $this->rd->verifyDestination($goodToken), 'a configured trusted host should be accepted');

			$badToken = e107::getJWT()->encode(array('dest' => 'https://not-trusted.example.test/x'), 600);
			self::assertFalse($this->rd->verifyDestination($badToken), 'an untrusted third-party host must be rejected');
		}
		finally
		{
			$cfg->set('trusted_hosts', $originalTrusted);
		}
	}

	public function testGetLoginDestinationFromPostField()
	{
		$token = $this->rd->getLoginDestinationToken('/restricted-page');
		self::assertNotSame('', $token);

		$_POST[redirection::LOGIN_DEST_FIELD] = $token;
		self::assertSame('/restricted-page', $this->rd->getLoginDestination());

		// With no token present, there is no destination.
		unset($_POST[redirection::LOGIN_DEST_FIELD], $_COOKIE[redirection::LOGIN_DEST_COOKIE]);
		self::assertFalse($this->rd->getLoginDestination());
	}

	/**
	 * issue #5698: on a members-only site, registration set to "Disabled"
	 * (user_reg=0) with no social login provider sends a guest to a login.php
	 * that turns them straight back, looping until the browser aborts. The
	 * redirect must fall back to the members-only splash (a dead end) in that
	 * case only; "Login Only" (user_reg=2) and a custom login page are untouched.
	 *
	 * @throws Exception
	 */
	public function testMembersOnlyRedirectUrlBreaksLoopWhenLoginUnreachable()
	{
		// The loop guard only applies to the stock login page with social login off.
		self::assertSame(SITEURL.'login.php', e_LOGIN, 'test env must use the stock login page');
		self::assertFalse(e107::getUserProvider()->isSocialLoginEnabled(), 'test env must have social login off');

		$cfg = e107::getConfig();
		$beforeReg = $cfg->getPref('user_reg');
		$beforeRedir = $cfg->getPref('membersonly_redirect');

		try
		{
			// Disabled + default (non-splash) redirect: break the loop -> splash.
			$cfg->setPref('user_reg', 0)->setPref('membersonly_redirect', '');
			self::assertSame(e_HTTP.'membersonly.php', $this->rd->getMembersOnlyRedirectUrl(),
				'user_reg=0 (Disabled) must fall back to the members-only splash');

			// Login Only: members can still log in, so the login page is used.
			$cfg->setPref('user_reg', 2);
			self::assertSame(e_LOGIN, $this->rd->getMembersOnlyRedirectUrl(),
				'user_reg=2 (Login Only) must still go to the login page');

			// Register & Login: login page as well.
			$cfg->setPref('user_reg', 1);
			self::assertSame(e_LOGIN, $this->rd->getMembersOnlyRedirectUrl(),
				'user_reg=1 (Register & Login) must go to the login page');

			// An explicit splash redirect is honoured regardless of user_reg.
			$cfg->setPref('user_reg', 1)->setPref('membersonly_redirect', 'splash');
			self::assertSame(e_HTTP.'membersonly.php', $this->rd->getMembersOnlyRedirectUrl(),
				'an explicit splash redirect must always use the splash');
		}
		finally
		{
			$cfg->setPref('user_reg', $beforeReg)->setPref('membersonly_redirect', $beforeRedir);
		}
	}


}
