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
		 * The alphanumeric class generateRandomString() builds carries - and _,
		 * and both cases of every letter, because it was written for names and
		 * passwords. One of the three faces the challenge is drawn in turns its
		 * letters as part of its design, which leaves a hyphen and an underscore
		 * as the same short stroke and gives a reader no way to tell a case
		 * apart, while the answer is compared exactly. The handler draws from an
		 * alphabet of its own instead.
		 */
		public function testAChallengeIsDrawnOnlyFromTheCaptchaAlphabet()
		{
			$alphabet = secure_image::SOLUTION_ALPHABET;
			$letters  = str_split($alphabet);

			$this->assertSame(0, preg_match('/[^A-Z0-9]/', $alphabet),
				'the alphabet may hold no lower case, no hyphen and no underscore: '.$alphabet);
			$this->assertSame(count($letters), count(array_unique($letters)),
				'no character may appear in the alphabet twice');

			$seen = array();

			for($i = 0; $i < 120; $i++)
			{
				$this->si->createCode();
				$solution = $this->si->getSecret();

				$this->assertIsString($solution, 'the handler issued a challenge carrying no answer');
				$this->assertSame(secure_image::SOLUTION_LENGTH, strlen($solution),
					'a challenge has to be the length the input and the image are sized for');
				$this->assertSame('', str_replace($letters, '', $solution),
					'a challenge drew a character no visitor can read off the image: '.$solution);

				$seen += array_flip(str_split($solution));
			}

			$this->assertSame(count($letters), count($seen),
				'every character of the alphabet has to be reachable');
		}

		/**
		 * The image is 100 pixels wide and render() starts the text at x=1, so the
		 * rightmost column a challenge may ink is 99. One that runs past the edge is
		 * drawn short, and the token is spent by the attempt, so the visitor loses
		 * their one go at it.
		 *
		 * imagettfbbox() of a string is the advances of all but the last character
		 * plus the last character's own extent, so the widest challenge the alphabet
		 * can produce is somewhere in "one character repeated, then any character".
		 * The draw repeats freely, so that family has to be measured rather than the
		 * five widest distinct characters.
		 */
		public function testTheWidestChallengeFitsTheImage()
		{
			if(!function_exists('imagettfbbox'))
			{
				$this->markTestSkipped('GD was built without FreeType, so no text can be measured');
			}

			$faces   = array('chaostimes.ttf' => 19, 'crazy_style.ttf' => 18, 'puchakhonmagnifier3.ttf' => 19);
			$letters = str_split(secure_image::SOLUTION_ALPHABET);

			foreach($faces as $face => $size)
			{
				$file = realpath(e_CORE.'fonts/'.$face);
				$this->assertNotFalse($file, 'render() picks this face at random and it is not there');

				$widest = '';
				$edge   = -1;

				foreach($letters as $repeated)
				{
					foreach($letters as $last)
					{
						$challenge = str_repeat($repeated, secure_image::SOLUTION_LENGTH - 1).$last;
						$box       = imagettfbbox($size, 0, $file, $challenge);

						if(1 + $box[2] > $edge)
						{
							$edge   = 1 + $box[2];
							$widest = $challenge;
						}
					}
				}

				$this->assertLessThan(100, $edge,
					'the widest challenge this alphabet can draw, '.$widest.', inks column '.$edge.' of 99 in '.$face);
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
