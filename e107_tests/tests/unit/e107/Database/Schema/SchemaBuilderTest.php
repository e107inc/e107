<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

namespace e107\Database\Schema;

use e107\Database\ConnectionInterface;
use e107\Database\IdentifierFilter;
use e107\Database\Platform\MysqlPlatform;
use e107\Database\SqlFragment;
use InvalidArgumentException;
use RuntimeException;

	/**
	 * DB-less tests for {@see SchemaBuilder} and its value objects
	 * {@see Column} / {@see Index}: every test asserts the compiled DDL
	 * string against a stub connection, without executing anything. The focus is
	 * the two guarantees the schema builder adds over raw DDL - fail-closed
	 * identifiers and "structured value object or vouched fragment, never a bare
	 * string" - plus byte-exact SQL skeletons for each verb.
	 */
	class SchemaBuilderTest extends \Codeception\Test\Unit
	{

		protected function _before()
		{
			require_once(e_HANDLER."Database/IdentifierFilter.php");
			require_once(e_HANDLER."Database/Platform/MysqlPlatform.php");
			require_once(e_HANDLER."Database/SqlFragment.php");
			require_once(e_HANDLER."Database/Schema/Column.php");
			require_once(e_HANDLER."Database/Schema/Index.php");
			require_once(e_HANDLER."Database/Schema/SchemaBuilder.php");
		}

		/**
		 * @return SchemaBuilder
		 */
		private function makeSchema(&$stub = null)
		{
			$stub = new SchemaBuilderTest_dbStub();

			return new SchemaBuilder($stub);
		}

		private function assertThrowsInvalidArgument($callback)
		{
			try
			{
				$callback();
			}
			catch(InvalidArgumentException $e)
			{
				$this->assertInstanceOf('InvalidArgumentException', $e);

				return;
			}

			$this->fail('Expected InvalidArgumentException was not thrown');
		}

		// --- ADD / MODIFY / CHANGE / DROP COLUMN ---------------------------

		public function testAddColumnStructured()
		{
			$schema = $this->makeSchema($stub);
			$schema->addColumn('user_extended', 'user_twitter',
				Column::define('VARCHAR', 255)->notNull()->default(''));

			$this->assertEquals(
				"ALTER TABLE `e107_user_extended` ADD COLUMN `user_twitter` VARCHAR(255) NOT NULL DEFAULT ''",
				$stub->lastSql
			);
		}

		public function testAddColumnWithAfter()
		{
			$schema = $this->makeSchema($stub);
			$schema->addColumn('comments', 'comment_author_id',
				Column::define('INT', 10)->unsigned()->notNull()->default('0'),
				'comment_author');

			$this->assertEquals(
				"ALTER TABLE `e107_comments` ADD COLUMN `comment_author_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `comment_author`",
				$stub->lastSql
			);
		}

		public function testAddColumnFirst()
		{
			$schema = $this->makeSchema($stub);
			$schema->addColumn('banlist', 'banlist_id',
				Column::define('INT', 11)->unsigned()->notNull(),
				SchemaBuilder::FIRST);

			$this->assertEquals(
				"ALTER TABLE `e107_banlist` ADD COLUMN `banlist_id` INT(11) UNSIGNED NOT NULL FIRST",
				$stub->lastSql
			);
		}

		public function testAddColumnAcceptsVouchedRawDefinition()
		{
			$schema = $this->makeSchema($stub);
			$schema->addColumn('user_extended', 'user_addon', SqlFragment::raw('JSON'));

			$this->assertEquals(
				"ALTER TABLE `e107_user_extended` ADD COLUMN `user_addon` JSON",
				$stub->lastSql
			);
		}

		public function testAddColumnRejectsBareStringDefinition()
		{
			$schema = $this->makeSchema();

			$this->assertThrowsInvalidArgument(function() use ($schema)
			{
				$schema->addColumn('user_extended', 'user_x', 'VARCHAR(255) NOT NULL');
			});
		}

		public function testAddColumnRejectsHostileColumnName()
		{
			$schema = $this->makeSchema();

			$this->assertThrowsInvalidArgument(function() use ($schema)
			{
				$schema->addColumn('user_extended', 'user`); DROP TABLE x; --',
					Column::define('INT'));
			});
		}

		public function testModifyColumn()
		{
			$schema = $this->makeSchema($stub);
			$schema->modifyColumn('pm_messages', 'pm_subject',
				Column::define('VARCHAR', 45)->notNull()->default(''));

			$this->assertEquals(
				"ALTER TABLE `e107_pm_messages` MODIFY COLUMN `pm_subject` VARCHAR(45) NOT NULL DEFAULT ''",
				$stub->lastSql
			);
		}

		public function testChangeColumn()
		{
			$schema = $this->makeSchema($stub);
			$schema->changeColumn('plugin', 'plugin_rss', 'plugin_addons',
				SqlFragment::raw('TEXT NOT NULL'));

			$this->assertEquals(
				"ALTER TABLE `e107_plugin` CHANGE COLUMN `plugin_rss` `plugin_addons` TEXT NOT NULL",
				$stub->lastSql
			);
		}

		public function testDropColumn()
		{
			$schema = $this->makeSchema($stub);
			$schema->dropColumn('comments', 'comment_author');

			$this->assertEquals(
				"ALTER TABLE `e107_comments` DROP COLUMN `comment_author`",
				$stub->lastSql
			);
		}

		// --- BATCHED ALTER --------------------------------------------------

		public function testBatchedAlterCombinesClauses()
		{
			$schema = $this->makeSchema($stub);
			$schema->table('comments')
				->addColumn('comment_author_id', Column::define('INT', 10)->unsigned()->notNull()->default('0'), 'comment_author')
				->addColumn('comment_author_name', Column::define('VARCHAR', 100)->notNull()->default(''), 'comment_author_id')
				->execute();

			$this->assertEquals(
				"ALTER TABLE `e107_comments`"
				." ADD COLUMN `comment_author_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `comment_author`,"
				." ADD COLUMN `comment_author_name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `comment_author_id`",
				$stub->lastSql
			);
		}

		public function testEmptyAlterThrows()
		{
			$schema = $this->makeSchema();

			try
			{
				$schema->table('comments')->getSQL();
			}
			catch(RuntimeException $e)
			{
				$this->assertInstanceOf('RuntimeException', $e);

				return;
			}

			$this->fail('Expected RuntimeException for an empty ALTER was not thrown');
		}

		public function testAddRawClause()
		{
			$schema = $this->makeSchema($stub);
			$schema->table('banlist')
				->addRaw(SqlFragment::raw('ADD `banlist_id` INT(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'))
				->execute();

			$this->assertEquals(
				"ALTER TABLE `e107_banlist` ADD `banlist_id` INT(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST",
				$stub->lastSql
			);
		}

		public function testAddRawRejectsBareString()
		{
			$schema = $this->makeSchema();

			$this->assertThrowsInvalidArgument(function() use ($schema)
			{
				$schema->table('banlist')->addRaw('ADD `x` INT');
			});
		}

		// --- INDEXES --------------------------------------------------------

		public function testAddIndex()
		{
			$schema = $this->makeSchema($stub);
			$schema->addIndex('generic', Index::index('gen_type', 'gen_type'));

			$this->assertEquals(
				"ALTER TABLE `e107_generic` ADD INDEX `gen_type` (`gen_type`)",
				$stub->lastSql
			);
		}

		public function testAddUniqueIndexMultiColumn()
		{
			$schema = $this->makeSchema($stub);
			$schema->addIndex('plugin', Index::unique('u_path', array('plugin_path', 'plugin_id')));

			$this->assertEquals(
				"ALTER TABLE `e107_plugin` ADD UNIQUE KEY `u_path` (`plugin_path`, `plugin_id`)",
				$stub->lastSql
			);
		}

		public function testDropIndex()
		{
			$schema = $this->makeSchema($stub);
			$schema->dropIndex('tmp', 'tmp_ip');

			$this->assertEquals(
				"ALTER TABLE `e107_tmp` DROP INDEX `tmp_ip`",
				$stub->lastSql
			);
		}

		public function testAddPrimaryKey()
		{
			$schema = $this->makeSchema($stub);
			$schema->addPrimaryKey('banlist', 'banlist_id');

			$this->assertEquals(
				"ALTER TABLE `e107_banlist` ADD PRIMARY KEY (`banlist_id`)",
				$stub->lastSql
			);
		}

		public function testDropPrimaryKey()
		{
			$schema = $this->makeSchema($stub);
			$schema->dropPrimaryKey('banlist');

			$this->assertEquals(
				"ALTER TABLE `e107_banlist` DROP PRIMARY KEY",
				$stub->lastSql
			);
		}

		public function testAddIndexRejectsBareString()
		{
			$schema = $this->makeSchema();

			$this->assertThrowsInvalidArgument(function() use ($schema)
			{
				$schema->addIndex('generic', 'INDEX `x` (`y`)');
			});
		}

		public function testIndexRejectsHostileColumn()
		{
			$this->assertThrowsInvalidArgument(function()
			{
				Index::index('idx', 'col`); DROP');
			});
		}

		// --- ENGINE / CHARSET ----------------------------------------------

		public function testEngineAndCharsetClauses()
		{
			$schema = $this->makeSchema($stub);
			$schema->table('news')
				->engine('InnoDB')
				->charset('utf8mb4')
				->execute();

			$this->assertEquals(
				"ALTER TABLE `e107_news` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4",
				$stub->lastSql
			);
		}

		public function testEngineRejectsHostileName()
		{
			$schema = $this->makeSchema();

			$this->assertThrowsInvalidArgument(function() use ($schema)
			{
				$schema->setEngine('news', 'InnoDB; DROP TABLE x');
			});
		}

		// --- CREATE / RENAME / OPTIMIZE TABLE ------------------------------

		public function testCreateTableStructured()
		{
			$schema = $this->makeSchema($stub);
			$schema->createTable('foo',
				array(
					'foo_id'   => Column::define('INT', 10)->unsigned()->notNull()->autoIncrement(),
					'foo_name' => Column::define('VARCHAR', 100)->notNull()->default(''),
				),
				array(
					Index::primary('foo_id'),
				),
				array('engine' => 'InnoDB', 'charset' => 'utf8mb4')
			);

			$this->assertEquals(
				"CREATE TABLE `e107_foo` ("
				."`foo_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, "
				."`foo_name` VARCHAR(100) NOT NULL DEFAULT '', "
				."PRIMARY KEY (`foo_id`)"
				.") ENGINE = InnoDB DEFAULT CHARSET = utf8mb4",
				$stub->lastSql
			);
		}

		public function testCreateTableRawBody()
		{
			$schema = $this->makeSchema($stub);
			$schema->createTableRaw('foo',
				SqlFragment::raw("`foo_id` int(10) NOT NULL"),
				SqlFragment::raw(" ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4")
			);

			$this->assertEquals(
				"CREATE TABLE `e107_foo` (`foo_id` int(10) NOT NULL) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4",
				$stub->lastSql
			);
		}

		public function testRenameTable()
		{
			$schema = $this->makeSchema($stub);
			$schema->renameTable('rl_history', 'dblog');

			$this->assertEquals(
				"RENAME TABLE `e107_rl_history` TO `e107_dblog`",
				$stub->lastSql
			);
		}

		// --- PHYSICAL (NON-ROUTED) TABLE TARGETING ------------------------

		public function testTablePhysicalAppliesPrefixOnly()
		{
			$schema = $this->makeSchema($stub);
			$schema->tablePhysical('comments')
				->addRaw(SqlFragment::raw("DROP `foo`"))
				->execute();

			$this->assertEquals(
				"ALTER TABLE `e107_comments` DROP `foo`",
				$stub->lastSql
			);
		}

		public function testTablePhysicalBypassesLanguageRouting()
		{
			// table() honours the connection's lan_* routing; tablePhysical()
			// addresses the literal prefixed table, which schema-maintenance tools
			// (db_verify, db_table_admin) require.
			$schema = $this->makeSchema($stub);

			$schema->table('routedtable')->addRaw(SqlFragment::raw("DROP `foo`"))->execute();
			$this->assertEquals(
				"ALTER TABLE `e107_lan_test_routedtable` DROP `foo`",
				$stub->lastSql
			);

			$schema->tablePhysical('routedtable')->addRaw(SqlFragment::raw("DROP `foo`"))->execute();
			$this->assertEquals(
				"ALTER TABLE `e107_routedtable` DROP `foo`",
				$stub->lastSql
			);
		}

		public function testTablePhysicalRejectsHostileName()
		{
			$schema = $this->makeSchema($stub);

			$this->assertThrowsInvalidArgument(function() use ($schema) {
				$schema->tablePhysical('bad`name');
			});
		}

		public function testBuildCreateTablePhysicalRaw()
		{
			$schema = $this->makeSchema($stub);
			$sql = $schema->buildCreateTablePhysicalRaw(
				'routedtable',
				SqlFragment::raw("id int(10) unsigned NOT NULL"),
				SqlFragment::raw(" ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4;")
			);

			// Returns the statement text (no execute) for a literal prefixed table.
			$this->assertEquals(
				"CREATE TABLE `e107_routedtable` (id int(10) unsigned NOT NULL) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4;",
				$sql
			);
			$this->assertNull($stub->lastSql);
		}

		public function testBuildCreateTablePhysicalRawRejectsBareString()
		{
			$schema = $this->makeSchema($stub);

			$this->assertThrowsInvalidArgument(function() use ($schema) {
				$schema->buildCreateTablePhysicalRaw('foo', 'id int(10)');
			});
		}

		public function testBuildCreateTablePhysicalRawRejectsHostileName()
		{
			$schema = $this->makeSchema($stub);

			$this->assertThrowsInvalidArgument(function() use ($schema) {
				$schema->buildCreateTablePhysicalRaw('bad`name', SqlFragment::raw("id int(10)"));
			});
		}

		public function testOptimizeTableSingle()
		{
			$schema = $this->makeSchema($stub);
			$schema->optimizeTable('news');

			$this->assertEquals("OPTIMIZE TABLE `e107_news`", $stub->lastSql);
		}

		public function testOptimizeTableMultiple()
		{
			$schema = $this->makeSchema($stub);
			$schema->optimizeTable(array('news', 'user'));

			$this->assertEquals("OPTIMIZE TABLE `e107_news`, `e107_user`", $stub->lastSql);
		}

		// --- ADMIN-FENCED VERBS --------------------------------------------

		public function testCreateDatabase()
		{
			$schema = $this->makeSchema($stub);
			$schema->createDatabase('mydb', 'utf8mb4');

			$this->assertEquals("CREATE DATABASE `mydb` CHARACTER SET utf8mb4", $stub->lastSql);
		}

		public function testCreateDatabaseWithoutCharset()
		{
			$schema = $this->makeSchema($stub);
			$schema->createDatabase('mydb');

			$this->assertEquals("CREATE DATABASE `mydb`", $stub->lastSql);
		}

		public function testCreateDatabaseRejectsHostileName()
		{
			$schema = $this->makeSchema();

			$this->assertThrowsInvalidArgument(function() use ($schema)
			{
				$schema->createDatabase('my`db');
			});
		}

		public function testGrant()
		{
			$schema = $this->makeSchema($stub);
			$schema->grant('mydb', 'myuser', 'localhost');

			$this->assertEquals("GRANT ALL ON `mydb`.* TO `myuser`@'localhost'", $stub->lastSql);
		}

		public function testGrantRejectsHostileHost()
		{
			$schema = $this->makeSchema();

			$this->assertThrowsInvalidArgument(function() use ($schema)
			{
				$schema->grant('mydb', 'myuser', "local'host");
			});
		}

		public function testFlushPrivileges()
		{
			$schema = $this->makeSchema($stub);
			$schema->flushPrivileges();

			$this->assertEquals("FLUSH PRIVILEGES", $stub->lastSql);
		}

		// --- DELEGATION + INTROSPECTION ------------------------------------

		public function testDropTableDelegates()
		{
			$schema = $this->makeSchema($stub);
			$schema->dropTable('tmp');

			$this->assertEquals('tmp', $stub->droppedTable);
			$this->assertNull($stub->lastSql);
		}

		public function testTruncateDelegates()
		{
			$schema = $this->makeSchema($stub);
			$schema->truncate('tmp');

			$this->assertEquals('tmp', $stub->truncatedTable);
			$this->assertNull($stub->lastSql);
		}

		public function testGetColumnsIntrospection()
		{
			$schema = $this->makeSchema($stub);
			$stub->rows = array(
				array('Field' => 'user_id', 'Type' => 'int(10) unsigned'),
				array('Field' => 'user_name', 'Type' => 'varchar(100)'),
			);

			$cols = $schema->getColumns('user');

			$this->assertEquals("SHOW COLUMNS FROM `e107_user`", $stub->lastSql);
			$this->assertCount(2, $cols);
			$this->assertEquals('user_name', $cols[1]['Field']);
		}

		public function testGetCreateTable()
		{
			$schema = $this->makeSchema($stub);
			$stub->rows = array(
				array('Table' => 'e107_user', 'Create Table' => 'CREATE TABLE `e107_user` (...)'),
			);

			$create = $schema->getCreateTable('user');

			$this->assertEquals("SHOW CREATE TABLE `e107_user`", $stub->lastSql);
			$this->assertEquals('CREATE TABLE `e107_user` (...)', $create);
		}

		public function testInvalidTableNameThrows()
		{
			$schema = $this->makeSchema();

			$this->assertThrowsInvalidArgument(function() use ($schema)
			{
				$schema->addColumn('bad table!', 'x', Column::define('INT'));
			});
		}

		// --- Column value object --------------------------------------

		public function testColumnRendersAllPieces()
		{
			$col = Column::define('INT', 10)->unsigned()->zerofill()->notNull()
				->default(0)->autoIncrement()->comment("it's fine");

			$this->assertEquals(
				"INT(10) UNSIGNED ZEROFILL NOT NULL DEFAULT 0 AUTO_INCREMENT COMMENT 'it''s fine'",
				$col->getDefinition()
			);
		}

		public function testColumnDefaultNull()
		{
			$col = Column::define('VARCHAR', 255)->nullable()->default(null);

			$this->assertEquals("VARCHAR(255) NULL DEFAULT NULL", $col->getDefinition());
		}

		public function testColumnStringDefaultIsEscaped()
		{
			$col = Column::define('VARCHAR', 50)->notNull()->default("a'b\\c");

			$this->assertEquals("VARCHAR(50) NOT NULL DEFAULT 'a''b\\\\c'", $col->getDefinition());
		}

		public function testColumnDefaultRawExpression()
		{
			$col = Column::define('DATETIME')->notNull()->defaultRaw('CURRENT_TIMESTAMP');

			$this->assertEquals("DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP", $col->getDefinition());
		}

		public function testColumnRawWholeDefinition()
		{
			$col = Column::raw(SqlFragment::raw("ENUM('a','b') NOT NULL DEFAULT 'a'"));

			$this->assertEquals("ENUM('a','b') NOT NULL DEFAULT 'a'", $col->getDefinition());
		}

		public function testColumnRejectsBareStringRaw()
		{
			$this->assertThrowsInvalidArgument(function()
			{
				Column::raw("VARCHAR(255)");
			});
		}

		public function testColumnRejectsHostileType()
		{
			$this->assertThrowsInvalidArgument(function()
			{
				Column::define('INT; DROP TABLE x');
			});
		}

		public function testColumnRejectsHostileLength()
		{
			$this->assertThrowsInvalidArgument(function()
			{
				Column::define('VARCHAR', '255) NOT NULL, ADD x INT(1');
			});
		}

		// --- Index value object ---------------------------------------

		public function testIndexFulltext()
		{
			$index = Index::fulltext('ft_body', array('news_body', 'news_extended'));

			$this->assertEquals("FULLTEXT KEY `ft_body` (`news_body`, `news_extended`)", $index->getDefinition());
		}

		public function testIndexPrimaryMultiColumn()
		{
			$index = Index::primary(array('a_id', 'b_id'));

			$this->assertEquals("PRIMARY KEY (`a_id`, `b_id`)", $index->getDefinition());
			$this->assertNull($index->getQuotedName());
		}

		public function testIndexRawDefinition()
		{
			$index = Index::raw(SqlFragment::raw("KEY `k` (`col`(10))"));

			$this->assertEquals("KEY `k` (`col`(10))", $index->getDefinition());
		}

		public function testIndexRejectsEmptyColumns()
		{
			$this->assertThrowsInvalidArgument(function()
			{
				Index::index('k', array());
			});
		}
	}


	/**
	 * Stand-in for a ConnectionInterface connection: resolves table names with a fixed prefix
	 * and no language routing, records what execute() receives, serves queued
	 * rows through fetch(), and records dropTable()/truncate() delegations.
	 */
	class SchemaBuilderTest_dbStub
	{
		public $lastSql = null;
		public $lastParams = null;
		public $rows = array();
		public $executeReturn = 1;
		public $droppedTable = null;
		public $truncatedTable = null;

		public function resolveTableName($table)
		{
			$table = ltrim((string) $table, '#');

			if(!preg_match('/^[A-Za-z0-9_]+$/D', $table))
			{
				return false;
			}

			// Simulate multi-language lan_* routing for one magic table so the
			// tests can prove tablePhysical() bypasses it (see resolvePhysicalTableName()).
			if($table === 'routedtable')
			{
				return 'e107_lan_test_routedtable';
			}

			return 'e107_'.$table;
		}

		public function resolvePhysicalTableName($table)
		{
			$table = ltrim((string) $table, '#');

			if(!preg_match('/^[A-Za-z0-9_]+$/D', $table))
			{
				return false;
			}

			// Prefix only, never the lan_* routing resolveTableName() applies.
			return 'e107_'.$table;
		}

		public function quoteIdentifier($identifier)
		{
			return IdentifierFilter::identifier($identifier);
		}

		public function getPlatform()
		{
			return new MysqlPlatform();
		}

		public function execute($sql, $params = array())
		{
			$this->lastSql = $sql;
			$this->lastParams = $params;

			return $this->executeReturn;
		}

		public function fetch()
		{
			$row = array_shift($this->rows);

			return ($row === null) ? false : $row;
		}

		public function dropTable($table)
		{
			$this->droppedTable = $table;

			return true;
		}

		public function truncate($table)
		{
			$this->truncatedTable = $table;

			return true;
		}
	}
