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

require_once(__DIR__ . '/AesCbcHmacSha2.php');

/**
 * JWE compact serialisation, RFC 7516 sections 7.1 and 5.2.
 *
 * Exactly one header is ever produced and exactly one is ever accepted:
 *
 *     {"alg":"dir","enc":"A256CBC-HS512"}
 *
 * alg=dir means there is no key wrapping, so the encrypted key is the empty
 * string and a valid token contains the literal sequence '..' between the
 * header and the initialisation vector. That is correct and required.
 * DO NOT "repair" the parser with array_filter() or array_diff(), and do not
 * trim the token: dropping the empty element leaves four parts and the
 * length check below then rejects every legitimate token in circulation.
 *
 * {@see Compact::parse()} is reached by anonymous visitors, on the CAPTCHA
 * image endpoint among others. It therefore validates the whole structure
 * itself and returns false for every kind of malformed input rather than
 * throwing, and it has exactly one rejection value so that a caller cannot
 * accidentally build an oracle by reporting why a token was refused. A
 * failed authentication tag and a token with fifteen octets of IV are
 * indistinguishable from the outside.
 */
class Compact
{
	/**
	 * The single accepted key management algorithm.
	 */
	const ALG = 'dir';

	/**
	 * The single accepted content encryption algorithm.
	 */
	const ENC = 'A256CBC-HS512';

	/**
	 * The single accepted protected header, an allow-list of one.
	 */
	const HEADER_JSON = '{"alg":"dir","enc":"A256CBC-HS512"}';

	/**
	 * Base64url of {@see Compact::HEADER_JSON}, precomputed because it is
	 * compared on every request.
	 */
	const HEADER = 'eyJhbGciOiJkaXIiLCJlbmMiOiJBMjU2Q0JDLUhTNTEyIn0';

	/**
	 * Longest token the parser will look at, in characters.
	 *
	 * A sealed CSRF or CAPTCHA token runs to a few hundred characters. The
	 * cap is generous enough that no legitimate payload approaches it and
	 * small enough that an anonymous caller cannot make the parser work.
	 */
	const MAX_LENGTH = 8192;

	/**
	 * RFC 7516 section 7.1 serialisation.
	 *
	 * @param string $iv raw octets, exactly 16
	 * @param string $ciphertext raw octets, a non-zero multiple of 16
	 * @param string $tag raw octets, exactly the tag length of the enc
	 * @return string|false the compact token
	 */
	public static function serialise($iv, $ciphertext, $tag)
	{
		if(!self::lengthsOk($iv, $ciphertext, $tag))
		{
			return false;
		}

		return self::HEADER . '..' . self::base64UrlEncode($iv)
			. '.' . self::base64UrlEncode($ciphertext)
			. '.' . self::base64UrlEncode($tag);
	}

	/**
	 * RFC 7516 section 5.2 steps 1 to 4, as far as this profile goes.
	 *
	 * The returned aad is the header part as it arrived on the wire, not
	 * {@see Compact::HEADER}, even though the two have just been proved
	 * equal. The moment a second enc value is ever accepted, an
	 * implementation that authenticates its own idea of the header instead
	 * of the received one becomes vulnerable to algorithm confusion, and
	 * that change would be made in the allow-list above by somebody who
	 * never reads this method.
	 *
	 * @param string $token untrusted input
	 * @return array|false array('aad' => string, 'iv' => string,
	 *                    'ciphertext' => string, 'tag' => string)
	 */
	public static function parse($token)
	{
		if(!is_string($token) || $token === '' || strlen($token) > self::MAX_LENGTH)
		{
			return false;
		}

		$parts = explode('.', $token);

		if(count($parts) !== 5)
		{
			return false;
		}

		if(!hash_equals(self::HEADER, $parts[0]))
		{
			return false;
		}

		if($parts[1] !== '')
		{
			return false;
		}

		$iv = self::base64UrlDecode($parts[2]);
		$ciphertext = self::base64UrlDecode($parts[3]);
		$tag = self::base64UrlDecode($parts[4]);

		if($iv === false || $ciphertext === false || $tag === false)
		{
			return false;
		}

		if(!self::lengthsOk($iv, $ciphertext, $tag))
		{
			return false;
		}

		return array(
			'aad'        => $parts[0],
			'iv'         => $iv,
			'ciphertext' => $ciphertext,
			'tag'        => $tag,
		);
	}

	/**
	 * Base64url without padding, RFC 7515 appendix C.
	 *
	 * @param string $raw
	 * @return string
	 */
	public static function base64UrlEncode($raw)
	{
		return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
	}

	/**
	 * Strict base64url decode.
	 *
	 * PHP's own strict mode still skips whitespace, so the alphabet is
	 * checked here first. Padding characters are rejected outright because
	 * this encoding has none, and the result is re-encoded and compared so
	 * that a non-canonical final character, one carrying bits that decode to
	 * nothing, cannot give two spellings of the same token.
	 *
	 * preg_match() answers int for a decision and false for a PCRE engine
	 * failure, which a bare truthiness test reads as "no character outside
	 * the alphabet was found". A caller can provoke that failure, so the two
	 * are told apart and only a hard zero counts as clean.
	 *
	 * @param string $text
	 * @return string|false
	 */
	public static function base64UrlDecode($text)
	{
		if(!is_string($text) || $text === '')
		{
			return false;
		}

		if(preg_match('/[^A-Za-z0-9\-_]/', $text) !== 0)
		{
			return false;
		}

		$remainder = strlen($text) % 4;

		if($remainder === 1)
		{
			return false;
		}

		$padded = $remainder === 0 ? $text : $text . str_repeat('=', 4 - $remainder);
		$raw = base64_decode(strtr($padded, '-_', '+/'), true);

		if($raw === false || self::base64UrlEncode($raw) !== $text)
		{
			return false;
		}

		return $raw;
	}

	/**
	 * @param string $iv
	 * @param string $ciphertext
	 * @param string $tag
	 * @return bool
	 */
	private static function lengthsOk($iv, $ciphertext, $tag)
	{
		if(!is_string($iv) || !is_string($ciphertext) || !is_string($tag))
		{
			return false;
		}

		if(strlen($iv) !== AesCbcHmacSha2::IV_LENGTH)
		{
			return false;
		}

		if($ciphertext === '' || strlen($ciphertext) % 16 !== 0)
		{
			return false;
		}

		return strlen($tag) === AesCbcHmacSha2::tagLength(self::ENC);
	}
}
