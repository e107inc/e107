<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Coverage for GHSA-gm6q-rqm6-p9m4.
 *
 * Swapping rand() for a CSPRNG had to leave every secret's shape alone: these
 * values are compared against database columns of a fixed width, matched by
 * regular expressions elsewhere in the tree, and in the reset code's case fed
 * back through preg_replace("#[\W_]#", "", ...) before the comparison, so a
 * widened alphabet would silently break redemption rather than fail loudly.
 */
class randomSecretFormatsTest extends \Codeception\Test\Unit
{
	/** @var UserHandler */
	private $usr;

	/**
	 * The exact alphabets generateRandomString() documents. O, o and l are absent
	 * from the alpha set so a human retyping a password cannot confuse them with
	 * digits.
	 */
	const ALPHA = 'AaBbCcDdEeFfGgHhIiJjKkLMmNnPpQqRrSsTtUuVvWwXxYyZz';
	const DIGIT = '0123456789';
	const SYMBOLS = '~!@#$%^*-+?;:';

	protected function _before()
	{
		e107::getInstance();
		require_once(e_HANDLER . 'random_handler.php');

		$this->usr = $this->make('UserHandler');
	}

	private function alphaNum()
	{
		return self::ALPHA . self::DIGIT . '-_';
	}

	/**
	 * @param string $value
	 * @param string $set every character of $value must come from this set
	 * @return void
	 */
	private function assertDrawnFrom($value, $set)
	{
		for($i = 0, $length = strlen($value); $i < $length; $i++)
		{
			$this->assertNotFalse(
				strpos($set, $value[$i]),
				'Character ' . var_export($value[$i], true) . ' is outside the documented alphabet.'
			);
		}
	}

	public function testAlphaPattern()
	{
		for($i = 0; $i < 100; $i++)
		{
			$value = $this->usr->generateRandomString('####');

			$this->assertSame(4, strlen($value));
			$this->assertDrawnFrom($value, self::ALPHA);
		}
	}

	public function testNumericPattern()
	{
		for($i = 0; $i < 100; $i++)
		{
			$value = $this->usr->generateRandomString('.....');

			$this->assertSame(5, strlen($value));
			$this->assertSame(1, preg_match('/^[0-9]{5}$/', $value));
		}
	}

	public function testAlphanumericPattern()
	{
		for($i = 0; $i < 100; $i++)
		{
			$value = $this->usr->generateRandomString('********');

			$this->assertSame(8, strlen($value));
			$this->assertDrawnFrom($value, $this->alphaNum());
		}
	}

	public function testSymbolPattern()
	{
		for($i = 0; $i < 100; $i++)
		{
			$value = $this->usr->generateRandomString('!!!!');

			$this->assertSame(4, strlen($value));
			$this->assertDrawnFrom($value, self::SYMBOLS);
		}
	}

	public function testAlphanumericSymbolPattern()
	{
		for($i = 0; $i < 100; $i++)
		{
			$value = $this->usr->generateRandomString('??????');

			$this->assertSame(6, strlen($value));
			$this->assertDrawnFrom($value, $this->alphaNum() . self::SYMBOLS);
		}
	}

	/**
	 * '^' consumes the seed one character at a time and, once the seed is spent,
	 * appends nothing at all. e107_admin/cron.php relies on the first half and is
	 * bitten by the second, taking 7 characters from a 9-character pattern.
	 */
	public function testSeededPattern()
	{
		$this->assertSame('seed', $this->usr->generateRandomString('^^^^', 'seed'));
		$this->assertSame('se', $this->usr->generateRandomString('^^^^', 'se'));

		$value = $this->usr->generateRandomString('^^..', 'ab');

		$this->assertSame(4, strlen($value));
		$this->assertSame('ab', substr($value, 0, 2));
		$this->assertSame(1, preg_match('/^[0-9]{2}$/', substr($value, 2)));
	}

	/**
	 * Alphanumerics in the pattern are copied through; anything else is dropped.
	 */
	public function testLiteralCharactersInThePattern()
	{
		$this->assertSame('abc123', $this->usr->generateRandomString('abc123'));
		$this->assertSame('', $this->usr->generateRandomString('()<>'));
	}

	public function testDefaultPattern()
	{
		$value = $this->usr->generateRandomString();

		$this->assertSame(6, strlen($value));
		$this->assertDrawnFrom(substr($value, 0, 2), self::ALPHA);
		$this->assertSame(1, preg_match('/^[0-9]{4}$/', substr($value, 2)));
	}

	public function testConsecutiveCallsDiffer()
	{
		$seen = array();

		for($i = 0; $i < 200; $i++)
		{
			$value = $this->usr->generateRandomString('############');
			$this->assertArrayNotHasKey($value, $seen);
			$seen[$value] = true;
		}
	}

	/**
	 * The fpw.php reset code. It travels in the query string and the redemption
	 * path strips every non-word character before demanding equality, so anything
	 * outside [A-Za-z0-9] would make the emailed link unusable.
	 */
	public function testPasswordResetCodeShape()
	{
		for($i = 0; $i < 100; $i++)
		{
			$code = $this->usr->generateRandomString('############');

			$this->assertSame(12, strlen($code));
			$this->assertSame(1, preg_match('/^[A-Za-z0-9]{12}$/', $code));
			$this->assertSame($code, preg_replace('#[\W_]#', '', $code));
		}
	}

	/**
	 * Account-activation tokens and admin-issued user_sess values. The column is
	 * varchar(100) and several comparisons are made against md5-shaped strings.
	 */
	public function testRandomKeyShape()
	{
		for($i = 0; $i < 50; $i++)
		{
			$key = e_user_model::randomKey();

			$this->assertSame(32, strlen($key));
			$this->assertSame(1, preg_match('/^[a-f0-9]{32}$/', $key));
		}
	}

	/**
	 * The CHAP challenge used to be sha1(time() . rand() . $sid). Anything that
	 * validates a login against it, including the JavaScript that hashes the
	 * password client-side, treats it as 40 lowercase hex characters.
	 */
	public function testChapChallengeShape()
	{
		for($i = 0; $i < 50; $i++)
		{
			$challenge = e_random::hex(40);

			$this->assertSame(40, strlen($challenge));
			$this->assertSame(1, preg_match('/^[a-f0-9]{40}$/', $challenge));
		}
	}

	/**
	 * The auto-generated password an operator's password reset emails out. The
	 * length is picked at random too, so pin the range rather than one value.
	 */
	public function testResetPasswordShape()
	{
		for($i = 0; $i < 50; $i++)
		{
			$password = $this->usr->generateRandomString(str_repeat('*', e_random::int(8, 12)));

			$this->assertGreaterThanOrEqual(8, strlen($password));
			$this->assertLessThanOrEqual(12, strlen($password));
			$this->assertDrawnFrom($password, $this->alphaNum());
		}
	}
}
