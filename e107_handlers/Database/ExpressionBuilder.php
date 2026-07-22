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

use InvalidArgumentException;

/**
 * Builds WHERE/HAVING predicate fragments for {@see QueryBuilder}. Column
 * names are validated against the identifier grammar and every comparison
 * value is registered as a bound parameter on the owning query, never placed
 * in the SQL text. This is a deliberate divergence from builders whose
 * expression helpers accept raw SQL on the right-hand side: here the safe
 * spelling is the only spelling.
 *
 * Every fragment-producing method returns an {@see SqlFragment}: the values it
 * compares are already bound on the owning query, so the returned fragment
 * carries an empty parameter map and is a pure type marker. Because
 * {@see SqlFragment::__toString()} returns the SQL string, a fragment may be
 * string-cast or concatenated exactly like the bare string these methods used
 * to return.
 */
class ExpressionBuilder
{
	/** @var QueryBuilder owning query; parameters register on it */
	private $qb;

	/**
	 * @var array Allowlisted comparison operators for {@see ExpressionBuilder::comparison()}
	 *      and the value forms of where()/having(); '!=' normalises to '<>'.
	 *      Regular-expression matching is intentionally left to
	 *      {@see ExpressionBuilder::regexp()}, which sources its operator from the platform.
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
	 * @var array Allowlisted aggregate functions for {@see ExpressionBuilder::aggregate()}
	 *      and {@see ExpressionBuilder::aggregateComparison()}; the lookup key is the
	 *      uppercased function name.
	 */
	private static $aggregateFunctions = array(
		'COUNT' => 'COUNT',
		'SUM'   => 'SUM',
		'AVG'   => 'AVG',
		'MIN'   => 'MIN',
		'MAX'   => 'MAX',
	);

	/**
	 * @param QueryBuilder $qb
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
	 * @return SqlFragment
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
	 * @return SqlFragment
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
	 * @return SqlFragment
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
	 * @return SqlFragment
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
	 * @return SqlFragment
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
	 * @return SqlFragment
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
	 * @return SqlFragment
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
	 * @return SqlFragment
	 */
	public function notIn($column, array $values)
	{
		return $this->_inList($column, $values, 'NOT IN', '1=1');
	}

	/**
	 * `column` LIKE :pattern, with $pattern bound verbatim: the caller
	 * controls the % and _ wildcards. For matching plain substrings, use
	 * {@see ExpressionBuilder::contains()} instead.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return SqlFragment
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
	 * @return SqlFragment
	 */
	public function contains($column, $value)
	{
		return $this->_comparison($column, 'LIKE', '%'.$this->_escapeLike($value).'%');
	}

	/**
	 * Prefix match; see {@see ExpressionBuilder::contains()} for wildcard handling.
	 *
	 * @param string $column
	 * @param string $value
	 * @return SqlFragment
	 */
	public function startsWith($column, $value)
	{
		return $this->_comparison($column, 'LIKE', $this->_escapeLike($value).'%');
	}

	/**
	 * Suffix match; see {@see ExpressionBuilder::contains()} for wildcard handling.
	 *
	 * @param string $column
	 * @param string $value
	 * @return SqlFragment
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
	 * @return SqlFragment
	 */
	public function regexp($column, $pattern)
	{
		return $this->_comparison($column, $this->qb->getPlatform()->getRegexpOperator(), $pattern);
	}

	/**
	 * <code>FIND_IN_SET(:value, `column`)</code> - true when $value is one of
	 * the comma-separated values stored in $column. This is e107's recurring
	 * userclass-membership idiom; the needle binds as one parameter and the
	 * column validates fail-closed.
	 *
	 * @param string $column comma-separated SET column (the haystack)
	 * @param mixed $value the needle searched for in the set
	 * @return SqlFragment
	 * @throws InvalidArgumentException when the column name fails validation.
	 */
	public function findInSet($column, $value)
	{
		return SqlFragment::fragment('FIND_IN_SET('.$this->qb->createNamedParameter($value).', '.$this->qb->quoteColumn($column).')');
	}

	/**
	 * `column` IS NULL
	 *
	 * @param string $column
	 * @return SqlFragment
	 */
	public function isNull($column)
	{
		return SqlFragment::fragment($this->qb->quoteColumn($column).' IS NULL');
	}

	/**
	 * `column` IS NOT NULL
	 *
	 * @param string $column
	 * @return SqlFragment
	 */
	public function isNotNull($column)
	{
		return SqlFragment::fragment($this->qb->quoteColumn($column).' IS NOT NULL');
	}

	/**
	 * Generic "`column` OP :value" with OP checked against the operator
	 * allowlist ('!=' normalises to '<>'). For IN/NOT IN, $value is taken as a
	 * list and delegated to {@see ExpressionBuilder::in()}/{@see ExpressionBuilder::notIn()}.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return SqlFragment
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
	 * @return SqlFragment
	 */
	public function between($column, $min, $max)
	{
		return SqlFragment::fragment($this->qb->quoteColumn($column).' BETWEEN '
			.$this->qb->createNamedParameter($min).' AND '.$this->qb->createNamedParameter($max));
	}

	/**
	 * `column` NOT BETWEEN :min AND :max.
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return SqlFragment
	 */
	public function notBetween($column, $min, $max)
	{
		return SqlFragment::fragment($this->qb->quoteColumn($column).' NOT BETWEEN '
			.$this->qb->createNamedParameter($min).' AND '.$this->qb->createNamedParameter($max));
	}

	/**
	 * `column` NOT LIKE :pattern, with $pattern bound verbatim; see
	 * {@see ExpressionBuilder::like()}.
	 *
	 * @param string $column
	 * @param string $pattern
	 * @return SqlFragment
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
	 * @return SqlFragment
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

		return SqlFragment::fragment($this->qb->quoteColumn($first).' '.$op.' '.$this->qb->quoteColumn($second));
	}

	/**
	 * Combine pre-built fragments with AND, each parenthesised. Foreign
	 * fragments (e.g. {@see QueryBuilder::raw()} carrying its own binds) have
	 * their parameters merged onto the owning query, so no bind is dropped; the
	 * returned fragment therefore carries an empty parameter map of its own.
	 *
	 * @param SqlFragment|string ...$parts
	 * @return SqlFragment
	 */
	public function allOf(...$parts)
	{
		return $this->_combine('AND', $parts);
	}

	/**
	 * Combine pre-built fragments with OR; see {@see ExpressionBuilder::allOf()}.
	 *
	 * @param SqlFragment|string ...$parts
	 * @return SqlFragment
	 */
	public function anyOf(...$parts)
	{
		return $this->_combine('OR', $parts);
	}

	/**
	 * Negate a pre-built fragment: NOT (...). A foreign fragment's parameters
	 * are merged onto the owning query; the returned fragment carries none of
	 * its own; see {@see ExpressionBuilder::allOf()}.
	 *
	 * @param SqlFragment|string $fragment
	 * @return SqlFragment
	 */
	public function not($fragment)
	{
		return $this->_combine('NOT', array($fragment));
	}

	/**
	 * A vouched aggregate expression, "FUNC(`col`) [AS `alias`]", binding
	 * nothing. The function is checked against the allowlist
	 * {COUNT,SUM,AVG,MIN,MAX}; the column (and the alias, when given) are
	 * validated identifiers, and '*' is accepted only for COUNT.
	 *
	 * @param string $function One of COUNT,SUM,AVG,MIN,MAX (case-insensitive).
	 * @param string $column Column name, or '*' for COUNT.
	 * @param string|null $alias Optional column alias; validated and quoted.
	 * @return SqlFragment
	 * @throws InvalidArgumentException on a bad function, column or alias.
	 */
	public function aggregate($function, $column, $alias = null)
	{
		$sql = $this->_aggregateExpression($function, $column);

		if($alias !== null)
		{
			$sql .= ' AS '.$this->qb->quoteColumn($alias);
		}

		return SqlFragment::fragment($sql);
	}

	/**
	 * A vouched string-aggregation expression (MySQL GROUP_CONCAT, PostgreSQL
	 * string_agg, ...), "GROUP_CONCAT([DISTINCT ]`col`[ ORDER BY ...] SEPARATOR
	 * 'sep')[ AS `alias`]" on MySQL, compiled through the platform dialect
	 * ({@see PlatformInterface::compileGroupConcat()}), binding nothing.
	 *
	 * The column, the ORDER BY columns, and the alias are validated
	 * identifiers; ORDER BY directions are checked against {ASC, DESC}. The
	 * separator is inlined as a driver-quoted string literal
	 * ({@see ConnectionInterface::quoteStringLiteral()}) because some dialects
	 * reject a bound parameter in that position; it is developer-authored and
	 * must never receive user input.
	 *
	 * @param string $column Aggregated column name (or table.column).
	 * @param string|null $alias Optional column alias; validated and quoted.
	 * @param array $orderBy column => 'ASC'|'DESC' pairs; may be empty.
	 * @param string $separator Separator string; defaults to ','.
	 * @param bool $distinct Aggregate only distinct values.
	 * @return SqlFragment
	 * @throws InvalidArgumentException on a bad column, alias or direction.
	 */
	public function groupConcat($column, $alias = null, array $orderBy = array(), $separator = ',', $distinct = false)
	{
		$quotedOrderBy = array();

		foreach($orderBy as $orderColumn => $direction)
		{
			$dir = strtoupper((string) $direction);

			if($dir !== 'ASC' && $dir !== 'DESC')
			{
				throw new InvalidArgumentException('groupConcat() ORDER BY direction must be ASC or DESC: '.$direction);
			}

			$quotedOrderBy[] = $this->qb->quoteColumn($orderColumn).' '.$dir;
		}

		$sql = $this->qb->getPlatform()->compileGroupConcat(
			$this->qb->quoteColumn($column),
			$quotedOrderBy,
			$this->qb->quoteStringLiteral((string) $separator),
			(bool) $distinct
		);

		if($alias !== null)
		{
			$sql .= ' AS '.$this->qb->quoteColumn($alias);
		}

		return SqlFragment::fragment($sql);
	}

	/**
	 * Compare an aggregate against a bound value, "FUNC(`col`) OP :value". The
	 * function and column follow {@see ExpressionBuilder::aggregate()}; the operator is
	 * checked against the allowlist (IN/NOT IN are rejected) and the one value
	 * is bound on the owning query, so the returned fragment carries no
	 * parameters of its own.
	 *
	 * @param string $function One of COUNT,SUM,AVG,MIN,MAX (case-insensitive).
	 * @param string $column Column name, or '*' for COUNT.
	 * @param string $operator
	 * @param mixed $value
	 * @return SqlFragment
	 * @throws InvalidArgumentException on a bad function, column or operator.
	 */
	public function aggregateComparison($function, $column, $operator, $value)
	{
		$expr = $this->_aggregateExpression($function, $column);
		$op = self::operator($operator);

		if($op === 'IN' || $op === 'NOT IN')
		{
			throw new InvalidArgumentException('Aggregate comparisons do not support IN/NOT IN.');
		}

		return SqlFragment::fragment($expr.' '.$op.' '.$this->qb->createNamedParameter($value));
	}

	/**
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return SqlFragment
	 */
	private function _comparison($column, $operator, $value)
	{
		return SqlFragment::fragment($this->qb->quoteColumn($column).' '.$operator.' '.$this->qb->createNamedParameter($value));
	}

	/**
	 * @param string $column
	 * @param array $values
	 * @param string $operator 'IN' or 'NOT IN'
	 * @param string $emptyResult predicate to emit for an empty list
	 * @return SqlFragment
	 */
	private function _inList($column, array $values, $operator, $emptyResult)
	{
		$quoted = $this->qb->quoteColumn($column); // validate even when the list is empty

		if(count($values) === 0)
		{
			return SqlFragment::fragment($emptyResult);
		}

		$placeholders = array();

		foreach($values as $value)
		{
			$placeholders[] = $this->qb->createNamedParameter($value);
		}

		return SqlFragment::fragment($quoted.' '.$operator.' ('.implode(', ', $placeholders).')');
	}

	/**
	 * Combine fragments with a conjunction, merging each {@see SqlFragment} part's
	 * parameters onto the owning query so a foreign raw() part cannot silently
	 * drop its binds. The 'NOT' conjunction negates the single part as
	 * "NOT (...)"; 'AND'/'OR' parenthesise each part and join them.
	 *
	 * @param string $conjunction 'AND', 'OR' or 'NOT'
	 * @param array $parts list of {@see SqlFragment}|string
	 * @return SqlFragment empty-parameter fragment
	 */
	private function _combine($conjunction, array $parts)
	{
		$strings = array();

		foreach($parts as $part)
		{
			if($part instanceof SqlFragment)
			{
				$this->qb->mergeParameters($part->getParameters());
			}

			$strings[] = (string) $part;
		}

		if($conjunction === 'NOT')
		{
			return SqlFragment::fragment('NOT ('.$strings[0].')');
		}

		return SqlFragment::fragment('('.implode(') '.$conjunction.' (', $strings).')');
	}

	/**
	 * Build the "FUNC(`col`)" core shared by {@see ExpressionBuilder::aggregate()} and
	 * {@see ExpressionBuilder::aggregateComparison()}: the function is allowlisted and
	 * the column validated, with '*' accepted only for COUNT.
	 *
	 * @param string $function
	 * @param string $column
	 * @return string
	 * @throws InvalidArgumentException on a bad function or column.
	 */
	private function _aggregateExpression($function, $column)
	{
		$fn = self::_aggregateFunction($function);

		if($column === '*')
		{
			if($fn !== 'COUNT')
			{
				throw new InvalidArgumentException('The * column is only valid for COUNT, not '.$fn.'.');
			}

			return $fn.'(*)';
		}

		return $fn.'('.$this->qb->quoteColumn($column).')';
	}

	/**
	 * Normalise and validate an aggregate function name against the allowlist
	 * {COUNT,SUM,AVG,MIN,MAX}, mirroring {@see ExpressionBuilder::operator()}.
	 *
	 * @param string $function
	 * @return string canonical (uppercase) function name
	 * @throws InvalidArgumentException on an unsupported function.
	 */
	private static function _aggregateFunction($function)
	{
		$fn = strtoupper(trim((string) $function));

		if(!isset(self::$aggregateFunctions[$fn]))
		{
			throw new InvalidArgumentException('Unsupported aggregate function: '.$function);
		}

		return self::$aggregateFunctions[$fn];
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
