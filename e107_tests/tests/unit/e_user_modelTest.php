<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_user_modelTest extends \Codeception\Test\Unit
	{

		/** @var e_user_model */
		protected $usr;

		protected function _before()
		{

			try
			{
				$this->usr = $this->make('e_user_model');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_user_model object");
			}

			$this->usr->load(1); // load user_id  = 1.

		}

/*		public function testSave()
		{

		}

		public function testGetAdminEmail()
		{

		}*/

		public function testGetClassList()
		{
			$result = $this->usr->getClassList();
			$this->assertContains(e_UC_MEMBER, $result);
			$this->assertContains(e_UC_ADMIN, $result);
			$this->assertContains(e_UC_MAINADMIN, $result);

			$result = $this->usr->getClassList(true);
			$result = array_map('intval', explode(',', $result));
			$this->assertContains(e_UC_MEMBER, $result);
			$this->assertContains(e_UC_ADMIN, $result);
			$this->assertContains(e_UC_MAINADMIN, $result);
		}

/*		public function testIsNewUser()
		{

		}

		public function testGetCore()
		{

		}

		public function testGetEditor()
		{

		}

		public function testDestroy()
		{

		}

		public function testRemoveClass()
		{

		}

		public function testGetAdminName()
		{

		}

		public function testCheckToken()
		{

		}

		public function testFindPref()
		{

		}

		public function testLoad()
		{

		}

		public function testGetAdminId()
		{

		}

		public function testSaveDebug()
		{

		}

		public function testSetCore()
		{

		}

		public function testHasRestriction()
		{

		}

		public function testGetExtendedFront()
		{

		}

		public function testGetTimezone()
		{

		}

		public function testIsExtendedField()
		{

		}

		public function testSetPrefData()
		{

		}

		public function testIsAdmin()
		{

		}

		public function testIsCurrent()
		{

		}

		public function testIsWritable()
		{

		}*/

		public function testGetName()
		{
			$result = $this->usr->getName();
			$this->assertEquals('e107', $result);
		}
/*
		public function testGetAdminPerms()
		{

		}

		public function testIsCoreField()
		{

		}

		public function testHasProviderName()
		{

		}

		public function testMergePostedData()
		{

		}

		public function testGetDisplayName()
		{

		}

		public function testGetClassRegex()
		{

		}

		public function testIsGuest()
		{

		}

		public function testGetAdminPwchange()
		{

		}

		public function testSetEditor()
		{

		}

		public function testGetUserData()
		{

		}

		public function testSetPref()
		{

		}

		public function testAddClass()
		{

		}

		public function testHasEditor()
		{

		}

		public function testGetConfig()
		{

		}

		public function testIsReadable()
		{

		}

		public function testGetValue()
		{

		}

		public function testGetToken()
		{

		}

		public function testGetExtendedModel()
		{

		}*/

		public function testRandomKey()
		{
			$obj = $this->usr;

			$result = $obj::randomKey();

			$this->assertEquals(32,strlen($result));

		}
/*
		public function testGetSignatureValue()
		{

		}

		public function testGetId()
		{

		}

		public function testSetConfig()
		{

		}

		public function testGetPref()
		{

		}

		public function testGetRealName()
		{

		}

		public function testCheckClass()
		{

		}

		public function testHasBan()
		{

		}

		public function testSetSystem()
		{

		}

		public function testCheckAdminPerms()
		{

		}

		public function testCheckEditorPerms()
		{

		}

		public function test__construct()
		{

		}

		public function testIsUser()
		{

		}

		public function testSetValue()
		{

		}

		public function testSetSignatureValue()
		{

		}

		public function testGetProviderName()
		{

		}

		public function testSetExtendedModel()
		{

		}

		public function testSetExtendedFront()
		{

		}

		public function testGetExtended()
		{

		}

		public function testGetLoginName()
		{

		}*/

		public function testIsBot()
		{
			$result = $this->usr->isBot();
			$this->assertFalse($result);
		}
/*
		public function testSetExtended()
		{

		}

		public function testGetSystem()
		{

		}

		public function testIsMainAdmin()
		{

		}
*/



	}
