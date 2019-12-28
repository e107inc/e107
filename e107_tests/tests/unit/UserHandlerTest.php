<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class UserHandlerTest extends \Codeception\Test\Unit
	{

		/** @var UserHandler */
		protected $usr;

		protected function _before()
		{

			try
			{
				$this->usr = $this->make('UserHandler');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load UserHandler object");
			}

		}

/*
		public function testCheckPassword()
		{

		}

		public function testDeleteExpired()
		{

		}

		public function testIsPasswordRequired()
		{

		}

		public function testAddCommonClasses()
		{

		}

		public function test__construct()
		{

		}

		public function testResetPassword()
		{

		}

		public function testMakeUserCookie()
		{

		}

		public function testUserValidation()
		{

		}

		public function testConvertPassword()
		{

		}

		public function testHasReadonlyField()
		{

		}

		public function testRehashPassword()
		{

		}

		public function testNeedEmailPassword()
		{

		}

		public function testHashPassword()
		{

		}

		public function testCanConvert()
		{

		}

		public function testCheckCHAP()
		{

		}

		public function testUserClassUpdate()
		{

		}

		public function testGetHashType()
		{

		}

		public function testGenerateUserLogin()
		{

		}

		public function testGenerateRandomString()
		{

		}

		public function testGetDefaultHashType()
		{

		}

		public function testPasswordAPIExists()
		{

		}

		public function testAddNonDefaulted()
		{

		}

		public function testGetNiceNames()
		{

		}

		public function testUserStatusUpdate()
		{

		}

*/

	}
