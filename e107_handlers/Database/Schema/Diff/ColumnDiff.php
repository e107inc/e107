<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Diff;

use e107\Database\Schema\Introspect\ColumnSchema;

/**
 * One live column that does not match its declared counterpart, holding both
 * whole {@see ColumnSchema} objects. Immutable.
 */
final class ColumnDiff
{
	/** @var string[] fields that never count as a difference */
	private static $ignoredFields = array('position');

	/** @var ColumnSchema the column as the schema file declares it */
	private $expected;

	/** @var ColumnSchema the column as the database has it */
	private $actual;

	/** @var string[] */
	private $changedFields;

	/**
	 * @param ColumnSchema $expected Declared column, materialised so the server has normalised it.
	 * @param ColumnSchema $actual Live column of the same name.
	 */
	public function __construct(ColumnSchema $expected, ColumnSchema $actual)
	{
		$this->expected = $expected;
		$this->actual = $actual;
		$this->changedFields = $this->_computeChangedFields();
	}

	/**
	 * The column name, taken from the expected side.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->expected->getName();
	}

	/**
	 * @return ColumnSchema
	 */
	public function getExpected()
	{
		return $this->expected;
	}

	/**
	 * @return ColumnSchema
	 */
	public function getActual()
	{
		return $this->actual;
	}

	/**
	 * Names of the {@see ColumnSchema} fields that differ, in the order
	 * {@see ColumnSchema::toArray()} lists them. 'position' is never among them.
	 *
	 * @return string[]
	 */
	public function getChangedFields()
	{
		return $this->changedFields;
	}

	/**
	 * @return bool
	 */
	public function hasChanges()
	{
		return count($this->changedFields) > 0;
	}

	/**
	 * @return string[]
	 */
	private function _computeChangedFields()
	{
		$expected = $this->expected->toArray();
		$actual = $this->actual->toArray();
		$changed = array();

		foreach(array_keys($expected + $actual) as $field)
		{
			if(in_array($field, self::$ignoredFields, true))
			{
				continue;
			}

			$left = array_key_exists($field, $expected) ? $expected[$field] : null;
			$right = array_key_exists($field, $actual) ? $actual[$field] : null;

			if($left !== $right)
			{
				$changed[] = $field;
			}
		}

		return $changed;
	}
}
