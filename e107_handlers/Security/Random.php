<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Security;

use Exception;

/**
 * Fail-closed source of cryptographically secure randomness.
 *
 * Every token, key, password, nonce and CAPTCHA answer in e107 draws from
 * here. There is deliberately no weak fallback: when the platform offers no
 * CSPRNG at all, every method throws {@see RandomException} and the request
 * fails, because a guessable secret is worse than an unavailable site.
 *
 * {@see Random::isAvailable()} exists so the installer and the upgrade
 * requirement checks can report the problem before an operator meets it as a
 * white page. A false result does not soften the runtime contract; the other
 * methods still throw.
 *
 * On PHP 7 and later random_bytes() and random_int() are built in. On PHP 5.6
 * they are supplied by the vendored paragonie/random_compat polyfill, which
 * this class loads on demand when the Composer autoloader has not run yet.
 *
 * Aliased to the v2-style name `e_random` by
 * e107_handlers/random_handler.php. The public surface is kept identical to
 * the `e_random` class shipped on release/v2.3.x, so a fix can be moved
 * between the branches unchanged.
 */
class Random
{
	/**
	 * Operator-facing diagnostic naming what PHP is missing.
	 */
	const UNAVAILABLE = 'No cryptographically secure random number generator is available. e107 needs random_bytes() and random_int(), which are native on PHP 7 or later. On PHP 5.6 the vendored paragonie/random_compat polyfill supplies them, but it still requires one of: a readable /dev/urandom, the OpenSSL extension, libsodium, or mcrypt.';

	/**
	 * Random bytes.
	 *
	 * @param int $length number of bytes, must be at least 1
	 * @return string raw binary string of exactly $length bytes
	 * @throws RandomException when no CSPRNG is available
	 */
	public static function bytes($length)
	{
		$length = (int) $length;

		if($length < 1)
		{
			throw new RandomException('e_random::bytes() requires a length of at least 1 byte.');
		}

		self::assertAvailable();

		try
		{
			return random_bytes($length);
		}
		catch(Exception $e)
		{
			throw new RandomException(self::UNAVAILABLE, 0, $e);
		}
	}

	/**
	 * Random bytes rendered as lowercase hexadecimal.
	 *
	 * $length is a count of HEX CHARACTERS, not of bytes, so hex(40) returns
	 * the same 40 characters sha1() would have. An odd length is honoured.
	 *
	 * @param int $length number of hex characters, must be at least 1
	 * @return string exactly $length characters matching [0-9a-f]
	 * @throws RandomException when no CSPRNG is available
	 */
	public static function hex($length)
	{
		$length = (int) $length;

		if($length < 1)
		{
			throw new RandomException('e_random::hex() requires a length of at least 1 character.');
		}

		return (string) substr(bin2hex(self::bytes((int) ceil($length / 2))), 0, $length);
	}

	/**
	 * Uniformly distributed random integer, both bounds inclusive.
	 *
	 * @param int $min
	 * @param int $max must not be lower than $min
	 * @return int
	 * @throws RandomException when no CSPRNG is available
	 */
	public static function int($min, $max)
	{
		$min = (int) $min;
		$max = (int) $max;

		if($min > $max)
		{
			throw new RandomException('e_random::int() requires $min to be no greater than $max.');
		}

		if($min === $max)
		{
			return $min;
		}

		self::assertAvailable();

		try
		{
			return random_int($min, $max);
		}
		catch(Exception $e)
		{
			throw new RandomException(self::UNAVAILABLE, 0, $e);
		}
	}

	/**
	 * One element drawn uniformly from an array, by value.
	 *
	 * @param array $values non-empty
	 * @return mixed
	 * @throws RandomException when no CSPRNG is available, or $values is empty
	 */
	public static function pick(array $values)
	{
		if(empty($values))
		{
			throw new RandomException('e_random::pick() requires a non-empty array.');
		}

		$values = array_values($values);

		return $values[self::int(0, count($values) - 1)];
	}

	/**
	 * Probe for a usable CSPRNG without raising.
	 *
	 * Intended for the installer and the upgrade requirement checks, so an
	 * operator is told up front instead of meeting a fatal error later.
	 *
	 * @return bool
	 */
	public static function isAvailable()
	{
		try
		{
			self::bytes(1);
			self::int(0, 1);
		}
		catch(RandomException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * @return void
	 * @throws RandomException when no CSPRNG is available
	 */
	private static function assertAvailable()
	{
		if(function_exists('random_bytes') && function_exists('random_int'))
		{
			return;
		}

		$autoload = __DIR__ . '/../vendor/autoload.php';

		if(is_readable($autoload))
		{
			include_once($autoload);
		}

		if(function_exists('random_bytes') && function_exists('random_int'))
		{
			return;
		}

		throw new RandomException(self::UNAVAILABLE);
	}
}
