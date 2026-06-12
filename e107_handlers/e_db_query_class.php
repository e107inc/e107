<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

/**
 * Fluent SQL query builder bound to an e107 database connection.
 *
 * Create one with {@see e_db::createQueryBuilder()}. The builder compiles to
 * SQL with bound :named placeholders and runs through {@see e_db::execute()},
 * so no value ever becomes SQL text. Table names are logical: no '#' marker
 * and no database prefix; both the prefix and multi-language routing are
 * applied at compile time via {@see e_db::resolveTableName()}. Identifier
 * positions (tables, aliases, assignment columns, expression columns, ORDER
 * BY) are validated against a strict grammar and throw on violation rather
 * than guessing.
 *
 * <code>
 * $sql = e107::getDb();
 *
 * $rows = $sql->createQueryBuilder()
 *     ->select('user_id', 'user_name')
 *     ->from('user')
 *     ->whereIn('user_class', array(1, 2))
 *     ->orderBy('user_name', 'ASC')
 *     ->setMaxResults(20)
 *     ->fetchAll();
 *
 * $qb = $sql->createQueryBuilder();
 * $name = $qb->select('user_name')
 *     ->from('user')
 *     ->where($qb->expr()->eq('user_id', $id))
 *     ->fetchOne();
 * </code>
 *
 * Positions that accept developer-authored SQL fragments verbatim, select()
 * expressions, join() conditions and hand-written where()/having() strings,
 * must never receive user input directly; put values through
 * {@see e_db_expr} or {@see e_db_query::createNamedParameter()} instead.
 */
class e_db_query
{
	const TYPE_SELECT = 0;
	const TYPE_INSERT = 1;
	const TYPE_UPDATE = 2;
	const TYPE_DELETE = 3;

	/** @var e_db */
	private $db;

	/** @var e_db_platform */
	private $platform;

	/** @var int one of the TYPE_* constants */
	private $type = self::TYPE_SELECT;

	/** @var string[] SELECT expressions, already quoted where identifier-shaped */
	private $select = array();

	/** @var string|null logical table name (no '#', no prefix) */
	private $table = null;

	/** @var string|null alias for the FROM table */
	private $alias = null;

	/** @var array[] queued joins: array('type', 'table', 'alias', 'condition') */
	private $join = array();

	/** @var string[] WHERE predicate fragments, combined with AND */
	private $where = array();

	/** @var string[] GROUP BY expressions */
	private $groupBy = array();

	/** @var string[] HAVING predicate fragments, combined with AND */
	private $having = array();

	/** @var string[] canonical ORDER BY fragments, e.g. "`col` DESC" */
	private $orderBy = array();

	/** @var int|null OFFSET */
	private $firstResult = null;

	/** @var int|null LIMIT */
	private $maxResults = null;

	/** @var array quoted column => placeholder, for INSERT/UPDATE */
	private $set = array();

	/** @var array placeholder name => value, in the {@see e_db::execute()} shape */
	private $params = array();

	/** @var int placeholder name counter */
	private $paramCounter = 0;

	/** @var e_db_expr|null lazily created expression helper */
	private $expr = null;

	/**
	 * @param e_db $db Connection the query compiles against and executes on.
	 * @param e_db_platform|null $platform SQL dialect; taken from
	 *                           {@see e_db::getPlatform()} when omitted.
	 */
	public function __construct($db, $platform = null)
	{
		$this->db = $db;
		$this->platform = ($platform !== null) ? $platform : $db->getPlatform();
	}

	/**
	 * Expression helper bound to this query. Every value it receives becomes
	 * a bound parameter on this query, never SQL text.
	 *
	 * @return e_db_expr
	 */
	public function expr()
	{
		if($this->expr === null)
		{
			$this->expr = new e_db_expr($this);
		}

		return $this->expr;
	}

	/**
	 * SQL dialect this query compiles for.
	 *
	 * @return e_db_platform
	 */
	public function getPlatform()
	{
		return $this->platform;
	}

	/**
	 * Register a value as a bound parameter and return its placeholder.
	 *
	 * The escape hatch for hand-written fragments:
	 * <code>
	 * $qb->andWhere('user_join > '.$qb->createNamedParameter($since));
	 * </code>
	 *
	 * @param mixed $value
	 * @param int|null $type Optional {@see e_db}::PARAM_* override;
	 *                       auto-detected from the PHP type when omitted.
	 * @return string placeholder, e.g. ':qb1'
	 */
	public function createNamedParameter($value, $type = null)
	{
		$name = 'qb'.(++$this->paramCounter);
		$this->params[$name] = ($type === null) ? $value : array('value' => $value, 'type' => $type);

		return ':'.$name;
	}

	/**
	 * Validate and quote a column identifier (`column` or `table.column`).
	 * Fails closed: anything outside the {@see e_db_filter::identifier()}
	 * grammar throws.
	 *
	 * @param string $column
	 * @return string backtick-quoted identifier
	 * @throws InvalidArgumentException when the name fails validation.
	 */
	public function quoteColumn($column)
	{
		$quoted = $this->db->quoteIdentifier($column);

		if($quoted === false)
		{
			throw new InvalidArgumentException('Invalid column name: '.$column);
		}

		return $quoted;
	}

	/**
	 * Start a SELECT query and set the column list. Plain column names
	 * (`col`, `tbl.col`, `tbl.*`, `*`) are validated and quoted; anything
	 * else (e.g. "COUNT(*) AS cnt") is kept verbatim as a developer-authored
	 * expression, so never place user input here.
	 *
	 * @param string|array $columns Column list as multiple string arguments
	 *                              or as one array; defaults to '*'.
	 * @return e_db_query $this
	 */
	public function select($columns = '*')
	{
		$this->type = self::TYPE_SELECT;
		$this->select = array();

		$args = is_array($columns) ? $columns : func_get_args();

		foreach($args as $column)
		{
			$this->select[] = $this->_quoteExpression($column);
		}

		return $this;
	}

	/**
	 * Set the table to select from.
	 *
	 * @param string $table Logical table name, e.g. 'user' (no '#', no prefix).
	 * @param string|null $alias Optional table alias.
	 * @return e_db_query $this
	 */
	public function from($table, $alias = null)
	{
		$this->table = $table;
		$this->alias = $alias;

		return $this;
	}

	/**
	 * INNER JOIN another table. The ON condition is developer-authored SQL
	 * (it usually compares two columns); pass any values in it through
	 * {@see e_db_query::createNamedParameter()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @param string $alias Alias for the joined table.
	 * @param string $condition ON condition.
	 * @return e_db_query $this
	 */
	public function join($table, $alias, $condition)
	{
		return $this->_join('INNER', $table, $alias, $condition);
	}

	/**
	 * LEFT JOIN another table; see {@see e_db_query::join()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @param string $alias Alias for the joined table.
	 * @param string $condition ON condition.
	 * @return e_db_query $this
	 */
	public function leftJoin($table, $alias, $condition)
	{
		return $this->_join('LEFT', $table, $alias, $condition);
	}

	/**
	 * Replace the WHERE clause with one predicate. Build predicates with
	 * {@see e_db_query::expr()} so values are bound, or write the fragment
	 * by hand using placeholders from
	 * {@see e_db_query::createNamedParameter()}.
	 *
	 * @param string $predicate
	 * @return e_db_query $this
	 */
	public function where($predicate)
	{
		$this->where = array((string) $predicate);

		return $this;
	}

	/**
	 * AND another predicate onto the WHERE clause. Each predicate is wrapped
	 * in parentheses at compile time, so fragments containing OR compose
	 * safely.
	 *
	 * @param string $predicate
	 * @return e_db_query $this
	 */
	public function andWhere($predicate)
	{
		$this->where[] = (string) $predicate;

		return $this;
	}

	/**
	 * AND a "column IN (...)" predicate with every value bound. Shorthand for
	 * andWhere($qb->expr()->in($column, $values)).
	 *
	 * @param string $column
	 * @param array $values
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function whereIn($column, array $values)
	{
		return $this->andWhere($this->expr()->in($column, $values));
	}

	/**
	 * Set the GROUP BY list. Identifier-shaped entries are quoted; other
	 * expressions are kept verbatim (developer-authored, never user input).
	 *
	 * @param string|array $columns As multiple string arguments or one array.
	 * @return e_db_query $this
	 */
	public function groupBy($columns)
	{
		$this->groupBy = array();

		$args = is_array($columns) ? $columns : func_get_args();

		foreach($args as $column)
		{
			$this->groupBy[] = $this->_quoteExpression($column);
		}

		return $this;
	}

	/**
	 * Replace the HAVING clause with one predicate; see
	 * {@see e_db_query::where()} for how to keep values bound.
	 *
	 * @param string $predicate
	 * @return e_db_query $this
	 */
	public function having($predicate)
	{
		$this->having = array((string) $predicate);

		return $this;
	}

	/**
	 * AND another predicate onto the HAVING clause.
	 *
	 * @param string $predicate
	 * @return e_db_query $this
	 */
	public function andHaving($predicate)
	{
		$this->having[] = (string) $predicate;

		return $this;
	}

	/**
	 * Replace the ORDER BY clause. Validated by the {@see e_db_filter}
	 * grammar and fails closed: anything outside "column [ASC|DESC]" lists
	 * (functions, parentheses, subqueries) throws.
	 *
	 * @param string $sort Column name; or, when $direction is null, a full
	 *                     legacy fragment such as 'col1 DESC, t.col2'.
	 * @param string|null $direction 'ASC' or 'DESC' (case-insensitive).
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when the fragment fails validation.
	 */
	public function orderBy($sort, $direction = null)
	{
		$this->orderBy = array();

		return $this->addOrderBy($sort, $direction);
	}

	/**
	 * Append to the ORDER BY clause; same validation as
	 * {@see e_db_query::orderBy()}.
	 *
	 * @param string $sort
	 * @param string|null $direction
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when the fragment fails validation.
	 */
	public function addOrderBy($sort, $direction = null)
	{
		$this->_loadFilter();

		if($direction !== null)
		{
			$quoted = e_db_filter::identifier($sort);

			if($quoted === false)
			{
				throw new InvalidArgumentException('Invalid ORDER BY column: '.$sort);
			}

			$dir = strtoupper(trim((string) $direction));

			if($dir !== 'ASC' && $dir !== 'DESC')
			{
				throw new InvalidArgumentException('Invalid ORDER BY direction: '.$direction);
			}

			$this->orderBy[] = $quoted.' '.$dir;

			return $this;
		}

		$canonical = e_db_filter::orderBy($sort);

		if($canonical === false)
		{
			throw new InvalidArgumentException('Invalid ORDER BY fragment: '.$sort);
		}

		$this->orderBy[] = $canonical;

		return $this;
	}

	/**
	 * Skip this many rows (OFFSET). Always inlined as an integer.
	 *
	 * @param int|null $firstResult null to clear.
	 * @return e_db_query $this
	 */
	public function setFirstResult($firstResult)
	{
		$this->firstResult = ($firstResult === null) ? null : max(0, (int) $firstResult);

		return $this;
	}

	/**
	 * Return at most this many rows (LIMIT). Always inlined as an integer.
	 *
	 * @param int|null $maxResults null to clear.
	 * @return e_db_query $this
	 */
	public function setMaxResults($maxResults)
	{
		$this->maxResults = ($maxResults === null) ? null : max(0, (int) $maxResults);

		return $this;
	}

	/**
	 * Start an INSERT query; supply the row with
	 * {@see e_db_query::values()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @return e_db_query $this
	 */
	public function insert($table)
	{
		$this->type = self::TYPE_INSERT;
		$this->table = $table;

		return $this;
	}

	/**
	 * Set the column => value map for INSERT. Every value is bound.
	 *
	 * @param array $values column name => value
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when a column name fails validation.
	 */
	public function values(array $values)
	{
		foreach($values as $column => $value)
		{
			$this->set($column, $value);
		}

		return $this;
	}

	/**
	 * Start an UPDATE query; queue assignments with
	 * {@see e_db_query::set()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @return e_db_query $this
	 */
	public function update($table)
	{
		$this->type = self::TYPE_UPDATE;
		$this->table = $table;

		return $this;
	}

	/**
	 * Queue one column assignment for UPDATE (or INSERT). The value is
	 * always bound; SQL expressions such as "col + 1" are not accepted here
	 * and would be stored as the literal string.
	 *
	 * @param string $column
	 * @param mixed $value
	 * @param int|null $type Optional {@see e_db}::PARAM_* override.
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function set($column, $value, $type = null)
	{
		$this->set[$this->quoteColumn($column)] = $this->createNamedParameter($value, $type);

		return $this;
	}

	/**
	 * Start a DELETE query. As with the legacy API, compiling without a
	 * WHERE clause deletes every row in the table.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @return e_db_query $this
	 */
	public function delete($table)
	{
		$this->type = self::TYPE_DELETE;
		$this->table = $table;

		return $this;
	}

	/**
	 * Compile the query to SQL. Together with
	 * {@see e_db_query::getParameters()} this is the query's complete
	 * intermediate representation; no caller-supplied value remains in the
	 * SQL text.
	 *
	 * @return string
	 * @throws InvalidArgumentException when the table or an identifier fails validation.
	 */
	public function getSQL()
	{
		switch($this->type)
		{
			case self::TYPE_INSERT:
				return $this->_compileInsert();

			case self::TYPE_UPDATE:
				return $this->_compileUpdate();

			case self::TYPE_DELETE:
				return $this->_compileDelete();

			default:
				return $this->_compileSelect();
		}
	}

	/**
	 * Bound parameters, in the shape {@see e_db::execute()} accepts.
	 *
	 * @return array placeholder name => value, or
	 *               name => array('value' => mixed, 'type' => e_db::PARAM_*)
	 */
	public function getParameters()
	{
		return $this->params;
	}

	/**
	 * Compile and run the query on the connection.
	 *
	 * @return int|bool the {@see e_db::execute()} return: row count for
	 *                  SELECT (read rows with {@see e_db::fetch()}), affected
	 *                  rows for INSERT/UPDATE/DELETE, false on error.
	 * @throws InvalidArgumentException when the query fails to compile.
	 */
	public function execute()
	{
		return $this->db->execute($this->getSQL(), $this->params);
	}

	/**
	 * Run the query and return every row.
	 *
	 * @param string|null $indexBy Column whose value keys the result array.
	 * @return array rows as associative arrays; empty when no rows match or
	 *               on error (see {@see e_db::getLastErrorText()}).
	 */
	public function fetchAll($indexBy = null)
	{
		$ret = array();

		if($this->execute() === false)
		{
			return $ret;
		}

		while($row = $this->db->fetch())
		{
			if($indexBy !== null && isset($row[$indexBy]))
			{
				$ret[$row[$indexBy]] = $row;
			}
			else
			{
				$ret[] = $row;
			}
		}

		return $ret;
	}

	/**
	 * Run the query and return the first row.
	 *
	 * @return array associative row; empty array when there is none.
	 */
	public function fetchRow()
	{
		if($this->execute() === false)
		{
			return array();
		}

		$row = $this->db->fetch();

		return is_array($row) ? $row : array();
	}

	/**
	 * Run the query and return the first column of the first row.
	 *
	 * @return mixed the value, or null when there is no row.
	 */
	public function fetchOne()
	{
		$row = $this->fetchRow();

		if(count($row) === 0)
		{
			return null;
		}

		return array_shift($row);
	}

	/**
	 * Run the query and return one column from every row.
	 *
	 * @param string|null $column Column name; the first selected column when omitted.
	 * @return array
	 */
	public function fetchColumn($column = null)
	{
		$ret = array();

		if($this->execute() === false)
		{
			return $ret;
		}

		while($row = $this->db->fetch())
		{
			if($column === null)
			{
				$ret[] = array_shift($row);
			}
			elseif(array_key_exists($column, $row))
			{
				$ret[] = $row[$column];
			}
		}

		return $ret;
	}

	/**
	 * Run the query and return a key => value map built from two columns.
	 *
	 * @param string|null $keyColumn The first selected column when omitted.
	 * @param string|null $valueColumn The second selected column when
	 *                    omitted (the first when only one was selected).
	 * @return array
	 */
	public function fetchPairs($keyColumn = null, $valueColumn = null)
	{
		$ret = array();

		if($this->execute() === false)
		{
			return $ret;
		}

		while($row = $this->db->fetch())
		{
			$values = array_values($row);

			$key = ($keyColumn === null) ? $values[0] : $row[$keyColumn];
			$value = ($valueColumn === null) ? (isset($values[1]) ? $values[1] : $values[0]) : $row[$valueColumn];

			$ret[$key] = $value;
		}

		return $ret;
	}

	/**
	 * Quote an expression for a SELECT/GROUP BY position: plain identifiers
	 * ('col', 'tbl.col', '*', 'tbl.*') are validated and quoted, anything
	 * else passes through verbatim as developer-authored SQL.
	 *
	 * @param string $expression
	 * @return string
	 */
	private function _quoteExpression($expression)
	{
		$expression = trim((string) $expression);

		if($expression === '*')
		{
			return '*';
		}

		if(substr($expression, -2) === '.*')
		{
			$quoted = $this->db->quoteIdentifier(substr($expression, 0, -2));

			if($quoted !== false)
			{
				return $quoted.'.*';
			}
		}

		$quoted = $this->db->quoteIdentifier($expression);

		return ($quoted !== false) ? $quoted : $expression;
	}

	/**
	 * @param string $type 'INNER' or 'LEFT'
	 * @param string $table
	 * @param string $alias
	 * @param string $condition
	 * @return e_db_query $this
	 */
	private function _join($type, $table, $alias, $condition)
	{
		$this->join[] = array(
			'type'      => $type,
			'table'     => $table,
			'alias'     => $alias,
			'condition' => $condition,
		);

		return $this;
	}

	/**
	 * Resolve a logical table name to its quoted physical name.
	 *
	 * @param string $table
	 * @return string
	 * @throws InvalidArgumentException when the name fails validation.
	 */
	private function _quotedTable($table)
	{
		$resolved = $this->db->resolveTableName($table);

		if($resolved === false)
		{
			throw new InvalidArgumentException('Invalid table name: '.$table);
		}

		$quote = $this->platform->getIdentifierQuoteCharacter();

		return $quote.$resolved.$quote;
	}

	/**
	 * @param string $alias
	 * @return string
	 * @throws InvalidArgumentException when the alias fails validation.
	 */
	private function _quotedAlias($alias)
	{
		$quoted = $this->db->quoteIdentifier($alias);

		if($quoted === false)
		{
			throw new InvalidArgumentException('Invalid table alias: '.$alias);
		}

		return $quoted;
	}

	/**
	 * @return string
	 */
	private function _compileSelect()
	{
		if($this->table === null)
		{
			throw new InvalidArgumentException('No table set; call from() first.');
		}

		$sql = 'SELECT '.(count($this->select) === 0 ? '*' : implode(', ', $this->select));
		$sql .= ' FROM '.$this->_quotedTable($this->table);

		if($this->alias !== null)
		{
			$sql .= ' AS '.$this->_quotedAlias($this->alias);
		}

		foreach($this->join as $join)
		{
			$sql .= ' '.$join['type'].' JOIN '.$this->_quotedTable($join['table']);

			if($join['alias'] !== null)
			{
				$sql .= ' AS '.$this->_quotedAlias($join['alias']);
			}

			$sql .= ' ON '.$join['condition'];
		}

		$sql .= $this->_compileWhere();

		if(count($this->groupBy) > 0)
		{
			$sql .= ' GROUP BY '.implode(', ', $this->groupBy);
		}

		if(count($this->having) > 0)
		{
			$sql .= ' HAVING ('.implode(') AND (', $this->having).')';
		}

		if(count($this->orderBy) > 0)
		{
			$sql .= ' ORDER BY '.implode(', ', $this->orderBy);
		}

		$sql .= $this->platform->getLimitClause($this->maxResults, $this->firstResult);

		return $sql;
	}

	/**
	 * @return string
	 */
	private function _compileInsert()
	{
		if($this->table === null || count($this->set) === 0)
		{
			throw new InvalidArgumentException('INSERT needs a table and a non-empty values() map.');
		}

		return 'INSERT INTO '.$this->_quotedTable($this->table)
			.' ('.implode(', ', array_keys($this->set)).')'
			.' VALUES ('.implode(', ', array_values($this->set)).')';
	}

	/**
	 * @return string
	 */
	private function _compileUpdate()
	{
		if($this->table === null || count($this->set) === 0)
		{
			throw new InvalidArgumentException('UPDATE needs a table and at least one set().');
		}

		$assignments = array();

		foreach($this->set as $column => $placeholder)
		{
			$assignments[] = $column.' = '.$placeholder;
		}

		return 'UPDATE '.$this->_quotedTable($this->table)
			.' SET '.implode(', ', $assignments)
			.$this->_compileWhere()
			.$this->platform->getLimitClause($this->maxResults);
	}

	/**
	 * @return string
	 */
	private function _compileDelete()
	{
		if($this->table === null)
		{
			throw new InvalidArgumentException('DELETE needs a table; call delete() with one.');
		}

		return 'DELETE FROM '.$this->_quotedTable($this->table)
			.$this->_compileWhere()
			.$this->platform->getLimitClause($this->maxResults);
	}

	/**
	 * @return string ' WHERE (...) AND (...)' or ''
	 */
	private function _compileWhere()
	{
		if(count($this->where) === 0)
		{
			return '';
		}

		return ' WHERE ('.implode(') AND (', $this->where).')';
	}

	/**
	 * @return void
	 */
	private function _loadFilter()
	{
		if(!class_exists('e_db_filter'))
		{
			require_once(__DIR__.'/e_db_filter_class.php');
		}
	}
}


/**
 * Builds WHERE/HAVING predicate fragments for {@see e_db_query}. Column
 * names are validated against the identifier grammar and every comparison
 * value is registered as a bound parameter on the owning query, never placed
 * in the SQL text. This is a deliberate divergence from builders whose
 * expression helpers accept raw SQL on the right-hand side: here the safe
 * spelling is the only spelling.
 */
class e_db_expr
{
	/** @var e_db_query owning query; parameters register on it */
	private $qb;

	/**
	 * @param e_db_query $qb
	 */
	public function __construct($qb)
	{
		$this->qb = $qb;
	}

	/**
	 * `column` = :value
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return string SQL fragment
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function eq($column, $value)
	{
		return $this->_comparison($column, '=', $value);
	}

	/**
	 * `column` <> :value
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return string SQL fragment
	 */
	public function neq($column, $value)
	{
		return $this->_comparison($column, '<>', $value);
	}

	/**
	 * `column` < :value
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return string SQL fragment
	 */
	public function lt($column, $value)
	{
		return $this->_comparison($column, '<', $value);
	}

	/**
	 * `column` <= :value
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return string SQL fragment
	 */
	public function lte($column, $value)
	{
		return $this->_comparison($column, '<=', $value);
	}

	/**
	 * `column` > :value
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return string SQL fragment
	 */
	public function gt($column, $value)
	{
		return $this->_comparison($column, '>', $value);
	}

	/**
	 * `column` >= :value
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return string SQL fragment
	 */
	public function gte($column, $value)
	{
		return $this->_comparison($column, '>=', $value);
	}

	/**
	 * `column` IN (...) with every value bound. An empty $values list
	 * compiles to the always-false predicate 1=0, the correct semantics of
	 * "in the empty set".
	 *
	 * @param string $column
	 * @param array $values
	 * @return string SQL fragment
	 */
	public function in($column, array $values)
	{
		return $this->_inList($column, $values, 'IN', '1=0');
	}

	/**
	 * `column` NOT IN (...) with every value bound. An empty $values list
	 * compiles to the always-true predicate 1=1.
	 *
	 * @param string $column
	 * @param array $values
	 * @return string SQL fragment
	 */
	public function notIn($column, array $values)
	{
		return $this->_inList($column, $values, 'NOT IN', '1=1');
	}

	/**
	 * `column` LIKE :pattern, with $pattern bound verbatim: the caller
	 * controls the % and _ wildcards. For matching plain substrings, use
	 * {@see e_db_expr::contains()} instead.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return string SQL fragment
	 */
	public function like($column, $pattern)
	{
		return $this->_comparison($column, 'LIKE', $pattern);
	}

	/**
	 * Substring match: LIKE with the needle's %, _ and \ escaped and the
	 * result wrapped in %...%, so a literal '%' in $value matches a literal
	 * '%' in the data.
	 *
	 * @param string $column
	 * @param string $value
	 * @return string SQL fragment
	 */
	public function contains($column, $value)
	{
		return $this->_comparison($column, 'LIKE', '%'.$this->_escapeLike($value).'%');
	}

	/**
	 * Prefix match; see {@see e_db_expr::contains()} for wildcard handling.
	 *
	 * @param string $column
	 * @param string $value
	 * @return string SQL fragment
	 */
	public function startsWith($column, $value)
	{
		return $this->_comparison($column, 'LIKE', $this->_escapeLike($value).'%');
	}

	/**
	 * Suffix match; see {@see e_db_expr::contains()} for wildcard handling.
	 *
	 * @param string $column
	 * @param string $value
	 * @return string SQL fragment
	 */
	public function endsWith($column, $value)
	{
		return $this->_comparison($column, 'LIKE', '%'.$this->_escapeLike($value));
	}

	/**
	 * Regular-expression match with the whole pattern bound as one value.
	 * The operator spelling comes from the platform.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return string SQL fragment
	 */
	public function regexp($column, $pattern)
	{
		return $this->_comparison($column, $this->qb->getPlatform()->getRegexpOperator(), $pattern);
	}

	/**
	 * `column` IS NULL
	 *
	 * @param string $column
	 * @return string SQL fragment
	 */
	public function isNull($column)
	{
		return $this->qb->quoteColumn($column).' IS NULL';
	}

	/**
	 * `column` IS NOT NULL
	 *
	 * @param string $column
	 * @return string SQL fragment
	 */
	public function isNotNull($column)
	{
		return $this->qb->quoteColumn($column).' IS NOT NULL';
	}

	/**
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return string
	 */
	private function _comparison($column, $operator, $value)
	{
		return $this->qb->quoteColumn($column).' '.$operator.' '.$this->qb->createNamedParameter($value);
	}

	/**
	 * @param string $column
	 * @param array $values
	 * @param string $operator 'IN' or 'NOT IN'
	 * @param string $emptyResult predicate to emit for an empty list
	 * @return string
	 */
	private function _inList($column, array $values, $operator, $emptyResult)
	{
		$quoted = $this->qb->quoteColumn($column); // validate even when the list is empty

		if(count($values) === 0)
		{
			return $emptyResult;
		}

		$placeholders = array();

		foreach($values as $value)
		{
			$placeholders[] = $this->qb->createNamedParameter($value);
		}

		return $quoted.' '.$operator.' ('.implode(', ', $placeholders).')';
	}

	/**
	 * @param string $value
	 * @return string $value with LIKE metacharacters escaped
	 */
	private function _escapeLike($value)
	{
		return addcslashes((string) $value, '%_\\');
	}
}
