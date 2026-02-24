<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */


class e_search_fulltext_indexerTest extends \Codeception\Test\Unit
{

	/** @var e_search_fulltext_indexer */
	private $indexer;

	protected function _before()
	{

		require_once(e_HANDLER . "e_search_fulltext_indexer_class.php");
		try
		{
			$this->indexer = new e_search_fulltext_indexer();
		}
		catch(Exception $e)
		{
			self::fail("Couldn't load e_search_fulltext_indexer object");
		}
	}

	/**
	 * Test parsing a simple table name with no alias
	 */
	public function testParseTableAliasesSimpleTable()
	{

		$tableClause = 'user';
		$expected = array('user' => 'user');

		$actual = $this->indexer->parseTableAliases($tableClause);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test parsing a table with prefix marker
	 */
	public function testParseTableAliasesWithPrefix()
	{

		$tableClause = '#user';
		$expected = array('user' => 'user');

		$actual = $this->indexer->parseTableAliases($tableClause);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test parsing a table with AS alias
	 */
	public function testParseTableAliasesWithAsAlias()
	{

		$tableClause = 'news AS n';
		$expected = array('n' => 'news');

		$actual = $this->indexer->parseTableAliases($tableClause);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test parsing a table with implicit alias (no AS keyword)
	 */
	public function testParseTableAliasesWithImplicitAlias()
	{

		$tableClause = 'news n';
		$expected = array('n' => 'news');

		$actual = $this->indexer->parseTableAliases($tableClause);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test parsing complex JOIN clause
	 */
	public function testParseTableAliasesComplexJoin()
	{

		$tableClause = 'forum_thread AS t LEFT JOIN #user AS u ON t.thread_user = u.user_id';
		$expected = array(
			't' => 'forum_thread',
			'u' => 'user',
		);

		$actual = $this->indexer->parseTableAliases($tableClause);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test parsing multiple JOINs
	 */
	public function testParseTableAliasesMultipleJoins()
	{

		$tableClause = 'news AS n LEFT JOIN #news_category AS c ON n.news_category = c.category_id LEFT JOIN #user AS u ON n.news_author = u.user_id';
		$expected = array(
			'n' => 'news',
			'c' => 'news_category',
			'u' => 'user',
		);

		$actual = $this->indexer->parseTableAliases($tableClause);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test parsing multi-line table definition
	 */
	public function testParseTableAliasesMultiLine()
	{

		$tableClause = "news AS n\n\t\tLEFT JOIN #news_category AS c ON n.news_category = c.category_id";
		$expected = array(
			'n' => 'news',
			'c' => 'news_category',
		);

		$actual = $this->indexer->parseTableAliases($tableClause);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test mapping search fields with aliased columns
	 */
	public function testMapSearchFieldsWithAliases()
	{

		$searchFields = array(
			'n.news_title' => '1.2',
			'n.news_body'  => '0.6',
		);
		$tableAliases = array('n' => 'news');
		$expected = array(
			'news' => array('news_title', 'news_body'),
		);

		$actual = $this->indexer->mapSearchFields($searchFields, $tableAliases);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test mapping search fields to multiple tables
	 */
	public function testMapSearchFieldsMultipleTables()
	{

		$searchFields = array(
			't.thread_name' => '1.0',
			'p.post_entry'  => '0.8',
		);
		$tableAliases = array(
			't' => 'forum_thread',
			'p' => 'forum_post',
		);
		$expected = array(
			'forum_thread' => array('thread_name'),
			'forum_post'   => array('post_entry'),
		);

		$actual = $this->indexer->mapSearchFields($searchFields, $tableAliases);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test mapping search fields without alias (uses primary table)
	 */
	public function testMapSearchFieldsNoAlias()
	{

		$searchFields = array(
			'page_title' => '1.0',
			'page_text'  => '0.8',
		);
		$tableAliases = array('page' => 'page');
		$expected = array(
			'page' => array('page_title', 'page_text'),
		);

		$actual = $this->indexer->mapSearchFields($searchFields, $tableAliases);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test mapping search fields with unknown alias (should be skipped)
	 */
	public function testMapSearchFieldsUnknownAlias()
	{

		$searchFields = array(
			'x.unknown_field' => '1.0',
			'n.news_title'    => '1.0',
		);
		$tableAliases = array('n' => 'news');
		$expected = array(
			'news' => array('news_title'),
		);

		$actual = $this->indexer->mapSearchFields($searchFields, $tableAliases);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test that duplicate columns are not added
	 */
	public function testMapSearchFieldsNoDuplicates()
	{

		$searchFields = array(
			'n.news_title'    => '1.0',
			'n.news_title'    => '0.8', // Same field, different weight
		);
		$tableAliases = array('n' => 'news');

		$actual = $this->indexer->mapSearchFields($searchFields, $tableAliases);
		self::assertCount(1, $actual['news']);
	}

	/**
	 * Test generating FULLTEXT index definition
	 */
	public function testGenerateIndexDefinition()
	{

		$tableName = 'news';
		$columnName = 'news_title';

		$expected = array(
			'type'    => 'FULLTEXT',
			'keyname' => 'news_title',          // column name (goes in parentheses)
			'field'   => 'ft_news_news_title',  // index name (goes in backticks)
		);

		$actual = $this->indexer->generateIndexDefinition($tableName, $columnName);
		self::assertEquals($expected, $actual);
	}

	/**
	 * Test that index naming follows convention
	 */
	public function testGenerateIndexDefinitionNamingConvention()
	{

		$actual = $this->indexer->generateIndexDefinition('forum_post', 'post_entry');

		// Index name should follow ft_{table}_{column} convention
		self::assertEquals('ft_forum_post_post_entry', $actual['field']);
		self::assertEquals('FULLTEXT', $actual['type']);
		self::assertEquals('post_entry', $actual['keyname']);
	}

	/**
	 * Test clearing cache
	 */
	public function testClearCache()
	{

		// Access private properties via reflection to verify cache clearing
		$reflection = new ReflectionClass($this->indexer);

		$searchConfigsProp = $reflection->getProperty('searchConfigs');
		$searchConfigsProp->setAccessible(true);
		$searchConfigsProp->setValue($this->indexer, array('test' => 'data'));

		$derivedIndexesProp = $reflection->getProperty('derivedIndexes');
		$derivedIndexesProp->setAccessible(true);
		$derivedIndexesProp->setValue($this->indexer, array('test' => 'indexes'));

		// Clear cache
		$this->indexer->clearCache();

		// Verify cleared
		self::assertEmpty($searchConfigsProp->getValue($this->indexer));
		self::assertEmpty($derivedIndexesProp->getValue($this->indexer));
	}

	/**
	 * Test getIndexesForTable returns empty array for unknown table
	 */
	public function testGetIndexesForTableUnknownTable()
	{

		// Use reflection to set empty derived indexes
		$reflection = new ReflectionClass($this->indexer);
		$derivedIndexesProp = $reflection->getProperty('derivedIndexes');
		$derivedIndexesProp->setAccessible(true);
		$derivedIndexesProp->setValue($this->indexer, array('news' => array('test' => 'index')));

		$actual = $this->indexer->getIndexesForTable('nonexistent_table');
		self::assertEquals(array(), $actual);
	}

	/**
	 * Test parsing various JOIN types
	 */
	public function testParseTableAliasesVariousJoinTypes()
	{

		// LEFT JOIN
		$tableClause = 'news n LEFT JOIN user u ON n.author = u.user_id';
		$actual = $this->indexer->parseTableAliases($tableClause);
		self::assertArrayHasKey('n', $actual);
		self::assertArrayHasKey('u', $actual);

		// INNER JOIN
		$tableClause = 'news n INNER JOIN user u ON n.author = u.user_id';
		$actual = $this->indexer->parseTableAliases($tableClause);
		self::assertArrayHasKey('n', $actual);
		self::assertArrayHasKey('u', $actual);

		// RIGHT JOIN
		$tableClause = 'news n RIGHT JOIN user u ON n.author = u.user_id';
		$actual = $this->indexer->parseTableAliases($tableClause);
		self::assertArrayHasKey('n', $actual);
		self::assertArrayHasKey('u', $actual);
	}

	/**
	 * Test that SQL keywords are not mistaken for aliases
	 */
	public function testParseTableAliasesSqlKeywordsNotTreatedAsAliases()
	{

		// This clause has 'ON' after the table name - should not be treated as alias
		$tableClause = 'news ON';
		$actual = $this->indexer->parseTableAliases($tableClause);

		// 'ON' should not be an alias - the table should use itself as alias
		self::assertEquals(array('news' => 'news'), $actual);
	}

	/**
	 * Integration test: Full workflow from table clause to index definitions
	 */
	public function testFullWorkflow()
	{

		$tableClause = 'news AS n LEFT JOIN #user AS u ON n.news_author = u.user_id';
		$searchFields = array(
			'n.news_title'   => '1.2',
			'n.news_body'    => '0.6',
			'n.news_summary' => '0.8',
		);

		// Step 1: Parse table aliases
		$tableAliases = $this->indexer->parseTableAliases($tableClause);
		self::assertEquals('news', $tableAliases['n']);
		self::assertEquals('user', $tableAliases['u']);

		// Step 2: Map search fields
		$fieldMapping = $this->indexer->mapSearchFields($searchFields, $tableAliases);
		self::assertArrayHasKey('news', $fieldMapping);
		self::assertCount(3, $fieldMapping['news']);
		self::assertContains('news_title', $fieldMapping['news']);
		self::assertContains('news_body', $fieldMapping['news']);
		self::assertContains('news_summary', $fieldMapping['news']);

		// Step 3: Generate index definitions
		foreach($fieldMapping['news'] as $column)
		{
			$indexDef = $this->indexer->generateIndexDefinition('news', $column);
			self::assertEquals('FULLTEXT', $indexDef['type']);
			self::assertStringStartsWith('ft_news_', $indexDef['field']);
			self::assertEquals($column, $indexDef['keyname']);
		}
	}

}