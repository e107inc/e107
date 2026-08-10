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

use e107\Security\Jwe\AesCbcHmacSha2;
use e107\Security\Jwe\Compact;
use Exception;

require_once(__DIR__ . '/SealedTokenException.php');
require_once(__DIR__ . '/Random.php');
require_once(__DIR__ . '/Hkdf.php');
require_once(__DIR__ . '/Jwe/Compact.php');

/**
 * Server-side state carried through an untrusted client.
 *
 * {@see SealedToken::seal()} takes an array of claims and returns one opaque
 * string. {@see SealedToken::open()} takes that string back from wherever it
 * has been, a form field, a cookie, a query string, and returns the claims
 * if and only if this site sealed them, for this purpose, and they have not
 * expired. Nothing is written to the database, the session or the filesystem
 * in between. That is the point: the CAPTCHA endpoint is hit by spiders that
 * never submit anything, and a design that stores a row per rendered
 * challenge stores millions of rows nobody ever reads.
 *
 * THIS ENCRYPTS. The claims are confidential, not merely tamper-evident.
 * Anyone holding the token, including the visitor it was handed to, sees an
 * authenticated ciphertext and nothing else. A CAPTCHA answer or a session
 * fingerprint can therefore be sealed and handed out. Its predecessor, the
 * e_jwt handler, carried a docblock that said "encrypting and decrypting"
 * over an HS256 signature, so every CAPTCHA solution e107 ever issued was
 * base64 in the page source; that is the defect this class exists to close
 * and this paragraph exists to stop from recurring. If a future change makes
 * this class sign rather than encrypt, this paragraph is the first thing
 * that has to be deleted.
 *
 * The format is JWE compact serialisation, RFC 7516 section 7.1, with
 * alg="dir" and enc="A256CBC-HS512". alg="dir" means the key is derived, not
 * wrapped, so no key management data travels in the token.
 * enc="A256CBC-HS512" is AES_256_CBC_HMAC_SHA_512 from RFC 7518 section 5.2,
 * encrypt-then-MAC over AES-256-CBC.
 *
 * AES-GCM is deliberately not used. Below PHP 7.1 openssl_encrypt() takes
 * five parameters with no $tag and no $aad, because AEAD arrived in 7.1, yet
 * "aes-256-gcm" is still listed by openssl_get_cipher_methods() there and the
 * call SUCCEEDS while discarding the tag, which yields unauthenticated
 * ciphertext that nothing can ever open again. Probing the cipher name cannot
 * detect this; only PHP_VERSION_ID can. Current e107 requires PHP 8.0, so no
 * supported host is exposed to that, but this code is written to a 5.6 floor
 * for the 2.3 branch and the trap is silent enough that choosing an algorithm
 * which cannot fall into it is worth more than the speed of GCM on tokens a
 * few hundred bytes long. See {@see \e107\Security\Cipher\CipherFactory} and
 * the CipherFactory tests.
 *
 * Key material comes from one core preference, token_secret, 64 CSPRNG
 * octets stored as 128 hexadecimal characters. Each purpose gets its own
 * content encryption key derived from it with HKDF-SHA512 under an info
 * string naming the format version and the purpose, so a token sealed for
 * the CAPTCHA cannot be opened as a login destination even though both are
 * this site's tokens. The secret is provisioned eagerly by the installer and
 * by the upgrade routines; the lazy path here is the safety net for a site
 * that arrived by neither route.
 *
 * Aliased to the v2-style name `e_sealed_token` by
 * e107_handlers/sealed_token_handler.php. Reach an instance through
 * {@see \e107::getSealedToken()} rather than constructing one, so the key
 * derivation is paid for once per purpose per request.
 */
class SealedToken
{
	/**
	 * Core preference holding the master secret.
	 */
	const PREF_SECRET = 'token_secret';

	/**
	 * Length of that preference in hexadecimal characters.
	 */
	const SECRET_LENGTH = 128;

	/**
	 * Extraction and expansion hash for the key derivation.
	 */
	const HKDF_HASH = 'sha512';

	/**
	 * Leading part of the HKDF info string.
	 *
	 * The version is in here rather than in the JWE header because the
	 * header is an allow-list of exactly one value, {@see Compact::HEADER}.
	 * Changing this constant retires every token in flight at once, which is
	 * the wanted behaviour for a format change: nothing sealed by the old
	 * code can be opened by the new code, in either direction.
	 */
	const KEY_INFO = 'e107 sealed token v1 ';

	/**
	 * Purpose used when a caller names none.
	 */
	const DEFAULT_PURPOSE = 'general';

	/**
	 * Claims this class writes itself, which a caller may not supply.
	 *
	 * @var array
	 */
	private static $reserved = array('iss', 'iat', 'nbf', 'exp', 'jti');

	/**
	 * The master secret for this request: a 128 character hexadecimal string,
	 * false once acquisition has been tried and failed, null before it has
	 * been tried at all.
	 *
	 * Static so that a request which cannot provision does not try again for
	 * every purpose that asks. Minting twice would be worse than failing:
	 * the second write wins and the tokens sealed under the first become
	 * unopenable.
	 *
	 * @var string|false|null
	 */
	private static $secret = null;

	/**
	 * @var string
	 */
	protected $purpose;

	/**
	 * Content encryption key for this purpose, derived once.
	 *
	 * @var string|null
	 */
	protected $key = null;

	/**
	 * @param string $purpose names what the token is for, and is mixed into
	 *                the key derivation, so tokens of one purpose are
	 *                unopenable under any other. A code-chosen identifier,
	 *                never anything a visitor supplied.
	 */
	public function __construct($purpose = self::DEFAULT_PURPOSE)
	{
		$purpose = is_string($purpose) ? trim($purpose) : '';
		$this->purpose = $purpose === '' ? self::DEFAULT_PURPOSE : $purpose;
	}

	/**
	 * The purpose this instance seals and opens under.
	 *
	 * @return string
	 */
	public function getPurpose()
	{
		return $this->purpose;
	}

	/**
	 * Seal claims into a token.
	 *
	 * iss, iat, nbf, exp and jti are added inside the encrypted payload, so
	 * neither the expiry nor the token identifier is readable from outside.
	 * A caller may not supply any of those five names; doing so is a
	 * programming error and is refused rather than silently overwritten.
	 *
	 * A negative $ttl is accepted and produces an already expired token,
	 * which is the only way to write a test for the expiry check.
	 *
	 * @param array $claims caller's own claims, JSON-encodable
	 * @param int $ttl seconds the token stays valid
	 * @return string|false the compact token, or false when it could not be
	 *                produced. It never throws: none of the call sites
	 *                catches, so a site whose key cannot be acquired must
	 *                degrade rather than white-page
	 */
	public function seal(array $claims, $ttl = 600)
	{
		try
		{
			$key = $this->key();

			if($key === false)
			{
				return false;
			}

			if(array_intersect(array_keys($claims), self::$reserved))
			{
				return false;
			}

			$now = $this->now();
			$payload = json_encode(array_merge($claims, array(
				'iss' => $this->issuer(),
				'iat' => $now,
				'nbf' => $now,
				'exp' => $now + (int) $ttl,
				'jti' => Random::hex(32),
			)));

			if(!is_string($payload))
			{
				return false;
			}

			$iv = Random::bytes(AesCbcHmacSha2::IV_LENGTH);
			$sealed = AesCbcHmacSha2::encrypt(Compact::ENC, $key, $iv, $payload, Compact::HEADER);

			if($sealed === false)
			{
				return false;
			}

			return Compact::serialise($iv, $sealed['ciphertext'], $sealed['tag']);
		}
		catch(Exception $e)
		{
			return false;
		}
		catch(\Throwable $t)
		{
			return false;
		}
	}

	/**
	 * Open a token sealed by this site for this purpose.
	 *
	 * Every rejection answers false and says nothing about which check
	 * failed. The caller is holding a string an anonymous visitor sent, and
	 * a reason would be an oracle.
	 *
	 * There is no clock skew leeway, where the predecessor allowed 60
	 * seconds either way. Leeway is for a verifier whose clock differs from
	 * the issuer's, and here they are one host reading one clock; a farm
	 * sharing a database already has to keep its clocks together for far
	 * more than this. What the leeway did do was add a minute to the life of
	 * every token in each direction, which is most of the CAPTCHA lifetime
	 * given away, and make nbf grant a minute of validity before the token
	 * was issued. Both are pure loss here, so both are gone.
	 *
	 * @param string $token untrusted input of any shape whatsoever
	 * @return array|false the sealed claims, including iss, iat, nbf, exp
	 *                and jti, or false. It never throws for any input
	 */
	public function open($token)
	{
		try
		{
			$key = $this->key();

			if($key === false)
			{
				return false;
			}

			$parts = Compact::parse($token);

			if($parts === false)
			{
				return false;
			}

			$payload = AesCbcHmacSha2::decrypt(
				Compact::ENC,
				$key,
				$parts['iv'],
				$parts['ciphertext'],
				$parts['tag'],
				$parts['aad']
			);

			if($payload === false)
			{
				return false;
			}

			$claims = json_decode($payload, true);

			if(!is_array($claims) || !$this->claimsHold($claims))
			{
				return false;
			}

			return $claims;
		}
		catch(Exception $e)
		{
			return false;
		}
		catch(\Throwable $t)
		{
			return false;
		}
	}

	/**
	 * Make sure the site has a master secret, and answer it.
	 *
	 * Called by the installer and by the upgrade routines so that the lazy
	 * path in {@see SealedToken::secret()} is a safety net rather than the
	 * normal route. Safe to call repeatedly: an already provisioned secret
	 * is returned untouched.
	 *
	 * @return string 128 hexadecimal characters
	 * @throws SealedTokenException when no secret could be established
	 */
	public static function provision()
	{
		$config = \e107::getConfig();
		$stored = $config->get(self::PREF_SECRET);

		if(self::isSecret($stored))
		{
			return $stored;
		}

		try
		{
			$minted = Random::hex(self::SECRET_LENGTH);
		}
		catch(RandomException $e)
		{
			throw new SealedTokenException(Random::UNAVAILABLE, 0, $e);
		}

		// nologs, because the first request to reach here on a site that
		// arrived by neither the installer nor the upgrade routine is usually
		// an anonymous page view, and e_pref::save() reports a failure through
		// e107::getLog()->addError(), which registers the MySQL error number
		// and text with the message handler for display. A visitor must not be
		// shown that.
		$logs = $config->getParam('nologs');
		$config->setParam('nologs', true);

		// e_pref::save() answers 0 for "nothing to write", true for written
		// and false for both a database error and losing every one of its
		// three compare-and-swap attempts. Only false is a failure, and 0 is
		// as falsy as false is, so the test has to be identity. No $force: a
		// key the site did not have is a change, so the save happens on its
		// own, and forcing would write the whole preference blob on a request
		// that had nothing to save.
		$result = $config->set(self::PREF_SECRET, $minted)->save(false, false, false);

		$config->setParam('nologs', $logs);

		if($result !== false)
		{
			self::logProvisioned();

			return $minted;
		}

		// The write did not happen. Another writer may nonetheless have put a
		// secret there while this one was trying, and sealing under a key that
		// was never stored would produce tokens nothing can open, so take
		// whatever actually persisted.
		$persisted = self::storedSecret();

		if(self::isSecret($persisted))
		{
			return $persisted;
		}

		throw new SealedTokenException('The ' . self::PREF_SECRET . ' core preference could not be written, so no sealed token key is available.');
	}

	/**
	 * Forget the master secret memoised for this request.
	 *
	 * Tests only, in the same spirit as
	 * {@see \e107\Security\Cipher\CipherFactory::reset()}.
	 *
	 * @return void
	 */
	public static function resetKeyRing()
	{
		self::$secret = null;
	}

	/**
	 * The master secret, acquired at most once per request.
	 *
	 * @return string|false 128 hexadecimal characters, or false when the site
	 *                has none and could not be given one
	 */
	protected function secret()
	{
		if(self::$secret === null)
		{
			try
			{
				self::$secret = self::provision();
			}
			catch(SealedTokenException $e)
			{
				self::$secret = false;
			}
		}

		return self::$secret;
	}

	/**
	 * The single clock reading this class makes.
	 *
	 * @return int
	 */
	protected function now()
	{
		return time();
	}

	/**
	 * Who this site claims to be.
	 *
	 * A site that moves to a new address retires its tokens, which is a few
	 * minutes of failed CAPTCHAs on the day of the move and a check worth
	 * having on every other day.
	 *
	 * @return string
	 */
	protected function issuer()
	{
		return defined('SITEURL') ? SITEURL : '';
	}

	/**
	 * The content encryption key for this purpose, derived once per instance.
	 *
	 * @return string|false 64 octets, MAC key first
	 */
	protected function key()
	{
		if($this->key !== null)
		{
			return $this->key;
		}

		$secret = $this->secret();

		if(!self::isSecret($secret))
		{
			return false;
		}

		$key = Hkdf::derive(
			self::HKDF_HASH,
			pack('H*', $secret),
			AesCbcHmacSha2::keyLength(Compact::ENC),
			self::KEY_INFO . $this->purpose
		);

		if(!is_string($key))
		{
			return false;
		}

		$this->key = $key;

		return $this->key;
	}

	/**
	 * Whether the registered claims allow the token to be accepted.
	 *
	 * Authenticity is already settled by the time this runs, so these are
	 * questions about a token this site genuinely sealed: was it sealed by
	 * this site under this address, and is now inside its window.
	 *
	 * @param array $claims
	 * @return bool
	 */
	private function claimsHold(array $claims)
	{
		if(!isset($claims['iss']) || !is_string($claims['iss']) || !hash_equals($this->issuer(), $claims['iss']))
		{
			return false;
		}

		if(!isset($claims['nbf'], $claims['exp']) || !is_int($claims['nbf']) || !is_int($claims['exp']))
		{
			return false;
		}

		$now = $this->now();

		return $now >= $claims['nbf'] && $now < $claims['exp'];
	}

	/**
	 * @param mixed $secret
	 * @return bool
	 */
	private static function isSecret($secret)
	{
		return is_string($secret) && strlen($secret) === self::SECRET_LENGTH && ctype_xdigit($secret);
	}

	/**
	 * Read the master secret straight out of storage.
	 *
	 * The shared configuration object still holds the value whose write just
	 * failed, and reloading it would discard whatever else the request has
	 * set on it and not yet saved, so this goes to the row instead and
	 * touches nothing.
	 *
	 * @return string|false
	 */
	private static function storedSecret()
	{
		$config = \e107::getConfig();
		$row = \e107::getDb()->createQueryBuilder()
			->select('e107_value')
			->from('core')
			->where('e107_name', $config->getConfigId('core'))
			->fetchRow();

		if(empty($row['e107_value']))
		{
			return false;
		}

		$stored = \e107::unserialize($row['e107_value']);

		return isset($stored[self::PREF_SECRET]) ? $stored[self::PREF_SECRET] : false;
	}

	/**
	 * Record that this site has just been given a key, once, at the moment it
	 * happens. An operator who later finds every token failing needs to be
	 * able to see when the key changed.
	 *
	 * @return void
	 */
	private static function logProvisioned()
	{
		if(!defined('E_LOG_INFORMATIVE') || !defined('LOG_TO_ADMIN'))
		{
			return;
		}

		\e107::getLog()->add(
			'Sealed token secret provisioned',
			'A new ' . self::PREF_SECRET . ' core preference was generated. Any token sealed under the previous secret, if there was one, can no longer be opened.',
			E_LOG_INFORMATIVE,
			'SEALEDTOKEN'
		);
	}
}
