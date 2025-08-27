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
		if (!$this->session->has('__form_token') && !defined('e_TOKEN_DISABLE'))
		{
			$this->session->set('__form_token', uniqid(md5(rand()), true));
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
		return ($token === md5($utoken));
	}

	/**
	 * Regenerate the token
	 * @return void
	 */
	public function regenerate()
	{
		$this->session->set('__form_token', uniqid(md5(rand()), true));
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
 * Uses double-submit cookie pattern with JWT tokens
 */
class CSRFCookieHandler extends CSRFTokenHandler
{
	/** @var string Cookie name for CSRF token */
	const COOKIE_NAME = 'e107_csrf';

	/** @var e_jwt */
	protected $jwt;

	/** @var string Current token value */
	protected $currentToken = null;

	/** @var e_session Reference to session for reusing validation logic */
	protected $session;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->jwt = e107::getJWT();
		$this->session = e107::getSession();
	}

	/**
	 * Get the CSRF token for forms
	 * @param bool $in_form If true, return JWT; if false, return raw value
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
			// Create JWT containing the cookie value and validation data
			// Reuse the session's validation data collection method
			$payload = [
				'csrf' => $cookieToken,
				'validation' => $this->session->getValidateData()
			];

			// Use session lifetime for JWT token TTL
			$ttl = $this->session->getOption('lifetime', 3600);
			return $this->jwt->encode($payload, $ttl);
		}

		return $cookieToken;
	}

	/**
	 * Validate a submitted token
	 * @param string $token The JWT token to validate
	 * @return bool
	 */
	public function validate($token)
	{
		// Decode JWT
		$data = $this->jwt->decode($token);

		if ($data === false || !isset($data['csrf']))
		{
			e107::getDebug()->log('CSRF validation failed: Invalid JWT token');
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
		if ($data['csrf'] !== $cookieToken)
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
	 * @param array|stdClass $storedData The validation data stored in the JWT
	 * @return bool
	 */
	protected function validateRequestFingerprint($storedData)
	{
		// Convert stdClass to array if needed (JWT decode returns objects)
		$storedData = (array) $storedData;

		// Get current request data
		$currentData = $this->session->getValidateData();

		// Check what should be validated based on security level
		$validationRules = [
			'RemoteAddr' => (e_SECURITY_LEVEL >= e_session::SECURITY_LEVEL_BALANCED),
			'HttpVia' => (e_SECURITY_LEVEL >= e_session::SECURITY_LEVEL_HIGH),
			'HttpXForwardedFor' => (e_SECURITY_LEVEL >= e_session::SECURITY_LEVEL_LOW),
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
		return bin2hex(random_bytes(16));
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

		$params = [
			'expires' => 0,
			'path' => $options['path'] ?: '/',
			'domain' => $options['domain'] ?: '',
			'secure' => $options['secure'] ?: false,
			'httponly' => true,
			'samesite' => 'Lax'
		];

		setcookie(self::COOKIE_NAME, $token, $params);

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
		setcookie(self::COOKIE_NAME, '', time() - 3600,
				  $options['path'] ?: '/',
				  $options['domain'] ?: '',
				  $options['secure'] ?: false,
				  true);

		// Remove from $_COOKIE
		unset($_COOKIE[self::COOKIE_NAME]);
	}
}