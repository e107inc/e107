<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_sessionTest extends \Codeception\Test\Unit
	{
		/** @var e_session  */
		private $sess;

		protected function _before()
		{
			try
			{
				$this->sess = $this->make('e_session');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_session object");
			}

		}

		/**
		 * GHSA-72q5-94gw-prww. Nearly every site has never written this
		 * preference, so what it reads as when unset is what nearly every site
		 * runs. It resolves through a named constant rather than a literal so the
		 * recommendation can move in a later release without an operator acting.
		 */
		public function testTokenCheckModeDefaultsToTheRecommendation()
		{
			$this::assertNull(e107::getConfig()->get('csrf_enforce'));
			$this::assertSame(e_session::CSRF_CHECK_RECOMMENDED, e_session::tokenCheckMode());
		}

		/**
		 * The recommended mode has to accept a token, because an upgrade cannot
		 * ask the operator's browser what it supports and a Fetch Metadata-only
		 * mode refuses every POST from a browser too old to send the header. If
		 * this ever fails, the default is locking those visitors out of the site.
		 */
		public function testTheRecommendedModeLocksNobodyOut()
		{
			$this::assertTrue(e_session::modeUsesToken(e_session::CSRF_CHECK_RECOMMENDED));
		}

		/**
		 * Run a closure against a given request environment.
		 *
		 * tokenCheckMode() reads $_SERVER, because whether a browser can send
		 * Sec-Fetch-Site at all is a property of the connection rather than of the
		 * preference. The CLI runner's own $_SERVER is neither HTTPS nor loopback.
		 *
		 * @param array    $server
		 * @param callable $fn
		 * @return void
		 */
		private function withRequest(array $server, $fn)
		{
			$previous = $_SERVER;
			$_SERVER = $server;

			try
			{
				$fn();
			}
			finally
			{
				$_SERVER = $previous;
			}
		}

		/**
		 * A mode that reads only the browser is unsatisfiable where the browser
		 * will never be asked.
		 *
		 * Sec-Fetch-Site is appended only to a potentially trustworthy origin, so
		 * on a site served over plain HTTP no browser ever sends it. A mode that
		 * asks for nothing else would refuse every write for good, and because
		 * such a mode publishes no token there would be nothing to fall back on.
		 * Verified against a real Chrome, which sends no Sec-Fetch-* header at all
		 * over plain HTTP to a named host.
		 *
		 * The recommendation on this branch still reads a token, so nothing here
		 * changes what an ordinary site does. It matters for an operator who chose
		 * mode 4 or 5 outright, who must not be able to lock themselves out.
		 */
		public function testABrowserOnlyModeSoftensWhereTheBrowserCannotAnswer()
		{
			$config = e107::getConfig();
			$insecure = array('HTTP_HOST' => 'example.org');

			foreach(array(e_session::CSRF_CHECK_SAME_SITE, e_session::CSRF_CHECK_SAME_ORIGIN) as $mode)
			{
				$config->set('csrf_enforce', $mode);

				$this->withRequest($insecure, function() use ($mode) {
					$this::assertSame(e_session::CSRF_CHECK_TOKEN_OR_SAME_SITE, e_session::tokenCheckMode(),
						'mode ' . $mode . ' must soften where Sec-Fetch-Site can never arrive');

					// The point of softening to the hybrid rather than to nothing:
					// a token is now both asked for and published, so the injector
					// hands one to the very documents that will be asked for it.
					$this::assertTrue(e_session::modeUsesToken(), 'the softened mode has to publish a token');
				});

				// Nothing is softened where the header can actually arrive.
				$this->withRequest(array('HTTPS' => 'on'), function() use ($mode) {
					$this::assertSame($mode, e_session::tokenCheckMode(),
						'mode ' . $mode . ' must be left alone on a trustworthy origin');
				});
			}

			// A mode that already reads a token is satisfiable anywhere, and Off
			// asks for nothing, so neither is rewritten.
			foreach(array(
				e_session::TOKEN_CHECK_OFF,
				e_session::TOKEN_CHECK_LOG,
				e_session::TOKEN_CHECK_ENFORCE,
				e_session::CSRF_CHECK_TOKEN_OR_SAME_SITE,
			) as $mode)
			{
				$config->set('csrf_enforce', $mode);

				$this->withRequest($insecure, function() use ($mode) {
					$this::assertSame($mode, e_session::tokenCheckMode(),
						'mode ' . $mode . ' has nothing to soften');
				});
			}

			$config->remove('csrf_enforce');
		}

		/**
		 * What counts as an origin a browser would send Fetch Metadata to.
		 *
		 * Deliberately reads the request and nothing else. A preference saying the
		 * site is HTTPS describes how it is meant to be reached, not how this
		 * request arrived, and believing it about a request that truly came over
		 * HTTP would preserve the lockout this exists to prevent.
		 */
		public function testFetchMetadataReachesUsReadsTheRequestAlone()
		{
			$reaches = function(array $server) {
				return e_session::fetchMetadataReachesUs($server);
			};

			// Plain HTTP to a named host is the case that started all this.
			$this::assertFalse($reaches(array('HTTP_HOST' => 'example.org')));
			$this::assertFalse($reaches(array()));
			$this::assertFalse($reaches(array('HTTPS' => 'off', 'HTTP_HOST' => 'example.org')));

			// Straightforwardly secure.
			$this::assertTrue($reaches(array('HTTPS' => 'on')));
			$this::assertTrue($reaches(array('HTTPS' => '1')));
			$this::assertTrue($reaches(array('SERVER_PORT' => 443)));
			$this::assertTrue($reaches(array('SERVER_PORT' => '443')));

			// Secure Contexts counts loopback as potentially trustworthy, so a
			// site developed at http://localhost does receive the header.
			foreach(array('localhost', 'localhost:8080', '127.0.0.1', '127.0.0.1:8080', '127.1.2.3', '[::1]', '[::1]:8080', '::1') as $host)
			{
				$this::assertTrue($reaches(array('HTTP_HOST' => $host)), $host . ' is loopback');
			}

			foreach(array('notlocalhost', 'localhost.example.org', '128.0.0.1', 'example.org:80') as $host)
			{
				$this::assertFalse($reaches(array('HTTP_HOST' => $host)), $host . ' is not loopback');
			}

			// A TLS terminating proxy. The first entry is the client's own.
			$this::assertTrue($reaches(array('HTTP_HOST' => 'example.org', 'HTTP_X_FORWARDED_PROTO' => 'https')));
			$this::assertTrue($reaches(array('HTTP_HOST' => 'example.org', 'HTTP_X_FORWARDED_PROTO' => 'HTTPS')));
			$this::assertTrue($reaches(array('HTTP_HOST' => 'example.org', 'HTTP_X_FORWARDED_PROTO' => 'https, http')));
			$this::assertFalse($reaches(array('HTTP_HOST' => 'example.org', 'HTTP_X_FORWARDED_PROTO' => 'http, https')));
			$this::assertTrue($reaches(array('HTTP_HOST' => 'example.org', 'HTTP_X_FORWARDED_SSL' => 'on')));
			$this::assertTrue($reaches(array('HTTP_HOST' => 'example.org', 'HTTP_FRONT_END_HTTPS' => 'on')));

			// The rescue: a proxy that forwards none of the above still gives
			// itself away, because the browser sending Fetch Metadata is proof
			// that the browser considered this origin trustworthy.
			$this::assertTrue($reaches(array('HTTP_HOST' => 'example.org', 'HTTP_SEC_FETCH_SITE' => 'same-origin')));
			$this::assertTrue($reaches(array('HTTP_HOST' => 'example.org', 'HTTP_SEC_FETCH_SITE' => 'cross-site')));
			$this::assertTrue($reaches(array('HTTP_HOST' => 'example.org', 'HTTP_SEC_FETCH_DEST' => 'document')));
			$this::assertTrue($reaches(array('HTTP_HOST' => 'example.org', 'HTTP_SEC_FETCH_MODE' => 'navigate')));

			// A header that merely looks similar is not the rescue.
			$this::assertFalse($reaches(array('HTTP_HOST' => 'example.org', 'HTTP_SEC_WEBSOCKET_KEY' => 'x')));
		}

		/**
		 * A value out of range is a typo, a bad migration, or a preference written
		 * by a newer release than this one. None is a reason to guess, and the
		 * nearest number could be Off.
		 */
		public function testAnUnusableStoredValueFallsBackToTheRecommendation()
		{
			$config = e107::getConfig();

			foreach(array(99, -1, 6, 'banana', '', null) as $stored)
			{
				$config->set('csrf_enforce', $stored);

				$this::assertSame(e_session::CSRF_CHECK_RECOMMENDED, e_session::tokenCheckMode(),
					'stored value ' . var_export($stored, true) . ' should fall back');
			}

			// A numeric string is what a form post delivers, so it has to resolve.
			$config->set('csrf_enforce', '1');
			$this::assertSame(e_session::TOKEN_CHECK_LOG, e_session::tokenCheckMode());

			$config->remove('csrf_enforce');
		}

		/**
		 * Which proof each mode asks for. The numbers are a menu rather than a
		 * ladder, so this is stated outright rather than inferred by comparison.
		 */
		public function testEachModeAsksForTheProofItNames()
		{
			$token = array(
				e_session::TOKEN_CHECK_LOG,
				e_session::TOKEN_CHECK_ENFORCE,
				e_session::CSRF_CHECK_TOKEN_OR_SAME_SITE,
			);

			$fetch = array(
				e_session::CSRF_CHECK_TOKEN_OR_SAME_SITE,
				e_session::CSRF_CHECK_SAME_SITE,
				e_session::CSRF_CHECK_SAME_ORIGIN,
			);

			foreach(range(0, 5) as $mode)
			{
				$this::assertSame(in_array($mode, $token, true), e_session::modeUsesToken($mode),
					'mode ' . $mode . ' token expectation');
				$this::assertSame(in_array($mode, $fetch, true), e_session::modeUsesFetchMetadata($mode),
					'mode ' . $mode . ' Fetch Metadata expectation');
			}

			// Off asks for nothing at all.
			$this::assertFalse(e_session::modeUsesToken(e_session::TOKEN_CHECK_OFF));
			$this::assertFalse(e_session::modeUsesFetchMetadata(e_session::TOKEN_CHECK_OFF));
		}

		/**
		 * Sec-Fetch-Site is the whole of the browser-side proof, so what counts as
		 * vouching is worth pinning down value by value.
		 *
		 * 'same-site' covers a sibling host under the same registrable domain,
		 * which a language-per-subdomain site needs, but taken at face value it
		 * would also vouch for a user-content host or one that has been taken
		 * over. It is honoured only for a host this site is configured to serve.
		 */
		public function testOnlyTheBrowserSayingThisSiteCounts()
		{
			$server = $_SERVER;

			$_SERVER['HTTP_HOST'] = 'example.org';

			$vouches = function($site, $mode, $origin = null)
			{
				unset($_SERVER['HTTP_SEC_FETCH_SITE'], $_SERVER['HTTP_ORIGIN']);

				if($site !== null)
				{
					$_SERVER['HTTP_SEC_FETCH_SITE'] = $site;
				}

				if($origin !== null)
				{
					$_SERVER['HTTP_ORIGIN'] = $origin;
				}

				$method = new ReflectionMethod('e_core_session', 'fetchMetadataVouches');
				$method->setAccessible(true);

				return $method->invoke(null, $mode);
			};

			// A browser that says nothing is not a browser that vouches.
			$this::assertFalse($vouches(null, e_session::CSRF_CHECK_SAME_SITE));
			$this::assertFalse($vouches('', e_session::CSRF_CHECK_SAME_SITE));

			foreach(array('cross-site', 'none', 'nonsense') as $site)
			{
				$this::assertFalse($vouches($site, e_session::CSRF_CHECK_SAME_SITE), $site . ' must not vouch');
				$this::assertFalse($vouches($site, e_session::CSRF_CHECK_SAME_ORIGIN), $site . ' must not vouch');
			}

			// Our own origin vouches in every mode that reads the header, and the
			// comparison is case and whitespace insensitive.
			$this::assertTrue($vouches('same-origin', e_session::CSRF_CHECK_SAME_SITE));
			$this::assertTrue($vouches('same-origin', e_session::CSRF_CHECK_SAME_ORIGIN));
			$this::assertTrue($vouches(' Same-Origin ', e_session::CSRF_CHECK_SAME_ORIGIN));

			// A sibling host is the difference between the two Fetch Metadata modes.
			$this::assertTrue($vouches('same-site', e_session::CSRF_CHECK_SAME_SITE, 'http://example.org'));
			$this::assertFalse($vouches('same-site', e_session::CSRF_CHECK_SAME_ORIGIN, 'http://example.org'));

			// A sibling we do not serve is exactly the case the Origin check exists
			// for: the browser is telling the truth, and the truth is not enough.
			$this::assertFalse($vouches('same-site', e_session::CSRF_CHECK_SAME_SITE, 'https://uploads.example.org'));
			$this::assertFalse($vouches('same-site', e_session::CSRF_CHECK_SAME_SITE, 'null'));

			$_SERVER = $server;
		}

		/**
		 * The runtime override is the designated seam for a test, and for a
		 * bootstrap that knows better than the stored preference.
		 */
		public function testSetTokenCheckModeOverridesThePreference()
		{
			$previous = e_session::setTokenCheckMode(e_session::TOKEN_CHECK_LOG);

			try
			{
				$this::assertSame(e_session::TOKEN_CHECK_LOG, e_session::tokenCheckMode());

				e_session::setTokenCheckMode(e_session::TOKEN_CHECK_OFF);
				$this::assertSame(e_session::TOKEN_CHECK_OFF, e_session::tokenCheckMode());

				// null hands control back to the preference, which is unset here
				e_session::setTokenCheckMode(null);
				$this::assertSame(e_session::CSRF_CHECK_RECOMMENDED, e_session::tokenCheckMode());
			}
			catch(Exception $e)
			{
				e_session::setTokenCheckMode($previous);
				throw $e;
			}

			e_session::setTokenCheckMode($previous);
		}

		/**
		 * setTokenCheckMode() hands back what it displaced, so a caller can put
		 * the previous value back without knowing what it was.
		 */
		public function testSetTokenCheckModeReturnsThePreviousOverride()
		{
			$this::assertNull(e_session::setTokenCheckMode(e_session::TOKEN_CHECK_LOG));
			$this::assertSame(e_session::TOKEN_CHECK_LOG, e_session::setTokenCheckMode(null));
			$this::assertNull(e_session::setTokenCheckMode(null));
		}

		public function testSetOption()
		{
			$opt = array(
				'lifetime'	 => 3600 ,
				'path'		 => '/',
				'domain'	 => 'test.com',
				'secure'	 => false,
				'httponly'	 => true,
				'_dummy'    => 'not here'
			);

			$this->sess->setOptions($opt);

			$newOpt = $this->sess->getOptions();

			unset($opt['_dummy']);

			$this->assertEquals($opt,$newOpt);


		}

		public function testClear()
		{
			$this->sess->set('clear/one', 'Test 1');
			$this->sess->set('clear/two', 'Test 2');
			$this->sess->set('clear/three', 'Test 3');

			$this->sess->clear('clear/two');

			$expected = array (
			  'one' => 'Test 1',
			  'three' => 'Test 3',
			);

			$result = $this->sess->get('clear');
			$this->assertSame($expected, $result);

		}



		public function testSetGet()
		{
			$expected = '123456';

			$this->sess->set('whatever', $expected);

			$result = $this->sess->get('whatever');

			$this->assertEquals($expected, $result);


			// Multi-dimensional array support.
			$newsess = e107::getSession('newtest');

			$newsess->set('customer', array('firstname'=>'Fred'));
			$newsess->set('customer/lastname', 'Smith');

			$expected = array (
			  'firstname' => 'Fred',
			  'lastname' => 'Smith',
			);

			$result = $newsess->get('customer');
			$this->assertSame($expected, $result);

		}
/*
		public function testGetOption()
		{

		}

		public function testSetDefaultSystemConfig()
		{

		}

		public function testGet()
		{

		}

		public function testGetData()
		{

		}

		public function testSet()
		{

		}

		public function testSetData()
		{

		}

		public function testIs()
		{

		}

		public function testHas()
		{

		}

		public function testHasData()
		{

		}

		public function testClear()
		{

		}

		public function testClearData()
		{

		}

		public function testSetConfig()
		{

		}

		public function testGetNamespaceKey()
		{

		}

		public function testSetOptions()
		{

		}

		public function testInit()
		{

		}

		public function testStart()
		{

		}

		public function testSetSessionId()
		{

		}

		public function testGetSessionId()
		{

		}

		public function testGetSaveMethod()
		{

		}

		public function testSetSessionName()
		{

		}

		public function testGetSessionName()
		{

		}

		public function testValidateSessionCookie()
		{

		}

		public function testCookieDelete()
		{

		}

		public function testValidate()
		{

		}

		public function testGetValidateData()
		{

		}

		public function testGetFormToken()
		{

		}

		public function testCheckFormToken()
		{

		}

		public function testClose()
		{

		}

		public function testEnd()
		{

		}

		public function testDestroy()
		{

		}

		public function testReplaceRegistry()
		{

		}*/
	}
