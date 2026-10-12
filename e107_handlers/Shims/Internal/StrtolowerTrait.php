<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Shims for PHP internal functions
 * strtolower()
 */

namespace e107\Shims\Internal;

/**
 *
 */
trait StrtolowerTrait
{
	/**
	 * Lowercase the ASCII letters alone, in any locale, as {@see strtolower()} does from PHP 8.2.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function strtolower($string)
	{
		if (PHP_VERSION_ID >= 80200)
		{
			return \strtolower((string) $string);
		}

		return self::strtolower_alt($string);
	}

	/**
	 * Lowercase the ASCII letters alone with {@see strtr()}, which no locale affects.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function strtolower_alt($string)
	{
		return strtr((string) $string, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
	}
}
