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
 * $name = $sql->createQueryBuilder()
 *     ->select('user_name')
 *     ->from('user')
 *     ->where('user_id', $id)
 *     ->fetchOne();
 * </code>
 *
 * Conditions accumulate: each {@see e_db_query::where()} (and
 * {@see e_db_query::having()}) is ANDed onto the clause and
 * {@see e_db_query::orWhere()} ORs onto it; there is no implicit reset, so to
 * start over build a new query. A condition may be written as a bound value
 * form (where('col', $value), or where('col', '>=', $value) with an operator),
 * as a column => value array, as an {@see e_db_expr} fragment, as a closure for
 * a parenthesised sub-group, or, only when the builder cannot express it, as a
 * hand-written string.
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
	const TYPE_REPLACE = 4;
	const TYPE_UPSERT = 5;

	/** @var e_db */
	private $db;

	/** @var e_db_platform */
	private $platform;

	/** @var int one of the TYPE_* constants */
	private $type = self::TYPE_SELECT;

	/** @var string[] SELECT expressions, already quoted where identifier-shaped */
	private $select = array();

	/** @var bool whether to emit SELECT DISTINCT */
	private $distinct = false;

	/** @var string|null logical table name (no '#', no prefix) */
	private $table = null;

	/** @var string|null alias for the FROM table */
	private $alias = null;

	/** @var string|null pre-built "(sub-select)" used as the FROM source */
	private $fromSub = null;

	/** @var string|null alias for the FROM sub-select */
	private $fromSubAlias = null;

	/**
	 * @var array[] queued joins. Each entry is
	 *      array('type', 'table', 'alias', 'condition'); 'table' may instead be
	 *      a pre-built "(sub-select)" expression (see {@see e_db_query::joinSub()})
	 *      and 'condition' is null for a CROSS JOIN.
	 */
	private $join = array();

	/** @var array[] WHERE entries: array('conjunction' => 'AND'|'OR', 'sql' => string) */
	private $where = array();

	/** @var string[] GROUP BY expressions */
	private $groupBy = array();

	/** @var array[] HAVING entries: array('conjunction' => 'AND'|'OR', 'sql' => string) */
	private $having = array();

	/** @var string[] canonical ORDER BY fragments, e.g. "`col` DESC" */
	private $orderBy = array();

	/** @var int|null OFFSET */
	private $firstResult = null;

	/** @var int|null LIMIT */
	private $maxResults = null;

	/** @var array quoted column => placeholder, for a single INSERT/UPDATE/REPLACE row */
	private $set = array();

	/** @var array[] additional INSERT/UPSERT rows, each quoted column => placeholder */
	private $rows = array();

	/** @var string '' or 'IGNORE', the INSERT modifier */
	private $insertModifier = '';

	/** @var string|null compiled SELECT for INSERT ... SELECT */
	private $insertSelect = null;

	/** @var string[] quoted target columns for INSERT ... SELECT */
	private $insertSelectColumns = array();

	/** @var string[] "quoted column = value-reference" assignments for UPSERT */
	private $upsertUpdate = array();

	/** @var string trailing lock clause for SELECT, e.g. ' FOR UPDATE' */
	private $lock = '';

	/** @var array[] queued UNIONs: array('all' => bool, 'sql' => string) */
	private $unions = array();

	/** @var array placeholder name => value, in the {@see e_db::execute()} shape */
	private $params = array();

	/** @var int placeholder name counter */
	private $paramCounter = 0;

	/**
	 * @var e_db_query|null when set, parameter creation delegates here so a
	 *      sub-builder (closure group, sub-query, UNION arm) shares one counter
	 *      and one parameter map with its parent.
	 */
	private $paramOwner = null;

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
		if($this->paramOwner !== null)
		{
			return $this->paramOwner->createNamedParameter($value, $type);
		}

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
	 * Add more columns to the SELECT list without clearing it; same quoting
	 * rules as {@see e_db_query::select()}.
	 *
	 * @param string|array $columns As multiple string arguments or one array.
	 * @return e_db_query $this
	 */
	public function addSelect($columns = '*')
	{
		$args = is_array($columns) ? $columns : func_get_args();

		foreach($args as $column)
		{
			$this->select[] = $this->_quoteExpression($column);
		}

		return $this;
	}

	/**
	 * Toggle SELECT DISTINCT.
	 *
	 * @param bool $distinct
	 * @return e_db_query $this
	 */
	public function distinct($distinct = true)
	{
		$this->distinct = (bool) $distinct;

		return $this;
	}

	/**
	 * Start a SELECT whose column list is a single developer-authored
	 * expression, taken verbatim. {@see e_db_query::select()} already keeps
	 * non-identifier expressions as-is; this is sugar that documents the intent
	 * and must never receive user input.
	 *
	 * @param string $expression Raw SELECT list.
	 * @return e_db_query $this
	 */
	public function selectRaw($expression)
	{
		$this->type = self::TYPE_SELECT;
		$this->select = array((string) $expression);

		return $this;
	}

	/**
	 * Add a scalar sub-query to the SELECT list as "(SELECT ...) AS alias"; see
	 * {@see e_db_query::fromSub()} for how the sub-query is supplied.
	 *
	 * @param Closure|e_db_query $query Sub-query source.
	 * @param string $alias Column alias; validated and quoted.
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when the alias fails validation.
	 */
	public function selectSub($query, $alias)
	{
		$this->type = self::TYPE_SELECT;
		$this->select[] = $this->_subQuery($query).' AS '.$this->_quotedAlias($alias);

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
		$this->fromSub = null;
		$this->fromSubAlias = null;

		return $this;
	}

	/**
	 * Select from a derived table (sub-query) instead of a named table.
	 *
	 * <code>
	 * $qb->select('*')->fromSub(function (e_db_query $sub) {
	 *     $sub->select('user_class', 'COUNT(*) AS cnt')->from('user')->groupBy('user_class');
	 * }, 'counts');
	 * </code>
	 *
	 * @param Closure|e_db_query $query Closure receiving a fresh builder, or a
	 *                           builder made with {@see e_db_query::newSubQuery()}.
	 * @param string $alias Alias for the derived table; validated and quoted.
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when the alias fails validation.
	 */
	public function fromSub($query, $alias)
	{
		$this->type = self::TYPE_SELECT;
		$this->fromSub = $this->_subQuery($query);
		$this->fromSubAlias = $alias;
		$this->table = null;
		$this->alias = null;

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
	 * INNER JOIN; an explicit alias of {@see e_db_query::join()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @param string $alias Alias for the joined table.
	 * @param string $condition ON condition.
	 * @return e_db_query $this
	 */
	public function innerJoin($table, $alias, $condition)
	{
		return $this->_join('INNER', $table, $alias, $condition);
	}

	/**
	 * RIGHT JOIN another table; see {@see e_db_query::join()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @param string $alias Alias for the joined table.
	 * @param string $condition ON condition.
	 * @return e_db_query $this
	 */
	public function rightJoin($table, $alias, $condition)
	{
		return $this->_join('RIGHT', $table, $alias, $condition);
	}

	/**
	 * CROSS JOIN another table; there is no ON condition.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @param string|null $alias Optional alias for the joined table.
	 * @return e_db_query $this
	 */
	public function crossJoin($table, $alias = null)
	{
		return $this->_join('CROSS', $table, $alias, null);
	}

	/**
	 * INNER JOIN a derived table (sub-query); see {@see e_db_query::fromSub()}
	 * for how the sub-query is supplied.
	 *
	 * @param Closure|e_db_query $query Sub-query source.
	 * @param string $alias Alias for the derived table; validated and quoted.
	 * @param string $condition ON condition.
	 * @return e_db_query $this
	 */
	public function joinSub($query, $alias, $condition)
	{
		return $this->_joinSub('INNER', $query, $alias, $condition);
	}

	/**
	 * LEFT JOIN a derived table (sub-query); see {@see e_db_query::joinSub()}.
	 *
	 * @param Closure|e_db_query $query Sub-query source.
	 * @param string $alias Alias for the derived table; validated and quoted.
	 * @param string $condition ON condition.
	 * @return e_db_query $this
	 */
	public function leftJoinSub($query, $alias, $condition)
	{
		return $this->_joinSub('LEFT', $query, $alias, $condition);
	}

	/**
	 * AND a condition onto the WHERE clause. A condition may be:
	 * <ul>
	 *   <li>a bound value comparison, where('user_id', $id) or, with an
	 *       operator, where('user_join', '>=', $since);</li>
	 *   <li>a column => value array, each pair ANDed and every value bound:
	 *       where(array('user_class' => 1, 'user_ban' => 0));</li>
	 *   <li>a list of array(column, value) or array(column, operator, value)
	 *       tuples;</li>
	 *   <li>a closure receiving a fresh builder, compiled as a parenthesised
	 *       sub-group: where(function (e_db_query $q) { ... });</li>
	 *   <li>an {@see e_db_expr} fragment, or a hand-written string for the rare
	 *       case the builder cannot express (developer SQL, never user input).</li>
	 * </ul>
	 * The operator in the value forms is checked against a fixed allowlist,
	 * column names are validated, and values are always bound.
	 *
	 * @param mixed ...$args
	 * @return e_db_query $this
	 * @throws InvalidArgumentException on an unknown operator or invalid identifier.
	 */
	public function where(...$args)
	{
		return $this->_addPredicate('where', 'AND', $args);
	}

	/**
	 * OR a condition onto the WHERE clause; takes the same forms as
	 * {@see e_db_query::where()}.
	 *
	 * @param mixed ...$args
	 * @return e_db_query $this
	 */
	public function orWhere(...$args)
	{
		return $this->_addPredicate('where', 'OR', $args);
	}

	/**
	 * AND a condition onto the WHERE clause; an explicit alias of
	 * {@see e_db_query::where()} for readers who prefer to spell out the
	 * conjunction.
	 *
	 * @param mixed ...$args
	 * @return e_db_query $this
	 */
	public function andWhere(...$args)
	{
		return $this->_addPredicate('where', 'AND', $args);
	}

	/**
	 * AND a "column IN (...)" condition with every value bound. The values may
	 * be an array, or a sub-query (closure or {@see e_db_query::newSubQuery()}
	 * builder) for "column IN (SELECT ...)". An empty array compiles to the
	 * always-false predicate 1=0.
	 *
	 * @param string $column
	 * @param array|Closure|e_db_query $values
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function whereIn($column, $values)
	{
		return $this->_appendWhere('AND', $this->_inPredicate($column, $values, 'IN', '1=0'));
	}

	/**
	 * OR form of {@see e_db_query::whereIn()}.
	 *
	 * @param string $column
	 * @param array|Closure|e_db_query $values
	 * @return e_db_query $this
	 */
	public function orWhereIn($column, $values)
	{
		return $this->_appendWhere('OR', $this->_inPredicate($column, $values, 'IN', '1=0'));
	}

	/**
	 * AND a "column NOT IN (...)" condition; see {@see e_db_query::whereIn()}.
	 * An empty array compiles to the always-true predicate 1=1.
	 *
	 * @param string $column
	 * @param array|Closure|e_db_query $values
	 * @return e_db_query $this
	 */
	public function whereNotIn($column, $values)
	{
		return $this->_appendWhere('AND', $this->_inPredicate($column, $values, 'NOT IN', '1=1'));
	}

	/**
	 * OR form of {@see e_db_query::whereNotIn()}.
	 *
	 * @param string $column
	 * @param array|Closure|e_db_query $values
	 * @return e_db_query $this
	 */
	public function orWhereNotIn($column, $values)
	{
		return $this->_appendWhere('OR', $this->_inPredicate($column, $values, 'NOT IN', '1=1'));
	}

	/**
	 * AND "column IS NULL".
	 *
	 * @param string $column
	 * @return e_db_query $this
	 */
	public function whereNull($column)
	{
		return $this->_appendWhere('AND', $this->expr()->isNull($column));
	}

	/**
	 * OR "column IS NULL".
	 *
	 * @param string $column
	 * @return e_db_query $this
	 */
	public function orWhereNull($column)
	{
		return $this->_appendWhere('OR', $this->expr()->isNull($column));
	}

	/**
	 * AND "column IS NOT NULL".
	 *
	 * @param string $column
	 * @return e_db_query $this
	 */
	public function whereNotNull($column)
	{
		return $this->_appendWhere('AND', $this->expr()->isNotNull($column));
	}

	/**
	 * OR "column IS NOT NULL".
	 *
	 * @param string $column
	 * @return e_db_query $this
	 */
	public function orWhereNotNull($column)
	{
		return $this->_appendWhere('OR', $this->expr()->isNotNull($column));
	}

	/**
	 * AND "column BETWEEN :min AND :max", both bounds bound.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return e_db_query $this
	 */
	public function whereBetween($column, $min, $max)
	{
		return $this->_appendWhere('AND', $this->expr()->between($column, $min, $max));
	}

	/**
	 * OR form of {@see e_db_query::whereBetween()}.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return e_db_query $this
	 */
	public function orWhereBetween($column, $min, $max)
	{
		return $this->_appendWhere('OR', $this->expr()->between($column, $min, $max));
	}

	/**
	 * AND "column NOT BETWEEN :min AND :max".
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return e_db_query $this
	 */
	public function whereNotBetween($column, $min, $max)
	{
		return $this->_appendWhere('AND', $this->expr()->notBetween($column, $min, $max));
	}

	/**
	 * OR form of {@see e_db_query::whereNotBetween()}.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return e_db_query $this
	 */
	public function orWhereNotBetween($column, $min, $max)
	{
		return $this->_appendWhere('OR', $this->expr()->notBetween($column, $min, $max));
	}

	/**
	 * AND a comparison between two columns, e.g. whereColumn('a', '<', 'b'), or
	 * whereColumn('a', 'b') for equality. Both sides are validated identifiers
	 * and nothing is bound, so neither may carry user input.
	 *
	 * @param string $first
	 * @param string $operator Operator, or the second column when $second is omitted.
	 * @param string|null $second Second column.
	 * @return e_db_query $this
	 */
	public function whereColumn($first, $operator, $second = null)
	{
		return $this->_appendWhere('AND', $this->expr()->compareColumns($first, $operator, $second));
	}

	/**
	 * OR form of {@see e_db_query::whereColumn()}.
	 *
	 * @param string $first
	 * @param string $operator
	 * @param string|null $second
	 * @return e_db_query $this
	 */
	public function orWhereColumn($first, $operator, $second = null)
	{
		return $this->_appendWhere('OR', $this->expr()->compareColumns($first, $operator, $second));
	}

	/**
	 * AND "column LIKE :pattern"; the pattern is bound verbatim, so the caller
	 * controls the % and _ wildcards. See {@see e_db_expr::contains()} for
	 * matching a plain substring.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return e_db_query $this
	 */
	public function whereLike($column, $pattern)
	{
		return $this->_appendWhere('AND', $this->expr()->like($column, $pattern));
	}

	/**
	 * OR form of {@see e_db_query::whereLike()}.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return e_db_query $this
	 */
	public function orWhereLike($column, $pattern)
	{
		return $this->_appendWhere('OR', $this->expr()->like($column, $pattern));
	}

	/**
	 * AND "column NOT LIKE :pattern"; see {@see e_db_query::whereLike()}.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return e_db_query $this
	 */
	public function whereNotLike($column, $pattern)
	{
		return $this->_appendWhere('AND', $this->expr()->notLike($column, $pattern));
	}

	/**
	 * OR form of {@see e_db_query::whereNotLike()}.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return e_db_query $this
	 */
	public function orWhereNotLike($column, $pattern)
	{
		return $this->_appendWhere('OR', $this->expr()->notLike($column, $pattern));
	}

	/**
	 * AND "EXISTS (sub-query)"; see {@see e_db_query::fromSub()} for how the
	 * sub-query is supplied.
	 *
	 * @param Closure|e_db_query $query
	 * @return e_db_query $this
	 */
	public function whereExists($query)
	{
		return $this->_appendWhere('AND', 'EXISTS '.$this->_subQuery($query));
	}

	/**
	 * OR form of {@see e_db_query::whereExists()}.
	 *
	 * @param Closure|e_db_query $query
	 * @return e_db_query $this
	 */
	public function orWhereExists($query)
	{
		return $this->_appendWhere('OR', 'EXISTS '.$this->_subQuery($query));
	}

	/**
	 * AND "NOT EXISTS (sub-query)".
	 *
	 * @param Closure|e_db_query $query
	 * @return e_db_query $this
	 */
	public function whereNotExists($query)
	{
		return $this->_appendWhere('AND', 'NOT EXISTS '.$this->_subQuery($query));
	}

	/**
	 * OR form of {@see e_db_query::whereNotExists()}.
	 *
	 * @param Closure|e_db_query $query
	 * @return e_db_query $this
	 */
	public function orWhereNotExists($query)
	{
		return $this->_appendWhere('OR', 'NOT EXISTS '.$this->_subQuery($query));
	}

	/**
	 * AND a negated parenthesised sub-group, NOT (...), built from a closure.
	 *
	 * @param Closure $callback Receives a fresh builder.
	 * @return e_db_query $this
	 */
	public function whereNot($callback)
	{
		return $this->_appendWhere('AND', 'NOT ('.$this->_buildGroup($callback).')');
	}

	/**
	 * OR form of {@see e_db_query::whereNot()}.
	 *
	 * @param Closure $callback
	 * @return e_db_query $this
	 */
	public function orWhereNot($callback)
	{
		return $this->_appendWhere('OR', 'NOT ('.$this->_buildGroup($callback).')');
	}

	/**
	 * AND a comparison against the date part of a column, e.g.
	 * whereDate('created', '>=', '2026-01-01') or whereDate('created', $day).
	 * The value is bound; the date function spelling comes from the dialect.
	 *
	 * @param string $column
	 * @param string $operator Operator, or the value when $value is omitted.
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function whereDate($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_datePredicate('date', $column, $operator, $value));
	}

	/**
	 * OR form of {@see e_db_query::whereDate()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function orWhereDate($column, $operator, $value = null)
	{
		return $this->_appendWhere('OR', $this->_datePredicate('date', $column, $operator, $value));
	}

	/**
	 * AND a comparison against the year part of a column; see
	 * {@see e_db_query::whereDate()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function whereYear($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_datePredicate('year', $column, $operator, $value));
	}

	/**
	 * OR form of {@see e_db_query::whereYear()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function orWhereYear($column, $operator, $value = null)
	{
		return $this->_appendWhere('OR', $this->_datePredicate('year', $column, $operator, $value));
	}

	/**
	 * AND a comparison against the month part of a column; see
	 * {@see e_db_query::whereDate()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function whereMonth($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_datePredicate('month', $column, $operator, $value));
	}

	/**
	 * OR form of {@see e_db_query::whereMonth()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function orWhereMonth($column, $operator, $value = null)
	{
		return $this->_appendWhere('OR', $this->_datePredicate('month', $column, $operator, $value));
	}

	/**
	 * AND a comparison against the day part of a column; see
	 * {@see e_db_query::whereDate()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function whereDay($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_datePredicate('day', $column, $operator, $value));
	}

	/**
	 * OR form of {@see e_db_query::whereDay()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function orWhereDay($column, $operator, $value = null)
	{
		return $this->_appendWhere('OR', $this->_datePredicate('day', $column, $operator, $value));
	}

	/**
	 * AND a comparison against the time part of a column; see
	 * {@see e_db_query::whereDate()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function whereTime($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_datePredicate('time', $column, $operator, $value));
	}

	/**
	 * OR form of {@see e_db_query::whereTime()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function orWhereTime($column, $operator, $value = null)
	{
		return $this->_appendWhere('OR', $this->_datePredicate('time', $column, $operator, $value));
	}

	/**
	 * AND "the JSON column contains $value" (the value is JSON-encoded and
	 * bound). The function spelling comes from the dialect.
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function whereJsonContains($column, $value)
	{
		return $this->_appendWhere('AND', $this->_jsonContains($column, $value, false));
	}

	/**
	 * OR form of {@see e_db_query::whereJsonContains()}.
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function orWhereJsonContains($column, $value)
	{
		return $this->_appendWhere('OR', $this->_jsonContains($column, $value, false));
	}

	/**
	 * AND "the JSON column does not contain $value".
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function whereJsonDoesntContain($column, $value)
	{
		return $this->_appendWhere('AND', $this->_jsonContains($column, $value, true));
	}

	/**
	 * OR form of {@see e_db_query::whereJsonDoesntContain()}.
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function orWhereJsonDoesntContain($column, $value)
	{
		return $this->_appendWhere('OR', $this->_jsonContains($column, $value, true));
	}

	/**
	 * AND "the JSON column contains the path $path" (e.g. '$.key'); the path is
	 * bound.
	 *
	 * @param string $column
	 * @param string $path
	 * @return e_db_query $this
	 */
	public function whereJsonContainsKey($column, $path)
	{
		return $this->_appendWhere('AND', $this->_jsonContainsKey($column, $path, false));
	}

	/**
	 * OR form of {@see e_db_query::whereJsonContainsKey()}.
	 *
	 * @param string $column
	 * @param string $path
	 * @return e_db_query $this
	 */
	public function orWhereJsonContainsKey($column, $path)
	{
		return $this->_appendWhere('OR', $this->_jsonContainsKey($column, $path, false));
	}

	/**
	 * AND "the JSON column does not contain the path $path".
	 *
	 * @param string $column
	 * @param string $path
	 * @return e_db_query $this
	 */
	public function whereJsonDoesntContainKey($column, $path)
	{
		return $this->_appendWhere('AND', $this->_jsonContainsKey($column, $path, true));
	}

	/**
	 * OR form of {@see e_db_query::whereJsonDoesntContainKey()}.
	 *
	 * @param string $column
	 * @param string $path
	 * @return e_db_query $this
	 */
	public function orWhereJsonDoesntContainKey($column, $path)
	{
		return $this->_appendWhere('OR', $this->_jsonContainsKey($column, $path, true));
	}

	/**
	 * AND a comparison against a JSON array/object length, e.g.
	 * whereJsonLength('tags', '>=', 3) or whereJsonLength('tags', 0). The value
	 * is bound.
	 *
	 * @param string $column
	 * @param string $operator Operator, or the value when $value is omitted.
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function whereJsonLength($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_jsonLength($column, $operator, $value));
	}

	/**
	 * OR form of {@see e_db_query::whereJsonLength()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return e_db_query $this
	 */
	public function orWhereJsonLength($column, $operator, $value = null)
	{
		return $this->_appendWhere('OR', $this->_jsonLength($column, $operator, $value));
	}

	/**
	 * AND a full-text search over one or more columns; the search terms are
	 * bound and the predicate spelling comes from the dialect.
	 *
	 * @param string|array $columns One column, or a list of columns.
	 * @param string $value Search terms.
	 * @return e_db_query $this
	 */
	public function whereFullText($columns, $value)
	{
		return $this->_appendWhere('AND', $this->_fullText($columns, $value));
	}

	/**
	 * OR form of {@see e_db_query::whereFullText()}.
	 *
	 * @param string|array $columns
	 * @param string $value
	 * @return e_db_query $this
	 */
	public function orWhereFullText($columns, $value)
	{
		return $this->_appendWhere('OR', $this->_fullText($columns, $value));
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
	 * Add more columns to the GROUP BY list without clearing it.
	 *
	 * @param string|array $columns As multiple string arguments or one array.
	 * @return e_db_query $this
	 */
	public function addGroupBy($columns)
	{
		$args = is_array($columns) ? $columns : func_get_args();

		foreach($args as $column)
		{
			$this->groupBy[] = $this->_quoteExpression($column);
		}

		return $this;
	}

	/**
	 * AND a condition onto the HAVING clause; takes the same forms as
	 * {@see e_db_query::where()}.
	 *
	 * @param mixed ...$args
	 * @return e_db_query $this
	 */
	public function having(...$args)
	{
		return $this->_addPredicate('having', 'AND', $args);
	}

	/**
	 * OR a condition onto the HAVING clause.
	 *
	 * @param mixed ...$args
	 * @return e_db_query $this
	 */
	public function orHaving(...$args)
	{
		return $this->_addPredicate('having', 'OR', $args);
	}

	/**
	 * AND a condition onto the HAVING clause; an explicit alias of
	 * {@see e_db_query::having()}.
	 *
	 * @param mixed ...$args
	 * @return e_db_query $this
	 */
	public function andHaving(...$args)
	{
		return $this->_addPredicate('having', 'AND', $args);
	}

	/**
	 * AND a hand-written HAVING fragment (developer SQL, never user input;
	 * bind any values with {@see e_db_query::createNamedParameter()}).
	 *
	 * @param string $fragment
	 * @return e_db_query $this
	 */
	public function havingRaw($fragment)
	{
		return $this->_appendHaving('AND', (string) $fragment);
	}

	/**
	 * OR form of {@see e_db_query::havingRaw()}.
	 *
	 * @param string $fragment
	 * @return e_db_query $this
	 */
	public function orHavingRaw($fragment)
	{
		return $this->_appendHaving('OR', (string) $fragment);
	}

	/**
	 * AND "column BETWEEN :min AND :max" onto the HAVING clause, both bounds bound.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return e_db_query $this
	 */
	public function havingBetween($column, $min, $max)
	{
		return $this->_appendHaving('AND', $this->expr()->between($column, $min, $max));
	}

	/**
	 * OR form of {@see e_db_query::havingBetween()}.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return e_db_query $this
	 */
	public function orHavingBetween($column, $min, $max)
	{
		return $this->_appendHaving('OR', $this->expr()->between($column, $min, $max));
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
	 * Append a descending ordering; shorthand for addOrderBy($column, 'DESC').
	 *
	 * @param string $column
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when the column fails validation.
	 */
	public function orderByDesc($column)
	{
		return $this->addOrderBy($column, 'DESC');
	}

	/**
	 * Append a descending ordering ("most recent first"); shorthand for
	 * addOrderBy($column, 'DESC').
	 *
	 * @param string $column
	 * @return e_db_query $this
	 */
	public function latest($column)
	{
		return $this->addOrderBy($column, 'DESC');
	}

	/**
	 * Append an ascending ordering ("oldest first"); shorthand for
	 * addOrderBy($column, 'ASC').
	 *
	 * @param string $column
	 * @return e_db_query $this
	 */
	public function oldest($column)
	{
		return $this->addOrderBy($column, 'ASC');
	}

	/**
	 * Append a random ordering; the dialect spells the function.
	 *
	 * @return e_db_query $this
	 */
	public function inRandomOrder()
	{
		$this->orderBy[] = $this->platform->getRandomFunction();

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
	 * Alias of {@see e_db_query::setMaxResults()} (LIMIT).
	 *
	 * @param int|null $limit
	 * @return e_db_query $this
	 */
	public function limit($limit)
	{
		return $this->setMaxResults($limit);
	}

	/**
	 * Alias of {@see e_db_query::setMaxResults()} (LIMIT).
	 *
	 * @param int|null $limit
	 * @return e_db_query $this
	 */
	public function take($limit)
	{
		return $this->setMaxResults($limit);
	}

	/**
	 * Alias of {@see e_db_query::setFirstResult()} (OFFSET).
	 *
	 * @param int|null $offset
	 * @return e_db_query $this
	 */
	public function offset($offset)
	{
		return $this->setFirstResult($offset);
	}

	/**
	 * Alias of {@see e_db_query::setFirstResult()} (OFFSET).
	 *
	 * @param int|null $offset
	 * @return e_db_query $this
	 */
	public function skip($offset)
	{
		return $this->setFirstResult($offset);
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
	 * Start an "INSERT IGNORE" query: rows that would violate a primary or
	 * unique key are skipped rather than raising an error. The dialect spells
	 * the modifier; the call site stays portable.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @return e_db_query $this
	 */
	public function insertOrIgnore($table)
	{
		$this->type = self::TYPE_INSERT;
		$this->table = $table;
		$this->insertModifier = 'IGNORE';

		return $this;
	}

	/**
	 * Set the values for INSERT or REPLACE. Pass one column => value map for a
	 * single row, or a list of such maps for a multi-row INSERT; every value is
	 * bound.
	 *
	 * <code>
	 * $qb->insert('tmp')->values(array(
	 *     array('tmp_ip' => '10.0.0.1', 'tmp_info' => 'a'),
	 *     array('tmp_ip' => '10.0.0.2', 'tmp_info' => 'b'),
	 * ));
	 * </code>
	 *
	 * @param array $values column => value, or a list of column => value rows
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when a column name fails validation.
	 */
	public function values($values)
	{
		if($this->_isListOfRows($values))
		{
			foreach($values as $row)
			{
				$this->_addRow($row);
			}

			return $this;
		}

		foreach($values as $column => $value)
		{
			$this->set($column, $value);
		}

		return $this;
	}

	/**
	 * Populate this table from a SELECT (INSERT ... SELECT). Name the table
	 * first with {@see e_db_query::insert()} or
	 * {@see e_db_query::insertOrIgnore()}; pass the columns to fill, or an empty
	 * array for "INSERT INTO t SELECT ...".
	 *
	 * <code>
	 * $qb->insert('archive')->insertUsing(array('id', 'name'), function (e_db_query $s) {
	 *     $s->select('id', 'name')->from('live')->where('expired', 1);
	 * });
	 * </code>
	 *
	 * @param array $columns Target columns, or array() to insert every column.
	 * @param Closure|e_db_query $query Sub-query source.
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when a column name fails validation.
	 */
	public function insertUsing($columns, $query)
	{
		$this->type = self::TYPE_INSERT;

		$quoted = array();

		foreach((array) $columns as $column)
		{
			$quoted[] = $this->quoteColumn($column);
		}

		$this->insertSelectColumns = $quoted;
		$this->insertSelect = $this->_subQuerySql($query);

		return $this;
	}

	/**
	 * Insert one row and return the auto-increment id of the new row. Name the
	 * table first with {@see e_db_query::insert()}.
	 *
	 * @param array $values column => value
	 * @return int|string|bool the last insert id, or false on error.
	 * @throws InvalidArgumentException when a column name fails validation.
	 */
	public function insertGetId($values)
	{
		$this->values($values);

		if($this->execute() === false)
		{
			return false;
		}

		return $this->db->lastInsertId();
	}

	/**
	 * Start a REPLACE query: insert a row, replacing any existing row that has
	 * the same primary or unique key. Supply the row with
	 * {@see e_db_query::values()} (or {@see e_db_query::set()}); every value is
	 * bound. The dialect-specific statement is produced by the platform, so the
	 * call site stays portable.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @return e_db_query $this
	 */
	public function replace($table)
	{
		$this->type = self::TYPE_REPLACE;
		$this->table = $table;

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
	 * Queue one column assignment for UPDATE (or INSERT). The value is always
	 * bound; to assign a SQL expression such as "col + 1" instead of a value,
	 * use {@see e_db_query::setExpression()}.
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
	 * Queue a SQL expression as a column assignment for UPDATE, e.g.
	 * "user_visits = user_visits + 1". Unlike {@see e_db_query::set()}, the
	 * right-hand side is developer-authored SQL placed verbatim, so it must
	 * never contain user input; bind any values inside it with
	 * {@see e_db_query::createNamedParameter()}.
	 *
	 * <code>
	 * $qb->update('user')
	 *     ->setExpression('user_visits', 'user_visits + 1')
	 *     ->setExpression('user_score', 'user_score + '.$qb->createNamedParameter($delta))
	 *     ->where($qb->expr()->eq('user_id', $id))
	 *     ->execute();
	 * </code>
	 *
	 * @param string $column Assignment target; validated and quoted.
	 * @param string $expression Raw SQL expression for the right-hand side.
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function setExpression($column, $expression)
	{
		$this->set[$this->quoteColumn($column)] = (string) $expression;

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
	 * Insert one or more rows, updating the listed columns instead when a row
	 * would collide on a primary or unique key. Start by naming the table with
	 * {@see e_db_query::insert()}. Every value is bound and the dialect-specific
	 * statement is produced by the platform, so the call site stays portable.
	 *
	 * <code>
	 * $qb->insert('user')->upsert(
	 *     array('user_id' => 5, 'user_name' => 'Bob'),
	 *     'user_id',              // key(s) that decide a collision
	 *     array('user_name')      // columns to refresh on collision
	 * );
	 * </code>
	 *
	 * @param array $values One column => value row, or a list of such rows.
	 * @param string|array $uniqueBy Column(s) identifying a collision; validated.
	 * @param array|null $update Columns to update on collision; when null, every
	 *                   inserted column except those in $uniqueBy.
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when no table is set or an identifier fails validation.
	 */
	public function upsert($values, $uniqueBy, $update = null)
	{
		if($this->table === null)
		{
			throw new InvalidArgumentException('upsert() needs a table; start with insert($table).');
		}

		$this->type = self::TYPE_UPSERT;

		foreach((array) $uniqueBy as $column)
		{
			$this->quoteColumn($column); // validate, fail closed
		}

		if($this->_isListOfRows($values))
		{
			foreach($values as $row)
			{
				$this->_addRow($row);
			}

			$updateSource = array_keys($values[0]);
		}
		else
		{
			$this->_addRow($values);
			$updateSource = array_keys($values);
		}

		$updateColumns = ($update === null)
			? array_values(array_diff($updateSource, (array) $uniqueBy))
			: $update;

		$this->upsertUpdate = array();

		foreach($updateColumns as $column)
		{
			$quoted = $this->quoteColumn($column);
			$this->upsertUpdate[] = $quoted.' = '.$this->platform->getUpsertValueReference($quoted);
		}

		return $this;
	}

	/**
	 * Update the row matching $attributes, or insert one when none matches.
	 * Name the table first with a write statement such as
	 * {@see e_db_query::update()}. This issues a lookup then a write and is not
	 * atomic; prefer {@see e_db_query::upsert()} when a unique key exists.
	 *
	 * @param array $attributes column => value used to find and to seed the row
	 * @param array $values column => value applied on update (and on insert)
	 * @return int|bool affected rows, or 0 when an existing row needs no change.
	 * @throws InvalidArgumentException when no table is set.
	 */
	public function updateOrInsert($attributes, $values = array())
	{
		if($this->table === null)
		{
			throw new InvalidArgumentException('updateOrInsert() needs a table; start with a write statement naming one.');
		}

		$exists = new e_db_query($this->db, $this->platform);
		$exists->select('1')->from($this->table);

		foreach($attributes as $column => $value)
		{
			$exists->where($column, $value);
		}

		if($exists->setMaxResults(1)->fetchOne() !== null)
		{
			if(count($values) === 0)
			{
				return 0;
			}

			$update = new e_db_query($this->db, $this->platform);
			$update->update($this->table);

			foreach($values as $column => $value)
			{
				$update->set($column, $value);
			}

			foreach($attributes as $column => $value)
			{
				$update->where($column, $value);
			}

			return $update->execute();
		}

		$insert = new e_db_query($this->db, $this->platform);

		return $insert->insert($this->table)->values($attributes + $values)->execute();
	}

	/**
	 * Queue "column = column + :amount" for UPDATE; the amount is bound. Start
	 * with {@see e_db_query::update()} and add a {@see e_db_query::where()}.
	 *
	 * @param string $column
	 * @param int|float $amount
	 * @param array $extra Further column => value assignments, each bound.
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when an identifier fails validation.
	 */
	public function increment($column, $amount = 1, $extra = array())
	{
		return $this->_incDec($column, '+', $amount, $extra);
	}

	/**
	 * Queue "column = column - :amount" for UPDATE; see
	 * {@see e_db_query::increment()}.
	 *
	 * @param string $column
	 * @param int|float $amount
	 * @param array $extra Further column => value assignments, each bound.
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when an identifier fails validation.
	 */
	public function decrement($column, $amount = 1, $extra = array())
	{
		return $this->_incDec($column, '-', $amount, $extra);
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

			case self::TYPE_REPLACE:
				return $this->_compileReplace();

			case self::TYPE_UPSERT:
				return $this->_compileUpsert();

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
	 * Run a COUNT and return the result as an integer. Pass a column to count
	 * non-null values in it, or omit for COUNT(*).
	 *
	 * @param string $column Column name, or '*'.
	 * @return int
	 * @throws InvalidArgumentException when a column name fails validation.
	 */
	public function count($column = '*')
	{
		$arg = ($column === '*') ? '*' : $this->quoteColumn($column);

		return (int) $this->select('COUNT('.$arg.')')->fetchOne();
	}

	/**
	 * Run a MAX and return the scalar result.
	 *
	 * @param string $column
	 * @return mixed
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function max($column)
	{
		return $this->select('MAX('.$this->quoteColumn($column).')')->fetchOne();
	}

	/**
	 * Run a MIN and return the scalar result.
	 *
	 * @param string $column
	 * @return mixed
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function min($column)
	{
		return $this->select('MIN('.$this->quoteColumn($column).')')->fetchOne();
	}

	/**
	 * Run a SUM and return the scalar result.
	 *
	 * @param string $column
	 * @return mixed
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function sum($column)
	{
		return $this->select('SUM('.$this->quoteColumn($column).')')->fetchOne();
	}

	/**
	 * Run an AVG and return the scalar result.
	 *
	 * @param string $column
	 * @return mixed
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function avg($column)
	{
		return $this->select('AVG('.$this->quoteColumn($column).')')->fetchOne();
	}

	/**
	 * Acquire an exclusive write lock on the selected rows (e.g. FOR UPDATE).
	 * The dialect spells the clause.
	 *
	 * @return e_db_query $this
	 */
	public function lockForUpdate()
	{
		$this->lock = $this->platform->getForUpdateClause();

		return $this;
	}

	/**
	 * Acquire a shared read lock on the selected rows. The dialect spells the
	 * clause.
	 *
	 * @return e_db_query $this
	 */
	public function sharedLock()
	{
		$this->lock = $this->platform->getSharedLockClause();

		return $this;
	}

	/**
	 * Append a UNION arm. Build the arm with {@see e_db_query::newUnionQuery()}
	 * so it shares this query's bound-parameter numbering.
	 *
	 * @param e_db_query $query
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when the arm does not share parameters.
	 */
	public function union($query)
	{
		return $this->_addUnion($query, false);
	}

	/**
	 * Append a UNION ALL arm; see {@see e_db_query::union()}.
	 *
	 * @param e_db_query $query
	 * @return e_db_query $this
	 */
	public function unionAll($query)
	{
		return $this->_addUnion($query, true);
	}

	/**
	 * A fresh builder that shares this query's parameter numbering, for use as
	 * a UNION arm. See {@see e_db_query::union()}.
	 *
	 * @return e_db_query
	 */
	public function newUnionQuery()
	{
		return $this->newSubQuery();
	}

	/**
	 * A fresh builder that shares this query's parameter numbering, for use as
	 * a sub-query (e.g. {@see e_db_query::whereExists()},
	 * {@see e_db_query::whereIn()}, {@see e_db_query::fromSub()}).
	 *
	 * @return e_db_query
	 */
	public function newSubQuery()
	{
		$query = new e_db_query($this->db, $this->platform);
		$query->paramOwner = ($this->paramOwner !== null) ? $this->paramOwner : $this;

		return $query;
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
			$quoted = $this->db->quoteIdentifier((string) substr($expression, 0, -2));

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
	 * Queue a join whose source is a derived table (sub-query).
	 *
	 * @param string $type 'INNER' or 'LEFT'
	 * @param Closure|e_db_query $query
	 * @param string $alias
	 * @param string $condition
	 * @return e_db_query $this
	 */
	private function _joinSub($type, $query, $alias, $condition)
	{
		$this->join[] = array(
			'type'      => $type,
			'table'     => null,
			'expr'      => $this->_subQuery($query),
			'alias'     => $alias,
			'condition' => $condition,
		);

		return $this;
	}

	/**
	 * Build one WHERE/HAVING fragment from a where()/having() argument list.
	 *
	 * @param array $args
	 * @return string
	 */
	private function _addPredicate($clause, $conjunction, array $args)
	{
		$sql = $this->_buildPredicate($args);

		return ($clause === 'where')
			? $this->_appendWhere($conjunction, $sql)
			: $this->_appendHaving($conjunction, $sql);
	}

	/**
	 * @param array $args
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private function _buildPredicate(array $args)
	{
		$n = count($args);

		if($n === 1)
		{
			$arg = $args[0];

			if($arg instanceof Closure)
			{
				return $this->_buildGroup($arg);
			}

			if(is_array($arg))
			{
				return $this->_buildArrayPredicate($arg);
			}

			return (string) $arg;
		}

		if($n === 2)
		{
			return $this->expr()->eq($args[0], $args[1]);
		}

		if($n === 3)
		{
			return $this->expr()->comparison($args[0], $args[1], $args[2]);
		}

		throw new InvalidArgumentException('where()/having() take 1 to 3 arguments, got '.$n);
	}

	/**
	 * Build an ANDed fragment from a column => value array or a list of
	 * array(column, value) / array(column, operator, value) tuples.
	 *
	 * @param array $conditions
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private function _buildArrayPredicate(array $conditions)
	{
		if(count($conditions) === 0)
		{
			throw new InvalidArgumentException('Empty condition array.');
		}

		$parts = array();

		foreach($conditions as $key => $value)
		{
			if(is_int($key))
			{
				if(!is_array($value))
				{
					throw new InvalidArgumentException('A list-style condition must be array(column, value) or array(column, operator, value).');
				}

				$parts[] = (count($value) === 3)
					? $this->expr()->comparison($value[0], $value[1], $value[2])
					: $this->expr()->eq($value[0], $value[1]);
			}
			else
			{
				$parts[] = $this->expr()->eq($key, $value);
			}
		}

		return implode(' AND ', $parts);
	}

	/**
	 * Compile a closure into a parenthesised sub-group that shares this query's
	 * bound-parameter numbering.
	 *
	 * @param Closure $callback
	 * @return string
	 */
	private function _buildGroup(Closure $callback)
	{
		$sub = $this->newSubQuery();

		$callback($sub);

		$inner = $sub->_compilePredicateList($sub->where, '');

		return ($inner === '') ? '1=1' : $inner;
	}

	/**
	 * Build a "column IN/NOT IN (...)" fragment from an array or a sub-query.
	 *
	 * @param string $column
	 * @param array|Closure|e_db_query $values
	 * @param string $operator 'IN' or 'NOT IN'
	 * @param string $emptyResult predicate for an empty array
	 * @return string
	 */
	private function _inPredicate($column, $values, $operator, $emptyResult)
	{
		if($values instanceof Closure || $values instanceof e_db_query)
		{
			return $this->quoteColumn($column).' '.$operator.' '.$this->_subQuery($values);
		}

		$quoted = $this->quoteColumn($column); // validate even when the list is empty
		$values = (array) $values;

		if(count($values) === 0)
		{
			return $emptyResult;
		}

		$placeholders = array();

		foreach($values as $value)
		{
			$placeholders[] = $this->createNamedParameter($value);
		}

		return $quoted.' '.$operator.' ('.implode(', ', $placeholders).')';
	}

	/**
	 * Embed a sub-query as a parenthesised "(SELECT ...)" string. The sub-query
	 * shares this query's parameters, so its placeholders stay unique.
	 *
	 * @param Closure|e_db_query $query
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private function _subQuery($query)
	{
		return '('.$this->_subQuerySql($query).')';
	}

	/**
	 * Compile a sub-query to SQL without surrounding parentheses (used where the
	 * grammar forbids them, e.g. INSERT ... SELECT).
	 *
	 * @param Closure|e_db_query $query
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private function _subQuerySql($query)
	{
		if($query instanceof Closure)
		{
			$sub = $this->newSubQuery();
			$query($sub);

			return $sub->getSQL();
		}

		if($query instanceof e_db_query)
		{
			$owner = ($this->paramOwner !== null) ? $this->paramOwner : $this;

			if($query->paramOwner !== $owner)
			{
				throw new InvalidArgumentException('A sub-query builder must be created with newSubQuery() so it shares parameters.');
			}

			return $query->getSQL();
		}

		throw new InvalidArgumentException('Sub-query expects a Closure or a builder from newSubQuery().');
	}

	/**
	 * Build a "datePart(column) OP :value" predicate; OP defaults to '=' when
	 * $value is omitted.
	 *
	 * @param string $part 'date'|'year'|'month'|'day'|'time'
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private function _datePredicate($part, $column, $operator, $value)
	{
		if($value === null)
		{
			$value = $operator;
			$operator = '=';
		}

		$op = e_db_expr::operator($operator);

		if($op === 'IN' || $op === 'NOT IN')
		{
			throw new InvalidArgumentException('Date comparisons do not support IN/NOT IN.');
		}

		return $this->platform->compileDatePart($part, $this->quoteColumn($column))
			.' '.$op.' '.$this->createNamedParameter($value);
	}

	/**
	 * @param string $column
	 * @param mixed $value JSON-encoded before binding
	 * @param bool $negate
	 * @return string
	 */
	private function _jsonContains($column, $value, $negate)
	{
		$fragment = $this->platform->compileJsonContains(
			$this->quoteColumn($column),
			$this->createNamedParameter(json_encode($value))
		);

		return $negate ? 'NOT '.$fragment : $fragment;
	}

	/**
	 * @param string $column
	 * @param string $path
	 * @param bool $negate
	 * @return string
	 */
	private function _jsonContainsKey($column, $path, $negate)
	{
		$fragment = $this->platform->compileJsonContainsKey(
			$this->quoteColumn($column),
			$this->createNamedParameter($path)
		);

		return $negate ? 'NOT '.$fragment : $fragment;
	}

	/**
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private function _jsonLength($column, $operator, $value)
	{
		if($value === null)
		{
			$value = $operator;
			$operator = '=';
		}

		$op = e_db_expr::operator($operator);

		if($op === 'IN' || $op === 'NOT IN')
		{
			throw new InvalidArgumentException('JSON length comparisons do not support IN/NOT IN.');
		}

		return $this->platform->compileJsonLength($this->quoteColumn($column))
			.' '.$op.' '.$this->createNamedParameter($value);
	}

	/**
	 * @param string|array $columns
	 * @param string $value
	 * @return string
	 */
	private function _fullText($columns, $value)
	{
		$quoted = array();

		foreach((array) $columns as $column)
		{
			$quoted[] = $this->quoteColumn($column);
		}

		return $this->platform->compileFullText($quoted, $this->createNamedParameter($value));
	}

	/**
	 * @param e_db_query $query
	 * @param bool $all
	 * @return e_db_query $this
	 * @throws InvalidArgumentException
	 */
	private function _addUnion($query, $all)
	{
		if(!($query instanceof e_db_query))
		{
			throw new InvalidArgumentException('union() expects a builder from newUnionQuery().');
		}

		$owner = ($this->paramOwner !== null) ? $this->paramOwner : $this;

		if($query->paramOwner !== $owner)
		{
			throw new InvalidArgumentException('A UNION arm must be created with newUnionQuery() so it shares parameters.');
		}

		$this->unions[] = array('all' => (bool) $all, 'sql' => $query->getSQL());

		return $this;
	}

	/**
	 * Queue "column = column +/- :amount" plus any extra bound assignments.
	 *
	 * @param string $column
	 * @param string $sign '+' or '-'
	 * @param int|float $amount
	 * @param array $extra
	 * @return e_db_query $this
	 */
	private function _incDec($column, $sign, $amount, array $extra)
	{
		$this->setExpression($column, $this->quoteColumn($column).' '.$sign.' '.$this->createNamedParameter($amount));

		foreach($extra as $col => $value)
		{
			$this->set($col, $value);
		}

		return $this;
	}

	/**
	 * @param array $values
	 * @return bool whether $values is a list of rows (multi-row INSERT/UPSERT)
	 */
	private function _isListOfRows(array $values)
	{
		if(count($values) === 0)
		{
			return false;
		}

		reset($values);

		return is_int(key($values)) && is_array(current($values));
	}

	/**
	 * Validate, quote and bind one INSERT/UPSERT row.
	 *
	 * @param array $row column => value
	 * @return e_db_query $this
	 * @throws InvalidArgumentException when a column name fails validation.
	 */
	private function _addRow(array $row)
	{
		$compiled = array();

		foreach($row as $column => $value)
		{
			$compiled[$this->quoteColumn($column)] = $this->createNamedParameter($value);
		}

		$this->rows[] = $compiled;

		return $this;
	}

	/**
	 * The rows for an INSERT/UPSERT: the explicit multi-row list when present,
	 * otherwise the single row built with set()/values().
	 *
	 * @return array[]
	 */
	private function _insertRows()
	{
		if(count($this->rows) > 0)
		{
			return $this->rows;
		}

		if(count($this->set) > 0)
		{
			return array($this->set);
		}

		return array();
	}

	/**
	 * Normalise INSERT/UPSERT rows to a column list plus "(...)" VALUES tuples,
	 * failing closed when rows disagree on columns.
	 *
	 * @param array[] $rows
	 * @return array array($columns, $tuples)
	 * @throws InvalidArgumentException
	 */
	private function _valuesTuples(array $rows)
	{
		$columns = array_keys($rows[0]);
		$tuples = array();

		foreach($rows as $row)
		{
			$ordered = array();

			foreach($columns as $column)
			{
				if(!array_key_exists($column, $row))
				{
					throw new InvalidArgumentException('Multi-row insert rows must share the same columns.');
				}

				$ordered[] = $row[$column];
			}

			if(count($row) !== count($columns))
			{
				throw new InvalidArgumentException('Multi-row insert rows must share the same columns.');
			}

			$tuples[] = '('.implode(', ', $ordered).')';
		}

		return array($columns, $tuples);
	}

	/**
	 * Compile WHERE/HAVING entries with their AND/OR conjunctions, each wrapped
	 * in parentheses.
	 *
	 * @param array[] $list entries of array('conjunction', 'sql')
	 * @param string $prefix e.g. ' WHERE ' or ' HAVING '
	 * @return string
	 */
	private function _compilePredicateList(array $list, $prefix)
	{
		if(count($list) === 0)
		{
			return '';
		}

		$sql = $prefix;

		foreach($list as $i => $entry)
		{
			if($i > 0)
			{
				$sql .= ' '.$entry['conjunction'].' ';
			}

			$sql .= '('.$entry['sql'].')';
		}

		return $sql;
	}

	/**
	 * @param string $conjunction 'AND' or 'OR'
	 * @param string $sql
	 * @return e_db_query $this
	 */
	private function _appendWhere($conjunction, $sql)
	{
		$this->where[] = array('conjunction' => $conjunction, 'sql' => $sql);

		return $this;
	}

	/**
	 * @param string $conjunction 'AND' or 'OR'
	 * @param string $sql
	 * @return e_db_query $this
	 */
	private function _appendHaving($conjunction, $sql)
	{
		$this->having[] = array('conjunction' => $conjunction, 'sql' => $sql);

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
		if($this->fromSub !== null)
		{
			$source = $this->fromSub.' AS '.$this->_quotedAlias($this->fromSubAlias);
		}
		elseif($this->table !== null)
		{
			$source = $this->_quotedTable($this->table);

			if($this->alias !== null)
			{
				$source .= ' AS '.$this->_quotedAlias($this->alias);
			}
		}
		else
		{
			throw new InvalidArgumentException('No table set; call from() first.');
		}

		$sql = 'SELECT '.($this->distinct ? 'DISTINCT ' : '')
			.(count($this->select) === 0 ? '*' : implode(', ', $this->select));
		$sql .= ' FROM '.$source;

		foreach($this->join as $join)
		{
			$joinSource = isset($join['expr']) ? $join['expr'] : $this->_quotedTable($join['table']);
			$sql .= ' '.$join['type'].' JOIN '.$joinSource;

			if($join['alias'] !== null)
			{
				$sql .= ' AS '.$this->_quotedAlias($join['alias']);
			}

			if($join['condition'] !== null)
			{
				$sql .= ' ON '.$join['condition'];
			}
		}

		$sql .= $this->_compileWhere();

		if(count($this->groupBy) > 0)
		{
			$sql .= ' GROUP BY '.implode(', ', $this->groupBy);
		}

		$sql .= $this->_compilePredicateList($this->having, ' HAVING ');

		if(count($this->orderBy) > 0)
		{
			$sql .= ' ORDER BY '.implode(', ', $this->orderBy);
		}

		$sql .= $this->platform->getLimitClause($this->maxResults, $this->firstResult);
		$sql .= $this->lock;

		foreach($this->unions as $union)
		{
			$sql .= ($union['all'] ? ' UNION ALL ' : ' UNION ').$union['sql'];
		}

		return $sql;
	}

	/**
	 * @return string
	 */
	private function _compileInsert()
	{
		if($this->insertSelect !== null)
		{
			if($this->table === null)
			{
				throw new InvalidArgumentException('INSERT ... SELECT needs a table; call insert() with one.');
			}

			return $this->platform->compileInsertSelect(
				$this->_quotedTable($this->table),
				$this->insertSelectColumns,
				$this->insertSelect,
				$this->insertModifier
			);
		}

		$rows = $this->_insertRows();

		if($this->table === null || count($rows) === 0)
		{
			throw new InvalidArgumentException('INSERT needs a table and a non-empty values() map.');
		}

		list($columns, $tuples) = $this->_valuesTuples($rows);

		return $this->platform->compileInsert(
			$this->_quotedTable($this->table),
			$columns,
			$tuples,
			$this->insertModifier
		);
	}

	/**
	 * @return string
	 */
	private function _compileUpsert()
	{
		$rows = $this->_insertRows();

		if($this->table === null || count($rows) === 0 || count($this->upsertUpdate) === 0)
		{
			throw new InvalidArgumentException('UPSERT needs a table, a non-empty row, and at least one column to update.');
		}

		list($columns, $tuples) = $this->_valuesTuples($rows);

		return $this->platform->compileUpsert(
			$this->_quotedTable($this->table),
			$columns,
			$tuples,
			$this->upsertUpdate
		);
	}

	/**
	 * @return string
	 */
	private function _compileReplace()
	{
		if($this->table === null || count($this->set) === 0)
		{
			throw new InvalidArgumentException('REPLACE needs a table and a non-empty values() map.');
		}

		return $this->platform->compileReplace(
			$this->_quotedTable($this->table),
			array_keys($this->set),
			array_values($this->set)
		);
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
		return $this->_compilePredicateList($this->where, ' WHERE ');
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
	 * @var array Allowlisted comparison operators for {@see e_db_expr::comparison()}
	 *      and the value forms of where()/having(); '!=' normalises to '<>'.
	 *      Regular-expression matching is intentionally left to
	 *      {@see e_db_expr::regexp()}, which sources its operator from the platform.
	 */
	private static $operators = array(
		'='        => '=',
		'<>'       => '<>',
		'!='       => '<>',
		'<'        => '<',
		'<='       => '<=',
		'>'        => '>',
		'>='       => '>=',
		'LIKE'     => 'LIKE',
		'NOT LIKE' => 'NOT LIKE',
		'IN'       => 'IN',
		'NOT IN'   => 'NOT IN',
	);

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
	public function in($column, $values)
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
	public function notIn($column, $values)
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
	 * Generic "`column` OP :value" with OP checked against the operator
	 * allowlist ('!=' normalises to '<>'). For IN/NOT IN, $value is taken as a
	 * list and delegated to {@see e_db_expr::in()}/{@see e_db_expr::notIn()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return string SQL fragment
	 * @throws InvalidArgumentException on an unsupported operator.
	 */
	public function comparison($column, $operator, $value)
	{
		$op = self::operator($operator);

		if($op === 'IN')
		{
			return $this->in($column, (array) $value);
		}

		if($op === 'NOT IN')
		{
			return $this->notIn($column, (array) $value);
		}

		return $this->_comparison($column, $op, $value);
	}

	/**
	 * Normalise and validate a comparison operator against the allowlist
	 * ('!=' becomes '<>'); shared by the value forms of where()/having() and
	 * the date helpers.
	 *
	 * @param string $operator
	 * @return string canonical operator
	 * @throws InvalidArgumentException on an unsupported operator.
	 */
	public static function operator($operator)
	{
		$op = strtoupper(trim((string) $operator));

		if(!isset(self::$operators[$op]))
		{
			throw new InvalidArgumentException('Unsupported operator: '.$operator);
		}

		return self::$operators[$op];
	}

	/**
	 * `column` BETWEEN :min AND :max, both bounds bound.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return string SQL fragment
	 */
	public function between($column, $min, $max)
	{
		return $this->qb->quoteColumn($column).' BETWEEN '
			.$this->qb->createNamedParameter($min).' AND '.$this->qb->createNamedParameter($max);
	}

	/**
	 * `column` NOT BETWEEN :min AND :max.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return string SQL fragment
	 */
	public function notBetween($column, $min, $max)
	{
		return $this->qb->quoteColumn($column).' NOT BETWEEN '
			.$this->qb->createNamedParameter($min).' AND '.$this->qb->createNamedParameter($max);
	}

	/**
	 * `column` NOT LIKE :pattern, with $pattern bound verbatim; see
	 * {@see e_db_expr::like()}.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return string SQL fragment
	 */
	public function notLike($column, $pattern)
	{
		return $this->_comparison($column, 'NOT LIKE', $pattern);
	}

	/**
	 * Compare two columns, e.g. `a` < `b`. With two arguments the operator
	 * defaults to '='. Both sides are validated identifiers and nothing is
	 * bound, so neither may carry user input.
	 *
	 * @param string $first
	 * @param string $operator Operator, or the second column when $second is null.
	 * @param string|null $second
	 * @return string SQL fragment
	 * @throws InvalidArgumentException on an unsupported operator or invalid identifier.
	 */
	public function compareColumns($first, $operator, $second = null)
	{
		if($second === null)
		{
			$second = $operator;
			$operator = '=';
		}

		$op = self::operator($operator);

		if($op === 'IN' || $op === 'NOT IN')
		{
			throw new InvalidArgumentException('Unsupported column-comparison operator: '.$operator);
		}

		return $this->qb->quoteColumn($first).' '.$op.' '.$this->qb->quoteColumn($second);
	}

	/**
	 * Combine pre-built fragments with AND, each parenthesised. The fragments
	 * already carry their bound parameters, so this binds nothing itself.
	 *
	 * @param string ...$parts
	 * @return string SQL fragment
	 */
	public function andX(...$parts)
	{
		return '('.implode(') AND (', array_map('strval', $parts)).')';
	}

	/**
	 * Combine pre-built fragments with OR; see {@see e_db_expr::andX()}.
	 *
	 * @param string ...$parts
	 * @return string SQL fragment
	 */
	public function orX(...$parts)
	{
		return '('.implode(') OR (', array_map('strval', $parts)).')';
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
