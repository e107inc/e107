<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Shims for PHP internal functions
 */

namespace e107\Shims\Internal;

/**
 *
 */
trait SetcookieTrait
{
	/**
	 * Drop-in replacement for {@see setcookie()} that understands the PHP 7.3+
	 * options-array form on every runtime e107 supports.
	 *
	 * PHP 7.3 turned the third argument into an options array so attributes such
	 * as `SameSite` could be set; before that there was no way to send `SameSite`
	 * through {@see setcookie()} at all. Because e107 still targets PHP 5.6 (see
	 * the Rector downgrade in e107inc/e107#5669), callers that pass the options
	 * array would fatal on older runtimes. Routing every {@see setcookie()} call
	 * through this shim keeps that version branch in one place: on PHP 7.3+ the
	 * array is forwarded untouched; on older runtimes it is translated to the
	 * legacy positional call and any `samesite` value is folded into the path,
	 * the widely-supported workaround.
	 *
	 * Both call shapes are accepted, mirroring {@see setcookie()} itself:
	 *   eShims::setcookie($name, $value, $expires, $path, $domain, $secure, $httponly)
	 *   eShims::setcookie($name, $value, ['expires' => ..., 'samesite' => 'Lax', ...])
	 *
	 * @param string    $name
	 * @param string    $value
	 * @param int|array $expires_or_options expiry timestamp, or a 7.3+ options array
	 * @param string    $path
	 * @param string    $domain
	 * @param bool      $secure
	 * @param bool      $httponly
	 * @return bool whether the header was queued successfully
	 */
	public static function setcookie($name, $value = '', $expires_or_options = 0, $path = '', $domain = '', $secure = false, $httponly = false)
	{
		if (!is_array($expires_or_options))
		{
			return \setcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly);
		}

		if (PHP_VERSION_ID >= 70300)
		{
			return \setcookie($name, $value, $expires_or_options);
		}

		// PHP < 7.3: unpack the options array into the legacy positional call.
		$options = $expires_or_options;
		$expires = isset($options['expires']) ? $options['expires'] : 0;
		$path = isset($options['path']) ? $options['path'] : '';
		$domain = isset($options['domain']) ? $options['domain'] : '';
		$secure = isset($options['secure']) ? $options['secure'] : false;
		$httponly = isset($options['httponly']) ? $options['httponly'] : false;

		if (!empty($options['samesite']))
		{
			// No SameSite parameter exists before 7.3; append it to the path (widely supported).
			$path .= '; samesite=' . $options['samesite'];
		}

		return \setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
	}
}
