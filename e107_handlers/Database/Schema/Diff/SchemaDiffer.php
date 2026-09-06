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

use e107\Database\Schema\Introspect\TableSchema;
use InvalidArgumentException;

/**
 * Compares one declared table against its live counterpart and reports the
 * difference as a {@see TableDiff}.
 *
 * Compares field by field and applies no equivalence rules of its own, so both
 * sides must be read by the same reader from the same server.
 *
 * Stateless; one instance may diff any number of tables.
 */
final class SchemaDiffer
{
	/**
	 * @param string $sqlFile 'core' or the plugin folder that declared the table.
	 * @param TableSchema|null $expected The declared shape, materialised and read back; required.
	 * @param TableSchema|null $actual The live shape, or null when the table is absent.
	 * @param string|null $tableName Unprefixed logical name; neither TableSchema carries it reliably, so pass it.
	 * @param array $disownedIndexNames Live index names to file as redundant rather than extra when $expected omits them.
	 * @return TableDiff
	 * @throws InvalidArgumentException when a side is neither a TableSchema nor null, or when $expected is null or has no columns.
	 */
	public function diff($sqlFile, $expected = null, $actual = null, $tableName = null, array $disownedIndexNames = array())
	{
		if($expected !== null && !$expected instanceof TableSchema)
		{
			throw new InvalidArgumentException('SchemaDiffer::diff() was given a declared shape that is not a TableSchema.');
		}

		if($actual !== null && !$actual instanceof TableSchema)
		{
			throw new InvalidArgumentException('SchemaDiffer::diff() was given a live shape that is not a TableSchema.');
		}

		if($expected === null)
		{
			throw new InvalidArgumentException(
				'SchemaDiffer::diff() needs a declared TableSchema to compare against. '
				.'Nothing can be said about a table with no declaration, and saying it anyway is how a fix goes missing.'
			);
		}

		if(count($expected->getColumns()) === 0)
		{
			throw new InvalidArgumentException(
				'SchemaDiffer::diff() was given a declared shape for "'.$expected->getName().'" that has no columns. '
				.'No table has none, so the declaration did not survive materialisation. Comparing against it would '
				.'file every live column as merely extra and pronounce a drifted table clean.'
			);
		}

		$name = $this->_resolveTableName($tableName, $expected, $actual);

		if($actual === null)
		{
			return TableDiff::missingTable($sqlFile, $name, $expected);
		}

		$parts = array(
			'expectedTable' => $expected,
			'engineChange'  => $this->_engineChange($expected, $actual),
			'charsetChange' => $this->_charsetChange($expected, $actual),
		);

		$parts = array_merge(
			$parts,
			$this->_columnParts($expected, $actual),
			$this->_indexParts($expected, $actual, $disownedIndexNames)
		);

		return new TableDiff($sqlFile, $name, $parts);
	}

	/**
	 * @param string|null $tableName
	 * @param TableSchema $expected
	 * @param TableSchema|null $actual
	 * @return string
	 */
	private function _resolveTableName($tableName, TableSchema $expected, $actual)
	{
		$tableName = ($tableName === null) ? '' : trim((string) $tableName);

		if($tableName !== '')
		{
			return $tableName;
		}

		if($expected->getName() !== '')
		{
			return $expected->getName();
		}

		return ($actual === null) ? '' : $actual->getName();
	}

	/**
	 * The engine difference, compared case-insensitively. A declared side that
	 * states no engine is not reported.
	 *
	 * @param TableSchema $expected
	 * @param TableSchema $actual
	 * @return array|null ['expected'=>string, 'actual'=>string]
	 */
	private function _engineChange(TableSchema $expected, TableSchema $actual)
	{
		$declared = $expected->getEngine();

		if($declared === '' || $expected->hasEngine($actual->getEngine()))
		{
			return null;
		}

		return array('expected' => $declared, 'actual' => $actual->getEngine());
	}

	/**
	 * The character set difference, compared exactly. A declared side that
	 * states no character set is not reported.
	 *
	 * @param TableSchema $expected
	 * @param TableSchema $actual
	 * @return array|null ['expected'=>string, 'actual'=>string|null]
	 */
	private function _charsetChange(TableSchema $expected, TableSchema $actual)
	{
		$declared = $expected->getCharset();

		if($declared === null || $declared === '' || $declared === $actual->getCharset())
		{
			return null;
		}

		return array('expected' => $declared, 'actual' => $actual->getCharset());
	}

	/**
	 * Columns sorted into missing, modified and extra, each list keyed by column
	 * name. The missing list keeps the declared ordinal order.
	 *
	 * @param TableSchema $expected
	 * @param TableSchema $actual
	 * @return array TableDiff parts.
	 */
	private function _columnParts(TableSchema $expected, TableSchema $actual)
	{
		$missing = array();
		$modified = array();
		$extra = array();

		foreach($expected->getColumns() as $columnName => $declaredColumn)
		{
			$liveColumn = $actual->getColumn($columnName);

			if($liveColumn === null)
			{
				$missing[$columnName] = $declaredColumn;
				continue;
			}

			if(!$declaredColumn->equals($liveColumn))
			{
				$modified[$columnName] = new ColumnDiff($declaredColumn, $liveColumn);
			}
		}

		foreach($actual->getColumns() as $columnName => $liveColumn)
		{
			if($expected->getColumn($columnName) === null)
			{
				$extra[$columnName] = $liveColumn;
			}
		}

		return array(
			'missingColumns'  => $missing,
			'modifiedColumns' => $modified,
			'extraColumns'    => $extra,
		);
	}

	/**
	 * Indexes sorted into missing, modified, extra and redundant, each list keyed
	 * by index name.
	 *
	 * Matching is by name alone, and an index is then compared whole through
	 * {@see \e107\Database\Schema\Introspect\IndexSchema::equals()}.
	 *
	 * @param TableSchema $expected
	 * @param TableSchema $actual
	 * @param array $disownedIndexNames see {@see SchemaDiffer::diff()}.
	 * @return array TableDiff parts.
	 */
	private function _indexParts(TableSchema $expected, TableSchema $actual, array $disownedIndexNames = array())
	{
		$missing = array();
		$modified = array();
		$extra = array();
		$redundant = array();

		foreach($expected->getIndexes() as $indexName => $declaredIndex)
		{
			$liveIndex = $actual->getIndex($indexName);

			if($liveIndex === null)
			{
				$missing[$indexName] = $declaredIndex;
				continue;
			}

			if(!$declaredIndex->equals($liveIndex))
			{
				$modified[$indexName] = new IndexDiff($declaredIndex, $liveIndex);
			}
		}

		foreach($actual->getIndexes() as $indexName => $liveIndex)
		{
			if($expected->getIndex($indexName) !== null)
			{
				continue;
			}

			if(in_array($indexName, $disownedIndexNames, true))
			{
				$redundant[$indexName] = $liveIndex;

				continue;
			}

			$extra[$indexName] = $liveIndex;
		}

		return array(
			'missingIndexes'   => $missing,
			'modifiedIndexes'  => $modified,
			'extraIndexes'     => $extra,
			'redundantIndexes' => $redundant,
		);
	}
}
