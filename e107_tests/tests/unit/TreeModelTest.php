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
