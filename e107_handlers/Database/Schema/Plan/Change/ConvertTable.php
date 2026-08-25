<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Plan\Change;

use e107\Database\Schema\SchemaBuilder;
use InvalidArgumentException;

/**
 * Move a table onto its intended storage engine and character set.
 *
 * Renders two statements, the engine before the character set. Either half may
 * be omitted, but not both.
 */
final class ConvertTable extends AbstractChange
{
	/** @var string|null intended storage engine, or null when it already matches */
	private $engine;

	/** @var string|null intended character set, or null when it already matches */
	private $charset;

	/**
	 * @param string $sqlFile
	 * @param string $table Unprefixed logical table name.
	 * @param string|null $engine Intended storage engine, or null to leave it.
	 * @param string|null $charset Intended character set, or null to leave it.
	 * @throws InvalidArgumentException when neither is given.
	 */
	public function __construct($sqlFile, $table, $engine = null, $charset = null)
	{
		parent::__construct($sqlFile, $table);

		$this->engine = self::normaliseOption($engine);
		$this->charset = self::normaliseOption($charset);

		if($this->engine === null && $this->charset === null)
		{
			throw new InvalidArgumentException('ConvertTable for table "'.$this->getTable().'" was given neither an engine nor a character set; there would be nothing to run.');
		}
	}

	/**
	 * @return string|null
	 */
	public function getEngine()
	{
		return $this->engine;
	}

	/**
	 * @return string|null
	 */
	public function getCharset()
	{
		return $this->charset;
	}

	/**
	 * A character set conversion rewrites every value the target cannot hold; an engine change moves rows unaltered.
	 *
	 * @return bool
	 */
	public function mayLoseData()
	{
		return $this->charset !== null;
	}

	/**
	 * @return string
	 */
	public function describe()
	{
		$parts = array();

		if($this->engine !== null)
		{
			$parts[] = 'engine '.$this->engine;
		}

		if($this->charset !== null)
		{
			$parts[] = 'character set '.$this->charset;
		}

		return 'Convert table `'.$this->getTable().'` to '.implode(' and ', $parts);
	}

	/**
	 * @param SchemaBuilder $schema
	 * @return string[] the engine statement first, then the character set one.
	 */
	public function toSql(SchemaBuilder $schema)
	{
		$statements = array();

		if($this->engine !== null)
		{
			$statements[] = $schema->tablePhysical($this->getTable())->engine($this->engine)->getSQL();
		}

		if($this->charset !== null)
		{
			$statements[] = $schema->tablePhysical($this->getTable())->charset($this->charset)->getSQL();
		}

		return $statements;
	}
}
