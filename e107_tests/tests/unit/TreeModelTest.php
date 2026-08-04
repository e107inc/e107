<?php

class TreeModelTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $sample_key = 'link_id';
    protected $sample_parent_key = 'link_parent';
    protected $tree;

    protected function _before()
    {
        $class = new \ReflectionClass('e_tree_model');

        $method = $class->getMethod('arrayToTree');
        $method->setAccessible(true);
        $this->tree = $method->invoke(null, $this->sample_rows, $this->sample_key, $this->sample_parent_key);
    }

    protected function _after()
    {
    }

    // tests
    public function testTreeIsAnArray()
    {
        $this->assertTrue(is_array($this->tree));
    }

    public function testTreeRootNodeIsTheOnlyRootNode()
    {
        $this->assertEquals(count($this->tree), 1);
    }

    public function testTreeRootNodeHasCorrectKeyValuePair()
    {
        $key = $this->sample_key;
        $this->assertArrayHasKey($key, $this->tree[0]);
        $this->assertEquals($this->tree[0][$key], 0);
    }

    public function testTreeRootNodeHasChildren()
    {
        $this->assertArrayHasKey('_children', $this->tree[0]);
        $this->assertTrue(is_array($this->tree[0]['_children']));
    }

    public function testTreeParentsAreAssignedCorrectly()
    {
        $key = $this->sample_key;
        $parent_key = $this->sample_parent_key;
        $l0_id     = $this->tree[0][$key];
        $l1_id     = $this->tree[0]['_children'][0][$key];
        $l1_parent = $this->tree[0]['_children'][0][$parent_key];
        $l2_id     = $this->tree[0]['_children'][0]['_children'][0][$key];
        $l2_parent = $this->tree[0]['_children'][0]['_children'][0][$parent_key];

        $this->assertEquals($l0_id, $l1_parent);
        $this->assertEquals($l1_id, $l2_parent);
    }

    public function testTreeValuesAreFlattenedInExpectedOrder()
    {
        $class = new \ReflectionClass('e_tree_model');

        $method = $class->getMethod('flattenTree');
        $method->setAccessible(true);
        $rows = $method->invoke(null, $this->tree, 'link_order', 1);

	$expected = ['General', 'Home', 'Downloads', 'Members', 'Online Users',
	             'Site Stats', 'Submit News', 'Newsfeeds', 'About Us',
	             'Contact Us', 'Nodes', 'Main Website', 'My Deltik',
                     'x10Deltik', 'Deltik Docs', 'Legacy Deltik Products',
                     'Deltik Minecraft Server', 'Register'];

	foreach($expected as $key => $value)
	{
		$this->assertEquals($value, $rows[$key]['link_name']);
	}

	$this->assertEquals(count($expected), count($rows));
    }

    public function testPrepareSimulatedPaginationProcessesCountOnly()
    {
        $tree_model = $this->make('e_tree_model');
        $tree_model->setParam('db_query', 'ORDER BY n.news_sticky DESC, n.news_datestamp DESC LIMIT 4');

        $class = new \ReflectionClass(get_class($tree_model));
        $method = $class->getMethod('prepareSimulatedPagination');
        $method->setAccessible(true);
        $method->invoke($tree_model);

        $this->assertEquals('ORDER BY n.news_sticky DESC, n.news_datestamp DESC', trim($tree_model->getParam('db_query')));
        $this->assertEquals('4', $tree_model->getParam('db_limit_count'));
        $this->assertEmpty($tree_model->getParam('db_limit_offset'));
    }

    public function testPrepareSimulatedPaginationProcessesOffsetAndCount()
    {
        $tree_model = $this->make('e_tree_model');
        $tree_model->setParam('db_query', 'ORDER BY n.news_sticky DESC, n.news_datestamp DESC LIMIT 79,163');

        $class = new \ReflectionClass(get_class($tree_model));
        $method = $class->getMethod('prepareSimulatedPagination');
        $method->setAccessible(true);
        $method->invoke($tree_model);

        $this->assertEquals('ORDER BY n.news_sticky DESC, n.news_datestamp DESC', trim($tree_model->getParam('db_query')));
        $this->assertEquals('163', $tree_model->getParam('db_limit_count'));
        $this->assertEquals('79', $tree_model->getParam('db_limit_offset'));
    }

    public function testMultiFieldCompareWithSortFieldsReturnsExpectedValues()
    {
    	$tree_model = $this->make('e_tree_model');
	$class = new \ReflectionClass(get_class($tree_model));
	$method = $class->getMethod('multiFieldCmp');
	$method->setAccessible(true);

	$row1 = array(
	    'field1' => '0',
	    'field2' => '-1',
	);
	$row2 = array(
	    'field1' => '0',
	    'field2' => '1',
	);
	$sort_fields = ['field1', 'field2'];

	$result = $method->invoke(null, $row1, $row2, $sort_fields, 1);
	$this->assertEquals(-1, $result);

	$row1['field2'] = 1;
	$result = $method->invoke(null, $row1, $row2, $sort_fields, 1);
	$this->assertEquals(0, $result);

	$row1['field2'] = 2;
	$result = $method->invoke(null, $row1, $row2, $sort_fields, 1);
	$this->assertEquals(1, $result);

	$row1['field1'] = -1;
	$result = $method->invoke(null, $row1, $row2, $sort_fields, 1);
	$this->assertEquals(-1, $result);

	$row1['field1'] = 1;
	$result = $method->invoke(null, $row1, $row2, $sort_fields, 1);
	$this->assertEquals(1, $result);
    }

    public function testMultiFieldCompareWithSortFieldReturnsExpectedValues()
    {
    	$tree_model = $this->make('e_tree_model');
	$class = new \ReflectionClass(get_class($tree_model));
	$method = $class->getMethod('multiFieldCmp');
	$method->setAccessible(true);

	$row1 = array(
	    'field1' => '0',
	    'field2' => '-1',
	);
	$row2 = array(
	    'field1' => '0',
	    'field2' => '1',
	);
	$sort_field = 'field1';

	$result = $method->invoke(null, $row1, $row2, $sort_field, 1);
	$this->assertEquals(0, $result);

	$row1['field1'] = -1;
	$result = $method->invoke(null, $row1, $row2, $sort_field, 1);
	$this->assertEquals(-1, $result);

	$row1['field1'] = 1;
	$result = $method->invoke(null, $row1, $row2, $sort_field, 1);
	$this->assertEquals(1, $result);

	$row1['field2'] = 1337;
	$this->assertEquals(1, $result);
    }

	protected function invokeBuildCountQuery($qry)
	{
		$class = new \ReflectionClass('e_tree_model');
		$method = $class->getMethod('buildCountQuery');
		$method->setAccessible(true);
		return $method->invoke(null, $qry);
	}

	public function testBuildCountQueryReducesMultiWildcardProjection()
	{
		// Reproduces the projection that triggers issue #5761: u.* and ue.*
		// both expose user_timezone, which MySQL rejects inside a derived table.
		$qry = "SELECT  u.*,ue.* from #user AS u LEFT JOIN #user_extended AS ue ON u.user_id = ue.user_extended_id ORDER BY u.user_id ASC";
		$count = $this->invokeBuildCountQuery($qry);

		$this->assertStringContainsString('SELECT COUNT(*) AS e_tree_total FROM (', $count);
		$this->assertStringContainsString('AS grouped_rows', $count);
		$this->assertStringNotContainsString('u.*', $count);
		$this->assertStringNotContainsString('ue.*', $count);
		$this->assertStringContainsString('SELECT  1 from #user AS u LEFT JOIN #user_extended AS ue', $count);
		// ORDER BY references a real column, so it is left in place.
		$this->assertStringContainsString('ORDER BY u.user_id ASC', $count);
	}

	public function testBuildCountQueryWrapsScalarSubqueryProjectionVerbatim()
	{
		// Regression guard: the Book/Chapter list has a single wildcard beside a
		// correlated subquery whose own FROM must not be mistaken for the real
		// one. This query has no duplicate column, so it is wrapped untouched.
		$qry = "SELECT a.*, CASE WHEN a.chapter_parent = 0 THEN a.chapter_order ELSE b.chapter_order + ((a.chapter_order)/1000) END AS Sort, (SELECT COUNT(*) FROM `#page` p WHERE p.page_chapter = a.chapter_id) AS chapter_page_count FROM `#page_chapters` AS a LEFT JOIN `#page_chapters` AS b ON a.chapter_parent = b.chapter_id ORDER BY Sort, chapter_order";
		$count = $this->invokeBuildCountQuery($qry);

		$this->assertEquals("SELECT COUNT(*) AS e_tree_total FROM ($qry) AS grouped_rows", $count);
		// The inner subquery FROM was not split out into a broken projection.
		$this->assertStringNotContainsString('SELECT 1 FROM `#page`', $count);
		$this->assertStringContainsString('chapter_page_count', $count);
	}

	public function testBuildCountQueryWrapsSingleWildcardVerbatim()
	{
		// A single wildcard cannot collide, so the projection is left intact.
		$qry = "SELECT n.*, u.user_name FROM #news AS n LEFT JOIN #user AS u ON n.news_author = u.user_id ORDER BY n.news_datestamp DESC";
		$count = $this->invokeBuildCountQuery($qry);

		$this->assertEquals("SELECT COUNT(*) AS e_tree_total FROM ($qry) AS grouped_rows", $count);
	}

	public function testBuildCountQueryFindsTopLevelFromPastSubquery()
	{
		// Two wildcards plus a correlated subquery: the projection must be
		// reduced at the real top-level FROM, never the subquery's FROM.
		$qry = "SELECT u.*, ue.*, (SELECT COUNT(*) FROM #user_extended x WHERE x.user_extended_id = u.user_id) AS c FROM #user AS u LEFT JOIN #user_extended AS ue ON u.user_id = ue.user_extended_id";
		$count = $this->invokeBuildCountQuery($qry);

		// The whole projection collapses to the constant; the real FROM and its
		// joins survive, while the subquery's own FROM clause does not leak out.
		$this->assertStringContainsString('SELECT 1 FROM #user AS u LEFT JOIN #user_extended AS ue ON u.user_id = ue.user_extended_id', $count);
		$this->assertStringNotContainsString('ue.*', $count);
		$this->assertStringNotContainsString('WHERE x.user_extended_id', $count);
	}

	public function testBuildCountQueryPreservesDistinctProjection()
	{
		// DISTINCT makes the projection significant to the row count, so even a
		// multi-wildcard projection must be left untouched.
		$qry = "SELECT DISTINCT u.*, ue.* FROM #user AS u LEFT JOIN #user_extended AS ue ON u.user_id = ue.user_extended_id";
		$count = $this->invokeBuildCountQuery($qry);

		$this->assertEquals("SELECT COUNT(*) AS e_tree_total FROM ($qry) AS grouped_rows", $count);
	}

	/**
	 * End-to-end guard for issue #5761: counting a list query that joins two
	 * tables sharing a column name must not raise "1060 Duplicate column name".
	 */
	public function testCountResultsToleratesDuplicateColumnNames()
	{
		$sql = e107::getDb();

		$sql->gen("DROP TEMPORARY TABLE IF EXISTS tmp_5761_a");
		$sql->gen("DROP TEMPORARY TABLE IF EXISTS tmp_5761_b");
		$sql->gen("CREATE TEMPORARY TABLE tmp_5761_a (id INT PRIMARY KEY, user_timezone VARCHAR(10) NOT NULL DEFAULT '')");
		$sql->gen("CREATE TEMPORARY TABLE tmp_5761_b (ext_id INT PRIMARY KEY, user_timezone VARCHAR(10) NOT NULL DEFAULT '')");
		$sql->gen("INSERT INTO tmp_5761_a (id, user_timezone) VALUES (1,'UTC'),(2,'CET'),(3,'PST')");
		$sql->gen("INSERT INTO tmp_5761_b (ext_id, user_timezone) VALUES (1,'UTC'),(2,'CET')");

		$tree = $this->make('e_tree_model');
		$tree->setModelTable('tmp_5761_a');
		$tree->setParam('db_query', "SELECT a.*, b.* FROM tmp_5761_a AS a LEFT JOIN tmp_5761_b AS b ON a.id = b.ext_id ORDER BY a.id ASC");

		$class = new \ReflectionClass('e_tree_model');
		$method = $class->getMethod('countResults');
		$method->setAccessible(true);
		$total = $method->invoke($tree, $sql);

		$this->assertEquals(0, $sql->getLastErrorNumber(), $sql->getLastErrorText());
		$this->assertEquals(3, $total);

		$sql->gen("DROP TEMPORARY TABLE IF EXISTS tmp_5761_a");
		$sql->gen("DROP TEMPORARY TABLE IF EXISTS tmp_5761_b");
	}

	/**
	 * Regression guard for the Book/Chapter list (PR #5771 review): a list
	 * query with a correlated subquery in its projection must count without a
	 * SQL syntax error. The subquery's own FROM is the part that broke an
	 * earlier fix; the Book/Chapter list's self-join is left out here because
	 * older engines cannot reference a TEMPORARY table twice (error 1137).
	 */
	public function testCountResultsCountsScalarSubqueryListVerbatim()
	{
		$sql = e107::getDb();

		$sql->gen("DROP TEMPORARY TABLE IF EXISTS tmp_5761_chapters");
		$sql->gen("DROP TEMPORARY TABLE IF EXISTS tmp_5761_pages");
		$sql->gen("CREATE TEMPORARY TABLE tmp_5761_chapters (chapter_id INT PRIMARY KEY, chapter_parent INT NOT NULL DEFAULT 0, chapter_order INT NOT NULL DEFAULT 0)");
		$sql->gen("CREATE TEMPORARY TABLE tmp_5761_pages (page_id INT PRIMARY KEY, page_chapter INT NOT NULL DEFAULT 0)");
		$sql->gen("INSERT INTO tmp_5761_chapters (chapter_id, chapter_parent, chapter_order) VALUES (1,0,1),(2,1,1),(3,1,2),(4,0,2)");
		$sql->gen("INSERT INTO tmp_5761_pages (page_id, page_chapter) VALUES (1,2),(2,2),(3,3)");

		$tree = $this->make('e_tree_model');
		$tree->setModelTable('tmp_5761_chapters');
		$tree->setParam('db_query', "SELECT a.*, CASE WHEN a.chapter_parent = 0 THEN 0 ELSE 1 END AS is_child, (SELECT COUNT(*) FROM tmp_5761_pages p WHERE p.page_chapter = a.chapter_id) AS chapter_page_count FROM tmp_5761_chapters AS a ORDER BY a.chapter_order");

		$class = new \ReflectionClass('e_tree_model');
		$method = $class->getMethod('countResults');
		$method->setAccessible(true);
		$total = $method->invoke($tree, $sql);

		$this->assertEquals(0, $sql->getLastErrorNumber(), $sql->getLastErrorText());
		$this->assertEquals(4, $total);

		$sql->gen("DROP TEMPORARY TABLE IF EXISTS tmp_5761_chapters");
		$sql->gen("DROP TEMPORARY TABLE IF EXISTS tmp_5761_pages");
	}

	protected $sample_rows =
		array(
			0 =>
				array (
					'link_id' => '1',
					'link_name' => 'General',
					'link_url' => '/index.php',
					'link_description' => '',
					'link_button' => '{e_IMAGE}icons/icon2.png',
					'link_category' => '1',
					'link_order' => '1',
					'link_parent' => '0',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			1 =>
				array (
					'link_id' => '99',
					'link_name' => 'Nodes',
					'link_url' => '',
					'link_description' => '',
					'link_button' => 'icon14.png',
					'link_category' => '1',
					'link_order' => '2',
					'link_parent' => '0',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			2 =>
				array (
					'link_id' => '8',
					'link_name' => 'Register',
					'link_url' => '/signup.php',
					'link_description' => '',
					'link_button' => '{e_IMAGE}icons/deltik-favicon.png',
					'link_category' => '1',
					'link_order' => '3',
					'link_parent' => '0',
					'link_open' => '0',
					'link_class' => '252',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			3 =>
				array (
					'link_id' => '6',
					'link_name' => 'Home',
					'link_url' => '/index.php',
					'link_description' => '',
					'link_button' => '{e_IMAGE}icons/icon18.png',
					'link_category' => '1',
					'link_order' => '1',
					'link_parent' => '1',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			4 =>
				array (
					'link_id' => '18',
					'link_name' => 'About Us',
					'link_url' => '/page.php?4',
					'link_description' => '',
					'link_button' => '{e_IMAGE}icons/deltik-favicon.png',
					'link_category' => '1',
					'link_order' => '8',
					'link_parent' => '1',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			5 =>
				array (
					'link_id' => '17',
					'link_name' => 'Newsfeeds',
					'link_url' => '/{e_PLUGIN}newsfeed/newsfeed.php',
					'link_description' => '',
					'link_button' => '{e_IMAGE}icons/html.png',
					'link_category' => '1',
					'link_order' => '7',
					'link_parent' => '1',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			6 =>
				array (
					'link_id' => '4',
					'link_name' => 'Submit News',
					'link_url' => '/submitnews.php',
					'link_description' => '',
					'link_button' => '{e_IMAGE}icons/icon26.png',
					'link_category' => '1',
					'link_order' => '6',
					'link_parent' => '1',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			7 =>
				array (
					'link_id' => '16',
					'link_name' => 'Site Stats',
					'link_url' => '/{e_PLUGIN}log/stats.php?1',
					'link_description' => '',
					'link_button' => '{e_IMAGE}icons/icon11.png',
					'link_category' => '1',
					'link_order' => '5',
					'link_parent' => '1',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			8 =>
				array (
					'link_id' => '7',
					'link_name' => 'Online Users',
					'link_url' => '/online.php',
					'link_description' => '',
					'link_button' => '{e_IMAGE}icons/icon22.png',
					'link_category' => '1',
					'link_order' => '4',
					'link_parent' => '1',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			9 =>
				array (
					'link_id' => '2',
					'link_name' => 'Downloads',
					'link_url' => '/download.php',
					'link_description' => '',
					'link_button' => '{e_IMAGE}icons/download_32.png',
					'link_category' => '1',
					'link_order' => '2',
					'link_parent' => '1',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			10 =>
				array (
					'link_id' => '3',
					'link_name' => 'Members',
					'link_url' => '/user.php',
					'link_description' => '',
					'link_button' => '{e_IMAGE}icons/icon20.png',
					'link_category' => '1',
					'link_order' => '3',
					'link_parent' => '1',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			11 =>
				array (
					'link_id' => '5',
					'link_name' => 'Contact Us',
					'link_url' => '/contact.php',
					'link_description' => '',
					'link_button' => '{e_IMAGE}icons/icon19.png',
					'link_category' => '1',
					'link_order' => '9',
					'link_parent' => '1',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			12 =>
				array (
					'link_id' => '12',
					'link_name' => 'x10Deltik',
					'link_url' => 'https://x10.deltik.org/',
					'link_description' => 'Deltik Additional Resources Website',
					'link_button' => 'deltik_x10-favicon.png',
					'link_category' => '1',
					'link_order' => '3',
					'link_parent' => '99',
					'link_open' => '0',
					'link_class' => '255',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			13 =>
				array (
					'link_id' => '15',
					'link_name' => 'Deltik Docs',
					'link_url' => 'https://man.deltik.org/',
					'link_description' => 'Manual Pages of Deltik',
					'link_button' => 'deltik_docs-favicon.png',
					'link_category' => '1',
					'link_order' => '4',
					'link_parent' => '99',
					'link_open' => '0',
					'link_class' => '255',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			14 =>
				array (
					'link_id' => '14',
					'link_name' => 'Legacy Deltik Products',
					'link_url' => 'https://products.deltik.org/',
					'link_description' => 'Legacy Deltik Products',
					'link_button' => 'deltik_products-favicon.png',
					'link_category' => '1',
					'link_order' => '5',
					'link_parent' => '99',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			15 =>
				array (
					'link_id' => '11',
					'link_name' => 'My Deltik',
					'link_url' => 'https://my.deltik.org/',
					'link_description' => 'User Control Panel for all of Deltik&#039;s Features',
					'link_button' => 'deltik_my-favicon.png',
					'link_category' => '1',
					'link_order' => '2',
					'link_parent' => '99',
					'link_open' => '0',
					'link_class' => '255',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			16 =>
				array (
					'link_id' => '13',
					'link_name' => 'Deltik Minecraft Server',
					'link_url' => 'https://mc.deltik.org/',
					'link_description' => 'Deltik Minecraft Server',
					'link_button' => 'deltik_mc-favicon.png',
					'link_category' => '1',
					'link_order' => '6',
					'link_parent' => '99',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
			17 =>
				array (
					'link_id' => '10',
					'link_name' => 'Main Website',
					'link_url' => 'https://www.deltik.org/',
					'link_description' => 'The Official Deltik Website',
					'link_button' => 'deltik-favicon.png',
					'link_category' => '1',
					'link_order' => '1',
					'link_parent' => '99',
					'link_open' => '0',
					'link_class' => '0',
					'link_function' => '',
					'link_sefurl' => '',
					'link_owner' => '',
				),
		);
}
