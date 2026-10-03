<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Platform;

use e107\Database\Exception\UnsupportedException;
use e107\Database\Schema\Definition\ColumnDefinition;
use e107\Database\Schema\Definition\IndexDefinition;
use e107\Database\Schema\Definition\MysqlLiteral;
use e107\Database\Schema\Definition\TableDefinition;

require_once(__DIR__.'/AbstractPlatform.php');
require_once(dirname(__DIR__).'/Schema/Definition/MysqlLiteral.php');

/**
 * SQLite dialect (3.35 and later); what SQLite lacks comes from {@see \e107\Database\Driver\SqliteFunctions}, which
 * the SQLite driver registers on every connection. Text compares as COLLATE NOCASE, and an index is named
 * {table}__{index}, since index names are global in SQLite.
 */
class SqlitePlatform extends AbstractPlatform
{
	/** @var string separator between a table name and an index name in a physical index name */
	const INDEX_NAME_SEPARATOR = '__';

	/** @var string the SQLite library version, for features that arrived after 3.35 */
	private $version;

	/**
	 * @param string|null $version SQLite library version; null assumes the oldest supported, 3.35
	 */
	public function __construct($version = null)
	{
		$this->version = ($version === null || $version === '') ? '3.35.0' : (string) $version;
	}

	/**
	 * @inheritDoc
	 */
	public function getLimitClause($limit, $offset = null)
	{
		$offset = ($offset === null) ? 0 : (int) $offset;

		if($limit === null)
		{
			return ($offset > 0) ? ' LIMIT -1 OFFSET '.$offset : '';
		}

		return ' LIMIT '.(int) $limit.(($offset > 0) ? ' OFFSET '.$offset : '');
	}

	/**
	 * @inheritDoc
	 */
	public function quoteRegexpLiteral($value)
	{
		return preg_quote((string) $value);
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultCharset()
	{
		return 'utf8';
	}

	/**
	 * A colliding row that already holds every value is left alone, so it counts as no change, as on MySQL.
	 *
	 * @inheritDoc
	 */
	public function compileUpsert($quotedTable, array $columns, array $tuples, array $updateAssignments, array $conflictColumns = array(), $modifier = '')
	{
		if($modifier !== '')
		{
			throw new UnsupportedException('SQLite has no INSERT '.$modifier.' for an upsert: a key collision its DO UPDATE makes still fails.');
		}

		$guard = $this->changeGuard($updateAssignments);

		return $this->compileInsert($quotedTable, $columns, $tuples)
			.' ON CONFLICT'.(empty($conflictColumns) ? '' : ' ('.implode(', ', $conflictColumns).')')
			.' DO UPDATE SET '.$this->assignmentList($updateAssignments)
			.(($guard === null) ? '' : ' WHERE '.$guard);
	}

	/**
	 * @inheritDoc
	 */
	public function getUpsertValueReference($quotedColumn)
	{
		return 'excluded.'.$quotedColumn;
	}

	/**
	 * Counts the rows changed, as MySQL does; a limit picks its rows by rowid from the rows matched.
	 *
	 * @inheritDoc
	 */
	public function compileUpdate($quotedTable, array $assignments, $where, $limit = null)
	{
		$where = $this->limitedWhere($quotedTable, $where, $limit);
		$guard = $this->changeGuard($assignments);

		if($guard !== null)
		{
			$where = ($where === '') ? ' WHERE '.$guard : ' WHERE ('.$this->condition($where).') AND '.$guard;
		}

		return 'UPDATE '.$quotedTable.' SET '.$this->assignmentList($assignments).$where;
	}

	/**
	 * A limit picks its rows by rowid.
	 *
	 * @inheritDoc
	 */
	public function compileDelete($quotedTable, $where, $limit = null)
	{
		return 'DELETE FROM '.$quotedTable.$this->limitedWhere($quotedTable, $where, $limit);
	}

	/**
	 * @inheritDoc
	 */
	public function compileFindInSet($needle, $quotedColumn)
	{
		return 'find_in_set('.$needle.', '.$quotedColumn.')';
	}

	/**
	 * @inheritDoc
	 */
	public function getForUpdateClause()
	{
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getSharedLockClause()
	{
		return '';
	}

	/**
	 * @inheritDoc
	 */
	protected function insertVerb($modifier)
	{
		if($modifier === 'IGNORE')
		{
			return 'INSERT OR IGNORE INTO';
		}

		return parent::insertVerb($modifier);
	}

	/**
	 * @inheritDoc
	 */
	public function getRandomFunction()
	{
		return 'RANDOM()';
	}

	/**
	 * @inheritDoc
	 */
	public function compileDatePart($part, $quotedColumn)
	{
		switch($part)
		{
			case 'date':
				return 'date('.$quotedColumn.')';

			case 'time':
				return 'time('.$quotedColumn.')';

			case 'year':
				return "CAST(strftime('%Y', ".$quotedColumn.') AS INTEGER)';

			case 'month':
				return "CAST(strftime('%m', ".$quotedColumn.') AS INTEGER)';

			case 'day':
				return "CAST(strftime('%d', ".$quotedColumn.') AS INTEGER)';
		}

		throw new \InvalidArgumentException('Unknown date part: '.$part);
	}

	/**
	 * @inheritDoc
	 */
	public function compileJsonContains($quotedColumn, $placeholder)
	{
		return 'e107_json_contains('.$quotedColumn.', '.$placeholder.')';
	}

	/**
	 * @inheritDoc
	 */
	public function compileJsonContainsKey($quotedColumn, $placeholder)
	{
		return 'e107_json_contains_path('.$quotedColumn.', '.$placeholder.')';
	}

	/**
	 * @inheritDoc
	 */
	public function compileJsonLength($quotedColumn)
	{
		return 'e107_json_length('.$quotedColumn.')';
	}

	/**
	 * SQLite has substr() and instr() rather than SUBSTRING ... FROM ... FOR and POSITION.
	 *
	 * @inheritDoc
	 */
	public function compileSubstringBefore($expression, $delimiter)
	{
		return 'substr('.$expression.', 1, instr('.$expression.' || '.$delimiter.', '.$delimiter.') - 1)';
	}

	/**
	 * @inheritDoc
	 */
	public function compileCaseSensitiveLike($quotedColumn, $placeholder)
	{
		return 'e107_like_binary('.$quotedColumn.', '.$placeholder.')';
	}

	/**
	 * @inheritDoc
	 */
	public function compileRemoveFromSet($quotedColumn, $placeholder)
	{
		return "trim(replace(',' || ".$quotedColumn." || ',', ',' || ".$placeholder." || ',', ','), ',')";
	}

	/**
	 * Scored row by row, with no index.
	 *
	 * @inheritDoc
	 */
	public function compileFullText(array $quotedColumns, $placeholder, $booleanMode = false)
	{
		return ($booleanMode ? 'e107_match_boolean(' : 'e107_match(').$placeholder.', '.implode(', ', $quotedColumns).')';
	}

	/**
	 * An ORDER BY needs SQLite 3.44.
	 *
	 * @inheritDoc
	 */
	public function compileGroupConcat($quotedExpression, array $quotedOrderBy, $separatorLiteral, $distinct = false)
	{
		$order = '';

		if(!empty($quotedOrderBy))
		{
			if(version_compare($this->version, '3.44.0', '<'))
			{
				throw new UnsupportedException('An ordered GROUP_CONCAT needs SQLite 3.44 or later; this is '.$this->version.'.');
			}

			$order = ' ORDER BY '.implode(', ', $quotedOrderBy);
		}

		if($distinct && $separatorLiteral === "','")
		{
			return 'group_concat(DISTINCT '.$quotedExpression.$order.')';
		}

		if($distinct)
		{
			return 'e107_group_concat_distinct('.$quotedExpression.', '.$separatorLiteral.$order.')';
		}

		return 'group_concat('.$quotedExpression.', '.$separatorLiteral.$order.')';
	}

	/**
	 * One clause per statement; more are refused.
	 *
	 * @inheritDoc
	 */
	public function compileAlterTable($quotedTable, array $clauses)
	{
		if(count($clauses) !== 1)
		{
			throw new UnsupportedException('SQLite alters one thing per ALTER TABLE statement.');
		}

		return 'ALTER TABLE '.$quotedTable.' '.reset($clauses);
	}

	/**
	 * Table options are not written.
	 *
	 * @inheritDoc
	 */
	public function compileCreateTable($quotedTable, array $definitions, $options = '')
	{
		return 'CREATE TABLE '.$quotedTable.' ('.implode(', ', $definitions).')';
	}

	/**
	 * VACUUM, which rebuilds the whole database file.
	 *
	 * @inheritDoc
	 */
	public function compileOptimizeTable(array $quotedTables)
	{
		return 'VACUUM';
	}

	/**
	 * The statements that create a table of the engine-neutral model.
	 *
	 * @param string $physicalTable unquoted; it qualifies the index names too
	 * @param TableDefinition $table
	 * @return string[]
	 * @throws UnsupportedException on something SQLite cannot create
	 */
	public function compileTableDefinition($physicalTable, TableDefinition $table)
	{
		$primary = $table->getPrimaryKey();
		$rowidColumn = $this->rowidColumn($table);
		$definitions = array();

		foreach($table->getColumns() as $column)
		{
			if($column->isAutoIncrement() && ($rowidColumn === null || $column->getName() !== $rowidColumn))
			{
				throw new UnsupportedException('SQLite can only give a lone INTEGER PRIMARY KEY an auto-increment value, not '.$table->getName().'.'.$column->getName().'.');
			}

			if($column->getName() === $rowidColumn)
			{
				$this->refuseOnUpdate($column);
				$definitions[] = $this->quoteDeclared($column->getName()).' INTEGER PRIMARY KEY AUTOINCREMENT';
				continue;
			}

			$keyed = $primary !== null && in_array($column->getName(), $primary->getColumnNames(), true);
			$definitions[] = $this->columnDefinition($column, $this->isLoneKey($table, $column) ? 'INT' : 'INTEGER', $keyed);
		}

		if($primary !== null && $rowidColumn === null)
		{
			$definitions[] = 'PRIMARY KEY ('.implode(', ', $this->quoteNames($primary->getColumnNames())).')';
		}

		$statements = array('CREATE TABLE '.$this->quoteDeclared($physicalTable)." (\n  ".implode(",\n  ", $definitions)."\n)");

		foreach($table->getIndexes() as $index)
		{
			if($index->isPrimary() || !$this->materialises($index))
			{
				continue;
			}

			$statements[] = $this->compileCreateIndex($physicalTable, $index);
		}

		return $statements;
	}

	/**
	 * A column definition, name included, as CREATE TABLE and ALTER TABLE ... ADD COLUMN take it.
	 *
	 * @param ColumnDefinition $column
	 * @return string
	 * @throws UnsupportedException for an auto-increment or ON UPDATE column
	 */
	public function compileColumnDefinition(ColumnDefinition $column)
	{
		return $this->columnDefinition($column, 'INTEGER');
	}

	/**
	 * @param ColumnDefinition $column
	 * @param string $integer the type an integer column is declared with
	 * @param bool $keyed whether the column is in the primary key, NOT NULL whatever it declares
	 * @return string
	 * @throws UnsupportedException for an auto-increment or ON UPDATE column
	 */
	private function columnDefinition(ColumnDefinition $column, $integer, $keyed = false)
	{
		$this->refuseOnUpdate($column);

		if($column->isAutoIncrement())
		{
			throw new UnsupportedException('SQLite cannot add an auto-increment column after the table exists ('.$column->getName().').');
		}

		$affinity = $this->affinity($column);
		$sql = $this->quoteDeclared($column->getName()).' '.(($affinity === 'INTEGER') ? $integer : $affinity);

		if($affinity === 'TEXT' && !$this->isBinaryCollation($column->getCollation()))
		{
			$sql .= ' COLLATE NOCASE';
		}

		$nullable = $column->isNullable() && !$keyed;

		if(!$nullable)
		{
			$sql .= ' NOT NULL';
		}

		$default = $this->defaultFor($column, $affinity, $nullable);

		if($default !== null)
		{
			$sql .= ' DEFAULT '.$default;
		}

		return $sql;
	}

	/**
	 * A CREATE INDEX for one index of a table.
	 *
	 * @param string $physicalTable
	 * @param IndexDefinition $index a UNIQUE or plain INDEX
	 * @return string
	 */
	public function compileCreateIndex($physicalTable, IndexDefinition $index)
	{
		$parts = array();

		foreach($index->getParts() as $part)
		{
			$parts[] = $this->quoteDeclared($part['column']).($part['direction'] === 'DESC' ? ' DESC' : '');
		}

		return 'CREATE '.($index->isUnique() ? 'UNIQUE ' : '').'INDEX '
			.$this->quoteDeclared($this->physicalIndexName($physicalTable, $index->getName()))
			.' ON '.$this->quoteDeclared($physicalTable).' ('.implode(', ', $parts).')';
	}

	/**
	 * The name an index of a table has in the database: index names are global in SQLite.
	 *
	 * @param string $physicalTable
	 * @param string $indexName
	 * @return string
	 */
	public function physicalIndexName($physicalTable, $indexName)
	{
		return $physicalTable.self::INDEX_NAME_SEPARATOR.$indexName;
	}

	/**
	 * Whether an index of this kind is built on SQLite: FULLTEXT and SPATIAL indexes are not.
	 *
	 * @param IndexDefinition $index
	 * @return bool
	 */
	public function materialises(IndexDefinition $index)
	{
		return $index->getKind() !== IndexDefinition::KIND_FULLTEXT && $index->getKind() !== IndexDefinition::KIND_SPATIAL;
	}

	/**
	 * @param ColumnDefinition $column
	 * @return string INTEGER, REAL, NUMERIC, TEXT or BLOB
	 */
	private function affinity(ColumnDefinition $column)
	{
		if($column->isInteger())
		{
			return 'INTEGER';
		}

		if($column->isBinary())
		{
			return 'BLOB';
		}

		switch($column->getType())
		{
			case 'float':
			case 'double':
			case 'real':
				return 'REAL';

			case 'decimal':
			case 'numeric':
			case 'dec':
			case 'fixed':
				return 'NUMERIC';
		}

		return 'TEXT';
	}

	/**
	 * Puts the sequence back to the highest rowid in use, in the table's own database when it is an attached one.
	 *
	 * @inheritDoc
	 */
	public function compileAutoIncrementReset($quotedTable)
	{
		$name = str_replace('`', '', $quotedTable);
		$quoted = $this->quoteIdentifier($name);

		if($quoted === false)
		{
			throw new \InvalidArgumentException('Not a table name: '.$quotedTable);
		}

		$parts = explode('.', trim($name));
		$table = array_pop($parts);
		$schema = empty($parts) ? '' : $this->quoteDeclared($parts[0]).'.';

		return 'UPDATE '.$schema.'sqlite_sequence SET seq = (SELECT COALESCE(MAX(rowid), 0) FROM '.$quoted.") WHERE name = '".$table."'";
	}

	/**
	 * last_insert_rowid() follows every insert, whatever the table.
	 *
	 * @inheritDoc
	 */
	public function reportsInsertIdForEveryTable()
	{
		return true;
	}

	/**
	 * @param array $assignments quoted column => value expression
	 * @return string|null the condition under which they change a row, a change of letter case included; null when
	 *                     there is nothing to assign
	 */
	private function changeGuard(array $assignments)
	{
		$tests = array();

		foreach($assignments as $column => $expression)
		{
			$tests[] = $column.' IS NOT ('.$expression.') COLLATE BINARY';
		}

		return empty($tests) ? null : '('.implode(' OR ', $tests).')';
	}

	/**
	 * @param string $where ' WHERE ...', as the compile methods take it
	 * @return string the condition alone
	 */
	private function condition($where)
	{
		if(!preg_match('/^\s*WHERE\s+(.+)$/is', $where, $match))
		{
			throw new \InvalidArgumentException('Expected a WHERE clause, got: '.$where);
		}

		return $match[1];
	}

	/**
	 * @param string $quotedTable
	 * @param string $where ' WHERE ...' or ''
	 * @param int|null $limit
	 * @return string the WHERE clause, narrowed to at most $limit rows
	 */
	private function limitedWhere($quotedTable, $where, $limit)
	{
		if($limit === null)
		{
			return $where;
		}

		return ' WHERE rowid IN (SELECT rowid FROM '.$quotedTable.$where.' LIMIT '.(int) $limit.')';
	}

	/**
	 * The column that becomes the table's INTEGER PRIMARY KEY AUTOINCREMENT, when the primary key is one
	 * auto-increment column.
	 *
	 * @param TableDefinition $table
	 * @return string|null
	 */
	private function rowidColumn(TableDefinition $table)
	{
		$primary = $table->getPrimaryKey();
		$auto = $table->getAutoIncrementColumn();

		if($primary === null || $auto === null || $primary->getColumnNames() !== array($auto->getName()))
		{
			return null;
		}

		return $auto->getName();
	}

	/**
	 * The DEFAULT a column is written with: its declared one, or for a NOT NULL column without one the implicit
	 * default MySQL's non-strict mode would fill in.
	 *
	 * @param ColumnDefinition $column
	 * @param string $affinity
	 * @param bool $nullable whether the column is written without NOT NULL
	 * @return string|null SQL for the DEFAULT clause, or null for none
	 */
	private function defaultFor(ColumnDefinition $column, $affinity, $nullable)
	{
		switch($column->getDefaultKind())
		{
			case ColumnDefinition::DEFAULT_NULL:
				return 'NULL';

			case ColumnDefinition::DEFAULT_LITERAL:
				return $this->literalFor($column, $column->getDefault(), $affinity);

			case ColumnDefinition::DEFAULT_EXPRESSION:
				return ($column->getDefault() === 'CURRENT_TIMESTAMP') ? 'CURRENT_TIMESTAMP' : '('.$column->getDefault().')';
		}

		if($nullable)
		{
			return null;
		}

		switch($column->getType())
		{
			case 'datetime':
			case 'timestamp':
				return "'0000-00-00 00:00:00'";

			case 'date':
				return "'0000-00-00'";

			case 'time':
				return "'00:00:00'";

			case 'enum':
				$members = $column->getMembers();
				return $this->literalFor($column, isset($members[0]) ? $members[0] : '', 'TEXT');
		}

		switch($affinity)
		{
			case 'INTEGER':
			case 'REAL':
			case 'NUMERIC':
				return '0';
		}

		return "''";
	}

	/**
	 * @param ColumnDefinition $column
	 * @param string $value
	 * @param string $affinity
	 * @return string a numeric literal bare in a numeric column, otherwise a quoted string
	 * @throws UnsupportedException for text SQLite cannot hold as a string literal: a NUL byte, or bytes that are not UTF-8
	 */
	private function literalFor(ColumnDefinition $column, $value, $affinity)
	{
		$value = (string) $value;

		if($affinity !== 'TEXT' && $affinity !== 'BLOB' && MysqlLiteral::isDecimal($value))
		{
			return $value;
		}

		if(strpos($value, "\0") !== false || !preg_match('//u', $value))
		{
			throw new UnsupportedException('SQLite cannot write the default of '.$column->getName().' as text: it holds a NUL byte or bytes that are not UTF-8.');
		}

		return "'".str_replace("'", "''", $value)."'";
	}

	/**
	 * @param TableDefinition $table
	 * @param ColumnDefinition $column
	 * @return bool whether the column is the whole primary key
	 */
	private function isLoneKey(TableDefinition $table, ColumnDefinition $column)
	{
		$primary = $table->getPrimaryKey();

		return $primary !== null && $primary->getColumnNames() === array($column->getName());
	}

	/**
	 * @param string|null $collation
	 * @return bool whether a declared collation compares bytes rather than letters, as binary, _bin and _cs ones do
	 */
	private function isBinaryCollation($collation)
	{
		return $collation !== null && ($collation === 'binary' || preg_match('/_(bin|cs)$/i', $collation) === 1);
	}

	/**
	 * @param string[] $names
	 * @return string[]
	 */
	private function quoteNames(array $names)
	{
		return array_map(array($this, 'quoteDeclared'), $names);
	}

	/**
	 * @param string $name a table, column or index name
	 * @return string
	 * @throws \InvalidArgumentException for a name outside {@see \e107\Database\IdentifierFilter}'s grammar
	 */
	private function quoteDeclared($name)
	{
		$quoted = (strpos((string) $name, '.') === false) ? $this->quoteIdentifier($name) : false;

		if($quoted === false)
		{
			throw new \InvalidArgumentException('Not a table, column or index name: '.$name);
		}

		return $quoted;
	}

	/**
	 * @param ColumnDefinition $column
	 * @return void
	 * @throws UnsupportedException for ON UPDATE, which SQLite has no column clause for
	 */
	private function refuseOnUpdate(ColumnDefinition $column)
	{
		if($column->getOnUpdate() !== null)
		{
			throw new UnsupportedException('SQLite has no ON UPDATE for the column '.$column->getName().'.');
		}
	}
}
