<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Driver;

use InvalidArgumentException;

require_once(__DIR__.'/DriverInterface.php');

/**
 * The database engines e107 knows, by the name e107_config.php uses for them.
 */
final class DriverRegistry
{
	/** @var string the driver a configuration without a 'driver' key uses */
	const DEFAULT_DRIVER = 'mysql';

	/**
	 * @var array name => array(class, the file in this directory that declares it)
	 */
	private static $drivers = array(
		'mysql'  => array('e107\Database\Driver\MysqlDriver', 'MysqlDriver.php'),
		'sqlite' => array('e107\Database\Driver\SqliteDriver', 'SqliteDriver.php'),
	);

	/**
	 * @param string $name
	 * @return bool whether an engine is registered under the name
	 */
	public static function has($name)
	{
		return is_string($name) && isset(self::$drivers[$name]);
	}

	/**
	 * @return string[] every registered name
	 */
	public static function names()
	{
		return array_keys(self::$drivers);
	}

	/**
	 * Names of the registered engines this PHP installation can use.
	 *
	 * @return string[]
	 */
	public static function available()
	{
		$names = array();

		foreach(self::names() as $name)
		{
			if(self::create($name)->isAvailable())
			{
				$names[] = $name;
			}
		}

		return $names;
	}

	/**
	 * A new driver instance.
	 *
	 * @param string|null $name registry name; null or '' for {@see DriverRegistry::DEFAULT_DRIVER}
	 * @param array $settings what the driver's constructor takes, e.g. {@see SqliteDriver::__construct()}'s
	 * @return DriverInterface
	 * @throws InvalidArgumentException when no engine is registered under the name
	 */
	public static function create($name = null, array $settings = array())
	{
		if($name === null || $name === '')
		{
			$name = self::DEFAULT_DRIVER;
		}

		if(!self::has($name))
		{
			throw new InvalidArgumentException('Unknown database driver "'.$name.'"; known drivers: '.implode(', ', self::names()).'.');
		}

		list($class, $file) = self::$drivers[$name];

		if(!class_exists($class))
		{
			require_once(__DIR__.'/'.$file);
		}

		return new $class($settings);
	}
}
