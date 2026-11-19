<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Shims for PHP internal functions
 * strcoll()
 */

namespace e107\Shims\Internal;

/**
 *
 */
trait StrcollTrait
{
	/**
	 * Compatible replacement for PHP internal {@see strcoll()}, which PHP 8.6 deprecates.
	 *
	 * @param string $string1
	 * @param string $string2
	 * @return int below, at or above zero as $string1 sorts before, with or after $string2 in the LC_COLLATE locale
	 */
	public static function strcoll($string1, $string2)
	{
		if(PHP_VERSION_ID < 80600)
		{
			return \strcoll($string1, $string2);
		}

		return self::strcoll_alt($string1, $string2);
	}

	/**
	 * Alternative implementation through intl's {@see \Collator}, which passes over spaces and punctuation until the letters tie, as glibc's strcoll() does.
	 *
	 * Byte order where intl is missing or the locale is C or POSIX, as strcoll() sorts there; a string that is not UTF-8 sorts after every string that is.
	 *
	 * @param string $string1
	 * @param string $string2
	 * @return int below, at or above zero as $string1 sorts before, with or after $string2 in the LC_COLLATE locale
	 */
	public static function strcoll_alt($string1, $string2)
	{
		$string1 = (string) $string1;
		$string2 = (string) $string2;
		$collator = self::collatorForLcCollate();

		if(null === $collator)
		{
			return strcmp($string1, $string2);
		}

		$utf8 = array(1 === preg_match('//u', $string1), 1 === preg_match('//u', $string2));

		if(array(true, true) === $utf8)
		{
			return $collator->compare($string1, $string2);
		}

		return $utf8[0] === $utf8[1] ? strcmp($string1, $string2) : ($utf8[0] ? -1 : 1);
	}

	/**
	 * @return \Collator|null for the LC_COLLATE locale, in ICU's root order where ICU has no rules of its own for it; null where that is C or POSIX, or where intl is missing
	 */
	private static function collatorForLcCollate()
	{
		static $collators = array();

		$locale = (string) setlocale(LC_COLLATE, 0);

		if(!class_exists('Collator') || 1 === preg_match('/^(C|POSIX)?([.@]|$)/i', $locale))
		{
			return null;
		}

		if(!isset($collators[$locale]))
		{
			$collator = new \Collator($locale);
			$collator->setAttribute(\Collator::ALTERNATE_HANDLING, \Collator::SHIFTED);
			$collator->setAttribute(\Collator::STRENGTH, \Collator::QUATERNARY);
			$collators[$locale] = $collator;
		}

		return $collators[$locale];
	}
}
