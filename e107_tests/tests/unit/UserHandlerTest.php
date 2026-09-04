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
				$this->fail("Couldn't load UserHandler object");
			}

		}

		/** Admin > Password reads both of the handler's field-type tables from outside the class to build the _FIELD_TYPES envelope it writes with. */
		public function testFieldTypesAreReadableFromOutsideTheHandler()
		{

			$userMethods = e107::getUserSession();

			$userData = array('data' => array(
				'user_password' => 'a-stored-hash',
				'user_prefs'    => 'a:0:{}',
				'user_pwchange' => 1756944000,
			));

			validatorClass::addFieldTypes($userMethods->userVettingInfo, $userData, $userMethods->otherFieldTypes);

			$this->assertSame(array(
				'user_password' => 'string',
				'user_prefs'    => 'string',
				'user_pwchange' => 'int',
			), $userData['_FIELD_TYPES']);

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
