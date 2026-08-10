<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2025 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * CSRF Token Handler - Provides CSRF protection strategies
 */

if (!defined('e107_INIT'))
{
	exit;
}

/**
 * Abstract base class for CSRF token handlers
 */
abstract class CSRFTokenHandler
{
	/**
	 * Get the CSRF token
	 * @param bool $in_form If true, return form-ready value; if false, return raw value
	 * @return string
	 */
	abstract public function getToken($in_form = true);

	/**
	 * Validate a submitted token
	 * @param string $token The token to validate
	 * @return bool
	 */
	abstract public function validate($token);

	/**
	 * Regenerate the token
	 * @return void
	 */
	abstract public function regenerate();

	/**
	 * Clean up any stored data (on logout, etc)
	 * @return void
	 */
	abstract public function cleanup();
}

/**
 * Session-based CSRF token handler for authenticated users
 * Maintains backward compatibility with existing session-based tokens
 */
class CSRFSessionHandler extends CSRFTokenHandler
{
	/** @var e_session */
	protected $session;

	/**
	 * @param e_session $session
	 */
	public function __construct($session)
	{
		$this->session = $session;
	}

	/**
	 * Get the CSRF token from session
	 * @param bool $in_form If true, return MD5 hash; if false, return raw value
	 * @return string
	 */
	public function getToken($in_form = true)
	{
		// e_TOKEN_DISABLE deliberately does not stop the token being minted. It is
		// defined by error.php, and skipping the mint there did not withhold a
		// token, it published md5(null) instead: a constant, on a fully themed
		// error page, in the meta tag and in every form on it. A visitor whose
		// first request of a session was a dead link then had that value submitted
		// from the theme's login box, search or comment form, and a token that is
		// present and wrong is refused by every mode, including the ones an
		// operator sets to allow or merely log a request that brought none.
		if (!$this->session->has('__form_token'))
		{
			$this->session->set('__form_token', e_random::hex(64));
			if (deftrue('e_DEBUG_SESSION'))
			{
				$message = date('r') . "\t\t" . e_REQUEST_URI . "\n";
				file_put_contents(__DIR__ . '/session.log', $message, FILE_APPEND);
			}
		}
		return ($in_form ? md5($this->session->get('__form_token')) : $this->session->get('__form_token'));
	}

	/**
	 * Validate a submitted token
	 * @param string $token The token to validate
	 * @return bool
	 */
	public function validate($token)
	{
		$utoken = $this->getToken(false);
		return hash_equals(md5($utoken), (string) $token);
	}

	/**
	 * Regenerate the token
	 * @return void
	 */
	public function regenerate()
	{
		$this->session->set('__form_token', e_random::hex(64));
	}

	/**
	 * Clean up session token
	 * @return void
	 */
	public function cleanup()
	{
		$this->session->clear('__form_token');
	}
}

/**
 * Cookie-based CSRF token handler for guest users
 * Uses the double-submit cookie pattern with sealed tokens
 */
class CSRFCookieHandler extends CSRFTokenHandler
{
	/** @var string Cookie name for CSRF token */
	const COOKIE_NAME = 'e107_csrf';

	/** @var string Sealed token purpose, keeping these tokens unopenable elsewhere */
	const TOKEN_PURPOSE = 'csrf';

	/** @var e_sealed_token */
	protected $sealedToken;

	/** @var string Current token value */
	protected $currentToken = null;

	/** @var e_session Reference to session for reusing validation logic */
	protected $session;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->sealedToken = e107::getSealedToken(self::TOKEN_PURPOSE);
		$this->session = e107::getSession();
	}

	/**
	 * Get the CSRF token for forms
	 * @param bool $in_form If true, return the sealed token; if false, return raw value
	 * @return string
	 */
	public function getToken($in_form = true)
	{
		// Check if we have a valid cookie token
		$cookieToken = $this->getCookieToken();

		if (!$cookieToken)
		{
			// Generate new token
			$cookieToken = $this->generateToken();
			$this->setCookieToken($cookieToken);
		}

		$this->currentToken = $cookieToken;

		if ($in_form)
		{
			// Seal the cookie value together with the validation data, reusing
			// the session's own collection method. Sealing rather than signing
			// keeps the visitor's User-Agent and Via headers out of the page.
			$payload = [
				'csrf' => $cookieToken,
				'validation' => $this->stripNetworkAddresses($this->session->getValidateData())
			];

			// Use session lifetime for the sealed token's TTL. A lifetime of 0
			// is a supported setting meaning "until the browser closes", and
			// getOption() only falls back to its default when the key is
			// absent, which setDefaultSystemConfig() guarantees it never is. Fed
			// through unaltered it would seal every guest token with exp equal
			// to iat, so every guest POST on the site would be refused.
			$ttl = (int) $this->session->getOption('lifetime', 3600);

			if ($ttl <= 0)
			{
				$ttl = 3600;
			}

			$sealed = $this->sealedToken->seal($payload, $ttl);

			if ($sealed === false)
			{
				e107::getDebug()->log('CSRF token could not be sealed');
				return '';
			}

			return $sealed;
		}

		return $cookieToken;
	}

	/**
	 * Validate a submitted token
	 * @param string $token The sealed token to validate
	 * @return bool
	 */
	public function validate($token)
	{
		$data = $this->sealedToken->open($token);

		if ($data === false || !isset($data['csrf']))
		{
			e107::getDebug()->log('CSRF validation failed: Invalid sealed token');
			return false;
		}

		// Get cookie value
		$cookieToken = $this->getCookieToken();

		if (!$cookieToken)
		{
			e107::getDebug()->log('CSRF validation failed: No cookie token found');
			return false;
		}

		// Compare values
		if (!hash_equals((string) $data['csrf'], (string) $cookieToken))
		{
			e107::getDebug()->log('CSRF validation failed: Token mismatch');
			return false;
		}

		// Validate request fingerprint if present
		if (isset($data['validation']) && !$this->validateRequestFingerprint($data['validation']))
		{
			e107::getDebug()->log('CSRF validation failed: Request fingerprint mismatch');
			return false;
		}

		return true;
	}

	/**
	 * Validate request fingerprint using the same logic as session validation in {@see e_session::_validate()}
	 * @param array|stdClass $storedData The validation data carried in the sealed token
	 * @return bool
	 */
	protected function validateRequestFingerprint($storedData)
	{
		$storedData = (array) $storedData;

		// Get current request data
		$currentData = $this->session->getValidateData();

		// Check what should be validated based on security level.
		// The caller's network address is deliberately not among the rules, see
		// {@see CSRFCookieHandler::stripNetworkAddresses()}.
		$validationRules = [
			'HttpVia' => (e_SECURITY_LEVEL >= e_session::SECURITY_LEVEL_HIGH),
			'HttpUserAgent' => (e_SECURITY_LEVEL >= e_session::SECURITY_LEVEL_HIGH)
		];

		foreach ($validationRules as $field => $shouldValidate)
		{
			if ($shouldValidate)
			{
				// Compare stored vs current, but allow empty values
				if (!empty($storedData[$field]) && !empty($currentData[$field])
					&& $storedData[$field] !== $currentData[$field])
				{
					e107::getDebug()->log("CSRF validation: $field mismatch - stored: {$storedData[$field]}, current: {$currentData[$field]}");
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Drop the caller's network address from the fingerprint carried by the token.
	 *
	 * The token is handed to a guest, so it travels in the page. Binding it to an
	 * IP address buys nothing against forgery, because a forged request is made by
	 * the victim's own browser from the victim's own address, while it costs a
	 * rejection every time a visitor moves between mobile data and Wi-Fi, and it
	 * would publish that visitor's address in the page to anything that caches it.
	 *
	 * @param array $data as collected by {@see e_session::getValidateData()}
	 * @return array
	 */
	protected function stripNetworkAddresses($data)
	{
		unset($data['RemoteAddr'], $data['HttpXForwardedFor']);

		return $data;
	}

	/**
	 * Regenerate the token
	 * @return void
	 */
	public function regenerate()
	{
		$newToken = $this->generateToken();
		$this->setCookieToken($newToken);
		$this->currentToken = $newToken;
	}

	/**
	 * Clean up cookie token
	 * @return void
	 */
	public function cleanup()
	{
		// Delete the cookie
		$this->deleteCookieToken();
		$this->currentToken = null;
	}

	/**
	 * Generate a new random token
	 * @return string
	 */
	protected function generateToken()
	{
		return e_random::hex(32);
	}

	/**
	 * Get token from cookie
	 * @return string|null
	 */
	protected function getCookieToken()
	{
		return isset($_COOKIE[self::COOKIE_NAME]) ? $_COOKIE[self::COOKIE_NAME] : null;
	}

	/**
	 * Set token in cookie
	 * @param string $token
	 * @return void
	 */
	protected function setCookieToken($token)
	{
		// Get session options for consistency
		$session = e107::getSession();
		$options = $session->getOptions();

		// Outlive the browser exactly as long as the session cookie does. A
		// browser-session cookie was thrown away on close while the page holding
		// the matching token was not, so a restored tab submitted a token there
		// was no longer anything to compare it with.
		$lifetime = (int) $session->getOption('lifetime', 0);

		$params = [
			'expires' => ($lifetime > 0) ? (time() + $lifetime) : 0,
			'path' => $options['path'] ?: '/',
			'domain' => $options['domain'] ?: '',
			'secure' => $options['secure'] ?: false,
			'httponly' => true,
			'samesite' => $session->getOption('samesite', 'Lax')
		];

		eShims::setcookie(self::COOKIE_NAME, $token, $params);

		// Also set in $_COOKIE for immediate availability
		$_COOKIE[self::COOKIE_NAME] = $token;
	}

	/**
	 * Delete the cookie token
	 * @return void
	 */
	protected function deleteCookieToken()
	{
		// Get session options for consistency
		$session = e107::getSession();
		$options = $session->getOptions();

		// Delete cookie
		eShims::setcookie(self::COOKIE_NAME, '', time() - 3600,
				  $options['path'] ?: '/',
				  $options['domain'] ?: '',
				  $options['secure'] ?: false,
				  true);

		// Remove from $_COOKIE
		unset($_COOKIE[self::COOKIE_NAME]);
	}
}