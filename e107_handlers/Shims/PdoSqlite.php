<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;

/**
 * pdo_sqlite the same on every PHP: 8.4 moved the handle and function registration to Pdo\Sqlite, 8.5 deprecates
 * PDO::sqliteCreateFunction(), and before 7.3.20 and 7.4.8 an empty result describes no column.
 */
final class PdoSqlite
{
	/**
	 * @param string $dsn 'sqlite:' and a path, or 'sqlite::memory:'
	 * @param array $options PDO attributes
	 * @return PDO a Pdo\Sqlite where PHP has the class
	 */
	public static function connect($dsn, array $options = array())
	{
		$class = self::hasSqliteClass() ? 'Pdo\\Sqlite' : 'PDO';

		return new $class($dsn, null, null, $options);
	}

	/**
	 * @param PDO $pdo a handle from {@see PdoSqlite::connect()}
	 * @param string $name
	 * @param callable $callback
	 * @param int $argumentCount -1 for any number
	 * @return void
	 * @throws InvalidArgumentException for a plain PDO handle where PHP has Pdo\Sqlite
	 */
	public static function createFunction(PDO $pdo, $name, $callback, $argumentCount)
	{
		if(self::hasSqliteClass())
		{
			self::sqliteHandle($pdo)->createFunction($name, $callback, $argumentCount);
			return;
		}

		$pdo->sqliteCreateFunction($name, $callback, $argumentCount);
	}

	/**
	 * @param PDO $pdo a handle from {@see PdoSqlite::connect()}
	 * @param string $name
	 * @param callable $step
	 * @param callable $final
	 * @param int $argumentCount -1 for any number
	 * @return void
	 * @throws InvalidArgumentException for a plain PDO handle where PHP has Pdo\Sqlite
	 */
	public static function createAggregate(PDO $pdo, $name, $step, $final, $argumentCount)
	{
		if(self::hasSqliteClass())
		{
			self::sqliteHandle($pdo)->createAggregate($name, $step, $final, $argumentCount);
			return;
		}

		$pdo->sqliteCreateAggregate($name, $step, $final, $argumentCount);
	}

	/**
	 * {@see PDOStatement::getColumnMeta()}, but false instead of an error where pdo_sqlite cannot describe the column.
	 *
	 * @param PDOStatement $statement
	 * @param int $column zero-based
	 * @return array|false
	 */
	public static function columnMeta(PDOStatement $statement, $column)
	{
		try
		{
			return $statement->getColumnMeta($column);
		}
		catch(PDOException $e)
		{
			return false;
		}
	}

	/**
	 * @return bool
	 */
	private static function hasSqliteClass()
	{
		return class_exists('Pdo\\Sqlite', false);
	}

	/**
	 * @param PDO $pdo
	 * @return PDO
	 * @throws InvalidArgumentException
	 */
	private static function sqliteHandle(PDO $pdo)
	{
		if(!is_a($pdo, 'Pdo\\Sqlite'))
		{
			throw new InvalidArgumentException('Open the SQLite handle with '.__CLASS__.'::connect().');
		}

		return $pdo;
	}
}
