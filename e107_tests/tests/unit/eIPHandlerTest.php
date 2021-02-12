<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2020 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */



	class eIPHandlerTest extends \Codeception\Test\Unit
	{

		/** @var eIPHandler */
		protected $ip;

		protected function _before()
		{

			try
			{
				$this->ip = $this->make('eIPHandler');
			} catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load eIPHandler object");
			}

			$this->__construct();
		}

/*		public function testMakeEmailQuery()
		{

		}

		public function testGet_host_name()
		{

		}

		public function testSetIP()
		{

		}

		public function testIpDecode()
		{

		}

		public function testWhatIsThis()
		{

		}

		public function testIp6AddWildcards()
		{

		}

		public function testIsUserLogged()
		{

		}

		public function testCheckFilePerms()
		{

		}

		public function test__construct()
		{

		}

		public function testCheckBan()
		{

		}

		public function testPermsToString()
		{

		}

		public function testMakeDomainQuery()
		{

		}*/

		public function testAdd_ban()
		{
			// $bantype = 1 for manual, 2 for flooding, 4 for multiple logins

			$banDurations = array(
			'0' => 0,
			'-1' => 0, // manually added ban
			'-2' => 0, // flood
			'-3' => 8, // hits
			'-4' => 10, // multi-login
			'-5' => 0, // imported
			'-6' => 0,  // banned user
			'-8' => 0 // unknown
			);

			//set ban duration pref.
			e107::getConfig()->set('ban_durations',$banDurations)->save(false,true, false);

			$result = $this->ip->add_ban(2,"unit test generated ban", '123.123.123.123', 0);
			$this->assertTrue($result);


		}
/*
		public function testGetIP()
		{

		}

		public function testGetConfigDir()
		{

		}

		public function testRegenerateFiles()
		{

		}

		public function testBan()
		{

		}

		public function testIsAddressRoutable()
		{

		}

		public function testIpEncode()
		{

		}

		public function testDebug()
		{

		}

		public function testGetUserToken()
		{

		}

	*/


	}
