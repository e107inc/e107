<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims\Internal;

/**
 *
 */
trait GetParentClassTrait
{
	/**
	 * Retrieves the parent class name for object or class
	 *
	 * Compatible replacement for PHP internal get_parent_class()
	 *
	 * Maintains compatibility with PHP 5.6 while suppressing the TypeError
	 * thrown by PHP 8.0 if the string provided does not resolve into an extant
	 * class
	 *
	 * @param string|object $object An object or a string that may be a class
	 * @return false|string The parent class as a string or FALSE otherwise
	 */
	public static function get_parent_class($object)
	{
		try
		{
			return \get_parent_class($object);
		}
		catch (\Throwable $_)
		{
			return false;
		}
	}
}