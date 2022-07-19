<?php


	class redirectionTest extends \Codeception\Test\Unit
	{

		/** @var redirection */
		protected $rd;

		protected function _before()
		{

			try
			{
				$this->rd = $this->make('redirection');
			}

			catch(Exception $e)
			{
				$this->fail($e->getMessage());
			}

		}

/*		public function testRedirect()
		{

		}

		public function testGetPreviousUrl()
		{

		}

		public function testGo()
		{

		}

		public function testCheckMaintenance()
		{

		}

		public function testSetPreviousUrl()
		{

		}

		public function testRedirectPrevious()
		{

		}

		public function testGetSelfExceptions()
		{

		}

		public function testGetCookie()
		{

		}

		public function testCheckMembersOnly()
		{

		}

		public function testSetCookie()
		{

		}

		public function testClearCookie()
		{

		}

		public function testGetSelf()
		{

		}*/

		public function testRedirectStaticDomain()
		{
			$result = $this->rd->redirectStaticDomain();
			$this->assertEmpty($result);

			$this->rd->domain = 'e107.org';
			$this->rd->subdomain = 'static1';
			$this->rd->staticDomains = ['https://static1.e107.org', 'https://static2.e107.org'];

			$this->rd->self = 'https://static1.e107.org/blogs';
			$this->rd->siteurl = 'https://e107.org/';

			$result = $this->rd->redirectStaticDomain();

			$this->assertSame("https://e107.org/blogs", $result);

		}




	}
