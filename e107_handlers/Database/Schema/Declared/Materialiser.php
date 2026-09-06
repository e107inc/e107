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
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Introspect\SchemaReader;
use e107\Database\Schema\Introspect\TableSchema;
use e107\Database\Schema\SchemaBuilder;
use e107\Database\SqlFragment;
use InvalidArgumentException;

/**
 * Turns a declared `CREATE TABLE` body into a {@see TableSchema} by building it as a real, empty scratch table, reading that back through {@see SchemaReader}, and dropping it again.
 *
 * Every column, index and table returned from here also carries the server's own SHOW CREATE TABLE text, which a live table read through {@see SchemaReader} does not; that text takes no part in any `equals()`.
 *
 * <code>
 * $materialiser = new Materialiser(e107::getDb(), new SchemaReader(e107::getDb()), MPREFIX);
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

	/** @var SchemaReader reader the scratch table is read back through */
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
	 * @param SchemaReader $reader Reader the scratch table is read back through, being the same kind that reads the live table.
	 * @param string $prefix Database table prefix, e.g. 'e107_'.
	 * @throws InvalidArgumentException when $prefix disagrees with the prefix the connection itself resolves tables to.
	 */
	public function __construct($db, SchemaReader $reader, $prefix)
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
	 * Build the declared body as a scratch table and return what the server made of it.
	 *
	 * The returned schema carries the scratch table's name, not the declared one.
	 *
	 * @param DeclaredTable $table The declaration to build.
	 * @param string $engine Storage engine to build it with, as settled by {@see EngineCharsetResolverInterface::resolve()}. Required.
	 * @param string $charset Character set to build it with. Required.
	 * @return TableSchema
	 * @throws InvalidArgumentException when the declared body is empty, when either the engine or the character set is absent, or when one falls outside the identifier grammar {@see SchemaBuilder} enforces.
	 * @throws QueryException when the server refuses the CREATE, when the scratch table cannot be read back, or when the server states a CREATE TABLE that does not account for every column and index it just reported. Never a partial schema.
	 */
	public function materialise(DeclaredTable $table, $engine, $charset)
	{
		if(trim($table->getBody()) === '')
		{
			throw new InvalidArgumentException('Table "'.$table->getName().'" declared in '.$table->getSqlFile().' has an empty body; there is nothing to materialise.');
		}

		$options = array(
			'engine'  => $this->_require($engine, 'storage engine', $table),
			'charset' => $this->_require($charset, 'character set', $table),
		);

		$ddl = $this->schema->buildCreateTablePhysicalRaw(
			$this->scratchTable,
			SqlFragment::raw($table->getBody()),
			$options
		);

		try
		{
			if($this->db->execute($ddl) === false)
			{
				throw new QueryException($this->_failure($table, 'the server refused the CREATE ('.$this->db->getLastErrorText().')', $ddl));
			}

			$schema = $this->reader->read($this->scratchPhysical);

			if($schema === null)
			{
				throw new QueryException($this->_failure($table, 'the scratch table '.$this->scratchPhysical.' could not be read back', $ddl));
			}

			return $this->_withServerDdl($schema, $table, $ddl);
		}
		finally
		{
			$this->_dropScratch();
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
		$sql = 'SELECT TABLE_NAME AS scratch_name FROM information_schema.TABLES'
			.' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = \'BASE TABLE\' AND TABLE_NAME LIKE :pattern';

		$params = array('pattern' => $this->_escapeLike($this->prefix.self::SCRATCH_INFIX).'%');

		if($this->db->execute($sql, $params) === false)
		{
			throw new QueryException('Could not list the scratch tables to sweep: '.$this->db->getLastErrorText());
		}

		$leaked = array();

		while($row = $this->db->fetch())
		{
			if(isset($row['scratch_name']))
			{
				$leaked[] = $row['scratch_name'];
			}
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
	 * The same schema with the server's own SHOW CREATE TABLE spread over it: one definition line per column and per index, and the whole create body and table options on the table itself.
	 *
	 * @param TableSchema $schema as the reader returned it.
	 * @param DeclaredTable $table for the error message.
	 * @param string $ddl the CREATE that built the scratch table, for the error message.
	 * @return TableSchema
	 * @throws QueryException when the server will not state its own CREATE TABLE, when that statement cannot be split at the outer parentheses, or when it does not account for some column or index information_schema just reported.
	 */
	private function _withServerDdl(TableSchema $schema, DeclaredTable $table, $ddl)
	{
		$create = $this->schema->getCreateTablePhysical($this->scratchTable);

		if(!is_string($create) || trim($create) === '')
		{
			throw new QueryException($this->_failure($table, 'the server would not state its own CREATE TABLE for the scratch table '.$this->scratchPhysical, $ddl));
		}

		$statement = self::splitCreateStatement($create);

		if($statement === null)
		{
			throw new QueryException($this->_failure($table, 'the server\'s own CREATE TABLE could not be split at its outer parentheses: '.$create, $ddl));
		}

		$definitions = self::definitionsByName($statement['body']);

		$columns = array();

		foreach($schema->getColumns() as $name => $column)
		{
			if(!isset($definitions['columns'][$name]))
			{
				throw new QueryException($this->_failure($table, 'the server\'s own CREATE TABLE has no definition line for column "'.$name.'": '.$create, $ddl));
			}

			$columns[] = $column->withDdl($definitions['columns'][$name]);
		}

		$indexes = array();

		foreach($schema->getIndexes() as $name => $index)
		{
			if(!isset($definitions['indexes'][$name]))
			{
				throw new QueryException($this->_failure($table, 'the server\'s own CREATE TABLE has no definition line for index "'.$name.'": '.$create, $ddl));
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
			$statement['body'],
			$statement['options']
		);
	}

	/**
	 * A SHOW CREATE TABLE statement cut into the block between its outer parentheses and the options that follow them.
	 *
	 * @param string $create
	 * @return array|null ['body' => string, 'options' => string], everything after the closing parenthesis counting as options; null when the statement has no recognisable outer parentheses.
	 */
	public static function splitCreateStatement($create)
	{
		$lines = preg_split('/\r\n|\n|\r/', $create);
		$body = array();
		$options = null;
		$opened = false;

		foreach($lines as $at => $line)
		{
			if(!$opened)
			{
				$opened = (substr(rtrim($line), -1) === '(');
				continue;
			}

			if(substr($line, 0, 1) === ')')
			{
				$trailing = array_merge(array((string) substr($line, 1)), array_slice($lines, $at + 1));
				$options = trim(implode("\n", $trailing));

				break;
			}

			$body[] = $line;
		}

		if(!$opened || $options === null || count($body) === 0)
		{
			return null;
		}

		return array(
			'body'    => implode("\n", $body),
			'options' => self::_withoutAutoIncrement($options),
		);
	}

	/**
	 * The create body's lines, without their leading indentation or trailing comma, keyed by the column or index each defines.
	 *
	 * @param string $body
	 * @return array ['columns' => name => string, 'indexes' => name => string]; a line defining neither, such as a CHECK constraint or a foreign key, is left out.
	 */
	public static function definitionsByName($body)
	{
		$columns = array();
		$indexes = array();

		foreach(explode("\n", $body) as $line)
		{
			$fragment = preg_replace('/,$/', '', trim($line));

			if($fragment === '')
			{
				continue;
			}

			if(substr($fragment, 0, 1) === '`')
			{
				$name = self::_leadingIdentifier($fragment);

				if($name !== null)
				{
					$columns[$name] = $fragment;
				}

				continue;
			}

			$name = self::_indexNameOf($fragment);

			if($name !== null)
			{
				$indexes[$name] = $fragment;
			}
		}

		return array('columns' => $columns, 'indexes' => $indexes);
	}

	/**
	 * The index an index definition line names, as information_schema names it.
	 *
	 * @param string $fragment
	 * @return string|null null when the line does not define an index.
	 */
	private static function _indexNameOf($fragment)
	{
		if(!preg_match('/^(PRIMARY KEY|UNIQUE KEY|FULLTEXT KEY|SPATIAL KEY|KEY)\s/i', $fragment, $matches))
		{
			return null;
		}

		if(strcasecmp($matches[1], 'PRIMARY KEY') === 0)
		{
			return IndexSchema::KIND_PRIMARY;
		}

		return self::_leadingIdentifier(ltrim((string) substr($fragment, strlen($matches[1]))));
	}

	/**
	 * @param string $fragment
	 * @return string|null The identifier the fragment opens with, unquoted; null when it does not open with a backticked identifier.
	 */
	private static function _leadingIdentifier($fragment)
	{
		if(!preg_match('/^`((?:[^`]|``)*)`/', $fragment, $matches))
		{
			return null;
		}

		return str_replace('``', '`', $matches[1]);
	}

	/**
	 * The table options without any AUTO_INCREMENT counter.
	 *
	 * @param string $options
	 * @return string
	 */
	private static function _withoutAutoIncrement($options)
	{
		$quote = strpos($options, "'");
		$head = ($quote === false) ? $options : (string) substr($options, 0, $quote);
		$tail = ($quote === false) ? '' : (string) substr($options, $quote);

		return trim(preg_replace('/\bAUTO_INCREMENT\s*=\s*\d+\s*/i', '', $head).$tail);
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

	/**
	 * Escape a literal string for use as the fixed part of a LIKE pattern.
	 *
	 * @param string $literal
	 * @return string
	 */
	private function _escapeLike($literal)
	{
		return str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $literal);
	}
}
