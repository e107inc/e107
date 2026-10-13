<?php


class page_sitelinkTest extends \Test\Unit
{

	/** @var page_sitelink */
	protected $nav;

	/** @var callable puts the URL configuration back */
	private $restoreUrlConfig;

	protected function _before()
	{

		// Enable SEF Urls. book/chapter/page
		$this->restoreUrlConfig = $this->withUrlConfig(array('page' => 'core/sef_chapters'));

		require_once(e_PLUGIN . "page/e_sitelink.php");
		//	e107::getConfig()->set
		try
		{
			$this->nav = $this->make('page_sitelink');
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$this->nav->__construct();

	}

	protected function _after()
	{
		call_user_func($this->restoreUrlConfig);
	}

	public function testBookNav()
	{

		$result = $this->nav->bookNav();
		$expected = array(
			0 =>
				array(
					'link_id'          => '1',
					'link_name'        => 'General',
					'link_url'         => '/page/general',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '0',
					'link_parent'      => '0',
					'link_open'        => '',
					'link_class'       => 0,
					'link_sub'         => array(),
					'link_active'      => false,
					'link_identifier'  => 'page-nav-1',
				),
		);

		$this->assertSame($expected, $result);

	}


	public function testBookNavChapters()
	{

		$result = $this->nav->bookNavChapters();
		$expected = array(
			0 =>
				array(
					'link_id'          => '1',
					'link_name'        => 'General',
					'link_url'         => '/page/general',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '0',
					'link_parent'      => '0',
					'link_open'        => '',
					'link_class'       => 0,
					'link_sub'         =>
						array(
							0 =>
								array(
									'link_id'          => '2',
									'link_name'        => 'Chapter 1',
									'link_url'         => '/page/general/chapter-1',
									'link_description' => '',
									'link_button'      => '',
									'link_category'    => '',
									'link_order'       => '1',
									'link_parent'      => '1',
									'link_open'        => '',
									'link_class'       => 0,
									'link_sub'         =>
										array(),
									'link_active'      => false,
									'link_identifier'  => 'page-nav-2',
								),
							1 =>
								array(
									'link_id'          => '3',
									'link_name'        => 'Custom Fields',
									'link_url'         => '/page/general/customfields',
									'link_description' => '',
									'link_button'      => '',
									'link_category'    => '',
									'link_order'       => '2',
									'link_parent'      => '1',
									'link_open'        => '',
									'link_class'       => 0,
									'link_sub'         =>
										array(),
									'link_active'      => false,
									'link_identifier'  => 'page-nav-3',
								),
						),
					'link_active'      => false,
					'link_identifier'  => 'page-nav-1',
				),
		);

		$this->assertSame($expected, $result);
	}

	public function testBookNavChaptersPages()
	{

		$result = $this->nav->bookNavChaptersPages();
		$expected = array(
			0 =>
				array(
					'link_id'          => '1',
					'link_name'        => 'General',
					'link_url'         => '/page/general',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '0',
					'link_parent'      => '0',
					'link_open'        => '',
					'link_class'       => 0,
					'link_sub'         =>
						array(
							0 =>
								array(
									'link_id'          => '2',
									'link_name'        => 'Chapter 1',
									'link_url'         => '/page/general/chapter-1',
									'link_description' => '',
									'link_button'      => '',
									'link_category'    => '',
									'link_order'       => '1',
									'link_parent'      => '1',
									'link_open'        => '',
									'link_class'       => 0,
									'link_sub'         =>
										array(
											0 =>
												array(
													'link_id'          => '1',
													'link_name'        => 'Article 1',
													'link_url'         => '/page/general/chapter-1/article-1',
													'link_description' => '',
													'link_button'      => '',
													'link_category'    => '',
													'link_order'       => '9999',
													'link_parent'      => '2',
													'link_open'        => '',
													'link_class'       => 0,
													'link_active'      => false,
													'link_identifier'  => 'page-nav-1',
												),
											1 =>
												array(
													'link_id'          => '2',
													'link_name'        => 'Article 2',
													'link_url'         => '/page/general/chapter-1/article-2',
													'link_description' => '',
													'link_button'      => '',
													'link_category'    => '',
													'link_order'       => '9999',
													'link_parent'      => '2',
													'link_open'        => '',
													'link_class'       => 0,
													'link_active'      => false,
													'link_identifier'  => 'page-nav-2',
												),
											2 =>
												array(
													'link_id'          => '3',
													'link_name'        => 'Article 3',
													'link_url'         => '/page/general/chapter-1/article-3',
													'link_description' => '',
													'link_button'      => '',
													'link_category'    => '',
													'link_order'       => '9999',
													'link_parent'      => '2',
													'link_open'        => '',
													'link_class'       => 0,
													'link_active'      => false,
													'link_identifier'  => 'page-nav-3',
												),
											3 =>
												array(
													'link_id'          => '5',
													'link_name'        => 'Feature 1',
													'link_url'         => '/page/general/chapter-1/feature-1',
													'link_description' => '',
													'link_button'      => '',
													'link_category'    => '',
													'link_order'       => '9999',
													'link_parent'      => '2',
													'link_open'        => '',
													'link_class'       => 0,
													'link_active'      => false,
													'link_identifier'  => 'page-nav-5',
												),
											4 =>
												array(
													'link_id'          => '6',
													'link_name'        => 'Feature 2',
													'link_url'         => '/page/general/chapter-1/feature-2',
													'link_description' => '',
													'link_button'      => '',
													'link_category'    => '',
													'link_order'       => '9999',
													'link_parent'      => '2',
													'link_open'        => '',
													'link_class'       => 0,
													'link_active'      => false,
													'link_identifier'  => 'page-nav-6',
												),
											5 =>
												array(
													'link_id'          => '7',
													'link_name'        => 'Feature 3',
													'link_url'         => '/page/general/chapter-1/feature-3',
													'link_description' => '',
													'link_button'      => '',
													'link_category'    => '',
													'link_order'       => '9999',
													'link_parent'      => '2',
													'link_open'        => '',
													'link_class'       => 0,
													'link_active'      => false,
													'link_identifier'  => 'page-nav-7',
												),
										),
									'link_active'      => false,
									'link_identifier'  => 'page-nav-2',
								),
							1 =>
								array(
									'link_id'          => '3',
									'link_name'        => 'Custom Fields',
									'link_url'         => '/page/general/customfields',
									'link_description' => '',
									'link_button'      => '',
									'link_category'    => '',
									'link_order'       => '2',
									'link_parent'      => '1',
									'link_open'        => '',
									'link_class'       => 0,
									'link_sub'         =>
										array(
											0 =>
												array(
													'link_id'          => '4',
													'link_name'        => 'Article 4',
													'link_url'         => '/page/general/customfields/article-4',
													'link_description' => '',
													'link_button'      => '',
													'link_category'    => '',
													'link_order'       => '9999',
													'link_parent'      => '3',
													'link_open'        => '',
													'link_class'       => 0,
													'link_active'      => false,
													'link_identifier'  => 'page-nav-4',
												),
										),
									'link_active'      => false,
									'link_identifier'  => 'page-nav-3',
								),
						),
					'link_active'      => false,
					'link_identifier'  => 'page-nav-1',
				),
		);

		$this->assertSame($expected, $result);
	}


	public function testChapterNav()
	{

		$result = $this->nav->chapterNav(1);
		$expected = array(
			2 =>
				array(
					'link_name'        => 'Chapter 1',
					'link_url'         => '/page/general/chapter-1',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '',
					'link_parent'      => '1',
					'link_open'        => '',
					'link_class'       => 0,
					'link_sub'         =>
						array(),
					'link_identifier'  => 'page-nav-2',
				),
			3 =>
				array(
					'link_name'        => 'Custom Fields',
					'link_url'         => '/page/general/customfields',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '',
					'link_parent'      => '1',
					'link_open'        => '',
					'link_class'       => 0,
					'link_sub'         =>
						array(),
					'link_identifier'  => 'page-nav-3',
				),
		);


		$this->assertSame($expected, $result);
	}


	public function testPagesFromChapter()
	{

		$result = $this->nav->pagesFromChapter(2);
		$expected = array(
			0 =>
				array(
					'link_id'          => '1',
					'link_name'        => 'Article 1',
					'link_url'         => '/page/general/chapter-1/article-1',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '9999',
					'link_parent'      => '2',
					'link_open'        => '',
					'link_class'       => 0,
					'link_active'      => false,
					'link_identifier'  => 'page-nav-1',
				),
			1 =>
				array(
					'link_id'          => '2',
					'link_name'        => 'Article 2',
					'link_url'         => '/page/general/chapter-1/article-2',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '9999',
					'link_parent'      => '2',
					'link_open'        => '',
					'link_class'       => 0,
					'link_active'      => false,
					'link_identifier'  => 'page-nav-2',
				),
			2 =>
				array(
					'link_id'          => '3',
					'link_name'        => 'Article 3',
					'link_url'         => '/page/general/chapter-1/article-3',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '9999',
					'link_parent'      => '2',
					'link_open'        => '',
					'link_class'       => 0,
					'link_active'      => false,
					'link_identifier'  => 'page-nav-3',
				),
			3 =>
				array(
					'link_id'          => '5',
					'link_name'        => 'Feature 1',
					'link_url'         => '/page/general/chapter-1/feature-1',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '9999',
					'link_parent'      => '2',
					'link_open'        => '',
					'link_class'       => 0,
					'link_active'      => false,
					'link_identifier'  => 'page-nav-5',
				),
			4 =>
				array(
					'link_id'          => '6',
					'link_name'        => 'Feature 2',
					'link_url'         => '/page/general/chapter-1/feature-2',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '9999',
					'link_parent'      => '2',
					'link_open'        => '',
					'link_class'       => 0,
					'link_active'      => false,
					'link_identifier'  => 'page-nav-6',
				),
			5 =>
				array(
					'link_id'          => '7',
					'link_name'        => 'Feature 3',
					'link_url'         => '/page/general/chapter-1/feature-3',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '9999',
					'link_parent'      => '2',
					'link_open'        => '',
					'link_class'       => 0,
					'link_active'      => false,
					'link_identifier'  => 'page-nav-7',
				),
		);

		$this->assertSame($expected, $result);
	}


	public function testChapterNavPages()
	{

		$result = $this->nav->chapterNavPages(1);
		$expected = array(
			2 =>
				array(
					'link_name'        => 'Chapter 1',
					'link_url'         => '/page/general/chapter-1',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '',
					'link_parent'      => '1',
					'link_open'        => '',
					'link_class'       => 0,
					'link_sub'         =>
						array(
							0 =>
								array(
									'link_id'          => '1',
									'link_name'        => 'Article 1',
									'link_url'         => '/page/general/chapter-1/article-1',
									'link_description' => '',
									'link_button'      => '',
									'link_category'    => '',
									'link_order'       => '9999',
									'link_parent'      => '2',
									'link_open'        => '',
									'link_class'       => 0,
									'link_active'      => false,
									'link_identifier'  => 'page-nav-1',
								),
							1 =>
								array(
									'link_id'          => '2',
									'link_name'        => 'Article 2',
									'link_url'         => '/page/general/chapter-1/article-2',
									'link_description' => '',
									'link_button'      => '',
									'link_category'    => '',
									'link_order'       => '9999',
									'link_parent'      => '2',
									'link_open'        => '',
									'link_class'       => 0,
									'link_active'      => false,
									'link_identifier'  => 'page-nav-2',
								),
							2 =>
								array(
									'link_id'          => '3',
									'link_name'        => 'Article 3',
									'link_url'         => '/page/general/chapter-1/article-3',
									'link_description' => '',
									'link_button'      => '',
									'link_category'    => '',
									'link_order'       => '9999',
									'link_parent'      => '2',
									'link_open'        => '',
									'link_class'       => 0,
									'link_active'      => false,
									'link_identifier'  => 'page-nav-3',
								),
							3 =>
								array(
									'link_id'          => '5',
									'link_name'        => 'Feature 1',
									'link_url'         => '/page/general/chapter-1/feature-1',
									'link_description' => '',
									'link_button'      => '',
									'link_category'    => '',
									'link_order'       => '9999',
									'link_parent'      => '2',
									'link_open'        => '',
									'link_class'       => 0,
									'link_active'      => false,
									'link_identifier'  => 'page-nav-5',
								),
							4 =>
								array(
									'link_id'          => '6',
									'link_name'        => 'Feature 2',
									'link_url'         => '/page/general/chapter-1/feature-2',
									'link_description' => '',
									'link_button'      => '',
									'link_category'    => '',
									'link_order'       => '9999',
									'link_parent'      => '2',
									'link_open'        => '',
									'link_class'       => 0,
									'link_active'      => false,
									'link_identifier'  => 'page-nav-6',
								),
							5 =>
								array(
									'link_id'          => '7',
									'link_name'        => 'Feature 3',
									'link_url'         => '/page/general/chapter-1/feature-3',
									'link_description' => '',
									'link_button'      => '',
									'link_category'    => '',
									'link_order'       => '9999',
									'link_parent'      => '2',
									'link_open'        => '',
									'link_class'       => 0,
									'link_active'      => false,
									'link_identifier'  => 'page-nav-7',
								),
						),
					'link_identifier'  => 'page-nav-2',
				),
			3 =>
				array(
					'link_name'        => 'Custom Fields',
					'link_url'         => '/page/general/customfields',
					'link_description' => '',
					'link_button'      => '',
					'link_category'    => '',
					'link_order'       => '',
					'link_parent'      => '1',
					'link_open'        => '',
					'link_class'       => 0,
					'link_sub'         =>
						array(
							0 =>
								array(
									'link_id'          => '4',
									'link_name'        => 'Article 4',
									'link_url'         => '/page/general/customfields/article-4',
									'link_description' => '',
									'link_button'      => '',
									'link_category'    => '',
									'link_order'       => '9999',
									'link_parent'      => '3',
									'link_open'        => '',
									'link_class'       => 0,
									'link_active'      => false,
									'link_identifier'  => 'page-nav-4',
								),
						),
					'link_identifier'  => 'page-nav-3',
				),
		);


		$this->assertSame($expected, $result);

	}

	function testPageNavigationShortcode()
	{
		$result = e107::getParser()->parseTemplate('{PAGE_NAVIGATION: book=1&chapters=true&pages=true}');

		$this->assertStringContainsString('Chapter 1', $result);
		$this->assertStringContainsString('Article 3', $result);
		$this->assertStringContainsString('Custom Fields', $result);
		$this->assertStringContainsString('Article 4', $result);
	}

	/**
	 * {PAGE_NAVIGATION: name=...} names a link from the page's menu title for a reader the menu admits, and otherwise from the page title.
	 */
	public function testPageNavigationNamesLinksOnlyFromATitleTheReaderMaySee()
	{
		$sql = e107::getDb();
		$page = $sql->createQueryBuilder()
			->select('page_id', 'page_chapter', 'page_title', 'page_password', 'menu_title', 'menu_class')->from('page')
			->where('page_chapter', '>', 0)->where('page_title', '!=', '')
			->orderBy('page_id')->limit(1)->fetchRow();
		$set = function($values) use ($sql, $page)
		{
			$update = $sql->createQueryBuilder()->update('page');
			foreach($values as $column => $value)
			{
				$update->set($column, $value);
			}
			$update->where('page_id', (int) $page['page_id'])->execute();
		};
		$navigation = function($name) use ($page)
		{
			return e107::getParser()->parseTemplate('{PAGE_NAVIGATION: chapter='.intval($page['page_chapter']).'&name='.$name.'}');
		};

		try
		{
			$set(array('page_password' => 'PageNavPassword', 'menu_title' => 'PageNavMenuTitle', 'menu_class' => '0'));
			$password = $navigation('page_password');
			$menuTitle = $navigation('menu_title');

			$set(array('menu_class' => (string) e_UC_NOBODY));
			$hiddenMenuTitle = $navigation('menu_title');

			$set(array('menu_title' => '', 'menu_class' => '0'));
			$emptyMenuTitle = $navigation('menu_title');
		}
		finally
		{
			$set(array('page_password' => $page['page_password'], 'menu_title' => $page['menu_title'], 'menu_class' => $page['menu_class']));
		}

		$this->assertStringNotContainsString('PageNavPassword', $password);
		$this->assertStringContainsString($page['page_title'], $password);
		$this->assertStringContainsString('PageNavMenuTitle', $menuTitle);
		$this->assertStringNotContainsString('PageNavMenuTitle', $hiddenMenuTitle);
		$this->assertStringContainsString($page['page_title'], $hiddenMenuTitle);
		$this->assertStringContainsString($page['page_title'], $emptyMenuTitle);
	}

}
