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
