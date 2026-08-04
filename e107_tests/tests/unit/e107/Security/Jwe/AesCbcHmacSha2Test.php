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

/**
 * RFC 7518 appendix B and RFC 7516 appendix A.3 vectors, byte for byte.
 *
 * Beware A.3.8's prose, which says the example uses AES GCM for content
 * encryption. It does not; that sentence is a copy-paste slip from A.1 and
 * A.3 is A128CBC-HS256 throughout, as its own title, section A.3.6 and the
 * arithmetic here all agree.
 */
class AesCbcHmacSha2Test extends \Codeception\Test\Unit
{
	/**
	 * @var array
	 */
	protected $vectors = array();

	protected function _before()
	{
		require_once(e_HANDLER . 'Security/Jwe/AesCbcHmacSha2.php');

		$this->vectors = include(codecept_data_dir() . 'jose_vectors.php');
	}

	public function testEncryptMatchesRfc7518()
	{
		foreach($this->vectors['aes_cbc_hmac_sha2'] as $enc => $vector)
		{
			$result = AesCbcHmacSha2::encrypt(
				$enc,
				hex2bin($vector['K']),
				hex2bin($vector['IV']),
				hex2bin($vector['P']),
				hex2bin($vector['A'])
			);

			$this->assertInternalTypeIsArray($result, $enc);
			$this->assertSame($vector['E'], bin2hex($result['ciphertext']), $enc . ' ciphertext');
			$this->assertSame($vector['T'], bin2hex($result['tag']), $enc . ' tag');
		}
	}

	public function testDecryptMatchesRfc7518()
	{
		foreach($this->vectors['aes_cbc_hmac_sha2'] as $enc => $vector)
		{
			$plaintext = AesCbcHmacSha2::decrypt(
				$enc,
				hex2bin($vector['K']),
				hex2bin($vector['IV']),
				hex2bin($vector['E']),
				hex2bin($vector['T']),
				hex2bin($vector['A'])
			);

			$this->assertSame($vector['P'], bin2hex($plaintext), $enc . ' plaintext');
		}
	}

	/**
	 * PKCS#7 padding is unconditional. The RFC's own P is 128 octets, a
	 * whole number of blocks, and its E is 144: one full block of padding.
	 */
	public function testPaddingIsAppliedToBlockAlignedPlaintext()
	{
		foreach($this->vectors['aes_cbc_hmac_sha2'] as $enc => $vector)
		{
			$this->assertSame(0, strlen(hex2bin($vector['P'])) % 16, $enc . ' vector is block aligned');
			$this->assertSame(
				strlen(hex2bin($vector['P'])) + 16,
				strlen(hex2bin($vector['E'])),
				$enc . ' gains a whole padding block'
			);
		}
	}

	public function testTagLengthIsHalfTheHashOutput()
	{
		$this->assertSame(16, AesCbcHmacSha2::tagLength('A128CBC-HS256'));
		$this->assertSame(24, AesCbcHmacSha2::tagLength('A192CBC-HS384'));
		$this->assertSame(32, AesCbcHmacSha2::tagLength('A256CBC-HS512'));

		foreach($this->vectors['aes_cbc_hmac_sha2'] as $enc => $vector)
		{
			$this->assertSame(
				AesCbcHmacSha2::tagLength($enc),
				strlen(hex2bin($vector['T'])),
				$enc . ' vector tag length'
			);
			$this->assertSame(
				substr($vector['M'], 0, AesCbcHmacSha2::tagLength($enc) * 2),
				$vector['T'],
				$enc . ' tag is a leading truncation of the MAC'
			);
		}
	}

	/**
	 * MAC key first, encryption key second. Getting this the wrong way round
	 * still round-trips, so only a vector catches it.
	 */
	public function testKeyHalvesAreMacThenEncryption()
	{
		foreach($this->vectors['aes_cbc_hmac_sha2'] as $enc => $vector)
		{
			$half = AesCbcHmacSha2::keyLength($enc) / 2;

			$this->assertSame(substr($vector['K'], 0, $half * 2), $vector['MAC_KEY'], $enc);
			$this->assertSame(substr($vector['K'], $half * 2), $vector['ENC_KEY'], $enc);
		}
	}

	/**
	 * RFC 7516 A.3 uses A128KW for key management, so its encrypted key
	 * cannot be reproduced without AES Key Wrap. The content encryption path
	 * is shared with dir, and that is what is checked: take the CEK as the
	 * combined key and everything else follows.
	 */
	public function testRfc7516AppendixA3ContentEncryption()
	{
		$vector = $this->vectors['jwe_compact']['rfc7516_a3'];

		$result = AesCbcHmacSha2::encrypt(
			'A128CBC-HS256',
			hex2bin($vector['cek']),
			hex2bin($vector['iv']),
			hex2bin($vector['plaintext']),
			hex2bin($vector['aad'])
		);

		$this->assertSame($vector['ciphertext'], bin2hex($result['ciphertext']));
		$this->assertSame($vector['tag'], bin2hex($result['tag']));

		$this->assertSame(
			'Live long and prosper.',
			AesCbcHmacSha2::decrypt(
				'A128CBC-HS256',
				hex2bin($vector['cek']),
				hex2bin($vector['iv']),
				hex2bin($vector['ciphertext']),
				hex2bin($vector['tag']),
				hex2bin($vector['aad'])
			)
		);
	}

	/**
	 * The AAD is the ASCII of the base64url protected header, not the header
	 * JSON. Confirmed against the fixture rather than assumed.
	 */
	public function testRfc7516AppendixA3AadIsTheEncodedHeader()
	{
		$vector = $this->vectors['jwe_compact']['rfc7516_a3'];

		$this->assertSame($vector['protected_b64'], hex2bin($vector['aad']));
	}

	public function testAlIsTheBitLengthOfTheAad()
	{
		foreach($this->vectors['aes_cbc_hmac_sha2'] as $enc => $vector)
		{
			$this->assertSame(
				bin2hex(pack('N2', 0, strlen(hex2bin($vector['A'])) * 8)),
				$vector['AL'],
				$enc . ' AL'
			);
		}
	}

	public function testAadIsAuthenticated()
	{
		$key = str_repeat("\x11", 64);
		$iv = str_repeat("\x22", 16);

		$result = AesCbcHmacSha2::encrypt('A256CBC-HS512', $key, $iv, 'secret', 'header-a');

		$this->assertFalse(AesCbcHmacSha2::decrypt(
			'A256CBC-HS512', $key, $iv, $result['ciphertext'], $result['tag'], 'header-b'
		));
		$this->assertSame('secret', AesCbcHmacSha2::decrypt(
			'A256CBC-HS512', $key, $iv, $result['ciphertext'], $result['tag'], 'header-a'
		));
	}

	/**
	 * Every bit of the ciphertext and of the tag has to be covered, and a
	 * failure has to be false rather than an exception.
	 */
	public function testTamperingIsRejected()
	{
		$key = str_repeat("\x33", 64);
		$iv = str_repeat("\x44", 16);
		$result = AesCbcHmacSha2::encrypt('A256CBC-HS512', $key, $iv, 'the quick brown fox', 'aad');

		foreach(array('ciphertext', 'tag') as $field)
		{
			$parts = $result;
			$parts[$field] = self::flip($parts[$field]);

			$this->assertFalse(AesCbcHmacSha2::decrypt(
				'A256CBC-HS512', $key, $iv, $parts['ciphertext'], $parts['tag'], 'aad'
			), 'flipped ' . $field);
		}

		$this->assertFalse(AesCbcHmacSha2::decrypt(
			'A256CBC-HS512', $key, self::flip($iv), $result['ciphertext'], $result['tag'], 'aad'
		), 'flipped iv');

		$this->assertFalse(AesCbcHmacSha2::decrypt(
			'A256CBC-HS512', self::flip($key), $iv, $result['ciphertext'], $result['tag'], 'aad'
		), 'flipped key');
	}

	public function testMalformedInputReturnsFalse()
	{
		$key = str_repeat("\x55", 64);
		$iv = str_repeat("\x66", 16);
		$result = AesCbcHmacSha2::encrypt('A256CBC-HS512', $key, $iv, 'payload', '');

		$this->assertFalse(AesCbcHmacSha2::encrypt('A256CBC-HS512', substr($key, 1), $iv, 'x', ''), 'short key');
		$this->assertFalse(AesCbcHmacSha2::encrypt('A256CBC-HS512', $key, substr($iv, 1), 'x', ''), 'short iv');
		$this->assertFalse(AesCbcHmacSha2::encrypt('A256GCM', $key, $iv, 'x', ''), 'unsupported enc');
		$this->assertFalse(AesCbcHmacSha2::encrypt('', $key, $iv, 'x', ''), 'empty enc');
		$this->assertFalse(AesCbcHmacSha2::encrypt(null, $key, $iv, 'x', ''), 'null enc');

		$this->assertFalse(AesCbcHmacSha2::decrypt('A256CBC-HS512', $key, $iv, '', $result['tag'], ''), 'empty ciphertext');
		$this->assertFalse(AesCbcHmacSha2::decrypt('A256CBC-HS512', $key, $iv, substr($result['ciphertext'], 1), $result['tag'], ''), 'ragged ciphertext');
		$this->assertFalse(AesCbcHmacSha2::decrypt('A256CBC-HS512', $key, $iv, $result['ciphertext'], substr($result['tag'], 1), ''), 'short tag');
		$this->assertFalse(AesCbcHmacSha2::decrypt('A256CBC-HS512', $key, $iv, $result['ciphertext'], $result['tag'] . "\0", ''), 'long tag');
	}

	/**
	 * Encrypt-then-MAC is only encrypt-then-MAC if the MAC is checked first.
	 *
	 * Swapping the two blocks of decrypt() so the tag is compared after the
	 * plaintext comes back leaves every return value, every warning and every
	 * timing unchanged, and turns the CAPTCHA endpoint into a CBC padding
	 * oracle for anonymous visitors. A mutation that made exactly that edit
	 * left this suite green, so what is watched here is not the answer but
	 * whether the block cipher was asked at all.
	 */
	public function testTheCipherIsNeverAskedToDecryptWhenAuthenticationFails()
	{
		require_once(e_HANDLER . 'Security/Cipher/CipherFactory.php');
		require_once(__DIR__ . '/../Cipher/spy_cipher.php');

		$key = str_repeat("\x91", 64);
		$iv = str_repeat("\x92", 16);
		$sealed = AesCbcHmacSha2::encrypt('A256CBC-HS512', $key, $iv, 'the answer is 4T9K', 'aad');

		$forgeries = array(
			'an all-zero tag'    => array($sealed['ciphertext'], str_repeat("\x00", 32), 'aad'),
			'a one-bit tag flip' => array($sealed['ciphertext'], self::flip($sealed['tag']), 'aad'),
			'a tampered block'   => array(self::flip($sealed['ciphertext']), $sealed['tag'], 'aad'),
			'a substituted aad'  => array($sealed['ciphertext'], $sealed['tag'], 'other'),
		);

		$spy = \e107\Security\Cipher\SpyCipher::install();

		try
		{
			foreach($forgeries as $label => $forgery)
			{
				$before = $spy->decryptCalls;

				$this->assertFalse(AesCbcHmacSha2::decrypt(
					'A256CBC-HS512', $key, $iv, $forgery[0], $forgery[1], $forgery[2]
				), $label . ' was accepted');

				$this->assertSame($before, $spy->decryptCalls, $label . ' reached the block cipher');
			}

			$before = $spy->decryptCalls;

			$this->assertSame('the answer is 4T9K', AesCbcHmacSha2::decrypt(
				'A256CBC-HS512', $key, $iv, $sealed['ciphertext'], $sealed['tag'], 'aad'
			));
			$this->assertSame($before + 1, $spy->decryptCalls, 'the spy is not wired in at all');
		}
		finally
		{
			\e107\Security\Cipher\CipherFactory::reset();
		}
	}

	/**
	 * The tag has to be compared with hash_equals().
	 *
	 * A comparison that stops at the first differing octet leaks how far a
	 * forgery matched, and 2^256 guesses become 32 rounds of 256. There is no
	 * behavioural test for this: for a loose == to accept a wrong tag the
	 * genuine tag would have to be a numeric string, and it is HMAC output,
	 * while the timing difference the real attack needs is far below what a
	 * unit test can measure. A mutation replacing hash_equals() with != left
	 * the suite green, so the call itself is what is pinned.
	 */
	public function testTheTagIsComparedInConstantTime()
	{
		require_once(__DIR__ . '/../source_contract.php');

		$source = \e107\Security\SourceContract::methodBody('e107\\Security\\Jwe\\AesCbcHmacSha2', 'decrypt');

		$this->assertTrue(strpos($source, 'hash_equals(') !== false, 'decrypt() no longer calls hash_equals()');
		$this->assertSame(0, preg_match('~\$tag\s*(===|!==|==|!=)~', $source), 'the tag is compared with an operator');
		$this->assertSame(0, preg_match('~(===|!==|==|!=)\s*\$tag~', $source), 'the tag is compared with an operator');
	}

	/**
	 * decrypt() is public, so its own bound cannot be left to its callers.
	 */
	public function testDecryptCapsTheCiphertextItWillAuthenticate()
	{
		require_once(e_HANDLER . 'Security/Jwe/Compact.php');

		$key = str_repeat("\xa1", 64);
		$iv = str_repeat("\xa2", 16);
		$atCap = AesCbcHmacSha2::encrypt(
			'A256CBC-HS512', $key, $iv, str_repeat('x', AesCbcHmacSha2::MAX_CIPHERTEXT - 16), ''
		);
		$overCap = AesCbcHmacSha2::encrypt(
			'A256CBC-HS512', $key, $iv, str_repeat('x', AesCbcHmacSha2::MAX_CIPHERTEXT), ''
		);

		$this->assertSame(AesCbcHmacSha2::MAX_CIPHERTEXT, strlen($atCap['ciphertext']), 'the fixture sits on the cap');
		$this->assertSame(AesCbcHmacSha2::MAX_CIPHERTEXT + 16, strlen($overCap['ciphertext']), 'and one block past it');

		$this->assertTrue(is_string(AesCbcHmacSha2::decrypt(
			'A256CBC-HS512', $key, $iv, $atCap['ciphertext'], $atCap['tag'], ''
		)), 'a ciphertext exactly on the cap was refused');

		$this->assertFalse(AesCbcHmacSha2::decrypt(
			'A256CBC-HS512', $key, $iv, $overCap['ciphertext'], $overCap['tag'], ''
		), 'a ciphertext one block over the cap was authenticated');

		$this->assertTrue(
			(int) (Compact::MAX_LENGTH * 3 / 4) < AesCbcHmacSha2::MAX_CIPHERTEXT,
			'no token the parser accepts may carry a ciphertext this class refuses'
		);
	}

	public function testEmptyPlaintextRoundTrips()
	{
		$key = str_repeat("\x77", 64);
		$iv = str_repeat("\x88", 16);
		$result = AesCbcHmacSha2::encrypt('A256CBC-HS512', $key, $iv, '', '');

		$this->assertSame(16, strlen($result['ciphertext']));
		$this->assertSame('', AesCbcHmacSha2::decrypt('A256CBC-HS512', $key, $iv, $result['ciphertext'], $result['tag'], ''));
	}

	public function testSupportedAlgorithms()
	{
		$this->assertTrue(AesCbcHmacSha2::isSupported('A128CBC-HS256'));
		$this->assertTrue(AesCbcHmacSha2::isSupported('A256CBC-HS512'));
		$this->assertFalse(AesCbcHmacSha2::isSupported('A256GCM'));
		$this->assertFalse(AesCbcHmacSha2::isSupported('none'));
	}

	/**
	 * @param string $raw
	 * @return string the same octets with one bit of the first flipped
	 */
	private static function flip($raw)
	{
		$raw[0] = chr(ord($raw[0]) ^ 0x01);

		return $raw;
	}

	/**
	 * assertInternalType() was removed in PHPUnit 9 and assertIsArray() does
	 * not exist before PHPUnit 7.5, and this suite runs across both.
	 *
	 * @param mixed $value
	 * @param string $message
	 * @return void
	 */
	private function assertInternalTypeIsArray($value, $message = '')
	{
		$this->assertTrue(is_array($value), $message);
	}
}
