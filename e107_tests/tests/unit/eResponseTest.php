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
