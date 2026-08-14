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

/**
 * Drop a live index whose definition has drifted, so the declared one can be
 * added back in its place.
 *
 * Only ever queued for an index that exists on both sides and differs. An index
 * nothing declares is not drift and is never dropped: on a live site it is
 * usually a plugin's or an administrator's, and no amount of verifying makes it
 * ours to remove.
 *
 * It is built from the live {@see IndexSchema}, because that is the thing being
 * dropped, and the kind decides the spelling: the primary key has no name to
 * drop by.
 */
final class DropIndex extends AbstractChange
{
	/** @var IndexSchema the live index, whole */
	private $index;

	/**
	 * @param string $sqlFile
	 * @param string $table Unprefixed logical table name.
	 * @param IndexSchema $index The live index being dropped.
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
			return 'Drop primary key';
		}

		return 'Drop index `'.$this->index->getName().'`';
	}

	/**
	 * @param SchemaBuilder $schema
	 * @return string
	 */
	public function toSql(SchemaBuilder $schema)
	{
		$table = $schema->tablePhysical($this->getTable());

		if($this->index->getKind() === IndexSchema::KIND_PRIMARY)
		{
			$table->dropPrimaryKey();
		}
		else
		{
			$table->dropIndex($this->index->getName());
		}

		return $table->getSQL();
	}
}
