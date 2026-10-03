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
use e107\Database\Platform\MysqlPlatform;
use e107\Database\Schema\MysqlSchemaManager;
use Exception;
use Ifsnop\Mysqldump\Mysqldump;
use PDO;
use RuntimeException;

require_once(__DIR__.'/AbstractPdoDriver.php');

/**
 * MySQL and MariaDB, reached through pdo_mysql.
 */
class MysqlDriver extends AbstractPdoDriver
{
	/** @var int the port a server listens on unless the configuration says otherwise */
	const DEFAULT_PORT = 3306;

	/**
	 * @inheritDoc
	 */
	public function getName()
	{
		return 'mysql';
	}

	/**
	 * @inheritDoc
	 */
	public function getLabel()
	{
		return 'MySQL / MariaDB';
	}

	/**
	 * @inheritDoc
	 */
	public function isAvailable()
	{
		return extension_loaded('pdo_mysql');
	}

	/**
	 * @inheritDoc
	 */
	public function requiresServer()
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getMinimumServerVersion()
	{
		return '4.1.2';
	}

	/**
	 * @inheritDoc
	 */
	public function createPlatform($serverVersion = null)
	{
		if(!class_exists(MysqlPlatform::class, false))
		{
			require_once(dirname(__DIR__).'/Platform/MysqlPlatform.php');
		}

		return new MysqlPlatform();
	}

	/**
	 * @inheritDoc
	 */
	public function createSchemaManager(ConnectionInterface $connection)
	{
		if(!class_exists(MysqlSchemaManager::class, false))
		{
			require_once(dirname(__DIR__).'/Schema/MysqlSchemaManager.php');
		}

		return new MysqlSchemaManager($connection);
	}

	/**
	 * @inheritDoc
	 */
	public function getSessionMode(ConnectionInterface $connection)
	{
		if($connection->execute('SELECT @@sql_mode') === false)
		{
			return '';
		}

		$row = $connection->fetch();

		return isset($row['@@sql_mode']) ? (string) $row['@@sql_mode'] : '';
	}

	/**
	 * @inheritDoc
	 */
	public function enterStrictMode(ConnectionInterface $connection)
	{
		$mode = (string) $connection->getMode();
		$connection->execute("SET SESSION sql_mode = CONCAT(@@sql_mode, ',STRICT_TRANS_TABLES')");

		return $mode;
	}

	/**
	 * @inheritDoc
	 */
	public function leaveStrictMode(ConnectionInterface $connection, $restore)
	{
		$connection->execute('SET SESSION sql_mode = :mode', array('mode' => (string) $restore));
	}

	/**
	 * @inheritDoc
	 */
	public function connect(array $params)
	{
		return new PDO('mysql:host='.$params['server'].';port='.$params['port'], $params['user'], $params['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	}

	/**
	 * @inheritDoc
	 */
	public function selectDatabase($pdo, $database, array $params)
	{
		$pdo->exec('use '.$this->quoteDatabase($database));

		return $pdo;
	}

	/**
	 * @inheritDoc
	 */
	public function qualifyPrefix($pdo, $database, $prefix)
	{
		return $this->quoteDatabase($database).'.'.$prefix;
	}

	/**
	 * Disables PHP 8.1's typed result sets, for consistency with PHP 5.6 through 8.0.
	 *
	 * @link https://github.com/php/php-src/blob/4025cf2875f895e9f7193cebb1c8efa4290d052e/UPGRADING#L130-L134
	 * @inheritDoc
	 */
	public function configure($pdo)
	{
		$pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
	}

	/**
	 * @inheritDoc
	 */
	public function getCharsetStatement($charset)
	{
		return "SET NAMES `$charset`";
	}

	/**
	 * @inheritDoc
	 */
	public function getSessionStatements()
	{
		return array("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION';");
	}

	/**
	 * @inheritDoc
	 */
	public function getFoundRowsStatement()
	{
		return 'SELECT FOUND_ROWS()';
	}

	/**
	 * @inheritDoc
	 */
	public function getBeginTransactionStatements()
	{
		return array('START TRANSACTION');
	}

	/**
	 * @inheritDoc
	 */
	public function isTransactionOpen(ConnectionInterface $connection)
	{
		return $connection->execute('SAVEPOINT e107_probe') !== false && $connection->execute('RELEASE SAVEPOINT e107_probe') !== false;
	}

	/**
	 * @inheritDoc
	 */
	public function getServerVersion($pdo)
	{
		return $pdo->query('select version()')->fetchColumn();
	}

	/**
	 * pdo_mysql buffers every result client-side already, so the statement serves as it is.
	 *
	 * @inheritDoc
	 */
	public function wrapResult($statement)
	{
		return $statement;
	}

	/**
	 * Dumps through mysqldump-php, which opens its own connection from the same parameters.
	 *
	 * @inheritDoc
	 */
	public function backup(array $params, array $tables, $file, array $options)
	{
		$dumpSettings = array(
			'compress'                      => !empty($options['gzip']) ? Mysqldump::GZIP : Mysqldump::NONE,
			'include-tables'                => array_values($tables),
			'no-data'                       => false,
			'add-drop-table'                => !empty($options['droptable']),
			'single-transaction'            => true,
			'lock-tables'                   => true,
			'add-locks'                     => true,
			'extended-insert'               => true,
			'disable-foreign-keys-check'    => true,
			'skip-triggers'                 => false,
			'add-drop-trigger'              => true,
			'databases'                     => false,
			'add-drop-database'             => false,
			'hex-blob'                      => true,
			'reset-auto-increment'          => false,
		);

		try
		{
			$dump = new Mysqldump('mysql:host='.$params['server'].';port='.$params['port'].';dbname='.$params['database'], $params['user'], $params['password'], $dumpSettings);
			$dump->start($file);
		}
		catch(Exception $e)
		{
			throw new RuntimeException('mysqldump-php error: '.$e->getMessage(), 0, $e);
		}

		return $file;
	}

	/**
	 * @param string $database
	 * @return string the name as a backtick-quoted identifier
	 */
	private function quoteDatabase($database)
	{
		return '`'.str_replace('`', '``', $database).'`';
	}
}
