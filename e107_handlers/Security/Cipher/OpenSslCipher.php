<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Security\Cipher;

/**
 * AES-CBC through ext-openssl, the fast path.
 *
 * openssl_encrypt() with OPENSSL_RAW_DATA and without OPENSSL_ZERO_PADDING
 * applies PKCS#7 itself, including a whole extra block when the plaintext is
 * already a multiple of the block size, which is exactly what RFC 7518
 * section 5.2 requires.
 *
 * The availability probe names CBC ciphers only. It is safe to ask
 * openssl_get_cipher_methods() about a CBC mode, unlike GCM: on PHP below
 * 7.1 aes-256-gcm is listed and openssl_encrypt() succeeds while quietly
 * dropping the authentication tag, because the $tag and $aad parameters did
 * not exist yet. Nothing in e107 may probe for an AEAD mode by name.
 */
class OpenSslCipher implements CbcCipherInterface
{
	/**
	 * Cipher name per key length in octets.
	 *
	 * @var array
	 */
	private static $ciphers = array(
		16 => 'aes-128-cbc',
		24 => 'aes-192-cbc',
		32 => 'aes-256-cbc',
	);

	/**
	 * {@inheritdoc}
	 */
	public static function isAvailable()
	{
		if(!function_exists('openssl_encrypt') || !function_exists('openssl_decrypt') || !function_exists('openssl_get_cipher_methods'))
		{
			return false;
		}

		$methods = openssl_get_cipher_methods();

		if(!is_array($methods))
		{
			return false;
		}

		$methods = array_map('strtolower', $methods);

		foreach(self::$ciphers as $cipher)
		{
			if(!in_array($cipher, $methods, true))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function name()
	{
		return 'openssl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function encrypt($key, $iv, $plaintext)
	{
		$cipher = self::cipherFor($key, $iv, $plaintext);

		if($cipher === false)
		{
			return false;
		}

		$result = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);

		return is_string($result) ? $result : false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function decrypt($key, $iv, $ciphertext)
	{
		$cipher = self::cipherFor($key, $iv, $ciphertext);

		if($cipher === false || $ciphertext === '' || strlen($ciphertext) % 16 !== 0)
		{
			return false;
		}

		$result = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);

		return is_string($result) ? $result : false;
	}

	/**
	 * @param string $key
	 * @param string $iv
	 * @param string $data
	 * @return string|false
	 */
	private static function cipherFor($key, $iv, $data)
	{
		if(!is_string($key) || !is_string($iv) || !is_string($data) || strlen($iv) !== 16)
		{
			return false;
		}

		$length = strlen($key);

		return isset(self::$ciphers[$length]) ? self::$ciphers[$length] : false;
	}
}
