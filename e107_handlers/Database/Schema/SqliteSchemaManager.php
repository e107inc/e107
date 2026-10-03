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
use e107\Database\Exception\UnsupportedException;
use e107\Database\Platform\SqlitePlatform;
use e107\Database\Schema\Definition\ColumnDefinition;
use e107\Database\Schema\Definition\IndexDefinition;
use e107\Database\Schema\Definition\MysqlDdlParser;
use e107\Database\Schema\Definition\MysqlDdlWriter;
use e107\Database\Schema\Definition\MysqlLiteral;
use e107\Database\Schema\Definition\TableDefinition;
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Introspect\SqliteSchemaReader;
use e107\Database\Schema\Introspect\TableSchema;
use e107\Database\SqlLexer;
use InvalidArgumentException;

require_once(__DIR__.'/SchemaManagerInterface.php');
require_once(__DIR__.'/Introspect/SqliteSchemaReader.php');
require_once(__DIR__.'/Definition/TableDefinition.php');
require_once(__DIR__.'/Definition/MysqlLiteral.php');
require_once(__DIR__.'/TableOperation.php');

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
	 * The rows are counted. The bytes come from the dbstat table, which an SQLite library built without it does
	 * not have; they are null there.
	 *
	 * @inheritDoc
	 */
	public function getTableStatus($table)
	{
		list($schema, $bare) = SqliteSchemaReader::split($table);
		$master = SqliteSchemaReader::master($schema);
		$quoted = $this->quote($table);

		if(!$this->db->execute('SELECT 1 FROM '.$master." WHERE type = 'table' AND name = :name", array('name' => $bare)))
		{
			return null;
		}

		$this->db->execute('SELECT COUNT(*) AS n FROM '.$quoted);
		$row = $this->db->fetch();
		$rows = is_array($row) ? (int) $row['n'] : null;
		$data = null;
		$index = null;

		$pages = 'SELECT COALESCE(SUM(pgsize), 0) AS bytes FROM dbstat'.(($schema === null) ? '' : '(:schema)');
		$params = ($schema === null) ? array('name' => $bare) : array('name' => $bare, 'schema' => $schema);

		if($this->db->execute($pages.' WHERE name = :name', $params) !== false)
		{
			$row = $this->db->fetch();
			$data = (int) $row['bytes'];

			$this->db->execute($pages.' WHERE name IN (SELECT name FROM '.$master." WHERE type = 'index' AND tbl_name = :name)", $params);
			$row = $this->db->fetch();
			$index = is_array($row) ? (int) $row['bytes'] : null;
		}

		return array(
			'rows'           => $rows,
			'data_length'    => $data,
			'index_length'   => $index,
			'avg_row_length' => ($data === null) ? null : ($rows ? (int) floor($data / $rows) : 0),
		);
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
	 * Read from the schema DSL and rendered in SQLite's dialect; of the table options only a collation has a meaning here.
	 *
	 * @inheritDoc
	 */
	public function compileCreateTable($table, array $definitions, $options = '')
	{
		$this->requireUnqualified($table, 'create');

		if(!class_exists(MysqlDdlParser::class, false))
		{
			require_once(__DIR__.'/Definition/MysqlDdlParser.php');
		}

		$parser = new MysqlDdlParser();

		return $this->platform()->compileTableDefinition($table, $parser->parseTableBody($table, implode(', ', $definitions), $parser->parseTableOptions($options)));
	}

	/**
	 * The statements that rename a table, and its indexes after it, since SQLite's index names are global.
	 *
	 * @param string $from physical name of the table
	 * @param string $to physical name it is to have
	 * @return string[]
	 * @throws QueryException when the table does not exist
	 */
	public function compileRenameTable($from, $to)
	{
		$this->requireUnqualified($from, 'rename');
		$this->requireUnqualified($to, 'rename');

		$definition = $this->getDefinition($from);

		if($definition === null)
		{
			throw new QueryException('Cannot rename "'.$from.'": no such table.');
		}

		$platform = $this->platform();
		$statements = array($platform->compileRenameTable($this->quote($from), $this->quote($to)));
		$physical = $this->indexNames($from);

		foreach($definition->getIndexes() as $index)
		{
			$old = $platform->physicalIndexName($from, $index->getName());

			if(in_array($old, $physical, true))
			{
				$statements[] = 'DROP INDEX '.$this->quote($old);
				$statements[] = $platform->compileCreateIndex($to, $index);
			}
		}

		return $statements;
	}

	/**
	 * VACUUM, which names no table.
	 *
	 * @inheritDoc
	 */
	public function compileOptimizeTable(array $tables)
	{
		return array($this->platform()->compileOptimizeTable(array()));
	}

	/**
	 * One statement per change SQLite's ALTER TABLE, CREATE INDEX or DROP INDEX can make; a rebuild of the table for
	 * any other change in the batch; none for an engine or character set; a raw MySQL clause is refused.
	 *
	 * @inheritDoc
	 * @throws QueryException when the table, or a column or index a change names, is not there
	 * @throws InvalidArgumentException on a definition that cannot be read, or a column or index added twice
	 */
	public function compileAlterTable($table, array $operations)
	{
		$this->requireUnqualified($table, 'alter');

		$current = $this->getDefinition($table);

		if($current === null)
		{
			throw new QueryException('Cannot alter "'.$table.'": no such table.');
		}

		$platform = $this->platform();
		$quoted = $this->quote($table);
		$target = $current;
		$sourceOf = array();

		foreach(array_keys($current->getColumns()) as $name)
		{
			$sourceOf[$name] = $name;
		}

		$native = array();
		$rebuild = false;

		foreach($operations as $operation)
		{
			switch($operation->getType())
			{
				case TableOperation::ADD_COLUMN:
					$column = $operation->getColumnDefinition();
					$names = array_keys($target->getColumns());
					$after = $operation->getAfter();

					if($column->isAutoIncrement() || $column->getDefaultKind() === ColumnDefinition::DEFAULT_EXPRESSION || ($after !== null && $after !== end($names)))
					{
						$rebuild = true;
					}
					else
					{
						$native[] = 'ALTER TABLE '.$quoted.' ADD COLUMN '.$platform->compileColumnDefinition($column);
					}

					$target = self::withColumn($target, $column, $after);
					break;

				case TableOperation::MODIFY_COLUMN:
				case TableOperation::CHANGE_COLUMN:
					$existing = self::requireColumn($target, $operation->getColumnName());
					$old = $existing->getName();
					$column = $operation->getColumnDefinition();
					$renameOnly = ($operation->getAfter() === null && !$column->isAutoIncrement() && !$existing->isAutoIncrement()
						&& $platform->compileColumnDefinition($existing->withName($column->getName())) === $platform->compileColumnDefinition($column));

					if(!$renameOnly)
					{
						$rebuild = true;
					}
					elseif($old !== $column->getName())
					{
						$native[] = 'ALTER TABLE '.$quoted.' RENAME COLUMN '.$this->quote($old).' TO '.$this->quote($column->getName());
					}

					if(isset($sourceOf[$old]) && $old !== $column->getName())
					{
						$sourceOf[$column->getName()] = $sourceOf[$old];
						unset($sourceOf[$old]);
					}

					$target = self::withColumn($target, $column, $operation->getAfter(), $old);
					break;

				case TableOperation::DROP_COLUMN:
					$dropped = self::requireColumn($target, $operation->getColumnName());
					$name = $dropped->getName();

					if($dropped->isAutoIncrement() || self::isIndexed($target, $name))
					{
						$rebuild = true;
					}
					else
					{
						$native[] = 'ALTER TABLE '.$quoted.' DROP COLUMN '.$this->quote($name);
					}

					unset($sourceOf[$name]);
					$target = self::withoutColumn($target, $name);
					break;

				case TableOperation::ADD_INDEX:
					$index = $operation->getIndexDefinition();

					if($index->isPrimary())
					{
						$rebuild = true;
					}
					elseif($platform->materialises($index))
					{
						$native[] = $platform->compileCreateIndex($table, $index);
					}

					$target = self::withIndex($target, $index);
					break;

				case TableOperation::DROP_INDEX:
					$index = $target->getIndex($operation->getValue());

					if($index === null)
					{
						throw new QueryException('Cannot drop index "'.$operation->getValue().'" of "'.$table.'": no such index.');
					}

					if($index->isPrimary())
					{
						$rebuild = true;
					}
					elseif($platform->materialises($index))
					{
						$native[] = 'DROP INDEX '.$this->quote($platform->physicalIndexName($table, $index->getName()));
					}

					$target = self::withoutIndex($target, $index->getName());
					break;

				case TableOperation::DROP_PRIMARY_KEY:
					$rebuild = true;
					$target = self::withoutIndex($target, 'PRIMARY');
					break;

				case TableOperation::SET_ENGINE:
				case TableOperation::CONVERT_CHARSET:
					break;

				default:
					throw new UnsupportedException('SQLite cannot run a raw MySQL ALTER TABLE clause: '.$operation->getClause());
			}
		}

		if(!$rebuild)
		{
			return $native;
		}

		return $this->compileRebuild($table, $target, $sourceOf);
	}

	/**
	 * @param string $table
	 * @param string $temporary the table rebuilt from it, whose counter starts from its highest id
	 * @return string[] the statements that raise the rebuilt table's AUTOINCREMENT counter to the old one's
	 */
	private function compileCounterCarry($table, $temporary)
	{
		$old = "(SELECT seq FROM sqlite_sequence WHERE name = '".$table."')";

		return array(
			"INSERT INTO sqlite_sequence (name, seq) SELECT '".$temporary."', ".$old
				." WHERE ".$old." IS NOT NULL AND NOT EXISTS (SELECT 1 FROM sqlite_sequence WHERE name = '".$temporary."')",
			"UPDATE sqlite_sequence SET seq = ".$old." WHERE name = '".$temporary."' AND seq < ".$old,
		);
	}

	/**
	 * The statements, for one transaction, that rebuild a table to a new definition under a temporary name and give it
	 * the old one's name, rows and AUTOINCREMENT counter.
	 *
	 * @param string $table physical name of the table to rebuild
	 * @param TableDefinition $target the table it is to become
	 * @param string[] $sourceOf column of the target => the column of the table its rows are copied from
	 * @return string[]
	 */
	private function compileRebuild($table, TableDefinition $target, array $sourceOf)
	{
		$platform = $this->platform();
		$temporary = $table.'__rebuild';
		$definition = $platform->compileTableDefinition($temporary, $target->withName($temporary));
		$statements = array('DROP TABLE IF EXISTS '.$this->quote($temporary), array_shift($definition));

		if(!empty($sourceOf))
		{
			$statements[] = 'INSERT INTO '.$this->quote($temporary).' ('.implode(', ', array_map(array($this, 'quote'), array_keys($sourceOf))).')'
				.' SELECT '.implode(', ', array_map(array($this, 'quote'), $sourceOf)).' FROM '.$this->quote($table);
		}

		if($target->getAutoIncrementColumn() !== null)
		{
			$statements = array_merge($statements, $this->compileCounterCarry($table, $temporary));
		}

		$statements[] = 'DROP TABLE '.$this->quote($table);
		$statements[] = 'ALTER TABLE '.$this->quote($temporary).' RENAME TO '.$this->quote($table);

		foreach($target->getIndexes() as $index)
		{
			if(!$index->isPrimary() && $platform->materialises($index))
			{
				$statements[] = $platform->compileCreateIndex($table, $index);
			}
		}

		return $statements;
	}

	/**
	 * Written in SQLite's own types (integer, text ...), which render back to the same affinities; there are no options.
	 *
	 * @inheritDoc
	 */
	public function describeDefinitions($table)
	{
		$definition = $this->getDefinition($table);

		if($definition === null)
		{
			return null;
		}

		if(!class_exists(MysqlDdlWriter::class, false))
		{
			require_once(__DIR__.'/Definition/MysqlDdlWriter.php');
		}

		$writer = new MysqlDdlWriter();
		$columns = array();
		$indexes = array();

		foreach($definition->getColumns() as $name => $column)
		{
			$columns[$name] = $writer->writeColumn($column);
		}

		foreach($definition->getIndexes() as $name => $index)
		{
			$indexes[$name] = $writer->writeIndex($index);
		}

		return array(
			'body'    => $writer->writeBody($definition),
			'options' => '',
			'columns' => $columns,
			'indexes' => $indexes,
		);
	}

	/**
	 * @param string|null $default dflt_value as SQLite stores it
	 * @return string|null the default as SHOW COLUMNS reports it: the value, unquoted
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

		if(count($tokens) === 1 && $tokens[0]['type'] === SqlLexer::T_NUMBER && MysqlLiteral::isDecimal($tokens[0]['text']))
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
	 * @param string $table
	 * @return string[] the physical names of the table's indexes
	 */
	private function indexNames($table)
	{
		$names = array();

		if($this->db->execute("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = :t", array('t' => $table)) !== false)
		{
			while($row = $this->db->fetch())
			{
				$names[] = (string) $row['name'];
			}
		}

		return $names;
	}

	/**
	 * @param string $table
	 * @param string $what the operation, for the message
	 * @return void
	 * @throws UnsupportedException for a table of an attached database
	 * @throws InvalidArgumentException on an invalid table name
	 */
	private function requireUnqualified($table, $what)
	{
		if(strpos($table, '.') !== false)
		{
			throw new UnsupportedException('SQLite cannot '.$what.' "'.$table.'" here: it belongs to an attached database.');
		}

		$this->quote($table);
	}

	/**
	 * @param TableDefinition $table
	 * @param string $name
	 * @return ColumnDefinition
	 * @throws QueryException when the table has no such column
	 */
	private static function requireColumn(TableDefinition $table, $name)
	{
		$column = $table->getColumn($name);

		if($column === null)
		{
			throw new QueryException('Table "'.$table->getName().'" has no column "'.$name.'".');
		}

		return $column;
	}

	/**
	 * @param TableDefinition $table
	 * @param string $column
	 * @return bool whether an index holds the column
	 */
	private static function isIndexed(TableDefinition $table, $column)
	{
		foreach($table->getIndexes() as $index)
		{
			if(in_array($column, $index->getColumnNames(), true))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * A copy with a column added, or put in place of another, where $after says.
	 *
	 * @param TableDefinition $table
	 * @param ColumnDefinition $column
	 * @param string|null $after null for the end (or, when replacing, the replaced column's place),
	 *                           {@see SchemaBuilder::FIRST}, or the column to follow, in any case
	 * @param string|null $replaces the column it takes the place of
	 * @return TableDefinition
	 */
	private static function withColumn(TableDefinition $table, ColumnDefinition $column, $after, $replaces = null)
	{
		$columns = array();
		$placed = false;
		$anchor = ($after === null || $after === SchemaBuilder::FIRST) ? null : $table->getColumn($after);

		if($anchor !== null)
		{
			$after = $anchor->getName();
		}

		if($after === SchemaBuilder::FIRST)
		{
			$columns[] = $column;
			$placed = true;
		}

		foreach($table->getColumns() as $name => $existing)
		{
			if($name === $replaces)
			{
				if($after === null)
				{
					$columns[] = $column;
					$placed = true;
				}

				continue;
			}

			$columns[] = $existing;

			if($after !== null && $name === $after && !$placed)
			{
				$columns[] = $column;
				$placed = true;
			}
		}

		if(!$placed)
		{
			if($after !== null && $after !== SchemaBuilder::FIRST)
			{
				throw new QueryException('Table "'.$table->getName().'" has no column "'.$after.'" to place "'.$column->getName().'" after.');
			}

			$columns[] = $column;
		}

		$indexes = $table->getIndexes();

		if($replaces !== null && $replaces !== $column->getName())
		{
			$indexes = self::renameInIndexes($indexes, $replaces, $column->getName());
		}

		return new TableDefinition($table->getName(), $columns, $indexes, $table->getOptions());
	}

	/**
	 * A copy without a column; an index loses the column, and goes when it holds nothing else, as in MySQL.
	 *
	 * @param TableDefinition $table
	 * @param string $name
	 * @return TableDefinition
	 */
	private static function withoutColumn(TableDefinition $table, $name)
	{
		$columns = $table->getColumns();
		unset($columns[$name]);
		$indexes = array();

		foreach($table->getIndexes() as $index)
		{
			$parts = array();

			foreach($index->getParts() as $part)
			{
				if($part['column'] !== $name)
				{
					$parts[] = $part;
				}
			}

			if(!empty($parts))
			{
				$indexes[] = new IndexDefinition($index->getName(), $index->getKind(), $parts);
			}
		}

		return new TableDefinition($table->getName(), $columns, $indexes, $table->getOptions());
	}

	/**
	 * @param TableDefinition $table
	 * @param IndexDefinition $index
	 * @return TableDefinition
	 */
	private static function withIndex(TableDefinition $table, IndexDefinition $index)
	{
		if($table->getIndex($index->getName()) !== null)
		{
			throw new QueryException('Table "'.$table->getName().'" already has an index "'.$index->getName().'".');
		}

		$indexes = $table->getIndexes();
		$indexes[] = $index;

		return new TableDefinition($table->getName(), $table->getColumns(), $indexes, $table->getOptions());
	}

	/**
	 * @param TableDefinition $table
	 * @param string $name
	 * @return TableDefinition
	 */
	private static function withoutIndex(TableDefinition $table, $name)
	{
		$indexes = $table->getIndexes();
		unset($indexes[$name]);

		return new TableDefinition($table->getName(), $table->getColumns(), $indexes, $table->getOptions());
	}

	/**
	 * @param IndexDefinition[] $indexes
	 * @param string $from
	 * @param string $to
	 * @return IndexDefinition[]
	 */
	private static function renameInIndexes(array $indexes, $from, $to)
	{
		$renamed = array();

		foreach($indexes as $index)
		{
			$parts = array();

			foreach($index->getParts() as $part)
			{
				if($part['column'] === $from)
				{
					$part['column'] = $to;
				}

				$parts[] = $part;
			}

			$renamed[] = new IndexDefinition($index->getName(), $index->getKind(), $parts);
		}

		return $renamed;
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
