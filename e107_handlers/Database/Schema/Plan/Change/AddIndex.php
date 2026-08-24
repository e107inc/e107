<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Plan\Change;

use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\SchemaBuilder;
use RuntimeException;

/**
 * Add a declared index the live table does not have, or re-add one a
 * {@see DropIndex} has just removed because its definition had drifted.
 *
 * Splices the key clause the server wrote for it ({@see IndexSchema::getDdl()})
 * verbatim.
 */
final class AddIndex extends AbstractChange
{
	/** @var IndexSchema the declared index, whole */
	private $index;

	/**
	 * @param string $sqlFile
	 * @param string $table Unprefixed logical table name.
	 * @param IndexSchema $index The declared index, carrying the server's own key clause.
	 */
	public function __construct($sqlFile, $table, IndexSchema $index)
	{
		parent::__construct($sqlFile, $table);

		$this->index = $index;
	}

	/**
	 * @return IndexSchema
	 */
	public function getIndex()
	{
		return $this->index;
	}

	/**
	 * @return string
	 */
	public function describe()
	{
		if($this->index->getKind() === IndexSchema::KIND_PRIMARY)
		{
			return 'Add primary key';
		}

		return 'Add index `'.$this->index->getName().'`';
	}

	/**
	 * @param SchemaBuilder $schema
	 * @return string
	 * @throws RuntimeException when the index carries no captured key clause.
	 */
	public function toSql(SchemaBuilder $schema)
	{
		$definition = $this->captured($this->index->getDdl(), 'index `'.$this->index->getName().'`');

		return $schema->tablePhysical($this->getTable())
			->addIndex($definition)
			->getSQL();
	}
}
