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

use e107;

/**
 * A vouched SQL fragment: a pre-built SQL string paired with the parameters it
 * binds.
 *
 * This is the one type the query builder accepts wherever a raw SQL expression
 * used to be spliced in verbatim. Two provenances share the class, and the
 * runtime discriminator everywhere is simply `instanceof SqlFragment`:
 *
 *  - safe-by-construction fragments, authored by {@see ExpressionBuilder} (and by the
 *    builder itself) through {@see SqlFragment::fragment()}; their values are
 *    already bound on the owning {@see QueryBuilder}, so they carry no parameters
 *    of their own and need no warning;
 *  - developer-vouched SQL, passed verbatim through {@see SqlFragment::raw()} or a
 *    whole builder through {@see SqlFragment::fromQuery()} - the sole explicit
 *    escape hatch for SQL the builder cannot express.
 *
 * The carrier never quotes an identifier, never spells a dialect, and never
 * binds a value; it only transports a string and a parameter map produced by
 * the builder's own primitives. A builder seam accepts a SqlFragment as vouched
 * and rejects a bare string.
 *
 * <code>
 * $sql = e107::getDb();
 * $qb  = $sql->createQueryBuilder();
 * $qb->select('user_id')->from('user')
 *    ->where($qb->raw('user_lastvisit > '.$qb->createNamedParameter($since)));
 * </code>
 *
 * @see QueryBuilder::raw() thin sugar for {@see SqlFragment::raw()}
 */
final class SqlFragment
{
	/** @var string the pre-built SQL fragment; stored once, never recomputed */
	private $sql;

	/**
	 * @var array bound parameters in {@see ConnectionInterface::execute()} shape:
	 *      name => value | array('value' => mixed, 'type' => int)
	 */
	private $params;

	/**
	 * Not public: a fragment is only ever minted through one of the named
	 * factories, each of which documents the trust it implies.
	 *
	 * @param string $sql
	 * @param array $params
	 */
	private function __construct($sql, array $params = array())
	{
		$this->sql = (string) $sql;
		$this->params = $params;
	}

	/**
	 * Wrap developer-authored SQL verbatim. The SOLE explicit raw-SQL hatch.
	 *
	 * The fragment is spliced into the query unchanged, so it must NEVER carry
	 * user input. Bind every value with
	 * {@see QueryBuilder::createNamedParameter()} and splice the placeholder, or
	 * pass uniquely-named binds in $params:
	 *
	 * <code>
	 * $qb->where($qb->raw('FIND_IN_SET('.$qb->createNamedParameter($id).', user_plugin)'));
	 * $frag = SqlFragment::raw('a = :a AND b = :b', array('a' => 1, 'b' => 2));
	 * </code>
	 *
	 * @param string $sql
	 * @param array $params name => value | array('value' => mixed, 'type' => int)
	 * @return SqlFragment
	 */
	public static function raw($sql, array $params = array())
	{
		return new self($sql, $params);
	}

	/**
	 * Wrap a whole builder for a legacy slot that expects one SQL string (e.g. a
	 * subquery handed to a caller-SQL API). Forwards the builder's compiled SQL
	 * and the parameters it has bound.
	 *
	 * @param QueryBuilder $qb
	 * @return SqlFragment
	 */
	public static function fromQuery($qb)
	{
		return new self($qb->getSQL(), $qb->getParameters());
	}

	/**
	 * Mint a safe-by-construction fragment authored by the builder or
	 * {@see ExpressionBuilder}. The values are already bound on the owning query, so
	 * $params defaults to empty; this factory is internal and carries no
	 * developer-SQL warning.
	 *
	 * @internal
	 * @param string $sql
	 * @param array $params
	 * @return SqlFragment
	 */
	public static function fragment($sql, array $params = array())
	{
		return new self($sql, $params);
	}

	/**
	 * The stored SQL fragment. A getter over a pre-built field: it never
	 * recomputes, so it is safe to call from {@see SqlFragment::__toString()}.
	 *
	 * @return string
	 */
	public function getSql()
	{
		return $this->sql;
	}

	/**
	 * The parameters this fragment binds, in {@see ConnectionInterface::execute()} shape.
	 * Builder and expr fragments carry an empty map; only {@see SqlFragment::raw()}
	 * and {@see SqlFragment::fromQuery()} carry binds.
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->params;
	}

	/**
	 * Total string coercion: returns the stored fragment and never throws, so
	 * the builder may freely strval() a fragment while assembling SQL.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getSql();
	}
}
