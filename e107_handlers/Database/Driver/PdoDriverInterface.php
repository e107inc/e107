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

use Exception;
use PDO;
use PDOException;

require_once(__DIR__.'/DriverInterface.php');

/**
 * The engine-specific steps of a PDO-backed connection ({@see \e_db_pdo}).
 */
interface PdoDriverInterface extends DriverInterface
{
	/**
	 * Open the handle a session starts from.
	 *
	 * @param array $params 'server', 'port', 'user', 'password' and 'database'
	 * @return PDO|null null when the engine opens a handle per database, in {@see PdoDriverInterface::selectDatabase()}
	 * @throws PDOException when the engine refuses the connection
	 */
	public function connect(array $params);

	/**
	 * Make a database the session's default.
	 *
	 * @param PDO|null $pdo the handle {@see PdoDriverInterface::connect()} returned
	 * @param string $database database name, or whatever names a database on this engine
	 * @param array $params as for {@see PdoDriverInterface::connect()}
	 * @return PDO the handle to use from now on
	 * @throws PDOException when the database cannot be selected or opened
	 */
	public function selectDatabase($pdo, $database, array $params);

	/**
	 * The table prefix that reaches a second database, for {@see \e107\Database\ConnectionInterface::database()} with $multiple.
	 *
	 * @param PDO|null $pdo the session's handle
	 * @param string $database the second database
	 * @param string $prefix its e107 table prefix, e.g. 'e107_'
	 * @return string prefix to place in front of table names, e.g. '`otherdb`.e107_'
	 * @throws PDOException when the engine cannot reach the second database
	 */
	public function qualifyPrefix($pdo, $database, $prefix);

	/**
	 * Apply handle attributes once the session is open.
	 *
	 * @param PDO $pdo
	 * @return void
	 */
	public function configure($pdo);

	/**
	 * The version the server (or the library, for an embedded engine) reports.
	 *
	 * @param PDO|null $pdo the session's handle; null asks an embedded engine for its library version
	 * @return string
	 */
	public function getServerVersion($pdo);

	/**
	 * The number e107 records for a failed operation, {@see \e107\Database\ConnectionInterface::getLastErrorNumber()}.
	 *
	 * @param Exception $exception
	 * @return int a driver error number, or -1 when the failure carries none
	 */
	public function errorNumber($exception);

	/**
	 * Write a backup of some tables to a file.
	 *
	 * @param array $params as for {@see PdoDriverInterface::connect()}
	 * @param string[] $tables physical table names
	 * @param string $file destination path
	 * @param array $options 'gzip' (bool), 'droptable' (bool)
	 * @return string the path written
	 * @throws Exception when the backup fails; the message is recorded as the connection's last error
	 */
	public function backup(array $params, array $tables, $file, array $options);
}
