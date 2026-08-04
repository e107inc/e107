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
use e107\Database\Platform\PlatformInterface;
use e107\Database\SqlFragment;
use InvalidArgumentException;

/**
 * Identifier and definition guards shared by {@see SchemaBuilder} and its
 * per-table batching context {@see Table}.
 *
 * Every method fails closed: a table, column, index, engine, charset or host
 * that falls outside its grammar throws rather than being "cleaned", and a
 * column/index definition must be a structured value object
 * ({@see Column}/{@see Index}) or a vouched {@see SqlFragment} fragment -
 * a bare string is rejected, the same rule the query builder enforces at its
 * structured seams.
 */
trait SchemaBuilderTrait
{
	/** @var ConnectionInterface connection the schema operations run on */
	protected $db;

	/** @var PlatformInterface SQL dialect that spells the DDL skeletons */
	protected $platform;

	/**
	 * Resolve a logical e107 table name to its quoted physical name (prefix and
	 * language routing applied), fail-closed.
	 *
	 * @param string $table
	 * @return string backtick-quoted physical name
	 * @throws InvalidArgumentException on an invalid table name.
	 */
	protected function quoteTable($table)
	{
		$physical = $this->db->resolveTableName($table);

		if($physical === false)
		{
			throw new InvalidArgumentException('Invalid table name "'.$table.'" for a schema operation.');
		}

		return '`'.$physical.'`';
	}

	/**
	 * Resolve a logical table name to its quoted physical name applying the
	 * prefix only, fail-closed, with no multi-language lan_* routing (see
	 * {@see ConnectionInterface::resolvePhysicalTableName()}).
	 *
	 * @param string $table
	 * @return string backtick-quoted physical name
	 * @throws InvalidArgumentException on an invalid table name.
	 */
	protected function quotePhysicalTable($table)
	{
		$physical = $this->db->resolvePhysicalTableName($table);

		if($physical === false)
		{
			throw new InvalidArgumentException('Invalid table name "'.$table.'" for a schema operation.');
		}

		return '`'.$physical.'`';
	}

	/**
	 * Validate and backtick-quote a column identifier, fail-closed.
	 *
	 * @param string $name
	 * @return string
	 * @throws InvalidArgumentException on an invalid identifier.
	 */
	protected function quoteColumn($name)
	{
		$quoted = $this->db->quoteIdentifier($name);

		if($quoted === false)
		{
			throw new InvalidArgumentException('Invalid column name "'.$name.'" for a schema operation.');
		}

		return $quoted;
	}

	/**
	 * Validate and backtick-quote a bare identifier (database or user name) that
	 * carries no e107 prefix or language routing, fail-closed.
	 *
	 * @param string $name
	 * @param string $what label for the error message
	 * @return string
	 * @throws InvalidArgumentException on an invalid identifier.
	 */
	protected function quoteBareIdentifier($name, $what)
	{
		if(!is_string($name) || !preg_match('/^[A-Za-z0-9_]+$/D', $name))
		{
			throw new InvalidArgumentException('Invalid '.$what.' "'.$name.'" for a schema operation.');
		}

		return '`'.$name.'`';
	}

	/**
	 * Render a column definition argument, accepting a structured
	 * {@see Column} or a vouched {@see SqlFragment} fragment.
	 *
	 * @param Column|SqlFragment $definition
	 * @return string
	 * @throws InvalidArgumentException on a bare string or other type.
	 */
	protected function resolveColumnDefinition($definition)
	{
		if($definition instanceof Column)
		{
			return $definition->getDefinition();
		}

		if($definition instanceof SqlFragment)
		{
			return $definition->getSql();
		}

		throw new InvalidArgumentException('A column definition must be a Column or a vouched SqlFragment (e.g. $qb->raw(...)); a bare string is not accepted.');
	}

	/**
	 * Render an index definition argument, accepting a structured
	 * {@see Index} or a vouched {@see SqlFragment} fragment.
	 *
	 * @param Index|SqlFragment $definition
	 * @return string
	 * @throws InvalidArgumentException on a bare string or other type.
	 */
	protected function resolveIndexDefinition($definition)
	{
		if($definition instanceof Index)
		{
			return $definition->getDefinition();
		}

		if($definition instanceof SqlFragment)
		{
			return $definition->getSql();
		}

		throw new InvalidArgumentException('An index definition must be an Index or a vouched SqlFragment (e.g. $qb->raw(...)); a bare string is not accepted.');
	}

	/**
	 * Validate a storage-engine name, fail-closed.
	 *
	 * @param string $engine
	 * @return string
	 * @throws InvalidArgumentException
	 */
	protected function validateEngine($engine)
	{
		if(!is_string($engine) || !preg_match('/^[A-Za-z0-9_]+$/D', $engine))
		{
			throw new InvalidArgumentException('Invalid storage engine "'.$engine.'".');
		}

		return $engine;
	}

	/**
	 * Validate a character-set name, fail-closed.
	 *
	 * @param string $charset
	 * @return string
	 * @throws InvalidArgumentException
	 */
	protected function validateCharset($charset)
	{
		if(!is_string($charset) || !preg_match('/^[A-Za-z0-9_]+$/D', $charset))
		{
			throw new InvalidArgumentException('Invalid character set "'.$charset.'".');
		}

		return $charset;
	}

	/**
	 * Validate a grant host (hostname, IP or '%' wildcard), fail-closed. It is
	 * placed in a single-quoted literal, so the grammar excludes quotes.
	 *
	 * @param string $host
	 * @return string
	 * @throws InvalidArgumentException
	 */
	protected function validateHost($host)
	{
		if(!is_string($host) || !preg_match('/^[A-Za-z0-9_.:%-]+$/D', $host))
		{
			throw new InvalidArgumentException('Invalid grant host "'.$host.'".');
		}

		return $host;
	}
}
