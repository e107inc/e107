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
 * Backend selection, and the promise that the choice cannot be observed.
 *
 * e107 does not require ext-openssl, so AES has to come from phpseclib when
 * the extension is missing. A token sealed on a host with OpenSSL must open
 * on a host without it, which only holds if both backends agree octet for
 * octet.
 */
class CipherFactoryTest extends \Test\Unit
{
	protected function _before()
	{
		require_once(e_HANDLER . 'Security/Cipher/CipherFactory.php');

		CipherFactory::reset();
	}

	protected function _after()
	{
		CipherFactory::reset();
	}

	public function testSomeBackendIsAvailable()
	{
		$this->assertNotEmpty(CipherFactory::available(), 'no AES backend at all');
		$this->assertTrue(CipherFactory::create() instanceof CbcCipherInterface);
	}

	public function testOpenSslIsPreferredWhenPresent()
	{
		if(!extension_loaded('openssl'))
		{
			$this->markTestSkipped('ext-openssl is not loaded.');
		}

		$available = CipherFactory::available();

		$this->assertSame(CipherFactory::OPENSSL, CipherFactory::create()->name());
		$this->assertSame(CipherFactory::OPENSSL, reset($available));
	}

	public function testCreateIsMemoised()
	{
		$this->assertSame(CipherFactory::create(), CipherFactory::create());
	}

	public function testUnknownBackendIsRefused()
	{
		$this->assertFalse(CipherFactory::backend('mcrypt'));
		$this->assertFalse(CipherFactory::backend(''));
		$this->assertFalse(CipherFactory::backend(null));
	}

	/**
	 * The phpseclib backend must not be loaded, or even named, on a host
	 * that has no phpseclib.
	 */
	public function testPhpseclibIsNotTouchedWhenAbsent()
	{
		if(class_exists('phpseclib3\\Crypt\\AES'))
		{
			$this->markTestSkipped('phpseclib is installed.');
		}

		$this->assertFalse(CipherFactory::backend(CipherFactory::PHPSECLIB));
		$this->assertNotContains(CipherFactory::PHPSECLIB, CipherFactory::available());
		$this->assertFalse(
			class_exists('e107\\Security\\Cipher\\PhpseclibCipher', false),
			'PhpseclibCipher was loaded on a host with no phpseclib'
		);
	}

	public function testBothBackendsProduceIdenticalCiphertext()
	{
		$backends = array();

		foreach(CipherFactory::available() as $name)
		{
			$backends[$name] = CipherFactory::backend($name);
		}

		if(count($backends) < 2)
		{
			$this->markTestSkipped('only one AES backend is available: ' . implode(', ', array_keys($backends)));
		}

		$plaintexts = array('', 'a', str_repeat("\x00", 16), 'the quick brown fox jumps', str_repeat("\xfe", 1000));

		foreach(array(16, 24, 32) as $keyLength)
		{
			$key = str_repeat("\x9c", $keyLength);
			$iv = str_repeat("\x3d", 16);

			foreach($plaintexts as $plaintext)
			{
				$reference = null;
				$referenceName = null;

				foreach($backends as $name => $backend)
				{
					$ciphertext = $backend->encrypt($key, $iv, $plaintext);

					$this->assertTrue(is_string($ciphertext), $name . ' failed to encrypt');
					$this->assertSame(
						$plaintext,
						$backend->decrypt($key, $iv, $ciphertext),
						$name . ' failed to round trip'
					);

					if($reference === null)
					{
						$reference = $ciphertext;
						$referenceName = $name;
						continue;
					}

					$this->assertSame(
						bin2hex($reference),
						bin2hex($ciphertext),
						$name . ' disagrees with ' . $referenceName . ' at key length ' . $keyLength
					);
				}

				foreach($backends as $name => $backend)
				{
					$this->assertSame(
						$plaintext,
						$backend->decrypt($key, $iv, $reference),
						$name . ' cannot open what ' . $referenceName . ' sealed'
					);
				}
			}
		}
	}

	public function testPaddingIsUnconditional()
	{
		$backend = CipherFactory::create();
		$key = str_repeat("\x1f", 32);
		$iv = str_repeat("\x2e", 16);

		$this->assertSame(16, strlen($backend->encrypt($key, $iv, '')), 'empty plaintext still pads');
		$this->assertSame(32, strlen($backend->encrypt($key, $iv, str_repeat('x', 16))), 'block aligned gains a block');
		$this->assertSame(32, strlen($backend->encrypt($key, $iv, str_repeat('x', 17))));
	}

	public function testMalformedArgumentsReturnFalse()
	{
		$backend = CipherFactory::create();
		$key = str_repeat("\x1f", 32);
		$iv = str_repeat("\x2e", 16);
		$ciphertext = $backend->encrypt($key, $iv, 'payload');

		$this->assertFalse($backend->encrypt(substr($key, 1), $iv, 'x'), 'a 31-octet key');
		$this->assertFalse($backend->encrypt($key, substr($iv, 1), 'x'), 'a 15-octet iv');
		$this->assertFalse($backend->encrypt('', $iv, 'x'), 'an empty key');
		$this->assertFalse($backend->decrypt($key, $iv, ''), 'an empty ciphertext');
		$this->assertFalse($backend->decrypt($key, $iv, substr($ciphertext, 1)), 'a ragged ciphertext');
	}

	/**
	 * Bad padding is a false, never an exception. The authentication tag is
	 * checked before this is reached, so this only ever fires on a bug, but
	 * a bug must not become a 500 on an anonymous endpoint.
	 *
	 * Counting rejections is not enough on its own: an assertion that at
	 * least one block was refused tolerates every other block being unpadded
	 * into nonsense and returned. So each block that is not refused has to
	 * re-encrypt to the ciphertext it came from, which no swallowed padding
	 * error can do.
	 */
	public function testBadPaddingReturnsFalse()
	{
		$backend = CipherFactory::create();
		$key = str_repeat("\x1f", 32);
		$iv = str_repeat("\x2e", 16);
		$rejected = 0;

		for($i = 0; $i < 64; $i++)
		{
			$garbage = str_repeat(chr($i), 32);
			$result = $backend->decrypt($key, $iv, $garbage);

			if($result === false)
			{
				$rejected++;

				continue;
			}

			$this->assertTrue(is_string($result), 'block ' . $i . ' came back as neither false nor a string');
			$this->assertSame(
				bin2hex($garbage),
				bin2hex($backend->encrypt($key, $iv, $result)),
				'block ' . $i . ' was unpadded into something that does not re-encrypt to it'
			);
		}

		$this->assertGreaterThan(0, $rejected, 'no random block was ever rejected as badly padded');
	}

	/**
	 * Why the JWE profile is AES-CBC-HMAC and not AES-GCM.
	 *
	 * PHP could not do AEAD before 7.1: openssl_encrypt() took five
	 * parameters, with no $tag and no $aad. aes-256-gcm was listed by
	 * openssl_get_cipher_methods() all the same, and the five-argument call
	 * SUCCEEDED, returning genuine GCM ciphertext and discarding the
	 * authentication tag on the way out. Measured on 5.6.40 and 7.0.33
	 * against 8.3: the same key, IV and plaintext give byte-identical
	 * ciphertext, and the 16 octets of tag that 8.3 hands back through its
	 * sixth parameter simply never exist. A JWE built that way ships an empty
	 * tag and authenticates nothing. The matching five-argument
	 * openssl_decrypt() then returns false even for untampered input, so such
	 * a site would mint tokens it could never open.
	 *
	 * The consequence for anyone modernising this: asking
	 * openssl_get_cipher_methods() whether the host has GCM proves nothing at
	 * all. A capability probe has to test PHP_VERSION_ID >= 70100.
	 */
	public function testAesGcmLosesItsTagOnThePhpVersionsE107SupportsBelow71()
	{
		if(!function_exists('openssl_encrypt') || !function_exists('openssl_get_cipher_methods'))
		{
			$this->markTestSkipped('ext-openssl is not loaded.');
		}

		if(!in_array('aes-256-gcm', array_map('strtolower', openssl_get_cipher_methods()), true))
		{
			$this->markTestSkipped('this OpenSSL build offers no aes-256-gcm.');
		}

		$key = str_repeat("\x0f", 32);
		$iv = str_repeat("\x1e", 12);
		$plaintext = 'the CAPTCHA answer';

		set_error_handler(function ()
		{
			return true;
		});

		try
		{
			$fiveArguments = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv);
		}
		finally
		{
			restore_error_handler();
		}

		if(PHP_VERSION_ID < 70100)
		{
			$this->assertTrue(is_string($fiveArguments), 'the five-argument call was refused after all');
			$this->assertSame(
				strlen($plaintext),
				strlen($fiveArguments),
				'the output is exactly the plaintext length, so the tag is unrecoverable'
			);

			return;
		}

		$this->assertFalse($fiveArguments, 'PHP 7.1 and later refuse the five-argument AEAD call');

		$tag = null;
		$sealed = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

		$this->assertSame(strlen($plaintext), strlen($sealed), 'the ciphertext carries no tag of its own');
		$this->assertSame(16, strlen($tag), 'the tag the five-argument form used to throw away');
	}

	/**
	 * Nothing in the security tree may name an AEAD cipher where PHP could
	 * pass it to OpenSSL. The docblocks discuss GCM at length and must go on
	 * doing so, so only string literals are read.
	 */
	public function testNoAeadCipherIsNamedInTheSecurityTree()
	{
		require_once(__DIR__ . '/../source_contract.php');

		$files = \e107\Security\SourceContract::phpFiles(e_HANDLER . 'Security');

		$this->assertGreaterThan(5, count($files), 'the security tree was not found');

		foreach($files as $file)
		{
			foreach(\e107\Security\SourceContract::stringLiterals($file) as $literal)
			{
				$this->assertSame(
					0,
					preg_match('~gcm|ccm|ocb|chacha|poly1305~i', $literal),
					basename($file) . ' names an AEAD cipher in a string literal: ' . $literal
				);
			}
		}
	}
}
