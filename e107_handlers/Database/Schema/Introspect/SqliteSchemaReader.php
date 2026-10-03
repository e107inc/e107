<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Introspect;

use e107\Database\ConnectionInterface;
use e107\Database\Exception\QueryException;
use e107\Database\IdentifierFilter;
use e107\Database\Platform\SqlitePlatform;
use e107\Database\SqlLexer;

require_once(__DIR__.'/SchemaReaderInterface.php');
require_once(__DIR__.'/IndexPart.php');
require_once(__DIR__.'/ColumnSchema.php');
require_once(__DIR__.'/IndexSchema.php');
require_once(__DIR__.'/TableSchema.php');

/**
 * Reads live SQLite tables into {@see TableSchema} objects in SQLite's own types, with no engine, charset or comment,
 * and with each index under its declared name ({@see SqlitePlatform::physicalIndexName()} reversed).
 */
final class SqliteSchemaReader implements SchemaReaderInterface
{
	/** @var ConnectionInterface */
	private $db;

	/**
	 * @param ConnectionInterface $db
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * @inheritDoc
	 */
	public function read($physicalTableName)
	{
		$physicalTableName = (string) $physicalTableName;

		if($physicalTableName === '')
		{
			return null;
		}

		list($schema, $table) = self::split($physicalTableName);

		$sql = $this->fetchOne('SELECT sql FROM '.self::master($schema).' WHERE type = \'table\' AND name = :name', array('name' => $table));

		if($sql === null)
		{
			return null;
		}

		$declared = $this->declaredColumns((string) $sql);
		$columns = array();
		$primary = array();

		foreach($this->rows('SELECT * FROM pragma_table_xinfo(:name'.($schema === null ? '' : ', :schema').')', $this->params($table, $schema)) as $row)
		{
			if((int) $row['hidden'] !== 0)
			{
				continue;
			}

			$name = $row['name'];
			$info = isset($declared[strtolower($name)]) ? $declared[strtolower($name)] : array('collation' => null, 'autoincrement' => false);
			$rowid = ((int) $row['pk'] === 1 && strtolower($row['type']) === 'integer' && $info['autoincrement']);

			$columns[] = new ColumnSchema(
				$name,
				strtolower($row['type']),
				!((int) $row['notnull'] || $rowid),
				$row['dflt_value'],
				$rowid ? 'auto_increment' : '',
				null,
				$info['collation'],
				'',
				(int) $row['cid'] + 1
			);

			if((int) $row['pk'] > 0)
			{
				$primary[(int) $row['pk']] = $name;
			}
		}

		$indexes = array();

		if(!empty($primary))
		{
			ksort($primary);
			$parts = array();
			foreach($primary as $name)
			{
				$parts[] = new IndexPart($name, null, IndexPart::ASC);
			}
			$indexes[] = new IndexSchema('PRIMARY', IndexSchema::KIND_PRIMARY, $parts);
		}

		foreach($this->rows('SELECT * FROM pragma_index_list(:name'.($schema === null ? '' : ', :schema').')', $this->params($table, $schema)) as $index)
		{
			if($index['origin'] === 'pk')
			{
				continue;
			}

			$parts = array();

			foreach($this->rows('SELECT * FROM pragma_index_xinfo(:name'.($schema === null ? '' : ', :schema').')', $this->params($index['name'], $schema)) as $part)
			{
				if((int) $part['key'] === 1)
				{
					$parts[] = new IndexPart($part['name'], null, ((int) $part['desc'] === 1) ? IndexPart::DESC : IndexPart::ASC, (int) $part['cid'] === -2);
				}
			}

			$indexes[] = new IndexSchema($this->declaredIndexName($table, $index['name']), ((int) $index['unique'] === 1) ? IndexSchema::KIND_UNIQUE : IndexSchema::KIND_INDEX, $parts);
		}

		return new TableSchema($table, '', null, null, $columns, $indexes);
	}

	/**
	 * @inheritDoc
	 */
	public function readMany(array $physicalTableNames)
	{
		$tables = array();

		foreach($physicalTableNames as $name)
		{
			$name = (string) $name;

			if($name === '' || isset($tables[$name]))
			{
				continue;
			}

			$schema = $this->read($name);

			if($schema !== null)
			{
				$tables[$name] = $schema;
			}
		}

		return $tables;
	}

	/**
	 * Separate an attached database's alias from a table name.
	 *
	 * @param string $name 'table' or 'alias.table'
	 * @return array array(alias or null, table)
	 */
	public static function split($name)
	{
		$dot = strpos((string) $name, '.');

		return ($dot === false) ? array(null, (string) $name) : array((string) substr($name, 0, $dot), (string) substr($name, $dot + 1));
	}

	/**
	 * @param string|null $schema
	 * @return string the sqlite_master table of the main or an attached database
	 * @throws QueryException on an invalid database name
	 */
	public static function master($schema)
	{
		if($schema === null)
		{
			return 'sqlite_master';
		}

		if(!class_exists(IdentifierFilter::class, false))
		{
			require_once(dirname(dirname(__DIR__)).'/IdentifierFilter.php');
		}

		$master = IdentifierFilter::identifier($schema.'.sqlite_master');

		if($master === false)
		{
			throw new QueryException('Invalid attached database name "'.$schema.'".');
		}

		return $master;
	}

	/**
	 * @param string $table
	 * @param string $physicalIndex
	 * @return string the name the index was declared with
	 */
	private function declaredIndexName($table, $physicalIndex)
	{
		$qualifier = $table.SqlitePlatform::INDEX_NAME_SEPARATOR;

		return (strpos($physicalIndex, $qualifier) === 0) ? (string) substr($physicalIndex, strlen($qualifier)) : $physicalIndex;
	}

	/**
	 * What the table's CREATE statement says of each column that the pragmas do not: its COLLATE clause and whether
	 * it is an AUTOINCREMENT key.
	 *
	 * @param string $createSql
	 * @return array lowercased column name => array('collation' => string|null, 'autoincrement' => bool)
	 */
	private function declaredColumns($createSql)
	{
		if(!class_exists(SqlLexer::class, false))
		{
			require_once(dirname(dirname(__DIR__)).'/SqlLexer.php');
		}

		$tokens = SqlLexer::sqlite()->significantTokens($createSql);
		$columns = array();
		$depth = 0;
		$definition = array();

		foreach($tokens as $token)
		{
			if($token['text'] === '(')
			{
				$depth++;
				if($depth === 1)
				{
					continue;
				}
			}
			elseif($token['text'] === ')')
			{
				$depth--;
				if($depth === 0)
				{
					$this->addDeclaredColumn($definition, $columns);
					break;
				}
			}
			elseif($token['text'] === ',' && $depth === 1)
			{
				$this->addDeclaredColumn($definition, $columns);
				$definition = array();
				continue;
			}

			if($depth >= 1)
			{
				$definition[] = $token;
			}
		}

		return $columns;
	}

	/**
	 * @param array[] $definition tokens of one column or constraint definition
	 * @param array $columns updated
	 * @return void
	 */
	private function addDeclaredColumn(array $definition, array &$columns)
	{
		if(empty($definition))
		{
			return;
		}

		$first = strtoupper($definition[0]['text']);

		if($definition[0]['type'] === SqlLexer::T_WORD && in_array($first, array('PRIMARY', 'UNIQUE', 'CHECK', 'FOREIGN', 'CONSTRAINT'), true))
		{
			return;
		}

		$info = array('collation' => null, 'autoincrement' => false);

		foreach($definition as $i => $token)
		{
			$word = strtoupper($token['text']);

			if($word === 'COLLATE' && isset($definition[$i + 1]))
			{
				$info['collation'] = strtoupper($definition[$i + 1]['value']);
			}
			elseif($word === 'AUTOINCREMENT')
			{
				$info['autoincrement'] = true;
			}
		}

		$columns[strtolower($definition[0]['value'])] = $info;
	}

	/**
	 * @param string $name
	 * @param string|null $schema
	 * @return array
	 */
	private function params($name, $schema)
	{
		return ($schema === null) ? array('name' => $name) : array('name' => $name, 'schema' => $schema);
	}

	/**
	 * @param string $sql
	 * @param array $params
	 * @return array[]
	 * @throws QueryException
	 */
	private function rows($sql, array $params)
	{
		if($this->db->execute($sql, $params) === false)
		{
			throw new QueryException('Could not read the SQLite schema: '.$this->db->getLastErrorText());
		}

		$rows = array();

		while($row = $this->db->fetch())
		{
			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * @param string $sql
	 * @param array $params
	 * @return string|null the first column of the first row
	 */
	private function fetchOne($sql, array $params)
	{
		$rows = $this->rows($sql, $params);

		return empty($rows) ? null : reset($rows[0]);
	}
}
