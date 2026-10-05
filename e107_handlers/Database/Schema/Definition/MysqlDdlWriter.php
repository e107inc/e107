<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Definition;

use e107\Database\IdentifierFilter;
use InvalidArgumentException;

require_once(__DIR__.'/MysqlLiteral.php');
require_once(__DIR__.'/TableDefinition.php');

if(!class_exists(IdentifierFilter::class, false))
{
	require_once(dirname(dirname(__DIR__)).'/IdentifierFilter.php');
}

/**
 * Writes the engine-neutral {@see TableDefinition} model as MySQL DDL, which is also e107's schema DSL: the
 * counterpart of {@see MysqlDdlParser}. Parsing what it writes gives back an equal model.
 *
 * <code>
 * $writer = new MysqlDdlWriter();
 * echo $writer->writeBody($table); // the definitions, one per line
 * </code>
 */
final class MysqlDdlWriter
{
	/**
	 * The column and index definitions, comma-separated, one per line.
	 *
	 * @param TableDefinition $table
	 * @return string
	 */
	public function writeBody(TableDefinition $table)
	{
		$lines = array();

		foreach($table->getColumns() as $column)
		{
			$lines[] = '  '.$this->writeColumn($column);
		}

		foreach($table->getIndexes() as $index)
		{
			$lines[] = '  '.$this->writeIndex($index);
		}

		return implode(",\n", $lines);
	}

	/**
	 * A column definition with its name, e.g. "`hits` int(10) unsigned NOT NULL DEFAULT '0'".
	 *
	 * @param ColumnDefinition $column
	 * @return string
	 */
	public function writeColumn(ColumnDefinition $column)
	{
		return $this->quote($column->getName()).' '.$this->writeColumnType($column).$this->writeColumnAttributes($column);
	}

	/**
	 * @param ColumnDefinition $column
	 * @return string the type part of a column definition, e.g. "int(10) unsigned" or "enum('a','b')"
	 */
	private function writeColumnType(ColumnDefinition $column)
	{
		$sql = $column->getType();

		if($column->getMembers())
		{
			$members = array();
			foreach($column->getMembers() as $member)
			{
				$members[] = $this->literal($member);
			}
			$sql .= '('.implode(',', $members).')';
		}
		elseif($column->getLength() !== null)
		{
			$sql .= '('.$column->getLength().')';
		}

		if($column->isUnsigned())
		{
			$sql .= ' unsigned';
		}

		if($column->isZerofill())
		{
			$sql .= ' zerofill';
		}

		return $sql;
	}

	/**
	 * An index clause as it sits in a CREATE TABLE, e.g. "UNIQUE KEY `u` (`a`,`b`(20))".
	 *
	 * @param IndexDefinition $index
	 * @return string
	 */
	public function writeIndex(IndexDefinition $index)
	{
		$parts = array();

		foreach($index->getParts() as $part)
		{
			$parts[] = $this->quote($part['column'])
				.($part['length'] !== null ? '('.$part['length'].')' : '')
				.($part['direction'] === 'DESC' ? ' DESC' : '');
		}

		$columns = ' ('.implode(',', $parts).')';

		switch($index->getKind())
		{
			case IndexDefinition::KIND_PRIMARY:
				return 'PRIMARY KEY'.$columns;

			case IndexDefinition::KIND_UNIQUE:
				return 'UNIQUE KEY '.$this->quote($index->getName()).$columns;

			case IndexDefinition::KIND_FULLTEXT:
				return 'FULLTEXT KEY '.$this->quote($index->getName()).$columns;

			case IndexDefinition::KIND_SPATIAL:
				return 'SPATIAL KEY '.$this->quote($index->getName()).$columns;
		}

		return 'KEY '.$this->quote($index->getName()).$columns;
	}

	/**
	 * @param ColumnDefinition $column
	 * @return string attributes after the type, each with its leading space
	 */
	private function writeColumnAttributes(ColumnDefinition $column)
	{
		$sql = '';

		if($column->getCharset() !== null)
		{
			$sql .= ' CHARACTER SET '.$column->getCharset();
		}

		if($column->getCollation() === 'binary')
		{
			$sql .= ' BINARY';
		}
		elseif($column->getCollation() !== null)
		{
			$sql .= ' COLLATE '.$column->getCollation();
		}

		if(!$column->isNullable())
		{
			$sql .= ' NOT NULL';
		}

		switch($column->getDefaultKind())
		{
			case ColumnDefinition::DEFAULT_NULL:
				$sql .= ' DEFAULT NULL';
				break;

			case ColumnDefinition::DEFAULT_LITERAL:
				$sql .= ' DEFAULT '.(($column->isNumeric() && MysqlLiteral::isDecimal($column->getDefault())) ? $column->getDefault() : $this->literal($column->getDefault()));
				break;

			case ColumnDefinition::DEFAULT_EXPRESSION:
				$sql .= ' DEFAULT '.$column->getDefault();
				break;
		}

		if($column->isAutoIncrement())
		{
			$sql .= ' AUTO_INCREMENT';
		}

		if($column->getOnUpdate() !== null)
		{
			$sql .= ' ON UPDATE '.$column->getOnUpdate();
		}

		if($column->getComment() !== null)
		{
			$sql .= ' COMMENT '.$this->literal($column->getComment());
		}

		return $sql;
	}

	/**
	 * @param string $name
	 * @return string
	 * @throws InvalidArgumentException on a name outside the {@see IdentifierFilter} grammar
	 */
	private function quote($name)
	{
		$quoted = IdentifierFilter::identifier($name);

		if($quoted === false)
		{
			throw new InvalidArgumentException('Invalid name "'.$name.'" for the schema DSL.');
		}

		return $quoted;
	}

	/**
	 * A MySQL string literal: backslashes and quotes escaped.
	 *
	 * @param string $value
	 * @return string
	 */
	private function literal($value)
	{
		return "'".str_replace(array('\\', "'"), array('\\\\', "''"), (string) $value)."'";
	}
}
