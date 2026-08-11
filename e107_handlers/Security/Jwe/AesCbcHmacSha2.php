<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Security\Jwe;

use e107\Security\Cipher\CipherFactory;

require_once(__DIR__ . '/../Cipher/CipherFactory.php');

/**
 * AES_CBC_HMAC_SHA2 authenticated encryption, RFC 7518 section 5.2.
 *
 * Encrypt-then-MAC over AES-CBC, which is what a JOSE implementation has to
 * use when the platform cannot do AES-GCM. PHP could not do AEAD at all
 * before 7.1: openssl_encrypt() took five parameters with no $tag and no
 * $aad, and aes-256-gcm nonetheless appeared in openssl_get_cipher_methods()
 * and encrypted happily while discarding the tag.
 *
 * Raw octets in, raw octets out. Base64, JSON, headers and key management
 * are somebody else's problem, {@see Compact}.
 *
 * The four details that implementations of this get wrong:
 *
 *  - the key is split MAC first, encryption second, which is the reverse of
 *    the reading order suggested by the name AES-CBC-HMAC
 *  - AL is the length of the additional authenticated data in BITS, as a
 *    64-bit big-endian integer, not its length in octets
 *  - the authentication tag is the HMAC truncated to half the hash output,
 *    so 24 octets for HS384, not 16
 *  - the MAC covers AAD || IV || ciphertext || AL in that order, and it is
 *    checked before a single block is decrypted. Decrypting first and
 *    comparing afterwards is a padding oracle.
 */
class AesCbcHmacSha2
{
	/**
	 * Octets of initialisation vector, one AES block.
	 */
	const IV_LENGTH = 16;

	/**
	 * Longest ciphertext {@see AesCbcHmacSha2::decrypt()} will authenticate.
	 *
	 * {@see Compact::MAX_LENGTH} caps the whole token an order of magnitude
	 * below this, and on the CAPTCHA endpoint that is the cap which actually
	 * bites. This one exists because decrypt() is public and a second caller
	 * would otherwise be free to hand it an eight megabyte string and have it
	 * HMAC the lot before returning false.
	 */
	const MAX_CIPHERTEXT = 65536;

	/**
	 * Per-algorithm hash and combined key length in octets.
	 *
	 * The encryption key, the MAC key and the tag are each half the
	 * combined length.
	 *
	 * @var array
	 */
	private static $algorithms = array(
		'A128CBC-HS256' => array('hash' => 'sha256', 'keyLength' => 32),
		'A192CBC-HS384' => array('hash' => 'sha384', 'keyLength' => 48),
		'A256CBC-HS512' => array('hash' => 'sha512', 'keyLength' => 64),
	);

	/**
	 * Whether an enc value is one this class implements and this host can run.
	 *
	 * @param string $enc JWE enc header value
	 * @return bool
	 */
	public static function isSupported($enc)
	{
		if(!is_string($enc) || !isset(self::$algorithms[$enc]))
		{
			return false;
		}

		if(!in_array(self::$algorithms[$enc]['hash'], hash_algos(), true))
		{
			return false;
		}

		return CipherFactory::create() !== false;
	}

	/**
	 * Combined content encryption key length in octets.
	 *
	 * @param string $enc
	 * @return int|false
	 */
	public static function keyLength($enc)
	{
		if(!is_string($enc) || !isset(self::$algorithms[$enc]))
		{
			return false;
		}

		return self::$algorithms[$enc]['keyLength'];
	}

	/**
	 * Authentication tag length in octets.
	 *
	 * @param string $enc
	 * @return int|false
	 */
	public static function tagLength($enc)
	{
		$keyLength = self::keyLength($enc);

		return $keyLength === false ? false : (int) ($keyLength / 2);
	}

	/**
	 * The IV has to be fresh from a CSPRNG for every single call, and this
	 * class cannot check that for you. AES-CBC under a fixed key and a fixed
	 * IV is deterministic, so two payloads sharing a prefix produce identical
	 * leading ciphertext blocks and an observer can bucket tokens by their
	 * first block. Deriving an IV from the time, the session or a constant is
	 * the classic way to throw the whole construction away. Callers take it
	 * from {@see \e107\Security\Random::bytes()}.
	 *
	 * @param string $enc
	 * @param string $key combined key of {@see AesCbcHmacSha2::keyLength()} octets
	 * @param string $iv raw octets, exactly {@see AesCbcHmacSha2::IV_LENGTH}
	 * @param string $plaintext raw octets
	 * @param string $aad additional authenticated data, raw octets
	 * @return array|false array('ciphertext' => string, 'tag' => string)
	 */
	public static function encrypt($enc, $key, $iv, $plaintext, $aad)
	{
		if(!self::acceptable($enc, $key, $iv, $aad) || !is_string($plaintext))
		{
			return false;
		}

		$cipher = CipherFactory::create();

		if($cipher === false)
		{
			return false;
		}

		$ciphertext = $cipher->encrypt(self::encryptionKey($enc, $key), $iv, $plaintext);

		if(!is_string($ciphertext) || $ciphertext === '')
		{
			return false;
		}

		return array(
			'ciphertext' => $ciphertext,
			'tag'        => self::tag($enc, $key, $iv, $ciphertext, $aad),
		);
	}

	/**
	 * @param string $enc
	 * @param string $key combined key of {@see AesCbcHmacSha2::keyLength()} octets
	 * @param string $iv raw octets, exactly {@see AesCbcHmacSha2::IV_LENGTH}
	 * @param string $ciphertext raw octets, a non-zero multiple of 16, at most
	 *                    {@see AesCbcHmacSha2::MAX_CIPHERTEXT}
	 * @param string $tag raw octets, exactly {@see AesCbcHmacSha2::tagLength()}
	 * @param string $aad additional authenticated data, raw octets
	 * @return string|false plaintext, or false for any failure at all.
	 *                    An empty string is a legitimate plaintext, so test
	 *                    the return with === false and never for truthiness
	 */
	public static function decrypt($enc, $key, $iv, $ciphertext, $tag, $aad)
	{
		if(!self::acceptable($enc, $key, $iv, $aad) || !is_string($ciphertext) || !is_string($tag))
		{
			return false;
		}

		if($ciphertext === '' || strlen($ciphertext) % 16 !== 0 || strlen($ciphertext) > self::MAX_CIPHERTEXT)
		{
			return false;
		}

		if(strlen($tag) !== self::tagLength($enc))
		{
			return false;
		}

		if(!hash_equals(self::tag($enc, $key, $iv, $ciphertext, $aad), $tag))
		{
			return false;
		}

		$cipher = CipherFactory::create();

		if($cipher === false)
		{
			return false;
		}

		$plaintext = $cipher->decrypt(self::encryptionKey($enc, $key), $iv, $ciphertext);

		return is_string($plaintext) ? $plaintext : false;
	}

	/**
	 * The authentication tag over AAD || IV || ciphertext || AL.
	 *
	 * @param string $enc
	 * @param string $key
	 * @param string $iv
	 * @param string $ciphertext
	 * @param string $aad
	 * @return string raw octets, truncated to half the hash output
	 */
	private static function tag($enc, $key, $iv, $ciphertext, $aad)
	{
		$algorithm = self::$algorithms[$enc];
		$input = $aad . $iv . $ciphertext . self::al($aad);
		$mac = hash_hmac($algorithm['hash'], $input, self::macKey($enc, $key), true);

		return (string) substr($mac, 0, (int) ($algorithm['keyLength'] / 2));
	}

	/**
	 * AL: the length of the additional authenticated data in bits, as an
	 * unsigned 64-bit big-endian integer.
	 *
	 * Built from two 32-bit words because pack('J') arrived in PHP 5.6.3 and
	 * is unavailable on 32-bit builds. The shift is guarded for the same
	 * reason: where PHP_INT_SIZE is 4, a >> 32 is undefined and the high
	 * word is always zero anyway, an AAD would have to reach 512MB to make
	 * it anything else.
	 *
	 * @param string $aad
	 * @return string exactly 8 octets
	 */
	private static function al($aad)
	{
		$octets = strlen($aad);

		if(PHP_INT_SIZE < 8)
		{
			return pack('N2', 0, $octets * 8);
		}

		$bits = $octets * 8;

		return pack('N2', ($bits >> 32) & 0xffffffff, $bits & 0xffffffff);
	}

	/**
	 * The FIRST half of the combined key.
	 *
	 * @param string $enc
	 * @param string $key
	 * @return string
	 */
	private static function macKey($enc, $key)
	{
		return (string) substr($key, 0, (int) (self::$algorithms[$enc]['keyLength'] / 2));
	}

	/**
	 * The SECOND half of the combined key.
	 *
	 * @param string $enc
	 * @param string $key
	 * @return string
	 */
	private static function encryptionKey($enc, $key)
	{
		return (string) substr($key, (int) (self::$algorithms[$enc]['keyLength'] / 2));
	}

	/**
	 * @param string $enc
	 * @param string $key
	 * @param string $iv
	 * @param string $aad
	 * @return bool
	 */
	private static function acceptable($enc, $key, $iv, $aad)
	{
		if(!self::isSupported($enc) || !is_string($key) || !is_string($iv) || !is_string($aad))
		{
			return false;
		}

		return strlen($key) === self::$algorithms[$enc]['keyLength'] && strlen($iv) === self::IV_LENGTH;
	}
}
