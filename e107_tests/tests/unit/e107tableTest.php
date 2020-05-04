<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e107tableTest extends \Codeception\Test\Unit
	{

		/** @var e107table */
		protected $ns;

		protected function _before()
		{

			try
			{
				$this->ns = $this->make('e107table');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e107table object");
			}

			$this->ns->init();

		}
/*
		public function testGetStyle()
		{

		}

		public function testSetUniqueId()
		{

		}
*/

		public function testSetGetContent()
		{

			$unique = 'news-view-default';

			$this->ns->setUniqueId($unique);
			$this->ns->setContent('title', 'news-title');
			$this->ns->setContent('text', 'news-summary');
			$this->ns->setUniqueId(false); // reset the ID.

			$this->ns->tablerender('caption', 'other', 'default', true); // render a different table.

			$result = $this->ns->setUniqueId($unique)->getContent(); // get content using uniqueId.
			$expected = array (  'title' => 'news-title',   'text' => 'news-summary', );
			$this->assertEquals($expected, $result);


			$result = $this->ns->getContent('title');
			$this->assertEquals('news-title', $result);


		}
/*
		public function testGetMagicShortcodes()
		{

		}

		public function testGetContent()
		{

		}

		public function testGetMainCaption()
		{

		}

		public function testTablerender()
		{

		}

		public function testSetStyle()
		{

		}

		public function testGetUniqueId()
		{

		}
*/



	}
