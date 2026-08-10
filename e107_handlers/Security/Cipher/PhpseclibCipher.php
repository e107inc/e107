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

use Exception;
use phpseclib3\Crypt\AES;

/**
 * AES-CBC in pure PHP, for hosts without ext-openssl.
 *
 * e107 does not declare ext-openssl as a requirement, so there has to be a
 * second way to reach AES. phpseclib needs no extensions at all and runs on
 * PHP 5.6.1 upwards. It is far slower than OpenSSL, which does not matter
 * for the few hundred octets a sealed token carries.
 *
 * phpseclib/phpseclib is in composer.json for exactly this reason. Take it
 * out and every host without ext-openssl loses sealed tokens silently, with
 * {@see CipherFactory::create()} returning false and no diagnostic anywhere.
 *
 * This file is only ever loaded when phpseclib is actually installed, which
 * {@see CipherFactory} decides. Nothing else may reference this class name
 * without making the same check first.
 *
 * Padding is left on phpseclib's default of PKCS#7. Do not call
 * disablePadding(): RFC 7518 section 5.2 requires a full extra block of
 * padding on a block-aligned plaintext.
 */
class PhpseclibCipher implements CbcCipherInterface
{
	/**
	 * {@inheritdoc}
	 */
	public static function isAvailable()
	{
		return class_exists('phpseclib3\\Crypt\\AES');
	}

	/**
	 * {@inheritdoc}
	 */
	public function name()
	{
		return 'phpseclib';
	}

	/**
	 * {@inheritdoc}
	 */
	public function encrypt($key, $iv, $plaintext)
	{
		$aes = self::prepare($key, $iv, $plaintext);

		if($aes === false)
		{
			return false;
		}

		try
		{
			$result = $aes->encrypt($plaintext);
		}
		catch(Exception $e)
		{
			return false;
		}

		return is_string($result) ? $result : false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function decrypt($key, $iv, $ciphertext)
	{
		$aes = self::prepare($key, $iv, $ciphertext);

		if($aes === false || $ciphertext === '' || strlen($ciphertext) % 16 !== 0)
		{
			return false;
		}

		try
		{
			$result = $aes->decrypt($ciphertext);
		}
		catch(Exception $e)
		{
			return false;
		}

		return is_string($result) ? $result : false;
	}

	/**
	 * Bad padding surfaces as a thrown LengthException on phpseclib 3 and as
	 * a false return on earlier lines of development, so both are handled.
	 *
	 * @param string $key
	 * @param string $iv
	 * @param string $data
	 * @return AES|false
	 */
	private static function prepare($key, $iv, $data)
	{
		if(!is_string($key) || !is_string($iv) || !is_string($data) || strlen($iv) !== 16)
		{
			return false;
		}

		if(!in_array(strlen($key), array(16, 24, 32), true))
		{
			return false;
		}

		$aes = new AES('cbc');
		$aes->setKey($key);
		$aes->setIV($iv);
		$aes->enablePadding();

		return $aes;
	}
}
