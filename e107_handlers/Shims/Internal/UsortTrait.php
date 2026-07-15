<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Shims for PHP internal functions
 * usort()
 */

namespace e107\Shims\Internal;

/**
 *
 */
trait UsortTrait
{
	/**
	 * Sort an array by values using a user-defined comparison function
	 *
	 * Compatible replacement for PHP internal {@see usort()} that guarantees a
	 * stable sort on every supported PHP version. PHP made all sorts stable in
	 * PHP 8.0; on older versions, elements that compare equal may be reordered
	 * arbitrarily by the native implementation.
	 *
	 * @param array    $array    The input array, sorted in place and reindexed.
	 * @param callable $callback Comparison function returning an integer less
	 *                           than, equal to, or greater than zero.
	 * @return bool Always TRUE.
	 */
	public static function usort(array &$array, callable $callback)
	{
		if (PHP_VERSION_ID >= 80000)
		{
			return \usort($array, $callback);
		}

		return self::usort_alt($array, $callback);
	}

	/**
	 * Sort an array by values using a user-defined comparison function
	 *
	 * Alternative implementation that decorates each element with its original
	 * position, so elements that compare equal keep their input order on any
	 * PHP version.
	 *
	 * @param array    $array    The input array, sorted in place and reindexed.
	 * @param callable $callback Comparison function.
	 * @return bool Always TRUE.
	 */
	public static function usort_alt(array &$array, callable $callback)
	{
		$decorated = array();
		$position = 0;
		foreach ($array as $value)
		{
			$decorated[] = array($position++, $value);
		}

		\usort($decorated, function ($a, $b) use ($callback)
		{
			$result = (int) call_user_func($callback, $a[1], $b[1]);

			return $result !== 0 ? $result : $a[0] - $b[0];
		});

		$array = array();
		foreach ($decorated as $item)
		{
			$array[] = $item[1];
		}

		return true;
	}
}
