<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class UserHandlerTest extends \Test\Unit
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

		/**
		 * GHSA-m8v8-wc99-3h82: user_xup sat in the member-editable whitelist, so a
		 * profile update carried it through to the caller's own account even though
		 * no form renders the field. Only the OAuth callback may write that column.
		 */
		public function testUserXupIsNotAcceptedFromPostedFields()
		{
			// Not $this->usr: make() skips the constructor that builds the whitelist.
			$vettingInfo = e107::getUserSession()->userVettingInfo;

			$posted = array(
				'realname' => 'Legitimate Real Name',
				'user_xup' => 'Facebook_100000000000001',
			);

			$result = validatorClass::validateFields($posted, $vettingInfo, true);

			$this->assertArrayNotHasKey('user_xup', $vettingInfo);
			$this->assertArrayNotHasKey('user_xup', $result['data']);
			$this->assertSame('Legitimate Real Name', $result['data']['user_login']);
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
