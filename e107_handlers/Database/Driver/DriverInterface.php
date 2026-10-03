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

use e107\Database\ConnectionInterface;
use e107\Database\Platform\PlatformInterface;
use e107\Database\Schema\SchemaManagerInterface;

/**
 * One stateless database engine, made by name with {@see DriverRegistry::create()}.
 *
 * @see PdoDriverInterface
 */
interface DriverInterface
{
	/**
	 * The registry name, as written in e107_config.php, e.g. 'mysql'.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * A human-readable name for installer and admin screens, e.g. 'MySQL / MariaDB'.
	 *
	 * @return string
	 */
	public function getLabel();

	/**
	 * Whether this PHP installation can use the engine at all (its extension is loaded).
	 *
	 * @return bool
	 */
	public function isAvailable();

	/**
	 * Whether the engine is reached through a server rather than opened from a local file.
	 *
	 * @return bool
	 */
	public function requiresServer();

	/**
	 * The oldest server (or library) version e107 supports on this engine.
	 *
	 * @return string|null version string comparable with version_compare(), or null for no floor
	 */
	public function getMinimumServerVersion();

	/**
	 * The statement that sets the session's character set; the connection runs it through its own query path.
	 *
	 * @param string $charset character set name, already validated by the caller
	 * @return string|null the statement, or null when the engine has a single fixed encoding
	 */
	public function getCharsetStatement($charset);

	/**
	 * Statements that put a new session into the mode e107 expects, for the connection to run.
	 *
	 * @return string[]
	 */
	public function getSessionStatements();

	/**
	 * The statement that reads the row total the previous SELECT SQL_CALC_FOUND_ROWS would have returned without its LIMIT.
	 *
	 * @return string|null the statement, or null when the engine keeps no such count
	 */
	public function getFoundRowsStatement();

	/**
	 * The statements that open a transaction, run in order until one fails; COMMIT or ROLLBACK ends it, and SAVEPOINT nests inside it.
	 *
	 * @return string[]
	 */
	public function getBeginTransactionStatements();

	/**
	 * Whether the engine has a transaction open in the connection's session; asking leaves the session as it was.
	 *
	 * @param ConnectionInterface $connection a connection to the session that holds no result the question could replace
	 * @return bool
	 */
	public function isTransactionOpen(ConnectionInterface $connection);

	/**
	 * The SQL dialect of this engine.
	 *
	 * @param string|null $serverVersion the server's version, or null when not yet known
	 * @return PlatformInterface
	 */
	public function createPlatform($serverVersion = null);

	/**
	 * The schema manager for one connection to this engine.
	 *
	 * @param ConnectionInterface $connection the connection the manager runs its statements on
	 * @return SchemaManagerInterface
	 */
	public function createSchemaManager(ConnectionInterface $connection);

	/**
	 * The session's SQL mode, for display ({@see ConnectionInterface::getMode()}).
	 *
	 * @param ConnectionInterface $connection
	 * @return string the mode, or '' when the engine has no such setting
	 */
	public function getSessionMode(ConnectionInterface $connection);

	/**
	 * Make the session refuse, not rewrite, a value that does not fit, until {@see DriverInterface::leaveStrictMode()}.
	 *
	 * @param ConnectionInterface $connection
	 * @return mixed what leaveStrictMode() needs; null on an engine that never rewrites a value to fit
	 */
	public function enterStrictMode(ConnectionInterface $connection);

	/**
	 * @param ConnectionInterface $connection
	 * @param mixed $restore what {@see DriverInterface::enterStrictMode()} returned
	 * @return void
	 */
	public function leaveStrictMode(ConnectionInterface $connection, $restore);

	/**
	 * Take a named advisory lock; names are shared by every site the engine serves.
	 *
	 * @param ConnectionInterface $connection the connection that holds the lock
	 * @param string $name
	 * @param int $timeout seconds to wait for a holder to let go; 0 to fail at once
	 * @return bool|null true when taken, false when another session holds it, null when the engine did not say
	 */
	public function acquireLock(ConnectionInterface $connection, $name, $timeout);

	/**
	 * Give back a lock {@see DriverInterface::acquireLock()} took.
	 *
	 * @param ConnectionInterface $connection the connection that holds the lock
	 * @param string $name
	 * @return bool whether the lock was released
	 */
	public function releaseLock(ConnectionInterface $connection, $name);
}
