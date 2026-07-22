<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database;

use Closure;
use e107;
use e107\Database\Platform\PlatformInterface;
use InvalidArgumentException;

/**
 * Fluent SQL query builder bound to an e107 database connection.
 *
 * Create one with {@see ConnectionInterface::createQueryBuilder()}. The builder compiles to
 * SQL with bound :named placeholders and runs through {@see ConnectionInterface::execute()},
 * so no value ever becomes SQL text. Table names are logical: no '#' marker
 * and no database prefix; both the prefix and multi-language routing are
 * applied at compile time via {@see ConnectionInterface::resolveTableName()}. Identifier
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
 * Conditions accumulate: each {@see QueryBuilder::where()} (and
 * {@see QueryBuilder::having()}) is ANDed onto the clause and
 * {@see QueryBuilder::orWhere()} ORs onto it; there is no implicit reset, so to
 * start over build a new query. A condition may be written as a bound value
 * form (where('col', $value), or where('col', '>=', $value) with an operator),
 * as a column => value array, as an {@see ExpressionBuilder} fragment, as a closure for
 * a parenthesised sub-group, or, only when the builder cannot express it, as a
 * hand-written string.
 *
 * Positions that accept developer-authored SQL fragments verbatim, select()
 * expressions, join() conditions and hand-written where()/having() strings,
 * must never receive user input directly; put values through
 * {@see ExpressionBuilder} or {@see QueryBuilder::createNamedParameter()} instead.
 */
class QueryBuilder
{
	const TYPE_SELECT = 0;
	const TYPE_INSERT = 1;
	const TYPE_UPDATE = 2;
	const TYPE_DELETE = 3;
	const TYPE_REPLACE = 4;
	const TYPE_UPSERT = 5;

	/** @var ConnectionInterface */
	private $db;

	/** @var PlatformInterface */
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
	 *      a pre-built "(sub-select)" expression (see {@see QueryBuilder::joinSub()})
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

	/** @var array placeholder name => value, in the {@see ConnectionInterface::execute()} shape */
	private $params = array();

	/** @var int placeholder name counter */
	private $paramCounter = 0;

	/**
	 * @var QueryBuilder|null when set, parameter creation delegates here so a
	 *      sub-builder (closure group, sub-query, UNION arm) shares one counter
	 *      and one parameter map with its parent.
	 */
	private $paramOwner = null;

	/** @var ExpressionBuilder|null lazily created expression helper */
	private $expr = null;

	/**
	 * @param ConnectionInterface $db Connection the query compiles against and executes on.
	 * @param PlatformInterface|null $platform SQL dialect; taken from
	 *                           {@see ConnectionInterface::getPlatform()} when omitted.
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
	 * @return ExpressionBuilder
	 */
	public function expr()
	{
		if($this->expr === null)
		{
			$this->expr = new ExpressionBuilder($this);
		}

		return $this->expr;
	}

	/**
	 * SQL dialect this query compiles for.
	 *
	 * @return PlatformInterface
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
	 * @param int|null $type Optional {@see ConnectionInterface}::PARAM_* override;
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
	 * Wrap developer-authored SQL as an {@see SqlFragment} fragment: thin sugar for
	 * {@see SqlFragment::raw()}. The SOLE explicit raw-SQL hatch; the string is
	 * spliced verbatim, so it must NEVER carry user input. Bind values with
	 * {@see QueryBuilder::createNamedParameter()} and splice the placeholder, or
	 * pass uniquely-named binds in $params.
	 *
	 * <code>
	 * $qb->where($qb->raw('FIND_IN_SET('.$qb->createNamedParameter($id).', user_plugin)'));
	 * </code>
	 *
	 * @param string $sql
	 * @param array $params name => value | array('value' => mixed, 'type' => int)
	 * @return SqlFragment
	 */
	public function raw($sql, array $params = array())
	{
		return SqlFragment::raw($sql, $params);
	}

	/**
	 * Absorb a fragment's bound parameters onto this query. Delegates to the
	 * shared parameter owner exactly like
	 * {@see QueryBuilder::createNamedParameter()} and fails closed: a name already
	 * present throws rather than silently overwriting (the loud backstop against
	 * a double-drain or a placeholder clash). Builder and {@see ExpressionBuilder}
	 * fragments carry an empty map, so only {@see QueryBuilder::raw()} /
	 * {@see SqlFragment::fromQuery()} parts have anything to merge.
	 *
	 * @param array $params name => value | array('value' => mixed, 'type' => int)
	 * @return void
	 * @throws InvalidArgumentException on a duplicate parameter name.
	 */
	public function mergeParameters(array $params)
	{
		if($this->paramOwner !== null)
		{
			$this->paramOwner->mergeParameters($params);

			return;
		}

		foreach($params as $name => $value)
		{
			if(array_key_exists($name, $this->params))
			{
				throw new InvalidArgumentException('Duplicate bound parameter name: '.$name);
			}

			$this->params[$name] = $value;
		}
	}

	/**
	 * Validate and quote a column identifier (`column` or `table.column`).
	 * Fails closed: anything outside the {@see IdentifierFilter::identifier()}
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
	 * Start a SELECT query and set the column list. Each entry must be a
	 * plain column name (`col`, `tbl.col`, `tbl.*`, `*`), validated and
	 * quoted fail-closed, or a vouched {@see SqlFragment} (its bound
	 * parameters are merged onto this query). A bare SQL expression is
	 * rejected: use {@see QueryBuilder::selectAs()} for aliasing,
	 * {@see QueryBuilder::selectAggregate()} for aggregates,
	 * {@see QueryBuilder::selectRaw()} for a whole developer-authored list,
	 * or wrap vouched developer SQL in {@see QueryBuilder::raw()}.
	 *
	 * @param string|array|SqlFragment $columns Column list as multiple
	 *                              arguments or as one array; defaults to '*'.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException on a bare SQL expression.
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
	 * Add more columns to the SELECT list without clearing it; same strict
	 * rules as {@see QueryBuilder::select()}.
	 *
	 * @param string|array|SqlFragment $columns As multiple arguments or one array.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException on a bare SQL expression.
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
	 * @return QueryBuilder $this
	 */
	public function distinct($distinct = true)
	{
		$this->distinct = (bool) $distinct;

		return $this;
	}

	/**
	 * Start a SELECT whose column list is a single developer-authored
	 * expression, taken verbatim: the explicit raw hatch for a SELECT list
	 * {@see QueryBuilder::select()} refuses (it accepts only identifiers and
	 * vouched fragments). Must never receive user input.
	 *
	 * @param string $expression Raw SELECT list.
	 * @return QueryBuilder $this
	 */
	public function selectRaw($expression)
	{
		$this->type = self::TYPE_SELECT;
		$this->select = array($this->_vouchedFragment($expression));

		return $this;
	}

	/**
	 * Add a scalar sub-query to the SELECT list as "(SELECT ...) AS alias"; see
	 * {@see QueryBuilder::fromSub()} for how the sub-query is supplied.
	 *
	 * @param Closure|QueryBuilder $query Sub-query source.
	 * @param string $alias Column alias; validated and quoted.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when the alias fails validation.
	 */
	public function selectSub($query, $alias)
	{
		$this->type = self::TYPE_SELECT;
		$this->select[] = $this->_subQuery($query).' AS '.$this->_quotedAlias($alias);

		return $this;
	}

	/**
	 * Add a plain "<column> AS `alias`" projection to the SELECT list. The
	 * column (an identifier, or table.column) and the alias are each validated
	 * and quoted independently and fail-closed; nothing is parsed out of a bare
	 * string, so an identifier or alias that itself contains a space or the word
	 * "as" is handled correctly. For a function/computed/literal projection use
	 * {@see QueryBuilder::selectAggregate()} / {@see QueryBuilder::selectLiteral()},
	 * or addSelect({@see QueryBuilder::raw()}).
	 *
	 * @param string $column Column name (or table.column); validated and quoted.
	 * @param string $alias Column alias; validated and quoted.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when the column or the alias fails validation.
	 */
	public function selectAs($column, $alias)
	{
		$this->type = self::TYPE_SELECT;
		$this->select[] = $this->quoteColumn($column).' AS '.$this->_quotedAlias($alias);

		return $this;
	}

	/**
	 * Add a "COUNT(<col>) [AS `alias`]" expression to the SELECT list, a
	 * structured spelling of a count-select that needs no developer SQL. The
	 * column (or '*') and the alias are validated and quoted, nothing is bound.
	 *
	 * @param string $column Column name, or '*' (the default).
	 * @param string|null $alias Optional column alias; validated and quoted.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when an identifier fails validation.
	 */
	public function selectCount($column = '*', $alias = null)
	{
		return $this->selectAggregate('COUNT', $column, $alias);
	}

	/**
	 * Add a "FUNC(<col>) [AS `alias`]" aggregate expression to the SELECT list.
	 * The function is checked against the allowlist {COUNT,SUM,AVG,MIN,MAX}, the
	 * column (or, for COUNT, '*') and the alias are validated identifiers, and
	 * nothing is bound. Built through {@see ExpressionBuilder::aggregate()}.
	 *
	 * @param string $function One of COUNT,SUM,AVG,MIN,MAX (case-insensitive).
	 * @param string $column Column name, or '*' for COUNT.
	 * @param string|null $alias Optional column alias; validated and quoted.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException on a bad function, column or alias.
	 */
	public function selectAggregate($function, $column, $alias = null)
	{
		$this->type = self::TYPE_SELECT;

		$fragment = $this->expr()->aggregate($function, $column, $alias);
		$this->mergeParameters($fragment->getParameters());
		$this->select[] = $fragment->getSql();

		return $this;
	}

	/**
	 * Add a bound literal as a SELECT expression, ":qbN AS `alias`" (e.g. the
	 * constant "1 AS is_active"). The value is bound, never inlined, and the
	 * alias is validated and quoted.
	 *
	 * @param mixed $value Bound literal value.
	 * @param string $alias Column alias; validated and quoted.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when the alias fails validation.
	 */
	public function selectLiteral($value, $alias)
	{
		$this->type = self::TYPE_SELECT;
		$this->select[] = $this->createNamedParameter($value).' AS '.$this->quoteColumn($alias);

		return $this;
	}

	/**
	 * Set the table to select from.
	 *
	 * @param string $table Logical table name, e.g. 'user' (no '#', no prefix).
	 * @param string|null $alias Optional table alias.
	 * @return QueryBuilder $this
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
	 * $qb->select('*')->fromSub(function (QueryBuilder $sub) {
	 *     $sub->select('user_class', 'COUNT(*) AS cnt')->from('user')->groupBy('user_class');
	 * }, 'counts');
	 * </code>
	 *
	 * @param Closure|QueryBuilder $query Closure receiving a fresh builder, or a
	 *                           builder made with {@see QueryBuilder::newSubQuery()}.
	 * @param string $alias Alias for the derived table; validated and quoted.
	 * @return QueryBuilder $this
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
	 * {@see QueryBuilder::createNamedParameter()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @param string $alias Alias for the joined table.
	 * @param string $condition ON condition.
	 * @return QueryBuilder $this
	 */
	public function join($table, $alias, $condition)
	{
		return $this->_join('INNER', $table, $alias, $condition);
	}

	/**
	 * LEFT JOIN another table; see {@see QueryBuilder::join()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @param string $alias Alias for the joined table.
	 * @param string $condition ON condition.
	 * @return QueryBuilder $this
	 */
	public function leftJoin($table, $alias, $condition)
	{
		return $this->_join('LEFT', $table, $alias, $condition);
	}

	/**
	 * INNER JOIN; an explicit alias of {@see QueryBuilder::join()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @param string $alias Alias for the joined table.
	 * @param string $condition ON condition.
	 * @return QueryBuilder $this
	 */
	public function innerJoin($table, $alias, $condition)
	{
		return $this->_join('INNER', $table, $alias, $condition);
	}

	/**
	 * RIGHT JOIN another table; see {@see QueryBuilder::join()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @param string $alias Alias for the joined table.
	 * @param string $condition ON condition.
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function crossJoin($table, $alias = null)
	{
		return $this->_join('CROSS', $table, $alias, null);
	}

	/**
	 * INNER JOIN a derived table (sub-query); see {@see QueryBuilder::fromSub()}
	 * for how the sub-query is supplied.
	 *
	 * @param Closure|QueryBuilder $query Sub-query source.
	 * @param string $alias Alias for the derived table; validated and quoted.
	 * @param string $condition ON condition.
	 * @return QueryBuilder $this
	 */
	public function joinSub($query, $alias, $condition)
	{
		return $this->_joinSub('INNER', $query, $alias, $condition);
	}

	/**
	 * LEFT JOIN a derived table (sub-query); see {@see QueryBuilder::joinSub()}.
	 *
	 * @param Closure|QueryBuilder $query Sub-query source.
	 * @param string $alias Alias for the derived table; validated and quoted.
	 * @param string $condition ON condition.
	 * @return QueryBuilder $this
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
	 *       sub-group: where(function (QueryBuilder $q) { ... });</li>
	 *   <li>an {@see ExpressionBuilder} fragment, or a hand-written string for the rare
	 *       case the builder cannot express (developer SQL, never user input).</li>
	 * </ul>
	 * The operator in the value forms is checked against a fixed allowlist,
	 * column names are validated, and values are always bound.
	 *
	 * @param mixed ...$args
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException on an unknown operator or invalid identifier.
	 */
	public function where(...$args)
	{
		return $this->_addPredicate('where', 'AND', $args);
	}

	/**
	 * OR a condition onto the WHERE clause; takes the same forms as
	 * {@see QueryBuilder::where()}.
	 *
	 * @param mixed ...$args
	 * @return QueryBuilder $this
	 */
	public function orWhere(...$args)
	{
		return $this->_addPredicate('where', 'OR', $args);
	}

	/**
	 * AND a condition onto the WHERE clause; an explicit alias of
	 * {@see QueryBuilder::where()} for readers who prefer to spell out the
	 * conjunction.
	 *
	 * @param mixed ...$args
	 * @return QueryBuilder $this
	 */
	public function andWhere(...$args)
	{
		return $this->_addPredicate('where', 'AND', $args);
	}

	/**
	 * AND a "column IN (...)" condition with every value bound. The values may
	 * be an array, or a sub-query (closure or {@see QueryBuilder::newSubQuery()}
	 * builder) for "column IN (SELECT ...)". An empty array compiles to the
	 * always-false predicate 1=0.
	 *
	 * @param string $column
	 * @param array|Closure|QueryBuilder $values
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function whereIn($column, $values)
	{
		return $this->_appendWhere('AND', $this->_inPredicate($column, $values, 'IN', '1=0'));
	}

	/**
	 * OR form of {@see QueryBuilder::whereIn()}.
	 *
	 * @param string $column
	 * @param array|Closure|QueryBuilder $values
	 * @return QueryBuilder $this
	 */
	public function orWhereIn($column, $values)
	{
		return $this->_appendWhere('OR', $this->_inPredicate($column, $values, 'IN', '1=0'));
	}

	/**
	 * AND a "column NOT IN (...)" condition; see {@see QueryBuilder::whereIn()}.
	 * An empty array compiles to the always-true predicate 1=1.
	 *
	 * @param string $column
	 * @param array|Closure|QueryBuilder $values
	 * @return QueryBuilder $this
	 */
	public function whereNotIn($column, $values)
	{
		return $this->_appendWhere('AND', $this->_inPredicate($column, $values, 'NOT IN', '1=1'));
	}

	/**
	 * OR form of {@see QueryBuilder::whereNotIn()}.
	 *
	 * @param string $column
	 * @param array|Closure|QueryBuilder $values
	 * @return QueryBuilder $this
	 */
	public function orWhereNotIn($column, $values)
	{
		return $this->_appendWhere('OR', $this->_inPredicate($column, $values, 'NOT IN', '1=1'));
	}

	/**
	 * AND "column IS NULL".
	 *
	 * @param string $column
	 * @return QueryBuilder $this
	 */
	public function whereNull($column)
	{
		return $this->_appendWhere('AND', $this->expr()->isNull($column));
	}

	/**
	 * OR "column IS NULL".
	 *
	 * @param string $column
	 * @return QueryBuilder $this
	 */
	public function orWhereNull($column)
	{
		return $this->_appendWhere('OR', $this->expr()->isNull($column));
	}

	/**
	 * AND "column IS NOT NULL".
	 *
	 * @param string $column
	 * @return QueryBuilder $this
	 */
	public function whereNotNull($column)
	{
		return $this->_appendWhere('AND', $this->expr()->isNotNull($column));
	}

	/**
	 * OR "column IS NOT NULL".
	 *
	 * @param string $column
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function whereBetween($column, $min, $max)
	{
		return $this->_appendWhere('AND', $this->expr()->between($column, $min, $max));
	}

	/**
	 * OR form of {@see QueryBuilder::whereBetween()}.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function whereNotBetween($column, $min, $max)
	{
		return $this->_appendWhere('AND', $this->expr()->notBetween($column, $min, $max));
	}

	/**
	 * OR form of {@see QueryBuilder::whereNotBetween()}.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function whereColumn($first, $operator, $second = null)
	{
		return $this->_appendWhere('AND', $this->expr()->compareColumns($first, $operator, $second));
	}

	/**
	 * OR form of {@see QueryBuilder::whereColumn()}.
	 *
	 * @param string $first
	 * @param string $operator
	 * @param string|null $second
	 * @return QueryBuilder $this
	 */
	public function orWhereColumn($first, $operator, $second = null)
	{
		return $this->_appendWhere('OR', $this->expr()->compareColumns($first, $operator, $second));
	}

	/**
	 * AND "column LIKE :pattern"; the pattern is bound verbatim, so the caller
	 * controls the % and _ wildcards. See {@see ExpressionBuilder::contains()} for
	 * matching a plain substring.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return QueryBuilder $this
	 */
	public function whereLike($column, $pattern)
	{
		return $this->_appendWhere('AND', $this->expr()->like($column, $pattern));
	}

	/**
	 * OR form of {@see QueryBuilder::whereLike()}.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return QueryBuilder $this
	 */
	public function orWhereLike($column, $pattern)
	{
		return $this->_appendWhere('OR', $this->expr()->like($column, $pattern));
	}

	/**
	 * AND "column NOT LIKE :pattern"; see {@see QueryBuilder::whereLike()}.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return QueryBuilder $this
	 */
	public function whereNotLike($column, $pattern)
	{
		return $this->_appendWhere('AND', $this->expr()->notLike($column, $pattern));
	}

	/**
	 * OR form of {@see QueryBuilder::whereNotLike()}.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return QueryBuilder $this
	 */
	public function orWhereNotLike($column, $pattern)
	{
		return $this->_appendWhere('OR', $this->expr()->notLike($column, $pattern));
	}

	/**
	 * AND "EXISTS (sub-query)"; see {@see QueryBuilder::fromSub()} for how the
	 * sub-query is supplied.
	 *
	 * @param Closure|QueryBuilder $query
	 * @return QueryBuilder $this
	 */
	public function whereExists($query)
	{
		return $this->_appendWhere('AND', 'EXISTS '.$this->_subQuery($query));
	}

	/**
	 * OR form of {@see QueryBuilder::whereExists()}.
	 *
	 * @param Closure|QueryBuilder $query
	 * @return QueryBuilder $this
	 */
	public function orWhereExists($query)
	{
		return $this->_appendWhere('OR', 'EXISTS '.$this->_subQuery($query));
	}

	/**
	 * AND "NOT EXISTS (sub-query)".
	 *
	 * @param Closure|QueryBuilder $query
	 * @return QueryBuilder $this
	 */
	public function whereNotExists($query)
	{
		return $this->_appendWhere('AND', 'NOT EXISTS '.$this->_subQuery($query));
	}

	/**
	 * OR form of {@see QueryBuilder::whereNotExists()}.
	 *
	 * @param Closure|QueryBuilder $query
	 * @return QueryBuilder $this
	 */
	public function orWhereNotExists($query)
	{
		return $this->_appendWhere('OR', 'NOT EXISTS '.$this->_subQuery($query));
	}

	/**
	 * AND a negated parenthesised sub-group, NOT (...), built from a closure.
	 *
	 * @param Closure $callback Receives a fresh builder.
	 * @return QueryBuilder $this
	 */
	public function whereNot(Closure $callback)
	{
		return $this->_appendWhere('AND', 'NOT ('.$this->_buildGroup($callback).')');
	}

	/**
	 * OR form of {@see QueryBuilder::whereNot()}.
	 *
	 * @param Closure $callback
	 * @return QueryBuilder $this
	 */
	public function orWhereNot(Closure $callback)
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
	 * @return QueryBuilder $this
	 */
	public function whereDate($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_datePredicate('date', $column, $operator, $value));
	}

	/**
	 * OR form of {@see QueryBuilder::whereDate()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
	 */
	public function orWhereDate($column, $operator, $value = null)
	{
		return $this->_appendWhere('OR', $this->_datePredicate('date', $column, $operator, $value));
	}

	/**
	 * AND a comparison against the year part of a column; see
	 * {@see QueryBuilder::whereDate()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
	 */
	public function whereYear($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_datePredicate('year', $column, $operator, $value));
	}

	/**
	 * OR form of {@see QueryBuilder::whereYear()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
	 */
	public function orWhereYear($column, $operator, $value = null)
	{
		return $this->_appendWhere('OR', $this->_datePredicate('year', $column, $operator, $value));
	}

	/**
	 * AND a comparison against the month part of a column; see
	 * {@see QueryBuilder::whereDate()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
	 */
	public function whereMonth($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_datePredicate('month', $column, $operator, $value));
	}

	/**
	 * OR form of {@see QueryBuilder::whereMonth()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
	 */
	public function orWhereMonth($column, $operator, $value = null)
	{
		return $this->_appendWhere('OR', $this->_datePredicate('month', $column, $operator, $value));
	}

	/**
	 * AND a comparison against the day part of a column; see
	 * {@see QueryBuilder::whereDate()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
	 */
	public function whereDay($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_datePredicate('day', $column, $operator, $value));
	}

	/**
	 * OR form of {@see QueryBuilder::whereDay()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
	 */
	public function orWhereDay($column, $operator, $value = null)
	{
		return $this->_appendWhere('OR', $this->_datePredicate('day', $column, $operator, $value));
	}

	/**
	 * AND a comparison against the time part of a column; see
	 * {@see QueryBuilder::whereDate()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
	 */
	public function whereTime($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_datePredicate('time', $column, $operator, $value));
	}

	/**
	 * OR form of {@see QueryBuilder::whereTime()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function whereJsonContains($column, $value)
	{
		return $this->_appendWhere('AND', $this->_jsonContains($column, $value, false));
	}

	/**
	 * OR form of {@see QueryBuilder::whereJsonContains()}.
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function whereJsonDoesntContain($column, $value)
	{
		return $this->_appendWhere('AND', $this->_jsonContains($column, $value, true));
	}

	/**
	 * OR form of {@see QueryBuilder::whereJsonDoesntContain()}.
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function whereJsonContainsKey($column, $path)
	{
		return $this->_appendWhere('AND', $this->_jsonContainsKey($column, $path, false));
	}

	/**
	 * OR form of {@see QueryBuilder::whereJsonContainsKey()}.
	 *
	 * @param string $column
	 * @param string $path
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function whereJsonDoesntContainKey($column, $path)
	{
		return $this->_appendWhere('AND', $this->_jsonContainsKey($column, $path, true));
	}

	/**
	 * OR form of {@see QueryBuilder::whereJsonDoesntContainKey()}.
	 *
	 * @param string $column
	 * @param string $path
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function whereJsonLength($column, $operator, $value = null)
	{
		return $this->_appendWhere('AND', $this->_jsonLength($column, $operator, $value));
	}

	/**
	 * OR form of {@see QueryBuilder::whereJsonLength()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function whereFullText($columns, $value)
	{
		return $this->_appendWhere('AND', $this->_fullText($columns, $value));
	}

	/**
	 * OR form of {@see QueryBuilder::whereFullText()}.
	 *
	 * @param string|array $columns
	 * @param string $value
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function groupBy($columns)
	{
		$this->groupBy = array();

		$args = is_array($columns) ? $columns : func_get_args();

		foreach($args as $column)
		{
			$this->groupBy[] = $this->_groupByExpression($column);
		}

		return $this;
	}

	/**
	 * Add more columns to the GROUP BY list without clearing it.
	 *
	 * @param string|array $columns As multiple string arguments or one array.
	 * @return QueryBuilder $this
	 */
	public function addGroupBy($columns)
	{
		$args = is_array($columns) ? $columns : func_get_args();

		foreach($args as $column)
		{
			$this->groupBy[] = $this->_groupByExpression($column);
		}

		return $this;
	}

	/**
	 * AND a condition onto the HAVING clause; takes the same forms as
	 * {@see QueryBuilder::where()}.
	 *
	 * @param mixed ...$args
	 * @return QueryBuilder $this
	 */
	public function having(...$args)
	{
		return $this->_addPredicate('having', 'AND', $args);
	}

	/**
	 * OR a condition onto the HAVING clause.
	 *
	 * @param mixed ...$args
	 * @return QueryBuilder $this
	 */
	public function orHaving(...$args)
	{
		return $this->_addPredicate('having', 'OR', $args);
	}

	/**
	 * AND a condition onto the HAVING clause; an explicit alias of
	 * {@see QueryBuilder::having()}.
	 *
	 * @param mixed ...$args
	 * @return QueryBuilder $this
	 */
	public function andHaving(...$args)
	{
		return $this->_addPredicate('having', 'AND', $args);
	}

	/**
	 * AND a hand-written HAVING fragment (developer SQL, never user input;
	 * bind any values with {@see QueryBuilder::createNamedParameter()}).
	 *
	 * @param string $fragment
	 * @return QueryBuilder $this
	 */
	public function havingRaw($fragment)
	{
		return $this->_appendHaving('AND', $this->_vouchedFragment($fragment));
	}

	/**
	 * OR form of {@see QueryBuilder::havingRaw()}.
	 *
	 * @param string $fragment
	 * @return QueryBuilder $this
	 */
	public function orHavingRaw($fragment)
	{
		return $this->_appendHaving('OR', $this->_vouchedFragment($fragment));
	}

	/**
	 * AND "COUNT(*) OP :value" onto the HAVING clause, a structured spelling of
	 * the common grouped-count filter. The operator is checked against the
	 * allowlist and the value is bound.
	 *
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException on an unsupported operator.
	 */
	public function havingCount($operator, $value)
	{
		return $this->havingAggregate('COUNT', '*', $operator, $value);
	}

	/**
	 * AND "FUNC(<col>) OP :value" onto the HAVING clause. The function is checked
	 * against the allowlist {COUNT,SUM,AVG,MIN,MAX}, the column (or '*' for
	 * COUNT) is validated, the operator is allowlisted and the value is bound.
	 * Built through {@see ExpressionBuilder::aggregateComparison()}.
	 *
	 * @param string $function One of COUNT,SUM,AVG,MIN,MAX (case-insensitive).
	 * @param string $column Column name, or '*' for COUNT.
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException on a bad function, column or operator.
	 */
	public function havingAggregate($function, $column, $operator, $value)
	{
		return $this->_appendHaving('AND', $this->expr()->aggregateComparison($function, $column, $operator, $value));
	}

	/**
	 * OR form of {@see QueryBuilder::havingAggregate()}.
	 *
	 * @param string $function One of COUNT,SUM,AVG,MIN,MAX (case-insensitive).
	 * @param string $column Column name, or '*' for COUNT.
	 * @param string $operator
	 * @param mixed $value
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException on a bad function, column or operator.
	 */
	public function orHavingAggregate($function, $column, $operator, $value)
	{
		return $this->_appendHaving('OR', $this->expr()->aggregateComparison($function, $column, $operator, $value));
	}

	/**
	 * AND "column BETWEEN :min AND :max" onto the HAVING clause, both bounds bound.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return QueryBuilder $this
	 */
	public function havingBetween($column, $min, $max)
	{
		return $this->_appendHaving('AND', $this->expr()->between($column, $min, $max));
	}

	/**
	 * OR form of {@see QueryBuilder::havingBetween()}.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return QueryBuilder $this
	 */
	public function orHavingBetween($column, $min, $max)
	{
		return $this->_appendHaving('OR', $this->expr()->between($column, $min, $max));
	}

	/**
	 * Replace the ORDER BY clause. Validated by the {@see IdentifierFilter}
	 * grammar and fails closed: anything outside "column [ASC|DESC]" lists
	 * (functions, parentheses, subqueries) throws.
	 *
	 * @param string $sort Column name; or, when $direction is null, a full
	 *                     legacy fragment such as 'col1 DESC, t.col2'.
	 * @param string|null $direction 'ASC' or 'DESC' (case-insensitive).
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when the fragment fails validation.
	 */
	public function orderBy($sort, $direction = null)
	{
		$this->orderBy = array();

		return $this->addOrderBy($sort, $direction);
	}

	/**
	 * Append to the ORDER BY clause; same validation as
	 * {@see QueryBuilder::orderBy()}.
	 *
	 * @param string $sort
	 * @param string|null $direction
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when the fragment fails validation.
	 */
	public function addOrderBy($sort, $direction = null)
	{
		$this->_loadFilter();

		if($direction !== null)
		{
			$quoted = IdentifierFilter::identifier($sort);

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

		$canonical = IdentifierFilter::orderBy($sort);

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
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function oldest($column)
	{
		return $this->addOrderBy($column, 'ASC');
	}

	/**
	 * Append a random ordering; the dialect spells the function.
	 *
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function setMaxResults($maxResults)
	{
		$this->maxResults = ($maxResults === null) ? null : max(0, (int) $maxResults);

		return $this;
	}

	/**
	 * Alias of {@see QueryBuilder::setMaxResults()} (LIMIT).
	 *
	 * @param int|null $limit
	 * @return QueryBuilder $this
	 */
	public function limit($limit)
	{
		return $this->setMaxResults($limit);
	}

	/**
	 * Alias of {@see QueryBuilder::setMaxResults()} (LIMIT).
	 *
	 * @param int|null $limit
	 * @return QueryBuilder $this
	 */
	public function take($limit)
	{
		return $this->setMaxResults($limit);
	}

	/**
	 * Alias of {@see QueryBuilder::setFirstResult()} (OFFSET).
	 *
	 * @param int|null $offset
	 * @return QueryBuilder $this
	 */
	public function offset($offset)
	{
		return $this->setFirstResult($offset);
	}

	/**
	 * Alias of {@see QueryBuilder::setFirstResult()} (OFFSET).
	 *
	 * @param int|null $offset
	 * @return QueryBuilder $this
	 */
	public function skip($offset)
	{
		return $this->setFirstResult($offset);
	}

	/**
	 * Start an INSERT query; supply the row with
	 * {@see QueryBuilder::values()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when a column name fails validation.
	 */
	public function values(array $values)
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
	 * first with {@see QueryBuilder::insert()} or
	 * {@see QueryBuilder::insertOrIgnore()}; pass the columns to fill, or an empty
	 * array for "INSERT INTO t SELECT ...".
	 *
	 * <code>
	 * $qb->insert('archive')->insertUsing(array('id', 'name'), function (QueryBuilder $s) {
	 *     $s->select('id', 'name')->from('live')->where('expired', 1);
	 * });
	 * </code>
	 *
	 * @param array $columns Target columns, or array() to insert every column.
	 * @param Closure|QueryBuilder $query Sub-query source.
	 * @return QueryBuilder $this
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
	 * table first with {@see QueryBuilder::insert()}.
	 *
	 * @param array $values column => value
	 * @return int|string|bool the last insert id, or false on error.
	 * @throws InvalidArgumentException when a column name fails validation.
	 */
	public function insertGetId(array $values)
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
	 * {@see QueryBuilder::values()} (or {@see QueryBuilder::set()}); every value is
	 * bound. The dialect-specific statement is produced by the platform, so the
	 * call site stays portable.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @return QueryBuilder $this
	 */
	public function replace($table)
	{
		$this->type = self::TYPE_REPLACE;
		$this->table = $table;

		return $this;
	}

	/**
	 * Start an UPDATE query; queue assignments with
	 * {@see QueryBuilder::set()}.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @return QueryBuilder $this
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
	 * use {@see QueryBuilder::setExpression()}.
	 *
	 * @param string $column
	 * @param mixed $value
	 * @param int|null $type Optional {@see ConnectionInterface}::PARAM_* override.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function set($column, $value, $type = null)
	{
		$this->set[$this->quoteColumn($column)] = $this->createNamedParameter($value, $type);

		return $this;
	}

	/**
	 * Queue a SQL expression as a column assignment for UPDATE, e.g.
	 * "user_visits = user_visits + 1". Unlike {@see QueryBuilder::set()}, the
	 * right-hand side is developer-authored SQL placed verbatim, so it must
	 * never contain user input; bind any values inside it with
	 * {@see QueryBuilder::createNamedParameter()}.
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
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function setExpression($column, $expression)
	{
		$this->set[$this->quoteColumn($column)] = $this->_vouchedFragment($expression);

		return $this;
	}

	/**
	 * Queue a column-to-column copy for UPDATE, "SET `col` = `src`". Both sides
	 * are validated identifiers and nothing is bound, so neither may carry user
	 * input; this is the structured spelling that replaces a hand-written
	 * {@see QueryBuilder::setExpression()} RHS of one bare column.
	 *
	 * @param string $column Assignment target; validated and quoted.
	 * @param string $sourceColumn Source column; validated and quoted.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when an identifier fails validation.
	 */
	public function setColumn($column, $sourceColumn)
	{
		$this->set[$this->quoteColumn($column)] = $this->quoteColumn($sourceColumn);

		return $this;
	}

	/**
	 * Queue a typed column assignment, applying the e107 field-type STORAGE
	 * transform so the bound value is byte-identical to the deprecated
	 * array-form {@see ConnectionInterface::update()}/{@see ConnectionInterface::insert()}. Unlike
	 * {@see QueryBuilder::set()} - whose third argument is a {@see ConnectionInterface}::PARAM_*
	 * override - $fieldType here is a field-type TOKEN ('int', 'float', 'array',
	 * 'todb', 'null', 'str', 'cmd', ...).
	 *
	 * @param string $column
	 * @param mixed $value
	 * @param string $fieldType Field-type token.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function setTyped($column, $value, $fieldType)
	{
		return $this->_typedParameter($column, $value, $fieldType);
	}

	/**
	 * Queue one row of typed values, applying the e107 field-type STORAGE
	 * transform per column so writes are byte-identical to the deprecated
	 * array-form CRUD. Each value's token comes from $fieldTypes[$column],
	 * falling back to $fieldTypes['_DEFAULT'] then 'string' for columns absent
	 * from the map, mirroring the legacy lookup.
	 *
	 * Pass a single column => value map; a list of rows is rejected (bind the
	 * rows individually, since each may carry different field types).
	 *
	 * <code>
	 * $defs = e107::getDb()->getFieldDefs('user');
	 * $qb->insert('user')->valuesTyped($data, $defs['_FIELD_TYPES'])->execute();
	 * </code>
	 *
	 * @param array $values column => value (single row)
	 * @param array $fieldTypes column => field-type token, optionally with a
	 *                          '_DEFAULT' fallback.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException on a list-of-rows input or a bad column.
	 */
	public function valuesTyped(array $values, array $fieldTypes = array())
	{
		if($this->_isListOfRows($values))
		{
			throw new InvalidArgumentException('valuesTyped() takes one row; pass a single column => value map.');
		}

		foreach($values as $column => $value)
		{
			$this->_typedParameter($column, $value, $this->_resolveFieldType($column, $fieldTypes));
		}

		return $this;
	}

	/**
	 * The field-type token for a column: an explicit entry, else the '_DEFAULT'
	 * fallback, else 'string'. All three identity-transform to a PARAM_STR bind,
	 * matching the legacy default for an untyped field.
	 *
	 * @param string $column
	 * @param array $fieldTypes
	 * @return string
	 */
	private function _resolveFieldType($column, array $fieldTypes)
	{
		if(isset($fieldTypes[$column]))
		{
			return $fieldTypes[$column];
		}

		if(isset($fieldTypes['_DEFAULT']))
		{
			return $fieldTypes['_DEFAULT'];
		}

		return 'string';
	}

	/**
	 * Apply one field-type transform and queue the assignment. For 'cmd' on an
	 * UPDATE the value is developer-authored SQL emitted verbatim with no bind,
	 * matching the legacy _prepareUpdateArg() path; every other case (including
	 * 'cmd' on INSERT/REPLACE, where the legacy bind tuple stores the literal
	 * value) binds the transformed value with its field-type PARAM_*, the value
	 * transformed first and the bind type then derived from it - exactly the
	 * legacy bind tuple.
	 *
	 * @param string $column
	 * @param mixed $value
	 * @param string $fieldType Field-type token.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	private function _typedParameter($column, $value, $fieldType)
	{
		if($fieldType === 'cmd' && $this->type === self::TYPE_UPDATE)
		{
			return $this->setExpression($column, SqlFragment::fragment((string) $value));
		}

		$transformed = $this->db->applyFieldType($fieldType, $value);
		$bindType = $this->db->fieldTypeBind($fieldType, $transformed);
		$this->set[$this->quoteColumn($column)] = $this->createNamedParameter($transformed, $bindType);

		return $this;
	}

	/**
	 * Start a DELETE query. As with the legacy API, compiling without a
	 * WHERE clause deletes every row in the table.
	 *
	 * @param string $table Logical table name (no '#', no prefix).
	 * @return QueryBuilder $this
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
	 * {@see QueryBuilder::insert()}. Every value is bound and the dialect-specific
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
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when no table is set or an identifier fails validation.
	 */
	public function upsert(array $values, $uniqueBy, $update = null)
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

		$this->_buildUpsertUpdate($updateSource, $uniqueBy, $update);

		return $this;
	}

	/**
	 * Insert one typed row, updating the listed columns instead when a row would
	 * collide on a primary or unique key. The typed analogue of
	 * {@see QueryBuilder::upsert()}: each value passes through the e107 field-type
	 * STORAGE transform (as {@see QueryBuilder::valuesTyped()}) so the inserted
	 * bytes are identical to the deprecated field-typed array
	 * `_DUPLICATE_KEY_UPDATE` write, while the ON DUPLICATE KEY UPDATE clause
	 * refreshes each column from the (already typed) inserted value.
	 *
	 * Takes a single column => value row; a list of rows is rejected (bind rows
	 * individually, since each may carry different field types).
	 *
	 * <code>
	 * $defs = e107::getDb()->getFieldDefs('user_extended');
	 * $qb->insert('user_extended')
	 *    ->upsertTyped($data, 'user_extended_id', null, $defs['_FIELD_TYPES'])
	 *    ->execute();
	 * </code>
	 *
	 * @param array $values One column => value row (single row only).
	 * @param string|array $uniqueBy Column(s) identifying a collision; validated.
	 * @param array|null $update Columns to update on collision; when null, every
	 *                   inserted column except those in $uniqueBy.
	 * @param array $fieldTypes column => field-type token, optionally with a
	 *                          '_DEFAULT' fallback.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException on a list-of-rows input, no table, or a bad column.
	 */
	public function upsertTyped(array $values, $uniqueBy, $update = null, array $fieldTypes = array())
	{
		if($this->table === null)
		{
			throw new InvalidArgumentException('upsertTyped() needs a table; start with insert($table).');
		}

		if($this->_isListOfRows($values))
		{
			throw new InvalidArgumentException('upsertTyped() takes one row; pass a single column => value map.');
		}

		$this->type = self::TYPE_UPSERT;

		foreach((array) $uniqueBy as $column)
		{
			$this->quoteColumn($column); // validate, fail closed
		}

		foreach($values as $column => $value)
		{
			$this->_typedParameter($column, $value, $this->_resolveFieldType($column, $fieldTypes));
		}

		$this->_buildUpsertUpdate(array_keys($values), $uniqueBy, $update);

		return $this;
	}

	/**
	 * Build the ON DUPLICATE KEY UPDATE assignment list (each column refreshed
	 * from its inserted value) shared by {@see QueryBuilder::upsert()} and
	 * {@see QueryBuilder::upsertTyped()}.
	 *
	 * @param array $updateSource inserted column names, in order
	 * @param string|array $uniqueBy collision key column(s), excluded when $update is null
	 * @param array|null $update explicit update columns, or null to derive from $updateSource
	 * @return void
	 */
	private function _buildUpsertUpdate(array $updateSource, $uniqueBy, $update)
	{
		$updateColumns = ($update === null)
			? array_values(array_diff($updateSource, (array) $uniqueBy))
			: $update;

		$this->upsertUpdate = array();

		foreach($updateColumns as $column)
		{
			$quoted = $this->quoteColumn($column);
			$this->upsertUpdate[] = $quoted.' = '.$this->platform->getUpsertValueReference($quoted);
		}
	}

	/**
	 * Update the row matching $attributes, or insert one when none matches.
	 * Name the table first with a write statement such as
	 * {@see QueryBuilder::update()}. This issues a lookup then a write and is not
	 * atomic; prefer {@see QueryBuilder::upsert()} when a unique key exists.
	 *
	 * @param array $attributes column => value used to find and to seed the row
	 * @param array $values column => value applied on update (and on insert)
	 * @return int|bool affected rows, or 0 when an existing row needs no change.
	 * @throws InvalidArgumentException when no table is set.
	 */
	public function updateOrInsert(array $attributes, array $values = array())
	{
		if($this->table === null)
		{
			throw new InvalidArgumentException('updateOrInsert() needs a table; start with a write statement naming one.');
		}

		$exists = new QueryBuilder($this->db, $this->platform);
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

			$update = new QueryBuilder($this->db, $this->platform);
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

		$insert = new QueryBuilder($this->db, $this->platform);

		return $insert->insert($this->table)->values($attributes + $values)->execute();
	}

	/**
	 * Queue "column = column + :amount" for UPDATE; the amount is bound. Start
	 * with {@see QueryBuilder::update()} and add a {@see QueryBuilder::where()}.
	 *
	 * @param string $column
	 * @param int|float $amount
	 * @param array $extra Further column => value assignments, each bound.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when an identifier fails validation.
	 */
	public function increment($column, $amount = 1, array $extra = array())
	{
		return $this->_incDec($column, '+', $amount, $extra);
	}

	/**
	 * Queue "column = column - :amount" for UPDATE; see
	 * {@see QueryBuilder::increment()}.
	 *
	 * @param string $column
	 * @param int|float $amount
	 * @param array $extra Further column => value assignments, each bound.
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when an identifier fails validation.
	 */
	public function decrement($column, $amount = 1, array $extra = array())
	{
		return $this->_incDec($column, '-', $amount, $extra);
	}

	/**
	 * Compile the query to SQL. Together with
	 * {@see QueryBuilder::getParameters()} this is the query's complete
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
	 * Bound parameters, in the shape {@see ConnectionInterface::execute()} accepts.
	 *
	 * @return array placeholder name => value, or
	 *               name => array('value' => mixed, 'type' => ConnectionInterface::PARAM_*)
	 */
	public function getParameters()
	{
		return $this->params;
	}

	/**
	 * Compile and run the query on the connection.
	 *
	 * @return int|bool the {@see ConnectionInterface::execute()} return: row count for
	 *                  SELECT (read rows with {@see ConnectionInterface::fetch()}), affected
	 *                  rows for INSERT/UPDATE/DELETE, false on error.
	 * @throws InvalidArgumentException when the query fails to compile.
	 */
	public function execute()
	{
		return $this->db->execute($this->getSQL(), $this->params);
	}

	/**
	 * Run the query and return every row in one array.
	 *
	 * This MATERIALISES the whole result set: every row is held in PHP memory at
	 * once, so peak memory scales with the row count. For a large or unbounded
	 * result set, prefer {@see QueryBuilder::fetchEach()}, which streams one row at
	 * a time and keeps only the current row resident. Reach for fetchAll() when
	 * you genuinely need the array: to key it with $indexBy, to count it or index
	 * into it, or when the loop body must run another query on the same ConnectionInterface
	 * handle (which would clobber a live stream; see fetchEach()).
	 *
	 * @param string|null $indexBy Column whose value keys the result array.
	 * @return array rows as associative arrays; empty when no rows match or
	 *               on error (see {@see ConnectionInterface::getLastErrorText()}).
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
	 * Run the query and stream the rows one at a time.
	 *
	 * Unlike {@see QueryBuilder::fetchAll()}, this does NOT build an array of the
	 * whole result set: it yields each row as it is read, so only a single row is
	 * resident in PHP memory at once. Prefer it over fetchAll() whenever the
	 * result set can be large or unbounded and the loop consumes rows one by one.
	 *
	 * The query runs lazily, on the first iteration, so nothing happens until the
	 * generator is traversed. On a query error nothing is yielded (an empty
	 * stream), matching fetchAll()'s fail-soft behaviour.
	 *
	 * Caveat: the stream reads from the shared ConnectionInterface result on this handle, so do
	 * NOT run another query on the SAME handle while iterating over it; that
	 * overwrites the result mid-stream. Use a second handle (e107::getDb('sql2'))
	 * for nested queries, or fetchAll() to buffer the rows first.
	 *
	 * @return \Generator<int, array> each row as an associative array.
	 */
	public function fetchEach()
	{
		if($this->execute() === false)
		{
			return;
		}

		while($row = $this->db->fetch())
		{
			yield $row;
		}
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
		return (int) $this->_aggregateTerminal('COUNT', $column);
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
		return $this->_aggregateTerminal('MAX', $column);
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
		return $this->_aggregateTerminal('MIN', $column);
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
		return $this->_aggregateTerminal('SUM', $column);
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
		return $this->_aggregateTerminal('AVG', $column);
	}

	/**
	 * Run an aggregate over a throwaway copy of this query and return the scalar
	 * result. The aggregate select is built on a {@see clone} so calling an
	 * aggregate terminal never rewrites this builder's SELECT list (aggregate
	 * purity); the clone's type and select are set directly rather than through
	 * {@see QueryBuilder::select()}, which would wipe the list. The fragment is
	 * authored by {@see ExpressionBuilder::aggregate()} and binds nothing, so the
	 * clone's WHERE parameters (copied by value) are the only binds.
	 *
	 * @param string $function One of COUNT,SUM,AVG,MIN,MAX.
	 * @param string $column Column name, or '*' for COUNT.
	 * @return mixed the scalar result, or null when there is no row.
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	private function _aggregateTerminal($function, $column)
	{
		$q = clone $this;
		$q->expr = null;
		$q->type = self::TYPE_SELECT;
		$q->select = array($q->expr()->aggregate($function, $column)->getSql());

		return $q->fetchOne();
	}

	/**
	 * Acquire an exclusive write lock on the selected rows (e.g. FOR UPDATE).
	 * The dialect spells the clause.
	 *
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	public function sharedLock()
	{
		$this->lock = $this->platform->getSharedLockClause();

		return $this;
	}

	/**
	 * Append a UNION arm. Build the arm with {@see QueryBuilder::newUnionQuery()}
	 * so it shares this query's bound-parameter numbering.
	 *
	 * @param QueryBuilder $query
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException when the arm does not share parameters.
	 */
	public function union($query)
	{
		return $this->_addUnion($query, false);
	}

	/**
	 * Append a UNION ALL arm; see {@see QueryBuilder::union()}.
	 *
	 * @param QueryBuilder $query
	 * @return QueryBuilder $this
	 */
	public function unionAll($query)
	{
		return $this->_addUnion($query, true);
	}

	/**
	 * A fresh builder that shares this query's parameter numbering, for use as
	 * a UNION arm. See {@see QueryBuilder::union()}.
	 *
	 * @return QueryBuilder
	 */
	public function newUnionQuery()
	{
		return $this->newSubQuery();
	}

	/**
	 * A fresh builder that shares this query's parameter numbering, for use as
	 * a sub-query (e.g. {@see QueryBuilder::whereExists()},
	 * {@see QueryBuilder::whereIn()}, {@see QueryBuilder::fromSub()}).
	 *
	 * @return QueryBuilder
	 */
	public function newSubQuery()
	{
		$query = new QueryBuilder($this->db, $this->platform);
		$query->paramOwner = ($this->paramOwner !== null) ? $this->paramOwner : $this;

		return $query;
	}

	/**
	 * Strict SELECT-list term: a SqlFragment (its parameters merged), or a
	 * validated identifier ('*', 'tbl.*', 'col', 'tbl.col'). A bare SQL
	 * string is rejected rather than emitted verbatim, so the SELECT list
	 * cannot carry unvouched SQL; see {@see QueryBuilder::select()} for the
	 * structured spellings.
	 *
	 * @param SqlFragment|string $expression
	 * @return string
	 * @throws InvalidArgumentException on a bare SQL expression.
	 */
	private function _quoteExpression($expression)
	{
		return $this->_strictExpression(
			$expression,
			'select() will not accept a bare SQL expression: %s.'
			.' Use selectAs()/selectAggregate()/selectRaw(), or wrap vouched developer SQL in $qb->raw().'
		);
	}

	/**
	 * Strict GROUP BY term; same contract as the SELECT-list path
	 * ({@see QueryBuilder::_quoteExpression()}), GROUP BY cannot carry
	 * unvouched SQL either.
	 *
	 * @param SqlFragment|string $expression
	 * @return string
	 * @throws InvalidArgumentException on a bare SQL expression.
	 */
	private function _groupByExpression($expression)
	{
		return $this->_strictExpression(
			$expression,
			'groupBy() will not accept a bare SQL expression: %s.'
			.' Pass a column name or wrap vouched developer SQL in $qb->raw().'
		);
	}

	/**
	 * Shared strict term parser behind the SELECT-list and GROUP BY seams: a
	 * SqlFragment is accepted as vouched (its parameters merged onto this
	 * query), a plain identifier ('*', 'tbl.*', 'col', 'tbl.col') is
	 * validated and quoted fail-closed, and anything else throws.
	 *
	 * @param SqlFragment|string $expression
	 * @param string $errorFormat sprintf format for the rejection message;
	 *                            %s receives the offending expression.
	 * @return string
	 * @throws InvalidArgumentException on a bare SQL expression.
	 */
	private function _strictExpression($expression, $errorFormat)
	{
		if($expression instanceof SqlFragment)
		{
			$this->mergeParameters($expression->getParameters());

			return $expression->getSql();
		}

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

		if($quoted === false)
		{
			throw new InvalidArgumentException(sprintf($errorFormat, $expression));
		}

		return $quoted;
	}

	/**
	 * @param string $type 'INNER' or 'LEFT'
	 * @param string $table
	 * @param string $alias
	 * @param string $condition
	 * @return QueryBuilder $this
	 */
	private function _join($type, $table, $alias, $condition)
	{
		$this->join[] = array(
			'type'      => $type,
			'table'     => $table,
			'alias'     => $alias,
			'condition' => $this->_vouchedCondition($condition),
		);

		return $this;
	}

	/**
	 * Vouch a JOIN ON condition: null for a CROSS JOIN, or a SqlFragment
	 * (its parameters merged into the query). A bare PHP string is rejected so a
	 * hand-built ON clause cannot reach the query unvouched; build conditions with
	 * expr()->compareColumns()/allOf() or wrap developer SQL in $qb->raw().
	 *
	 * @param SqlFragment|null $condition
	 * @return string|null
	 * @throws InvalidArgumentException on a bare string.
	 */
	private function _vouchedCondition($condition)
	{
		if($condition === null)
		{
			return null;
		}

		if($condition instanceof SqlFragment)
		{
			$this->mergeParameters($condition->getParameters());

			return $condition->getSql();
		}

		throw new InvalidArgumentException(
			'JOIN ON condition must be an expr() comparison (e.g. '
			.'$qb->expr()->compareColumns(...)) or vouched SQL via $qb->raw(); '
			.'a bare string is not accepted.'
		);
	}

	/**
	 * Resolve a named raw-SQL hatch argument (selectRaw/havingRaw/setExpression).
	 * The method name is itself the vouch, so a bare string is accepted; an
	 * SqlFragment is also accepted and its parameters merged so a foreign
	 * fragment's binds are not silently dropped.
	 *
	 * @param SqlFragment|string $fragment
	 * @return string
	 */
	private function _vouchedFragment($fragment)
	{
		if($fragment instanceof SqlFragment)
		{
			$this->mergeParameters($fragment->getParameters());

			return $fragment->getSql();
		}

		return (string) $fragment;
	}

	/**
	 * Queue a join whose source is a derived table (sub-query).
	 *
	 * @param string $type 'INNER' or 'LEFT'
	 * @param Closure|QueryBuilder $query
	 * @param string $alias
	 * @param string $condition
	 * @return QueryBuilder $this
	 */
	private function _joinSub($type, $query, $alias, $condition)
	{
		$this->join[] = array(
			'type'      => $type,
			'table'     => null,
			'expr'      => $this->_subQuery($query),
			'alias'     => $alias,
			'condition' => $this->_vouchedCondition($condition),
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

			if($arg instanceof SqlFragment)
			{
				$this->mergeParameters($arg->getParameters());

				return $arg;
			}

			throw new InvalidArgumentException(
				'where()/having() will not accept a bare SQL string. Pass a structured '
				.'comparison - (column, value) or (column, operator, value) - an array, a '
				.'closure group, an expr() fragment, or wrap vouched developer SQL in $qb->raw().'
			);
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
	 * @param array|Closure|QueryBuilder $values
	 * @param string $operator 'IN' or 'NOT IN'
	 * @param string $emptyResult predicate for an empty array
	 * @return string
	 */
	private function _inPredicate($column, $values, $operator, $emptyResult)
	{
		if($values instanceof Closure || $values instanceof QueryBuilder)
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
	 * @param Closure|QueryBuilder $query
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
	 * @param Closure|QueryBuilder $query
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

		if($query instanceof QueryBuilder)
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

		$op = ExpressionBuilder::operator($operator);

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

		$op = ExpressionBuilder::operator($operator);

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
	 * @param QueryBuilder $query
	 * @param bool $all
	 * @return QueryBuilder $this
	 * @throws InvalidArgumentException
	 */
	private function _addUnion($query, $all)
	{
		if(!($query instanceof QueryBuilder))
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
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
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
	 * @return QueryBuilder $this
	 */
	private function _appendWhere($conjunction, $sql)
	{
		$this->where[] = array('conjunction' => $conjunction, 'sql' => $sql);

		return $this;
	}

	/**
	 * @param string $conjunction 'AND' or 'OR'
	 * @param string $sql
	 * @return QueryBuilder $this
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
		if(!class_exists(IdentifierFilter::class))
		{
			require_once(__DIR__.'/IdentifierFilter.php');
		}
	}
}
