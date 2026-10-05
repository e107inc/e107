<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Definition;

use e107\Database\Exception\UnsupportedException;
use e107\Database\SqlLexer;
use InvalidArgumentException;

require_once(__DIR__.'/MysqlLiteral.php');
require_once(__DIR__.'/TableDefinition.php');

if(!class_exists(SqlLexer::class, false))
{
	require_once(dirname(dirname(__DIR__)).'/SqlLexer.php');
}

/**
 * Reads the rows of a MySQL INSERT, as e107's SQL files and MySQL dumps write them, into values to bind, each
 * literal read by {@see MysqlLiteral}.
 *
 * <code>
 * $insert = (new MysqlInsertReader())->read("INSERT INTO t VALUES ('it\'s', 1), ('b', NULL)");
 * // array('table' => 't', 'columns' => array(), 'rows' => array(array("it's", '1'), array('b', null)), 'ignore' => false)
 * </code>
 */
final class MysqlInsertReader
{
	/** @var SqlLexer */
	private $lexer;

	/** @var MysqlLiteral */
	private $literal;

	public function __construct()
	{
		$this->lexer = SqlLexer::mysql();
		$this->literal = new MysqlLiteral();
	}

	/**
	 * Split a script of statements and read every INSERT among them; any other statement is skipped.
	 *
	 * @param string $sql
	 * @param TableDefinition[] $tables as {@see MysqlInsertReader::read()} takes them
	 * @return array[] one entry per INSERT, as {@see MysqlInsertReader::read()} returns it
	 * @throws InvalidArgumentException|UnsupportedException on an INSERT that cannot be read
	 */
	public function readAll($sql, array $tables = array())
	{
		$inserts = array();

		foreach($this->lexer->splitStatements($sql) as $statement)
		{
			$tokens = $this->lexer->significantTokens($statement);

			if(!empty($tokens) && strtoupper($tokens[0]['text']) === 'INSERT')
			{
				$inserts[] = $this->readTokens($tokens, $statement, $tables);
			}
		}

		return $inserts;
	}

	/**
	 * Read one INSERT [IGNORE] INTO name [(columns)] VALUES (...), (...) statement.
	 *
	 * @param string $statement
	 * @param TableDefinition[] $tables tables by the name an INSERT gives them; a hexadecimal or bit literal is read only
	 *                                  for a column of one of them
	 * @return array array('table' => string, 'columns' => string[] (empty when the statement names none),
	 *               'rows' => array[] (each a list of values), 'ignore' => bool)
	 * @throws InvalidArgumentException when the statement is not such an INSERT
	 * @throws UnsupportedException on a hexadecimal or bit literal for a column of no known table
	 */
	public function read($statement, array $tables = array())
	{
		return $this->readTokens($this->lexer->significantTokens($statement), $statement, $tables);
	}

	/**
	 * The rows of an INSERT keyed by column: the columns the INSERT names, or else the table's in declaration order.
	 *
	 * @param array $insert as {@see MysqlInsertReader::read()} returns it
	 * @param string[] $declaredColumns the table's columns in declaration order
	 * @return array[]|false one column => value array per row; false when a row has not one value for each column
	 */
	public function rowsByColumn(array $insert, array $declaredColumns)
	{
		$names = empty($insert['columns']) ? array_values($declaredColumns) : $insert['columns'];
		$rows = array();

		foreach($insert['rows'] as $row)
		{
			if(count($row) !== count($names))
			{
				return false;
			}

			$rows[] = array_combine($names, $row);
		}

		return $rows;
	}

	/**
	 * @param array[] $tokens significant tokens of the statement
	 * @param string $statement for messages
	 * @param TableDefinition[] $tables by name
	 * @return array
	 */
	private function readTokens(array $tokens, $statement, array $tables)
	{
		$i = 0;
		$this->expect($tokens, $i, 'INSERT', $statement);
		$ignore = false;

		if($this->isWord($tokens, $i, 'IGNORE'))
		{
			$ignore = true;
			$i++;
		}

		$this->expect($tokens, $i, 'INTO', $statement);
		$table = $this->name($tokens, $i, $statement);
		$columns = array();

		if($this->isSymbol($tokens, $i, '('))
		{
			$i++;
			$columns[] = $this->name($tokens, $i, $statement);

			while($this->isSymbol($tokens, $i, ','))
			{
				$i++;
				$columns[] = $this->name($tokens, $i, $statement);
			}

			$this->expect($tokens, $i, ')', $statement);
		}

		if(!$this->isWord($tokens, $i, 'VALUES') && !$this->isWord($tokens, $i, 'VALUE'))
		{
			throw new InvalidArgumentException('Expected VALUES in: '.self::excerpt($statement));
		}

		$i++;
		$rows = array();
		$targets = $this->targetColumns(isset($tables[$table]) ? $tables[$table] : null, $columns);

		while(true)
		{
			$this->expect($tokens, $i, '(', $statement);
			$row = array($this->value($tokens, $i, $targets, 0, $statement));

			while($this->isSymbol($tokens, $i, ','))
			{
				$i++;
				$row[] = $this->value($tokens, $i, $targets, count($row), $statement);
			}

			$this->expect($tokens, $i, ')', $statement);

			if(!empty($columns) && count($row) !== count($columns))
			{
				throw new InvalidArgumentException('A row has '.count($row).' values for '.count($columns).' columns in: '.self::excerpt($statement));
			}

			$rows[] = $row;

			if(!$this->isSymbol($tokens, $i, ','))
			{
				break;
			}

			$i++;
		}

		if(isset($tokens[$i]))
		{
			throw new InvalidArgumentException('Unexpected "'.$tokens[$i]['text'].'" after the rows of: '.self::excerpt($statement));
		}

		return array('table' => $table, 'columns' => $columns, 'rows' => $rows, 'ignore' => $ignore);
	}

	/**
	 * @param TableDefinition|null $table
	 * @param string[] $columns the columns the INSERT names; none for every column of the table
	 * @return array the column each value of a row goes to, by position; null where it is not known
	 */
	private function targetColumns($table, array $columns)
	{
		if($table === null)
		{
			return array();
		}

		if(empty($columns))
		{
			return array_values($table->getColumns());
		}

		return array_map(array($table, 'getColumn'), $columns);
	}

	/**
	 * @param array[] $tokens
	 * @param int $i position; moved past the value
	 * @param array $targets from {@see MysqlInsertReader::targetColumns()}
	 * @param int $n position of the value in its row
	 * @param string $statement for messages
	 * @return string|null
	 */
	private function value(array $tokens, &$i, array $targets, $n, $statement)
	{
		$literal = $this->literal->read($tokens, $i, isset($targets[$n]) ? $targets[$n] : null);

		if($literal === null)
		{
			throw new InvalidArgumentException(isset($tokens[$i]) ? 'Cannot read the value '.$tokens[$i]['text'].' in: '.self::excerpt($statement) : 'The statement ends inside a row: '.self::excerpt($statement));
		}

		return $literal[1];
	}

	/**
	 * @param array[] $tokens
	 * @param int $i position; moved past the name
	 * @param string $statement
	 * @return string
	 */
	private function name(array $tokens, &$i, $statement)
	{
		if(!isset($tokens[$i]) || ($tokens[$i]['type'] !== SqlLexer::T_QUOTED_IDENTIFIER && $tokens[$i]['type'] !== SqlLexer::T_WORD))
		{
			throw new InvalidArgumentException('Expected a name in: '.self::excerpt($statement));
		}

		return $tokens[$i++]['value'];
	}

	/**
	 * @param array[] $tokens
	 * @param int $i position; moved past the token
	 * @param string $text a keyword or symbol
	 * @param string $statement
	 * @return void
	 */
	private function expect(array $tokens, &$i, $text, $statement)
	{
		if(!isset($tokens[$i]) || strtoupper($tokens[$i]['text']) !== $text)
		{
			throw new InvalidArgumentException('Expected '.$text.' in: '.self::excerpt($statement));
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

	/**
	 * @param string $statement
	 * @return string
	 */
	private static function excerpt($statement)
	{
		return (strlen($statement) > 200) ? substr($statement, 0, 200).'...' : $statement;
	}
}
