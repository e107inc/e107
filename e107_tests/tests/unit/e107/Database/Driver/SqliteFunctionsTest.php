<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Driver;

use e107\Shims\PdoSqlite;
use PDO;

/**
 * The SQL functions the SQLite driver registers, called the way SQLite calls them.
 */
class SqliteFunctionsTest extends \Test\Unit
{
	/** @var PDO */
	private $pdo;

	protected function _before()
	{
		require_once(e_HANDLER.'Database/Driver/SqliteFunctions.php');

		if(!extension_loaded('pdo_sqlite'))
		{
			$this->markTestSkipped('pdo_sqlite is not loaded');
		}

		$this->pdo = $this->open(true);
	}

	public function testRegexpBacksTheOperatorCaseInsensitivelyAndPropagatesNull()
	{
		$this->assertSame('1', $this->value("SELECT 'Hello' REGEXP '^h'"));
		$this->assertSame('1', $this->value("SELECT '2,5,253' REGEXP '(^|,)(5|7)(,|$)'"));
		$this->assertSame('0', $this->value("SELECT '25' REGEXP '(^|,)(5)(,|$)'"));
		$this->assertSame('1', $this->value("SELECT 'a1' REGEXP '[[:alnum:]]+'"));
		$this->assertNull($this->value("SELECT NULL REGEXP 'a'"));
	}

	public function testAHostilePatternFailsFastInsteadOfStalling()
	{
		$start = microtime(true);

		try
		{
			$this->value("SELECT '".str_repeat('a', 5000)."!' REGEXP '(a+)+$'");
			$this->fail('a pattern that exhausts the backtracking budget answered');
		}
		catch(\PDOException $e)
		{
			$this->assertLessThan(2.0, microtime(true) - $start);
		}
	}

	public function testAPatternPcreCannotRunFailsTheStatementRatherThanMatchingNothing()
	{
		$this->pdo->exec('CREATE TABLE r (v TEXT)');
		$this->pdo->exec("INSERT INTO r VALUES ('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxz'), ('abc')");

		foreach(array("'(x+x+)+y|z'", "'('", "'a'||char(1)") as $pattern)
		{
			try
			{
				$this->value('SELECT COUNT(*) FROM r WHERE v NOT REGEXP '.$pattern);
				$this->fail('NOT REGEXP '.$pattern.' let rows through');
			}
			catch(\PDOException $e)
			{
				$this->assertStringContainsString('REGEXP', $e->getMessage());
			}
		}
	}

	public function testACaseSensitiveLikeMatchesTheWholeSubjectOnly()
	{
		$this->assertSame('1', $this->value("SELECT e107_like_binary('abc', 'abc')"));
		$this->assertSame('0', $this->value("SELECT e107_like_binary('abc' || char(10), 'abc')"));
		$this->assertSame('0', $this->value("SELECT e107_like_binary('ABC', 'abc')"));
		$this->assertSame('1', $this->value("SELECT e107_like_binary('a' || char(10) || 'c', 'a_c')"));
	}

	public function testACaseSensitiveLikePcreCannotRunFailsTheStatement()
	{
		$this->expectException('PDOException');
		$this->value("SELECT e107_like_binary('b".str_repeat('a', 60)."', '".str_repeat('%a', 18)."%b')");
	}

	public function testFullTextRelevanceFollowsBooleanModeRules()
	{
		$text = "'The <b>quick</b> brown fox jumps over the lazy dog'";

		$this->assertGreaterThan(0, (float) $this->value("SELECT e107_match_boolean('quick', $text)"));
		$this->assertSame('0', $this->value("SELECT e107_match_boolean('+quick -lazy', $text)"));
		$this->assertGreaterThan(0, (float) $this->value("SELECT e107_match_boolean('+quick fox', $text)"));
		$this->assertSame('0', $this->value("SELECT e107_match_boolean('+cat fox', $text)"));
		$this->assertGreaterThan(0, (float) $this->value("SELECT e107_match_boolean('jum*', $text)"));
		$this->assertSame('0', $this->value("SELECT e107_match_boolean('jum', $text)"));
		$this->assertGreaterThan(0, (float) $this->value("SELECT e107_match_boolean('\"brown fox\"', $text)"));
		$this->assertSame('0', $this->value("SELECT e107_match_boolean('\"fox brown\"', $text)"));
		$this->assertSame('0', $this->value("SELECT e107_match_boolean('-cat', $text)"), 'only excluded words match nothing');
		$this->assertGreaterThan(0, (float) $this->value("SELECT e107_match_boolean('dog', 'a', NULL, $text)"), 'every column is searched');
		$this->assertGreaterThan(0, (float) $this->value("SELECT e107_match_boolean('+quick#cat', $text)"), 'a term is cut into words; the operator binds the first');
		$this->assertGreaterThan(0, (float) $this->value("SELECT e107_match_boolean('\"brown+fox\"', $text)"), 'a phrase is its words, whatever lies between');
	}

	public function testFullTextScoresEachQueryAndRowOnItsOwnHoweverOftenTheyRepeat()
	{
		$this->pdo->exec('CREATE TABLE ft (id INTEGER, a TEXT, b TEXT)');

		for($i = 1; $i <= 40; $i++)
		{
			$this->pdo->exec("INSERT INTO ft VALUES ($i, '".str_repeat('fox ', $i % 3)."dog', '".str_repeat('cat ', $i % 5)."')");
		}

		$statement = $this->pdo->prepare('SELECT id, e107_match_boolean(:q1, a) AS fa, e107_match_boolean(:q2, b) AS fb,'
			.' e107_match_boolean(:q3, a, b) AS fab, e107_match(:q4, b) AS nb FROM ft WHERE e107_match_boolean(:q5, a) > 0 ORDER BY id');
		$statement->execute(array('q1' => 'fox dog', 'q2' => 'cat', 'q3' => 'fox+cat', 'q4' => 'cat fox', 'q5' => '+dog'));

		foreach($statement->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			$foxes = $row['id'] % 3;
			$cats = $row['id'] % 5;

			$this->assertEquals($foxes + 1, $row['fa'], 'row '.$row['id']);
			$this->assertEquals($cats, $row['fb'], 'row '.$row['id']);
			$this->assertEquals($foxes + $cats, $row['fab'], 'row '.$row['id']);
			$this->assertEquals($cats, $row['nb'], 'row '.$row['id']);
		}
	}

	public function testFullTextRelevanceInNaturalLanguageModeCountsEveryWord()
	{
		$text = "'The <b>quick</b> brown fox jumps over the lazy dog'";

		$this->assertGreaterThan(0, (float) $this->value("SELECT e107_match('cat fox', $text)"), 'any word matches');
		$this->assertGreaterThan((float) $this->value("SELECT e107_match('fox', $text)"), (float) $this->value("SELECT e107_match('fox dog', $text)"), 'each word adds relevance');
		$this->assertGreaterThan(0, (float) $this->value("SELECT e107_match('+cat -fox', $text)"), 'operators are only punctuation');
		$this->assertSame('0', $this->value("SELECT e107_match('jum', $text)"));
		$this->assertSame('0', $this->value("SELECT e107_match('cat', 'a', NULL, $text)"));
	}

	public function testJsonContainmentFollowsMysqlForNestedArraysAndObjects()
	{
		$this->assertSame('0', $this->value("SELECT e107_json_contains('[1,2]', '[[1,2]]')"), 'an array element needs an array to sit in');
		$this->assertSame('1', $this->value("SELECT e107_json_contains('[[1,2],3]', '[[1]]')"));
		$this->assertSame('0', $this->value("SELECT e107_json_contains('[[1]]', '1')"), 'a scalar is looked for among the elements, not deeper');
		$this->assertSame('0', $this->value("SELECT e107_json_contains('[1]', '{\"0\":1}')"), 'an object is not an array');
		$this->assertSame('1', $this->value("SELECT e107_json_contains('[{\"a\":1,\"b\":2}]', '{\"a\":1}')"));
		$this->assertSame('1', $this->value("SELECT e107_json_contains('1.0', '1')"), 'integers and decimals compare');
		$this->assertSame('0', $this->value("SELECT e107_json_contains('\"1\"', '1')"), 'a string is not a number');
		$this->assertSame('1', $this->value("SELECT e107_json_contains('null', 'null')"));
	}

	public function testInvalidJsonAndUnsupportedPathsFailTheStatement()
	{
		foreach(array(
			"e107_json_contains('not json', 'also not')",
			"e107_json_contains('garbage', 'null')",
			"e107_json_contains('', '1')",
			"e107_json_length('{')",
			"e107_json_contains_path('nope', '$.a')",
			"e107_json_contains_path('{\"a\":[1]}', '$.a[*]')",
			"e107_json_contains_path('{\"a\":1}', 'a')",
		) as $call)
		{
			try
			{
				$this->value('SELECT '.$call);
				$this->fail($call.' answered');
			}
			catch(\PDOException $e)
			{
				$this->assertStringContainsString('JSON', $e->getMessage(), $call);
			}
		}
	}

	public function testJsonPathsReachQuotedAndEmptyMemberNames()
	{
		$this->requireSqliteLibrary();

		$this->assertSame('1', $this->value("SELECT e107_json_contains_path('{\"\":1}', '$.\"\"')"));
		$this->assertSame('1', $this->value("SELECT e107_json_contains_path('{\"a b\":{\"c\":[0,1]}}', '$.\"a b\".c[1]')"));
		$this->assertSame('0', $this->value("SELECT e107_json_contains_path('{\"a b\":{\"c\":[0,1]}}', '$.\"a b\".c[2]')"));
		$this->assertSame('1', $this->value("SELECT e107_json_contains_path('{\"a\":1}', '$[0]')"), 'a non-array is its own element 0');
		$this->assertSame('0', $this->value("SELECT e107_json_contains_path('[1]', '$.\"0\"')"), 'an array has no members');
	}

	public function testJsonTestsFollowMysql()
	{
		$this->assertSame('1', $this->value("SELECT e107_json_contains('[1,2,3]', '2')"));
		$this->assertSame('1', $this->value("SELECT e107_json_contains('[1,2,3]', '[3,1]')"));
		$this->assertSame('0', $this->value("SELECT e107_json_contains('[1,2,3]', '4')"));
		$this->assertSame('1', $this->value("SELECT e107_json_contains('{\"a\":1,\"b\":[1,2]}', '{\"b\":[2]}')"));
		$this->assertSame('1', $this->value("SELECT e107_json_contains_path('{\"a\":{\"b\":1}}', '$.a.b')"));
		$this->assertSame('0', $this->value("SELECT e107_json_contains_path('{\"a\":{\"b\":1}}', '$.a.c')"));
		$this->assertSame('1', $this->value("SELECT e107_json_contains_path('[10,20]', '$[1]')"));
		$this->assertSame('3', $this->value("SELECT e107_json_length('[1,2,3]')"));
		$this->assertSame('2', $this->value("SELECT e107_json_length('{\"a\":1,\"b\":2}')"));
	}

	public function testDistinctGroupConcatWithASeparator()
	{
		$this->pdo->exec("CREATE TABLE g (v TEXT)");
		$this->pdo->exec("INSERT INTO g VALUES ('a'), ('b'), ('a'), (NULL), ('c')");

		$this->assertSame('a;b;c', $this->value("SELECT e107_group_concat_distinct(v, ';') FROM g"));
		$this->assertNull($this->value("SELECT e107_group_concat_distinct(v, ';') FROM g WHERE 0"));
	}

	public function testFindInSetAnswersAsMysqlDoes()
	{
		$this->assertSame('0', $this->value("SELECT find_in_set('a,b', 'a,b,c')"), 'a needle with a comma is never found');
		$this->assertSame('0', $this->value("SELECT find_in_set('', '')"), 'nothing is found in an empty set');
		$this->assertSame('2', $this->value("SELECT find_in_set('', 'a,,b')"));
		$this->assertSame('3', $this->value("SELECT find_in_set('253', '1,2,253')"));
		$this->assertSame('1', $this->value("SELECT find_in_set('ÉTÉ', 'été,x')"));
		$this->assertNull($this->value("SELECT find_in_set(NULL, 'a')"));
	}

	public function testConcatIsNullWhenAnyArgumentIsWhateverTheSqliteVersion()
	{
		$this->assertNull($this->value("SELECT concat('a', NULL)"));
		$this->assertSame('ab1', $this->value("SELECT concat('a', 'b', 1)"));
	}

	public function testLocateAnswersZeroOutsideTheString()
	{
		$this->assertSame('0', $this->value("SELECT locate('a', 'abc', 10)"));
		$this->assertSame('0', $this->value("SELECT locate('a', 'abc', 0)"));
		$this->assertSame('0', $this->value("SELECT locate('a', 'abc', -1)"));
		$this->assertSame('0', $this->value("SELECT locate('a', 'abca', 5)"));
		$this->assertSame('4', $this->value("SELECT locate('a', 'abca', 2)"));
		$this->assertSame('1', $this->value("SELECT locate('', 'abc')"));
		$this->assertSame('4', $this->value("SELECT locate('', 'abc', 4)"));
		$this->assertSame('0', $this->value("SELECT locate('', 'abc', 5)"));
	}

	public function testFromUnixtimeFormatsAsDateFormatDoes()
	{
		$this->assertSame('001', $this->value("SELECT from_unixtime(".mktime(12, 0, 0, 1, 1, 2024).", '%j')"));
		$this->assertSame('366', $this->value("SELECT from_unixtime(".mktime(12, 0, 0, 12, 31, 2024).", '%j')"));
		$this->assertSame('2nd 1 01:02:03 AM 100%', $this->value("SELECT from_unixtime(".mktime(1, 2, 3, 3, 2, 2026).", '%D %w %r 100%%')"));

		$this->expectException('PDOException');
		$this->value("SELECT from_unixtime(0, '%U')");
	}

	public function testGreatestAndLeastCompareAsMysqlDoes()
	{
		$this->assertSame('9', $this->value("SELECT greatest('10', '9')"), 'strings compare as strings');
		$this->assertSame('10', $this->value("SELECT least('10', '9')"));
		$this->assertSame('10', $this->value('SELECT greatest(10, 9)'), 'integers compare as integers');
		$this->assertSame('10', $this->value("SELECT greatest(2.5, '10')"), 'a REAL makes it numeric');
		$this->assertSame('B', $this->value("SELECT greatest('a', 'B')"), 'letter case is ignored');
		$this->assertNull($this->value("SELECT least(1, NULL)"));
	}

	public function testPaddingPastTheMaximumPacketIsNullNotAFatalError()
	{
		$this->assertTrue($this->value("SELECT lpad('x', 300000000, 'y')") === null, 'a 300 MB result is NULL');
		$this->assertNull($this->value("SELECT rpad('x', -1, 'y')"));
		$this->assertSame('yyx', $this->value("SELECT lpad('x', 3, 'y')"));
		$this->assertSame('xé', $this->value("SELECT rpad('x', 2, 'éa')"));
		$this->assertSame('ab', $this->value("SELECT lpad('abc', 2, 'y')"));
	}

	public function testTheMysqlCompatibilityPack()
	{
		$this->requireSqliteLibrary();

		$this->assertSame('2', $this->value("SELECT find_in_set('b', 'a,B,c')"));
		$this->assertSame('0', $this->value("SELECT find_in_set('x', 'a,b')"));
		$this->assertSame('www.mysql', $this->value("SELECT substring_index('www.mysql.com', '.', 2)"));
		$this->assertSame('mysql.com', $this->value("SELECT substring_index('www.mysql.com', '.', -2)"));
		$this->assertSame('7', $this->value('SELECT greatest(3, 7, 5)'));
		$this->assertNull($this->value('SELECT greatest(3, NULL)'));
		$this->assertSame('ab', $this->value("SELECT left('abc', 2)"));
		$this->assertSame('bc', $this->value("SELECT right('abc', 2)"));
		$this->assertSame('007', $this->value("SELECT lpad('7', 3, '0')"));
		$this->assertSame('3', $this->value("SELECT locate('c', 'abcd')"));
		$this->assertSame(date('Y-m-d', 0), $this->value("SELECT from_unixtime(0, '%Y-%m-%d')"), "in PHP's time zone, as MySQL reads the session's");
		$this->assertSame('b', $this->value("SELECT if(1 = 0, 'a', 'b')"));
		$this->assertSame(md5('x'), $this->value("SELECT md5('x')"));
	}

	public function testTheCompatibilityPackCanBeLeftOut()
	{
		$pdo = $this->open(false);

		$this->assertSame(1, (int) $pdo->query("SELECT 'a' REGEXP 'a'")->fetchColumn(), 'the core pack is always there');
		$this->assertSame(1, (int) $pdo->query("SELECT find_in_set('a', 'a')")->fetchColumn(), 'the platform writes find_in_set()');

		$this->expectException('PDOException');
		$pdo->query("SELECT substring_index('a.b', '.', 1)");
	}

	/**
	 * @param bool $mysqlCompat
	 * @return PDO an in-memory database with the packs registered
	 */
	private function open($mysqlCompat)
	{
		require_once(e_HANDLER.'Shims/PdoSqlite.php');

		$pdo = PdoSqlite::connect('sqlite::memory:', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES => true));
		SqliteFunctions::register($pdo, (string) $pdo->query('SELECT sqlite_version()')->fetchColumn(), $mysqlCompat);

		return $pdo;
	}

	/**
	 * @param string $sql
	 * @return string|null
	 */
	private function value($sql)
	{
		$value = $this->pdo->query($sql)->fetchColumn();

		return ($value === null) ? null : (string) $value;
	}
}
