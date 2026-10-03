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
use e107\Database\Schema\Introspect\MysqlCreateStatement;
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

	/** @var bool whether this session has been told to quote identifiers in SHOW CREATE TABLE */
	private $quotesShowCreate = false;

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
			.' LIKE '.$this->db->quoteStringLiteral($this->db->getPlatform()->quoteLikeLiteral($bare).'%');

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
	 * Written with every identifier quoted (SQL_QUOTE_SHOW_CREATE), which reading the statement back relies on.
	 *
	 * @inheritDoc
	 */
	public function getCreateStatement($table)
	{
		if(!$this->quotesShowCreate)
		{
			$this->db->execute('SET SQL_QUOTE_SHOW_CREATE = 1');
			$this->quotesShowCreate = true;
		}

		if($this->db->execute('SHOW CREATE TABLE '.$this->quote($table)) === false)
		{
			return null;
		}

		$row = $this->db->fetch();

		return (is_array($row) && isset($row['Create Table'])) ? $row['Create Table'] : null;
	}

	/**
	 * SHOW TABLE STATUS. Its row count is the engine's estimate for InnoDB.
	 *
	 * @inheritDoc
	 */
	public function getTableStatus($table)
	{
		list($database, $bare) = $this->split($table);

		if($this->db->execute('SHOW TABLE STATUS'.(($database === null) ? '' : ' FROM '.$database).' WHERE Name = :name', array('name' => $bare)) === false)
		{
			return null;
		}

		$row = $this->db->fetch();

		if(!is_array($row))
		{
			return null;
		}

		$figure = function($key) use ($row)
		{
			return (isset($row[$key]) && $row[$key] !== null) ? (int) $row[$key] : null;
		};

		return array(
			'rows'           => $figure('Rows'),
			'data_length'    => $figure('Data_length'),
			'index_length'   => $figure('Index_length'),
			'avg_row_length' => $figure('Avg_row_length'),
		);
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
	 * The schema DSL is MySQL's own dialect, so the definitions go into the statement as written, on lines of their
	 * own: a body may end in a line comment.
	 *
	 * @inheritDoc
	 */
	public function compileCreateTable($table, array $definitions, $options = '')
	{
		return array($this->db->getPlatform()->compileCreateTable($this->quoteForDdl($table), array("\n".implode(",\n", $definitions)."\n"), $options));
	}

	/**
	 * @inheritDoc
	 */
	public function compileRenameTable($from, $to)
	{
		return array($this->db->getPlatform()->compileRenameTable($this->quoteForDdl($from), $this->quoteForDdl($to)));
	}

	/**
	 * @inheritDoc
	 */
	public function compileOptimizeTable(array $tables)
	{
		return array($this->db->getPlatform()->compileOptimizeTable(array_map(array($this, 'quoteForDdl'), $tables)));
	}

	/**
	 * One ALTER TABLE of every clause, as written.
	 *
	 * @inheritDoc
	 */
	public function compileAlterTable($table, array $operations)
	{
		$clauses = array();

		foreach($operations as $operation)
		{
			$clauses[] = $operation->getClause();
		}

		return array($this->db->getPlatform()->compileAlterTable($this->quoteForDdl($table), $clauses));
	}

	/**
	 * Cut from the server's own SHOW CREATE TABLE, so a definition put back changes nothing the server would
	 * otherwise spell differently. The AUTO_INCREMENT counter is left out of the options.
	 *
	 * @inheritDoc
	 */
	public function describeDefinitions($table)
	{
		if(!class_exists(MysqlCreateStatement::class, false))
		{
			require_once(__DIR__.'/Introspect/MysqlCreateStatement.php');
		}

		$create = $this->getCreateStatement($table);
		$statement = is_string($create) ? MysqlCreateStatement::split($create) : null;

		if($statement === null)
		{
			return null;
		}

		$definitions = MysqlCreateStatement::definitionsByName($statement['body']);

		return array(
			'body'    => $statement['body'],
			'options' => $statement['options'],
			'columns' => $definitions['columns'],
			'indexes' => $definitions['indexes'],
		);
	}

	/**
	 * A table name quoted for DDL, inside the identifier grammar or not.
	 *
	 * @param string $table
	 * @return string
	 */
	private function quoteForDdl($table)
	{
		$quoted = $this->db->getPlatform()->quoteIdentifier($table);

		return ($quoted === false) ? $this->quote($table) : $quoted;
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
