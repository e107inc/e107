<?php


class e_renderTest extends \Codeception\Test\Unit
{

	/** @var e_render */
	protected $ns;

	protected function _before()
	{

		try
		{
			$this->ns = $this->make('e_render');
		}

		catch(Exception $e)
		{
			$this->assertTrue(false, $e->getMessage());
		}

		$this->ns->_init(); // load theme preferences.

	}

	public function testSetGetContent()
	{

		$unique = 'news-view-default';

		$this->ns->setUniqueId($unique);
		$this->ns->setContent('title', 'news-title');
		$this->ns->setContent('text', 'news-summary');
		$this->ns->setUniqueId(false); // reset the ID.

		$this->ns->tablerender('caption', 'other', 'default', true); // render a different table.

		$result = $this->ns->setUniqueId($unique)->getContent(); // get content using uniqueId.
		$expected = array('title' => 'news-title', 'text' => 'news-summary',);
		$this->assertEquals($expected, $result);


		$result = $this->ns->getContent('title');
		$this->assertEquals('news-title', $result);


	}

	/*		public function test_init()
			{

			}

			public function testSetStyle()
			{

			}

			public function testSetUniqueId()
			{

			}

			public function testGetContent()
			{

			}

			public function testGetStyle()
			{

			}*/

	public function testTablerender()
	{

		$result = $this->ns->tablerender("My Caption", "<p>My Content</p>", 'default', true);
		$this->assertStringContainsString('<h2 class="caption">My Caption</h2><p>My Content</p>', $result);
	}
/*
	public function testGetMagicShortcodes()
	{

	}

	public function testGetUniqueId()
	{

	}

	public function testSetContent()
	{

	}

	public function testInit()
	{

	}

	public function testGetMainCaption()
	{

	}
*/

}
