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
use e107\Database\Platform\SqlitePlatform;
use e107\Database\Result\BufferedResult;
use e107\Database\Schema\SqliteSchemaManager;
use e107\Shims\PdoSqlite;
use PDO;
use PDOException;
use RuntimeException;

require_once(__DIR__.'/AbstractPdoDriver.php');

/**
 * SQLite through pdo_sqlite: the 'db' of e107_config.php's 'database' block is the path of a database file that must
 * exist, absolute or relative to the e107 root, and its folder must be writable for the -wal and -shm files.
 */
class SqliteDriver extends AbstractPdoDriver
{
	/** @var string the oldest SQLite library e107 installs on */
	const MINIMUM_VERSION = '3.35.0';

	/** @var int SQLite's result code for a file that is not a database */
	const SQLITE_NOTADB = 26;

	/** @var int milliseconds a session waits for another one's write lock */
	const BUSY_TIMEOUT = 10000;

	/** @var string[] the files SQLite keeps beside a database, by suffix */
	const COMPANION_SUFFIXES = array('-wal', '-shm', '-journal');

	/** @var string|null the linked library version, read once */
	private static $libraryVersion = null;

	/** @var string the folder a relative database path lies under; '' when none was given */
	private $root;

	/** @var bool whether connections get the MySQL compatibility function pack */
	private $mysqlCompat;

	/**
	 * @param array $settings 'root': the e107 root, which a relative database path lies under, refused without it;
	 *                        'mysql_compat': whether to register the MySQL compatibility pack, true unless given false
	 */
	public function __construct(array $settings = array())
	{
		$this->root = isset($settings['root']) ? (string) $settings['root'] : '';
		$this->mysqlCompat = !isset($settings['mysql_compat']) || (bool) $settings['mysql_compat'];
	}

	/**
	 * @inheritDoc
	 */
	public function getName()
	{
		return 'sqlite';
	}

	/**
	 * @inheritDoc
	 */
	public function getLabel()
	{
		return 'SQLite';
	}

	/**
	 * @inheritDoc
	 */
	public function isAvailable()
	{
		return extension_loaded('pdo_sqlite');
	}

	/**
	 * @inheritDoc
	 */
	public function requiresServer()
	{
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getMinimumServerVersion()
	{
		return self::MINIMUM_VERSION;
	}

	/**
	 * @inheritDoc
	 */
	public function createPlatform($serverVersion = null)
	{
		if(!class_exists(SqlitePlatform::class, false))
		{
			require_once(dirname(__DIR__).'/Platform/SqlitePlatform.php');
		}

		return new SqlitePlatform(($serverVersion === null || $serverVersion === '') ? self::libraryVersion() : $serverVersion);
	}

	/**
	 * @inheritDoc
	 */
	public function createSchemaManager(ConnectionInterface $connection)
	{
		if(!class_exists(SqliteSchemaManager::class, false))
		{
			require_once(dirname(__DIR__).'/Schema/SqliteSchemaManager.php');
		}

		return new SqliteSchemaManager($connection);
	}

	/**
	 * SQLite has no SQL mode.
	 *
	 * @inheritDoc
	 */
	public function getSessionMode(ConnectionInterface $connection)
	{
		return '';
	}

	/**
	 * SQLite stores a value as given rather than cut it to fit, so there is no mode to enter.
	 *
	 * @inheritDoc
	 */
	public function enterStrictMode(ConnectionInterface $connection)
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function leaveStrictMode(ConnectionInterface $connection, $restore)
	{
	}

	/**
	 * Nothing to connect to until a database file is named.
	 *
	 * @inheritDoc
	 */
	public function connect(array $params)
	{
		return null;
	}

	/**
	 * Opens the database file.
	 *
	 * @inheritDoc
	 */
	public function selectDatabase($pdo, $database, array $params)
	{
		$path = $this->existingFile($database);
		$pdo = $this->open($path);

		if(!$this->readsAsDatabase($pdo))
		{
			throw new PDOException('SQLite database file "'.$path.'" is not an SQLite database.');
		}

		return $pdo;
	}

	/**
	 * Attaches the second database file under an alias derived from its path.
	 *
	 * @inheritDoc
	 */
	public function qualifyPrefix($pdo, $database, $prefix)
	{
		if($pdo === null)
		{
			throw new PDOException('Open a database before attaching another.');
		}

		$path = $this->databasePath($database);
		$alias = 'e107db_'.substr(md5($path), 0, 8);

		foreach($pdo->query('PRAGMA database_list')->fetchAll(PDO::FETCH_ASSOC) as $attached)
		{
			if($attached['name'] === $alias)
			{
				return $alias.'.'.$prefix;
			}
		}

		$pdo->exec('ATTACH DATABASE '.$pdo->quote($path).' AS '.$alias);

		return $alias.'.'.$prefix;
	}

	/**
	 * Every value is fetched as a string, as pdo_mysql's are; the function packs are registered.
	 *
	 * @inheritDoc
	 */
	public function configure($pdo)
	{
		$pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);

		if(!class_exists(SqliteFunctions::class, false))
		{
			require_once(__DIR__.'/SqliteFunctions.php');
		}

		SqliteFunctions::register($pdo, $this->getServerVersion($pdo), (bool) $this->mysqlCompat);
	}

	/**
	 * SQLite stores text as UTF-8 whatever is asked.
	 *
	 * @inheritDoc
	 */
	public function getCharsetStatement($charset)
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getSessionStatements()
	{
		return array(
			'PRAGMA busy_timeout = '.self::BUSY_TIMEOUT,
			'PRAGMA journal_mode = WAL',
			'PRAGMA synchronous = NORMAL',
		);
	}

	/**
	 * SQLite keeps no count of rows a LIMIT left out.
	 *
	 * @inheritDoc
	 */
	public function getFoundRowsStatement()
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getBeginTransactionStatements()
	{
		return array_merge($this->outsideATransaction(), array('BEGIN IMMEDIATE'));
	}

	/**
	 * @inheritDoc
	 */
	public function isTransactionOpen(ConnectionInterface $connection)
	{
		foreach($this->outsideATransaction() as $statement)
		{
			if($connection->execute($statement) === false)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return string[] statements that succeed only where no transaction is open, and leave none open
	 */
	private function outsideATransaction()
	{
		return array('BEGIN', 'ROLLBACK');
	}

	/**
	 * @inheritDoc
	 */
	public function getServerVersion($pdo)
	{
		return ($pdo === null) ? self::libraryVersion() : (string) $pdo->query('SELECT sqlite_version()')->fetchColumn();
	}

	/**
	 * @inheritDoc
	 */
	public function wrapResult($statement)
	{
		if(!$statement instanceof \PDOStatement)
		{
			return $statement;
		}

		if(!class_exists(BufferedResult::class, false))
		{
			require_once(dirname(__DIR__).'/Result/BufferedResult.php');
		}

		return BufferedResult::fromStatement($statement);
	}

	/**
	 * Maps SQLite's conditions onto e107's error codes where one fits.
	 *
	 * @inheritDoc
	 */
	public function errorNumber($exception)
	{
		$message = $exception->getMessage();

		$conditions = array(
			'UNIQUE constraint failed'   => ConnectionInterface::ERROR_DUPLICATE_KEY,
			'NOT NULL constraint failed' => ConnectionInterface::ERROR_NOT_NULL,
			'no such table'              => ConnectionInterface::ERROR_NO_SUCH_TABLE,
			'no such column'             => ConnectionInterface::ERROR_NO_SUCH_COLUMN,
			'duplicate column name'      => ConnectionInterface::ERROR_DUPLICATE_COLUMN,
			'syntax error'               => ConnectionInterface::ERROR_SYNTAX,
			'database file "'            => ConnectionInterface::ERROR_UNKNOWN_DATABASE,
			'unable to open database'    => ConnectionInterface::ERROR_UNKNOWN_DATABASE,
		);

		foreach($conditions as $text => $number)
		{
			if(strpos($message, $text) !== false)
			{
				return $number;
			}
		}

		if(preg_match('/\b(table|index) \S+ already exists/', $message, $match))
		{
			return ($match[1] === 'table') ? ConnectionInterface::ERROR_TABLE_EXISTS : ConnectionInterface::ERROR_DUPLICATE_KEY_NAME;
		}

		return parent::errorNumber($exception);
	}

	/**
	 * The database is the file: it is created empty, and refused where one is already there or its folder is not.
	 *
	 * @inheritDoc
	 */
	public function createDatabase(ConnectionInterface $connection, $database)
	{
		$path = $this->resolvePath($database);

		if(file_exists($path))
		{
			throw new RuntimeException('The database file "'.$database.'" already exists.');
		}

		if(!is_dir(dirname($path)) || !is_writable(dirname($path)))
		{
			throw new RuntimeException('The folder for the database file "'.$database.'" does not exist or cannot be written to.');
		}

		if(!@touch($path))
		{
			throw new RuntimeException('The database file "'.$database.'" could not be created.');
		}
	}

	/**
	 * An SQLite file needs nothing done to it; it has to be there.
	 *
	 * @inheritDoc
	 */
	public function adoptDatabase(ConnectionInterface $connection, $database)
	{
		$path = $this->resolvePath($database);

		if(!is_file($path))
		{
			throw new RuntimeException('The database file "'.$database.'" does not exist.');
		}

		if(!$this->isDatabaseFile($path))
		{
			throw new RuntimeException('The file "'.$database.'" is not an SQLite database.');
		}
	}

	/**
	 * The file goes, with the write-ahead log and shared-memory files beside it; a file that is not an SQLite
	 * database is refused and left alone.
	 *
	 * @inheritDoc
	 */
	public function dropDatabase(ConnectionInterface $connection, $database)
	{
		$path = $this->resolvePath($database);

		if(file_exists($path) && !$this->isDatabaseFile($path))
		{
			throw new RuntimeException('The file "'.$database.'" is not an SQLite database, so it is not removed.');
		}

		$connection->close();

		foreach(array_merge(array(''), self::COMPANION_SUFFIXES) as $suffix)
		{
			if(file_exists($path.$suffix) && !@unlink($path.$suffix))
			{
				throw new RuntimeException('The database file "'.$database.$suffix.'" could not be removed.');
			}
		}
	}

	/**
	 * Writes an SQL script that recreates the tables in SQLite: schema from sqlite_master, rows as literals SQLite's
	 * quote() writes, so text and binary values survive exactly.
	 *
	 * @inheritDoc
	 */
	public function backup(array $params, array $tables, $file, array $options)
	{
		$pdo = $this->selectDatabase(null, $params['database'], $params);
		$version = $this->getServerVersion($pdo);
		$pdo->beginTransaction();

		try
		{
			$this->writeBackup($pdo, $this->backupPlan($pdo, $this->createPlatform($version), $tables), $version, $file, $options);
		}
		finally
		{
			$pdo->rollBack();
		}

		return $file;
	}

	/**
	 * Every name the script will hold, quoted, and every table's counter, all read before the file is opened.
	 *
	 * @param PDO $pdo inside a transaction, so every table is read from one snapshot
	 * @param SqlitePlatform $platform
	 * @param string[] $tables
	 * @return array[] table => array('quoted' => name, 'columns' => literal expressions, 'sequence' => int|null)
	 * @throws RuntimeException for a table that is not there, or a name e107 would never have given one
	 */
	private function backupPlan($pdo, SqlitePlatform $platform, array $tables)
	{
		$exists = $pdo->prepare("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = :t");
		$exists->execute(array('t' => 'sqlite_sequence'));
		$sequences = $exists->fetchColumn() ? $pdo->query('SELECT name, seq FROM sqlite_sequence')->fetchAll(PDO::FETCH_KEY_PAIR) : array();
		$plan = array();

		foreach($tables as $table)
		{
			$exists->execute(array('t' => $table));

			if(!$exists->fetchColumn())
			{
				throw new RuntimeException('Table "'.$table.'" not found in the database.');
			}

			$quoted = $this->quoteName($platform, $table);
			$plan[$table] = array(
				'quoted'   => $quoted,
				'columns'  => $this->literalColumns($pdo, $platform, $quoted),
				'sequence' => isset($sequences[$table]) ? (int) $sequences[$table] : null,
			);
		}

		return $plan;
	}

	/**
	 * @param PDO $pdo inside the transaction {@see SqliteDriver::backupPlan()} read from
	 * @param array[] $plan what {@see SqliteDriver::backupPlan()} returned
	 * @param string $version
	 * @param string $file removed again when the script cannot be written whole
	 * @param array $options
	 * @return void
	 */
	private function writeBackup($pdo, array $plan, $version, $file, array $options)
	{
		$gzip = !empty($options['gzip']);
		$out = $gzip ? gzopen($file, 'wb9') : fopen($file, 'wb');

		if($out === false)
		{
			throw new RuntimeException('Cannot write the backup file "'.$file.'".');
		}

		$write = function($text) use ($out, $gzip, $file)
		{
			if(($gzip ? gzwrite($out, $text) : fwrite($out, $text)) !== strlen($text))
			{
				throw new RuntimeException('Cannot write the backup file "'.$file.'".');
			}
		};
		$written = false;

		try
		{
			$write('-- e107 SQLite backup, '.date('r')."\n-- SQLite ".$version."\n\nBEGIN TRANSACTION;\n");
			$schema = $pdo->prepare("SELECT type, sql FROM sqlite_master WHERE tbl_name = :t AND sql IS NOT NULL ORDER BY (type = 'table') DESC, name");
			$afterRows = array();

			foreach($plan as $table => $entry)
			{
				$schema->execute(array('t' => $table));
				$write("\n");

				if(!empty($options['droptable']))
				{
					$write('DROP TABLE IF EXISTS '.$entry['quoted'].";\n");
				}

				foreach($schema->fetchAll(PDO::FETCH_ASSOC) as $definition)
				{
					if($definition['type'] === 'table')
					{
						$write($definition['sql'].";\n");
						continue;
					}

					$afterRows[] = $definition['sql'].";\n";
				}

				$rows = $pdo->query('SELECT '.implode(" || ',' || ", $entry['columns']).' FROM '.$entry['quoted']);

				while(($values = $rows->fetchColumn()) !== false)
				{
					$write('INSERT INTO '.$entry['quoted'].' VALUES ('.$values.");\n");
				}

				if($entry['sequence'] !== null)
				{
					$name = $pdo->quote($table);
					$write('DELETE FROM sqlite_sequence WHERE name = '.$name.";\nINSERT INTO sqlite_sequence (name, seq) VALUES (".$name.', '.$entry['sequence'].");\n");
				}
			}

			$write("\n".implode('', $afterRows)."\nCOMMIT;\n");
			$written = true;
		}
		finally
		{
			$gzip ? gzclose($out) : fclose($out);

			if(!$written)
			{
				@unlink($file);
			}
		}
	}

	/**
	 * @param PDO $pdo
	 * @param SqlitePlatform $platform
	 * @param string $quotedTable
	 * @return string[] an SQL literal of each column's value; text holding a NUL byte goes as a cast blob, since
	 *                  quote() stops at the NUL
	 */
	private function literalColumns($pdo, SqlitePlatform $platform, $quotedTable)
	{
		$columns = array();

		foreach($pdo->query('PRAGMA table_info('.$quotedTable.')')->fetchAll(PDO::FETCH_ASSOC) as $column)
		{
			$quoted = $this->quoteName($platform, $column['name']);
			$columns[] = "CASE WHEN typeof(".$quoted.") = 'text' AND instr(CAST(".$quoted." AS BLOB), X'00') > 0"
				." THEN 'CAST(' || quote(CAST(".$quoted." AS BLOB)) || ' AS TEXT)' ELSE quote(".$quoted.') END';
		}

		return $columns;
	}

	/**
	 * @param SqlitePlatform $platform
	 * @param string $name
	 * @return string
	 * @throws RuntimeException for a name e107 would never have given a table or column
	 */
	private function quoteName(SqlitePlatform $platform, $name)
	{
		$quoted = $platform->quoteIdentifier($name);

		if($quoted === false)
		{
			throw new RuntimeException('Cannot back up "'.$name.'": not a table or column name.');
		}

		return $quoted;
	}

	/**
	 * @return string the version of the SQLite library pdo_sqlite is linked against; '0' when it is not loaded
	 */
	private static function libraryVersion()
	{
		if(self::$libraryVersion === null)
		{
			self::$libraryVersion = '0';

			if(extension_loaded('pdo_sqlite'))
			{
				$pdo = new PDO('sqlite::memory:');
				self::$libraryVersion = (string) $pdo->query('SELECT sqlite_version()')->fetchColumn();
			}
		}

		return self::$libraryVersion;
	}

	/**
	 * @param string $database
	 * @return string the path as given when absolute, otherwise under the e107 root
	 * @throws PDOException for a relative path when no root was given
	 */
	private function resolvePath($database)
	{
		$database = (string) $database;

		if($database === '' || $database[0] === '/' || $database[0] === '\\' || preg_match('#^[A-Za-z]:[\\\\/]#', $database))
		{
			return $database;
		}

		if((string) $this->root === '')
		{
			throw new PDOException('SQLite database file "'.$database.'" is relative to the e107 root, which this driver was not given.');
		}

		return rtrim($this->root, '/\\').DIRECTORY_SEPARATOR.$database;
	}

	/**
	 * @param string $database
	 * @return string the path of the existing file it names
	 * @throws PDOException
	 */
	private function existingFile($database)
	{
		$path = $this->resolvePath($database);

		if(!is_file($path))
		{
			throw new PDOException('SQLite database file "'.$path.'" does not exist.');
		}

		return $path;
	}

	/**
	 * @param string $database
	 * @return string the path of the existing SQLite database file it names
	 * @throws PDOException
	 */
	private function databasePath($database)
	{
		$path = $this->existingFile($database);

		if(!$this->isDatabaseFile($path))
		{
			throw new PDOException('SQLite database file "'.$path.'" is not an SQLite database.');
		}

		return $path;
	}

	/**
	 * Asks SQLite, since opening and closing the file outside SQLite would cancel the locks this process holds on it.
	 *
	 * @param string $path
	 * @return bool whether the file is an SQLite database, or the empty file {@see SqliteDriver::createDatabase()} makes
	 * @throws PDOException when SQLite cannot say
	 */
	private function isDatabaseFile($path)
	{
		if(!is_file($path))
		{
			return false;
		}

		return filesize($path) === 0 || $this->readsAsDatabase($this->open($path));
	}

	/**
	 * @param PDO $pdo
	 * @return bool whether the file the handle opened is an SQLite database
	 * @throws PDOException when SQLite cannot say
	 */
	private function readsAsDatabase($pdo)
	{
		try
		{
			$pdo->query('PRAGMA schema_version');
		}
		catch(PDOException $e)
		{
			if(isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === self::SQLITE_NOTADB)
			{
				return false;
			}

			throw $e;
		}

		return true;
	}

	/**
	 * @param string $path
	 * @return PDO
	 */
	private function open($path)
	{
		if(!class_exists(PdoSqlite::class, false))
		{
			require_once(dirname(dirname(__DIR__)).'/Shims/PdoSqlite.php');
		}

		return PdoSqlite::connect('sqlite:'.$path, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	}
}
