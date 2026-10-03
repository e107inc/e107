<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\Driver\SqliteDriver;
use e107\Database\Platform\SqlitePlatform;
use e107\Database\Schema\Definition\MysqlDdlParser;
use e107\Database\Schema\Definition\MysqlInsertReader;
use e107\Database\Schema\Definition\TableDefinition;
use e107\Database\SqlLexer;

$e107Handlers = dirname(dirname(__DIR__)).'/e107_handlers/Database';
require_once($e107Handlers.'/Exception/UnsupportedException.php');
require_once($e107Handlers.'/SqlLexer.php');
require_once($e107Handlers.'/Schema/Definition/MysqlDdlParser.php');
require_once($e107Handlers.'/Schema/Definition/MysqlInsertReader.php');
require_once($e107Handlers.'/Platform/SqlitePlatform.php');
require_once($e107Handlers.'/Driver/SqliteDriver.php');
unset($e107Handlers);

/**
 * Builds the sqlite lane's database from the MySQL dump the MySQL lanes load; a statement it cannot load stops the build.
 *
 * <code>
 * SqliteFixture::build('tests/_data/e107_v2.3.0.sample.sql', 'tests/_output/e107.sqlite');
 * </code>
 */
final class SqliteFixture
{
	/** @var string[] first words of the statements a dump holds for a MySQL server alone */
	private static $skipped = array('DROP', 'LOCK', 'UNLOCK', 'SET');

	/** @var PDO */
	private $pdo;

	/** @var SqlLexer */
	private $lexer;

	/** @var MysqlDdlParser */
	private $parser;

	/** @var MysqlInsertReader */
	private $inserts;

	/** @var SqlitePlatform */
	private $platform;

	/** @var TableDefinition[] tables created so far, by name */
	private $tables = array();

	/** @var PDOStatement[] prepared row inserts, by table and column list */
	private $prepared = array();

	/**
	 * Replace everything the database file holds with the dump's tables and rows, in place and in one transaction; a file
	 * that is not a database is deleted and built anew.
	 *
	 * @param string $dumpFile MySQL dump
	 * @param string $databaseFile SQLite database file
	 * @return void
	 * @throws RuntimeException|PDOException on a statement the builder cannot load
	 */
	public static function build($dumpFile, $databaseFile)
	{
		$sql = @file_get_contents($dumpFile);

		if($sql === false)
		{
			throw new RuntimeException('Cannot read the dump '.$dumpFile.'.');
		}

		$pdo = self::open($databaseFile);

		try
		{
			$pdo->query('PRAGMA schema_version');
		}
		catch(PDOException $e)
		{
			if(!isset($e->errorInfo[1]) || (int) $e->errorInfo[1] !== SqliteDriver::SQLITE_NOTADB)
			{
				throw $e;
			}

			$pdo = null;

			foreach(array_merge(array(''), SqliteDriver::COMPANION_SUFFIXES) as $suffix)
			{
				if(is_file($databaseFile.$suffix))
				{
					unlink($databaseFile.$suffix);
				}
			}

			$pdo = self::open($databaseFile);
		}

		$fixture = new self($pdo);
		$pdo->beginTransaction();

		try
		{
			$fixture->clear();
			$fixture->load($sql);
			$pdo->commit();
		}
		catch(Exception $e)
		{
			$pdo->rollBack();
			throw $e;
		}

		// The web server of the acceptance lane writes to it as another user.
		@chmod($databaseFile, 0666);
	}

	/**
	 * @param string $databaseFile
	 * @return PDO
	 */
	private static function open($databaseFile)
	{
		$pdo = new PDO('sqlite:'.$databaseFile, null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		$pdo->exec('PRAGMA busy_timeout = '.SqliteDriver::BUSY_TIMEOUT);

		return $pdo;
	}

	/**
	 * @param PDO $pdo connection to the SQLite database to fill
	 */
	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
		$this->lexer = SqlLexer::mysql();
		$this->parser = new MysqlDdlParser();
		$this->inserts = new MysqlInsertReader();
		$this->platform = new SqlitePlatform($pdo->query('SELECT sqlite_version()')->fetchColumn());
	}

	/**
	 * Drop every table and view, and forget every table's next id.
	 *
	 * @return void
	 */
	public function clear()
	{
		$objects = $this->pdo->query("SELECT type, name FROM sqlite_master WHERE type IN ('table', 'view') AND name NOT LIKE 'sqlite\\_%' ESCAPE '\\'")->fetchAll(PDO::FETCH_NUM);

		foreach($objects as $object)
		{
			$this->pdo->exec('DROP '.strtoupper($object[0]).' '.$this->platform->quoteIdentifier($object[1]));
		}

		if($this->pdo->query("SELECT 1 FROM sqlite_master WHERE name = 'sqlite_sequence'")->fetchColumn())
		{
			$this->pdo->exec('DELETE FROM sqlite_sequence');
		}
	}

	/**
	 * Create the tables and insert the rows of a MySQL dump.
	 *
	 * @param string $sql
	 * @return void
	 * @throws RuntimeException|PDOException
	 */
	public function load($sql)
	{
		foreach($this->lexer->splitStatements($sql) as $statement)
		{
			$tokens = $this->lexer->significantTokens($statement);
			$verb = strtoupper($tokens[0]['text']);

			if($verb === 'CREATE')
			{
				$this->createTable($statement);
			}
			elseif($verb === 'INSERT')
			{
				$this->insertRows($this->inserts->read($statement, $this->tables));
			}
			elseif(!in_array($verb, self::$skipped, true))
			{
				throw new RuntimeException('The SQLite fixture cannot load this statement of the dump: '.substr($statement, 0, 200));
			}
		}

		$this->carryNextIds();
	}

	/**
	 * @param string $statement CREATE TABLE ...
	 * @return void
	 */
	private function createTable($statement)
	{
		$table = $this->parser->parseCreateTable($statement);

		foreach($this->platform->compileTableDefinition($table->getName(), $table) as $ddl)
		{
			$this->pdo->exec($ddl);
		}

		$this->tables[$table->getName()] = $table;
	}

	/**
	 * Insert the rows one INSERT statement of the dump holds.
	 *
	 * @param array $insert as {@see MysqlInsertReader::read()} returns it
	 * @return void
	 */
	private function insertRows(array $insert)
	{
		$table = $insert['table'];

		if(!isset($this->tables[$table]))
		{
			throw new RuntimeException('The dump inserts into '.$table.' before creating it.');
		}

		$rows = $this->inserts->rowsByColumn($insert, array_keys($this->tables[$table]->getColumns()));

		if($rows === false)
		{
			throw new RuntimeException('A row of the dump\'s insert into '.$table.' does not have one value for each column.');
		}

		foreach($rows as $row)
		{
			$statement = $this->prepareInsert($table, array_keys($row), $insert['ignore'] ? 'IGNORE' : '');

			foreach(array_values($row) as $n => $value)
			{
				$statement->bindValue($n + 1, $value, ($value === null) ? PDO::PARAM_NULL : PDO::PARAM_STR);
			}

			$statement->execute();
		}
	}

	/**
	 * @param string $table
	 * @param string[] $columns
	 * @param string $modifier '' or 'IGNORE'
	 * @return PDOStatement
	 */
	private function prepareInsert($table, array $columns, $modifier)
	{
		$key = $modifier.' '.$table.' '.implode(',', $columns);

		if(!isset($this->prepared[$key]))
		{
			$quoted = array();

			foreach($columns as $column)
			{
				$quoted[] = $this->platform->quoteIdentifier($column);
			}

			$tuple = '('.implode(', ', array_fill(0, count($columns), '?')).')';
			$this->prepared[$key] = $this->pdo->prepare($this->platform->compileInsert($this->platform->quoteIdentifier($table), $quoted, array($tuple), $modifier));
		}

		return $this->prepared[$key];
	}

	/**
	 * Give every auto-increment table the next id its AUTO_INCREMENT option names, where that is past its rows.
	 *
	 * @return void
	 */
	private function carryNextIds()
	{
		foreach($this->tables as $name => $table)
		{
			$next = (int) $table->getOption('auto_increment');

			if($next < 2 || $table->getAutoIncrementColumn() === null)
			{
				continue;
			}

			$update = $this->pdo->prepare('UPDATE sqlite_sequence SET seq = :seq WHERE name = :name AND seq < :seq');
			$update->execute(array('seq' => $next - 1, 'name' => $name));

			$known = $this->pdo->prepare('SELECT 1 FROM sqlite_sequence WHERE name = :name');
			$known->execute(array('name' => $name));

			if(!$known->fetchColumn())
			{
				$insert = $this->pdo->prepare('INSERT INTO sqlite_sequence (name, seq) VALUES (:name, :seq)');
				$insert->execute(array('name' => $name, 'seq' => $next - 1));
			}
		}
	}
}
