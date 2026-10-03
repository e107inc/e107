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

use e107\Database\Schema\Introspect\TableSchema;

/**
 * The field-type map e107's typed writes bind with ({@see \e107\Database\ConnectionInterface::getFieldDefs()}), read
 * off a live table when no db_field_defs.php file describes it.
 *
 * '_FIELD_TYPES' maps integer columns to 'int' and character columns to 'escape'; other types are left to the
 * '_DEFAULT' the writer applies. '_NOTNULL' gives every NOT NULL column that has no default an empty-string stand-in,
 * which a legacy insert binds when the caller leaves the column out.
 */
final class FieldTypeMap
{
	/** @var string[] base types bound as integers */
	private static $integerTypes = array('int', 'integer', 'smallint', 'shortint', 'tinyint', 'mediumint', 'bigint');

	/** @var string[] base types bound as escaped strings */
	private static $stringTypes = array('char', 'text', 'varchar', 'tinytext', 'mediumtext', 'longtext', 'enum');

	/**
	 * @param TableSchema $table
	 * @return array possibly holding '_FIELD_TYPES' and '_NOTNULL', each column name => token
	 */
	public static function fromTableSchema(TableSchema $table)
	{
		$defs = array();

		foreach($table->getColumns() as $name => $column)
		{
			$base = self::baseType($column->getColumnType());

			if(in_array($base, self::$integerTypes, true))
			{
				$defs['_FIELD_TYPES'][$name] = 'int';
			}
			elseif(in_array($base, self::$stringTypes, true))
			{
				$defs['_FIELD_TYPES'][$name] = 'escape';
			}

			if(!$column->isNullable() && $column->getDefault() === null)
			{
				$defs['_NOTNULL'][$name] = '';
			}
		}

		return $defs;
	}

	/**
	 * @param string $columnType e.g. 'int(10) unsigned', "enum('a','b')", 'INTEGER'
	 * @return string lowercased type name without length or attributes, e.g. 'int'
	 */
	private static function baseType($columnType)
	{
		return preg_match('/^\s*([A-Za-z]+)/', (string) $columnType, $match) ? strtolower($match[1]) : '';
	}
}
