<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Installer wizard-state transport.
 */

if(!defined('e107_INIT'))
{
	exit;
}

/**
 * Encode installer wizard state for transport in a hidden form field.
 *
 * The multi-stage installer carries its accumulated answers ($previous_steps,
 * a plain array of scalar config) across requests.
 *
 * @param array $state
 * @return string base64-encoded JSON
 */
function install_state_encode(array $state)
{
	return base64_encode(json_encode($state));
}

/**
 * Decode wizard state produced by {@see install_state_encode()}.
 *
 * Always returns an array and never instantiates an object: client input is
 * only ever passed through json_decode(), which yields arrays and scalars but
 * no objects.
 *
 * @param mixed $raw base64-encoded JSON state
 * @return array
 */
function install_state_decode($raw)
{
	$decoded = base64_decode((string) $raw, true);
	if($decoded === false || $decoded === '')
	{
		return array();
	}

	$state = json_decode($decoded, true);

	return is_array($state) ? $state : array();
}

/**
 * Generate a provisioning token: the installation lock and HMAC signing key.
 *
 * 256 bits of CSPRNG output as 64 hex characters (safe to embed in either
 * e107_config.php format). Fails closed - returns false rather than falling back
 * to a weak source - so the caller must abort the install when no CSPRNG exists.
 *
 * @return string|false 64 hex characters, or false if no CSPRNG is available
 */
function install_state_generate_token()
{
	$bytes = false;

	if(function_exists('random_bytes'))
	{
		try
		{
			$bytes = random_bytes(32);
		}
		catch(Exception $e)
		{
			$bytes = false;
		}
	}

	if($bytes === false && function_exists('openssl_random_pseudo_bytes'))
	{
		$strong = false;
		$candidate = openssl_random_pseudo_bytes(32, $strong);
		if($strong === true && is_string($candidate) && strlen($candidate) === 32)
		{
			$bytes = $candidate;
		}
	}

	if($bytes === false)
	{
		return false;
	}

	return bin2hex($bytes);
}

/**
 * Sign wizard state for handing back to the client.
 *
 * Wire form is "<payload>.<mac>": payload is base64-encoded JSON, mac is the hex
 * HMAC-SHA256 of that payload under $token. Lets the server trust state it
 * receives back without keeping a server-side copy.
 *
 * @param array  $state
 * @param string $token provisioning token (signing key)
 * @return string
 */
function install_state_sign(array $state, $token)
{
	$payload = base64_encode(json_encode($state));
	$mac = hash_hmac('sha256', $payload, (string) $token);

	return $payload . '.' . $mac;
}

/**
 * Verify and decode a blob produced by {@see install_state_sign()}.
 *
 * Fails closed: the MAC is checked with hash_equals() over the payload bytes
 * exactly as received, BEFORE decoding, so canonicalisation differences and
 * object injection cannot influence the result. A missing or too-short token is
 * rejected, so an empty key can never validate. Returns null (not array()) for
 * "no valid signed state" so callers can tell it from a valid empty array.
 *
 * @param mixed  $blob  signed blob from $_POST or a cookie
 * @param string $token provisioning token read from e107_config.php
 * @return array|null
 */
function install_state_verify($blob, $token)
{
	if(!is_string($blob) || !is_string($token) || strlen($token) < 32)
	{
		return null;
	}

	$dot = strrpos($blob, '.');
	if($dot === false || $dot === 0 || $dot === strlen($blob) - 1)
	{
		return null;
	}

	$payload = substr($blob, 0, $dot);
	$provided = substr($blob, $dot + 1);
	$expected = hash_hmac('sha256', $payload, $token);

	if(!hash_equals($expected, $provided))
	{
		return null;
	}

	$json = base64_decode($payload, true);
	if($json === false)
	{
		return null;
	}

	$state = json_decode($json, true);

	return is_array($state) ? $state : null;
}
