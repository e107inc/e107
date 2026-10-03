<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\Schema\FieldTypeMap;
use e107\Database\Schema\MysqlSchemaManager;

/**
 * The MySQL schema manager against the live test database.
 */
class MysqlSchemaManagerTest extends \Test\Unit
{
	/** @var e_db */
	private $db;

	/** @var MysqlSchemaManager */
	private $manager;

	protected function _before()
	{
		$this->db = e107::getDb();

		$this->requireDatabaseDriver('mysql', 'it tests the MySQL schema manager');

		$this->manager = $this->db->getSchemaManager();
	}

	public function testTheConnectionHandsOutItsDriversManager()
	{
		$this->assertInstanceOf(MysqlSchemaManager::class, $this->manager);
		$this->assertSame($this->manager, $this->db->getSchemaManager(), 'one manager per connection');
	}

	public function testThePrefixIsMatchedLiterallyNotAsALikePattern()
	{
		$this->db->execute('DROP TABLE IF EXISTS `e107Xsmtest`');
		$this->db->execute('CREATE TABLE `e107Xsmtest` (id INT)');

		try
		{
			$names = $this->manager->listTableNames('e107_');

			$this->assertContains(MPREFIX.'user', $names);
			$this->assertNotContains('e107Xsmtest', $names, "an unescaped '_' would match any character");
		}
		finally
		{
			$this->db->execute('DROP TABLE IF EXISTS `e107Xsmtest`');
		}
	}

	public function testAPrefixHoldingAQuoteOrAPercentSignIsMatchedLiterally()
	{
		$this->db->execute("DROP TABLE IF EXISTS `sm'te%st_one`, `sm'teXYst_two`");
		$this->db->execute("CREATE TABLE `sm'te%st_one` (id INT)");
		$this->db->execute("CREATE TABLE `sm'teXYst_two` (id INT)");

		try
		{
			$this->assertSame(array("sm'te%st_one"), $this->manager->listTableNames("sm'te%st_"));
		}
		finally
		{
			$this->db->execute("DROP TABLE IF EXISTS `sm'te%st_one`, `sm'teXYst_two`");
		}
	}

	public function testAListingTheServerRefusesIsAFailureNotAnEmptyList()
	{
		$this->assertFalse($this->manager->listTableNames('`e107_no_such_database`.'.MPREFIX));
	}

	public function testColumnRowsHaveTheShowColumnsShape()
	{
		$rows = $this->manager->getColumnRows(MPREFIX.'user');

		$this->assertIsArray($rows);
		$this->assertSame('user_id', $rows[0]['Field']);
		$this->assertSame('PRI', $rows[0]['Key']);
		$this->assertStringContainsString('auto_increment', $rows[0]['Extra']);
		$this->assertSame(array('Field', 'Type', 'Null', 'Key', 'Default', 'Extra'), array_keys($rows[0]));
		$this->assertFalse($this->manager->getColumnRows(MPREFIX.'no_such_table'));
	}

	public function testIndexRowsNameThePrimaryKeyPrimary()
	{
		$rows = $this->manager->getIndexRows(MPREFIX.'user');

		$this->assertSame('PRIMARY', $rows[0]['Key_name']);
		$this->assertSame('user_id', $rows[0]['Column_name']);
		$this->assertSame('0', (string) $rows[0]['Non_unique']);
	}

	public function testADatabaseQualifiedNameIsQuotedPartByPart()
	{
		$database = e107::getMySQLConfig('defaultdb');

		$this->assertSame(
			$this->manager->getColumnRows(MPREFIX.'user'),
			$this->manager->getColumnRows('`'.$database.'`.'.MPREFIX.'user')
		);
		$this->assertSame(
			$this->manager->getColumnRows(MPREFIX.'user'),
			$this->manager->getColumnRows($database.'.'.MPREFIX.'user')
		);
		$this->assertContains(MPREFIX.'user', $this->manager->listTableNames('`'.$database.'`.'.MPREFIX));
	}

	public function testACopyKeepsTheKeysAndTheCreateStatementOfTheOriginal()
	{
		$this->db->execute('DROP TABLE IF EXISTS `'.MPREFIX.'smtest_copy`');

		try
		{
			$this->assertTrue($this->manager->createTableLike(MPREFIX.'user', MPREFIX.'smtest_copy'));

			$this->assertSame(
				$this->keyNames($this->manager->getIndexRows(MPREFIX.'user')),
				$this->keyNames($this->manager->getIndexRows(MPREFIX.'smtest_copy'))
			);
			$this->assertStringStartsWith('CREATE TABLE `'.MPREFIX.'smtest_copy`', $this->manager->getCreateStatement(MPREFIX.'smtest_copy'));
		}
		finally
		{
			$this->db->execute('DROP TABLE IF EXISTS `'.MPREFIX.'smtest_copy`');
		}
	}

	public function testTruncatingStartsTheCounterAgain()
	{
		$this->db->execute('DROP TABLE IF EXISTS `'.MPREFIX.'smtest_trunc`');
		$this->db->execute('CREATE TABLE `'.MPREFIX.'smtest_trunc` (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, v INT) ENGINE=InnoDB');

		try
		{
			$this->db->execute('INSERT INTO `'.MPREFIX.'smtest_trunc` (v) VALUES (1), (2)');
			$this->assertNotFalse($this->manager->truncateTable(MPREFIX.'smtest_trunc'));
			$this->db->execute('INSERT INTO `'.MPREFIX.'smtest_trunc` (v) VALUES (3)');

			$this->assertSame(1, (int) $this->db->lastInsertId());
		}
		finally
		{
			$this->db->execute('DROP TABLE IF EXISTS `'.MPREFIX.'smtest_trunc`');
		}
	}

	/**
	 * The field-type map is now read through the schema reader instead of by parsing SHOW CREATE TABLE with
	 * db_table_admin; on MySQL both must give the same map for every table, or typed writes would bind differently.
	 */
	public function testTheFieldTypeMapMatchesTheOneParsedFromShowCreateTable()
	{
		require_once(e_HANDLER.'db_table_admin_class.php');
		$admin = new db_table_admin();
		$reader = $this->manager->getReader();
		$compared = 0;

		foreach($this->db->tables() as $table)
		{
			$current = $admin->get_current_table($table);

			if(!isset($current[0][2]))
			{
				continue;
			}

			$expected = $admin->make_field_types($admin->parse_field_defs($current[0][2]));
			$actual = FieldTypeMap::fromTableSchema($reader->read(MPREFIX.$table));

			$this->assertSame($expected, $actual, 'field types of '.$table);
			$compared++;
		}

		$this->assertGreaterThan(20, $compared);
	}

	/**
	 * @param array[] $rows SHOW INDEX rows
	 * @return string[] distinct key names, sorted
	 */
	private function keyNames(array $rows)
	{
		$names = array_values(array_unique(array_map(function($row) { return $row['Key_name']; }, $rows)));
		sort($names);

		return $names;
	}
}
