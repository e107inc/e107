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

/**
 * HMAC-based Extract-and-Expand Key Derivation Function, RFC 5869.
 *
 * PHP grew a native hash_hkdf() in 7.1.2, which is later than the oldest PHP
 * e107 supports, so the two steps are written out here over hash_hmac(). The
 * output is byte-identical to hash_hkdf() for every algorithm both accept;
 * the unit tests assert that on the platforms which have it.
 *
 * The point of the info parameter is domain separation. Two callers deriving
 * from the same master secret under different info strings get keys with no
 * usable relationship, so a token minted for one purpose cannot be opened
 * under another.
 *
 * Every method returns false rather than throwing, and an unsupported hash
 * algorithm is refused before it can reach hash_hmac(). An algorithm
 * hash_hmac() does not accept raises E_WARNING on PHP 5.6 and an uncatchable
 * ValueError on PHP 8, and one of the callers of this class runs inside an
 * image endpoint where a warning is emitted into the middle of the PNG.
 */
class Hkdf
{
	/**
	 * The hash algorithms this class will derive with.
	 *
	 * An allow-list, not everything the platform reports. hash_algos() also
	 * names checksums, adler32, crc32b, fnv164, joaat, murmur3a and the xxh
	 * family among them, which hash_hmac() rejects outright from PHP 7.2
	 * onwards and, far worse, accepts on PHP 5.6 through 7.1. Keying a 32-bit
	 * checksum as an HMAC derives key material with no strength at all and
	 * raises nothing while doing it.
	 *
	 * @var array
	 */
	private static $allowed = array('sha256', 'sha384', 'sha512');

	/**
	 * Cached output length in octets per hash algorithm.
	 *
	 * @var array
	 */
	private static $lengths = array();

	/**
	 * Whether a hash algorithm is one this class will use and this platform
	 * can key as an HMAC.
	 *
	 * @param string $algo hash algorithm name as hash_algos() spells it
	 * @return bool
	 */
	public static function isSupported($algo)
	{
		if(!is_string($algo) || !in_array($algo, self::$allowed, true))
		{
			return false;
		}

		if(function_exists('hash_hmac_algos'))
		{
			return in_array($algo, hash_hmac_algos(), true);
		}

		return in_array($algo, hash_algos(), true);
	}

	/**
	 * Output length of a hash algorithm in octets.
	 *
	 * @param string $algo
	 * @return int|false false when the algorithm is unavailable
	 */
	public static function hashLength($algo)
	{
		if(!self::isSupported($algo))
		{
			return false;
		}

		if(!isset(self::$lengths[$algo]))
		{
			self::$lengths[$algo] = strlen(hash_hmac($algo, '', '', true));
		}

		return self::$lengths[$algo];
	}

	/**
	 * RFC 5869 section 2.2 extract step.
	 *
	 * An empty salt is not the same as skipping the HMAC: the salt is
	 * replaced by HashLen zero octets, which is what an all-zero HMAC key
	 * amounts to anyway.
	 *
	 * @param string $algo
	 * @param string $ikm input keying material, raw octets
	 * @param string $salt optional salt, raw octets
	 * @return string|false pseudorandom key of HashLen octets
	 */
	public static function extract($algo, $ikm, $salt = '')
	{
		$hashLength = self::hashLength($algo);

		if($hashLength === false || !is_string($ikm) || !is_string($salt))
		{
			return false;
		}

		if($salt === '')
		{
			$salt = str_repeat("\0", $hashLength);
		}

		return hash_hmac($algo, $ikm, $salt, true);
	}

	/**
	 * RFC 5869 section 2.3 expand step.
	 *
	 * @param string $algo
	 * @param string $prk pseudorandom key from {@see Hkdf::extract()}
	 * @param string $info context and application specific information
	 * @param int $length wanted output length in octets, at most 255 * HashLen
	 * @return string|false output keying material of exactly $length octets
	 */
	public static function expand($algo, $prk, $info, $length)
	{
		$hashLength = self::hashLength($algo);
		$length = (int) $length;

		if($hashLength === false || !is_string($prk) || !is_string($info))
		{
			return false;
		}

		if($length < 1 || $length > 255 * $hashLength || strlen($prk) < $hashLength)
		{
			return false;
		}

		$rounds = (int) ceil($length / $hashLength);
		$okm = '';
		$block = '';

		for($counter = 1; $counter <= $rounds; $counter++)
		{
			$block = hash_hmac($algo, $block . $info . chr($counter), $prk, true);
			$okm .= $block;
		}

		return substr($okm, 0, $length);
	}

	/**
	 * Both steps in one call, which is how callers should use this class.
	 *
	 * @param string $algo
	 * @param string $ikm input keying material, raw octets
	 * @param int $length wanted output length in octets
	 * @param string $info context and application specific information
	 * @param string $salt optional salt, raw octets
	 * @return string|false
	 */
	public static function derive($algo, $ikm, $length, $info = '', $salt = '')
	{
		$prk = self::extract($algo, $ikm, $salt);

		if($prk === false)
		{
			return false;
		}

		return self::expand($algo, $prk, $info, $length);
	}
}
