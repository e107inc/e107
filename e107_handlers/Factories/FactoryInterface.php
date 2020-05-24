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
 * Factory for making objects of different implementations
 */
interface FactoryInterface
{
	/**
	 * Create an new instance of whatever implementation of interface this factory can supply
	 * @param string|null $type If not null, create an instance of this specific type rather than the default
	 * @param array $args Dependencies to inject into the constructor
	 * @return object A new instance of whatever implementation of interface this factory can supply
	 * @throws InvalidArgumentException if the provided type cannot be supplied by this factory
	 */
	public static function make($type = null, ...$args);

	/**
	 * Get a list of classes that this factory can instantiate
	 * @return string[] List of classes (in no particular order) that this factory can instantiate
	 */
	public static function getImplementations();

	/**
	 * Get the class that would be instantiated if make() were called without any parameters
	 * @return string The class that would be instantiated if make() were called without any parameters
	 * @see FactoryInterface::make() to get an object from this class
	 */
	public static function getDefaultImplementation();
}