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

use e107\Database\Schema\Plan\ChangeInterface;
use e107\Database\SqlFragment;
use InvalidArgumentException;
use RuntimeException;

/**
 * What every {@see ChangeInterface} carries: the table it acts on and the
 * schema file that asked for it.
 */
abstract class AbstractChange implements ChangeInterface
{
	/** @var string 'core' or a plugin folder */
	private $sqlFile;

	/** @var string unprefixed logical table name */
	private $table;

	/**
	 * @param string $sqlFile 'core' or the plugin folder that declared the table.
	 * @param string $table Unprefixed logical table name.
	 * @throws InvalidArgumentException on an empty table name.
	 */
	public function __construct($sqlFile, $table)
	{
		$this->sqlFile = (string) $sqlFile;
		$this->table = trim((string) $table);

		if($this->table === '')
		{
			throw new InvalidArgumentException(get_class($this).' requires a non-empty table name.');
		}
	}

	/**
	 * @return string
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * @return string
	 */
	public function getSqlFile()
	{
		return $this->sqlFile;
	}

	/**
	 * @return bool
	 */
	public function mayLoseData()
	{
		return false;
	}

	/**
	 * A definition the server itself wrote, as a vouched fragment ready to be
	 * spliced into a statement.
	 *
	 * @param string|null $ddl The captured fragment.
	 * @param string $subject What it should have described, for the error message.
	 * @return SqlFragment
	 * @throws RuntimeException when nothing was captured.
	 */
	protected function captured($ddl, $subject)
	{
		if(!is_string($ddl) || trim($ddl) === '')
		{
			throw new RuntimeException(
				get_class($this).' for table "'.$this->table.'" has no captured definition for '.$subject.'. '
				.'A fix splices the definition the server wrote for the materialised schema; this one was built from '
				.'a schema that was never materialised, and nothing here invents DDL of its own.'
			);
		}

		return SqlFragment::raw($ddl);
	}

	/**
	 * An optional storage engine or character set as a change carries it:
	 * trimmed, or null when nothing but whitespace was stated.
	 *
	 * @param string|null $value
	 * @return string|null
	 */
	protected static function normaliseOption($value)
	{
		if($value === null)
		{
			return null;
		}

		$value = trim((string) $value);

		return ($value === '') ? null : $value;
	}
}
