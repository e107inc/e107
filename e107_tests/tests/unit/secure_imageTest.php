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

		public function testCodeAndVerify()
		{
			$code = $this->si->create_code();

			$this->si->renderImage();
			$this->si->renderInput();

			$secret = $this->si->getSecret();

			$result = $this->si->invalidCode($code, $secret);
			$this->assertFalse($result);

			$code = $this->si->create_code(); // code above is destroyed upon successful match.
			$secret = $this->si->getSecret();
			$result = $this->si->verify_code($code, $secret);
			$this->assertTrue($result);

			$code = $this->si->create_code();
			$result = $this->si->invalidCode($code, 'bad code');
			$this->assertSame('Incorrect code entered.', $result);


			$result = $this->si->verify_code($code, 'bad code');
			$this->assertFalse($result);


		}

		/**
		 * Test that accessing $random_number triggers lazy generation
		 */
		public function testMagicGetterLazyGeneration()
		{
			$si = new secure_image();
			
			// Access $random_number - should trigger lazy generation
			$token = $si->random_number;
			
			// Verify it's a JWT token (non-empty string)
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
		 * Test that setting $random_number manually overrides the JWT
		 */
		public function testMagicSetterOverride()
		{
			$si = new secure_image();
			
			// Manually set a custom value
			$customToken = 'custom_test_token_123';
			$si->random_number = $customToken;
			
			// Verify getter returns our custom value
			$this->assertEquals($customToken, $si->random_number);
			
			// getSecret() should return null since it's not a valid JWT
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
			
			// Test with wrong code
			$result = $si->verify_code($code, 'wrong_secret');
			$this->assertFalse($result);
		}
/*
		public function testInvalidCode()
		{

		}

		public function testRenderImage()
		{

		}

		public function testCreate_code()
		{

		}

		public function testHex2rgb()
		{

		}

		public function testRender()
		{

		}

		public function testRenderLabel()
		{

		}

		public function test__construct()
		{

		}

		public function testR_image()
		{

		}

		public function testRenderInput()
		{

		}

		public function testVerify_code()
		{

		}

		public function testImageCreateTransparent()
		{

		}
*/



	}
