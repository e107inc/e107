<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Declared;

use e107\Database\ConnectionInterface;
use e107\Database\Exception\QueryException;
use e107\Database\Schema\Introspect\SchemaReaderInterface;
use e107\Database\Schema\Introspect\TableSchema;
use e107\Database\Schema\SchemaBuilder;
use e107\Database\SqlFragment;
use InvalidArgumentException;

/**
 * Turns a declared `CREATE TABLE` body into a {@see TableSchema} by building it as a real, empty scratch table, reading that back through the connection's schema reader, and taking it away again: dropped on MySQL, rolled back on an engine whose DDL is transactional, so a verify run there leaves nothing to write to disk.
 *
 * Every column, index and table returned from here also carries its definition in the schema DSL ({@see \e107\Database\Schema\SchemaManagerInterface::describeDefinitions()}; on MySQL the server's own SHOW CREATE TABLE text), which a live table read through the reader does not; that text takes no part in any `equals()`.
 *
 * <code>
 * $materialiser = new Materialiser(e107::getDb(), e107::getDb()->getSchemaManager()->getReader(), MPREFIX);
 * $materialiser->sweep();
 * $expected = $materialiser->materialise($declaredTable, 'InnoDB', 'utf8mb4');
 * </code>
 */
final class Materialiser
{
	/** @var string the name part every scratch table shares, between the prefix and the random suffix */
	const SCRATCH_INFIX = 'dbvscratch_';

	/** @var ConnectionInterface connection the scratch table is built on */
	private $db;

	/** @var SchemaReaderInterface reader the scratch table is read back through */
	private $reader;

	/** @var string database table prefix */
	private $prefix;

	/** @var SchemaBuilder builder that owns the CREATE and the DROP */
	private $schema;

	/** @var string unprefixed scratch table name, fixed for this instance */
	private $scratchTable;

	/** @var string prefixed scratch table name, as the server sees it */
	private $scratchPhysical;

	/**
	 * @param ConnectionInterface $db Connection to build the scratch table on. Needs CREATE and DROP.
	 * @param SchemaReaderInterface $reader Reader the scratch table is read back through, being the same kind that reads the live table.
	 * @param string $prefix Database table prefix, e.g. 'e107_'.
	 * @throws InvalidArgumentException when $prefix disagrees with the prefix the connection itself resolves tables to.
	 */
	public function __construct($db, SchemaReaderInterface $reader, $prefix)
	{
		$this->db = $db;
		$this->reader = $reader;
		$this->prefix = (string) $prefix;
		$this->schema = $db->schema();
		$this->scratchTable = self::SCRATCH_INFIX.substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
		$this->scratchPhysical = $this->prefix.$this->scratchTable;

		$resolved = $db->resolvePhysicalTableName($this->scratchTable);

		if($resolved !== $this->scratchPhysical)
		{
			throw new InvalidArgumentException('Materialiser was given the prefix "'.$this->prefix.'", but this connection resolves "'.$this->scratchTable.'" to "'.(string) $resolved.'".');
		}
	}

	/**
	 * @return string Prefixed name of this instance's scratch table, which exists only for the duration of a {@see Materialiser::materialise()} call.
	 */
	public function getScratchTableName()
	{
		return $this->scratchPhysical;
	}

	/**
	 * Build the declared body as a scratch table and return what the engine made of it.
	 *
	 * The returned schema carries the scratch table's name, not the declared one.
	 *
	 * @param DeclaredTable $table The declaration to build.
	 * @param string|null $engine Storage engine to build it with, as settled by {@see EngineCharsetResolverInterface::resolve()}. Required where the platform has storage engines, ignored where it has none.
	 * @param string|null $charset Character set to build it with. Required where the platform has character sets, ignored where it has none.
	 * @return TableSchema
	 * @throws InvalidArgumentException when the declared body is empty, when a required engine or character set is absent, or when one falls outside the identifier grammar {@see SchemaBuilder} enforces.
	 * @throws QueryException when the engine refuses the CREATE, when the scratch table cannot be read back, or when its definitions do not account for every column and index the reader reported. Never a partial schema.
	 */
	public function materialise(DeclaredTable $table, $engine, $charset)
	{
		if(trim($table->getBody()) === '')
		{
			throw new InvalidArgumentException('Table "'.$table->getName().'" declared in '.$table->getSqlFile().' has an empty body; there is nothing to materialise.');
		}

		$platform = $this->db->getPlatform();
		$options = array();

		if($platform->supportsStorageEngines())
		{
			$options['engine'] = $this->_require($engine, 'storage engine', $table);
		}

		if($platform->supportsCharsets())
		{
			$options['charset'] = $this->_require($charset, 'character set', $table);
		}

		$statements = $this->schema->buildCreateTablePhysicalStatements(
			$this->scratchTable,
			SqlFragment::raw($table->getBody()),
			$options
		);
		$ddl = implode(";\n", $statements);
		$rollBack = $platform->supportsTransactionalDdl();

		if($rollBack && !$this->db->beginTransaction())
		{
			throw new QueryException($this->_failure($table, 'no transaction could be opened to build it in ('.$this->db->getLastErrorText().')', $ddl));
		}

		try
		{
			foreach($statements as $statement)
			{
				if($this->db->execute($statement) === false)
				{
					throw new QueryException($this->_failure($table, 'the server refused the CREATE ('.$this->db->getLastErrorText().')', $ddl));
				}
			}

			$schema = $this->reader->read($this->scratchPhysical);

			if($schema === null)
			{
				throw new QueryException($this->_failure($table, 'the scratch table '.$this->scratchPhysical.' could not be read back', $ddl));
			}

			return $this->_withDefinitions($schema, $table, $ddl);
		}
		finally
		{
			if($rollBack)
			{
				$this->db->rollBack();
			}
			else
			{
				$this->_dropScratch();
			}
		}
	}

	/**
	 * Drop every table named `{prefix}dbvscratch_%`, whoever left it there, and report how many went.
	 *
	 * Call it once at the start of a verify run. It does not distinguish this instance's scratch table from another's, so two verify runs must not overlap.
	 *
	 * @return int tables dropped.
	 * @throws QueryException when the scratch tables cannot be listed.
	 */
	public function sweep()
	{
		$leaked = $this->db->getSchemaManager()->listTableNames($this->prefix.self::SCRATCH_INFIX);

		if($leaked === false)
		{
			throw new QueryException('Could not list the scratch tables to sweep: '.$this->db->getLastErrorText());
		}

		$dropped = 0;

		foreach($leaked as $name)
		{
			if($this->schema->dropTable((string) substr($name, strlen($this->prefix))) !== false)
			{
				$dropped++;
			}
		}

		return $dropped;
	}

	/**
	 * The same schema with its definitions in the schema DSL spread over it: one per column and per index, and the whole create body and table options on the table itself.
	 *
	 * @param TableSchema $schema as the reader returned it.
	 * @param DeclaredTable $table for the error message.
	 * @param string $ddl the statements that built the scratch table, for the error message.
	 * @return TableSchema
	 * @throws QueryException when the engine cannot describe the scratch table, or the description does not account for some column or index the reader just reported.
	 */
	private function _withDefinitions(TableSchema $schema, DeclaredTable $table, $ddl)
	{
		$definitions = $this->db->getSchemaManager()->describeDefinitions($this->scratchPhysical);

		if($definitions === null)
		{
			throw new QueryException($this->_failure($table, 'the engine could not describe the scratch table '.$this->scratchPhysical, $ddl));
		}

		$columns = array();

		foreach($schema->getColumns() as $name => $column)
		{
			if(!isset($definitions['columns'][$name]))
			{
				throw new QueryException($this->_failure($table, 'the description of the scratch table has no definition for column "'.$name.'": '.$definitions['body'], $ddl));
			}

			$columns[] = $column->withDdl($definitions['columns'][$name]);
		}

		$indexes = array();

		foreach($schema->getIndexes() as $name => $index)
		{
			if(!isset($definitions['indexes'][$name]))
			{
				throw new QueryException($this->_failure($table, 'the description of the scratch table has no definition for index "'.$name.'": '.$definitions['body'], $ddl));
			}

			$indexes[] = $index->withDdl($definitions['indexes'][$name]);
		}

		return new TableSchema(
			$schema->getName(),
			$schema->getEngine(),
			$schema->getCharset(),
			$schema->getCollation(),
			$columns,
			$indexes,
			$definitions['body'],
			$definitions['options']
		);
	}

	/**
	 * Drop this instance's scratch table, best effort: a drop that fails leaves a table {@see Materialiser::sweep()} collects on the next run.
	 *
	 * @return void
	 */
	private function _dropScratch()
	{
		$this->schema->dropTable($this->scratchTable);
	}

	/**
	 * @param mixed $value
	 * @param string $what label for the error message
	 * @param DeclaredTable $table
	 * @return string
	 * @throws InvalidArgumentException when the option is absent or blank.
	 */
	private function _require($value, $what, DeclaredTable $table)
	{
		if(!is_string($value) || trim($value) === '')
		{
			throw new InvalidArgumentException('Table "'.$table->getName().'" declared in '.$table->getSqlFile().' was given no '.$what.' to materialise with; it is settled by EngineCharsetResolverInterface::resolve() before this call, and falling back to the server default here would report drift the schema file never asked for.');
		}

		return $value;
	}

	/**
	 * @param DeclaredTable $table
	 * @param string $reason
	 * @param string $ddl attached to the message verbatim
	 * @return string
	 */
	private function _failure(DeclaredTable $table, $reason, $ddl)
	{
		return 'Could not materialise table "'.$table->getName().'" declared in '.$table->getSqlFile().': '.$reason.'. DDL: '.$ddl;
	}
}
