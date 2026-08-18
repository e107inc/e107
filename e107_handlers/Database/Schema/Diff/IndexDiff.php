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

use e107\Database\Schema\Introspect\IndexSchema;

/**
 * One live index that does not match its declared counterpart, holding both
 * whole {@see IndexSchema} objects. Immutable.
 */
final class IndexDiff
{
	/** @var IndexSchema the index as the schema file declares it */
	private $expected;

	/** @var IndexSchema the index as the database has it */
	private $actual;

	/** @var string[] */
	private $changedFields;

	/**
	 * @param IndexSchema $expected Declared index, materialised so the server has named and ordered it.
	 * @param IndexSchema $actual Live index of the same name.
	 */
	public function __construct(IndexSchema $expected, IndexSchema $actual)
	{
		$this->expected = $expected;
		$this->actual = $actual;
		$this->changedFields = $this->_computeChangedFields();
	}

	/**
	 * The index name, taken from the expected side.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->expected->getName();
	}

	/**
	 * @return IndexSchema
	 */
	public function getExpected()
	{
		return $this->expected;
	}

	/**
	 * @return IndexSchema
	 */
	public function getActual()
	{
		return $this->actual;
	}

	/**
	 * Names of the {@see IndexSchema} fields that differ, in the order
	 * {@see IndexSchema::toArray()} lists them.
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
