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
 * Runtime selection of an AES-CBC backend.
 *
 * OpenSSL when the extension is loaded, phpseclib when it is not. Both
 * produce identical ciphertext for identical inputs, which the unit tests
 * assert on any host that has both, so the choice is invisible to callers.
 *
 * Backends are required by path rather than autoloaded, so this works in the
 * bootstrap contexts that run before the e107 autoloader is registered, the
 * installer and MYSQL_LIGHT among them. The phpseclib backend is only
 * reached, and its file only read, once phpseclib3\Crypt\AES is known to
 * exist; nothing here mentions that class otherwise.
 */
class CipherFactory
{
	const OPENSSL = 'openssl';
	const PHPSECLIB = 'phpseclib';

	/**
	 * @var CbcCipherInterface|null
	 */
	private static $instance = null;

	/**
	 * The backend this host will use, memoised.
	 *
	 * @return CbcCipherInterface|false false when the host can do neither
	 */
	public static function create()
	{
		if(self::$instance === null)
		{
			foreach(self::available() as $name)
			{
				$backend = self::backend($name);

				if($backend !== false)
				{
					self::$instance = $backend;
					break;
				}
			}
		}

		return self::$instance === null ? false : self::$instance;
	}

	/**
	 * Names of every usable backend, most preferred first.
	 *
	 * @return array of strings, possibly empty
	 */
	public static function available()
	{
		$names = array();

		require_once(__DIR__ . '/CbcCipherInterface.php');
		require_once(__DIR__ . '/OpenSslCipher.php');

		if(OpenSslCipher::isAvailable())
		{
			$names[] = self::OPENSSL;
		}

		if(class_exists('phpseclib3\\Crypt\\AES'))
		{
			$names[] = self::PHPSECLIB;
		}

		return $names;
	}

	/**
	 * A named backend, unmemoised, for tests and for callers that have a
	 * reason to pin one.
	 *
	 * @param string $name one of the class constants
	 * @return CbcCipherInterface|false false when that backend is unusable here
	 */
	public static function backend($name)
	{
		require_once(__DIR__ . '/CbcCipherInterface.php');

		if($name === self::OPENSSL)
		{
			require_once(__DIR__ . '/OpenSslCipher.php');

			return OpenSslCipher::isAvailable() ? new OpenSslCipher() : false;
		}

		if($name === self::PHPSECLIB && class_exists('phpseclib3\\Crypt\\AES'))
		{
			require_once(__DIR__ . '/PhpseclibCipher.php');

			return new PhpseclibCipher();
		}

		return false;
	}

	/**
	 * Drop the memoised backend. Tests only.
	 *
	 * @return void
	 */
	public static function reset()
	{
		self::$instance = null;
	}
}
