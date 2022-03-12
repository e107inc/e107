<?php


class page_sitelinkTest extends \Codeception\Test\Unit
{

	/** @var page_sitelink */
	protected $nav;

	protected function _before()
	{

		// Enable SEF Urls. book/chapter/page
		e107::getConfig()->setPref('url_config/page', 'core/sef_chapters')->save(false, true, false);

		/** @var eRouter $router */
		$router = e107::getUrl()->router(); // e107::getSingleton('eRouter');
		$rules = $router->getRuleSets();

		if(empty($rules['page']))
		{
			$router->loadConfig(true);
		}

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
		e107::getConfig()->setPref('url_config/page', 'core')->save(false, true, false); // disable SEF Urls.
	}

	/*
			public function testPageNav()
			{

			}

			public function testPageList()
			{

			}

			public function testConfig()
			{

			}
	*/


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

}
