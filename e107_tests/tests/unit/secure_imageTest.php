<?php


	class secure_imageTest extends \Codeception\Test\Unit
	{

		/** @var secure_image */
		protected $si;

		protected function _before()
		{

			try
			{
				$this->si = e107::getSecureImg();
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

		}

		/**
		 * Seal a challenge with claims of the test's own choosing, so that a
		 * single check of the verifier can be exercised without having to
		 * arrange the whole world around it.
		 *
		 * @param array $claims
		 * @param int $ttl
		 * @return string
		 */
		private function sealChallenge(array $claims, $ttl = 120)
		{
			$token = e107::getSealedToken(secure_image::TOKEN_PURPOSE)->seal($claims, $ttl);
			$this->assertIsString($token, 'the test could not seal a challenge of its own');

			return $token;
		}

		public function testCodeAndVerify()
		{
			$code = $this->si->create_code();
			$secret = $this->si->getSecret();

			$this->si->renderImage();
			$this->si->renderInput();

			$result = $this->si->invalidCode($code, $secret);
			$this->assertFalse($result);

			$code = $this->si->create_code();
			$secret = $this->si->getSecret();
			$result = $this->si->verify_code($code, $secret);
			$this->assertTrue($result);

			$code = $this->si->create_code();
			$result = $this->si->invalidCode($code, 'bad code');
			$this->assertSame('Incorrect code entered.', $result);

			$code = $this->si->create_code();
			$result = $this->si->verify_code($code, 'bad code');
			$this->assertFalse($result);
		}

		/**
		 * The whole reason the token stopped being a JWT: the answer used to be
		 * readable from the page source with nothing but base64_decode().
		 */
		public function testTheAnswerIsNotInTheTokenTheFormCarries()
		{
			$si = new secure_image();

			$token = $si->getToken(secure_image::FORM_CONTACT);
			$answer = $si->getSecret(secure_image::FORM_CONTACT);

			$this->assertNotEmpty($answer);
			$this->assertStringNotContainsString($answer, $token);

			foreach(explode('.', $token) as $part)
			{
				$decoded = base64_decode(strtr($part, '-_', '+/'));
				$this->assertStringNotContainsString($answer, (string) $decoded,
					'a part of the token decoded to something carrying the answer');
			}
		}

		/**
		 * Asserting only that the literal answer is absent from the markup
		 * would have passed against the signed JWT this replaced, where the
		 * answer sat base64 encoded in the hidden field and in the image URL.
		 * So every run of base64 characters in the page, whether it came from
		 * the field or from the query string, is decoded and read.
		 */
		public function testTheAnswerIsNotInTheRenderedForm()
		{
			$si = new secure_image();

			$markup = $si->renderImage(secure_image::FORM_CONTACT)
				. $si->renderLabel()
				. $si->renderInput(secure_image::FORM_CONTACT);

			$answer = $si->getSecret(secure_image::FORM_CONTACT);

			$this->assertNotEmpty($answer);
			$this->assertStringNotContainsString($answer, $markup);
			$this->assertStringNotContainsString(urlencode($answer), $markup);
			$this->assertStringContainsString($si->getToken(secure_image::FORM_CONTACT), $markup,
				'the input must carry the token the image was drawn from');

			$candidates = array();
			preg_match_all('#[A-Za-z0-9+/=_-]{8,}#', urldecode($markup), $candidates);

			$this->assertNotEmpty($candidates[0], 'the markup carried nothing worth decoding');

			foreach($candidates[0] as $candidate)
			{
				$decoded = base64_decode(strtr($candidate, '-_', '+/'));

				$this->assertStringNotContainsString($answer, (string) $decoded,
					'something in the rendered form decoded to the answer: ' . $candidate);
			}
		}

		public function testACorrectAnswerIsAccepted()
		{
			$si = new secure_image();

			$token = $si->getToken(secure_image::FORM_CONTACT);

			$this->assertTrue($si->verify_code($token, $si->getSecret(secure_image::FORM_CONTACT), secure_image::FORM_CONTACT));
		}

		public function testAWrongAnswerIsRefused()
		{
			$si = new secure_image();

			$token = $si->getToken(secure_image::FORM_CONTACT);

			$this->assertFalse($si->verify_code($token, 'not-the-answer', secure_image::FORM_CONTACT));
		}

		public function testASecondAttemptAfterAWrongAnswerIsRefused()
		{
			$si = new secure_image();

			$token = $si->getToken(secure_image::FORM_CONTACT);
			$answer = $si->getSecret(secure_image::FORM_CONTACT);

			$this->assertFalse($si->verify_code($token, 'not-the-answer', secure_image::FORM_CONTACT));
			$this->assertFalse($si->verify_code($token, $answer, secure_image::FORM_CONTACT),
				'a wrong answer must still spend the challenge, or the five characters can be guessed');
		}

		public function testASecondAttemptAfterACorrectAnswerIsRefused()
		{
			$si = new secure_image();

			$token = $si->getToken(secure_image::FORM_CONTACT);
			$answer = $si->getSecret(secure_image::FORM_CONTACT);

			$this->assertTrue($si->verify_code($token, $answer, secure_image::FORM_CONTACT));
			$this->assertFalse($si->verify_code($token, $answer, secure_image::FORM_CONTACT),
				'a solved challenge must not be replayable');
		}

		public function testAChallengeIssuedForOneFormIsRefusedByAnother()
		{
			$si = new secure_image();

			$token = $si->getToken(secure_image::FORM_CONTACT);
			$answer = $si->getSecret(secure_image::FORM_CONTACT);

			$this->assertFalse($si->verify_code($token, $answer, secure_image::FORM_SIGNUP));
		}

		/**
		 * A theme or plugin that renders its own CAPTCHA markup names no form,
		 * and its login box has to keep working.
		 */
		public function testAnUnnamedChallengeIsAcceptedByANamedForm()
		{
			$si = new secure_image();

			$token = $si->getToken();
			$answer = $si->getSecret();

			$this->assertTrue($si->verify_code($token, $answer, secure_image::FORM_LOGIN));
		}

		public function testAnExpiredChallengeIsRefused()
		{
			$si = new secure_image();

			$token = $this->sealChallenge(array(
				'solution' => 'AbC12',
				'ip'       => e107::getIPHandler()->getIP(false),
				'form'     => secure_image::FORM_CONTACT,
			), -100);

			$this->assertFalse($si->verify_code($token, 'AbC12', secure_image::FORM_CONTACT));
		}

		/**
		 * The address check is on by default since v2.4.0, and no preference is
		 * set here, so this also pins the default.
		 */
		public function testAChallengeIssuedToAnotherAddressIsRefused()
		{
			$si = new secure_image();

			$token = $this->sealChallenge(array(
				'solution' => 'AbC12',
				'ip'       => '203.0.113.9',
				'form'     => secure_image::FORM_CONTACT,
			));

			$this->assertFalse($si->verify_code($token, 'AbC12', secure_image::FORM_CONTACT));
		}

		/**
		 * At e_SECURITY_LEVEL 10 the session replaces its form token on every
		 * request, before any page body runs. A challenge was once bound to that
		 * token, so one issued during a request was answered during the next
		 * against a value that no longer existed: every CAPTCHA on the site was
		 * refused and spent, admin login among them.
		 *
		 * Nothing binds a challenge to a client any more, so this passes by
		 * construction. It stays because the binding was reintroduced once
		 * already and this is what caught it.
		 */
		public function testAChallengeSurvivesTheFormTokenBeingRotated()
		{
			$si = new secure_image();

			$token = $si->getToken(secure_image::FORM_CONTACT);
			$answer = $si->getSecret(secure_image::FORM_CONTACT);

			// What e_session::_regenerateFormToken() does, for both kinds of
			// visitor: a new cookie for a guest, a new session value for a member.
			$_COOKIE[CSRFCookieHandler::COOKIE_NAME] = e_random::hex(32);
			e107::getSession()->set('__form_token', e_random::hex(64));

			$this->assertTrue($si->verify_code($token, $answer, secure_image::FORM_CONTACT),
				'rotating the form token must not invalidate a challenge already in a visitor\'s hands');
		}

		/**
		 * A CAPTCHA costs the visitor no cookie, in either direction.
		 *
		 * Drawing one sets nothing, and answering one needs nothing. A binding
		 * to a cookie of this class was tried and removed: both halves of it
		 * were strings the client held, so it never stopped a solved challenge
		 * being passed on, and a client that simply omitted the cookie skipped
		 * the check outright.
		 */
		public function testACaptchaNeitherSetsNorNeedsACookie()
		{
			$si = new secure_image();

			$before = $_COOKIE;
			$token  = $si->getToken(secure_image::FORM_CONTACT);
			$answer = $si->getSecret(secure_image::FORM_CONTACT);

			$this->assertSame($before, $_COOKIE, 'drawing a challenge must set no cookie');

			$_COOKIE = array();

			$this->assertTrue($si->verify_code($token, $answer, secure_image::FORM_CONTACT),
				'answering a challenge must not require a cookie');

			$_COOKIE = $before;
		}

		/**
		 * A preference of zero seconds would seal a challenge whose expiry
		 * equals its issue time, so every CAPTCHA on the site would be refused
		 * the instant it was drawn.
		 */
		public function testATimeToLivePreferenceOfZeroFallsBackToTheDefault()
		{
			$config = e107::getConfig();
			$restore = $config->get(secure_image::PREF_CAPTCHA_TTL);
			$config->set(secure_image::PREF_CAPTCHA_TTL, 0);

			try
			{
				$si = new secure_image();

				$token = $si->getToken(secure_image::FORM_CONTACT);
				$claims = e107::getSealedToken(secure_image::TOKEN_PURPOSE)->open($token);

				$this->assertIsArray($claims, 'a zero preference sealed a challenge nothing can open');
				$this->assertSame(secure_image::DEFAULT_TTL, $claims['exp'] - $claims['iat']);
			}

			finally
			{
				$config->set(secure_image::PREF_CAPTCHA_TTL, $restore);
			}
		}

		/**
		 * The marker recording a spent challenge is named after that
		 * challenge's own expiry, and the sweep reads the name, so a marker
		 * stops occupying the disk at the moment the token it records stops
		 * being openable. Without this an anonymous visitor could leave a
		 * permanent file behind for the price of one wrong answer.
		 */
		public function testAnExpiredSpentMarkerIsSweptAway()
		{
			$si = new secure_image();

			$directory = e_CACHE_CONTENT . secure_image::SPENT_DIRECTORY;

			$si->verify_code($si->getToken(secure_image::FORM_CONTACT), 'not-the-answer', secure_image::FORM_CONTACT);

			$this->assertDirectoryExists($directory);

			$stale = $directory . sprintf('%010d', time() - 3600) . '-' . str_repeat('b', 32) . secure_image::SPENT_SUFFIX;
			$this->assertNotFalse(file_put_contents($stale, ''));

			// The sweep runs at most once a minute, and one has just run.
			@unlink($directory . secure_image::SWEEP_STAMP);

			$si->verify_code($si->getToken(secure_image::FORM_CONTACT), 'not-the-answer', secure_image::FORM_CONTACT);

			clearstatcache();
			$this->assertFileDoesNotExist($stale);
		}

		public function testAChallengeSealedForAnotherPurposeIsRefused()
		{
			$si = new secure_image();

			$token = e107::getSealedToken('login-destination')->seal(array(
				'solution' => 'AbC12',
				'ip'       => e107::getIPHandler()->getIP(false),
				'form'     => secure_image::FORM_CONTACT,
			), 120);

			$this->assertIsString($token);
			$this->assertFalse($si->verify_code($token, 'AbC12', secure_image::FORM_CONTACT));
		}

		/**
		 * The hazard the single use creates: a form that answers a failed
		 * submission with the token it was posted is a form nobody can ever
		 * complete. Nothing in the handler may remember a submitted token.
		 */
		public function testAFailedSubmissionIsFollowedByAFreshChallenge()
		{
			$si = new secure_image();

			$token = $si->getToken(secure_image::FORM_CONTACT);

			$this->assertFalse($si->verify_code($token, 'not-the-answer', secure_image::FORM_CONTACT));

			$si = new secure_image(); // the re-render is a new page, and often a new object
			$fresh = $si->getToken(secure_image::FORM_CONTACT);

			$this->assertNotSame($token, $fresh);
			$this->assertTrue($si->verify_code($fresh, $si->getSecret(secure_image::FORM_CONTACT), secure_image::FORM_CONTACT));
		}

		/**
		 * Two forms on the same page must not be handed each other's challenge.
		 */
		public function testEachFormKeepsItsOwnChallenge()
		{
			$si = new secure_image();

			$contact = $si->getToken(secure_image::FORM_CONTACT);
			$login = $si->getToken(secure_image::FORM_LOGIN);

			$this->assertNotSame($contact, $login);
			$this->assertSame($contact, $si->getToken(secure_image::FORM_CONTACT),
				'the image and the input of one form must agree on one challenge');
		}

		public function testAChallengeLastsTwoMinutesByDefault()
		{
			$si = new secure_image();

			$claims = e107::getSealedToken(secure_image::TOKEN_PURPOSE)->open($si->getToken(secure_image::FORM_CONTACT));

			$this->assertIsArray($claims);
			$this->assertSame(secure_image::DEFAULT_TTL, $claims['exp'] - $claims['iat']);
			$this->assertSame(86400, secure_image::DEFAULT_TTL);
		}

		/**
		 * Test that accessing $random_number triggers lazy generation
		 */
		public function testMagicGetterLazyGeneration()
		{
			$si = new secure_image();

			// Access $random_number - should trigger lazy generation
			$token = $si->random_number;

			// Verify it's a sealed token (non-empty string)
			$this->assertNotEmpty($token);
			$this->assertIsString($token);

			// Verify we can extract the secret from it
			$secret = $si->getSecret();
			$this->assertNotEmpty($secret);

			// Verify the token validates with the secret
			$result = $si->invalidCode($token, $secret);
			$this->assertFalse($result);
		}

		/**
		 * Test that setting $random_number manually overrides the sealed token
		 */
		public function testMagicSetterOverride()
		{
			$si = new secure_image();

			// Manually set a custom value
			$customToken = 'custom_test_token_123';
			$si->random_number = $customToken;

			// Verify getter returns our custom value
			$this->assertEquals($customToken, $si->random_number);

			// getSecret() should return null since it's not a valid token
			$secret = $si->getSecret();
			$this->assertNull($secret);
		}

		/**
		 * Test isset() behavior on $random_number
		 */
		public function testMagicIsset()
		{
			$si = new secure_image();

			// Before generation, isset should return false (doesn't trigger generation)
			$this->assertFalse(isset($si->random_number));

			// Access the property to trigger generation
			$token = $si->random_number;
			$this->assertNotEmpty($token);

			// Now isset should return true
			$this->assertTrue(isset($si->random_number));
		}

		/**
		 * Test multiple accesses return the same token
		 */
		public function testConsistentTokenReturn()
		{
			$si = new secure_image();

			// First access
			$token1 = $si->random_number;

			// Second access
			$token2 = $si->random_number;

			// Should be the same token
			$this->assertEquals($token1, $token2);
		}

		/**
		 * Test getToken() method also triggers lazy generation
		 */
		public function testGetTokenMethod()
		{
			$si = new secure_image();

			// getToken() should trigger generation
			$token = $si->getToken();
			$this->assertNotEmpty($token);

			// Should be same as accessing via property
			$this->assertEquals($token, $si->random_number);
		}

		/**
		 * Test that createCode() can be called explicitly and updates the token
		 */
		public function testExplicitCreateCode()
		{
			$si = new secure_image();

			// First generation via property access
			$token1 = $si->random_number;

			// Explicit call to createCode()
			$token2 = $si->createCode();

			// Should generate a new token
			$this->assertNotEquals($token1, $token2);

			// Property should now return the new token
			$this->assertEquals($token2, $si->random_number);
		}

		/**
		 * Test backward compatibility with legacy code patterns
		 */
		public function testLegacyUsagePattern()
		{
			$si = new secure_image();

			// Legacy pattern: access property then verify
			$code = $si->random_number;
			$secret = $si->getSecret();

			// Should work as before
			$result = $si->verify_code($code, $secret);
			$this->assertTrue($result);

			// One image, one attempt: the same code cannot be tried again
			$code = $si->createCode();
			$result = $si->verify_code($code, 'wrong_secret');
			$this->assertFalse($result);
		}

	}
