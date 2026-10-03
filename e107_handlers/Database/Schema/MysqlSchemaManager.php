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
use e107\Database\Schema\Introspect\SchemaReader;

require_once(__DIR__.'/SchemaManagerInterface.php');

/**
 * Schema work on MySQL and MariaDB, through the SHOW statements and information_schema.
 */
final class MysqlSchemaManager implements SchemaManagerInterface
{
	/** @var ConnectionInterface */
	private $db;

	/** @var SchemaReader|null */
	private $reader = null;

	/**
	 * @param ConnectionInterface $db the connection every statement runs on
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * @return SchemaReader
	 */
	public function getReader()
	{
		if($this->reader === null)
		{
			if(!class_exists(SchemaReader::class, false))
			{
				require_once(__DIR__.'/Introspect/SchemaReader.php');
			}

			$this->reader = new SchemaReader($this->db);
		}

		return $this->reader;
	}

	/**
	 * @inheritDoc
	 */
	public function listTableNames($prefix)
	{
		list($database, $bare) = $this->split($prefix);

		$sql = 'SHOW TABLES'.($database === null ? '' : ' FROM '.$database)
			.' LIKE '.$this->db->quoteStringLiteral(addcslashes($bare, '%_\\').'%');

		if($this->db->execute($sql) === false)
		{
			return false;
		}

		$names = array();

		while($row = $this->db->fetch('num'))
		{
			$names[] = (string) $row[0];
		}

		return $names;
	}

	/**
	 * @inheritDoc
	 */
	public function getColumnRows($table)
	{
		return $this->rows('SHOW COLUMNS FROM '.$this->quote($table));
	}

	/**
	 * @inheritDoc
	 */
	public function getIndexRows($table)
	{
		return $this->rows('SHOW INDEX FROM '.$this->quote($table));
	}

	/**
	 * @inheritDoc
	 */
	public function getCreateStatement($table)
	{
		if($this->db->execute('SHOW CREATE TABLE '.$this->quote($table)) === false)
		{
			return null;
		}

		$row = $this->db->fetch();

		return (is_array($row) && isset($row['Create Table'])) ? $row['Create Table'] : null;
	}

	/**
	 * Replays the source table's own SHOW CREATE TABLE under the new name, so the copy keeps every key, option and
	 * the auto-increment counter.
	 *
	 * @inheritDoc
	 */
	public function createTableLike($from, $to)
	{
		$create = $this->getCreateStatement($from);

		if($create === null)
		{
			return false;
		}

		list(, $bareFrom) = $this->split($from);
		$create = preg_replace('#CREATE\sTABLE\s`?'.preg_quote($bareFrom, '#').'`?\s#', 'CREATE TABLE '.$this->quote($to).' ', $create, 1);

		return $this->db->execute($create) !== false;
	}

	/**
	 * @inheritDoc
	 */
	public function truncateTable($table)
	{
		return $this->db->execute('TRUNCATE TABLE '.$this->quote($table));
	}

	/**
	 * Run a statement and collect every row.
	 *
	 * @param string $sql
	 * @return array[]|false
	 */
	private function rows($sql)
	{
		if($this->db->execute($sql) === false)
		{
			return false;
		}

		$rows = array();

		while($row = $this->db->fetch())
		{
			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * Quote a table argument: a bare name, 'db.name', or a name behind a connection prefix that is already
	 * qualified and quoted, such as '`db`.e107_news'.
	 *
	 * @param string $table
	 * @return string
	 */
	private function quote($table)
	{
		list($database, $bare) = $this->split($table);

		return ($database === null ? '' : $database.'.').'`'.str_replace('`', '``', $bare).'`';
	}

	/**
	 * Separate a database qualifier from a table name or prefix.
	 *
	 * @param string $name 'name', 'db.name' or '`db`.name'
	 * @return array array(quoted database or null, bare name)
	 */
	private function split($name)
	{
		$name = (string) $name;

		if(preg_match('/^`((?:[^`]|``)+)`\.(.*)$/sD', $name, $match))
		{
			return array('`'.$match[1].'`', $match[2]);
		}

		if(($dot = strrpos($name, '.')) !== false)
		{
			return array('`'.str_replace('`', '``', (string) substr($name, 0, $dot)).'`', (string) substr($name, $dot + 1));
		}

		return array(null, $name);
	}
}
