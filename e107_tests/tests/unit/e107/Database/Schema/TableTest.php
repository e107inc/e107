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

use e107;
use e107\Database\Schema\Declared\SqlFileCatalogue;
use e107\Database\SqlFragment;

/**
 * Schema changes on the suite's own connection, so each gives the same answer on every engine.
 */
class TableTest extends \Test\Unit
{
	/** @var string[] tables a test creates */
	private static $tables = array('schema_table_test', 'schema_table_bin', 'schema_table_case', 'schema_table_hex', 'schema_table_serial', 'schema_table_comment', 'schema_table_dashes', 'schema_table_constraint', 'schema_table_decimal');

	/** @var \e_db */
	private $db;

	protected function _before()
	{
		$this->db = e107::getDb();
		$this->dropTables();

		$this->assertNotFalse($this->db->schema()->createTableRaw('schema_table_test', SqlFragment::raw(
			"t_id int(10) unsigned NOT NULL auto_increment, t_name varchar(20) NOT NULL default '', t_hits int(10) NOT NULL default '0', PRIMARY KEY (t_id), KEY t_hits (t_hits)"
		)), $this->db->getLastErrorText());
	}

	protected function _after()
	{
		$this->dropTables();
	}

	public function testAChangeTheTableCannotTakeIsFalse()
	{
		$schema = $this->db->schema();

		$this->assertFalse($schema->dropIndex('schema_table_test', 'no_such_index'));
		$this->assertFalse($schema->dropColumn('schema_table_test', 'no_such_column'));
		$this->assertFalse($schema->modifyColumn('schema_table_test', 'no_such_column', SqlFragment::raw('int NOT NULL')));
		$this->assertFalse($schema->changeColumn('schema_table_test', 'no_such_column', 'other', SqlFragment::raw('int NOT NULL')));
		$this->assertFalse($schema->addColumn('schema_table_test', 't_name', SqlFragment::raw("varchar(20) NOT NULL default ''")), 'a column added twice');
		$this->assertFalse($schema->addIndex('schema_table_test', Index::index('t_hits', 't_name')), 'an index added twice');
		$this->assertFalse($schema->modifyColumn('schema_table_test', 't_name', SqlFragment::raw('varchar(20) NOT NULL NONSENSE')), 'a definition no engine can read');
		$this->assertSame(array('t_id', 't_name', 't_hits'), $this->columnsOf('schema_table_test'));
	}

	public function testAColumnIsPlacedAfterAnotherNamedInAnyCase()
	{
		$this->assertNotFalse($this->db->schema()->addColumn('schema_table_test', 't_extra', SqlFragment::raw('int NOT NULL DEFAULT 0'), 'T_ID'), $this->db->getLastErrorText());
		$this->assertSame(array('t_id', 't_extra', 't_name', 't_hits'), $this->columnsOf('schema_table_test'));
	}

	public function testAColumnWithAnExpressionDefaultCanBeAdded()
	{
		$this->insert('schema_table_test', array('t_name' => 'before'));

		$this->assertNotFalse($this->db->schema()->addColumn('schema_table_test', 't_seen', SqlFragment::raw('timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP')), $this->db->getLastErrorText());

		$this->insert('schema_table_test', array('t_name' => 'after'));
		$this->assertNotEmpty($this->db->createQueryBuilder()->select('t_seen')->from('schema_table_test')->where('t_name', 'after')->fetchOne());
		$this->assertSame(array('before', 'after'), $this->db->createQueryBuilder()->select('t_name')->from('schema_table_test')->orderBy('t_id')->fetchColumn());
	}

	public function testATableCollationDecidesHowItsKeysCompare()
	{
		$this->assertNotFalse($this->db->schema()->createTable('schema_table_bin', array(
			'b_token' => Column::define('VARCHAR', 20)->notNull(),
		), array(Index::unique('b_token', 'b_token')), SqlFragment::raw(' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin')), $this->db->getLastErrorText());

		$this->insert('schema_table_bin', array('b_token' => 'abc'));
		$this->insert('schema_table_bin', array('b_token' => 'ABC'));

		$this->assertSame(array('abc'), $this->db->createQueryBuilder()->select('b_token')->from('schema_table_bin')->where('b_token', 'abc')->fetchColumn());
	}

	public function testAKeyMayNameItsColumnInAnotherCase()
	{
		$this->assertNotFalse($this->db->schema()->createTableRaw('schema_table_case', SqlFragment::raw('userId int NOT NULL, KEY user_key (userid)')), $this->db->getLastErrorText());
		$this->assertTrue($this->db->index('schema_table_case', 'user_key'));
	}

	public function testAKeyNamedByItsConstraintIsFoundAndDroppedByThatName()
	{
		$this->assertNotFalse($this->db->schema()->createTableRaw('schema_table_constraint', SqlFragment::raw('k_token varchar(10) NOT NULL, CONSTRAINT uq_token UNIQUE (k_token)')), $this->db->getLastErrorText());
		$this->assertTrue($this->db->index('schema_table_constraint', 'uq_token'));

		$this->assertNotFalse($this->db->schema()->dropIndex('schema_table_constraint', 'uq_token'), $this->db->getLastErrorText());
		$this->assertFalse($this->db->index('schema_table_constraint', 'uq_token'));
	}

	public function testAHexadecimalDefaultIsBytesInATextColumnAndANumberInANumericOne()
	{
		$this->assertNotFalse($this->db->schema()->createTableRaw('schema_table_hex', SqlFragment::raw(
			"h_id int NOT NULL, h_text varchar(5) NOT NULL DEFAULT 0x41, h_number int NOT NULL DEFAULT 0x41"
		)), $this->db->getLastErrorText());

		$this->insert('schema_table_hex', array('h_id' => 1));

		$this->assertSame(array('h_text' => 'A', 'h_number' => '65'), $this->db->createQueryBuilder()->select(array('h_text', 'h_number'))->from('schema_table_hex')->fetchRow());
	}

	public function testADeclaredTableMayEndInALineComment()
	{
		$catalogue = new SqlFileCatalogue();
		$declared = $catalogue->parse("CREATE TABLE schema_table_comment (\n  c_id int(10) NOT NULL,\n  c_text varchar(20) NOT NULL default '',\n  PRIMARY KEY (c_id)\n  # KEY c_text (c_text)\n) ENGINE=InnoDB;\n"
			."CREATE TABLE schema_table_dashes (\n  d_id int(10) NOT NULL,\n  PRIMARY KEY (d_id)\n  -- KEY d_old (d_id)\n) ENGINE=InnoDB;", 'core');

		$this->assertNotFalse($this->db->schema()->createDeclaredTable($declared['schema_table_comment'], 'InnoDB', 'utf8mb4'), $this->db->getLastErrorText());
		$this->assertNotFalse($this->db->schema()->createDeclaredTable($declared['schema_table_dashes']), $this->db->getLastErrorText());
		$this->assertSame(array('c_id', 'c_text'), $this->columnsOf('schema_table_comment'));
		$this->assertSame(array('d_id'), $this->columnsOf('schema_table_dashes'));
	}

	public function testATablePrefixOutsideTheIdentifierGrammarIsQuotedRatherThanRefused()
	{
		$this->requireDatabaseDriver('mysql', 'only a MySQL site can have been installed with such a prefix');

		$prefix = $this->db->mySQLPrefix;
		$this->assertNotFalse($this->db->execute('CREATE TABLE `e107$schema_table_odd` (o_id int NOT NULL, o_name varchar(10) NOT NULL)'), $this->db->getLastErrorText());

		try
		{
			$this->db->mySQLPrefix = 'e107$';
			$schema = $this->db->schema();
			$indexed = $schema->tablePhysical('schema_table_odd')->addIndex(Index::index('o_name', 'o_name'))->execute();
			$added = $schema->addColumn('schema_table_odd', 'o_more', SqlFragment::raw('int NOT NULL DEFAULT 0'));
			$optimised = $schema->optimizeTable('schema_table_odd');
			$columns = $this->columnsOf('schema_table_odd');
		}
		finally
		{
			$this->db->mySQLPrefix = $prefix;
			$this->db->resetTableList();
			$this->db->execute('DROP TABLE IF EXISTS `e107$schema_table_odd`');
		}

		$this->assertNotFalse($indexed);
		$this->assertNotFalse($added);
		$this->assertNotFalse($optimised);
		$this->assertSame(array('o_id', 'o_name', 'o_more'), $columns);
	}

	public function testADecimalDefaultInAStringColumnIsStoredAsMysqlStoresIt()
	{
		$this->assertNotFalse($this->db->schema()->createTableRaw('schema_table_decimal', SqlFragment::raw(
			"d_id int NOT NULL, d_zeros varchar(9) NOT NULL DEFAULT 007, d_point varchar(9) NOT NULL DEFAULT .5, d_kept varchar(9) NOT NULL DEFAULT 1.50"
		)), $this->db->getLastErrorText());

		$this->insert('schema_table_decimal', array('d_id' => 1));

		$this->assertSame(array('d_zeros' => '7', 'd_point' => '0.5', 'd_kept' => '1.50'), $this->db->createQueryBuilder()->select(array('d_zeros', 'd_point', 'd_kept'))->from('schema_table_decimal')->fetchRow());
	}

	public function testASerialColumnNumbersItsRows()
	{
		$this->assertNotFalse($this->db->schema()->createTableRaw('schema_table_serial', SqlFragment::raw('s_id SERIAL PRIMARY KEY, s_name varchar(5) NOT NULL')), $this->db->getLastErrorText());

		$this->insert('schema_table_serial', array('s_name' => 'a'));
		$this->insert('schema_table_serial', array('s_name' => 'b'));

		$this->assertSame(array('1', '2'), $this->db->createQueryBuilder()->select('s_id')->from('schema_table_serial')->orderBy('s_id')->fetchColumn());
	}

	/**
	 * @param string $table
	 * @param array $row
	 * @return void
	 */
	private function insert($table, array $row)
	{
		$this->assertNotFalse($this->db->createQueryBuilder()->insert($table)->values($row)->execute(), $this->db->getLastErrorText());
	}

	/**
	 * @param string $table
	 * @return string[]
	 */
	private function columnsOf($table)
	{
		$names = array();

		foreach($this->db->schema()->getColumns($table) as $column)
		{
			$names[] = $column['Field'];
		}

		return $names;
	}

	/**
	 * @return void
	 */
	private function dropTables()
	{
		foreach(self::$tables as $table)
		{
			$this->db->dropTable($table);
		}
	}
}
