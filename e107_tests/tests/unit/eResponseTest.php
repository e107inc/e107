<?php


	class eResponseTest extends \Codeception\Test\Unit
	{

		/** @var eResponse */
		protected $er;

		protected function _before()
		{

			try
			{
				$this->er = $this->make('eResponse');
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

		}
/*
		public function testGetRobotDescriptions()
		{

		}

		public function testSetParams()
		{

		}

		public function testGetMetaData()
		{

		}

		public function testGetMetaDescription()
		{

		}

		public function testSetParam()
		{

		}
*/
		public function testAddMeta()
		{
			$title = "Admin's Blog Title";
			$this->er->addMeta('og:title', $title);
			$result = $this->er->getMeta('og:title');

			$expected = array (
			  'og:title' =>
			  array (
			    'property' => 'og:title',
			    'content' => "Admin's Blog Title",
			  ),
			);

			$this->assertSame($expected, $result);

		}
/*
		public function testAddMetaDescription()
		{

		}

		public function testGetRenderMod()
		{

		}

		public function testIsParam()
		{

		}

		public function testSetContentType()
		{

		}

		public function testGetTitle()
		{

		}

		public function testRemoveMeta()
		{

		}

		public function testSetMeta()
		{

		}

		public function testSetRenderMod()
		{

		}

		public function testAppendTitle()
		{

		}

		public function testPrependBody()
		{

		}

		public function testPrependTitle()
		{

		}

		public function testGetParam()
		{

		}

		public function testAddMetaKeywords()
		{

		}

		public function testGetMeta()
		{

		}

		public function testGetBody()
		{

		}

		public function testSendContentType()
		{

		}

		public function testGetRobotTypes()
		{

		}
*/
		public function testAddMetaData()
		{

			$title = "Admin's Blog Title";

			$this->er->addMetaData('e_PAGETITLE', $title);
			$result = $this->er->getMetaData('e_PAGETITLE');

			$this->assertSame("Admin's Blog Title", $result);

			$title = ' - "Quote"';

			$this->er->addMetaData('e_PAGETITLE', $title);
			$result = $this->er->getMetaData('e_PAGETITLE');

			$this->assertSame("Admin's Blog Title - \"Quote\"", $result);

		}
/*
		public function testSendJson()
		{

		}

		public function testGetJs()
		{

		}

		public function testGetContentMediaType()
		{

		}

		public function testSetTitle()
		{

		}

		public function testGetMetaKeywords()
		{

		}

		public function testAddContentType()
		{

		}

		public function testGetMetaTitle()
		{

		}

		public function testSendMeta()
		{

		}

		public function testAddHeader()
		{

		}

		public function testAppendBody()
		{

		}

		public function testGetContentType()
		{

		}

		public function testRenderMeta()
		{

		}

		public function testSend()
		{

		}

		public function testSetBody()
		{

		}
*/
		public function testAddMetaTitle()
		{
			$title = 'Admin&#39;s Blog Title';
			$this->er->addMetaTitle($title);
			$result = $this->er->getMetaTitle();
			$this->assertSame("Admin's Blog Title", $result);


			$title = ' "quote"';
			$this->er->addMetaTitle($title);
			$result = $this->er->getMetaTitle();
			$this->assertSame("Admin's Blog Title -  \"quote\"", $result);


			$title = 'Cam&#039;s Fixed &quot;Meta&quot;';
			$this->er->addMetaTitle($title, true);
			$result = $this->er->getMetaTitle();
			$this->assertSame("Cam's Fixed &quot;Meta&quot;", $result);

		}




	}
