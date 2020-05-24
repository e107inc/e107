<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace e107\Factories;

use InvalidArgumentException;

/**
 * Common factory for making new objects
 */
abstract class BaseFactory implements FactoryInterface
{
	public static function make($type = null, ...$args)
	{
		if (is_null($type)) $type = static::getDefaultImplementation();
		if (!in_array($type, static::getImplementations())) throw new InvalidArgumentException(
			"Factory " . self::class . " cannot create a " . $type . " object"
		);
		return new $type(...$args);
	}
}