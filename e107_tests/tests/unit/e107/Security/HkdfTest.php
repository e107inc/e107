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
 * RFC 5869 test vectors, plus the contract the key derivation depends on.
 *
 * The vectors in tests/_data/jose_vectors.php were extracted from the RFC
 * text programmatically and recomputed from the algorithm definition before
 * being trusted. If one of these fails, the implementation is wrong.
 */
class HkdfTest extends \Test\Unit
{
	/**
	 * @var array
	 */
	protected $vectors = array();

	protected function _before()
	{
		require_once(e_HANDLER . 'Security/Hkdf.php');

		$this->vectors = include(codecept_data_dir() . 'jose_vectors.php');
	}

	public function testExtractMatchesRfc5869()
	{
		foreach($this->vectors['hkdf_sha256'] as $name => $vector)
		{
			$prk = Hkdf::extract($vector['hash'], hex2bin($vector['ikm']), hex2bin($vector['salt']));

			$this->assertSame($vector['prk'], bin2hex($prk), 'RFC 5869 ' . $name . ' PRK');
		}
	}

	public function testExpandMatchesRfc5869()
	{
		foreach($this->vectors['hkdf_sha256'] as $name => $vector)
		{
			$okm = Hkdf::expand($vector['hash'], hex2bin($vector['prk']), hex2bin($vector['info']), $vector['length']);

			$this->assertSame($vector['okm'], bin2hex($okm), 'RFC 5869 ' . $name . ' OKM');
			$this->assertSame($vector['length'], strlen($okm), 'RFC 5869 ' . $name . ' OKM length');
		}
	}

	public function testDeriveMatchesRfc5869()
	{
		foreach($this->vectors['hkdf_sha256'] as $name => $vector)
		{
			$okm = Hkdf::derive(
				$vector['hash'],
				hex2bin($vector['ikm']),
				$vector['length'],
				hex2bin($vector['info']),
				hex2bin($vector['salt'])
			);

			$this->assertSame($vector['okm'], bin2hex($okm), 'RFC 5869 ' . $name . ' one-shot');
		}
	}

	/**
	 * hash_hkdf() is PHP 7.1.2 and later, which is why this class exists at
	 * all. Where it is present the two must agree octet for octet.
	 */
	public function testAgreesWithNativeHashHkdf()
	{
		if(PHP_VERSION_ID < 70102 || !function_exists('hash_hkdf'))
		{
			$this->markTestSkipped('hash_hkdf() requires PHP 7.1.2 or later.');
		}

		foreach($this->vectors['hkdf_sha256'] as $name => $vector)
		{
			$native = hash_hkdf(
				$vector['hash'],
				hex2bin($vector['ikm']),
				$vector['length'],
				hex2bin($vector['info']),
				hex2bin($vector['salt'])
			);

			$ours = Hkdf::derive(
				$vector['hash'],
				hex2bin($vector['ikm']),
				$vector['length'],
				hex2bin($vector['info']),
				hex2bin($vector['salt'])
			);

			$this->assertSame(bin2hex($native), bin2hex($ours), 'native hash_hkdf() ' . $name);
		}
	}

	/**
	 * sha512 is what e107 derives with, and it is not covered by the RFC's
	 * own appendix.
	 */
	public function testAgreesWithNativeHashHkdfOnSha512()
	{
		if(PHP_VERSION_ID < 70102 || !function_exists('hash_hkdf'))
		{
			$this->markTestSkipped('hash_hkdf() requires PHP 7.1.2 or later.');
		}

		$ikm = str_repeat("\x2a", 64);
		$salt = 'e107';

		foreach(array(1, 32, 64, 65, 200) as $length)
		{
			$this->assertSame(
				bin2hex(hash_hkdf('sha512', $ikm, $length, 'captcha', $salt)),
				bin2hex(Hkdf::derive('sha512', $ikm, $length, 'captcha', $salt)),
				'sha512 at length ' . $length
			);
		}
	}

	/**
	 * An empty salt means HashLen zero octets, not "skip the HMAC".
	 *
	 * No assertion can catch the zero fill being deleted. HMAC pads any key
	 * shorter than its block size with zeroes itself, and every algorithm
	 * here has a block size larger than its output, so the two spellings are
	 * equal by construction. The earlier test compared them to each other and
	 * was therefore a tautology. What is worth pinning is the RFC's own
	 * zero-salt case and agreement with the native implementation, which
	 * catch an empty salt being mishandled in any way that could matter.
	 */
	public function testEmptySaltMatchesTheRfcAndTheNativeImplementation()
	{
		$vector = $this->vectors['hkdf_sha256']['A.3'];

		$this->assertSame('', $vector['salt'], 'RFC 5869 A.3 is the zero-length salt case');
		$this->assertSame(
			$vector['prk'],
			bin2hex(Hkdf::extract($vector['hash'], hex2bin($vector['ikm']))),
			'A.3 PRK with the salt argument left off entirely'
		);

		if(PHP_VERSION_ID < 70102 || !function_exists('hash_hkdf'))
		{
			$this->markTestSkipped('hash_hkdf() requires PHP 7.1.2 or later.');
		}

		$this->assertSame(
			bin2hex(hash_hkdf('sha512', 'material', 64, 'captcha', '')),
			bin2hex(Hkdf::derive('sha512', 'material', 64, 'captcha')),
			'sha512 against an explicitly empty salt'
		);
	}

	/**
	 * hash_algos() is not the right question to ask.
	 *
	 * It also names checksums. hash_hmac() refuses those with an uncatchable
	 * ValueError from PHP 7.2 onwards, which is the very failure the
	 * availability check exists to prevent, and accepts them in silence on
	 * PHP 5.6 through 7.1, which is worse: Hkdf::derive('crc32b', ...) would
	 * hand back 64 octets of key material stretched out of a 32-bit checksum
	 * and raise nothing at all.
	 */
	public function testAlgorithmsOutsideTheAllowListAreRefusedInSilence()
	{
		$allowed = array('sha256', 'sha384', 'sha512');
		$available = hash_algos();
		$raised = array();

		$this->assertContains('crc32b', $available, 'hash_algos() no longer lists a checksum, so this proves nothing');

		set_error_handler(function ($number, $message) use (&$raised)
		{
			$raised[] = $message;

			return true;
		});

		try
		{
			foreach($available as $algo)
			{
				if(in_array($algo, $allowed, true))
				{
					$this->assertTrue(Hkdf::isSupported($algo), $algo . ' is refused');

					continue;
				}

				$this->assertFalse(Hkdf::isSupported($algo), $algo . ' is accepted');
				$this->assertFalse(Hkdf::hashLength($algo), $algo . ' reported a length');
				$this->assertFalse(Hkdf::extract($algo, 'material'), $algo . ' extracted');
				$this->assertFalse(Hkdf::expand($algo, str_repeat("\0", 64), '', 32), $algo . ' expanded');
				$this->assertFalse(Hkdf::derive($algo, 'material', 32, 'captcha'), $algo . ' derived');
			}
		}
		finally
		{
			restore_error_handler();
		}

		$this->assertSame(array(), $raised, 'a diagnostic escaped into the output');
	}

	public function testInfoSeparatesKeys()
	{
		$ikm = str_repeat("\x01", 64);

		$this->assertNotSame(
			bin2hex(Hkdf::derive('sha512', $ikm, 64, 'captcha')),
			bin2hex(Hkdf::derive('sha512', $ikm, 64, 'csrf'))
		);
	}

	public function testUnsupportedAlgorithmReturnsFalseAndDoesNotWarn()
	{
		$this->assertFalse(Hkdf::isSupported('no-such-hash'));
		$this->assertFalse(Hkdf::hashLength('no-such-hash'));
		$this->assertFalse(Hkdf::extract('no-such-hash', 'x'));
		$this->assertFalse(Hkdf::expand('no-such-hash', 'x', '', 16));
		$this->assertFalse(Hkdf::derive('no-such-hash', 'x', 16));
	}

	public function testHashLength()
	{
		$this->assertSame(32, Hkdf::hashLength('sha256'));
		$this->assertSame(48, Hkdf::hashLength('sha384'));
		$this->assertSame(64, Hkdf::hashLength('sha512'));
	}

	public function testExpandRejectsOutOfRangeLengths()
	{
		$prk = Hkdf::extract('sha256', 'material');

		$this->assertFalse(Hkdf::expand('sha256', $prk, '', 0));
		$this->assertFalse(Hkdf::expand('sha256', $prk, '', -1));
		$this->assertFalse(Hkdf::expand('sha256', $prk, '', 255 * 32 + 1));
		$this->assertSame(255 * 32, strlen(Hkdf::expand('sha256', $prk, '', 255 * 32)));
	}

	public function testExpandRejectsShortPrk()
	{
		$this->assertFalse(Hkdf::expand('sha256', str_repeat("\0", 31), '', 16));
	}
}
