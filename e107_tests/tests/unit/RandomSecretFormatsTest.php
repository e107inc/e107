<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Regression coverage for GHSA-gm6q-rqm6-p9m4.
 *
 * Swapping rand()/mt_rand()/uniqid() for a CSPRNG had to be invisible to every
 * consumer: the generated secrets are stored in fixed-width columns, embedded
 * in dot-delimited activation URLs and matched by regular expressions that
 * predate the change. These tests pin the observable shape of each one so a
 * later tidy-up cannot widen a column or break a link by accident.
 */
class RandomSecretFormatsTest extends \Codeception\Test\Unit
{
	/**
	 * Alphabets documented by {@see UserHandler::generateRandomString()}.
	 * O, o and l are absent on purpose, to avoid confusion with 0 and 1.
	 */
	const ALPHA   = 'AaBbCcDdEeFfGgHhIiJjKkLMmNnPpQqRrSsTtUuVvWwXxYyZz';
	const DIGIT   = '0123456789';
	const SYMBOLS = '~!@#$%^*-+?;:';

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

	// -----------------------------------------------------------------------
	// generateRandomString(): one output character per pattern character
	// -----------------------------------------------------------------------

	public function testAlphaPatternDrawsOnlyFromTheAlphaAlphabet()
	{
		$this->assertDrawnFrom('#', self::ALPHA);
	}

	public function testNumericPatternDrawsOnlyFromTheDigitAlphabet()
	{
		$this->assertDrawnFrom('.', self::DIGIT);
	}

	public function testAlphanumericPatternDrawsOnlyFromTheAlphanumericAlphabet()
	{
		$this->assertDrawnFrom('*', self::ALPHA.self::DIGIT.'-_');
	}

	public function testSymbolPatternDrawsOnlyFromTheSymbolAlphabet()
	{
		$this->assertDrawnFrom('!', self::SYMBOLS);
	}

	public function testAlphanumericSymbolPatternDrawsOnlyFromTheCombinedAlphabet()
	{
		$this->assertDrawnFrom('?', self::ALPHA.self::DIGIT.'-_'.self::SYMBOLS);
	}

	/**
	 * The index range is 0..strlen($alphabet)-1, so the final character has to
	 * be reachable. An off-by-one here would silently shrink every alphabet.
	 */
	public function testEveryCharacterOfEveryAlphabetIsReachable()
	{
		$cases = array(
			'#' => self::ALPHA,
			'.' => self::DIGIT,
			'*' => self::ALPHA.self::DIGIT.'-_',
			'!' => self::SYMBOLS,
			'?' => self::ALPHA.self::DIGIT.'-_'.self::SYMBOLS,
		);

		foreach($cases as $token => $alphabet)
		{
			$draws = $this->usr->generateRandomString(str_repeat($token, 4000));
			$missing = array();

			for($i = 0, $len = strlen($alphabet); $i < $len; $i++)
			{
				if(strpos($draws, $alphabet[$i]) === false)
				{
					$missing[] = $alphabet[$i];
				}
			}

			$this->assertSame(array(), $missing,
				'pattern "'.$token.'" never produced: '.implode('', $missing));
		}
	}

	public function testSeededPatternEmitsTheSeedInOrder()
	{
		$this->assertSame('wxyz', $this->usr->generateRandomString('^^^^', 'wxyz'));

		$result = $this->usr->generateRandomString('*^*^*', 'ab');
		$this->assertSame(5, strlen($result));
		$this->assertSame('a', $result[1]);
		$this->assertSame('b', $result[3]);
	}

	/**
	 * A '^' beyond the end of the seed contributes nothing, so the result is
	 * shorter than the pattern. e107_admin/cron.php relied on this by accident;
	 * it is pinned so the quirk stays visible rather than surprising the next
	 * caller.
	 */
	public function testSeedExhaustionDropsRemainingSeedSlots()
	{
		$this->assertSame('q', $this->usr->generateRandomString('^^^', 'q'));
		$this->assertSame('', $this->usr->generateRandomString('^^^'));
	}

	public function testUnrecognisedPatternCharactersArePassedThroughOnlyWhenAlphanumeric()
	{
		$this->assertSame('abc-123_XYZ', $this->usr->generateRandomString('abc-123_XYZ'));

		// l, O and o are not in the alphanumeric set, so they are dropped.
		$this->assertSame('', $this->usr->generateRandomString('lOo'));
	}

	public function testEmptyPatternFallsBackToTwoLettersAndFourDigits()
	{
		$result = $this->usr->generateRandomString();

		$this->assertSame(6, strlen($result));
		$this->assertSame(1, preg_match('/^['.preg_quote(self::ALPHA, '/').']{2}[0-9]{4}$/', $result),
			'the default pattern is "##....", got: '.$result);
	}

	// -----------------------------------------------------------------------
	// Consumer formats
	// -----------------------------------------------------------------------

	/**
	 * fpw.php mints its reset code with '############' and then requires the
	 * code to survive preg_replace('#[\W_]#', '', ...) unchanged, both to keep
	 * the link intact and, on this branch, to keep the concatenated LIKE safe.
	 */
	public function testPasswordResetCodeIsTwelveWordCharacters()
	{
		for($i = 0; $i < 50; $i++)
		{
			$code = $this->usr->generateRandomString('############');

			$this->assertSame(12, strlen($code));
			$this->assertSame(1, preg_match('/^[A-Za-z0-9]{12}$/', $code),
				'the reset code must be alphanumeric, got: '.$code);
			$this->assertSame($code, preg_replace('#[\W_]#', '', $code),
				'fpw.php rejects any code that its own filter would alter');
		}
	}

	/**
	 * Auto-generated passwords are 8 to 12 alphanumerics.
	 *
	 * @see UserHandler::resetPassword()
	 */
	public function testResetPasswordLengthDrawStaysInRange()
	{
		$seen = array();

		for($i = 0; $i < 500; $i++)
		{
			$length = e_random::int(8, 12);
			$this->assertGreaterThanOrEqual(8, $length);
			$this->assertLessThanOrEqual(12, $length);
			$seen[$length] = true;
		}

		ksort($seen);
		$this->assertSame(array(8, 9, 10, 11, 12), array_keys($seen));
	}

	/**
	 * Account activation keys and admin-issued user_sess values. The column is
	 * varchar(100) and the value is embedded in a dot-delimited activation URL,
	 * so 32 lowercase hex characters is the contract.
	 *
	 * @see e_user_model::randomKey()
	 */
	public function testRandomKeyIsThirtyTwoLowercaseHexCharacters()
	{
		$seen = array();

		for($i = 0; $i < 50; $i++)
		{
			$key = e_user_model::randomKey();

			$this->assertSame(32, strlen($key));
			$this->assertSame(1, preg_match('/^[a-f0-9]{32}$/', $key),
				'randomKey() must stay 32 lowercase hex characters, got: '.$key);

			$seen[$key] = true;
		}

		$this->assertCount(50, $seen, 'randomKey() must not repeat');
	}

	/**
	 * The CHAP challenge used to be sha1(), so it was 40 hex characters and the
	 * JavaScript login still assumes that width.
	 *
	 * @see e_core_session::challenge()
	 */
	public function testChapChallengeIsFortyHexCharacters()
	{
		$pref = e107::getConfig('core');
		$restore = $pref->get('password_CHAP');
		$pref->set('password_CHAP', 1);

		try
		{
			$sess = $this->make('e_core_session');
			$sess->challenge();
			$challenge = $sess->get('challenge');

			$this->assertSame(40, strlen($challenge));
			$this->assertSame(1, preg_match('/^[a-f0-9]{40}$/', $challenge),
				'the CHAP challenge must stay 40 hex characters, got: '.$challenge);
		}
		catch(Exception $e)
		{
			$pref->set('password_CHAP', $restore);
			throw $e;
		}

		$pref->set('password_CHAP', $restore);
	}

	/**
	 * With CHAP off nothing is minted at all, which is what master already did.
	 */
	public function testChapChallengeIsNotGeneratedWhenChapIsDisabled()
	{
		$pref = e107::getConfig('core');
		$restore = $pref->get('password_CHAP');
		$pref->set('password_CHAP', 0);

		$sess = $this->make('e_core_session');
		$sess->challenge();
		$challenge = $sess->get('challenge');

		$pref->set('password_CHAP', $restore);

		$this->assertEmpty($challenge);
	}

	/**
	 * Assert that every character a pattern produces belongs to its alphabet,
	 * and that the output is exactly as long as the pattern.
	 *
	 * @param string $token single pattern character
	 * @param string $alphabet permitted output characters
	 * @return void
	 */
	private function assertDrawnFrom($token, $alphabet)
	{
		$length = 200;
		$result = $this->usr->generateRandomString(str_repeat($token, $length));

		$this->assertSame($length, strlen($result),
			'pattern "'.$token.'" must produce one character per pattern character');

		for($i = 0; $i < $length; $i++)
		{
			$this->assertTrue(strpos($alphabet, $result[$i]) !== false,
				'pattern "'.$token.'" produced "'.$result[$i].'", which is outside its alphabet');
		}
	}
}
