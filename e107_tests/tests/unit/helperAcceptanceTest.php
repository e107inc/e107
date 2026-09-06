<?php

/**
 * Helper\Acceptance::parseCreateTableStatements() is what every acceptance test
 * that needs a bundled plugin's schema depends on, and it was quietly wrong.
 *
 * Its pattern required the statement to end immediately after the engine name,
 * so a table declaring options after it did not match. Because the body group
 * is lazy, the engine did not simply skip such a table: it ran the body on to
 * the next statement that did end at its engine. forum has four CREATE TABLEs
 * and three of them carry AUTO_INCREMENT, so the whole file collapsed into one
 * match named `forum` whose body spanned all four. hero, linkwords and pm
 * matched nothing at all, which made havePluginTables() throw for them.
 *
 * The engine was also hardcoded to MyISAM on the way in. hero ships InnoDB, so
 * a test would have exercised a table with different transactional semantics
 * from the one the plugin actually installs.
 *
 * These tests read the shipped SQL rather than a fixture: the point is that the
 * helper agrees with what e107 really ships, and a fixture could drift.
 */
class helperAcceptanceTest extends \Test\Unit
{

	/**
	 * Every CREATE TABLE in the file must come back, whatever trails the engine.
	 */
	public function testParsesEveryTableInAFileWithTableOptions()
	{
		$tables = $this->parse('forum');

		$this->assertCount(4, $tables, 'forum ships four tables and all four must parse');
		$this->assertSame(
			array('forum', 'forum_thread', 'forum_post', 'forum_track'),
			$this->names($tables)
		);
	}

	/**
	 * The failure that hid the bug: one match whose body swallows its siblings.
	 * A body carrying a CREATE TABLE is a body that ran past its own statement.
	 */
	public function testNoTableBodySwallowsAnother()
	{
		foreach ($this->pluginSqlFiles() as $plugin => $file)
		{
			foreach (\Helper\Acceptance::parseCreateTableStatements(file_get_contents($file)) as $table)
			{
				$this->assertStringNotContainsStringIgnoringCase(
					'CREATE TABLE', $table['body'],
					$plugin.': the body of `'.$table['name'].'` runs past its own statement'
				);
			}
		}
	}

	/**
	 * Nothing bundled may parse to zero tables while declaring some. That is the
	 * shape that made havePluginTables() throw for hero, linkwords and pm.
	 */
	public function testEveryBundledPluginParsesAsManyTablesAsItDeclares()
	{
		foreach ($this->pluginSqlFiles() as $plugin => $file)
		{
			$sql = file_get_contents($file);

			$declared = preg_match_all('/CREATE\s+TABLE\s+`?\w+`?/i', $sql);
			$parsed = count(\Helper\Acceptance::parseCreateTableStatements($sql));

			$this->assertSame($declared, $parsed, $plugin.' declares '.$declared.' tables but parses '.$parsed);
		}
	}

	/**
	 * The engine is read from the file rather than assumed. hero is the one
	 * bundled plugin that does not say MyISAM, so it is what stops this
	 * regressing to a hardcoded value.
	 */
	public function testEngineIsReadFromTheFileRatherThanAssumed()
	{
		$hero = $this->parse('hero');

		$this->assertCount(1, $hero);
		$this->assertSame('InnoDB', $hero[0]['engine'], 'hero ships InnoDB');

		$forum = $this->parse('forum');
		$this->assertSame('MyISAM', $forum[0]['engine'], 'forum ships MyISAM');
	}

	/**
	 * What a schema declares and what e107 installs are not the same thing.
	 * db_verify treats the declared engine as a request and satisfies it with
	 * the best available substitute, so a MyISAM schema becomes InnoDB on any
	 * server that has InnoDB. The helper has to make the same substitution or
	 * the table under test has different transactional and FULLTEXT behaviour
	 * from the one the plugin manager builds.
	 */
	public function testDeclaredMyIsamBecomesInnoDbWhereInnoDbExists()
	{
		$modern = array('InnoDB', 'MyISAM', 'MEMORY', 'CSV');

		$this->assertSame('InnoDB', \Helper\Acceptance::intendedStorageEngine('MyISAM', $modern));
		$this->assertSame('InnoDB', \Helper\Acceptance::intendedStorageEngine('myisam', $modern));
		$this->assertSame('InnoDB', \Helper\Acceptance::intendedStorageEngine('InnoDB', $modern));
	}

	/**
	 * The fallbacks still have to work where the preferred engine is absent.
	 */
	public function testDeclaredEngineFallsBackThroughThePreferenceOrder()
	{
		$this->assertSame('Aria',
			\Helper\Acceptance::intendedStorageEngine('MyISAM', array('Aria', 'MyISAM', 'CSV')));

		$this->assertSame('MyISAM',
			\Helper\Acceptance::intendedStorageEngine('MyISAM', array('MyISAM', 'CSV')));

		$this->assertSame('XtraDB',
			\Helper\Acceptance::intendedStorageEngine('InnoDB', array('XtraDB', 'MyISAM')));
	}

	/**
	 * An engine nobody can supply must be refused rather than silently swapped,
	 * so havePluginTables() reports it instead of building the wrong table.
	 */
	public function testUnsatisfiableEngineIsRefused()
	{
		$this->assertFalse(\Helper\Acceptance::intendedStorageEngine('InnoDB', array('MyISAM', 'CSV')));
		$this->assertFalse(\Helper\Acceptance::intendedStorageEngine('RocksDB', array('InnoDB')));
	}

	/**
	 * An engine outside the map is passed through when the server has it. This
	 * is db_verify's behaviour and the helper must not diverge from it.
	 */
	public function testUnmappedEngineIsUsedVerbatimWhenAvailable()
	{
		$this->assertSame('MEMORY',
			\Helper\Acceptance::intendedStorageEngine('MEMORY', array('InnoDB', 'MEMORY')));
	}

	/**
	 * The helper keeps its own copy of the alias table because the acceptance
	 * suite runs outside the application and cannot boot db_verify. This is what
	 * stops that copy drifting: if core ever changes its preferences, the two
	 * stop matching and this fails.
	 */
	public function testStorageEnginePreferenceMatchesTheHandler()
	{
		require_once(e_HANDLER.'db_verify_class.php');

		$this->assertTrue(class_exists('db_verify'), 'db_verify should load from e_HANDLER');

		$defaults = (new \ReflectionClass('db_verify'))->getDefaultProperties();

		$this->assertArrayHasKey('storageEnginePreferenceMap', $defaults,
			'db_verify no longer declares storageEnginePreferenceMap; the helper copy needs revisiting');

		$this->assertSame(
			$defaults['storageEnginePreferenceMap'],
			\Helper\Acceptance::STORAGE_ENGINE_PREFERENCE,
			'Helper\Acceptance::STORAGE_ENGINE_PREFERENCE has drifted from db_verify'
		);
	}

	/**
	 * The body has to be usable on its own, since havePluginTables() wraps it in
	 * a fresh CREATE TABLE. Balanced parentheses is the cheap proxy for that.
	 */
	public function testParsedBodiesHaveBalancedParentheses()
	{
		foreach ($this->pluginSqlFiles() as $plugin => $file)
		{
			foreach (\Helper\Acceptance::parseCreateTableStatements(file_get_contents($file)) as $table)
			{
				$this->assertSame(
					substr_count($table['body'], '('), substr_count($table['body'], ')'),
					$plugin.': unbalanced parentheses in the body of `'.$table['name'].'`'
				);
			}
		}
	}

	public function testAFileWithNoCreateTableParsesToNothing()
	{
		$this->assertSame(array(), \Helper\Acceptance::parseCreateTableStatements('<?php // nothing here'));
	}

	/**
	 * @param string $plugin
	 * @return array
	 */
	private function parse($plugin)
	{
		$file = APP_PATH.'/e107_plugins/'.$plugin.'/'.$plugin.'_sql.php';

		$this->assertFileIsReadable($file);

		return \Helper\Acceptance::parseCreateTableStatements(file_get_contents($file));
	}

	/**
	 * @param array $tables
	 * @return array
	 */
	private function names(array $tables)
	{
		$names = array();

		foreach ($tables as $table)
		{
			$names[] = $table['name'];
		}

		return $names;
	}

	/**
	 * Only the SQL file a plugin is actually named for; some folders carry others.
	 *
	 * @return array plugin folder => path
	 */
	private function pluginSqlFiles()
	{
		$files = array();

		foreach (glob(APP_PATH.'/e107_plugins/*/*_sql.php') as $file)
		{
			$plugin = basename(dirname($file));

			if (basename($file) === $plugin.'_sql.php')
			{
				$files[$plugin] = $file;
			}
		}

		$this->assertNotEmpty($files, 'no bundled plugin SQL files found under '.APP_PATH);

		return $files;
	}
}
