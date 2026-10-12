<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Shims for PHP internal functions
 * strtoupper()
 */

namespace e107\Shims\Internal;

/**
 *
 */
trait StrtoupperTrait
{
	/**
	 * Uppercase the ASCII letters alone, in any locale, as {@see strtoupper()} does from PHP 8.2.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function strtoupper($string)
	{
		if (PHP_VERSION_ID >= 80200)
		{
			return \strtoupper((string) $string);
		}

		return self::strtoupper_alt($string);
	}

	/**
	 * Uppercase the ASCII letters alone with {@see strtr()}, which no locale affects.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function strtoupper_alt($string)
	{
		return strtr((string) $string, 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
	}
}
