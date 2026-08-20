<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class UserHandlerTest extends \Test\Unit
	{
		/** Login name the salted fixtures below were computed for. */
		const CHAP_LOGIN = 'chapuser';

		/** PASSWORD_E107_ID . md5(md5('correct horse battery staple') . 'chapuser'). */
		const CHAP_STORED = '$E$1a8c044273cf1d2bf3b851ce42334c3c';

		/**
		 * A challenge whose server-side CHAP value under CHAP_STORED is
		 * 0e355597511017710677358296587448, i.e. magic-hash form. Found by
		 * search over 40 hex characters, the shape e_random::hex(40) issues.
		 */
		const CHAP_MAGIC_CHALLENGE = '2df954859f440f5d5d461e70d1cb1408145c5168';

		/** What the server computes for CHAP_STORED and CHAP_MAGIC_CHALLENGE. */
		const CHAP_MAGIC_EXPECTED = '0e355597511017710677358296587448';

		/**
		 * A magic hash that is not CHAP_MAGIC_EXPECTED. PHP reads both as the
		 * float 0.0, so `==` calls them equal and `hash_equals()` does not.
		 */
		const CHAP_MAGIC_RESPONSE = '0e462097431906509019562988736854';

		/** PASSWORD_E107_ID . md5(md5('hunter2') . 'plainuser'). */
		const CHAP_PLAIN_STORED = '$E$d5ef39bc1b834eac4e4f4a8fffd83814';

		const CHAP_PLAIN_CHALLENGE = 'ab12cd34ab12cd34ab12cd34ab12cd34ab12cd34';

		/** What the server computes for CHAP_PLAIN_STORED and the challenge above. */
		const CHAP_PLAIN_EXPECTED = 'b5cc193404d8b950d55039486b51ddb5';

		/** @var UserHandler */
		protected $usr;

		protected function _before()
		{

			try
			{
				$this->usr = $this->make('UserHandler');
			}
			catch(Exception $e)
			{
				$this->fail("Couldn't load UserHandler object");
			}

		}

		/**
		 * CheckCHAP() compares the response the client sent against a value the
		 * server computes from the stored hash and the challenge it issued. Both
		 * are md5 output, so a loose comparison accepts any response in
		 * magic-hash form on the challenges whose computed value is also in
		 * magic-hash form.
		 *
		 * The two values here are different strings by every byte and equal
		 * under `==`, which is what this asserts is no longer enough.
		 */
		public function testCheckCHAPRejectsAMagicHashResponse()
		{
			$this->assertTrue(self::CHAP_MAGIC_EXPECTED == self::CHAP_MAGIC_RESPONSE,
				'The fixture no longer demonstrates type juggling, so the case below proves nothing.');

			$result = $this->usr->CheckCHAP(self::CHAP_MAGIC_CHALLENGE, self::CHAP_MAGIC_RESPONSE,
				self::CHAP_LOGIN, self::CHAP_STORED);

			$this->assertSame(PASSWORD_INVALID, $result,
				'CheckCHAP() accepted '.self::CHAP_MAGIC_RESPONSE.' where the server had computed '
				.self::CHAP_MAGIC_EXPECTED.'. The two are equal only to PHP\'s numeric comparison.');
		}

		/**
		 * Positive control on the same fixture: the response the client would
		 * honestly compute is still accepted.
		 */
		public function testCheckCHAPAcceptsTheResponseTheServerComputed()
		{
			$result = $this->usr->CheckCHAP(self::CHAP_MAGIC_CHALLENGE, self::CHAP_MAGIC_EXPECTED,
				self::CHAP_LOGIN, self::CHAP_STORED);

			$this->assertSame(PASSWORD_VALID, $result);
		}

		/**
		 * Positive control on a fixture whose computed value is an ordinary
		 * hash, so the accepting path is not exercised only with magic hashes.
		 */
		public function testCheckCHAPAcceptsAnOrdinaryResponse()
		{
			$result = $this->usr->CheckCHAP(self::CHAP_PLAIN_CHALLENGE, self::CHAP_PLAIN_EXPECTED,
				'plainuser', self::CHAP_PLAIN_STORED);

			$this->assertSame(PASSWORD_VALID, $result);
		}

		/**
		 * Backwards-compatibility control: a wrong response of the right shape
		 * was refused before and is refused now.
		 */
		public function testCheckCHAPRejectsAnOrdinaryWrongResponse()
		{
			$result = $this->usr->CheckCHAP(self::CHAP_PLAIN_CHALLENGE, str_repeat('d', 32),
				'plainuser', self::CHAP_PLAIN_STORED);

			$this->assertSame(PASSWORD_INVALID, $result);
		}

		/**
		 * Backwards-compatibility control: a site still on simple md5 storage
		 * gets back the salted hash to store, not just PASSWORD_VALID.
		 */
		public function testCheckCHAPReturnsTheConvertedHashForMd5Storage()
		{
			$this->usr->passwordOpts = PASSWORD_E107_SALT;

			$result = $this->usr->CheckCHAP(self::CHAP_PLAIN_CHALLENGE, '9b7bade1eec3f88f022f2dbefde32f7d',
				'md5user', md5('hunter2'));

			$this->assertSame('$E$e305b04a2cc50a7b0e0e9232b0153e36', $result);
		}

		/**
		 * Backwards-compatibility control: CheckPassword() still validates a
		 * salted hash it produced itself.
		 *
		 * Salted hashes carry the PASSWORD_E107_ID prefix, so neither side is a
		 * numeric string and the loose comparison there was never exploitable.
		 * This guards the change from `==` to hash_equals() on that line.
		 */
		public function testCheckPasswordRoundTripsASaltedHash()
		{
			$stored = $this->usr->HashPassword('hunter2', 'plainuser', PASSWORD_E107_SALT);

			$this->assertSame(self::CHAP_PLAIN_STORED, $stored);
			$this->assertSame(PASSWORD_VALID, $this->usr->CheckPassword('hunter2', 'plainuser', $stored));
			$this->assertSame(PASSWORD_INVALID, $this->usr->CheckPassword('hunter3', 'plainuser', $stored));
		}

/*
		public function testCheckPassword()
		{

		}

		public function testDeleteExpired()
		{

		}

		public function testIsPasswordRequired()
		{

		}

		public function testAddCommonClasses()
		{

		}

		public function test__construct()
		{

		}

		public function testResetPassword()
		{

		}

		public function testMakeUserCookie()
		{

		}

		public function testUserValidation()
		{

		}

		public function testConvertPassword()
		{

		}

		public function testHasReadonlyField()
		{

		}

		public function testRehashPassword()
		{

		}

		public function testNeedEmailPassword()
		{

		}

		public function testHashPassword()
		{

		}

		public function testCanConvert()
		{

		}

		public function testCheckCHAP()
		{

		}

		public function testUserClassUpdate()
		{

		}

		public function testGetHashType()
		{

		}

		public function testGenerateUserLogin()
		{

		}

		public function testGenerateRandomString()
		{

		}

		public function testGetDefaultHashType()
		{

		}

		public function testPasswordAPIExists()
		{

		}

		public function testAddNonDefaulted()
		{

		}

		public function testGetNiceNames()
		{

		}

		public function testUserStatusUpdate()
		{

		}

*/

	}
