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

use e107\Database\SqlFragment;

/**
 * The SQLite schema manager on an e_db_pdo connection to a database file of its own, so every lane runs it.
 */
class SqliteSchemaManagerTest extends \Test\Unit
{
	/** @var string */
	private $file;

	/** @var \e_db_pdo */
	private $db;

	protected function _before()
	{
		require_once(e_HANDLER.'e_db_pdo_class.php');
		$this->requireSqliteLibrary();

		$this->file = codecept_output_dir().'SqliteSchemaManagerTest_'.getmypid().'.sqlite';
		$this->removeFiles();
		touch($this->file);

		$this->db = new \e_db_pdo();
		$this->db->useDriver('sqlite');
		$this->db->connect('', '', '');
		$this->assertTrue($this->db->database($this->file, 'e107_'), $this->db->getLastErrorText());

		$this->assertNotFalse($this->db->schema()->createTableRaw('item', SqlFragment::raw(
			"item_id int(10) unsigned NOT NULL auto_increment, item_name varchar(100) NOT NULL default '', item_hits int(10) NOT NULL default '0', PRIMARY KEY (item_id), UNIQUE KEY item_name (item_name), KEY item_hits (item_hits)"
		)), $this->db->getLastErrorText());
	}

	protected function _after()
	{
		if($this->db !== null)
		{
			$this->db->close();
		}

		$this->removeFiles();
	}

	public function testAnExpressionDefaultSurvivesARebuild()
	{
		$this->assertNotFalse($this->db->execute("CREATE TABLE e107_raw (raw_id INTEGER PRIMARY KEY, raw_pair TEXT NOT NULL DEFAULT ('x' || 'y'), raw_hits INTEGER NOT NULL DEFAULT 0)"));

		$this->assertNotFalse($this->db->schema()->modifyColumn('raw', 'raw_hits', SqlFragment::raw("varchar(10) NOT NULL DEFAULT ''")), $this->db->getLastErrorText());

		$this->assertNotFalse($this->db->execute('INSERT INTO e107_raw (raw_id) VALUES (1)'));
		$this->assertNotFalse($this->db->execute('SELECT raw_pair FROM e107_raw'));
		$this->assertSame(array('raw_pair' => 'xy'), $this->db->fetch());
	}

	public function testARenamedTableTakesItsIndexesAlongAndLeavesTheirNamesFree()
	{
		$manager = $this->db->getSchemaManager();

		$this->assertTrue($this->db->transactional(function() use ($manager)
		{
			foreach($manager->compileRenameTable('e107_item', 'e107_item_old') as $sql)
			{
				if($this->db->execute($sql) === false)
				{
					return false;
				}
			}

			return true;
		}), $this->db->getLastErrorText());

		$this->assertSame(array('PRIMARY', 'item_name', 'item_hits'), array_keys($manager->getReader()->read('e107_item_old')->getIndexes()));
		$this->assertNotFalse($this->db->schema()->createTableRaw('item', SqlFragment::raw("item_id int NOT NULL, item_name varchar(100) NOT NULL default '', UNIQUE KEY item_name (item_name)")), $this->db->getLastErrorText());
		$this->assertNotFalse($this->db->schema()->dropIndex('item_old', 'item_hits'));
	}

	public function testARebuildKeepsTheRowsAndLeavesOutTheIndexesTheBatchDrops()
	{
		foreach(array('one', 'two', 'three') as $hits => $name)
		{
			$this->assertNotFalse($this->db->execute('INSERT INTO e107_item (item_name, item_hits) VALUES (:n, :h)', array('n' => $name, 'h' => $hits)));
		}

		$table = $this->db->schema()->table('item')->dropIndex('item_hits')->modifyColumn('item_hits', SqlFragment::raw("varchar(10) NOT NULL default ''"));

		$this->assertContains('DROP TABLE `e107_item`', $table->getStatements());
		$this->assertTrue($table->execute(), $this->db->getLastErrorText());
		$this->assertSame(array('PRIMARY', 'item_name'), array_keys($this->db->getSchemaManager()->getReader()->read('e107_item')->getIndexes()));
		$this->assertSame(array('one', 'two', 'three'), $this->db->createQueryBuilder()->select('item_name')->from('item')->orderBy('item_id')->fetchColumn());
	}

	public function testTablesUnderAPrefixAreFalseWhenTheyCannotBeListed()
	{
		$this->assertSame(array('e107_item'), $this->db->getSchemaManager()->listTableNames('e107_'));
		$this->assertFalse($this->db->getSchemaManager()->listTableNames('nowhere.e107_'), 'no database is attached as nowhere');
	}

	public function testALongVarbinaryColumnHoldsBytes()
	{
		$this->assertNotFalse($this->db->schema()->createTableRaw('bytes', SqlFragment::raw('b_data long varbinary')), $this->db->getLastErrorText());

		$this->assertSame('blob', $this->db->getSchemaManager()->getColumnRows('e107_bytes')[0]['Type']);
	}

	/**
	 * @return void
	 */
	private function removeFiles()
	{
		foreach(array('', '-wal', '-shm') as $suffix)
		{
			if(is_file($this->file.$suffix))
			{
				unlink($this->file.$suffix);
			}
		}
	}
}
