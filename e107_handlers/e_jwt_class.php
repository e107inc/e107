<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2025 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

/**
 * Handler for encrypting and decrypting a string secret that only the e107 server can read
 *
 * Provides JWT encoding/decoding functionality
 */
class e_jwt
{
	/** @var string */
	private $algorithm = 'HS256';
	/** @var null|string */
	private $secretKey = null;
	/** @var string */
	private $issuer;
	/** @var int Clock skew tolerance in seconds */
	private $leeway = 60;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->issuer = SITEURL;
		$this->initializeSecretKey();
		
		// Set leeway for clock skew
		JWT::$leeway = $this->leeway;
	}
	
	/**
	 * Initialize or retrieve the secret key
	 */
	private function initializeSecretKey()
	{
		// Try to get from config first
		$config = e107::getConfig();
		$jwtSecret = $config->get('jwt_secret');
		
		if (empty($jwtSecret))
		{
			// Generate a new secret key
			$jwtSecret = $this->generateSecretKey();
			
			// Store it in preferences
			$config->set('jwt_secret', $jwtSecret);
			$config->save(false, true, false);
		}
		
		$this->secretKey = $jwtSecret;
	}
	
	/**
	 * Generate a cryptographically secure secret key
	 * @return string
	 */
	private function generateSecretKey()
	{
		// Use site-specific data for some entropy
		$siteData = [
			e107::getPref('sitename', ''),
			e107::getPref('siteurl', ''),
			e107::getPref('siteadminemail', ''),
			defined('ADMINPERMS') ? ADMINPERMS : '',
			PHP_VERSION,
			__FILE__
		];
		
		// Generate random bytes for additional entropy
		$randomBytes = random_bytes(32);
		
		// Combine site data with random bytes
		$combined = implode('|', $siteData) . '|' . base64_encode($randomBytes);
		
		// Create a strong hash
		return hash('sha256', $combined);
	}
	
	/**
	 * Encode data into a JWT token
	 * @param array $payload The data to encode
	 * @param int $ttl Time to live in seconds (default: 600 = 10 minutes)
	 * @return string The JWT token
	 */
	public function encode($payload, $ttl = 600)
	{
		$issuedAt = time();
		$expire = $issuedAt + $ttl;
		
		// Standard JWT claims
		$token = array(
			'iss' => $this->issuer,        // Issuer
			'iat' => $issuedAt,             // Issued at
			'nbf' => $issuedAt,             // Not before
			'exp' => $expire,               // Expire
			'jti' => uniqid('', true),     // JWT ID
			'data' => $payload              // Custom data
		);
		
		return JWT::encode($token, $this->secretKey, $this->algorithm);
	}
	
	/**
	 * Decode and verify a JWT token
	 * @param string $token The JWT token to decode
	 * @return array|false The decoded payload or false on failure
	 */
	public function decode($token)
	{
		$e107_db_debug = e107::getDebug();

		try
		{
			$decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
			
			// Verify issuer
			if ($decoded->iss !== $this->issuer)
			{
				$e107_db_debug->log('JWT decode failed: Invalid issuer');
				return false;
			}
			
			// Return the custom data
			return (array) $decoded->data;
		}
		catch (ExpiredException $e)
		{
			$e107_db_debug->log('JWT decode failed: Token expired - ' . $e->getMessage());
			return false;
		}
		catch (SignatureInvalidException $e)
		{
			$e107_db_debug->log('JWT decode failed: Invalid signature - ' . $e->getMessage());
			return false;
		}
		catch (BeforeValidException $e)
		{
			$e107_db_debug->log('JWT decode failed: Token not yet valid - ' . $e->getMessage());
			return false;
		}
		catch (Exception $e)
		{
			$e107_db_debug->log('JWT decode failed: ' . $e->getMessage());
			return false;
		}
	}
}