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

use e107\Database\Schema\Introspect\TableSchema;
use e107\Database\Schema\SchemaBuilder;
use e107\Database\SqlFragment;
use RuntimeException;

/**
 * Create a declared table the database does not have.
 *
 * Renders the create body ({@see TableSchema::getCreateBody()}) and the table
 * options ({@see TableSchema::getCreateOptions()}) the server itself wrote,
 * with only the table identifier substituted. No engine or character set is
 * carried; those options already state both.
 */
final class CreateTable extends AbstractChange
{
	/** @var TableSchema the declared shape, whole */
	private $expected;

	/**
	 * @param string $sqlFile
	 * @param string $table Unprefixed logical table name.
	 * @param TableSchema $expected The declared shape, carrying the server's own CREATE TABLE text.
	 */
	public function __construct($sqlFile, $table, TableSchema $expected)
	{
		parent::__construct($sqlFile, $table);

		$this->expected = $expected;
	}

	/**
	 * @return TableSchema
	 */
	public function getExpectedTable()
	{
		return $this->expected;
	}

	/**
	 * @return string
	 */
	public function describe()
	{
		return 'Create table `'.$this->getTable().'`';
	}

	/**
	 * @param SchemaBuilder $schema
	 * @return string
	 * @throws RuntimeException when the declared shape carries no captured create body or options.
	 */
	public function toSql(SchemaBuilder $schema)
	{
		$body = $this->captured($this->expected->getCreateBody(), 'the table body');
		$options = $this->captured($this->expected->getCreateOptions(), 'the table options');

		return $schema->buildCreateTablePhysicalRaw(
			$this->getTable(),
			SqlFragment::raw("\n".$body->getSql()."\n"),
			SqlFragment::raw(' '.trim($options->getSql()))
		);
	}
}
