<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Shims for PHP internal functions
 * session_create_id()
 */

namespace e107\Shims\Internal;

use e107\Security\Random;

/**
 *
 */
trait SessionCreateIdTrait
{
	/**
	 * Compatible replacement for PHP internal {@see session_create_id()}, which PHP 7.1 introduced.
	 *
	 * @param string $prefix up to 256 of the characters a-z, A-Z, 0-9, "," and "-", put in front of the id
	 * @return string|false a new session id, false with a warning for a prefix with any other character or where PHP could not make one
	 * @throws \ValueError from PHP 8.5 on, for a prefix longer than 256 characters, which older PHP refuses with a warning and false
	 * @throws \e107\Security\RandomException when PHP 5.6 or 7.0 has no cryptographically secure source
	 */
	public static function session_create_id($prefix = '')
	{
		if(function_exists('session_create_id'))
		{
			return \session_create_id($prefix);
		}

		return self::session_create_id_alt($prefix);
	}

	/**
	 * Alternative implementation, which makes the id PHP makes at its default settings.
	 *
	 * @param string $prefix as for {@see SessionCreateIdTrait::session_create_id()}
	 * @return string|false $prefix and 32 lowercase hexadecimal characters, false with a warning for a prefix PHP would refuse
	 * @throws \e107\Security\RandomException when there is no cryptographically secure source
	 */
	public static function session_create_id_alt($prefix = '')
	{
		$prefix = (string) $prefix;

		if('' !== $prefix && 1 !== preg_match('/^[a-zA-Z0-9,-]{1,256}$/', $prefix))
		{
			trigger_error('session_create_id(): Prefix cannot contain special characters. Only the A-Z, a-z, 0-9, "-", and "," characters are allowed', E_USER_WARNING);

			return false;
		}

		return $prefix.Random::hex(32);
	}
}
