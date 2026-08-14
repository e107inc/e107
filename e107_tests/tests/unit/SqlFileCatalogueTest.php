<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\Schema\Declared\DeclaredTable;
use e107\Database\Schema\Declared\SqlFileCatalogue;

/**
 * {@see SqlFileCatalogue} read against a frozen copy of the parser it replaces,
 * over every `CREATE TABLE` e107 ships. Nothing here touches the database.
 *
 * `getSqlFileTables()` now delegates here, which means comparing the two at
 * runtime would be comparing this class against a projection of itself and
 * would pass however wrong the port was. The reference implementation is
 * therefore frozen into this file: {@see SqlFileCatalogueTest::legacyReferenceParse()}
 * is the body of `db_verify::getSqlFileTables()` as it stood on `master`
 * (@ ea0762ac7d), expressions and all, and the differential runs against that.
 * It is a fixture, not a dependency; it is not maintained alongside the class
 * under test, and that is the point.
 *
 * The examples that follow pin the cases the corpus happens not to contain,
 * including two legacy behaviours that are deliberately preserved rather than
 * quietly fixed, and one defect of the reference that the new return shape makes
 * unrepresentable.
 *
 * Nothing here touches the database.
 */
class SqlFileCatalogueTest extends \Test\Unit
{
	/** @var SqlFileCatalogue */
	private $catalogue;

	protected function _before()
	{
		require_once(e_HANDLER.'db_verify_class.php');

		$this->catalogue = new SqlFileCatalogue();
	}

	public function testTheCatalogueAutoloadsFromItsNamespacePath()
	{
		$this->assertTrue(
			class_exists('e107\\Database\\Schema\\Declared\\SqlFileCatalogue'),
			'SqlFileCatalogue must autoload from e107_handlers/Database/Schema/Declared/SqlFileCatalogue.php'
		);
	}

	// --- the differential test over the shipped corpus ---------------------

	public function testItAgreesWithTheFrozenLegacyParserOverTheWholeCorpus()
	{
		$files = $this->corpus();

		$this->assertGreaterThan(10, count($files), 'The corpus should be core_sql.php plus every bundled plugin schema file.');

		$tablesSeen = 0;

		foreach($files as $sqlFile => $path)
		{
			$sqlText = file_get_contents($path);

			$legacy = self::legacyReferenceParse($sqlText);
			$actual = $this->catalogue->parse($sqlText, $sqlFile);

			$this->assertNotEmpty($legacy['tables'], $path.' should declare at least one table.');

			$this->assertCount(
				count($legacy['tables']),
				$legacy['engine'],
				$path.": the reference parser's parallel arrays are out of step, so this comparison would be "
				."comparing one table's body against another table's engine. Every table in the tree declares "
				."table options; if that stops being true, see testTheLegacyOrdinalSlipIsUnrepresentable()."
			);

			$expected = $this->expectedFromLegacy($legacy);

			$this->assertSame(array_keys($expected), array_keys($actual), $path.': declared table names differ.');

			foreach($expected as $table => $want)
			{
				$this->assertInstanceOf(DeclaredTable::class, $actual[$table]);
				$this->assertSame($want['body'], $actual[$table]->getBody(), $path.' ['.$table.']: body differs.');
				$this->assertSame($want['engine'], $actual[$table]->getDeclaredEngine(), $path.' ['.$table.']: engine differs.');
				$this->assertSame($want['charset'], $actual[$table]->getDeclaredCharset(), $path.' ['.$table.']: charset differs.');
				$this->assertSame($sqlFile, $actual[$table]->getSqlFile(), $path.' ['.$table.']: declaring file differs.');

				$tablesSeen++;
			}
		}

		$this->assertGreaterThan(50, $tablesSeen, 'The corpus should cover a few dozen tables.');
	}

	public function testTheLegacyProjectionKeepsEveryArrayInStepOverTheWholeCorpus()
	{
		$legacyParser = new db_verify(false);
		$tablesSeen = 0;

		foreach($this->corpus() as $sqlFile => $path)
		{
			$sqlText = file_get_contents($path);

			$projected = $legacyParser->getSqlFileTables($sqlText);
			$declared = array_values($this->catalogue->parse($sqlText, $sqlFile));

			$this->assertSame(range(0, count($declared) - 1), array_keys($projected['tables']), $path.': ordinals must be dense and zero-based.');
			$this->assertSame(array_keys($projected['tables']), array_keys($projected['data']), $path.': data is out of step with tables.');
			$this->assertSame(array_keys($projected['tables']), array_keys($projected['engine']), $path.': engine is out of step with tables.');
			$this->assertSame(array_keys($projected['tables']), array_keys($projected['charset']), $path.': charset is out of step with tables.');

			foreach($declared as $ordinal => $table)
			{
				$this->assertSame($table->getName(), $projected['tables'][$ordinal], $path.' ['.$ordinal.']: name.');
				$this->assertSame($table->getBody(), $projected['data'][$ordinal], $path.' ['.$ordinal.']: body.');
				$this->assertSame($table->getDeclaredEngine(), $projected['engine'][$ordinal], $path.' ['.$ordinal.']: engine.');
				$this->assertSame($table->getDeclaredCharset(), $projected['charset'][$ordinal], $path.' ['.$ordinal.']: charset.');

				$tablesSeen++;
			}
		}

		$this->assertGreaterThan(50, $tablesSeen, 'The corpus should cover a few dozen tables.');
	}

	public function testTheLegacyProjectionStillRefusesEmptyInput()
	{
		$this->assertFalse((new db_verify(false))->getSqlFileTables(''));
	}

	// --- cases the corpus does not contain --------------------------------

	public function testAFileWithNoCreateTableDeclaresNoTables()
	{
		$this->assertSame(array(), $this->catalogue->parse("<?php\n// A plugin that ships no schema.\n", 'nothing'));
	}

	public function testEmptyTextDeclaresNoTables()
	{
		$this->assertSame(array(), $this->catalogue->parse('', 'nothing'));
	}

	public function testATableWithNoEngineClauseDeclaresNeitherEngineNorCharset()
	{
		$table = $this->parseOne("CREATE TABLE e107_foo (\n\tfoo_id int(10) unsigned NOT NULL\n);");

		$this->assertSame('foo', $table->getName());
		$this->assertNull($table->getDeclaredEngine());
		$this->assertNull($table->getDeclaredCharset());
		$this->assertSame('foo_id int(10) unsigned NOT NULL', $table->getBody());
	}

	public function testTypeIsAcceptedAsTheLegacySpellingOfEngine()
	{
		$table = $this->parseOne("CREATE TABLE e107_foo (foo_id int(10) NOT NULL) TYPE=MyISAM;");

		$this->assertSame('MyISAM', $table->getDeclaredEngine());
	}

	public function testDefaultCharacterSetIsRecognised()
	{
		$table = $this->parseOne("CREATE TABLE e107_foo (foo_id int(10) NOT NULL) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4;");

		$this->assertSame('InnoDB', $table->getDeclaredEngine());
		$this->assertSame('utf8mb4', $table->getDeclaredCharset());
	}

	/**
	 * The four spellings the legacy switch knows, all of which reach the same
	 * field.
	 *
	 * @return array
	 */
	public function charsetSpellings()
	{
		return array(
			'DEFAULT CHARSET'       => array('DEFAULT CHARSET=utf8mb4'),
			'DEFAULT CHARACTER SET' => array('DEFAULT CHARACTER SET=utf8mb4'),
			'CHARSET'               => array('CHARSET=utf8mb4'),
			'CHARACTER SET'         => array('CHARACTER SET=utf8mb4'),
		);
	}

	/**
	 * @dataProvider charsetSpellings
	 * @param string $clause
	 */
	public function testEveryCharacterSetSpellingReachesTheSameField($clause)
	{
		$table = $this->parseOne("CREATE TABLE e107_foo (foo_id int(10) NOT NULL) ENGINE=InnoDB ".$clause.";");

		$this->assertSame('utf8mb4', $table->getDeclaredCharset());
	}

	/**
	 * A legacy limitation, preserved on purpose. MySQL accepts
	 * `DEFAULT CHARACTER SET utf8mb4` without an equals sign, but the ported
	 * statement expression requires `name=value` in the options tail, so a
	 * statement whose only option is spelled that way is not seen as a
	 * `CREATE TABLE` at all. Widening the expression would change how every
	 * body in the corpus is captured, which is a bigger change than this file
	 * is allowed to make; it is recorded here so the next reader finds it
	 * stated rather than discovers it.
	 */
	public function testCharacterSetWithoutAnEqualsSignIsInvisibleToTheSplitter()
	{
		$sql = "CREATE TABLE e107_foo (foo_id int(10) NOT NULL) DEFAULT CHARACTER SET utf8mb4;";

		$this->assertSame(array(), $this->catalogue->parse($sql, 'core'));
		$this->assertSame(array(), (new db_verify(false))->getSqlFileTables($sql)['tables'], 'The legacy parser misses it too.');
	}

	public function testAnEqualsLessCharacterSetBesideAnEngineReadsAsNoCharset()
	{
		$table = $this->parseOne("CREATE TABLE e107_foo (foo_id int(10) NOT NULL) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4;");

		$this->assertSame('InnoDB', $table->getDeclaredEngine());
		$this->assertNull($table->getDeclaredCharset());
	}

	public function testTheUppercaseEngineSpellingIsCanonicalised()
	{
		$table = $this->parseOne("CREATE TABLE e107_foo (foo_id int(10) NOT NULL) ENGINE = MYISAM;");

		$this->assertSame('MyISAM', $table->getDeclaredEngine());
	}

	public function testTableOptionKeywordsAreReadCaseInsensitively()
	{
		$table = $this->parseOne("CREATE TABLE e107_foo (foo_id int(10) NOT NULL) engine=InnoDB default charset=utf8mb4;");

		$this->assertSame('InnoDB', $table->getDeclaredEngine());
		$this->assertSame('utf8mb4', $table->getDeclaredCharset());
	}

	public function testCommentedOutTablesAreNotDeclared()
	{
		$sql = "/*\nCREATE TABLE e107_hidden (x int) ENGINE=MyISAM;\n*/\nCREATE TABLE e107_real (x int) ENGINE=MyISAM;";

		$this->assertSame(array('real'), array_keys($this->catalogue->parse($sql, 'core')));
	}

	public function testTheDumpPrefixIsStrippedFromTheDeclaredName()
	{
		$sql = "CREATE TABLE IF NOT EXISTS `e107_foo` (x int) ENGINE=MyISAM;\nCREATE TABLE `bar` (x int) ENGINE=MyISAM;";

		$this->assertSame(array('foo', 'bar'), array_keys($this->catalogue->parse($sql, 'core')));
	}

	public function testTabsAreStrippedFromTheBodyAsTheLegacyPipelineAlwaysDid()
	{
		$table = $this->parseOne("CREATE TABLE e107_foo (\n\t\tfoo_id int(10) NOT NULL,\n\t\tPRIMARY KEY (foo_id)\n) ENGINE=MyISAM;");

		$this->assertSame("foo_id int(10) NOT NULL,\nPRIMARY KEY (foo_id)", $table->getBody());
	}

	// --- the shape change ---------------------------------------------------

	/**
	 * The reference implementation returns `tables`, `data`, `engine` and
	 * `charset` as parallel ordinal arrays, and appends to the last two rather
	 * than keying them, skipping any table that declares no options at all. From
	 * that table onwards every engine belongs to the wrong table: below, the
	 * engine declared by `b` is reported against `a`, and `b` has none.
	 *
	 * That is not cosmetic. `db_verify::compare()` feeds the pair to the
	 * materialiser, and `e107plugin`'s installer creates the table with it, so a
	 * plugin schema file with one option-less declaration built every later table
	 * in that file with another table's character set.
	 *
	 * This is the one place the catalogue is allowed to disagree with the
	 * reference, and it is why the assertion below is on the frozen copy rather
	 * than on the shipped method: each engine is a field on the table that
	 * declared it, so there is no index left to slip, and
	 * {@see SqlFileCatalogueTest::testTheLegacyProjectionKeepsEveryArrayInStepOverTheWholeCorpus()}
	 * shows the shipped projection now keys them too.
	 */
	public function testTheLegacyOrdinalSlipIsUnrepresentable()
	{
		$sql = "CREATE TABLE e107_a (a int);\nCREATE TABLE e107_b (b int) ENGINE=MyISAM;";

		$reference = self::legacyReferenceParse($sql);

		$this->assertSame(array('a', 'b'), $reference['tables']);
		$this->assertSame(array('MyISAM'), $reference['engine'], "Reference: b's engine sits at a's ordinal.");

		$actual = $this->catalogue->parse($sql, 'core');

		$this->assertNull($actual['a']->getDeclaredEngine());
		$this->assertSame('MyISAM', $actual['b']->getDeclaredEngine());

		$shipped = (new db_verify(false))->getSqlFileTables($sql);

		$this->assertSame(array(null, 'MyISAM'), $shipped['engine'], "Shipped: every engine sits at its own table's ordinal.");
	}

	public function testEveryTableCarriesTheFileThatDeclaredIt()
	{
		$tables = $this->catalogue->parse(file_get_contents(e_PLUGIN.'forum/forum_sql.php'), 'forum');

		$this->assertNotEmpty($tables);

		foreach($tables as $table)
		{
			$this->assertSame('forum', $table->getSqlFile());
		}
	}

	public function testARepeatedDeclarationKeepsTheLastOne()
	{
		$sql = "CREATE TABLE e107_foo (old int) ENGINE=MyISAM;\nCREATE TABLE e107_foo (new int) ENGINE=InnoDB;";

		$tables = $this->catalogue->parse($sql, 'core');

		$this->assertSame(array('foo'), array_keys($tables));
		$this->assertSame('new int', $tables['foo']->getBody());
		$this->assertSame('InnoDB', $tables['foo']->getDeclaredEngine());
	}

	// --- fail closed --------------------------------------------------------

	public function testAnEmptyDeclaringFileNameIsRefused()
	{
		$this->expectException('InvalidArgumentException');

		$this->catalogue->parse("CREATE TABLE e107_foo (x int) ENGINE=MyISAM;", '  ');
	}

	public function testAnEmptyDeclaringFileNameIsRefusedEvenWhenNoTableIsDeclared()
	{
		$this->expectException('InvalidArgumentException');

		$this->catalogue->parse("<?php\n// A plugin that ships no schema.\n", '  ');
	}

	public function testAnUnnamedTableIsRefused()
	{
		$this->expectException('InvalidArgumentException');

		$this->catalogue->parse("CREATE TABLE (x int) ENGINE=MyISAM;", 'core');
	}

	public function testATableNamedOnlyByTheDumpPrefixIsRefused()
	{
		$this->expectException('InvalidArgumentException');

		$this->catalogue->parse("CREATE TABLE e107_ (x int) ENGINE=MyISAM;", 'core');
	}

	/**
	 * "This file declares no tables" and "the expression could not be run over
	 * this file" are different answers, and reading the second as the first is
	 * how a drifted table passes as clean: `db_verify` verifies what the
	 * catalogue reports and nothing else. PCRE abandons a subject rather than
	 * answering wrongly, and so does this.
	 */
	public function testAStatementExpressionThatPcreAbandonsIsRefused()
	{
		$sql = "CREATE TABLE e107_foo (".str_repeat("a int NOT NULL, ", 200)."b int) ENGINE=MyISAM;";

		$this->assertCount(1, $this->catalogue->parse($sql, 'core'), 'The text parses at the default PCRE limits.');

		$this->assertPcreFailureIsRefused($sql, 'CREATE TABLE statements');
	}

	/**
	 * The same rule for the comment strip, which runs first and exhausts its
	 * own budget.
	 */
	public function testACommentStripThatPcreAbandonsIsRefused()
	{
		$sql = "/*".str_repeat('x', 5000)."*/\nCREATE TABLE e107_foo (x int) ENGINE=MyISAM;";

		$this->assertSame(array('foo'), array_keys($this->catalogue->parse($sql, 'core')), 'The text parses at the default PCRE limits.');

		$this->assertPcreFailureIsRefused($sql, 'strip comments');
	}

	// --- helpers ------------------------------------------------------------

	/**
	 * Assert the catalogue refuses $sql under a backtrack limit no expression can finish within.
	 *
	 * @param string $sql
	 * @param string $expectedBranch fragment naming the expression that gave up
	 */
	private function assertPcreFailureIsRefused($sql, $expectedBranch)
	{
		$was = ini_get('pcre.backtrack_limit');
		ini_set('pcre.backtrack_limit', '10');

		try
		{
			$this->catalogue->parse($sql, 'plugin_under_test');
			$thrown = null;
		}
		catch(\Exception $e)
		{
			$thrown = $e;
		}

		ini_set('pcre.backtrack_limit', $was);

		$this->assertInstanceOf(
			'RuntimeException',
			$thrown,
			'A PCRE failure must be refused, not answered with an empty catalogue.'
		);
		$this->assertStringContainsString($expectedBranch, $thrown->getMessage());
		$this->assertStringContainsString(
			'plugin_under_test',
			$thrown->getMessage(),
			'The refusal must name the file it could not read.'
		);
	}

	/**
	 * The body of `db_verify::getSqlFileTables()` as it stood on `master` at
	 * ea0762ac7d, with the messages and the `$this->currentTable` reference dropped.
	 *
	 * A frozen fixture, deliberately not refactored and deliberately not shared
	 * with the class it tests. `getSqlFileTables()` now delegates to
	 * {@see SqlFileCatalogue}, so comparing the shipped method against the
	 * catalogue would compare the catalogue against itself; this is the copy that
	 * still knows what the old expressions did, including the ordinal slip
	 * {@see SqlFileCatalogueTest::testTheLegacyOrdinalSlipIsUnrepresentable()}
	 * pins as the one intended difference.
	 *
	 * @param string $sql_data
	 * @return array ['tables'=>[], 'data'=>[], 'engine'=>[], 'charset'=>[]]
	 */
	private static function legacyReferenceParse($sql_data)
	{
		$ret = array();

		$sql_data = preg_replace("#\/\*.*?\*\/#mis", '', $sql_data);    // remove comments

		$regex = "/CREATE TABLE (?:IF NOT EXISTS )?`?(\w*)`?\s*?\(([^;]*)\)\s*((?:[\w\s]+=[^;]+)+\s*)*;/i";

		preg_match_all($regex, $sql_data, $match);

		$tables = array();

		foreach($match[1] as $c => $k)
		{
			if(strpos($k, 'e107_') === 0) // remove prefix if found in sql dump.
			{
				$k = (string) substr($k, 5);
			}

			$tables[$c] = $k;
		}

		$ret['tables'] = $tables;

		$data = array();

		if(!empty($match[2])) // clean/trim data.
		{
			foreach($match[2] as $dat)
			{
				$dat = str_replace("\t", '', $dat); // remove tab chars.
				$data[] = trim($dat);
			}
		}

		$ret['data'] = $data;

		$ret['engine'] = array();
		$ret['charset'] = array();

		foreach($match[3] as $rawTableOptions)
		{
			if(empty($rawTableOptions))
			{
				continue;
			}

			$engine = null;
			$charset = null;

			$tableOptionsRegex = "/([\w\s]+=\s?\w+)+?\s*/";
			preg_match_all($tableOptionsRegex, $rawTableOptions, $tableOptionsSplit);
			$tableOptionsSplit = current($tableOptionsSplit);

			foreach($tableOptionsSplit as $rawTableOption)
			{
				list($tableOptionName, $tableOptionValue) = explode("=", $rawTableOption, 2);
				$tableOptionName = strtoupper(trim($tableOptionName));
				$tableOptionValue = trim($tableOptionValue);

				switch($tableOptionName)
				{
					case "ENGINE":
					case "TYPE":
						$engine = $tableOptionValue;
						break;
					case "DEFAULT CHARSET":
					case "DEFAULT CHARACTER SET":
					case "CHARSET":
					case "CHARACTER SET":
						$charset = $tableOptionValue;
						break;
				}
			}

			$ret['engine'][] = str_replace('MYISAM', 'MyISAM', $engine);
			$ret['charset'][] = $charset;
		}

		return $ret;
	}

	/**
	 * Every schema file in the tree, keyed by the identity `db_verify::load()` files it under.
	 *
	 * @return array [sqlFile => absolute path]
	 */
	private function corpus()
	{
		$files = array('core' => e_CORE.'sql/core_sql.php');

		$paths = glob(e_PLUGIN.'*/*_sql.php');
		sort($paths);

		foreach($paths as $path)
		{
			$files[str_replace('_sql', '', basename($path, '.php'))] = $path;
		}

		return $files;
	}

	/**
	 * The legacy parallel arrays folded into one record per table, last declaration winning.
	 *
	 * The legacy empty string for "no engine declared" is read as null.
	 *
	 * @param array $legacy {@see SqlFileCatalogueTest::legacyReferenceParse()} output
	 * @return array [table => ['body'=>string, 'engine'=>string|null, 'charset'=>string|null]]
	 */
	private function expectedFromLegacy(array $legacy)
	{
		$expected = array();

		foreach($legacy['tables'] as $i => $table)
		{
			$expected[$table] = array(
				'body'    => $legacy['data'][$i],
				'engine'  => $this->undeclaredAsNull($legacy['engine'][$i]),
				'charset' => $this->undeclaredAsNull($legacy['charset'][$i]),
			);
		}

		return $expected;
	}

	/**
	 * @param string|null $value
	 * @return string|null
	 */
	private function undeclaredAsNull($value)
	{
		return ($value === null || trim((string) $value) === '') ? null : $value;
	}

	/**
	 * @param string $sql
	 * @return DeclaredTable
	 */
	private function parseOne($sql)
	{
		$tables = $this->catalogue->parse($sql, 'core');

		$this->assertCount(1, $tables, 'Expected exactly one declared table.');

		return array_shift($tables);
	}
}
