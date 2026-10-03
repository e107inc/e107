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
use e107\Database\Schema\Definition\TableDefinition;
use e107\Database\SqlLexer;

$e107Handlers = dirname(dirname(__DIR__)).'/e107_handlers/Database';
require_once($e107Handlers.'/Exception/UnsupportedException.php');
require_once($e107Handlers.'/SqlLexer.php');
require_once($e107Handlers.'/Schema/Definition/MysqlDdlParser.php');
require_once($e107Handlers.'/Platform/SqlitePlatform.php');
require_once($e107Handlers.'/Driver/SqliteDriver.php');
unset($e107Handlers);

/**
 * Builds the sqlite lane's database from the MySQL dump the MySQL lanes load, so every lane starts from the same site.
 *
 * No SQL text is translated. Each CREATE TABLE is read by e107's schema DSL parser and rendered by the SQLite dialect,
 * the way a declared table is created on a SQLite site, and each INSERT is read as MySQL literals whose values are
 * bound. A table's AUTO_INCREMENT option carries over as its next id. The other statements a dump holds (DROP TABLE,
 * LOCK TABLES, SET) only prepare a MySQL server for the load and are skipped; anything else stops the build, so a dump
 * the builder does not understand cannot leave half a site behind.
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

	/** @var SqlitePlatform */
	private $platform;

	/** @var TableDefinition[] tables created so far, by name */
	private $tables = array();

	/** @var PDOStatement[] prepared row inserts, by table and column list */
	private $inserts = array();

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
				$this->insertRows($tokens);
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
	 * Insert the rows of an INSERT [IGNORE] INTO name [(columns)] VALUES (...), (...) statement.
	 *
	 * @param array[] $tokens significant tokens of the statement
	 * @return void
	 */
	private function insertRows(array $tokens)
	{
		$i = 1;
		$modifier = '';

		if($this->isWord($tokens, $i, 'IGNORE'))
		{
			$modifier = 'IGNORE';
			$i++;
		}

		$this->expect($tokens, $i, 'INTO');
		$table = $this->name($tokens, $i);

		if(!isset($this->tables[$table]))
		{
			throw new RuntimeException('The dump inserts into '.$table.' before creating it.');
		}

		$columns = array();

		if($this->isSymbol($tokens, $i, '('))
		{
			$i++;
			$columns[] = $this->name($tokens, $i);

			while($this->isSymbol($tokens, $i, ','))
			{
				$i++;
				$columns[] = $this->name($tokens, $i);
			}

			$this->expect($tokens, $i, ')');
		}
		else
		{
			foreach($this->tables[$table]->getColumns() as $column)
			{
				$columns[] = $column->getName();
			}
		}

		if(!$this->isWord($tokens, $i, 'VALUES') && !$this->isWord($tokens, $i, 'VALUE'))
		{
			throw new RuntimeException('Expected VALUES in the dump\'s insert into '.$table.'.');
		}

		$insert = $this->prepareInsert($table, $columns, $modifier);
		$i++;

		while(true)
		{
			$this->expect($tokens, $i, '(');
			$row = array($this->literal($tokens, $i));

			while($this->isSymbol($tokens, $i, ','))
			{
				$i++;
				$row[] = $this->literal($tokens, $i);
			}

			$this->expect($tokens, $i, ')');

			if(count($row) !== count($columns))
			{
				throw new RuntimeException('A row of the dump\'s insert into '.$table.' has '.count($row).' values for '.count($columns).' columns.');
			}

			foreach($row as $n => $value)
			{
				$insert->bindValue($n + 1, $value, ($value === null) ? PDO::PARAM_NULL : PDO::PARAM_STR);
			}

			$insert->execute();

			if(!$this->isSymbol($tokens, $i, ','))
			{
				break;
			}

			$i++;
		}

		if(isset($tokens[$i]))
		{
			throw new RuntimeException('Unexpected "'.$tokens[$i]['text'].'" after the rows of the dump\'s insert into '.$table.'.');
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

		if(!isset($this->inserts[$key]))
		{
			$quoted = array();

			foreach($columns as $column)
			{
				$quoted[] = $this->platform->quoteIdentifier($column);
			}

			$tuple = '('.implode(', ', array_fill(0, count($columns), '?')).')';
			$this->inserts[$key] = $this->pdo->prepare($this->platform->compileInsert($this->platform->quoteIdentifier($table), $quoted, array($tuple), $modifier));
		}

		return $this->inserts[$key];
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

	/**
	 * Read one MySQL literal: a string, a number, NULL, TRUE/FALSE, a signed number or a _charset'string'.
	 *
	 * @param array[] $tokens
	 * @param int $i position; moved past the literal
	 * @return string|null
	 */
	private function literal(array $tokens, &$i)
	{
		if(!isset($tokens[$i]))
		{
			throw new RuntimeException('The dump ends inside a row.');
		}

		$token = $tokens[$i++];

		switch($token['type'])
		{
			case SqlLexer::T_STRING:
				return $token['value'];

			case SqlLexer::T_NUMBER:
				return $this->number($token['text']);

			case SqlLexer::T_WORD:
				$word = strtoupper($token['text']);

				if($word === 'NULL')
				{
					return null;
				}

				if($word === 'TRUE' || $word === 'FALSE')
				{
					return ($word === 'TRUE') ? '1' : '0';
				}

				if($word[0] === '_' && isset($tokens[$i]) && $tokens[$i]['type'] === SqlLexer::T_STRING)
				{
					return $tokens[$i++]['value']; // a character set introducer, e.g. _binary'...'
				}
				break;

			case SqlLexer::T_SYMBOL:
				if(($token['text'] === '-' || $token['text'] === '+') && isset($tokens[$i]) && $tokens[$i]['type'] === SqlLexer::T_NUMBER)
				{
					$number = $this->number($tokens[$i++]['text']);

					return ($token['text'] === '-') ? '-'.$number : $number;
				}
				break;
		}

		throw new RuntimeException('The SQLite fixture cannot read the value '.$token['text'].' in the dump.');
	}

	/**
	 * @param string $text a numeric literal: decimal, 0x..., X'...' or b'...'
	 * @return string the number as text, or the bytes a hexadecimal or bit literal spells
	 */
	private function number($text)
	{
		if(stripos($text, '0x') === 0)
		{
			return (string) hex2bin((strlen($text) % 2) ? '0'.substr($text, 2) : substr($text, 2));
		}

		if(stripos($text, "x'") === 0)
		{
			return (string) hex2bin(substr($text, 2, -1));
		}

		if(stripos($text, "b'") === 0)
		{
			$bits = substr($text, 2, -1);
			$bytes = '';

			foreach(str_split(str_pad($bits, (int) ceil(strlen($bits) / 8) * 8, '0', STR_PAD_LEFT), 8) as $byte)
			{
				$bytes .= chr(bindec($byte));
			}

			return $bytes;
		}

		return $text;
	}

	/**
	 * @param array[] $tokens
	 * @param int $i position; moved past the name
	 * @return string
	 */
	private function name(array $tokens, &$i)
	{
		if(!isset($tokens[$i]) || ($tokens[$i]['type'] !== SqlLexer::T_QUOTED_IDENTIFIER && $tokens[$i]['type'] !== SqlLexer::T_WORD))
		{
			throw new RuntimeException('Expected a name in the dump, found '.(isset($tokens[$i]) ? $tokens[$i]['text'] : 'its end').'.');
		}

		return $tokens[$i++]['value'];
	}

	/**
	 * @param array[] $tokens
	 * @param int $i position; moved past the token
	 * @param string $text a keyword or symbol
	 * @return void
	 */
	private function expect(array $tokens, &$i, $text)
	{
		if(!isset($tokens[$i]) || strtoupper($tokens[$i]['text']) !== $text)
		{
			throw new RuntimeException('Expected '.$text.' in the dump, found '.(isset($tokens[$i]) ? $tokens[$i]['text'] : 'its end').'.');
		}

		$i++;
	}

	/**
	 * @param array[] $tokens
	 * @param int $i
	 * @param string $word
	 * @return bool
	 */
	private function isWord(array $tokens, $i, $word)
	{
		return isset($tokens[$i]) && $tokens[$i]['type'] === SqlLexer::T_WORD && strtoupper($tokens[$i]['text']) === $word;
	}

	/**
	 * @param array[] $tokens
	 * @param int $i
	 * @param string $symbol
	 * @return bool
	 */
	private function isSymbol(array $tokens, $i, $symbol)
	{
		return isset($tokens[$i]) && $tokens[$i]['type'] === SqlLexer::T_SYMBOL && $tokens[$i]['text'] === $symbol;
	}
}
