<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_userTest extends \Codeception\Test\Unit
	{

		protected $user;

		protected function _before()
		{
			try
			{
				$this->user = $this->make('e_user');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_user object");
			}
		}



		public function testIsCurrent()
		{

		}

		public function testGetProvider()
		{

		}

		public function testLogoutAs()
		{

		}

		public function testInitProvider()
		{

		}

		public function testSetSessionData()
		{

		}

		public function test__construct()
		{

		}

		public function testLogin()
		{

		}

		public function testLogout()
		{

		}

		public function testHasProvider()
		{

		}

		public function testSetProvider()
		{

		}

		public function testGetSessionDataAs()
		{

		}

		public function testLoginProvider()
		{

		}

		public function testLoginAs()
		{

		}

		public function testTryProviderSession()
		{

		}

		public function testLoad()
		{

		}

		public function testLoadAs()
		{

		}

		public function testGetParentId()
		{

		}

		public function testDestroy()
		{

		}

		public function testHasSessionError()
		{

		}
	}
