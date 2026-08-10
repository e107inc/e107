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
 * Compact serialisation, and the parser's behaviour on hostile input.
 *
 * Everything the parser rejects has to come back as false. This class is
 * reached by anonymous visitors on the CAPTCHA image endpoint, so an
 * exception here is a 500 that anybody can trigger with a query string.
 */
class CompactTest extends \Codeception\Test\Unit
{
	/**
	 * @var array
	 */
	protected $vectors = array();

	protected function _before()
	{
		require_once(e_HANDLER . 'Security/Jwe/Compact.php');

		$this->vectors = include(codecept_data_dir() . 'jose_vectors.php');
	}

	public function testHeaderConstantMatchesItsJson()
	{
		$this->assertSame(Compact::HEADER, Compact::base64UrlEncode(Compact::HEADER_JSON));
		$this->assertSame(
			array('alg' => Compact::ALG, 'enc' => Compact::ENC),
			json_decode(Compact::HEADER_JSON, true)
		);
	}

	public function testBase64UrlRoundTrip()
	{
		foreach(array('', 'a', 'ab', 'abc', 'abcd', "\0\xff\xfe", str_repeat("\x7f", 100)) as $raw)
		{
			$encoded = Compact::base64UrlEncode($raw);

			$this->assertSame(0, preg_match('/[+\/=]/', $encoded), 'alphabet of ' . bin2hex($raw));

			if($raw !== '')
			{
				$this->assertSame($raw, Compact::base64UrlDecode($encoded));
			}
		}
	}

	public function testBase64UrlDecodeIsStrict()
	{
		$this->assertFalse(Compact::base64UrlDecode(''), 'empty');
		$this->assertFalse(Compact::base64UrlDecode('YWJj='), 'padding');
		$this->assertFalse(Compact::base64UrlDecode('YW Jj'), 'embedded space');
		$this->assertFalse(Compact::base64UrlDecode("YWJ\nj"), 'embedded newline');
		$this->assertFalse(Compact::base64UrlDecode('YWJ+'), 'plus');
		$this->assertFalse(Compact::base64UrlDecode('YWJ/'), 'slash');
		$this->assertFalse(Compact::base64UrlDecode('Y'), 'impossible remainder');
		$this->assertFalse(Compact::base64UrlDecode('YWJja'), 'impossible remainder, longer');
		$this->assertFalse(Compact::base64UrlDecode(null), 'null');
		$this->assertFalse(Compact::base64UrlDecode(array()), 'array');

		// 'QQ' and 'QR' both decode to "A"; only the canonical spelling passes.
		$this->assertSame('A', Compact::base64UrlDecode('QQ'));
		$this->assertFalse(Compact::base64UrlDecode('QR'), 'non-canonical trailing bits');
	}

	public function testSerialiseShape()
	{
		$token = Compact::serialise(str_repeat("\x01", 16), str_repeat("\x02", 32), str_repeat("\x03", 32));

		$this->assertTrue(is_string($token));

		$parts = explode('.', $token);

		$this->assertCount(5, $parts);
		$this->assertSame(Compact::HEADER, $parts[0]);
		$this->assertSame('', $parts[1], 'alg=dir leaves the encrypted key empty');
		$this->assertTrue(strpos($token, '..') !== false, 'a valid token contains a literal ..');
	}

	public function testRoundTrip()
	{
		$iv = str_repeat("\x0a", 16);
		$ciphertext = str_repeat("\x0b", 48);
		$tag = str_repeat("\x0c", 32);

		$token = Compact::serialise($iv, $ciphertext, $tag);
		$parsed = Compact::parse($token);

		$this->assertSame($iv, $parsed['iv']);
		$this->assertSame($ciphertext, $parsed['ciphertext']);
		$this->assertSame($tag, $parsed['tag']);
		$this->assertSame(substr($token, 0, strpos($token, '.')), $parsed['aad'], 'the aad is the header part');
	}

	/**
	 * The aad handed back has to be the header as it arrived, not the
	 * constant it was just compared against.
	 *
	 * While the allow-list holds exactly one header the two are equal by the
	 * time parse() returns, so no assertion about the value can tell them
	 * apart, and a mutation that returned the constant left the suite green.
	 * The property still has to be defended, because the day a second enc is
	 * accepted an implementation that authenticates its own idea of the
	 * header rather than the received one is an algorithm-confusion hole, and
	 * that change gets made in the allow-list by somebody who never opens
	 * this method.
	 */
	public function testParseAuthenticatesTheReceivedHeaderRatherThanTheConstant()
	{
		require_once(__DIR__ . '/../source_contract.php');

		$source = \e107\Security\SourceContract::methodBody('e107\\Security\\Jwe\\Compact', 'parse');

		$this->assertSame(
			1,
			preg_match('~\'aad\'\s*=>\s*\$parts\[0\]~', $source),
			'parse() no longer returns the received header part as the aad'
		);
		$this->assertSame(
			0,
			preg_match('~\'aad\'\s*=>\s*self::~', $source),
			'parse() returns a constant of its own as the aad'
		);
	}

	/**
	 * The header comparison is public data and leaks nothing, so this pins a
	 * discipline rather than closes a hole: an operator comparison is where a
	 * refactor of the tag comparison next door starts. A mutation replacing
	 * the hash_equals() here with != left the suite green.
	 */
	public function testTheHeaderIsComparedInConstantTime()
	{
		require_once(__DIR__ . '/../source_contract.php');

		$source = \e107\Security\SourceContract::methodBody('e107\\Security\\Jwe\\Compact', 'parse');

		$this->assertTrue(strpos($source, 'hash_equals(') !== false, 'parse() no longer calls hash_equals()');
		$this->assertSame(0, preg_match('~self::HEADER\s*(===|!==|==|!=)~', $source), 'compared with an operator');
		$this->assertSame(0, preg_match('~(===|!==|==|!=)\s*self::HEADER~', $source), 'compared with an operator');
	}

	/**
	 * preg_match() answers false rather than 0 when the PCRE engine gives up,
	 * and a caller can provoke that by exhausting the backtrack limit. Read
	 * for truthiness that means "no character outside the alphabet was
	 * found", and the gate is skipped.
	 *
	 * There is no behavioural test. Lowering pcre.backtrack_limit from inside
	 * the suite does nothing, because PCRE caches the compiled pattern from
	 * the first call in the process and the alphabet pattern has been used
	 * hundreds of times by the time any test could try; measured here, the
	 * same fixture that fails in a fresh process returns a clean 0 once the
	 * pattern is warm. The canonicality re-encode below the gate also refuses
	 * the same input independently, which is why this was never exploitable
	 * and why an end-to-end assertion would pass with or without the fix.
	 * Depth is only depth while both layers hold.
	 */
	public function testAPcreEngineFailureIsTreatedAsARejection()
	{
		require_once(__DIR__ . '/../source_contract.php');

		$source = \e107\Security\SourceContract::methodBody('e107\\Security\\Jwe\\Compact', 'base64UrlDecode');

		$this->assertSame(
			1,
			preg_match('~preg_match\([^)]*\)\s*!==\s*0~', $source),
			'a PCRE engine failure would be read as a clean alphabet'
		);
	}

	/**
	 * The whole point of the exercise: a sealed payload survives the wire.
	 */
	public function testRoundTripThroughContentEncryption()
	{
		require_once(e_HANDLER . 'Security/Jwe/AesCbcHmacSha2.php');

		$key = str_repeat("\x5a", AesCbcHmacSha2::keyLength(Compact::ENC));
		$iv = str_repeat("\x5b", AesCbcHmacSha2::IV_LENGTH);
		$plaintext = '{"solution":"AB12C","ip":"192.0.2.1"}';

		$sealed = AesCbcHmacSha2::encrypt(Compact::ENC, $key, $iv, $plaintext, Compact::HEADER);
		$token = Compact::serialise($iv, $sealed['ciphertext'], $sealed['tag']);

		$this->assertTrue(strpos($token, 'solution') === false, 'the payload is not readable in the token');

		$parsed = Compact::parse($token);

		$this->assertSame($plaintext, AesCbcHmacSha2::decrypt(
			Compact::ENC, $key, $parsed['iv'], $parsed['ciphertext'], $parsed['tag'], $parsed['aad']
		));
	}

	/**
	 * One bit flipped in each of the five parts in turn. Parts 0 and 1 are
	 * refused by the parser, parts 2 to 4 by the authentication tag, and
	 * neither route may throw.
	 */
	public function testOneBitFlippedInEveryPart()
	{
		require_once(e_HANDLER . 'Security/Jwe/AesCbcHmacSha2.php');

		$key = str_repeat("\x6a", AesCbcHmacSha2::keyLength(Compact::ENC));
		$iv = str_repeat("\x6b", AesCbcHmacSha2::IV_LENGTH);
		$sealed = AesCbcHmacSha2::encrypt(Compact::ENC, $key, $iv, 'round trip me', Compact::HEADER);
		$token = Compact::serialise($iv, $sealed['ciphertext'], $sealed['tag']);
		$parts = explode('.', $token);

		for($index = 0; $index < 5; $index++)
		{
			$mutated = $parts;
			$mutated[$index] = self::mutate($mutated[$index]);
			$candidate = implode('.', $mutated);

			$this->assertNotSame($token, $candidate, 'part ' . $index . ' actually changed');

			$parsed = Compact::parse($candidate);

			if($parsed === false)
			{
				continue;
			}

			$this->assertFalse(AesCbcHmacSha2::decrypt(
				Compact::ENC, $key, $parsed['iv'], $parsed['ciphertext'], $parsed['tag'], $parsed['aad']
			), 'part ' . $index . ' passed authentication after being altered');
		}
	}

	public function testStructuralRejections()
	{
		$iv = str_repeat("\x01", 16);
		$ciphertext = str_repeat("\x02", 32);
		$tag = str_repeat("\x03", 32);
		$token = Compact::serialise($iv, $ciphertext, $tag);
		$parts = explode('.', $token);

		$this->assertFalse(Compact::parse(implode('.', array_slice($parts, 0, 4))), 'four parts');
		$this->assertFalse(Compact::parse($token . '.' . Compact::base64UrlEncode('extra')), 'six parts');
		$this->assertFalse(Compact::parse($token . '.'), 'trailing separator');
		$this->assertFalse(Compact::parse('.' . $token), 'leading separator');

		$withKey = $parts;
		$withKey[1] = Compact::base64UrlEncode(str_repeat("\x04", 32));
		$this->assertFalse(Compact::parse(implode('.', $withKey)), 'non-empty encrypted key');

		$rewritten = $parts;
		$rewritten[0] = Compact::base64UrlEncode('{"alg":"dir","enc":"A128CBC-HS256"}');
		$this->assertFalse(Compact::parse(implode('.', $rewritten)), 'a different enc');

		$rewritten[0] = Compact::base64UrlEncode('{"alg":"none","enc":"A256CBC-HS512"}');
		$this->assertFalse(Compact::parse(implode('.', $rewritten)), 'alg none');

		$rewritten[0] = Compact::base64UrlEncode('{"enc":"A256CBC-HS512","alg":"dir"}');
		$this->assertFalse(Compact::parse(implode('.', $rewritten)), 'the same header, reordered');

		$rewritten[0] = Compact::base64UrlEncode('{"alg":"dir","enc":"A256CBC-HS512","crit":["exp"]}');
		$this->assertFalse(Compact::parse(implode('.', $rewritten)), 'an extra header parameter');

		$rewritten[0] = '';
		$this->assertFalse(Compact::parse(implode('.', $rewritten)), 'empty header');
	}

	public function testLengthRejections()
	{
		$iv = str_repeat("\x01", 16);
		$ciphertext = str_repeat("\x02", 32);
		$tag = str_repeat("\x03", 32);

		$this->assertFalse(Compact::parse(self::assemble(str_repeat("\x01", 15), $ciphertext, $tag)), '15-octet iv');
		$this->assertFalse(Compact::parse(self::assemble(str_repeat("\x01", 17), $ciphertext, $tag)), '17-octet iv');
		$this->assertFalse(Compact::parse(self::assemble($iv, str_repeat("\x02", 33), $tag)), 'ragged ciphertext');
		$this->assertFalse(Compact::parse(self::assemble($iv, '', $tag)), 'empty ciphertext');
		$this->assertFalse(Compact::parse(self::assemble($iv, $ciphertext, str_repeat("\x03", 31))), 'short tag');
		$this->assertFalse(Compact::parse(self::assemble($iv, $ciphertext, str_repeat("\x03", 33))), 'long tag');

		$this->assertFalse(Compact::serialise(str_repeat("\x01", 15), $ciphertext, $tag), 'serialise rejects a short iv');
		$this->assertFalse(Compact::serialise($iv, '', $tag), 'serialise rejects an empty ciphertext');
		$this->assertFalse(Compact::serialise($iv, $ciphertext, ''), 'serialise rejects an empty tag');
	}

	public function testParseRejectsNonStringsAndOverlongInput()
	{
		$this->assertFalse(Compact::parse(null));
		$this->assertFalse(Compact::parse(array()));
		$this->assertFalse(Compact::parse(12345));
		$this->assertFalse(Compact::parse(true));
		$this->assertFalse(Compact::parse(''));
		$this->assertFalse(Compact::parse(str_repeat('a', Compact::MAX_LENGTH + 1)));

		$iv = str_repeat("\x01", 16);
		$tag = str_repeat("\x03", 32);
		$long = Compact::serialise($iv, str_repeat("\x02", 16 * 1024), $tag);

		$this->assertTrue(strlen($long) > Compact::MAX_LENGTH, 'the fixture is genuinely over the cap');
		$this->assertFalse(Compact::parse($long));
	}

	/**
	 * A valid token contains '..', so a parser written with array_filter()
	 * would see four parts and refuse everything. Pin the behaviour.
	 */
	public function testEmptyEncryptedKeyPartIsNotDiscarded()
	{
		$token = Compact::serialise(str_repeat("\x01", 16), str_repeat("\x02", 32), str_repeat("\x03", 32));

		$this->assertCount(4, array_filter(explode('.', $token)), 'array_filter drops the empty part');
		$this->assertCount(5, explode('.', $token));
		$this->assertTrue(is_array(Compact::parse($token)));
	}

	/**
	 * @param string $iv
	 * @param string $ciphertext
	 * @param string $tag
	 * @return string a token built without serialise()'s validation
	 */
	private static function assemble($iv, $ciphertext, $tag)
	{
		return Compact::HEADER . '..' . Compact::base64UrlEncode($iv)
			. '.' . Compact::base64UrlEncode($ciphertext)
			. '.' . Compact::base64UrlEncode($tag);
	}

	/**
	 * Flip one bit of one octet inside a base64url part, without changing
	 * its length. The empty encrypted key part has no octet to flip, so it
	 * gains one instead, which is exactly the tampering the parser exists
	 * to catch.
	 *
	 * @param string $part
	 * @return string
	 */
	private static function mutate($part)
	{
		if($part === '')
		{
			return 'AA';
		}

		$raw = Compact::base64UrlDecode($part);

		if($raw === false)
		{
			return $part . 'A';
		}

		$raw[0] = chr(ord($raw[0]) ^ 0x01);

		return Compact::base64UrlEncode($raw);
	}
}
