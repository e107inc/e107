<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema;

use e107\Database\ConnectionInterface;
use e107\Database\Exception\QueryException;
use e107\Database\Platform\SqlitePlatform;
use e107\Database\Schema\Definition\ColumnDefinition;
use e107\Database\Schema\Definition\IndexDefinition;
use e107\Database\Schema\Definition\TableDefinition;
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Introspect\SqliteSchemaReader;
use e107\Database\Schema\Introspect\TableSchema;
use e107\Database\SqlLexer;
use InvalidArgumentException;

require_once(__DIR__.'/SchemaManagerInterface.php');
require_once(__DIR__.'/Introspect/SqliteSchemaReader.php');
require_once(__DIR__.'/Definition/TableDefinition.php');

/**
 * Schema work on SQLite through sqlite_master and the schema pragmas; a change its ALTER TABLE cannot make rebuilds
 * the table.
 */
final class SqliteSchemaManager implements SchemaManagerInterface
{
	/** @var ConnectionInterface */
	private $db;

	/** @var SqliteSchemaReader|null */
	private $reader = null;

	/**
	 * @param ConnectionInterface $db the connection every statement runs on
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * @return SqliteSchemaReader
	 */
	public function getReader()
	{
		if($this->reader === null)
		{
			$this->reader = new SqliteSchemaReader($this->db);
		}

		return $this->reader;
	}

	/**
	 * @inheritDoc
	 */
	public function listTableNames($prefix)
	{
		list($schema, $bare) = SqliteSchemaReader::split($prefix);

		$sql = 'SELECT name FROM '.SqliteSchemaReader::master($schema)." WHERE type = 'table' AND name LIKE :pattern".$this->platform()->getLikeEscapeClause().' ORDER BY name';

		if($this->db->execute($sql, array('pattern' => $this->platform()->quoteLikeLiteral($bare).'%')) === false)
		{
			return false;
		}

		$names = array();

		while($row = $this->db->fetch())
		{
			$names[] = (string) $row['name'];
		}

		return $names;
	}

	/**
	 * Built from the table's schema: Key is PRI for a primary-key column, UNI for the column of a one-column unique
	 * index, MUL for the first column of any other index; Default is the value, unquoted.
	 *
	 * @inheritDoc
	 */
	public function getColumnRows($table)
	{
		$schema = $this->readOrFalse($table);

		if($schema === false)
		{
			return false;
		}

		$keys = $this->columnKeys($schema);
		$rows = array();

		foreach($schema->getColumns() as $name => $column)
		{
			$rows[] = array(
				'Field'   => $name,
				'Type'    => $column->getColumnType(),
				'Null'    => $column->isNullable() ? 'YES' : 'NO',
				'Key'     => isset($keys[$name]) ? $keys[$name] : '',
				'Default' => $this->literalValue($column->getDefault()),
				'Extra'   => $column->getExtra(),
			);
		}

		return $rows;
	}

	/**
	 * @inheritDoc
	 */
	public function getIndexRows($table)
	{
		$schema = $this->readOrFalse($table);

		if($schema === false)
		{
			return false;
		}

		$rows = array();

		foreach($schema->getIndexes() as $index)
		{
			foreach($index->getParts() as $position => $part)
			{
				$column = $schema->getColumn($part->getColumnName());

				$rows[] = array(
					'Table'         => $schema->getName(),
					'Non_unique'    => ($index->getKind() === IndexSchema::KIND_INDEX) ? '1' : '0',
					'Key_name'      => $index->getName(),
					'Seq_in_index'  => (string) ($position + 1),
					'Column_name'   => $part->getColumnName(),
					'Collation'     => ($part->getDirection() === 'DESC') ? 'D' : 'A',
					'Cardinality'   => null,
					'Sub_part'      => null,
					'Packed'        => null,
					'Null'          => ($column !== null && $column->isNullable()) ? 'YES' : '',
					'Index_type'    => 'BTREE',
					'Comment'       => '',
					'Index_comment' => '',
				);
			}
		}

		return $rows;
	}

	/**
	 * The table's own CREATE TABLE followed by its CREATE INDEX statements, ';'-separated.
	 *
	 * @inheritDoc
	 */
	public function getCreateStatement($table)
	{
		list($schema, $bare) = SqliteSchemaReader::split($table);

		if($this->db->execute('SELECT type, sql FROM '.SqliteSchemaReader::master($schema).' WHERE tbl_name = :t AND sql IS NOT NULL ORDER BY (type = \'table\') DESC, name', array('t' => $bare)) === false)
		{
			return null;
		}

		$statements = array();

		while($row = $this->db->fetch())
		{
			$statements[] = $row['sql'];
		}

		return empty($statements) ? null : implode(";\n", $statements);
	}

	/**
	 * Created from the source table's model, so the copy's indexes are named after the copy.
	 *
	 * @inheritDoc
	 */
	public function createTableLike($from, $to)
	{
		$definition = $this->getDefinition($from);

		if($definition === null)
		{
			return false;
		}

		list(, $bareTo) = SqliteSchemaReader::split($to);

		foreach($this->platform()->compileTableDefinition($bareTo, $definition->withName($bareTo)) as $statement)
		{
			if($this->db->execute($statement) === false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * SQLite has no TRUNCATE: every row is deleted and the table's AUTOINCREMENT counter removed.
	 *
	 * @inheritDoc
	 */
	public function truncateTable($table)
	{
		list($schema, $bare) = SqliteSchemaReader::split($table);
		$quoted = $this->quote($table);
		$master = SqliteSchemaReader::master($schema);
		$sequence = $this->quote(($schema === null) ? 'sqlite_sequence' : $schema.'.sqlite_sequence');

		if($this->db->execute('DELETE FROM '.$quoted) === false)
		{
			return false;
		}

		if($this->db->execute('SELECT 1 FROM '.$master." WHERE type = 'table' AND name = 'sqlite_sequence'"))
		{
			$this->db->execute('DELETE FROM '.$sequence.' WHERE name = :t', array('t' => $bare));
		}

		return true;
	}

	/**
	 * The neutral model of a live table in SQLite's own types; a text column is NOCASE unless its collation is 'binary'.
	 *
	 * @param string $table
	 * @return TableDefinition|null null when the table does not exist
	 */
	private function getDefinition($table)
	{
		$schema = $this->getReader()->read($table);

		if($schema === null)
		{
			return null;
		}

		$columns = array();

		foreach($schema->getColumns() as $name => $column)
		{
			$attributes = array(
				'nullable'      => $column->isNullable(),
				'autoIncrement' => ($column->getExtra() === 'auto_increment'),
			);

			list($attributes['defaultKind'], $attributes['default']) = $this->defaultOf($column->getDefault());

			if($column->getColumnType() === 'text' && $column->getCollation() !== 'NOCASE')
			{
				$attributes['collation'] = 'binary';
			}

			$columns[] = new ColumnDefinition($name, ($column->getColumnType() === '') ? 'blob' : $column->getColumnType(), $attributes);
		}

		$indexes = array();

		foreach($schema->getIndexes() as $index)
		{
			$parts = array();
			foreach($index->getParts() as $part)
			{
				$parts[] = array('column' => $part->getColumnName(), 'direction' => $part->getDirection());
			}

			$indexes[] = new IndexDefinition($index->getName(), $index->getKind(), $parts);
		}

		return new TableDefinition($schema->getName(), $columns, $indexes);
	}

	/**
	 * Rebuild a table to a new definition: create it under a temporary name, copy the rows of the columns both
	 * share (or as $columnMap says), drop the old table and rename the new one, in one transaction.
	 *
	 * @param string $table physical name of the table to rebuild
	 * @param TableDefinition $target the table it is to become
	 * @param array|null $columnMap target column => source column or SQL expression; null copies same-named columns
	 * @return bool
	 * @throws QueryException when a step fails; the transaction is rolled back
	 */
	public function rebuildTable($table, TableDefinition $target, $columnMap = null)
	{
		$current = $this->getReader()->read($table);

		if($current === null)
		{
			throw new QueryException('Cannot rebuild "'.$table.'": no such table.');
		}

		if($columnMap === null)
		{
			$columnMap = array();
			foreach(array_keys($target->getColumns()) as $name)
			{
				if($current->getColumn($name) !== null)
				{
					$columnMap[$name] = '`'.str_replace('`', '``', $name).'`';
				}
			}
		}

		$temporary = $table.'__rebuild';
		$platform = $this->platform();
		$db = $this->db;
		$manager = $this;

		return $db->transactional(function() use ($db, $platform, $manager, $table, $temporary, $target, $columnMap, $current)
		{
			$manager->run('DROP TABLE IF EXISTS `'.$temporary.'`');

			$statements = $platform->compileTableDefinition($temporary, $target->withName($temporary));
			$manager->run(array_shift($statements)); // the table; its indexes are made once it has its real name

			if(!empty($columnMap))
			{
				$quoted = array();
				foreach(array_keys($columnMap) as $name)
				{
					$quoted[] = '`'.str_replace('`', '``', $name).'`';
				}

				$manager->run('INSERT INTO `'.$temporary.'` ('.implode(', ', $quoted).') SELECT '.implode(', ', $columnMap).' FROM `'.$table.'`');
			}

			$manager->run('DROP TABLE `'.$table.'`');
			$manager->run('ALTER TABLE `'.$temporary.'` RENAME TO `'.$table.'`');

			foreach($target->getIndexes() as $index)
			{
				if(!$index->isPrimary() && $platform->materialises($index))
				{
					$manager->run($platform->compileCreateIndex($table, $index));
				}
			}

			return true;
		});
	}

	/**
	 * Run one statement, throwing on failure.
	 *
	 * @param string $sql
	 * @return void
	 * @throws QueryException
	 */
	public function run($sql)
	{
		if($this->db->execute($sql) === false)
		{
			throw new QueryException($this->db->getLastErrorText().' ['.$sql.']');
		}
	}

	/**
	 * A default as SHOW COLUMNS reports it: the value, unquoted.
	 *
	 * @param string|null $default dflt_value as SQLite stores it
	 * @return string|null
	 */
	private function literalValue($default)
	{
		list($kind, $value) = $this->defaultOf($default);

		return ($kind === ColumnDefinition::DEFAULT_NULL || $kind === ColumnDefinition::DEFAULT_NONE) ? null : $value;
	}

	/**
	 * @param string|null $default dflt_value as SQLite stores it, an expression's outer parentheses already removed
	 * @return array array(ColumnDefinition::DEFAULT_* kind, value or expression)
	 */
	private function defaultOf($default)
	{
		if($default === null)
		{
			return array(ColumnDefinition::DEFAULT_NONE, null);
		}

		$tokens = SqlLexer::sqlite()->significantTokens($default);
		$sign = (count($tokens) === 2 && $tokens[0]['type'] === SqlLexer::T_SYMBOL && in_array($tokens[0]['text'], array('-', '+'), true)) ? array_shift($tokens) : null;

		if(count($tokens) === 1 && $tokens[0]['type'] === SqlLexer::T_WORD && $sign === null && strtoupper($tokens[0]['text']) === 'NULL')
		{
			return array(ColumnDefinition::DEFAULT_NULL, null);
		}

		if(count($tokens) === 1 && $tokens[0]['type'] === SqlLexer::T_STRING && $sign === null)
		{
			return array(ColumnDefinition::DEFAULT_LITERAL, $tokens[0]['value']);
		}

		if(count($tokens) === 1 && $tokens[0]['type'] === SqlLexer::T_NUMBER && preg_match('/^(?:\d+\.?\d*|\.\d+)(?:[eE][+-]?\d+)?$/D', $tokens[0]['text']))
		{
			return array(ColumnDefinition::DEFAULT_LITERAL, (($sign !== null && $sign['text'] === '-') ? '-' : '').$tokens[0]['text']);
		}

		return array(ColumnDefinition::DEFAULT_EXPRESSION, trim($default));
	}

	/**
	 * @param TableSchema $schema
	 * @return array column name => 'PRI', 'UNI' or 'MUL'
	 */
	private function columnKeys(TableSchema $schema)
	{
		$keys = array();

		foreach($schema->getIndexes() as $index)
		{
			$names = $index->getColumnNames();
			$first = reset($names);

			if($index->getKind() === IndexSchema::KIND_PRIMARY)
			{
				foreach($names as $name)
				{
					$keys[$name] = 'PRI';
				}
			}
			elseif(!isset($keys[$first]))
			{
				$keys[$first] = ($index->getKind() === IndexSchema::KIND_UNIQUE && count($names) === 1) ? 'UNI' : 'MUL';
			}
		}

		return $keys;
	}

	/**
	 * @param string $table
	 * @return TableSchema|false
	 */
	private function readOrFalse($table)
	{
		try
		{
			$schema = $this->getReader()->read($table);
		}
		catch(QueryException $e)
		{
			return false;
		}

		return ($schema === null) ? false : $schema;
	}

	/**
	 * @return SqlitePlatform
	 */
	private function platform()
	{
		return $this->db->getPlatform();
	}

	/**
	 * @param string $name a name, or a table qualified with its attached database
	 * @return string the name quoted for SQLite
	 * @throws InvalidArgumentException on a name outside the {@see \e107\Database\IdentifierFilter} grammar
	 */
	private function quote($name)
	{
		$quoted = $this->platform()->quoteIdentifier($name);

		if($quoted === false)
		{
			throw new InvalidArgumentException('Invalid name "'.$name.'" for a schema operation.');
		}

		return $quoted;
	}
}
